<?php

namespace ASENHA\Classes;

/**
 * Class for Custom Admin Footer Text module
 *
 * @since 6.9.5
 */
class Custom_Admin_Footer_Text {
    
    /**
     * Modify footer text
     *
     * @since 6.9.0
     */
    public function custom_admin_footer_text_left() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $custom_admin_footer_left = isset( $options['custom_admin_footer_left'] ) ? $options['custom_admin_footer_left'] : '';
        echo wp_kses_post( $custom_admin_footer_left );
    }

    /**
     * Change WP version number text in footer
     * 
     * @since 6.9.0
     */
    public function custom_admin_footer_text_right() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $custom_admin_footer_right = isset( $options['custom_admin_footer_right'] ) ? $options['custom_admin_footer_right'] : '';
        echo wp_kses_post( $custom_admin_footer_right );
    }
    
}