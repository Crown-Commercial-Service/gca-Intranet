<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Workday User Sync
//
// Syncs WordPress users with the Workday staff list API. Registers the WP cron
// hook `gca_sync_workday_users` (schedule via Tools → Cron Jobs) and the
// WP-CLI command `wp gca sync-users` for manual runs.
// -----------------------------------------------------------------------------

gca_register_feature_flag('workday-user-sync', [
    'label'       => 'Workday User Sync',
    'description' => 'Syncs WordPress users with the Workday staff list API. Schedule via Tools → Cron Jobs using hook name `gca_sync_workday_users`, or run manually with `wp gca sync-users`.',
    'default'     => true,
    'tags'        => ['users', 'cron', 'workday', 'wp-cli'],
]);
