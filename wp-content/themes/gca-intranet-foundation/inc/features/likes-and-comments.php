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
//   GET    /wp-json/gca/v1/posts/{post_id}/interactions  – likes + comments + saved
//   POST   /wp-json/gca/v1/posts/{post_id}/like          – toggle post like
//   POST   /wp-json/gca/v1/posts/{post_id}/save          – toggle post save  [flag: post-saves]
//   POST   /wp-json/gca/v1/posts/{post_id}/comments      – add comment
//   DELETE /wp-json/gca/v1/comments/{comment_id}         – delete own comment
//   POST   /wp-json/gca/v1/comments/{comment_id}/like    – toggle comment like
//   GET    /wp-json/gca/v1/users/search                  – @mention user search
//   GET    /wp-json/gca/v1/profile/me/saves              – saved posts list   [flag: post-saves]
//   GET    /wp-json/gca/v1/profile/me/posts              – authored posts      [flag: post-saves]
//   GET    /wp-json/gca/v1/profile/me/mentions           – comment mentions    [flag: post-saves]
// ---------------------------------------------------------------------------

gca_register_feature_flag('likes-and-comments', [
    'label'       => 'Likes & Comments',
    'description' => 'Enables the likes and comments component on blog, news, work update, and event posts, along with the supporting REST API endpoints.',
    'default'     => false,
    'tags'        => ['ui', 'engagement'],
]);

gca_register_feature_flag('post-saves', [
    'label'       => 'Post Saves & Profile Tabs',
    'description' => 'Save button on posts and pages, plus the My Saves / Mentions / My Posts tabs on the profile page.',
    'default'     => true,
    'tags'        => ['posts', 'profiles'],
]);

add_action('rest_api_init', function (): void {

    // GET – all interactions (likes + comments + saved state) for a post
    // Registered whenever either flag is on so the save button can initialise its state
    // even when likes-and-comments is disabled.
    if (gca_flag_enabled('likes-and-comments') || gca_flag_enabled('post-saves')) {
        register_rest_route('gca/v1', '/posts/(?P<post_id>\d+)/interactions', [
            'methods'             => 'GET',
            'callback'            => 'gca_lc_get_interactions',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'post_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
            ],
        ]);
    }

    if (gca_flag_enabled('likes-and-comments')) {
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
    }

    // POST – toggle save on a post  [flag: post-saves]
    if (gca_flag_enabled('post-saves')) {
        register_rest_route('gca/v1', '/posts/(?P<post_id>\d+)/save', [
            'methods'             => 'POST',
            'callback'            => 'gca_lc_toggle_post_save',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'post_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
            ],
        ]);

        // Profile tab routes
        register_rest_route('gca/v1', '/profile/me/saves', [
            'methods'             => 'GET',
            'callback'            => 'gca_profile_get_saves',
            'permission_callback' => 'is_user_logged_in',
        ]);

        register_rest_route('gca/v1', '/profile/me/posts', [
            'methods'             => 'GET',
            'callback'            => 'gca_profile_get_posts',
            'permission_callback' => 'is_user_logged_in',
        ]);

        register_rest_route('gca/v1', '/profile/me/mentions', [
            'methods'             => 'GET',
            'callback'            => 'gca_profile_get_mentions',
            'permission_callback' => 'is_user_logged_in',
        ]);
    }
});

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** Meta key for post likes (serialized array of user IDs). */
const GCA_LC_POST_LIKES_META = '_gca_lc_post_likes';

/** Meta key for comment likes (serialized array of user IDs). */
const GCA_LC_COMMENT_LIKES_META = '_gca_lc_comment_likes';

/** User meta key for saved post IDs (serialized array of post IDs). */
const GCA_LC_SAVED_POSTS_META = '_gca_saved_posts';

/** User meta key for saved-at timestamps (associative array of post_id => unix timestamp). */
const GCA_LC_SAVED_POSTS_AT_META = '_gca_saved_posts_at';

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

    $saved_posts = (array) get_user_meta($current_user_id, GCA_LC_SAVED_POSTS_META, true);
    $saved_posts = array_filter(array_map('intval', $saved_posts));

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
        'user_has_saved'   => in_array($post_id, $saved_posts, true),
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

