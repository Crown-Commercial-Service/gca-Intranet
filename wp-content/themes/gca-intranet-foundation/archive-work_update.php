<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="archive-work-update-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="archive-work-update-main">
    <div class="govuk-grid-row" data-testid="archive-work-update-row">
      <div class="govuk-grid-column-two-thirds" data-testid="archive-work-update-col">

        <?php if (have_posts()) : ?>

          <div data-testid="archive-work-update-posts">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="govuk-!-margin-bottom-6"
                data-testid="archive-work-update-post"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="archive-work-update-post-title">
                  <a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="archive-work-update-post-link"
                  >
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p class="govuk-body govuk-!-margin-bottom-1" data-testid="archive-work-update-post-date">
                  <?php echo esc_html(get_the_date('jS F Y')); ?>
                </p>

                <div class="govuk-body" data-testid="archive-work-update-post-excerpt">
                  <?php the_excerpt(); ?>
                </div>
              </article>
            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6" data-testid="archive-work-update-pagination">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="archive-work-update-no-posts">No work updates found.</p>
        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>