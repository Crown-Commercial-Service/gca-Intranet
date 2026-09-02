<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Comments module
 *
 * @since 6.9.5
 */
class Disable_Comments {
    /**
     * Public post types that should never have comments disabled by this module.
     *
     * @since 8.5.2
     *
     * @var string[]
     */
    public $inapplicable_post_types = array('kt_gallery');

    /**
     * Get the disable comments mode.
     *
     * @since 8.5.2
     *
     * @param array $options Module options.
     * @return string
     */
    private function get_disable_comments_type( $options ) {
        return 'only-on';
    }

    /**
     * Get the post types selected in the Disable Comments settings.
     *
     * @since 8.5.2
     *
     * @param array $options Module options.
     * @return string[]
     */
    private function get_disabled_comments_post_types( $options ) {
        $disable_comments_for = ( isset( $options['disable_comments_for'] ) ? $options['disable_comments_for'] : array() );
        if ( empty( $disable_comments_for ) || !is_array( $disable_comments_for ) ) {
            return array();
        }
        $common_methods = new Common_Methods();
        $disable_comments_for = $common_methods->get_array_of_keys_with_true_value( $disable_comments_for );
        return array_values( array_diff( $disable_comments_for, $this->inapplicable_post_types ) );
    }

    /**
     * Get public post types that support comments and can be managed by this module.
     *
     * @since 8.5.2
     *
     * @return string[]
     */
    private function get_applicable_public_post_types( $options = array() ) {
        if ( empty( $options ) ) {
            $options = get_option( ASENHA_SLUG_U, array() );
        }
        $public_post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        $configured_post_types = ( isset( $options['disable_comments_for'] ) && is_array( $options['disable_comments_for'] ) ? array_keys( $options['disable_comments_for'] ) : array() );
        $candidate_post_types = array_unique( array_merge( $public_post_types, $configured_post_types ) );
        $applicable_post_types = array();
        if ( is_array( $candidate_post_types ) ) {
            foreach ( $candidate_post_types as $post_type ) {
                $post_type_object = get_post_type_object( $post_type );
                if ( !is_object( $post_type_object ) || !$post_type_object->public || $this->is_post_type_inapplicable( $post_type ) ) {
                    continue;
                }
                if ( post_type_supports( $post_type, 'comments' ) || array_key_exists( $post_type, (array) $options['disable_comments_for'] ) ) {
                    $applicable_post_types[] = $post_type;
                }
            }
        }
        return $applicable_post_types;
    }

    /**
     * Check if the post type should never be affected by Disable Comments.
     *
     * @since 8.5.2
     *
     * @param string $post_type Post type slug.
     * @return bool
     */
    public function is_post_type_inapplicable( $post_type ) {
        return in_array( $post_type, $this->inapplicable_post_types, true );
    }

