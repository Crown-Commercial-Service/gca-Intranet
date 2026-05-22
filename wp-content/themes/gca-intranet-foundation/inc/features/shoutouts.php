<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Community Shout-outs
//
// Custom post type + REST API for shout-outs in the Community Hub feed.
//
// Routes (all require authentication):
//
//   GET    /wp-json/gca/v1/shoutouts                         – paginated list
//   POST   /wp-json/gca/v1/shoutouts                         – create shout-out
//   DELETE /wp-json/gca/v1/shoutouts/{shoutout_id}           – delete own
//   GET    /wp-json/gca/v1/shoutouts/users?search=…          – user autocomplete
// ---------------------------------------------------------------------------

gca_register_feature_flag('community-shoutouts', [
    'label'       => 'Community Shout-outs',
    'description' => 'Enables the Shout-outs section in the Community Hub.',
    'default'     => true,
    'tags'        => ['social', 'community'],
]);

const GCA_SHOUTOUT_RECIPIENT_META = '_gca_shoutout_recipient_id';

// ---------------------------------------------------------------------------
// Custom post type
// ---------------------------------------------------------------------------

add_action('init', function (): void {
    if (!gca_flag_enabled('community-shoutouts')) {
        return;
    }

    register_post_type('community_shoutout', [
        'label'  => 'Shout-outs',
        'labels' => [
            'name'               => 'Shout-outs',
            'singular_name'      => 'Shout-out',
            'add_new_item'       => 'Add New Shout-out',
            'edit_item'          => 'View Shout-out',
            'search_items'       => 'Search Shout-outs',
            'not_found'          => 'No shout-outs found',
            'not_found_in_trash' => 'No shout-outs in Trash',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'show_in_rest'       => false,
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'hierarchical'       => false,
        'supports'           => ['title', 'editor', 'author'],
        'has_archive'        => false,
        'rewrite'            => false,
        'query_var'          => false,
    ]);
});

// ---------------------------------------------------------------------------
// Admin – menu item
// ---------------------------------------------------------------------------

add_action('admin_menu', function (): void {
    if (!gca_flag_enabled('community-shoutouts')) {
        return;
    }

    $counts = wp_count_posts('community_shoutout');
    $total  = (int) ($counts->publish ?? 0);

    $label = 'Shout-outs';
    if ($total > 0) {
        $label .= sprintf(
            ' <span class="awaiting-mod count-%d"><span class="pending-count">%d</span></span>',
            $total,
            $total
        );
    }

    add_menu_page(
        'Shout-outs',
        $label,
        'edit_posts',
        'edit.php?post_type=community_shoutout',
        '',
        'dashicons-awards',
        24
    );
}, 5);

// ---------------------------------------------------------------------------
// Admin – list columns
// ---------------------------------------------------------------------------

add_filter('manage_community_shoutout_posts_columns', function (array $cols): array {
    return [
        'cb'        => $cols['cb'] ?? '<input type="checkbox">',
        'title'     => 'Message',
        'author'    => 'Given by',
        'recipient' => 'Shouted out',
        'date'      => 'Date',
    ];
});

add_action('manage_community_shoutout_posts_custom_column', function (string $col, int $post_id): void {
    if ($col !== 'recipient') {
        return;
    }
    $recipient_id = (int) get_post_meta($post_id, GCA_SHOUTOUT_RECIPIENT_META, true);
    $recipient    = $recipient_id ? get_userdata($recipient_id) : null;
    echo $recipient instanceof WP_User ? esc_html($recipient->display_name) : '—';
}, 10, 2);

// ---------------------------------------------------------------------------
// Admin – metabox
// ---------------------------------------------------------------------------

add_action('add_meta_boxes', function (): void {
    if (!gca_flag_enabled('community-shoutouts')) {
        return;
    }

    add_meta_box(
        'gca-shoutout-details',
        'Shout-out Details',
        'gca_shoutout_details_metabox',
        'community_shoutout',
        'normal',
        'high'
    );
});

function gca_shoutout_details_metabox(WP_Post $post): void
{
    $recipient_id = (int) get_post_meta($post->ID, GCA_SHOUTOUT_RECIPIENT_META, true);
    $recipient    = $recipient_id ? get_userdata($recipient_id) : null;
    $giver        = get_userdata((int) $post->post_author);
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:130px;padding-top:8px">Given by</th>
            <td style="padding-top:8px">
                <?php echo $giver instanceof WP_User ? esc_html($giver->display_name) : '—'; ?>
            </td>
        </tr>
        <tr>
            <th style="padding-top:8px">Shouted out</th>
            <td style="padding-top:8px">
                <?php if ($recipient instanceof WP_User) : ?>
                    <strong><?php echo esc_html($recipient->display_name); ?></strong>
                    <?php
                    $team = trim((string) get_user_meta($recipient->ID, 'team', true));
                    if ($team) :
                    ?>
                        <em style="color:#666">(<?php echo esc_html($team); ?>)</em>
                    <?php endif; ?>
                <?php else : ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th style="padding-top:10px;vertical-align:top">Message</th>
            <td>
                <div style="background:#f6f7f7;border-left:4px solid #f5a623;padding:12px 16px;border-radius:0 4px 4px 0;line-height:1.6">
                    <?php echo nl2br(esc_html($post->post_content)); ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
}

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function gca_shoutout_format(WP_Post $post, int $current_user_id): array
{
    $recipient_id = (int) get_post_meta($post->ID, GCA_SHOUTOUT_RECIPIENT_META, true);
    $recipient    = $recipient_id ? get_userdata($recipient_id) : null;

    $recipient_name    = $recipient instanceof WP_User ? $recipient->display_name : '';
    $recipient_profile = '';
    if ($recipient instanceof WP_User && gca_flag_enabled('staff-profiles')) {
        $recipient_profile = esc_url(home_url('/profile/' . $recipient->user_nicename));
    }

    $giver       = get_userdata((int) $post->post_author);
    $avatar_url  = '';
    $profile_url = '';
    $giver_team  = '';

    if ($giver instanceof WP_User) {
        $local      = trim((string) get_user_meta($giver->ID, 'google_profile_picture_local_url', true));
        $avatar_url = $local ?: (string) get_avatar_url($giver->ID, ['size' => 48]);
        if (gca_flag_enabled('staff-profiles')) {
            $profile_url = esc_url(home_url('/profile/' . $giver->user_nicename));
        }
        $giver_team = trim((string) get_user_meta($giver->ID, 'team', true));
    }

    $likes_meta   = defined('GCA_LC_POST_LIKES_META') ? GCA_LC_POST_LIKES_META : '_gca_lc_post_likes';
    $comment_type = defined('GCA_LC_COMMENT_TYPE')    ? GCA_LC_COMMENT_TYPE    : 'gca_comment';

    $liked_by = array_filter(array_map('intval', (array) get_post_meta($post->ID, $likes_meta, true)));

    $comment_count = (int) get_comments([
        'post_id' => $post->ID,
        'type'    => $comment_type,
        'status'  => 'approve',
        'count'   => true,
    ]);

    return [
        'id'                => $post->ID,
        'type'              => 'shoutout',
        'content_html'      => nl2br(esc_html($post->post_content)),
        'content_raw'       => $post->post_content,
        'giver_id'          => (int) $post->post_author,
        'giver_name'        => $giver instanceof WP_User ? $giver->display_name : '',
        'giver_avatar'      => $avatar_url,
        'giver_profile'     => $profile_url,
        'giver_team'        => $giver_team,
        'recipient_id'      => $recipient_id,
        'recipient_name'    => $recipient_name,
        'recipient_profile' => $recipient_profile,
        'date_iso'          => (string) get_post_time('c', true, $post),
        'date_formatted'    => (string) get_post_time('j F Y', false, $post),
        'is_own'            => (int) $post->post_author === $current_user_id,
        'like_count'        => count($liked_by),
        'user_has_liked'    => in_array($current_user_id, $liked_by, true),
        'comment_count'     => $comment_count,
    ];
}

// ---------------------------------------------------------------------------
// REST routes
// ---------------------------------------------------------------------------

add_action('rest_api_init', function (): void {
    if (!gca_flag_enabled('community-shoutouts')) {
        return;
    }

    // User search for autocomplete
    register_rest_route('gca/v1', '/shoutouts/users', [
        'methods'             => 'GET',
        'callback'            => 'gca_shoutout_search_users',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'search' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) >= 2,
            ],
        ],
    ]);

    // Paginated list
    register_rest_route('gca/v1', '/shoutouts', [
        [
            'methods'             => 'GET',
            'callback'            => 'gca_shoutout_list',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'page'     => ['default' => 1,  'sanitize_callback' => 'absint'],
                'per_page' => ['default' => 15, 'sanitize_callback' => 'absint'],
            ],
        ],
        [
            'methods'             => 'POST',
            'callback'            => 'gca_shoutout_create',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'recipient_id' => [
                    'required'          => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => fn ($v) => is_numeric($v) && (bool) get_userdata((int) $v),
                ],
                'content' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) > 0 && mb_strlen($v) <= 1000,
                ],
            ],
        ],
    ]);

    register_rest_route('gca/v1', '/shoutouts/(?P<shoutout_id>\d+)', [
        'methods'             => 'DELETE',
        'callback'            => 'gca_shoutout_delete',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'shoutout_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);
});

