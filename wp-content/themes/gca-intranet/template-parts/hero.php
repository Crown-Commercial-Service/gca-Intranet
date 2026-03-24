<?php
/**
 * Hero banner (GCA)
 * Args:
 *  - title (string)
 *  - image_url (string)  // optional
 */

$title     = isset($args['title']) ? (string) $args['title'] : '';
$image_url = isset($args['image_url']) ? (string) $args['image_url'] : '';

if ($title === '') {
  $title = get_the_title();
}
?>

<section class="gca-hero-banner" aria-label="Page banner">
  <div class="govuk-width-container">
    <div class="gca-hero-banner__inner">
      <h1 class="govuk-heading-xl gca-hero-banner__title">
        <?php echo esc_html($title); ?>
      </h1>
    </div>
  </div>

  <?php if (!empty($image_url)) : ?>
    <div class="gca-hero-banner__media" aria-hidden="true">
      <img src="<?php echo esc_url($image_url); ?>" alt="">
    </div>
  <?php endif; ?>
</section>
