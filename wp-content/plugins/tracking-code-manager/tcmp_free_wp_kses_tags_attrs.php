<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

global $tcmp_allowed_html_tags;
$tcmp_allowed_atts                  = array(
    'action'             => array(),
    'align'              => array(),
    'alt'                => array(),
    'async'              => array(),
    'class'              => array(),
    'content'            => array(),
    'crossorigin'        => array(),
    'data-blockingmode'  => array(),
    'data-cbid'          => array(),
    'data-form-block-id' => array(),
    'data-hostname'      => array(),
    'data-website-id'    => array(),
    'data'               => array(),
    'defer'              => array(),
    'dir'                => array(),
    'for'                => array(),
    'height'             => array(),
    'href'               => array(),
    'id'                 => array(),
    'integrity'          => array(),
    'lang'               => array(),
    'loading'            => array(),
    'method'             => array(),
    'name'               => array(),
    'nomodule'           => array(),
    'novalidate'         => array(),
    'onload'             => array(),
    'referrerpolicy'     => array(),
    'rel'                => array(),
    'rev'                => array(),
    'sandbox'            => array(),
    'src'                => array(),
    'style'              => array(),
    'tabindex'           => array(),
    'target'             => array(),
    'title'              => array(),
    'type'               => array(),
    'uetq'               => array(),
    'value'              => array(),
    'width'              => array(),
    'xml:lang'           => array(),
);
$tcmp_allowed_html_tags['a']        = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['abbr']     = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['b']        = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['body']     = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['br']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['code']     = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['div']      = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['em']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['form']     = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['h1']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['h2']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['h3']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['h4']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['h5']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['h6']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['hr']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['i']        = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['iframe']   = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['img']      = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['input']    = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['label']    = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['li']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['meta']     = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['noscript'] = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['ol']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['p']        = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['pre']      = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['script']   = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['small']    = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['span']     = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['strong']   = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['style']    = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['table']    = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['td']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['textarea'] = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['tr']       = $tcmp_allowed_atts;
$tcmp_allowed_html_tags['ul']       = $tcmp_allowed_atts;
global $tcmp_default_tags;
global $tcmp_default_attrs;
$tcmp_default_tags  = $tcmp_allowed_html_tags;
$tcmp_default_attrs = $tcmp_allowed_atts;

function tcmp_free_add_additional_tags_atts() {
    global $tcmp;
    global $tcmp_allowed_html_tags;
    global $tcmp_allowed_atts;
    global $tcmp_default_tags;
    global $tcmp_default_attrs;

    $tags  = explode( ',', sanitize_text_field( $tcmp->options->getAdditionalRecognizedTags() ) );
    $attrs = explode( ',', sanitize_text_field( $tcmp->options->getAdditionalRecognizedAttributes() ) );

    $remove       = false;
    $update_attrs = array();
    foreach ( $attrs as $a ) {
        $a = trim( $a );
        if ( strlen( $a ) > 0 ) {
            if ( ! isset( $tcmp_allowed_atts[ $a ] ) ) {
                $tcmp_allowed_atts[ $a ] = array();
                $update_attrs[]          = $a;
            } else {
                if ( isset( $tcmp_default_attrs[ $a ] ) ) {
                    $tcmp->options->pushInfoMessage( '<span style="text-transform:uppercase"><strong>' . $a . '</strong></span> is already in the attribute whitelist' );
                    $remove = true;
                }
            }
        }
    }
    if ( $remove ) {
        $new = implode( ',', $update_attrs );
        $tcmp->options->setAdditionalRecognizedAttributes( $new );
    }

    $remove      = false;
    $update_tags = array();
    foreach ( $tags as $t ) {
        $t = trim( $t );
        if ( strlen( $t ) > 0 ) {
            if ( ! isset( $tcmp_allowed_html_tags[ $t ] ) ) {
                $tcmp_allowed_html_tags[ $t ] = array();
                $update_tags[]                = $t;
            } else {
                if ( isset( $tcmp_default_tags[ $t ] ) ) {
                    $tcmp->options->pushInfoMessage( '<span style="text-transform:uppercase"><strong>' . $t . '</strong></span> is already in the tag whitelist' );
                    $remove = true;
                }
            }
        }
    }
    if ( $remove ) {
        $new = implode( ',', $update_tags );
        $tcmp->options->setAdditionalRecognizedTags( $new );
    }

    foreach ( $tcmp_allowed_html_tags as $key => $value ) {
        $tcmp_allowed_html_tags[ $key ] = $tcmp_allowed_atts;
    }
}

function tcmp_free_jetpack_shortcode_callback( $shortcode_includes ) {
    unset( $shortcode_includes['class.filter-embedded-html-objects'] );
    return $shortcode_includes;
}

add_filter( 'jetpack_shortcodes_to_include', 'tcmp_free_jetpack_shortcode_callback' );
