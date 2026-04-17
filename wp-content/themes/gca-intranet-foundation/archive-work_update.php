<?php get_header(); ?>

<?php
$hero_image_url = gca_get_banner_url('gca_banner_work_updates', 'work_updates.jpg');

get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="work-update-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="work-update-main">

    <div class="govuk-grid-row">

      <!-- FILTERS -->
      <div class="govuk-grid-column-one-quarter">
        <?php
        get_template_part(
          'template-parts/components/filter-panel',
          null,
          [
            'post_type' => 'work_update',
            'include_taxonomies' => ['label', 'responsible_team']
          ]
        );
        ?>
      </div>

      <!-- RESULTS -->
      <div class="govuk-grid-column-three-quarters">

        <?php if (have_posts()) : ?>

          <?php while (have_posts()) : the_post(); ?>
            <article class="work-update-box" data-testid="work-update-post">

              <div class="work_update_profile_img">
                <?php echo gca_get_author_image_html(get_the_ID(), (int) get_the_author_meta('ID')); ?>
              </div>

              <div>
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="work-update-post-title">
                  <a class="govuk-link" href="<?php the_permalink(); ?>" data-testid="work-update-post-link">
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p data-testid="work-update-decs">
                  <?php echo esc_html(gca_clean_post_excerpt(320)); ?>
                </p>

                <p class="govuk-body">
                  By <?php echo esc_html(get_the_author()); ?>
                </p>

                <p class="date_bottom" style="margin: 0;" data-testid="work-update-post-date">

                  <span class="govuk-!-margin-right-2" style="font-size: initial;">
                    <?php echo esc_html(get_the_date('j F Y')); ?>
                  </span>

                  <?php
                  $terms = get_the_terms(get_the_ID(), 'label');
                  if ($terms && !is_wp_error($terms)) :
                    $term = array_shift($terms); ?>
                    <span class="govuk-body-s tag_label green" style="margin:0;">
                      <?php echo esc_html($term->name); ?>
                    </span>
                  <?php endif; ?>

                  <?php
                  $teams = get_the_terms(get_the_ID(), 'responsible_team');
                  if ($teams && !is_wp_error($teams)) :
                    $team = array_shift($teams); ?>
                    <span class="govuk-body-s tag_label grey" style="margin:0;">
                      <?php echo esc_html($team->name); ?>
                    </span>
                  <?php endif; ?>

                </p>
              </div>

            </article>
          <?php endwhile; ?>

          <!-- PAGINATION (WITH FILTER PRESERVATION) -->
          <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8" data-testid="work-update-pagination">
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
          <p class="govuk-body" data-testid="work-update-no-posts">No work updates found.</p>
        <?php endif; ?>

      </div>

    </div>

  </main>
</div>

<?php get_footer(); ?>
