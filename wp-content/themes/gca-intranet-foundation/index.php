<?php get_header(); ?>

<main id="main-content" class="container py-4" role="main">

    <div class="row g-0 mb-3">
        <div class="col-8">
            <h2 class="fw-bold m-0">Lastest news</h2>
            <p class="text-muted">What's happening in our organisation</p>
            <hr class="me-3 border opacity-50" />
        </div>
        <div class="col-4">
            <h2 class="fw-bold m-0">Take a look</h2>
            <p class="text-muted">Hello world</p>
            <hr class="border opacity-50" />
        </div>
    </div>

    <div class="row g-0 align-items-stretch">
        
        <div class="col-sm-4 gca-featured-news d-flex flex-column pb-3">
            <?php
            $latest_post = new WP_Query(array('posts_per_page' => 1));
            if ($latest_post->have_posts()) : while ($latest_post->have_posts()) : $latest_post->the_post();
            ?>
                <?php if (has_post_thumbnail()) : ?>
                    <img class="w-100 rounded-top object-fit-cover" 
                         src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" 
                         style="height: 250px;" 
                         alt="<?php the_title(); ?>">
                <?php endif; ?>

                <div class="p-3 flex-grow-1">
                    <h3 class="h4"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p class="small"><?php echo wp_trim_words(get_the_excerpt(), 32, '...'); ?></p>
                </div>
                <div class="px-3 mt-auto">
                    <p class="small m-0"><?php echo get_the_date('jS F Y'); ?></p>
                </div>
            <?php endwhile; endif; wp_reset_postdata(); ?>
        </div>

        <div class="col-sm-4 d-flex flex-column px-3"> 
            <?php
            $secondary_posts = new WP_Query(array('posts_per_page' => 3, 'offset' => 1));
            if ($secondary_posts->have_posts()) : while ($secondary_posts->have_posts()) : $secondary_posts->the_post();

            $is_last = (($secondary_posts->current_post + 1) == $secondary_posts->post_count);
            $margin_bottom_class = $is_last ? '' : 'mb-3';
            ?>

                <div class="row g-0 <?php echo $margin_bottom_class; ?> flex-grow-1 border-bottom">
                    <div class="col-4">
                        <?php if (has_post_thumbnail()) : ?>
                            <img class="rounded w-100 object-fit-cover" 
                                 src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
                                 alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col ps-3">
                        <h4 class="h6 mb-1 pb-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <p class="small"><?php echo wp_trim_words(get_the_excerpt(), 12, '...'); ?></p>
                        <p class="m-0 small "><?php echo get_the_date('jS F Y'); ?></p>
                    </div>
                </div>
            <?php endwhile; endif; wp_reset_postdata(); ?>
        </div>
        
        
        <div class="col-sm-4 p-3 d-flex flex-column" style="background-color: #7fffd4;">
            <div class="h-100">
                <p>Additional organization content or widgets...</p>
            </div>
        </div>

    </div>

    <!-- New row - content below second column -->
    <div class="row g-0">
        <div class="text-end col-sm-4 offset-sm-4 px-3 mt-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" fill="currentColor" 
                class="bi bi-chevron-right" viewBox="0 0 16 16" 
                style="stroke: currentColor; stroke-width: 1.8;">  
            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
            </svg>
            <a href="#" class=""> Browse all news articles</a>
        </div>
    </div>

</main>

<?php get_footer(); ?>