function gca_lc_toggle_post_save(WP_REST_Request $req): WP_REST_Response
{
    $post_id         = (int) $req->get_param('post_id');
    $current_user_id = get_current_user_id();

    if (!get_post($post_id)) {
        return new WP_REST_Response(['error' => 'Post not found'], 404);
    }

    $saved_posts   = (array) get_user_meta($current_user_id, GCA_LC_SAVED_POSTS_META, true);
    $saved_posts   = array_filter(array_map('intval', $saved_posts));
    $already_saved = in_array($post_id, $saved_posts, true);

    $saved_at = (array) get_user_meta($current_user_id, GCA_LC_SAVED_POSTS_AT_META, true);

    if ($already_saved) {
        $saved_posts = array_values(array_diff($saved_posts, [$post_id]));
        unset($saved_at[$post_id]);
    } else {
        $saved_posts[]      = $post_id;
        $saved_at[$post_id] = time();
    }

    update_user_meta($current_user_id, GCA_LC_SAVED_POSTS_META, $saved_posts);
    update_user_meta($current_user_id, GCA_LC_SAVED_POSTS_AT_META, $saved_at);

    return new WP_REST_Response(['saved' => !$already_saved]);
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

// ---------------------------------------------------------------------------
// Profile tab callbacks
// ---------------------------------------------------------------------------

function gca_profile_get_saves(): WP_REST_Response
{
    $user_id   = get_current_user_id();
    $saved_ids = (array) get_user_meta($user_id, GCA_LC_SAVED_POSTS_META, true);
    $saved_ids = array_filter(array_map('intval', $saved_ids));
    $saved_at  = (array) get_user_meta($user_id, GCA_LC_SAVED_POSTS_AT_META, true);

    $results = [];
    foreach (array_reverse($saved_ids) as $post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            continue;
        }
        $post_type_obj = get_post_type_object($post->post_type);
        $timestamp     = isset($saved_at[$post_id]) ? (int) $saved_at[$post_id] : null;

        $results[] = [
            'id'              => $post_id,
            'title'           => get_the_title($post),
            'url'             => get_permalink($post),
            'post_type'       => $post->post_type,
            'post_type_label' => $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst($post->post_type),
            'saved_at'        => $timestamp ? gmdate('c', $timestamp) : null,
        ];
    }

    return new WP_REST_Response($results);
}

function gca_profile_get_posts(): WP_REST_Response
{
    $user_id = get_current_user_id();
    $posts   = get_posts([
        'author'      => $user_id,
        'post_type'   => ['blog', 'work_update'],
        'post_status' => 'publish',
        'numberposts' => 20,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ]);

    $results = array_map(function (WP_Post $post): array {
        $post_type_obj = get_post_type_object($post->post_type);
        return [
            'type'            => 'post',
            'id'              => $post->ID,
            'title'           => get_the_title($post),
            'url'             => get_permalink($post),
            'post_type'       => $post->post_type,
            'post_type_label' => $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst($post->post_type),
            'date'            => get_the_date('c', $post),
            'content_html'    => '',
        ];
    }, $posts);

    // Community wall posts authored by the current user
    if (post_type_exists('community_post')) {
        $community_posts = get_posts([
            'author'      => $user_id,
            'post_type'   => 'community_post',
            'post_status' => 'publish',
            'numberposts' => 20,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ]);

        foreach ($community_posts as $post) {
            $results[] = [
                'type'            => 'post',
                'id'              => $post->ID,
                'title'           => '',
                'url'             => '',
                'post_type'       => 'community_post',
                'post_type_label' => 'Community update',
                'date'            => get_the_date('c', $post),
                'content_html'    => nl2br(esc_html($post->post_content)),
            ];
        }
    }

    // Shoutouts given by the current user
    if (post_type_exists('community_shoutout')) {
        $shoutout_meta_key = defined('GCA_SHOUTOUT_RECIPIENT_META') ? GCA_SHOUTOUT_RECIPIENT_META : '_gca_shoutout_recipient_id';

        $shoutout_posts = get_posts([
            'author'      => $user_id,
            'post_type'   => 'community_shoutout',
            'post_status' => 'publish',
            'numberposts' => 20,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ]);

        foreach ($shoutout_posts as $post) {
            $recipient_id  = (int) get_post_meta($post->ID, $shoutout_meta_key, true);
            $recipient     = $recipient_id ? get_userdata($recipient_id) : null;
            $recip_profile = ($recipient && gca_flag_enabled('staff-profiles'))
                ? esc_url(home_url('/profile/' . $recipient->user_nicename))
                : '';

            $results[] = [
                'type'              => 'shoutout',
                'id'                => $post->ID,
                'title'             => $recipient ? $recipient->display_name : '',
                'url'               => $recip_profile,
                'post_type'         => 'community_shoutout',
                'post_type_label'   => 'Shout-out',
                'date'              => get_the_date('c', $post),
                'content_html'      => nl2br(esc_html($post->post_content)),
                'recipient_name'    => $recipient ? $recipient->display_name : '',
                'recipient_profile' => $recip_profile,
            ];
        }
    }

    usort($results, function (array $a, array $b): int {
        return strcmp($b['date'], $a['date']);
    });

    return new WP_REST_Response($results);
}

