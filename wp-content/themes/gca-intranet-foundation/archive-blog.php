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
        $selected_labels = isset($_GET['label'])
          ? array_map('sanitize_text_field', (array) $_GET['label'])
          : [];

        $filter_terms = get_terms([
          'taxonomy'   => 'label',
          'hide_empty' => true,
        ]);

        $view_all_checked = empty($selected_labels) ? 'checked' : '';
        ?>

        <form method="get" class="gca-filters">

          <!-- HEADER -->
          <div class="gca-filters__header">
            <h2 class="govuk-heading-m">Apply filters</h2>

            <?php if (!empty($selected_labels)) : ?>
              <a href="<?php echo esc_url(get_post_type_archive_link('blog')); ?>" class="govuk-link govuk-body-s">
                Clear filters
              </a>
            <?php endif; ?>
          </div>

          <!-- GOV ACCORDION -->
          <div 
            class="govuk-accordion gca-filter-card" 
            data-module="govuk-accordion"
            data-show-all-sections="false"
            data-show-all-text="false"
            id="filters-accordion"
          >

            <div class="govuk-accordion__section govuk-accordion__section--expanded">

              <!-- HEADER -->
              <div class="govuk-accordion__section-header">
                <h3 class="govuk-accordion__section-heading">
                  <button
                    type="button"
                    class="govuk-accordion__section-button gca-filter-card__title"
                    id="filter-heading-type"
                  >
                    Type of article
                  </button>
                </h3>
              </div>

              <!-- CONTENT -->
              <div
                id="filter-content-type"
                class="govuk-accordion__section-content gca-filter-card__content"
                aria-labelledby="filter-heading-type"
              >

                <div class="govuk-checkboxes" data-module="govuk-checkboxes">

                  <!-- VIEW ALL -->
                  <div class="govuk-checkboxes__item govuk-checkboxes__item--small">
                    <input
                      class="govuk-checkboxes__input govuk-checkboxes__input--small"
                      id="view-all"
                      type="checkbox"
                      <?php echo $view_all_checked; ?>
                      onclick="window.location.href='<?php echo esc_url(get_post_type_archive_link('blog')); ?>'"
                    >
                    <label class="govuk-label govuk-checkboxes__label" for="view-all">
                      View all
                    </label>
                  </div>

                  <!-- TERMS -->
                  <?php foreach ($filter_terms as $term) : ?>
                    <?php $checked = in_array($term->slug, $selected_labels) ? 'checked' : ''; ?>

                    <div class="govuk-checkboxes__item govuk-checkboxes__item--small">
                      <input
                        class="govuk-checkboxes__input govuk-checkboxes__input--small"
                        id="term-<?php echo esc_attr($term->slug); ?>"
                        name="label[]"
                        type="checkbox"
                        value="<?php echo esc_attr($term->slug); ?>"
                        <?php echo $checked; ?>
                        onchange="this.form.submit();"
                      >
                      <label
                        class="govuk-label govuk-checkboxes__label"
                        for="term-<?php echo esc_attr($term->slug); ?>"
                      >
                        <?php echo esc_html($term->name); ?>
                      </label>
                    </div>

                  <?php endforeach; ?>

                </div>

              </div>

            </div>

          </div>

        </form>

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

          <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8">
            <?php 
              the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => '<span>Previous</span>',
                'next_text' => '<span>Next</span>',
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