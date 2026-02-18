<?php

/**
 * Get kses ruleset extended to allow svg
 * 
 * @since 6.9.5
 */
function get_kses_with_svg_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );
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
            'stroke-linejoin' => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
        ),
        'title'  => array(
            'title' => true,
        ),
        'path'   => array(
            'd'               => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-linejoin' => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
        ),
        'rect'   => array(
            'width'  => true,
            'height' => true,
            'x'      => true,
            'y'      => true,
            'rx'     => true,
            'ry'     => true,
        ),
        'circle' => array(
            'cx' => true,
            'cy' => true,
            'r'  => true,
        ),
    );
    return array_merge( $kses_defaults, $svg_args );
    // Example usage: wp_kses( $the_svg_icon, get_kses_with_svg_ruleset() );
}

/**
 * Get kses ruleset extended to allow style and script tags
 * 
 * @since 6.9.5
 */
function get_kses_with_style_src_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );
    $style_script_args = array(
        'link'   => array(
            'rel'         => true,
            'href'        => true,
            'sizes'       => true,
            'crossorigin' => true,
        ),
        'style'  => true,
        'script' => array(
            'src' => true,
        ),
    );
    return array_merge( $kses_defaults, $style_script_args );
    // Example usage: wp_kses( $the_html, get_kses_with_style_src_ruleset() );
}

/**
 * Get kses ruleset extended to allow style and script tags
 * 
 * @since 6.9.5
 */
function get_kses_with_style_src_svg_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );
    $style_script_svg_args = array(
        'input'  => array(
            'type'  => true,
            'id'    => true,
            'class' => true,
            'name'  => true,
            'value' => true,
            'style' => true,
        ),
        'style'  => true,
        'script' => array(
            'src' => true,
        ),
        'iframe' => array(
            'title'           => true,
            'name'            => true,
            'wdith'           => true,
            'height'          => true,
            'src'             => true,
            'srcdoc'          => true,
            'align'           => true,
            'frameborder'     => true,
            'scrolling'       => true,
            'allow'           => true,
            'referrerpolicy'  => true,
            'allowfullscreen' => true,
            'loading'         => true,
            'sandbox'         => true,
        ),
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
            'stroke-linejoin' => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
        ),
        'title'  => array(
            'title' => true,
        ),
        'path'   => array(
            'd'               => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-linejoin' => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
        ),
        'rect'   => array(
            'width'  => true,
            'height' => true,
            'x'      => true,
            'y'      => true,
            'rx'     => true,
            'ry'     => true,
        ),
        'circle' => array(
            'cx' => true,
            'cy' => true,
            'r'  => true,
        ),
    );
    return array_merge( $kses_defaults, $style_script_svg_args );
    // Example usage: wp_kses( $the_html, get_kses_with_style_src_svg_ruleset() );
}

/**
 * Get kses ruleset extended to allow input tags
 * 
 * @since 6.9.5
 */
function get_kses_with_custom_html_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );
    $custom_html_args = array(
        'input' => array(
            'type'  => true,
            'id'    => true,
            'class' => true,
            'name'  => true,
            'value' => true,
            'style' => true,
        ),
    );
    return array_merge( $kses_defaults, $custom_html_args );
    // Example usage: wp_kses( $the_html, get_kses_with_custom_html_ruleset() );
}

/**
 * Return an array (json_decode-d) of imported file
 * 
 * @since 7.8.8
 */
function asenha_get_import_content(  $name  ) {
    $file_extension = pathinfo( $_FILES[$name]['name'], PATHINFO_EXTENSION );
    $file_size = $_FILES[$name]['size'];
    // Only process JSON file that do not exceed max upload size
    if ( $file_extension === 'json' && $file_size < wp_max_upload_size() ) {
        $file_name = sanitize_file_name( $_FILES[$name]['name'] );
        $temp_file_path = $_FILES[$name]['tmp_name'];
        if ( is_uploaded_file( $temp_file_path ) ) {
            $file_contents = file_get_contents( $temp_file_path );
            $imported_settings = json_decode( $file_contents, true );
            // vi( $imported_settings );
            return $imported_settings;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
