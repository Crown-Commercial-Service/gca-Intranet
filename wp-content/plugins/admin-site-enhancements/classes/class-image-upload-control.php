<?php

namespace ASENHA\Classes;

use Imagick;
/**
 * Class for Image Upload Control module
 *
 * @since 6.9.5
 */
class Image_Upload_Control {
    public $png_is_transparent;

    /**
     * Array storing the file names that were processed, as keys.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L30
     *
     * @access private
     *
     * @var array
     */
    private $orientation_fixed;

    /**
     * Array storing the meta data of original files in case it
     * needs to be restored later.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L42
     *
     * @access private
     *
     * @var array
     */
    private $previous_meta;

    /**
     * Constructor
     * @since 7.4.3
     */
    function __construct() {
        $this->png_is_transparent = false;
        $this->orientation_fixed = array();
        $this->previous_meta = array();
    }

    /**
     * Handler for image uploads. Convert and resize images.
     *
     * @since 4.3.0
     */
    public function image_upload_handler( $upload ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $applicable_mime_types = array(
            'image/bmp',
            'image/x-ms-bmp',
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/webp'
        );
        $disable_image_conversion = false;
        if ( in_array( $upload['type'], $applicable_mime_types ) ) {
            // Exlude from conversion and resizing images with filenames ending with '-nr', e.g. birds-nr.png
            if ( false !== strpos( $upload['file'], '-nr.' ) ) {
                return $upload;
            }
            // Image conversion is not disabled
            if ( !$disable_image_conversion ) {
                // Convert BMP
                if ( 'image/bmp' === $upload['type'] || 'image/x-ms-bmp' === $upload['type'] ) {
                    $upload = $this->maybe_convert_image( 'bmp', $upload );
                }
                // Convert PNG without transparency
                if ( 'image/png' === $upload['type'] ) {
                    $upload = $this->maybe_convert_image( 'png', $upload );
                }
            }
            // At this point, BMPs and non-transparent PNGs are already converted to JPGs, unless excluded with '-nr' suffix.
            // Let's perform resize operation as needed, i.e. if image dimension is larger than specified
            $mime_types_to_resize = array(
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp'
            );
            if ( !is_wp_error( $upload ) && in_array( $upload['type'], $mime_types_to_resize ) && filesize( $upload['file'] ) > 0 ) {
                // https://developer.wordpress.org/reference/classes/wp_image_editor/
                $wp_image_editor = wp_get_image_editor( $upload['file'] );
                if ( !is_wp_error( $wp_image_editor ) ) {
                    $image_size = $wp_image_editor->get_size();
                    $max_width = $options['image_max_width'];
                    $max_height = $options['image_max_height'];
                    $convert_to_jpg_quality = 82;
                    $did_resize = false;
                    // Check upload image's dimension and only resize if larger than the defined max dimension
                    if ( isset( $image_size['width'] ) && $image_size['width'] > $max_width || isset( $image_size['height'] ) && $image_size['height'] > $max_height ) {
                        $wp_image_editor->resize( $max_width, $max_height, false );
                        // false is for no cropping
                        $did_resize = true;
                    }
                    // Save only when a resize happened.
                    // Avoid re-encoding (and potentially recompressing) images that are already within max dimensions.
                    if ( $did_resize ) {
                        if ( 'image/jpg' === $upload['type'] || 'image/jpeg' === $upload['type'] ) {
                            $wp_image_editor->set_quality( $convert_to_jpg_quality );
                        }
                        $wp_image_editor->save( $upload['file'] );
                    }
                }
            }
        }
        return $upload;
    }

