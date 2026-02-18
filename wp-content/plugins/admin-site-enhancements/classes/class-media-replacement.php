<?php

namespace ASENHA\Classes;

/**
 * Class for Media Replacement module
 *
 * @since 6.9.5
 */
class Media_Replacement {

    /**
     * Modify the 'Edit' link to be 'Edit or Replace'
     * 
     */
    public function modify_media_list_table_edit_link( $actions, $post ) {

        $new_actions = array();

        foreach( $actions as $key => $value ) {

            if ( $key == 'edit' ) {

                $new_actions['edit'] = '<a href="' . get_edit_post_link( $post ) . '" aria-label="Edit or Replace">Edit or Replace</a>';

            } else {

                $new_actions[$key] = $value;

            }

        }

        return $new_actions;

    }
        
    /**
     * Add media replacement button in the edit screen of media/attachment
     *
     * @since 1.1.0
     */
    public function add_media_replacement_button( $fields, $post ) {
        global $pagenow, $typenow;
        
        // Do not do this on post creation and editing screen
        // May cause media frame layout / display issues
        if ( 'attachment' == $typenow ||
            ( 'attachment' != $typenow && 'post-new.php' != $pagenow && 'post.php' != $pagenow )        
            ) {
            $original_attachment_id = '';
            $image_mime_type = '';
            if ( is_object( $post ) ) {
                $original_attachment_id = $post->ID;
                if ( property_exists( $post, 'post_mime_type' ) ) {
                    $image_mime_type = $post->post_mime_type;       
                }
            }
                    
            // Enqueues all scripts, styles, settings, and templates necessary to use all media JS APIs.
            // Reference: https://codex.wordpress.org/Javascript_Reference/wp.media
            wp_enqueue_media();

            // Add new field to attachment fields for the media replace functionality
            $fields['asenha-media-replace'] = array();
            $fields['asenha-media-replace']['label'] = '';
            $fields['asenha-media-replace']['input'] = 'html';
            $fields['asenha-media-replace']['html'] = '
                <div id="media-replace-div' . '" class="postbox attachment-id-' . $original_attachment_id . '" data-original-image-id="' . $original_attachment_id . '">
                    <div class="postbox-header">
                        <h2 class="hndle ui-sortable-handle">' . __( 'Replace Media', 'admin-site-enhancements' ) . '</h2>
                    </div>
                    <div class="inside">
                    <button type="button" id="asenha-media-replace" class="button-secondary button-large asenha-media-replace-button" data-old-image-mime-type="' . $image_mime_type . '" onclick="replaceMedia(\'' . $original_attachment_id . '\',\'' . $image_mime_type . '\');">' . __( 'Select New Media File', 'admin-site-enhancements' ) . '</button>
                    <input type="hidden" id="new-attachment-id-' . $original_attachment_id . '" name="new-attachment-id-' . $original_attachment_id . '" />
                    <div class="asenha-media-replace-notes"><p>' . __( 'The current file will be replaced with the uploaded / selected file (of the same type) while retaining the current ID, publish date and file name. Thus, no existing links will break.', 'admin-site-enhancements' ) . '</p></div>
                    </div>
                </div>
            ';
        }

        return $fields;

    }
    
    public function attachment_for_js( $image_url, $attachment_id ) {
        // vi( $image_url );
        // vi( $attachment_id );
    }

