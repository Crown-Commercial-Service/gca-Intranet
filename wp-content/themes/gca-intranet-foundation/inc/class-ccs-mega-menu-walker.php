<?php
class CCS_Mega_Menu_Walker extends Walker_Nav_Menu {
    // 1. Wrap the sub-menu in our mega-menu div
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        if ( $depth === 0 ) {
            // Top level dropdown gets the mega menu wrapper
            $output .= "\n$indent<div class=\"mega-menu-wrapper\"><ul class=\"sub-menu\">\n";
        } else {
            // Deeper levels (if any) just get standard ul
            $output .= "\n$indent<ul class=\"sub-menu\">\n";
        }
    }

    // 2. Close the mega-menu div
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        if ( $depth === 0 ) {
            $output .= "$indent</ul></div>\n";
        } else {
            $output .= "$indent</ul>\n";
        }
    }

    // 3. Add chevrons and ARIA attributes to items with children
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        
        // Check if this item has children
        $has_children = in_array( 'menu-item-has-children', $classes );

        // Add custom classes for our CSS
        if ( $has_children && $depth === 0 ) {
            $classes[] = 'has-mega-menu';
        }
        
        // Add active state class matching your design
        if ( in_array('current-menu-item', $classes) || in_array('current-menu-ancestor', $classes) ) {
            $classes[] = 'active';
        }

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $output .= $indent . '<li' . $class_names . '>';

        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters( 'the_title', $item->title, $item->ID );

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . $title . $args->link_after;
        $item_output .= '</a>';

        if ( $has_children && $depth === 0 ) {
            $item_output .= '<button class="mega-menu-toggle" aria-expanded="false" aria-label="Toggle ' . esc_attr( $title ) . ' submenu">';
            $item_output .= '<svg class="nav-chevron" width="12" height="8" viewBox="0 0 12 8" aria-hidden="true"><path fill="currentColor" d="M1.41 0L6 4.58 10.59 0 12 1.41l-6 6-6-6z"/></svg>';
            $item_output .= '</button>';
        }

        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}