    /**
     * Convert BMP or PNG without transparency into JPG
     *
     * @since 4.3.0
     */
    public function maybe_convert_image( $file_extension, $upload ) {
        $image_object = null;
        // Get image object from uploaded BMP/PNG
        if ( 'bmp' === $file_extension ) {
            if ( is_file( $upload['file'] ) ) {
                // Generate image object from BMP for conversion to JPG later
                if ( function_exists( 'imagecreatefrombmp' ) ) {
                    // PHP >= v7.2
                    $image_object = imagecreatefrombmp( $upload['file'] );
                } else {
                    // PHP < v7.2
                    require_once ASENHA_PATH . 'includes/bmp-to-image-object.php';
                    $image_object = bmp_to_image_object( $upload['file'] );
                }
            }
        }
        if ( 'png' === $file_extension ) {
            // Detect alpha/transparency in PNG
            $this->png_is_transparent = false;
            if ( is_file( $upload['file'] ) ) {
                if ( function_exists( 'imagecreatefrompng' ) ) {
                    // GD library is present, so 'imagecreatefrompng' function is available
                    // Generate image object from PNG for potential conversion to JPG later.
                    $image_object = imagecreatefrompng( $upload['file'] );
                    // Get image dimension
                    list( $width, $height ) = getimagesize( $upload['file'] );
                    // Run through pixels until transparent pixel is found
                    for ($x = 0; $x < $width; $x++) {
                        for ($y = 0; $y < $height; $y++) {
                            $pixel_color_index = imagecolorat( $image_object, $x, $y );
                            $pixel_rgba = imagecolorsforindex( $image_object, $pixel_color_index );
                            // array of red, green, blue and alpha values
                            if ( $pixel_rgba['alpha'] > 0 ) {
                                // a pixel with alpha/transparency has been found
                                // alpha value range from 0 (completely opaque) to 127 (fully transparent).
                                // Ref: https://www.php.net/manual/en/function.imagecolorallocatealpha.php
                                $this->png_is_transparent = true;
                                break 2;
                                // Break both 'for' loops
                            }
                        }
                    }
                } else {
                    if ( class_exists( 'Imagick' ) ) {
                        $imagick = new Imagick();
                        $imagick->readImage( $upload['file'] );
                        // Ref: https://stackoverflow.com/a/52295997
                        // Ref: https://www.php.net/manual/en/imagick.getimagechannelrange.php
                        // If the channel is defined, and has any transparent areas across any frame, then maxima will always be greater then minima.
                        // If the channel is NOT defined, then minima will be Inf placeholder, and maxima will be -Inf placeholder, so the above check will still work.
                        $alpha_range = $imagick->getImageChannelRange( Imagick::CHANNEL_ALPHA );
                        $this->png_is_transparent = $alpha_range['minima'] < $alpha_range['maxima'];
                    }
                }
            }
            // Do not convert PNG with alpha/transparency
            if ( $this->png_is_transparent ) {
                return $upload;
            }
        }
        // Let's convert BMP and non-transparent PNG into JPG
        $converted_to_jpg = false;
        if ( is_object( $image_object ) || class_exists( 'Imagick' ) ) {
            $wp_uploads = wp_upload_dir();
            $old_filename = wp_basename( $upload['file'] );
            // Assign new, unique file name for the converted image
            // $new_filename    = wp_basename( str_ireplace( '.' . $file_extension, '.jpg', $old_filename ) );
            $new_filename = str_ireplace( '.' . $file_extension, '.jpg', $old_filename );
            $new_filename = wp_unique_filename( dirname( $upload['file'] ), $new_filename );
            // original image is always deleted in ASE Free
            $keep_original_image = false;
            $converted_to_jpg = false;
        }
        if ( is_object( $image_object ) ) {
            // When image object creation is successful
            // When conversion from BMP/PNG to JPG is successful using GD. Last parameter is JPG quality (0-100).
            if ( imagejpeg( $image_object, $wp_uploads['path'] . '/' . $new_filename, 90 ) ) {
                $converted_to_jpg = true;
            }
        } else {
            // When image object creation with imagecreatefrombmp(), bmp_to_image_object() or imagecreatefrompng() is not successful, we use Imagick to convert from BMP and non-transparent PNG to JPG.
            if ( class_exists( 'Imagick' ) ) {
                $imagick = new Imagick();
                $imagick->readImage( $upload['file'] );
                $imagick->setImageCompressionQuality( 90 );
                $imagick->setImageFormat( 'jpg' );
                // $imagick->setFormat( 'jpg' );
                if ( $imagick->writeImage( $wp_uploads['path'] . '/' . $new_filename ) ) {
                    $converted_to_jpg = true;
                }
                // Clear the Imagick object
                $imagick->clear();
                $imagick->destroy();
            }
        }
        if ( $converted_to_jpg ) {
            // Delete original BMP / PNG
            if ( !$keep_original_image ) {
                unlink( $upload['file'] );
            }
            // Add converted JPG info into $upload
            $upload['file'] = $wp_uploads['path'] . '/' . $new_filename;
            $upload['url'] = $wp_uploads['url'] . '/' . $new_filename;
            $upload['type'] = 'image/jpeg';
        }
        return $upload;
    }

