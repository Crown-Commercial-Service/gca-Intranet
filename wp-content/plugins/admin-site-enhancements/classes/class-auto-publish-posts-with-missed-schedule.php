<?php

namespace ASENHA\Classes;

/**
 * Class for Auto-Publishing of Posts with Missed Schedules module
 *
 * @since 6.9.5
 */
class Auto_Publish_Posts_With_Missed_Schedule {

    /**
     * Publish posts of any type with missed schedule. 
     * We use the Transients API to reduce straining the site with DB queries on busy sites.
     * So, this function will only query the DB once every 15 minutes at most.
     *
     * @since 3.1.0
     */
    public function publish_missed_schedule_posts() {

        if ( is_front_page() || is_home() || is_page() || is_single() || is_singular() || is_archive() || is_admin() || is_blog_admin() || is_robots() || is_ssl() ) {

            // Get missed schedule posts data from cache
            $missed_schedule_posts = get_transient( 'asenha_missed_schedule_posts' );

            // Nothing found in cache
            if ( false === $missed_schedule_posts ) {

                global $wpdb;

                $current_gmt_datetime = gmdate( 'Y-m-d H:i:00' );

                $args = array(
                    'public'    => true,
                    '_builtin'  => false, // not post, page, attachment, revision or nav_menu_item
                );

                $custom_post_types = get_post_types( $args, 'names' ); // array, e.g. array( 'project', 'book', 'staff' )

                if ( count( $custom_post_types ) > 0 ) {
                    $custom_post_types = "'" . implode( "','", $custom_post_types ) . "'"; // string, e.g. 'project','book','staff'
                    $post_types = "'page','post'," . $custom_post_types; // 'page','post','project','book','staff'
                } else {
                    $post_types = "'page','post'";
                }

                $sql = "SELECT ID FROM $wpdb->posts WHERE post_type IN ($post_types) AND post_status='future' AND post_date_gmt<'$current_gmt_datetime'";

                // The following does not work as backslashes are inserted before single quotes in $post_types
                // $sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type IN (%s) AND post_status='future' AND post_date_gmt<'%s'", array( $post_types, $current_gmt_datetime ) );

                $missed_schedule_posts = $wpdb->get_results( $sql, ARRAY_A );

                // Save query results as a transient with expiry of 15 minutes
                set_transient( 'asenha_missed_schedule_posts', $missed_schedule_posts, 15 * MINUTE_IN_SECONDS );

            }

            if ( empty( $missed_schedule_posts ) || ! is_array( $missed_schedule_posts ) ) {
                return;
            }

            foreach( $missed_schedule_posts as $post ) {
                wp_publish_post( $post['ID'] );
            }

        }

    }
        
}