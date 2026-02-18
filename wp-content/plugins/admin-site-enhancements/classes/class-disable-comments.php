<?php

namespace ASENHA\Classes;

/**
 * Class for Disable Comments module
 *
 * @since 6.9.5
 */
class Disable_Comments {
    /**
     * Disable comments for post types
     *
     * @since 2.7.0
     */
    public function disable_comments_for_post_types_edit() {
        $options = get_option( ASENHA_SLUG_U );
        $disable_comments_type = 'only-on';
        $disable_comments_for = $options['disable_comments_for'];
        foreach ( $disable_comments_for as $post_type_slug => $is_post_type_checked ) {
            if ( 'only-on' == $disable_comments_type && $is_post_type_checked || 'except-on' == $disable_comments_type && !$is_post_type_checked || 'all-post-types' == $disable_comments_type ) {
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
        $options = get_option( ASENHA_SLUG_U );
        $disable_comments_type = 'only-on';
        $disable_comments_for = $options['disable_comments_for'];
        $current_post_type = get_post_type();
        foreach ( $disable_comments_for as $post_type_slug => $is_post_type_checked ) {
            if ( 'only-on' == $disable_comments_type && $current_post_type === $post_type_slug && $is_post_type_checked || 'except-on' == $disable_comments_type && $current_post_type === $post_type_slug && !$is_post_type_checked || 'all-post-types' == $disable_comments_type ) {
                add_filter(
                    'comments_array',
                    '__return_empty_array',
                    10,
                    2
                );
            }
        }
    }

    /**
     * Return empty comments array for comment templates
     * 
     * @since 6.3.1
     */
    public function maybe_return_empty_comments( $comments, $post_id ) {
        $options = get_option( ASENHA_SLUG_U );
        $disable_comments_type = 'only-on';
        $disable_comments_for = $options['disable_comments_for'];
        $post = get_post( $post_id );
        $current_post_type = $post->post_type;
        foreach ( $disable_comments_for as $post_type_slug => $is_post_type_checked ) {
            if ( 'only-on' == $disable_comments_type && $current_post_type === $post_type_slug && $is_post_type_checked || 'except-on' == $disable_comments_type && $current_post_type === $post_type_slug && !$is_post_type_checked || 'all-post-types' == $disable_comments_type ) {
                return array();
            } else {
                return $comments;
            }
        }
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
        $options = get_option( ASENHA_SLUG_U );
        $disable_comments_type = 'only-on';
        $disable_comments_for = $options['disable_comments_for'];
        $post = get_post( $post_id );
        $current_post_type = $post->post_type;
        foreach ( $disable_comments_for as $post_type_slug => $is_post_type_checked ) {
            if ( 'only-on' == $disable_comments_type && $current_post_type === $post_type_slug && $is_post_type_checked || 'except-on' == $disable_comments_type && $current_post_type === $post_type_slug && !$is_post_type_checked || 'all-post-types' == $disable_comments_type ) {
                return false;
            }
        }
        return $comments_pings_open;
    }

    /**
     * Always return zero for comments count on a post where the post type has commenting disabled
     * 
     * @since 6.2.7
     */
    public function return_zero_comments_count( $comments_number, $post_id ) {
        $options = get_option( ASENHA_SLUG_U );
        $disable_comments_type = 'only-on';
        $disable_comments_for = $options['disable_comments_for'];
        $post = get_post( $post_id );
        if ( is_object( $post ) && property_exists( $post, 'post_type' ) ) {
            $current_post_type = $post->post_type;
            foreach ( $disable_comments_for as $post_type_slug => $is_post_type_checked ) {
                if ( 'only-on' == $disable_comments_type && $current_post_type === $post_type_slug && $is_post_type_checked || 'except-on' == $disable_comments_type && $current_post_type === $post_type_slug && !$is_post_type_checked || 'all-post-types' == $disable_comments_type ) {
                    return 0;
                }
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
        unset($methods['wp.newComment']);
        return $methods;
    }

    /**
     * Disables comments endpoint in REST API
     * 
     * @link https://plugins.trac.wordpress.org/browser/disable-comments/tags/2.4.5/disable-comments.php
     * @since 6.3.1
     */
    public function disable_rest_api_comments_endpoints( $endpoints ) {
        if ( isset( $endpoints['comments'] ) ) {
            unset($endpoints['comments']);
        }
        if ( isset( $endpoints['/wp/v2/comments'] ) ) {
            unset($endpoints['/wp/v2/comments']);
        }
        if ( isset( $endpoints['/wp/v2/comments/(?P<id>[\\d]+)'] ) ) {
            unset($endpoints['/wp/v2/comments/(?P<id>[\\d]+)']);
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
        return;
    }

    /**
     * Show blank template on singular views when comment is disabled
     * 
     * @since 4.9.2
     */
    public function show_blank_comment_template() {
        $options = get_option( ASENHA_SLUG_U );
        $disable_comments_type = 'only-on';
        $disable_comments_for = $options['disable_comments_for'];
        $current_post_type = get_post_type();
        foreach ( $disable_comments_for as $post_type_slug => $is_post_type_checked ) {
            if ( 'only-on' == $disable_comments_type && $current_post_type === $post_type_slug && $is_post_type_checked || 'except-on' == $disable_comments_type && $current_post_type === $post_type_slug && !$is_post_type_checked || 'all-post-types' == $disable_comments_type ) {
                if ( is_singular() ) {
                    add_filter( 'comments_template', [$this, 'load_blank_comment_template'], 20 );
                }
            }
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
    public function is_commenting_disabled_for_post_type( $post_type ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $comment_is_disabled_for_post_type = false;
        if ( array_key_exists( 'disable_comments', $options ) && $options['disable_comments'] ) {
            if ( array_key_exists( 'disable_comments_for', $options ) && !empty( $options['disable_comments_for'] ) ) {
                $disable_comments_for = $options['disable_comments_for'];
                $common_methods = new Common_Methods();
                $disable_comments_for = $common_methods->get_array_of_keys_with_true_value( $disable_comments_for );
                $disable_comments_type = 'only-on';
                if ( 'only-on' == $disable_comments_type && in_array( $post_type, $disable_comments_for ) || 'except-on' == $disable_comments_type && !in_array( $post_type, $disable_comments_for ) || 'all-post-types' == $disable_comments_type ) {
                    $comment_is_disabled_for_post_type = true;
                }
            }
        }
        return $comment_is_disabled_for_post_type;
    }

}
