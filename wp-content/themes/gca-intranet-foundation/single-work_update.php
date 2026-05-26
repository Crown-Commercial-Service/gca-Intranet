<?php get_header(); ?>

<?php
$hero_image_url = gca_get_banner_url('gca_banner_work_updates', 'work_updates.jpg');

get_template_part('template-parts/hero', null, [
    'title'     => 'Work update',
    'image_url' => $hero_image_url
]);

get_template_part('template-parts/breadcrumbs');
?>



<div class="govuk-width-container work-updates-container" data-testid="work-update-container">
    <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="work-update-main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                
        <div class="govuk-grid-row gca-author-grid">

            <div class="govuk-grid-column-one-third">
                <div class="profile_img_wrapper">
                    <?php echo gca_get_author_image_html( get_the_ID(), (int) get_the_author_meta( 'ID' ) ); ?>
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
                        <span class="govuk-body-s govuk-!-margin-right-3" style="margin:0;">
                            <?php echo esc_html(get_the_date('j F Y')); ?>
                        </span>
                        <?php
                        $terms = get_the_terms(get_the_ID(), 'label');
                        $responsible_teams = get_the_terms(get_the_ID(), 'responsible_team');

                        if ($terms && !is_wp_error($terms)) : $term = array_shift($terms); ?>
                            <span class="govuk-body-s tag_label" data-testid="work-update-tax" style="margin:0;">
                                <?php echo esc_html($term->name); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php
                        if ($responsible_teams && !is_wp_error($responsible_teams)) : $responsible_team = array_shift($responsible_teams); ?>
                            <span class="govuk-body-s tag_label grey" data-testid="work-update-team" style="margin:0;">
                                <?php echo esc_html($responsible_team->name); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="govuk-body" data-testid="work-update-content">
                  <?php the_content(); ?>
                </div>

                <?php if (gca_flag_enabled('likes-and-comments')) : ?>
                    <?php get_template_part('template-parts/likes-and-comments'); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php endwhile;
        endif; ?>

        <?php get_template_part( 'template-parts/related-articles' ); ?>
    </main>
</div>

<?php get_footer(); ?>