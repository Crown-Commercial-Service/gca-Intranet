<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Purge Events
//
// Registers the WP-CLI command `wp gca purge-events` and the WordPress cron
// hook `gca_purge_events` to permanently delete event posts more than one
// month past their end date (or start date when no end date is set).
// -----------------------------------------------------------------------------

gca_register_feature_flag('purge-events', [
    'label'       => 'Purge Events',
    'description' => 'Permanently delete events more than one month after their end date (or start date if no end date is set). Schedule via Tools → Cron Jobs using the hook name `gca_purge_events`, or run manually with `wp gca purge-events`.',
    'default'     => false,
    'tags'        => ['cron', 'events', 'wp-cli'],
]);
