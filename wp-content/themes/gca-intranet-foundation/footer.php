<?php
/**
 * Footer template
 * - Legal / policy links
 * - Crown mark + copyright
 */
?>

</main><!-- #main-content -->

<footer class="site-footer mt-auto" role="contentinfo">

  <div class="border-top" style="background: #e9efc5;">
    <div class="container-xxl py-4">

      <div class="d-flex align-items-center justify-content-between gap-4 flex-wrap">

        <!-- Legal links -->
        <nav aria-label="Footer legal links">
          <?php
            // Option A: footer menu (recommended)
            if (has_nav_menu('footer_legal')) {
              wp_nav_menu([
                'theme_location' => 'footer_legal',
                'container'      => false,
                'menu_class'     => 'nav gap-3',
                'depth'          => 1,
                'fallback_cb'    => false,
                'item_spacing'   => 'discard',
              ]);
            } else {
              // Option B: fallback to theme mods
              $links = [
                ['label' => 'Accessibility statement', 'url' => get_theme_mod('gca_accessibility_url', '#')],
                ['label' => 'Cookie settings',         'url' => get_theme_mod('gca_cookies_url', '#')],
                ['label' => 'Privacy notice',          'url' => get_theme_mod('gca_privacy_url', '#')],
                ['label' => 'Cabinet Office intranet', 'url' => get_theme_mod('gca_co_intranet_url', '#')],
                ['label' => 'GCA website',             'url' => get_theme_mod('gca_gca_website_url', '#')],
              ];
              echo '<ul class="nav gap-3">';
              foreach ($links as $l) {
                printf(
                  '<li class="nav-item"><a class="nav-link px-0 py-0 text-decoration-underline" href="%s">%s</a></li>',
                  esc_url($l['url']),
                  esc_html($l['label'])
                );
              }
              echo '</ul>';
            }
          ?>
        </nav>

		<!-- Crown brand area -->
		<div class="gca-footer-brand d-flex align-items-center gap-3 ms-lg-auto">
		<img
			src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/govuk-crest.svg'); ?>"
			class="gca-footer-crest"
			alt=""
			aria-hidden="true"
			loading="lazy"
			decoding="async"
			>
		<div class="small">
			<a class="text-decoration-underline" href="#">
			&copy; Crown copyright
			</a>
		</div>
		</div>

      </div>

    </div>
  </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
