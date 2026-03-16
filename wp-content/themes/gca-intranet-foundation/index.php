<?php get_header(); ?>

<main id="main-content" role="main" data-testid="home-main">
    <section class="gca-home-hero" aria-label="Homepage hero" data-testid="home-hero">
        <div class="container-xxl" data-testid="home-hero-container">
            <div class="gca-home-hero-inner" data-testid="home-hero-inner">
                <div class="gca-home-hero-content" data-testid="home-hero-content">
                    <p class="gca-home-hero-kicker mb-2" data-testid="home-hero-kicker">Welcome to our</p>
                    <h1 class="gca-home-hero-title mb-0" data-testid="home-hero-title">GCA Intranet</h1>
                </div>

                <div class="gca-home-hero-tagline" aria-hidden="true" data-testid="home-hero-tagline">
                    <span class="gca-home-hero-tagline-rule" data-testid="home-hero-tagline-rule"></span>
                    <span class="gca-home-hero-tagline-text" data-testid="home-hero-tagline-text">value for the
                        nation</span>
                </div>
            </div>
        </div>
    </section>

    <div class="govuk-width-container" data-testid="home-width-container">
        <main class="govuk-main-wrapper" data-testid="home-main-wrapper">
            <div class="govuk-grid-row" data-testid="home-top-row">
                <div class="govuk-grid-column-two-thirds" data-testid="latest-news-column">
                    <div class="gca-homepage-section-title" data-testid="latest-news-header">
                        <h2 class="govuk-heading-m" data-testid="latest-news-heading">Latest news</h2>
                        <p class="govuk-body" data-testid="latest-news-subheading">What's happening in our organisation
                        </p>
                    </div>

                    <div class="govuk-grid-row gca-equal-height-row" data-testid="latest-news-section">
                        <div class="govuk-grid-column-one-half" data-testid="latest-news-featured-col">
                            <div class="gca-featured-news" data-testid="latest-news-featured-card">
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

                                        <div data-testid="latest-news-featured-content">
                                            <h3 class="govuk-heading-m" data-testid="latest-news-featured-title">
                                                <a data-testid="latest-news-featured-link" href="<?php the_permalink(); ?>">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h3>

                                            <p data-testid="latest-news-featured-excerpt">
                                                <?php echo wp_trim_words(get_the_excerpt(), 32, '...'); ?>
                                            </p>

                                            <p data-testid="latest-news-featured-date">
                                                <?php echo get_the_date('j F Y'); ?>
                                            </p>
                                        </div>
                                        <?php
                                    endwhile;
                                endif;
                                wp_reset_postdata();
                                ?>
                            </div>
                        </div>

                        <div class="govuk-grid-column-one-half gca-flex-box-news"
                            data-testid="latest-news-secondary-col">
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
                                        <div class="govuk-grid-row" data-testid="latest-news-secondary-row">
                                            <div class="govuk-grid-column-one-third"
                                                data-testid="latest-news-secondary-image-col">
                                                <?php if (has_post_thumbnail()): ?>
                                                    <img data-testid="latest-news-secondary-image"
                                                        src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>"
                                                        alt="<?php the_title(); ?>">
                                                <?php endif; ?>
                                            </div>

                                            <div class="govuk-grid-column-two-third gca-flex-box-news"
                                                data-testid="latest-news-secondary-content">
                                                <h3 class="govuk-heading-s govuk-!-margin-bottom-1"
                                                    data-testid="latest-news-secondary-title">
                                                    <a data-testid="latest-news-secondary-link"
                                                        href="<?php the_permalink(); ?>">
                                                        <?php the_title(); ?>
                                                    </a>
                                                </h3>

                                                <p data-testid="latest-news-secondary-excerpt">
                                                    <?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?>
                                                </p>

                                                <p data-testid="latest-news-secondary-date">
                                                    <?php echo get_the_date('j F Y'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                            endif;
                            wp_reset_postdata();
                            ?>
                        </div>

                        <div class="see-more-link-homepage" data-testid="latest-news-see-more">
                            <svg data-testid="latest-news-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16"
                                height="22" fill="currentColor" class="bi bi-chevron-right govuk-!-padding-top-1"
                                viewBox="0 0 16 16" style="stroke: currentColor;;">
                                <path fill-rule="evenodd"
                                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                            </svg>

                            <p data-testid="latest-news-see-more-text">
                                <a data-testid="latest-news-see-more-link" href="#" class="govuk-!-padding-left-1">
                                    Browse all news articles
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="govuk-grid-column-one-third" data-testid="take-a-look-column">
                    <div class="gca-homepage-section-title" data-testid="take-a-look-header">
                        <h2 class="govuk-heading-m" data-testid="take-a-look-heading">Take a look</h2>
                        <p class="govuk-body" data-testid="take-a-look-subheading">Lorem ipsum Alakazam is a Psi
                            Pokémon.</p>
                    </div>

                    <div class="govuk-!-padding-4" style="background-color: #7fffd4; height: 200px;"
                        data-testid="take-a-look-card">
                        <p class="govuk-body" data-testid="take-a-look-card-text">Additional content...</p>
                    </div>
                </div>
            </div>

            <div class="govuk-width-container" data-testid="home-lower-width-container">
                <main class="govuk-main-wrapper" data-testid="home-lower-main-wrapper">
                    <div class="govuk-grid-row flex" data-testid="home-lower-row">
                        <div class="govuk-grid-column-two-thirds" data-testid="work-updates-column">
                            <div class="gca-homepage-section-title" data-testid="work-updates-header">
                                <h2 class="govuk-heading-m" data-testid="work-updates-heading">Work updates</h2>
                                <p class="govuk-body" data-testid="work-updates-subheading">
                                    Lorem ipsum Super Nerd's favorite Pokémon is Weepinbell.
                                </p>
                            </div>

                            <div class="govuk-grid-row gca-equal-height-row" data-testid="work-updates-section">
                                <?php
                                $work_updates = new WP_Query(array(
                                    'post_type' => 'work update',
                                    'posts_per_page' => 2
                                ));

                                if ($work_updates->have_posts()):
                                    while ($work_updates->have_posts()):
                                        $work_updates->the_post();
                                        ?>
                                        <div class="govuk-grid-column-one-half gca-work-update-card"
                                            data-testid="work-update-card">
                                            <div class="govuk-grid-row gca-work-updates" data-testid="work-update-row">
                                                <div class="govuk-grid-column-one-third" data-testid="work-update-avatar">
                                                    <?php if ($avatar = get_avatar(get_the_author_meta('ID'))): ?>
                                                        <?php echo $avatar; ?>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="govuk-grid-column-two-thirds" data-testid="work-update-content">
                                                    <h3 class="govuk-heading-s" data-testid="work-update-title">
                                                        <a href="<?php the_permalink(); ?>" data-testid="work-update-link">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h3>

                                                    <p data-testid="work-update-author">By
                                                        <?php echo get_the_author(); ?>
                                                    </p>

                                                    <p data-testid="work-update-date">
                                                        <?php echo get_the_date('j F Y'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    endwhile;
                                endif;
                                wp_reset_postdata();
                                ?>

                                <div class="see-more-link-homepage" data-testid="work-updates-see-more">
                                    <svg data-testid="work-updates-see-more-icon" xmlns="http://www.w3.org/2000/svg"
                                        width="16" height="22" fill="currentColor"
                                        class="bi bi-chevron-right govuk-!-padding-top-1" viewBox="0 0 16 16"
                                        style="stroke: currentColor;;">
                                        <path fill-rule="evenodd"
                                            d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                                    </svg>

                                    <p data-testid="work-updates-see-more-text">
                                        <a data-testid="work-updates-see-more-link" href="#"
                                            class="govuk-!-padding-left-1">
                                            More work updates
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div data-testid="home-divider">
                            <div class="vr" data-testid="home-divider-vr"></div>
                        </div>

                        <div class="govuk-grid-column-one-third" data-testid="blogs-column">
                            <div class="gca-homepage-section-title" data-testid="blogs-header">
                                <h2 class="govuk-heading-m" data-testid="blogs-heading">Blogs</h2>
                                <p class="govuk-body" data-testid="blogs-subheading">Lorem ipsum Rising Star used a Dusk
                                    Ball.</p>
                            </div>

                            <div class="govuk-grid-row" data-testid="blogs-section">
                                <div class="govuk-grid-column-full gca-work-update-card" data-testid="blogs-card">
                                    <div class="govuk-grid-row gca-work-updates" data-testid="blogs-row">
                                        <?php
                                        $blogs = new WP_Query(array('post_type' => 'blog', 'posts_per_page' => 1));
                                        if ($blogs->have_posts()):
                                            while ($blogs->have_posts()):
                                                $blogs->the_post();
                                                ?>
                                                <div class="govuk-grid-column-one-third" data-testid="blogs-avatar">
                                                    <?php if ($avatar = get_avatar(get_the_author_meta('ID'))): ?>
                                                        <?php echo $avatar; ?>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="govuk-grid-column-two-thirds" data-testid="blogs-content">
                                                    <h3 class="govuk-heading-s" data-testid="blogs-title">
                                                        <a data-testid="blogs-link" href="<?php the_permalink(); ?>">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h3>

                                                    <p data-testid="blogs-author">By
                                                        <?php echo get_the_author(); ?>
                                                    </p>

                                                    <p data-testid="blogs-date">
                                                        <?php echo get_the_date('j F Y'); ?>
                                                    </p>
                                                </div>
                                                <?php
                                            endwhile;
                                        endif;
                                        wp_reset_postdata();
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="see-more-link-homepage" data-testid="blogs-see-more">
                                <svg data-testid="blogs-see-more-icon" xmlns="http://www.w3.org/2000/svg" width="16"
                                    height="22" fill="currentColor" class="bi bi-chevron-right govuk-!-padding-top-1"
                                    viewBox="0 0 16 16" style="stroke: currentColor;;">
                                    <path fill-rule="evenodd"
                                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                                </svg>

                                <p data-testid="blogs-see-more-text">
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