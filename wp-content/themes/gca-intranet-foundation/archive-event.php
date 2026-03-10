<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="archive-event-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="archive-event-main">
    <div class="govuk-grid-row" data-testid="archive-event-row">
      <div class="govuk-grid-column-full" data-testid="archive-event-col">

        <?php if (have_posts()) : ?>

          <div data-testid="archive-event-posts">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="event-card"
                data-testid="archive-event-post"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="archive-event-post-title">
                  <a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="archive-event-post-link"
                  >
                    <?php the_title(); ?>
                  </a>
                </h2>

                <?php
                $start_datetime = get_field('start_datetime');
                if ($start_datetime) {
                  $dt = DateTime::createFromFormat('d-m-Y g:i a', $start_datetime);
                  $formatted_date = $dt ? $dt->format('jS F Y g:i a') : $start_datetime;
                } else {
                  $formatted_date = get_the_date('jS F Y');
                }
                ?>
                <p class="govuk-body govuk-!-margin-bottom-2" data-testid="archive-event-post-date">
                  <strong><?php echo esc_html($formatted_date); ?></strong>
                </p>

                <div class="govuk-body govuk-!-margin-bottom-3" data-testid="archive-event-post-excerpt">
                  <?php the_excerpt(); ?>
                </div>

                <div class="event-card__tags" data-testid="archive-event-post-tags">
                  <?php
                  $event_categories = get_the_terms(get_the_ID(), 'category');
                  if ($event_categories && !is_wp_error($event_categories)) :
                    foreach ($event_categories as $i => $cat) : ?>
                      <span class="tag_label <?php echo $i === 0 ? 'grey' : 'green'; ?> govuk-body-s" data-testid="archive-event-post-category">
                        <?php echo esc_html($cat->name); ?>
                      </span>
                    <?php endforeach;
                  endif; ?>

                  <?php
                  $event_locations = get_the_terms(get_the_ID(), 'event_location');
                  if ($event_locations && !is_wp_error($event_locations)) :
                    foreach ($event_locations as $location) : ?>
                      <span class="tag_label govuk-body-s" data-testid="archive-event-post-location">
                        <?php echo esc_html($location->name); ?>
                      </span>
                    <?php endforeach;
                  endif; ?>
                </div>

              </article>
            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6 govuk-!-margin-bottom-8" data-testid="archive-event-pagination">
            <?php
              the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => sprintf(
                  '<span class="icon">
                    <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M6.7 0l1.4 1.4-4.3 4.3h13v2H3.9l4.2 4-1.4 1.4L0 6.7z" fill="#007194" fill-rule="evenodd"/></svg>
                  </span> <span>Previous</span>
                  <span class="govuk-visually-hidden">page</span>'
                ),
                'next_text' => sprintf(
                  '<span>Next</span> <span class="govuk-visually-hidden">page</span>
                  <span class="icon">
                    <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M10.1 0L8.7 1.4 13 5.7H0v2h12.9l-4.2 4 1.4 1.4 6.7-6.4z" fill="#007194" fill-rule="evenodd"/></svg>
                  </span>'
                ),
              ) );
            ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="archive-event-no-posts">No events found.</p>
        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>