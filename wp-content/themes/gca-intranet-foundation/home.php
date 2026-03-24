<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => __('News', 'gca-intranet'),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="home-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="home-main">
    <div class="govuk-grid-row" data-testid="home-row">
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
                  <?php echo esc_html(get_the_date('j F Y')); ?>
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
    </div>
  </main>
</div>

<?php get_footer(); ?>