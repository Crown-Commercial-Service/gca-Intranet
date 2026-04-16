<?php
/**
 * Usage:
 * get_template_part('template-parts/published-by');
 */

// Get post context safely
$post_id   = get_the_ID();
$post_type = get_post_type($post_id);

// Author logic
if ($post_type === 'work_update') {
    $author = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
} else {
    if (function_exists('gca_get_display_author')) {
        $author = gca_get_display_author($post_id);
    } else {
        $author = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
    }
}

// Modified date
$modified_date = get_the_modified_date('j F Y', $post_id);

if (!$author && !$modified_date) {
    return;
}

// Args (safe fallback)
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

    <?php if ($modified_date && !$hide_last_updated) : ?>
      <span class="gca-published-by__date" data-testid="published-by-date">
        Last updated <?php echo esc_html($modified_date); ?>
      </span>
    <?php endif; ?>

  </div>
</div>
