<?php

namespace ASENHA\Classes;

/**
 * Class for Redirect 404 module
 *
 * @since 6.9.5
 */
class Redirect_Fourofour {
    /**
     * Redirect 404 to homepage
     *
     * @since 1.7.0
     */
    public function redirect_404() {
        if ( !is_404() || is_admin() || defined( 'DOING_CRON' ) && DOING_CRON || defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            return;
        } elseif ( is_404() ) {
            $redirect_url = site_url();
            header( 'HTTP/1.1 301 Moved Permanently' );
            header( 'Location: ' . sanitize_url( $redirect_url ) );
            exit;
        } else {
        }
    }

}
