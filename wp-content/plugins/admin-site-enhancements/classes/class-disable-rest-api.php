<?php

namespace ASENHA\Classes;

use WP_Error;
/**
 * Class for Disable REST API module
 *
 * @since 6.9.5
 */
class Disable_REST_API {
    /**
     * REST API access gate (runs after core authentication handlers).
     *
     * Role checks use the logged-in auth cookie when core has cleared the current user
     * for requests without an X-WP-Nonce (see rest_cookie_check_errors at priority 100).
     *
     * @since 2.9.0
     *
     * @param WP_Error|null|true $errors Prior filter value from rest_authentication_errors.
     * @return WP_Error|null|true
     */
    public function disable_rest_api( $errors ) {
        // Respect prior filter callbacks — e.g. core incorrect_password or rest_cookie_invalid_nonce.
        if ( is_wp_error( $errors ) ) {
            return $errors;
        }
        $allow_rest_api_access = false;
        // Get the REST API route being requested,e.g. wp/v2/posts | altcha/v1/challenge (without preceding slash /)
        // Ref: https://developer.wordpress.org/reference/hooks/rest_authentication_errors/#comment-6463
        $route = ltrim( $GLOBALS['wp']->query_vars['rest_route'], '/' );
        $route = rtrim( $route, '/' );
        if ( empty( $route ) ) {
            // This is when visiting /wp-json root
            $allow_rest_api_access = false;
        } elseif ( false !== strpos( $route, 'altcha/v1' ) || false !== strpos( $route, 'two-factor/1.0' ) || in_array( 'contact-form-7/wp-contact-form-7.php', get_option( 'active_plugins', array() ) ) && false !== strpos( $route, 'contact-form-7/' ) || in_array( 'the-events-calendar/the-events-calendar.php', get_option( 'active_plugins', array() ) ) && false !== strpos( $route, 'tribe/' ) ) {
            $allow_rest_api_access = true;
        } else {
        }
        $user = $this->get_user_for_rest_access_check();
        if ( $user->exists() ) {
            $allow_rest_api_access = true;
        }
        if ( !$allow_rest_api_access ) {
            return new WP_Error('rest_api_authentication_required', __( 'The REST API has been restricted to authenticated users.', 'admin-site-enhancements' ), array(
                'status' => rest_authorization_required_code(),
            ));
        }
        // Allow path: pass through unchanged so core's `true` is preserved.
        return $errors;
    }

    /**
     * User to evaluate for REST role allowlist.
     *
     * rest_cookie_check_errors (priority 100) may clear the current user when no REST nonce
     * is sent, so fall back to the logged-in auth cookie for role checks at priority 200.
     *
     * @since 8.8.2
     *
     * @return \WP_User
     */
    private function get_user_for_rest_access_check() {
        $user = wp_get_current_user();
        if ( $user->exists() ) {
            return $user;
        }
        $user_id = wp_validate_auth_cookie( '', 'logged_in' );
        if ( $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user instanceof \WP_User ) {
                return $user;
            }
        }
        return new \WP_User(0);
    }

}
