<?php

namespace ASENHA\Classes;

/**
 * Class for Display System Summary module
 *
 * @since 6.9.5
 */
class Display_System_Summary {
    /**
     * Display system summary in the "At a Glance" dashboard widget
     * 
     * @since 5.6.0
     */
    public function display_system_summary() {
        // When user is logged-in as in an administrator
        if ( is_user_logged_in() ) {
            if ( current_user_can( 'manage_options' ) ) {
                if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
                    $server_software_raw = str_replace( "/", " ", $_SERVER['SERVER_SOFTWARE'] );
                    $server_software_parts = explode( " (", $server_software_raw );
                    $server_software = ucfirst( $server_software_parts[0] );
                } else {
                    $server_software = 'Unknown';
                }
                $php_version = phpversion();
                // From WP core /wp-admin/includes/class-wp-debug-data.php
                global $wpdb;
                $db_server = $wpdb->get_var( 'SELECT VERSION()' );
                $db_server_parts = explode( ':', $db_server );
                $db_server = $db_server_parts[0];
                $db_separator = '&9670;';
                $ip = 'localhost';
                if ( isset( $_SERVER['HTTP_X_SERVER_ADDR'] ) ) {
                    $ip = sanitize_text_field( $_SERVER['HTTP_X_SERVER_ADDR'] );
                } elseif ( isset( $_SERVER['SERVER_ADDR'] ) ) {
                    $ip = sanitize_text_field( $_SERVER['SERVER_ADDR'] );
                } else {
                }
                echo '<div class="system-summary"><a href="' . esc_url( admin_url( 'site-health.php?tab=debug' ) ) . '">System</a>: ' . esc_html( $server_software ) . ' &#9642; PHP ' . esc_html( $php_version ) . ' (' . esc_html( php_sapi_name() ) . ') &#9642;' . esc_html( $db_server ) . ' &#9642; IP: ' . esc_html( $ip ) . '</div>';
            }
        }
    }

}
