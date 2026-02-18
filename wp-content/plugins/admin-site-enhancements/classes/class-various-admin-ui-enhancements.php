<?php

namespace ASENHA\Classes;

/**
 * Class for Various Admin UI Enhancements module
 *
 * @since 7.0.2
 */
class Various_Admin_Ui_Enhancements {
    /**
     * Custom sort on the plugins listing to show active plugins first
     * 
     * @link https://plugins.trac.wordpress.org/browser/display-active-plugins-first/tags/1.1/display-active-plugins-first.php
     * @since 6.7.0
     */
    public function show_active_plugins_first() {
        global $wp_list_table, $status;
        if ( !in_array( $status, array(
            'active',
            'inactive',
            'recently_activated',
            'mustuse'
        ), true ) ) {
            if ( is_array( $wp_list_table->items ) ) {
                uksort( $wp_list_table->items, array($this, 'plugins_order_callback') );
            }
        }
    }

    /**
     * Reorder plugins list to show active ones first
     * 
     * @link https://plugins.trac.wordpress.org/browser/display-active-plugins-first/tags/1.1/display-active-plugins-first.php
     * @since 6.7.0
     */
    public function plugins_order_callback( $a, $b ) {
        global $wp_list_table;
        $items = $wp_list_table->items;
        $a_active = is_plugin_active( $a );
        $b_active = is_plugin_active( $b );
        if ( $a_active && !$b_active ) {
            return -1;
        } elseif ( !$a_active && $b_active ) {
            return 1;
        } else {
            if ( isset( $items[$a] ) && isset( $items[$b] ) ) {
                return strcasecmp( $items[$a]['Name'], $items[$b]['Name'] );
            }
        }
    }

}
