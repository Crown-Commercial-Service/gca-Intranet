<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Search Filters
//
// Adds a content type filter sidebar to the search results page, allowing
// users to narrow results by post type (News, Blog, Work Updates, Events,
// and optionally Staff if the staff-profiles flag is on), content type
// taxonomy, and audience taxonomy.
//
// URL params:
//   filter_post_type[]    — WP post type slugs
//   filter_content_type[] — content_type taxonomy term slugs
//   filter_audience[]     — audience taxonomy term slugs
// -----------------------------------------------------------------------------

gca_register_feature_flag('search-filters', [
    'label'       => 'Search: Content type filter',
    'description' => 'Show a content type filter sidebar on the search results page.',
    'default'     => true,
    'tags'        => ['search', 'filters'],
    'parent'      => 'archive-filters',
]);

/**
 * Apply post type, content type, and audience filters on search queries.
 *
 * Handles three URL params:
 *   filter_post_type[]    — WP post type slugs (work_update, news, staff)
 *   filter_content_type[] — content_type taxonomy term slugs (guidance, etc.)
 *   filter_audience[]     — static values: 'all_colleagues' or 'line_manager'
 *
 * When post_type + content_type are both active, an OR tax_query is used so
 * non-page post types (which carry no content_type terms) are not excluded.
 * Audience is ANDed on top: 'all_colleagues' excludes the line-manager audience
 * term; 'line_manager' restricts to it. Both selected = no-op (show all).
 */
add_action('pre_get_posts', function (WP_Query $query): void {
    if (!gca_flag_enabled('search-filters')) {
        return;
    }

    if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
        return;
    }

    $raw_post_types = (!empty($_GET['filter_post_type']) && is_array($_GET['filter_post_type']))
        ? array_values(array_filter(array_map('sanitize_text_field', $_GET['filter_post_type']), fn($t) => $t !== 'staff' && $t !== ''))
        : [];

    $raw_content_types = (!empty($_GET['filter_content_type']) && is_array($_GET['filter_content_type']))
        ? array_values(array_filter(array_map('sanitize_text_field', $_GET['filter_content_type']), fn($t) => $t !== ''))
        : [];

    $raw_audiences = (!empty($_GET['filter_audience']) && is_array($_GET['filter_audience']))
        ? array_values(array_filter(array_map('sanitize_text_field', $_GET['filter_audience']), fn($t) => $t !== ''))
        : [];

    $has_post_type_filter    = !empty($raw_post_types);
    $has_content_type_filter = !empty($raw_content_types);
    $has_audience_filter     = !empty($raw_audiences);

    if (!$has_post_type_filter && !$has_content_type_filter && !$has_audience_filter) {
        // Check if only staff was selected — no WP posts needed.
        if (!empty($_GET['filter_post_type'])) {
            $query->set('post__in', [0]); // WHERE ID IN (0) — returns nothing
        }
        return;
    }

    $post_types  = $raw_post_types;
    $tax_clauses = [];

    if ($has_content_type_filter) {
        $post_types[] = 'page';
        $post_types[] = 'post';
        $post_types   = array_unique($post_types);

        if ($has_post_type_filter) {
            // Mixed: keep non-page post types + matching pages.
            // NOT EXISTS ensures work_update/event posts aren't excluded.
            $tax_clauses[] = [
                'relation' => 'OR',
                ['taxonomy' => 'content_type', 'field' => 'slug', 'terms' => $raw_content_types],
                ['taxonomy' => 'content_type', 'operator' => 'NOT EXISTS'],
            ];
        } else {
            // Pages only — filter strictly by content_type term.
            $tax_clauses[] = ['taxonomy' => 'content_type', 'field' => 'slug', 'terms' => $raw_content_types];
        }
    }

    // Audience: static values 'all_colleagues' and 'line_manager'.
    // If both are selected (or neither), the filter is a no-op — show everything.
    $has_all_colleagues = in_array('all_colleagues', $raw_audiences, true);
    $has_line_manager   = in_array('line_manager',   $raw_audiences, true);

    if ($has_audience_filter && !($has_all_colleagues && $has_line_manager)) {
        if ($has_all_colleagues) {
            // Show everything EXCEPT line-manager-only content.
            $tax_clauses[] = [
                'relation' => 'OR',
                ['taxonomy' => 'audience', 'field' => 'slug', 'terms' => ['line-managers'], 'operator' => 'NOT IN'],
                ['taxonomy' => 'audience', 'operator' => 'NOT EXISTS'],
            ];
        } elseif ($has_line_manager) {
            $tax_clauses[] = ['taxonomy' => 'audience', 'field' => 'slug', 'terms' => ['line-managers'], 'operator' => 'IN'];
        }
    }

    if (!empty($tax_clauses)) {
        $tax_query = count($tax_clauses) > 1
            ? array_merge(['relation' => 'AND'], $tax_clauses)
            : $tax_clauses;
        $query->set('tax_query', $tax_query);
    }

    if (!empty($post_types)) {
        $query->set('post_type', $post_types);
    } elseif (!$has_audience_filter) {
        // Only staff selected (no other active filter) — no WP posts needed.
        $query->set('post__in', [0]); // WHERE ID IN (0) — returns nothing
    }
}, 25);

