<?php

add_action('pre_get_posts', function ($query) {

  // Only affect frontend main query
  if (is_admin() || !$query->is_main_query()) {
    return;
  }

  // Only run on archive + search pages
  if (!is_archive() && !is_search()) {
    return;
  }

  // Start tax query with AND relation
  $tax_query = [
    'relation' => 'AND'
  ];

  // Loop through all GET params
  foreach ($_GET as $key => $values) {

    // Skip post_type (handled separately)
    if ($key === 'post_type') {
      continue;
    }

    // Only process array values (e.g. label[])
    if (!is_array($values)) {
      continue;
    }

    // Skip if taxonomy doesn't exist
    if (!taxonomy_exists($key)) {
      continue;
    }

    // Sanitize values
    $values = array_map('sanitize_text_field', $values);

    $tax_query[] = [
      'taxonomy' => $key,
      'field'    => 'slug',
      'terms'    => $values,
      'operator' => 'IN' // OR within a taxonomy
    ];
  }

  // Apply taxonomy filters if present
  if (count($tax_query) > 1) {
    $query->set('tax_query', $tax_query);
  }

  /**
   * Handle post_type filter (for search page)
   */
  if (!empty($_GET['post_type'])) {

    $post_types = array_map(
      'sanitize_text_field',
      (array) $_GET['post_type']
    );

    $query->set('post_type', $post_types);
  }
});