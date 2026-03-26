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
    const SMTP_PASSWORD_PREFIX = 'asenha_encrypted::smtp_password::v1::';

    const SMTP_PASSWORD_STATUS_EMPTY = 'empty';

    const SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT = 'legacy_plaintext';

    const SMTP_PASSWORD_STATUS_ENCRYPTED_VALID = 'encrypted_valid';

    const SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID = 'encrypted_invalid';

    private $log_entry_id;

    /**
     * Derive a stable encryption key for the stored SMTP password.
     *
     * @since 8.4.3
     *
     * @return string
     */
    private function get_smtp_password_encryption_key() {
        return hash( 'sha256', \wp_salt( 'auth' ) . \wp_salt( 'secure_auth' ) . 'asenha_smtp_password', true );
    }

    /**
     * Check whether the current environment can encrypt/decrypt the SMTP password.
     *
     * @since 8.4.3
     *
     * @return bool
     */
    private function can_handle_smtp_password_encryption() {
        return function_exists( 'openssl_encrypt' ) && function_exists( 'openssl_decrypt' ) && function_exists( 'openssl_cipher_iv_length' ) && function_exists( 'random_bytes' );
    }

    /**
     * Check whether the stored SMTP password value is encrypted.
     *
     * @since 8.4.3
     *
     * @param string $stored_password Stored option value.
     * @return bool
     */
    public function is_smtp_password_encrypted( $stored_password ) {
        return is_string( $stored_password ) && 0 === strpos( $stored_password, self::SMTP_PASSWORD_PREFIX );
    }

    /**
     * Encrypt SMTP password for storage in options.
     *
     * @since 8.4.3
     *
     * @param string $password SMTP password.
     * @return string Encrypted payload or empty string on failure.
     */
    public function encrypt_smtp_password( $password ) {
        if ( '' === $password ) {
            return '';
        }
        if ( !$this->can_handle_smtp_password_encryption() ) {
            return '';
        }
        $cipher = 'AES-256-CBC';
        $key = $this->get_smtp_password_encryption_key();
        $iv_len = openssl_cipher_iv_length( $cipher );
        if ( false === $iv_len ) {
            return '';
        }
        try {
            $iv = random_bytes( $iv_len );
        } catch ( \Exception $exception ) {
            return '';
        }
        $ciphertext_raw = openssl_encrypt(
            $password,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ( false === $ciphertext_raw ) {
            return '';
        }
        $hmac = hash_hmac(
            'sha256',
            $ciphertext_raw,
            $key,
            true
        );
        return self::SMTP_PASSWORD_PREFIX . base64_encode( $iv . $hmac . $ciphertext_raw );
    }

    /**
     * Decrypt stored SMTP password.
     *
     * @since 8.4.3
     *
     * @param string $stored_password Stored option value.
     * @return string|false
     */
    public function decrypt_smtp_password( $stored_password ) {
        if ( !$this->is_smtp_password_encrypted( $stored_password ) ) {
            return false;
        }
        if ( !$this->can_handle_smtp_password_encryption() ) {
            return false;
        }
        $payload = substr( $stored_password, strlen( self::SMTP_PASSWORD_PREFIX ) );
        $decoded = base64_decode( $payload, true );
        if ( false === $decoded ) {
            return false;
        }
        $cipher = 'AES-256-CBC';
        $key = $this->get_smtp_password_encryption_key();
        $iv_len = openssl_cipher_iv_length( $cipher );
        if ( false === $iv_len || strlen( $decoded ) <= $iv_len + 32 ) {
            return false;
        }
        $iv = substr( $decoded, 0, $iv_len );
        $stored_hmac = substr( $decoded, $iv_len, 32 );
        $ciphertext_raw = substr( $decoded, $iv_len + 32 );
        $calc_hmac = hash_hmac(
            'sha256',
            $ciphertext_raw,
            $key,
            true
        );
        if ( !hash_equals( $stored_hmac, $calc_hmac ) ) {
            return false;
        }
        return openssl_decrypt(
            $ciphertext_raw,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * Get the current storage status of the SMTP password.
     *
     * @since 8.4.3
     *
     * @param string|null $stored_password Stored option value.
     * @return string
     */
    public function get_smtp_password_status( $stored_password = null ) {
        if ( null === $stored_password ) {
            $options = get_option( ASENHA_SLUG_U, array() );
            $stored_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
        }
        if ( empty( $stored_password ) ) {
            return self::SMTP_PASSWORD_STATUS_EMPTY;
        }
        if ( $this->is_smtp_password_encrypted( $stored_password ) ) {
            return ( false !== $this->decrypt_smtp_password( $stored_password ) ? self::SMTP_PASSWORD_STATUS_ENCRYPTED_VALID : self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID );
        }
        return self::SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT;
    }

    /**
     * Resolve SMTP password for runtime delivery use.
     *
     * Legacy plaintext remains supported as a migration fallback until the
     * settings are re-saved and rewritten to the encrypted format.
     *
     * @since 8.4.3
     *
     * @param string|null $stored_password Stored option value.
     * @return string
     */
    public function get_smtp_password_for_runtime( $stored_password = null ) {
        if ( null === $stored_password ) {
            $options = get_option( ASENHA_SLUG_U, array() );
            $stored_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
        }
        switch ( $this->get_smtp_password_status( $stored_password ) ) {
            case self::SMTP_PASSWORD_STATUS_ENCRYPTED_VALID:
                $decrypted_password = $this->decrypt_smtp_password( $stored_password );
                return ( false !== $decrypted_password ? $decrypted_password : '' );
            case self::SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT:
                return (string) $stored_password;
            default:
                return '';
        }
    }

    /**
     * Get message shown when the stored SMTP password can no longer be used.
     *
     * @since 8.4.3
     *
     * @return string
     */
    public function get_smtp_password_reentry_message() {
        return __( 'The stored SMTP password can no longer be decrypted. Please enter it again and save changes.', 'admin-site-enhancements' );
    }

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
        $smtp_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
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
        $smtp_password = $this->get_smtp_password_for_runtime( $smtp_password );
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
                $options = get_option( ASENHA_SLUG_U, array() );
                $smtp_host = ( isset( $options['smtp_host'] ) ? $options['smtp_host'] : '' );
                $smtp_port = ( isset( $options['smtp_port'] ) ? $options['smtp_port'] : '' );
                $smtp_security = ( isset( $options['smtp_security'] ) ? $options['smtp_security'] : '' );
                $smtp_authentication = ( isset( $options['smtp_authentication'] ) ? $options['smtp_authentication'] : 'enable' );
                $smtp_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
                $smtp_password_status = $this->get_smtp_password_status( $smtp_password );
                $smtp_is_configured = !empty( $smtp_host ) && !empty( $smtp_port ) && !empty( $smtp_security );
                if ( $smtp_is_configured && 'enable' === $smtp_authentication && self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID === $smtp_password_status ) {
                    wp_send_json( array(
                        'status'  => 'failed',
                        'message' => $this->get_smtp_password_reentry_message(),
                    ) );
                }
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
                $to = sanitize_email( wp_unslash( $_REQUEST['email_to'] ) );
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
                wp_send_json( $response );
            }
        }
    }

}
