<?php

namespace ASENHA\Classes;

/**
 * Class for Redirect After Login module
 *
 * @since 6.9.5
 */
class Redirect_After_Login {
    /**
     * Redirect to custom internal URL after login for user roles
     *
     * @param string $username Username.
     * @param object $user     Logged-in user's data.
     * @since 1.5.0
     */
    public function redirect_after_login( $username, $user ) {
        $redirect_url = $this->get_redirect_url_for_user( $user );
        if ( !empty( $redirect_url ) ) {
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Apply custom redirect URL after login completes (e.g. after 2FA validation).
     *
     * @param string         $redirect_to           The redirect destination URL.
     * @param string         $requested_redirect_to The requested redirect destination URL passed as a parameter.
     * @param WP_User|object $user                  WP_User object if login was successful.
     * @return string
     * @since 8.2.4
     */
    public function filter_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        $custom_redirect_url = $this->get_redirect_url_for_user( $user );
        if ( !empty( $custom_redirect_url ) ) {
            return $custom_redirect_url;
        }
        return $redirect_to;
    }

    /**
     * Redirect all applicable user roles to a single URL
     *
     * @param string $username Username.
     * @param object $user     Logged-in user's data.
     * @since 7.3.3
     */
    public function redirect_to_single_url( $username, $user ) {
        $redirect_url = $this->get_redirect_url_for_user( $user );
        if ( !empty( $redirect_url ) ) {
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Get the custom redirect URL for a user based on module settings and role.
     *
     * @param WP_User|object $user Logged-in user's data.
     * @return string Custom redirect URL, or empty string if no redirect applies.
     * @since 8.2.4
     */
    public function get_redirect_url_for_user( $user ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( !isset( $user->roles ) || !is_array( $user->roles ) ) {
            return '';
        }
        $current_user_roles = $user->roles;
        $redirect_after_login_to_slug_raw = ( isset( $options['redirect_after_login_to_slug'] ) ? $options['redirect_after_login_to_slug'] : '' );
        $relative_path = $this->get_redirect_relative_path( $redirect_after_login_to_slug_raw );
        $redirect_after_login_for = ( isset( $options['redirect_after_login_for'] ) ? $options['redirect_after_login_for'] : array() );
        if ( !isset( $redirect_after_login_for ) || count( $redirect_after_login_for ) <= 0 ) {
            return '';
        }
        $roles_for_custom_redirect = array();
        foreach ( $redirect_after_login_for as $role_slug => $custom_redirect ) {
            if ( $custom_redirect ) {
                $roles_for_custom_redirect[] = $role_slug;
            }
        }
        foreach ( $current_user_roles as $role ) {
            if ( in_array( $role, $roles_for_custom_redirect, true ) ) {
                return home_url( $relative_path );
            }
        }
        return '';
    }

    /**
     * Get the relative path to redirect to based on the raw redirect slug
     *
     * @since 7.3.3
     */
    public function get_redirect_relative_path( $redirect_after_login_to_slug_raw ) {
        if ( !empty( $redirect_after_login_to_slug_raw ) ) {
            $redirect_after_login_to_slug = trim( trim( $redirect_after_login_to_slug_raw ), '/' );
            if ( false !== strpos( $redirect_after_login_to_slug, '#' ) || false !== strpos( $redirect_after_login_to_slug, '?' ) || false !== strpos( $redirect_after_login_to_slug, '.php' ) || false !== strpos( $redirect_after_login_to_slug, '.html' ) ) {
                $slug_suffix = '';
            } else {
                $slug_suffix = '/';
            }
            $relative_path = $redirect_after_login_to_slug . $slug_suffix;
        } else {
            $relative_path = '';
        }
        return $relative_path;
    }

}