function gca_profile_get_mentions(): WP_REST_Response
{
    $user_id      = get_current_user_id();
    $all_comments = get_comments([
        'type'    => GCA_LC_COMMENT_TYPE,
        'status'  => 'approve',
        'search'  => '(' . $user_id . ')',
        'number'  => 100,
        'orderby' => 'comment_date',
        'order'   => 'DESC',
    ]);

    $pattern  = '/@\[[^\]]+\]\(' . $user_id . '\)/';
    $mentions = array_values(array_filter((array) $all_comments, function ($c) use ($pattern, $user_id): bool {
        return preg_match($pattern, $c->comment_content) && (int) $c->user_id !== $user_id;
    }));

    $results = array_map(function (WP_Comment $comment): array {
        $post      = get_post($comment->comment_post_ID);
        $commenter = get_userdata((int) $comment->user_id);
        return [
            'type'           => 'mention',
            'comment_id'     => (int) $comment->comment_ID,
            'post_id'        => (int) $comment->comment_post_ID,
            'post_title'     => $post ? get_the_title($post) : '',
            'post_url'       => $post ? get_permalink($post) : '',
            'author_name'    => $comment->comment_author,
            'author_avatar'  => get_avatar_url((int) $comment->user_id, ['size' => 40]),
            'author_profile' => $commenter ? esc_url(home_url('/profile/' . $commenter->user_nicename)) : '',
            'content_html'   => gca_lc_render_content($comment->comment_content),
            'date'           => get_comment_date('c', $comment),
        ];
    }, $mentions);

    // Shoutouts received by the current user
    if (gca_flag_enabled('community-shoutouts') && post_type_exists('community_shoutout')) {
        $shoutout_meta_key = defined('GCA_SHOUTOUT_RECIPIENT_META') ? GCA_SHOUTOUT_RECIPIENT_META : '_gca_shoutout_recipient_id';

        $shoutout_query = new WP_Query([
            'post_type'      => 'community_shoutout',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'meta_query'     => [[
                'key'     => $shoutout_meta_key,
                'value'   => $user_id,
                'type'    => 'NUMERIC',
                'compare' => '=',
            ]],
        ]);

        foreach ($shoutout_query->posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            $giver   = get_userdata((int) $post->post_author);
            $local   = $giver ? trim((string) get_user_meta($giver->ID, 'google_profile_picture_local_url', true)) : '';
            $avatar  = $local ?: ($giver ? (string) get_avatar_url($giver->ID, ['size' => 40]) : '');
            $profile = ($giver && gca_flag_enabled('staff-profiles'))
                ? esc_url(home_url('/profile/' . $giver->user_nicename))
                : '';

            $results[] = [
                'type'           => 'shoutout',
                'comment_id'     => null,
                'post_id'        => $post->ID,
                'post_title'     => '',
                'post_url'       => '',
                'author_name'    => $giver ? $giver->display_name : '',
                'author_avatar'  => $avatar,
                'author_profile' => $profile,
                'content_html'   => nl2br(esc_html($post->post_content)),
                'date'           => (string) get_post_time('c', true, $post),
            ];
        }

        usort($results, function (array $a, array $b): int {
            return strcmp($b['date'], $a['date']);
        });
    }

    return new WP_REST_Response($results);
}
