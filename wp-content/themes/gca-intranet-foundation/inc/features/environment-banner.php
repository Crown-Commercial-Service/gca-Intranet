<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Environment Banner
//
// Shows a yellow banner at the top of every page indicating the current
// environment. Requires WP_ENVIRONMENT_TYPE to be set (e.g. in wp-config.php
// or as an environment variable). Never shown in production regardless of the
// flag state.
// -----------------------------------------------------------------------------

gca_register_feature_flag('environment-banner', [
    'label'       => 'Environment Banner',
    'description' => 'Shows a yellow banner at the top of every page indicating the current environment. Never shown in production.',
    'default'     => false,
    'tags'        => ['ui', 'environment'],
]);

add_action('wp_body_open', function (): void {
    if (!gca_flag_enabled('environment-banner')) {
        return;
    }

    $env = wp_get_environment_type(); // reads WP_ENVIRONMENT_TYPE; defaults to 'production'

    if ('production' === $env) {
        return;
    }

    $labels = [
        'local'       => 'local',
        'development' => 'dev',
        'staging'     => 'staging',
    ];

    $label = $labels[$env] ?? $env;
    ?>
    <div class="gca-env-banner" role="status">
        This is the <strong><?php echo esc_html($label); ?></strong> environment
    </div>
    <?php
}, 5); // priority 5 — runs before other wp_body_open hooks

add_action('wp_head', function (): void {
    if (!gca_flag_enabled('environment-banner')) {
        return;
    }

    if ('production' === wp_get_environment_type()) {
        return;
    }
    ?>
    <style>
        .gca-env-banner {
            background: #fd0;
            color: #0b0c0c;
            padding: 10px 15px;
            font-family: "GDS Transport", arial, sans-serif;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.25;
        }
    </style>
    <?php
});
