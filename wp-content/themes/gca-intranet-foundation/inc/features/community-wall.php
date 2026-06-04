<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Community Hub
//
// Custom post type + REST API for the social community feed.
//
// Routes (all require authentication):
//
//   GET    /wp-json/gca/v1/community/feed            – paginated mixed feed
//   POST   /wp-json/gca/v1/community/posts           – create a post (community hosts only)
//   DELETE /wp-json/gca/v1/community/posts/{post_id} – delete own post (admins delete any)
// ---------------------------------------------------------------------------

gca_register_feature_flag('community-hub', [
    'label'       => 'Community Hub',
    'description' => 'Enables the Community Hub page where staff can post updates, share content, and engage with colleagues.',
    'default'     => true,
    'tags'        => ['social', 'community'],
]);

/** Meta key storing media attachment IDs for a community post. */
const GCA_CW_MEDIA_IDS_META = '_gca_cw_media_ids';

// ---------------------------------------------------------------------------
// Shared helpers (used by shoutouts, qa, polls feature files too)
// ---------------------------------------------------------------------------

/**
 * Returns true if the given user (defaults to current user) is a community
 * host or admin. Community hosts can post and create polls.
 */
function gca_cw_is_community_host(?int $user_id = null): bool
{
    $user_id = $user_id ?? get_current_user_id();
    if (!$user_id) {
        return false;
    }
    return user_can($user_id, 'manage_options') || user_can($user_id, 'community_host_access');
}

/**
 * Register the Community Host role (idempotent – safe to call on every load).
 * The role is intentionally minimal: read access plus the custom cap that
 * gates post/poll creation on the Community Wall.
 */
function gca_cw_register_community_host_role(): void
{
    if (!get_role('community_host')) {
        add_role('community_host', 'Community Host', [
            'read'                  => true,
            'community_host_access' => true,
        ]);
    }

    // Grant the cap to existing admins so they always pass the host check.
    $admin_role = get_role('administrator');
    if ($admin_role instanceof WP_Role && !$admin_role->has_cap('community_host_access')) {
        $admin_role->add_cap('community_host_access', true);
    }
}
add_action('init', 'gca_cw_register_community_host_role');

/**
 * Returns author-info fields for a given user ID, ready for API responses.
 */
function gca_cw_get_user_info(int $user_id, int $avatar_size = 48): array
{
    $author = get_userdata($user_id);
    if (!($author instanceof WP_User)) {
        return [
            'author_id'      => $user_id,
            'author_name'    => '',
            'author_avatar'  => '',
            'author_profile' => '',
            'author_team'    => '',
        ];
    }

    $local       = trim((string) get_user_meta($author->ID, 'google_profile_picture_local_url', true));
    $avatar_url  = $local ?: (string) get_avatar_url($author->ID, ['size' => $avatar_size]);
    $profile_url = '';
    if (gca_flag_enabled('staff-profiles')) {
        $profile_url = esc_url(home_url('/profile/' . $author->user_nicename));
    }

    return [
        'author_id'      => $user_id,
        'author_name'    => $author->display_name,
        'author_avatar'  => $avatar_url,
        'author_profile' => $profile_url,
        'author_team'    => trim((string) get_user_meta($author->ID, 'business_title', true)),
    ];
}

/**
 * Returns like + comment counts for any post, ready to merge into a response.
 */
function gca_cw_get_lc_data(int $post_id, int $current_user_id): array
{
    $likes_meta   = defined('GCA_LC_POST_LIKES_META') ? GCA_LC_POST_LIKES_META : '_gca_lc_post_likes';
    $comment_type = defined('GCA_LC_COMMENT_TYPE')    ? GCA_LC_COMMENT_TYPE    : 'gca_comment';

    $liked_by = array_filter(array_map('intval', (array) get_post_meta($post_id, $likes_meta, true)));

    $comment_count = (int) get_comments([
        'post_id' => $post_id,
        'type'    => $comment_type,
        'status'  => 'approve',
        'count'   => true,
    ]);

    return [
        'like_count'     => count($liked_by),
        'user_has_liked' => in_array($current_user_id, $liked_by, true),
        'comment_count'  => $comment_count,
    ];
}

/**
 * Escape content for display: nl2br + linkify @mentions and #hashtags.
 */
