<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Staff Directory
//
// Browse-by-directorate/team staff directory with a name-search box.
// Data is pulled from WordPress user meta synced from Workday.
// Rendered by the "Staff Directory" page template (page-staff-directory.php).
// -----------------------------------------------------------------------------

gca_register_feature_flag('staff-directory', [
    'label'       => 'Staff Directory',
    'description' => 'Enables the staff directory page with browse-by-directorate/team navigation.',
    'default'     => true,
    'tags'        => ['users', 'directory'],
]);

// ── REST API ──────────────────────────────────────────────────────────────────

add_action('rest_api_init', function (): void {
    if (!gca_flag_enabled('staff-directory')) {
        return;
    }

    $auth = fn() => is_user_logged_in();

    register_rest_route('gca/v1', '/directory/directorates', [
        'methods'             => 'GET',
        'callback'            => 'gca_directory_rest_directorates',
        'permission_callback' => $auth,
    ]);

    register_rest_route('gca/v1', '/directory/teams', [
        'methods'             => 'GET',
        'callback'            => 'gca_directory_rest_teams',
        'permission_callback' => $auth,
        'args'                => [
            'directorate' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);

    register_rest_route('gca/v1', '/directory/staff', [
        'methods'             => 'GET',
        'callback'            => 'gca_directory_rest_staff',
        'permission_callback' => $auth,
        'args'                => [
            'team'        => ['sanitize_callback' => 'sanitize_text_field'],
            'directorate' => ['sanitize_callback' => 'sanitize_text_field'],
        ],
    ]);

    register_rest_route('gca/v1', '/directory/search', [
        'methods'             => 'GET',
        'callback'            => 'gca_directory_rest_search',
        'permission_callback' => $auth,
        'args'                => [
            'q' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

// ── Admin: Team Summaries ─────────────────────────────────────────────────────

add_action('admin_menu', function (): void {
    if (!gca_flag_enabled('staff-directory')) {
        return;
    }
    add_options_page(
        'Team Summaries',
        'Team Summaries',
        'manage_options',
        'gca-team-summaries',
        'gca_team_summaries_admin_page'
    );
});

add_action('admin_init', function (): void {
    if (!isset($_POST['gca_team_summaries_nonce'])) {
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', 403);
    }
    check_admin_referer('gca_team_summaries_save', 'gca_team_summaries_nonce');

    $raw = wp_unslash($_POST['team_summary'] ?? []);
    gca_save_team_summaries(is_array($raw) ? $raw : []);

    wp_redirect(add_query_arg('updated', '1', admin_url('options-general.php?page=gca-team-summaries')));
    exit;
});

function gca_team_summaries_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT d.meta_value AS directorate, t.meta_value AS team
         FROM {$wpdb->usermeta} d
         INNER JOIN {$wpdb->usermeta} t ON d.user_id = t.user_id
         WHERE d.meta_key = 'directorate'
           AND d.meta_value != ''
           AND t.meta_key = 'team'
           AND t.meta_value != ''
         GROUP BY d.meta_value, t.meta_value
         ORDER BY d.meta_value ASC, t.meta_value ASC"
    );

    $grouped = [];
    foreach ($rows as $row) {
        $grouped[$row->directorate][] = $row->team;
    }

    $summaries = gca_get_team_summaries();
    ?>
    <style>
    .gca-ts-directorate {
        border: 1px solid #dcdcde;
        margin-bottom: 1.25em;
        max-width: 700px;
    }
    .gca-ts-directorate > summary {
        list-style: none;
        display: flex;
        align-items: center;
        gap: .6em;
        padding: .6em .9em;
        cursor: pointer;
        font-weight: 600;
        font-size: 1em;
        color: #1d2327;
        user-select: none;
        background: #f0f0f1;
        border-left: 3px solid #2271b1;
    }
    .gca-ts-directorate > summary::-webkit-details-marker { display: none; }
    .gca-ts-directorate > summary:hover { background: #e8e8e9; }
    .gca-ts-directorate[open] > summary { border-bottom: 1px solid #dcdcde; }
    .gca-ts-directorate__teams { padding: 0; }
    .gca-ts-item {
        border-top: 1px solid #dcdcde;
    }
    .gca-ts-item:first-child { border-top: none; }
    .gca-ts-item summary {
        list-style: none;
        display: flex;
        align-items: center;
        gap: .6em;
        padding: .65em .9em .65em 1.25em;
        cursor: pointer;
        font-weight: 500;
        user-select: none;
        background: #fff;
    }
    .gca-ts-item summary::-webkit-details-marker { display: none; }
    .gca-ts-item summary:hover { background: #f6f7f7; }
    .gca-ts-item[open] summary { background: #f6f7f7; border-bottom: 1px solid #dcdcde; }
    .gca-ts-chevron {
        margin-left: auto;
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        transition: transform .15s ease;
        color: #787c82;
    }
    .gca-ts-item[open] .gca-ts-chevron,
    .gca-ts-directorate[open] > summary .gca-ts-chevron { transform: rotate(180deg); }
    .gca-ts-badge {
        display: inline-block;
        font-size: .75em;
        font-weight: 400;
        color: #fff;
        background: #2271b1;
        padding: .1em .55em;
        border-radius: 10px;
        line-height: 1.6;
    }
    .gca-ts-item__body { padding: .9em; background: #fff; }
    .gca-ts-item__body textarea {
        width: 100%;
        box-sizing: border-box;
        resize: vertical;
    }
    .gca-ts-counter { display: block; color: #787c82; font-size: .82em; margin-top: .25em; }
    .gca-ts-save { margin: 1.25em 0; }
    </style>

    <div class="wrap">
        <h1>Team Summaries</h1>
        <p class="description">Add an optional summary for each team. It appears above the member count on the staff directory. Maximum 200 characters per team.</p>

        <?php if (isset($_GET['updated']) && $_GET['updated'] === '1') : ?>
            <div class="notice notice-success is-dismissible"><p>Summaries saved.</p></div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('gca_team_summaries_save', 'gca_team_summaries_nonce'); ?>

            <?php if (!empty($grouped)) : ?>
                <div class="gca-ts-save">
                    <button type="submit" class="button button-primary">Save summaries</button>
                </div>
            <?php endif; ?>

            <?php foreach ($grouped as $directorate => $teams) :
                $dir_has_summary = false;
                foreach ($teams as $team) {
                    if (isset($summaries[$team]) && $summaries[$team] !== '') {
                        $dir_has_summary = true;
                        break;
                    }
                }
            ?>
                <details class="gca-ts-directorate"<?php echo $dir_has_summary ? ' open' : ''; ?>>
                    <summary>
                        <?php echo esc_html($directorate); ?>
                        <svg class="gca-ts-chevron" viewBox="0 0 20 20" fill="none"
                             xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M5 7.5l5 5 5-5" stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </summary>
                    <div class="gca-ts-directorate__teams">
                        <?php foreach ($teams as $team) :
                            $field_id    = 'ts_' . sanitize_html_class(md5($team));
                            $current     = $summaries[$team] ?? '';
                            $char_count  = mb_strlen($current);
                            $has_summary = $current !== '';
                        ?>
                            <details class="gca-ts-item"<?php echo $has_summary ? ' open' : ''; ?>>
                                <summary>
                                    <?php echo esc_html($team); ?>
                                    <?php if ($has_summary) : ?>
                                        <span class="gca-ts-badge">Summary set</span>
                                    <?php endif; ?>
                                    <svg class="gca-ts-chevron" viewBox="0 0 20 20" fill="none"
                                         xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <path d="M5 7.5l5 5 5-5" stroke="currentColor" stroke-width="1.5"
                                              stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>
                                <div class="gca-ts-item__body">
                                    <label for="<?php echo esc_attr($field_id); ?>" class="screen-reader-text">
                                        Summary for <?php echo esc_attr($team); ?>
                                    </label>
                                    <textarea
                                        id="<?php echo esc_attr($field_id); ?>"
                                        name="team_summary[<?php echo esc_attr($team); ?>]"
                                        maxlength="200"
                                        rows="3"
                                        placeholder="Optional summary shown on the staff directory…"
                                        data-counter="<?php echo esc_attr($field_id); ?>_count"
                                        data-has-summary="<?php echo $has_summary ? '1' : '0'; ?>"
                                    ><?php echo esc_textarea($current); ?></textarea>
                                    <span id="<?php echo esc_attr($field_id); ?>_count" class="gca-ts-counter"><?php echo $char_count; ?>/200</span>
                                </div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>

            <?php if (empty($grouped)) : ?>
                <p>No teams found. Staff data may not have been synced yet.</p>
            <?php else : ?>
                <div class="gca-ts-save">
                    <button type="submit" class="button button-primary">Save summaries</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
    <script>
    (function () {
        // Live character counter
        document.querySelectorAll('textarea[data-counter]').forEach(function (ta) {
            var counter = document.getElementById(ta.dataset.counter);
            if (!counter) { return; }
            ta.addEventListener('input', function () {
                counter.textContent = ta.value.length + '/200';
            });
        });

        // Update "Summary set" badge dynamically as the user types
        document.querySelectorAll('textarea[data-has-summary]').forEach(function (ta) {
            var details = ta.closest('details');
            var summary = details ? details.querySelector('summary') : null;
            if (!summary) { return; }

            ta.addEventListener('input', function () {
                var badge = summary.querySelector('.gca-ts-badge');
                if (ta.value.trim().length > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'gca-ts-badge';
                        badge.textContent = 'Summary set';
                        var chevron = summary.querySelector('.gca-ts-chevron');
                        summary.insertBefore(badge, chevron);
                    }
                } else {
                    if (badge) { badge.remove(); }
                }
            });
        });
    })();
    </script>
    <?php
}

// ── Script enqueue ────────────────────────────────────────────────────────────

add_action('wp_enqueue_scripts', function (): void {
    if (!gca_flag_enabled('staff-directory')) {
        return;
    }

    if (get_page_template_slug() !== 'page-staff-directory.php') {
        return;
    }

    $handle = 'gca-staff-directory';
    wp_register_script($handle, '', [], false, true);
    wp_enqueue_script($handle);

    wp_add_inline_script($handle, 'window.gcaDirectoryData = ' . wp_json_encode([
        'restUrl' => rest_url('gca/v1/directory/'),
        'nonce'   => wp_create_nonce('wp_rest'),
    ]) . ';', 'before');

    wp_add_inline_script($handle, gca_directory_get_js());
});

add_action('wp_head', function (): void {
    if (!gca_flag_enabled('staff-directory')) {
        return;
    }
    if (get_page_template_slug() !== 'page-staff-directory.php') {
        return;
    }
    echo '<style>.sd-directory__team-summary{overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}</style>' . "\n";
});

// ── REST callbacks ────────────────────────────────────────────────────────────

/**
 * GET /wp-json/gca/v1/directory/directorates
 * Returns all distinct, non-empty directorate values sorted A-Z.
 */
function gca_directory_rest_directorates(): WP_REST_Response
{
    $cached = get_transient('gca_dir_directorates');
    if ($cached !== false) {
        return new WP_REST_Response($cached);
    }

    global $wpdb;

    $rows = $wpdb->get_col(
        "SELECT DISTINCT meta_value
         FROM {$wpdb->usermeta}
         WHERE meta_key = 'directorate'
           AND meta_value != ''
         ORDER BY meta_value ASC"
    );

    $data = array_values(array_map(fn($n) => ['name' => $n], $rows));
    set_transient('gca_dir_directorates', $data, HOUR_IN_SECONDS);

    return new WP_REST_Response($data);
}

/**
 * GET /wp-json/gca/v1/directory/teams?directorate=X
 * Returns teams within a directorate with member counts.
 */
function gca_directory_rest_teams(WP_REST_Request $request): WP_REST_Response
{
    $directorate = $request->get_param('directorate');
    $cache_key   = 'gca_dir_teams_' . md5($directorate);

    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return new WP_REST_Response($cached);
    }

    global $wpdb;

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT t.meta_value AS name, COUNT(*) AS cnt
         FROM {$wpdb->usermeta} d
         INNER JOIN {$wpdb->usermeta} t ON d.user_id = t.user_id
         WHERE d.meta_key = 'directorate'
           AND d.meta_value = %s
           AND t.meta_key = 'team'
           AND t.meta_value != ''
         GROUP BY t.meta_value
         ORDER BY t.meta_value ASC",
        $directorate
    ));

    $summaries = gca_get_team_summaries();
    $data = array_map(fn($r) => [
        'name'    => $r->name,
        'count'   => (int) $r->cnt,
        'summary' => $summaries[$r->name] ?? '',
    ], $rows);

    set_transient($cache_key, $data, HOUR_IN_SECONDS);

    return new WP_REST_Response($data);
}

/**
 * GET /wp-json/gca/v1/directory/staff?team=X&directorate=X
 * Returns staff members for a team, optionally filtered by directorate.
 */
function gca_directory_rest_staff(WP_REST_Request $request): WP_REST_Response
{
    $team        = (string) ($request->get_param('team') ?? '');
    $directorate = (string) ($request->get_param('directorate') ?? '');

    if ($team === '' && $directorate === '') {
        return new WP_REST_Response(['message' => 'team or directorate is required'], 400);
    }

    $meta_query = ['relation' => 'AND'];

    if ($directorate !== '') {
        $meta_query[] = ['key' => 'directorate', 'value' => $directorate, 'compare' => '='];
    }

    if ($team !== '') {
        $meta_query[] = ['key' => 'team', 'value' => $team, 'compare' => '='];
    }

    $user_query = new WP_User_Query([
        'meta_query' => $meta_query,
        'orderby'    => 'display_name',
        'order'      => 'ASC',
        'fields'     => 'all',
        'number'     => 500,
    ]);

    return new WP_REST_Response(gca_directory_format_staff($user_query->get_results()));
}

/**
 * GET /wp-json/gca/v1/directory/search?q=X
 * Searches staff by display name.
 */
function gca_directory_rest_search(WP_REST_Request $request): WP_REST_Response
{
    $q = trim($request->get_param('q'));

    if (mb_strlen($q) < 2) {
        return new WP_REST_Response([]);
    }

    $user_query = new WP_User_Query([
        'search'         => '*' . $q . '*',
        'search_columns' => ['display_name'],
        'orderby'        => 'display_name',
        'order'          => 'ASC',
        'fields'         => 'all',
        'number'         => 30,
    ]);

    return new WP_REST_Response(gca_directory_format_staff($user_query->get_results()));
}

// ── Data helpers ──────────────────────────────────────────────────────────────

/**
 * Format WP_User[] into the directory staff payload.
 *
 * @param  WP_User[] $users
 * @return array<int, array<string, mixed>>
 */
function gca_directory_format_staff(array $users): array
{
    $results = [];

    foreach ($users as $user) {
        if (!$user instanceof WP_User) {
            continue;
        }

        if (in_array($user->user_login, ['admin', 'adminuser', 'former-employee'], true)) {
            continue;
        }

        $avatar_url = trim((string) get_user_meta($user->ID, 'google_profile_picture_local_url', true));
        if ($avatar_url === '') {
            $avatar_url = get_avatar_url($user->ID, ['size' => 96]);
        }

        // Support both common Workday meta key names for manager.
        $manager = trim((string) get_user_meta($user->ID, 'manager', true));
        if ($manager === '') {
            $manager = trim((string) get_user_meta($user->ID, 'manager_name', true));
        }

        $results[] = [
            'id'          => $user->ID,
            'name'        => $user->display_name ?: $user->user_login,
            'business_title'   => trim((string) get_user_meta($user->ID, 'business_title', true)),
            'directorate' => trim((string) get_user_meta($user->ID, 'directorate', true)),
            'team'        => trim((string) get_user_meta($user->ID, 'team', true)),
            'manager'     => $manager,
            'avatar_url'  => $avatar_url,
            'profile_url' => home_url('/profile/' . rawurlencode($user->user_login) . '/'),
        ];
    }

    return $results;
}

// ── Team summary helpers ──────────────────────────────────────────────────────

function gca_get_team_summaries(): array
{
    return (array) get_option('gca_team_summaries', []);
}

function gca_save_team_summaries(array $summaries): void
{
    $clean = [];
    foreach ($summaries as $team => $text) {
        $text = mb_substr(sanitize_textarea_field((string) $text), 0, 200);
        if ($text !== '') {
            $clean[sanitize_text_field((string) $team)] = $text;
        }
    }
    update_option('gca_team_summaries', $clean);

    global $wpdb;
    $dirs = $wpdb->get_col(
        "SELECT DISTINCT meta_value FROM {$wpdb->usermeta}
         WHERE meta_key = 'directorate' AND meta_value != ''"
    );
    foreach ($dirs as $dir) {
        delete_transient('gca_dir_teams_' . md5($dir));
    }
}

// ── JavaScript ────────────────────────────────────────────────────────────────

function gca_directory_get_js(): string
{
    return <<<'JS'
(function () {
    'use strict';

    var cfg     = window.gcaDirectoryData || {};
    var restUrl = cfg.restUrl || '';
    var nonce   = cfg.nonce   || '';

    if (!document.querySelector('.sd-directory')) { return; }

    // ── Element refs ──────────────────────────────────────────────────────────

    var directorateSelect = document.getElementById('sd-directorate-select');
    var teamsSection     = document.getElementById('sd-teams-section');
    var teamsList        = document.getElementById('sd-teams-list');
    var defaultContent   = document.getElementById('sd-default-content');
    var staffPanel       = document.getElementById('sd-staff-panel');
    var searchPanel      = document.getElementById('sd-search-panel');
    var panelMeta        = document.getElementById('sd-panel-meta');
    var panelBreadcrumb  = document.getElementById('sd-breadcrumb');
    var panelTitle       = document.getElementById('sd-panel-title');
    var staffCount       = document.getElementById('sd-staff-count');
    var staffGrid        = document.getElementById('sd-staff-grid');
    var searchGrid       = document.getElementById('sd-search-grid');
    var searchTitle      = document.getElementById('sd-search-title');
    var searchInput      = document.getElementById('sd-search-input');
    var searchBtn        = document.getElementById('sd-search-btn');
    var loadingEl        = document.getElementById('sd-loading');
    var teamSummary      = document.getElementById('sd-team-summary');

    var pendingTeam = null;
    var state       = { directorate: null, team: null };
    var teamsCache  = {};

    // ── Utilities ─────────────────────────────────────────────────────────────

    function apiFetch(path) {
        return fetch(restUrl + path, {
            headers:     { 'X-WP-Nonce': nonce },
            credentials: 'same-origin',
        }).then(function (r) {
            if (!r.ok) { throw new Error('HTTP ' + r.status); }
            return r.json();
        });
    }

    function getQueryParam(name) {
        var params = new URLSearchParams(window.location.search);
        var value = params.get(name);
        return value ? value.trim() : '';
    }

    function updateUrl(team) {
        var url = new URL(window.location.href);
        var params = url.searchParams;

        if (team) {
            params.set('team', team);
        } else {
            params.delete('team');
        }

        url.search = params.toString();
        history.replaceState(null, '', url.toString());
    }

    function enc(v) { return encodeURIComponent(String(v)); }

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = String(str == null ? '' : str);
        return d.innerHTML;
    }

    function show(el) { if (el) { el.removeAttribute('hidden'); } }
    function hide(el) { if (el) { el.setAttribute('hidden', ''); } }
    function setLoading(on) { if (on) { show(loadingEl); } else { hide(loadingEl); } }

    // ── Card rendering ────────────────────────────────────────────────────────

    function renderCard(person) {
        var managerHtml = '';
        if (person.manager) {
            managerHtml = '<p class="sd-staff-card__manager">' +
                'Reports to: <strong>' + esc(person.manager) + '</strong>' +
                '</p>';
        }

        return '<article class="sd-staff-card">' +
            '<a href="' + esc(person.profile_url) + '" class="sd-staff-card__link">' +
                '<div class="sd-staff-card__avatar-wrap">' +
                    '<img src="' + esc(person.avatar_url) + '" alt="" ' +
                        'class="sd-staff-card__avatar" width="64" height="64">' +
                '</div>' +
                '<div class="sd-staff-card__body">' +
                    '<p class="sd-staff-card__name">' + esc(person.name) + '</p>' +
                    (person.business_title
                        ? '<p class="sd-staff-card__role">' + esc(person.business_title) + '</p>'
                        : '') +
                    managerHtml +
                '</div>' +
            '</a>' +
        '</article>';
    }

    function renderGrid(staff, container) {
        if (!container) { return; }
        container.innerHTML = staff.length
            ? staff.map(renderCard).join('')
            : '<p class="govuk-body">No staff members found.</p>';
    }

    // ── Panel switching ───────────────────────────────────────────────────────

    function showDefault() {
        hide(panelMeta);
        show(defaultContent);
        hide(staffPanel);
        hide(searchPanel);
        if (teamSummary) { teamSummary.textContent = ''; hide(teamSummary); }
    }

    function showStaff(directorateName, teamName, staff, summary) {
        if (panelBreadcrumb) { panelBreadcrumb.textContent = directorateName; }
        if (panelTitle)      { panelTitle.textContent      = teamName; }
        if (teamSummary) {
            if (summary) { teamSummary.textContent = summary; show(teamSummary); }
            else         { teamSummary.textContent = ''; hide(teamSummary); }
        }
        if (staffCount) {
            staffCount.textContent = 'Showing ' + staff.length + ' team member' +
                (staff.length === 1 ? '' : 's') + '.';
        }

        renderGrid(staff, staffGrid);

        hide(defaultContent);
        hide(searchPanel);
        show(panelMeta);
        show(staffPanel);
    }

    function showSearch(q, staff) {
        if (searchTitle) {
            searchTitle.textContent = staff.length
                ? 'Found ' + staff.length + ' result' + (staff.length === 1 ? '' : 's') + ' for “' + q + '”'
                : 'No results for “' + q + '”';
        }

        renderGrid(staff, searchGrid);

        hide(defaultContent);
        hide(staffPanel);
        hide(panelMeta);
        show(searchPanel);
    }

    // ── Directorate selection ─────────────────────────────────────────────────

    function selectDirectorate(name) {
        document.querySelectorAll('.sd-directory__team-btn').forEach(function (b) {
            b.classList.remove('sd-directory__team-btn--active');
        });

        state.directorate = name;
        state.team        = null;

        if (teamsList)    { teamsList.innerHTML = ''; }
        if (teamsSection) { hide(teamsSection); }

        showDefault();
        setLoading(true);

        apiFetch('teams?directorate=' + enc(name))
            .then(function (teams) {
                setLoading(false);
                teamsCache[name] = teams;

                if (!teams.length) { return; }

                teamsList.innerHTML = teams.map(function (t) {
                    return '<li class="sd-directory__nav-item">' +
                        '<button class="sd-directory__team-btn" type="button" ' +
                            'data-team="' + esc(t.name) + '" aria-pressed="false">' +
                            '<span class="sd-directory__team-dot" aria-hidden="true"></span>' +
                            esc(t.name) +
                        '</button>' +
                        '</li>';
                }).join('');

                teamsList.querySelectorAll('.sd-directory__team-btn').forEach(function (b) {
                    b.addEventListener('click', function () {
                        selectTeam(this.dataset.team, this);
                    });
                });

                if (pendingTeam) {
                    var matchBtn = null;
                    teamsList.querySelectorAll('.sd-directory__team-btn').forEach(function (b) {
                        if (b.dataset.team === pendingTeam) {
                            matchBtn = b;
                        }
                    });

                    if (matchBtn) {
                        selectTeam(pendingTeam, matchBtn);
                        pendingTeam = null;
                    }
                }

                show(teamsSection);
            })
            .catch(function () { setLoading(false); });
    }

    // ── Team selection ────────────────────────────────────────────────────────

    function selectTeam(name, btn) {
        document.querySelectorAll('.sd-directory__team-btn').forEach(function (b) {
            b.classList.remove('sd-directory__team-btn--active');
            b.setAttribute('aria-pressed', 'false');
        });
        if (btn) {
            btn.classList.add('sd-directory__team-btn--active');
            btn.setAttribute('aria-pressed', 'true');
        }

        state.team = name;
        updateUrl(name);
        setLoading(true);

        apiFetch('staff?directorate=' + enc(state.directorate) + '&team=' + enc(name))
            .then(function (staff) {
                setLoading(false);
                var teams = teamsCache[state.directorate] || [];
                var teamObj = null;
                for (var i = 0; i < teams.length; i++) {
                    if (teams[i].name === name) { teamObj = teams[i]; break; }
                }
                showStaff(state.directorate, name, staff, teamObj ? (teamObj.summary || '') : '');
            })
            .catch(function () { setLoading(false); });
    }

    function loadTeamByName(name) {
        // We need to discover the directorate for the given team so the
        // sidebar can expand to show the directorate -> team path. Query
        // the staff endpoint (which includes `directorate` in each record),
        // then set the directorate select and trigger `selectDirectorate`.
        pendingTeam = name;
        updateUrl(name);
        setLoading(true);

        apiFetch('staff?team=' + enc(name))
            .then(function (staff) {
                setLoading(false);

                var directorate = '';
                if (staff && staff.length) {
                    for (var i = 0; i < staff.length; i++) {
                        if (staff[i].directorate) { directorate = staff[i].directorate; break; }
                    }
                }

                if (directorate && directorateSelect) {
                    directorateSelect.value = directorate;
                    selectDirectorate(directorate);
                } else {
                    showStaff(directorate || '', name, staff || []);
                    pendingTeam = null;
                }
            })
            .catch(function () { setLoading(false); pendingTeam = null; });
    }

    // ── Name search ───────────────────────────────────────────────────────────

    var searchTimer = null;

    function doSearch(q) {
        q = q.trim();

        if (q.length < 2) {
            if (state.team) {
                // Re-show the active team view
                setLoading(true);
                apiFetch('staff?directorate=' + enc(state.directorate) + '&team=' + enc(state.team))
                    .then(function (staff) {
                        setLoading(false);
                        var teams = teamsCache[state.directorate] || [];
                        var teamObj = null;
                        for (var i = 0; i < teams.length; i++) {
                            if (teams[i].name === state.team) { teamObj = teams[i]; break; }
                        }
                        showStaff(state.directorate, state.team, staff, teamObj ? (teamObj.summary || '') : '');
                    })
                    .catch(function () { setLoading(false); });
            } else {
                showDefault();
            }
            return;
        }

        setLoading(true);
        apiFetch('search?q=' + enc(q))
            .then(function (staff) {
                setLoading(false);
                showSearch(q, staff);
            })
            .catch(function () { setLoading(false); });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { doSearch(searchInput.value); }, 300);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimer);
                doSearch(searchInput.value);
            }
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            clearTimeout(searchTimer);
            doSearch(searchInput ? searchInput.value : '');
        });
    }

    // ── Bind directorate dropdown ─────────────────────────────────────────────

    if (directorateSelect) {
        directorateSelect.addEventListener('change', function () {
            var name = this.value;
            if (!name) {
                // Reset to default state when placeholder is selected.
                state.directorate = null;
                state.team        = null;
                updateUrl(null);
                if (teamsList)    { teamsList.innerHTML = ''; }
                if (teamsSection) { hide(teamsSection); }
                showDefault();
                return;
            }
            selectDirectorate(name);
        });

        // Auto-select if there is only one directorate.
        if (directorateSelect.options.length === 2) {
            directorateSelect.selectedIndex = 1;
            directorateSelect.dispatchEvent(new Event('change'));
        }
    }

    // ── Initialize from URL query params ─────────────────────────────────────

    (function () {
        var initialTeam = getQueryParam('team');
        
        if (initialTeam) {
            loadTeamByName(initialTeam);
        }

    })();

})();
JS;
}
