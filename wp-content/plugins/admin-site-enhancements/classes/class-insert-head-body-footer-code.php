<?php

namespace ASENHA\Classes;

/**
 * Class for Insert <head>, <body> and <footer> code module
 *
 * @since 6.9.5
 */
class Insert_Head_Body_Footer_Code {
    
    /**
     * Insert code before </head> tag
     *
     * @since 3.3.0
     */
    public function insert_head_code() {

        $this->insert_code( 'head' );

    }

    /**
     * Insert code after <body> tag
     *
     * @since 3.3.0
     */
    public function insert_body_code() {

        $this->insert_code( 'body' );
        
    }

    /**
     * Insert code in footer section before </body> tag
     *
     * @since 3.3.0
     */
    public function insert_footer_code() {

        $this->insert_code( 'footer' );
        
    }

    /**
     * Insert code
     *
     * @since 3.3.0
     */
    public function insert_code( $location ) {

        // Do not insert code in admin, feed, robots or trackbacks
        if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
            return;
        }

        // Get option that stores the code
        $options = get_option( ASENHA_SLUG_U, array() );

        if ( 'head' == $location ) {

            $code = array_key_exists( 'head_code', $options ) ? $options['head_code'] : '';

        }

        if ( 'body' == $location ) {

            $code = array_key_exists( 'body_code', $options ) ? $options['body_code'] : '';

        }

        if ( 'footer' == $location ) {

            $code = array_key_exists( 'footer_code', $options ) ? $options['footer_code'] : '';

        }

        $disable_code_unslash = array_key_exists( 'disable_code_unslash', $options ) ? $options['disable_code_unslash'] : false;

        // [TODO] Properly escape code
        if ( $disable_code_unslash ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $code . PHP_EOL;
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo wp_unslash( $code ) . PHP_EOL;
        }

    }

}