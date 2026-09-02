<?php

namespace ASENHA\Classes;

/**
 * Class for View Admin as Role module
 *
 * @since 6.9.5
 */
class View_Admin_As_Role {

    /**
     * User meta key for hashed recovery token.
     *
     * @var string
     */
    const RESET_TOKEN_META_KEY = '_asenha_view_admin_as_reset_token';

    /**
     * User meta key for plain recovery token (settings display only).
     *
     * @var string
     */
    const PLAIN_TOKEN_META_KEY = '_asenha_view_admin_as_reset_plain';

    /**
     * Transient key prefix for displaying recovery URL after switching roles.
     *
     * @var string
     */
    const RECOVERY_URL_TRANSIENT_PREFIX = 'asenha_view_admin_as_recovery_';

    /**
     * Query argument for lockout recovery URL.
     *
     * @var string
     */
    const RECOVERY_QUERY_ARG = 'asenha-role-reset';

    /**
     * User meta key for pending recovery URL refreshed admin notice.
     *
     * @var string
     */
    const RECOVERY_NOTICE_URL_META_KEY = '_asenha_view_admin_as_recovery_notice_url';

    /**
     * Whether the current request is performing a plugin-initiated role switch.
     *
     * @var bool
     */
    private static $is_internal_role_switch = false;

    /**
     * Add menu bar item to view admin as one of the user roles
     *
     * @param $wp_admin_bar The WP_Admin_Bar instance
     * @link https://developer.wordpress.org/reference/hooks/admin_bar_menu/
     * @link https://developer.wordpress.org/reference/classes/wp_admin_bar/
     * @since 1.8.0
     */
    public function view_admin_as_admin_bar_menu( $wp_admin_bar ) {

        $options = get_option( ASENHA_SLUG_U, array() );
        $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();
        
        $current_user = wp_get_current_user();
        $current_user_roles = array_values( $current_user->roles ); // indexed array
        $current_user_username = $current_user->user_login;

        // Get which role slug is currently set to "View as"
        $viewing_admin_as = get_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', true );

        if ( empty( $viewing_admin_as ) ) {
            update_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', 'administrator' );
        }

        // Get the role name, translated if available, from the role slug
        $wp_roles = wp_roles()->roles;

        foreach ( $wp_roles as $wp_role_slug => $wp_role_info ) {

            if ( $wp_role_slug == $viewing_admin_as ) {

                $viewing_admin_as_role_name = $wp_role_info['name'];

            }

        }

        if ( ! isset( $viewing_admin_as_role_name ) ) {

            $viewing_admin_as_role_name = $viewing_admin_as;

        }

        $translated_name_for_viewing_admin_as = ucfirst( $viewing_admin_as_role_name );

        // Add parent menu based on the role being set to "View as"

        if ( 'administrator' == $viewing_admin_as ) {

            if ( in_array( 'administrator', $current_user_roles ) ) {

                // Add parent menu for administrators
                $wp_admin_bar->add_menu( array(
                    'id'        => 'asenha-view-admin-as-role',
                    'parent'    => 'top-secondary',
                    'title'     => 'View as <span style="font-size:0.8125em;">&#9660;</span>',
                    'href'      => '#',
                    'meta'      => array(
                        'title' => 'View admin pages and the site (logged-in) as one of the following user roles.'
                    ),
                ) );

            }

        } else {

            // Limit to users performing role switching only. i.e. Don't show role switcher to regularly logging in users.
            if ( in_array( $current_user_username, $usernames ) ) {

                // Add parent menu
                $wp_admin_bar->add_menu( array(
                    'id'        => 'asenha-view-admin-as-role',
                    'parent'    => 'top-secondary',
                    'title'     => 'Viewing as ' . $translated_name_for_viewing_admin_as . ' <span style="font-size:0.8125em;">&#9660;</span>',
                    'href'      => '#',
                ) );
                
            }

        }

        // Get available role(s) to switch to
        $roles_to_switch_to = $this->get_roles_to_switch_to();

        // Add role(s) to switch to as sub-menu

        if ( 'administrator' == $viewing_admin_as ) {

            if ( in_array( 'administrator', $current_user_roles ) ) {
                
                // Add submenu for each role other than Administrator

                $i = 1;

                foreach ( $roles_to_switch_to as $role_slug => $data ) {

                    $wp_admin_bar->add_menu( array(
                        'id'        => 'role' . $i . '_' . $role_slug, // id based on role slug, e.g. role1_editor, role5_shop_manager
                        'parent'    => 'asenha-view-admin-as-role',
                        'title'     => $data['role_name'], // role name, e.g. Editor, Shop Manager
                        'href'      => $data['nonce_url'], // nonce URL for each role
                    ) );

                    $i++;

                }

            }

        } else {

            // Add submenu to switch back to Administrator role
            // Limit to users performing role switching only. i.e. Don't show role switcher to regularly logging in users.

            if ( in_array( $current_user_username, $usernames ) ) {

                foreach ( $roles_to_switch_to as $role_slug => $data ) {

                    $wp_admin_bar->add_menu( array(
                        'id'        => 'role_' . $role_slug, // id based on role slug, e.g. role1_editor, role5_shop_manager
                        'parent'    => 'asenha-view-admin-as-role',
                        'title'     => 'Switch back to ' . $data['role_name'], // role name, e.g. Editor, Shop Manager
                        'href'      => $data['nonce_url'], // nonce URL for each role

                    ) );
                
                }
                
            }

        }

    }

