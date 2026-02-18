<?php

namespace ASENHA\Classes;

/**
 * Class for View Admin as Role module
 *
 * @since 6.9.5
 */
class View_Admin_As_Role {

    /**
     * Add menu bar item to view admin as one of the user roles
     *
     * @param $wp_admin_bar The WP_Admin_Bar instance
     * @link https://developer.wordpress.org/reference/hooks/admin_bar_menu/
     * @link https://developer.wordpress.org/reference/classes/wp_admin_bar/
     * @since 1.8.0
     */
    public function view_admin_as_admin_bar_menu( $wp_admin_bar ) {

        $options = get_option( ASENHA_SLUG_U, array() );
        $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();
        
        $current_user = wp_get_current_user();
        $current_user_roles = array_values( $current_user->roles ); // indexed array
        $current_user_username = $current_user->user_login;

        // Get which role slug is currently set to "View as"
        $viewing_admin_as = get_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', true );

        if ( empty( $viewing_admin_as ) ) {
            update_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', 'administrator' );
        }

        // Get the role name, translated if available, from the role slug
        $wp_roles = wp_roles()->roles;

        foreach ( $wp_roles as $wp_role_slug => $wp_role_info ) {

            if ( $wp_role_slug == $viewing_admin_as ) {

                $viewing_admin_as_role_name = $wp_role_info['name'];

            }

        }

        if ( ! isset( $viewing_admin_as_role_name ) ) {

            $viewing_admin_as_role_name = $viewing_admin_as;

        }

        $translated_name_for_viewing_admin_as = ucfirst( $viewing_admin_as_role_name );

        // Add parent menu based on the role being set to "View as"

        if ( 'administrator' == $viewing_admin_as ) {

            if ( in_array( 'administrator', $current_user_roles ) ) {

                // Add parent menu for administrators
                $wp_admin_bar->add_menu( array(
                    'id'        => 'asenha-view-admin-as-role',
                    'parent'    => 'top-secondary',
                    'title'     => 'View as <span style="font-size:0.8125em;">&#9660;</span>',
                    'href'      => '#',
                    'meta'      => array(
                        'title' => 'View admin pages and the site (logged-in) as one of the following user roles.'
                    ),
                ) );

            }

        } else {

            // Limit to users performing role switching only. i.e. Don't show role switcher to regularly logging in users.
            if ( in_array( $current_user_username, $usernames ) ) {

                // Add parent menu
                $wp_admin_bar->add_menu( array(
                    'id'        => 'asenha-view-admin-as-role',
                    'parent'    => 'top-secondary',
                    'title'     => 'Viewing as ' . $translated_name_for_viewing_admin_as . ' <span style="font-size:0.8125em;">&#9660;</span>',
                    'href'      => '#',
                ) );
                
            }

        }

        // Get available role(s) to switch to
        $roles_to_switch_to = $this->get_roles_to_switch_to();

        // Add role(s) to switch to as sub-menu

        if ( 'administrator' == $viewing_admin_as ) {

            if ( in_array( 'administrator', $current_user_roles ) ) {
                
                // Add submenu for each role other than Administrator

                $i = 1;

                foreach ( $roles_to_switch_to as $role_slug => $data ) {

                    $wp_admin_bar->add_menu( array(
                        'id'        => 'role' . $i . '_' . $role_slug, // id based on role slug, e.g. role1_editor, role5_shop_manager
                        'parent'    => 'asenha-view-admin-as-role',
                        'title'     => $data['role_name'], // role name, e.g. Editor, Shop Manager
                        'href'      => $data['nonce_url'], // nonce URL for each role
                    ) );

                    $i++;

                }

            }

        } else {

            // Add submenu to switch back to Administrator role
            // Limit to users performing role switching only. i.e. Don't show role switcher to regularly logging in users.

            if ( in_array( $current_user_username, $usernames ) ) {

                foreach ( $roles_to_switch_to as $role_slug => $data ) {

                    $wp_admin_bar->add_menu( array(
                        'id'        => 'role_' . $role_slug, // id based on role slug, e.g. role1_editor, role5_shop_manager
                        'parent'    => 'asenha-view-admin-as-role',
                        'title'     => 'Switch back to ' . $data['role_name'], // role name, e.g. Editor, Shop Manager
                        'href'      => $data['nonce_url'], // nonce URL for each role

                    ) );
                
                }
                
            }

        }

    }

