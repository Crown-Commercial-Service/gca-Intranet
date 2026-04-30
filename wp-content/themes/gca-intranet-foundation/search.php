<?php
get_header();

global $wp_query;
$search_query = get_search_query();
$search_url   = get_theme_mod('gca_search_url', home_url('/'));

$flag_on = function_exists('gca_flag_enabled')
    && gca_flag_enabled('staff-profiles')
    && function_exists('gca_search_staff');

if ($flag_on) {
    // -------------------------------------------------------------------------
    // Merged mode: staff + posts in one paginated list.
    //
    // pre_get_posts (priority 20 in staff-profiles.php) already set
    // posts_per_page = -1, so $wp_query->posts contains ALL matching posts.
    // We merge them with staff results, paginate manually, and override
    // $wp_query->max_num_pages so the_posts_pagination() generates correct links.
    // -------------------------------------------------------------------------

    $per_page     = 10;
    $current_page = max(1, (int) (get_query_var('paged') ?: 1));

    $staff_results = gca_search_staff($search_query, 50);
    $all_posts     = $wp_query->posts ?: [];

    // Relevance scoring:
    //   100 — title / display name is an exact match (case-insensitive)
    //    50 — title / display name contains the query
    //    20 — job title contains the query  (staff only)
    //    10 — content / team / email match  (catch-all — WP already filtered these in)
    // Sort: score DESC, then alphabetically as tiebreaker.
    $q_lower = mb_strtolower($search_query);

    $score = function (string $text) use ($q_lower): int {
        $t = mb_strtolower($text);
        if ($t === $q_lower)                  return 100;
        if (str_contains($t, $q_lower))       return 50;
        return 0;
    };

    $all_items = [];

    foreach ($staff_results as $result) {
        $s = max(
            $score($result->display_name),
            $score($result->job_title) > 0 ? 20 : 0,
            $score($result->team)      > 0 ? 10 : 0,
            10 // floor: WP already confirmed a match exists
        );
        $all_items[] = [
            'type'     => 'staff',
            'data'     => $result,
            'score'    => $s,
            'sort_key' => $result->display_name,
        ];
    }

    foreach ($all_posts as $post_obj) {
        $title_score   = $score($post_obj->post_title);
        $content_score = $title_score > 0 ? 0 : 10; // content-only match gets 10
        $s = max($title_score, $content_score, 10);
        $all_items[] = [
            'type'     => 'post',
            'data'     => $post_obj,
            'score'    => $s,
            'sort_key' => $post_obj->post_title,
        ];
    }

    usort($all_items, function ($a, $b) {
        if ($b['score'] !== $a['score']) {
            return $b['score'] - $a['score']; // higher score first
        }
        return strcasecmp($a['sort_key'], $b['sort_key']); // alphabetical tiebreaker
    });

    $total_count = count($all_items);
    $total_pages = max(1, (int) ceil($total_count / $per_page));
    $offset      = ($current_page - 1) * $per_page;
    $page_items  = array_slice($all_items, $offset, $per_page);

    // Let the_posts_pagination() use our computed page count.
    $wp_query->max_num_pages = $total_pages;

} else {
    // -------------------------------------------------------------------------
    // Original mode: standard WP paginated posts only.
    // -------------------------------------------------------------------------
    $total_count = (int) $wp_query->found_posts;
    $page_items  = [];
    $flag_on     = false;
}

?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => __('Search the intranet', 'gca-intranet'),
  'image_url' => '',
]);
?>

<?php get_template_part('template-parts/breadcrumbs'); ?>

