<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/class-ccs-mega-menu-walker.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require get_template_directory() . '/inc/auth-logic.php';

// Feature flag registrations
require get_template_directory() . '/inc/features.php';

/**
 * Theme setup
 */
add_action('after_setup_theme', function (): void {

    load_theme_textdomain('gca-intranet');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_post_type_support('page', 'excerpt');

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
        'top_bar'      => __('Top Bar menu', 'gca-intranet'),
        'footer_legal' => __('Footer legal links', 'gca-intranet'),
    ]);
});

/**
 * Ensure the Customizer logo uses our header logo class for consistent sizing.
 */
add_filter('get_custom_logo', function (string $html): string {
    return preg_replace('/class="custom-logo"/', 'class="custom-logo gca-header-logo"', $html);
});

/**
 * Enqueue Core Assets
 * Merged into one block to prevent script conflicts and 404s.
 */
add_action('wp_enqueue_scripts', function (): void {

    // 1. Parent Theme CSS
    $css_rel_path = '/assets/dist/gca-theme.css';
    $css_abs_path = get_template_directory() . $css_rel_path;
    $css_ver      = file_exists($css_abs_path) ? (string) filemtime($css_abs_path) : '1.0.0';

    wp_enqueue_style(
        'gca-theme',
        get_template_directory_uri() . $css_rel_path,
        [],
        $css_ver
    );

    // 2. Theme style.css (contains migrated child theme CSS)
    $style_abs = get_template_directory() . '/style.css';
    $style_ver = file_exists($style_abs) ? (string) filemtime($style_abs) : '1.0.0';

    wp_enqueue_style(
        'gca-theme-style',
        get_template_directory_uri() . '/style.css',
        ['gca-theme'],
        $style_ver
    );

    // 2. Cookie banner inline CSS (avoids a separate build step)
    wp_add_inline_style('gca-theme', '
.gca-cookie-banner{box-sizing:border-box;width:100%;padding:20px 0}
@media print{.gca-cookie-banner{display:none!important}}
.gca-cookie-banner__inner{padding-left:15px;padding-right:15px;max-width:960px;margin:0 auto}
@media(min-width:641px){.gca-cookie-banner__inner{padding-left:30px;padding-right:30px}}
.gca-cookie-banner__title{margin-bottom:10px}
.gca-cookie-banner__content{margin-bottom:20px}
.gca-cookie-banner__content p:last-child{margin-bottom:0}
.gca-cookie-banner__btn{margin-bottom:0;background-color:#B8CA1C;color:#000000;font-family:"Source Sans Pro",sans-serif;font-size:19px;font-weight:400;line-height:20px;letter-spacing:0.1px;border:none;border-radius:4px;padding:10px 20px;cursor:pointer}
.gca-cookie-banner__btn:hover{background-color:#a3b419;color:#000000}
.gca-cookie-banner__btn:focus{outline:3px solid #fd0;outline-offset:0;box-shadow:inset 0 0 0 2px #000;background-color:#B8CA1C;color:#000000}
/* Fix: .gca-header-logo-col uses position:absolute — give .site-header a positioning context
   so the logo anchors to the header, not the viewport. Without this, the logo renders at
   top:35px from the viewport and overlaps the cookie banner sitting above the header. */
.site-header{position:relative}
');

    // 3. Custom Navigation Logic (Mobile Menu & Dropdowns)
    wp_register_script('gca-nav', '', [], false, true);
    wp_enqueue_script('gca-nav');
    wp_add_inline_script('gca-nav', '
    document.addEventListener("DOMContentLoaded", function() {
        // Mobile Menu Toggler
        var toggleBtn = document.querySelector(".global-navigation__toggler");
        var navContainer = document.getElementById("primaryNav");

        if (toggleBtn && navContainer) {
            toggleBtn.addEventListener("click", function() {
                var isExpanded = this.getAttribute("aria-expanded") === "true";
                this.setAttribute("aria-expanded", !isExpanded);
                navContainer.setAttribute("aria-hidden", isExpanded ? "true" : "false");
            });
        }

        // Tablet dropdown toggle (769px–1023px)
        // Arrow button click opens/closes sub-menu; hovering a different item closes any open one
        if (window.innerWidth >= 769 && window.innerWidth < 1024) {
            var megaItems   = document.querySelectorAll(".nav-list--primary > li.has-mega-menu");
            var megaToggles = document.querySelectorAll(".nav-list--primary > li.has-mega-menu > .mega-menu-toggle");

            megaToggles.forEach(function(btn) {
                btn.addEventListener("click", function() {
                    var parentLi = this.closest("li.has-mega-menu");
                    var isOpen   = parentLi.classList.contains("is-open");

                    // Close all open items first
                    megaItems.forEach(function(li) { li.classList.remove("is-open"); });

                    // Open this one if it was not already open
                    if (!isOpen) {
                        parentLi.classList.add("is-open");
                    }
                });
            });

            // When hovering a menu item, close any previously clicked-open item
            megaItems.forEach(function(li) {
                li.addEventListener("mouseenter", function() {
                    megaItems.forEach(function(otherLi) {
                        if (otherLi !== li) {
                            otherLi.classList.remove("is-open");
                        }
                    });
                });
            });
        }

    });');

    wp_register_script('gca-cookie-banner', '', [], false, true);
    wp_enqueue_script('gca-cookie-banner');
    wp_add_inline_script('gca-cookie-banner', '
(function () {
    var STORAGE_KEY = "gca_cookie_consent";
    var EXPIRY_DAYS = 30;

    function shouldShowBanner(banner) {
        var currentVersion = banner.getAttribute("data-cookies-version") || "1.0";
        try {
            var stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || "null");
            if (!stored)                           return true; // first visit
            if (stored.version !== currentVersion) return true; // policy updated
            if (Date.now() > stored.expiry)        return true; // 30-day expiry
            return false;
        } catch (e) {
            return true; // localStorage unavailable — show banner
        }
    }

    function saveConsent(version) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                version: version,
                expiry:  Date.now() + (EXPIRY_DAYS * 24 * 60 * 60 * 1000)
            }));
        } catch (e) {}
    }

    document.addEventListener("DOMContentLoaded", function () {
        var banner = document.getElementById("gca-cookie-banner");
        if (!banner) return;

        if (shouldShowBanner(banner)) {
            banner.removeAttribute("hidden");
        }

        var btn = document.getElementById("gca-cookie-banner-accept");
        if (btn) {
            btn.addEventListener("click", function () {
                var version = banner.getAttribute("data-cookies-version") || "1.0";
                saveConsent(version);
                banner.setAttribute("hidden", "");
            });
        }
    });
})();
');

    // GOV.UK Frontend JS
    if (is_singular()) {
        $govuk_js_rel = '/assets/scripts/all.js';
        $govuk_js_abs = get_template_directory() . $govuk_js_rel;
        $govuk_js_ver = file_exists($govuk_js_abs) ? (string) filemtime($govuk_js_abs) : '1.0.0';

        wp_enqueue_script(
            'gca-govuk-frontend',
            get_template_directory_uri() . $govuk_js_rel,
            [],
            $govuk_js_ver,
            true
        );

        wp_add_inline_script(
            'gca-govuk-frontend',
            'document.addEventListener("DOMContentLoaded", function() {
                if (window.GOVUKFrontend && typeof window.GOVUKFrontend.initAll === "function") {
                    window.GOVUKFrontend.initAll();
                    document.documentElement.classList.add("js-enabled");
                }
            });',
            'after'
        );
    }

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
        $post_type = get_post_type();

        $items[] = [
            'label' => get_post_type_object($post_type)->labels->name,
            'url'   => get_post_type_archive_link($post_type)
        ];

        $post_id = get_the_ID();

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

