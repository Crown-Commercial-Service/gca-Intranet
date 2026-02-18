<?php

namespace ASENHA\Classes;

use WP_Error;
use ASENHA\EmailDelivery\Email_Log_Table;
/**
 * Class for Email Delivery module
 *
 * @since 6.9.5
 */
class Email_Delivery {
    private $log_entry_id;

    /**
     * Send emails using external SMTP service
     *
     * @since 4.6.0
     */
    public function deliver_email_via_smtp( $phpmailer ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $smtp_host = $options['smtp_host'];
        $smtp_port = $options['smtp_port'];
        $smtp_security = $options['smtp_security'];
        $smtp_authentication = ( isset( $options['smtp_authentication'] ) ? $options['smtp_authentication'] : 'enable' );
        $smtp_username = $options['smtp_username'];
        $smtp_password = $options['smtp_password'];
        $smtp_default_from_name = $options['smtp_default_from_name'];
        $smtp_default_from_email = $options['smtp_default_from_email'];
        $smtp_force_from = $options['smtp_force_from'];
        $smtp_bypass_ssl_verification = $options['smtp_bypass_ssl_verification'];
        $smtp_debug = $options['smtp_debug'];
        // Do nothing if host or password is empty
        // if ( empty( $smtp_host ) || empty( $smtp_password ) ) {
        //  return;
        // }
        // Maybe override FROM email and/or name if the sender is "WordPress <wordpress@sitedomain.com>", the default from WordPress core and not yet overridden by another plugin.
        $from_name = $phpmailer->FromName;
        $from_email_beginning = substr( $phpmailer->From, 0, 9 );
        // Get the first 9 characters of the current FROM email
        if ( $smtp_force_from ) {
            $phpmailer->FromName = $smtp_default_from_name;
            $phpmailer->From = $smtp_default_from_email;
            // WP 6.9 fix. Ref: https://make.wordpress.org/core/2025/11/18/more-reliable-email-in-wordpress-6-9/
            $phpmailer->Sender = $smtp_default_from_email;
            $phpmailer->ReturnPath = $smtp_default_from_email;
        } else {
            if ( 'WordPress' === $from_name && !empty( $smtp_default_from_name ) ) {
                $phpmailer->FromName = $smtp_default_from_name;
            }
            if ( 'wordpress' === $from_email_beginning && !empty( $smtp_default_from_email ) ) {
                $phpmailer->From = $smtp_default_from_email;
                // WP 6.9 fix. Ref: https://make.wordpress.org/core/2025/11/18/more-reliable-email-in-wordpress-6-9/
                $phpmailer->Sender = $smtp_default_from_email;
                $phpmailer->ReturnPath = $smtp_default_from_email;
            }
        }
        // Only attempt to send via SMTP if all the required info is present. Otherwise, use default PHP Mailer settings as set by wp_mail()
        if ( !empty( $smtp_host ) && !empty( $smtp_port ) && !empty( $smtp_security ) ) {
            // Send using SMTP
            $phpmailer->isSMTP();
            // phpcs:ignore
            if ( 'enable' == $smtp_authentication ) {
                $phpmailer->SMTPAuth = true;
                // phpcs:ignore
            } else {
                $phpmailer->SMTPAuth = false;
                // phpcs:ignore
            }
            // Set some other defaults
            // $phpmailer->CharSet  = 'utf-8'; // phpcs:ignore
            $phpmailer->XMailer = 'Admin and Site Enhancements v' . ASENHA_VERSION . ' - a WordPress plugin';
            // phpcs:ignore
            $phpmailer->Host = $smtp_host;
            // phpcs:ignore
            $phpmailer->Port = $smtp_port;
            // phpcs:ignore
            $phpmailer->SMTPSecure = $smtp_security;
            // phpcs:ignore
            if ( 'enable' == $smtp_authentication ) {
                $phpmailer->Username = trim( $smtp_username );
                // phpcs:ignore
                $phpmailer->Password = trim( $smtp_password );
                // phpcs:ignore
            }
        }
        // If verification of SSL certificate is bypassed
        // Reference: https://www.php.net/manual/en/context.ssl.php & https://stackoverflow.com/a/30803024
        if ( $smtp_bypass_ssl_verification ) {
            $phpmailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
        }
        // If debug mode is enabled, send debug info (SMTP::DEBUG_CONNECTION) to WordPress debug.log file set in wp-config.php
        // Reference: https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging
        if ( $smtp_debug ) {
            $phpmailer->SMTPDebug = 4;
            //phpcs:ignore
            $phpmailer->Debugoutput = 'error_log';
            //phpcs:ignore
        }
    }

    /**
     * Send a test email and use SMTP host if defined in settings
     * 
     * @since 5.3.0
     */
    public function send_test_email() {
        if ( isset( $_REQUEST['email_to'] ) && isset( $_REQUEST['nonce'] ) && current_user_can( 'manage_options' ) ) {
            if ( wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'send-test-email-nonce_' . get_current_user_id() ) ) {
                $content = array(
                    array(
                        'title' => 'Hey... are you getting this?',
                        'body'  => '<p><strong>Looks like you did!</strong></p>',
                    ),
                    array(
                        'title' => 'There\'s a message for you...',
                        'body'  => '<p><strong>Here it is:</strong></p>',
                    ),
                    array(
                        'title' => 'Is it working?',
                        'body'  => '<p><strong>Yes, it\'s working!</strong></p>',
                    ),
                    array(
                        'title' => 'Hope you\'re getting this...',
                        'body'  => '<p><strong>Looks like this was sent out just fine and you got it.</strong></p>',
                    ),
                    array(
                        'title' => 'Testing delivery configuration...',
                        'body'  => '<p><strong>Everything looks good!</strong></p>',
                    ),
                    array(
                        'title' => 'Testing email delivery',
                        'body'  => '<p><strong>Looks good!</strong></p>',
                    ),
                    array(
                        'title' => 'Config is looking good',
                        'body'  => '<p><strong>Seems like everything has been set up properly!</strong></p>',
                    ),
                    array(
                        'title' => 'All set up',
                        'body'  => '<p><strong>Your configuration is working properly.</strong></p>',
                    ),
                    array(
                        'title' => 'Good to go',
                        'body'  => '<p><strong>Config is working great.</strong></p>',
                    ),
                    array(
                        'title' => 'Good job',
                        'body'  => '<p><strong>Everything is set.</strong></p>',
                    )
                );
                $random_number = rand( 0, count( $content ) - 1 );
                $to = $_REQUEST['email_to'];
                $title = $content[$random_number]['title'];
                $body = $content[$random_number]['body'] . '<p>This message was sent from <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'url' ) . '</a> on ' . wp_date( 'F j, Y' ) . ' at ' . wp_date( 'H:i:s' ) . ' via ASE.</p>';
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $success = wp_mail(
                    $to,
                    $title,
                    $body,
                    $headers
                );
                if ( $success ) {
                    $response = array(
                        'status' => 'success',
                    );
                } else {
                    $response = array(
                        'status' => 'failed',
                    );
                }
                echo json_encode( $response );
            }
        }
    }

}
