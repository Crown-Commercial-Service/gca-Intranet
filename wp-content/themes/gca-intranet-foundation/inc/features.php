<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Feature flag registrations.
 *
 * Register a flag:
 *
 *   gca_register_feature_flag('my-feature', [
 *       'label'       => 'My Feature',
 *       'description' => 'Short description of what this controls.',
 *       'default'     => false,
 *   ]);
 *
 * Check a flag anywhere in templates or theme files:
 *
 *   if (gca_flag_enabled('my-feature')) { ... }
 *
 * Toggle flags in WP Admin → Settings → Feature Flags.
 *
 * Each feature lives in its own file under inc/features/.
 */

foreach (glob(__DIR__ . '/features/*.php') as $feature_file) {
    require_once $feature_file;
}
