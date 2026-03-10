<?php
get_header();

global $wp_query;
$search_query = get_search_query();
$search_url   = get_theme_mod('gca_search_url', home_url('/'));
$found_posts  = (int) $wp_query->found_posts;

$hero_image_url = get_template_directory_uri() . '/assets/img/office.jpg';
?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => __('Search the intranet', 'gca-intranet'),
  'image_url' => $hero_image_url,
]);
?>

<?php get_template_part('template-parts/breadcrumbs'); ?>

<div class="govuk-width-container" data-testid="search-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="search-main">
    <div class="govuk-grid-row" data-testid="search-row">
      <div class="govuk-grid-column-two-thirds" data-testid="search-col">

        <h1 class="govuk-heading-l govuk-!-margin-bottom-4" data-testid="search-heading">
          Search results for <span class="gca-search-query">&ldquo;<?php echo esc_html($search_query); ?>&rdquo;</span>
        </h1>

        <form
          class="gca-search-results-form govuk-!-margin-bottom-4"
          role="search"
          action="<?php echo esc_url($search_url); ?>"
          method="get"
          data-testid="search-results-form"
        >
          <label class="govuk-visually-hidden" for="search-results-input"><?php esc_html_e('Search the intranet', 'gca-intranet'); ?></label>
          <div class="search-input-group">
            <input
              id="search-results-input"
              name="s"
              type="search"
              class="govuk-input"
              placeholder="<?php esc_attr_e('Search again', 'gca-intranet'); ?>"
              value="<?php echo esc_attr($search_query); ?>"
              autocomplete="off"
            >
            <button class="govuk-button search-submit" type="submit" aria-label="<?php esc_attr_e('Search', 'gca-intranet'); ?>">
              <span class="govuk-visually-hidden"><?php esc_html_e('Search', 'gca-intranet'); ?></span>
              <svg class="gca-search-icon" width="20" height="20" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M11.742 10.344l3.387 3.387-.998.998-3.387-3.387a6 6 0 1 1 .998-.998zM6.5 11.5a5 5 0 0 0 0-10 5 5 0 0 0 0 10z"></path>
              </svg>
            </button>
          </div>
        </form>

        <?php if (have_posts()) : ?>

          <p class="govuk-body govuk-!-margin-bottom-6" data-testid="search-result-count">
            Found <?php echo esc_html((string) $found_posts); ?> result<?php echo $found_posts !== 1 ? 's' : ''; ?>
          </p>

          <div data-testid="search-results">
            <?php
            $result_index = 0;
            $total_results = $wp_query->post_count;
            while (have_posts()) : the_post();
              $result_index++;

              $content_type = gca_search_get_content_type_label();
              $raw_title    = get_the_title();
              $title        = gca_search_truncate($raw_title, 85);
              $raw_excerpt  = get_the_excerpt();
              $excerpt      = gca_search_truncate($raw_excerpt, 125);
              $terms        = gca_search_get_post_terms();
            ?>

              <article
                class="gca-search-result govuk-!-margin-bottom-0"
                data-testid="search-result"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="search-result-title">
                  <span class="gca-search-result__type"><?php echo esc_html($content_type); ?> - </span><a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="search-result-link"
                  ><?php echo esc_html($title); ?></a>
                </h2>

                <?php if ($excerpt) : ?>
                  <p class="govuk-body govuk-!-margin-bottom-2" data-testid="search-result-excerpt">
                    <?php echo esc_html($excerpt); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($terms)) : ?>
                  <div class="gca-search-result__terms" data-testid="search-result-terms">
                    <?php foreach ($terms as $term) : ?>
                      <?php $pill_class = ($term->taxonomy === 'category') ? 'tag_label' : 'tag_label grey'; ?>
                      <span class="<?php echo esc_attr($pill_class); ?>"><?php echo esc_html($term->name); ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

              </article>

              <?php if ($result_index < $total_results) : ?>
                <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
              <?php endif; ?>

            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6" data-testid="search-pagination">
            <?php
            the_posts_pagination([
              'prev_text' => __('&larr; Previous', 'gca-intranet'),
              'next_text' => __('Next &rarr;', 'gca-intranet'),
              'mid_size'  => 2,
            ]);
            ?>
          </div>

        <?php else : ?>

          <p class="govuk-body" data-testid="search-no-results">
            No results found for &ldquo;<?php echo esc_html($search_query); ?>&rdquo;. Try a different search term.
          </p>

        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>
