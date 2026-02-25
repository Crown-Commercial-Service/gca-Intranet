<?php get_header(); ?>

<div class="govuk-width-container" data-testid="search-container">
  <main class="govuk-main-wrapper" id="main-content" data-testid="search-main">
    <div class="govuk-grid-row" data-testid="search-row">
      <div class="govuk-grid-column-two-thirds" data-testid="search-col">

        <h1 class="govuk-heading-l" data-testid="search-heading">
          Search results for: <span data-testid="search-query"><?php echo esc_html(get_search_query()); ?></span>
        </h1>

        <?php if (have_posts()) : ?>

          <div data-testid="search-results">
            <?php while (have_posts()) : the_post(); ?>
              <article
                class="govuk-!-margin-bottom-6"
                data-testid="search-result"
                data-post-id="<?php echo esc_attr((string) get_the_ID()); ?>"
              >
                <h2 class="govuk-heading-m govuk-!-margin-bottom-2" data-testid="search-result-title">
                  <a
                    class="govuk-link"
                    href="<?php the_permalink(); ?>"
                    data-testid="search-result-link"
                  >
                    <?php the_title(); ?>
                  </a>
                </h2>

                <div class="govuk-body" data-testid="search-result-excerpt">
                  <?php the_excerpt(); ?>
                </div>
              </article>
            <?php endwhile; ?>
          </div>

          <div class="govuk-!-margin-top-6" data-testid="search-pagination">
            <?php the_posts_pagination(); ?>
          </div>

        <?php else : ?>

          <p class="govuk-body" data-testid="search-no-results">No results found.</p>

        <?php endif; ?>

      </div>
    </div>
  </main>
</div>

<?php get_footer(); ?>