    /**
     * Generate image object from PNG/JPG with GD library
     * 
     * @since 6.9.11
     */
    public function gd_generate_webp(
        $file,
        $file_extension,
        $webp_path,
        $webp_conversion_quality
    ) {
        if ( 'png' == $file_extension ) {
            $image_object = imagecreatefrompng( $file );
            if ( $this->png_is_transparent ) {
                imagepalettetotruecolor( $image_object );
            }
        }
        if ( 'jpg' == $file_extension || 'jpeg' == $file_extension ) {
            $image_object = imagecreatefromjpeg( $file );
        }
        // When creation of image object from PNG/JPG is successful. let's generate WebP image
        // Second parameter is file path, last parameter is WebP quality (0-100).
        if ( !is_null( $image_object ) && is_object( $image_object ) ) {
            imagewebp( $image_object, $webp_path, $webp_conversion_quality );
        }
    }

    /**
     * Checks the filename before it is uploaded to WordPress and
     * runs the fix_image_orientation function in case its needed.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L172
     *
     * @access public
     *
     * @hook wp_handle_upload_prefilter
     *
     * @param array $file An array of data for a single file.
     *
     * @return array An array of data for a single file.
     */
    public function prefilter_maybe_fix_image_orientation( $file ) {
        // Get the file extension
        // $suffix = substr( $file['name'], strrpos( $file['name'], '.', -1 ) + 1 );
        $suffix = pathinfo( $file['name'], PATHINFO_EXTENSION );
        if ( in_array( strtolower( $suffix ), array('jpg', 'jpeg', 'tiff'), true ) ) {
            $this->fix_image_orientation( $file['tmp_name'] );
        }
        return $file;
    }

    /**
     * Checks the filename before it is uploaded to WordPress and
     * runs the fix_image_orientation function in case its needed.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L150
     *
     * @access public
     *
     * @hook wp_handle_upload
     *
     * @param array $file {
     *    Array of upload data.
     *
     *     @type string $file Filename of the newly-uploaded file.
     *     @type string $url  URL of the uploaded file.
     *     @type string $type File type.
     * }
     *
     * @return array Array of upload data.
     */
    public function maybe_fix_image_orientation( $file ) {
        $suffix = substr( $file['file'], strrpos( $file['file'], '.', -1 ) + 1 );
        if ( in_array( strtolower( $suffix ), array('jpg', 'jpeg', 'tiff'), true ) ) {
            $this->fix_image_orientation( $file['file'] );
        }
        return $file;
    }

    /**
     * Fixes the orientation of the image based on exif data
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L191
     *
     * @access public
     *
     * @param string $file Path of the file.
     *
     * @return void
     */
    public function fix_image_orientation( $file ) {
        if ( !isset( $this->orientation_fixed[$file] ) ) {
            $exif = @exif_read_data( $file );
            if ( isset( $exif ) && isset( $exif['Orientation'] ) && $exif['Orientation'] > 1 ) {
                // Need it so that image editors are available to us.
                // include_once ABSPATH . 'wp-admin/includes/image-edit.php';
                // Calculate the operations we need to perform on the image.
                $operations = $this->calculate_flip_and_rotate( $file, $exif );
                if ( false !== $operations ) {
                    // Lets flip flop and rotate the image as needed.
                    $this->do_flip_and_rotate( $file, $operations );
                }
            }
        }
    }

