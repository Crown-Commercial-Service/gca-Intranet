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

    // Allow WP admin to set a site logo (Customizer)
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 260,
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
        'primary'      => __('Primary Navigation', 'gca-intranet'),
        'footer_legal' => __('Footer legal links', 'gca-intranet'),
        'footer'       => __('Footer Navigation (legacy)', 'gca-intranet'),
    ]);
});

/**
 * Ensure the Customizer logo uses our header logo class for consistent sizing.
 */
add_filter('get_custom_logo', function (string $html): string {
    return preg_replace('/class="custom-logo"/', 'class="custom-logo gca-header-logo"', $html);
});

/**
 * Enqueue assets
 *
 * - gca-theme.css is compiled from SCSS and includes Bootstrap + theme overrides
 * - Bootstrap JS is served locally for dropdowns/collapse
 */
add_action('wp_enqueue_scripts', function (): void {

    // Cache-bust CSS on every build by using file modified time.
    $css_rel_path = '/assets/dist/gca-theme.css';
    $css_abs_path = get_template_directory() . $css_rel_path;
    $css_ver      = file_exists($css_abs_path) ? (string) filemtime($css_abs_path) : '1.0.0';

    wp_enqueue_style(
        'gca-theme',
        get_template_directory_uri() . $css_rel_path,
        [],
        $css_ver
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

////////// remove post tags and related UI elements //////////
add_action('admin_menu', function() {
    remove_menu_page('edit-tags.php?taxonomy=post_tag');
});

add_action('init', function() {
    unregister_taxonomy_for_object_type('post_tag', 'post');
});

add_filter('dashboard_glance_items', function($items) {
    foreach ($items as $key => $item) {
        if (strpos($item, 'taxonomy=post_tag') !== false) {
            unset($items[$key]);
        }
    }
    return $items;
}, 10, 1);
////////// remove post tags and related UI elements //////////


// ?import_gca_categories=1
add_action('init', function() {
    if (!isset($_GET['import_gca_categories'])) {
        return;
    }
    
    $categories = [
        'About GCA', 'People survey', 'Accessibility', 'Change management', 'Customers and suppliers', 'Digital and data', 'Finance', 'Knowledge Centre', 'Marketing and communications', 'Events', 'Inclusion and diversity', 'HR', 'Anti-fraud and corruption', 'Employee benefits', 'Health and wellbeing', 'Learning and development', 'Leave, absence and flexible working', 'New starters and leavers', 'Recruitment', 'Performance management', 'Pay and pensions', 'Respect at work', 'Workday', 'Information security', 'IT support', 'Workplace and travel', 'Health and safety', 'Community'
    ];

    $sub_categories = [
        'CCS live', 'People update', 'One Big Thing', 'Business update', 'Reward', 'Recognition'
    ];

    $content_types = [
        'Corporate information', 'Guidance', 'Blogs', 'Events', 'Staff network', 'News', 'Work updates'
    ];
    
    $audience = [
        'All colleagues', 'Line managers'
    ];

    $responsible_team = [
        'HR Directorate', 'Customer Experience Directorate', 'Procurement Operations Directorate', 'Commercial Directorate', 'Digital and Data Directorate', 'Strategic Delivery Directorate', 'Marketing and Communications', 'Finance, Planning and Performance Directorate', 'Finance', 'Workplace services', 'Customer service centere', 'Pride Network', 'Gender Equality Network', 'Race Equality Network', 'Able Network', 'Uniformed Services Network'
    ];

    $event_location = [
        'Online', 'In-person'
    ];

    add_tax($categories, 'category');
    add_tax($sub_categories, 'sub_categories');
    add_tax($content_types, 'content_type');
    add_tax($audience, 'audience');
    add_tax($responsible_team, 'responsible_team');
    add_tax($event_location, 'event_location');


    echo '</ul><p><strong>Import complete!</strong></p></div>';
    exit; 
});

function add_tax($array, $tax_name){

    foreach ($array as $cat_name) {
        $term = term_exists($cat_name, $tax_name);

        if (!$term) {
            $result = wp_insert_term(
                $cat_name,
                $tax_name,
                array(
                    'slug' => sanitize_title($cat_name)
                )
            );
            if (is_wp_error($result)) {
                echo "<li style='color:red;'>Error: " . $cat_name . " - " . $result->get_error_message() . "</li>";
            } else {
                echo "<li style='color:green;'>Created: " . $cat_name . "</li>";
            }
        } else {
            echo "<li style='color:orange;'>Skipped: " . $cat_name . " (Already exists)</li>";
        }
    }

}