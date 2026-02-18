<?php

namespace ASENHA\Classes;

/**
 * Class for Enhance List Tables module
 *
 * @since 6.9.5
 */
class Enhance_List_Tables {
    /**
     * Current post type. For Content Admin >> Show Custom Taxonomy Filters functionality.
     */
    public $post_type;

    /**
     * Show featured images column in list tables for pages and post types that support featured image
     *
     * @since 1.0.0
     */
    public function show_featured_image_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( post_type_supports( $post_type_key, 'thumbnail' ) ) {
                add_filter( "manage_{$post_type_name}_posts_columns", [$this, 'add_featured_image_column'], 999 );
                add_action(
                    "manage_{$post_type_name}_posts_custom_column",
                    [$this, 'add_featured_image'],
                    10,
                    2
                );
            }
        }
    }

    /**
     * Add a column called Featured Image as the first column
     *
     * @param mixed $columns
     * @return void
     * @since 1.0.0
     */
    public function add_featured_image_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            if ( 'title' == $key ) {
                // We add featured image column before the 'title' column
                $new_columns['asenha-featured-image'] = __( 'Featured Image', 'admin-site-enhancements' );
            }
            if ( 'thumb' == $key ) {
                // For WooCommerce products, we add featured image column before it's native thumbnail column
                $new_columns['asenha-featured-image'] = __( 'Product Image', 'admin-site-enhancements' );
            }
            $new_columns[$key] = $value;
        }
        // Replace WooCommerce thumbnail column with ASE featured image column
        if ( array_key_exists( 'thumb', $new_columns ) ) {
            unset($new_columns['thumb']);
        }
        return $new_columns;
    }

    /**
     * Echo featured image's in thumbnail size to a column
     *
     * @param mixed $column_name
     * @param mixed $id
     * @since 1.0.0
     */
    public function add_featured_image( $column_name, $id ) {
        if ( 'asenha-featured-image' === $column_name ) {
            if ( has_post_thumbnail( $id ) ) {
                $size = 'thumbnail';
                echo '<a href="' . get_edit_post_link( $id ) . '">' . get_the_post_thumbnail( $id, $size, '' ) . '</a>';
            } else {
                echo '<a href="' . get_edit_post_link( $id ) . '"><img src="' . esc_url( plugins_url( 'assets/img/default_featured_image.jpg', __DIR__ ) ) . '" /></a>';
            }
        }
    }

    /**
     * Show excerpt column in list tables for pages and post types that support excerpt.
     *
     * @since 1.0.0
     */
    public function show_excerpt_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( post_type_supports( $post_type_key, 'excerpt' ) ) {
                add_filter( "manage_{$post_type_name}_posts_columns", [$this, 'add_excerpt_column'] );
                add_action(
                    "manage_{$post_type_name}_posts_custom_column",
                    [$this, 'add_excerpt'],
                    10,
                    2
                );
            }
        }
    }

    /**
     * Add a column called Excerpt as the first column
     *
     * @param mixed $columns
     * @return void
     * @since 1.0.0
     */
    public function add_excerpt_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[$key] = $value;
            if ( $key == 'title' ) {
                $new_columns['asenha-excerpt'] = __( 'Excerpt', 'admin-site-enhancements' );
            }
        }
        return $new_columns;
    }

    /**
     * Echo featured image's in thumbnail size to a column
     *
     * @param mixed $column_name
     * @param mixed $id
     * @since 1.0.0
     */
    public function add_excerpt( $column_name, $id ) {
        if ( 'asenha-excerpt' === $column_name ) {
            $excerpt = wp_strip_all_tags( get_the_excerpt( $id ) );
            // about 310 characters
            $excerpt = substr( $excerpt, 0, 160 );
            // truncate to 160 characters
            $short_excerpt = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
            echo wp_kses_post( $short_excerpt );
        }
    }

    /** 
     * Show last modified column for pages, posts and CPTs
     * 
     * @since 7.4.0
     */
    public function show_last_modified_column() {
        foreach ( get_post_types() as $post_type ) {
            add_filter(
                'manage_' . $post_type . '_posts_columns',
                [$this, 'add_last_modified_column'],
                10,
                1
            );
            add_action(
                'manage_' . $post_type . '_posts_custom_column',
                [$this, 'show_last_modified_datetime'],
                10,
                2
            );
            add_filter(
                'manage_edit-' . $post_type . '_sortable_columns',
                [$this, 'make_last_modified_column_sortable'],
                10,
                1
            );
        }
    }

    /**
     * Add a column called Last Modified
     * 
     * @since 7.4.0
     */
    public function add_last_modified_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[$key] = $value;
            if ( $key == 'date' ) {
                $new_columns['asenha-last-modified'] = __( 'Last Modified', 'admin-site-enhancements' );
            }
        }
        return $new_columns;
    }

    /**
     * Output the last modified date time for each post
     * 
     * @since 7.4.0
     */
    public function show_last_modified_datetime( $column_name, $id ) {
        if ( 'asenha-last-modified' == $column_name ) {
            $modified_date_time_unix = strtotime( get_post_field( 'post_modified_gmt', $id ) );
            echo '<span class="last-modified-timestamp">' . wp_date( get_option( 'date_format' ), $modified_date_time_unix ) . '<br />' . wp_date( get_option( 'time_format' ), $modified_date_time_unix ) . '</span>';
        }
    }

    /**
     * Make last modified column sortable
     * 
     * @since 7.4.0
     */
    public function make_last_modified_column_sortable( $columns ) {
        $columns['asenha-last-modified'] = 'modified';
        return $columns;
    }

    /**
     * Add ID column list table of pages, posts, custom post types, media, taxonomies, custom taxonomies, users amd comments
     *
     * @since 1.0.0
     */
    public function show_id_column() {
        // For pages and hierarchical post types list table
        add_filter( 'manage_pages_columns', [$this, 'add_id_column'] );
        add_action(
            'manage_pages_custom_column',
            [$this, 'add_id_echo_value'],
            10,
            2
        );
        // For posts and non-hierarchical custom posts list table
        add_filter( 'manage_posts_columns', [$this, 'add_id_column'] );
        add_action(
            'manage_posts_custom_column',
            [$this, 'add_id_echo_value'],
            10,
            2
        );
        // For media list table
        add_filter( 'manage_media_columns', [$this, 'add_id_column'] );
        add_action(
            'manage_media_custom_column',
            [$this, 'add_id_echo_value'],
            10,
            2
        );
        // For list table of all taxonomies
        $taxonomies = get_taxonomies( [
            'public' => true,
        ], 'names' );
        foreach ( $taxonomies as $taxonomy ) {
            add_filter( 'manage_edit-' . $taxonomy . '_columns', [$this, 'add_id_column'] );
            add_action(
                'manage_' . $taxonomy . '_custom_column',
                [$this, 'add_id_return_value'],
                10,
                3
            );
        }
        // For users list table
        add_filter( 'manage_users_columns', [$this, 'add_id_column'] );
        add_action(
            'manage_users_custom_column',
            [$this, 'add_id_return_value'],
            10,
            3
        );
        // For comments list table
        add_filter( 'manage_edit-comments_columns', [$this, 'add_id_column'] );
        add_action(
            'manage_comments_custom_column',
            [$this, 'add_id_echo_value'],
            10,
            3
        );
    }

    /**
     * Add a column called ID
     *
     * @param mixed $columns
     * @return void
     * @since 1.0.0
     */
    public function add_id_column( $columns ) {
        $columns['asenha-id'] = 'ID';
        return $columns;
    }

    /**
     * Echo post ID value to a column
     *
     * @param mixed $column_name
     * @param mixed $id
     * @since 1.0.0
     */
    public function add_id_echo_value( $column_name, $id ) {
        if ( 'asenha-id' === $column_name ) {
            echo esc_html( $id );
        }
    }

    /**
     * Return post ID value to a column
     *
     * @param mixed $value
     * @param mixed $column_name
     * @param mixed $id
     * @since 1.0.0
     */
    public function add_id_return_value( $value, $column_name, $id ) {
        if ( 'asenha-id' === $column_name ) {
            $value = $id;
        }
        return $value;
    }

    /**
     * Add file size column to media library
     *
     * @since 6.9.5
     */
    public function add_column_file_size( $columns ) {
        $columns['asenha-file-size'] = __( 'File Size', 'admin-site-enhancements' );
        return $columns;
    }

    /**
     * Display the file size value
     *
     * @since 6.9.5
     */
    public function display_file_size( $column_name, $attachment_id ) {
        if ( 'asenha-file-size' != $column_name ) {
            return;
        }
        $file_size = filesize( get_attached_file( $attachment_id ) );
        $file_size = size_format( $file_size, 1 );
        // Show one decimal point
        echo esc_html( $file_size );
    }

    /**
     * Add file size column to media library
     *
     * @since 6.9.5
     */
    public function add_media_styles() {
        echo '<style>.column-asenha-file-siz {width: 60px;}</style>';
    }

    /**
     * Add ID in the action row of list tables for pages, posts, custom post types, media, taxonomies, custom taxonomies, users amd comments
     *
     * @since 4.7.4
     */
    public function show_id_in_action_row() {
        add_filter(
            'page_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
        add_filter(
            'post_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
        add_filter(
            'cat_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
        add_filter(
            'tag_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
        add_filter(
            'media_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
        add_filter(
            'comment_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
        add_filter(
            'user_row_actions',
            array($this, 'add_id_in_action_row'),
            99,
            2
        );
    }

    /**
     * Output the ID in the action row
     *
     * @since 4.7.4
     */
    public function add_id_in_action_row( $actions, $object ) {
        if ( current_user_can( 'edit_posts' ) ) {
            // For pages, posts, custom post types, media/attachments, users
            if ( property_exists( $object, 'ID' ) ) {
                $id = $object->ID;
            }
            // For taxonomies
            if ( property_exists( $object, 'term_id' ) ) {
                $id = $object->term_id;
            }
            // For comments
            if ( property_exists( $object, 'comment_ID' ) ) {
                $id = $object->comment_ID;
            }
            $actions['asenha-list-table-item-id'] = '<span class="asenha-list-table-item-id">ID: ' . $id . '</span>';
        }
        return $actions;
    }

    /** 
     * Show last modified column for pages, posts and CPTs
     * 
     * @since 7.4.0
     */
    public function hide_date_column() {
        foreach ( get_post_types() as $post_type ) {
            add_filter(
                'manage_' . $post_type . '_posts_columns',
                [$this, 'remove_date_column'],
                10,
                1
            );
        }
    }

    /**
     * Add a column called Last Modified
     * 
     * @since 7.4.0
     */
    public function remove_date_column( $columns ) {
        unset($columns['date']);
        return $columns;
    }

    /**
     * Hide comments column in list tables for pages, post types that support comments, and alse media/attachments.
     *
     * @since 1.0.0
     */
    public function hide_comments_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( post_type_supports( $post_type_key, 'comments' ) ) {
                if ( 'attachment' != $post_type_name ) {
                    // For list tables of pages, posts and other post types
                    add_filter( "manage_{$post_type_name}_posts_columns", [$this, 'remove_comment_column'] );
                } else {
                    // For list table of media/attachment
                    add_filter( 'manage_media_columns', [$this, 'remove_comment_column'] );
                }
            }
        }
    }

    /**
     * Add a column called ID
     *
     * @param mixed $columns
     * @return void
     * @since 1.0.0
     */
    public function remove_comment_column( $columns ) {
        unset($columns['comments']);
        return $columns;
    }

    /**
     * Hide tags column in list tables for posts.
     *
     * @since 1.0.0
     */
    public function hide_post_tags_column() {
        $post_types = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $post_types as $post_type_key => $post_type_name ) {
            if ( $post_type_name == 'post' ) {
                add_filter( "manage_posts_columns", [$this, 'remove_post_tags_column'] );
            }
        }
    }

    /**
     * Add a column called ID
     *
     * @param mixed $columns
     * @return void
     * @since 1.0.0
     */
    public function remove_post_tags_column( $columns ) {
        unset($columns['tags']);
        return $columns;
    }

}
