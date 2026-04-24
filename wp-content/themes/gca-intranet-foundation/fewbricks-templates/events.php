<?php
/**
 * Fewbricks Template: Events
 * Fields: events_events_title, _description, _count, _pinned_events, _see_more_text, _see_more_url
 */
$title         = trim( (string) ( $row['events_events_title']         ?? 'Events' ) );
$desc          = trim( (string) ( $row['events_events_description']   ?? '' ) );
$count         = max( 1, (int) ( $row['events_events_count']          ?? 3 ) );
$pinned_ids    = $row['events_events_pinned_events']                  ?? [];
$see_more_text = $row['events_events_see_more_text']                  ?? 'More events';
$see_more_url  = $row['events_events_see_more_url']                   ?? '/event/';

if ( ! is_array( $pinned_ids ) ) {
    $pinned_ids = [];
}

$event_count = wp_count_posts( 'event' )->publish;
if ( ! $event_count ) {
    return;
}

// Build query: manually pinned events take priority over the auto upcoming query.
if ( ! empty( $pinned_ids ) ) {
    $events = new WP_Query( [
        'post_type'      => 'event',
        'post__in'       => $pinned_ids,
        'posts_per_page' => count( $pinned_ids ),
        'orderby'        => 'post__in',
    ] );
} else {
    $events = new WP_Query( [
        'post_type'      => 'event',
        'posts_per_page' => $count,
        'meta_key'       => 'start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => 'start_date',
                'value'   => date( 'Y-m-d H:i:s' ),
                'compare' => '>=',
                'type'    => 'DATETIME',
            ],
        ],
    ] );
}

if ( ! $events->have_posts() ) {
    wp_reset_postdata();
    return;
}
?>

<div data-testid="event-section">
    <div class="gca-homepage-section-title<?php echo $desc === '' ? ' gca-homepage-section-title--no-border' : ''; ?>"
        data-testid="latest-events-header">
        <h2 class="govuk-heading-m" data-testid="latest-events-heading">
            <?php echo esc_html( $title ); ?>
        </h2>
        <?php if ( $desc !== '' ) : ?>
            <p class="govuk-body" data-testid="latest-events-subheading">
                <?php echo esc_html( $desc ); ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="govuk-grid-row gca-equal-height-row event-entries" data-testid="events-updates-section">
        <?php
        while ( $events->have_posts() ) :
            $events->the_post();
            ?>
            <div class="govuk-grid-column-one-third gca-event-card" data-testid="events-card">
                <div class="gca-events" data-testid="events-row">
                    <p class="govuk-body-s gca-event-date" data-testid="events-date">
                        <?php echo esc_html( gca_get_event_datetime( 'start_date' ) ); ?>
                    </p>
                    <h3 class="govuk-heading-s" data-testid="events-title">
                        <a class="govuk-link govuk-!-text-break-word" href="<?php the_permalink(); ?>" data-testid="events-link">
                            <?php
                            $event_title = get_the_title();
                            echo esc_html( mb_strlen( $event_title ) > 58 ? mb_substr( $event_title, 0, 58 ) . '...' : $event_title );
                            ?>
                        </a>
                    </h3>
                    <div class="gca-card-meta">
                        <?php
                        $categories = get_the_category();
                        $locations  = get_the_terms( get_the_ID(), 'event_location' );

                        if ( $categories && $categories[0]->name !== 'Uncategorized' ) : ?>
                            <span class="govuk-body-s tag_label" data-testid="events-category">
                                <?php echo esc_html( $categories[0]->name ); ?>
                            </span>
                        <?php endif;

                        if ( $locations ) : ?>
                            <span class="govuk-body-s tag_label grey" data-testid="events-location">
                                <?php echo esc_html( $locations[0]->name ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </div>

    <?php if ( $see_more_url ) : ?>
        <div class="see-more-link-homepage" data-testid="events-see-more">
            <svg data-testid="events-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
                fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"
                style="stroke: currentColor; padding-top: 9px;" aria-hidden="true" focusable="false">
                <path fill-rule="evenodd"
                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
            </svg>
            <p data-testid="events-see-more-text">
                <a class="govuk-link" data-testid="events-see-more-link" href="<?php echo esc_url( $see_more_url ); ?>">
                    <?php echo esc_html( $see_more_text ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
</div>