    /** 
     * Get roles availble to switch to
     *
     * @since 1.8.0
     */
    private function get_roles_to_switch_to() {

        $current_user = wp_get_current_user();
        $current_user_role_slugs = $current_user->roles; // indexed array of current user role slug(s)

        // Get full list of roles defined in WordPress
        $wp_roles = wp_roles()->roles;

        $roles_to_switch_to = array();

        // Get which role slug is currently active for viewing
        $viewing_admin_as = get_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', true );

        if ( 'administrator' == $viewing_admin_as ) {

             // Exclude 'Administrator' from the "View as" menu

            foreach ( $wp_roles as $wp_role_slug => $wp_role_info ) {

                if ( ! in_array( $wp_role_slug,$current_user_role_slugs ) ) {

                    $roles_to_switch_to[$wp_role_slug] = array( 
                        'role_name' => $wp_role_info['name'], // role name, e.g. Editor, Shop Manager
                        'nonce_url' => wp_nonce_url(
                                            add_query_arg( array(
                                                'action'    => 'switch_role_to',
                                                'role'      => $wp_role_slug,
                                            ) ), // add query parameters to current URl, this is the $actionurl that will be appended with the nonce action
                                            'asenha_view_admin_as_' . $wp_role_slug, // the nonce $action name
                                            'nonce' // the nonce url parameter name
                                        ) // will result in a URL that looks like https://www.example.com/wp-admin/index.php?action=switch_role_to&role=editor&nonce=2ced3a40df
                    );

                }

            }

        } else {

            // Only show switch back to Administrator in the "View as" menu

            $roles_to_switch_to['administrator'] = array( 
                'role_name' => 'Administrator', // role name, e.g. Editor, Shop Manager
                'nonce_url' => wp_nonce_url(
                                    add_query_arg( array(
                                        'action'    => 'switch_back_to_administrator',
                                        'role'      => 'administrator',
                                    ) ), // add query parameters to current URl, this is the $actionurl that will be appended with the nonce action
                                    'asenha_view_admin_as_administrator', // the nonce $action name
                                    'nonce' // the nonce url parameter name
                                ) // will result in a URL that looks like https://www.example.com/wp-admin/index.php?action=switch_role_to&role=editor&nonce=2ced3a40df
            );
        }

        return $roles_to_switch_to; // array of $role_slug => $nonce_url

    }

