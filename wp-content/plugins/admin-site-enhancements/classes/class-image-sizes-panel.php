<?php

namespace ASENHA\Classes;

/**
 * Class for Image Sizes Panel module
 *
 * @since 6.9.5
 */
class Image_Sizes_Panel {
    /**
     * Add image sizes meta box in image view/edit screen
     * 
     * @since 6.3.0
     */
    public function add_image_sizes_meta_box() {
        global $post;
        // Only add meta box if the attachment is an image
        if ( is_object( $post ) && property_exists( $post, 'post_mime_type' ) && false !== strpos( $post->post_mime_type, 'image' ) ) {
            add_meta_box(
                'image_sizes',
                'Image Sizes',
                [$this, 'image_sizes_table'],
                'attachment',
                'side'
            );
        }
    }

    /**
     * Output table of image sizes
     * 
     * @link https://plugins.trac.wordpress.org/browser/image-sizes-panel/tags/0.4/admin/admin.php
     * @since 6.3.0
     */
    public function image_sizes_table( $post ) {
        global $_wp_additional_image_sizes;
        $image_sizes = get_intermediate_image_sizes();
        $metadata = wp_get_attachment_metadata( $post->ID );
        $generated_sizes = array();
        // Merge defined image sizes with generated image sizes
        if ( isset( $metadata['sizes'] ) && count( $metadata['sizes'] ) > 0 ) {
            $generated_sizes = array_keys( $metadata['sizes'] );
            $image_sizes = array_unique( array_merge( $image_sizes, $generated_sizes ) );
        }
        $image_sizes[] = 'full';
        $full = wp_get_attachment_image_src( $post->ID, 'full' );
        sort( $image_sizes );
        if ( count( $image_sizes ) > 0 ) {
            echo '<table>';
            foreach ( $image_sizes as $size ) {
                $src = wp_get_attachment_image_src( $post->ID, $size );
                if ( isset( $metadata['sizes'][$size] ) ) {
                    $width = $metadata['sizes'][$size]['width'];
                    $height = $metadata['sizes'][$size]['height'];
                } else {
                    if ( 'full' == $size ) {
                        $width = $full[1];
                        $height = $full[2];
                    } else {
                        $width = $src[1];
                        $height = $src[2];
                    }
                }
                if ( in_array( $size, $generated_sizes ) || 'full' == $size ) {
                    echo '<tr id="image-sizes-panel-' . sanitize_html_class( $size ) . '" class="image-size-row">';
                    echo '<td class="size"><span class="name"><a href="' . esc_url( $src[0] ) . '" target="_blank" class="image-url">' . esc_html( $size ) . '</a></span></td>';
                    echo '<td class="dim">' . esc_html( $width ) . ' &times ' . esc_html( $height ) . '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        } else {
            echo '<p>No image sizes</p>';
        }
    }

}
