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
    var input = document.getElementById('<?php echo esc_js($search_id); ?>');
    if (!input) return;

    input.addEventListener('input', function () {
        var query = this.value.trim().toLowerCase();
        var accordion = document.getElementById('<?php echo esc_js($accordion_id); ?>');
        if (!accordion) return;

        var noResults = document.getElementById('<?php echo esc_js($accordion_id); ?>-no-results');
        var showAllBtn = accordion.querySelector('.govuk-accordion__show-all');

        var sections = accordion.querySelectorAll('.govuk-accordion__section');
        var visibleCount = 0;
        sections.forEach(function (section) {
            var btn = section.querySelector('.govuk-accordion__section-button');
            if (!btn) return;
            var title = btn.textContent.trim().toLowerCase();
            var contentEl = section.querySelector('.govuk-accordion__section-content');
            var content = contentEl ? contentEl.textContent.trim().toLowerCase() : '';
            var matches = !query || title.indexOf(query) !== -1 || content.indexOf(query) !== -1;
            section.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        var hasNoResults = query && visibleCount === 0;
        if (noResults) noResults.style.display = hasNoResults ? '' : 'none';
        if (showAllBtn) showAllBtn.style.display = hasNoResults ? 'none' : '';
    });
}());
</script>
<?php endif; ?>
