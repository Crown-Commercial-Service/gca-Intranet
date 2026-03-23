<?php

namespace ASENHA\Classes;

/**
 * Class for Email Address Obfuscator module
 *
 * @since 6.9.5
 */
class Email_Address_Obfuscator {
    /**
     * Cached associative array of allowed upload extensions (lowercase), e.g. [ 'jpg' => true ].
     *
     * Derived from WordPress core `get_allowed_mime_types()`, which respects `upload_mimes` filters.
     *
     * @since 7.??.? (ASE)
     * @var array|null
     */
    private static $allowed_upload_extensions = null;

    /**
     * Get the render mode used by obfuscate shortcode output.
     *
     * @since 8.4.3
     *
     * @return string Render mode.
     */
    private function get_obfuscate_email_render_mode() {
        $render_mode = 'legacy';
        return $render_mode;
    }

    /**
     * Obfuscate email address on the frontend using antispambot() native WP function
     * 
     * @link: https://gist.github.com/eclarrrk/349360b52e8822b69cb6fc499722520f
     * @since 5.5.0
     */
    public function obfuscate_string( $atts ) {
        $atts = shortcode_atts( array(
            'email'   => '',
            'subject' => '',
            'text'    => '',
            'display' => 'newline',
            'link'    => 'no',
            'class'   => '',
        ), $atts );
        $email = sanitize_email( $atts['email'] );
        if ( !is_email( $email ) ) {
            return '';
        }
        $render_mode = $this->get_obfuscate_email_render_mode();
        $css_bidi_styles = '';
        $direction_styles = '';
        if ( !empty( $atts['text'] ) ) {
            $text = esc_html( $atts['text'] );
        } else {
            if ( 'legacy' === $render_mode ) {
                // Reverse email address characters if not in Firefox, which has bug related to unicode-bidi CSS property.
                $http_user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : 'generic' );
                if ( false !== stripos( $http_user_agent, 'firefox' ) || false !== stripos( $http_user_agent, 'iphone' ) ) {
                    // Do nothing. Do not reverse characters.
                    $email_reversed = $email;
                    $email_rev_parts = explode( '@', $email_reversed );
                    $email_rev_parts = array($email_rev_parts[0], $email_rev_parts[1]);
                    $css_bidi_styles = '';
                    $direction_styles = 'direction:rtl;';
                } else {
                    $email_reversed = strrev( $email );
                    $email_rev_parts = explode( '@', $email_reversed );
                    $css_bidi_styles = 'unicode-bidi:bidi-override;';
                    $direction_styles = 'direction:rtl;';
                }
                $random_number = dechex( rand( 1000000, 9999999 ) );
                $text = esc_html( $email_rev_parts[0] ) . '<span style="display:none;">obfsctd-' . esc_html( $random_number ) . '</span>&#64;' . esc_html( $email_rev_parts[1] );
            } else {
                // builder-safe / high-compatibility mode
                $text = antispambot( $email );
            }
        }
        $display = sanitize_text_field( $atts['display'] );
        if ( !in_array( $display, array('newline', 'inline'), true ) ) {
            $display = 'newline';
        }
        if ( 'newline' === $display ) {
            if ( 'legacy' === $render_mode ) {
                $display_css = 'display:flex;justify-content:flex-end;';
            } else {
                $display_css = 'display:block;';
            }
        } else {
            $display_css = 'display:inline;';
        }
        $subject = sanitize_text_field( $atts['subject'] );
        if ( !empty( $subject ) ) {
            $subject = '?subject=' . rawurlencode( $subject );
        }
        $link = sanitize_text_field( $atts['link'] );
        if ( !in_array( $link, array('no', 'yes', 'mailto'), true ) ) {
            $link = 'no';
        }
        $class = sanitize_text_field( $atts['class'] );
        $span_style = $display_css . $css_bidi_styles . $direction_styles;
        return '<span style="' . esc_attr( $span_style ) . '" class="' . esc_attr( $class ) . '">' . $text . '</span>';
    }

    /**
     * Add additional attributes to the list of safe CSS attributes
     * This prevents those attributes from being stripped out when displaying the obfuscated email address
     * 
     * @since 7.3.1
     */
    public function add_additional_attributes_to_safe_css( $css_attributes ) {
        $css_attributes[] = 'display';
        $css_attributes[] = 'unicode-bidi';
        return $css_attributes;
    }

}
