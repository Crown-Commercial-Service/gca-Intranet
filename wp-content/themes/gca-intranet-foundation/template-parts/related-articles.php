<?php
$heading      = get_field( 'related_articles_heading' ) ?: 'Related articles';
$manual_posts = get_field( 'related_articles_posts' ) ?: [];
$current_id   = get_the_ID();
$post_type    = get_post_type( $current_id );

if ( ! empty( $manual_posts ) ) {
    $posts = array_slice(
        array_values( array_filter( $manual_posts, fn ( $p ) => (int) $p->ID !== $current_id ) ),
        0, 3
    );
} else {
    $posts = get_posts( [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__not_in'   => [ $current_id ],
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ] );
}

if ( empty( $posts ) ) {
    return;
}
?>

<section class="gca-related-articles govuk-!-margin-top-8" data-testid="related-articles">
    <div class="govuk-grid-row">
        <div class="govuk-grid-column-full">
            <h2 class="govuk-heading-m" data-testid="related-articles-heading">
                <?php echo esc_html( $heading ); ?>
            </h2>
            <hr class="govuk-section-break govuk-section-break--visible govuk-!-margin-bottom-6">
        </div>
    </div>
    <div class="govuk-grid-row" data-testid="related-articles-list">
        <?php foreach ( $posts as $related_post ) : ?>
            <div class="govuk-grid-column-one-third" data-testid="related-articles-card">
                <div class="gca-featured-news">
                    <div>
                        <h3 class="govuk-heading-s govuk-!-margin-bottom-1" data-testid="related-articles-card-title">
                            <a class="govuk-link govuk-!-text-break-word"
                               href="<?php echo esc_url( get_permalink( $related_post->ID ) ); ?>"
                               data-testid="related-articles-card-link">
                                <?php echo esc_html( get_the_title( $related_post->ID ) ); ?>
                            </a>
                        </h3>
                        <p class="govuk-body-s" data-testid="related-articles-card-excerpt">
                            <?php echo esc_html( wp_trim_words( get_the_excerpt( $related_post->ID ), 20, '...' ) ); ?>
                        </p>
                        <p class="govuk-body-s govuk-!-margin-bottom-0" data-testid="related-articles-card-date">
                            <?php echo esc_html( get_the_date( 'j F Y', $related_post->ID ) ); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
