<?php
/**
 * Template Name: Staff Directory
 *
 * Two-panel layout: directorate/team sidebar on the left, staff grid on the right.
 * Driven by user meta synced from Workday (directorate, team, business_title, manager).
 */

declare(strict_types=1);

// Pre-render directorates from the DB so the sidebar is populated on first load
// without requiring JavaScript, then JS takes over for all subsequent interactions.
$sd_directorates = [];
if (function_exists('gca_flag_enabled') && gca_flag_enabled('staff-directory')) {
    global $wpdb;
    $sd_directorates = $wpdb->get_col(
        "SELECT DISTINCT meta_value
         FROM {$wpdb->usermeta}
         WHERE meta_key = 'directorate'
           AND meta_value != ''
         ORDER BY meta_value ASC"
    );
}

get_header();

if (!gca_flag_enabled('staff-directory')) :
?>
<section class="gca-hero-banner" aria-label="Page banner">
  <div class="govuk-width-container">
    <div class="gca-hero-banner__inner">
      <h1 class="govuk-heading-xl gca-hero-banner__title"><?php the_title(); ?></h1>
    </div>
  </div>
</section>
<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-9">
  <main id="main-content" tabindex="-1">
    <div class="govuk-notification-banner govuk-notification-banner--important" role="region" aria-labelledby="govuk-notification-banner-title" data-module="govuk-notification-banner">
      <div class="govuk-notification-banner__header">
        <h2 class="govuk-notification-banner__title" id="govuk-notification-banner-title">Feature not enabled</h2>
      </div>
      <div class="govuk-notification-banner__content">
        <p class="govuk-body">The Staff Directory is not currently available.<?php if (current_user_can('manage_options')) : ?> To enable it,  please contact your administrator.<?php endif; ?></p>
      </div>
    </div>
  </main>
</div>
<?php
get_footer();
exit;
endif;
?>

<section class="gca-hero-banner" aria-label="Page banner">
  <div class="govuk-width-container">
    <div class="gca-hero-banner__inner">
      <h1 class="govuk-heading-xl gca-hero-banner__title">
        <?php the_title(); ?>
      </h1>
    </div>
  </div>
</section>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-9">
  <main id="main-content" tabindex="-1">

    <div class="sd-directory">
      <div class="sd-directory__layout">

        <!-- ─────────────────────────────────────────────────────── Sidebar -->
        <nav class="sd-directory__sidebar" aria-label="Browse staff directory">

          <!-- Search by name -->
          <div class="sd-directory__nav-section sd-directory__nav-section--search">
            <div class="sd-directory__search-box">
              <label class="govuk-label" for="sd-search-input">Search by name</label>
              <div class="sd-directory__search-field">
                <input
                  class="govuk-input sd-directory__search-input"
                  type="search"
                  id="sd-search-input"
                  name="q"
                  autocomplete="off"
                  placeholder="e.g. Jane Smith"
                >
                <button
                  class="sd-directory__search-btn"
                  type="button"
                  id="sd-search-btn"
                  aria-label="Search staff by name"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                       viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21l-4.35-4.35" stroke="currentColor"
                          stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- 1. Directorates -->
          <div class="sd-directory__nav-section">
            <label class="sd-directory__nav-label" for="sd-directorate-select">
              1. Select Directorate
            </label>

            <?php if (empty($sd_directorates)) : ?>
              <p class="govuk-body-s">No directorates found.</p>
            <?php else : ?>
              <select
                class="govuk-select sd-directory__directorate-select"
                id="sd-directorate-select"
              >
                <option value="">— Choose a directorate —</option>
                <?php foreach ($sd_directorates as $d_name) : ?>
                  <option value="<?php echo esc_attr($d_name); ?>">
                    <?php echo esc_html($d_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <!-- 2. Teams (populated by JS after a directorate is selected) -->
          <div class="sd-directory__nav-section sd-directory__nav-section--teams"
               id="sd-teams-section" hidden>
            <p class="sd-directory__nav-label">2. Select Team</p>
            <ul class="sd-directory__nav-list sd-directory__nav-list--teams"
                id="sd-teams-list" role="list">
            </ul>
          </div>

        </nav>
        <!-- /.sd-directory__sidebar -->


        <!-- ────────────────────────────────────────────────── Main content -->
        <div class="sd-directory__main">

          <!-- Panel meta: breadcrumb/title -->
          <div class="sd-directory__panel-meta" id="sd-panel-meta" hidden>
            <p class="sd-directory__breadcrumb govuk-body-s" id="sd-breadcrumb"></p>
            <h1 class="sd-directory__panel-title govuk-heading-l" id="sd-panel-title"></h1>
            <p class="govuk-body-s" id="sd-staff-count"></p>
          </div>

          <!-- Loading indicator -->
          <p class="sd-directory__loading govuk-body-s" id="sd-loading" hidden
             aria-live="polite" aria-label="Loading">Loading&hellip;</p>

          <!-- Default CMS content — shown until a team or search is active -->
          <div id="sd-default-content">
            <p class="govuk-body">Select a directorate or search by name to see results</p>
          </div>

          <!-- Staff grid — shown when a team is selected -->
          <div id="sd-staff-panel" hidden>
            <div class="sd-directory__staff-grid"
                 id="sd-staff-grid"
                 aria-live="polite"
                 aria-label="Team members">
            </div>
          </div>

          <!-- Search results — shown during a name search -->
          <div id="sd-search-panel" hidden>
            <h2 class="govuk-heading-m" id="sd-search-title"></h2>
            <div class="sd-directory__staff-grid"
                 id="sd-search-grid"
                 aria-live="polite">
            </div>
          </div>

        </div>
        <!-- /.sd-directory__main -->

      </div>
      <!-- /.sd-directory__layout -->
    </div>
    <!-- /.sd-directory -->

  </main>
</div>

<?php get_footer(); ?>
