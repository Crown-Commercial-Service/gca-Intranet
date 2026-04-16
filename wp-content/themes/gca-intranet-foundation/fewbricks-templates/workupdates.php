<?php
/**
 * Fewbricks Template: Work Updates
 * Fields: workupdates_workupdates_title, _description, _count, _see_more_text, _see_more_url
 */
$title         = $row['workupdates_workupdates_title']         ?? 'Work updates';
$description   = $row['workupdates_workupdates_description']   ?? 'Highlights from across the organisation.';
$count         = (int) ( $row['workupdates_workupdates_count']         ?? 2 );
$see_more_text = $row['workupdates_workupdates_see_more_text'] ?? 'More work updates';
$see_more_url  = $row['workupdates_workupdates_see_more_url']  ?? '/work_update/';

$count = max( 1, $count );
?>

<div class="govuk-grid-column-two-thirds gca-right-line" data-testid="work-updates-column">
    <div class="gca-homepage-section-title" data-testid="work-updates-header">
        <h2 class="govuk-heading-m gca-clamp-2" data-testid="work-updates-heading">
            <?php echo esc_html( $title ); ?>
        </h2>
        <p class="govuk-body" data-testid="work-updates-subheading">
            <?php echo esc_html( $description ); ?>
        </p>
    </div>

    <div class="govuk-grid-row gca-equal-height-row" data-testid="work-updates-section">
        <?php
        $work_updates = new WP_Query( [
            'post_type'      => 'work_update',
            'posts_per_page' => $count,
        ] );

        if ( $work_updates->have_posts() ) :
            while ( $work_updates->have_posts() ) :
                $work_updates->the_post();
                ?>
                <div class="govuk-grid-column-one-half gca-work-update-card" data-testid="work-update-card">
                    <div class="govuk-grid-row gca-work-updates" data-testid="work-update-row">
                        <div class="govuk-grid-column-one-third" data-testid="work-update-avatar">
                            <?php echo gca_get_author_image_html( get_the_ID(), (int) get_the_author_meta( 'ID' ) ); ?>
                        </div>
                        <div class="govuk-grid-column-two-thirds" data-testid="work-update-content">
                            <h3 class="govuk-heading-s" data-testid="work-update-title">
                                <a class="govuk-link govuk-!-text-break-word" href="<?php the_permalink(); ?>" data-testid="work-update-link">
                                    <?php echo esc_html( get_the_title() ); ?>
                                </a>
                            </h3>
                            <p class="govuk-body-s" data-testid="work-update-author">
                                By <?php echo esc_html( get_the_author() ); ?>
                            </p>
                            <p class="govuk-body-xs" data-testid="work-update-date">
                                <?php echo esc_html( get_the_date( 'j F Y' ) ); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
        endif;
        wp_reset_postdata();
        ?>

        <?php if ( $see_more_url ) : ?>
            <div class="see-more-link-homepage" data-testid="work-updates-see-more">
                <svg data-testid="work-updates-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
                    fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"
                    style="stroke: currentColor; padding-top: 9px;" aria-hidden="true" focusable="false">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                </svg>
                <p data-testid="work-updates-see-more-text">
                    <a class="govuk-link" data-testid="work-updates-see-more-link" href="<?php echo esc_url( $see_more_url ); ?>">
                        <?php echo esc_html( $see_more_text ); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
