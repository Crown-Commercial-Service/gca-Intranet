<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cron run history logger.
 *
 * Stores run history in wp_gca_cron_log and provides query helpers used
 * by GCA_Cron_Manager to render the Logs page and Last Run column.
 */
class GCA_Cron_Logger {

    const TABLE_VERSION_OPTION = 'gca_cron_log_db_version';
    const TABLE_VERSION        = 1;
    const PURGE_HOOK           = 'gca_cron_log_purge';

    /**
     * Hooks that are watched for scheduled-run detection (priority sandwich).
     * Add a hook name here whenever a new GCA cron job is registered.
     */
    const WATCHED_HOOKS = [
        'gca_sync_workday_users',
    ];

    /** @var array<string, array{start: float, log_id: int|null}> */
    private static array $run_state = [];

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    public static function init(): void {
        self::create_table();

        // Weekly purge of old log rows.
        if (!wp_next_scheduled(self::PURGE_HOOK)) {
            wp_schedule_event(time(), 'weekly', self::PURGE_HOOK);
        }
        add_action(self::PURGE_HOOK, [__CLASS__, 'purge_old']);

        // Structured-stats pickup from cooperative hooks.
        add_action('gca_cron_run_completed', [__CLASS__, 'handle_run_completed'], 10, 3);

        // Priority-sandwich for scheduled-run detection on known hooks.
        foreach (self::WATCHED_HOOKS as $hook) {
            add_action($hook, [__CLASS__, 'before_scheduled_run'], 1);
            add_action($hook, [__CLASS__, 'after_scheduled_run'], PHP_INT_MAX);
        }
    }

    // -------------------------------------------------------------------------
    // Table management
    // -------------------------------------------------------------------------

    public static function create_table(): void {
        if ((int) get_option(self::TABLE_VERSION_OPTION) >= self::TABLE_VERSION) {
            return;
        }

        global $wpdb;

        $table   = self::table_name();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            hook         VARCHAR(255)    NOT NULL,
            triggered_by ENUM('manual','schedule') NOT NULL DEFAULT 'schedule',
            ran_at       DATETIME        NOT NULL,
            duration_ms  INT UNSIGNED    NOT NULL DEFAULT 0,
            status       ENUM('success','error') NOT NULL DEFAULT 'success',
            output       LONGTEXT,
            stats        TEXT,
            PRIMARY KEY (id),
            KEY hook_ran (hook, ran_at)
        ) ENGINE=InnoDB {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::TABLE_VERSION_OPTION, self::TABLE_VERSION);
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    /**
     * Insert a log row and return the inserted ID.
     *
     * @param array{
     *     hook: string,
     *     triggered_by?: 'manual'|'schedule',
     *     duration_ms?: int,
     *     status?: 'success'|'error',
     *     output?: string,
     *     stats?: string|null,
     * } $data
     */
    public static function insert(array $data): int {
        global $wpdb;

        $wpdb->insert(
            self::table_name(),
            [
                'hook'         => $data['hook'],
                'triggered_by' => $data['triggered_by'] ?? 'schedule',
                'ran_at'       => current_time('mysql', true),
                'duration_ms'  => $data['duration_ms'] ?? 0,
                'status'       => $data['status'] ?? 'success',
                'output'       => $data['output'] ?? '',
                'stats'        => $data['stats'] ?? null,
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    /**
     * Fetch recent runs for one hook, newest first.
     *
     * @return array<int, array{id:int, hook:string, triggered_by:string, ran_at:string, duration_ms:int, status:string, output:string, stats:string|null}>
     */
    public static function get_recent(string $hook, int $limit = 25, int $offset = 0): array {
        global $wpdb;

        $table = self::table_name();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE hook = %s ORDER BY ran_at DESC, id DESC LIMIT %d OFFSET %d",
                $hook,
                $limit,
                $offset
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Fetch the N most-recent runs per hook (used for the Last Run column).
     * Returns array keyed by hook name, each value an array of up to $per_hook rows.
     *
     * @return array<string, list<array>>
     */
    public static function get_all_recent(int $per_hook = 1): array {
        global $wpdb;

        $table = self::table_name();

        // One query: fetch the latest $per_hook rows for each distinct hook.
        // Uses a self-join rank trick compatible with MySQL 5.6+.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*
                 FROM {$table} a
                 LEFT JOIN {$table} b
                   ON a.hook = b.hook AND (a.ran_at < b.ran_at OR (a.ran_at = b.ran_at AND a.id < b.id))
                 GROUP BY a.id
                 HAVING COUNT(b.id) < %d
                 ORDER BY a.hook, a.ran_at DESC, a.id DESC",
                $per_hook
            ),
            ARRAY_A
        ) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['hook']][] = $row;
        }
        return $result;
    }

