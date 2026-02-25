<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="archive-blog-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="archive-blog-main">
    <div class="govuk-grid-row" data-testid="archive-blog-row">
      <div class="govuk-grid-column-two-thirds" data-testid="archive-blog-col">

        <?php if (have_posts()) : ?>

          <div data-testid="archive-blog-posts">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="govuk-!-margin-bottom-6"
                data-testid="archive-blog-post"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="archive-blog-post-title">
                  <a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="archive-blog-post-link"
                  >
                    <?php the_title(); ?>
                  </a>
                </h2>

                <p class="govuk-body govuk-!-margin-bottom-1" data-testid="archive-blog-post-date">
                  <?php echo esc_html(get_the_date('jS F Y')); ?>
                </p>

                <div class="govuk-body" data-testid="archive-blog-post-excerpt">
                  <?php the_excerpt(); ?>
                </div>
              </article>
            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6" data-testid="archive-blog-pagination">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>
          <p class="govuk-body" data-testid="archive-blog-no-posts">No blog posts found.</p>
        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>