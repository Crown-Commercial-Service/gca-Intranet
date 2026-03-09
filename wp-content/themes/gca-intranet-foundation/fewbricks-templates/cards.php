<?php
/**
 * Fewbricks Template: Cards
 */
$heading = $row['cards_cards_heading'] ?? '';
$cards   = $row['cards_cards_cards'] ?? [];
?>

<div class="govuk-width-container">
    <div class="govuk-grid-row ccs-grid-row--loose">
        <div class="govuk-grid-column-full">

            <?php if ($heading) : ?>
                <h2 class="govuk-heading-l intro__heading">
                    <?php echo esc_html($heading); ?>
                </h2>
            <?php endif; ?>

            <?php if (!empty($cards)) : ?>
                <ul class="card-list govuk-!-margin-top-6 card-list--two-on-large">
                    <?php foreach ($cards as $card) : ?>
                        <li class="card-list__item">
                            <div class="card-list__item__wrapper">
                                
                                <h3 class="govuk-heading-m">
                                    <?php echo esc_html($card['cards_title']); ?>
                                </h3>

                                <div class="wysiwyg-content govuk-body">
                                    <?php 
                                    // Using apply_filters to handle the WYSIWYG content
                                    echo apply_filters('the_content', $card['cards_content']); 
                                    ?>
                                </div>

                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    </div>
</div>