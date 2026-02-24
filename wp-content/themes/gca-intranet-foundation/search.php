<?php get_header(); ?>

<div class="govuk-width-container">
  <main class="govuk-main-wrapper" id="main-content">
    <div class="govuk-grid-row">
      <div class="govuk-grid-column-two-thirds">

        <h1 class="govuk-heading-l">
          Search results for: <?php echo esc_html(get_search_query()); ?>
        </h1>

        <?php if (have_posts()) : ?>

          <?php while (have_posts()) : the_post(); ?>
            <article class="govuk-!-margin-bottom-6">
              <h2 class="govuk-heading-m govuk-!-margin-bottom-2">
                <a class="govuk-link" href="<?php the_permalink(); ?>">
                  <?php the_title(); ?>
                </a>
              </h2>

              <div class="govuk-body">
                <?php the_excerpt(); ?>
              </div>
            </article>
          <?php endwhile; ?>

          <div class="govuk-!-margin-top-6">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>

          <p class="govuk-body">No results found.</p>

        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>
