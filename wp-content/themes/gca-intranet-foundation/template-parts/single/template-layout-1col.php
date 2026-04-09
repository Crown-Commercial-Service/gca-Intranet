<?php
/**
 * Template Name: Layout – 1 column
 */

get_template_part('template-parts/single/_chrome');
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-6">
  <?php if (have_posts()) : while (have_posts()) : the_post(); 
      $post_type = get_post_type();
  ?>

    <main class="gca-single gca-single--1col" id="main-content" tabindex="-1">
      <?php 
        /**
         * This looks for template-parts/content-event.php, content-page.php, etc.
         * You put your title, helper function, and featured image logic in those files.
         */
        get_template_part('template-parts/content', $post_type); 
      ?>

      <div class="gca-content">
          <?php get_template_part('template-parts/template-body-content'); ?>
      </div>
    </main>

  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>