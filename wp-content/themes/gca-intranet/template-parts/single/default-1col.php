<?php
/**
 * Template Name: Layout – 1 column
 *
 * Default 1-column layout (shared by pages + CPT singles)
 * - Pages: hero shows title; body does NOT repeat title; no featured image; no date
 * - CPTs: hero shows CPT label; body shows post title + date + optional featured image
 * - News/Blogs: featured image can be hidden via checkbox meta: _gca_hide_featured_image
 */

get_template_part('template-parts/single/_chrome');

$is_page = !empty($GLOBALS['gca_is_page']);
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-6" data-testid="page-container">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <?php
      $post_id   = get_the_ID();
      $post_type = (string) get_post_type($post_id);

      $hide_featured =
        in_array($post_type, ['news', 'blog'], true) &&
        (bool) get_post_meta($post_id, '_gca_hide_featured_image', true);
    ?>

    <main class="gca-single gca-single--1col" data-testid="layout-1col">

      <?php if (!$is_page) : ?>
        <h1 class="govuk-heading-l govuk-!-margin-bottom-2" data-testid="content-title">
          <?php the_title(); ?>
        </h1>

        <div class="gca-news-meta govuk-!-margin-bottom-4" data-testid="content-meta">
          <time class="govuk-body-s" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
            <?php echo esc_html(get_the_date('j F Y')); ?>
          </time>
        </div>

        <?php if (has_post_thumbnail($post_id) && !$hide_featured) : ?>
          <figure class="gca-featured-media govuk-!-margin-bottom-4" data-testid="featured-image">
            <?php echo get_the_post_thumbnail($post_id, 'large', ['class' => 'gca-featured-media__img']); ?>
          </figure>
        <?php endif; ?>
      <?php endif; ?>

      <div class="gca-richtext" data-testid="content-body">
        <?php the_content(); ?>
      </div>

    </main>

  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>