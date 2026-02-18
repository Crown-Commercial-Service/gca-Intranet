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
     * Disable REST API for non-authenticated users. This is for WP v4.7 or later.
     *
     * @since 2.9.0
     */
    public function disable_rest_api( $errors ) {
        $allow_rest_api_access = false;
        // Get the REST API route being requested,e.g. wp/v2/posts | altcha/v1/challenge (without preceding slash /)
        // Ref: https://developer.wordpress.org/reference/hooks/rest_authentication_errors/#comment-6463
        $route = ltrim( $GLOBALS['wp']->query_vars['rest_route'], '/' );
        if ( empty( $route ) ) {
            // This is when visiting /wp-json root
            $allow_rest_api_access = false;
        } elseif ( false !== strpos( $route, 'altcha/v1' ) || in_array( 'contact-form-7/wp-contact-form-7.php', get_option( 'active_plugins', array() ) ) && false !== strpos( $route, 'contact-form-7/' ) || in_array( 'the-events-calendar/the-events-calendar.php', get_option( 'active_plugins', array() ) ) && false !== strpos( $route, 'tribe/' ) ) {
            $allow_rest_api_access = true;
        } else {
        }
        if ( is_user_logged_in() ) {
            $allow_rest_api_access = true;
        }
        if ( !$allow_rest_api_access ) {
            return new WP_Error('rest_api_authentication_required', __( 'The REST API has been restricted to authenticated users.', 'admin-site-enhancements' ), array(
                'status' => rest_authorization_required_code(),
            ));
        }
    }

}
