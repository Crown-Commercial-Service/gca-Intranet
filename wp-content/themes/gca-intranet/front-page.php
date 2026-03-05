<?php
/**
 * Front page template
 */
get_header();
?>

<section class="gca-home-hero" aria-label="Homepage hero" data-testid="home-hero">
  <div class="govuk-width-container" data-testid="home-hero-container">
    <div class="gca-home-hero-inner" data-testid="home-hero-inner">
      <div class="gca-home-hero-content" data-testid="home-hero-content">
        <p class="govuk-body gca-home-hero-kicker" data-testid="home-hero-kicker">Welcome to our</p>
        <h1 class="govuk-body gca-home-hero-title" data-testid="home-hero-title">GCA Intranet</h1>
      </div>

      <div class="gca-home-hero-tagline" aria-hidden="true" data-testid="home-hero-tagline">
        <span class="gca-home-hero-tagline-rule" data-testid="home-hero-tagline-rule"></span>
        <span class="gca-home-hero-tagline-text" data-testid="home-hero-tagline-text">value for the nation</span>
      </div>
    </div>
  </div>
</section>

<div class="govuk-width-container" data-testid="home-width-container">
  <main class="govuk-main-wrapper" data-testid="home-main-wrapper">

    <div class="govuk-grid-row" data-testid="home-top-row">

      <!-- Latest news -->
      <div class="govuk-grid-column-two-thirds" data-testid="latest-news-column">
        <div class="gca-homepage-section-title" data-testid="latest-news-header">
          <h2 class="govuk-heading-m gca-clamp-2" data-testid="latest-news-heading">
            <?php
            $latestnews_title = trim((string) get_theme_mod('gca_latestnews_title', __('Latest news', 'gca-intranet')));
            echo esc_html($latestnews_title !== '' ? $latestnews_title : __('Latest news', 'gca-intranet'));
            ?>
          </h2>

          <?php
          $latestnews_desc = trim((string) get_theme_mod('gca_latestnews_desc', ''));
          ?>
          <p class="govuk-body" data-testid="latest-news-subheading">
            <?php echo esc_html($latestnews_desc !== '' ? $latestnews_desc : "What's happening in our organisation"); ?>
          </p>
        </div>

        <div class="govuk-grid-row gca-equal-height-row" data-testid="latest-news-section">
          <div class="govuk-grid-column-one-half" data-testid="latest-news-featured-col">
            <div class="gca-featured-news" data-testid="latest-news-featured-card">
              <?php
              $latest_post = new WP_Query(['post_type' => 'news', 'posts_per_page' => 1]);
              if ($latest_post->have_posts()):
                while ($latest_post->have_posts()):
                  $latest_post->the_post();
                  ?>
                  <?php if (has_post_thumbnail()): ?>
                    <img
                      data-testid="latest-news-featured-image"
                      src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>"
                      alt="<?php echo esc_attr(get_the_title()); ?>"
                    >
                  <?php endif; ?>

                  <div data-testid="latest-news-featured-content">
                    <h3 class="govuk-heading-m" data-testid="latest-news-featured-title">
                      <a class="govuk-link govuk-!-text-break-word" data-testid="latest-news-featured-link" href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                      </a>
                    </h3>

                    <p class="govuk-body-s" data-testid="latest-news-featured-excerpt">
                      <?php echo esc_html(wp_trim_words(get_the_excerpt(), 32, '...')); ?>
                    </p>

                    <p class="govuk-body-s" data-testid="latest-news-featured-date">
                      <?php echo esc_html(get_the_date('jS F Y')); ?>
                    </p>
                  </div>
                  <?php
                endwhile;
              endif;
              wp_reset_postdata();
              ?>
            </div>
          </div>

          <div class="govuk-grid-column-one-half gca-flex-box-news" data-testid="latest-news-secondary-col">
            <?php
            $secondary_posts = new WP_Query(['post_type' => 'news', 'posts_per_page' => 3, 'offset' => 1]);
            if ($secondary_posts->have_posts()):
              while ($secondary_posts->have_posts()):
                $secondary_posts->the_post();
                $is_first = ($secondary_posts->current_post === 0);
                $padding_top_class = $is_first ? 'gca-first-small-list-news' : 'gca-not_first-small-list-news';
                ?>
                <div class="gca-small-list-news <?php echo esc_attr($padding_top_class); ?>" data-testid="latest-news-secondary-card">
                  <div class="govuk-grid-row" data-testid="latest-news-secondary-row">
                    <div class="govuk-grid-column-one-third" data-testid="latest-news-secondary-image-col">
                      <?php if (has_post_thumbnail()): ?>
                        <img
                          data-testid="latest-news-secondary-image"
                          src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium')); ?>"
                          alt="<?php echo esc_attr(get_the_title()); ?>"
                        >
                      <?php endif; ?>
                    </div>

                    <div class="govuk-grid-column-two-third gca-flex-box-news" data-testid="latest-news-secondary-content">
                      <h3 class="govuk-heading-s govuk-!-margin-bottom-1" data-testid="latest-news-secondary-title">
                        <a class="govuk-link govuk-!-text-break-word" data-testid="latest-news-secondary-link" href="<?php the_permalink(); ?>">
                          <?php the_title(); ?>
                        </a>
                      </h3>

                      <p class="govuk-body-s" data-testid="latest-news-secondary-excerpt">
                        <?php echo esc_html(wp_trim_words(get_the_excerpt(), 12, '...')); ?>
                      </p>

                      <p class="govuk-body-s" data-testid="latest-news-secondary-date">
                        <?php echo esc_html(get_the_date('jS F Y')); ?>
                      </p>
                    </div>
                  </div>
                </div>
                <?php
              endwhile;
            endif;
            wp_reset_postdata();
            ?>
          </div>

          <div class="see-more-link-homepage" data-testid="latest-news-see-more">
            <svg data-testid="latest-news-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
              fill="currentColor" class="bi bi-chevron-right govuk-!-padding-top-1"
              viewBox="0 0 16 16" style="stroke: currentColor; stroke-width: 1.8;" aria-hidden="true" focusable="false">
              <path fill-rule="evenodd"
                d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
            </svg>

            <p data-testid="latest-news-see-more-text">
              <a class="govuk-link" data-testid="latest-news-see-more-link" href="/news/">
                Browse all news articles
              </a>
            </p>
          </div>
        </div>
      </div>

      <?php
      // ------------------------------------------------------------
      // Take a look (Customizer-driven) - GI-100
      // ------------------------------------------------------------
      $take_enabled = (bool) get_theme_mod('gca_takealook_enabled', true);

      $take_title = trim((string) get_theme_mod('gca_takealook_title', __('Take a look', 'gca-intranet')));
      $take_desc  = trim((string) get_theme_mod('gca_takealook_desc', ''));
      $take_text  = trim((string) get_theme_mod('gca_takealook_link_text', __('Learn more', 'gca-intranet')));
      $take_url   = trim((string) get_theme_mod('gca_takealook_link_url', ''));

      if ($take_title === '') {
        $take_title = __('Take a look', 'gca-intranet');
      }
      if ($take_text === '') {
        $take_text = __('Learn more', 'gca-intranet');
      }

      $take_href = ($take_url !== '') ? esc_url($take_url) : '';

      // ------------------------------------------------------------
      // Quick links (Customizer-driven) - GI-101
      // ------------------------------------------------------------
      $ql_enabled = (bool) get_theme_mod('gca_quicklinks_enabled', true);

      $ql_title = trim((string) get_theme_mod('gca_quicklinks_title', __('Quick links', 'gca-intranet')));
      $ql_desc  = trim((string) get_theme_mod('gca_quicklinks_desc', ''));

      $quick_links = [];
      for ($i = 1; $i <= 3; $i++) {
        $t = trim((string) get_theme_mod("gca_quicklinks_{$i}_text", ''));
        $u = trim((string) get_theme_mod("gca_quicklinks_{$i}_url", ''));

        if ($t !== '' && $u !== '') {
          $quick_links[] = [
            'text' => $t,
            'url'  => $u,
          ];
        }
      }
      ?>

      <?php if ($take_enabled) : ?>
        <!-- Right column -->
        <div class="govuk-grid-column-one-third" data-testid="take-a-look-column">

          <div class="gca-homepage-section-title" data-testid="take-a-look-header">
            <h2 class="govuk-heading-m gca-clamp-2" data-testid="take-a-look-heading"><?php echo esc_html($take_title); ?></h2>

            <?php if ($take_desc !== '') : ?>
              <p class="govuk-body" data-testid="take-a-look-subheading"><?php echo esc_html($take_desc); ?></p>
            <?php endif; ?>
          </div>

          <?php if ($take_href !== '') : ?>
            <!-- Single card: no outer wrapper, no inner content wrapper -->
            <a class="gca-take-a-look__link govuk-link"
              data-testid="take-a-look-link"
              href="<?php echo $take_href; ?>">

              <p class="govuk-body gca-take-a-look__text govuk-!-margin-bottom-0">
                <?php echo esc_html($take_text); ?>
              </p>

              <span class="gca-take-a-look__icon" aria-hidden="true">
                <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                  <path d="M32 16C32 12.8355 31.0616 9.74206 29.3035 7.11088C27.5454 4.47969 25.0466 2.42893 22.1229 1.21793C19.1993 0.00692534 15.9823 -0.309928 12.8786 0.307436C9.77486 0.924799 6.92393 2.44865 4.68629 4.68629C2.44865 6.92393 0.924799 9.77486 0.307435 12.8786C-0.309928 15.9823 0.00692538 19.1993 1.21793 22.1229C2.42893 25.0466 4.47969 27.5454 7.11088 29.3035C9.74206 31.0616 12.8355 32 16 32L16 16H32Z" fill="#9CAF27"/>
                  <path d="M22 22L31.3802 31.5833M31.3802 31.5833V22M31.3802 31.5833H22" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </a>

          <?php else : ?>
            <!-- Single card: no outer wrapper, no inner content wrapper -->
            <div class="gca-take-a-look__link"
              data-testid="take-a-look-link"
              aria-label="<?php echo esc_attr__('Take a look not configured', 'gca-intranet'); ?>">

              <p class="govuk-body gca-take-a-look__text govuk-!-margin-bottom-0">
                <?php echo esc_html($take_text); ?>
              </p>

              <span class="gca-take-a-look__icon" aria-hidden="true">
                <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                  <path d="M32 16C32 12.8355 31.0616 9.74206 29.3035 7.11088C27.5454 4.47969 25.0466 2.42893 22.1229 1.21793C19.1993 0.00692534 15.9823 -0.309928 12.8786 0.307436C9.77486 0.924799 6.92393 2.44865 4.68629 4.68629C2.44865 6.92393 0.924799 9.77486 0.307435 12.8786C-0.309928 15.9823 0.00692538 19.1993 1.21793 22.1229C2.42893 25.0466 4.47969 27.5454 7.11088 29.3035C9.74206 31.0616 12.8355 32 16 32L16 16H32Z" fill="#9CAF27"/>
                  <path d="M22 22L31.3802 31.5833M31.3802 31.5833V22M31.3802 31.5833H22" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>
          <?php endif; ?>

          <!-- GI-101: Quick links (ONLY show when enabled AND has 1+ links) -->
          <?php if ($ql_enabled && !empty($quick_links)) : ?>
            <div class="gca-quick-links" data-testid="quick-links">

              <div class="gca-homepage-section-title" data-testid="quick-links-header">
                <h2 class="govuk-heading-m gca-clamp-2" data-testid="quick-links-heading">
                  <?php echo esc_html($ql_title !== '' ? $ql_title : __('Quick links', 'gca-intranet')); ?>
                </h2>

                <?php if ($ql_desc !== '') : ?>
                  <p class="govuk-body" data-testid="quick-links-subheading">
                    <?php echo esc_html($ql_desc); ?>
                  </p>
                <?php endif; ?>
              </div>

              <div class="gca-quick-links__list" data-testid="quick-links-list">
                <?php foreach ($quick_links as $link) : ?>
                  <a class="gca-quick-links__item govuk-link"
                    href="<?php echo esc_url($link['url']); ?>"
                    data-testid="quick-links-item">
                    <span class="gca-quick-links__text"><?php echo esc_html($link['text']); ?></span>

                    <svg class="gca-quick-links__chevron"
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="22"
                      fill="currentColor"
                      viewBox="0 0 16 16"
                      style="stroke: currentColor; stroke-width: 1.8;"
                      aria-hidden="true"
                      focusable="false">
                      <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                    </svg>
                  </a>
                <?php endforeach; ?>
              </div>

            </div>
          <?php endif; ?>

        </div>
      <?php endif; ?>

    </div>

    <div class="govuk-width-container" data-testid="home-lower-width-container">
      <div class="govuk-grid-row flex" data-testid="home-lower-row">

        <!-- Work updates -->
        <div class="govuk-grid-column-two-thirds" data-testid="work-updates-column">
          <div class="gca-homepage-section-title" data-testid="work-updates-header">
            <h2 class="govuk-heading-m gca-clamp-2" data-testid="work-updates-heading">
              <?php
              $workupdates_title = trim((string) get_theme_mod('gca_workupdates_title', __('Work updates', 'gca-intranet')));
              echo esc_html($workupdates_title !== '' ? $workupdates_title : __('Work updates', 'gca-intranet'));
              ?>
            </h2>

            <?php
            $workupdates_desc = trim((string) get_theme_mod('gca_workupdates_desc', ''));
            ?>

            <p class="govuk-body" data-testid="work-updates-subheading">
              <?php echo esc_html($workupdates_desc !== '' ? $workupdates_desc : 'Highlights from across the organisation.'); ?>
            </p>
          </div>

          <div class="govuk-grid-row gca-equal-height-row" data-testid="work-updates-section">
            <?php
            $work_updates = new WP_Query([
              'post_type'      => 'work_update',
              'posts_per_page' => 2,
            ]);

            if ($work_updates->have_posts()):
              while ($work_updates->have_posts()):
                $work_updates->the_post();
                ?>
                <div class="govuk-grid-column-one-half gca-work-update-card" data-testid="work-update-card">
                  <div class="govuk-grid-row gca-work-updates" data-testid="work-update-row">
                    <div class="govuk-grid-column-one-third" data-testid="work-update-avatar">
                      <?php if ($avatar = get_avatar(get_the_author_meta('ID'))): ?>
                        <?php echo $avatar; ?>
                      <?php endif; ?>
                    </div>

                    <div class="govuk-grid-column-two-thirds" data-testid="work-update-content">
                      <h3 class="govuk-heading-s" data-testid="work-update-title">
                        <a class="govuk-link govuk-!-text-break-word" href="<?php the_permalink(); ?>" data-testid="work-update-link">
                          <?php
                            $title = get_the_title();
                            echo esc_html(mb_strlen($title) > 30 ? mb_substr($title, 0, 30) . '...' : $title);
                          ?>
                        </a>
                      </h3>

                      <p class="govuk-body-s" data-testid="work-update-author">
                        By 
                        <?php
                          $author_name = get_the_author();
                          echo esc_html(mb_strlen($author_name) > 20 ? mb_substr($author_name, 0, 20) . '...' : $author_name);
                        ?>
                      </p>

                      <p class="govuk-body-s" data-testid="work-update-date">
                        <?php echo esc_html(get_the_date('jS F Y')); ?>
                      </p>
                    </div>
                  </div>
                </div>
                <?php
              endwhile;
            endif;
            wp_reset_postdata();
            ?>

            <div class="see-more-link-homepage" data-testid="work-updates-see-more">
              <svg data-testid="work-updates-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
                fill="currentColor" class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16"
                style="stroke: currentColor; stroke-width: 1.8;" aria-hidden="true" focusable="false">
                <path fill-rule="evenodd"
                  d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
              </svg>

              <p data-testid="work-updates-see-more-text">
                <a class="govuk-link" data-testid="work-updates-see-more-link" href="/work_update/">
                  More work updates
                </a>
              </p>
            </div>
          </div>
        </div>

        <div data-testid="home-divider">
          <div class="vr" data-testid="home-divider-vr"></div>
        </div>

        <!-- Blogs -->
        <div class="govuk-grid-column-one-third" data-testid="blogs-column">
          <div class="gca-homepage-section-title" data-testid="blogs-header">
            <h2 class="govuk-heading-m gca-clamp-2" data-testid="blogs-heading">
              <?php
              $blogs_title = trim((string) get_theme_mod('gca_blogs_title', __('Blogs', 'gca-intranet')));
              echo esc_html($blogs_title !== '' ? $blogs_title : __('Blogs', 'gca-intranet'));
              ?>
            </h2>

            <?php
            $blogs_desc = trim((string) get_theme_mod('gca_blogs_desc', ''));
            ?>

            <p class="govuk-body" data-testid="blogs-subheading">
              <?php echo esc_html($blogs_desc !== '' ? $blogs_desc : 'Latest posts from colleagues.'); ?>
            </p>
          </div>

          <div class="govuk-grid-row" data-testid="blogs-section">
            <div class="govuk-grid-column-full gca-work-update-card gca-blogs-card" data-testid="blogs-card">
              <div class="govuk-grid-row gca-work-updates" data-testid="blogs-row">
                <?php
                $blogs = new WP_Query(['post_type' => 'blog', 'posts_per_page' => 1]);
                if ($blogs->have_posts()):
                  while ($blogs->have_posts()):
                    $blogs->the_post();
                    ?>
                    <div class="govuk-grid-column-one-third" data-testid="blogs-avatar">
                      <?php if ($avatar = get_avatar(get_the_author_meta('ID'))): ?>
                        <?php echo $avatar; ?>
                      <?php endif; ?>
                    </div>

                    <div class="govuk-grid-column-two-thirds" data-testid="blogs-content">
                      <h3 class="govuk-heading-s" data-testid="blogs-title">
                        <a class="govuk-link govuk-!-text-break-word" data-testid="blogs-link" href="<?php the_permalink(); ?>">
                          <?php
                            $title = get_the_title();
                            echo esc_html(mb_strlen($title) > 30 ? mb_substr($title, 0, 30) . '...' : $title);
                          ?>
                        </a>
                      </h3>

                      <p class="govuk-body-s" data-testid="blogs-author">
                        By
                        <?php
                          $author_name = get_the_author();
                          echo esc_html(mb_strlen($author_name) > 20 ? mb_substr($author_name, 0, 20) . '...' : $author_name);
                        ?>
                      </p>


                      <p class="govuk-body-s" data-testid="blogs-date">
                        <?php echo esc_html(get_the_date('jS F Y')); ?>
                      </p>
                    </div>
                    <?php
                  endwhile;
                endif;
                wp_reset_postdata();
                ?>
              </div>
            </div>
          </div>

          <div class="see-more-link-homepage" data-testid="blogs-see-more">
            <svg data-testid="blogs-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
              fill="currentColor" class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16"
              style="stroke: currentColor; stroke-width: 1.8;" aria-hidden="true" focusable="false">
              <path fill-rule="evenodd"
                d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
            </svg>

            <p data-testid="blogs-see-more-text">
              <a class="govuk-link" data-testid="blogs-see-more-link" href="/blog/">
                More blogs
              </a>
            </p>
          </div>

        </div>

      </div>
    </div>

    <?php
    $count_events = wp_count_posts('event')->publish;
    if ( $count_events ) : ?>
      
      <div data-testid="event-section">
        <div class="gca-homepage-section-title" data-testid="latest-events-header">
          <h2 class="govuk-heading-m" data-testid="latest-events-heading">Events</h2>
          <p class="govuk-body" data-testid="latest-events-subheading">Get involve with our events</p>
        </div>


        <div class="govuk-grid-row gca-equal-height-row event-entries" data-testid="events-updates-section">
          <?php
          $events = new WP_Query([
            'post_type'      => 'event',
            'posts_per_page' => 3,
          ]);

          if ($events->have_posts()):
            while ($events->have_posts()):
              $events->the_post();
              ?>
              <div class="govuk-grid-column-one-third gca-event-card" data-testid="events-card">
                <div class="gca-events" data-testid="events-row">
                    
                  <p class="govuk-body-s" data-testid="events-date"> <?php echo esc_html(get_the_date('jS F Y')); ?> </p>
                  <h3 class="govuk-heading-s" data-testid="events-title">
                    <a class="govuk-link govuk-!-text-break-word" href="<?php the_permalink(); ?>" data-testid="events-link">
                      <?php
                        $title = get_the_title();
                        echo esc_html(mb_strlen($title) > 60 ? mb_substr($title, 0, 60) . '...' : $title);
                      ?>
                    </a>
                  </h3>

                  <div class="gca-card-meta">
                      <?php 
                      $categories = get_the_category();
                      $locations = get_the_terms(get_the_ID(), 'event_location');

                      if ($categories && $categories[0]->name !== 'Uncategorized') : ?>
                          <span class="govuk-body-s tag_label">
                              <?php echo esc_html($categories[0]->name); ?>
                          </span>
                      <?php endif; 

                      if ($locations) : ?>
                          <span class="govuk-body-s tag_label grey">
                              <?php echo esc_html($locations[0]->name); ?>
                          </span>
                      <?php endif; ?>
                  </div>

                </div>
              </div>
              <?php
            endwhile;
          endif;
          wp_reset_postdata();
          ?>
        </div>

        <div class="see-more-link-homepage" data-testid="events-see-more">
          <svg data-testid="events-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="22"
            fill="currentColor" class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16"
            style="stroke: currentColor; stroke-width: 1.8;">
            <path fill-rule="evenodd"
              d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
          </svg>

          <p data-testid="events-see-more-text">
            <a class="govuk-link" data-testid="events-see-more-link" href="/event/">
              More events
            </a>
          </p>
        </div>
        
        
        
        
        
      </div>
      
    <?php endif; ?>
    
  </main>
</div>

<?php get_footer(); ?>