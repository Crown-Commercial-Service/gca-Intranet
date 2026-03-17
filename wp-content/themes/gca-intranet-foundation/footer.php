<?php
/**
 * Footer template
 * GOV.UK Design System structure (no Bootstrap layout)
 * - Legal / policy links (menu: footer_legal)
 * - Crest + Crown copyright
 */
?>

</main><!-- #main-content -->

<?php if (is_singular(['page'])) : ?>
  <div class="gca-published-by-wrapper" role="region" aria-label="Page information">
    <div class="govuk-width-container">
      <?php get_template_part('template-parts/published-by'); ?>
    </div>
  </div>
<?php endif; ?>

<footer class="govuk-footer site-footer" role="contentinfo">
  <div class="govuk-width-container">

    <div class="govuk-footer__meta gca-footer-row">

      <!-- Links -->
      <div class="govuk-footer__meta-item govuk-footer__meta-item--grow">
        <h2 class="govuk-visually-hidden">Support links</h2>

        <?php
          // Helper to render links as GOV.UK footer inline list
          $render_links = function(array $links) {
            echo '<ul class="govuk-footer__inline-list footer-legal-nav">';
            foreach ($links as $l) {
              printf(
                '<li class="govuk-footer__inline-list-item"><a class="govuk-footer__link" href="%s">%s</a></li>',
                esc_url($l['url']),
                esc_html($l['label'])
              );
            }
            echo '</ul>';
          };

          if (has_nav_menu('footer_legal')) {
            // Pull menu items and output them with GOV.UK markup (no walker needed)
            $items = wp_get_nav_menu_items(get_nav_menu_locations()['footer_legal'] ?? 0);

            if (!empty($items)) {
              $links = [];
              foreach ($items as $it) {
                if ((int) $it->menu_item_parent !== 0) continue; // depth 1 only
                $links[] = ['label' => $it->title, 'url' => $it->url];
              }
              $render_links($links);
            }

          } else {
            $render_links([
              ['label' => 'Accessibility statement', 'url' => get_theme_mod('gca_accessibility_url', '#')],
              ['label' => 'Cookie settings',         'url' => get_theme_mod('gca_cookies_url', '#')],
              ['label' => 'Privacy notice',          'url' => get_theme_mod('gca_privacy_url', '#')],
              ['label' => 'Cabinet Office intranet', 'url' => get_theme_mod('gca_co_intranet_url', '#')],
              ['label' => 'GCA website',             'url' => get_theme_mod('gca_gca_website_url', '#')],
            ]);
          }
        ?>
      </div>

      <!-- Crest + copyright -->
      <div class="govuk-footer__meta-item gca-footer-brand">
        <img
          src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/govuk-crest.svg'); ?>"
          class="gca-footer-crest"
          alt=""
          aria-hidden="true"
          loading="lazy"
          decoding="async"
        >
        <div class="small">
          <a
            class="govuk-footer__link footer-crown-link"
            href="https://www.nationalarchives.gov.uk/information-management/re-using-public-sector-information/uk-government-licensing-framework/crown-copyright/">
            © Crown copyright
          </a>
        </div>
      </div>

    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>