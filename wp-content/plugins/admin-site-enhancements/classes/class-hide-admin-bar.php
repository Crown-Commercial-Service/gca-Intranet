<?php

namespace ASENHA\Classes;

/**
 * Class for Hide Admin Bar module
 *
 * @since 6.9.5
 */
class Hide_Admin_Bar {
    /**
     * Hide admin bar on the frontend for the user roles selected
     *
     * @since 1.3.0
     */
    public function hide_admin_bar_for_roles_on_frontend() {
        $options = get_option( ASENHA_SLUG_U );
        $hide_admin_bar = $options['hide_admin_bar'];
        $for_roles_frontend = $options['hide_admin_bar_for'];
        $always_show_for_admins_on_frontend = ( isset( $options['hide_admin_bar_always_show_for_admins'] ) ? $options['hide_admin_bar_always_show_for_admins'] : false );
        $current_user = wp_get_current_user();
        $current_user_roles = (array) $current_user->roles;
        // single dimensional array of role slugs
        // User has no role, i.e. logged-out
        if ( count( $current_user_roles ) == 0 ) {
            return false;
            // hide admin bar
        }
        // User has role(s). Do further checks.
        // Maybe show admin bar if user is an administrator
        if ( in_array( 'administrator', $current_user_roles ) && $always_show_for_admins_on_frontend ) {
            return true;
        }
        if ( isset( $for_roles_frontend ) && count( $for_roles_frontend ) > 0 ) {
            // Assemble single-dimensional array of roles for which admin bar would be hidden
            $roles_admin_bar_hidden_frontend = array();
            foreach ( $for_roles_frontend as $role_slug => $admin_bar_hidden ) {
                if ( $admin_bar_hidden ) {
                    $roles_admin_bar_hidden_frontend[] = $role_slug;
                }
            }
            // Check if any of the current user roles is one for which admin bar should be hidden
            foreach ( $current_user_roles as $role ) {
                if ( in_array( $role, $roles_admin_bar_hidden_frontend ) ) {
                    return false;
                    // hide admin bar
                }
            }
        }
        return true;
        // show admin bar
    }

}
