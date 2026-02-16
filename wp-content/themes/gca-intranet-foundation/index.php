<?php get_header(); ?>

<main id="main-content" class="container  py-4" role="main">

    <div class="row">
        <div class="col-8 g-0">
            <h2>Lastest news</h2>
            <p>What's happening in our organisation</p>
            <hr class="me-3 hr" />

        </div>
        <div class="col-4 g-0">
            <h2>Take a look</h2>
            <p>Hello world</p>
            <hr class="hr" />

        </div>
    </div>

    <div class="row">
        <div class="col-sm g-0 gca-card">
            <?php
                $latest_post_args = array(
                    'posts_per_page' => 1, 
                );
                    
                $latest_post = new WP_Query( $latest_post_args );
                    
                if ( $latest_post->have_posts() ) {
                    while ( $latest_post->have_posts() ) {
                        $latest_post->the_post();
                        ?>
                        
                    <?php if ( has_post_thumbnail() ) { ?>
                        <img class="w-100 rounded-top" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>">
                    <?php } ?>

                    <div class="p-3">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p>
                            <?php 
                            $excerpt = get_the_excerpt(); 
                            if (mb_strlen($excerpt) > 120) {
                                echo mb_substr($excerpt, 0, 120) . '...';
                            } else {
                                echo $excerpt;
                            }
                            ?>
                        </p>
                        <?php

                        the_date('jS F Y');

                    }
                }
                wp_reset_postdata();
            ?>
        </div>
    </div>

    <div class="col-sm">
        <!-- One of three columns -->
    </div>
    <div class="col-sm">
        <!-- One of three columns -->
    </div>
    </div>
</div>

</main>

<?php get_footer(); ?>