<?php get_header(); ?>

<?php
$hero_image_url = get_template_directory_uri() . '/assets/img/blogs.jpg';

get_template_part('template-parts/hero', null, [
    'title'     => 'Blog',
    'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>



<div class="govuk-width-container blogs" data-testid="blog-container">
    <main class="govuk-main-wrapper" id="main-content" data-testid="blog-main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                
        <div class="govuk-grid-row">
            
            <div class="govuk-grid-column-one-third">
                <div class="profile_img_wrapper">
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
            </div>

            <div class="govuk-grid-column-two-thirds ">
                <h1 class="govuk-heading-m govuk-!-margin-bottom-1" data-testid="blog-title">
                    <?php the_title(); ?>
                </h1>

                <div data-testid="blog-detials">

                    <span>
                        By <?php echo esc_html(get_the_author()); ?>
                    </span>

                    
                    <div class="govuk-!-margin-bottom-5 govuk-!-margin-top-5" data-testid="blog-date">
                        <span class="govuk-body-s govuk-!-margin-right-3">
                            <?php echo esc_html(get_the_date('j F Y')); ?>
                        </span>
                        <?php
                        $terms = get_the_terms(get_the_ID(), 'label');

                        if ($terms && !is_wp_error($terms)) : $term = array_shift($terms); ?>

                            <span class="govuk-body-s tag_label" data-testid="blog-tax">
                                <?php echo esc_html($term->name); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="govuk-body" data-testid="blog-content">
                  <?php the_content(); ?>
                </div>
            </div>
        </div>

        <?php endwhile;
        endif; ?>
    </main>
</div>

<?php get_footer(); ?>