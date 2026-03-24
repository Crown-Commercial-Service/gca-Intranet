<?php get_header(); ?>

<?php
$_thumbnail_id = get_post_thumbnail_id();
get_template_part('template-parts/hero', null, [
  'title'     => get_the_title(),
  'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
  'image_alt' => $_thumbnail_id ? (string) get_post_meta($_thumbnail_id, '_wp_attachment_image_alt', true) : '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="single-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="single-main">
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

    <?php get_template_part('template-parts/published-by'); ?>

  </main>
</div>

<?php get_footer(); ?>