<div class="govuk-width-container" data-testid="search-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="search-main">
    <div class="govuk-grid-row" data-testid="search-row">
      <div class="govuk-grid-column-full" data-testid="search-col">

        <h1 class="govuk-heading-l govuk-!-margin-bottom-4" data-testid="search-heading">
          Search results for <span class="gca-search-query">&ldquo;<?php echo esc_html($search_query); ?>&rdquo;</span>
        </h1>

        <form
          class="gca-search-results-form govuk-!-margin-bottom-4"
          role="search"
          action="<?php echo esc_url($search_url); ?>"
          method="get"
          data-testid="search-results-form"
        >
          <label class="govuk-visually-hidden" for="search-results-input"><?php esc_html_e('Search the intranet', 'gca-intranet'); ?></label>
          <div class="search-input-group">
            <input
              id="search-results-input"
              name="s"
              type="search"
              class="govuk-input"
              placeholder="<?php esc_attr_e('Search again', 'gca-intranet'); ?>"
              value="<?php echo esc_attr($search_query); ?>"
              autocomplete="off"
            >
            <button class="govuk-button search-submit" type="submit" aria-label="<?php esc_attr_e('Search', 'gca-intranet'); ?>">
              <span class="govuk-visually-hidden"><?php esc_html_e('Search', 'gca-intranet'); ?></span>
              <svg class="gca-search-icon" width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.25 19.25L15.2625 15.2625M17.4167 10.0833C17.4167 14.1334 14.1334 17.4167 10.0833 17.4167C6.03325 17.4167 2.75 14.1334 2.75 10.0833C2.75 6.03325 6.03325 2.75 10.0833 2.75C14.1334 2.75 17.4167 6.03325 17.4167 10.0833Z" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </form>

        <?php if ($flag_on) : ?>

          <?php if ($total_count > 0) : ?>

            <p class="govuk-body govuk-!-margin-bottom-6" data-testid="search-result-count">
              Found <?php echo esc_html((string) $total_count); ?> result(s)
            </p>

            <div data-testid="search-results">
              <?php foreach ($page_items as $i => $item) : ?>

                <?php if ($item['type'] === 'staff') :
                  $result     = $item['data'];
                  $role_parts = array_filter([$result->job_title, $result->team]);
                  $role_line  = implode(' | ', $role_parts);
                ?>

                  <article class="gca-search-result gca-staff-result govuk-!-margin-bottom-0">
                    <h2 class="govuk-heading-m govuk-!-margin-bottom-2">
                      <span class="gca-search-result__type">Staff profile - </span><a
                        href="<?php echo esc_url($result->profile_url); ?>"
                        class="govuk-link"
                      ><?php echo esc_html($result->display_name); ?></a>
                    </h2>
                    <div class="gca-staff-result__detail">
                      <div class="gca-staff-result__avatar" aria-hidden="true">
                        <img
                          src="<?php echo esc_url($result->avatar_url); ?>"
                          alt=""
                          class="gca-staff-result__avatar-img"
                          width="48"
                          height="48"
                        >
                      </div>
                      <div class="gca-staff-result__body">
                        <?php if ($role_line) : ?>
                          <p class="govuk-body govuk-!-margin-bottom-1"><?php echo gca_highlight_search_excerpt($role_line, $search_query); ?></p>
                        <?php endif; ?>
                        <?php if ($result->email) : ?>
                          <p class="govuk-body-s govuk-!-margin-bottom-0" style="color:#505a5f;"><?php echo esc_html($result->email); ?></p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </article>

                <?php else :
                  global $post;
                  $post = $item['data'];
                  setup_postdata($post);

                  $content_type = gca_search_get_content_type_label();
                  $title        = gca_search_truncate(get_the_title(), 85);
                  $excerpt      = gca_search_context_excerpt($search_query);
                  $terms        = gca_search_get_post_terms();
                ?>

                  <article
                    class="gca-search-result govuk-!-margin-bottom-0"
                    data-testid="search-result"
                    data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
                  >
                    <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="search-result-title">
                      <span class="gca-search-result__type"><?php echo esc_html($content_type); ?> - </span><a
                        class="govuk-link"
                        href="<?php the_permalink(); ?>"
                        data-testid="search-result-link"
                      ><?php echo esc_html($title); ?></a>
                    </h2>

                    <?php if ($excerpt) : ?>
                      <p class="govuk-body govuk-!-margin-bottom-2" data-testid="search-result-excerpt">
                        <?php echo gca_highlight_search_excerpt($excerpt, $search_query); ?>
                      </p>
                    <?php endif; ?>

                    <?php if (!empty($terms)) : ?>
                      <div class="gca-search-result__terms" data-testid="search-result-terms">
                        <?php foreach ($terms as $term) : ?>
                          <?php $pill_class = ($term->taxonomy === 'category') ? 'tag_label' : 'tag_label grey'; ?>
                          <span class="<?php echo esc_attr($pill_class); ?>"><?php echo esc_html($term->name); ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>

                  </article>

                  <?php wp_reset_postdata(); ?>

                <?php endif; ?>

                <?php if ($i < count($page_items) - 1) : ?>
                  <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
                <?php endif; ?>

              <?php endforeach; ?>
            </div>

            <div class="govuk-!-margin-top-6" data-testid="search-pagination">
              <?php
              the_posts_pagination([
                'prev_text' => __('&larr; Previous', 'gca-intranet'),
                'next_text' => __('Next &rarr;', 'gca-intranet'),
                'end_size'  => 1,
                'mid_size'  => 3,
              ]);
              ?>
            </div>

          <?php else : ?>

            <p class="govuk-body" data-testid="search-no-results">
              No results found for &ldquo;<?php echo esc_html($search_query); ?>&rdquo;. Try a different search term.
            </p>

          <?php endif; ?>

        <?php else : ?>

          <?php // Original mode — flag is off, use standard WP loop. ?>

          <?php if (have_posts()) : ?>

            <p class="govuk-body govuk-!-margin-bottom-6" data-testid="search-result-count">
              Found <?php echo esc_html((string) $total_count); ?> result(s)
            </p>

            <div data-testid="search-results">
              <?php
              $result_index = 0;
              $post_count   = $wp_query->post_count;
              while (have_posts()) : the_post();
                $result_index++;

                $content_type = gca_search_get_content_type_label();
                $title        = gca_search_truncate(get_the_title(), 85);
                $excerpt      = gca_search_context_excerpt($search_query);
                $terms        = gca_search_get_post_terms();
              ?>

                <article
                  class="gca-search-result govuk-!-margin-bottom-0"
                  data-testid="search-result"
                  data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
                >
                  <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="search-result-title">
                    <span class="gca-search-result__type"><?php echo esc_html($content_type); ?> - </span><a
                      class="govuk-link"
                      href="<?php the_permalink(); ?>"
                      data-testid="search-result-link"
                    ><?php echo esc_html($title); ?></a>
                  </h2>

                  <?php if ($excerpt) : ?>
                    <p class="govuk-body govuk-!-margin-bottom-2" data-testid="search-result-excerpt">
                      <?php echo gca_highlight_search_excerpt($excerpt, $search_query); ?>
                    </p>
                  <?php endif; ?>

                  <?php if (!empty($terms)) : ?>
                    <div class="gca-search-result__terms" data-testid="search-result-terms">
                      <?php foreach ($terms as $term) : ?>
                        <?php $pill_class = ($term->taxonomy === 'category') ? 'tag_label' : 'tag_label grey'; ?>
                        <span class="<?php echo esc_attr($pill_class); ?>"><?php echo esc_html($term->name); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                </article>

                <?php if ($result_index < $post_count) : ?>
                  <hr class="govuk-section-break govuk-section-break--m govuk-section-break--visible">
                <?php endif; ?>

              <?php endwhile; ?>
            </div>

            <div class="govuk-!-margin-top-6" data-testid="search-pagination">
              <?php
              the_posts_pagination([
                'prev_text' => __('&larr; Previous', 'gca-intranet'),
                'next_text' => __('Next &rarr;', 'gca-intranet'),
                'end_size'  => 1,
                'mid_size'  => 3,
              ]);
              ?>
            </div>

          <?php else : ?>

            <p class="govuk-body" data-testid="search-no-results">
              No results found for &ldquo;<?php echo esc_html($search_query); ?>&rdquo;. Try a different search term.
            </p>

          <?php endif; ?>

        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>
