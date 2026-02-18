<?php

namespace ASENHA\Classes;

/**
 * Class for Multiple User Roles module
 *
 * @since 6.9.5
 */
class Multiple_User_Roles {
    
    /**
     * Add UI to enable multiple user roles selection
     *
     * @since 4.8.0
     */
    public function add_multiple_roles_ui( $user ) {
        // Get user roles that the current user is allowed to edit
        $roles = get_editable_roles();

        $current_user = wp_get_current_user();
        $current_user_roles = array_intersect( array_values( $current_user->roles ), array_keys( $roles ) ); // indexed array of role slugs

        // Get the roles of the user being shown / edited / created
        if ( ! empty( $user->roles ) ) {
            $user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) ); // indexed array of role slugs
        } else {
            $user_roles = array();
        }

        // Only show roles checkboxes for users that can assign roles to other users
        if ( current_user_can( 'promote_users', get_current_user_id() ) ) {

            ?>
            <div class="asenha-roles-temporary-container">
                <table class="form-table">
                    <tr>
                        <th>
                            <label>Roles</label>
                        </th>
                        <td>
                            <?php
                            foreach ( $roles as $role_slug => $role_info ) {

                                $checkbox_id = $role_slug . '_role';
                                $role_name = translate_user_role( $role_info['name'] );
                                if ( ! empty( $user_roles ) && in_array( $role_slug, $user_roles ) ) {
                                    $checked = 'checked="checked"';
                                } else {
                                    $checked = '';
                                }
                                
                                // We disable the 'Administrator' checkbox so it could not be unchecked and cause user to lose administrator priviledges without a way to restore it
                                $disabled = '';

                                if ( 'administrator' == $role_slug 
                                    && is_object( $user )
                                    && is_object( $current_user )
                                ) {
                                    if ( property_exists( $user, 'ID' )
                                    && property_exists( $current_user, 'ID' )
                                    && $user->ID == $current_user->ID
                                    ) {
                                        $disabled = 'disabled ';
                                    }
                                }
                                
                                // Output roles checkboxes
                                ?>
                                <label for="<?php esc_attr_e( $checkbox_id ); ?>"><input type="checkbox" id="<?php esc_attr_e( $checkbox_id ); ?>" value="<?php esc_attr_e( $role_slug ); ?>" name="asenha_assigned_roles[]" <?php esc_attr_e( $checked ); ?> <?php esc_attr_e( $disabled ); ?>/> <?php esc_html_e( $role_name ); ?></label><br />
                                <?php

                            }

                            wp_nonce_field( 'asenha_set_multiple_roles', 'asenha_multiple_roles_nonce' );

                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            <?php

        }

    }

    /**
     * Save changes in roles assignment
     *
     * @since 4.8.0
     */
    public function save_roles_assignment( $user_id ) {
        
        if ( ! isset( $_POST['asenha_multiple_roles_nonce'] ) ) {
            return;
        }

        if ( ! current_user_can( 'promote_users', get_current_user_id() ) || ! wp_verify_nonce( $_POST['asenha_multiple_roles_nonce'], 'asenha_set_multiple_roles' ) ) {
            return;
        }

        // Get user roles that the current user is allowed to edit
        $roles = get_editable_roles();

        $current_user = wp_get_current_user();
        $current_user_roles = array_intersect( array_values( $current_user->roles ), array_keys( $roles ) ); // indexed array of role slugs

        // Get the roles of the user being shown / edited / created
        $user = get_user_by( 'id', (int) $user_id ); // WP_User object
        $user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) ); // Current/existing roles

        if ( ! empty( $_POST['asenha_assigned_roles'] ) ) {

            $assigned_roles = array_map( 'sanitize_text_field', $_POST['asenha_assigned_roles'] );

            // Make sure only valid roles are processed
            $assigned_roles = array_intersect( $assigned_roles, array_keys( $roles ) );

            $roles_to_remove = array();
            $roles_to_add = array();

            if ( empty( $assigned_roles ) ) {

                // Remove all current/existing roles
                $roles_to_remove = $user_roles;

            } else {

                // Identify and remove roles not present in the newly assigned roles
                $roles_to_remove = array_diff( $user_roles, $assigned_roles );

                if ( ! empty( $roles_to_remove ) ) {
                    foreach ( $roles_to_remove as $role_to_remove ) {
                        $user->remove_role( $role_to_remove );
                    }
                }

                // Identify and add roles not present in the existing roles
                $roles_to_add = array_diff( $assigned_roles, $user_roles );

                if ( $user->ID == $current_user->ID ) {
                    $roles_to_add[] = 'administrator';
                }

                if ( ! empty( $roles_to_add ) ) {
                    foreach ( $roles_to_add as $role_to_add ) {
                        $user->add_role( $role_to_add );
                    }
                }

            }

        }

    }

}