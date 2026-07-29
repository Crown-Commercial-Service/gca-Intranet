<?php

namespace ASENHA\Classes;

/**
 * Class for Hide Admin Bar module
 *
 * @since 6.9.5
 */
class Hide_Admin_Bar {
    /**
     * Whether the current user should see the frontend admin bar per Hide Admin Bar role rules.
     *
     * @since 8.9.0
     * @return bool True when the toolbar should display on the frontend.
     */
    public function is_frontend_admin_bar_visible_for_current_user() {
        $options = get_option( ASENHA_SLUG_U );
        if ( !is_array( $options ) ) {
            return false;
        }
        $for_roles_frontend = ( isset( $options['hide_admin_bar_for'] ) ? $options['hide_admin_bar_for'] : array() );
        $always_show_for_admins_on_frontend = ( isset( $options['hide_admin_bar_always_show_for_admins'] ) ? $options['hide_admin_bar_always_show_for_admins'] : false );
        $current_user = wp_get_current_user();
        $current_user_roles = (array) $current_user->roles;
        if ( count( $current_user_roles ) === 0 ) {
            return false;
        }
        if ( in_array( 'administrator', $current_user_roles, true ) && $always_show_for_admins_on_frontend ) {
            return true;
        }
        if ( isset( $for_roles_frontend ) && count( $for_roles_frontend ) > 0 ) {
            $roles_admin_bar_hidden_frontend = array();
            foreach ( $for_roles_frontend as $role_slug => $admin_bar_hidden ) {
                if ( $admin_bar_hidden ) {
                    $roles_admin_bar_hidden_frontend[] = $role_slug;
                }
            }
            foreach ( $current_user_roles as $role ) {
                if ( in_array( $role, $roles_admin_bar_hidden_frontend, true ) ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Hide admin bar on the frontend for the user roles selected
     *
     * @since 1.3.0
     * @param bool $show Default visibility from WordPress.
     * @return bool
     */
    public function hide_admin_bar_for_roles_on_frontend( $show = true ) {
        if ( !$show ) {
            return false;
        }
        return $this->is_frontend_admin_bar_visible_for_current_user();
    }

}
