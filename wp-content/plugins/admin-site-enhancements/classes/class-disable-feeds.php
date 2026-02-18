<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Feeds module
 *
 * @since 6.9.5
 */
class Disable_Feeds {

    /**
     * Ensure /feed/ page outputs a 403 Forbidden header and message
     * 
     * @since 5.5.2
     */
    public function redirect_feed_to_403() {
        if ( is_feed() ) {
            status_header( 403 ); // Send an HTTP 403 Forbidden status header
            die( '403 Forbidden' ); // End execution and display a 403 Forbidden message
        }
    }

}