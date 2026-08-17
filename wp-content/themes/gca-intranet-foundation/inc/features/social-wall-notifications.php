<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Social Wall – Email Notifications
//
// Sends an email when a user is shouted out, their Q&A question is answered,
// or they're @mentioned in a comment. Each type is independently toggle-able
// (default on) from the "Notification settings" tab on the user's own staff
// profile.
//
// Routes (all require authentication):
//
//   GET  /wp-json/gca/v1/profile/me/notification-settings
//   POST /wp-json/gca/v1/profile/me/notification-settings
// ---------------------------------------------------------------------------

gca_register_feature_flag('social-wall-notifications', [
    'label'       => 'Social Wall Email Notifications',
    'description' => 'Sends email notifications for shout-outs, Q&A answers, and comment mentions, per the recipient\'s profile preferences.',
    'default'     => true,
    'tags'        => ['social', 'community', 'email'],
]);

const GCA_NOTIFY_SHOUTOUTS_META = '_gca_notify_shoutouts';
const GCA_NOTIFY_QA_META        = '_gca_notify_qa';
const GCA_NOTIFY_MENTIONS_META  = '_gca_notify_mentions';

const GCA_NOTIFY_TYPES = [
    'shoutouts' => GCA_NOTIFY_SHOUTOUTS_META,
    'qa'        => GCA_NOTIFY_QA_META,
    'mentions'  => GCA_NOTIFY_MENTIONS_META,
];

// ---------------------------------------------------------------------------
// Preferences
// ---------------------------------------------------------------------------

/**
 * Every notification type defaults to enabled; unset user meta ('') counts as
 * enabled, only an explicit '0' turns a notification off.
 */
function gca_notify_preference_enabled(int $user_id, string $type): bool
{
    if (!isset(GCA_NOTIFY_TYPES[$type])) {
        return false;
    }
    return get_user_meta($user_id, GCA_NOTIFY_TYPES[$type], true) !== '0';
}

// ---------------------------------------------------------------------------
// REST routes
// ---------------------------------------------------------------------------

add_action('rest_api_init', function (): void {
    if (!gca_flag_enabled('social-wall-notifications')) {
        return;
    }

    register_rest_route('gca/v1', '/profile/me/notification-settings', [
        [
            'methods'             => 'GET',
            'callback'            => 'gca_notify_get_settings',
            'permission_callback' => 'is_user_logged_in',
        ],
        [
            'methods'             => 'POST',
            'callback'            => 'gca_notify_save_settings',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'type' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_key',
                    'validate_callback' => fn ($v) => isset(GCA_NOTIFY_TYPES[$v]),
                ],
                'enabled' => [
                    'required' => true,
                ],
            ],
        ],
    ]);
});

function gca_notify_get_settings(): WP_REST_Response
{
    $user_id = get_current_user_id();

    return new WP_REST_Response([
        'shoutouts' => gca_notify_preference_enabled($user_id, 'shoutouts'),
        'qa'        => gca_notify_preference_enabled($user_id, 'qa'),
        'mentions'  => gca_notify_preference_enabled($user_id, 'mentions'),
    ]);
}

function gca_notify_save_settings(WP_REST_Request $req): WP_REST_Response
{
    $user_id = get_current_user_id();
    $type    = (string) $req->get_param('type');
    $raw     = $req->get_param('enabled');
    $enabled = is_string($raw) ? filter_var($raw, FILTER_VALIDATE_BOOLEAN) : (bool) $raw;

    update_user_meta($user_id, GCA_NOTIFY_TYPES[$type], $enabled ? '1' : '0');

    return new WP_REST_Response(['saved' => true, 'enabled' => $enabled]);
}

// ---------------------------------------------------------------------------
// Community Hub URL helper
//
// community_shoutout and qa_question are non-public CPTs with no permalink of
// their own — they're rendered as feed tabs on whichever page uses the
// "Community Hub" page template. Link there (with a ?tab= hint) rather than
// to a post that doesn't exist as a public URL.
// ---------------------------------------------------------------------------

function gca_notify_community_hub_url(string $tab, array $extra_args = []): string
{
    static $page_id = null;

    if ($page_id === null) {
        $pages   = get_posts([
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'template-community-wall.php',
        ]);
        $page_id = $pages[0] ?? 0;
    }

    if (!$page_id) {
        return home_url('/');
    }

    return add_query_arg(
        array_merge(['tab' => $tab], $extra_args),
        (string) get_permalink($page_id)
    );
}

// ---------------------------------------------------------------------------
// Email builder
// ---------------------------------------------------------------------------

