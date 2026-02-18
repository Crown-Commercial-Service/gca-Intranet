<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Updates module
 *
 * @since 6.9.5
 */
class Disable_Author_Archives {

    /**
     * Redirect author archives to 404
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-author-archives/trunk/disable-author-archives.php
     * @since 7.9.0
     */
    public function redirect_to_404() {
        if ( isset( $_GET['author'] ) || is_author() ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            nocache_headers();
        }
    }
    
    /**
     * Remove 'View' link in user table action rows
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-author-archives/trunk/disable-author-archives.php
     * @since 7.9.0
     */
    public function remove_user_view_action( $actions ) {
        if ( isset( $actions['view'] ) ) {
            unset( $actions['view'] );
        }

        return $actions;        
    }

    /**
     * Disable linking from frontend post author name to the author archive
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-author-archives/trunk/disable-author-archives.php
     * @since 7.9.0
     */
    public function disable_frontend_author_link() {
        return '#';
    }

    /**
     * Remove users from the sitemap
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-author-archives/trunk/disable-author-archives.php
     * @since 7.9.0
     */
    public function remove_users_from_sitemap( $provider, $name ) {
        if ( $name === 'users' ) {
            return false;
        }

        return $provider;
    }
    
    /**
     * Disable rewrite rules for authors
     * 
     * @link https://plugins.trac.wordpress.org/browser/xo-security/tags/3.10.4/inc/class-xo-security.php#L886
     * @since 7.9.0
     */
    public function disable_rewrite_rules_for_authors() {
        return array();
    }
    
}