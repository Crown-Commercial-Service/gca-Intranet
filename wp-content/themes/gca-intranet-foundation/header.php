<?php
/**
 * Header template - Pure GDS/CCS Responsive Structure
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
  <script type="text/javascript" src="https://api.useberry.com/integrations/liveUrl/scripts/useberryScript.js"></script>
</head>

<body <?php body_class('govuk-template__body govuk-frontend-supported'); ?>>
<?php wp_body_open(); ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P9TZ7BPX"
height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php get_template_part('template-parts/cookie-banner'); ?>

<header class="site-header" role="banner">
  <a href="#main-content" class="govuk-skip-link" data-module="govuk-skip-link">Skip to main content</a>

  <div class="bg-white gca-header-bg">
    <div class="ccs-width-container govuk-!-padding-top-3">

      <div class="gca-header-main-row">

        <div class="gca-header-logo-col">
          <div class="site-branding">
            <?php
              if (function_exists('the_custom_logo') && has_custom_logo()) {
                the_custom_logo();
              } else {
                $logo_rel = '/assets/img/Government-Commercial-Agency.svg';
                $logo_url = file_exists(get_stylesheet_directory() . $logo_rel)
                  ? get_stylesheet_directory_uri() . $logo_rel
                  : get_template_directory_uri() . $logo_rel;
                ?>
                <a class="custom-logo-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                  <img class="gca-header-logo" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
                </a>
                <?php
              }
            ?>
          </div>
        </div>

        <div class="gca-header-actions-col">

          <div class="gca-header-topbar-inner">

            <nav class="utility-nav" aria-label="Top bar navigation">
              <?php
                wp_nav_menu([
                  'theme_location' => 'top_bar',
                  'container'      => false,
                  'menu_class'     => 'utility-nav-list',
                  'fallback_cb'    => false,
                  'depth'          => 1,
                ]);
              ?>
            </nav>

            <?php if (!is_search()) : ?>
            <form class="site-search" role="search" action="<?php echo esc_url(get_theme_mod('gca_search_url', home_url('/'))); ?>" method="get">
              <label class="govuk-visually-hidden" for="site-search">Search the intranet</label>
              <div class="search-input-group">
                <input id="site-search" name="s" type="search" class="govuk-input" placeholder="Search the intranet" autocomplete="off">
                <button class="govuk-button search-submit" type="submit" aria-label="Search the intranet">
                  <span class="govuk-visually-hidden">Search</span>
                  <svg class="gca-search-icon" width="20" height="20" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M11.742 10.344l3.387 3.387-.998.998-3.387-3.387a6 6 0 1 1 .998-.998zM6.5 11.5a5 5 0 0 0 0-10 5 5 0 0 0 0 10z"></path>
                  </svg>
                </button>
              </div>
            </form>
            <?php endif; ?>

            <button class="global-navigation__toggler" type="button" aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
              Menu
            </button>

          </div>
        </div>

      </div>

    </div>
  </div>

  <div class="bg-white gca-header-bg">
    <div class="nav-wrapper govuk-width-container">
        <nav class="global-navigation" id="primaryNav" aria-label="Primary navigation">
        <?php
          $args = [
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'nav-list nav-list--primary',
            'fallback_cb'    => false,
            'item_spacing'   => 'discard',
            'depth'          => 2,
          ];

          if (class_exists('CCS_Mega_Menu_Walker')) {
            $args['walker'] = new CCS_Mega_Menu_Walker();
          }

          wp_nav_menu($args);
        ?>
        </nav>
      </div>
  </div>
</header>