add_action('admin_menu', function() {
    remove_menu_page('edit.php');
});

add_action('admin_bar_menu', function($wp_admin_bar) {
    $wp_admin_bar->remove_node('new-post');
}, 999);


add_action('admin_menu', function() {
    global $menu;

    $pages_key = null;
    foreach ($menu as $key => $item) {
        if ($item[2] === 'edit.php?post_type=page') {
            $pages_key = $key;
            break;
        }
    }

    if ($pages_key !== null) {
        $pages_item = $menu[$pages_key];
        unset($menu[$pages_key]);
        $menu[31] = $pages_item;
        ksort($menu);
    }
}, 999);


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


function gca_search_get_content_type_label(): string
{
    $post_type = (string) get_post_type();

    if ($post_type === 'page') {
        $ct_terms = get_the_terms(get_the_ID(), 'content_type');
        if (!empty($ct_terms) && !is_wp_error($ct_terms)) {
            return $ct_terms[0]->name;
        }
        return __('Page', 'gca-intranet');
    }

    $obj = get_post_type_object($post_type);
    return $obj ? $obj->labels->singular_name : ucwords(str_replace(['-', '_'], ' ', $post_type));
}

/**
 * Get all displayable taxonomy terms for the current post in the loop.
 *
 * @return WP_Term[]
 */
function gca_search_get_post_terms(): array
{
    $post_id  = get_the_ID();
    $excluded = ['post_format', 'post_tag', 'content_type', 'nav_menu', 'link_category'];
    $all      = [];

    foreach (get_object_taxonomies((string) get_post_type()) as $tax_name) {
        if (in_array($tax_name, $excluded, true)) {
            continue;
        }
        $terms = get_the_terms($post_id, $tax_name);
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if (strtolower($term->name) === 'uncategorized' || strtolower($term->name) === 'uncategorised') {
                    continue;
                }
                if ($term->taxonomy === 'audience' && strtolower($term->name) === 'all colleagues') {
                    continue;
                }
                $all[] = $term;
            }
        }
    }

    return $all;
}

function gca_search_truncate(string $str, int $length): string
{
    $str = wp_strip_all_tags($str);
    if (mb_strlen($str) <= $length) {
        return $str;
    }
    return rtrim(mb_substr($str, 0, $length - 1)) . '…';
}

function gca_clean_post_excerpt(int $length = 320): string
{
    $content = get_the_content();
    $content = preg_replace('/<table[\s\S]*?<\/table>/i', '', $content);
    $content = strip_shortcodes($content);
    $content = wp_strip_all_tags($content);
    $content = preg_replace('/(?:https?:\/\/|www\.)\S+/i', '', $content);
    $content = trim(preg_replace('/\s+/', ' ', $content));

    if (mb_strlen($content) <= $length) {
        return $content;
    }
    return rtrim(mb_substr($content, 0, $length)) . '...';
}

add_action('pre_get_posts', function (WP_Query $query): void {
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('event')) {
        $query->set('meta_key', 'start_date');
        $query->set('orderby', 'meta_value');
        $query->set('order', 'ASC');
        $query->set('meta_query', [
            [
                'key'     => 'start_date',
                'value'   => date('Ymd'),
                'compare' => '>=',
                'type'    => 'DATE',
            ],
        ]);
    }
});

/**
 * Limit search results to 10 per page and exclude news post type.
 */
add_action('pre_get_posts', function (WP_Query $query): void {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $query->set('posts_per_page', 10);

        $post_types = array_values(array_diff(
            array_keys(get_post_types(['public' => true, 'exclude_from_search' => false])),
            ['news']
        ));
        $query->set('post_type', $post_types);
    }
});

add_filter('posts_join', function (string $join, WP_Query $query): string {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        global $wpdb;
        $join .= " LEFT JOIN {$wpdb->postmeta} AS acf_search_meta
                    ON ({$wpdb->posts}.ID = acf_search_meta.post_id) ";
    }
    return $join;
}, 10, 2);

add_filter('posts_search', function (string $search, WP_Query $query): string {
    if (!is_admin() && $query->is_main_query() && $query->is_search() && !empty($search)) {
        global $wpdb;

        $term = $query->get('s');
        if (empty($term)) {
            return $search;
        }

        $like = '%' . $wpdb->esc_like($term) . '%';

        $meta_sql = $wpdb->prepare(
            "(
                acf_search_meta.meta_value LIKE %s
                AND (
                    acf_search_meta.meta_key NOT LIKE '\\_%%'
                    OR acf_search_meta.meta_key = '_gca_col2_wysiwyg'
                )
            )",
            $like
        );

        $search = ' AND (' . substr($search, 5) . ' OR ' . $meta_sql . ')';
    }
    return $search;
}, 10, 2);

add_filter('posts_groupby', function (string $groupby, WP_Query $query): string {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        global $wpdb;
        $groupby = "{$wpdb->posts}.ID";
    }
    return $groupby;
}, 10, 2);

////////// assigning category to page object //////////
add_action('init', function() {
    register_taxonomy_for_object_type('category', 'page');

    global $wp_taxonomies;
    if (isset($wp_taxonomies['category'])) {
        $wp_taxonomies['category']->show_in_rest = true;
    }
});
////////// assigning category to page object //////////

