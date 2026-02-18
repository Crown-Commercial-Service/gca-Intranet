<?php

namespace ASENHA\Classes;

/**
 * Class for Redirect After Login module
 *
 * @since 6.9.5
 */
class CAPTCHA_Protection {
    
    /**
     * Maybe keep original redirect
     * 
     * @since 7.8.0
     */
    public function maybe_keep_original_redirect( $username, $user ) {
        // Skip redirection if login is performed from a WooCommerce checkout page
        // This will ensure user is redirected back to the checkout page after successful login
        if ( isset( $_REQUEST['woocommerce-login-nonce'] ) 
            && isset( $_REQUEST['redirect'] )
            && wc_get_checkout_url() == $_REQUEST['redirect']
        ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit();
        }
    }
}