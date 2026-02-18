<?php

namespace ASENHA\Classes;

/**
 * Class for Revisions Control module
 *
 * @since 6.9.5
 */
class Revisions_Control {

    /**
     * Limit the number of revisions for post types
     *
     * @since 3.7.0
     */
    public function limit_revisions_to_max_number( $num, $post ) {

        $options = get_option( ASENHA_SLUG_U, array() );
        $revisions_max_number = $options['revisions_max_number'];
        $for_post_types = $options['enable_revisions_control_for'];

        // Assemble single-dimensional array of post type slugs for which revisinos is being limited
        $limited_post_types = array();
        foreach( $for_post_types as $post_type_slug => $post_type_is_limited ) {
            if ( $post_type_is_limited ) {
                $limited_post_types[] = $post_type_slug;
            }
        }

        // Change revisions number to keep if set for the post type as such
        $post_type = $post->post_type;
        if ( in_array( $post_type, $limited_post_types ) ) {
            $num = $revisions_max_number;
        }

        return $num;

    }

}