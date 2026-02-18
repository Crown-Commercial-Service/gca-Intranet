<?php

namespace ASENHA\Classes;

/**
 * Class for Search Engines Visibility Status module
 *
 * @since 6.9.5
 */
class Search_Engines_Visibility {
    /**
     * Display search engine visibility status indicator and notice
     * 
     * @since 6.6.0
     */
    public function maybe_display_search_engine_visibility_status() {
        // Check if the user is an admin
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        // Get the option 'blog_public' to check search engine visibility
        // If 'blog_public' is '0', it means 'Discourage search engines from indexing this site' is checked
        if ( get_option( 'blog_public' ) === '0' ) {
            // add_action( 'admin_notices', array( $this, 'display_admin_notice_for_search_visibility' ) );
            add_action( 'admin_bar_menu', array($this, 'add_notice_in_admin_bar'), 100 );
        }
    }

    public function display_admin_notice_for_search_visibility() {
        // echo '<div class="notice notice-warning is-dismissible">';
        // echo '<p><strong>Search Engine Visibility is OFF</strong>. Search engines are discouraged from indexing this site. <a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '"><strong>Change the setting »</strong></a></p>';
        // echo '</div>';
    }

    public function add_notice_in_admin_bar( $wp_admin_bar ) {
        $node_id = 'search_visibility_notice';
        // Add inline style for warning background color
        ?>
        <style>#wpadminbar #wp-admin-bar-search_visibility_notice > .ab-item { background-color: #ff9a00; color: #fff; font-weight: 600; }</style>
        <?php 
        $args = array(
            'id'     => $node_id,
            'parent' => 'top-secondary',
            'title'  => __( 'SE Visibility: OFF', 'admin-site-enhancements' ),
            'href'   => admin_url( 'options-reading.php' ),
            'meta'   => array(
                'title' => __( 'Search engines are discouraged from indexing this site. Click to change the settings.', 'admin-site-enhancements' ),
            ),
        );
        $wp_admin_bar->add_node( $args );
    }

}
