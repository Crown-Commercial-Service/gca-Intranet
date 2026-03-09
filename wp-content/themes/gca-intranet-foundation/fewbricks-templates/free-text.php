<?php
/**
 * Fewbricks Template: Free Text (Two Column)
 * Converted from Twig for GCA Intranet
 */

// Left Column Data
$left_heading  = $row['free_text_free_text_left_heading'] ?? '';
$left_content  = $row['free_text_free_text_left_content_new'] ?? '';
$cta_label     = $row['free_text_free_text_cta_label'] ?? '';
$cta_dest      = $row['free_text_free_text_cta_destination'] ?? '';

// Right Column Data
$right_heading = $row['free_text_free_text_right_heading'] ?? '';
$right_content = $row['free_text_free_text_right_content_new'] ?? '';

// Check if CTA exists and isn't just whitespace
$has_cta = (!empty(trim($cta_label)) && !empty(trim($cta_dest)));
?>

<div class="govuk-width-container">
    <div class="govuk-grid-row ccs-grid-row--tight freetext-component">
        
        <div class="govuk-grid-column-one-half govuk-!-padding-right-15 govuk-!-padding-top-20 left-freetext-column-padding-right free-wid">
            <?php if ($left_heading) : ?>
                <div class="intro-freetext-heading">
                    <?php echo apply_filters('the_content', $left_heading); ?>
                </div>
            <?php endif; ?>

            <?php if ($left_content) : ?>
                <div class="govuk-body govuk-!-font-size-19">
                    <?php echo apply_filters('the_content', $left_content); ?>
                </div>
            <?php endif; ?>

            <?php if ($has_cta) : ?>
                <a href="<?php echo esc_url($cta_dest); ?>" class="govuk-button freetext-cta-btn">
                    <?php echo esc_html($cta_label); ?>
                </a>
            <?php endif; ?> 
        </div>

        <div class="govuk-grid-column-one-half govuk-padding-left-35 govuk-!-padding-left-15 govuk-!-padding-top-20 free-wid freetext-right">
            <span class="govuk-border-left-free-text"></span>
            
            <?php if ($right_heading) : ?>
                <div class="intro-freetext-heading intro-freetext-heading-right">
                     <?php echo apply_filters('the_content', $right_heading); ?>
                </div>
            <?php endif; ?>

            <?php if ($right_content) : ?>
                <div class="govuk-body govuk-!-font-size-19">
                    <?php echo apply_filters('the_content', $right_content); ?>
                </div>
            <?php endif; ?>
        </div> 

    </div>    
</div>