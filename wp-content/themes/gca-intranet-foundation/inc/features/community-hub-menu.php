<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Community Hub – shared admin menu parent
//
// Groups the Shout-outs, Q&A Questions, and Community Polls admin screens
// under a single "Community Hub" top-level menu item, so internal comms can
// find them in one place instead of three separate top-level entries.
//
// Registered at priority 4 so it runs before shoutouts.php / qa.php /
// polls.php (priority 5), which each attach their screen as a submenu of
// GCA_COMMUNITY_HUB_MENU_SLUG via add_submenu_page().
// ---------------------------------------------------------------------------

const GCA_COMMUNITY_HUB_MENU_SLUG = 'gca-community-hub';

add_action('admin_menu', function (): void {
    $has_shoutouts = gca_flag_enabled('community-shoutouts');
    $has_qa        = gca_flag_enabled('community-qa');
    $has_polls     = gca_flag_enabled('community-polls');

    if (!$has_shoutouts && !$has_qa && !$has_polls) {
        return;
    }

    add_menu_page(
        'Community Hub',
        'Community Hub',
        'edit_posts',
        GCA_COMMUNITY_HUB_MENU_SLUG,
        'gca_community_hub_menu_redirect',
        'dashicons-groups',
        24
    );
}, 4);

function gca_community_hub_menu_redirect(): void {
    if (gca_flag_enabled('community-shoutouts')) {
        wp_safe_redirect(admin_url('edit.php?post_type=community_shoutout'));
    } elseif (gca_flag_enabled('community-qa')) {
        wp_safe_redirect(admin_url('edit.php?post_type=qa_question'));
    } elseif (gca_flag_enabled('community-polls')) {
        wp_safe_redirect(admin_url('edit.php?post_type=community_poll'));
    } else {
        wp_safe_redirect(admin_url());
    }
    exit;
}
