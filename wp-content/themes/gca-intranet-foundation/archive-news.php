<?php get_header(); ?>

<?php
$hero_image_url = gca_get_banner_url('gca_banner_news', 'news.jpg');

get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="news-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="news-main">

    <div class="govuk-grid-row">

      <!-- FILTERS -->
      <div class="govuk-grid-column-one-quarter">
        <?php
        get_template_part(
          'template-parts/components/filter-panel',
          null,
          [
            'post_type' => 'news',
            'include_taxonomies' => ['category', 'label']
          ]
        );
        ?>
      </div>

      <!-- RESULTS -->
      <div class="govuk-grid-column-three-quarters">

        <?php if (have_posts()) : ?>

          <?php while (have_posts()) : the_post(); ?>
            <article class="news-card flex" data-testid="news-post">

              <div class="news-image-wrap">
                <?php if (has_post_thumbnail()) : ?>
                  <?php the_post_thumbnail('medium', ['class' => 'news-image']); ?>
                <?php else : ?>
                  <div class="news-placeholder"></div>
                <?php endif; ?>
              </div>

              <div>
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="news-post-title">
                  <a class="govuk-link" href="<?php the_permalink(); ?>" data-testid="news-post-link">
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p data-testid="news-desc">
                  <?php echo esc_html(gca_clean_post_excerpt(140)); ?>
                </p>

                <div class="date_bottom" data-testid="news-post-meta">
                  <span class="govuk-body-s govuk-!-margin-right-2" style="margin:0">
                    <?php echo esc_html(get_the_date('j F Y')); ?>
                  </span>

                  <?php
                  $categories = get_the_category();

                  if ($categories && $categories[0]->name !== 'Uncategorized') : ?>
                    <span class="govuk-body-s tag_label green" style="margin:0" data-testid="archive-news-post-category">
                      <?php echo esc_html($categories[0]->name); ?>
                    </span>
                  <?php endif;

                  $terms = get_the_terms(get_the_ID(), 'label');

                  if ($terms && !is_wp_error($terms)) : 
                    $term = array_shift($terms); ?>
                    <span class="govuk-body-s tag_label grey" style="margin:0;" data-testid="archive-news-post-label">
                      <?php echo esc_html($term->name); ?>
                    </span>
                  <?php endif; ?>
                </div>

              </div>

            </article>
          <?php endwhile; ?>

          <!-- PAGINATION (WITH FILTER PRESERVATION) -->
          <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8" data-testid="news-pagination">
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
          <p class="govuk-body" data-testid="news-no-posts">No News found.</p>
        <?php endif; ?>

      </div>

    </div>

  </main>
</div>

<?php get_footer(); ?>