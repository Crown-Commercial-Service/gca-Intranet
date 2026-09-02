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

        $is_self = (
            is_object( $user )
            && is_object( $current_user )
            && isset( $user->ID )
            && isset( $current_user->ID )
            && (int) $user->ID === (int) $current_user->ID
        );
        $user_is_administrator = is_object( $user ) && in_array( 'administrator', (array) $user->roles, true );

        // Only show roles checkboxes for users that can assign roles to other users
        if ( current_user_can( 'promote_users', get_current_user_id() ) ) {

            ?>
            <div class="asenha-roles-temporary-container">
                <table class="form-table">
                    <tr class="asenha-multiple-roles-row">
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
                                
                                // On own profile: lock Administrator checkbox. Only emit a hidden retain field when
                                // the user already has administrator — never force-grant it to non-admins.
                                $disabled = '';
                                $lock_own_administrator = false;

                                if ( 'administrator' === $role_slug && $is_self ) {
                                    $disabled = 'disabled ';
                                    if ( $user_is_administrator ) {
                                        $lock_own_administrator = true;
                                    }
                                }
                                
                                // Output roles checkboxes
                                ?>
                                <label for="<?php esc_attr_e( $checkbox_id ); ?>"><input type="checkbox" id="<?php esc_attr_e( $checkbox_id ); ?>" value="<?php esc_attr_e( $role_slug ); ?>" name="asenha_assigned_roles[]" <?php esc_attr_e( $checked ); ?> <?php esc_attr_e( $disabled ); ?>/> <?php esc_html_e( $role_name ); ?></label><br />
                                <?php
                                if ( $lock_own_administrator ) {
                                    ?>
                                    <input type="hidden" name="asenha_assigned_roles[]" value="administrator" />
                                    <?php
                                }

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

        if ( ! $user ) {
            return;
        }

        $user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) ); // Current/existing roles

        $assigned_roles = array();

        if ( isset( $_POST['asenha_assigned_roles'] ) && is_array( $_POST['asenha_assigned_roles'] ) ) {
            $assigned_roles = array_map( 'sanitize_text_field', wp_unslash( $_POST['asenha_assigned_roles'] ) );
        }

        // Make sure only valid roles are processed
        $assigned_roles = array_intersect( $assigned_roles, array_keys( $roles ) );

        // Self-edit: retain administrator only if already held; otherwise strip it
        // so non-admins with promote_users cannot self-escalate via POST.
        $is_self               = ( (int) $user->ID === (int) $current_user->ID );
        $user_is_administrator = in_array( 'administrator', (array) $user->roles, true );

        if ( $is_self ) {
            if ( $user_is_administrator ) {
                $assigned_roles[] = 'administrator';
                $assigned_roles   = array_unique( $assigned_roles );
            } else {
                $assigned_roles = array_values( array_diff( $assigned_roles, array( 'administrator' ) ) );
            }
        }

        // Identify and remove roles not present in the newly assigned roles
        $roles_to_remove = array_diff( $user_roles, $assigned_roles );

        if ( ! empty( $roles_to_remove ) ) {
            foreach ( $roles_to_remove as $role_to_remove ) {
                $user->remove_role( $role_to_remove );
            }
        }

        // Identify and add roles not present in the existing roles
        $roles_to_add = array_diff( $assigned_roles, $user_roles );

        if ( ! empty( $roles_to_add ) ) {
            foreach ( $roles_to_add as $role_to_add ) {
                $user->add_role( $role_to_add );
            }
        }

    }

}