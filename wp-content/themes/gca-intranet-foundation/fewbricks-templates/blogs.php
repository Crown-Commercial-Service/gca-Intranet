<?php
/**
 * Fewbricks Template: Blogs
 * Fields: blogs_blogs_title, _description, _count, _selected_posts, _see_more_text, _see_more_url
 */
$title         = $row['blogs_blogs_title']         ?? 'Blogs';
$description   = $row['blogs_blogs_description']   ?? 'Latest posts from colleagues.';
$count         = (int) ( $row['blogs_blogs_count']         ?? 1 );
$selected_posts = $row['blogs_blogs_selected_posts']       ?? [];
$see_more_text = $row['blogs_blogs_see_more_text'] ?? 'More blogs';
$see_more_url  = $row['blogs_blogs_see_more_url']  ?? '/blog/';

$count      = max( 1, $count );
$title      = trim((string) $title);
$description = trim((string) $description);
$has_header = ($title !== '' || $description !== '');
$blogs      = gca_get_selected_or_latest_posts( 'blog', is_array( $selected_posts ) ? $selected_posts : [], $count );
?>

<div class="govuk-grid-column-one-third" data-testid="blogs-column">
    <?php if ( $has_header ) : ?>
        <div class="gca-homepage-section-title" data-testid="blogs-header">
            <?php if ( $title !== '' ) : ?>
                <h2 class="govuk-heading-m gca-clamp-2" data-testid="blogs-heading">
                    <?php echo esc_html( $title ); ?>
                </h2>
            <?php endif; ?>
            <?php if ( $description !== '' ) : ?>
                <p class="govuk-body" data-testid="blogs-subheading">
                    <?php echo esc_html( $description ); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="govuk-grid-row" data-testid="blogs-section">
        <div class="govuk-grid-column-full gca-work-update-card" data-testid="blogs-card">
            <div class="govuk-grid-row gca-work-updates" data-testid="blogs-row">
                <?php if ( ! empty( $blogs ) ) : ?>
                    <?php foreach ( $blogs as $blog_post ) : ?>
                        <?php setup_postdata( $blog_post ); ?>
                        <div class="govuk-grid-column-one-third" data-testid="blogs-avatar">
                            <?php echo gca_get_author_image_html( get_the_ID(), (int) get_the_author_meta( 'ID' ) ); ?>
                        </div>
                        <div class="govuk-grid-column-two-thirds" data-testid="blogs-content">
                            <h4 class="govuk-heading-s" data-testid="blogs-title">
                                <a class="govuk-link govuk-!-text-break-word" data-testid="blogs-link" href="<?php the_permalink(); ?>">
                                    <?php echo esc_html( get_the_title() ); ?>
                                </a>
                            </h4>
                            <p class="govuk-body-s" data-testid="blogs-author">
                                By <?php echo esc_html( get_the_author() ); ?>
                            </p>
                            <p class="govuk-body-xs" data-testid="blogs-date">
                                <?php echo esc_html( get_the_date( 'j F Y' ) ); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ( $see_more_url ) : ?>
        <div class="see-more-link-homepage" data-testid="blogs-see-more">
            <svg data-testid="blogs-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                fill="#0b0c0c" viewBox="0 0 16 16"
                aria-hidden="true" focusable="false">
                <path fill-rule="evenodd"
                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
            </svg>
            <a class="govuk-link" data-testid="blogs-see-more-link" href="<?php echo esc_url( $see_more_url ); ?>">
                <?php echo esc_html( $see_more_text ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
