<?php

namespace ASENHA\Classes;

/**
 * Class for Site Identity on Login Page module
 *
 * @since 6.9.5
 */
class Site_Identity_On_Login_Page {

    /**
     * Use site icon as the login page icon, the one on top of the login form
     * 
     * @link https://plugins.trac.wordpress.org/browser/login-site-icon/trunk/login-site-icon.php
     * @since 6.0.0
     */
    public function use_site_icon_on_login() {
        if ( has_site_icon() ) { 
            ?>
            <style type="text/css">
                    .login h1 a,
                    .login h1.wp-login-logo a {
                            background-image: url('<?php site_icon_url( 180 ); ?>');
                    }
            </style>
            <?php
        }
    }

    /**
     * Use site icon URL as a link on the login page icon
     * 
     * @link https://plugins.trac.wordpress.org/browser/login-site-icon/trunk/login-site-icon.php
     * @since 6.0.0
     */
    public function use_site_url_on_login() {
        return get_site_url();
    }
        
}