    /**
     * Replace existing media with the newly updated file
     *
     * @link https://plugins.trac.wordpress.org/browser/replace-image/tags/1.1.7/hm-replace-image.php#L55
     * @since 1.1.0
     */
    public function replace_media( $old_attachment_id ) {
        // vi( $old_attachment_id, '', 'replace_media has been triggered via edit_attachment_hook' );

        // Get the new attachment/media ID, meta and mime type
        if ( isset( $_POST['new-attachment-id-'.$old_attachment_id] ) && ! empty( $_POST['new-attachment-id-'.$old_attachment_id] ) ) {
            $new_attachment_id = intval( sanitize_text_field( $_POST['new-attachment-id-'.$old_attachment_id] ) );
            // vi( $new_attachment_id, '', '$new_attachment_id is detected in replace_media' );

            // Verify the user has permission to delete the new attachment (source file for replacement)
            // This prevents unauthorized users from using another user's attachment as a replacement source
            // which would result in that attachment being deleted
            if ( ! current_user_can( 'delete_post', $new_attachment_id ) ) {
                return;
            }

            $old_post_meta = get_post( $old_attachment_id, ARRAY_A );
            $old_post_mime = $old_post_meta['post_mime_type']; // e.g. 'image/jpeg'
            // vi( $old_post_mime, '', 'old_post_mime is detected in replace_media' );

            $new_post_meta = get_post( $new_attachment_id, ARRAY_A );
            $new_post_mime = $new_post_meta['post_mime_type']; // e.g. 'image/jpeg'
            // vi( $new_post_mime, '', 'new_post_mime is detected in replace_media' );

            // Check if the media file ID selected via the media frame and passed on to the #new-attachment-id hidden field
            // Ensure the mime type matches too
            if ( ( ! empty( $new_attachment_id ) ) && is_numeric( $new_attachment_id ) && ( $old_post_mime == $new_post_mime ) ) {
                // vi( $new_attachment_id, '', 'We are processing the replacement of the old image.' );

                $new_attachment_meta = wp_get_attachment_metadata( $new_attachment_id );

                // If original file is larger than 2560 pixel
                // https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
                if ( is_array( $new_attachment_meta ) && array_key_exists( 'original_image', $new_attachment_meta ) ) {

                    // Get the original media file path
                    $new_media_file_path = wp_get_original_image_path( $new_attachment_id );

                } else {

                    // Get the path to newly uploaded media file. An image file name may end with '-scaled'.
                    $new_attachment_file = get_post_meta( $new_attachment_id, '_wp_attached_file', true );
                    $upload_dir = wp_upload_dir();
                    $new_media_file_path = $upload_dir['basedir'] . '/' . $new_attachment_file;

                }

                // Check if the new media file exist / was successfully uploaded
                if ( ! is_file( $new_media_file_path ) ) {
                    return false;
                }

                // Delete existing/old media files. Post and post meta entries for it are still there in the database.
                $this->delete_media_files( $old_attachment_id );

                // If original file is larger than 2560 pixel
                // https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
                if ( is_array( $new_attachment_meta ) && array_key_exists( 'original_image', $new_attachment_meta ) ) {

                    // Get the original media file path
                    $old_media_file_path = wp_get_original_image_path( $old_attachment_id );

                } else {

                    // Get the path to the old/existing media file that will be replaced and deleted. An image file name may end with '-scaled'.
                    $old_attachment_file = get_post_meta( $old_attachment_id, '_wp_attached_file', true );
                    $old_media_file_path = $upload_dir['basedir'] . '/' . $old_attachment_file;

                }

                // Check if the directory path to the old media file is still intact
                if ( ! file_exists( dirname( $old_media_file_path ) ) ) {

                    // Recreate the directory path
                    mkdir( dirname( $old_media_file_path ), 0755, true );

                }

                // Copy the new media file into the old media file's path
                copy( $new_media_file_path, $old_media_file_path );

                // Regenerate attachment meta data and image sub-sizes from the new media file that was just copied to the old path
                $old_media_post_meta_updated = wp_generate_attachment_metadata( $old_attachment_id, $old_media_file_path );

                // Update new media file's meta data with the ones from the old media. i.e. new media file will carry over the post ID and post meta of the old media file. i.e. only the files are replaced for the old media's ID and post meta in the database.
                wp_update_attachment_metadata( $old_attachment_id, $old_media_post_meta_updated );

                // Delete the newly uploaded media file and it's sub-sizes, and also delete post and post meta entries for it in the database.
                wp_delete_attachment( $new_attachment_id, true );
                
                // Add old attachment ID to recently replaced media option. This will be used for cache busting to ensure the new replacement images are immediately loaded in the browser / wp-admin
                $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
                $recently_replaced_media = isset( $options_extra['recently_replaced_media'] ) ? $options_extra['recently_replaced_media'] : array();
                $max_media_number_to_cache_bust = 5;
                // vi( $recently_replaced_media, '', 'before' );
                if ( count( $recently_replaced_media ) >= $max_media_number_to_cache_bust ) {
                    // Remove first/oldest attachment ID
                    array_shift( $recently_replaced_media );
                }
                $recently_replaced_media[] = $old_attachment_id;
                $recently_replaced_media = array_unique( $recently_replaced_media );
                // vi( $recently_replaced_media, '', 'after' );
                $options_extra['recently_replaced_media'] = $recently_replaced_media;
                update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
                sleep(2);
            }
        }
    }

