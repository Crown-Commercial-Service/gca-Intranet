<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// Site-wide Announcement Banner
//
// Displays a configurable announcement banner at the top of every page.
// Toggle on/off and edit content under WP Admin → Global Settings → Announcement.
// Supports links (rendered underlined per GCA branding).
// -----------------------------------------------------------------------------

gca_register_feature_flag('site-wide-announcement', [
    'label'       => 'Site-wide Announcement Banner',
    'description' => 'Enables the site-wide announcement banner. Configure content and visibility under Global Settings → Announcement.',
    'default'     => false,
    'tags'        => ['ui', 'communications'],
]);

add_action('wp_body_open', function (): void {
    if (!gca_flag_enabled('site-wide-announcement')) {
        return;
    }

    if (!get_option('gca_announcement_enabled')) {
        return;
    }

    $content = (string) get_option('gca_announcement_content', '');

    if ('' === trim(wp_strip_all_tags($content))) {
        return;
    }
    ?>
    <div class="gca-announcement-banner" role="region" aria-label="<?php esc_attr_e('Site announcement', 'gca-intranet'); ?>">
        <div class="gca-announcement-banner__inner govuk-width-container">
            <div class="gca-announcement-banner__content govuk-body">
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
    </div>
    <?php
}, 10);
