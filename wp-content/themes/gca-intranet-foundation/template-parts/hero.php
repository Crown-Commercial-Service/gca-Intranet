<?php
/**
 * Hero band (title + optional image)
 *
 * Usage:
 * get_template_part('template-parts/hero', null, [
 *   'title' => 'Page title',
 *   'image_url' => 'https://...'
 * ]);
 */
$title     = $args['title'] ?? '';
$image_url = $args['image_url'] ?? '';
$image_alt = $args['image_alt'] ?? '';
?>

<section class="gca-hero" aria-label="Page header">
  <div class="container-xxl">
    <div class="row align-items-center g-4">
      <div class="col-12 col-lg-6">
        <?php if ($title) : ?>
          <h1 class="gca-hero__title m-0"><?php echo esc_html($title); ?></h1>
        <?php endif; ?>
      </div>

      <div class="col-12 col-lg-6 text-lg-end">
        <?php if ($image_url) : ?>
          <img
            class="gca-hero__image"
            src="<?php echo esc_url($image_url); ?>"
            alt="<?php echo esc_attr($image_alt); ?>"
            <?php if (!$image_alt) : ?>aria-hidden="true"<?php endif; ?>
            loading="lazy"
          >
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
