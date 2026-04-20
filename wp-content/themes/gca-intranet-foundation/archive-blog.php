<?php get_header(); ?>

<?php
$hero_image_url = gca_get_banner_url('gca_banner_blogs', 'blogs.jpg');

get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="blog-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="blog-main">

    <div class="govuk-grid-row">

      <!-- FILTERS -->
      <div class="govuk-grid-column-one-quarter">
        <?php
        get_template_part(
          'template-parts/components/filter-panel',
          null,
          [
            'post_type' => 'blog',
            'include_taxonomies' => ['label']
          ]
        );
        ?>
      </div>

      <!-- RESULTS -->
      <div class="govuk-grid-column-three-quarters">

        <?php if (have_posts()) : ?>

          <?php while (have_posts()) : the_post(); ?>
            <article class="blog-box" data-testid="blog-post">

              <div class="blog_profile_img">
                <?php echo gca_get_author_image_html(get_the_ID(), (int) get_the_author_meta('ID')); ?>
              </div>

              <div>
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="blog-post-title">
                  <a class="govuk-link" href="<?php the_permalink(); ?>" data-testid="blog-post-link">
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p data-testid="blog-desc">
                  <?php echo esc_html(gca_clean_post_excerpt(140)); ?>
                </p>

                <p class="govuk-body">
                  By <?php echo esc_html(get_the_author()); ?>
                </p>
                
                <div class="date_bottom" data-testid="blog-post-date">
                  <span class="govuk-!-margin-right-2" style="margin:0;">
                    <?php echo esc_html(get_the_date('j F Y')); ?>
                  </span>

                  <?php 
                  $terms = get_the_terms(get_the_ID(), 'label');

                  if ($terms && !is_wp_error($terms)) : 
                    $term = array_shift($terms); ?>
                    <span class="govuk-body-s tag_label location" style="margin:0;">
                      <?php echo esc_html($term->name); ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

            </article>
          <?php endwhile; ?>

          <!-- PAGINATION -->
          <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8" data-testid="blog-pagination">
            <?php 
              the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => sprintf(
                  '<span class="icon">
                    <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M6.7 0l1.4 1.4-4.3 4.3h13v2H3.9l4.2 4-1.4 1.4L0 6.7z" fill="#007194"/></svg>
                  </span> <span>Previous</span>
                  <span class="govuk-visually-hidden">page</span>'
                ),
                'next_text' => sprintf(
                  '<span>Next</span> <span class="govuk-visually-hidden">page</span>
                  <span class="icon">
                    <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M10.1 0L8.7 1.4 13 5.7H0v2h12.9l-4.2 4 1.4 1.4 6.7-6.4z" fill="#007194"/></svg>
                  </span>'
                ),
              ]); 
            ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="blog-no-posts">No Blog found.</p>
        <?php endif; ?>

      </div>

    </div>

  </main>
</div>

<?php get_footer(); ?>