<?php get_header(); ?>

<?php
$hero_image_url = get_template_directory_uri() . '/assets/img/news.jpg';

get_template_part('template-parts/hero', null, [
    'title'     => 'News',
    'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>



<div class="govuk-width-container" data-testid="news-container">
    <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="news-main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <div class="govuk-grid-row">

                    <div class="govuk-grid-column-one-third">
                        <div class="">
                            <?php
                            if (has_post_thumbnail()): ?>
                                <img
                                    data-testid="news-featured-image"
                                    src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>"
                                    alt="<?php echo esc_attr(get_the_title()); ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="govuk-grid-column-two-thirds ">
                        <h1 class="govuk-heading-m govuk-!-margin-bottom-1" data-testid="news-title">
                            <?php the_title(); ?>
                        </h1>

                        <div data-testid="news-details">
                            <span class="govuk-body-s">
                                <?php echo esc_html(get_the_date('j F Y')); ?>
                            </span>

                            <div class="govuk-!-margin-top-5">
                                <?php
                                $categories = get_the_category();
                                $terms = get_the_terms(get_the_ID(), 'label');

                                if ($categories && $categories[0]->name !== 'Uncategorized') : ?>
                                    <span class="govuk-body-s tag_label" data-testid="news-category">
                                        <?php echo esc_html($categories[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php
                                if ($terms && !is_wp_error($terms)) : $term = array_shift($terms); ?>
                                    <span class="govuk-body-s tag_label grey" data-testid="news-tax">
                                        <?php echo esc_html($term->name); ?>
                                    </span>
                                <?php endif; ?>

                            </div>
                        </div>

                        <div class="govuk-body" data-testid="news-content">
                            <?php the_content(); ?>
                        </div>

                        <?php get_template_part('template-parts/published-by', null, [
                            'hide_last_updated' => true,
                        ]); ?>

                    </div>
                </div>

        <?php endwhile;
        endif; ?>
    </main>
</div>

<?php get_footer(); ?>