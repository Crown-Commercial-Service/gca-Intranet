<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container">
  <main class="govuk-main-wrapper" id="main-content">
    <div class="govuk-grid-row">
      <div class="govuk-grid-column-two-thirds">

        <?php if (have_posts()) : ?>

          <?php while (have_posts()) : the_post(); ?>
            <article class="govuk-!-margin-bottom-6">
              <h2 class="govuk-heading-m govuk-!-margin-bottom-2">
                <a class="govuk-link" href="<?php the_permalink(); ?>">
                  <?php the_title(); ?>
                </a>
              </h2>

              <p class="govuk-body govuk-!-margin-bottom-1">
                <?php echo esc_html(get_the_date('jS F Y')); ?>
              </p>

              <div class="govuk-body">
                <?php the_excerpt(); ?>
              </div>
            </article>
          <?php endwhile; ?>

          <div class="govuk-!-margin-top-6">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>
          <p class="govuk-body">No blog posts found.</p>
        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>