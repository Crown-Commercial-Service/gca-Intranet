<?php
/**
 * Sort Control Component
 * 
 * Usage:
 * get_template_part('template-parts/components/sort-control');
 */

$current_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';
?>

<form method="get" class="govuk-!-margin-bottom-4" data-testid="sort-control">

  <div class="govuk-form-group">
    <label class="govuk-label" for="sort">
      Sort by
    </label>

    <select class="govuk-select" id="sort" name="sort" onchange="this.form.submit()">
      <option value="newest" <?php selected($current_sort, 'newest'); ?>>
        Newest First
      </option>
      <option value="oldest" <?php selected($current_sort, 'oldest'); ?>>
        Oldest first
      </option>
    </select>
  </div>

  <?php
  // Preserve existing query params (filters, search, etc.)
  foreach ($_GET as $key => $value) {
    if ($key === 'sort' || $key === 'paged') continue;

    if (is_array($value)) {
      foreach ($value as $val) {
        echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($val) . '">';
      }
    } else {
      echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
    }
  }
  ?>

</form>