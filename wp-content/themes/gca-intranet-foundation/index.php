<?php get_header(); ?>

<main id="main-content" role="main" data-testid="home-main">

    <section class="gca-home-hero" aria-label="Homepage hero" data-testid="home-hero">
        <div class="container-xxl">
            <div class="gca-home-hero-inner">

                <div class="gca-home-hero-content" data-testid="home-hero-content">
                    <p class="gca-home-hero-kicker mb-2" data-testid="home-hero-kicker">Welcome to our</p>
                    <h1 class="gca-home-hero-title mb-0" data-testid="home-hero-title">GCA Intranet</h1>
                </div>

                <div class="gca-home-hero-tagline" aria-hidden="true" data-testid="home-hero-tagline">
                    <span class="gca-home-hero-tagline-rule"></span>
                    <span class="gca-home-hero-tagline-text" data-testid="home-hero-tagline-text">value for the
                        nation</span>
                </div>

            </div>
        </div>
    </section>

    <div class="govuk-width-container">
        <main class="govuk-main-wrapper">
            <div class="govuk-grid-row">

                <!-- ===================== LATEST NEWS ===================== -->
                <div class="govuk-grid-column-two-thirds" data-testid="latest-news-column">
                    <div class="gca-homepage-section-title">
                        <h2 class="govuk-heading-m" data-testid="latest-news-heading">Latest news</h2>
                        <p class="govuk-body">What's happening in our organisation</p>
                    </div>

                    <div class="govuk-grid-row gca-equal-height-row" data-testid="latest-news-section">

                        <div class="govuk-grid-column-one-half">
                            <div class="gca-featured-news" data-testid="latest-news-featured">
                                <?php
                                $latest_post = new WP_Query(array('posts_per_page' => 1));
                                if ($latest_post->have_posts()):
                                    while ($latest_post->have_posts()):
                                        $latest_post->the_post();
                                        ?>
                                        <?php if (has_post_thumbnail()): ?>
                                            <img data-testid="latest-news-featured-image"
                                                src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>"
                                                alt="<?php the_title(); ?>">
                                        <?php endif; ?>

                                        <div>
                                            <h3 class="govuk-heading-m">
                                                <a data-testid="latest-news-featured-title" href="<?php the_permalink(); ?>">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h3>
                                            <p data-testid="latest-news-featured-excerpt">
                                                <?php echo wp_trim_words(get_the_excerpt(), 32, '...'); ?>
                                            </p>
                                            <p data-testid="latest-news-featured-date">
                                                <?php echo get_the_date('jS F Y'); ?>
                                            </p>
                                        </div>

                                    <?php endwhile; endif;
                                wp_reset_postdata(); ?>
                            </div>
                        </div>

                        <div class="govuk-grid-column-one-half gca-flex-box-news">
                            <?php
                            $secondary_posts = new WP_Query(array('posts_per_page' => 3, 'offset' => 1));
                            if ($secondary_posts->have_posts()):
                                while ($secondary_posts->have_posts()):
                                    $secondary_posts->the_post();
                                    $is_first = ($secondary_posts->current_post === 0);
                                    $padding_top_class = $is_first ? 'gca-first-small-list-news' : 'gca-not_first-small-list-news';
                                    ?>
                                    <div class="gca-small-list-news <?php echo $padding_top_class; ?>"
                                        data-testid="latest-news-secondary-card">
                                        <div class="govuk-grid-row">
                                            <div class="govuk-grid-column-one-third">
                                                <?php if (has_post_thumbnail()): ?>
                                                    <img data-testid="latest-news-secondary-image"
                                                        src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>"
                                                        alt="<?php the_title(); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="govuk-grid-column-two-third gca-flex-box-news">
                                                <h3 class="govuk-heading-s govuk-!-margin-bottom-1">
                                                    <a data-testid="latest-news-secondary-title"
                                                        href="<?php the_permalink(); ?>">
                                                        <?php the_title(); ?>
                                                    </a>
                                                </h3>
                                                <p data-testid="latest-news-secondary-excerpt">
                                                    <?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?>
                                                </p>
                                                <p data-testid="latest-news-secondary-date">
                                                    <?php echo get_the_date('jS F Y'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; endif;
                            wp_reset_postdata(); ?>
                        </div>

                        <div class="see-more-link-homepage" data-testid="latest-news-see-more">
                            <p>
                                <a data-testid="latest-news-see-more-link" href="#" class="govuk-!-padding-left-1">
                                    Browse all news articles
                                </a>
                            </p>
                        </div>

                    </div>
                </div>

                <!-- ===================== TAKE A LOOK ===================== -->
                <div class="govuk-grid-column-one-third" data-testid="take-a-look-column">
                    <div class="gca-homepage-section-title">
                        <h2 class="govuk-heading-m" data-testid="take-a-look-heading">Take a look</h2>
                        <p class="govuk-body">Lorem ipsum Alakazam is a Psi Pokémon.</p>
                    </div>
                    <div class="govuk-!-padding-4" style="background-color: #7fffd4; height: 200px;"
                        data-testid="take-a-look-card">
                        <p class="govuk-body">Additional content...</p>
                    </div>
                </div>

            </div>

            <!-- ===================== LOWER SECTION ===================== -->
            <div class="govuk-width-container">
                <main class="govuk-main-wrapper">
                    <div class="govuk-grid-row flex">

                        <!-- WORK UPDATES -->
                        <div class="govuk-grid-column-two-thirds" data-testid="work-updates-column">
                            <div class="gca-homepage-section-title">
                                <h2 class="govuk-heading-m" data-testid="work-updates-heading">Work updates</h2>
                                <p class="govuk-body">Lorem ipsum Super Nerd's favorite Pokémon is Weepinbell.</p>
                            </div>

                            <div class="govuk-grid-row gca-equal-height-row" data-testid="work-updates-section">
                                <?php
                                $work_updates = new WP_Query(array('post_type' => 'work update', 'posts_per_page' => 2));
                                if ($work_updates->have_posts()):
                                    while ($work_updates->have_posts()):
                                        $work_updates->the_post();
                                        ?>
                                        <div class="govuk-grid-column-one-half gca-work-update-card"
                                            data-testid="work-update-card">
                                            <div class="govuk-grid-row gca-work-updates">
                                                <div class="govuk-grid-column-one-third" data-testid="work-update-avatar">
                                                    <?php if ($avatar = get_avatar(get_the_author_meta('ID'))): ?>
                                                        <?php echo $avatar; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="govuk-grid-column-two-thirds">
                                                    <h3 class="govuk-heading-s">
                                                        <a data-testid="work-update-title" href="<?php the_permalink(); ?>">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h3>
                                                    <p data-testid="work-update-author">By
                                                        <?php echo get_the_author(); ?>
                                                    </p>
                                                    <p data-testid="work-update-date">
                                                        <?php echo get_the_date('jS F Y'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; endif;
                                wp_reset_postdata(); ?>

                                <div class="see-more-link-homepage" data-testid="work-updates-see-more">
                                    <p>
                                        <a data-testid="work-updates-see-more-link" href="#"
                                            class="govuk-!-padding-left-1">
                                            More work updates
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- BLOGS -->
                        <div class="govuk-grid-column-one-third" data-testid="blogs-column">
                            <div class="gca-homepage-section-title">
                                <h2 class="govuk-heading-m" data-testid="blogs-heading">Blogs</h2>
                                <p class="govuk-body">Lorem ipsum Rising Star used a Dusk Ball.</p>
                            </div>

                            <div class="govuk-grid-row" data-testid="blogs-section">
                                <div class="govuk-grid-column-full gca-work-update-card">
                                    <div class="govuk-grid-row gca-work-updates">
                                        <div class="govuk-grid-column-one-third" data-testid="blogs-avatar">
                                            <?php $blogs = new WP_Query(array('post_type' => 'blog', 'posts_per_page' => 1));
                                            if ($blogs->have_posts()):
                                                while ($blogs->have_posts()):
                                                    $blogs->the_post();
                                                    ?>
                                                    <?php if ($avatar = get_avatar(get_the_author_meta('ID'))): ?>
                                                        <?php echo $avatar; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="govuk-grid-column-two-thirds">
                                                    <h3 class="govuk-heading-s">
                                                        <a data-testid="blogs-title" href="<?php the_permalink(); ?>">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h3>
                                                    <p data-testid="blogs-author">By
                                                        <?php echo get_the_author(); ?>
                                                    </p>
                                                    <p data-testid="blogs-date">
                                                        <?php echo get_the_date('jS F Y'); ?>
                                                    </p>
                                                </div>
                                            <?php endwhile; endif;
                                            wp_reset_postdata(); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="see-more-link-homepage" data-testid="blogs-see-more">
                                <p>
                                    <a data-testid="blogs-see-more-link" href="#" class="govuk-!-padding-left-1">
                                        More blogs
                                    </a>
                                </p>
                            </div>

                        </div>

                    </div>
                </main>
            </div>

        </main>
    </div>
</main>

<?php get_footer(); ?>