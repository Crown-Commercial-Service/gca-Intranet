<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Restrict the REST API to authenticated users only.
 *
 * Unauthenticated requests to /wp-json/* receive a 401 response.
 * Logged-in users (including those using the block editor) are unaffected.
 */
add_filter( 'rest_authentication_errors', function ( $result ) {
    // If another filter has already set an authentication result, honour it.
    if ( null !== $result ) {
        return $result;
    }

    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            __( 'You must be logged in to access the REST API.', 'gca-intranet' ),
            array( 'status' => 401 )
        );
    }

    return $result;
} );
