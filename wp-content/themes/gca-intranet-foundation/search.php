<?php
get_header();

global $wp_query;
$search_query = get_search_query();
$search_url   = get_theme_mod('gca_search_url', home_url('/'));

// KEEP staff search logic
$flag_on = function_exists('gca_flag_enabled')
    && gca_flag_enabled('staff-profiles')
    && function_exists('gca_search_staff');

if ($flag_on) {

    $per_page     = 10;
    $current_page = max(1, (int) (get_query_var('paged') ?: 1));

    $staff_results = gca_search_staff($search_query, 50);
    $all_posts     = $wp_query->posts ?: [];

    $q_lower = mb_strtolower($search_query);

    $score = function (string $text) use ($q_lower): int {
        $t = mb_strtolower($text);
        if ($t === $q_lower) return 100;
        if (str_contains($t, $q_lower)) return 50;
        return 0;
    };

    $all_items = [];

    foreach ($staff_results as $result) {
        $s = max(
            $score($result->display_name),
            $score($result->job_title) > 0 ? 20 : 0,
            $score($result->team) > 0 ? 10 : 0,
            10
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
        $content_score = $title_score > 0 ? 0 : 10;
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
            return $b['score'] - $a['score'];
        }
        return strcasecmp($a['sort_key'], $b['sort_key']);
    });

    $total_count = count($all_items);
    $total_pages = max(1, (int) ceil($total_count / 10));
    $offset      = ($current_page - 1) * 10;
    $page_items  = array_slice($all_items, $offset, 10);

    $wp_query->max_num_pages = $total_pages;

} else {
    $total_count = (int) $wp_query->found_posts;
}
?>

<?php
get_template_part('template-parts/hero', null, [
  'title' => __('Search the intranet', 'gca-intranet'),
]);
?>

<?php get_template_part('template-parts/breadcrumbs'); ?>

<div class="govuk-width-container">
  <main class="govuk-main-wrapper">

    <div class="govuk-grid-row">

      <!-- LEFT COLUMN -->
      <div class="govuk-grid-column-one-quarter">

        <!-- SEARCH -->
        <form action="<?php echo esc_url($search_url); ?>" method="get">
          <input name="s" value="<?php echo esc_attr($search_query); ?>">
        </form>

        <!-- ✅ KEEP reusable filters -->
        <?php
        get_template_part(
          'template-parts/components/filter-panel',
          null,
          [
            'include_taxonomies' => ['content_type', 'label', 'category', 'audience']
          ]
        );
        ?>

      </div>

      <!-- RESULTS -->
      <div class="govuk-grid-column-three-quarters">

        <h1 class="govuk-heading-l">
          Search results for “<?php echo esc_html($search_query); ?>”
        </h1>

        <?php if ($flag_on) : ?>

          <p>Found <?php echo esc_html($total_count); ?> results</p>

          <?php foreach ($page_items as $item) : ?>

            <?php if ($item['type'] === 'staff') : ?>
              <p><strong>Staff:</strong> <?php echo esc_html($item['data']->display_name); ?></p>

            <?php else :
              $post = $item['data'];
              setup_postdata($post);
            ?>
              <p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
              <?php wp_reset_postdata(); ?>
            <?php endif; ?>

          <?php endforeach; ?>

        <?php else : ?>

          <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <p><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>
          <?php endwhile; endif; ?>

        <?php endif; ?>

      </div>

    </div>

  </main>
</div>

<?php get_footer(); ?>