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

    <?php
    $filter_taxonomies = gca_get_archive_filter_taxonomies('event');
    $show_filters      = gca_flag_enabled('archive-filters') && !empty($filter_taxonomies);
    $current_view      = isset($_GET['view']) && $_GET['view'] === 'past' ? 'past' : 'upcoming';
    $today             = date('Ymd');
    $archive_url       = get_post_type_archive_link('event');
    ?>

    <div class="govuk-grid-row archive-layout" data-testid="archive-event-row">

      <?php if ($show_filters) : ?>
        <div class="govuk-grid-column-one-quarter archive-layout__filters">
          <?php
          get_template_part('template-parts/archive-filters', null, [
            'archive_url'  => $archive_url,
            'taxonomies'   => $filter_taxonomies,
            'current_view' => $current_view,
          ]);
          ?>
        </div>
        <div class="govuk-grid-column-three-quarters archive-layout__results" data-testid="archive-event-col">
      <?php else : ?>
        <div class="govuk-grid-column-full archive-layout__results" data-testid="archive-event-col">
      <?php endif; ?>

        <nav class="events-tabs" aria-label="Events view" data-testid="events-tabs">
          <a
            href="<?php echo esc_url($archive_url); ?>"
            class="events-tabs__tab <?php echo $current_view === 'upcoming' ? 'events-tabs__tab--active' : ''; ?>"
            <?php echo $current_view === 'upcoming' ? 'aria-current="page"' : ''; ?>
            data-testid="events-tab-upcoming"
          >
            Upcoming Events
          </a>
          <a
            href="<?php echo esc_url(add_query_arg('view', 'past', $archive_url)); ?>"
            class="events-tabs__tab <?php echo $current_view === 'past' ? 'events-tabs__tab--active' : ''; ?>"
            <?php echo $current_view === 'past' ? 'aria-current="page"' : ''; ?>
            data-testid="events-tab-past"
          >
            Past Events
          </a>
        </nav>

        <?php
        $events_by_month = [];

        if (have_posts()) {
            while (have_posts()) {
                the_post();
                $post_id    = get_the_ID();
                $start_date = get_field('start_date', $post_id);
                $end_date   = get_field('end_date', $post_id);

                if ($current_view === 'past' && $end_date && $end_date >= $today) {
                    continue;
                }

                if ($current_view === 'upcoming' && $start_date && $start_date < $today && $end_date && $end_date >= $today) {
                    $group_date = $end_date;
                } else {
                    $group_date = $start_date;
                }

                if (!$group_date) {
                    continue;
                }

                $month_key = date('Y-m', strtotime($group_date));
                if (!isset($events_by_month[$month_key])) {
                    $events_by_month[$month_key] = [
                        'label' => date('F Y', strtotime($group_date)),
                        'posts' => [],
                    ];
                }
                $events_by_month[$month_key]['posts'][] = get_post();
            }
        }
        ?>

        <?php if (!empty($events_by_month)) : ?>

          <div data-testid="archive-event-posts">
            <?php foreach ($events_by_month as $month_data) : ?>

              <h2 class="events-month-heading govuk-heading-m" data-testid="archive-event-month-heading">
                <?php echo esc_html($month_data['label']); ?>
              </h2>

              <?php foreach ($month_data['posts'] as $post) :
                setup_postdata($post); ?>

                <article
                  class="event-card"
                  data-testid="archive-event-post"
                  data-post-id="<?php echo esc_attr((string) $post->ID); ?>"
                  style="padding-bottom:0;"
                >
                  <h3 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="archive-event-post-title">
                    <a
                      class="govuk-link"
                      href="<?php the_permalink(); ?>"
                      data-testid="archive-event-post-link"
                    >
                      <?php the_title(); ?>
                    </a>
                  </h3>

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

              <?php endforeach; ?>
              <?php wp_reset_postdata(); ?>

            <?php endforeach; ?>
          </div>

          <div class="govuk-!-margin-top-6 govuk-!-margin-bottom-8" data-testid="archive-event-pagination">
            <?php
            the_posts_pagination(array(
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
            ));
            ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="archive-event-no-posts">
            <?php echo $current_view === 'past' ? 'No past events found.' : 'No events found.'; ?>
          </p>
        <?php endif; ?>

      </div><!-- .archive-layout__results -->

    </div><!-- .govuk-grid-row -->

  </main>
</div>

<?php get_footer(); ?>
