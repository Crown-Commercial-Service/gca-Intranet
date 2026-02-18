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
     * @param string $redirect_to_url URL to redirect to. Default is admin dashboard URL.
     * @param string $origin_url URL the user is coming from.
     * @param object $user logged-in user's data.
     * @since 1.5.0
     */
    public function redirect_after_login( $username, $user ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $this->redirect_to_single_url( $username, $user );
    }

    /**
     * Redirect all applicable user roles to a single URL
     * 
     * @since 7.3.3
     */
    public function redirect_to_single_url( $username, $user ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $redirect_after_login_to_slug_raw = ( isset( $options['redirect_after_login_to_slug'] ) ? $options['redirect_after_login_to_slug'] : '' );
        $relative_path = $this->get_redirect_relative_path( $redirect_after_login_to_slug_raw );
        $redirect_after_login_for = ( isset( $options['redirect_after_login_for'] ) ? $options['redirect_after_login_for'] : array() );
        if ( isset( $redirect_after_login_for ) && count( $redirect_after_login_for ) > 0 ) {
            // Assemble single-dimensional array of roles for which custom URL redirection should happen
            $roles_for_custom_redirect = array();
            foreach ( $redirect_after_login_for as $role_slug => $custom_redirect ) {
                if ( $custom_redirect ) {
                    $roles_for_custom_redirect[] = $role_slug;
                }
            }
            // Does the user have roles data in array form?
            if ( isset( $user->roles ) && is_array( $user->roles ) ) {
                $current_user_roles = $user->roles;
            }
            // Set custom redirect URL for roles set in the settings. Otherwise, leave redirect URL to the default, i.e. admin dashboard.
            foreach ( $current_user_roles as $role ) {
                if ( in_array( $role, $roles_for_custom_redirect ) ) {
                    wp_safe_redirect( home_url( $relative_path ) );
                    exit;
                }
            }
        }
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
