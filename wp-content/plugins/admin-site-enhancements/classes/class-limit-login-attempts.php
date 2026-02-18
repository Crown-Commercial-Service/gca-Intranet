<?php

namespace ASENHA\Classes;

use WP_Error;

/**
 * Class for Limit Login Attempts module
 *
 * @since 6.9.5
 */
class Limit_Login_Attempts {

    /**
     * Maybe allow login if not locked out. Should return WP_Error object if not allowed to login.
     *
     * @since 2.5.0
     */
    public function maybe_allow_login( $user_or_error, $username, $password ) {
        global $wpdb, $asenha_limit_login;

        $table_name = $wpdb->prefix . 'asenha_failed_logins';

        // Maybe create table if it does not exist yet, e.g. upgraded from previous version of plugin, so, no activation methods are fired
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

        if ( $wpdb->get_var( $query ) === $table_name ) {
            // Table already exists, do nothing.
        } else {
            $activation = new Activation;
            $activation->create_failed_logins_log_table();
        }

        // Get values from options needed to do various checks
        $options = get_option( ASENHA_SLUG_U, array() );
        $login_fails_allowed = $options['login_fails_allowed'];
        $login_lockout_maxcount = $options['login_lockout_maxcount'];

        $ip_address_whitelist_raw = ( isset( $options['limit_login_attempts_ip_whitelist'] ) ) ? explode( PHP_EOL, $options['limit_login_attempts_ip_whitelist'] ) : array();
        $ip_address_whitelist = array();
        if ( ! empty( $ip_address_whitelist_raw ) ) {
            foreach( $ip_address_whitelist_raw as $ip_address ) {
                $ip_address_whitelist[] = trim( $ip_address );
            }           
        }

        $change_login_url = $options['change_login_url'];
        $custom_login_slug = $options['custom_login_slug'];

        // Instantiate object to access common methods
        $common_methods = new Common_Methods;

        // Get user/visitor IP address
        $ip_address = $common_methods->get_user_ip_address( 'ip', 'limit-login-attempts' );
        
        if ( ! in_array( $ip_address, $ip_address_whitelist ) ) { // IP is not whitelisted
            // Check if IP address has failed login attempts recorded in the DB log
            $sql = $wpdb->prepare("SELECT * FROM `" . $table_name . "` Where `ip_address` = %s", $ip_address);
            $result = $wpdb->get_results( $sql, ARRAY_A );

            $result_count = count( $result );

            if ( $result_count > 0 ) { // IP address has been recorded in the database.
                $fail_count = $result[0]['fail_count'];
                $lockout_count = $result[0]['lockout_count'];
                $last_fail_on = $result[0]['unixtime'];
            } else {
                $fail_count = 0;
                $lockout_count = 0;
                $last_fail_on = '';
            }
        } else { // IP is whitelisted
            $result = array();
            $result_count = 0;
            $fail_count = 0;
            $lockout_count = 0;
            $last_fail_on = '';
        }
        
        // Initialize the global variable
        $asenha_limit_login = array (
            'ip_address'                => $ip_address,
            'request_uri'               => sanitize_text_field( $_SERVER['REQUEST_URI'] ),
            'ip_address_log'            => $result,
            'fail_count'                => $fail_count,
            'lockout_count'             => $lockout_count,
            'maybe_lockout'             => false,
            'extended_lockout'          => false,
            'within_lockout_period'     => false,
            'lockout_period'            => 0,
            'lockout_period_remaining'  => 0,
            'login_fails_allowed'       => $login_fails_allowed,
            'login_lockout_maxcount'    => $login_lockout_maxcount,
            // 'default_lockout_period'     => 15, // 15 seconds. FOR TESTING.
            // 'default_lockout_period'     => 60, // 1 minutes in seconds
            'default_lockout_period'    => 60*15, // 15 minutes in seconds
            // 'extended_lockout_period'    => 3*60, // 3 minutes in seconds
            'extended_lockout_period'   => 24*60*60, // 24 hours in seconds
            'change_login_url'          => $change_login_url, // is custom login URL enabled?
            'custom_login_slug'         => $custom_login_slug,
        );

        if ( ! in_array( $ip_address, $ip_address_whitelist ) ) { // IP is not whitelisted

            if ( $result_count > 0 ) { // IP address has been recorded in the database.

                // Failed attempts have been recorded and fulfills lockout condition
                if ( ! empty( $fail_count ) && ( ( $fail_count ) % $login_fails_allowed == 0 ) ) {

                    $asenha_limit_login['maybe_lockout'] = true;

                    // Has reached max / gone beyond number of lockouts allowed?
                    if ( $lockout_count >= $login_lockout_maxcount ) {
                        $asenha_limit_login['extended_lockout'] = true;
                        $lockout_period = $asenha_limit_login['extended_lockout_period'];
                    } else {
                        $asenha_limit_login['extended_lockout'] = false;
                        $lockout_period = $asenha_limit_login['default_lockout_period'];
                    }

                    $asenha_limit_login['lockout_period'] = $lockout_period;

                    // User/visitor is still within the lockout period
                    if ( ( time() - $last_fail_on ) <= $asenha_limit_login['lockout_period'] ) {

                        $asenha_limit_login['within_lockout_period'] = true;
                        $asenha_limit_login['lockout_period_remaining'] = $asenha_limit_login['lockout_period'] - ( time() - $last_fail_on );

                        if ( $asenha_limit_login['lockout_period_remaining'] <= 60 ) {

                            // Get remaining lockout period in minutes and seconds
                            $lockout_period_remaining = $asenha_limit_login['lockout_period_remaining'] . ' seconds';

                        } elseif ( $asenha_limit_login['lockout_period_remaining'] <= 60*60 ) {

                            // Get remaining lockout period in minutes and seconds
                            $lockout_period_remaining = $common_methods->seconds_to_period( $asenha_limit_login['lockout_period_remaining'], 'to-minutes-seconds' );

                        } elseif ( $asenha_limit_login['lockout_period_remaining'] > 60*60 && $asenha_limit_login['lockout_period_remaining'] <= 24*60*60 ) {

                            // Get remaining lockout period in minutes and seconds
                            $lockout_period_remaining = $common_methods->seconds_to_period( $asenha_limit_login['lockout_period_remaining'], 'to-hours-minutes-seconds' );

                        } elseif ( $asenha_limit_login['lockout_period_remaining'] > 24*60*60 ) {

                            // Get remaining lockout period in minutes and seconds
                            $lockout_period_remaining = $common_methods->seconds_to_period( $asenha_limit_login['lockout_period_remaining'], 'to-days-hours-minutes-seconds' );

                        }

                        $error = new WP_Error( 'ip_address_blocked', '<b>WARNING:</b> You\'ve been locked out. You can login again in ' . $lockout_period_remaining . '.' );

                        return $error;

                    } else { // User/visitor is no longer within the lockout period

                        $asenha_limit_login['within_lockout_period'] = false;

                        if ( $lockout_count == $login_lockout_maxcount ) {

                            // Remove the DB log entry for the current IP address. i.e. release from extended lockout

                            $where = array( 'ip_address' => $ip_address );
                            $where_format = array( '%s' );

                            // Delete existing data in the database
                            $wpdb->delete(
                                $table_name,
                                $where,
                                $where_format
                            );

                        }

                        return $user_or_error;

                    }

                } else {

                    $asenha_limit_login['maybe_lockout'] = false;

                    return $user_or_error;

                }

            } else { // IP address has not been recorded in the database.

                return $user_or_error;

            }
            
        } else {  // IP is whitelisted
            return $user_or_error;          
        }
    }

