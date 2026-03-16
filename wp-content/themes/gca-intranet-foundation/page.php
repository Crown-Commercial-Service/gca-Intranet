<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => get_the_title(),
  'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container">
  <main class="govuk-main-wrapper" id="main-content">
    <div class="govuk-grid-row">
      <div class="govuk-grid-column-two-thirds">
        <?php
        if (have_posts()) :
          while (have_posts()) : the_post();
            the_content();
          endwhile;
        endif;
        ?>
      </div>
    </div>

    <?php include(get_template_directory() . '/template-parts/fewbricks-components.php' ); ?>

    <?php get_template_part('template-parts/published-by'); ?>

  </main>
</div>

<?php get_footer(); ?>
