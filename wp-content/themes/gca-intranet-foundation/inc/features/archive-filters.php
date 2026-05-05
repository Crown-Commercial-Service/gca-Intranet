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

// Per-taxonomy toggles — each controls one filter section on its respective archive.
gca_register_feature_flag('archive-filter-news-category', [
    'label'       => 'News: Category filter',
    'description' => 'Show the Category filter on the News archive page.',
    'default'     => true,
    'tags'        => ['news', 'filters'],
    'parent'      => 'archive-filters',
]);

gca_register_feature_flag('archive-filter-news-label', [
    'label'       => 'News: Type of article filter',
    'description' => 'Show the Type of article (Label) filter on the News archive page.',
    'default'     => true,
    'tags'        => ['news', 'filters'],
    'parent'      => 'archive-filters',
]);

gca_register_feature_flag('archive-filter-blog-label', [
    'label'       => 'Blog: Type of article filter',
    'description' => 'Show the Type of article (Label) filter on the Blog archive page.',
    'default'     => true,
    'tags'        => ['blog', 'filters'],
    'parent'      => 'archive-filters',
]);

gca_register_feature_flag('archive-filter-event-category', [
    'label'       => 'Events: Category filter',
    'description' => 'Show the Category filter on the Events archive page.',
    'default'     => true,
    'tags'        => ['event', 'filters'],
    'parent'      => 'archive-filters',
]);

gca_register_feature_flag('archive-filter-event-location', [
    'label'       => 'Events: Location filter',
    'description' => 'Show the Event Location filter on the Events archive page.',
    'default'     => true,
    'tags'        => ['event', 'filters'],
    'parent'      => 'archive-filters',
]);

gca_register_feature_flag('archive-filter-work_update-label', [
    'label'       => 'Work Updates: Type of article filter',
    'description' => 'Show the Type of article (Label) filter on the Work Updates archive page.',
    'default'     => true,
    'tags'        => ['work_update', 'filters'],
    'parent'      => 'archive-filters',
]);

gca_register_feature_flag('archive-filter-work_update-responsible_team', [
    'label'       => 'Work Updates: Responsible Team filter',
    'description' => 'Show the Responsible Team filter on the Work Updates archive page.',
    'default'     => true,
    'tags'        => ['work_update', 'filters'],
    'parent'      => 'archive-filters',
]);

/**
 * Return the enabled taxonomy filter definitions for a given post type.
 *
 * Each entry: ['taxonomy' => string, 'label' => string, 'param' => string]
 * Only entries whose per-taxonomy feature flag is enabled are included.
 */