    /**
     * Check if comments are disabled for all applicable post types.
     *
     * @since 8.5.2
     *
     * @param array $options Optional module options.
     * @return bool
     */
    public function are_comments_disabled_for_all_applicable_post_types( $options = array() ) {
        if ( empty( $options ) ) {
            $options = get_option( ASENHA_SLUG_U, array() );
        }
        if ( !isset( $options['disable_comments'] ) || !$options['disable_comments'] ) {
            return false;
        }
        $applicable_post_types = $this->get_applicable_public_post_types( $options );
        if ( empty( $applicable_post_types ) ) {
            return false;
        }
        foreach ( $applicable_post_types as $post_type ) {
            if ( !$this->is_commenting_disabled_for_post_type( $post_type, $options ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Disable comments for post types
     *
     * @since 2.7.0
     */
    public function disable_comments_for_post_types_edit() {
        $options = get_option( ASENHA_SLUG_U, array() );
        foreach ( $this->get_applicable_public_post_types( $options ) as $post_type_slug ) {
            if ( $this->is_commenting_disabled_for_post_type( $post_type_slug, $options ) ) {
                remove_post_type_support( $post_type_slug, 'comments' );
                remove_post_type_support( $post_type_slug, 'trackbacks' );
                remove_meta_box( 'commentstatusdiv', $post_type_slug, 'normal' );
                remove_meta_box( 'commentstatusdiv', $post_type_slug, 'side' );
                remove_meta_box( 'commentsdiv', $post_type_slug, 'normal' );
                remove_meta_box( 'commentsdiv', $post_type_slug, 'side' );
                remove_meta_box( 'trackbacksdiv', $post_type_slug, 'normal' );
                remove_meta_box( 'trackbacksdiv', $post_type_slug, 'side' );
                // edit-comments.js
                wp_dequeue_script( 'admin-comments' );
            }
        }
    }

    /**
     * Hide existing comments from the frontend post
     *
     * @since 6.2.1
     */
    public function hide_existing_comments_on_frontend() {
        $current_post_type = get_post_type();
        if ( $this->is_commenting_disabled_for_post_type( $current_post_type ) ) {
            add_filter(
                'comments_array',
                '__return_empty_array',
                10,
                2
            );
        }
    }

    /**
     * Return empty comments array for comment templates
     * 
     * @since 6.3.1
     */
    public function maybe_return_empty_comments( $comments, $post_id ) {
        $post = get_post( $post_id );
        if ( !is_object( $post ) || !property_exists( $post, 'post_type' ) ) {
            return $comments;
        }
        if ( $this->is_commenting_disabled_for_post_type( $post->post_type ) ) {
            return array();
        }
        return $comments;
    }

    /**
     * Close commenting on the frontend
     *
     * @since 2.7.0
     */
    public function close_comments_pings_on_frontend( $comments_pings_open, $post_id ) {
        // If commenting or pinging is not open, let's keep it that way
        if ( !$comments_pings_open ) {
            return $comments_pings_open;
        }
        // Commenting or pinging is open for the post ID, let's decide if we should close it
        $post = get_post( $post_id );
        if ( is_object( $post ) && property_exists( $post, 'post_type' ) && $this->is_commenting_disabled_for_post_type( $post->post_type ) ) {
            return false;
        }
        return $comments_pings_open;
    }

    /**
     * Always return zero for comments count on a post where the post type has commenting disabled
     * 
     * @since 6.2.7
     */
    public function return_zero_comments_count( $comments_number, $post_id ) {
        $post = get_post( $post_id );
        if ( is_object( $post ) && property_exists( $post, 'post_type' ) ) {
            if ( $this->is_commenting_disabled_for_post_type( $post->post_type ) ) {
                return 0;
            }
        }
        return $comments_number;
    }

    /**
     * Disable commenting via XML-RPC
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-comments/tags/2.4.5/disable-comments.php
     * @since 6.3.1
     */
    public function disable_xmlrpc_comments( $methods ) {
        if ( $this->are_comments_disabled_for_all_applicable_post_types() ) {
            unset($methods['wp.newComment']);
        }
        return $methods;
    }

    /**
     * Disables comments endpoint in REST API
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-comments/tags/2.4.5/disable-comments.php
     * @since 6.3.1
     */
    public function disable_rest_api_comments_endpoints( $endpoints ) {
        if ( $this->are_comments_disabled_for_all_applicable_post_types() ) {
            if ( isset( $endpoints['comments'] ) ) {
                unset($endpoints['comments']);
            }
            if ( isset( $endpoints['/wp/v2/comments'] ) ) {
                unset($endpoints['/wp/v2/comments']);
            }
            if ( isset( $endpoints['/wp/v2/comments/(?P<id>[\\d]+)'] ) ) {
                unset($endpoints['/wp/v2/comments/(?P<id>[\\d]+)']);
            }
        }
        return $endpoints;
    }

    /**
     * Return blank comment before inserting to DB
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-comments/tags/2.4.5/disable-comments.php
     * @since 6.3.1
     */
    public function return_blank_comment( $prepared_comment, $request ) {
        $post_id = absint( $request->get_param( 'post' ) );
        if ( empty( $post_id ) ) {
            return $prepared_comment;
        }
        $post = get_post( $post_id );
        if ( !is_object( $post ) || !property_exists( $post, 'post_type' ) ) {
            return $prepared_comment;
        }
        if ( !$this->is_commenting_disabled_for_post_type( $post->post_type ) ) {
            return $prepared_comment;
        }
        return new \WP_Error('asenha_commenting_disabled', __( 'Comments are disabled for this content type.', 'admin-site-enhancements' ), array(
            'status' => 403,
        ));
    }

    /**
     * Show blank template on singular views when comment is disabled
     * 
     * @since 4.9.2
     */
    public function show_blank_comment_template() {
        $current_post_type = get_post_type();
        if ( is_singular() && $this->is_commenting_disabled_for_post_type( $current_post_type ) ) {
            add_filter( 'comments_template', [$this, 'load_blank_comment_template'], 20 );
        }
    }

    /**
     * Load the actual blank comment template
     * 
     * @since 4.9.2
     */
    public function load_blank_comment_template() {
        return ASENHA_PATH . 'includes/blank-comment-template.php';
    }

    /**
     * Check if commenting is disabled for the post type
     * 
     * @since 7.8.8
     */
    public function is_commenting_disabled_for_post_type( $post_type, $options = array() ) {
        if ( empty( $post_type ) || $this->is_post_type_inapplicable( $post_type ) ) {
            return false;
        }
        if ( empty( $options ) ) {
            $options = get_option( ASENHA_SLUG_U, array() );
        }
        $comment_is_disabled_for_post_type = false;
        if ( array_key_exists( 'disable_comments', $options ) && $options['disable_comments'] ) {
            $disable_comments_for = $this->get_disabled_comments_post_types( $options );
            $disable_comments_type = $this->get_disable_comments_type( $options );
            if ( 'only-on' === $disable_comments_type && in_array( $post_type, $disable_comments_for, true ) || 'except-on' === $disable_comments_type && !in_array( $post_type, $disable_comments_for, true ) || 'all-post-types' === $disable_comments_type ) {
                $comment_is_disabled_for_post_type = true;
            }
        }
        return $comment_is_disabled_for_post_type;
    }

}
