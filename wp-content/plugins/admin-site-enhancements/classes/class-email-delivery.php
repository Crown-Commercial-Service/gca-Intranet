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

    const SMTP_PASSWORD_PREFIX_V2 = 'asenha_encrypted::smtp_password::v2::';

    const SMTP_PASSWORD_UNAVAILABLE_TRANSIENT = 'asenha_smtp_password_unavailable';

    const SMTP_PASSWORD_NOTICE_DISMISSED_META = 'asenha_smtp_password_notice_dismissed';

    const SMTP_PASSWORD_STATUS_EMPTY = 'empty';

    const SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT = 'legacy_plaintext';

    const SMTP_PASSWORD_STATUS_ENCRYPTED_VALID = 'encrypted_valid';

    const SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID = 'encrypted_invalid';

    private $log_entry_id;

    /**
     * Runtime Email_Delivery instance registered from bootstrap.php.
     *
     * @since 8.8.5
     *
     * @var Email_Delivery|null
     */
    private static $runtime_instance = null;

    /**
     * Pending fallback log error when wp_mail_failed fires before a log row exists.
     *
     * @since 8.8.5
     *
     * @var string
     */
    private $pending_fallback_log_error = '';

    /**
     * Max length for subject column in asenha_email_delivery.
     *
     * @since 8.8.5
     */
    private const LOG_SUBJECT_MAX_LENGTH = 250;

    /**
     * Max length for send_to column in asenha_email_delivery.
     *
     * @since 8.8.5
     */
    private const LOG_SEND_TO_MAX_LENGTH = 256;

    /**
     * Max length for error column in asenha_email_delivery.
     *
     * @since 8.8.5
     */
    private const LOG_ERROR_MAX_LENGTH = 250;

    /**
     * Register the runtime Email_Delivery instance used by hooked callbacks.
     *
     * @since 8.8.5
     *
     * @param Email_Delivery $instance Email Delivery instance.
     * @return void
     */
    public static function set_runtime_instance( Email_Delivery $instance ) {
        self::$runtime_instance = $instance;
    }

    /**
     * Get the runtime Email_Delivery instance registered from bootstrap.php.
     *
     * @since 8.8.5
     *
     * @return Email_Delivery|null
     */
    public static function get_runtime_instance() {
        return self::$runtime_instance;
    }

    /**
     * Pending email-log error when SMTP auth cannot use the stored password.
     *
     * @since 8.8.5
     *
     * @var string
     */
    private $pending_smtp_password_log_error = '';

    /**
     * Transient lifetime for surfacing SMTP password failures to administrators.
     *
     * @since 8.8.5
     *
     * @return int
     */
    public function get_smtp_password_unavailable_transient_ttl() {
        return 90 * DAY_IN_SECONDS;
    }

    /**
     * Derive the v1 encryption key from WordPress salts.
     *
     * @since 8.4.3
     *
     * @return string
     */
    private function get_smtp_password_encryption_key_v1() {
        return hash( 'sha256', \wp_salt( 'auth' ) . \wp_salt( 'secure_auth' ) . 'asenha_smtp_password', true );
    }

    /**
     * Load admin_site_enhancements_extra as an array.
     *
     * @since 8.8.5
     *
     * @return array
     */
    private function get_options_extra_array() {
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        return ( is_array( $options_extra ) ? $options_extra : array() );
    }

    /**
     * Get the stored v2 secret key without creating one.
     *
     * @since 8.8.5
     *
     * @return string|false
     */
    private function get_stored_smtp_secret_key_v2() {
        $options_extra = $this->get_options_extra_array();
        if ( empty( $options_extra['smtp_secret_key'] ) || !is_string( $options_extra['smtp_secret_key'] ) ) {
            return false;
        }
        $decoded = base64_decode( $options_extra['smtp_secret_key'], true );
        if ( false === $decoded || 32 !== strlen( $decoded ) ) {
            return false;
        }
        return $decoded;
    }

    /**
     * Get or create the per-site v2 SMTP secret key.
     *
     * @since 8.8.5
     *
     * @return string|false
     */
    private function get_or_create_smtp_secret_key_v2() {
        $stored_key = $this->get_stored_smtp_secret_key_v2();
        if ( false !== $stored_key ) {
            return $stored_key;
        }
        if ( !$this->can_handle_smtp_password_encryption() ) {
            return false;
        }
        try {
            $key = random_bytes( 32 );
        } catch ( \Exception $exception ) {
            return false;
        }
        $options_extra = $this->get_options_extra_array();
        $options_extra['smtp_secret_key'] = base64_encode( $key );
        update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
        return $key;
    }

    /**
     * Persist a non-secret fingerprint of the active encryption key.
     *
     * @since 8.8.5
     *
     * @param string $encryption_key Raw encryption key.
     * @return void
     */
    private function persist_smtp_password_key_fingerprint( $encryption_key ) {
        $options_extra = $this->get_options_extra_array();
        $options_extra['smtp_password_key_fingerprint'] = hash( 'sha256', $encryption_key );
        update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
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
     * Check whether the stored SMTP password uses the v1 encrypted format.
     *
     * @since 8.8.5
     *
     * @param string $stored_password Stored option value.
     * @return bool
     */
    public function is_smtp_password_v1_encrypted( $stored_password ) {
        return is_string( $stored_password ) && 0 === strpos( $stored_password, self::SMTP_PASSWORD_PREFIX );
    }

    /**
     * Check whether the stored SMTP password uses the v2 encrypted format.
     *
     * @since 8.8.5
     *
     * @param string $stored_password Stored option value.
     * @return bool
     */
    public function is_smtp_password_v2_encrypted( $stored_password ) {
        return is_string( $stored_password ) && 0 === strpos( $stored_password, self::SMTP_PASSWORD_PREFIX_V2 );
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
        return $this->is_smtp_password_v1_encrypted( $stored_password ) || $this->is_smtp_password_v2_encrypted( $stored_password );
    }

    /**
     * Check whether a value looks like stored SMTP ciphertext rather than plaintext.
     *
     * @since 8.8.6
     *
     * @param string $value Candidate value.
     * @return bool
     */
    public function is_probable_smtp_ciphertext( $value ) {
        return is_string( $value ) && ($this->is_smtp_password_v1_encrypted( $value ) || $this->is_smtp_password_v2_encrypted( $value ) || 0 === strpos( $value, 'asenha_encrypted::smtp_password::' ));
    }

    /**
     * Whether SMTP password storage has been upgraded to the v2 format.
     *
     * @since 8.8.6
     *
     * @return bool
     */
    public function is_smtp_password_storage_version_v2() {
        $options_extra = $this->get_options_extra_array();
        return isset( $options_extra['smtp_password_storage_version'] ) && 2 === (int) $options_extra['smtp_password_storage_version'];
    }

    /**
     * Mark SMTP password storage as upgraded to v2.
     *
     * @since 8.8.6
     *
     * @return void
     */
    public function mark_smtp_password_storage_version_v2() {
        $options_extra = $this->get_options_extra_array();
        $options_extra['smtp_password_storage_version'] = 2;
        update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
    }

    /**
     * Unwrap nested SMTP ciphertext layers down to plaintext.
     *
     * @since 8.8.6
     *
     * @param string $stored_password Stored option value.
     * @param int    $max_depth       Maximum unwrap attempts.
     * @return string|false Plaintext password or false when unusable.
     */
    public function unwrap_smtp_password_to_plaintext( $stored_password, $max_depth = 5 ) {
        if ( !is_string( $stored_password ) || '' === $stored_password ) {
            return false;
        }
        if ( !$this->is_probable_smtp_ciphertext( $stored_password ) ) {
            return $stored_password;
        }
        $current = $stored_password;
        $depth = 0;
        while ( $depth < $max_depth && $this->is_probable_smtp_ciphertext( $current ) ) {
            $decrypted = $this->decrypt_smtp_password( $current );
            if ( false === $decrypted ) {
                return false;
            }
            $current = $decrypted;
            $depth++;
        }
        if ( $this->is_probable_smtp_ciphertext( $current ) ) {
            return false;
        }
        return $current;
    }

    /**
     * Encrypt a payload with AES-256-CBC and an authenticated HMAC.
     *
     * @since 8.8.5
     *
     * @param string $password Plaintext password.
     * @param string $key      Encryption key.
     * @param string $prefix   Stored-value prefix.
     * @return string
     */
    private function encrypt_smtp_password_with_key( $password, $key, $prefix ) {
        if ( '' === $password || !$this->can_handle_smtp_password_encryption() ) {
            return '';
        }
        $cipher = 'AES-256-CBC';
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
        return $prefix . base64_encode( $iv . $hmac . $ciphertext_raw );
    }

    /**
     * Decrypt a stored SMTP password payload.
     *
     * @since 8.8.5
     *
     * @param string $stored_password Stored option value.
     * @param string $key             Encryption key.
     * @param string $prefix          Stored-value prefix.
     * @return string|false
     */
    private function decrypt_smtp_password_with_key( $stored_password, $key, $prefix ) {
        if ( !is_string( $stored_password ) || 0 !== strpos( $stored_password, $prefix ) ) {
            return false;
        }
        if ( !$this->can_handle_smtp_password_encryption() ) {
            return false;
        }
        $payload = substr( $stored_password, strlen( $prefix ) );
        $decoded = base64_decode( $payload, true );
        if ( false === $decoded ) {
            return false;
        }
        $cipher = 'AES-256-CBC';
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
     * Encrypt SMTP password for storage in options (always v2).
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
        if ( $this->is_probable_smtp_ciphertext( $password ) ) {
            return '';
        }
        $key = $this->get_or_create_smtp_secret_key_v2();
        if ( false === $key ) {
            return '';
        }
        $encrypted_password = $this->encrypt_smtp_password_with_key( $password, $key, self::SMTP_PASSWORD_PREFIX_V2 );
        if ( !empty( $encrypted_password ) ) {
            $this->persist_smtp_password_key_fingerprint( $key );
            $this->mark_smtp_password_storage_version_v2();
            $this->clear_smtp_password_unavailable_flag();
            $this->clear_smtp_password_notice_dismissals();
        }
        return $encrypted_password;
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
        if ( $this->is_smtp_password_v2_encrypted( $stored_password ) ) {
            $key = $this->get_stored_smtp_secret_key_v2();
            if ( false === $key ) {
                return false;
            }
            return $this->decrypt_smtp_password_with_key( $stored_password, $key, self::SMTP_PASSWORD_PREFIX_V2 );
        }
        if ( $this->is_smtp_password_v1_encrypted( $stored_password ) ) {
            return $this->decrypt_smtp_password_with_key( $stored_password, $this->get_smtp_password_encryption_key_v1(), self::SMTP_PASSWORD_PREFIX );
        }
        return false;
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
            $plaintext_password = $this->unwrap_smtp_password_to_plaintext( $stored_password );
            if ( false === $plaintext_password || $this->is_probable_smtp_ciphertext( $plaintext_password ) ) {
                return self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID;
            }
            return self::SMTP_PASSWORD_STATUS_ENCRYPTED_VALID;
        }
        if ( $this->is_probable_smtp_ciphertext( $stored_password ) ) {
            return self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID;
        }
        return self::SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT;
    }

    /**
     * Human-readable SMTP password status label for the settings UI.
     *
     * @since 8.8.5
     *
     * @param string|null $stored_password Stored option value.
     * @return string
     */
    public function get_smtp_password_status_label( $stored_password = null ) {
        $status = $this->get_smtp_password_status( $stored_password );
        switch ( $status ) {
            case self::SMTP_PASSWORD_STATUS_ENCRYPTED_VALID:
            case self::SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT:
                return __( 'Saved', 'admin-site-enhancements' );
            case self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID:
                return __( 'Re-entry required', 'admin-site-enhancements' );
            default:
                return __( 'Not set', 'admin-site-enhancements' );
        }
    }

    /**
     * Get SMTP password UI state for the settings screen and AJAX responses.
     *
     * Side-effect free: does not set transients or modify stored credentials.
     *
     * @since 8.8.7
     *
     * @param array|null $options ASE options array.
     * @return array {
     *     @type string $status                 Storage status constant value.
     *     @type string $label                  Human-readable status label.
     *     @type string $description            Full field description for the password row.
     *     @type bool   $show_warning           Whether to show the inline re-entry warning.
     *     @type string $warning_message        Warning notice text.
     *     @type bool   $authentication_enabled Whether SMTP auth is enabled.
     * }
     */
    public function get_smtp_password_ui_state( $options = null ) {
        if ( null === $options ) {
            if ( function_exists( '\\asenha_get_option_array' ) ) {
                $options = \asenha_get_option_array( ASENHA_SLUG_U, true );
            } else {
                $options = get_option( ASENHA_SLUG_U, array() );
            }
        }
        if ( !is_array( $options ) ) {
            $options = array();
        }
        $authentication_enabled = !isset( $options['smtp_authentication'] ) || 'enable' === $options['smtp_authentication'];
        $stored_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
        $status = $this->get_smtp_password_status( $stored_password );
        $label = ( $authentication_enabled ? $this->get_smtp_password_status_label( $stored_password ) : '' );
        $description = __( 'Leave blank to keep the current password.', 'admin-site-enhancements' );
        if ( $authentication_enabled && '' !== $label ) {
            $description .= ' ' . sprintf( 
                /* translators: %s: password storage status label */
                __( 'Status: %s.', 'admin-site-enhancements' ),
                $label
             );
        }
        $show_warning = false;
        $warning_message = '';
        if ( $authentication_enabled && self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID === $status ) {
            $description = __( 'Enter and save a new password to restore SMTP authentication.', 'admin-site-enhancements' );
            if ( '' !== $label ) {
                $description .= ' ' . sprintf( 
                    /* translators: %s: password storage status label */
                    __( 'Status: %s.', 'admin-site-enhancements' ),
                    $label
                 );
            }
            $show_warning = true;
            $warning_message = __( 'The stored SMTP password can no longer be decrypted. Please enter it again above and save changes.', 'admin-site-enhancements' );
        }
        return array(
            'status'                 => $status,
            'label'                  => $label,
            'description'            => $description,
            'show_warning'           => $show_warning,
            'warning_message'        => $warning_message,
            'authentication_enabled' => $authentication_enabled,
        );
    }

    /**
     * Map SMTP password UI state to AJAX response fields.
     *
     * @since 8.8.7
     *
     * @param array|null $options ASE options array.
     * @return array
     */
    public function get_smtp_password_ajax_ui_fields( $options = null ) {
        $ui_state = $this->get_smtp_password_ui_state( $options );
        return array(
            'smtp_password_status'       => $ui_state['status'],
            'smtp_password_status_label' => $ui_state['label'],
            'smtp_password_description'  => $ui_state['description'],
            'smtp_password_warning'      => ( $ui_state['show_warning'] ? $ui_state['warning_message'] : '' ),
        );
    }

    /**
     * Whether SMTP authentication is required but no usable password is available.
     *
     * @since 8.8.5
     *
     * @param array|null $options ASE options array.
     * @return bool
     */
    public function is_smtp_password_unavailable_for_delivery( $options = null ) {
        if ( null === $options ) {
            $options = get_option( ASENHA_SLUG_U, array() );
        }
        if ( !is_array( $options ) || empty( $options['smtp_email_delivery'] ) ) {
            return false;
        }
        $smtp_authentication = ( isset( $options['smtp_authentication'] ) ? $options['smtp_authentication'] : 'enable' );
        if ( 'enable' !== $smtp_authentication ) {
            return false;
        }
        $smtp_host = ( isset( $options['smtp_host'] ) ? $options['smtp_host'] : '' );
        $smtp_port = ( isset( $options['smtp_port'] ) ? $options['smtp_port'] : '' );
        $smtp_security = ( isset( $options['smtp_security'] ) ? $options['smtp_security'] : '' );
        if ( empty( $smtp_host ) || empty( $smtp_port ) || empty( $smtp_security ) ) {
            return false;
        }
        $stored_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
        $status = $this->get_smtp_password_status( $stored_password );
        if ( self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID === $status ) {
            return true;
        }
        if ( self::SMTP_PASSWORD_STATUS_EMPTY === $status ) {
            return true;
        }
        return '' === $this->get_smtp_password_for_runtime( $stored_password );
    }

    /**
     * Flag that SMTP password delivery is unavailable for administrator follow-up.
     *
     * @since 8.8.5
     *
     * @return void
     */
    public function set_smtp_password_unavailable_flag() {
        set_transient( self::SMTP_PASSWORD_UNAVAILABLE_TRANSIENT, 1, $this->get_smtp_password_unavailable_transient_ttl() );
    }

    /**
     * Clear the SMTP password unavailable flag.
     *
     * @since 8.8.5
     *
     * @return void
     */
    public function clear_smtp_password_unavailable_flag() {
        delete_transient( self::SMTP_PASSWORD_UNAVAILABLE_TRANSIENT );
    }

    /**
     * Whether the SMTP password unavailable flag is currently set.
     *
     * @since 8.8.5
     *
     * @return bool
     */
    public function is_smtp_password_unavailable_flagged() {
        return (bool) get_transient( self::SMTP_PASSWORD_UNAVAILABLE_TRANSIENT );
    }

    /**
     * Clear per-user SMTP password notice dismissals.
     *
     * @since 8.8.5
     *
     * @return void
     */
    public function clear_smtp_password_notice_dismissals() {
        delete_metadata(
            'user',
            0,
            self::SMTP_PASSWORD_NOTICE_DISMISSED_META,
            '',
            true
        );
    }

    /**
     * Check SMTP password health after a Site Backup restore.
     *
     * @since 8.8.5
     *
     * @return string Warning message or empty string when healthy.
     */
    public function check_smtp_password_after_restore() {
        if ( !$this->is_smtp_password_unavailable_for_delivery() ) {
            return '';
        }
        $this->set_smtp_password_unavailable_flag();
        return __( 'SMTP password could not be decrypted after restore. Re-enter it under ASE → Email Delivery.', 'admin-site-enhancements' );
    }

    /**
     * Repair nested SMTP password storage by rewriting a clean single-layer v2 value.
     *
     * @since 8.8.6
     *
     * @return bool Whether storage was repaired or already healthy.
     */
    public function repair_nested_smtp_password_storage() {
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( !is_array( $options ) || empty( $options['smtp_password'] ) ) {
            return false;
        }
        $stored_password = $options['smtp_password'];
        if ( $this->is_smtp_password_storage_version_v2() && $this->is_smtp_password_v2_encrypted( $stored_password ) ) {
            $outer_plaintext = $this->decrypt_smtp_password( $stored_password );
            if ( false !== $outer_plaintext && !$this->is_probable_smtp_ciphertext( $outer_plaintext ) ) {
                return true;
            }
        }
        $plaintext_password = $this->unwrap_smtp_password_to_plaintext( $stored_password );
        if ( false === $plaintext_password || $this->is_probable_smtp_ciphertext( $plaintext_password ) ) {
            $this->set_smtp_password_unavailable_flag();
            return false;
        }
        if ( $this->is_smtp_password_v2_encrypted( $stored_password ) ) {
            $outer_plaintext = $this->decrypt_smtp_password( $stored_password );
            if ( false !== $outer_plaintext && !$this->is_probable_smtp_ciphertext( $outer_plaintext ) && $outer_plaintext === $plaintext_password ) {
                $this->mark_smtp_password_storage_version_v2();
                $this->clear_smtp_password_unavailable_flag();
                return true;
            }
        }
        $encrypted_password = $this->encrypt_smtp_password( $plaintext_password );
        if ( empty( $encrypted_password ) ) {
            $this->set_smtp_password_unavailable_flag();
            return false;
        }
        $options['smtp_password'] = $encrypted_password;
        update_option( ASENHA_SLUG_U, $options, true );
        $this->clear_smtp_password_unavailable_flag();
        return true;
    }

    /**
     * Output a global admin notice when SMTP authentication cannot use the stored password.
     *
     * @since 8.8.5
     *
     * @return void
     */
    public function maybe_show_smtp_password_admin_notice() {
        if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
            return;
        }
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( !is_array( $options ) || empty( $options['smtp_email_delivery'] ) ) {
            return;
        }
        $should_warn = $this->is_smtp_password_unavailable_for_delivery( $options ) || $this->is_smtp_password_unavailable_flagged();
        if ( !$should_warn ) {
            return;
        }
        if ( get_user_meta( get_current_user_id(), self::SMTP_PASSWORD_NOTICE_DISMISSED_META, true ) && !$this->is_smtp_password_unavailable_flagged() ) {
            return;
        }
        $settings_url = admin_url( 'tools.php?page=' . ASENHA_SLUG . '#utilities' );
        $message = $this->get_smtp_password_reentry_message();
        ?>
        <div class="notice notice-warning is-dismissible asenha-smtp-password-notice" id="asenha-smtp-password-notice">
            <p>
                <?php 
        echo esc_html( $message );
        ?>
                <a href="<?php 
        echo esc_url( $settings_url );
        ?>"><?php 
        esc_html_e( 'Open Email Delivery settings', 'admin-site-enhancements' );
        ?></a>
            </p>
        </div>
        <?php 
    }

    /**
     * Dismiss the global SMTP password admin notice for the current administrator.
     *
     * @since 8.8.5
     *
     * @return void
     */
    public function dismiss_smtp_password_admin_notice() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ),
            ), 403 );
        }
        check_ajax_referer( 'asenha-dismiss-smtp-password-notice', 'nonce' );
        update_user_meta( get_current_user_id(), self::SMTP_PASSWORD_NOTICE_DISMISSED_META, true );
        wp_send_json_success();
    }

    /**
     * Enqueue the dismiss handler for the global SMTP password notice.
     *
     * @since 8.8.5
     *
     * @return void
     */
    public function enqueue_smtp_password_notice_script() {
        if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
            return;
        }
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( !is_array( $options ) || empty( $options['smtp_email_delivery'] ) ) {
            return;
        }
        if ( !$this->is_smtp_password_unavailable_for_delivery( $options ) && !$this->is_smtp_password_unavailable_flagged() ) {
            return;
        }
        if ( get_user_meta( get_current_user_id(), self::SMTP_PASSWORD_NOTICE_DISMISSED_META, true ) && !$this->is_smtp_password_unavailable_flagged() ) {
            return;
        }
        wp_enqueue_script( 'jquery' );
        wp_add_inline_script( 'jquery', 'jQuery(function($){$(document).on("click","#asenha-smtp-password-notice .notice-dismiss",function(){$.post(ajaxurl,{action:"asenha_dismiss_smtp_password_notice",nonce:' . wp_json_encode( wp_create_nonce( 'asenha-dismiss-smtp-password-notice' ) ) . '});});});' );
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
                $plaintext_password = $this->unwrap_smtp_password_to_plaintext( $stored_password );
                if ( false === $plaintext_password || $this->is_probable_smtp_ciphertext( $plaintext_password ) ) {
                    $this->set_smtp_password_unavailable_flag();
                    return '';
                }
                return $plaintext_password;
            case self::SMTP_PASSWORD_STATUS_LEGACY_PLAINTEXT:
                if ( $this->is_probable_smtp_ciphertext( $stored_password ) ) {
                    $this->set_smtp_password_unavailable_flag();
                    return '';
                }
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
            // WP 6.9: set SMTP envelope (MAIL FROM) using PHPMailer::Sender only.
            // PHPMailer maintainers treat envelope bounce handling as **Sender**, not a separate ReturnPath property;
            // receiving MTAs derive Return-Path from the envelope sender.
            // Ref: https://make.wordpress.org/core/2025/11/18/more-reliable-email-in-wordpress-6-9/
            $phpmailer->Sender = $smtp_default_from_email;
        } else {
            if ( 'WordPress' === $from_name && !empty( $smtp_default_from_name ) ) {
                $phpmailer->FromName = $smtp_default_from_name;
            }
            if ( 'wordpress' === $from_email_beginning && !empty( $smtp_default_from_email ) ) {
                $phpmailer->From = $smtp_default_from_email;
                // WP 6.9: set SMTP envelope (MAIL FROM) using PHPMailer::Sender only.
                // PHPMailer maintainers treat envelope bounce handling as **Sender**, not a separate ReturnPath property;
                // receiving MTAs derive Return-Path from the envelope sender.
                // Ref: https://make.wordpress.org/core/2025/11/18/more-reliable-email-in-wordpress-6-9/
                $phpmailer->Sender = $smtp_default_from_email;
            }
        }
        $smtp_password = $this->get_smtp_password_for_runtime( $smtp_password );
        if ( 'enable' === $smtp_authentication && !empty( $smtp_host ) && !empty( $smtp_port ) && !empty( $smtp_security ) && '' === trim( $smtp_password ) ) {
            $this->set_smtp_password_unavailable_flag();
            $this->pending_smtp_password_log_error = __( 'SMTP password unavailable (decryption failed or not configured).', 'admin-site-enhancements' );
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
                $options = get_option( ASENHA_SLUG_U, array() );
                $smtp_host = ( isset( $options['smtp_host'] ) ? $options['smtp_host'] : '' );
                $smtp_port = ( isset( $options['smtp_port'] ) ? $options['smtp_port'] : '' );
                $smtp_security = ( isset( $options['smtp_security'] ) ? $options['smtp_security'] : '' );
                $smtp_authentication = ( isset( $options['smtp_authentication'] ) ? $options['smtp_authentication'] : 'enable' );
                $smtp_password = ( isset( $options['smtp_password'] ) ? $options['smtp_password'] : '' );
                $smtp_password_status = $this->get_smtp_password_status( $smtp_password );
                $smtp_is_configured = !empty( $smtp_host ) && !empty( $smtp_port ) && !empty( $smtp_security );
                $runtime_smtp_password = $this->get_smtp_password_for_runtime( $smtp_password );
                if ( $smtp_is_configured && 'enable' === $smtp_authentication && (self::SMTP_PASSWORD_STATUS_ENCRYPTED_INVALID === $smtp_password_status || '' === $runtime_smtp_password || $this->is_probable_smtp_ciphertext( $runtime_smtp_password )) ) {
                    wp_send_json( array_merge( array(
                        'status'  => 'failed',
                        'message' => $this->get_smtp_password_reentry_message(),
                    ), $this->get_smtp_password_ajax_ui_fields( $options ) ) );
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
                $ui_fields = $this->get_smtp_password_ajax_ui_fields( $options );
                if ( $success ) {
                    $response = array_merge( array(
                        'status' => 'success',
                    ), $ui_fields );
                } else {
                    $response = array_merge( array(
                        'status' => 'failed',
                    ), $ui_fields );
                }
                wp_send_json( $response );
            }
        }
    }

}
