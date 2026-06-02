<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cron Manager admin page.
 *
 * Registered via the 'cron-manager' feature flag in inc/features/cron-manager.php.
 * Accessible at WP Admin → Tools → Cron Jobs.
 */
class GCA_Cron_Manager {

    const MENU_SLUG    = 'gca-cron-manager';
    const NONCE_CREATE = 'gca_cron_create';
    const NONCE_DELETE = 'gca_cron_delete';
    const NONCE_RUN    = 'gca_cron_run_now';
    const NONCE_EDIT   = 'gca_cron_edit';

    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'add_admin_page']);
        add_action('admin_post_gca_cron_create',          [__CLASS__, 'handle_create']);
        add_action('admin_post_gca_cron_delete',          [__CLASS__, 'handle_delete']);
        add_action('admin_post_gca_cron_run_now',         [__CLASS__, 'handle_run_now']);
        add_action('admin_post_gca_cron_edit',            [__CLASS__, 'handle_edit']);
        add_action('admin_notices',                       [__CLASS__, 'show_notices']);
    }

    public static function add_admin_page(): void {
        add_management_page(
            'Cron Jobs',
            'Cron Jobs',
            'manage_options',
            self::MENU_SLUG,
            [__CLASS__, 'render_admin_page']
        );
    }

    // -------------------------------------------------------------------------
    // Form handlers
    // -------------------------------------------------------------------------

    public static function handle_create(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer(self::NONCE_CREATE);

        $hook      = wp_unslash(sanitize_text_field($_POST['cron_hook'] ?? ''));
        $schedule  = sanitize_key($_POST['cron_schedule'] ?? '');
        $time_str  = sanitize_text_field($_POST['cron_next_run'] ?? '');
        $recurring = !empty($_POST['cron_recurring']);

        if ('' === $hook) {
            self::redirect(['error' => 'empty_hook']);
        }

        $timestamp = false;
        if ($time_str) {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $time_str, wp_timezone());
            if ($dt) {
                $timestamp = $dt->getTimestamp();
            }
        }
        if (!$timestamp || $timestamp < time()) {
            $timestamp = time() + 300; // fallback: 5 minutes from now
        }

        if ($recurring && $schedule) {
            $schedules = wp_get_schedules();
            if (!isset($schedules[$schedule])) {
                self::redirect(['error' => 'invalid_schedule']);
            }
            wp_schedule_event($timestamp, $schedule, $hook);
        } else {
            wp_schedule_single_event($timestamp, $hook);
        }

        self::redirect(['created' => '1']);
    }

    public static function handle_delete(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer(self::NONCE_DELETE);

        $hook      = wp_unslash(sanitize_text_field($_POST['cron_hook'] ?? ''));
        $timestamp = (int) ($_POST['cron_timestamp'] ?? 0);
        $key       = sanitize_text_field($_POST['cron_key'] ?? '');

        if ($hook && $timestamp) {
            $crons = _get_cron_array();
            $args  = $crons[$timestamp][$hook][$key]['args'] ?? [];
            wp_unschedule_event($timestamp, $hook, $args);
        }

        self::redirect(['deleted' => '1']);
    }

    public static function handle_run_now(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer(self::NONCE_RUN);

        $hook      = wp_unslash(sanitize_text_field($_POST['cron_hook'] ?? ''));
        $timestamp = (int) ($_POST['cron_timestamp'] ?? 0);
        $key       = sanitize_text_field($_POST['cron_key'] ?? '');

        $log_id = 0;

        if ($hook && $timestamp) {
            $crons = _get_cron_array();
            $event = $crons[$timestamp][$hook][$key] ?? null;
            if ($event) {
                if (class_exists('GCA_Cron_Logger')) {
                    $log_id = GCA_Cron_Logger::insert([
                        'hook'         => $hook,
                        'triggered_by' => 'manual',
                    ]);
                    GCA_Cron_Logger::set_run_log_id($hook, $log_id);
                }

                // Send the redirect response to the browser immediately so the
                // admin page is not blocked. On PHP-FPM, fastcgi_finish_request()
                // closes the HTTP connection while the PHP process keeps running.
                $redirect_url = $log_id
                    ? add_query_arg(
                        ['page' => self::MENU_SLUG, 'action' => 'logs', 'hook' => rawurlencode($hook), 'log_id' => $log_id],
                        admin_url('tools.php')
                    )
                    : add_query_arg(['page' => self::MENU_SLUG, 'run' => '1'], admin_url('tools.php'));

                // Prevent PHP from aborting when the browser closes the
                // connection after following the redirect.
                ignore_user_abort(true);

                if (!headers_sent()) {
                    header('Location: ' . $redirect_url, true, 302);
                    header('Connection: close');
                    header('Content-Length: 0');
                }
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                flush();
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

                $start = microtime(true);
                ob_start();
                try {
                    do_action_ref_array($hook, $event['args']);
                    $failed = false;
                } catch (Throwable $e) {
                    error_log('[GCA Cron] Uncaught error running ' . $hook . ': ' . $e->getMessage());
                    $failed = true;
                }
                $duration_ms = (int) round((microtime(true) - $start) * 1000);
                $output      = ob_get_clean();

                if ($log_id && class_exists('GCA_Cron_Logger')) {
                    global $wpdb;
                    $update = ['duration_ms' => $duration_ms];
                    $format = ['%d'];
                    // Only overwrite output if the ob buffer captured something —
                    // cooperative hooks (e.g. gca_sync_workday_users) write their
                    // output via gca_cron_run_completed → handle_run_completed.
                    if ('' !== $output) {
                        $update['output'] = $output;
                        $format[]         = '%s';
                    }
                    if ($failed) {
                        $update['status'] = 'error';
                        $format[]         = '%s';
                    }
                    $wpdb->update(
                        $wpdb->prefix . 'gca_cron_log',
                        $update,
                        ['id' => $log_id],
                        $format,
                        ['%d']
                    );
                }

                exit;
            }
        }

        self::redirect(array_filter(['run' => '1', 'log_id' => $log_id ?: null, 'log_hook' => $log_id ? $hook : null]));
    }

    public static function handle_edit(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer(self::NONCE_EDIT);

        $hook          = wp_unslash(sanitize_text_field($_POST['cron_hook'] ?? ''));
        $old_timestamp = (int) ($_POST['cron_old_timestamp'] ?? 0);
        $key           = sanitize_text_field($_POST['cron_key'] ?? '');
        $new_time      = sanitize_text_field($_POST['cron_next_run'] ?? '');
        $new_schedule  = sanitize_key($_POST['cron_schedule'] ?? '');
        $recurring     = !empty($_POST['cron_recurring']);

        if (!$hook || !$old_timestamp) {
            self::redirect(['error' => 'invalid_edit']);
        }

        $new_timestamp = false;
        if ($new_time) {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $new_time, wp_timezone());
            if ($dt) {
                $new_timestamp = $dt->getTimestamp();
            }
        }
        if (!$new_timestamp) {
            self::redirect(['error' => 'invalid_time']);
        }

        $crons    = _get_cron_array();
        $old_args = $crons[$old_timestamp][$hook][$key]['args'] ?? [];

        wp_unschedule_event($old_timestamp, $hook, $old_args);

        if ($recurring && $new_schedule) {
            $schedules = wp_get_schedules();
            if (isset($schedules[$new_schedule])) {
                wp_schedule_event($new_timestamp, $new_schedule, $hook, $old_args);
            }
        } else {
            wp_schedule_single_event($new_timestamp, $hook, $old_args);
        }

        self::redirect(['updated' => '1']);
    }

    // -------------------------------------------------------------------------
    // Admin notices
    // -------------------------------------------------------------------------

    public static function show_notices(): void {
        $screen = get_current_screen();
        if (!$screen || 'tools_page_' . self::MENU_SLUG !== $screen->id) {
            return;
        }

        $notices = [
            'created'          => ['success', 'Cron job created.'],
            'deleted'          => ['success', 'Cron job deleted.'],
            'updated'          => ['success', 'Cron job updated.'],
            'empty_hook'       => ['error',   'Hook name cannot be empty.'],
            'invalid_schedule' => ['error',   'Invalid schedule selected.'],
            'invalid_edit'     => ['error',   'Invalid edit request.'],
            'invalid_time'     => ['error',   'Invalid next-run time provided.'],
        ];

        foreach ($notices as $key => [$type, $text]) {
            if (!empty($_GET[$key])) {
                printf(
                    '<div class="notice notice-%s is-dismissible"><p><strong>%s</strong></p></div>',
                    esc_attr($type),
                    esc_html($text)
                );
            }
        }

        // "Run Now" notice — shown separately so we can include a Logs link.
        if (!empty($_GET['run'])) {
            $log_id   = (int) ($_GET['log_id'] ?? 0);
            $log_hook = sanitize_text_field(wp_unslash($_GET['log_hook'] ?? ''));
            $msg      = 'Cron job dispatched &mdash; it is now running in the background.';
            if ($log_id && $log_hook) {
                $logs_url = add_query_arg(
                    ['page' => self::MENU_SLUG, 'action' => 'logs', 'hook' => rawurlencode($log_hook)],
                    admin_url('tools.php')
                );
                $msg .= ' <a href="' . esc_url($logs_url) . '">View log &rarr;</a>';
            }
            printf(
                '<div class="notice notice-success is-dismissible"><p><strong>%s</strong></p></div>',
                wp_kses($msg, ['a' => ['href' => []]])
            );
        }
    }

    // -------------------------------------------------------------------------
    // Render dispatcher
    // -------------------------------------------------------------------------

    public static function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $action = sanitize_key($_GET['action'] ?? 'list');

        match ($action) {
            'add'  => self::render_add_page(),
            'edit' => self::render_edit_page(),
            'logs' => self::render_logs_page(),
            default => self::render_list_page(),
        };
    }

    // -------------------------------------------------------------------------
    // List page
    // -------------------------------------------------------------------------

    private static function render_list_page(): void {
        $schedules = wp_get_schedules();
        $add_url   = admin_url('tools.php?page=' . self::MENU_SLUG . '&action=add');

        // --- Validated sort / filter params ---
        $orderby = in_array($_GET['orderby'] ?? '', ['hook', 'next_run', 'recurrence'], true)
            ? sanitize_key($_GET['orderby'])
            : 'next_run';
        $order         = 'desc' === ($_GET['order'] ?? '') ? 'desc' : 'asc';
        $filter_type   = in_array($_GET['filter_type'] ?? '', ['one-time', 'recurring'], true)
            ? sanitize_key($_GET['filter_type'])
            : 'all';
        $filter_status = 'overdue' === ($_GET['filter_status'] ?? '') ? 'overdue' : 'all';
        $search        = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));

        $all_events     = self::get_all_events();
        $total          = count($all_events);
        $total_overdue  = count(array_filter($all_events, static fn($e) => $e['timestamp'] < time()));
        $total_recurring = count(array_filter($all_events, static fn($e) => (bool) $e['schedule']));
        $total_one_time = $total - $total_recurring;

        $events    = self::filter_events($all_events, $filter_type, $filter_status, $search);
        $events    = self::sort_events($events, $orderby, $order, $schedules);
        $last_runs = class_exists('GCA_Cron_Logger') ? GCA_Cron_Logger::get_all_recent(1) : [];

        // Helpers — capture $orderby / $order in closures.
        $sort_url = static function (string $col) use ($orderby, $order): string {
            $new_order = ($orderby === $col && $order === 'asc') ? 'desc' : 'asc';
            return add_query_arg(['page' => GCA_Cron_Manager::MENU_SLUG, 'orderby' => $col, 'order' => $new_order], admin_url('tools.php'));
        };

        $col_class = static function (string $col) use ($orderby, $order): string {
            if ($orderby !== $col) {
                return 'manage-column sortable asc';
            }
            return 'manage-column sorted ' . $order;
        };

        $filter_url = static function (array $args) use ($orderby, $order): string {
            return add_query_arg(
                array_merge(['page' => GCA_Cron_Manager::MENU_SLUG, 'orderby' => $orderby, 'order' => $order], $args),
                admin_url('tools.php')
            );
        };
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Cron Jobs</h1>
            <a href="<?php echo esc_url($add_url); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">

            <?php self::render_styles(); ?>

            <!-- Filter tabs -->
            <ul class="subsubsub">
                <li>
                    <a href="<?php echo esc_url($filter_url(['filter_type' => 'all', 'filter_status' => 'all', 's' => ''])); ?>"
                       class="<?php echo ('all' === $filter_type && 'all' === $filter_status) ? 'current' : ''; ?>">
                        All <span class="count">(<?php echo (int) $total; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url($filter_url(['filter_type' => 'recurring', 'filter_status' => 'all', 's' => ''])); ?>"
                       class="<?php echo 'recurring' === $filter_type ? 'current' : ''; ?>">
                        Recurring <span class="count">(<?php echo (int) $total_recurring; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url($filter_url(['filter_type' => 'one-time', 'filter_status' => 'all', 's' => ''])); ?>"
                       class="<?php echo 'one-time' === $filter_type ? 'current' : ''; ?>">
                        One-time <span class="count">(<?php echo (int) $total_one_time; ?>)</span>
                    </a>
                    <?php if ($total_overdue > 0) : ?>
                    |
                </li>
                <li>
                    <a href="<?php echo esc_url($filter_url(['filter_type' => 'all', 'filter_status' => 'overdue', 's' => ''])); ?>"
                       class="<?php echo 'overdue' === $filter_status ? 'current' : ''; ?>">
                        Overdue <span class="count">(<?php echo (int) $total_overdue; ?>)</span>
                    </a>
                    <?php endif; ?>
                </li>
            </ul>

            <!-- Search box -->
            <form method="get" class="search-form gca-cron-search">
                <input type="hidden" name="page"          value="<?php echo esc_attr(self::MENU_SLUG); ?>">
                <input type="hidden" name="orderby"       value="<?php echo esc_attr($orderby); ?>">
                <input type="hidden" name="order"         value="<?php echo esc_attr($order); ?>">
                <input type="hidden" name="filter_type"   value="<?php echo esc_attr($filter_type); ?>">
                <input type="hidden" name="filter_status" value="<?php echo esc_attr($filter_status); ?>">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                       placeholder="Search hooks…" class="gca-search-input">
                <?php submit_button('Search', 'secondary', '', false); ?>
                <?php if ('' !== $search) : ?>
                    <a href="<?php echo esc_url($filter_url(['filter_type' => $filter_type, 'filter_status' => $filter_status, 's' => ''])); ?>"
                       class="button">Clear</a>
                <?php endif; ?>
            </form>

            <br class="clear">

            <?php if (empty($events)) : ?>
                <div class="notice notice-info inline">
                    <p>No cron jobs match the current filter<?php echo '' !== $search ? ' and search' : ''; ?>.</p>
                </div>
            <?php else : ?>
                <p class="displaying-header-text">
                    <?php
                    $n = count($events);
                    echo esc_html($n . ' item' . ($n !== 1 ? 's' : ''));
                    ?>
                </p>
                <table class="wp-list-table widefat fixed striped gca-cron-table">
                    <thead>
                        <tr>
                            <th scope="col" class="column-hook <?php echo esc_attr($col_class('hook')); ?>">
                                <a href="<?php echo esc_url($sort_url('hook')); ?>">
                                    <span>Hook</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                                </a>
                            </th>
                            <th scope="col" class="column-next-run <?php echo esc_attr($col_class('next_run')); ?>">
                                <a href="<?php echo esc_url($sort_url('next_run')); ?>">
                                    <span>Next Run</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                                </a>
                            </th>
                            <th scope="col" class="column-recurrence <?php echo esc_attr($col_class('recurrence')); ?>">
                                <a href="<?php echo esc_url($sort_url('recurrence')); ?>">
                                    <span>Recurrence</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span>
                                </a>
                            </th>
                            <th scope="col" class="column-last-run">Last Run</th>
                            <th scope="col" class="column-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event) :
                            $is_overdue     = $event['timestamp'] < time();
                            $abs_date       = wp_date('d M Y H:i:s', $event['timestamp']);
                            $relative       = human_time_diff(time(), $event['timestamp']);
                            $next_run_label = $is_overdue
                                ? esc_html($relative) . ' ago'
                                : 'In ' . esc_html($relative);

                            $schedule_label = 'One-time';
                            if ($event['schedule'] && isset($schedules[$event['schedule']])) {
                                $schedule_label = $schedules[$event['schedule']]['display'];
                            }

                            $edit_url = add_query_arg([
                                'page'      => self::MENU_SLUG,
                                'action'    => 'edit',
                                'hook'      => rawurlencode($event['hook']),
                                'timestamp' => $event['timestamp'],
                                'key'       => $event['key'],
                            ], admin_url('tools.php'));

                            $logs_url = add_query_arg([
                                'page'   => self::MENU_SLUG,
                                'action' => 'logs',
                                'hook'   => rawurlencode($event['hook']),
                            ], admin_url('tools.php'));

                            $last_run = $last_runs[$event['hook']][0] ?? null;
                        ?>
                        <tr>
                            <td class="column-hook">
                                <strong><?php echo esc_html($event['hook']); ?></strong>
                            </td>
                            <td class="column-next-run<?php echo $is_overdue ? ' gca-overdue' : ''; ?>">
                                <?php if ($is_overdue) : ?>
                                    <span class="gca-overdue-badge">Overdue</span>
                                <?php endif; ?>
                                <span title="<?php echo esc_attr($abs_date); ?>">
                                    <?php echo $next_run_label; // already escaped above ?>
                                </span>
                                <br><small class="gca-date-abs"><?php echo esc_html($abs_date); ?></small>
                            </td>
                            <td class="column-recurrence">
                                <?php echo esc_html($schedule_label); ?>
                            </td>
                            <td class="column-last-run">
                                <?php if ($last_run) : ?>
                                    <span class="gca-status-badge gca-status-<?php echo esc_attr($last_run['status']); ?>">
                                        <?php echo esc_html(ucfirst($last_run['status'])); ?>
                                    </span>
                                    <br>
                                    <a href="<?php echo esc_url($logs_url); ?>">
                                        <?php echo esc_html(human_time_diff(strtotime($last_run['ran_at']), time())); ?> ago
                                    </a>
                                <?php else : ?>
                                    <a href="<?php echo esc_url($logs_url); ?>" style="color:#8c8f94">Never</a>
                                <?php endif; ?>
                            </td>
                            <td class="column-actions">
                                <a href="<?php echo esc_url($edit_url); ?>" class="button button-small">Edit</a>

                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                                    <?php wp_nonce_field(self::NONCE_RUN); ?>
                                    <input type="hidden" name="action"         value="gca_cron_run_now">
                                    <input type="hidden" name="cron_hook"      value="<?php echo esc_attr($event['hook']); ?>">
                                    <input type="hidden" name="cron_timestamp" value="<?php echo esc_attr((string) $event['timestamp']); ?>">
                                    <input type="hidden" name="cron_key"       value="<?php echo esc_attr($event['key']); ?>">
                                    <button type="submit" class="button button-small"
                                            onclick="return confirm('Run this cron job now?')">
                                        Run Now
                                    </button>
                                </form>

                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                                    <?php wp_nonce_field(self::NONCE_DELETE); ?>
                                    <input type="hidden" name="action"         value="gca_cron_delete">
                                    <input type="hidden" name="cron_hook"      value="<?php echo esc_attr($event['hook']); ?>">
                                    <input type="hidden" name="cron_timestamp" value="<?php echo esc_attr((string) $event['timestamp']); ?>">
                                    <input type="hidden" name="cron_key"       value="<?php echo esc_attr($event['key']); ?>">
                                    <button type="submit" class="button button-small button-link-delete"
                                            onclick="return confirm('Delete this cron job? This cannot be undone.')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Add page
    // -------------------------------------------------------------------------

    private static function render_add_page(): void {
        $schedules   = wp_get_schedules();
        $back_url    = admin_url('tools.php?page=' . self::MENU_SLUG);
        $default_dt  = wp_date('Y-m-d\TH:i', time() + 3600); // 1 hour from now
        $tz_label    = wp_timezone_string();
        ?>
        <div class="wrap">
            <h1>Add New Cron Job</h1>
            <a href="<?php echo esc_url($back_url); ?>">&larr; Back to Cron Jobs</a>
            <hr class="wp-header-end">

            <?php self::render_styles(); ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field(self::NONCE_CREATE); ?>
                <input type="hidden" name="action" value="gca_cron_create">

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="cron_hook">Hook Name <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" id="cron_hook" name="cron_hook"
                                   class="regular-text" required
                                   placeholder="e.g. my_custom_event">
                            <p class="description">
                                The WordPress action hook fired when this job runs.
                                You must also add a matching <code>add_action()</code> listener in your code.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cron_next_run">Next Run <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="datetime-local" id="cron_next_run" name="cron_next_run"
                                   value="<?php echo esc_attr($default_dt); ?>" required>
                            <p class="description">Server time zone: <?php echo esc_html($tz_label); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Recurring?</th>
                        <td>
                            <label>
                                <input type="checkbox" id="cron_recurring" name="cron_recurring" value="1">
                                This is a recurring cron job
                            </label>
                        </td>
                    </tr>
                    <tr id="gca-schedule-row" style="display:none">
                        <th scope="row">
                            <label for="cron_schedule">Schedule <span class="required">*</span></label>
                        </th>
                        <td>
                            <select id="cron_schedule" name="cron_schedule">
                                <?php foreach ($schedules as $key => $schedule) : ?>
                                    <option value="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($schedule['display']); ?>
                                        (every <?php echo esc_html(human_time_diff(0, $schedule['interval'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">Create Cron Job</button>
                    <a href="<?php echo esc_url($back_url); ?>" class="button">Cancel</a>
                </p>
            </form>

            <script>
            (function () {
                var cb  = document.getElementById('cron_recurring');
                var row = document.getElementById('gca-schedule-row');
                if (cb && row) {
                    cb.addEventListener('change', function () {
                        row.style.display = cb.checked ? '' : 'none';
                    });
                }
            }());
            </script>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Edit page
    // -------------------------------------------------------------------------

    private static function render_edit_page(): void {
        $hook      = wp_unslash(sanitize_text_field($_GET['hook'] ?? ''));
        $timestamp = (int) ($_GET['timestamp'] ?? 0);
        $key       = sanitize_text_field($_GET['key'] ?? '');
        $back_url  = admin_url('tools.php?page=' . self::MENU_SLUG);

        if (!$hook || !$timestamp) {
            wp_safe_redirect($back_url);
            exit;
        }

        $crons = _get_cron_array();
        $event = $crons[$timestamp][$hook][$key] ?? null;

        if (!$event) {
            echo '<div class="wrap"><div class="notice notice-error inline"><p>Cron event not found. It may have already run or been deleted.</p></div><p><a href="' . esc_url($back_url) . '">&larr; Back to Cron Jobs</a></p></div>';
            return;
        }

        $schedules       = wp_get_schedules();
        $current_schedule = $event['schedule'] ?? false;
        $is_recurring    = (bool) $current_schedule;
        $current_dt      = wp_date('Y-m-d\TH:i', $timestamp);
        $tz_label        = wp_timezone_string();
        ?>
        <div class="wrap">
            <h1>Edit Cron Job</h1>
            <a href="<?php echo esc_url($back_url); ?>">&larr; Back to Cron Jobs</a>
            <hr class="wp-header-end">

            <?php self::render_styles(); ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field(self::NONCE_EDIT); ?>
                <input type="hidden" name="action"             value="gca_cron_edit">
                <input type="hidden" name="cron_hook"          value="<?php echo esc_attr($hook); ?>">
                <input type="hidden" name="cron_old_timestamp" value="<?php echo esc_attr((string) $timestamp); ?>">
                <input type="hidden" name="cron_key"           value="<?php echo esc_attr($key); ?>">

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Hook Name</th>
                        <td>
                            <code><?php echo esc_html($hook); ?></code>
                            <p class="description">Hook name cannot be changed. Delete this job and create a new one if needed.</p>
                        </td>
                    </tr>
                    <?php if (!empty($event['args'])) : ?>
                    <tr>
                        <th scope="row">Arguments</th>
                        <td>
                            <code><?php echo esc_html(wp_json_encode($event['args'])); ?></code>
                            <p class="description">Arguments are preserved from the original job.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row">
                            <label for="cron_next_run">Next Run</label>
                        </th>
                        <td>
                            <input type="datetime-local" id="cron_next_run" name="cron_next_run"
                                   value="<?php echo esc_attr($current_dt); ?>" required>
                            <p class="description">Server time zone: <?php echo esc_html($tz_label); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Recurring?</th>
                        <td>
                            <label>
                                <input type="checkbox" id="cron_recurring" name="cron_recurring" value="1"
                                       <?php checked($is_recurring); ?>>
                                This is a recurring cron job
                            </label>
                        </td>
                    </tr>
                    <tr id="gca-schedule-row"<?php echo $is_recurring ? '' : ' style="display:none"'; ?>>
                        <th scope="row">
                            <label for="cron_schedule">Schedule</label>
                        </th>
                        <td>
                            <select id="cron_schedule" name="cron_schedule">
                                <?php foreach ($schedules as $skey => $schedule) : ?>
                                    <option value="<?php echo esc_attr($skey); ?>"
                                            <?php selected($current_schedule, $skey); ?>>
                                        <?php echo esc_html($schedule['display']); ?>
                                        (every <?php echo esc_html(human_time_diff(0, $schedule['interval'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">Update Cron Job</button>
                    <a href="<?php echo esc_url($back_url); ?>" class="button">Cancel</a>
                </p>
            </form>

            <script>
            (function () {
                var cb  = document.getElementById('cron_recurring');
                var row = document.getElementById('gca-schedule-row');
                if (cb && row) {
                    cb.addEventListener('change', function () {
                        row.style.display = cb.checked ? '' : 'none';
                    });
                }
            }());
            </script>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Return all scheduled cron events as a flat, time-sorted array.
     *
     * @return array<int, array{timestamp:int, hook:string, key:string, schedule:string|false, interval:int, args:array}>
     */
    private static function get_all_events(): array {
        $events = [];
        $crons  = _get_cron_array();

        if (!is_array($crons)) {
            return $events;
        }

        foreach ($crons as $timestamp => $hooks) {
            if (!is_array($hooks)) {
                continue;
            }
            foreach ($hooks as $hook => $hook_events) {
                foreach ($hook_events as $key => $event) {
                    $events[] = [
                        'timestamp' => (int) $timestamp,
                        'hook'      => (string) $hook,
                        'key'       => (string) $key,
                        'schedule'  => $event['schedule'] ?? false,
                        'interval'  => (int) ($event['interval'] ?? 0),
                        'args'      => (array) ($event['args'] ?? []),
                    ];
                }
            }
        }

        usort($events, static fn(array $a, array $b): int => $a['timestamp'] - $b['timestamp']);

        return $events;
    }

    /**
     * Filter events by type, status, and search string.
     *
     * @param array  $events        From get_all_events().
     * @param string $filter_type   'all' | 'one-time' | 'recurring'
     * @param string $filter_status 'all' | 'overdue'
     * @param string $search        Substring match against hook name.
     */
    private static function filter_events(array $events, string $filter_type, string $filter_status, string $search): array {
        return array_values(array_filter($events, static function (array $event) use ($filter_type, $filter_status, $search): bool {
            if ('one-time' === $filter_type && $event['schedule']) {
                return false;
            }
            if ('recurring' === $filter_type && !$event['schedule']) {
                return false;
            }
            if ('overdue' === $filter_status && $event['timestamp'] >= time()) {
                return false;
            }
            if ('' !== $search && false === stripos($event['hook'], $search)) {
                return false;
            }
            return true;
        }));
    }

    /**
     * Sort events by the given column and direction.
     *
     * @param array  $schedules wp_get_schedules() result, needed for recurrence label sort.
     */
    private static function sort_events(array $events, string $orderby, string $order, array $schedules): array {
        usort($events, static function (array $a, array $b) use ($orderby, $order, $schedules): int {
            $cmp = match ($orderby) {
                'hook' => strcmp($a['hook'], $b['hook']),
                'recurrence' => (static function () use ($a, $b, $schedules): int {
                    $la = ($a['schedule'] && isset($schedules[$a['schedule']])) ? $schedules[$a['schedule']]['display'] : '';
                    $lb = ($b['schedule'] && isset($schedules[$b['schedule']])) ? $schedules[$b['schedule']]['display'] : '';
                    return strcmp($la, $lb);
                })(),
                default => $a['timestamp'] - $b['timestamp'],
            };
            return 'desc' === $order ? -$cmp : $cmp;
        });
        return $events;
    }

    /**
     * Redirect back to the cron manager list page with query-string flags.
     */
    private static function redirect(array $args): void {
        wp_safe_redirect(add_query_arg(
            array_merge(['page' => self::MENU_SLUG], $args),
            admin_url('tools.php')
        ));
        exit;
    }

    // -------------------------------------------------------------------------
    // Logs page
    // -------------------------------------------------------------------------

    private static function render_logs_page(): void {
        $hook     = sanitize_text_field(wp_unslash($_GET['hook'] ?? ''));
        $back_url = admin_url('tools.php?page=' . self::MENU_SLUG);

        if (!class_exists('GCA_Cron_Logger')) {
            echo '<div class="wrap"><p>Logger not available.</p></div>';
            return;
        }

        $per_page      = 25;
        $log_page      = max(1, (int) ($_GET['log_page'] ?? 1));
        $offset        = ($log_page - 1) * $per_page;
        $total         = $hook ? GCA_Cron_Logger::count($hook) : 0;
        $runs          = $hook ? GCA_Cron_Logger::get_recent($hook, $per_page, $offset) : [];
        $total_pages   = $hook ? (int) ceil($total / $per_page) : 0;
        $pending_log_id = (int) ($_GET['log_id'] ?? 0);
        $is_polling    = $pending_log_id && !empty($runs) && (int) $runs[0]['duration_ms'] === 0 && (int) $runs[0]['id'] === $pending_log_id;
        ?>
        <div class="wrap">
            <h1>
                Cron Logs
                <?php if ($hook) : ?>
                    <span style="font-weight:normal;font-size:16px;color:#50575e"> &mdash; <?php echo esc_html($hook); ?></span>
                <?php endif; ?>
            </h1>
            <a href="<?php echo esc_url($back_url); ?>">&larr; Back to Cron Jobs</a>
            <hr class="wp-header-end">

            <?php self::render_styles(); ?>

            <?php if ($is_polling) : ?>
            <div class="notice notice-info gca-polling-notice" style="display:flex;align-items:center;gap:10px">
                <span class="gca-spinner"></span>
                <p style="margin:0"><strong>Job is running&hellip;</strong> This page will refresh automatically.</p>
            </div>
            <script>
            (function () {
                var pollUrl = <?php echo wp_json_encode(add_query_arg(['log_id' => $pending_log_id], remove_query_arg('run'))); ?>;
                setTimeout(function poll() {
                    fetch(pollUrl, {credentials: 'same-origin'})
                        .then(function (r) { return r.text(); })
                        .then(function (html) {
                            var match = html.match(/data-log-id="(\d+)" data-duration="(\d+)"/);
                            if (match && match[1] === '<?php echo (int) $pending_log_id; ?>' && parseInt(match[2], 10) > 0) {
                                window.location.href = pollUrl;
                            } else {
                                setTimeout(poll, 3000);
                            }
                        })
                        .catch(function () { setTimeout(poll, 5000); });
                }, 3000);
            }());
            </script>
            <?php endif; ?>

            <?php if (!$hook) : ?>
                <p>No hook specified. <a href="<?php echo esc_url($back_url); ?>">Return to Cron Jobs</a> and click the Logs button for a specific job.</p>
            <?php elseif (empty($runs)) : ?>
                <p>No runs recorded yet for <strong><?php echo esc_html($hook); ?></strong>.</p>
            <?php else : ?>
                <p style="color:#50575e"><?php echo (int) $total; ?> run(s) recorded &mdash; showing <?php echo (int) $per_page; ?> per page.</p>

                <table class="widefat striped gca-log-table">
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Triggered By</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Stats</th>
                            <th>Output</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($runs as $run) :
                            $ran_at_abs   = wp_date('d M Y H:i:s', strtotime($run['ran_at']));
                            $ran_at_rel   = human_time_diff(strtotime($run['ran_at']), time()) . ' ago';
                            $is_pending   = (int) $run['duration_ms'] === 0 && (int) $run['id'] === $pending_log_id;
                            $duration     = $is_pending
                                ? '<em style="color:#8c8f94">Running&hellip;</em>'
                                : ($run['duration_ms'] >= 1000
                                    ? round($run['duration_ms'] / 1000, 2) . 's'
                                    : $run['duration_ms'] . 'ms');
                            $stats        = $run['stats'] ? json_decode($run['stats'], true) : null;
                            $is_manual    = 'manual' === $run['triggered_by'];
                        ?>
                        <tr data-log-id="<?php echo (int) $run['id']; ?>" data-duration="<?php echo (int) $run['duration_ms']; ?>">
                            <td>
                                <span title="<?php echo esc_attr($ran_at_abs); ?>">
                                    <?php echo esc_html($ran_at_rel); ?>
                                </span>
                                <br><small class="gca-date-abs"><?php echo esc_html($ran_at_abs); ?></small>
                            </td>
                            <td>
                                <span class="gca-trigger-badge gca-trigger-<?php echo $is_manual ? 'manual' : 'schedule'; ?>">
                                    <?php echo $is_manual ? 'Manual' : 'Scheduled'; ?>
                                </span>
                            </td>
                            <td><?php echo $is_pending ? $duration : esc_html($duration); ?></td>
                            <td>
                                <span class="gca-run-dot gca-run-<?php echo esc_attr($run['status']); ?>"></span>
                                <?php echo esc_html(ucfirst($run['status'])); ?>
                            </td>
                            <td class="gca-log-stats">
                                <?php if ($stats) : ?>
                                    <?php foreach ($stats as $k => $v) : ?>
                                        <span class="gca-stat-item"><?php echo esc_html(ucfirst($k)); ?>: <strong><?php echo (int) $v; ?></strong></span>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <em style="color:#8c8f94">—</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($run['output'])) : ?>
                                    <details>
                                        <summary>Show output</summary>
                                        <pre class="gca-log-output"><?php echo esc_html($run['output']); ?></pre>
                                    </details>
                                <?php else : ?>
                                    <em style="color:#8c8f94">No output</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1) :
                    $page_url = static function (int $p) use ($hook): string {
                        return add_query_arg([
                            'page'     => GCA_Cron_Manager::MENU_SLUG,
                            'action'   => 'logs',
                            'hook'     => rawurlencode($hook),
                            'log_page' => $p,
                        ], admin_url('tools.php'));
                    };
                ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php if ($log_page > 1) : ?>
                            <a href="<?php echo esc_url($page_url($log_page - 1)); ?>" class="button">&laquo; Previous</a>
                        <?php endif; ?>
                        <span style="margin:0 8px">Page <?php echo (int) $log_page; ?> of <?php echo (int) $total_pages; ?></span>
                        <?php if ($log_page < $total_pages) : ?>
                            <a href="<?php echo esc_url($page_url($log_page + 1)); ?>" class="button">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    private static function render_styles(): void {
        ?>
        <style>
            .gca-cron-table .column-hook       { width: 28%; }
            .gca-cron-table .column-next-run   { width: 22%; }
            .gca-cron-table .column-recurrence { width: 14%; }
            .gca-cron-table .column-last-run   { width: 18%; }
            .gca-cron-table .column-actions    { width: 18%; white-space: nowrap; }
            .gca-cron-table .column-actions .button { margin-right: 4px; }

            /* Status dot */
            .gca-run-dot {
                display: inline-block;
                width: 8px; height: 8px;
                border-radius: 50%;
                margin-right: 4px;
                vertical-align: middle;
            }
            .gca-run-success { background: #00a32a; }
            .gca-run-error   { background: #d63638; }

            /* Last-run status badge */
            .gca-status-badge {
                display: inline-block;
                font-size: 11px;
                font-weight: 600;
                padding: 1px 7px;
                border-radius: 3px;
                margin-bottom: 2px;
            }
            .gca-status-success { background: #e6f0e6; color: #1e4620; }
            .gca-status-error   { background: #fde8e8; color: #8a1414; }

            /* Trigger badge */
            .gca-trigger-badge {
                display: inline-block;
                font-size: 11px;
                font-weight: 600;
                padding: 1px 7px;
                border-radius: 3px;
            }
            .gca-trigger-manual   { background: #dde3e8; color: #1d2327; }
            .gca-trigger-schedule { background: #e6f0e6; color: #1e4620; }

            /* Logs table */
            .gca-log-table { margin-top: 12px; }
            .gca-log-output {
                margin: 8px 0 0;
                padding: 8px;
                background: #f6f7f7;
                border: 1px solid #dcdcde;
                border-radius: 3px;
                font-size: 12px;
                max-height: 300px;
                overflow-y: auto;
                white-space: pre-wrap;
                word-break: break-all;
            }
            .gca-log-stats .gca-stat-item { display: inline-block; margin-right: 8px; font-size: 12px; }

            .gca-overdue-badge {
                display: inline-block;
                background: #d63638;
                color: #fff;
                font-size: 11px;
                font-weight: 600;
                padding: 1px 6px;
                border-radius: 3px;
                margin-right: 4px;
                vertical-align: middle;
            }
            .gca-date-abs {
                color: #8c8f94;
            }
            .gca-args {
                display: block;
                white-space: pre-wrap;
                word-break: break-all;
                font-size: 11px;
                background: #f6f7f7;
                padding: 4px 6px;
                border-radius: 3px;
            }
            .gca-cron-form .form-table th { width: 200px; }
            .gca-spinner {
                display: inline-block;
                width: 16px; height: 16px;
                border: 2px solid #c3c4c7;
                border-top-color: #2271b1;
                border-radius: 50%;
                animation: gca-spin 0.7s linear infinite;
                flex-shrink: 0;
            }
            @keyframes gca-spin { to { transform: rotate(360deg); } }
            .gca-polling-notice { padding: 10px 16px; }
            .required { color: #d63638; }
            .gca-cron-search { float: right; margin-bottom: 8px; }
            .gca-cron-search .button { margin-left: 4px; }
            .gca-search-input { width: 220px; }
        </style>
        <?php
    }
}
