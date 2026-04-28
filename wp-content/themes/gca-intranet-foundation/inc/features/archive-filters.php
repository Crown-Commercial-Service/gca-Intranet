<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Archive Filters
//
// Adds sort (newest/oldest) and taxonomy filter controls to the /news, /blog,
// /work_update, and /event landing pages. Renders a filter sidebar alongside
// the post list and reads URL params: sort, filter_category[], filter_label[],
// filter_responsible_team[], filter_event_location[].
// -----------------------------------------------------------------------------

gca_register_feature_flag('archive-filters', [
    'label'       => 'Archive Filters',
    'description' => 'Enables sort and taxonomy filter controls on the News, Blog, Work Update, and Events archive pages.',
    'default'     => true,
    'tags'        => ['news', 'blog', 'work_update', 'event', 'filters'],
]);

/**
 * Apply sort order and taxonomy filters on archive queries.
 *
 * Runs at priority 20 so it fires after the existing event hook in functions.php
 * (priority 10) which sets up the "future events only" meta_query. This hook
 * overrides the sort order for events but leaves that meta_query intact.
 */
add_action('pre_get_posts', function (WP_Query $query): void {
    if (!gca_flag_enabled('archive-filters')) {
        return;
    }

    $is_date_archive  = $query->is_post_type_archive(['news', 'blog', 'work_update']);
    $is_event_archive = $query->is_post_type_archive('event');

    if (!is_admin() && $query->is_main_query() && ($is_date_archive || $is_event_archive)) {
        $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';

        if ($is_date_archive) {
            // Sort by post published date
            $query->set('orderby', 'date');
            $query->set('order', $sort === 'oldest' ? 'ASC' : 'DESC');
        } else {
            // Events: sort by start_date meta. ASC = soonest first (default).
            $query->set('meta_key', 'start_date');
            $query->set('orderby', 'meta_value');
            $query->set('order', $sort === 'oldest' ? 'DESC' : 'ASC');
        }

        // Taxonomy filters
        $tax_map = [
            'filter_category'         => 'category',
            'filter_label'            => 'label',
            'filter_responsible_team' => 'responsible_team',
            'filter_event_location'   => 'event_location',
        ];

        $tax_query = [];
        foreach ($tax_map as $param => $taxonomy) {
            if (empty($_GET[$param]) || !is_array($_GET[$param])) {
                continue;
            }
            $slugs = array_values(array_filter(
                array_map('sanitize_text_field', $_GET[$param]),
                fn($s) => $s !== ''
            ));
            if (!empty($slugs)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $slugs,
                ];
            }
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $query->set('tax_query', $tax_query);
        }
    }
}, 20);

/**
 * Enqueue filter interaction JS on archive pages where the flag is active.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (!gca_flag_enabled('archive-filters')) {
        return;
    }

    if (!is_post_type_archive(['news', 'blog', 'work_update', 'event'])) {
        return;
    }

    wp_register_script('gca-archive-filters', '', [], false, true);
    wp_enqueue_script('gca-archive-filters');
    wp_add_inline_script('gca-archive-filters', <<<'JS'
document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector("[data-archive-filter-form]");
    if (!form) return;

    // Auto-submit when sort radio changes
    form.querySelectorAll("input[name='sort']").forEach(function (radio) {
        radio.addEventListener("change", function () {
            form.submit();
        });
    });

    // View all / individual checkbox logic
    form.querySelectorAll("[data-view-all]").forEach(function (viewAll) {
        var param = viewAll.getAttribute("data-view-all");

        function syncViewAll() {
            var terms = form.querySelectorAll("[data-filter-term='" + param + "']");
            var anyChecked = Array.from(terms).some(function (cb) { return cb.checked; });
            viewAll.checked = !anyChecked;
        }

        // Clicking "View all" unchecks all individual terms
        viewAll.addEventListener("change", function () {
            if (viewAll.checked) {
                form.querySelectorAll("[data-filter-term='" + param + "']").forEach(function (cb) {
                    cb.checked = false;
                });
            }
        });

        // Clicking any individual term unchecks "View all"
        form.querySelectorAll("[data-filter-term='" + param + "']").forEach(function (cb) {
            cb.addEventListener("change", function () {
                syncViewAll();
            });
        });
    });

    // Hide/Show section toggle
    form.querySelectorAll("[data-toggle-section]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var section = btn.closest("[data-filter-section]");
            if (!section) return;
            var body    = section.querySelector("[data-section-body]");
            var label   = btn.querySelector(".archive-filters__toggle-label");
            var expanded = btn.getAttribute("aria-expanded") === "true";

            if (expanded) {
                body.classList.add("is-hidden");
                btn.setAttribute("aria-expanded", "false");
                if (label) label.textContent = "Show";
            } else {
                body.classList.remove("is-hidden");
                btn.setAttribute("aria-expanded", "true");
                if (label) label.textContent = "Hide";
            }
        });
    });
});
JS);
});
