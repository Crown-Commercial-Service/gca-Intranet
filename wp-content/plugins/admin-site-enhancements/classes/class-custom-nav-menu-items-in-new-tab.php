<?php

namespace ASENHA\Classes;

/**
 * Class for Allow Custom Menu Links to Open in New Tab module
 *
 * @since 6.9.5
 */
class Custom_Nav_Menu_Items_In_New_Tab {

    /**
     * Add "open in new tab" checkbox in custom nav menu item settings
     * 
     * @since 5.4.0
     */
    public function add_custom_nav_menu_open_in_new_tab_field( $item_id, $menu_item, $depth, $args ) {
                
        $target_blank = get_post_meta( $item_id, '_menu_item_target_blank', true );
        
        if ( 'custom' == $menu_item->object ) {         
        ?>
            <p class="field-target_blank description-wide">
                <label for="edit-menu-item-target-blank-<?php echo esc_attr( $item_id ); ?>">
                    <input type="checkbox" id="edit-menu-item-target-blank-<?php echo esc_attr( $item_id ); ?>" name="menu-item-target-blank[<?php echo esc_attr( $item_id ); ?>]" value="1" <?php checked( $target_blank, '1' ); ?> />
                    Open link in new tab and add rel="noopener noreferrer nofollow" attribute.
                </label>
            </p>
        <?php
        }
        
    }
    
    /**
     * Save status of "open in new tab" checkbox in custom nav menu item settings
     * 
     * @since 5.4.0
     */
    public function save_custom_nav_menu_open_in_new_tab_status( $menu_id, $menu_item_db_id, $args ) {
        
        if ( isset( $_POST['menu-item-target-blank'][$menu_item_db_id] ) ) {
            update_post_meta( $menu_item_db_id, '_menu_item_target_blank', '1'  );
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_target_blank' );
        }

    }
    
    /**
     * Add attributes to custom nav menu item on the frontend
     * 
     * @since 5.4.0
     */
    public function add_attributes_to_custom_nav_menu_item( $atts, $menu_item, $args ) {

        $target_blank = get_post_meta( $menu_item->ID, '_menu_item_target_blank', true );
        
        if ( $target_blank ) {
            $atts['target'] = '_blank';
            $atts['rel']    = 'noopener noreferrer nofollow';
        }
        
        return $atts;
        
    }
        
}