    /** 
     * Get roles availble to switch to
     *
     * @since 1.8.0
     */
    private function get_roles_to_switch_to() {

        $current_user = wp_get_current_user();
        $current_user_role_slugs = $current_user->roles; // indexed array of current user role slug(s)

        // Get full list of roles defined in WordPress
        $wp_roles = wp_roles()->roles;

        $roles_to_switch_to = array();

        // Get which role slug is currently active for viewing
        $viewing_admin_as = get_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', true );

        if ( 'administrator' == $viewing_admin_as ) {

             // Exclude 'Administrator' from the "View as" menu

            foreach ( $wp_roles as $wp_role_slug => $wp_role_info ) {

                if ( ! in_array( $wp_role_slug,$current_user_role_slugs ) ) {

                    $roles_to_switch_to[$wp_role_slug] = array( 
                        'role_name' => $wp_role_info['name'], // role name, e.g. Editor, Shop Manager
                        'nonce_url' => wp_nonce_url(
                                            add_query_arg( array(
                                                'action'    => 'switch_role_to',
                                                'role'      => $wp_role_slug,
                                            ) ), // add query parameters to current URl, this is the $actionurl that will be appended with the nonce action
                                            'asenha_view_admin_as_' . $wp_role_slug, // the nonce $action name
                                            'nonce' // the nonce url parameter name
                                        ) // will result in a URL that looks like https://www.example.com/wp-admin/index.php?action=switch_role_to&role=editor&nonce=2ced3a40df
                    );

                }

            }

        } else {

            // Only show switch back to Administrator in the "View as" menu

            $roles_to_switch_to['administrator'] = array( 
                'role_name' => 'Administrator', // role name, e.g. Editor, Shop Manager
                'nonce_url' => wp_nonce_url(
                                    add_query_arg( array(
                                        'action'    => 'switch_back_to_administrator',
                                        'role'      => 'administrator',
                                    ) ), // add query parameters to current URl, this is the $actionurl that will be appended with the nonce action
                                    'asenha_view_admin_as_administrator', // the nonce $action name
                                    'nonce' // the nonce url parameter name
                                ) // will result in a URL that looks like https://www.example.com/wp-admin/index.php?action=switch_role_to&role=editor&nonce=2ced3a40df
            );
        }

        return $roles_to_switch_to; // array of $role_slug => $nonce_url

    }

