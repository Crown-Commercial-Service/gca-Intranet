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
        add_action('admin_post_gca_cron_create',  [__CLASS__, 'handle_create']);
        add_action('admin_post_gca_cron_delete',  [__CLASS__, 'handle_delete']);
        add_action('admin_post_gca_cron_run_now', [__CLASS__, 'handle_run_now']);
        add_action('admin_post_gca_cron_edit',    [__CLASS__, 'handle_edit']);
        add_action('admin_notices',               [__CLASS__, 'show_notices']);
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

        $timestamp = $time_str ? strtotime($time_str) : false;
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

        if ($hook && $timestamp) {
            $crons = _get_cron_array();
            $event = $crons[$timestamp][$hook][$key] ?? null;
            if ($event) {
                do_action_ref_array($hook, $event['args']);
            }
        }

        self::redirect(['run' => '1']);
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

        $new_timestamp = $new_time ? strtotime($new_time) : false;
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
            'run'              => ['success', 'Cron job triggered manually.'],
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

        $events = self::filter_events($all_events, $filter_type, $filter_status, $search);
        $events = self::sort_events($events, $orderby, $order, $schedules);

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
                            <th scope="col" class="column-args">Arguments</th>
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
                            <td class="column-args">
                                <?php if (empty($event['args'])) : ?>
                                    <em>None</em>
                                <?php else : ?>
                                    <code class="gca-args"><?php echo esc_html(wp_json_encode($event['args'], JSON_PRETTY_PRINT)); ?></code>
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

    private static function render_styles(): void {
        ?>
        <style>
            .gca-cron-table .column-hook       { width: 28%; }
            .gca-cron-table .column-next-run   { width: 20%; }
            .gca-cron-table .column-recurrence { width: 14%; }
            .gca-cron-table .column-args       { width: 22%; }
            .gca-cron-table .column-actions    { width: 16%; white-space: nowrap; }
            .gca-cron-table .column-actions .button { margin-right: 4px; }

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
            .required { color: #d63638; }
            .gca-cron-search { float: right; margin-bottom: 8px; }
            .gca-cron-search .button { margin-left: 4px; }
            .gca-search-input { width: 220px; }
        </style>
        <?php
    }
}
