<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => '',
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="blog-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="blog-main">

  </main>
</div>

<?php get_footer(); ?>