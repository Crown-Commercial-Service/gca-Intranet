<?php
/**
 * Front Page (GI-100) - GOV.UK layout
 * - No WP page title output
 * - Hero section + 3-column placeholder
 */

get_header();
?>

<main id="main-content" class="site-main" role="main">

  <!-- HERO -->
  <section class="gca-home-hero" aria-label="Homepage hero">
    <div class="govuk-width-container">
      <div class="gca-home-hero-inner">

        <div class="gca-home-hero-content">
          <p class="gca-home-hero-kicker govuk-body govuk-!-margin-bottom-2">Welcome to our</p>
          <h1 class="gca-home-hero-title govuk-heading-xl govuk-!-margin-bottom-0">GCA Intranet</h1>
        </div>

        <div class="gca-home-hero-tagline" aria-hidden="true">
          <span class="gca-home-hero-tagline-rule"></span>
          <span class="gca-home-hero-tagline-text">value for the nation</span>
        </div>

      </div>
    </div>
  </section>

  <!-- 3 COLUMNS (placeholder) -->
  <section class="gca-home-grid">
    <div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-6">
      <div class="govuk-grid-row">

        <div class="govuk-grid-column-one-third">
          <section class="gca-home-card" aria-label="Latest news">
            <h2 class="gca-home-card-title govuk-heading-m">Latest news</h2>
            <p class="govuk-body">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <p class="govuk-body govuk-!-margin-bottom-0">Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
          </section>
        </div>

        <div class="govuk-grid-column-one-third">
          <section class="gca-home-card" aria-label="Work updates">
            <h2 class="gca-home-card-title govuk-heading-m">Work updates</h2>
            <p class="govuk-body">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <p class="govuk-body govuk-!-margin-bottom-0">Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
          </section>
        </div>

        <div class="govuk-grid-column-one-third">
          <section class="gca-home-card" aria-label="Quick links">
            <h2 class="gca-home-card-title govuk-heading-m">Quick links</h2>
            <p class="govuk-body">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>
            <p class="govuk-body govuk-!-margin-bottom-0">Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum.</p>
          </section>
        </div>

      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
