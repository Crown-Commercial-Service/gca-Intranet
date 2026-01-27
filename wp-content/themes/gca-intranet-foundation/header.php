<?php
/**
 * Header template
 * - Utility bar (logo, utility links, search)
 * - Primary navigation (main IA)
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

<header class="site-header border-bottom" role="banner">

  <!-- A) Utility bar -->
  <div class="bg-white">
    <div class="container-xxl py-3">
      <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">

        <!-- Logo -->
<!-- Logo -->
<div class="d-flex align-items-center">
  <a class="site-branding d-inline-flex align-items-center text-decoration-none"
     href="<?php echo esc_url(home_url('/')); ?>"
     aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">

    <img
      src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/CCS_BLACK_AW_logo.svg'); ?>"
      class="gca-header-logo"
      alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
      loading="eager"
      decoding="async">
	</a>
	</div>

        <!-- Utility links -->
        <nav class="utility-nav" aria-label="Utility navigation">
          <ul class="nav gap-3">
            <li class="nav-item">
              <a class="nav-link px-0 py-0 text-decoration-underline" href="<?php echo esc_url(get_theme_mod('gca_definition_finder_url', '#')); ?>">
                Definition finder
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link px-0 py-0 text-decoration-underline" href="<?php echo esc_url(get_theme_mod('gca_staff_directory_url', '#')); ?>">
                Staff directory
              </a>
            </li>
          </ul>
        </nav>

        <!-- Global search -->
        <form class="d-flex align-items-center ms-auto" role="search"
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
              <!-- Bootstrap icon optional; replace with your SVG -->
              <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <path d="M11.742 10.344l3.387 3.387-.998.998-3.387-3.387a6 6 0 1 1 .998-.998zM6.5 11.5a5 5 0 1 0 0-10 5 5 0 0 0 0 10z"></path>
              </svg>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- B) Primary navigation bar -->
  <nav class="navbar navbar-expand-lg bg-white border-top" aria-label="Primary navigation">
    <div class="container-xxl">

      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse" data-bs-target="#primaryNav"
              aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="primaryNav" class="collapse navbar-collapse">
        <?php
          /**
           * Bootstrap 5 dropdown markup requires specific classes/attributes.
           * Use a Bootstrap nav walker (recommended) OR keep depth=1 (no dropdowns) for MVP.
           *
           * If you keep depth=2 dropdowns, set 'walker' to a BS5 walker.
           */
          wp_nav_menu([
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'navbar-nav me-auto mb-2 mb-lg-0 gap-lg-3',
			'fallback_cb'    => false,
			'depth'          => 2,
			'walker'         => new GCA_Bootstrap_5_Navwalker(),
			'item_spacing'   => 'discard',
			]);
        ?>
      </div>

    </div>
  </nav>

</header>

<!-- Page content starts -->
<main id="main-content" class="site-main" role="main">