// ?import_gca_categories=1
add_action('init', function() {
    if (!isset($_GET['import_gca_categories'])) {
        return;
    }

    $categories = [
        'About GCA', 'People survey', 'Accessibility', 'Change management', 'Customers and suppliers', 'Digital and data', 'Finance', 'Knowledge Centre', 'Marketing and communications', 'Events', 'Inclusion and diversity', 'HR', 'Anti-fraud and corruption', 'Employee benefits', 'Health and wellbeing', 'Learning and development', 'Leave, absence and flexible working', 'New starters and leavers', 'Recruitment', 'Performance management', 'Pay and pensions', 'Respect at work', 'Workday', 'Information security', 'IT support', 'Workplace and travel', 'Health and safety', 'Community'
    ];

    $labels = [
        'CCS live', 'People update', 'One Big Thing', 'Business update', 'Reward', 'Recognition'
    ];

    $content_types = [
        'Corporate information', 'Guidance', 'Staff network',
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
    add_tax($labels, 'label');
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

//  ?nuke_terms=1
add_action('init', function() {
    if (!isset($_GET['nuke_terms'])) return;

    // List the taxonomies you want to empty
    $taxonomies = ['category', 'label', 'content_type', 'audience', 'responsible_team', 'event_location',];

    foreach ($taxonomies as $tax) {
        $terms = get_terms([
            'taxonomy'   => $tax,
            'hide_empty' => false,
        ]);

        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $tax);
        }
    }

    echo "All terms in specified taxonomies have been deleted.";
    exit;
});

// Make sure WP_CLI is actually running before defining the class
if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class GCA_Import_Command {

        /**
         * Runs the custom GCA permalink and hierarchy importer.
         *
         * ## OPTIONS
         *
         * <file>
         * : The path to the CSV file you want to import.
         *
         * [--dry-run]
         * : Run the command without actually modifying the database.
         *
         * ## EXAMPLES
         *
         * wp gca importPermaLinks uat-permalinks-import.csv
         * wp gca importPermaLinks uat-permalinks-import.csv --dry-run
         *
         * @when after_wp_load
         */
        public function importPermaLinks( $args, $assoc_args ) {
            list( $file ) = $args;
            $dry_run = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );

            if ( ! file_exists( $file ) ) {
                \WP_CLI::error( "File not found: $file" );
            }

            \WP_CLI::line( "Starting import from: $file" );

            if ( $dry_run ) {
                \WP_CLI::success( "Dry run complete! No data was changed." );
                return;
            }

            global $wpdb;

            if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {

                $file_lines = file( $file, FILE_SKIP_EMPTY_LINES );
                $row_count  = count( $file_lines );
                $progress   = \WP_CLI\Utils\make_progress_bar( 'Updating permalinks & hierarchy', $row_count );

                while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {

                    // 1. Grab raw data from CSV
                    $raw_old_slug = trim( $data[0] );
                    $raw_new_path = trim( $data[1] );

                    // Skip empty rows or header rows if necessary
                    if ( empty( $raw_old_slug ) || empty( $raw_new_path ) || $raw_old_slug === 'old_slug' ) {
                        $progress->tick();
                        continue;
                    }

                    $old_slug     = sanitize_title( $raw_old_slug );
                    $cleaned_path = trim( $raw_new_path, '/' ); // Remove leading/trailing slashes
                    $path_parts   = explode( '/', $cleaned_path ); // e.g., ['hr', 'workday', 'workday-staff-directory']

                    // ==========================================
                    // PHASE 1: RENAME THE SLUG
                    // ==========================================
                    // The new slug is always the very last part of the path array
                    $new_final_slug = sanitize_title( end( $path_parts ) );

                    // Only run the update if the slug actually needs changing
                    if ( $old_slug !== $new_final_slug ) {
                        $wpdb->update(
                            $wpdb->posts,
                            array( 'post_name' => $new_final_slug ),
                            array( 'post_name' => $old_slug ),
                            array( '%s' ),
                            array( '%s' )
                        );
                    }

                    // ==========================================
                    // PHASE 2: BUILD THE HIERARCHY
                    // ==========================================
                    $current_parent_id = 0;

                    foreach ( $path_parts as $index => $slug ) {
                        $safe_slug = sanitize_title( $slug );

                        // Find the post ID for this piece of the path
                        // (We search by post_name, making sure we only hit actual pages/posts, not attachments)
                        $post_id = $wpdb->get_var( $wpdb->prepare(
                            "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type IN ('page', 'post') LIMIT 1",
                            $safe_slug
                        ) );

                        if ( $post_id ) {
                            // If this isn't the first item, assign it to the previous parent
                            if ( $index > 0 && $current_parent_id > 0 ) {
                                $wpdb->update(
                                    $wpdb->posts,
                                    array( 'post_parent' => $current_parent_id ),
                                    array( 'ID'          => $post_id ),
                                    array( '%d' ),
                                    array( '%d' )
                                );
                            }

                            // Set this current page as the parent for the NEXT loop iteration
                            $current_parent_id = $post_id;

                        } else {
                            // If a page in the middle of the folder path doesn't exist, we warn you and break the chain
                            \WP_CLI::warning( "Missing page: '$safe_slug' (from path: $cleaned_path). Breaking hierarchy." );
                            $current_parent_id = 0;
                        }
                    }

                    $progress->tick();
                }

                fclose( $handle );
                $progress->finish();

                \WP_CLI::success( "Boom! All permalinks and hierarchies updated successfully." );
                \WP_CLI::warning( "Remember to run 'wp rewrite flush' so WordPress picks up the new URLs!" );

            } else {
                \WP_CLI::error( "Could not open the CSV file for reading." );
            }
        }
    }

    \WP_CLI::add_command( 'gca', 'GCA_Import_Command' );
}

add_action('admin_menu', function (): void {
    add_menu_page(
        __('Global Settings', 'gca-intranet'),
        __('Global Settings', 'gca-intranet'),
        'manage_options',
        'gca-global-settings',
        'gca_global_settings_page',
        'dashicons-admin-settings',
        25
    );
});

add_action('admin_init', function (): void {
    register_setting('gca_global_settings', 'gca_cookies_title', [
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'Cookies on GCA',
    ]);

    register_setting('gca_global_settings', 'gca_cookies_content', [
        'sanitize_callback' => 'wp_kses_post',
        'default'           => '',
    ]);

    register_setting('gca_global_settings', 'gca_cookies_policy_version', [
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '1.0',
    ]);

    foreach (['gca_banner_homepage', 'gca_banner_news', 'gca_banner_blogs', 'gca_banner_events', 'gca_banner_work_updates'] as $key) {
        register_setting('gca_global_settings', $key, [
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ]);
    }
});

add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'toplevel_page_gca-global-settings') {
        return;
    }
    wp_enqueue_media();
});

/**
 * Returns the banner image URL for a given option key, falling back to a
 * bundled theme image when no upload has been saved.
 */
