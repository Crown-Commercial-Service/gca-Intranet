<?php
/**
 * Footer template
 * - Legal / policy links
 * - Crown mark + copyright
 */
?>

</main><!-- #main-content -->

<footer class="site-footer mt-auto" role="contentinfo">
  <div class="container-xxl py-4">
    <div class="gca-footer-row">

      <!-- Legal links -->
      <nav class="footer-legal-links" aria-label="Footer legal links">
        <?php
          if (has_nav_menu('footer_legal')) {
            wp_nav_menu([
              'theme_location' => 'footer_legal',
              'container'      => false,
              'menu_class'     => 'footer-legal-nav',
              'depth'          => 1,
              'fallback_cb'    => false,
              'item_spacing'   => 'discard',
            ]);
          } else {
            $links = [
              ['label' => 'Accessibility statement', 'url' => get_theme_mod('gca_accessibility_url', '#')],
              ['label' => 'Cookie settings',         'url' => get_theme_mod('gca_cookies_url', '#')],
              ['label' => 'Privacy notice',          'url' => get_theme_mod('gca_privacy_url', '#')],
              ['label' => 'Cabinet Office intranet', 'url' => get_theme_mod('gca_co_intranet_url', '#')],
              ['label' => 'GCA website',             'url' => get_theme_mod('gca_gca_website_url', '#')],
            ];

            echo '<ul class="footer-legal-nav">';
            foreach ($links as $l) {
              printf(
                '<li class="menu-item"><a href="%s">%s</a></li>',
                esc_url($l['url']),
                esc_html($l['label'])
              );
            }
            echo '</ul>';
          }
        ?>
      </nav>

      <!-- Crown brand area -->
      <div class="gca-footer-brand">
        <img
          src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/govuk-crest.svg'); ?>"
          class="gca-footer-crest"
          alt=""
          aria-hidden="true"
          loading="lazy"
          decoding="async"
        >
        <div class="small">
          <a class="footer-crown-link text-decoration-underline" href="#">
            &copy; Crown copyright
          </a>
        </div>
      </div>

    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>