// ---------------------------------------------------------------------------
// Callbacks
// ---------------------------------------------------------------------------

function gca_shoutout_search_users(WP_REST_Request $req): WP_REST_Response
{
    $search     = (string) $req->get_param('search');
    $current_id = get_current_user_id();

    $users = get_users([
        'search'         => '*' . $search . '*',
        'search_columns' => ['display_name', 'user_email', 'user_login'],
        'number'         => 10,
        'exclude'        => [$current_id],
        'orderby'        => 'display_name',
        'order'          => 'ASC',
    ]);

    $results = [];
    foreach ($users as $user) {
        if (!$user instanceof WP_User) {
            continue;
        }
        $local      = trim((string) get_user_meta($user->ID, 'google_profile_picture_local_url', true));
        $avatar_url = $local ?: (string) get_avatar_url($user->ID, ['size' => 40]);
        $team       = trim((string) get_user_meta($user->ID, 'team', true));

        $results[] = [
            'id'     => $user->ID,
            'name'   => $user->display_name,
            'team'   => $team,
            'avatar' => $avatar_url,
        ];
    }

    return new WP_REST_Response($results);
}

function gca_shoutout_list(WP_REST_Request $req): WP_REST_Response
{
    $page     = max(1, (int) $req->get_param('page'));
    $per_page = min(50, max(1, (int) $req->get_param('per_page')));
    $uid      = get_current_user_id();

    $query = new WP_Query([
        'post_type'      => 'community_shoutout',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false,
    ]);

    $shoutouts = [];
    foreach ($query->posts as $post) {
        if ($post instanceof WP_Post) {
            $shoutouts[] = gca_shoutout_format($post, $uid);
        }
    }

    return new WP_REST_Response([
        'shoutouts'   => $shoutouts,
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'page'        => $page,
    ]);
}

