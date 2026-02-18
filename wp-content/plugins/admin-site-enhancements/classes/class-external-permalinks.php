<?php

namespace ASENHA\Classes;

/**
 * Class for External Permalinks module
 *
 * @since 6.9.5
 */
class External_Permalinks {
    /**
     * Check if External Permalinks is enabled for the given post type.
     *
     * In the free version, this always behaves like 'only-on'. In the pro
     * version, this can be 'only-on', 'except-on' or 'all-post-types'.
     *
     * @param string $post_type_slug Post type slug.
     * @param array  $options        Plugin options.
     * @return bool True when enabled for the post type.
     */
    private function is_enabled_for_post_type( $post_type_slug, $options = array() ) {
        if ( empty( $post_type_slug ) ) {
            return false;
        }
        $enabled_for = ( isset( $options['enable_external_permalinks_for'] ) && is_array( $options['enable_external_permalinks_for'] ) ? $options['enable_external_permalinks_for'] : array() );
        // Only operate on applicable post types (public, excluding attachment).
        $applicable_post_types = array_keys( $enabled_for );
        $applicable_post_types = array_values( array_diff( $applicable_post_types, array('attachment') ) );
        if ( empty( $applicable_post_types ) || !in_array( $post_type_slug, $applicable_post_types, true ) ) {
            return false;
        }
        $type = 'only-on';
        $selected_post_types = array();
        foreach ( $enabled_for as $slug => $is_enabled ) {
            if ( 'attachment' === $slug ) {
                continue;
            }
            if ( $is_enabled ) {
                $selected_post_types[] = $slug;
            }
        }
        // Default to 'only-on'.
        return in_array( $post_type_slug, $selected_post_types, true );
    }

    /**
     * Add external permalink meta box for enabled post types
     * 
     * @since 3.9.0
     */
    public function add_external_permalink_meta_box( $post_type, $post ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( !$this->is_enabled_for_post_type( $post_type, $options ) ) {
            return;
        }
        // Skip adding meta box for post types where Gutenberg is enabled
        // if (
        //  function_exists( 'use_block_editor_for_post_type' )
        //  && use_block_editor_for_post_type( $post_type )
        // ) {
        //  return;
        // }
        add_meta_box(
            'asenha-external-permalink',
            // ID of meta box
            'External Permalink',
            // Title of meta box
            [$this, 'output_external_permalink_meta_box'],
            // Callback function
            $post_type,
            // The screen on which the meta box should be output to
            'normal',
            // context
            'high'
        );
    }

    /**
     * Render External Permalink meta box
     *
     * @since 3.9.0
     */
    public function output_external_permalink_meta_box( $post ) {
        ?>
        <div class="external-permalink-input">
            <input name="<?php 
        echo esc_attr( 'external_permalink' );
        ?>" class="large-text" id="<?php 
        echo esc_attr( 'external_permalink' );
        ?>" type="text" value="<?php 
        echo esc_url( get_post_meta( $post->ID, '_links_to', true ) );
        ?>" placeholder="https://" />
            <div class="external-permalink-input-description">Keep empty to use the default WordPress permalink. External permalink will open in a new browser tab.</div>
            <?php 
        wp_nonce_field(
            'external_permalink_' . $post->ID,
            'external_permalink_nonce',
            false,
            true
        );
        ?>
        </div>
        <?php 
    }

    /**
     * Save external permalink input
     *
     * @since 3.9.0
     */
    public function save_external_permalink( $post_id ) {
        // Only proceed if nonce is verified
        if ( isset( $_POST['external_permalink_nonce'] ) && wp_verify_nonce( $_POST['external_permalink_nonce'], 'external_permalink_' . $post_id ) ) {
            $options = get_option( ASENHA_SLUG_U, array() );
            $post_type = get_post_type( $post_id );
            if ( !$this->is_enabled_for_post_type( $post_type, $options ) ) {
                return;
            }
            // Get the value of external permalink from input field
            $external_permalink = ( isset( $_POST['external_permalink'] ) ? esc_url_raw( trim( $_POST['external_permalink'] ) ) : '' );
            // Update or delete external permalink post meta
            if ( !empty( $external_permalink ) ) {
                update_post_meta( $post_id, '_links_to', $external_permalink );
            } else {
                delete_post_meta( $post_id, '_links_to' );
            }
        }
    }

    /**
     * Change WordPress default permalink into external permalink for pages
     *
     * @since 3.9.0
     */
    public function use_external_permalink_for_pages( $permalink, $post_id ) {
        $request_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );
        // e.g. /wp-admin/index.php?page=page-slug
        if ( false === strpos( $request_uri, 'mfn-live-builder' ) ) {
            // When not in BeTheme template builder, that has the 'action=mfn-live-builder' parameter in the URL
            $options = get_option( ASENHA_SLUG_U, array() );
            $post_type = get_post_type( $post_id );
            if ( !$this->is_enabled_for_post_type( $post_type, $options ) ) {
                return $permalink;
            }
            $external_permalink = get_post_meta( $post_id, '_links_to', true );
            if ( !empty( $external_permalink ) ) {
                $permalink = $external_permalink;
            }
        }
        return $permalink;
    }

    /**
     * Change WordPress default permalink into external permalink for posts and custom post types
     *
     * @since 3.9.0
     */
    public function use_external_permalink_for_posts( $permalink, $post ) {
        $request_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );
        // e.g. /wp-admin/index.php?page=page-slug
        if ( false === strpos( $request_uri, 'mfn-live-builder' ) ) {
            // When not in BeTheme template builder, that has the 'action=mfn-live-builder' parameter in the URL
            $options = get_option( ASENHA_SLUG_U, array() );
            $post_type = ( is_object( $post ) && isset( $post->post_type ) ? $post->post_type : '' );
            if ( !$this->is_enabled_for_post_type( $post_type, $options ) ) {
                return $permalink;
            }
            $external_permalink = get_post_meta( $post->ID, '_links_to', true );
            if ( !empty( $external_permalink ) ) {
                $permalink = $external_permalink;
                if ( !is_admin() ) {
                    $permalink = $permalink . '#new_tab';
                }
            }
        }
        return $permalink;
    }

    /** 
     * Redirect page/post to external permalink if it's loaded directly from the WP default permalink
     *
     * @since 3.9.0
     */
    public function redirect_to_external_permalink() {
        // If not on/loading the single page/post URL, do nothing
        if ( !is_singular() ) {
            return;
        }
        global $post;
        if ( !is_null( $post ) && is_object( $post ) && is_a( $post, 'WP_Post' ) ) {
            $options = get_option( ASENHA_SLUG_U, array() );
            if ( empty( $post->post_type ) || !$this->is_enabled_for_post_type( $post->post_type, $options ) ) {
                return;
            }
            if ( property_exists( $post, 'ID' ) ) {
                $external_permalink = get_post_meta( $post->ID, '_links_to', true );
                if ( !empty( $external_permalink ) ) {
                    wp_redirect( $external_permalink, 302 );
                    // temporary redirect
                    exit;
                }
            }
        }
    }

}
