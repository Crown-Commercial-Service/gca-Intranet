<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Updates module
 *
 * @since 6.9.5
 */
class Disable_Updates {

    /**
     * Disable updates and related functionalities
     *
     * @since 4.0.0
     */
    public function disable_update_notices_version_checks() {

        // Remove nags
        remove_action( 'admin_notices', 'update_nag', 3 );
        remove_action( 'admin_notices', 'maintenance_nag' );

        // Disable WP version check
        remove_action( 'wp_version_check', 'wp_version_check' );
        remove_action( 'admin_init', 'wp_version_check' );
        wp_clear_scheduled_hook( 'wp_version_check' );

        add_filter( 'pre_option_update_core', '__return_null' );

        // Disable theme version checks
        remove_action( 'wp_update_themes', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        wp_clear_scheduled_hook( 'wp_update_themes' );

        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'load-update-core.php', 'wp_update_themes' );

        // Disable plugin version checks
        remove_action( 'wp_update_plugins', 'wp_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
        wp_clear_scheduled_hook( 'wp_update_plugins' );

        remove_action( 'load-plugins.php', 'wp_update_plugins' );
        remove_action( 'load-update.php', 'wp_update_plugins' );
        remove_action( 'load-update-core.php', 'wp_update_plugins' );

        // Disable auto updates
        wp_clear_scheduled_hook( 'wp_maybe_auto_update' );

        remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
        remove_action( 'admin_init', 'wp_maybe_auto_update' );
        remove_action( 'admin_init', 'wp_auto_update_core' );

        // Disable Site Health checks
        add_filter( 'site_status_tests', [ $this, 'disable_update_checks_in_site_health' ] );

    }

    /**
     * Override version check info stored in transients named update_core, update_plugins, update_themes.
     *
     * @since 4.0.0
     */
    public function override_version_check_info() {

        include( ABSPATH . WPINC . '/version.php' ); // get $wp_version from here

        $current = (object)array(); // create empty object
        $current->updates = array();
        $current->response = array();
        $current->version_checked = $wp_version;
        $current->last_checked = time();

        return $current;

    }

    /**
     * Disable Background Updates and Auto-Updates tests in Site Health tests
     *
     * @since 4.0.0
     */
    public function disable_update_checks_in_site_health( $tests ) {

        unset( $tests['async']['background_updates'] );
        unset( $tests['direct']['plugin_theme_auto_updates'] );

        return $tests;

    }

    /**
     * Remove Dashboard >> Updates menu item
     *
     * @since 4.0.0
     */
    public function remove_updates_menu() {
        global $submenu;
        remove_submenu_page( 'index.php', 'update-core.php' );
    }
    
}