<?php
/**
 * Posts index (home) template
 * Shows latest posts when Settings → Reading → “Your homepage displays” is set to “Your latest posts”.
 */
get_header();
?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => __('News', 'gca-intranet'),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');

// Take a look (Customizer-driven)
$take_enabled = (bool) get_theme_mod('gca_takealook_enabled', true);
$take_title   = (string) get_theme_mod('gca_takealook_title', __('Take a look', 'gca-intranet'));
$take_desc    = (string) get_theme_mod('gca_takealook_desc', '');
$take_text    = (string) get_theme_mod('gca_takealook_link_text', __('Learn more', 'gca-intranet'));
$take_url_raw = (string) get_theme_mod('gca_takealook_link_url', '');

$take_href = $take_url_raw !== '' ? esc_url($take_url_raw) : '';
?>

<div class="govuk-width-container" data-testid="home-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="home-main">
    <div class="govuk-grid-row" data-testid="home-row">

      <!-- Main column: posts -->
      <div class="govuk-grid-column-two-thirds" data-testid="home-col">

        <?php if (have_posts()) : ?>

          <div data-testid="home-posts">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="govuk-!-margin-bottom-6"
                data-testid="home-post"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="home-post-title">
                  <a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="home-post-link"
                  >
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p class="govuk-body govuk-!-margin-bottom-1" data-testid="home-post-date">
                  <?php echo esc_html(get_the_date('jS F Y')); ?>
                </p>

                <div class="govuk-body" data-testid="home-post-excerpt">
                  <?php the_excerpt(); ?>
                </div>
              </article>
            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6" data-testid="home-pagination">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="home-no-posts">
            <?php esc_html_e('No posts found.', 'gca-intranet'); ?>
          </p>
        <?php endif; ?>

      </div>

      <!-- Sidebar: Take a look -->
      <div class="govuk-grid-column-one-third" data-testid="take-a-look-column">
        <?php if ($take_enabled) : ?>

          <div class="gca-homepage-section-title" data-testid="take-a-look-header">
            <h2 class="govuk-heading-m" data-testid="take-a-look-heading">
              <?php echo esc_html($take_title); ?>
            </h2>

            <?php if ($take_desc !== '') : ?>
              <p class="govuk-body" data-testid="take-a-look-subheading">
                <?php echo esc_html($take_desc); ?>
              </p>
            <?php endif; ?>
          </div>

          <?php if ($take_href !== '') : ?>
            <div class="gca-take-a-look" data-testid="take-a-look-card">
              <a
                class="gca-take-a-look__link govuk-link"
                data-testid="take-a-look-link"
                href="<?php echo $take_href; ?>"
              >
                <span class="gca-take-a-look__text">
                  <?php echo esc_html($take_text); ?>
                </span>

                <span class="gca-take-a-look__icon" aria-hidden="true">
                  <svg width="18" height="18" viewBox="0 0 20 20" focusable="false" aria-hidden="true">
                    <path d="M6 14L14 6M9 6h5v5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </span>
              </a>
            </div>
          <?php else : ?>
            <div class="gca-take-a-look" data-testid="take-a-look-card">
              <div
                class="gca-take-a-look__link"
                aria-label="<?php echo esc_attr__('Take a look not configured', 'gca-intranet'); ?>"
              >
                <span class="gca-take-a-look__text">
                  <?php echo esc_html($take_text); ?>
                </span>
              </div>
            </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<?php get_footer(); ?>