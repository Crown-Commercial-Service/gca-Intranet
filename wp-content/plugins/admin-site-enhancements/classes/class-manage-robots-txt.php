<?php

namespace ASENHA\Classes;

/**
 * Class for Manage robots.txt module
 *
 * @since 6.9.5
 */
class Manage_Robots_Txt {

    /**
     * Maybe show custom robots.txt content
     *
     * @since 3.5.0
     */
    public function maybe_show_custom_robots_txt_content( $output, $public ) {

        $options = get_option( ASENHA_SLUG_U, array() );

        if ( array_key_exists( 'robots_txt_content', $options ) && ! empty( $options['robots_txt_content'] ) ) {

            $output = wp_strip_all_tags( $options['robots_txt_content'] );

        }

        return $output;

    }
    
}