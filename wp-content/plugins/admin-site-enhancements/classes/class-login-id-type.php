<?php

namespace ASENHA\Classes;

use WP_Error;

/**
 * Class for Login ID Type module
 *
 * @since 6.9.5
 */
class Login_ID_Type {

    /**
     * Change default label on login form
     * 
     * @param array $defaults an array of default login form arguments
     * @link https://plugins.trac.wordpress.org/browser/xo-security/tags/3.7.1/inc/class-xo-security.php
     * @since 6.8.0
     */
    public function change_login_form_defaults( $defaults ) {
        $defaults['label_username'] = __( 'Username', 'admin-site-enhancements' );
        return $defaults;
    }
    
    /**
     * Filter for gettext.
     *
     * @param string $translation Translated text.
     * @param string $text        Text to translate.
     * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
     * @link https://plugins.trac.wordpress.org/browser/xo-security/tags/3.7.1/inc/class-xo-security.php
     * @since 6.8.0
     */
    public function gettext_login_id_username( $translation, $text, $domain ) {
        global $pagenow;
        if ( 'wp-login.php' === $pagenow ) {
            if ( 'default' === $domain && 'Username or Email Address' === $text ) {
                $translation = __( 'Username', 'admin-site-enhancements' );
            }
        }
        return $translation;
    }

    /**
     * Filter for authenticate.
     *
     * @param WP_User|Mixed $user user object if authenticated.
     * @param String        $username username.
     * @return WP_User|Mixed authenticated user or error.
     * @link https://plugins.trac.wordpress.org/browser/xo-security/tags/3.7.1/inc/class-xo-security.php
     * @since 6.8.0
     */
    public function authenticate_email( $user, $username ) {
        if ( null !== $user && ! is_wp_error( $user ) && strtolower( $user->user_email ) !== strtolower( $username ) ) {
            $user = new WP_Error( 'invalid_username', __( '<strong>Error:</strong> Invalid email or incorrect password.', 'admin-site-enhancements' ) );
        }
        return $user;
    }

    /**
     * Filter for gettext.
     *
     * @param string $translation Translated text.
     * @param string $text        Text to translate.
     * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
     * @return WP_User|Mixed authenticated user or error.
     * @link https://plugins.trac.wordpress.org/browser/xo-security/tags/3.7.1/inc/class-xo-security.php
     */
    public function gettext_login_id_email( $translation, $text, $domain ) {
        global $pagenow;
        if ( 'wp-login.php' === $pagenow ) {
            if ( 'default' === $domain && 'Username or Email Address' === $text ) {
                $translation = __( 'Email', 'admin-site-enhancements' );
            }
        }
        return $translation;
    }
    
}