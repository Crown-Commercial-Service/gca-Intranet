<?php
/**
 * Template Name: Three Column Template (Category)
 * Description: 3-column card grid showing Posts from the same Category as this landing page.
 */

get_header();

get_template_part('template-parts/hero', null, [
  'title'     => get_the_title(),
  'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
]);

get_template_part('template-parts/breadcrumbs');

$terms = get_the_terms(get_the_ID(), 'category');
$term  = (!empty($terms) && !is_wp_error($terms)) ? $terms[0] : null;

$paged    = max(1, (int) get_query_var('paged'));
$per_page = 12;

$cards = null;
if ($term) {
  $cards = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => [
      [
        'taxonomy' => 'category',
        'field'    => 'term_id',
        'terms'    => (int) $term->term_id,
      ],
    ],
  ]);
}

function gca_render_govuk_pagination(int $current, int $total): void
{
  if ($total <= 1) {
    return;
  }

  echo '<nav class="govuk-pagination" aria-label="Pagination" data-testid="three-col-category-pagination">';

  if ($current > 1) {
    $prev = $current - 1;
    echo '<div class="govuk-pagination__prev">';
    echo '<a class="govuk-link govuk-pagination__link" data-testid="pagination-prev" href="' . esc_url(get_pagenum_link($prev)) . '" rel="prev">';
    echo '<svg class="govuk-pagination__icon govuk-pagination__icon--prev" xmlns="http://www.w3.org/2000/svg" height="13" width="15" aria-hidden="true" focusable="false" viewBox="0 0 15 13"><path d="m6.5938-0.0078125-6.7266 6.7266 6.7441 6.4062 1.377-1.449-4.1856-3.9768h12.896v-2h-12.984l4.2931-4.293-1.414-1.414z"></path></svg>';
    echo '<span class="govuk-pagination__link-title">Previous<span class="govuk-visually-hidden"> page</span></span>';
    echo '</a></div>';
  }

  $window = 2;
  $start  = max(1, $current - $window);
  $end    = min($total, $current + $window);

  echo '<ul class="govuk-pagination__list">';

  if ($start > 1) {
    echo '<li class="govuk-pagination__item"><a class="govuk-link govuk-pagination__link" href="' . esc_url(get_pagenum_link(1)) . '" aria-label="Page 1">1</a></li>';
  }

  for ($i = $start; $i <= $end; $i++) {
    if ($i === $current) {
      echo '<li class="govuk-pagination__item govuk-pagination__item--current">';
      echo '<a class="govuk-link govuk-pagination__link" href="' . esc_url(get_pagenum_link($i)) . '" aria-label="Page ' . esc_attr((string) $i) . '" aria-current="page">' . esc_html((string) $i) . '</a>';
      echo '</li>';
    } else {
      echo '<li class="govuk-pagination__item">';
      echo '<a class="govuk-link govuk-pagination__link" href="' . esc_url(get_pagenum_link($i)) . '" aria-label="Page ' . esc_attr((string) $i) . '">' . esc_html((string) $i) . '</a>';
      echo '</li>';
    }
  }

  if ($end < $total) {
    echo '<li class="govuk-pagination__item"><a class="govuk-link govuk-pagination__link" href="' . esc_url(get_pagenum_link($total)) . '" aria-label="Page ' . esc_attr((string) $total) . '">' . esc_html((string) $total) . '</a></li>';
  }

  echo '</ul>';

  if ($current < $total) {
    $next = $current + 1;
    echo '<div class="govuk-pagination__next">';
    echo '<a class="govuk-link govuk-pagination__link" data-testid="pagination-next" href="' . esc_url(get_pagenum_link($next)) . '" rel="next">';
    echo '<span class="govuk-pagination__link-title">Next<span class="govuk-visually-hidden"> page</span></span>';
    echo '<svg class="govuk-pagination__icon govuk-pagination__icon--next" xmlns="http://www.w3.org/2000/svg" height="13" width="15" aria-hidden="true" focusable="false" viewBox="0 0 15 13"><path d="m8.107-0.0078125-1.4136 1.414 4.2926 4.293h-12.986v2h12.896l-4.1855 3.9766 1.377 1.4492 6.7441-6.4062-6.7246-6.7266z"></path></svg>';
    echo '</a></div>';
  }

  echo '</nav>';
}
?>

<div class="govuk-width-container gca-three-col-category" data-testid="three-col-category-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="three-col-category-main">

    <div class="govuk-grid-row" data-testid="three-col-category-intro-row">
      <div class="govuk-grid-column-two-thirds">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
          <div class="govuk-body" data-testid="three-col-category-intro">
            <?php the_content(); ?>
          </div>
        <?php endwhile; endif; ?>
      </div>
    </div>

    <?php if (!$term) : ?>

      <p class="govuk-body govuk-!-margin-top-6" data-testid="three-col-category-no-category">
        This page needs a Category assigned to show cards.
      </p>

    <?php elseif ($cards && $cards->have_posts()) : ?>

      <div class="govuk-grid-row govuk-!-margin-top-6" data-testid="three-col-category-grid">
        <?php while ($cards->have_posts()) : $cards->the_post(); ?>

          <?php
          $raw = has_excerpt()
            ? get_the_excerpt()
            : wp_strip_all_tags(get_the_content());

          $summary = wp_html_excerpt($raw, 125, '…');
          ?>

          <div class="govuk-grid-column-one-third govuk-!-margin-bottom-6" data-testid="three-col-category-col">
            <article
              class="gca-threecol-card"
              data-testid="three-col-category-card"
              data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
            >
              <h3 class="govuk-heading-m gca-threecol-card__title" data-testid="three-col-category-card-title">
                <a
                  class="govuk-link govuk-link--no-visited-state gca-threecol-card__link"
                  href="<?php the_permalink(); ?>"
                  data-testid="three-col-category-card-link"
                >
                  <?php the_title(); ?>
                  <span class="gca-threecol-card__chevron" aria-hidden="true">›</span>
                </a>
              </h3>

              <p
                class="govuk-body gca-threecol-card__excerpt"
                data-testid="three-col-category-card-excerpt"
                data-test="card-post-info"
              >
                <?php echo esc_html($summary); ?>
              </p>
            </article>
          </div>

        <?php endwhile; wp_reset_postdata(); ?>
      </div>

      <?php gca_render_govuk_pagination($paged, (int) $cards->max_num_pages); ?>

    <?php else : ?>

      <p class="govuk-body govuk-!-margin-top-6" data-testid="three-col-category-no-results">
        No posts found in “<?php echo esc_html($term->name); ?>”.
      </p>

    <?php endif; ?>

  </main>
</div>

<?php get_footer(); ?>