<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Section Nav Shortcode
 * Recursively build a tree of child pages for the given parent page ID.
 *
 * @param int $parent_id
 * @return array
 */
function gca_section_nav_get_page_tree(int $parent_id): array
{
    $pages = get_pages([
        'parent'      => $parent_id,
        'sort_column' => 'menu_order',
        'post_status' => 'publish',
    ]);

    if (empty($pages)) {
        return [];
    }

    $nodes = [];
    foreach ($pages as $page) {
        $nodes[] = [
            'id'       => $page->ID,
            'title'    => $page->post_title,
            'url'      => get_permalink($page->ID),
            'children' => gca_section_nav_get_page_tree($page->ID),
        ];
    }

    return $nodes;
}

/**
 * Render a list of nav nodes as <li> elements.
 *
 * @param array  $nodes
 * @param int[]  $active_ids  IDs of the current item and all its ancestors.
 * @param string $current_url Permalink of the currently viewed page.
 * @return string
 */
function gca_section_nav_render(array $nodes, array $active_ids, string $current_url): string
{
    $html = '';

    foreach ($nodes as $node) {
        $has_children = !empty($node['children']);
        $node_url     = trailingslashit((string) $node['url']);
        $is_current   = ($node_url === $current_url);
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
 * @param array|string $atts  Unused — kept for shortcode API compatibility.
 */
function gca_section_nav_shortcode($atts): string
{
    if (!is_page_template('template-layout-left-nav.php')) {
        return '';
    }

    $current_id = get_queried_object_id();
    if (!$current_id) {
        return '';
    }

    $current_page = get_post($current_id);
    if (!$current_page || $current_page->post_type !== 'page') {
        return '';
    }

    // Walk up to the top-level ancestor so all pages in the hierarchy share the same nav.
    $ancestors = get_post_ancestors($current_id);
    
    if (count($ancestors) <= 1) {
        $section_root_id = $current_id;
    } else {
        $section_root_id = (int) $ancestors[count($ancestors) - 2];
    }

    $section_root_page = get_post($section_root_id);

    // Build the set of active IDs (current page + all its ancestors) for highlighting.
    $active_ids = array_map('intval', $ancestors);
    $active_ids[] = $current_id;

    $current_url = trailingslashit(get_permalink($current_id));

    $children = gca_section_nav_get_page_tree($section_root_id);

    $html  = '<nav class="section-nav" aria-label="Section navigation">';
    $html .= '<div class="section-nav__header">';
    $html .= '<a href="' . esc_url(get_permalink($section_root_id)) . '" class="section-nav__title">';
    $html .= esc_html($section_root_page->post_title);
    $html .= '</a>';
    $html .= '</div>';

    if (!empty($children)) {
        $html .= '<ul class="section-nav__list">';
        $html .= gca_section_nav_render($children, $active_ids, $current_url);
        $html .= '</ul>';
    }

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
