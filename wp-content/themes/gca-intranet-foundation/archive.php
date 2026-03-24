<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => get_the_archive_title(),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="archive-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="archive-main">
    <div class="govuk-grid-row" data-testid="archive-row">
      <div class="govuk-grid-column-two-thirds" data-testid="archive-col">

        <?php if (get_the_archive_description()) : ?>
          <div class="govuk-body govuk-!-margin-bottom-6" data-testid="archive-description">
            <?php echo wp_kses_post(get_the_archive_description()); ?>
          </div>
        <?php endif; ?>

        <?php if (have_posts()) : ?>

          <div data-testid="archive-posts">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="govuk-!-margin-bottom-6"
                data-testid="archive-post"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="archive-post-title">
                  <a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="archive-post-link"
                  >
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p class="govuk-body govuk-!-margin-bottom-1" data-testid="archive-post-date">
                  <?php echo esc_html(get_the_date('j F Y')); ?>
                </p>

                <div class="govuk-body" data-testid="archive-post-excerpt">
                  <?php the_excerpt(); ?>
                </div>
              </article>
            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6" data-testid="archive-pagination">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="archive-no-content">
            <?php esc_html_e('No content found.', 'gca-intranet'); ?>
          </p>
        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>