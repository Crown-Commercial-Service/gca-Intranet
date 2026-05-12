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
// and optionally Staff if the staff-profiles flag is on).
//
// URL param: filter_post_type[] (e.g. filter_post_type[]=news&filter_post_type[]=blog)
// -----------------------------------------------------------------------------

gca_register_feature_flag('search-filters', [
    'label'       => 'Search: Content type filter',
    'description' => 'Show a content type filter sidebar on the search results page.',
    'default'     => true,
    'tags'        => ['search', 'filters'],
    'parent'      => 'archive-filters',
]);

/**
 * Apply post type and content type filters on search queries.
 *
 * Handles two URL params:
 *   filter_post_type[]    — WP post type slugs (work_update, event, staff)
 *   filter_content_type[] — content_type taxonomy term slugs (guidance, etc.)
 *
 * When both are active the query uses an OR tax_query so that non-page post
 * types (which have no content_type terms) are not inadvertently excluded.
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

    $has_post_type_filter    = !empty($raw_post_types);
    $has_content_type_filter = !empty($raw_content_types);

    if (!$has_post_type_filter && !$has_content_type_filter) {
        // Check if only staff was selected — no WP posts needed.
        if (!empty($_GET['filter_post_type'])) {
            $query->set('posts_per_page', 0);
        }
        return;
    }

    $post_types = $raw_post_types;

    if ($has_content_type_filter) {
        $post_types[] = 'page';
        $post_types = array_unique($post_types);

        if ($has_post_type_filter) {
            // Mixed: keep non-page post types (no content_type terms) + matching pages.
            // NOT EXISTS ensures work_update/event posts aren't excluded by the tax_query.
            $query->set('tax_query', [
                'relation' => 'OR',
                ['taxonomy' => 'content_type', 'field' => 'slug', 'terms' => $raw_content_types],
                ['taxonomy' => 'content_type', 'operator' => 'NOT EXISTS'],
            ]);
        } else {
            // Pages only — filter strictly by content_type term.
            $query->set('tax_query', [
                ['taxonomy' => 'content_type', 'field' => 'slug', 'terms' => $raw_content_types],
            ]);
        }
    }

    if (!empty($post_types)) {
        $query->set('post_type', $post_types);
    } else {
        // Only staff selected — no WP posts needed.
        $query->set('posts_per_page', 0);
    }
}, 20);

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
            var anyChecked = Array.from(allTermCbs).some(function (cb) { return cb.checked; });
            viewAll.checked = !anyChecked;
        }

        viewAll.addEventListener("change", function () {
            if (viewAll.checked) {
                allTermCbs.forEach(function (cb) { cb.checked = false; });
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

    history.replaceState({ url: location.href }, "", location.href);
    bindPaginationLinks();
});
JS);
});
