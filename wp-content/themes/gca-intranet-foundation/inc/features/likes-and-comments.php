<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Likes & Comments – REST API
//
// Routes (all require authentication via the existing rest_authentication_errors
// filter in inc/rest-api-auth.php):
//
//   GET    /wp-json/gca/v1/posts/{post_id}/interactions  – likes + comments
//   POST   /wp-json/gca/v1/posts/{post_id}/like          – toggle post like
//   POST   /wp-json/gca/v1/posts/{post_id}/comments      – add comment
//   DELETE /wp-json/gca/v1/comments/{comment_id}         – delete own comment
//   POST   /wp-json/gca/v1/comments/{comment_id}/like    – toggle comment like
//   GET    /wp-json/gca/v1/users/search                  – @mention user search
// ---------------------------------------------------------------------------

add_action('rest_api_init', function (): void {

    // GET – all interactions (likes + comments) for a post
    register_rest_route('gca/v1', '/posts/(?P<post_id>\d+)/interactions', [
        'methods'             => 'GET',
        'callback'            => 'gca_lc_get_interactions',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'post_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);

    // POST – toggle like on a post
    register_rest_route('gca/v1', '/posts/(?P<post_id>\d+)/like', [
        'methods'             => 'POST',
        'callback'            => 'gca_lc_toggle_post_like',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'post_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);

    // POST – add a comment or reply
    register_rest_route('gca/v1', '/posts/(?P<post_id>\d+)/comments', [
        'methods'             => 'POST',
        'callback'            => 'gca_lc_add_comment',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'post_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
            'content' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'validate_callback' => fn ($v) => is_string($v) && trim($v) !== '',
            ],
            'parent_id' => [
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ],
        ],
    ]);

    // DELETE – delete own comment
    register_rest_route('gca/v1', '/comments/(?P<comment_id>\d+)', [
        'methods'             => 'DELETE',
        'callback'            => 'gca_lc_delete_comment',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'comment_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);

    // POST – toggle like on a comment
    register_rest_route('gca/v1', '/comments/(?P<comment_id>\d+)/like', [
        'methods'             => 'POST',
        'callback'            => 'gca_lc_toggle_comment_like',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'comment_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);

    // GET – user search for @mention autocomplete
    register_rest_route('gca/v1', '/users/search', [
        'methods'             => 'GET',
        'callback'            => 'gca_lc_search_users',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'q' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** Meta key for post likes (serialized array of user IDs). */
const GCA_LC_POST_LIKES_META = '_gca_lc_post_likes';

/** Meta key for comment likes (serialized array of user IDs). */
const GCA_LC_COMMENT_LIKES_META = '_gca_lc_comment_likes';

/** comment_type value used for all GCA comments. */
const GCA_LC_COMMENT_TYPE = 'gca_comment';

/**
 * Parse @[Display Name](user_id) markers in stored comment text and replace
 * with <a href="/profile/nicename">@Display Name</a>.
 */
function gca_lc_render_content(string $raw): string
{
    $escaped = wp_kses_post($raw);

    return (string) preg_replace_callback(
        '/@\[([^\]]+)\]\((\d+)\)/',
        function (array $m): string {
            $display = esc_html($m[1]);
            $user_id = (int) $m[2];
            $user    = get_userdata($user_id);
            if (!$user) {
                return '@' . $display;
            }
            $url = esc_url(home_url('/profile/' . $user->user_nicename));
            return '<a href="' . $url . '" class="gca-lc__mention">@' . $display . '</a>';
        },
        $escaped
    );
}

/**
 * Build a comment array suitable for JSON response, including nested replies.
 *
 * @param  WP_Comment[]  $all_comments  Flat list of all post comments.
 * @param  int           $parent_id
 * @param  int           $current_user_id
 * @return array<int, mixed>
 */
function gca_lc_build_comment_tree(array $all_comments, int $parent_id, int $current_user_id): array
{
    $out = [];

    foreach ($all_comments as $comment) {
        if ((int) $comment->comment_parent !== $parent_id) {
            continue;
        }

        $liked_by   = (array) get_comment_meta((int) $comment->comment_ID, GCA_LC_COMMENT_LIKES_META, true);
        $liked_by   = array_filter(array_map('intval', $liked_by));
        $avatar_url = get_avatar_url((int) $comment->user_id, ['size' => 48]);
        $user       = get_userdata((int) $comment->user_id);
        $profile_url = $user
            ? esc_url(home_url('/profile/' . $user->user_nicename))
            : '';

        $out[] = [
            'id'               => (int) $comment->comment_ID,
            'author_name'      => $comment->comment_author,
            'author_id'        => (int) $comment->user_id,
            'author_avatar'    => $avatar_url ?: '',
            'author_profile'   => $profile_url,
            'content_raw'      => $comment->comment_content,
            'content_html'     => gca_lc_render_content($comment->comment_content),
            'date_iso'         => get_comment_date('c', $comment),
            'date_formatted'   => get_comment_date('j F Y', $comment),
            'like_count'       => count($liked_by),
            'user_has_liked'   => in_array($current_user_id, $liked_by, true),
            'is_own'           => (int) $comment->user_id === $current_user_id,
            'replies'          => gca_lc_build_comment_tree($all_comments, (int) $comment->comment_ID, $current_user_id),
        ];
    }

    return $out;
}

// ---------------------------------------------------------------------------
// Callbacks
// ---------------------------------------------------------------------------

function gca_lc_get_interactions(WP_REST_Request $req): WP_REST_Response
{
    $post_id         = (int) $req->get_param('post_id');
    $current_user_id = get_current_user_id();

    if (!get_post($post_id)) {
        return new WP_REST_Response(['error' => 'Post not found'], 404);
    }

    $liked_by = (array) get_post_meta($post_id, GCA_LC_POST_LIKES_META, true);
    $liked_by = array_filter(array_map('intval', $liked_by));

    $all_comments = get_comments([
        'post_id' => $post_id,
        'type'    => GCA_LC_COMMENT_TYPE,
        'status'  => 'approve',
        'orderby' => 'comment_date',
        'order'   => 'ASC',
    ]);

    return new WP_REST_Response([
        'post_like_count'  => count($liked_by),
        'user_has_liked'   => in_array($current_user_id, $liked_by, true),
        'comment_count'    => count($all_comments),
        'comments'         => gca_lc_build_comment_tree((array) $all_comments, 0, $current_user_id),
    ]);
}

function gca_lc_toggle_post_like(WP_REST_Request $req): WP_REST_Response
{
    $post_id         = (int) $req->get_param('post_id');
    $current_user_id = get_current_user_id();

    if (!get_post($post_id)) {
        return new WP_REST_Response(['error' => 'Post not found'], 404);
    }

    $liked_by = (array) get_post_meta($post_id, GCA_LC_POST_LIKES_META, true);
    $liked_by = array_filter(array_map('intval', $liked_by));

    $already_liked = in_array($current_user_id, $liked_by, true);

    if ($already_liked) {
        $liked_by = array_values(array_diff($liked_by, [$current_user_id]));
    } else {
        $liked_by[] = $current_user_id;
    }

    update_post_meta($post_id, GCA_LC_POST_LIKES_META, $liked_by);

    return new WP_REST_Response([
        'liked'      => !$already_liked,
        'like_count' => count($liked_by),
    ]);
}

function gca_lc_add_comment(WP_REST_Request $req): WP_REST_Response
{
    $post_id         = (int) $req->get_param('post_id');
    $current_user_id = get_current_user_id();
    $content         = (string) $req->get_param('content');
    $parent_id       = (int) $req->get_param('parent_id');

    $post = get_post($post_id);
    if (!$post) {
        return new WP_REST_Response(['error' => 'Post not found'], 404);
    }

    if ($parent_id > 0) {
        $parent = get_comment($parent_id);
        if (!$parent || (int) $parent->comment_post_ID !== $post_id) {
            return new WP_REST_Response(['error' => 'Invalid parent comment'], 400);
        }
    }

    $user = wp_get_current_user();

    $comment_id = wp_insert_comment([
        'comment_post_ID'      => $post_id,
        'comment_author'       => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_author_url'   => '',
        'comment_content'      => $content,
        'comment_type'         => GCA_LC_COMMENT_TYPE,
        'comment_parent'       => $parent_id,
        'user_id'              => $current_user_id,
        'comment_approved'     => 1,
    ]);

    if (!$comment_id) {
        return new WP_REST_Response(['error' => 'Could not save comment'], 500);
    }

    $all_comments = get_comments([
        'post_id' => $post_id,
        'type'    => GCA_LC_COMMENT_TYPE,
        'status'  => 'approve',
    ]);

    $avatar_url  = get_avatar_url($current_user_id, ['size' => 48]);
    $profile_url = esc_url(home_url('/profile/' . $user->user_nicename));

    return new WP_REST_Response([
        'comment_count' => count($all_comments),
        'comment'       => [
            'id'             => $comment_id,
            'author_name'    => $user->display_name,
            'author_id'      => $current_user_id,
            'author_avatar'  => $avatar_url ?: '',
            'author_profile' => $profile_url,
            'content_raw'    => $content,
            'content_html'   => gca_lc_render_content($content),
            'date_iso'       => (string) get_comment_date('c', $comment_id),
            'date_formatted' => (string) get_comment_date('j F Y', $comment_id),
            'like_count'     => 0,
            'user_has_liked' => false,
            'is_own'         => true,
            'replies'        => [],
        ],
    ], 201);
}

function gca_lc_delete_comment(WP_REST_Request $req): WP_REST_Response
{
    $comment_id      = (int) $req->get_param('comment_id');
    $current_user_id = get_current_user_id();

    $comment = get_comment($comment_id);
    if (!$comment || $comment->comment_type !== GCA_LC_COMMENT_TYPE) {
        return new WP_REST_Response(['error' => 'Comment not found'], 404);
    }

    if ((int) $comment->user_id !== $current_user_id && !current_user_can('moderate_comments')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    $post_id = (int) $comment->comment_post_ID;
    wp_delete_comment($comment_id, true);

    $remaining = get_comments([
        'post_id' => $post_id,
        'type'    => GCA_LC_COMMENT_TYPE,
        'status'  => 'approve',
    ]);

    return new WP_REST_Response(['comment_count' => count($remaining)]);
}

function gca_lc_toggle_comment_like(WP_REST_Request $req): WP_REST_Response
{
    $comment_id      = (int) $req->get_param('comment_id');
    $current_user_id = get_current_user_id();

    $comment = get_comment($comment_id);
    if (!$comment || $comment->comment_type !== GCA_LC_COMMENT_TYPE) {
        return new WP_REST_Response(['error' => 'Comment not found'], 404);
    }

    $liked_by = (array) get_comment_meta($comment_id, GCA_LC_COMMENT_LIKES_META, true);
    $liked_by = array_filter(array_map('intval', $liked_by));

    $already_liked = in_array($current_user_id, $liked_by, true);

    if ($already_liked) {
        $liked_by = array_values(array_diff($liked_by, [$current_user_id]));
    } else {
        $liked_by[] = $current_user_id;
    }

    update_comment_meta($comment_id, GCA_LC_COMMENT_LIKES_META, $liked_by);

    return new WP_REST_Response([
        'liked'      => !$already_liked,
        'like_count' => count($liked_by),
    ]);
}

function gca_lc_search_users(WP_REST_Request $req): WP_REST_Response
{
    $q = (string) $req->get_param('q');

    if (strlen($q) < 2) {
        return new WP_REST_Response([]);
    }

    $users = get_users([
        'search'         => '*' . esc_attr($q) . '*',
        'search_columns' => ['display_name', 'user_login'],
        'number'         => 8,
        'fields'         => ['ID', 'display_name', 'user_nicename'],
    ]);

    $results = array_map(function ($u): array {
        return [
            'id'           => (int) $u->ID,
            'display_name' => $u->display_name,
            'nicename'     => $u->user_nicename,
            'avatar'       => get_avatar_url((int) $u->ID, ['size' => 32]),
        ];
    }, $users);

    return new WP_REST_Response(array_values($results));
}
