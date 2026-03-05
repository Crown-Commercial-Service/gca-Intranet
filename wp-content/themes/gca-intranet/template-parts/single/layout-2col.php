<?php
/**
 * Layout – 2 column
 * Left column: Featured image (News/Blog by default) + WYSIWYG meta (_gca_col2_wysiwyg)
 * Right column: main editor content (the_content)
 */

get_template_part('template-parts/single/_chrome');

$is_page = !empty($GLOBALS['gca_is_page']);
?>

<div class="govuk-width-container govuk-!-padding-top-6 govuk-!-padding-bottom-6" data-testid="page-container">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <?php
      $post_id = get_the_ID();

      $col2_raw  = (string) get_post_meta($post_id, '_gca_col2_wysiwyg', true);
      $col2_html = $col2_raw !== '' ? apply_filters('the_content', $col2_raw) : '';

      $post_type     = (string) get_post_type($post_id);
      $hide_featured = (bool) get_post_meta($post_id, '_gca_hide_featured_image', true);

      $should_show_featured =
        in_array($post_type, ['news', 'blog'], true) &&
        has_post_thumbnail($post_id) &&
        !$hide_featured;

      $has_left_content = $should_show_featured || $col2_html !== '';
    ?>

    <main class="gca-single gca-single--2col" data-testid="layout-2col">
      <div class="govuk-grid-row">

        <?php if ($has_left_content) : ?>
          <!-- LEFT: Featured image (News/Blog) + Column 2 WYSIWYG -->
          <div class="govuk-grid-column-one-third" data-testid="col-left">

            <?php if ($should_show_featured) : ?>
              <figure class="gca-featured-media govuk-!-margin-bottom-4" data-testid="featured-image">
                <?php echo get_the_post_thumbnail($post_id, 'large', ['class' => 'gca-featured-media__img']); ?>
              </figure>
            <?php endif; ?>

            <?php if ($col2_html !== '') : ?>
              <div class="gca-richtext" data-testid="column-2-body">
                <?php echo $col2_html; ?>
              </div>
            <?php endif; ?>

          </div>
        <?php endif; ?>

        <!-- RIGHT: Main content -->
        <div class="govuk-grid-column-two-thirds" data-testid="col-main">

          <?php if (!$is_page) : ?>
            <h1 class="govuk-heading-l govuk-!-margin-bottom-2" data-testid="content-title">
              <?php the_title(); ?>
            </h1>

            <div class="gca-news-meta govuk-!-margin-bottom-4" data-testid="content-meta">
              <time class="govuk-body-s" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                <?php echo esc_html(get_the_date('j F Y')); ?>
              </time>
            </div>
          <?php endif; ?>

          <div class="gca-richtext" data-testid="content-body">
            <?php the_content(); ?>
          </div>

        </div>

      </div>
    </main>

  <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>