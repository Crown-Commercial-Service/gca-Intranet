<?php get_header(); ?>

<main class="govuk-main-wrapper" id="main-content" tabindex="-1">
    <div class="govuk-width-container">
        <h1 class="govuk-heading-l">All for one, one for all</h1>
        
        <div class="govuk-grid-row">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="govuk-grid-column-full">
                    <h2 class="govuk-heading-m">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <p class="govuk-body">By <?php echo get_the_author(); ?> on <?php echo get_the_date('jS F Y'); ?></p>
                    <?php the_excerpt(); ?>
                </div>
            <?php endwhile; endif; ?>
        </div>
        
        <?php the_posts_pagination(); ?>
    </div>
</main>

<?php get_footer(); ?>