function gca_get_banner_url(string $option_key, string $fallback_filename): string
{
    $attachment_id = (int) get_option($option_key, 0);
    if ($attachment_id > 0) {
        $url = wp_get_attachment_image_url($attachment_id, 'full');
        if ($url) {
            return $url;
        }
    }
    return get_template_directory_uri() . '/assets/img/' . $fallback_filename;
}

function gca_global_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $updated = isset($_GET['settings-updated']);
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Global Settings', 'gca-intranet'); ?></h1>

      <?php if ($updated): ?>
        <div class="notice notice-success is-dismissible">
          <p><?php esc_html_e('Settings saved.', 'gca-intranet'); ?></p>
        </div>
      <?php endif; ?>

      <form method="post" action="options.php">
        <?php settings_fields('gca_global_settings'); ?>

        <h2><?php esc_html_e('Cookies banner', 'gca-intranet'); ?></h2>
        <p class="description"><?php esc_html_e('Content displayed in the cookies notice shown to users on first visit.', 'gca-intranet'); ?></p>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row">
              <label for="gca_cookies_title"><?php esc_html_e('Banner title', 'gca-intranet'); ?></label>
            </th>
            <td>
              <input
                type="text"
                id="gca_cookies_title"
                name="gca_cookies_title"
                value="<?php echo esc_attr(get_option('gca_cookies_title', 'Cookies on GCA')); ?>"
                class="regular-text"
              >
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="gca_cookies_content"><?php esc_html_e('Banner content', 'gca-intranet'); ?></label>
            </th>
            <td>
              <?php
                wp_editor(
                    get_option('gca_cookies_content', ''),
                    'gca_cookies_content',
                    [
                        'textarea_name' => 'gca_cookies_content',
                        'media_buttons' => false,
                        'textarea_rows' => 6,
                        'tinymce'       => true,
                        'quicktags'     => true,
                    ]
                );
              ?>
            </td>
          </tr>
          <tr>
            <th scope="row">
              <label for="gca_cookies_policy_version"><?php esc_html_e('Policy version', 'gca-intranet'); ?></label>
            </th>
            <td>
              <input
                type="text"
                id="gca_cookies_policy_version"
                name="gca_cookies_policy_version"
                value="<?php echo esc_attr(get_option('gca_cookies_policy_version', '1.0')); ?>"
                class="regular-text"
              >
              <p class="description">
                <?php esc_html_e('Update this value whenever the cookies policy changes (e.g. use today\'s date: 2026-03-10). Changing it forces the banner to reappear for all users.', 'gca-intranet'); ?>
              </p>
            </td>
          </tr>
        </table>

        <h2><?php esc_html_e('Banner images', 'gca-intranet'); ?></h2>
        <p class="description"><?php esc_html_e('Images displayed in the hero banner on listing pages and the homepage. Leave blank to use the default theme image.', 'gca-intranet'); ?></p>

        <?php
        $banner_fields = [
            'gca_banner_homepage'     => __('Homepage', 'gca-intranet'),
            'gca_banner_news'         => __('News', 'gca-intranet'),
            'gca_banner_blogs'        => __('Blogs', 'gca-intranet'),
            'gca_banner_events'       => __('Events', 'gca-intranet'),
            'gca_banner_work_updates' => __('Work updates', 'gca-intranet'),
        ];
        ?>
        <table class="form-table" role="presentation">
          <?php foreach ($banner_fields as $option_key => $label) :
            $attachment_id  = (int) get_option($option_key, 0);
            $preview_url    = $attachment_id > 0 ? wp_get_attachment_image_url($attachment_id, 'medium') : '';
            $field_id       = esc_attr($option_key);
          ?>
          <tr>
            <th scope="row">
              <label for="<?php echo $field_id; ?>"><?php echo esc_html($label); ?></label>
            </th>
            <td>
              <input
                type="hidden"
                id="<?php echo $field_id; ?>"
                name="<?php echo $field_id; ?>"
                value="<?php echo esc_attr($attachment_id ?: ''); ?>"
                class="gca-banner-input"
              >
              <div class="gca-banner-preview" style="margin-bottom:8px;">
                <?php if ($preview_url) : ?>
                  <img src="<?php echo esc_url($preview_url); ?>" alt="" style="max-width:300px;max-height:100px;display:block;object-fit:cover;border-radius:4px;">
                <?php endif; ?>
              </div>
              <button type="button" class="button gca-banner-select" data-target="<?php echo $field_id; ?>"><?php esc_html_e('Select image', 'gca-intranet'); ?></button>
              <button type="button" class="button gca-banner-remove" data-target="<?php echo $field_id; ?>"<?php echo $attachment_id ? '' : ' style="display:none"'; ?>><?php esc_html_e('Remove', 'gca-intranet'); ?></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>

        <script>
        (function ($) {
          $('.gca-banner-select').on('click', function () {
            var targetId  = $(this).data('target');
            var $input    = $('#' + targetId);
            var $preview  = $input.siblings('.gca-banner-preview');
            var $remove   = $input.siblings('.gca-banner-remove');
            var frame = wp.media({
              title:    '<?php echo esc_js(__('Select banner image', 'gca-intranet')); ?>',
              button:   { text: '<?php echo esc_js(__('Use this image', 'gca-intranet')); ?>' },
              multiple: false,
              library:  { type: 'image' }
            });
            frame.on('select', function () {
              var attachment = frame.state().get('selection').first().toJSON();
              $input.val(attachment.id);
              var src = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
              $preview.html('<img src="' + src + '" alt="" style="max-width:300px;max-height:100px;display:block;object-fit:cover;border-radius:4px;">');
              $remove.show();
            });
            frame.open();
          });

          $('.gca-banner-remove').on('click', function () {
            var targetId = $(this).data('target');
            var $input   = $('#' + targetId);
            $input.val('');
            $input.siblings('.gca-banner-preview').empty();
            $(this).hide();
          });
        }(jQuery));
        </script>

        <?php submit_button(); ?>
      </form>
    </div>
    <?php
}

add_filter('fewbricks/project_files_base_path', 'get_project_files_base_path');

function get_project_files_base_path() {
    return WP_PLUGIN_DIR . '/fewbricks_definitions';
}

add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = get_the_author();
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    } elseif (is_tax()) {
        $title = single_term_title('', false);
    }
    return $title;
});

/**
 * 1. Modify the Toolbar: Remove 'Headings' and Add 'Formats'
 */
add_filter('mce_buttons', function($buttons) {
    // Remove the default 'formatselect' (the Heading dropdown)
    if (($key = array_search('formatselect', $buttons)) !== false) {
        unset($buttons[$key]);
    }

    // Add 'styleselect' (the Formats dropdown) to the front of the toolbar
    array_unshift($buttons, 'styleselect');

    return $buttons;
});

/**
 * 2. Define the Single Unified List
 */
