<?php
/**
 * GCA Bootstrap 5 Nav Walker (Split Toggle)
 * - Parent remains a navigable <a>
 * - Separate <button> toggles dropdown
 * - Supports depth 2
 */

if (!defined('ABSPATH')) { exit; }

class GCA_Bootstrap_5_Navwalker extends Walker_Nav_Menu {

  public function start_lvl( &$output, $depth = 0, $args = null ) {
    $indent = str_repeat("\t", $depth);
    $output .= "\n$indent<ul class=\"dropdown-menu\">\n";
  }

  public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
    $indent = ($depth) ? str_repeat("\t", $depth) : '';

    $classes = empty($item->classes) ? [] : (array) $item->classes;
    $has_children = in_array('menu-item-has-children', $classes, true);

    // LI classes
    $li_classes = ($depth === 0) ? ['nav-item'] : [];
    if ($has_children && $depth === 0) {
      $li_classes[] = 'dropdown';
      // lets link + toggle sit nicely inline
      $li_classes[] = 'd-flex';
      $li_classes[] = 'align-items-stretch';
    }

    if (in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true)) {
      if ($depth === 0) $li_classes[] = 'active';
    }

    $output .= $indent . '<li class="' . esc_attr(implode(' ', array_map('sanitize_html_class', $li_classes))) . '">';

    // Title
    $title = apply_filters('the_title', $item->title, $item->ID);

    // Normal link (parent remains navigable)
    if ($depth === 0) {
      $link_atts = [
        'class' => 'nav-link',
        'href'  => !empty($item->url) ? $item->url : '',
      ];

      $attributes = '';
      foreach ($link_atts as $attr => $value) {
        if ($value === '') continue;
        $value = ($attr === 'href') ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }

      $item_output  = ($args->before ?? '');
      $item_output .= '<a' . $attributes . '>';
      $item_output .= ($args->link_before ?? '') . esc_html($title) . ($args->link_after ?? '');
      $item_output .= '</a>';

      // Split toggle button (only for top-level items with children)
      if ($has_children) {
        // unique id to associate toggle with menu (helpful for aria-controls)
        $toggle_id = 'nav-dd-toggle-' . $item->ID;

        $item_output .= '<button'
          . ' class="nav-link dropdown-toggle dropdown-toggle-split gca-dd-toggle"'
          . ' id="' . esc_attr($toggle_id) . '"'
          . ' type="button"'
          . ' data-bs-toggle="dropdown"'
          . ' aria-expanded="false"'
          . ' aria-label="' . esc_attr(sprintf(__('Toggle submenu for %s', 'gca'), $title)) . '"'
          . '>'
          . '<span class="visually-hidden">' . esc_html__('Toggle submenu', 'gca') . '</span>'
          . '</button>';
      }

      $item_output .= ($args->after ?? '');

      $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
      return;
    }

    // Dropdown items (depth 1)
    $link_atts = [
      'class' => 'dropdown-item',
      'href'  => !empty($item->url) ? $item->url : '',
    ];

    $attributes = '';
    foreach ($link_atts as $attr => $value) {
      if ($value === '') continue;
      $value = ($attr === 'href') ? esc_url($value) : esc_attr($value);
      $attributes .= ' ' . $attr . '="' . $value . '"';
    }

    $item_output  = ($args->before ?? '');
    $item_output .= '<a' . $attributes . '>';
    $item_output .= ($args->link_before ?? '') . esc_html($title) . ($args->link_after ?? '');
    $item_output .= '</a>';
    $item_output .= ($args->after ?? '');

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }

  public function end_el( &$output, $item, $depth = 0, $args = null ) {
    $output .= "</li>\n";
  }
}
