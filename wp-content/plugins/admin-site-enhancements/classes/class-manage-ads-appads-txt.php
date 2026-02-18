<?php

namespace ASENHA\Classes;

/**
 * Class for Manage ads.txt and app-ads.txt module
 *
 * @since 6.9.5
 */
class Manage_Ads_Appads_Txt {

    /** 
     * Show content of ads.txt saved to options
     *
     * @since 3.2.0
     */
    public function show_ads_appads_txt_content() {

        $options = get_option( ASENHA_SLUG_U, array() );

        $request = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : false;

        if ( '/ads.txt' === $request ) {

            $ads_txt_content = array_key_exists( 'ads_txt_content', $options ) ? $options['ads_txt_content'] : '';

            header( 'Content-Type: text/plain' );
            echo esc_textarea( $ads_txt_content );
            die();

        }

        if ( '/app-ads.txt' === $request ) {

            $app_ads_txt_content = array_key_exists( 'app_ads_txt_content', $options ) ? $options['app_ads_txt_content'] : '';

            header( 'Content-Type: text/plain' );
            echo esc_textarea( $app_ads_txt_content );
            die();

        }

    }
        
}