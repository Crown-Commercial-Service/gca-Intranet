<?php
/**
 * Fewbricks Template: Accordion
 * Verified Mapping from Array Output
 */

// Top-level fields
$heading      = $row['accordion_accordion_heading'] ?? '';
$intro        = $row['accordion_accordion_introduction'] ?? '';
$open_default = $row['accordion_accordion_open_all_by_default'] ?? false;

// The Repeater Array
$items        = $row['accordion_accordion_items'] ?? [];

// Logic for initial state
$expanded_class = ($open_default) ? ' govuk-accordion__section--expanded' : '';
?>

<div class="govuk-width-container">
    <div class="resources-section">

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

        <div class="govuk-accordion ccs-accordion govuk-!-margin-top-4" data-module="govuk-accordion" id="accordion-default">

            <?php if (!empty($items)) : ?>
                <?php foreach ($items as $index => $item) : 
                    $loop_id = $index + 1;
                    // INTERNAL KEYS: Verified as 'items_title' and 'items_content'
                    $title   = $item['items_title'] ?? '';
                    $content = $item['items_content'] ?? '';
                ?>
                    <div class="govuk-accordion__section<?php echo esc_attr($expanded_class); ?>">
                        <div class="govuk-accordion__section-header">
                            <h2 class="govuk-accordion__section-heading">
                                <span class="govuk-accordion__section-button" id="accordion-default-heading-<?php echo $loop_id; ?>">
                                    <?php echo esc_html($title); ?>
                                </span>
                            </h2>
                        </div>
                        <div id="accordion-default-content-<?php echo $loop_id; ?>" 
                             role="region" 
                             class="govuk-accordion__section-content" 
                             aria-labelledby="accordion-default-heading-<?php echo $loop_id; ?>">
                            <div class="wysiwyg-content">
                                <?php echo apply_filters('the_content', $content); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>