    /**
     * Calculate the flips and rotations image will need to do to fix its orientation.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L225
     *
     * @access private
     *
     * @param string $file Path of the file.
     *
     * @param array  $exif Exif data of the image.
     *
     * @return array|bool Array of operations to be performed on the image,
     *                    false if no operations are needed.
     */
    private function calculate_flip_and_rotate( $file, $exif ) {
        $rotator = false;
        $flipper = false;
        $orientation = 0;
        // Lets switch to the orientation defined in the exif data.
        switch ( $exif['Orientation'] ) {
            case 1:
                // We don't want to fix an already correct image :).
                $this->orientation_fixed[$file] = true;
                return false;
            case 2:
                $flipper = array(false, true);
                break;
            case 3:
                $orientation = -180;
                $rotator = true;
                break;
            case 4:
                $flipper = array(true, false);
                break;
            case 5:
                $orientation = -90;
                $rotator = true;
                $flipper = array(false, true);
                break;
            case 6:
                $orientation = -90;
                $rotator = true;
                break;
            case 7:
                $orientation = -270;
                $rotator = true;
                $flipper = array(false, true);
                break;
            case 8:
            case 9:
                $orientation = -270;
                $rotator = true;
                break;
            default:
                $orientation = 0;
                $rotator = true;
                break;
        }
        return compact( 'orientation', 'rotator', 'flipper' );
    }

    /**
     * Flips and rotates the image based on the parameters provided.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L299
     *
     * @access private
     *
     * @param string $file Path of the file.
     *
     * @param array  $operations {
     *      Array of operations to be performed on the image.
     *
     *      @type bool       $rotator Whether to rotate the image or not.
     *      @type int        $orientation Amount of rotation to be performed in degrees.
     *      @type array|bool $flipper {
     *          Whether to flip the image or not, false if no flipping needed.
     *
     *          @type bool $0 Flip along Horizontal Axis.
     *          @type bool $1 Flip along Vertical Axis.
     *      }
     * }
     *
     * @return bool Returns true if operations were successful, false otherwise.
     */
    private function do_flip_and_rotate( $file, $operations ) {
        $editor = wp_get_image_editor( $file );
        // If GD Library is being used, then we need to store metadata to restore later.
        if ( 'WP_Image_Editor_GD' === get_class( $editor ) ) {
            include_once ABSPATH . 'wp-admin/includes/image.php';
            $this->previous_meta[$file] = wp_read_image_metadata( $file );
        }
        if ( !is_wp_error( $editor ) ) {
            // Lets rotate and flip the image based on exif orientation.
            if ( true === $operations['rotator'] ) {
                $editor->rotate( $operations['orientation'] );
            }
            if ( false !== $operations['flipper'] ) {
                $editor->flip( $operations['flipper'][0], $operations['flipper'][1] );
            }
            $editor->save( $file );
            $this->orientation_fixed[$file] = true;
            add_filter(
                'wp_read_image_metadata',
                array($this, 'restore_meta_data'),
                10,
                2
            );
            return true;
        }
        return false;
    }

    /**
     * Restores the meta data of the image after being processed.
     *
     * WordPress' Imagick Library does not need this, but GD library
     * removes metadata from the image upon rotation or flip so this
     * method restores those values.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php#L341
     *
     * @hook wp_read_image_metadata
     *
     * @param array  $meta Image meta data.
     * @param string $file Path to image file.
     *
     * @return array Image meta data.
     */
    public function restore_meta_data( $meta, $file ) {
        if ( isset( $this->previous_meta[$file] ) ) {
            $meta = $this->previous_meta[$file];
            // Setting the Orientation meta to the new value after fixing the rotation.
            $meta['orientation'] = 1;
            return $meta;
        }
        return $meta;
    }

}
