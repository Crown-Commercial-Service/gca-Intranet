<?php
/**
 * Fewbricks Template: Accordion
 * Verified Mapping from Array Output
 */

// Top-level fields
$heading       = $row['accordion_accordion_heading'] ?? '';
$intro         = $row['accordion_accordion_introduction'] ?? '';
$open_default  = $row['accordion_accordion_open_all_by_default'] ?? false;
$enable_search = $row['accordion_accordion_enable_search'] ?? false;

// The Repeater Array
$items = $row['accordion_accordion_items'] ?? [];

// Logic for initial state
$expanded_class = ($open_default) ? ' govuk-accordion__section--expanded' : '';

$instance_id  = wp_unique_id('acc-');
$accordion_id = 'accordion-' . $instance_id;
$search_id    = 'glossary-search-' . $instance_id;
?>

<div class="govuk-width-container">
    <?php if ($enable_search) : ?>

    <div class="govuk-grid-row ccs-accordion-glossary">

        <div class="govuk-grid-column-one-third">
            <div class="ccs-accordion-glossary__search">
                <label class="govuk-label govuk-!-font-weight-medium" for="<?php echo esc_attr($search_id); ?>">
                    Search
                </label>
                <div class="ccs-accordion-glossary__search-wrap">
                    <input
                        class="govuk-input ccs-accordion-glossary__search-input"
                        type="search"
                        id="<?php echo esc_attr($search_id); ?>"
                        autocomplete="off"
                        aria-label="Search glossary terms"
                        data-accordion-target="<?php echo esc_attr($accordion_id); ?>"
                    >
                    <button class="ccs-accordion-glossary__search-btn" type="button" aria-label="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false" fill="currentColor">
                            <path d="M7 0a7 7 0 1 0 4.9 11.9l4.3 4.3 1.4-1.4-4.3-4.3A7 7 0 0 0 7 0zm0 2a5 5 0 1 1 0 10A5 5 0 0 1 7 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="govuk-grid-column-two-thirds">

    <?php else : ?>

    <div class="resources-section">

    <?php endif; ?>

            <?php if ($heading) : ?>
                <h2 class="govuk-heading-m resources-section__heading">
                    <?php echo esc_html($heading); ?>
                </h2>
            <?php endif; ?>

            <?php if ($intro) : ?>
                <div class="resources-section__intro">
                    <?php echo wp_kses_post($intro); ?>
                </div>
            <?php endif; ?>

            <?php if ($enable_search) : ?>
            <div id="<?php echo esc_attr($accordion_id); ?>-filter-summary" class="ccs-filters-summary" style="display:none;" aria-live="polite"></div>
            <p class="govuk-body govuk-!-margin-top-4" id="<?php echo esc_attr($accordion_id); ?>-no-results" style="display:none;" aria-live="polite">
                No results found. Try a different search term.
            </p>
            <?php endif; ?>

            <div class="govuk-accordion ccs-accordion govuk-!-margin-top-4" data-module="govuk-accordion" id="<?php echo esc_attr($accordion_id); ?>">

                <?php if (!empty($items)) : ?>
                    <?php foreach ($items as $index => $item) :
                        $loop_id = $index + 1;
                        $title   = $item['items_title'] ?? '';
                        $content = $item['items_content'] ?? '';
                    ?>
                        <div class="govuk-accordion__section<?php echo esc_attr($expanded_class); ?>">
                            <div class="govuk-accordion__section-header">
                                <h2 class="govuk-accordion__section-heading">
                                    <span class="govuk-accordion__section-button" id="<?php echo esc_attr($accordion_id); ?>-heading-<?php echo $loop_id; ?>">
                                        <?php echo esc_html($title); ?>
                                    </span>
                                </h2>
                            </div>
                            <div id="<?php echo esc_attr($accordion_id); ?>-content-<?php echo $loop_id; ?>"
                                 role="region"
                                 class="govuk-accordion__section-content"
                                 aria-labelledby="<?php echo esc_attr($accordion_id); ?>-heading-<?php echo $loop_id; ?>">
                                <div class="wysiwyg-content">
                                    <?php echo apply_filters('the_content', $content); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

    <?php if ($enable_search) : ?>
        </div>
    </div>
    <?php else : ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($enable_search) : ?>
