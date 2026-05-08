<?php
/**
 * Search filter sidebar.
 *
 * Expected $args:
 *   search_url           string   The search base URL (form action).
 *   search_query         string   The current search query string.
 *   flag_on              bool     Whether the staff-profiles flag is enabled.
 *   filter_types         array    Currently selected filter_post_type[] values.
 *   filter_content_types array    Currently selected filter_content_type[] values.
 */

$search_url           = $args['search_url']           ?? '/';
$search_query         = $args['search_query']          ?? '';
$flag_on              = $args['flag_on']               ?? false;
$filter_types         = $args['filter_types']          ?? [];
$filter_content_types = $args['filter_content_types']  ?? [];

$is_all = empty($filter_types) && empty($filter_content_types);

$post_type_options = [
    ['slug' => 'work_update', 'label' => 'Work Updates'],
    ['slug' => 'event',       'label' => 'Events'],
];

if ($flag_on) {
    $post_type_options[] = ['slug' => 'staff', 'label' => 'Staff'];
}

$content_type_terms = get_terms([
    'taxonomy'   => 'content_type',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);
?>

<div class="archive-filters" data-testid="search-filters">
  <div class="archive-filters__header">
    <h2 class="govuk-heading-m archive-filters__heading">Filter by type</h2>
    <?php if (!$is_all) : ?>
      <a href="<?php echo esc_url(add_query_arg('s', $search_query, $search_url)); ?>" class="govuk-link archive-filters__clear">Clear filters</a>
    <?php endif; ?>
  </div>

  <form
    method="get"
    action="<?php echo esc_url($search_url); ?>"
    data-search-filter-form
  >
    <input type="hidden" name="s" value="<?php echo esc_attr($search_query); ?>">

    <div class="archive-filters__section" data-filter-section>
      <div class="archive-filters__section-header">
        <h3 class="govuk-heading-s archive-filters__section-title">Content type</h3>
        <button type="button"
                class="archive-filters__toggle govuk-link"
                aria-expanded="true"
                data-toggle-section>
          <svg class="archive-filters__chevron" xmlns="http://www.w3.org/2000/svg"
               width="13" height="8" viewBox="0 0 13 8" aria-hidden="true" focusable="false">
            <path d="M1 1l5.5 5.5L12 1" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
          </svg>
          <span class="archive-filters__toggle-label">Hide</span>
        </button>
      </div>

      <div class="archive-filters__section-body" data-section-body>
        <div class="govuk-checkboxes govuk-checkboxes--small">

          <div class="govuk-checkboxes__item">
            <input class="govuk-checkboxes__input"
                   id="filter-view-all"
                   type="checkbox"
                   data-view-all="all"
                   <?php echo $is_all ? 'checked' : ''; ?>>
            <label class="govuk-label govuk-checkboxes__label"
                   for="filter-view-all">View all</label>
          </div>

          <?php foreach ($post_type_options as $type) : ?>
            <div class="govuk-checkboxes__item">
              <input class="govuk-checkboxes__input"
                     id="filter-post-type-<?php echo esc_attr($type['slug']); ?>"
                     name="filter_post_type[]"
                     type="checkbox"
                     value="<?php echo esc_attr($type['slug']); ?>"
                     data-filter-term="filter_post_type"
                     <?php echo in_array($type['slug'], $filter_types, true) ? 'checked' : ''; ?>>
              <label class="govuk-label govuk-checkboxes__label"
                     for="filter-post-type-<?php echo esc_attr($type['slug']); ?>">
                <?php echo esc_html($type['label']); ?>
              </label>
            </div>
          <?php endforeach; ?>

          <?php if (!empty($content_type_terms) && !is_wp_error($content_type_terms)) : ?>
            <?php foreach ($content_type_terms as $term) : ?>
              <div class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input"
                       id="filter-content-type-<?php echo esc_attr($term->slug); ?>"
                       name="filter_content_type[]"
                       type="checkbox"
                       value="<?php echo esc_attr($term->slug); ?>"
                       data-filter-term="filter_content_type"
                       <?php echo in_array($term->slug, $filter_content_types, true) ? 'checked' : ''; ?>>
                <label class="govuk-label govuk-checkboxes__label"
                       for="filter-content-type-<?php echo esc_attr($term->slug); ?>">
                  <?php echo esc_html($term->name); ?>
                </label>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </form>
</div>