function gca_shoutout_create(WP_REST_Request $req): WP_REST_Response
{
    $uid          = get_current_user_id();
    $recipient_id = (int) $req->get_param('recipient_id');
    $content      = (string) $req->get_param('content');

    $recipient = get_userdata($recipient_id);
    if (!$recipient instanceof WP_User) {
        return new WP_REST_Response(['error' => 'Recipient not found'], 404);
    }

    if ($recipient_id === $uid) {
        return new WP_REST_Response(['error' => 'You cannot shout yourself out'], 422);
    }

    $post_id = wp_insert_post([
        'post_type'    => 'community_shoutout',
        'post_status'  => 'publish',
        'post_title'   => wp_trim_words($content, 10, '…'),
        'post_content' => $content,
        'post_author'  => $uid,
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(['error' => $post_id->get_error_message()], 500);
    }

    update_post_meta($post_id, GCA_SHOUTOUT_RECIPIENT_META, $recipient_id);

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return new WP_REST_Response(['error' => 'Could not retrieve created shout-out'], 500);
    }

    return new WP_REST_Response(gca_shoutout_format($post, $uid), 201);
}

function gca_shoutout_delete(WP_REST_Request $req): WP_REST_Response
{
    $shoutout_id = (int) $req->get_param('shoutout_id');
    $uid         = get_current_user_id();

    $post = get_post($shoutout_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'community_shoutout') {
        return new WP_REST_Response(['error' => 'Shout-out not found'], 404);
    }

    if ((int) $post->post_author !== $uid && !current_user_can('delete_others_posts')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    wp_delete_post($shoutout_id, true);
    return new WP_REST_Response(['deleted' => true]);
}
