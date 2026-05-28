<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

gca_register_feature_flag('related-articles', [
    'label'       => 'Related Articles',
    'description' => 'Shows a "Related articles" section at the bottom of news, blog, work update, and event posts. Editors can pin specific articles or let the section auto-populate from a chosen taxonomy term.',
    'default'     => false,
    'tags'        => ['ui', 'content'],
]);

if (!gca_flag_enabled('related-articles')) {
    return;
}

add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    wp_enqueue_script(
        'gca-related-articles-admin',
        get_template_directory_uri() . '/assets/scripts/admin/related-articles.js',
        ['jquery'],
        '1.0.0',
        true
    );
});
