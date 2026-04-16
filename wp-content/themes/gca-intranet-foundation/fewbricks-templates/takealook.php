<?php
/**
 * Fewbricks Template: Take a Look
 * Fields: takealook_takealook_title, _description, _link_text, _link_url
 */
$title     = $row['takealook_takealook_title']     ?? 'Take a look';
$desc      = $row['takealook_takealook_description'] ?? '';
$link_text = $row['takealook_takealook_link_text'] ?? 'Learn more';
$link_url  = $row['takealook_takealook_link_url']  ?? '';

$has_link = ! empty( trim( $link_url ) );
?>

<div class="govuk-grid-column-one-third" data-testid="take-a-look-column">

    <div class="gca-homepage-section-title" data-testid="take-a-look-header">
        <h2 class="govuk-heading-m" data-testid="take-a-look-heading"><?php echo esc_html( $title ); ?></h2>
        <?php if ( $desc ) : ?>
            <p class="govuk-body" data-testid="take-a-look-subheading"><?php echo esc_html( $desc ); ?></p>
        <?php endif; ?>
    </div>

    <div class="govuk-grid-row" data-testid="take-a-look-section">
        <div class="govuk-grid-column-full">
            <?php if ( $has_link ) : ?>
                <a class="gca-take-a-look__link govuk-link"
                    data-testid="take-a-look-link"
                    href="<?php echo esc_url( $link_url ); ?>">
                    <p class="govuk-body gca-take-a-look__text govuk-!-margin-bottom-0">
                        <?php echo esc_html( $link_text ); ?>
                    </p>
                    <span class="gca-take-a-look__icon" aria-hidden="true">
                        <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M32 16C32 12.8355 31.0616 9.74206 29.3035 7.11088C27.5454 4.47969 25.0466 2.42893 22.1229 1.21793C19.1993 0.00692534 15.9823 -0.309928 12.8786 0.307436C9.77486 0.924799 6.92393 2.44865 4.68629 4.68629C2.44865 6.92393 0.924799 9.77486 0.307435 12.8786C-0.309928 15.9823 0.00692538 19.1993 1.21793 22.1229C2.42893 25.0466 4.47969 27.5454 7.11088 29.3035C9.74206 31.0616 12.8355 32 16 32L16 16H32Z" fill="#9CAF27"/>
                            <path d="M22 22L31.3802 31.5833M31.3802 31.5833V22M31.3802 31.5833H22" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>
            <?php else : ?>
                <div class="gca-take-a-look__link"
                    data-testid="take-a-look-link"
                    aria-label="<?php esc_attr_e( 'Take a look not configured', 'gca-intranet' ); ?>">
                    <p class="govuk-body gca-take-a-look__text govuk-!-margin-bottom-0">
                        <?php echo esc_html( $link_text ); ?>
                    </p>
                    <span class="gca-take-a-look__icon" aria-hidden="true">
                        <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                            <path d="M32 16C32 12.8355 31.0616 9.74206 29.3035 7.11088C27.5454 4.47969 25.0466 2.42893 22.1229 1.21793C19.1993 0.00692534 15.9823 -0.309928 12.8786 0.307436C9.77486 0.924799 6.92393 2.44865 4.68629 4.68629C2.44865 6.92393 0.924799 9.77486 0.307435 12.8786C-0.309928 15.9823 0.00692538 19.1993 1.21793 22.1229C2.42893 25.0466 4.47969 27.5454 7.11088 29.3035C9.74206 31.0616 12.8355 32 16 32L16 16H32Z" fill="#9CAF27"/>
                            <path d="M22 22L31.3802 31.5833M31.3802 31.5833V22M31.3802 31.5833H22" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
