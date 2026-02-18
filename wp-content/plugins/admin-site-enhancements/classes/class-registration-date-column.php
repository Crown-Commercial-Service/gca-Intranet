<?php

namespace ASENHA\Classes;

/**
 * Class for Last Login Column module
 *
 * @since 7.6.0
 */
class Registration_Date_Column {
    /**
     * Add registration date column
     * 
     * @since 7.6.0
     */
    public function add_registration_date_column( $columns ) {
        $columns['asenha_registered'] = __( 'Registered', 'admin-site-enhancements' );
        return $columns;
    }

    /**
     * Display registration date for each user
     * 
     * @since 7.6.0
     */
    public function display_registration_date( $output, $column_name, $user_id ) {
        $user = get_userdata( $user_id );
        $user_registered_unix = strtotime( $user->user_registered );
        if ( 'asenha_registered' === $column_name ) {
            $date_format = ( !empty( get_option( 'date_format' ) ) ? get_option( 'date_format' ) : 'F j, Y' );
            $time_format = ( !empty( get_option( 'time_format' ) ) ? get_option( 'time_format' ) : 'g:i a' );
            if ( function_exists( 'wp_date' ) ) {
                $output = wp_date( $date_format . ' ' . $time_format, $user_registered_unix );
            } else {
                $output = date_i18n( $date_format . ' ' . $time_format, $user_registered_unix );
            }
        }
        return $output;
    }

}
