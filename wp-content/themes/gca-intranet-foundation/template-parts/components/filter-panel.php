<?php
$args = $args ?? [];

$post_type = $args['post_type'] ?? get_post_type();

$include = $args['include_taxonomies'] ?? [];
$exclude = $args['exclude_taxonomies'] ?? ['post_format'];

/**
 * Use ALL taxonomies on search
 */
if (is_search()) {
  $taxonomies = get_taxonomies([], 'objects');
} else {
  $taxonomies = get_object_taxonomies($post_type, 'objects');
}

/**
 * Filter order
 */
$taxonomy_order = [
  'category',
  'label',
  'content_type',
  'audience',
  'event_location',
  'responsible_team',
];

/**
 * Labels
 */
$label_overrides = [
  'category' => 'Category',
  'label' => 'Type of article',
  'content_type' => 'Content type',
  'audience' => 'Audience',
  'event_location' => 'Location',
  'responsible_team' => 'Responsible directorate/team',
];

/**
 * Selected post types (for smart filtering)
 */
$selected_post_types = isset($_GET['post_type'])
  ? array_map('sanitize_text_field', (array) $_GET['post_type'])
  : [];

$is_event_selected = in_array('event', $selected_post_types);
$is_work_update_selected = in_array('work_update', $selected_post_types);

/**
 * Clear URL
 */
$clear_url = is_search()
  ? home_url('/?s=' . urlencode(get_search_query()))
  : get_post_type_archive_link($post_type);

/**
 * SORT VALUE
 */
$current_sort = isset($_GET['sort'])
  ? sanitize_text_field($_GET['sort'])
  : 'newest';
?>

