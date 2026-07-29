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
        $revisions_max_number = ( isset( $options['revisions_max_number'] ) ? $options['revisions_max_number'] : 10 );
        $for_post_types = ( isset( $options['enable_revisions_control_for'] ) && is_array( $options['enable_revisions_control_for'] ) ? $options['enable_revisions_control_for'] : array() );
        $revisions_control_type = 'only-on';
        // Assemble single-dimensional array of post type slugs for which revisions is being limited.
        $limited_post_types = array();
        foreach ( $for_post_types as $post_type_slug => $post_type_is_limited ) {
            if ( $post_type_is_limited ) {
                $limited_post_types[] = $post_type_slug;
            }
        }
        // Change revisions number to keep if set for the post type as such.
        $post_type = ( is_object( $post ) && property_exists( $post, 'post_type' ) ? $post->post_type : '' );
        if ( 'only-on' === $revisions_control_type && in_array( $post_type, $limited_post_types, true ) ) {
            $num = $revisions_max_number;
        }
        return $num;
    }

}
