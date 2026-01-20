<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
add_action('after_setup_theme', function () {

    load_theme_textdomain('gca-intranet');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

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
 * Enqueue Bootstrap (temporary CDN for local dev)
 */
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style(
        'gca-bootstrap',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
    );
});

/**
 * Remove unnecessary WordPress head output
 */
add_action('init', function () {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
});

/**
 * Provide a theme nonce for scripts - Even though we don’t have custom JS yet, for future proofing
 */
add_action('wp_enqueue_scripts', function () {
    wp_localize_script(
        'gca-bootstrap-js',
        'gcaSecurity',
        [
            'nonce' => wp_create_nonce('gca_theme_nonce'),
        ]
    );
});

/**
 * Bootstrap 5.3 (temporary CDN inclusion)
 *
 * Bootstrap is intentionally included via CDN during early development.
 * This allows layout and utility usage without introducing build tooling.
 *
 * Once designs are signed off and customisation is required, Bootstrap
 * will be compiled locally and this CDN inclusion removed.
 */
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'gca-bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
		[],
		'5.3.3'
	);
});