    /**
     * Delete the existing/old media files when performing media replacement
     *
     * @link https://plugins.trac.wordpress.org/browser/replace-image/tags/1.1.7/hm-replace-image.php#L80
     * @since 1.1.0
     */
    public function delete_media_files( $post_id ) {

        $attachment_meta = wp_get_attachment_metadata( $post_id );

        // Will get '-scaled' version if it exists, e.g. /path/to/uploads/year/month/file-name.jpg
        $attachment_file_path = get_attached_file( $post_id ); 

        // e.g. file-name.jpg
        $attachment_file_basename = basename( $attachment_file_path );

        // Delete intermediate images if there are any
        
        if ( isset( $attachment_meta['sizes'] ) && is_array( $attachment_meta['sizes'] ) ) {

            foreach( $attachment_meta['sizes'] as $size => $size_info) {

                // /path/to/uploads/year/month/file-name.jpg --> /path/to/uploads/year/month/file-name-1024x768.jpg
                $intermediate_file_path = str_replace( $attachment_file_basename, $size_info['file'], $attachment_file_path );
                wp_delete_file( $intermediate_file_path );

            }

        }

        // Delete the attachment file, which maybe the '-scaled' version
        wp_delete_file( $attachment_file_path );

        // If original file is larger than 2560 pixel
        // https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
        $attachment_original_file_path = wp_get_original_image_path( $post_id );

        // Maybe delete the original file if it's an image file, and the original file path exists / was found
        if ( $attachment_original_file_path ) {
            wp_delete_file( $attachment_original_file_path );            
        }

    }

    /**
     * Customize the attachment updated message
     *
     * @link https://github.com/WordPress/wordpress-develop/blob/6.0.2/src/wp-admin/edit-form-advanced.php#L180
     * @since 1.1.0
     */
    public function attachment_updated_custom_message( $messages ) {

        $new_messages = array();

        foreach( $messages as $post_type => $messages_array ) {

            if ( $post_type == 'attachment' ) {

                // Message ID for successful edit/update of an attachment is 4. e.g. /wp-admin/post.php?post=a&action=edit&classic-editor&message=4 Customize it here.
                $messages_array[4] = 'Media file updated. You may need to <a href="https://fabricdigital.co.nz/blog/how-to-hard-refresh-your-browser-and-clear-cache" target="_blank">hard refresh</a> your browser to see the updated media preview image below.';

            }

            $new_messages[$post_type] = $messages_array;

        }

        return $new_messages;

    }
    
    /**
     * Append cache busting parameter to the end of image srcset
     * 
     * @since 5.7.0
     */
    public function append_cache_busting_param_to_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $recently_replaced_media = isset( $options_extra['recently_replaced_media'] ) ? $options_extra['recently_replaced_media'] : array();
        $attachment_mime_type = get_post_mime_type( $attachment_id );

