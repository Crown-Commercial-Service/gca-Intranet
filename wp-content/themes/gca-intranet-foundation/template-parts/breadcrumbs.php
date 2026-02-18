<?php
/**
 * Breadcrumbs bar
 *
 * Depends on: gca_get_breadcrumb_items() in functions.php
 */
if (!function_exists('gca_get_breadcrumb_items') || is_front_page()) {
  return;
}

$items = gca_get_breadcrumb_items();
if (empty($items)) {
  return;
}
?>

<nav class="govuk-breadcrumbs gca-breadcrumbs" aria-label="Breadcrumb">
  <ol class="govuk-breadcrumbs__list container-xxl">
    <?php foreach ($items as $i => $item) : ?>
    <?php $is_last = ($i === array_key_last($items)); ?>

    <?php if (!$is_last) : ?>
    <li class="govuk-breadcrumbs__list-item">
      <a href="<?php echo esc_url($item['url']); ?>" class="govuk-breadcrumbs__link"><?php echo esc_html($item['label']); ?></a>
    </li>
    <?php else : ?>
    <li class="govuk-breadcrumbs__list-item">
      <?php echo esc_html($item['label']); ?>
    </li>
    <?php endif; ?>
    <?php endforeach; ?>
  </ol>
</nav>
