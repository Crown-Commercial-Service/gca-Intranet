<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Provide a safe in-theme fallback when the feature-flags plugin is inactive.
if (!function_exists('gca_register_feature_flag')) {
    $GLOBALS['gca_theme_feature_flags'] = $GLOBALS['gca_theme_feature_flags'] ?? [];

    function gca_register_feature_flag(string $id, array $args = []): void
    {
        $id = sanitize_key($id);

        if ($id === '') {
            return;
        }

        $GLOBALS['gca_theme_feature_flags'][$id] = wp_parse_args($args, [
            'label'       => ucwords(str_replace(['-', '_'], ' ', $id)),
            'description' => '',
            'default'     => false,
            'tags'        => [],
        ]);
    }
}

if (!function_exists('gca_flag_enabled')) {
    function gca_flag_enabled(string $id): bool
    {
        $id = sanitize_key($id);

        if ($id === '') {
            return false;
        }

        $flags = $GLOBALS['gca_theme_feature_flags'] ?? [];

        if (!isset($flags[$id])) {
            return false;
        }

        return (bool) ($flags[$id]['default'] ?? false);
    }
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