add_filter('tiny_mce_before_init', function($init_array) {

    $style_formats = [
        // Standard Structure
        ['title' => 'Heading 1', 'block' => 'h1'],
        ['title' => 'Heading 2', 'block' => 'h2'],
        ['title' => 'Heading 3', 'block' => 'h3'],

        // GDS Custom Styles
        [
            'title'   => 'Paragraph',
            'block'   => 'p',
            'classes' => 'govuk-body'
        ],
        [
            'title'   => 'Paragraph Small',
            'block'   => 'p',
            'classes' => 'govuk-body-s'
        ],
        [
            'title'   => 'Paragraph Extra Small',
            'block'   => 'p',
            'classes' => 'govuk-body-xs'
        ],
    ];

    $init_array['style_formats'] = json_encode($style_formats);

    return $init_array;
});

/**
 * Inject TinyMCE UI overrides into the WordPress Admin head
 */
add_action('admin_head', function() {
    ?>
    <style>
        /* Tinymce UI overrides */
        .mce-menu-item.mce-menu-item-preview.mce-active .mce-text,
        .mce-menu-item.mce-menu-item-preview.mce-active .mce-ico {
            color: inherit !important;
        }
    </style>
    <?php
});

/**
 * HERO IMAGE META (editable header/hero image, separate from featured image)
 * Stored as attachment ID in post meta: _gca_hero_image_id
 */
add_action('add_meta_boxes', function (): void {

    add_meta_box(
        'gca_hero_image',
        __('Hero header image', 'gca-intranet'),
        function ($post): void {
            $meta_key = '_gca_hero_image_id';
            $image_id = (int) get_post_meta($post->ID, $meta_key, true);

            wp_nonce_field('gca_hero_image_save', 'gca_hero_image_nonce');

            $thumb = $image_id
                ? wp_get_attachment_image($image_id, 'medium', false, ['style' => 'max-width:100%;height:auto;'])
                : '';

            echo '<p>' . esc_html__(
                'Choose an image to appear in the page header (oval). This is separate from the featured image used in the content.',
                'gca-intranet'
            ) . '</p>';

            echo '<div id="gca-hero-image-preview" style="margin:12px 0;">' . ($thumb ?: '') . '</div>';

            echo '<input type="hidden" id="gca_hero_image_id" name="gca_hero_image_id" value="' . esc_attr((string) $image_id) . '">';

            echo '<p style="display:flex;gap:8px;align-items:center;">';
            echo '<button type="button" class="button" id="gca-hero-image-select">' . esc_html__('Select image', 'gca-intranet') . '</button>';
            echo '<button type="button" class="button" id="gca-hero-image-remove" ' . ($image_id ? '' : 'disabled') . '>' . esc_html__('Remove', 'gca-intranet') . '</button>';
            echo '</p>';
        },
        ['page', 'news', 'blog', 'event', 'work_update', 'post'],
        'side',
        'default'
    );
});

add_action('save_post', function (int $post_id): void {

    if (!isset($_POST['gca_hero_image_nonce']) || !wp_verify_nonce((string) $_POST['gca_hero_image_nonce'], 'gca_hero_image_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $image_id = isset($_POST['gca_hero_image_id']) ? (int) $_POST['gca_hero_image_id'] : 0;

    if ($image_id > 0) {
        update_post_meta($post_id, '_gca_hero_image_id', $image_id);
    } else {
        delete_post_meta($post_id, '_gca_hero_image_id');
    }
});

/**
 * GI-17: Two-column layout left column WYSIWYG
 * Stored in post meta: _gca_col2_wysiwyg
 */
add_action('add_meta_boxes', function (): void {
    $screens = ['page', 'news', 'blog', 'event', 'work_update'];

    add_meta_box(
        'gca_col2_content',
        __('Layout: left column content', 'gca-intranet'),
        'gca_render_col2_metabox',
        $screens,
        'normal',
        'high'
    );
});

if (!function_exists('gca_render_col2_metabox')):
function gca_render_col2_metabox(\WP_Post $post): void
{
    wp_nonce_field('gca_save_layout_col2', 'gca_layout_col2_nonce');

    $value = (string) get_post_meta($post->ID, '_gca_col2_wysiwyg', true);

    wp_editor(
        $value,
        'gca_col2_wysiwyg_editor',
        [
            'textarea_name' => 'gca_col2_wysiwyg',
            'media_buttons' => true,
            'textarea_rows' => 10,
            'tinymce'       => true,
            'quicktags'     => true,
        ]
    );

    echo '<p class="description">' . esc_html__(
        'Only used when the "Layout – 2 column" template is selected.',
        'gca-intranet'
    ) . '</p>';
}
endif;

add_action('save_post', function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!isset($_POST['gca_layout_col2_nonce']) || !wp_verify_nonce((string) $_POST['gca_layout_col2_nonce'], 'gca_save_layout_col2')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['gca_col2_wysiwyg'])) {
        update_post_meta($post_id, '_gca_col2_wysiwyg', wp_kses_post((string) $_POST['gca_col2_wysiwyg']));
    }
});

/**
 * Featured image display toggle
 * Applies to: news, blog
 */
add_action('add_meta_boxes', function (): void {
    $screens = ['news', 'blog'];

    add_meta_box(
        'gca_featured_image_toggle',
        __('Featured image', 'gca-intranet'),
        function (\WP_Post $post): void {
            wp_nonce_field('gca_save_featured_image_toggle', 'gca_featured_image_toggle_nonce');

            $checked = get_post_meta($post->ID, '_gca_hide_featured_image', true) ? 'checked' : '';

            echo '<label style="display:flex;gap:8px;align-items:center;">';
            echo '<input type="checkbox" name="gca_hide_featured_image" value="1" ' . $checked . ' />';
            echo esc_html__('Hide featured image on page', 'gca-intranet');
            echo '</label>';

            echo '<p class="description" style="margin-top:8px;">' . esc_html__(
                'If checked, the featured image will not show on the page (regardless of template).',
                'gca-intranet'
            ) . '</p>';
        },
        $screens,
        'side',
        'default'
    );
});

add_action('save_post', function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!isset($_POST['gca_featured_image_toggle_nonce']) || !wp_verify_nonce((string) $_POST['gca_featured_image_toggle_nonce'], 'gca_save_featured_image_toggle')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) return;

    $hide = isset($_POST['gca_hide_featured_image']) ? 1 : 0;

    if ($hide) {
        update_post_meta($post_id, '_gca_hide_featured_image', 1);
    } else {
        delete_post_meta($post_id, '_gca_hide_featured_image');
    }
});

/**
 * Admin JS: hero image media picker + template switcher
 */