    /**
     * Get the nonce URL for switching back to the administrator role.
     *
     * @since 8.8.5
     * @return string
     */
    private function get_switch_back_nonce_url() {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'switch_back_to_administrator',
                    'role'   => 'administrator',
                )
            ),
            'asenha_view_admin_as_administrator',
            'nonce'
        );
    }

    /**
     * Generate and store a hashed recovery token for lockout recovery.
     *
     * @since 8.8.5
     * @param int $user_id User ID.
     * @return string Plain recovery token for URL construction.
     */
    private function generate_recovery_token( $user_id ) {
        $plain_token = wp_generate_password( 43, false, false );
        update_user_meta( $user_id, self::RESET_TOKEN_META_KEY, wp_hash_password( $plain_token ) );
        update_user_meta( $user_id, self::PLAIN_TOKEN_META_KEY, $plain_token );
        set_transient( self::RECOVERY_URL_TRANSIENT_PREFIX . $user_id, $plain_token, DAY_IN_SECONDS );

        return $plain_token;
    }

    /**
     * Return an existing recovery token or generate one for the current admin.
     *
     * @since 8.8.5
     * @param int $user_id User ID.
     * @return string Plain recovery token, or empty string when not allowed.
     */
    public function ensure_recovery_token( $user_id ) {
        $user_id = (int) $user_id;

        if ( $user_id <= 0 || $user_id !== get_current_user_id() || ! current_user_can( 'manage_options' ) ) {
            return '';
        }

        $options = get_option( ASENHA_SLUG_U, array() );

        if ( empty( $options['view_admin_as_role'] ) ) {
            return '';
        }

        $plain_token = get_user_meta( $user_id, self::PLAIN_TOKEN_META_KEY, true );

        if ( ! empty( $plain_token ) && is_string( $plain_token ) && $this->verify_reset_token( $user_id, $plain_token ) ) {
            return $plain_token;
        }

        return $this->generate_recovery_token( $user_id );
    }

    /**
     * Verify a submitted recovery token for a user.
     *
     * @since 8.8.5
     * @param int    $user_id User ID.
     * @param string $token   Plain recovery token.
     * @return bool
     */
    private function verify_reset_token( $user_id, $token ) {
        if ( empty( $token ) || ! is_numeric( $user_id ) || $user_id <= 0 ) {
            return false;
        }

        $stored_hash = get_user_meta( $user_id, self::RESET_TOKEN_META_KEY, true );

        if ( empty( $stored_hash ) || ! is_string( $stored_hash ) ) {
            return false;
        }

        return wp_check_password( $token, $stored_hash, $user_id );
    }

    /**
     * Build the lockout recovery URL for a plain token.
     *
     * @since 8.8.5
     * @param string $plain_token Plain recovery token.
     * @return string
     */
    public function get_recovery_url_for_token( $plain_token ) {
        return add_query_arg(
            self::RECOVERY_QUERY_ARG,
            rawurlencode( $plain_token ),
            site_url( '/' )
        );
    }

    /**
     * Get the recovery URL to display on the settings screen for the current user.
     *
     * @since 8.8.5
     * @param int $user_id User ID.
     * @return string Recovery URL or instructional text.
     */
    public function get_recovery_url_for_settings( $user_id ) {
        $plain_token = $this->ensure_recovery_token( $user_id );

        if ( ! empty( $plain_token ) ) {
            return $this->get_recovery_url_for_token( $plain_token );
        }

        return __( 'Enable this module to generate your secret recovery URL.', 'admin-site-enhancements' );
    }

    /**
     * Find a user ID in the allowlist that matches a recovery token.
     *
     * @since 8.8.5
     * @param string $token Plain recovery token.
     * @return int User ID or 0 when not found.
     */
    private function get_user_id_by_reset_token( $token ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();

        if ( empty( $usernames ) || ! is_array( $usernames ) ) {
            return 0;
        }

        foreach ( $usernames as $username ) {
            $user = get_user_by( 'login', $username );

            if ( ! $user ) {
                continue;
            }

            if ( $this->verify_reset_token( $user->ID, $token ) ) {
                return (int) $user->ID;
            }
        }

        return 0;
    }

    /**
     * Restore a user's original roles and clear recovery state.
     *
     * @since 8.8.5
     * @param \WP_User $user User object.
     * @return bool
     */
    private function restore_original_roles_for_user( $user ) {
        if ( ! $user instanceof \WP_User ) {
            return false;
        }

        $current_user_role_slugs = $user->roles;
        $original_role_slugs = get_user_meta( $user->ID, '_asenha_view_admin_as_original_roles', true );

        if ( empty( $original_role_slugs ) || ! is_array( $original_role_slugs ) ) {
            return false;
        }

        self::$is_internal_role_switch = true;

        foreach ( $current_user_role_slugs as $current_role_slug ) {
            $user->remove_role( $current_role_slug );
        }

        foreach ( $original_role_slugs as $original_role_slug ) {
            $user->add_role( $original_role_slug );
        }

        self::$is_internal_role_switch = false;

        update_user_meta( $user->ID, '_asenha_viewing_admin_as', 'administrator' );
        $this->clear_view_admin_as_recovery_state( $user->ID );

        return true;
    }

    /**
     * Redirect to the login page after a token-based recovery.
     *
     * @since 8.8.5
     * @return void
     */
    private function redirect_after_token_recovery() {
        $options = get_option( ASENHA_SLUG_U, array() );

        if ( array_key_exists( 'change_login_url', $options ) && $options['change_login_url'] ) {
            if ( array_key_exists( 'custom_login_slug', $options ) && ! empty( $options['custom_login_slug'] ) )  {
                $login_url = get_site_url( null, $options['custom_login_slug'] );
            }
        }

        if ( empty( $login_url ) ) {
            $login_url = wp_login_url();
        }

        ?>
        <script>
            window.location.href='<?php echo esc_url( $login_url ); ?>';
        </script>
        <?php
        exit;
    }

    /**
     * Switch user role to view admin and site
     *
     * @since 1.8.0
     */
    public function role_switcher_to_view_admin_as() {

        $current_user = wp_get_current_user();
        $current_user_role_slugs = $current_user->roles; // indexed array of current user role slug(s)
        $current_user_username = $current_user->user_login;

        $options = get_option( ASENHA_SLUG_U, array() );
        $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();

        if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['role'] ) && isset( $_REQUEST['nonce'] ) ) {

            $action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
            $new_role = sanitize_text_field( wp_unslash( $_REQUEST['role'] ) );
            $nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) );
            
            if ( 'switch_role_to' === $action ) {

                // Check nonce validity and role existence

                $wp_roles = array_keys( wp_roles()->roles ); // indexed array of all WP roles

                if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
                    return;
                }

                if ( ! wp_verify_nonce( $nonce, 'asenha_view_admin_as_' . $new_role ) || ! in_array( $new_role, $wp_roles, true ) ) {
                    return; // cancel role switching
                }

                // Get original roles (before role switching) of the current user
                $original_role_slugs = get_user_meta( get_current_user_id(), '_asenha_view_admin_as_original_roles', true );

                // Store original user role(s) before switching it to another role
                
                if ( empty( $original_role_slugs ) ) {

                    update_user_meta( get_current_user_id(), '_asenha_view_admin_as_original_roles', $current_user_role_slugs );

                }

                $this->ensure_recovery_token( get_current_user_id() );

                // Store current user's username in options
                if ( ! in_array( $current_user_username, $usernames, true ) ) {
                    $usernames[] = $current_user_username;
                }
                $options['viewing_admin_as_role_are'] = array_values( array_unique( $usernames ) );
                update_option( ASENHA_SLUG_U, $options, true );
                
                self::$is_internal_role_switch = true;

                // Remove all current roles from current user.
                foreach ( $current_user_role_slugs as $current_user_role_slug ) {

                    $current_user->remove_role( $current_user_role_slug );

                }

                // Add new role to current user
                $current_user->add_role( $new_role );

                self::$is_internal_role_switch = false;

                // Mark that the user has switched to a non-administrator role
                update_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', $new_role );

                wp_safe_redirect( get_admin_url() );
                exit;

            }

            if ( 'switch_back_to_administrator' === $action ) {

                // Check nonce validity

                if ( ! is_user_logged_in() ) {
                    return;
                }

                if ( ! wp_verify_nonce( $nonce, 'asenha_view_admin_as_administrator' ) || ( $new_role != 'administrator' ) ) {
                    return; // cancel role switching
                }

                if ( ! in_array( $current_user_username, $usernames, true ) ) {
                    return;
                }

                self::$is_internal_role_switch = true;

                // Remove all current roles from current user.
                foreach ( $current_user_role_slugs as $current_role_slug ) {

                    $current_user->remove_role( $current_role_slug );

                }

                // Get original roles (before role switching) of the current user
                $original_role_slugs = get_user_meta( get_current_user_id(), '_asenha_view_admin_as_original_roles', true );
                
                // Add the original roles to the current user
                if ( ! empty( $original_role_slugs ) && is_array( $original_role_slugs ) ) {
                    foreach ( $original_role_slugs as $original_role_slug ) {

                        $current_user->add_role( $original_role_slug );

                    }
                }

                self::$is_internal_role_switch = false;

                // Mark that the user has switched back to an administrator role
                update_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', 'administrator' );
                $switch_back_user_id = get_current_user_id();
                $this->clear_view_admin_as_recovery_state( $switch_back_user_id );
                $plain_token  = $this->generate_recovery_token( $switch_back_user_id );
                $recovery_url = $this->get_recovery_url_for_token( $plain_token );
                update_user_meta( $switch_back_user_id, self::RECOVERY_NOTICE_URL_META_KEY, $recovery_url );

                wp_safe_redirect( get_admin_url() );
                exit;

            }

        } elseif ( isset( $_REQUEST[ self::RECOVERY_QUERY_ARG ] ) ) {

            $reset_token = sanitize_text_field( wp_unslash( $_REQUEST[ self::RECOVERY_QUERY_ARG ] ) );
            $reset_user_id = $this->get_user_id_by_reset_token( $reset_token );

            if ( $reset_user_id > 0 ) {
                $reset_user = get_user_by( 'id', $reset_user_id );

                if ( $reset_user && $this->restore_original_roles_for_user( $reset_user ) ) {
                    $this->redirect_after_token_recovery();
                }
            }

        }

    }
    
    /**
     * Clear View Admin as Role recovery state for a user.
     *
     * @since 8.8.5
     * @param int $user_id User ID.
     * @return void
     */
    public function clear_view_admin_as_recovery_state( $user_id ) {
        $user_id = (int) $user_id;

        if ( $user_id <= 0 ) {
            return;
        }

        $user = get_user_by( 'id', $user_id );

        if ( ! $user ) {
            return;
        }

        $username = $user->user_login;
        $options = get_option( ASENHA_SLUG_U, array() );
        $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();

        if ( ! empty( $usernames ) && is_array( $usernames ) ) {
            foreach ( $usernames as $key => $stored_username ) {
                if ( $username === $stored_username ) {
                    unset( $usernames[ $key ] );
                }
            }

            $options['viewing_admin_as_role_are'] = array_values( $usernames );
            update_option( ASENHA_SLUG_U, $options, true );
        }

        delete_user_meta( $user_id, '_asenha_viewing_admin_as' );
        delete_user_meta( $user_id, '_asenha_view_admin_as_original_roles' );
        delete_user_meta( $user_id, self::RESET_TOKEN_META_KEY );
        delete_user_meta( $user_id, self::PLAIN_TOKEN_META_KEY );
        delete_user_meta( $user_id, self::RECOVERY_NOTICE_URL_META_KEY );
        delete_transient( self::RECOVERY_URL_TRANSIENT_PREFIX . $user_id );
    }

    /**
     * Clear recovery state when a user's role is changed outside this module.
     *
     * @since 8.8.5
     * @param int      $user_id   User ID.
     * @param string   $role      New role.
     * @param string[] $old_roles Previous roles.
     * @return void
     */
    public function maybe_clear_view_admin_as_on_external_role_change( $user_id, $role = '', $old_roles = array() ) {
        unset( $role, $old_roles );

        if ( self::$is_internal_role_switch ) {
            return;
        }

        $viewing_admin_as = get_user_meta( $user_id, '_asenha_viewing_admin_as', true );

        if ( 'administrator' !== $viewing_admin_as && ! empty( $viewing_admin_as ) ) {
            $this->clear_view_admin_as_recovery_state( $user_id );
        }
    }

    /**
     * When changing a user's role via their profile edit screen, maybe we sbould remove the user's username from a list of usernames that can switch back to the administrator role. This addresses a vulnerability in a rare scenario disclosed by Pathstack.
     * 
     * @since 7.6.3
     */
    public function maybe_prevent_switchback_to_administrator( $user_id ) {
        if ( self::$is_internal_role_switch ) {
            return;
        }

        $viewing_admin_as = get_user_meta( $user_id, '_asenha_viewing_admin_as', true );

        if ( 'administrator' != $viewing_admin_as && ! empty( $viewing_admin_as ) ) {
            $this->clear_view_admin_as_recovery_state( $user_id );
        }
    }
    
    /**
     * Add floating button to reset the view/account back to the administrator
     * 
     * @since 6.1.3
     */
    public function add_floating_reset_button() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $admin_usernames_viewing_as_role = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        // Show for non-admins
        if ( ! current_user_can( 'manage_options' ) && in_array( $username, $admin_usernames_viewing_as_role, true ) ) {
            ?>
            <div id="role-view-reset">
                <a href="<?php echo esc_url( $this->get_switch_back_nonce_url() ); ?>" class="button button-primary"><?php esc_html_e( 'Switch back to Administrator', 'admin-site-enhancements' ); ?></a>
            </div>
            <?php
        }           
    }

    /**
     * Show admin notice with refreshed recovery URL after switching back while logged in.
     *
     * @since 8.8.5
     * @return void
     */
    public function maybe_show_recovery_url_refreshed_notice() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $recovery_url = get_user_meta( get_current_user_id(), self::RECOVERY_NOTICE_URL_META_KEY, true );

        if ( empty( $recovery_url ) || ! is_string( $recovery_url ) ) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible asenha-view-admin-as-role-recovery-notice" id="asenha-view-admin-as-role-recovery-notice">
            <p>
                <?php
                echo esc_html__(
                    'You switched back to the administrator role. Your secret recovery URL has been refreshed. Bookmark the new URL below before testing again.',
                    'admin-site-enhancements'
                );
                ?>
                <br /><strong><?php echo esc_html( $recovery_url ); ?></strong>
            </p>
        </div>
        <?php
    }

    /**
     * Dismiss the recovery URL refreshed admin notice for the current administrator.
     *
     * @since 8.8.5
     * @return void
     */
    public function dismiss_recovery_url_refreshed_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Insufficient permissions.', 'admin-site-enhancements' ),
                ),
                403
            );
        }

        check_ajax_referer( 'asenha-dismiss-view-admin-as-recovery-notice', 'nonce' );

        delete_user_meta( get_current_user_id(), self::RECOVERY_NOTICE_URL_META_KEY );

        wp_send_json_success();
    }

    /**
     * Enqueue the dismiss handler for the recovery URL refreshed admin notice.
     *
     * @since 8.8.5
     * @return void
     */
    public function enqueue_recovery_url_refreshed_notice_script() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $recovery_url = get_user_meta( get_current_user_id(), self::RECOVERY_NOTICE_URL_META_KEY, true );

        if ( empty( $recovery_url ) ) {
            return;
        }

        wp_enqueue_script( 'jquery' );
        wp_add_inline_script(
            'jquery',
            'jQuery(function($){$(document).on("click","#asenha-view-admin-as-role-recovery-notice .notice-dismiss",function(){$.post(ajaxurl,{action:"asenha_dismiss_view_admin_as_recovery_notice",nonce:' . wp_json_encode( wp_create_nonce( 'asenha-dismiss-view-admin-as-recovery-notice' ) ) . '});});});'
        );
    }

    /**
     * Show custom error page on switch failure, which causes inability to view admin dashboard/pages
     *
     * @since 1.8.0
     */
    public function custom_error_page_on_switch_failure( $callback ) {

        ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8; ?>" />
    <meta name="viewport" content="width=device-width">
    <title>WordPress Error</title>
    <style type="text/css">
        html {
            background: #f1f1f1;
        }
        body {
            background: #fff;
            border: 1px solid #ccd0d4;
            color: #444;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 2em auto;
            padding: 1em 2em;
            max-width: 700px;
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        }
        h1 {
            border-bottom: 1px solid #dadada;
            clear: both;
            color: #666;
            font-size: 24px;
            margin: 30px 0 0 0;
            padding: 0;
            padding-bottom: 7px;
        }
        #error-page {
            margin-top: 50px;
        }
        #error-page p,
        #error-page .wp-die-message {
            font-size: 14px;
            line-height: 1.5;
            margin: 20px 0;
        }
        #error-page code {
            font-family: Consolas, Monaco, monospace;
        }
        a {
            color: #0073aa;
        }
        a:hover,
        a:active {
            color: #006799;
        }
        a:focus {
            color: #124964;
            -webkit-box-shadow:
                0 0 0 1px #5b9dd9,
                0 0 2px 1px rgba(30, 140, 190, 0.8);
            box-shadow:
                0 0 0 1px #5b9dd9,
                0 0 2px 1px rgba(30, 140, 190, 0.8);
            outline: none;
        }
    </style>
</head>
<body id="error-page">
    <div class="wp-die-message">Something went wrong. Please try logging in.</div>
</body>
</html>
        <?php

    }
    
}
