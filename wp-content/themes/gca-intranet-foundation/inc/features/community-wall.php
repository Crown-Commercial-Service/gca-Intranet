<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Community Wall
//
// Custom post type + REST API for the social community feed.
//
// Routes (all require authentication):
//
//   GET    /wp-json/gca/v1/community/feed            – paginated feed
//   POST   /wp-json/gca/v1/community/posts           – create a post
//   DELETE /wp-json/gca/v1/community/posts/{post_id} – delete own post
// ---------------------------------------------------------------------------

/** Meta key storing media attachment IDs for a community post. */
const GCA_CW_MEDIA_IDS_META = '_gca_cw_media_ids';

// ---------------------------------------------------------------------------
// Custom post type
// ---------------------------------------------------------------------------

add_action('init', function (): void {
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

    register_rest_route('gca/v1', '/community/feed', [
        'methods'             => 'GET',
        'callback'            => 'gca_cw_get_feed',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'page'     => ['default' => 1,  'sanitize_callback' => 'absint'],
            'per_page' => ['default' => 15, 'sanitize_callback' => 'absint'],
        ],
    ]);

    register_rest_route('gca/v1', '/community/posts', [
        'methods'             => 'POST',
        'callback'            => 'gca_cw_create_post',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'content' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) > 0 && mb_strlen($v) <= 2000,
            ],
            'media_ids' => [
                'default'           => [],
                'sanitize_callback' => fn ($v) => array_map('absint', (array) $v),
            ],
        ],
    ]);

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
// Helpers
// ---------------------------------------------------------------------------

/**
 * Escape content for display: nl2br + linkify #hashtags.
 */
function gca_cw_render_content(string $raw): string
{
    $html = nl2br(esc_html($raw));
    return (string) preg_replace_callback(
        '/#([a-zA-Z0-9_]+)/',
        fn ($m) => '<span class="gca-cw__hashtag">#' . esc_html($m[1]) . '</span>',
        $html
    );
}

/**
 * Serialise a community_post into an array for JSON responses.
 */
function gca_cw_format_post(WP_Post $post, int $current_user_id): array
{
    $author      = get_userdata((int) $post->post_author);
    $avatar_url  = '';
    $profile_url = '';
    $team        = '';

    if ($author instanceof WP_User) {
        $local      = trim((string) get_user_meta($author->ID, 'google_profile_picture_local_url', true));
        $avatar_url = $local ?: (string) get_avatar_url($author->ID, ['size' => 48]);
        $profile_url = esc_url(home_url('/profile/' . $author->user_nicename));
        $team        = trim((string) get_user_meta($author->ID, 'team', true));
    }

    // Attached media
    $raw_ids = get_post_meta($post->ID, GCA_CW_MEDIA_IDS_META, true);
    $media_ids = array_filter(array_map('absint', (array) $raw_ids));
    $media = [];
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

    // Likes – reuse the likes-and-comments constant if loaded
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
        'id'             => $post->ID,
        'content_html'   => gca_cw_render_content($post->post_content),
        'content_raw'    => $post->post_content,
        'author_id'      => (int) $post->post_author,
        'author_name'    => $author instanceof WP_User ? $author->display_name : '',
        'author_avatar'  => $avatar_url,
        'author_profile' => $profile_url,
        'author_team'    => $team,
        'date_iso'       => (string) get_post_time('c', true, $post),
        'date_formatted' => (string) get_post_time('j F Y', false, $post),
        'media'          => $media,
        'like_count'     => count($liked_by),
        'user_has_liked' => in_array($current_user_id, $liked_by, true),
        'comment_count'  => $comment_count,
        'is_own'         => (int) $post->post_author === $current_user_id,
    ];
}

// ---------------------------------------------------------------------------
// Callbacks
// ---------------------------------------------------------------------------

function gca_cw_get_feed(WP_REST_Request $req): WP_REST_Response
{
    $page            = max(1, (int) $req->get_param('page'));
    $per_page        = min(50, max(1, (int) $req->get_param('per_page')));
    $current_user_id = get_current_user_id();

    $query = new WP_Query([
        'post_type'      => 'community_post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false,
    ]);

    $posts = [];
    foreach ($query->posts as $post) {
        if ($post instanceof WP_Post) {
            $posts[] = gca_cw_format_post($post, $current_user_id);
        }
    }

    return new WP_REST_Response([
        'posts'       => $posts,
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'page'        => $page,
    ]);
}

function gca_cw_create_post(WP_REST_Request $req): WP_REST_Response
{
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

    if ((int) $post->post_author !== $current_user_id && !current_user_can('delete_others_posts')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    wp_delete_post($post_id, true);

    return new WP_REST_Response(['deleted' => true]);
}