function gca_get_archive_filter_taxonomies(string $post_type): array {
    $map = [
        'news' => [
            ['taxonomy' => 'category', 'label' => 'Category',        'param' => 'filter_category',   'flag' => 'archive-filter-news-category'],
            ['taxonomy' => 'label',    'label' => 'Type of article', 'param' => 'filter_label',       'flag' => 'archive-filter-news-label'],
        ],
        'blog' => [
            ['taxonomy' => 'label',    'label' => 'Type of article', 'param' => 'filter_label',       'flag' => 'archive-filter-blog-label'],
        ],
        'event' => [
            ['taxonomy' => 'category',       'label' => 'Category', 'param' => 'filter_category',       'flag' => 'archive-filter-event-category'],
            ['taxonomy' => 'event_location', 'label' => 'Location', 'param' => 'filter_event_location', 'flag' => 'archive-filter-event-location'],
        ],
        'work_update' => [
            ['taxonomy' => 'label',            'label' => 'Type of article',  'param' => 'filter_label',            'flag' => 'archive-filter-work_update-label'],
            ['taxonomy' => 'responsible_team', 'label' => 'Responsible Team', 'param' => 'filter_responsible_team', 'flag' => 'archive-filter-work_update-responsible_team'],
        ],
    ];

    $definitions = $map[$post_type] ?? [];

    return array_values(array_filter(
        $definitions,
        fn(array $def) => gca_flag_enabled($def['flag'])
    ));
}

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

    var resultsContainer = document.querySelector(".archive-layout__results");
    if (!resultsContainer) return;

    // ── AJAX fetch: replace results, optionally push a history entry ──
    function fetchResults(url, pushState) {
        resultsContainer.classList.add("is-loading");

        fetch(url)
            .then(function (res) { return res.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, "text/html");
                var newResults = doc.querySelector(".archive-layout__results");
                if (newResults) {
                    resultsContainer.innerHTML = newResults.innerHTML;
                    bindResultLinks();
                    resultsContainer.scrollIntoView({ behavior: "smooth", block: "nearest" });
                }
                if (pushState) {
                    history.pushState({ url: url }, "", url);
                }
            })
            .catch(function () {
                // Network error — fall back to a normal navigation
                window.location.href = url;
            })
            .finally(function () {
                resultsContainer.classList.remove("is-loading");
            });
    }

    // ── Build the filtered URL from the current form state ──
    function buildUrl() {
        var params = new URLSearchParams();
        new FormData(form).forEach(function (value, key) {
            params.append(key, value);
        });
        var qs = params.toString();
        return form.action + (qs ? "?" + qs : "");
    }

    // ── Bind pagination / any same-archive link inside the results ──
    function bindResultLinks() {
        var archiveBase = form.action.replace(/\/$/, "");
        resultsContainer.querySelectorAll("a[href]").forEach(function (link) {
            if (link.href.indexOf(archiveBase) === 0) {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    syncFormToUrl(link.href);
                    fetchResults(link.href, true);
                });
            }
        });
    }

    // ── Sync form controls to the params in a given URL ──
    function syncFormToUrl(url) {
        var params = new URLSearchParams(url.split("?")[1] || "");

        // Reset everything first
        form.querySelectorAll("[data-filter-term]").forEach(function (cb) { cb.checked = false; });
        form.querySelectorAll("[data-view-all]").forEach(function (va) { va.checked = true; });

        params.forEach(function (value, key) {
            var param = key.replace(/\[\]$/, "");
            if (key === "sort") {
                var radio = form.querySelector("input[name='sort'][value='" + CSS.escape(value) + "']");
                if (radio) radio.checked = true;
            } else {
                var cb = form.querySelector(
                    "[data-filter-term='" + CSS.escape(param) + "'][value='" + CSS.escape(value) + "']"
                );
                if (cb) {
                    cb.checked = true;
                    var va = form.querySelector("[data-view-all='" + CSS.escape(param) + "']");
                    if (va) va.checked = false;
                }
            }
        });
    }

    // ── Sort radio ──
    form.querySelectorAll("input[name='sort']").forEach(function (radio) {
        radio.addEventListener("change", function () {
            fetchResults(buildUrl(), true);
        });
    });

    // ── View all / individual checkbox logic ──
    form.querySelectorAll("[data-view-all]").forEach(function (viewAll) {
        var param = viewAll.getAttribute("data-view-all");

        function syncViewAll() {
            var terms = form.querySelectorAll("[data-filter-term='" + param + "']");
            var anyChecked = Array.from(terms).some(function (cb) { return cb.checked; });
            viewAll.checked = !anyChecked;
        }

        viewAll.addEventListener("change", function () {
            if (viewAll.checked) {
                form.querySelectorAll("[data-filter-term='" + param + "']").forEach(function (cb) {
                    cb.checked = false;
                });
            }
            fetchResults(buildUrl(), true);
        });

        form.querySelectorAll("[data-filter-term='" + param + "']").forEach(function (cb) {
            cb.addEventListener("change", function () {
                syncViewAll();
                fetchResults(buildUrl(), true);
            });
        });
    });

    // ── Hide/Show section toggle ──
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

    // ── Browser back / forward ──
    window.addEventListener("popstate", function (e) {
        var url = (e.state && e.state.url) ? e.state.url : location.href;
        syncFormToUrl(url);
        fetchResults(url, false);
    });

    // Replace the initial history entry so the back button can return here
    history.replaceState({ url: location.href }, "", location.href);

    // Bind links on first paint
    bindResultLinks();
});
JS);
});
