<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
    'title'     => 'Work update',
    'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
]);

get_template_part('template-parts/breadcrumbs');
?>



<div class="govuk-width-container" data-testid="work-update-container">
    <main class="govuk-main-wrapper" id="main-content" data-testid="work-update-main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                
        <div class="govuk-grid-row">
            
            <div class="govuk-grid-column-one-third">
                <div class="profile_img_wrapper">
                    <?php 
                    $custome_author_img = get_field('image'); 
                    
                    if ($custome_author_img) : 
                        echo wp_get_attachment_image($custome_author_img, 'thumbnail', false, ['class' => 'avatar, profile_img']); 
                    else : 
                        if ($avatar = get_avatar(get_the_author_meta('ID'))) :
                            echo $avatar;
                        endif;
                    endif; 
                    ?>
                </div>
            </div>

            <div class="govuk-grid-column-two-thirds ">
                <h1 class="govuk-heading-m govuk-!-margin-bottom-1" data-testid="work-update-title">
                    <?php the_title(); ?>
                </h1>

                <div data-testid="work-update-detials">

                    <span>
                        By <?php echo esc_html(get_the_author()); ?>
                    </span>

                    
                    <div class="govuk-!-margin-bottom-5 govuk-!-margin-top-5" data-testid="work-update-date">
                        <span class="govuk-body-s govuk-!-margin-right-3">
                            <?php echo esc_html(get_the_date('jS F Y')); ?>
                        </span>
                        <?php
                        $terms = get_the_terms(get_the_ID(), 'label');

                        if ($terms && !is_wp_error($terms)) : $term = array_shift($terms); ?>

                            <span class="govuk-body-s tag_label" data-testid="work-update-tax">
                                <?php echo esc_html($term->name); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="govuk-body" data-testid="work-update-content">
                  <?php the_content(); ?>
                </div>
            </div>
        </div>

        <?php endwhile;
        endif; ?>
    </main>
</div>

<?php get_footer(); ?>