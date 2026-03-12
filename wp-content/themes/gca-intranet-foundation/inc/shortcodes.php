<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Section Nav Shortcode
 */

function gca_section_nav_resolve_menu(string $menu_name): ?\WP_Term
{
    $menu = wp_get_nav_menu_object($menu_name);

    if (!$menu) {
        $locations = get_nav_menu_locations();
        if (!empty($locations[$menu_name])) {
            $menu = wp_get_nav_menu_object((int) $locations[$menu_name]);
        }
    }

    return $menu ?: null;
}

/**
 * @param object[] $menu_items   All items for the menu.
 * @param int      $parent_id   Menu item ID to start from (0 = top level).
 * @return array
 */
function gca_section_nav_build_tree(array $menu_items, int $parent_id): array
{
    $children = [];

    foreach ($menu_items as $item) {
        if ((int) $item->menu_item_parent === $parent_id) {
            $children[] = $item;
        }
    }

    usort($children, static fn ($a, $b) => (int) $a->menu_order - (int) $b->menu_order);

    $nodes = [];
    foreach ($children as $item) {
        $nodes[] = [
            'id'       => (int) $item->ID,
            'title'    => $item->title,
            'url'      => $item->url,
            'children' => gca_section_nav_build_tree($menu_items, (int) $item->ID),
        ];
    }

    return $nodes;
}

function gca_section_nav_url_path(string $url): string
{
    $path = parse_url(trim($url), PHP_URL_PATH);
    return trailingslashit($path ?: '/');
}

/**
 *
 * @param array  $nodes
 * @param string $current_path  Path-only, trailingslashed (e.g. /hr/leave-absence/).
 * @return int[]  IDs from the matched node up through its ancestors.
 */
function gca_section_nav_active_path(array $nodes, string $current_path): array
{
    foreach ($nodes as $node) {
        if (gca_section_nav_url_path($node['url']) === $current_path) {
            return [(int) $node['id']];
        }

        $child_path = gca_section_nav_active_path($node['children'], $current_path);
        if (!empty($child_path)) {
            return array_merge([(int) $node['id']], $child_path);
        }
    }

    return [];
}

/**
 *
 * @param array $nodes
 * @param int[] $active_ids  IDs of the current item and all its ancestors.
 * @param string $current_url
 * @return string
 */
function gca_section_nav_render(array $nodes, array $active_ids, string $current_url): string
{
    $html = '';

    foreach ($nodes as $node) {
        $has_children = !empty($node['children']);
        $is_current   = (gca_section_nav_url_path($node['url']) === $current_url);
        $is_in_path   = in_array((int) $node['id'], $active_ids, true);

        $classes = ['section-nav__item'];
        if ($has_children) {
            $classes[] = 'section-nav__item--has-children';
        }
        if ($is_current) {
            $classes[] = 'section-nav__item--current';
        }
        if ($is_in_path && !$is_current) {
            $classes[] = 'section-nav__item--in-path';
        }

        $link_class   = 'section-nav__link' . ($is_current ? ' section-nav__link--current' : '');
        $aria_current = $is_current ? ' aria-current="page"' : '';

        $html .= '<li class="' . esc_attr(implode(' ', $classes)) . '">';

        if ($has_children) {
            $expanded     = $is_in_path ? 'true' : 'false';
            $toggle_label = sprintf(
                esc_attr__('Toggle %s submenu', 'gca-intranet'),
                $node['title']
            );

            $html .= '<div class="section-nav__row">';
            $html .= '<a href="' . esc_url($node['url']) . '" class="' . esc_attr($link_class) . '"' . $aria_current . '>';
            $html .= esc_html($node['title']);
            $html .= '</a>';
            $html .= '<button type="button" class="section-nav__toggle" aria-expanded="' . $expanded . '" aria-label="' . $toggle_label . '">';
            $html .= '<svg class="section-nav__arrow" xmlns="http://www.w3.org/2000/svg" width="5" height="8" viewBox="0 0 5 8" fill="none" aria-hidden="true" focusable="false">';
            $html .= '<path d="M3.06667 4L0 0.933333L0.933333 0L4.93333 4L0.933333 8L0 7.06667L3.06667 4Z" fill="currentColor"/>';
            $html .= '</svg>';
            $html .= '</button>';
            $html .= '</div>';

            $html .= '<ul class="section-nav__sublist"' . ($is_in_path ? '' : ' hidden') . '>';
            $html .= gca_section_nav_render($node['children'], $active_ids, $current_url);
            $html .= '</ul>';
        } else {
            $html .= '<a href="' . esc_url($node['url']) . '" class="' . esc_attr($link_class) . '"' . $aria_current . '>';
            $html .= esc_html($node['title']);
            $html .= '</a>';
        }

        $html .= '</li>';
    }

    return $html;
}

/**
 * @param array|string $atts
 */
function gca_section_nav_shortcode($atts): string
{
    $atts = shortcode_atts(
        ['name' => 'primary'],
        (array) $atts,
        'menu'
    );

    $menu = gca_section_nav_resolve_menu($atts['name']);
    if (!$menu) {
        return '<!-- section_nav: menu not found -->';
    }

    $menu_items = wp_get_nav_menu_items($menu->term_id);
    if (empty($menu_items)) {
        return '<!-- section_nav: menu has no items -->';
    }

    $tree = gca_section_nav_build_tree($menu_items, 0);

    $current_path = gca_section_nav_url_path(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $active_ids   = gca_section_nav_active_path($tree, $current_path);

    $html  = '<nav class="section-nav" aria-label="Section navigation">';
    $html .= '<ul class="section-nav__list">';
    $html .= gca_section_nav_render($tree, $active_ids, $current_path);
    $html .= '</ul>';
    $html .= '</nav>';

    return $html;
}

add_shortcode('menu', 'gca_section_nav_shortcode');

add_action('wp_footer', function (): void {
    ?>
    <script>
    (function () {
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".section-nav__toggle").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    var expanded = this.getAttribute("aria-expanded") === "true";
                    this.setAttribute("aria-expanded", expanded ? "false" : "true");
                    var item    = this.closest(".section-nav__item");
                    var sublist = item ? item.querySelector(".section-nav__sublist") : null;
                    if (sublist) {
                        if (expanded) {
                            sublist.setAttribute("hidden", "");
                        } else {
                            sublist.removeAttribute("hidden");
                        }
                    }
                });
            });
        });
    })();
    </script>
    <?php
});
