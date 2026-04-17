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
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1">

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
            <article class="blog-box">

              <div class="blog_profile_img">
                <?php 
                  $custome_author_img = get_field('image'); 
                  
                  if ($custome_author_img) : 
                      echo wp_get_attachment_image($custome_author_img, 'thumbnail', false, ['class' => 'avatar']); 
                  else : 
                      echo get_avatar(get_the_author_meta('ID'));
                  endif; 
                ?>
              </div>

              <div>
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2">
                  <a class="govuk-link" href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p>
                  <?php echo esc_html(gca_clean_post_excerpt(320)); ?>
                </p>

                <p class="govuk-body">
                  By <?php echo esc_html(get_the_author()); ?>
                </p>
                
                <div class="date_bottom">
                  <span>
                    <?php echo esc_html(get_the_date('j F Y')); ?>
                  </span>

                  <?php 
                  $post_terms = get_the_terms(get_the_ID(), 'label');

                  if ($post_terms && !is_wp_error($post_terms)) : 
                    $term = array_shift($post_terms); ?>
                    <span class="govuk-body-s tag_label location">
                        <?php echo esc_html($term->name); ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

            </article>
          <?php endwhile; ?>

          <!-- PAGINATION WITH FILTER PERSISTENCE -->
          <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8">
            <?php
            // Sanitize GET params for safe pagination
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
          <p class="govuk-body">No Blog found.</p>
        <?php endif; ?>

      </div>

    </div>

  </main>
</div>

<?php get_footer(); ?>