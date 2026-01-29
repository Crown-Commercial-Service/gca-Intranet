<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Load Bootstrap 5 nav walker early (needed by header.php)
require_once get_template_directory() . '/inc/class-gca-bootstrap-5-navwalker.php';

/**
 * Theme setup
 */
add_action('after_setup_theme', function (): void {

    load_theme_textdomain('gca-intranet');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // Allow WP admin to set a site logo (GCA logo only)
    add_theme_support('custom-logo', [
        'height'      => 125,
        'width'       => 140,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_theme_support('align-wide');
    add_theme_support('editor-styles');

    register_nav_menus([
        'primary' => __('Primary Navigation', 'gca-intranet'),
        'footer'  => __('Footer Navigation', 'gca-intranet'),
    ]);
});

/**
 * Enqueue assets
 *
 * - gca-theme.css is compiled from SCSS and includes Bootstrap + theme overrides
 * - Bootstrap JS is served locally for dropdowns/collapse
 */
add_action('wp_enqueue_scripts', function (): void {

    wp_enqueue_style(
        'gca-theme',
        get_template_directory_uri() . '/assets/dist/gca-theme.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'gca-bootstrap-js',
        get_template_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
        [],
        '5.3.3',
        true
    );

    wp_localize_script(
        'gca-bootstrap-js',
        'gcaSecurity',
        [
            'nonce' => wp_create_nonce('gca_theme_nonce'),
        ]
    );
});

/**
 * Remove unnecessary WordPress head output
 */
add_action('init', function (): void {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
});

/**
 * Basic breadcrumbs (MVP)
 * - Home → ancestors → current
 * - Works for pages and single posts
 */
function gca_get_breadcrumb_items(): array
{
    $items = [];
    $items[] = [
        'label' => __('Home', 'gca-intranet'),
        'url'   => home_url('/'),
    ];

    if (is_home() || is_front_page()) {
        return $items;
    }

    if (is_page()) {
        global $post;

        $ancestors = get_post_ancestors($post);
        $ancestors = array_reverse($ancestors);

        foreach ($ancestors as $ancestor_id) {
            $items[] = [
                'label' => get_the_title($ancestor_id),
                'url'   => get_permalink($ancestor_id),
            ];
        }

        $items[] = [
            'label' => get_the_title($post),
            'url'   => get_permalink($post),
        ];

        return $items;
    }

    if (is_single()) {
        $post_id = get_the_ID();

        // Optional: if posts have a primary category, include it
        $cats = get_the_category($post_id);
        if (!empty($cats)) {
            $items[] = [
                'label' => $cats[0]->name,
                'url'   => get_category_link($cats[0]->term_id),
            ];
        }

        $items[] = [
            'label' => get_the_title($post_id),
            'url'   => get_permalink($post_id),
        ];

        return $items;
    }

    if (is_archive()) {
        $items[] = [
            'label' => wp_strip_all_tags(get_the_archive_title()),
            'url'   => '',
        ];
        return $items;
    }

    if (is_search()) {
        $items[] = [
            'label' => __('Search results', 'gca-intranet'),
            'url'   => '',
        ];
        return $items;
    }

    return $items;
}

add_action('after_setup_theme', function (): void {
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 260,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
});
