<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/class-ccs-mega-menu-walker.php';

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

    // ==========================================
    // ENABLE GOV.UK & CCS FRONTEND
    // ==========================================

    // Load GOV.UK JS
    wp_enqueue_script(
        'govuk-frontend-js',
        get_template_directory_uri() . '/assets/scripts/govuk.min.js',
        [],
        '5.4.0',
        true
    );

    // Load CCS JS (It depends on govuk-frontend-js)
    wp_enqueue_script(
        'ccs-frontend-js',
        get_template_directory_uri() . '/assets/scripts/ccs.min.js',
        ['govuk-frontend-js'],
        '2.5.0',
        true
    );

    // Initialize the components safely and add mobile dropdown logic
    wp_add_inline_script('ccs-frontend-js', '
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Initialize GDS/CCS Native Scripts
        if (window.GOVUKFrontend && window.GOVUKFrontend.initAll) {
            window.GOVUKFrontend.initAll();
        }
        if (window.CCSFrontend && window.CCSFrontend.initAll) {
            window.CCSFrontend.initAll();
        }

        // 2. Custom Mobile Dropdown Toggle
        document.querySelectorAll(".dropdown > .dropbtn").forEach(function(button) {
            button.addEventListener("click", function(e) {
                // Only intercept the click on mobile/tablet widths (below 1024px)
                if (window.innerWidth < 1024) {
                    e.preventDefault(); // Stop the link from navigating away

                    // Find the sub-menu right next to the button
                    var dropdownContent = this.nextElementSibling;

                    if (dropdownContent && dropdownContent.classList.contains("dropdown-content")) {
                        // Toggle the visibility class from your SCSS
                        dropdownContent.classList.toggle("open-dropdown");

                        // Optional: Flip the arrow icon upside down
                        var arrow = this.querySelector(".arrow");
                        if (arrow) {
                            arrow.classList.toggle("up");
                            arrow.classList.toggle("down");
                        }
                    }
                }
            });
        });

        // 3. Main Mobile Menu Toggle (Header)
        var toggleBtn = document.querySelector(".global-navigation__toggler");
        var navContainer = document.getElementById("primaryNav");

        if (toggleBtn && navContainer) {
            // Click handler
            toggleBtn.addEventListener("click", function() {
                var isExpanded = this.getAttribute("aria-expanded") === "true";
                this.setAttribute("aria-expanded", !isExpanded);
                navContainer.setAttribute("aria-hidden", isExpanded ? "true" : "false");
            });

            // Escape key handler for GDS accessibility compliance
            document.addEventListener("keydown", function(event) {
                if (event.key === "Escape" && toggleBtn.getAttribute("aria-expanded") === "true") {
                    toggleBtn.setAttribute("aria-expanded", "false");
                    navContainer.setAttribute("aria-hidden", "true");
                    toggleBtn.focus(); // Return focus to the button
                }
            });
        }
    });
', 'after');
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