function gca_cw_render_content(string $raw): string
{
    $html = nl2br(esc_html($raw));

    $html = (string) preg_replace_callback(
        '/@\[([^\]]+)\]\((\d+)\)/',
        function (array $m): string {
            $display = $m[1]; // already HTML-escaped by esc_html above
            $user_id = (int) $m[2];
            $user    = get_userdata($user_id);
            if (!$user) {
                return '@' . $display;
            }
            $url = esc_url(home_url('/profile/' . $user->user_nicename));
            return '<a href="' . $url . '" class="gca-lc__mention">@' . $display . '</a>';
        },
        $html
    );

    return (string) preg_replace_callback(
        '/#([a-zA-Z0-9_]+)/',
        fn ($m) => '<span class="gca-cw__hashtag">#' . esc_html($m[1]) . '</span>',
        $html
    );
}

/**
 * Format a community_post for JSON responses.
 */
function gca_cw_format_post(WP_Post $post, int $current_user_id): array
{
    $author_info = gca_cw_get_user_info((int) $post->post_author);

    $raw_ids   = get_post_meta($post->ID, GCA_CW_MEDIA_IDS_META, true);
    $media_ids = array_filter(array_map('absint', (array) $raw_ids));
    $media     = [];
    foreach ($media_ids as $mid) {
        $url = wp_get_attachment_url($mid);
        if (!$url) {
            continue;
        }
        $mime    = (string) get_post_mime_type($mid);
        $type    = strpos($mime, 'video') === 0 ? 'video' : 'image';
        $alt     = trim((string) get_post_meta($mid, '_wp_attachment_image_alt', true));
        $media[] = ['id' => $mid, 'url' => $url, 'type' => $type, 'alt' => $alt];
    }

    $lc = gca_cw_get_lc_data($post->ID, $current_user_id);

    return array_merge($author_info, $lc, [
        'id'             => $post->ID,
        'kind'           => 'post',
        'content_html'   => gca_cw_render_content($post->post_content),
        'content_raw'    => $post->post_content,
        'date_iso'       => (string) get_post_time('c', true, $post),
        'date_formatted' => (string) get_post_time('j F Y', false, $post),
        'media'          => $media,
        'is_own'         => (int) $post->post_author === $current_user_id,
        'can_delete'     => ((int) $post->post_author === $current_user_id) || current_user_can('manage_options'),
    ]);
}

/**
 * Dispatch a WP_Post to the correct formatter based on its post_type.
 * Normalises the response by adding `kind` and `can_delete` fields so the
 * JS mixed-feed renderer can dispatch to the right renderer via item.kind.
 */
function gca_cw_format_item(WP_Post $post, int $current_user_id): array
{
    $can_delete = ((int) $post->post_author === $current_user_id) || current_user_can('manage_options');

    switch ($post->post_type) {
        case 'community_shoutout':
            if (!function_exists('gca_shoutout_format')) { return []; }
            return array_merge(
                gca_shoutout_format($post, $current_user_id),
                ['kind' => 'shoutout', 'can_delete' => $can_delete]
            );
        case 'community_poll':
            if (!function_exists('gca_poll_format')) { return []; }
            return array_merge(
                gca_poll_format($post, $current_user_id),
                ['kind' => 'poll', 'can_delete' => $can_delete]
            );
        case 'qa_question':
            if (!function_exists('gca_qa_format_question')) { return []; }
            $qa = gca_qa_format_question($post, $current_user_id);
            if (($qa['status'] ?? '') !== 'answered') { return []; }
            return array_merge($qa, ['kind' => 'qa', 'can_delete' => $can_delete]);
        default:
            return gca_cw_format_post($post, $current_user_id);
    }
}

// ---------------------------------------------------------------------------
// Custom post type
// ---------------------------------------------------------------------------

