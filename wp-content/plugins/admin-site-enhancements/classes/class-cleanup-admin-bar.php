<?php

namespace ASENHA\Classes;

/**
 * Class for Clean Up Admin Bar module
 *
 * @since 6.9.5
 */
class Cleanup_Admin_Bar {
    /**
     * Modify admin bar menu for Admin Interface >> Hide or Modify Elements feature
     *
     * @param $wp_admin_bar object The admin bar.
     * @link https://wordpress.stackexchange.com/a/12652
     * @since 1.9.0
     */
    public function modify_admin_bar_menu( $wp_admin_bar ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        // Hide WP Logo Menu
        if ( array_key_exists( 'hide_ab_wp_logo_menu', $options ) && $options['hide_ab_wp_logo_menu'] ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 10 );
            // priority needs to match default value. Use QM to reference.
        }
        // Hide home icon and site name
        if ( array_key_exists( 'hide_ab_site_menu', $options ) && $options['hide_ab_site_menu'] ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_site_menu', 30 );
            // priority needs to match default value. Use QM to reference.
        }
        // Hide Customize Menu
        if ( array_key_exists( 'hide_ab_customize_menu', $options ) && $options['hide_ab_customize_menu'] ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_customize_menu', 40 );
            // priority needs to match default value. Use QM to reference.
        }
        // Hide Updates Counter/Link
        if ( array_key_exists( 'hide_ab_updates_menu', $options ) && $options['hide_ab_updates_menu'] ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 50 );
            // priority needs to match default value. Use QM to reference.
        }
        // Hide Comments Counter/Link
        if ( array_key_exists( 'hide_ab_comments_menu', $options ) && $options['hide_ab_comments_menu'] ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
            // priority needs to match default value. Use QM to reference.
        }
        // Hide New Content Menu
        if ( array_key_exists( 'hide_ab_new_content_menu', $options ) && $options['hide_ab_new_content_menu'] ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );
            // priority needs to match default value. Use QM to reference.
        }
    }

    /**
     * Remove 'Howdy' from admin bar's account item
     *
     * @param $wp_admin_bar object The admin bar.
     * @link https://wordpress.stackexchange.com/a/12652
     * @since 7.3.1
     */
    public function remove_howdy( $wp_admin_bar ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        // Hide 'Howdy' text
        if ( array_key_exists( 'hide_ab_howdy', $options ) && $options['hide_ab_howdy'] ) {
            // Remove the whole my account sectino and later rebuild it
            remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_item', 7 );
            // Up to WP v6.5.5
            remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_item', 9991 );
            // Since WP v6.6
            $current_user = wp_get_current_user();
            $user_id = get_current_user_id();
            $profile_url = get_edit_profile_url( $user_id );
            $avatar = get_avatar( $user_id, 26 );
            // size 26x26 pixels
            $display_name = $current_user->display_name;
            $class = ( $avatar ? 'with-avatar' : 'no-avatar' );
            $wp_admin_bar->add_menu( array(
                'id'     => 'my-account',
                'parent' => 'top-secondary',
                'title'  => $display_name . $avatar,
                'href'   => $profile_url,
                'meta'   => array(
                    'class' => $class,
                ),
            ) );
        }
    }

    /**
     * Hide the Help tab and drawer
     *
     * @since 4.5.0
     */
    public function hide_help_drawer() {
        if ( is_admin() ) {
            $screen = get_current_screen();
            $screen->remove_help_tabs();
        }
    }

}