/**
 * Enqueue filter interaction JS on search pages.
 *
 * Uses data-search-filter-form (distinct from data-archive-filter-form) and
 * intercepts only .navigation.pagination links to avoid AJAX-intercepting
 * article result links (which share the same home URL base).
 */
add_action('wp_enqueue_scripts', function (): void {
    if (!gca_flag_enabled('search-filters') || !is_search()) {
        return;
    }

    wp_register_script('gca-search-filters', '', [], false, true);
    wp_enqueue_script('gca-search-filters');
    wp_add_inline_script('gca-search-filters', <<<'JS'
document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector("[data-search-filter-form]");
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
                    bindPaginationLinks();
                    resultsContainer.scrollIntoView({ behavior: "smooth", block: "nearest" });
                }
                if (pushState) {
                    history.pushState({ url: url }, "", url);
                }
            })
            .catch(function () {
                window.location.href = url;
            })
            .finally(function () {
                resultsContainer.classList.remove("is-loading");
            });
    }

    // ── Build the filtered URL from form state ──
    function buildUrl() {
        var params = new URLSearchParams();
        new FormData(form).forEach(function (value, key) {
            params.append(key, value);
        });
        var qs = params.toString();
        return form.action + (qs ? "?" + qs : "");
    }

    // ── Bind only pagination links (not article links) ──
    function bindPaginationLinks() {
        resultsContainer.querySelectorAll(".navigation.pagination a[href]").forEach(function (link) {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                syncFormToUrl(link.href);
                fetchResults(link.href, true);
            });
        });
    }

    // ── Sync form controls to the params in a given URL ──
    function syncFormToUrl(url) {
        var params = new URLSearchParams(url.split("?")[1] || "");

        // Reset all filter checkboxes and set view-all to checked.
        form.querySelectorAll("[data-filter-term]").forEach(function (cb) { cb.checked = false; });
        form.querySelectorAll("[data-view-all]").forEach(function (va) { va.checked = true; });

        params.forEach(function (value, key) {
            var param = key.replace(/\[\]$/, "");
            var cb = form.querySelector(
                "[data-filter-term='" + CSS.escape(param) + "'][value='" + CSS.escape(value) + "']"
            );
            if (cb) {
                cb.checked = true;
                var va = form.querySelector("[data-view-all='" + CSS.escape(param) + "']");
                if (va) va.checked = false;
            }
        });
    }

    // ── View all / individual checkbox logic ──
    // data-view-all="all" covers every [data-filter-term] in the form.
    // data-view-all="<param>" covers only checkboxes for that param.
    form.querySelectorAll("[data-view-all]").forEach(function (viewAll) {
        var param = viewAll.getAttribute("data-view-all");
        var allTermCbs = form.querySelectorAll("[data-filter-term]");
        var termCbs = (param === "all")
            ? allTermCbs
            : form.querySelectorAll("[data-filter-term='" + param + "']");

        function syncViewAll() {
            var anyChecked = Array.from(termCbs).some(function (cb) { return cb.checked; });
            viewAll.checked = !anyChecked;
        }

        viewAll.addEventListener("change", function () {
            if (viewAll.checked) {
                termCbs.forEach(function (cb) { cb.checked = false; });
            }
            fetchResults(buildUrl(), true);
        });

        termCbs.forEach(function (cb) {
            cb.addEventListener("change", function () {
                syncViewAll();
                fetchResults(buildUrl(), true);
            });
        });
    });

    // ── Hide/Show section toggle ──
    form.querySelectorAll("[data-filter-section]").forEach(function (section) {
        var header = section.querySelector(".archive-filters__section-header");
        var btn    = section.querySelector("[data-toggle-section]");
        if (!header || !btn) return;

        header.addEventListener("click", function () {
            var body     = section.querySelector("[data-section-body]");
            var label    = btn.querySelector(".archive-filters__toggle-label");
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

    history.replaceState({ url: location.href }, "", location.href);
    bindPaginationLinks();
});
JS);
});
