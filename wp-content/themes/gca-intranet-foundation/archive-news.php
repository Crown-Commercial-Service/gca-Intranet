<?php get_header(); ?>

<?php
$hero_image_url = get_template_directory_uri() . '/assets/img/office.jpg';

get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="news-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="news-main">

    <?php if (have_posts()) : ?>

      <?php while (have_posts()) : the_post(); ?>
        <article class="news-card flex" data-testid="news-post">
          <div class="news-image-wrap">
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('medium', ['class' => 'news-image']); ?>
            <?php else : ?>
              <div class="news-placeholder"></div>
            <?php endif; ?>
          </div>

          <div>
            <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="news-post-title">
              <a class="govuk-link" href="<?php the_permalink(); ?>" data-testid="news-post-link">
                <?php the_title(); ?>
              </a>
            </h2>

            <p data-testid="news-desc">
              <?php
              $content = strip_tags(get_the_content());
              echo esc_html(mb_strlen($content) > 320 ? mb_substr($content, 0, 320) . '...' : $content);
              ?>
            </p>

            <div class="gca-news-meta date_bottom" data-testid="news-post-meta">
              <span class="govuk-body-s govuk-!-margin-right-2">
                <?php echo esc_html(get_the_date('jS F Y')); ?>
              </span>

              <div class="gca-taxonomy-tags" data-testid="news-post-tags">
                <?php
                $categories = get_the_terms(get_the_ID(), 'category');
                if ($categories && !is_wp_error($categories)) :
                  foreach ($categories as $category) :
                ?>
                  <span class="govuk-tag govuk-tag--green">
                    <?php echo esc_html($category->name); ?>
                  </span>
                <?php
                  endforeach;
                endif;

                $labels = get_the_terms(get_the_ID(), 'label');
                if ($labels && !is_wp_error($labels)) :
                  foreach ($labels as $label) :
                ?>
                  <span class="govuk-tag govuk-tag--grey">
                    <?php echo esc_html($label->name); ?>
                  </span>
                <?php
                  endforeach;
                endif;
                ?>
              </div>
            </div>

          </div>
        </article>
      <?php endwhile; ?>

      <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8" data-testid="news-pagination">
        <?php
        the_posts_pagination(array(
          'mid_size'  => 2,
          'prev_text' => sprintf(
            '<span class="icon">
              <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M6.7 0l1.4 1.4-4.3 4.3h13v2H3.9l4.2 4-1.4 1.4L0 6.7z" fill="#007194" fill-rule="evenodd"/></svg>
            </span> <span>Previous</span>
            <span class="govuk-visually-hidden">page</span>'
          ),
          'next_text' => sprintf(
            '<span>Next</span> <span class="govuk-visually-hidden">page</span>
            <span class="icon">
              <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M10.1 0L8.7 1.4 13 5.7H0v2h12.9l-4.2 4 1.4 1.4 6.7-6.4z" fill="#007194" fill-rule="evenodd"/></svg>
            </span>'
          ),
        ));
        ?>
      </div>

    <?php else : ?>
      <p class="govuk-body" data-testid="news-no-posts">No News found.</p>
    <?php endif; ?>

  </main>
</div>

<?php get_footer(); ?>