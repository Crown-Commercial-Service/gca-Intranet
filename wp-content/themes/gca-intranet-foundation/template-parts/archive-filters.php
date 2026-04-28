<?php
/**
 * Archive filter sidebar.
 *
 * Expected $args:
 *   archive_url  string   The archive base URL (form action).
 *   taxonomies   array    Each entry: ['taxonomy' => string, 'label' => string, 'param' => string]
 */

$archive_url = $args['archive_url'] ?? '/';
$taxonomies  = $args['taxonomies']  ?? [];

$current_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';
?>

<div class="archive-filters" data-testid="archive-filters">
  <h2 class="govuk-heading-m archive-filters__heading">Apply filters</h2>

  <form method="get" action="<?php echo esc_url($archive_url); ?>" data-archive-filter-form>

    <?php /* ── Sort by ── */ ?>
    <div class="archive-filters__section">
      <h3 class="govuk-heading-s archive-filters__section-title">Sort by</h3>
      <div class="archive-filters__section-body">
        <div class="govuk-radios govuk-radios--small">
          <div class="govuk-radios__item">
            <input class="govuk-radios__input" id="sort-newest" name="sort" type="radio" value="newest"
                   <?php checked($current_sort, 'newest'); ?>>
            <label class="govuk-label govuk-radios__label" for="sort-newest">Newest First</label>
          </div>
          <div class="govuk-radios__item">
            <input class="govuk-radios__input" id="sort-oldest" name="sort" type="radio" value="oldest"
                   <?php checked($current_sort, 'oldest'); ?>>
            <label class="govuk-label govuk-radios__label" for="sort-oldest">Oldest First</label>
          </div>
        </div>
      </div>
    </div>

    <?php /* ── Taxonomy filter sections ── */ ?>
    <?php foreach ($taxonomies as $tax) :
      $terms = get_terms(['taxonomy' => $tax['taxonomy'], 'hide_empty' => true]);
      if (empty($terms) || is_wp_error($terms)) {
        continue;
      }

      $param    = $tax['param'];
      $selected = (isset($_GET[$param]) && is_array($_GET[$param]))
        ? array_map('sanitize_text_field', $_GET[$param])
        : [];
      $is_all   = empty($selected);
    ?>
      <div class="archive-filters__section" data-filter-section>
        <div class="archive-filters__section-header">
          <h3 class="govuk-heading-s archive-filters__section-title"><?php echo esc_html($tax['label']); ?></h3>
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
                     id="<?php echo esc_attr($param); ?>-view-all"
                     type="checkbox"
                     data-view-all="<?php echo esc_attr($param); ?>"
                     <?php echo $is_all ? 'checked' : ''; ?>>
              <label class="govuk-label govuk-checkboxes__label"
                     for="<?php echo esc_attr($param); ?>-view-all">View all</label>
            </div>

            <?php foreach ($terms as $term) : ?>
              <div class="govuk-checkboxes__item">
                <input class="govuk-checkboxes__input"
                       id="<?php echo esc_attr($param . '-' . $term->slug); ?>"
                       name="<?php echo esc_attr($param); ?>[]"
                       type="checkbox"
                       value="<?php echo esc_attr($term->slug); ?>"
                       data-filter-term="<?php echo esc_attr($param); ?>"
                       <?php echo in_array($term->slug, $selected, true) ? 'checked' : ''; ?>>
                <label class="govuk-label govuk-checkboxes__label"
                       for="<?php echo esc_attr($param . '-' . $term->slug); ?>">
                  <?php echo esc_html($term->name); ?>
                </label>
              </div>
            <?php endforeach; ?>

          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="govuk-button govuk-!-margin-top-4 govuk-!-margin-bottom-0">
      Apply filters
    </button>

  </form>
</div>
