<?php get_header(); ?>

<?php
$hero_image_url = gca_get_banner_url('gca_banner_events', 'events.jpg');

get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => $hero_image_url,
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="archive-event-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="archive-event-main">

    <div class="govuk-grid-row" data-testid="archive-event-row">

      <!-- FILTERS -->
      <div class="govuk-grid-column-one-quarter">
        <?php
        get_template_part(
          'template-parts/components/filter-panel',
          null,
          [
            'post_type' => 'event',
            'include_taxonomies' => ['category', 'event_location', 'audience']
          ]
        );
        ?>
      </div>

      <!-- RESULTS -->
      <div class="govuk-grid-column-three-quarters" data-testid="archive-event-col">

        <?php if (have_posts()) : ?>

          <div data-testid="archive-event-posts">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="event-card"
                data-testid="archive-event-post"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
                style="padding-bottom:0;"
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

                <p class="govuk-body govuk-!-margin-bottom-2" data-testid="archive-event-post-date">
                  <strong>
                    <?php echo esc_html(gca_get_event_datetime('all')); ?>
                  </strong>
                </p>

                <div class="govuk-body govuk-!-margin-bottom-3" data-testid="archive-event-post-excerpt">
                  <?php echo esc_html(gca_clean_post_excerpt(320)); ?>
                </div>

                <div class="event-card__tags" data-testid="archive-event-post-tags">

                  <?php
                  $event_categories = get_the_terms(get_the_ID(), 'category');
                  if ($event_categories && !is_wp_error($event_categories)) :
                    $visible_i = 0;
                    foreach ($event_categories as $cat) :
                      if (strtolower($cat->name) === 'uncategorized' || strtolower($cat->name) === 'uncategorised') continue; ?>
                      
                      <span class="tag_label <?php echo $visible_i === 0 ? '' : 'grey'; ?> govuk-body-s" data-testid="archive-event-post-category">
                        <?php echo esc_html($cat->name); ?>
                      </span>

                      <?php $visible_i++;
                    endforeach;
                  endif; ?>

                  <?php
                  $event_locations = get_the_terms(get_the_ID(), 'event_location');
                  if ($event_locations && !is_wp_error($event_locations)) :
                    foreach ($event_locations as $location) : ?>
                      
                      <span class="tag_label grey govuk-body-s" data-testid="archive-event-post-location">
                        <?php echo esc_html($location->name); ?>
                      </span>

                    <?php endforeach;
                  endif; ?>

                </div>

              </article>
            <?php endwhile; ?>
          </div>

          <!-- PAGINATION (WITH FILTER PRESERVATION) -->
          <div class="govuk-!-margin-top-6 govuk-!-margin-bottom-8" data-testid="archive-event-pagination">
            <?php
            $pagination_args = [];

            foreach ($_GET as $key => $value) {
              $pagination_args[$key] = is_array($value)
                ? array_map('sanitize_text_field', $value)
                : sanitize_text_field($value);
            }

            echo paginate_links([
              'total'     => $wp_query->max_num_pages,
              'current'   => max(1, get_query_var('paged')),
              'mid_size'  => 2,
              'prev_text' => '<span>Previous</span>',
              'next_text' => '<span>Next</span>',
              'add_args'  => $pagination_args,
            ]);
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
