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

<nav class="gca-breadcrumbs" aria-label="Breadcrumb">
  <div class="container-xxl">
    <ol class="breadcrumb m-0">
      <?php foreach ($items as $i => $item) : ?>
        <?php $is_last = ($i === array_key_last($items)); ?>

        <?php if ($is_last) : ?>
          <li class="breadcrumb-item active" aria-current="page">
            <?php echo esc_html($item['label']); ?>
          </li>
        <?php else : ?>
          <li class="breadcrumb-item">
            <a href="<?php echo esc_url($item['url']); ?>">
              <?php echo esc_html($item['label']); ?>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>