<script>
(function () {
    var openByDefault = <?php echo $open_default ? 'true' : 'false'; ?>;
    var input = document.getElementById('<?php echo esc_js($search_id); ?>');
    var searchWrap = input ? input.closest('.ccs-accordion-glossary__search-wrap') : null;
    var searchBtn = searchWrap ? searchWrap.querySelector('.ccs-accordion-glossary__search-btn') : null;
    if (!input || !searchBtn) return;

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function expandSection(section) {
        section.classList.add('govuk-accordion__section--expanded');
        var content = section.querySelector('.govuk-accordion__section-content');
        if (content) content.removeAttribute('hidden');
        var btn = section.querySelector('.govuk-accordion__section-button');
        if (btn) btn.setAttribute('aria-expanded', 'true');
    }

    function collapseSection(section) {
        section.classList.remove('govuk-accordion__section--expanded');
        var content = section.querySelector('.govuk-accordion__section-content');
        if (content) content.setAttribute('hidden', '');
        var btn = section.querySelector('.govuk-accordion__section-button');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    function preprocessTermBlocks(accordion) {
        accordion.querySelectorAll('.wysiwyg-content').forEach(function (wysiwyg) {
            var nodes = Array.from(wysiwyg.childNodes);
            var hasHr = nodes.some(function (n) { return n.nodeName === 'HR'; });
            if (!hasHr) return;

            var blocks = [];
            var current = [];
            nodes.forEach(function (node) {
                if (node.nodeName === 'HR') {
                    if (current.length) { blocks.push(current); current = []; }
                } else {
                    current.push(node);
                }
            });
            if (current.length) blocks.push(current);

            // Drop blocks that are only whitespace text nodes or empty elements
            blocks = blocks.filter(function (blockNodes) {
                return blockNodes.some(function (n) {
                    return n.textContent && n.textContent.trim() !== '';
                });
            });

            if (blocks.length <= 1) return;

            wysiwyg.innerHTML = '';
            blocks.forEach(function (blockNodes) {
                var div = document.createElement('div');
                div.className = 'ccs-accordion__term-block';
                blockNodes.forEach(function (n) { div.appendChild(n.cloneNode(true)); });
                wysiwyg.appendChild(div);
            });
        });
    }

    function resetSections(sections) {
        sections.forEach(function (section) {
            section.style.display = '';
            section.querySelectorAll('.ccs-accordion__term-block').forEach(function (block) {
                block.style.display = '';
                block.classList.remove('ccs-accordion__term-block--last-visible');
            });
            if (openByDefault) {
                expandSection(section);
            } else {
                collapseSection(section);
            }
        });
    }

    function doSearch() {
        var rawQuery = input.value.trim();
        var query = rawQuery.toLowerCase();
        var accordion = document.getElementById('<?php echo esc_js($accordion_id); ?>');
        if (!accordion) return;

        var noResults = document.getElementById('<?php echo esc_js($accordion_id); ?>-no-results');
        var showAllBtn = accordion.querySelector('.govuk-accordion__show-all');
        var filterSummary = document.getElementById('<?php echo esc_js($accordion_id); ?>-filter-summary');
        var sections = accordion.querySelectorAll('.govuk-accordion__section');

        var params = new URLSearchParams(window.location.search);
        if (query) {
            params.set('termSearch', rawQuery);
        } else {
            params.delete('termSearch');
        }
        var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState(null, '', newUrl);

        if (!query) {
            resetSections(sections);
            if (noResults) noResults.style.display = 'none';
            if (showAllBtn) showAllBtn.style.display = '';
            if (filterSummary) {
                filterSummary.innerHTML = '';
                filterSummary.style.display = 'none';
            }
            return;
        }

        var visibleCount = 0;
        sections.forEach(function (section) {
            var titleEl = section.querySelector('.govuk-accordion__section-button');
            if (!titleEl) return;

            var termBlocks = section.querySelectorAll('.ccs-accordion__term-block');

            if (termBlocks.length > 0) {
                var sectionMatches = false;
                var lastVisible = null;
                termBlocks.forEach(function (block) {
                    block.classList.remove('ccs-accordion__term-block--last-visible');
                    var boldEl = block.querySelector('b, strong');
                    var searchText = boldEl ? boldEl.textContent.trim().toLowerCase() : block.textContent.trim().toLowerCase();
                    var matches = searchText.indexOf(query) !== -1;
                    block.style.display = matches ? '' : 'none';
                    if (matches) { sectionMatches = true; lastVisible = block; }
                });
                if (lastVisible) lastVisible.classList.add('ccs-accordion__term-block--last-visible');
                section.style.display = sectionMatches ? '' : 'none';
                if (sectionMatches) { visibleCount++; expandSection(section); } else { collapseSection(section); }
            } else {
                var contentEl = section.querySelector('.govuk-accordion__section-content');
                var content = contentEl ? contentEl.textContent.trim().toLowerCase() : '';
                var title = titleEl.textContent.trim().toLowerCase();
                var matches = title.indexOf(query) !== -1 || content.indexOf(query) !== -1;
                section.style.display = matches ? '' : 'none';
                if (matches) { visibleCount++; expandSection(section); } else { collapseSection(section); }
            }
        });

        var hasNoResults = visibleCount === 0;
        if (noResults) noResults.style.display = hasNoResults ? '' : 'none';
        if (showAllBtn) showAllBtn.style.display = hasNoResults ? 'none' : '';

        if (filterSummary) {
            filterSummary.style.display = '';
            filterSummary.innerHTML =
                '<div class="ccs-filters-summary__facets">' +
                    '<span class="ccs-filters-summary__label">Containing</span>' +
                    '<ul class="ccs-filters-summary__list">' +
                        '<li class="ccs-filters-summary__facet">' +
                            '<button type="button" class="ccs-filters-summary__facet__cancel">' +
                                escapeHtml(rawQuery) +
                            '</button>' +
                        '</li>' +
                    '</ul>' +
                '</div>';
            filterSummary.querySelector('.ccs-filters-summary__facet__cancel').addEventListener('click', function () {
                input.value = '';
                doSearch();
            });
        }
    }

    searchBtn.addEventListener('click', doSearch);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            doSearch();
        }
    });

    window.addEventListener('load', function () {
        var accordion = document.getElementById('<?php echo esc_js($accordion_id); ?>');
        if (accordion) preprocessTermBlocks(accordion);

        var params = new URLSearchParams(window.location.search);
        var term = params.get('termSearch');
        if (term) {
            input.value = term;
            doSearch();
        }
    });
}());
</script>
<?php endif; ?>
