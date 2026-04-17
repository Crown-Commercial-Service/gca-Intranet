<?php
/**
 * Footer template
 * GOV.UK Design System structure (no Bootstrap layout)
 * - Legal / policy links (menu: footer_legal)
 * - Crest + Crown copyright
 */
?>

<?php if (is_singular(['page'])) : ?>
  <div class="gca-published-by-wrapper" role="region" aria-label="Page information">
    <div class="govuk-width-container">
      <?php
        $responsible_teams = get_the_terms(get_the_ID(), 'responsible_team');
        if ($responsible_teams && !is_wp_error($responsible_teams)) :
      ?>
        <div class="gca-responsible-team" data-testid="responsible-team">
          <?php foreach ($responsible_teams as $term) :
            $dc_popup_title    = get_field('dc_popup_title', $term);
            $dc_header_color   = get_field('dc_header_color', $term) ?: '#3d53de';
            $dc_category_label = get_field('dc_popup_category_label', $term);
            $dc_description    = get_field('dc_popup_description', $term);
            $dc_items          = get_field('dc_contact_items', $term) ?: [];
            $dc_footer_text    = get_field('dc_footer_link_text', $term);
            $dc_footer_url     = get_field('dc_footer_link_url', $term);
            $has_popup         = !empty($dc_popup_title) && gca_flag_enabled('directorate-contact');
            $widget_id         = wp_unique_id('dc-term-');
          ?>
            <div class="dc-widget<?php echo $has_popup ? '' : ' dc-widget--no-popup'; ?>">

              <button
                class="tag_label grey govuk-body-s dc-widget__trigger"
                data-testid="responsible-team-pill"
                type="button"
                <?php if ($has_popup) : ?>
                  aria-expanded="false"
                  aria-controls="<?php echo esc_attr($widget_id); ?>-popup"
                  aria-haspopup="dialog"
                <?php else : ?>
                  disabled
                <?php endif; ?>
              ><?php echo esc_html($term->name); ?></button>

              <?php if ($has_popup) : ?>
                <div
                  class="dc-widget__popup"
                  id="<?php echo esc_attr($widget_id); ?>-popup"
                  role="dialog"
                  aria-label="<?php echo esc_attr($dc_popup_title); ?>"
                  aria-hidden="true"
                >
                  <div class="dc-widget__popup-header" style="background-color:<?php echo esc_attr($dc_header_color); ?>;">
                    <?php if ($dc_category_label) : ?>
                      <span class="dc-widget__category-label"><?php echo esc_html($dc_category_label); ?></span>
                    <?php endif; ?>
                    <h2 class="dc-widget__title"><?php echo esc_html($dc_popup_title); ?></h2>
                  </div>
                  <div class="dc-widget__popup-body">
                    <?php if ($dc_description) : ?>
                      <p class="dc-widget__description"><?php echo esc_html($dc_description); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($dc_items)) : ?>
                      <ul class="dc-widget__items">
                        <?php foreach ($dc_items as $item) :
                          $icon_type  = $item['dc_contact_items_dc_icon_type']     ?? 'link';
                          $icon_color = $item['dc_contact_items_dc_icon_bg_color'] ?? '#3d53de';
                          $i_title    = $item['dc_contact_items_dc_item_title']    ?? '';
                          $i_subtitle = $item['dc_contact_items_dc_item_subtitle'] ?? '';
                          $i_link     = $item['dc_contact_items_dc_item_link']     ?? '';
                          $tag        = $i_link ? 'a' : 'div';
                          $link_attr  = $i_link ? 'href="' . esc_url($i_link) . '"' : '';
                        ?>
                          <li class="dc-widget__item">
                            <<?php echo $tag; ?> class="dc-widget__item-inner" <?php echo $link_attr; ?>>
                              <span class="dc-widget__icon" style="background-color:<?php echo esc_attr($icon_color); ?>;">
                                <?php echo dc_get_icon_svg($icon_type); ?>
                              </span>
                              <span class="dc-widget__item-text">
                                <?php if ($i_title) : ?>
                                  <strong class="dc-widget__item-title"><?php echo esc_html($i_title); ?></strong>
                                <?php endif; ?>
                                <?php if ($i_subtitle) : ?>
                                  <span class="dc-widget__item-subtitle"><?php echo esc_html($i_subtitle); ?></span>
                                <?php endif; ?>
                              </span>
                            </<?php echo $tag; ?>>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                    <?php if ($dc_footer_text && $dc_footer_url) : ?>
                      <a href="<?php echo esc_url($dc_footer_url); ?>" class="dc-widget__footer-link">
                        <?php echo esc_html($dc_footer_text); ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
                <script>
                (function () {
                  var w = document.getElementById('<?php echo esc_js($widget_id); ?>-popup').closest('.dc-widget');
                  var t = w.querySelector('.dc-widget__trigger');
                  var p = w.querySelector('.dc-widget__popup');
                  var closeTimer;

                  function open() {
                    clearTimeout(closeTimer);
                    w.classList.add('dc-widget--open');
                    t.setAttribute('aria-expanded', 'true');
                    p.setAttribute('aria-hidden', 'false');
                  }

                  function close() {
                    w.classList.remove('dc-widget--open');
                    t.setAttribute('aria-expanded', 'false');
                    p.setAttribute('aria-hidden', 'true');
                  }

                  function scheduleClose() {
                    closeTimer = setTimeout(close, 300);
                  }

                  // Hover: open immediately, close after short delay so mouse can reach the popup
                  w.addEventListener('mouseenter', open);
                  w.addEventListener('mouseleave', scheduleClose);

                  // Click toggles open/close
                  t.addEventListener('click', function () { w.classList.contains('dc-widget--open') ? close() : open(); });

                  // Keyboard: close only when focus leaves the widget entirely
                  w.addEventListener('focusout', function (e) { if (!w.contains(e.relatedTarget)) scheduleClose(); });

                  // Close on outside click or Escape
                  document.addEventListener('click', function (e) { if (!w.contains(e.target)) close(); });
                  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
                }());
                </script>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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