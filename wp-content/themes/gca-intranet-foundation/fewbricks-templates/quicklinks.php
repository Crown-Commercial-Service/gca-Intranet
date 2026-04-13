<?php
/**
 * Fewbricks Template: Quick Links
 * Fields: quicklinks_quicklinks_title, _description, _links (repeater: text, url)
 */
$title = $row['quicklinks_quicklinks_title']       ?? 'Quick links';
$desc  = $row['quicklinks_quicklinks_description'] ?? '';
$links = $row['quicklinks_quicklinks_links']       ?? [];

// Normalise: filter out any rows missing both text and url
$links = array_filter( $links, function ( $link ) {
    return ! empty( trim( (string) ( $link['links_text'] ?? '' ) ) )
        && ! empty( trim( (string) ( $link['links_url'] ?? '' ) ) );
} );

if ( empty( $links ) ) {
    return;
}
?>

<div class="gca-quick-links" data-testid="quick-links">

    <div class="gca-homepage-section-title" data-testid="quick-links-header">
        <h2 class="govuk-heading-m gca-clamp-2" data-testid="quick-links-heading">
            <?php echo esc_html( $title ); ?>
        </h2>
        <?php if ( $desc ) : ?>
            <p class="govuk-body" data-testid="quick-links-subheading">
                <?php echo esc_html( $desc ); ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="gca-quick-links__list" data-testid="quick-links-list">
        <?php foreach ( $links as $link ) : ?>
            <a class="gca-quick-links__item govuk-link"
                href="<?php echo esc_url( $link['links_url'] ); ?>"
                data-testid="quick-links-item">
                <span class="gca-quick-links__text"><?php echo esc_html( $link['links_text'] ); ?></span>
                <svg class="gca-quick-links__chevron"
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="22"
                    fill="currentColor"
                    viewBox="0 0 16 16"
                    style="stroke: currentColor;"
                    aria-hidden="true"
                    focusable="false">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                </svg>
            </a>
        <?php endforeach; ?>
    </div>

</div>
