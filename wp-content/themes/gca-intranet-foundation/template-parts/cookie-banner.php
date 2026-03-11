<?php
/**
 * Cookie banner template part.
 *
 */

$title   = (string) get_option('gca_cookies_title',          'Cookies on GCA');
$content = (string) get_option('gca_cookies_content',        '');
$version = (string) get_option('gca_cookies_policy_version', '1.0');

$title   = $title   ?: 'Cookies on GCA';
$version = $version ?: '1.0';
?>
<div
  class="gca-cookie-banner"
  id="gca-cookie-banner"
  role="region"
  aria-label="<?php esc_attr_e('Cookies banner', 'gca-intranet'); ?>"
  data-cookies-version="<?php echo esc_attr($version); ?>"
  hidden
>
  <div class="gca-cookie-banner__inner govuk-width-container">

    <h2 class="govuk-heading-m gca-cookie-banner__title">
      <?php echo esc_html($title); ?>
    </h2>

    <?php if ($content): ?>
      <div class="gca-cookie-banner__content govuk-body">
        <?php echo wp_kses_post($content); ?>
      </div>
    <?php endif; ?>

    <button
      class="govuk-button gca-cookie-banner__btn"
      id="gca-cookie-banner-accept"
      type="button"
    >
      <?php esc_html_e('Confirm and close', 'gca-intranet'); ?>
    </button>

  </div>
</div>
