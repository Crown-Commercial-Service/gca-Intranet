<?php
/**
 * GCA Bootstrap 5 Nav Walker (Split Toggle, accessible)
 * - Parent remains a normal clickable <a> (navigates)
 * - Separate <button> toggles dropdown (so you don't lose the click target)
 * - Depth 2 (top level + one dropdown level)
 * - Adds aria-controls / aria-labelledby and sets aria-current on active items
 */

if (!defined('ABSPATH')) { exit; }

class GCA_Bootstrap_5_Navwalker extends Walker_Nav_Menu {

  /** @var string|null */
  private $current_toggle_id = null;

  /** @var string|null */
  private $current_menu_id = null;

  public function start_lvl( &$output, $depth = 0, $args = null ) {
    $indent = str_repeat("\t", $depth);

    $labelledby = $this->current_toggle_id ? ' aria-labelledby="' . esc_attr($this->current_toggle_id) . '"' : '';
    $menu_id    = $this->current_menu_id   ? ' id="' . esc_attr($this->current_menu_id) . '"' : '';

    $output .= "\n$indent<ul class=\"dropdown-menu\"$menu_id$labelledby>\n";
  }

  public function end_lvl( &$output, $depth = 0, $args = null ) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent</ul>\n";
  }

  public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
    $indent = ($depth) ? str_repeat("\t", $depth) : '';

    $classes      = empty($item->classes) ? [] : (array) $item->classes;
    $has_children = in_array('menu-item-has-children', $classes, true);

    // Reset per item so IDs don't leak between dropdowns
    $this->current_toggle_id = null;
    $this->current_menu_id   = null;

    $is_active = in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true);

    // LI classes
    $li_classes = [];
    if ($depth === 0) {
      $li_classes[] = 'nav-item';
    }

    if ($has_children && $depth === 0) {
      $li_classes[] = 'dropdown';
      $li_classes[] = 'gca-split-dd'; // styling hook (your CSS)
    }

    if ($is_active) {
      $li_classes[] = 'active';
    }

    $output .= $indent . '<li class="' . esc_attr(implode(' ', array_map('sanitize_html_class', $li_classes))) . '">';

    $title = apply_filters('the_title', $item->title, $item->ID);

    /**
     * TOP LEVEL
     */
    if ($depth === 0) {

      $href = !empty($item->url) ? $item->url : '#';

      // Link attributes (keeps WP features like target, rel, title etc.)
      $link_atts = [
        'class' => 'nav-link gca-parent-link',
        'href'  => $href,
      ];

      if (!empty($item->target))     { $link_atts['target'] = $item->target; }
      if (!empty($item->xfn))        { $link_atts['rel']    = $item->xfn; }
      if (!empty($item->attr_title)) { $link_atts['title']  = $item->attr_title; }
      if ($is_active)                { $link_atts['aria-current'] = 'page'; }

      $link_atts = apply_filters('nav_menu_link_attributes', $link_atts, $item, $args, $depth);

      $attributes = '';
      foreach ($link_atts as $attr => $value) {
        if ($value === '' || $value === null) continue;
        $value = ($attr === 'href') ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }

      $item_output  = ($args->before ?? '');
      $item_output .= '<a' . $attributes . '>';
      $item_output .= ($args->link_before ?? '') . esc_html($title) . ($args->link_after ?? '');
      $item_output .= '</a>';

      // Split toggle button (only for parents with children)
      if ($has_children) {
        $toggle_id = 'nav-dd-toggle-' . (int) $item->ID;
        $menu_id   = 'nav-dd-menu-'   . (int) $item->ID;

        $this->current_toggle_id = $toggle_id;
        $this->current_menu_id   = $menu_id;

        // IMPORTANT: dropdown-toggle class is what draws the caret via ::after
        $item_output .= '<button'
          . ' class="nav-link gca-dd-toggle dropdown-toggle dropdown-toggle-split"'
          . ' id="' . esc_attr($toggle_id) . '"'
          . ' type="button"'
          . ' data-bs-toggle="dropdown"'
          . ' aria-haspopup="true"'
          . ' aria-controls="' . esc_attr($menu_id) . '"'
          . ' aria-expanded="false"'
          . ' aria-label="' . esc_attr(sprintf(__('Toggle submenu for %s', 'gca-intranet'), $title)) . '"'
          . '>'
          . '<span class="visually-hidden">' . esc_html__('Toggle submenu', 'gca-intranet') . '</span>'
          . '</button>';
      }

      $item_output .= ($args->after ?? '');

      $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
      return;
    }

    /**
     * DROPDOWN (DEPTH 1)
     */
    $href = !empty($item->url) ? $item->url : '#';

    $drop_atts = [
      'class' => 'dropdown-item',
      'href'  => $href,
    ];

    if (!empty($item->target))     { $drop_atts['target'] = $item->target; }
    if (!empty($item->xfn))        { $drop_atts['rel']    = $item->xfn; }
    if (!empty($item->attr_title)) { $drop_atts['title']  = $item->attr_title; }
    if ($is_active)                { $drop_atts['aria-current'] = 'page'; }

    $drop_atts = apply_filters('nav_menu_link_attributes', $drop_atts, $item, $args, $depth);

    $attributes = '';
    foreach ($drop_atts as $attr => $value) {
      if ($value === '' || $value === null) continue;
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