add_action('admin_enqueue_scripts', function (string $hook): void {

    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    wp_enqueue_media();

    $js = <<<'JS'
(function(){
  function ready(fn){ if(document.readyState!=='loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }

  function initHeroImage(){
    var selectBtn = document.getElementById('gca-hero-image-select');
    var removeBtn = document.getElementById('gca-hero-image-remove');
    var input = document.getElementById('gca_hero_image_id');
    var preview = document.getElementById('gca-hero-image-preview');
    if(!selectBtn || !input || !preview) return;

    var frame;

    selectBtn.addEventListener('click', function(e){
      e.preventDefault();
      if(frame){ frame.open(); return; }

      frame = wp.media({
        title: 'Select hero header image',
        button: { text: 'Use this image' },
        multiple: false
      });

      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        input.value = attachment.id || '';
        preview.innerHTML = (attachment.sizes && attachment.sizes.medium)
          ? '<img src="'+attachment.sizes.medium.url+'" style="max-width:100%;height:auto;" />'
          : '<img src="'+attachment.url+'" style="max-width:100%;height:auto;" />';
        if(removeBtn) removeBtn.disabled = false;
      });

      frame.open();
    });

    if(removeBtn){
      removeBtn.addEventListener('click', function(e){
        e.preventDefault();
        input.value = '';
        preview.innerHTML = '';
        removeBtn.disabled = true;
      });
    }
  }

  function getTemplateValue(){
    var sel = document.getElementById('page_template');
    if (sel) return sel.value;

    var input = document.querySelector('input[name="page_template"]');
    if (input) return input.value;

    return '';
  }

  function set2ColBoxesVisibility(){
    var template = getTemplateValue();
    var shouldShow = (template === 'template-layout-2col.php');

    var col2Box = document.getElementById('gca_col2_content');
    if (col2Box) col2Box.style.display = shouldShow ? '' : 'none';
  }

  ready(function(){
    initHeroImage();
    set2ColBoxesVisibility();

    var sel = document.getElementById('page_template');
    if (sel) {
      sel.addEventListener('change', set2ColBoxesVisibility);
    }

    setInterval(set2ColBoxesVisibility, 800);
  });
})();
JS;

    wp_add_inline_script('jquery-core', $js, 'after');
});

/**
 * Homepage options (Customizer)
 */
if (!function_exists('gca_sanitize_takealook_link_text')):
function gca_sanitize_takealook_link_text($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = trim((string) $value);

    if (function_exists('mb_substr')) {
        $value = (string) mb_substr($value, 0, 90);
    } else {
        $value = (string) substr($value, 0, 90);
    }

    return $value;
}
endif;

if (!function_exists('gca_sanitize_quicklink_text')):
function gca_sanitize_quicklink_text($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = trim((string) $value);

    $max = 48;
    if (function_exists('mb_substr')) {
        $value = (string) mb_substr($value, 0, $max);
    } else {
        $value = (string) substr($value, 0, $max);
    }

    return $value;
}
endif;

if (!function_exists('gca_sanitize_home_text')):
function gca_sanitize_home_text($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim((string) $value);
}
endif;

if (!function_exists('gca_sanitize_home_desc_40')):
function gca_sanitize_home_desc_40($value): string {
    $value = (string) $value;
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = trim((string) $value);

    $max = 40;
    if (function_exists('mb_substr')) {
        return (string) mb_substr($value, 0, $max);
    }
    return (string) substr($value, 0, $max);
}
endif;

