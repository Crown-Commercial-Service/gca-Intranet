<?php get_header(); ?>

<?php
get_template_part('template-parts/hero', null, [
  'title'     => 'Event',
  'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '',
]);

get_template_part('template-parts/breadcrumbs');
?>



<div class="govuk-width-container" data-testid="event-container">
    <main class="govuk-main-wrapper" id="main-content" data-testid="event-main">
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
                <?php
                    // 1. Retrieve raw data
                    $raw_start_date = get_field('start_date');
                    $raw_start_time = get_field('start_time'); // No fallback here so we can check if it exists
                    $raw_end_date   = get_field('end_date');
                    $raw_end_time   = get_field('end_time');

                    $date_string = '';
                    $time_string = '';

                    if ($raw_start_date) {
                        // Basic setup for Start
                        $start_ts = strtotime($raw_start_date . ($raw_start_time ? " $raw_start_time" : ""));
                        $date_string = date('j F Y', $start_ts);

                        // --- TIME STRING LOGIC ---
                        if ($raw_start_time && $raw_end_time) {
                            // Scenario: Both times present
                            $time_string = date('g:i a', strtotime($raw_start_time)) . ' to ' . date('g:i a', strtotime($raw_end_time));
                        } elseif ($raw_start_time) {
                            // Scenario: Only start time
                            $time_string = date('g:i a', strtotime($raw_start_time));
                        } elseif ($raw_end_time) {
                            // Scenario: Only end time (rare but handled)
                            $time_string = 'Until ' . date('g:i a', strtotime($raw_end_time));
                        }

                        // --- DATE STRING LOGIC (Ranges) ---
                        if ($raw_end_date) {
                            $end_ts = strtotime($raw_end_date);

                            // Only append "to [End Date]" if it's actually a different day
                            if (date('Ymd', $start_ts) !== date('Ymd', $end_ts)) {
                                $date_string .= ' to ' . date('j F Y', $end_ts);
                            }
                        }
                    }
                ?>
                <p class="govuk-body" data-testid="events-date">
                    <strong class="govuk-!-font-weight-bold">Date:</strong>
                     <?php echo esc_html($date_string); ?>
                </p>

                <?php
                    if ($time_string) : ?>
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