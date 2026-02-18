<?php

namespace ASENHA\Classes;

/**
 * Class for Wider Admin Menu module
 *
 * @since 6.9.5
 */
class Wider_Admin_Menu {
    
    /**
     * Set custom admin menu width
     * 
     * @since 5.2.0
     */
    public function set_custom_menu_width() {

        $options = get_option( ASENHA_SLUG_U, array() );
        $custom_width = $options['admin_menu_width'];
        
        $wp_version = get_bloginfo( 'version' );
        if ( version_compare( $wp_version, '5', '>' ) ) {
            require_once ASENHA_PATH . 'includes/admin-menu-width/wp-v5-greater.php';
        } elseif ( version_compare( $wp_version, '4', '>=' ) ) {
            require_once ASENHA_PATH . 'includes/admin-menu-width/wp-v4-greater.php';           
        } else {}

    }
        
}