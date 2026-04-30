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
          // Pre-collect all term data so we can render pills and panels in separate passes
          $dc_terms = [];
          foreach ($responsible_teams as $term) {
            $popup_title = get_field('dc_popup_title', $term);
            $dc_terms[] = [
              'term'             => $term,
              'popup_title'      => $popup_title,
              'header_icon_type' => get_field('dc_header_icon_type', $term) ?: 'group',
              'header_color'     => get_field('dc_header_color', $term) ?: '#6b7280',
              'description'      => get_field('dc_popup_description', $term),
              'items'            => get_field('dc_contact_items', $term) ?: [],
              'footer_text'      => get_field('dc_footer_link_text', $term),
              'footer_url'       => get_field('dc_footer_link_url', $term),
              'has_panel'        => !empty($popup_title) && gca_flag_enabled('directorate-contact'),
              'widget_id'        => wp_unique_id('dc-term-'),
            ];
          }
      ?>
        <div class="gca-responsible-team" data-testid="responsible-team">
          <?php foreach ($dc_terms as $td) : ?>
            <div class="dc-widget<?php echo $td['has_panel'] ? '' : ' dc-widget--no-popup'; ?>">
              <button
                class="tag_label grey govuk-body-s dc-widget__trigger"
                data-testid="responsible-team-pill"
                type="button"
                <?php if ($td['has_panel']) : ?>
                  aria-expanded="false"
                  aria-controls="<?php echo esc_attr($td['widget_id']); ?>-panel"
                <?php else : ?>
                  disabled
                <?php endif; ?>
              ><svg class="dc-widget__trigger-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?php echo esc_attr(dc_get_icon_path_d($td['header_icon_type'])); ?>"/></svg><?php echo esc_html($td['term']->name); ?><?php if ($td['has_panel']) : ?><svg class="dc-widget__chevron" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><?php endif; ?></button>
            </div>
          <?php endforeach; ?>
        </div>

        <?php foreach ($dc_terms as $td) : if ($td['has_panel']) : ?>
          <div
            class="dc-widget__panel"
            id="<?php echo esc_attr($td['widget_id']); ?>-panel"
            role="region"
            aria-label="<?php echo esc_attr($td['popup_title']); ?>"
            aria-hidden="true"
          >
            <div class="dc-widget__panel-inner">
              <div class="dc-widget__panel-header">
                <span class="dc-widget__header-icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?php echo esc_attr(dc_get_icon_path_d($td['header_icon_type'])); ?>"/></svg>
                </span>
                <h3 class="dc-widget__title"><?php echo esc_html($td['popup_title']); ?></h3>
              </div>
              <div class="dc-widget__panel-body">
                <?php if ($td['description']) : ?>
                  <p class="dc-widget__description"><?php echo esc_html($td['description']); ?></p>
                <?php endif; ?>
                <?php if (!empty($td['items'])) : ?>
                  <ul class="dc-widget__items">
                    <?php foreach ($td['items'] as $item) :
                      $icon_type  = $item['dc_contact_items_dc_icon_type']     ?? 'link';
                      $i_title    = $item['dc_contact_items_dc_item_title']    ?? '';
                      $i_subtitle = $item['dc_contact_items_dc_item_subtitle'] ?? '';
                      $i_link     = $item['dc_contact_items_dc_item_link']     ?? '';
                      $tag        = $i_link ? 'a' : 'div';
                      $link_attr  = $i_link ? 'href="' . esc_url($i_link) . '"' : '';
                    ?>
                      <li class="dc-widget__item">
                        <<?php echo $tag; ?> class="dc-widget__item-inner" <?php echo $link_attr; ?>>
                          <span class="dc-widget__icon">
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
                <?php if ($td['footer_text'] && $td['footer_url']) : ?>
                  <a href="<?php echo esc_url($td['footer_url']); ?>" class="dc-widget__footer-link">
                    <?php echo esc_html($td['footer_text']); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <script>
          (function () {
            var p = document.getElementById('<?php echo esc_js($td['widget_id']); ?>-panel');
            var t = document.querySelector('[aria-controls="<?php echo esc_js($td['widget_id']); ?>-panel"]');

            function open() {
              t.setAttribute('aria-expanded', 'true');
              p.setAttribute('aria-hidden', 'false');
            }

            function close() {
              t.setAttribute('aria-expanded', 'false');
              p.setAttribute('aria-hidden', 'true');
            }

            // Click only — no hover
            t.addEventListener('click', function () {
              t.getAttribute('aria-expanded') === 'true' ? close() : open();
            });

            // Close on outside click or Escape
            document.addEventListener('click', function (e) {
              if (!p.contains(e.target) && !t.contains(e.target)) close();
            });

            document.addEventListener('keydown', function (e) {
              if (e.key === 'Escape') close();
            });
          }());
          </script>
        <?php endif; endforeach; ?>

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