    /**
     * Count total runs for one hook (for pagination).
     */
    public static function count(string $hook): int {
        global $wpdb;
        $table = self::table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE hook = %s", $hook));
    }

    // -------------------------------------------------------------------------
    // Maintenance
    // -------------------------------------------------------------------------

    public static function purge_old(int $days = 30): void {
        global $wpdb;
        $table = self::table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table} WHERE ran_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }

    // -------------------------------------------------------------------------
    // Scheduled-run detection (priority sandwich)
    // -------------------------------------------------------------------------

    public static function before_scheduled_run(): void {
        $hook = current_filter();

        // Manual runs: handle_run_now() owns the ob buffer and row insertion.
        // Preserve any log_id already set, just record start time.
        if ( // phpcs:ignore WordPress.Security.NonceVerification
            isset($_POST['action']) &&
            'gca_cron_run_now' === $_POST['action']
        ) {
            self::$run_state[$hook]['start'] = microtime(true);
            return;
        }

        self::$run_state[$hook] = [
            'start'  => microtime(true),
            'log_id' => null,
        ];
        ob_start();
    }

    public static function after_scheduled_run(): void {
        $hook  = current_filter();
        $state = self::$run_state[$hook] ?? null;

        if (!$state) {
            return;
        }

        // Manual runs: handle_run_now_bg() handles insertion and ob cleanup.
        if (in_array( // phpcs:ignore WordPress.Security.NonceVerification
            $_POST['action'] ?? '',
            ['gca_cron_run_now', 'gca_cron_run_now_bg'],
            true
        )) {
            return;
        }

        $output      = ob_get_clean();
        $duration_ms = (int) round((microtime(true) - $state['start']) * 1000);

        $log_id = self::insert([
            'hook'         => $hook,
            'triggered_by' => 'schedule',
            'duration_ms'  => $duration_ms,
            'status'       => 'success',
            'output'       => $output ?: '',
        ]);

        self::$run_state[$hook]['log_id'] = $log_id;
    }

    // -------------------------------------------------------------------------
    // Structured-stats pickup (gca_cron_run_completed)
    // -------------------------------------------------------------------------

    /**
     * Fired by cooperative cron callbacks (e.g. GCA_Sync_Users::run()) with
     * structured stats. Updates the most-recently inserted row for this hook.
     *
     * @param string     $hook      The cron hook name.
     * @param array|null $stats     Associative stats array, or null on error.
     * @param string[]   $log_lines Individual log lines from the run.
     */
    public static function handle_run_completed(string $hook, ?array $stats, array $log_lines): void {
        global $wpdb;

        $table  = self::table_name();
        $log_id = self::$run_state[$hook]['log_id'] ?? null;

        $stats_json = $stats ? wp_json_encode($stats) : null;
        $output     = implode("\n", $log_lines);
        $status     = null === $stats ? 'error' : 'success';

        if ($log_id) {
            // Update the row already inserted by after_scheduled_run / handle_run_now_bg.
            $wpdb->update(
                $table,
                ['stats' => $stats_json, 'output' => $output, 'status' => $status],
                ['id' => $log_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        }
        // If there's no existing row (e.g., the hook wasn't in WATCHED_HOOKS or
        // this fired outside the sandwich context) we don't insert here — the
        // caller is responsible for ensuring a row exists first.
    }

    /**
     * Allow handle_run_now() to register the log_id so handle_run_completed
     * can update it with structured stats from cooperative hooks.
     */
    public static function set_run_log_id(string $hook, int $log_id): void {
        if (!isset(self::$run_state[$hook])) {
            self::$run_state[$hook] = ['start' => microtime(true), 'log_id' => null];
        }
        self::$run_state[$hook]['log_id'] = $log_id;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'gca_cron_log';
    }
}
