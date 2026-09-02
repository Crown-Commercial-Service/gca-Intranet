<?php

namespace ASENHA\Classes;

/**
 * Class for Clean Up Admin Bar module
 *
 * @since 6.9.5
 */
class Cleanup_Admin_Bar {
    /**
     * Node ID prefix for Admin Bar Custom Elements (Pro). Must match generateItemId() in assets/premium/js/admin-bar-custom-elements.js.
     *
     * @var string
     */
    const ADMIN_BAR_CUSTOM_ELEMENTS_NODE_ID_PREFIX = 'asenha-ab-';

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
     * Remove 'Howdy' (or localized greeting) from admin bar's account item.
     *
     * Runs on wp_before_admin_bar_render (priority 100) so the final title is set
     * after third-party Howdy removers that comma-split the title and can shred
     * avatar srcset HTML (e.g. Divi Assistant's pac_da_custom_admin_bar_user_name).
     *
     * Does not remove_action wp_admin_bar_my_account_item or rebuild the account tree;
     * only updates the existing my-account node title/meta via add_node merge.
     *
     * @since 7.3.1
     * @since 8.9.x Avoid remove_action rebuild; set core-compatible title after third parties.
     */
    public function remove_howdy() {
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( !array_key_exists( 'hide_ab_howdy', $options ) || !$options['hide_ab_howdy'] ) {
            return;
        }
        global $wp_admin_bar;
        if ( !$wp_admin_bar instanceof \WP_Admin_Bar ) {
            return;
        }
        $node = $wp_admin_bar->get_node( 'my-account' );
        if ( !$node ) {
            return;
        }
        $current_user = wp_get_current_user();
        $user_id = get_current_user_id();
        $display_name = $current_user->display_name;
        $avatar = get_avatar( $user_id, 26 );
        // Core-compatible markup without the greeting; keeps display-name span + avatar intact.
        $title = '<span class="display-name">' . $display_name . '</span>' . $avatar;
        $wp_admin_bar->add_node( array(
            'id'    => 'my-account',
            'title' => $title,
            'meta'  => array(
                'menu_title' => $display_name,
            ),
        ) );
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
