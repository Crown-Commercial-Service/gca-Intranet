<?php
/**
 * Fewbricks Template: Blogs
 * Fields: blogs_blogs_title, _description, _count, _see_more_text, _see_more_url
 */
$title         = $row['blogs_blogs_title']         ?? 'Blogs';
$description   = $row['blogs_blogs_description']   ?? 'Latest posts from colleagues.';
$count         = (int) ( $row['blogs_blogs_count']         ?? 1 );
$see_more_text = $row['blogs_blogs_see_more_text'] ?? 'More blogs';
$see_more_url  = $row['blogs_blogs_see_more_url']  ?? '/blog/';

$count = max( 1, $count );
?>

<div class="govuk-grid-column-one-third" data-testid="blogs-column">
    <div class="gca-homepage-section-title" data-testid="blogs-header">
        <h2 class="govuk-heading-m gca-clamp-2" data-testid="blogs-heading">
            <?php echo esc_html( $title ); ?>
        </h2>
        <p class="govuk-body" data-testid="blogs-subheading">
            <?php echo esc_html( $description ); ?>
        </p>
    </div>

    <div class="govuk-grid-row" data-testid="blogs-section">
        <div class="govuk-grid-column-full gca-work-update-card" data-testid="blogs-card">
            <div class="govuk-grid-row gca-work-updates" data-testid="blogs-row">
                <?php
                $blogs = new WP_Query( [ 'post_type' => 'blog', 'posts_per_page' => $count ] );
                if ( $blogs->have_posts() ) :
                    while ( $blogs->have_posts() ) :
                        $blogs->the_post();
                        ?>
                        <div class="govuk-grid-column-one-third" data-testid="blogs-avatar">
                            <?php echo gca_get_author_image_html( get_the_ID(), (int) get_the_author_meta( 'ID' ) ); ?>
                        </div>
                        <div class="govuk-grid-column-two-thirds" data-testid="blogs-content">
                            <h3 class="govuk-heading-s" data-testid="blogs-title">
                                <a class="govuk-link govuk-!-text-break-word" data-testid="blogs-link" href="<?php the_permalink(); ?>">
                                    <?php echo esc_html( get_the_title() ); ?>
                                </a>
                            </h3>
                            <p class="govuk-body-s" data-testid="blogs-author">
                                By <?php echo esc_html( get_the_author() ); ?>
                            </p>
                            <p class="govuk-body-xs" data-testid="blogs-date">
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
    </div>

    <?php if ( $see_more_url ) : ?>
        <div class="see-more-link-homepage" data-testid="blogs-see-more">
            <svg data-testid="blogs-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
                fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"
                style="stroke: currentColor; padding-top: 9px;" aria-hidden="true" focusable="false">
                <path fill-rule="evenodd"
                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
            </svg>
            <p data-testid="blogs-see-more-text">
                <a class="govuk-link" data-testid="blogs-see-more-link" href="<?php echo esc_url( $see_more_url ); ?>">
                    <?php echo esc_html( $see_more_text ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
</div>
