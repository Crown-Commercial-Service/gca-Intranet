<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => get_the_title(),
  'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="container-xxl py-4">
  <?php
  if (have_posts()) :
    while (have_posts()) : the_post();
      the_content();
    endwhile;
  endif;
  ?>
</div>

<?php get_footer(); ?>
