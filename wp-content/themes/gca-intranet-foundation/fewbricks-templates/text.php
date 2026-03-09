<?php
/**
 * Fewbricks Template: text.php
 */
$content = $row['text_text_text']; 
?>

<div class="govuk-width-container">
    <div class="govuk-grid-row">
        <div class="govuk-grid-column-two-thirds">
            <?php if ( $content ) : ?>
                <div class="govuk-body">
                    <?php 
                    // Use apply_filters to handle line breaks/formatting
                    echo apply_filters( 'the_content', $content ); 
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>