<?php get_header(); ?>

<?php
$hero_image_url = get_template_directory_uri() . '/assets/img/events.jpg';

get_template_part('template-parts/hero', null, [
  'title'     => 'Event',
  'image_url' => $hero_image_url,
]);

get_template_part('template-parts/breadcrumbs');
?>



<div class="govuk-width-container" data-testid="event-container">
    <main class="govuk-main-wrapper" id="main-content" tabindex="-1" data-testid="event-main">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>


            <h1 class="govuk-heading-m" data-testid="event-title">
                <?php the_title(); ?>
            </h1>


            <div class="govuk-!-margin-bottom-5 govuk-!-margin-top-5" data-testid="event-tax">
                <?php
                $categories = get_the_category();
                $locations = get_the_terms(get_the_ID(), 'event_location');

                if ($categories && $categories[0]->name !== 'Uncategorized') : ?>
                    <span class="govuk-body tag_label">
                        <?php echo esc_html($categories[0]->name); ?>
                    </span>
                <?php endif;


                if ($locations) : ?>
                    <span class="govuk-body-s tag_label grey">
                        <?php echo esc_html($locations[0]->name); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="event-details" data-testid="event-details">
                <p class="govuk-body" data-testid="events-date">
                    <strong class="govuk-!-font-weight-bold">
                        Date:
                    </strong>
                    <?php echo esc_html(gca_get_event_datetime('dates')); ?>
                </p>

                <?php if ($time_string = gca_get_event_datetime('times')) : ?>
                    <p class="govuk-body govuk-!-margin-left-5" data-testid="events-time">
                        <strong class="govuk-!-font-weight-bold">Time:</strong>
                        <?php echo esc_html($time_string); ?>
                    </p>
                <?php endif; ?>

                <?php
                    $cta_label = get_field('secondary_cta_label');
                    $cta_url   = get_field('secondary_cta_destination');

                    if ($cta_label && $cta_url) : ?>
                    <div class="govuk-!-margin-left-5 govuk-!-margin-bottom-0">
                        <a href="<?php echo esc_url($cta_url); ?>"
                            class="govuk-button event_cta_button govuk-!-margin-bottom-0" data-module="govuk-button" data-testid="event-cta-button">
                                <?php echo esc_html($cta_label); ?>
                                <svg class="govuk-!-padding-left-2" xmlns="http://www.w3.org/2000/svg" width="17.5" height="19" viewBox="0 0 33 40" aria-hidden="true" focusable="false">
                                <path fill="currentColor" d="M0 0h13l20 20-20 20H0l20-20z" />
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="govuk-body" data-testid="event-content">
              <?php the_content(); ?>
            </div>

        <?php endwhile; endif; ?>
    </main>
</div>

<?php get_footer(); ?>