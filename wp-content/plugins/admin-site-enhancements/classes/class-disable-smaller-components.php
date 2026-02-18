<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Smaller Components module
 *
 * @since 6.9.5
 */
class Disable_Smaller_Components {

    /**
     * Remove version number from URLs of static resources (CSS, JS)
     * 
     * @since 5.8.0
     */
    public function remove_resource_version_number( $src ) {
        if ( ! is_user_logged_in() ) {
            // https://wordpress.org/support/topic/disable-smaller-components-version-can-be-hidden/
            if ( strpos( $src, 'ver=' ) ) {
                $src = remove_query_arg( 'ver', $src );
            }
        }
        return $src;
    }
    
    /** 
     * Remove generator tag from RSS feed
     * 
     * @since 7.3.3
     */
    public function remove_feed_generator_tag( $generator_type, $type ) {
        // e.g. <generator>https://wordpress.org/?v=6.6.1</generator>
        if ( false !== strpos( $generator_type, '<generator>https://wordpress.org/?v=' ) ) {
            return '';            
        }
    }

    /**
     * Disable loading of frontend public assets of dashicons
     *
     * @since 4.5.0
     */
    public function disable_dashicons_public_assets() {
        global $pagenow;
        if ( ! is_user_logged_in() ) {

            // This will get /path/file.php?param=val portion of the full URL
            $current_request_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );
            
            if ( empty( $current_request_uri ) ) {
                // On the homepage
                wp_dequeue_style( 'dashicons' );
                wp_deregister_style( 'dashicons' );
            } else {
                // Exclude the login page, where dashicon assets are requred to properly style the page             
                if ( false !== strpos( $current_request_uri, 'wp-login.php' ) || 'wp-login.php' === $pagenow ) {
                    // On wp-login.php, so, do nothing
                }
                // Exclude password protection form 
                elseif ( false !== strpos( $current_request_uri, 'protected-page=view' ) ) {
                    // On protected-page=view, so, do nothing                   
                } 
                else {
                    // NOT on wp-login.php, e.g. www.example.com/an-article/, so, dequeue dashicons
                    wp_dequeue_style( 'dashicons' );
                    wp_deregister_style( 'dashicons' );
                }
            }
        }
    }

    /**
     * Disable emoji support
     *
     * @since 4.5.0
     */
    public function disable_emoji_support() {

        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'embed_head', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );  
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        add_action( 'admin_init', [ $this, 'disable_admin_emojis' ] );
        add_filter( 'emoji_svg_url', '__return_false' );
        add_filter( 'tiny_mce_plugins', [ $this, 'disable_emoji_for_tinymce' ] );
        add_filter( 'wp_resource_hints', [ $this, 'disable_emoji_remove_dns_prefetch' ], 10, 2 );
        add_filter( 'option_use_smilies', '__return_false' );
        
    }
    
    /** 
     * Disable jQuery Migrate
     * 
     * @since 5.8.0
     * @link https://plugins.trac.wordpress.org/browser/remove-jquery-migrate/trunk/remove-jquery-migrate.php
     * @param WP_Scripts $scripts WP_Scripts object.
     */
    public function disable_jquery_migrate( $scripts ) {
        if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
            
            if ( ! empty( $script->deps ) ) { // Check whether the script has any dependencies
                $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
            }
        }
    }

    /**
     * Remove the tinymce emoji plugin
     * 
     * @since 4.5.0
     */
    public function disable_emoji_for_tinymce( $plugins ) {

        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        }

        return array();

    }

    /**
     * Remove emoji CDN hostname from DNS prefetching hints.
     *
     * @since 4.5.0
     */
    public function disable_emoji_remove_dns_prefetch( $urls, $relation_type ) {

        if ( 'dns-prefetch' == $relation_type ) {

            // Strip out any URLs referencing the WordPress.org emoji location
            $emoji_svg_url_base = 'https://s.w.org/images/core/emoji/';
            foreach ( $urls as $key => $url ) {
                if ( is_string( $url ) && false !== strpos( $url, $emoji_svg_url_base ) ) {
                    unset( $urls[$key] );
                }
            }

        }

        return $urls;

    }

    /** 
     * Disable emojis in wp-admin
     *
     * @since 4.7.2
     */
    public function disable_admin_emojis() {
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
    }
    
    /**
     * Add loading="eager" attribute for featured images
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-lazy-loading/tags/2.1/disable-lazy-loading.php
     * @since 7.3.0
     */
    public function eager_load_featured_images( $attr, $attachment = null ) {
        $attr['loading'] = 'eager';
        return $attr;
    }
    
    /**
     * Disable plugin and theme editor
     * 
     * @since 7.4.5
     */
    public function disable_plugin_theme_editor() {
        if ( wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wp_config = new WP_Config_Transformer;

        if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
            define( 'DISALLOW_FILE_EDIT', true );
        } else {
            $is_wpconfig_writeable = $wp_config->wpconfig_file( 'writeability' );
            
            if ( $is_wpconfig_writeable ) {
                $disallow_file_edit = $wp_config->get_value( 'constant', 'DISALLOW_FILE_EDIT' );

                if ( 'false' == $disallow_file_edit ) {
                    $wp_config_options = array(
                        'add'       => true, // Add the config if missing.
                        'raw'       => true, // Display value in raw format without quotes.
                        'normalize' => false, // Normalize config output using WP Coding Standards.
                    );

                    $update_success = $wp_config->update( 'constant', 'DISALLOW_FILE_EDIT', 'true', $wp_config_options );                    
                }
            }
        }
    }

    /**
     * Enable plugin and theme editor
     * 
     * @since 7.4.5
     */
    public function enable_plugin_theme_editor() {
        if ( wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $wp_config = new WP_Config_Transformer;

        if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
            define( 'DISALLOW_FILE_EDIT', false );            
        } else {
            $is_wpconfig_writeable = $wp_config->wpconfig_file( 'writeability' );
            
            if ( $is_wpconfig_writeable ) {
                $disallow_file_edit = $wp_config->get_value( 'constant', 'DISALLOW_FILE_EDIT' );

                if ( 'true' == $disallow_file_edit ) {
                    $wp_config_options = array(
                        'add'       => true, // Add the config if missing.
                        'raw'       => true, // Display value in raw format without quotes.
                        'normalize' => false, // Normalize config output using WP Coding Standards.
                    );

                    $update_success = $wp_config->update( 'constant', 'DISALLOW_FILE_EDIT', 'false', $wp_config_options );
                }
            }
        }
    }    
}