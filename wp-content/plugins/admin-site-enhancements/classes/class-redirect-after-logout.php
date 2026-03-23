<?php

namespace ASENHA\Classes;

/**
 * Class for Redirect After Logout module
 *
 * @since 6.9.5
 */
class Redirect_After_Logout {
    /**
     * Redirect to custom internal URL after login for user roles
     *
     * @param string $redirect_to_url URL to redirect to. Default is admin dashboard URL.
     * @param string $origin_url URL the user is coming from.
     * @param object $user logged-in user's data.
     * @since 1.6.0
     */
    public function redirect_after_logout( $user_id ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $this->redirect_to_single_url( $user_id );
    }

    /**
     * Redirect all applicable user roles to a single URL
     * 
     * @since 7.3.3
     */
    public function redirect_to_single_url( $user_id ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $redirect_after_logout_to_slug_raw = ( isset( $options['redirect_after_logout_to_slug'] ) ? $options['redirect_after_logout_to_slug'] : '' );
        if ( !empty( $redirect_after_logout_to_slug_raw ) ) {
            $redirect_after_logout_to_slug = trim( trim( $redirect_after_logout_to_slug_raw ), '/' );
            if ( false !== strpos( $redirect_after_logout_to_slug, '#' ) || false !== strpos( $redirect_after_logout_to_slug, '?' ) || false !== strpos( $redirect_after_logout_to_slug, '.php' ) || false !== strpos( $redirect_after_logout_to_slug, '.html' ) ) {
                $relative_path = $redirect_after_logout_to_slug;
                // do not append slash at the end
            } else {
                $relative_path = $redirect_after_logout_to_slug . '/';
            }
        } else {
            $relative_path = '';
        }
        $redirect_url = get_site_url() . '/' . $relative_path;
        $redirect_after_logout_for = $options['redirect_after_logout_for'];
        $user = get_userdata( $user_id );
        if ( isset( $redirect_after_logout_for ) && count( $redirect_after_logout_for ) > 0 ) {
            // Assemble single-dimensional array of roles for which custom URL redirection should happen
            $roles_for_custom_redirect = array();
            foreach ( $redirect_after_logout_for as $role_slug => $custom_redirect ) {
                if ( $custom_redirect ) {
                    $roles_for_custom_redirect[] = $role_slug;
                }
            }
            // Does the user have roles data in array form?
            if ( isset( $user->roles ) && is_array( $user->roles ) ) {
                $current_user_roles = $user->roles;
            }
            // Redirect for roles set in the settings. Otherwise, leave redirect URL to the default, i.e. admin dashboard.
            foreach ( $current_user_roles as $role ) {
                if ( in_array( $role, $roles_for_custom_redirect ) ) {
                    wp_safe_redirect( $redirect_url );
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
    public function get_redirect_relative_path( $redirect_after_logout_to_slug_raw ) {
        if ( !empty( $redirect_after_logout_to_slug_raw ) ) {
            $redirect_after_logout_to_slug = trim( trim( $redirect_after_logout_to_slug_raw ), '/' );
            if ( false !== strpos( $redirect_after_logout_to_slug, '#' ) || false !== strpos( $redirect_after_logout_to_slug, '?' ) || false !== strpos( $redirect_after_logout_to_slug, '.php' ) || false !== strpos( $redirect_after_logout_to_slug, '.html' ) ) {
                $relative_path = $redirect_after_logout_to_slug;
                // do not append slash at the end
            } else {
                $relative_path = $redirect_after_logout_to_slug . '/';
            }
        } else {
            $relative_path = '';
        }
        return $relative_path;
    }

}
