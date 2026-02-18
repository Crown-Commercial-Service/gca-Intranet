<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Dashboard Widgets module
 *
 * @since 6.9.5
 */
class Disable_Dashboard_Widgets {

    /**
     * Disable dashboard widgets
     *
     * @since 4.2.0
     */
    public function disable_dashboard_widgets() {
    
        global $wp_meta_boxes;

        // Get list of disabled widgets
        $options = get_option( ASENHA_SLUG_U, array() );
        $disabled_dashboard_widgets = isset( $options['disabled_dashboard_widgets'] ) ? $options['disabled_dashboard_widgets'] : array();

        // Store default widgets in extra options. This will be referenced from settings field.
        $dashboard_widgets = $this->get_dashboard_widgets();
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options_extra['dashboard_widgets'] = $dashboard_widgets;
        update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );

        // Disable widgets
        if ( is_array( $disabled_dashboard_widgets ) || is_object( $disabled_dashboard_widgets ) ) {
            if ( ! empty( $disabled_dashboard_widgets ) ) {
                foreach( $disabled_dashboard_widgets as $disabled_widget_id_context_priority => $is_disabled ) {
                    // e.g. dashboard_activity__normal__core => true/false
                    if ( $is_disabled ) {
                        $disabled_widget = explode('__', $disabled_widget_id_context_priority);
                        $widget_id = $disabled_widget[0];
                        $widget_context = $disabled_widget[1];
                        $widget_priority = $disabled_widget[2];
                        // remove_meta_box( $widget_id, get_current_screen()->base, $widget_context );
                        unset( $wp_meta_boxes['dashboard'][$widget_context][$widget_priority][$widget_id] );
                    }
                }                
            }
        }

    }
    
    /**
     * Get dashboard widgets
     *
     * @since 4.2.0
     */
    public function get_dashboard_widgets() {

        global $wp_meta_boxes;

        $dashboard_widgets = array();

        if ( ! isset( $wp_meta_boxes['dashboard'] ) ) {
            $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
            if ( ! array_key_exists( 'dashboard_widgets', $options_extra ) ) {
                require_once ABSPATH . '/wp-admin/includes/dashboard.php';
                set_current_screen( 'dashboard' );
                wp_dashboard_setup();
            }
        }

        if ( isset( $wp_meta_boxes['dashboard'] ) ) {
            foreach( $wp_meta_boxes['dashboard'] as $context => $priorities ) {
                foreach ( $priorities as $priority => $widgets ) {
                    foreach( $widgets as $widget_id => $data ) {
                        $widget_title = ( isset( $data['title'] ) ) ? wp_strip_all_tags( preg_replace( '/ <span.*span>/im', '', $data['title'] ) ) : 'Undetectable';
                        $dashboard_widgets[$widget_id] = array(
                            'id'        => $widget_id,
                            'title'     => $widget_title,
                            'context'   => $context, // 'normal' or 'side'
                            'priority'  => $priority, // 'core'
                       );
                    }
                }
            }
        }

        $dashboard_widgets = wp_list_sort( $dashboard_widgets, 'title', 'ASC', true );

        return $dashboard_widgets;

    }
    
    /**
     * Maybe remove welcome panel from dashboard
     * 
     * @since 6.9.10
     */
    public function maybe_remove_welcome_panel() {

        $options = get_option( ASENHA_SLUG_U, array() );
        $disable_welcome_panel = isset( $options['disable_welcome_panel_in_dashboard'] ) ? $options['disable_welcome_panel_in_dashboard'] : false;
        
        if ( $disable_welcome_panel ) {
            remove_action( 'welcome_panel', 'wp_welcome_panel' );
        }

    }
    
}