add_action('init', function (): void {
    if (!gca_flag_enabled('community-hub')) {
        return;
    }

    register_post_type('community_post', [
        'label'              => 'Community Posts',
        'labels'             => [
            'name'               => 'Community Posts',
            'singular_name'      => 'Community Post',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Community Post',
            'edit_item'          => 'Edit Community Post',
            'new_item'           => 'New Community Post',
            'view_item'          => 'View Community Post',
            'search_items'       => 'Search Community Posts',
            'not_found'          => 'No community posts found',
            'not_found_in_trash' => 'No community posts found in Trash',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'hierarchical'       => false,
        'supports'           => ['title', 'editor', 'author', 'thumbnail'],
        'has_archive'        => false,
        'rewrite'            => false,
        'query_var'          => false,
    ]);
});

// ---------------------------------------------------------------------------
// REST routes
// ---------------------------------------------------------------------------

add_action('rest_api_init', function (): void {
    if (!gca_flag_enabled('community-hub')) {
        return;
    }

    // Mixed feed – returns all community CPTs merged by date
    register_rest_route('gca/v1', '/community/feed', [
        'methods'             => 'GET',
        'callback'            => 'gca_cw_get_feed',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'page'     => ['default' => 1,  'sanitize_callback' => 'absint'],
            'per_page' => ['default' => 15, 'sanitize_callback' => 'absint'],
        ],
    ]);

    // Create post – community hosts only
    register_rest_route('gca/v1', '/community/posts', [
        'methods'             => 'POST',
        'callback'            => 'gca_cw_create_post',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'content' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) > 0 && mb_strlen($v) <= 500,
            ],
            'media_ids' => [
                'default'           => [],
                'sanitize_callback' => fn ($v) => array_map('absint', (array) $v),
            ],
        ],
    ]);

    // Delete post – own or admin
    register_rest_route('gca/v1', '/community/posts/(?P<post_id>\d+)', [
        'methods'             => 'DELETE',
        'callback'            => 'gca_cw_delete_post',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'post_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);
});

// ---------------------------------------------------------------------------
// Callbacks
// ---------------------------------------------------------------------------

function gca_cw_get_feed(WP_REST_Request $req): WP_REST_Response
{
    $page            = max(1, (int) $req->get_param('page'));
    $per_page        = min(50, max(1, (int) $req->get_param('per_page')));
    $current_user_id = get_current_user_id();

    // Include all registered community CPTs in the mixed feed
    // Note: Q&A uses the 'qa_question' CPT (registered in qa.php)
    $all_types = ['community_post', 'community_shoutout', 'community_poll', 'qa_question'];
    $post_types = array_values(array_filter($all_types, 'post_type_exists'));

    $query = new WP_Query([
        'post_type'      => $post_types,
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false,
    ]);

    $items = [];
    foreach ($query->posts as $post) {
        if ($post instanceof WP_Post) {
            $item = gca_cw_format_item($post, $current_user_id);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
    }

    return new WP_REST_Response([
        'posts'       => $items,
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'page'        => $page,
    ]);
}

function gca_cw_create_post(WP_REST_Request $req): WP_REST_Response
{
    if (!gca_cw_is_community_host()) {
        return new WP_REST_Response(['error' => 'Only community hosts can create posts.'], 403);
    }

    $current_user_id = get_current_user_id();
    $content         = (string) $req->get_param('content');
    $media_ids       = array_filter(array_map('absint', (array) $req->get_param('media_ids')));

    $post_id = wp_insert_post([
        'post_type'    => 'community_post',
        'post_status'  => 'publish',
        'post_title'   => wp_trim_words($content, 10, '…'),
        'post_content' => $content,
        'post_author'  => $current_user_id,
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(['error' => $post_id->get_error_message()], 500);
    }

    if (!empty($media_ids)) {
        update_post_meta($post_id, GCA_CW_MEDIA_IDS_META, array_values($media_ids));
        foreach ($media_ids as $mid) {
            if (strpos((string) get_post_mime_type($mid), 'image') === 0) {
                set_post_thumbnail($post_id, $mid);
                break;
            }
        }
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return new WP_REST_Response(['error' => 'Could not retrieve created post'], 500);
    }

    return new WP_REST_Response(gca_cw_format_post($post, $current_user_id), 201);
}

function gca_cw_delete_post(WP_REST_Request $req): WP_REST_Response
{
    $post_id         = (int) $req->get_param('post_id');
    $current_user_id = get_current_user_id();

    $post = get_post($post_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'community_post') {
        return new WP_REST_Response(['error' => 'Post not found'], 404);
    }

    if ((int) $post->post_author !== $current_user_id && !current_user_can('manage_options')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    wp_delete_post($post_id, true);

    return new WP_REST_Response(['deleted' => true]);
}
