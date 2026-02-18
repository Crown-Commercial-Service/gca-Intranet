<?php

namespace ASENHA\Classes;

use Walker_Nav_Menu_Checklist;
/**
 * Class for Login Logout Menu module
 *
 * @since 6.9.5
 */
class Login_Logout_Menu {
    /**
     * Add metabox to Appearance >> Menus page for the login logout menu items
     *
     * @since 3.4.0
     */
    public function add_login_logout_metabox() {
        add_meta_box(
            'add-login-logout',
            __( 'Log In / Log Out', 'admin-site-enhancements' ),
            array($this, 'add_login_logout_menu_items'),
            'nav-menus',
            'side',
            'default'
        );
    }

    /**
     * Add menu items for the login logout metabox
     *
     * @since 3.4.0
     */
    public function add_login_logout_menu_items() {
        // The ID of the currently selected menu
        global $nav_menu_selected_id;
        $menu_items = array(
            'asenha-login'        => array(
                'title'   => __( 'Log In', 'admin-site-enhancements' ),
                'url'     => '#asenha-login',
                'classes' => array('asenha-login-menu-item'),
            ),
            'asenha-logout'       => array(
                'title'   => __( 'Log Out', 'admin-site-enhancements' ),
                'url'     => '#asenha-logout',
                'classes' => array('asenha-logout-menu-item'),
            ),
            'asenha-login-logout' => array(
                'title'   => __( 'Log In / Log Out', 'admin-site-enhancements' ),
                'url'     => '#asenha-login-logout',
                'classes' => array('asenha-login-logout-menu-item'),
            ),
        );
        $item_details = array(
            'db_id'            => 0,
            'object'           => 'asenha',
            'object_id'        => '',
            'menu_item_parent' => 0,
            'type'             => 'custom',
            'title'            => '',
            'url'              => '',
            'target'           => '',
            'attr_title'       => '',
            'classes'          => array(),
            'xfn'              => '',
        );
        $menu_items_object = array();
        foreach ( $menu_items as $item_id => $details ) {
            $menu_items_object[$details['title']] = (object) $item_details;
            $menu_items_object[$details['title']]->object_id = $item_id;
            $menu_items_object[$details['title']]->title = $details['title'];
            $menu_items_object[$details['title']]->classes = $details['classes'];
            $menu_items_object[$details['title']]->url = $details['url'];
        }
        $walker = new Walker_Nav_Menu_Checklist(array());
        ?>
        <div id="login-logout-links" class="loginlinksdiv">
            <div id="tabs-panel-login-logout-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
            <ul id="login-logout-links-checklist" class="list:login-logout-links categorychecklist form-no-clear">
                <?php 
        echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $menu_items_object ), 0, (object) array(
            'walker' => $walker,
        ) );
        ?>
            </ul>
            </div>
            <p class="button-controls">
                <span class="add-to-menu">
                    <input type="submit"<?php 
        disabled( $nav_menu_selected_id, 0 );
        ?> class="button-secondary submit-add-to-menu right" value="<?php 
        echo esc_attr( __( 'Add to Menu', 'admin-site-enhancements' ) );
        ?>" name="add-login-logout-links-menu-item" id="submit-login-logout-links" />
                    <span class="spinner"></span>
                </span>
            </p>
        </div>
        <?php 
    }

    /** 
     * Setup login logout URL based on login state
     * 
     * @since 3.4.0
     */
    public function set_login_logout_menu_item_dynamic_url( $menu_item ) {
        global $pagenow;
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( $pagenow != 'nav-menus.php' && !defined( 'DOING_AJAX' ) && isset( $menu_item->url ) && false !== strpos( $menu_item->url, 'asenha' ) ) {
            // Define login URL based on whether
            if ( array_key_exists( 'change_login_url', $options ) && $options['change_login_url'] ) {
                if ( array_key_exists( 'custom_login_slug', $options ) && !empty( $options['custom_login_slug'] ) ) {
                    $login_page_url = get_site_url() . '/' . $options['custom_login_slug'];
                }
            } else {
                $login_page_url = wp_login_url();
            }
            $logout_redirect_url = home_url();
            switch ( $menu_item->url ) {
                case '#asenha-login':
                    $menu_item->url = $login_page_url;
                    break;
                case '#asenha-logout':
                    $menu_item->url = wp_logout_url();
                    break;
                case '#asenha-login-logout':
                    $login_text = __( 'Log In', 'admin-site-enhancements' );
                    $logout_text = __( 'Log Out', 'admin-site-enhancements' );
                    $menu_item->url = ( is_user_logged_in() ? wp_logout_url() : $login_page_url );
                    $menu_item->title = ( is_user_logged_in() ? $logout_text : $login_text );
                    break;
            }
        }
        return $menu_item;
    }

    /**
     * Conditionally remove login or logout menu item based on is_user_logged_in()
     *
     * @since 3.4.0
     */
    public function maybe_remove_login_or_logout_menu_item( $sorted_menu_items ) {
        foreach ( $sorted_menu_items as $menu => $item ) {
            $item_classes = $item->classes;
            // Maybe remove Log In menu item
            if ( in_array( 'asenha-login-menu-item', $item_classes ) ) {
                if ( is_user_logged_in() ) {
                    unset($sorted_menu_items[$menu]);
                }
            }
            // Maybe remove Log Out menu item
            if ( in_array( 'asenha-logout-menu-item', $item_classes ) ) {
                if ( !is_user_logged_in() ) {
                    unset($sorted_menu_items[$menu]);
                }
            }
        }
        return $sorted_menu_items;
    }

}
