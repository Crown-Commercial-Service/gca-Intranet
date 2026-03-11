<?php
/**
 * Fewbricks Template: Subpage Cards
 *
 * Displays child pages of a selected (or current) page as a clickable card grid.
 * Field keys follow the convention: {layout_name}_{brick_prefix}_{field_key}
 *
 * NOTE: This component queries WordPress page hierarchy (post_parent), not the
 * nav menu structure. For cards to appear, the target pages must have their
 * "Parent" set to this page under Page Attributes in the page editor.
 */

$heading     = $row['subpagecards_subpagecards_heading'] ?? '';
$parent_page = $row['subpagecards_subpagecards_parent_page'] ?? null;

if ( is_object( $parent_page ) && isset( $parent_page->ID ) ) {
    $parent_id = (int) $parent_page->ID;
} elseif ( is_numeric( $parent_page ) && (int) $parent_page > 0 ) {
    $parent_id = (int) $parent_page;
} else {
    $parent_id = get_the_ID();
}

$subpages = get_pages( [
    'parent'      => $parent_id,
    'sort_column' => 'menu_order',
    'sort_order'  => 'ASC',
    'post_status' => 'publish',
] );

if ( empty( $subpages ) ) {
    return;
}
?>

<div class="govuk-width-container">
    <div class="govuk-grid-row ccs-grid-row--loose">
        <div class="govuk-grid-column-full">

            <?php if ( $heading ) : ?>
                <h2 class="govuk-heading-l intro__heading">
                    <?php echo esc_html( $heading ); ?>
                </h2>
            <?php endif; ?>

            <ul class="card-list govuk-!-margin-top-6 subpage-card-list">
                <?php foreach ( $subpages as $subpage ) :
                    $url   = get_permalink( $subpage->ID );
                    $title = $subpage->post_title;

                    $summary = $subpage->post_excerpt
                        ? wp_trim_words( $subpage->post_excerpt, 30, '...' )
                        : wp_trim_words( wp_strip_all_tags( $subpage->post_content ), 30, '...' );
                ?>
                    <li class="card-list__item">
                        <div class="card-list__item__wrapper subpage-card">

                            <h3 class="govuk-heading-m subpage-card__title">
                                <a href="<?php echo esc_url( $url ); ?>" class="subpage-card__link">
                                    <?php echo esc_html( $title ); ?>
                                </a>
                            </h3>

                            <?php if ( $summary ) : ?>
                                <p class="govuk-body subpage-card__summary">
                                    <?php echo esc_html( $summary ); ?>
                                </p>
                            <?php endif; ?>

                            <span class="subpage-card__arrow" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12" fill="none" focusable="false">
                                    <path d="M4.6 6L0 1.4L1.4 0L7.4 6L1.4 12L0 10.6L4.6 6Z" fill="black"/>
                                </svg>
                            </span>

                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
    </div>
</div>
