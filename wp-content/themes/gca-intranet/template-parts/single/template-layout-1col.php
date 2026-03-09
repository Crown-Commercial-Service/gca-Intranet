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

        <div class="gca-news-meta govuk-!-margin-bottom-2" data-testid="content-meta">
          <time class="govuk-body-s" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
            <?php echo esc_html(get_the_date('jS F Y')); ?>
          </time>
        </div>

        <?php
        /* Show taxonomy tags for News + Blog */
        if (in_array($post_type, ['news', 'blog'], true)) :

          $categories = get_the_terms($post_id, 'category');
          $labels     = get_the_terms($post_id, 'label');

          if (!empty($categories) || !empty($labels)) :
        ?>

          <div class="gca-taxonomy-tags govuk-!-margin-bottom-4" data-testid="content-tags">

            <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
              <?php foreach ($categories as $term) : ?>
                <span class="govuk-tag govuk-tag--green">
                  <?php echo esc_html($term->name); ?>
                </span>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($labels) && !is_wp_error($labels)) : ?>
              <?php foreach ($labels as $term) : ?>
                <span class="govuk-tag govuk-tag--grey">
                  <?php echo esc_html($term->name); ?>
                </span>
              <?php endforeach; ?>
            <?php endif; ?>

          </div>

        <?php endif; endif; ?>

        <?php if (has_post_thumbnail($post_id) && !$hide_featured) : ?>
          <figure class="gca-featured-media govuk-!-margin-bottom-4" data-testid="featured-image">
            <?php echo get_the_post_thumbnail($post_id, 'large', ['class' => 'gca-featured-media__img']); ?>
          </figure>
        <?php endif; ?>

      <?php endif; ?>

      <?php get_template_part('template-parts/template-body-content'); ?>
    </main>

  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>