<?php
/**
 * Layout – Left Hand Nav
 * Left column:  Section nav menu, auto-resolved by finding the nav menu that contains the current page.
 * Right column: Main editor content + Fewbricks components (the_content).
 */

get_template_part('template-parts/single/_chrome');

$is_page = !empty($GLOBALS['gca_is_page']);
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-6" data-testid="page-container">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <main class="gca-single gca-single--left-nav" id="main-content" data-testid="layout-left-nav">
      <div class="govuk-grid-row">

        <!-- LEFT: Section nav -->
        <div class="govuk-grid-column-one-third" data-testid="col-left-nav">
          <?php echo do_shortcode('[menu]'); ?>
        </div>

        <!-- RIGHT: Main content -->
        <div class="govuk-grid-column-two-thirds" data-testid="col-main">

          <?php if (!$is_page) : ?>
            <h1 class="govuk-heading-l govuk-!-margin-bottom-2" data-testid="content-title">
              <?php the_title(); ?>
            </h1>

            <div class="gca-news-meta govuk-!-margin-bottom-2" data-testid="content-meta">
              <time class="govuk-body-s" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                <?php echo esc_html(get_the_date('j F Y')); ?>
              </time>
            </div>

            <?php
              $post_type = (string) get_post_type($post_id);
              if (in_array($post_type, ['news', 'blog'], true)) :
                $categories = get_the_terms($post_id, 'category');
                $labels     = get_the_terms($post_id, 'label');

                $has_categories = !empty($categories) && !is_wp_error($categories);
                $has_labels     = !empty($labels) && !is_wp_error($labels);

                if ($has_categories || $has_labels) :
            ?>
              <div class="gca-taxonomy-tags govuk-!-margin-bottom-4" data-testid="content-tags">

                <?php if ($has_categories) : ?>
                  <?php foreach ($categories as $term) : ?>
                    <span class="govuk-tag govuk-tag--green">
                      <?php echo esc_html($term->name); ?>
                    </span>
                  <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($has_labels) : ?>
                  <?php foreach ($labels as $term) : ?>
                    <span class="govuk-tag govuk-tag--grey">
                      <?php echo esc_html($term->name); ?>
                    </span>
                  <?php endforeach; ?>
                <?php endif; ?>

              </div>
            <?php endif; endif; ?>

          <?php endif; ?>

          <?php get_template_part('template-parts/template-body-content'); ?>

        </div>

      </div>
    </main>

  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
