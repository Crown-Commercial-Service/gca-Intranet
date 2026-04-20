<?php
/**
 * Fewbricks Template: Latest News
 * Fields: latestnews_latestnews_title, _description, _featured_count, _secondary_count,
 *         _see_more_text, _see_more_url
 */
$title           = $row['latestnews_latestnews_title']           ?? 'Latest news';
$description     = $row['latestnews_latestnews_description']     ?? "What's happening in our organisation";
$featured_count  = (int) ( $row['latestnews_latestnews_featured_count']  ?? 1 );
$secondary_count = (int) ( $row['latestnews_latestnews_secondary_count'] ?? 3 );
$see_more_text   = $row['latestnews_latestnews_see_more_text']   ?? 'Browse all news articles';
$see_more_url    = $row['latestnews_latestnews_see_more_url']    ?? '/news/';

$featured_count  = max( 1, $featured_count );
$secondary_count = max( 1, $secondary_count );
?>

<div class="govuk-grid-column-two-thirds gca-right-line" data-testid="latest-news-column">
    <div class="gca-homepage-section-title" data-testid="latest-news-header">
        <h2 class="govuk-heading-m gca-clamp-2" data-testid="latest-news-heading">
            <?php echo esc_html( $title ); ?>
        </h2>
        <p class="govuk-body" data-testid="latest-news-subheading">
            <?php echo esc_html( $description ); ?>
        </p>
    </div>

    <div class="gca-equal-height-row" data-testid="latest-news-section">
        <div class="govuk-grid-column-one-half govuk-!-padding-left-0" data-testid="latest-news-featured-col">
            <div class="gca-featured-news" data-testid="latest-news-featured-card">
                <?php
                $latest_post = new WP_Query( [ 'post_type' => 'news', 'posts_per_page' => $featured_count ] );
                if ( $latest_post->have_posts() ) :
                    while ( $latest_post->have_posts() ) :
                        $latest_post->the_post();
                        ?>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <img
                                data-testid="latest-news-featured-image"
                                src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>"
                                alt="<?php echo esc_attr( get_the_title() ); ?>"
                            >
                        <?php endif; ?>

                        <div data-testid="latest-news-featured-content">
                            <h2 class="govuk-heading-m" data-testid="latest-news-featured-title">
                                <a class="govuk-link govuk-!-text-break-word" data-testid="latest-news-featured-link" href="<?php the_permalink(); ?>">
                                    <?php echo esc_html( get_the_title() ); ?>
                                </a>
                            </h2>
                            <p class="govuk-body-s" data-testid="latest-news-featured-excerpt">
                                <?php echo esc_html( get_the_excerpt() ); ?>
                            </p>
                            <p class="govuk-body-s" data-testid="latest-news-featured-date">
                                <?php echo esc_html( get_the_date( 'j F Y' ) ); ?>
                            </p>
                        </div>
                        <?php
                    endwhile;
                endif;
                wp_reset_postdata();
                ?>
            </div>
        </div>

        <div class="govuk-grid-column-one-half gca-flex-box-news" data-testid="latest-news-secondary-col">
            <?php
            $secondary_posts = new WP_Query( [
                'post_type'      => 'news',
                'posts_per_page' => $secondary_count,
                'offset'         => $featured_count,
            ] );
            if ( $secondary_posts->have_posts() ) :
                while ( $secondary_posts->have_posts() ) :
                    $secondary_posts->the_post();
                    $is_first         = ( $secondary_posts->current_post === 0 );
                    $padding_top_class = $is_first ? 'gca-first-small-list-news' : 'gca-not_first-small-list-news';
                    ?>
                    <div class="gca-small-list-news <?php echo esc_attr( $padding_top_class ); ?>" data-testid="latest-news-secondary-card">
                        <div class="govuk-grid-row" data-testid="latest-news-secondary-row">
                            <div class="govuk-grid-column-one-third" data-testid="latest-news-secondary-image-col">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <img
                                        data-testid="latest-news-secondary-image"
                                        src="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ); ?>"
                                        alt="<?php echo esc_attr( get_the_title() ); ?>"
                                    >
                                <?php endif; ?>
                            </div>
                            <div class="govuk-grid-column-two-third gca-flex-box-news" data-testid="latest-news-secondary-content">
                                <h3 class="govuk-heading-s govuk-!-margin-bottom-1" data-testid="latest-news-secondary-title">
                                    <a class="govuk-link govuk-!-text-break-word" data-testid="latest-news-secondary-link" href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                <p class="govuk-body-s" data-testid="latest-news-secondary-excerpt">
                                    <?php echo esc_html( wp_trim_words( get_the_excerpt(), 12, '...' ) ); ?>
                                </p>
                                <p class="govuk-body-xs" data-testid="latest-news-secondary-date">
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
        </div>

        <?php if ( $see_more_url ) : ?>
            <div class="see-more-link-homepage" data-testid="latest-news-see-more">
                <svg data-testid="latest-news-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
                    fill="currentColor" class="bi bi-chevron-right"
                    viewBox="0 0 16 16" style="stroke: currentColor; padding-top: 9px;" aria-hidden="true" focusable="false">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                </svg>
                <p data-testid="latest-news-see-more-text">
                    <a class="govuk-link" data-testid="latest-news-see-more-link" href="<?php echo esc_url( $see_more_url ); ?>">
                        <?php echo esc_html( $see_more_text ); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
