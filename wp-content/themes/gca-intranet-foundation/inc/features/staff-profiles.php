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