add_action('customize_register', function (\WP_Customize_Manager $wp_customize): void {

    $section = 'gca_homepage_options';

    $wp_customize->add_section($section, [
        'title'       => __('Homepage options', 'gca-intranet'),
        'priority'    => 30,
        'description' => __('Controls for homepage components.', 'gca-intranet'),
    ]);

    $wp_customize->add_setting('gca_latestnews_title', [
        'default'           => __('Latest news', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_home_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_latestnews_title', [
        'type'     => 'text',
        'section'  => $section,
        'label'    => __('Latest news: title', 'gca-intranet'),
        'priority' => 10,
    ]);

    $wp_customize->add_setting('gca_latestnews_desc', [
        'default'           => "What's happening in our organisation",
        'sanitize_callback' => 'gca_sanitize_home_desc_40',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_latestnews_desc', [
        'type'        => 'textarea',
        'section'     => $section,
        'label'       => __('Latest news: description', 'gca-intranet'),
        'description' => __('Text shown under the "Latest news" heading on the homepage. Max 40 characters (longer text is truncated).', 'gca-intranet'),
        'input_attrs' => ['maxlength' => 40, 'rows' => 2],
        'priority'    => 20,
    ]);

    $wp_customize->add_setting('gca_takealook_enabled', [
        'default'           => true,
        'sanitize_callback' => static fn ($v) => (bool) $v,
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_takealook_enabled', [
        'type'     => 'checkbox',
        'section'  => $section,
        'label'    => __('Show "Take a look" block', 'gca-intranet'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('gca_takealook_title', [
        'default'           => __('Take a look', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_home_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_takealook_title', [
        'type'     => 'text',
        'section'  => $section,
        'label'    => __('Take a look: title', 'gca-intranet'),
        'priority' => 40,
    ]);

    $wp_customize->add_setting('gca_takealook_desc', [
        'default'           => '',
        'sanitize_callback' => 'gca_sanitize_home_desc_40',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_takealook_desc', [
        'type'        => 'textarea',
        'section'     => $section,
        'label'       => __('Take a look: description', 'gca-intranet'),
        'description' => __('Optional text shown under the title. Max 40 characters (longer text is truncated).', 'gca-intranet'),
        'input_attrs' => ['maxlength' => 40, 'rows' => 2],
        'priority'    => 50,
    ]);

    $wp_customize->add_setting('gca_takealook_link_text', [
        'default'           => __('Learn more', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_takealook_link_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_takealook_link_text', [
        'type'        => 'textarea',
        'section'     => $section,
        'label'       => __('Take a look: link text', 'gca-intranet'),
        'description' => __('Plain text only. Max 90 characters. No images or HTML.', 'gca-intranet'),
        'input_attrs' => ['maxlength' => 90, 'rows' => 3],
        'priority'    => 60,
    ]);

    $wp_customize->add_setting('gca_takealook_link_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_takealook_link_url', [
        'type'        => 'url',
        'section'     => $section,
        'label'       => __('Take a look: link URL', 'gca-intranet'),
        'description' => __('If empty, the block renders as "not configured".', 'gca-intranet'),
        'priority'    => 70,
    ]);

    $wp_customize->add_setting('gca_quicklinks_enabled', [
        'default'           => true,
        'sanitize_callback' => static fn ($v) => (bool) $v,
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_quicklinks_enabled', [
        'type'     => 'checkbox',
        'section'  => $section,
        'label'    => __('Show "Quick links" block', 'gca-intranet'),
        'priority' => 80,
    ]);

    $wp_customize->add_setting('gca_quicklinks_title', [
        'default'           => __('Quick links', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_home_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_quicklinks_title', [
        'type'     => 'text',
        'section'  => $section,
        'label'    => __('Quick links: title', 'gca-intranet'),
        'priority' => 90,
    ]);

    $wp_customize->add_setting('gca_quicklinks_desc', [
        'default'           => '',
        'sanitize_callback' => 'gca_sanitize_home_desc_40',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_quicklinks_desc', [
        'type'        => 'textarea',
        'section'     => $section,
        'label'       => __('Quick links: description', 'gca-intranet'),
        'description' => __('Optional text shown under the title. Max 40 characters (longer text is truncated).', 'gca-intranet'),
        'input_attrs' => ['maxlength' => 40, 'rows' => 2],
        'priority'    => 100,
    ]);

    $priority = 110;
    for ($i = 1; $i <= 3; $i++) {

        $wp_customize->add_setting("gca_quicklinks_{$i}_text", [
            'default'           => '',
            'sanitize_callback' => 'gca_sanitize_quicklink_text',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control("gca_quicklinks_{$i}_text", [
            'type'        => 'text',
            'section'     => $section,
            'label'       => sprintf(__('Quick link %d: text', 'gca-intranet'), $i),
            'description' => __('Plain text only.', 'gca-intranet'),
            'input_attrs' => ['maxlength' => 48],
            'priority'    => $priority,
        ]);

        $priority += 10;

        $wp_customize->add_setting("gca_quicklinks_{$i}_url", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control("gca_quicklinks_{$i}_url", [
            'type'        => 'url',
            'section'     => $section,
            'label'       => sprintf(__('Quick link %d: URL', 'gca-intranet'), $i),
            'description' => __('Full URL (e.g. https://…).', 'gca-intranet'),
            'priority'    => $priority,
        ]);

        $priority += 10;
    }

    $wp_customize->add_setting('gca_workupdates_title', [
        'default'           => __('Work updates', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_home_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_workupdates_title', [
        'type'     => 'text',
        'section'  => $section,
        'label'    => __('Work updates: title', 'gca-intranet'),
        'priority' => 170,
    ]);

    $wp_customize->add_setting('gca_workupdates_desc', [
        'default'           => '',
        'sanitize_callback' => 'gca_sanitize_home_desc_40',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_workupdates_desc', [
        'type'        => 'textarea',
        'section'     => $section,
        'label'       => __('Work updates: description', 'gca-intranet'),
        'description' => __('Text shown under the "Work updates" heading on the homepage. Max 40 characters (longer text is truncated).', 'gca-intranet'),
        'input_attrs' => ['maxlength' => 40, 'rows' => 2],
        'priority'    => 180,
    ]);

    $wp_customize->add_setting('gca_blogs_title', [
        'default'           => __('Blogs', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_home_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_blogs_title', [
        'type'     => 'text',
        'section'  => $section,
        'label'    => __('Blogs: title', 'gca-intranet'),
        'priority' => 190,
    ]);

    $wp_customize->add_setting('gca_blogs_desc', [
        'default'           => '',
        'sanitize_callback' => 'gca_sanitize_home_desc_40',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_blogs_desc', [
        'type'        => 'textarea',
        'section'     => $section,
        'label'       => __('Blogs: description', 'gca-intranet'),
        'description' => __('Text shown under the "Blogs" heading on the homepage. Max 40 characters (longer text is truncated).', 'gca-intranet'),
        'input_attrs' => ['maxlength' => 40, 'rows' => 2],
        'priority'    => 200,
    ]);

    $wp_customize->add_setting('gca_events_title', [
        'default'           => __('Events', 'gca-intranet'),
        'sanitize_callback' => 'gca_sanitize_home_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gca_events_title', [
        'type'     => 'text',
        'section'  => $section,
        'label'    => __('Events: title', 'gca-intranet'),
        'priority' => 210,
    ]);
});

add_action('customize_controls_enqueue_scripts', function (): void {
    $js = <<<'JS'
(function(){
  function addCounter(controlId, maxDefault){
    var control = document.getElementById(controlId);
    if(!control) return;

    var input = control.querySelector('textarea, input[type="text"]');
    if(!input) return;

    var max = parseInt(input.getAttribute('maxlength') || String(maxDefault || 0), 10);
    if(!max) return;

    var counter = document.createElement('div');
    counter.style.marginTop = '6px';
    counter.style.fontSize = '12px';
    counter.style.opacity = '0.85';

    function update(){
      var val = input.value || '';
      var len = val.length;

      if(len > max){
        input.value = val.substring(0, max);
        len = max;
      }

      counter.textContent = (max - len) + ' characters remaining';
    }

    input.parentNode.appendChild(counter);
    input.addEventListener('input', update);
    update();
  }

  function init(){
    addCounter('customize-control-gca_takealook_link_text', 90);
    addCounter('customize-control-gca_quicklinks_1_text', 48);
    addCounter('customize-control-gca_quicklinks_2_text', 48);
    addCounter('customize-control-gca_quicklinks_3_text', 48);
    addCounter('customize-control-gca_latestnews_desc', 40);
    addCounter('customize-control-gca_takealook_desc', 40);
    addCounter('customize-control-gca_quicklinks_desc', 40);
    addCounter('customize-control-gca_workupdates_desc', 40);
    addCounter('customize-control-gca_blogs_desc', 40);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
JS;

    wp_add_inline_script('customize-controls', $js, 'after');
});

add_action('admin_menu', function (): void {
    $url = add_query_arg(
        ['autofocus[section]' => 'gca_homepage_options'],
        admin_url('customize.php')
    );

    add_theme_page(
        __('Homepage options', 'gca-intranet'),
        __('Homepage options', 'gca-intranet'),
        'edit_theme_options',
        $url
    );
});

if (defined('WP_CLI') && WP_CLI) {

    if (!class_exists('GCA_Homepage_CLI_Command')) {
        class GCA_Homepage_CLI_Command {

            public function seed(array $args, array $assoc_args): void {
                $component = isset($assoc_args['component']) ? strtolower((string) $assoc_args['component']) : 'all';

                if (!in_array($component, ['all', 'takealook', 'quicklinks'], true)) {
                    \WP_CLI::error('Invalid --component value. Use: takealook, quicklinks, or all.');
                }

                if ($component === 'all' || $component === 'takealook') {
                    set_theme_mod('gca_takealook_enabled', true);
                    set_theme_mod('gca_takealook_title', 'Take a look');
                    set_theme_mod('gca_takealook_desc', 'Useful featured link');
                    set_theme_mod('gca_takealook_link_text', 'Read the latest guidance');
                    set_theme_mod('gca_takealook_link_url', 'https://www.gov.uk/');
                    \WP_CLI::log('Seeded Take a look test data.');
                }

                if ($component === 'all' || $component === 'quicklinks') {
                    set_theme_mod('gca_quicklinks_enabled', true);
                    set_theme_mod('gca_quicklinks_title', 'Quick links');
                    set_theme_mod('gca_quicklinks_desc', 'Useful shortcuts');
                    set_theme_mod('gca_quicklinks_1_text', 'Policies');
                    set_theme_mod('gca_quicklinks_1_url', 'https://www.gov.uk/government/organisations/crown-commercial-service');
                    set_theme_mod('gca_quicklinks_2_text', 'Guidance');
                    set_theme_mod('gca_quicklinks_2_url', 'https://www.gov.uk/guidance');
                    set_theme_mod('gca_quicklinks_3_text', 'Contact us');
                    set_theme_mod('gca_quicklinks_3_url', 'https://www.gov.uk/contact');
                    \WP_CLI::log('Seeded Quick links test data.');
                }

                \WP_CLI::success('Homepage test data seeded.');
            }

            public function disable(array $args, array $assoc_args): void {
                $component = isset($assoc_args['component']) ? strtolower((string) $assoc_args['component']) : '';

                if (!in_array($component, ['takealook', 'quicklinks'], true)) {
                    \WP_CLI::error('Invalid --component value. Use: takealook or quicklinks.');
                }

                if ($component === 'takealook') {
                    set_theme_mod('gca_takealook_enabled', false);
                    \WP_CLI::success('Take a look disabled.');
                }

                if ($component === 'quicklinks') {
                    set_theme_mod('gca_quicklinks_enabled', false);
                    \WP_CLI::success('Quick links disabled.');
                }
            }

            public function reset(array $args, array $assoc_args): void {
                remove_theme_mod('gca_takealook_enabled');
                remove_theme_mod('gca_takealook_title');
                remove_theme_mod('gca_takealook_desc');
                remove_theme_mod('gca_takealook_link_text');
                remove_theme_mod('gca_takealook_link_url');
                remove_theme_mod('gca_quicklinks_enabled');
                remove_theme_mod('gca_quicklinks_title');
                remove_theme_mod('gca_quicklinks_desc');
                remove_theme_mod('gca_quicklinks_1_text');
                remove_theme_mod('gca_quicklinks_1_url');
                remove_theme_mod('gca_quicklinks_2_text');
                remove_theme_mod('gca_quicklinks_2_url');
                remove_theme_mod('gca_quicklinks_3_text');
                remove_theme_mod('gca_quicklinks_3_url');
                \WP_CLI::success('Homepage test data reset.');
            }
        }
    }

    \WP_CLI::add_command('gca homepage', 'GCA_Homepage_CLI_Command');
}

add_action('acf/input/admin_footer', function() {
?>
<script type="text/javascript">
(function($) {
    if (typeof acf !== 'undefined') {
        acf.addAction('ready_field/type=time_picker', function(field) {

            var $input = field.$el.find('input[type="text"]');

            $input.attr('readonly', 'readonly').css('cursor', 'pointer');

            if (!field.$el.find('.gca-clear-time').length) {
                $input.after('<a href="#" class="gca-clear-time" style="margin-left:10px; color:#cc0000; text-decoration:none; font-size:12px; cursor:pointer;">Clear</a>');
            }

            field.$el.on('click', '.gca-clear-time', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $input.val('');
                field.$el.find('input[type="hidden"]').val('');
                field.val('');
                $input.datepicker('hide');
                $input.blur();
                $(this).hide();
            });

            $input.on('focus change keyup', function() {
                field.$el.find('.gca-clear-time').toggle(!!$(this).val());
            }).trigger('change');
        });
    }
})(jQuery);
</script>
<?php
});

if (!function_exists('gca_format_event_time')):
function gca_format_event_time( $raw_time ) {
    if ( ! $raw_time ) return '';
    $ts = strtotime( $raw_time );
    return date( 'i', $ts ) === '00' ? date( 'ga', $ts ) : date( 'g:ia', $ts );
}
endif;

if (!function_exists('gca_get_event_datetime')):
function gca_get_event_datetime( $return = 'dates', $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();

    $raw_start_date = get_field('start_date', $post_id);
    $raw_start_time = get_field('start_time', $post_id);
    $raw_end_date   = get_field('end_date', $post_id);
    $raw_end_time   = get_field('end_time', $post_id);

    if ( ! $raw_start_date && in_array($return, ['dates', 'start_date', 'end_date', 'all'])) {
        return '';
    }

    $f_start_date = $raw_start_date ? date('j F Y', strtotime($raw_start_date)) : '';
    $f_end_date   = $raw_end_date   ? date('j F Y', strtotime($raw_end_date))   : '';
    $f_start_time = gca_format_event_time( $raw_start_time );
    $f_end_time   = gca_format_event_time( $raw_end_time );

    $time_range = '';
    if ($f_start_time && $f_end_time) {
        $time_range = "{$f_start_time} to {$f_end_time}";
    } elseif ($f_start_time) {
        $time_range = $f_start_time;
    } elseif ($f_end_time) {
        $time_range = "Until {$f_end_time}";
    }

    $date_range = $f_start_date;
    if ($f_end_date && $raw_start_date !== $raw_end_date) {
        $date_range .= " to {$f_end_date}";
    }

    switch ( $return ) {
        case 'start_date': return $f_start_date;
        case 'end_date':   return $f_end_date;
        case 'start_time': return $f_start_time;
        case 'end_time':   return $f_end_time;
        case 'times':      return $time_range;
        case 'all':        return $time_range ? "{$date_range} {$time_range}" : $date_range;
        case 'dates':
        default:           return $date_range;
    }
}
endif;

add_filter('theme_page_templates', function($post_templates, $theme, $post, $post_type) {
    $target_file = 'template-layout-1col.php';

    foreach ( $post_templates as $file => $name ) {
        if ( $file === $target_file || substr($file, -strlen($target_file)) === $target_file ) {
            unset($post_templates[$file]);
        }
    }

    return $post_templates;
}, 9999, 4);

if (!function_exists('gca_show_all_screen_options')):
function gca_show_all_screen_options($hidden, $screen) {
    if (isset($screen->base) && $screen->base === 'post') {
        return [];
    }

    return $hidden;
}
endif;
add_filter('default_hidden_meta_boxes', 'gca_show_all_screen_options', 10, 2);