<?php
/**
 * Header template
 * Desktop:
 *  - Logo sits visually to the LEFT of the boxed container content
 *  - Boxed content is 2 equal-height rows: (top) utility+search, (bottom) nav
 * Mobile:
 *  - Row 1: logo left | utility right
 *  - Row 2: search left | toggle right
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="visually-hidden-focusable skip-link" href="#main-content">
  Skip to main content
</a>

<header class="site-header" role="banner">
  <div class="bg-white gca-header-bg">
    <div class="container-xxl pt-3">
      <div class="gca-header-shell">

        <!-- Top row -->
        <div class="gca-header-topbar">

          <!-- Logo (positioned outside the boxed layout on desktop via CSS) -->
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
                  <a class="custom-logo-link"
                     href="<?php echo esc_url(home_url('/')); ?>"
                     rel="home"
                     aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <img
                      class="gca-header-logo"
                      src="<?php echo esc_url($logo_url); ?>"
                      alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                    />
                  </a>
                  <?php
                }
              ?>
            </div>
          </div>

          <nav class="utility-nav" aria-label="Utility navigation">
            <ul class="nav">
              <li class="nav-item">
                <a class="nav-link" href="<?php echo esc_url(get_theme_mod('gca_definition_finder_url', '#')); ?>">
                  Definition finder
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?php echo esc_url(get_theme_mod('gca_staff_directory_url', '#')); ?>">
                  Staff directory
                </a>
              </li>
            </ul>
          </nav>

          <form class="site-search" role="search"
                action="<?php echo esc_url(get_theme_mod('gca_search_url', home_url('/'))); ?>"
                method="get">
            <label class="visually-hidden" for="site-search">Search the intranet</label>
            <div class="input-group">
              <input id="site-search" name="s" type="search"
                     class="form-control"
                     placeholder="Search the intranet"
                     autocomplete="off">
              <button class="btn btn-primary" type="submit" aria-label="Search">
                <span class="visually-hidden">Search</span>
                <svg class="gca-search-icon" width="18" height="18" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                  <path fill="currentColor" d="M11.742 10.344l3.387 3.387-.998.998-3.387-3.387a6 6 0 1 1 .998-.998zM6.5 11.5a5 5 0 0 0 0-10 5 5 0 0 0 0 10z"></path>
                </svg>
              </button>
            </div>
          </form>

          <!-- Hamburger (mobile only) -->
          <button class="navbar-toggler gca-header-toggler" type="button"
                  data-bs-toggle="collapse" data-bs-target="#primaryNav"
                  aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" aria-hidden="true"></span>
          </button>

        </div>

        <!-- Bottom row: Primary nav -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white gca-header-nav" aria-label="Primary navigation">
          <div id="primaryNav" class="collapse navbar-collapse">
            <?php
              $args = [
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'navbar-nav me-auto mb-2 mb-lg-0',
                'fallback_cb'    => false,
                'item_spacing'   => 'discard',
                'depth'          => 2,
              ];

              if (class_exists('GCA_Bootstrap_5_Navwalker')) {
                $args['walker'] = new GCA_Bootstrap_5_Navwalker();
              } else {
                $args['depth'] = 1;
              }

              wp_nav_menu($args);
            ?>
          </div>
        </nav>

      </div>
    </div>
  </div>
</header>