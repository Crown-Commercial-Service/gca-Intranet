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
    $logo_url  = get_theme_file_uri('assets/img/Government-Commercial-Agency-black-linear.png');
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
    $recipient = get_userdata($recipient_id);
    if (!$post instanceof WP_Post || !$giver instanceof WP_User || !$recipient instanceof WP_User) {
        return;
    }

    $giver_name = esc_html(html_entity_decode($giver->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $recipient_name = esc_html(html_entity_decode($recipient->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $giver_role = esc_html((string) get_user_meta($giver_id, 'business_title', true));
    
    $date = get_the_date('j F Y', $post);
    $message = nl2br(esc_html($post->post_content));
    
    $liked_by = (array) get_post_meta($post_id, '_gca_lc_post_likes', true);
    $like_count = count(array_filter(array_map('intval', $liked_by)));
    $comment_count = count(get_comments(['post_id' => $post_id, 'type' => 'gca_comment', 'status' => 'approve']));

    $role_html = $giver_role ? ' <span style="color:#505a5f;font-size:14px;">(' . $giver_role . ')</span>' : '';
    
    $body = '<div style="margin-bottom:12px;">'
        . '<strong style="font-size:16px;">' . $giver_name . '</strong>' . $role_html 
        . ' <span style="color:#505a5f;font-size:14px;">shouted out</span> '
        . '<strong style="font-size:16px;">' . $recipient_name . '</strong>'
        . ' <span style="color:#505a5f;font-size:14px;">on ' . $date . '</span>'
        . '</div>'
        . '<div style="margin:0 0 16px;color:#0b0c0c;font-size:16px;line-height:1.5;">' . $message . '</div>'
        . '<div style="font-size:14px;color:#505a5f;border-top:1px solid #e5e5e5;padding-top:12px;">'
        . '<span style="margin-right:16px;">👍 ' . $like_count . ' ' . _n('Like', 'Likes', $like_count, 'gca-intranet') . '</span>'
        . '<span>💬 ' . $comment_count . ' ' . _n('Comment', 'Comments', $comment_count, 'gca-intranet') . '</span>'
        . '</div>';

    gca_notify_send_email(
        $recipient_id,
        "You've received a shout out on the intranet",
        "You've received a shout out on the intranet",
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
    $answerer = get_userdata($answerer_id);
    if (!$post instanceof WP_Post || !$answerer instanceof WP_User) {
        return;
    }

    $answer_meta = defined('GCA_QA_ANSWER_META') ? GCA_QA_ANSWER_META : '_gca_qa_answer';
    $answer      = (string) get_post_meta($question_id, $answer_meta, true);

    $answerer_name = esc_html(html_entity_decode($answerer->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $answerer_role = esc_html((string) get_user_meta($answerer_id, 'business_title', true));
    
    $date = gmdate('j F Y');

    $liked_by = (array) get_post_meta($question_id, '_gca_lc_post_likes', true);
    $like_count = count(array_filter(array_map('intval', $liked_by)));
    $comment_count = count(get_comments(['post_id' => $question_id, 'type' => 'gca_comment', 'status' => 'approve']));

    $role_html = $answerer_role ? ' <span style="color:#505a5f;font-size:14px;">(' . $answerer_role . ')</span>' : '';
    
    $body = '<div style="margin-bottom:12px;">'
        . '<strong style="font-size:16px;">' . $answerer_name . '</strong>' . $role_html 
        . ' <span style="color:#505a5f;font-size:14px;">answered on ' . $date . '</span>'
        . '</div>'
        . '<div style="margin:0 0 12px;padding:12px;background:#f3f2f1;border-left:4px solid #b1b4b6;color:#505a5f;font-style:italic;">'
        . nl2br(esc_html($post->post_content))
        . '</div>'
        . '<div style="margin:0 0 16px;color:#0b0c0c;font-size:16px;line-height:1.5;">' . nl2br(esc_html($answer)) . '</div>'
        . '<div style="font-size:14px;color:#505a5f;border-top:1px solid #e5e5e5;padding-top:12px;">'
        . '<span style="margin-right:16px;">👍 ' . $like_count . ' ' . _n('Like', 'Likes', $like_count, 'gca-intranet') . '</span>'
        . '<span>💬 ' . $comment_count . ' ' . _n('Comment', 'Comments', $comment_count, 'gca-intranet') . '</span>'
        . '</div>';

    gca_notify_send_email(
        $asker_id,
        "Your question has been answered on the intranet",
        "Your question has been answered on the intranet",
        $body,
        gca_notify_community_hub_url('qa') . '#gca-qa-q-' . $post_id,
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

    $date = get_comment_date('j F Y', $comment);

    $plain_content = (string) preg_replace('/@\[([^\]]+)\]\(\d+\)/', '@$1', $comment->comment_content);
    $content_html  = nl2br(esc_html($plain_content));

    $post_id   = (int) $comment->comment_post_ID;
    $post_type = get_post_type($post_id);
    
    $tab = 'updates';
    $args = [];
    $hash = '#gca-lc-comment-' . $comment_id;

    if ($post_type === 'community_shoutout') {
        $tab = 'shoutouts';
        $args['shoutout_id'] = $post_id;
    } elseif ($post_type === 'qa_question') {
        $tab = 'qa';
        // Q&A comments are within the card, wait, is there an extra step? 
        // We'll just link to the comment hash
    } elseif ($post_type === 'community_poll') {
        $tab = 'polls';
    }

    $post_url = gca_notify_community_hub_url($tab, $args) . $hash;

    $body = '<div style="margin-bottom:12px;">'
        . '<strong style="font-size:16px;">' . $commenter_name . '</strong>'
        . ' <span style="color:#505a5f;font-size:14px;">mentioned you on ' . $date . '</span>'
        . '</div>'
        . '<div style="margin:0 0 16px;color:#0b0c0c;font-size:16px;line-height:1.5;">' . $content_html . '</div>';

    gca_notify_send_email(
        $mentioned_user_id,
        "You've been mentioned on the intranet",
        "You've been mentioned on the intranet",
        $body,
        $post_url ?: home_url('/'),
        'View the post'
    );
}, 10, 3);
