<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Staff Profiles
//
// Registers the /profile/<user_nicename> URL and renders a staff profile page
// pulling data from WordPress user meta (synced from Workday).
// -----------------------------------------------------------------------------

gca_register_feature_flag('staff-profiles', [
    'label'       => 'Staff Profiles',
    'description' => 'Enables public staff profile pages at /profile/<username>. Data is pulled from Workday user sync.',
    'default'     => true,
    'tags'        => ['users', 'profiles'],
]);

/**
 * When staff profiles are enabled, fetch ALL matching posts in the search
 * query (posts_per_page = -1) so search.php can merge them with staff
 * results and paginate the combined list manually.
 *
 * Priority 20 runs after functions.php's hook at priority 10 which sets
 * posts_per_page = 10, so this correctly overrides it.
 */
add_action('pre_get_posts', function (WP_Query $query): void {
    if (
        !is_admin()
        && $query->is_main_query()
        && $query->is_search()
        && gca_flag_enabled('staff-profiles')
    ) {
        $query->set('posts_per_page', -1);
        $query->set('no_found_rows', true); // skip SQL_CALC_FOUND_ROWS — we count manually
    }
}, 20);

/**
 * Register the custom rewrite rule and query var.
 */
add_action('init', function (): void {
    if (!gca_flag_enabled('staff-profiles')) {
        return;
    }

    add_rewrite_tag('%gca_profile%', '([^/]+)');
    add_rewrite_rule('^profile/([^/]+)/?$', 'index.php?gca_profile=$matches[1]', 'top');
});

/**
 * Search users by display name, job title, or team.
 *
 * @param  string $query  Raw search term.
 * @param  int    $limit  Max results to return.
 * @return array  Array of plain objects with: user, display_name, email, job_title, team, avatar_url, profile_url.
 */
function gca_search_staff(string $query, int $limit = 5): array
{
    $query = trim($query);
    if ($query === '') {
        return [];
    }

    // Primary: search display_name, user_login, user_email, user_nicename.
    $user_query = new WP_User_Query([
        'search'         => '*' . $query . '*',
        'search_columns' => ['display_name', 'user_login', 'user_email'],
        'number'         => $limit,
        'orderby'        => 'display_name',
        'order'          => 'ASC',
        'fields'         => 'all',
    ]);

    $users = $user_query->get_results();

    // Secondary: if fewer results than limit, also search job_title and team meta.
    if (count($users) < $limit) {
        $meta_query = new WP_User_Query([
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => 'job_title',
                    'value'   => $query,
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'team',
                    'value'   => $query,
                    'compare' => 'LIKE',
                ],
            ],
            'number'  => $limit - count($users),
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'fields'  => 'all',
            'exclude' => wp_list_pluck($users, 'ID'),
        ]);

        $users = array_merge($users, $meta_query->get_results());
    }

    $results = [];

    foreach ($users as $user) {
        if (!$user instanceof WP_User) {
            continue;
        }

        // Skip system accounts with no real name.
        if (in_array($user->user_login, ['admin', 'adminuser'], true)) {
            continue;
        }

        $avatar_url = (string) get_user_meta($user->ID, 'google_profile_picture_local_url', true);
        if (empty($avatar_url)) {
            $avatar_url = get_avatar_url($user->ID, ['size' => 64]);
        }

        $results[] = (object) [
            'user'         => $user,
            'display_name' => $user->display_name ?: $user->user_login,
            'email'        => $user->user_email,
            'job_title'    => (string) get_user_meta($user->ID, 'job_title', true),
            'team'         => (string) get_user_meta($user->ID, 'team', true),
            'avatar_url'   => $avatar_url,
            'profile_url'  => home_url('/profile/' . $user->user_nicename . '/'),
        ];
    }

    return $results;
}

/**
 * Point WordPress at our profile.php template when the query var is present.
 */
add_filter('template_include', function (string $template): string {
    if (!gca_flag_enabled('staff-profiles')) {
        return $template;
    }

    $slug = get_query_var('gca_profile');
    if (empty($slug)) {
        return $template;
    }

    $profile_template = get_template_directory() . '/profile.php';
    if (file_exists($profile_template)) {
        return $profile_template;
    }

    return $template;
});