        if ( in_array( $attachment_id, $recently_replaced_media ) 
            && false !== strpos( $attachment_mime_type, 'image' )
        ) {
            foreach ( $sources as $size => $source ) {
                $source['url'] .= $this->maybe_append_timestamp_parameter( $source['url'] );
                $sources[$size] = $source;
                // vi( $source, '', 'cache busting added via append_cache_busting_param_to_image_srcset' );
            }
        }
        return $sources;
    }

    /**
     * Append cache busting parameter to the end of image src
     * 
     * @since 5.7.0
     */
    public function append_cache_busting_param_to_attachment_image_src( $image, $attachment_id ) {
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $recently_replaced_media = isset( $options_extra['recently_replaced_media'] ) ? $options_extra['recently_replaced_media'] : array();
        $attachment_mime_type = get_post_mime_type( $attachment_id );

        if ( ! empty( $image[0] ) 
            && in_array( $attachment_id, $recently_replaced_media ) 
            && false !== strpos( $attachment_mime_type, 'image' )
        ) {
            $image[0] .= $this->maybe_append_timestamp_parameter( $image[0] );
            // vi( $image, '', 'cache busting added via append_cache_busting_param_to_attachment_image_src' );
         }

        return $image;
    }

    /**
     * Append cache busting parameter to image src for js
     * 
     * @since 5.7.0
     */
    public function append_cache_busting_param_to_attachment_for_js( $response, $attachment ) {
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $recently_replaced_media = isset( $options_extra['recently_replaced_media'] ) ? $options_extra['recently_replaced_media'] : array();
        $attachment_mime_type = get_post_mime_type( $attachment->ID );

        if ( in_array( $attachment->ID, $recently_replaced_media ) 
            // && false !== strpos( $attachment_mime_type, 'image' )
        ) {
            if ( false !== strpos( $response['url'], '?' ) ) {
                $response['url'] .= $this->maybe_append_timestamp_parameter( $response['url'] );
                // vi( $response, '', 'cache busting added via append_cache_busting_param_to_attachment_for_js' );
            }
            if ( isset( $response['sizes'] ) ) {
                foreach ( $response['sizes'] as $size_name => $size ) {
                    $response['sizes'][$size_name]['url'] .= $this->maybe_append_timestamp_parameter( $size['url'] );
                    // vi( $response, '', 'cache busting added via append_cache_busting_param_to_attachment_for_js' );
                }
            }
        }

        return $response;
    }
    
    /**
     * Append cache busting parameter to attachment URL
     * 
     * @since 6.8.2
     */
    public function append_cache_busting_param_to_attachment_url( $url, $attachment_id ) {
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $recently_replaced_media = isset( $options_extra['recently_replaced_media'] ) ? $options_extra['recently_replaced_media'] : array();
        $attachment_mime_type = get_post_mime_type( $attachment_id );

        if ( in_array( $attachment_id, $recently_replaced_media ) 
            // && false !== strpos( $attachment_mime_type, 'image' )
        ) {
            $url .= $this->maybe_append_timestamp_parameter( $url );
            // vi( $url, '', 'cache busting added via append_cache_busting_param_to_attachment_url' );
        }
        
        return $url;
    }
    
    /**
     * Maybe append timestamp parameter
     * 
     * @since 7.7
     */
    public function maybe_append_timestamp_parameter( $url ) {
        $parts = parse_url( $url );
        $additional_url_parameter = '';

        if ( isset( $parts['query'] ) ) {
            parse_str( $parts['query'], $query );

            if ( isset( $query['t'] ) && ! empty( $query['t'] ) ) {
                // Do not add another timestamp parameter
            } else {
                $additional_url_parameter = ( false === strpos( $url, '?' ) ? '?' : '&' ) . 't=' . time();
            }
            
        } else {
                $additional_url_parameter = ( false === strpos( $url, '?' ) ? '?' : '&' ) . 't=' . time();
        }

        return $additional_url_parameter;
    }
    
}