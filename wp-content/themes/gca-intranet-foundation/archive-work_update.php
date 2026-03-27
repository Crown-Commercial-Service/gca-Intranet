<?php get_header(); ?>

<?php
$hero_image_url = get_template_directory_uri() . '/assets/img/work_updates.jpg';

get_template_part('template-parts/hero', null, [
  'title'     => post_type_archive_title('', false),
  'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>

<div class="govuk-width-container" data-testid="work-update-container">
  <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="work-update-main">

    <?php if (have_posts()) : ?>

      <?php while (have_posts()) : the_post(); ?>
        <article class="work-update-box" data-testid="work-update-post">

          <div class="work_update_profile_img" >
            <?php
              $custome_author_img = get_field('image');

              if ($custome_author_img) :
                  echo wp_get_attachment_image($custome_author_img, 'thumbnail', false, ['class' => 'avatar']);
              else :
                  if ($avatar = get_avatar(get_the_author_meta('ID'))) :
                      echo $avatar;
                  endif;
              endif;
            ?>
          </div>

          <div>
            <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="work-update-post-title">
              <a class="govuk-link" href="<?php the_permalink(); ?>" data-testid="work-update-post-link" >
                <?php the_title(); ?>
              </a>
            </h2>

            <p data-testid="work-update-decs">
              <?php echo esc_html(gca_clean_post_excerpt(320)); ?>
            </p>

            <p class="govuk-body">
              By <?php echo esc_html(get_the_author()); ?>
            </p>

            <p class="date_bottom" style="margin: 0;" data-testid="work-update-post-date">

              <span class="govuk-!-margin-right-2" style="font-size: initial;">
                <?php echo esc_html(get_the_date('j F Y')); ?>
              </span>

              <?php
              $terms = get_the_terms(get_the_ID(), 'label');

              if ($terms && !is_wp_error($terms)) :
                $term = array_shift($terms); ?>
                <span class="govuk-body-s tag_label green">
                    <?php echo esc_html($term->name); ?>
                </span>
              <?endif; ?>

              <?php
              $teams = get_the_terms(get_the_ID(), 'responsible_team');

              if ($teams && !is_wp_error($teams)) :
                $team = array_shift($teams); ?>
                <span class="govuk-body-s tag_label grey" style="margin:0;">
                    <?php echo esc_html($team->name); ?>
                </span>
              <?php endif; ?>

            </p>
          </div>

        </article>
      <?php endwhile; ?>

        <div class="govuk-!-margin-top-8 govuk-!-margin-bottom-8" data-testid="work-update-pagination">
          <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => sprintf(
                    '<span class="icon">
                        <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M6.7 0l1.4 1.4-4.3 4.3h13v2H3.9l4.2 4-1.4 1.4L0 6.7z" fill="#007194" fill-rule="evenodd"/></svg>
                      </span> <span>Previous</span>
                    <span class="govuk-visually-hidden">page</span>'
                ),
                'next_text' => sprintf(
                    '<span>Next</span> <span class="govuk-visually-hidden">page</span>
                    <span class="icon">
                        <svg width="17" height="14" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M10.1 0L8.7 1.4 13 5.7H0v2h12.9l-4.2 4 1.4 1.4 6.7-6.4z" fill="#007194" fill-rule="evenodd"/></svg>
                    </span>'
                ),
            ) );
          ?>
        </div>

      <?php else : ?>
        <p class="govuk-body" data-testid="work-update-no-posts">No work updates found.</p>
      <?php endif; ?>

  </main>
</div>

<?php get_footer(); ?>