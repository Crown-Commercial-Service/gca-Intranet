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

    $data = array_map(fn($r) => [
        'name'  => $r->name,
        'count' => (int) $r->cnt,
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
            'job_title'   => trim((string) get_user_meta($user->ID, 'job_title', true)),
            'directorate' => trim((string) get_user_meta($user->ID, 'directorate', true)),
            'team'        => trim((string) get_user_meta($user->ID, 'team', true)),
            'manager'     => $manager,
            'avatar_url'  => $avatar_url,
            'profile_url' => home_url('/profile/' . rawurlencode($user->user_login) . '/'),
        ];
    }

    return $results;
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

    var state = { directorate: null, team: null };

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
                    (person.job_title
                        ? '<p class="sd-staff-card__role">' + esc(person.job_title) + '</p>'
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
    }

    function showStaff(directorateName, teamName, staff) {
        if (panelBreadcrumb) { panelBreadcrumb.textContent = directorateName; }
        if (panelTitle)      { panelTitle.textContent      = teamName; }
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
        setLoading(true);

        apiFetch('staff?directorate=' + enc(state.directorate) + '&team=' + enc(name))
            .then(function (staff) {
                setLoading(false);
                showStaff(state.directorate, name, staff);
            })
            .catch(function () { setLoading(false); });
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
                        showStaff(state.directorate, state.team, staff);
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

})();
JS;
}
