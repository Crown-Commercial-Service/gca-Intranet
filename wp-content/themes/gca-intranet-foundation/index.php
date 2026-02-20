<?php get_header(); ?>

<main id="main-content" role="main">

    <section class="gca-home-hero" aria-label="Homepage hero">
        <div class="container-xxl">
            <div class="gca-home-hero-inner">

                <div class="gca-home-hero-content">
                    <p class="gca-home-hero-kicker mb-2">Welcome to our</p>
                    <h1 class="gca-home-hero-title mb-0">GCA Intranet</h1>
                </div>

                <div class="gca-home-hero-tagline" aria-hidden="true">
                    <span class="gca-home-hero-tagline-rule"></span>
                    <span class="gca-home-hero-tagline-text">value for the nation</span>
                </div>

            </div>
        </div>
    </section>
    
    <div class="govuk-width-container">
        <main class="govuk-main-wrapper">
            <div class="govuk-grid-row">
                <div class="govuk-grid-column-two-thirds ">
                    <div class="gca-homepage-section-title">
                        <h2 class="govuk-heading-m">Latest news</h2>
                        <p class="govuk-body">What's happening in our organisation</p>
                    </div>

                    <div class="govuk-grid-row gca-equal-height-row">
                        <div class="govuk-grid-column-one-half">
                            <div class="gca-featured-news">
                                <?php
                                $latest_post = new WP_Query(array('posts_per_page' => 1));
                                if ($latest_post->have_posts()) : while ($latest_post->have_posts()) : $latest_post->the_post();
                                ?>
                                        <?php if (has_post_thumbnail()) : ?>
                                            <img
                                                src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>"
                                                alt="<?php the_title(); ?>">
                                        <?php endif; ?>

                                        <div>
                                            <h3 class="govuk-heading-m"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <p><?php echo wp_trim_words(get_the_excerpt(), 32, '...'); ?></p>
                                            <p><?php echo get_the_date('jS F Y'); ?></p>
                                        </div>

                                <?php endwhile; endif; wp_reset_postdata(); ?>
                            </div>
                        </div>
                        <div class="govuk-grid-column-one-half gca-flex-box-news">

                            <?php
                            $secondary_posts = new WP_Query(array('posts_per_page' => 3, 'offset' => 1));
                            if ($secondary_posts->have_posts()) : while ($secondary_posts->have_posts()) : $secondary_posts->the_post();
                                $is_first = ($secondary_posts->current_post === 0);
                                $padding_top_class = $is_first ? 'gca-first-small-list-news' : 'gca-not_first-small-list-news';
                            ?>
                                <div class="gca-small-list-news <?php echo $padding_top_class; ?>">
                                    <div class="govuk-grid-row">
                                        <div class="govuk-grid-column-one-third">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <img
                                                    src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>"
                                                    alt="<?php the_title(); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="govuk-grid-column-two-third gca-flex-box-news">
                                            <h3 class="govuk-heading-s govuk-!-margin-bottom-1"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <p><?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?></p>
                                            <p><?php echo get_the_date('jS F Y'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; endif; wp_reset_postdata(); ?>
                        </div>
                        <div class="see-more-link-homepage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="22" fill="currentColor" 
                                class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16" 
                                style="stroke: currentColor; stroke-width: 1.8;">  
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                            <p><a href="#" class="govuk-!-padding-left-1"> Browse all news articles</a></p>
                        </div>
                    </div>


                </div>
                <div class="govuk-grid-column-one-third">
                    <div class="gca-homepage-section-title">
                        <h2 class="govuk-heading-m">Take a look</h2>
                        <p class="govuk-body">Lorem ipsum Alakazam is a Psi Pokémon.</p>
                    </div>
                    <div class="govuk-!-padding-4" style="background-color: #7fffd4; height: 200px;">
                        <p class="govuk-body">Additional content...</p>
                    </div>
                </div>
            </div>

            <div class="govuk-width-container">
                <main class="govuk-main-wrapper">
                    <div class="govuk-grid-row flex" >
                    <div class="govuk-grid-column-two-thirds">
                        <div class="gca-homepage-section-title">
                            <h2 class="govuk-heading-m">Work updates</h2>
                            <p class="govuk-body">Lorem ipsum Super Nerd's favorite Pokémon is Weepinbell.</p>
                        </div>

                        <div class="govuk-grid-row gca-equal-height-row">
                            <?php
                                $work_updates = new WP_Query(array('post_type' => 'work update', 'posts_per_page' => 2));
                                if ($work_updates->have_posts()) : while ($work_updates->have_posts()) : $work_updates->the_post();
                                
                                ?>
                                <div class="govuk-grid-column-one-half gca-work-update-card">
                                    <div class="govuk-grid-row gca-work-updates" >
                                        <div class="govuk-grid-column-one-third" >
                                            <?php if($avatar = get_avatar(get_the_author_meta('ID')) !== FALSE): ?>
                                                <?php echo get_avatar(get_the_author_meta('ID')); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="govuk-grid-column-two-thirds">
                                            <h3 class="govuk-heading-s"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <p>By <?php echo get_the_author(); ?></p>
                                            <p><?php echo get_the_date('jS F Y'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; endif; wp_reset_postdata(); ?>

                            <div class="see-more-link-homepage">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="22" fill="currentColor" 
                                    class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16" 
                                    style="stroke: currentColor; stroke-width: 1.8;">  
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                                </svg>
                                <p><a href="#" class="govuk-!-padding-left-1"> More work updates </a></p>
                            </div>

                        </div>

                    </div>

                    <div>
                        <div class="vr"></div>
                    </div>

                    <div class="govuk-grid-column-one-third">
                        <div class="gca-homepage-section-title">
                            <h2 class="govuk-heading-m">Blogs</h2>
                            <p class="govuk-body">Lorem ipsum Rising Star used a Dusk Ball.</p>
                        </div>
                        <div class="govuk-grid-row">
                            <div class="govuk-grid-column-full gca-work-update-card">
                                <div class="govuk-grid-row gca-work-updates" >
                                    <div class="govuk-grid-column-one-third" >
                                        <?php $blogs = new WP_Query(array('post_type' => 'blog', 'posts_per_page' => 1));
                                        if ($blogs->have_posts()) : while ($blogs->have_posts()) : $blogs->the_post();
                                        
                                        ?>
                                        <?php if($avatar = get_avatar(get_the_author_meta('ID')) !== FALSE): ?>
                                            <?php echo get_avatar(get_the_author_meta('ID')); ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="govuk-grid-column-two-thirds">
                                        <h3 class="govuk-heading-s"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <p>By <?php echo get_the_author(); ?></p>
                                        <p><?php echo get_the_date('jS F Y'); ?></p>
                                    </div>
                                    <?php endwhile; endif; wp_reset_postdata(); ?>
                                </div>
                            </div>
                        </div>
                        <div class="see-more-link-homepage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="22" fill="currentColor" 
                                class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16" 
                                style="stroke: currentColor; stroke-width: 1.8;">  
                            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                            <p><a href="#" class="govuk-!-padding-left-1"> More blogs </a></p>
                        </div>
                        
                    </div>
                </main>
            </div>

        </main>
    </div>
</main>

<?php get_footer(); ?>