    /**
     * Switch user role to view admin and site
     *
     * @since 1.8.0
     */
    public function role_switcher_to_view_admin_as() {

        $current_user = wp_get_current_user();
        $current_user_role_slugs = $current_user->roles; // indexed array of current user role slug(s)
        $current_user_username = $current_user->user_login;

        $options = get_option( ASENHA_SLUG_U, array() );
        $options['viewing_admin_as_role_are'] = array();

        if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['role'] ) && isset( $_REQUEST['nonce'] ) ) {

            $action = sanitize_text_field( $_REQUEST['action'] );
            $new_role = sanitize_text_field( $_REQUEST['role'] );
            $nonce = sanitize_text_field( $_REQUEST['nonce'] );
            
            if ( 'switch_role_to' === $action ) {

                // Check nonce validity and role existence

                $wp_roles = array_keys( wp_roles()->roles ); // indexed array of all WP roles

                if ( ! wp_verify_nonce( $nonce, 'asenha_view_admin_as_' . $new_role ) || ! in_array( $new_role, $wp_roles ) ) {
                    return; // cancel role switching
                }

                // Get original roles (before role switching) of the current user
                $original_role_slugs = get_user_meta( get_current_user_id(), '_asenha_view_admin_as_original_roles', true );

                // Store original user role(s) before switching it to another role
                
                if ( empty( $original_role_slugs ) ) {

                    update_user_meta( get_current_user_id(), '_asenha_view_admin_as_original_roles', $current_user_role_slugs );

                }
                
                // Store current user's username in options
                $options['viewing_admin_as_role_are'][] = $current_user_username;
                update_option( ASENHA_SLUG_U, $options, true );
                
                // Remove all current roles from current user.
                foreach ( $current_user_role_slugs as $current_user_role_slug ) {

                    $current_user->remove_role( $current_user_role_slug );

                }

                // Add new role to current user
                $current_user->add_role( $new_role );

                // Mark that the user has switched to a non-administrator role
                update_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', $new_role );

                // if ( ! in_array( $new_role, array( 'administrator', 'editor', 'author', 'contributor' ) ) ) {

                    // Redirect to profile edit page
                    // wp_safe_redirect( get_edit_profile_url() );
                    
                // } else {
                    
                    // Redirect to admin dashboard
                    wp_safe_redirect( get_admin_url() );

                // }

                exit;

            }

            if ( 'switch_back_to_administrator' === $action ) {

                // Check nonce validity

                if ( ! wp_verify_nonce( $nonce, 'asenha_view_admin_as_administrator' ) || ( $new_role != 'administrator' ) ) {
                    return; // cancel role switching
                }

                // Remove all current roles from current user.
                foreach ( $current_user_role_slugs as $current_role_slug ) {

                    $current_user->remove_role( $current_role_slug );

                }

                // Get original roles (before role switching) of the current user
                $original_role_slugs = get_user_meta( get_current_user_id(), '_asenha_view_admin_as_original_roles', true );
                
                // Add the original roles to the current user
                foreach ( $original_role_slugs as $original_role_slug ) {

                    $current_user->add_role( $original_role_slug );

                }

                // Remove current user's username from stored usernames. 
                $usernames = $options['viewing_admin_as_role_are'];
                foreach ( $usernames as $key => $username ) {
                    if ( $current_user_username == $username ) {
                        unset( $usernames[$key] );
                    }
                }
                $options['viewing_admin_as_role_are'] = $usernames;
                update_option( ASENHA_SLUG_U, $options, true );

                // Mark that the user has switched back to an administrator role
                update_user_meta( get_current_user_id(), '_asenha_viewing_admin_as', 'administrator' );

            }

        } elseif ( isset( $_REQUEST['reset-for'] ) ) {

            $reset_for_username = sanitize_text_field( $_REQUEST['reset-for'] );
            
            $options = get_option( ASENHA_SLUG_U, array() );
            $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();
            
            if ( ! empty( $reset_for_username ) ) {
                
                if ( in_array( $reset_for_username, $usernames ) ) {
                    
                    $current_user = get_user_by( 'login', $reset_for_username );
                    $current_user_role_slugs = $current_user->roles; // indexed array of current user role slug(s)
                    
                    // Remove all current roles from current user.
                    foreach ( $current_user_role_slugs as $current_role_slug ) {

                        $current_user->remove_role( $current_role_slug );

                    }

                    // Get original roles (before role switching) of the current user
                    $original_role_slugs = get_user_meta( $current_user->ID, '_asenha_view_admin_as_original_roles', true );
                    
                    // Add the original roles to the current user
                    foreach ( $original_role_slugs as $original_role_slug ) {

                        $current_user->add_role( $original_role_slug );

                    }

                    // Mark that the user has switched back to an administrator role
                    update_user_meta( $current_user->ID, '_asenha_viewing_admin_as', 'administrator' );

                    // Remove current user's username from stored usernames. 
                    foreach ( $usernames as $key => $username ) {
                        if ( $reset_for_username == $username ) {
                            unset( $usernames[$key] );
                        }
                    }
                    $options['viewing_admin_as_role_are'] = $usernames;
                    update_option( ASENHA_SLUG_U, $options, true );

                    // Redirect to login URL, including when custom login slug is set and active
                    if ( array_key_exists( 'change_login_url', $options ) && $options['change_login_url'] ) {
                        if ( array_key_exists( 'custom_login_slug', $options ) && ! empty( $options['custom_login_slug'] ) )  {
                            $login_url = get_site_url( null, $options['custom_login_slug'] );
                        }
                    } else {
                        $login_url = wp_login_url();                    
                    }
                    
                    // Redirect to admin dashboard
                    // wp_safe_redirect( $login_url );
                    // exit;

                    // Use JS redirect, which works more reliably on the frontend
                    ?>
                    <script>
                        window.location.href='<?php echo esc_url( $login_url ); ?>';
                    </script>
                    <?php

                }

            }

        }

    }
    
    /**
     * When changing a user's role via their profile edit screen, maybe we sbould remove the user's username from a list of usernames that can switch back to the administrator role. This addresses a vulnerability in a rare scenario disclosed by Pathstack.
     * 
     * @since 7.6.3
     */
    public function maybe_prevent_switchback_to_administrator( $user_id ) {        
        $viewing_admin_as = get_user_meta( $user_id, '_asenha_viewing_admin_as', true );

        if ( 'administrator' != $viewing_admin_as ) {
            $user = get_user_by( 'id', $user_id );
            $current_user_username = $user->user_login;

            // Remove current user's username from stored usernames. 
            // Once removed, that user won't be able to switch back to the administrator role from the ?reset-for=username URL
            $options = get_option( ASENHA_SLUG_U, array() );
            $usernames = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();

            if ( ! empty( $usernames ) ) {
                foreach ( $usernames as $key => $username ) {
                    if ( $current_user_username == $username ) {
                        unset( $usernames[$key] );
                    }
                }                

                $options['viewing_admin_as_role_are'] = $usernames;
                update_option( ASENHA_SLUG_U, $options, true );
            }

            // Delete user meta related to View Admin As Role module
            delete_user_meta( $user_id, '_asenha_viewing_admin_as' );
            delete_user_meta( $user_id, '_asenha_view_admin_as_original_roles' );            
        }
    }
    
    /**
     * Add floating button to reset the view/account back to the administrator
     * 
     * @since 6.1.3
     */
    public function add_floating_reset_button() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $admin_usernames_viewing_as_role = isset( $options['viewing_admin_as_role_are'] ) ? $options['viewing_admin_as_role_are'] : array();;
        $current_user = wp_get_current_user();
        $username = $current_user->user_login;

        // Show for non-admins
        if ( ! current_user_can( 'manage_options' ) && in_array( $username, $admin_usernames_viewing_as_role ) ) {          
            ?>
            <div id="role-view-reset">
                <a href="<?php echo esc_url( get_site_url() ); ?>/?reset-for=<?php echo esc_attr( $username ); ?>" class="button button-primary">Switch back to Administrator</a>
            </div>
            <?php
        }           
    }

    /**
     * Show custom error page on switch failure, which causes inability to view admin dashboard/pages
     *
     * @since 1.8.0
     */
    public function custom_error_page_on_switch_failure( $callback ) {

        ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8; ?>" />
    <meta name="viewport" content="width=device-width">
    <title>WordPress Error</title>
    <style type="text/css">
        html {
            background: #f1f1f1;
        }
        body {
            background: #fff;
            border: 1px solid #ccd0d4;
            color: #444;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 2em auto;
            padding: 1em 2em;
            max-width: 700px;
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        }
        h1 {
            border-bottom: 1px solid #dadada;
            clear: both;
            color: #666;
            font-size: 24px;
            margin: 30px 0 0 0;
            padding: 0;
            padding-bottom: 7px;
        }
        #error-page {
            margin-top: 50px;
        }
        #error-page p,
        #error-page .wp-die-message {
            font-size: 14px;
            line-height: 1.5;
            margin: 20px 0;
        }
        #error-page code {
            font-family: Consolas, Monaco, monospace;
        }
        a {
            color: #0073aa;
        }
        a:hover,
        a:active {
            color: #006799;
        }
        a:focus {
            color: #124964;
            -webkit-box-shadow:
                0 0 0 1px #5b9dd9,
                0 0 2px 1px rgba(30, 140, 190, 0.8);
            box-shadow:
                0 0 0 1px #5b9dd9,
                0 0 2px 1px rgba(30, 140, 190, 0.8);
            outline: none;
        }
    </style>
</head>
<body id="error-page">
    <div class="wp-die-message">Something went wrong. Please try logging in.</div>
</body>
</html>
        <?php

    }
    
}