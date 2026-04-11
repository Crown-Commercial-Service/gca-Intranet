<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Cron Manager
//
// Provides a WP Admin → Tools → Cron Jobs screen where administrators can
// list, create, edit, delete, and manually trigger WordPress cron events.
// -----------------------------------------------------------------------------

gca_register_feature_flag('cron-manager', [
    'label'       => 'Cron Manager',
    'description' => 'Admin interface (Tools → Cron Jobs) to view, create, edit, delete, and manually run WordPress cron jobs.',
    'default'     => true,
    'tags'        => ['admin', 'cron'],
]);

add_action('init', function (): void {
    if (!is_admin()) {
        return;
    }

    if (!gca_flag_enabled('cron-manager')) {
        return;
    }

    require_once get_template_directory() . '/inc/class-gca-cron-manager.php';
    GCA_Cron_Manager::init();
});
