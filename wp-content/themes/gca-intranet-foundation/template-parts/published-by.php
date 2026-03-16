<?php
/**
 * Usage:
 * get_template_part('template-parts/published-by');
 */

$author        = get_the_author();
$modified_date = get_the_modified_date('j F Y');

if (!$author && !$modified_date) {
  return;
}
?>
<?php
  $hide_last_updated = $args['hide_last_updated'] ?? false;
?>

<div class="gca-published-by" data-testid="published-by">
  <hr class="gca-published-by__divider" aria-hidden="true">
  <div class="gca-published-by__meta govuk-body-s">
    <?php if ($author) : ?>
      <span class="gca-published-by__author" data-testid="published-by-author">
        By <?php echo esc_html($author); ?>
      </span>
    <?php endif; ?>

    <?php if ($modified_date and !$hide_last_updated) : ?>
      <span class="gca-published-by__date" data-testid="published-by-date">
        Last updated <?php echo esc_html($modified_date); ?>
      </span>
    <?php endif; ?>
  </div>
</div>
