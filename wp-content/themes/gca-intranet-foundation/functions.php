<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/class-ccs-mega-menu-walker.php';
require_once get_template_directory() . '/inc/shortcodes.php';

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
    // We attach this as an inline script to the 'gca-theme' handle
    wp_add_inline_script('gca-theme', '
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

        // Mobile Dropdown logic
        document.querySelectorAll(".dropdown > .dropbtn").forEach(function(button) {
            button.addEventListener("click", function(e) {
                if (window.innerWidth < 1024) {
                    e.preventDefault();
                    var dropdownContent = this.nextElementSibling;
                    if (dropdownContent && dropdownContent.classList.contains("dropdown-content")) {
                        dropdownContent.classList.toggle("open-dropdown");
                    }
                }
            });
        });
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

/**
 * Limit search results to 10 per page.
 */
add_action('pre_get_posts', function (WP_Query $query): void {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $query->set('posts_per_page', 10);
    }
});

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
});

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