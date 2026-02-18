<?php

namespace ASENHA\Classes;

/**
 * Class for Admin Menu Organizer module
 *
 * @since 6.9.5
 */
class Admin_Menu_Organizer {
    /**
     * Make the "Collapse Menu" toggler sticky at the bottom of the admin menu
     * 
     * @since 8.2.3
     */
    public function make_collapse_menu_item_sticky() {
        ?>
        <style>
            #adminmenu #collapse-menu {
                position: sticky;
                bottom: 0;
                background: #1d2327;
                z-index: 100;
                box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
                border-top: 1px solid #3c4349;
            }
            #adminmenuwrap {
                display: flex;
                flex-direction: column;
                height: 100%;
            }
            #adminmenu {
                display: flex;
                flex-direction: column;
                flex: 1;
                margin: 12px 0 0;
            }
            .folded #adminmenu #collapse-menu {
                position: sticky;
                bottom: 0;
                background: #1d2327;
            }
        </style>
        <?php 
    }

    /**
     * Add Admin Menu item under Settings menu
     * 
     * @since 7.8.5
     */
    public function add_menu_item() {
        add_submenu_page(
            'options-general.php',
            // Parent page/menu
            __( 'Admin Menu Settings', 'admin-site-enhancements' ),
            // Browser tab/window title
            __( 'Admin Menu', 'admin-site-enhancements' ),
            // Sube menu title
            'manage_options',
            // Minimal user capabililty
            'admin-menu-organizer',
            // Page slug. Shows up in URL.
            array($this, 'add_admin_menu_settings_page')
        );
    }

    /**
     * Create settings page for Admin Menu
     * 
     * @since 7.8.5
     */
    public function add_admin_menu_settings_page() {
        $render_field = new Settings_Fields_Render();
        ?>
        <div class="wrap admin-menu-organizer">
            <h1 class="wp-heading-inline"><?php 
        echo __( 'Admin Menu Organizer', 'admin-site-enhancements' );
        ?></h1>
            <div class="admin-menu-organizer-main">
                <div class="admin-menu-sortables-wrapper">
                    <?php 
        $render_field->render_sortable_menu();
        ?>
                </div>
                <div class="admin-menu-actions">
                    <button id="amo-save-changes" class="button button-primary button-large"><?php 
        echo __( 'Save Changes', 'admin-site-enhancements' );
        ?></button>
                    <div class="asenha-saving-changes" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2271b1" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="#2271b1" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg></div>
                    <div class="asenha-changes-saved" style="display:none;"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><path fill="seagreen" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zM9.29 16.29L5.7 12.7a.996.996 0 1 1 1.41-1.41L10 14.17l6.88-6.88a.996.996 0 1 1 1.41 1.41l-7.59 7.59a.996.996 0 0 1-1.41 0z"/></svg></div>
                </div>
            </div>
        </div>
        <?php 
    }

    /**
     * Render custom menu order
     *
     * @param $menu_order array an ordered array of menu items
     * @link https://developer.wordpress.org/reference/hooks/menu_order/
     * @since 2.0.0
     */
    public function render_custom_menu_order( $menu_order ) {
        global $menu;
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options = ( isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array() );
        // Get current menu order. We're not using the default $menu_order which uses index.php, edit.php as array values.
        $current_menu_order = array();
        foreach ( $menu as $menu_key => $menu_info ) {
            if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
                $menu_item_id = $menu_info[2];
            } else {
                $menu_item_id = $menu_info[5];
            }
            $current_menu_order[] = array($menu_item_id, $menu_info[2]);
        }
        // Get custom menu order
        $custom_menu_order = $options['custom_menu_order'];
        // comma separated
        $custom_menu_order = explode( ",", $custom_menu_order );
        // array of menu ID, e.g. menu-dashboard
        // Return menu order for rendering
        $rendered_menu_order = array();
        // Render menu based on items saved in custom menu order
        foreach ( $custom_menu_order as $custom_menu_item_id ) {
            foreach ( $current_menu_order as $current_menu_item_id => $current_menu_item ) {
                if ( $custom_menu_item_id == $current_menu_item[0] ) {
                    $rendered_menu_order[] = $current_menu_item[1];
                }
            }
        }
        // Add items from current menu not already part of custom menu order, e.g. new plugin activated and adds new menu item
        foreach ( $current_menu_order as $current_menu_item_id => $current_menu_item ) {
            if ( !in_array( $current_menu_item[0], $custom_menu_order ) ) {
                $rendered_menu_order[] = $current_menu_item[1];
            }
        }
        return $rendered_menu_order;
    }

    /**
     * Apply custom menu item titles
     *
     * @since 2.9.0
     */
    public function apply_custom_menu_item_titles() {
        global $menu;
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options = ( isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array() );
        // Get custom menu item titles
        $custom_menu_titles = $options['custom_menu_titles'];
        $custom_menu_titles = explode( ',', $custom_menu_titles );
        foreach ( $menu as $menu_key => $menu_info ) {
            if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
                $menu_item_id = $menu_info[2];
            } else {
                $menu_item_id = $menu_info[5];
            }
            // Get defaul/custom menu item title
            foreach ( $custom_menu_titles as $custom_menu_title ) {
                // At this point, $custom_menu_title value looks like toplevel_page_snippets__Code Snippets
                $custom_menu_title = explode( '__', $custom_menu_title );
                if ( $custom_menu_title[0] == $menu_item_id ) {
                    $menu_item_title = $custom_menu_title[1];
                    // e.g. Code Snippets
                    break;
                    // stop foreach loop so $menu_item_title is not overwritten in the next iteration
                } else {
                    $menu_item_title = $menu_info[0];
                }
            }
            $menu[$menu_key][0] = $menu_item_title;
        }
    }

    /**
     * Apply custom menu item titles
     *
     * @since 7.9.7
     */
    public function apply_custom_title_for_posts_menu() {
        global $wp_post_types;
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $admin_menu_options = ( isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array() );
        // $admin_menu_organizer = new ASENHA\Classes\Admin_Menu_Organizer();
        // For 'Posts' menu, if the title has been changed, try changing the labels for it everywhere
        $custom_menu_titles = explode( ',', $admin_menu_options['custom_menu_titles'] );
        foreach ( $custom_menu_titles as $custom_menu_title ) {
            if ( false !== strpos( $custom_menu_title, 'menu-posts__' ) ) {
                $custom_menu_title = explode( '__', $custom_menu_title );
                $posts_custom_title = $custom_menu_title[1];
                $posts_default_title = __( 'Posts', 'admin-site-enhancements' );
                if ( is_array( $wp_post_types ) ) {
                    if ( isset( $wp_post_types['post'] ) && property_exists( $wp_post_types['post'], 'label' ) ) {
                        $posts_default_title = $wp_post_types['post']->label;
                    }
                }
                if ( $posts_default_title != $posts_custom_title ) {
                    add_filter( 'post_type_labels_post', [$this, 'change_post_labels'] );
                    add_action( 'init', [$this, 'change_post_object_label'] );
                    add_action( 'admin_menu', [$this, 'change_post_menu_label'], PHP_INT_MAX );
                    add_action( 'admin_bar_menu', [$this, 'change_wp_admin_bar'], 80 );
                }
            }
        }
    }

    /**
     * Get custom title for 'Posts' menu item
     * 
     * @since 6.9.13
     */
    public function get_posts_custom_title() {
        $post_object = get_post_type_object( 'post' );
        // object
        $posts_default_title = '';
        if ( is_object( $post_object ) ) {
            if ( property_exists( $post_object, 'label' ) ) {
                $posts_default_title = $post_object->label;
            } else {
                $posts_default_title = $post_object->labels->name;
            }
        }
        $posts_custom_title = $posts_default_title;
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options = ( isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array() );
        $custom_menu_titles = ( isset( $options['custom_menu_titles'] ) ? explode( ',', $options['custom_menu_titles'] ) : array() );
        if ( !empty( $custom_menu_titles ) ) {
            foreach ( $custom_menu_titles as $custom_menu_title ) {
                if ( false !== strpos( $custom_menu_title, 'menu-posts__' ) ) {
                    $custom_menu_title = explode( '__', $custom_menu_title );
                    $posts_custom_title = $custom_menu_title[1];
                }
            }
        }
        return $posts_custom_title;
    }

    /**
     * For 'Posts', apply custom label
     * 
     * @link https://developer.wordpress.org/reference/hooks/post_type_labels_post_type/
     * @since 6.9.13
     */
    public function change_post_labels( $labels ) {
        $post_object = get_post_type_object( 'post' );
        // object
        $posts_default_title_plural = '';
        if ( is_object( $post_object ) ) {
            if ( property_exists( $post_object, 'label' ) ) {
                $posts_default_title_plural = $post_object->label;
            } else {
                $posts_default_title_plural = $post_object->labels->name;
            }
            $posts_default_title_singular = $post_object->labels->singular_name;
            $posts_custom_title = $this->get_posts_custom_title();
            foreach ( $labels as $key => $label ) {
                if ( null === $label ) {
                    continue;
                }
                $labels->{$key} = str_replace( [$posts_default_title_plural, $posts_default_title_singular], $posts_custom_title, $label );
            }
        }
        return $labels;
    }

    /**
     * For 'Posts', apply custom label in post object
     * 
     * @since 6.9.12
     */
    public function change_post_object_label() {
        global $wp_post_types;
        $posts_custom_title = $this->get_posts_custom_title();
        $labels =& $wp_post_types['post']->labels;
        $labels->name = $posts_custom_title;
        $labels->singular_name = $posts_custom_title;
        $labels->all_items = sprintf( 
            /* translators: %s is post type or taxonomy label */
            __( 'All %s', 'admin-site-enhancements' ),
            $posts_custom_title
         );
        $labels->add_new = __( 'Add New', 'admin-site-enhancements' );
        $labels->add_new_item = __( 'Add New', 'admin-site-enhancements' );
        $labels->edit_item = __( 'Edit', 'admin-site-enhancements' );
        $labels->new_item = $posts_custom_title;
        $labels->view_item = __( 'View', 'admin-site-enhancements' );
        $labels->search_items = sprintf( 
            /* translators: %s is post type or taxonomy label */
            __( 'Search %s', 'admin-site-enhancements' ),
            $posts_custom_title
         );
        $labels->not_found = sprintf( 
            /* translators: %s is the post type label */
            __( 'No %s found', 'admin-site-enhancements' ),
            strtolower( $posts_custom_title )
         );
        $labels->not_found_in_trash = sprintf( 
            /* translators: %s is the post type label */
            __( 'No %s found in Trash', 'admin-site-enhancements' ),
            strtolower( $posts_custom_title )
         );
    }

    /**
     * For 'Posts', apply custom label in menu and submenu
     * 
     * @since 6.9.12
     */
    public function change_post_menu_label() {
        global $submenu;
        $posts_custom_title = $this->get_posts_custom_title();
        if ( !empty( $posts_custom_title ) ) {
            $submenu['edit.php'][5][0] = sprintf( 
                /* translators: %s is post type or taxonomy label */
                __( 'All %s', 'admin-site-enhancements' ),
                $posts_custom_title
             );
        } else {
            $submenu['edit.php'][5][0] = sprintf( 
                /* translators: %s is post type or taxonomy label */
                __( 'All %s', 'admin-site-enhancements' ),
                $posts_default_title
             );
        }
    }

    /**
     * For 'Posts', apply custom label in admin bar
     * 
     * @since 6.9.12
     */
    public function change_wp_admin_bar( $wp_admin_bar ) {
        $posts_custom_title = $this->get_posts_custom_title();
        $new_post_node = $wp_admin_bar->get_node( 'new-post' );
        if ( $new_post_node ) {
            $new_post_node->title = $posts_custom_title;
            $wp_admin_bar->add_node( $new_post_node );
        }
    }

    /**
     * Hide parent menu items by adding class(es) to hide them
     *
     * @since 2.0.0
     */
    public function hide_menu_items() {
        global $menu;
        $common_methods = new Common_Methods();
        $menu_hidden_by_toggle = $common_methods->get_menu_hidden_by_toggle();
        // indexed array
        foreach ( $menu as $menu_key => $menu_info ) {
            if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
                $menu_item_id = $menu_info[2];
            } else {
                $menu_item_id = $menu_info[5];
            }
            // Append 'hidden' class to hide menu item until toggled
            if ( in_array( $menu_item_id, $menu_hidden_by_toggle ) ) {
                $menu[$menu_key][4] = $menu_info[4] . ' hidden asenha_hidden_menu';
            }
        }
    }

    /**
     * Add toggle to show hidden menu items
     *
     * @since 2.0.0
     */
    public function add_hidden_menu_toggle() {
        global $current_user;
        // Get menu items hidden by toggle
        $common_methods = new Common_Methods();
        $menu_hidden_by_toggle = $common_methods->get_menu_hidden_by_toggle();
        $submenu_hidden_by_toggle = array();
        // Get user capabilities the "Show All/Less" toggle should be shown for
        $user_capabilities_to_show_menu_toggle_for = $common_methods->get_user_capabilities_to_show_menu_toggle_for();
        // Get current user's capabilities from the user's role(s)
        $current_user_capabilities = '';
        $current_user_roles = $current_user->roles;
        // indexed array of role slugs
        foreach ( $current_user_roles as $current_user_role ) {
            $current_user_role_capabilities = get_role( $current_user_role )->capabilities;
            if ( is_array( $current_user_role_capabilities ) ) {
                $current_user_role_capabilities = array_keys( $current_user_role_capabilities );
                // indexed array
                $current_user_role_capabilities = implode( ",", $current_user_role_capabilities );
                $current_user_capabilities .= ',' . $current_user_role_capabilities;
            }
        }
        // Maybe show "Show All/Less" toggle
        $show_toggle_menu = false;
        if ( !empty( $current_user_capabilities ) ) {
            $current_user_capabilities = array_unique( explode( ",", $current_user_capabilities ) );
            foreach ( $user_capabilities_to_show_menu_toggle_for as $user_capability_to_show_menu_toggle_for ) {
                if ( in_array( $user_capability_to_show_menu_toggle_for, $current_user_capabilities ) ) {
                    $show_toggle_menu = true;
                    break;
                }
            }
        }
        if ( (!empty( $menu_hidden_by_toggle ) || !empty( $submenu_hidden_by_toggle )) && $show_toggle_menu ) {
            add_menu_page(
                __( 'Show All', 'admin-site-enhancements' ),
                __( 'Show All', 'admin-site-enhancements' ),
                'read',
                'asenha_show_hidden_menu',
                function () {
                    return false;
                },
                "dashicons-arrow-down-alt2",
                300
            );
            add_menu_page(
                __( 'Show Less', 'admin-site-enhancements' ),
                __( 'Show Less', 'admin-site-enhancements' ),
                'read',
                'asenha_hide_hidden_menu',
                function () {
                    return false;
                },
                "dashicons-arrow-up-alt2",
                301
            );
        }
    }

    /**
     * Script to toggle hidden menu itesm
     *
     * @since 2.0.0
     */
    public function enqueue_toggle_hidden_menu_script() {
        // Get menu items hidden by toggle
        $common_methods = new Common_Methods();
        $menu_hidden_by_toggle = $common_methods->get_menu_hidden_by_toggle();
        $submenu_hidden_by_toggle = array();
        if ( !empty( $menu_hidden_by_toggle ) || !empty( $submenu_hidden_by_toggle ) ) {
            // Script to set behaviour and actions of the sortable menu
            wp_enqueue_script(
                'asenha-toggle-hidden-menu',
                ASENHA_URL . 'assets/js/toggle-hidden-menu.js',
                array(),
                ASENHA_VERSION,
                false
            );
        }
    }

    /**
     * Save admin menu via AJAX
     * 
     * @since 6.3.1
     */
    public function save_admin_menu() {
        if ( isset( $_REQUEST ) ) {
            if ( check_ajax_referer( 'save-menu-nonce', 'nonce', false ) ) {
                $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
                $options = ( isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array() );
                $options['custom_menu_order'] = ( isset( $_REQUEST['custom_menu_order'] ) ? $_REQUEST['custom_menu_order'] : $options['custom_menu_order'] );
                $options['custom_menu_titles'] = ( isset( $_REQUEST['custom_menu_titles'] ) ? wp_unslash( $_REQUEST['custom_menu_titles'] ) : $options['custom_menu_titles'] );
                $options['custom_menu_hidden'] = ( isset( $_REQUEST['custom_menu_hidden'] ) ? $_REQUEST['custom_menu_hidden'] : $options['custom_menu_hidden'] );
                $options_extra['admin_menu'] = $options;
                // vi( $options_extra, '', 'save menu' );
                $updated = update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
                if ( $updated ) {
                    $response = array(
                        'status' => 'success',
                    );
                } else {
                    $response = array(
                        'status' => 'failed',
                    );
                }
                echo json_encode( $response );
            }
        }
    }

}