function gca_notify_email_html(string $heading, string $body_html, string $cta_url, string $cta_label, int $recipient_id): string
{
    $logo_id   = (int) get_theme_mod('custom_logo');
    $logo_url  = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : false;
    $site_name = esc_html(get_bloginfo('name'));

    $recipient = get_userdata($recipient_id);
    $prefs_url = $recipient instanceof WP_User
        ? esc_url(add_query_arg('tab', 'notifications', home_url('/profile/' . rawurlencode($recipient->user_login) . '/')))
        : esc_url(home_url('/'));

    ob_start();
    ?>
<div style="background:#f3f2f1;padding:32px 16px;font-family:Arial, Helvetica, sans-serif;">
  <div style="max-width:480px;margin:0 auto;">
    <div style="text-align:center;padding-bottom:24px;">
      <?php if ($logo_url) : ?>
        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo $site_name; ?>" style="max-height:40px;">
      <?php else : ?>
        <strong><?php echo $site_name; ?></strong>
      <?php endif; ?>
    </div>
    <div style="background:#ffffff;border-radius:4px;padding:32px;text-align:center;">
      <h1 style="font-size:19px;margin:0 0 20px;color:#0b0c0c;"><?php echo esc_html($heading); ?></h1>
      <div style="text-align:left;background:#f8f8f8;border:1px solid #e5e5e5;border-radius:4px;padding:16px;margin-bottom:20px;">
        <?php echo $body_html; ?>
      </div>
      <a href="<?php echo esc_url($cta_url); ?>" style="color:#1d70b8;font-weight:600;text-decoration:underline;"><?php echo esc_html($cta_label); ?></a>
    </div>
    <p style="text-align:center;font-size:13px;color:#505a5f;margin-top:20px;">
      You can change your <a href="<?php echo $prefs_url; ?>" style="color:#1d70b8;">email notification preferences</a> in your intranet profile.
    </p>
  </div>
</div>
    <?php
    return (string) ob_get_clean();
}

function gca_notify_send_email(int $recipient_id, string $subject, string $heading, string $body_html, string $cta_url, string $cta_label): void
{
    $recipient = get_userdata($recipient_id);
    if (!$recipient instanceof WP_User || !$recipient->user_email) {
        return;
    }

    $set_html_type = static fn () => 'text/html';
    add_filter('wp_mail_content_type', $set_html_type);

    wp_mail(
        $recipient->user_email,
        $subject,
        gca_notify_email_html($heading, $body_html, $cta_url, $cta_label, $recipient_id)
    );

    remove_filter('wp_mail_content_type', $set_html_type);
}

// ---------------------------------------------------------------------------
// Event listeners
// ---------------------------------------------------------------------------

add_action('gca_shoutout_created', function (int $post_id, int $recipient_id, int $giver_id): void {
    if (!gca_flag_enabled('social-wall-notifications') || !gca_notify_preference_enabled($recipient_id, 'shoutouts')) {
        return;
    }

    $post  = get_post($post_id);
    $giver = get_userdata($giver_id);
    if (!$post instanceof WP_Post || !$giver instanceof WP_User) {
        return;
    }

    $giver_name = esc_html(html_entity_decode($giver->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $message    = nl2br(esc_html($post->post_content));

    $body = '<p style="margin:0 0 8px;"><strong>' . $giver_name . '</strong> shouted you out</p>'
        . '<p style="margin:0;color:#0b0c0c;">' . $message . '</p>';

    gca_notify_send_email(
        $recipient_id,
        "You've received a shout-out",
        "You've received a shout-out on the intranet",
        $body,
        gca_notify_community_hub_url('shoutouts', ['shoutout_id' => $post_id]),
        'View your shout-out'
    );
}, 10, 3);

add_action('gca_qa_answered', function (int $question_id, int $asker_id, int $answerer_id): void {
    if ($asker_id === $answerer_id) {
        return;
    }
    if (!gca_flag_enabled('social-wall-notifications') || !gca_notify_preference_enabled($asker_id, 'qa')) {
        return;
    }

    $post = get_post($question_id);
    if (!$post instanceof WP_Post) {
        return;
    }

    $answer_meta = defined('GCA_QA_ANSWER_META') ? GCA_QA_ANSWER_META : '_gca_qa_answer';
    $answer      = (string) get_post_meta($question_id, $answer_meta, true);

    $body = '<p style="margin:0 0 8px;"><em style="color:#505a5f;">' . nl2br(esc_html($post->post_content)) . '</em></p>'
        . '<p style="margin:0;color:#0b0c0c;">' . nl2br(esc_html($answer)) . '</p>';

    gca_notify_send_email(
        $asker_id,
        'Your question has been answered',
        'Your question has been answered',
        $body,
        gca_notify_community_hub_url('qa'),
        'View the answer'
    );
}, 10, 3);

add_action('gca_comment_mention_created', function (int $comment_id, int $mentioned_user_id, int $commenter_id): void {
    if (!gca_flag_enabled('social-wall-notifications') || !gca_notify_preference_enabled($mentioned_user_id, 'mentions')) {
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment instanceof WP_Comment) {
        return;
    }

    $commenter      = get_userdata($commenter_id);
    $commenter_name = $commenter instanceof WP_User
        ? esc_html(html_entity_decode($commenter->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'))
        : 'Someone';

    $plain_content = (string) preg_replace('/@\[([^\]]+)\]\(\d+\)/', '@$1', $comment->comment_content);
    $content_html  = nl2br(esc_html($plain_content));

    $post_url = get_permalink((int) $comment->comment_post_ID);

    $body = '<p style="margin:0 0 8px;"><strong>' . $commenter_name . '</strong> mentioned you in a comment</p>'
        . '<p style="margin:0;color:#0b0c0c;">' . $content_html . '</p>';

    gca_notify_send_email(
        $mentioned_user_id,
        'You were mentioned in a comment',
        'You were mentioned in a comment',
        $body,
        $post_url ?: home_url('/'),
        'View the comment'
    );
}, 10, 3);