<form method="get" class="gca-filters" data-testid="filters-form">

  <!-- Preserve search -->
  <?php if (is_search() && get_search_query()) : ?>
    <input type="hidden" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
  <?php endif; ?>

  <!-- Header -->
  <div class="gca-filters__header" data-testid="filters-header">
    <h2 class="govuk-heading-m" id="filters-heading">Apply filters</h2>

    <?php if (!empty(array_filter($_GET))) : ?>
      <a 
        href="<?php echo esc_url($clear_url); ?>" 
        class="govuk-link govuk-body-s"
        data-testid="clear-filters"
      >
        Clear filters
      </a>
    <?php endif; ?>
  </div>

  <!-- SORT (NEW) -->
  <div class="gca-filter-card" data-testid="filter-sort">

    <fieldset class="govuk-fieldset">
      <legend class="govuk-fieldset__legend govuk-fieldset__legend--s">
        <h3 class="govuk-fieldset__heading">
          Sort by
        </h3>
      </legend>

      <div class="govuk-radios govuk-radios--small" data-module="govuk-radios">

        <div class="govuk-radios__item">
          <input
            class="govuk-radios__input"
            id="sort-newest"
            name="sort"
            type="radio"
            value="newest"
            <?php checked($current_sort, 'newest'); ?>
            onchange="this.form.submit();"
          >
          <label class="govuk-label govuk-radios__label" for="sort-newest">
            Newest First
          </label>
        </div>

        <div class="govuk-radios__item">
          <input
            class="govuk-radios__input"
            id="sort-oldest"
            name="sort"
            type="radio"
            value="oldest"
            <?php checked($current_sort, 'oldest'); ?>
            onchange="this.form.submit();"
          >
          <label class="govuk-label govuk-radios__label" for="sort-oldest">
            Oldest First
          </label>
        </div>

      </div>
    </fieldset>

  </div>

  <?php foreach ($taxonomy_order as $tax_name) :

    if (!isset($taxonomies[$tax_name])) continue;

    $taxonomy = $taxonomies[$tax_name];

    if (!empty($include) && !in_array($taxonomy->name, $include)) continue;
    if (in_array($taxonomy->name, $exclude)) continue;

    /**
     * Smart visibility rules
     */
    if (is_search()) {

      if ($taxonomy->name === 'event_location' && !$is_event_selected) continue;
      if ($taxonomy->name === 'responsible_team' && !$is_work_update_selected) continue;

    } else {

      if ($taxonomy->name === 'event_location' && !is_post_type_archive('event')) continue;
      if ($taxonomy->name === 'responsible_team' && !is_post_type_archive('work_update')) continue;
    }

    $selected = isset($_GET[$taxonomy->name])
      ? array_map('sanitize_text_field', (array) $_GET[$taxonomy->name])
      : [];

    $parent_terms = get_terms([
      'taxonomy'   => $taxonomy->name,
      'hide_empty' => true,
      'parent'     => 0,
    ]);

    if (empty($parent_terms) || is_wp_error($parent_terms)) continue;

    $view_all_checked = empty($selected) ? 'checked' : '';

    $display_label = $label_overrides[$taxonomy->name] ?? $taxonomy->label;

    $section_id = 'filter-' . esc_attr($taxonomy->name);
    $heading_id = $section_id . '-heading';
    $content_id = $section_id . '-content';
  ?>

    <div 
      class="govuk-accordion gca-filter-card"
      data-module="govuk-accordion"
      data-testid="filter-group-<?php echo esc_attr($taxonomy->name); ?>"
    >

      <div class="govuk-accordion__section govuk-accordion__section--expanded">

        <!-- Header -->
        <div class="govuk-accordion__section-header">
          <h3 class="govuk-accordion__section-heading">
            <button
              type="button"
              class="govuk-accordion__section-button gca-filter-card__title"
              id="<?php echo $heading_id; ?>"
              aria-controls="<?php echo $content_id; ?>"
              aria-expanded="true"
            >
              <?php echo esc_html($display_label); ?>
            </button>
          </h3>
        </div>

        <!-- Content -->
        <div
          id="<?php echo $content_id; ?>"
          class="govuk-accordion__section-content gca-filter-card__content"
          aria-labelledby="<?php echo $heading_id; ?>"
        >

          <fieldset class="govuk-fieldset">
            <legend class="govuk-visually-hidden">
              <?php echo esc_html($display_label); ?>
            </legend>

            <div class="govuk-checkboxes" data-module="govuk-checkboxes">

              <!-- View all -->
              <div class="govuk-checkboxes__item govuk-checkboxes__item--small">
                <input
                  class="govuk-checkboxes__input govuk-checkboxes__input--small"
                  id="<?php echo $section_id; ?>-all"
                  type="checkbox"
                  <?php echo $view_all_checked; ?>
                  onclick="window.location.href='<?php echo esc_url($clear_url); ?>'"
                >
                <label class="govuk-label govuk-checkboxes__label" for="<?php echo $section_id; ?>-all">
                  View all
                </label>
              </div>

              <!-- Terms -->
              <?php
              if (function_exists('gca_render_term_tree')) {
                gca_render_term_tree($taxonomy->name, $parent_terms, $selected);
              } else {
                foreach ($parent_terms as $term) :
                  $checked = in_array($term->slug, $selected) ? 'checked' : '';
                  $input_id = $section_id . '-' . esc_attr($term->slug);
              ?>
                  <div class="govuk-checkboxes__item govuk-checkboxes__item--small">
                    <input
                      class="govuk-checkboxes__input govuk-checkboxes__input--small"
                      id="<?php echo $input_id; ?>"
                      name="<?php echo esc_attr($taxonomy->name); ?>[]"
                      type="checkbox"
                      value="<?php echo esc_attr($term->slug); ?>"
                      <?php echo $checked; ?>
                      onchange="this.form.submit();"
                    >
                    <label class="govuk-label govuk-checkboxes__label" for="<?php echo $input_id; ?>">
                      <?php echo esc_html($term->name); ?>
                    </label>
                  </div>
              <?php endforeach; } ?>

            </div>

          </fieldset>

        </div>

      </div>

    </div>

  <?php endforeach; ?>

</form>