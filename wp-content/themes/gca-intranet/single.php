<?php
echo '<!-- CHILD SINGLE LOADED -->';
/**
 * Single Post (News)
 * - Hero banner image comes from meta _gca_hero_image_id (editable)
 * - Featured image is used in the LEFT column
 */
get_header();

$hero_id  = (int) get_post_meta(get_the_ID(), '_gca_hero_image_id', true);
$hero_url = $hero_id ? wp_get_attachment_image_url($hero_id, 'large') : '';

get_template_part('template-parts/hero', null, [
  'title'     => 'News',
  'image_url' => $hero_url ?: '', // header image ONLY
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-6">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <div class="govuk-grid-row">

      <!-- LEFT: Featured image -->
      <div class="govuk-grid-column-one-third">
        <?php if (has_post_thumbnail()) : ?>
          <figure class="gca-featured-media">
            <?php the_post_thumbnail('large', ['class' => 'gca-featured-media__img']); ?>
          </figure>
        <?php endif; ?>
      </div>

      <!-- RIGHT: Content -->
      <div class="govuk-grid-column-two-thirds">
        <h1 class="govuk-heading-l govuk-!-margin-bottom-2"><?php the_title(); ?></h1>

        <div class="gca-news-meta govuk-!-margin-bottom-4">
          <time class="govuk-body-s" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
            <?php echo esc_html(get_the_date('j F Y')); ?>
          </time>

          <?php $cats = get_the_category(); if (!empty($cats)) : ?>
            <strong class="govuk-tag"><?php echo esc_html($cats[0]->name); ?></strong>
          <?php endif; ?>
        </div>

        <div class="gca-richtext">
          <?php the_content(); ?>
        </div>
      </div>

    </div>

  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>