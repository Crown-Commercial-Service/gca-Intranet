<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => get_the_title(),
  'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="single-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="single-main">
    <div class="govuk-grid-row" data-testid="single-row">
      <div class="govuk-grid-column-two-thirds" data-testid="single-col">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

          <article
            <?php post_class(); ?>
            data-testid="single-article"
            data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
          >
            <header data-testid="single-header">
              <h1 class="govuk-heading-xl" data-testid="single-title">
                <?php the_title(); ?>
              </h1>
            </header>

            <div class="govuk-body" data-testid="single-content">
              <?php the_content(); ?>
            </div>
          </article>

        <?php endwhile; endif; ?>
      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>