    /**
     * Handle login errors
     *
     * @link https://developer.wordpress.org/reference/classes/wp_error/#methods
     * @since 2.5.0
     */
    public function login_error_handler( $errors, $redirect_to ) {
        global $asenha_limit_login;
        
        if ( is_wp_error( $errors ) ) {

            $error_codes = $errors->get_error_codes();

            foreach ( $error_codes as $error_code ) {

                if ( $error_code == 'invalid_username' || $error_code == 'incorrect_password' ) {

                    // Remove default error messages that may give out valueable info to hackers

                    $errors->remove( 'invalid_username' ); // Outputs info that says username does not exist. May encourage login attempt with a different username instead.

                    $errors->remove( 'incorrect_password' ); // Outputs info that implies username exist. May encourage login attempt with a different password.

                    // Add a new error message that does not provide useful clues to hackers
                    $errors->add( 'invalid_username_or_incorrect_password', '<b>' . __( 'Error:', 'admin-site-enhancements' ) . '</b> ' . __( 'Invalid username/email or incorrect password.', 'admin-site-enhancements' ) );

                    // $errors->add( 'another_error_code', 'The error message.' );

                }

            }

        }

        return $errors;
    }

    /**
     * Disable login form inputs via CSS
     * 
     * @since 2.5.0
     */
    public function maybe_hide_login_form() {
        global $asenha_limit_login;

        if ( isset( $asenha_limit_login['within_lockout_period'] ) && $asenha_limit_login['within_lockout_period'] ) {

            // Hide logo, login form and the links below it
            ?>
            <script>
                document.addEventListener("DOMContentLoaded", function(event) {
                    var loginForm = document.getElementById("loginform");
                    loginForm.remove();
                });
            </script>
            <style type="text/css">

                body.login {
                    background:#f6d6d7;
                }

                #login h1,
                #loginform,
                #login #nav,
                #backtoblog,
                .language-switcher { 
                    display: none; 
                }

                @media screen and (max-height: 550px) {

                    #login {
                        padding: 80px 0 20px !important;
                    }

                }

            </style>
            <?php
        } else {
            $options = get_option( ASENHA_SLUG_U, array() );
            $login_fails_allowed = $options['login_fails_allowed'];
            $page_was_reloaded = isset( $_GET['rl'] ) && 1 == sanitize_text_field( $_GET['rl'] ) ? true : false;

            if ( isset( $asenha_limit_login['fail_count'] ) 
                && ( ( $login_fails_allowed - 1 ) == intval( $asenha_limit_login['fail_count'] ) 
                    || ( 2 * $login_fails_allowed - 1 ) == intval( $asenha_limit_login['fail_count'] ) 
                    || ( 3 * $login_fails_allowed - 1 ) == intval( $asenha_limit_login['fail_count'] ) 
                    || ( 4 * $login_fails_allowed - 1 ) == intval( $asenha_limit_login['fail_count'] ) 
                    || ( 5 * $login_fails_allowed - 1 ) == intval( $asenha_limit_login['fail_count'] ) 
                    || ( 6 * $login_fails_allowed - 1 ) == intval( $asenha_limit_login['fail_count'] ) 
                )
            ) {             
                if ( array_key_exists( 'change_login_url', $options ) && $options['change_login_url'] ) {
                    // Custom Login URL is enabled, e.g. /manage
                    // Do nothing
                } else {
                    // Default login URL, i.e. /wp-login.php
                    // Reload the login page so we get the up-to-date data in $asenha_limit_login
                    // Only reload if page was not reloaded before. This prevents infinite reloads.
                    if ( ! $page_was_reloaded ) {                       
                        ?>
                        <script>
                            let url = window.location.href;    
                            if (url.indexOf('?') > -1){
                               url += '&rl=1'
                            } else {
                               url += '?rl=1'
                            }
                            location.replace(url);
                        </script>
                        <?php
                    }

                }
            }
        }
    }

    /**
     * Add login error message on top of the login form
     *
     * @since 2.5.0
     */
    public function add_failed_login_message( $message ) {
        global $asenha_limit_login;

        if ( isset( $_REQUEST['failed_login'] ) && $_REQUEST['failed_login'] == 'true' ) {

            if ( ! is_null( $asenha_limit_login ) && isset( $asenha_limit_login['within_lockout_period'] ) && ! $asenha_limit_login['within_lockout_period'] ) {

                $message = '<div id="login_error" class="notice notice-error"><b>' . __( 'Error:', 'admin-site-enhancements' ) . '</b> ' . __( 'Invalid username/email or incorrect password.', 'admin-site-enhancements' ) . '</div>';

            }

        }

        return $message;
    }
    
    /**
     * Log failed login attempts
     *
     * @since 2.5.0
     */
    public function log_failed_login( $username ) {
        global $wpdb, $asenha_limit_login;

        $table_name = $wpdb->prefix . 'asenha_failed_logins';

        $ip_address = isset( $asenha_limit_login['ip_address'] ) ? $asenha_limit_login['ip_address'] : '';
        $request_uri = isset( $asenha_limit_login['request_uri'] ) ? $asenha_limit_login['request_uri'] : '';
        $login_fails_allowed = isset( $asenha_limit_login['login_fails_allowed'] ) ? $asenha_limit_login['login_fails_allowed'] : 3;
        $login_lockout_maxcount = isset( $asenha_limit_login['login_lockout_maxcount'] ) ? $asenha_limit_login['login_lockout_maxcount'] : 3;
        
        // Check if the IP address has been used in a failed login attempt before, i.e. has it been recorded in the database?
        $sql = $wpdb->prepare( "SELECT * FROM `" . $table_name . "` WHERE `ip_address` = %s", $ip_address );
        $result = $wpdb->get_results( $sql, ARRAY_A );
        if ( $result ) {
            $result_count = count( $result );
        } else {
            $result_count = 0;
        }

        // Update logged info for the IP address in the global variable
        if ( $result ) {
            $asenha_limit_login['ip_address_log'] = $result;        
        }

        if ( $result_count == 0 ) { // IP address has not been recorded in the database.

            $new_fail_count = 1;
            $new_lockout_count = 0;

        } else { // IP address has been recorded in the database.

            $new_fail_count = $result[0]['fail_count'] + 1;
            $new_lockout_count = floor( ( $result[0]['fail_count'] + 1 ) / $login_fails_allowed );

        }

        // Get the URL where login failed, i.e. where brute force attack might be happening
        // $login_url = ( ! empty( $_SERVER['HTTPS'] ) ? 'https://' : 'http://') . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );

        // Time stamps
        $unixtime = time();
        if ( function_exists( 'wp_date' ) ) {
            $datetime_wp = wp_date( 'Y-m-d H:i:s', $unixtime );
        } else {
            $datetime_wp = date_i18n( 'Y-m-d H:i:s', $unixtime );
        }

        $data = array(
            'ip_address'    => $ip_address,
            'username'      => $username,
            'fail_count'    => $new_fail_count,
            'lockout_count' => $new_lockout_count,
            'request_uri'   => $request_uri,
            'unixtime'      => $unixtime,
            'datetime_wp'   => $datetime_wp,
            'info'          => '',
        );

        $data_format = array(
            '%s', // string
            '%s', // string
            '%d', // integer
            '%d', // integer
            '%s', // string
            '%d', // integer
            '%s', // string
            '%s', // string
        );

        if ( $result_count == 0 ) {

            // Insert into the database
            $result = $wpdb->insert(
                $table_name,
                $data,
                $data_format
            );

        } else {

            $fail_count = $result[0]['fail_count'];
            $lockout_count = $result[0]['lockout_count'];
            $last_fail_on = $result[0]['unixtime'];

            $where = array( 'ip_address' => $ip_address );
            $where_format = array( '%s' );

            // Failed attempts have been recorded and fulfills lockout condition
            if ( ! empty( $fail_count ) 
                && ( $login_fails_allowed > 0 )
                && ( $fail_count % $login_fails_allowed == 0 ) 
            ) {

                // Has reached max / gone beyond number of lockouts allowed?
                if ( $lockout_count >= $login_lockout_maxcount ) {
                    $asenha_limit_login['extended_lockout'] = true;
                    $lockout_period = $asenha_limit_login['extended_lockout_period'];
                } else {
                    $asenha_limit_login['extended_lockout'] = false;
                    $lockout_period = $asenha_limit_login['default_lockout_period'];
                }

                $asenha_limit_login['lockout_period'] = $lockout_period;

                // User/visitor is still within the lockout period
                if ( ( time() - $last_fail_on ) <= $lockout_period ) {

                    // Do nothing

                } else {

                    if ( $lockout_count < $login_lockout_maxcount ) {

                        // Update existing data in the database
                        $wpdb->update(
                            $table_name,
                            $data,
                            $where,
                            $data_format,
                            $where_format
                        );

                    }

                }

            } else {

                // Update existing data in the database
                $wpdb->update(
                    $table_name,
                    $data,
                    $where,
                    $data_format,
                    $where_format
                );

            }

        }
    }

    /** 
     * Clear failed login attempts log after successful login
     *
     * @since 2.5.0
     */
    public function clear_failed_login_log() {
        global $wpdb, $asenha_limit_login;

        $table_name = $wpdb->prefix . 'asenha_failed_logins';
        $ip_address = isset( $asenha_limit_login['ip_address'] ) ? $asenha_limit_login['ip_address'] : '';

        // Remove the DB log entry for the current IP address.

        $where = array( 'ip_address' => $ip_address );
        $where_format = array( '%s' );

        $wpdb->delete(
            $table_name,
            $where,
            $where_format
        );
    }

    /**
     * Trigger scheduling of email delivery log clean up event
     * 
     * @since 7.1.1
     */
    public function trigger_clear_or_schedule_log_clean_up_by_amount( $option_name ) {
        if ( 'failed_login_attempts_log_schedule_cleanup_by_amount' == $option_name ) {
            $this->clear_or_schedule_log_clean_up_by_amount();        
        }
    }

    /**
     * Schedule failed login attempts log clean up event
     * 
     * @link https://plugins.trac.wordpress.org/browser/lana-email-logger/tags/1.1.0/lana-email-logger.php#L750
     * @since 7.8.3
     */
    public function clear_or_schedule_log_clean_up_by_amount() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $failed_login_attempts_log_schedule_cleanup_by_amount = isset( $options['failed_login_attempts_log_schedule_cleanup_by_amount'] ) ? $options['failed_login_attempts_log_schedule_cleanup_by_amount'] : false;
        
        // If scheduled clean up is not enabled, let's clear the schedule
        if ( ! $failed_login_attempts_log_schedule_cleanup_by_amount ) {
            wp_clear_scheduled_hook( 'asenha_failed_login_attempts_log_cleanup_by_amount' );
            return;            
        }
        
        // If there's no next scheduled clean up event, let's schedule one
        if ( ! wp_next_scheduled( 'asenha_failed_login_attempts_log_cleanup_by_amount' ) ) {
            wp_schedule_event( time(), 'hourly', 'asenha_failed_login_attempts_log_cleanup_by_amount' );
        }
    }

    /**
     * Perform clean up of failed login attempts log by the amount of entries to keep
     * 
     * @link https://plugins.trac.wordpress.org/browser/lana-email-logger/tags/1.1.0/lana-email-logger.php#L768
     * @since 7.8.3
     */
    public function perform_failed_login_attempts_log_clean_up_by_amount() {
        global $wpdb;
        
        $options = get_option( ASENHA_SLUG_U, array() );
        $failed_login_attempts_log_schedule_cleanup_by_amount = isset( $options['failed_login_attempts_log_schedule_cleanup_by_amount'] ) ? $options['failed_login_attempts_log_schedule_cleanup_by_amount'] : false;
        $failed_login_attempts_log_entries_amount_to_keep = 1000;
        
        // Bail if scheduled clean up by amount is not enabled
        if ( ! $failed_login_attempts_log_schedule_cleanup_by_amount ) {
            return;
        }
                
        $table_name  = $wpdb->prefix.'asenha_failed_logins';
        
        $wpdb->query( "DELETE failed_login_entries FROM " . $table_name . " 
                        AS failed_login_entries JOIN ( SELECT id FROM " . $table_name . " ORDER BY id DESC LIMIT 1 OFFSET " . $failed_login_attempts_log_entries_amount_to_keep . " ) 
                        AS failed_login_entries_limit ON failed_login_entries.id <= failed_login_entries_limit.id;" );
    }

}