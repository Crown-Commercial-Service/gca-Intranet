<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Community Polls
//
// Custom post type + REST API for polls in the Community Hub feed.
//
// Routes (all require authentication):
//
//   GET    /wp-json/gca/v1/polls                            – paginated poll list
//   POST   /wp-json/gca/v1/polls                            – create a poll
//   POST   /wp-json/gca/v1/polls/{poll_id}/vote             – cast / change vote
//   DELETE /wp-json/gca/v1/polls/{poll_id}                  – delete own poll
//   GET    /wp-json/gca/v1/polls/{poll_id}/votes            – voter breakdown (creator/admin)
// ---------------------------------------------------------------------------

gca_register_feature_flag('community-polls', [
    'label'       => 'Community Polls',
    'description' => 'Enables polls in the Community Hub feed.',
    'default'     => true,
    'tags'        => ['social', 'community'],
]);

const GCA_POLL_OPTIONS_META  = '_gca_poll_options';
const GCA_POLL_DEADLINE_META = '_gca_poll_deadline';
const GCA_POLL_VOTES_META    = '_gca_poll_votes';
const GCA_POLL_TITLE_META    = '_gca_poll_title';
const GCA_POLL_TEAM_META     = '_gca_poll_team';

// ---------------------------------------------------------------------------
// Custom post type
// ---------------------------------------------------------------------------

add_action('init', function (): void {
    if (!gca_flag_enabled('community-polls')) {
        return;
    }

    register_post_type('community_poll', [
        'label'  => 'Community Polls',
        'labels' => [
            'name'               => 'Community Polls',
            'singular_name'      => 'Community Poll',
            'add_new_item'       => 'Add New Poll',
            'edit_item'          => 'View Poll',
            'search_items'       => 'Search Polls',
            'not_found'          => 'No polls found',
            'not_found_in_trash' => 'No polls in Trash',
        ],
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'show_in_rest'       => false,
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'hierarchical'       => false,
        'supports'           => ['title', 'author'],
        'has_archive'        => false,
        'rewrite'            => false,
        'query_var'          => false,
    ]);
});

// ---------------------------------------------------------------------------
// Admin – menu item with live count badge
// ---------------------------------------------------------------------------

add_action('admin_menu', function (): void {
    if (!gca_flag_enabled('community-polls')) {
        return;
    }

    $counts = wp_count_posts('community_poll');
    $total  = (int) ($counts->publish ?? 0);

    $label = 'Community Polls';
    if ($total > 0) {
        $label .= sprintf(
            ' <span class="awaiting-mod count-%d"><span class="pending-count">%d</span></span>',
            $total,
            $total
        );
    }

    add_menu_page(
        'Community Polls',
        $label,
        'edit_posts',
        'edit.php?post_type=community_poll',
        '',
        'dashicons-chart-bar',
        25
    );
}, 5);

add_action('admin_head', function (): void {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'community_poll') {
        echo '<style>.page-title-action { display: none !important; }</style>';
    }
});

// ---------------------------------------------------------------------------
// Admin – list columns
// ---------------------------------------------------------------------------

add_filter('manage_community_poll_posts_columns', function (array $cols): array {
    return [
        'cb'       => $cols['cb'] ?? '<input type="checkbox">',
        'title'    => 'Question',
        'author'   => 'Created by',
        'votes'    => 'Total votes',
        'deadline' => 'Deadline',
        'status'   => 'Status',
        'date'     => 'Created',
    ];
});

add_action('manage_community_poll_posts_custom_column', function (string $col, int $post_id): void {
    switch ($col) {
        case 'votes':
            echo esc_html((string) count(gca_poll_get_votes($post_id)));
            break;

        case 'deadline':
            $dl = (string) get_post_meta($post_id, GCA_POLL_DEADLINE_META, true);
            $ts = $dl ? (int) strtotime($dl) : 0;
            echo $ts ? esc_html(gmdate('j M Y, H:i', $ts)) : '<em style="color:#666">None</em>';
            break;

        case 'status':
            $dl = (string) get_post_meta($post_id, GCA_POLL_DEADLINE_META, true);
            $ts = $dl ? (int) strtotime($dl) : 0;
            if (!$ts || $ts > time()) {
                echo '<span style="color:#007017;font-weight:600">Live</span>';
            } else {
                echo '<span style="color:#666">Closed</span>';
            }
            break;
    }
}, 10, 2);

// Make 'votes' and 'deadline' non-sortable to avoid query errors
add_filter('manage_edit-community_poll_sortable_columns', function (array $cols): array {
    unset($cols['votes'], $cols['deadline'], $cols['status']);
    return $cols;
});

// ---------------------------------------------------------------------------
// Admin – metaboxes
// ---------------------------------------------------------------------------

add_action('add_meta_boxes', function (): void {
    if (!gca_flag_enabled('community-polls')) {
        return;
    }

    add_meta_box(
        'gca-poll-details',
        'Poll Details',
        'gca_poll_details_metabox',
        'community_poll',
        'normal',
        'high'
    );

    add_meta_box(
        'gca-poll-results',
        'Results & Voters',
        'gca_poll_results_metabox',
        'community_poll',
        'normal',
        'default'
    );
});

function gca_poll_details_metabox(WP_Post $post): void
{
    $options  = (array) (json_decode((string) get_post_meta($post->ID, GCA_POLL_OPTIONS_META, true), true) ?: []);
    $deadline = (string) get_post_meta($post->ID, GCA_POLL_DEADLINE_META, true);
    $creator  = get_userdata((int) $post->post_author);
    $title    = (string) get_post_meta($post->ID, GCA_POLL_TITLE_META, true);
    $team     = (string) get_post_meta($post->ID, GCA_POLL_TEAM_META, true);
    ?>
    <table class="form-table" style="margin-top:0">
        <?php if ($title) : ?>
        <tr>
            <th style="width:130px;padding-top:8px">Title</th>
            <td style="padding-top:8px"><?php echo esc_html($title); ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($team) : ?>
        <tr>
            <th style="padding-top:8px">Team</th>
            <td style="padding-top:8px"><?php echo esc_html($team); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th style="<?php echo $title || $team ? '' : 'width:130px;'; ?>padding-top:8px">Question</th>
            <td style="padding-top:8px"><strong><?php echo esc_html($post->post_title); ?></strong></td>
        </tr>
        <tr>
            <th>Options</th>
            <td>
                <ol style="margin:4px 0;padding-left:20px">
                    <?php foreach ($options as $opt) : ?>
                        <li style="padding:3px 0"><?php echo esc_html((string) $opt); ?></li>
                    <?php endforeach; ?>
                </ol>
            </td>
        </tr>
        <tr>
            <th>Created by</th>
            <td><?php echo $creator instanceof WP_User ? esc_html($creator->display_name) : '—'; ?></td>
        </tr>
        <tr>
            <th>Deadline</th>
            <td>
                <?php if ($deadline) : ?>
                    <?php $ts = (int) strtotime($deadline); ?>
                    <?php echo esc_html(gmdate('j F Y \a\t H:i', $ts)); ?>
                    <?php if ($ts < time()) : ?>
                        <em style="color:#b1091a"> — poll closed</em>
                    <?php else : ?>
                        <em style="color:#007017"> — poll live</em>
                    <?php endif; ?>
                <?php else : ?>
                    <em style="color:#666">No deadline — poll stays open indefinitely</em>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}

function gca_poll_results_metabox(WP_Post $post): void
{
    $options = (array) (json_decode((string) get_post_meta($post->ID, GCA_POLL_OPTIONS_META, true), true) ?: []);
    $votes   = gca_poll_get_votes($post->ID);
    $total   = count($votes);

    if ($total === 0) {
        echo '<p style="color:#666;margin:0">No votes yet.</p>';
        return;
    }

    echo '<p style="margin:0 0 16px;color:#555"><strong>' . esc_html((string) $total) . '</strong> vote' . ($total !== 1 ? 's' : '') . ' cast</p>';

    $tally          = array_fill(0, count($options), 0);
    $voters_by_opt  = array_fill(0, count($options), []);

    foreach ($votes as $v) {
        $opt = (int) ($v['opt'] ?? -1);
        if (isset($tally[$opt])) {
            $tally[$opt]++;
            $voters_by_opt[$opt][] = $v;
        }
    }

    foreach ($options as $idx => $opt_text) {
        $count = $tally[$idx];
        $pct   = $total > 0 ? (int) round(($count / $total) * 100) : 0;

        echo '<div style="margin-bottom:20px">';
        echo '<div style="display:flex;justify-content:space-between;margin-bottom:4px">';
        echo '<strong>' . esc_html((string) $opt_text) . '</strong>';
        echo '<span style="color:#555">' . esc_html((string) $count) . ' vote' . ($count !== 1 ? 's' : '') . ' &mdash; ' . esc_html((string) $pct) . '%</span>';
        echo '</div>';
        echo '<div style="background:#e8e8e8;border-radius:4px;height:8px;overflow:hidden;margin-bottom:8px">';
        echo '<div style="background:#1d70b8;height:100%;width:' . esc_attr((string) $pct) . '%"></div>';
        echo '</div>';

        if (!empty($voters_by_opt[$idx])) {
            echo '<ul style="margin:0;padding-left:20px;font-size:13px;color:#444;columns:2;gap:8px">';
            foreach ($voters_by_opt[$idx] as $vote) {
                $user = get_userdata((int) ($vote['uid'] ?? 0));
                if ($user instanceof WP_User) {
                    $at = isset($vote['at']) ? ' <span style="color:#999">' . esc_html(gmdate('j M, H:i', (int) strtotime((string) $vote['at']))) . '</span>' : '';
                    echo '<li>' . esc_html($user->display_name) . $at . '</li>';
                }
            }
            echo '</ul>';
        }
        echo '</div>';
    }
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function gca_poll_get_votes(int $post_id): array
{
    $raw = get_post_meta($post_id, GCA_POLL_VOTES_META, true);
    if (!$raw || !is_string($raw)) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function gca_poll_time_left(int $deadline_ts): string
{
    if (!$deadline_ts) {
        return '';
    }
    $formatted = (string) wp_date('j F Y \a\t H:i', $deadline_ts);
    return ($deadline_ts <= time() ? 'Closed ' : 'Closes ') . $formatted;
}

function gca_poll_format(WP_Post $post, int $current_user_id): array
{
    $options      = (array) (json_decode((string) get_post_meta($post->ID, GCA_POLL_OPTIONS_META, true), true) ?: []);
    $deadline_raw = (string) get_post_meta($post->ID, GCA_POLL_DEADLINE_META, true);
    $deadline_ts  = $deadline_raw ? (int) strtotime($deadline_raw) : 0;
    $is_open      = !$deadline_ts || $deadline_ts > time();
    $votes        = gca_poll_get_votes($post->ID);
    $total        = count($votes);

    // Current user's vote
    $user_vote = null;
    foreach ($votes as $v) {
        if ((int) ($v['uid'] ?? 0) === $current_user_id) {
            $user_vote = (int) $v['opt'];
            break;
        }
    }

    // Tally
    $tally = array_fill(0, count($options), 0);
    foreach ($votes as $v) {
        $opt = (int) ($v['opt'] ?? -1);
        if (isset($tally[$opt])) {
            $tally[$opt]++;
        }
    }

    $options_out = [];
    foreach ($options as $i => $text) {
        $count         = (int) $tally[$i];
        $options_out[] = [
            'index' => $i,
            'text'  => (string) $text,
            'votes' => $count,
            'pct'   => $total > 0 ? (int) round(($count / $total) * 100) : 0,
        ];
    }

    $poll_title  = trim((string) get_post_meta($post->ID, GCA_POLL_TITLE_META, true));
    $poll_team   = trim((string) get_post_meta($post->ID, GCA_POLL_TEAM_META, true));

    $author      = get_userdata((int) $post->post_author);
    $avatar_url  = '';
    $profile_url = '';
    $author_team = '';

    if ($author instanceof WP_User) {
        $local       = trim((string) get_user_meta($author->ID, 'google_profile_picture_local_url', true));
        $avatar_url  = $local ?: (string) get_avatar_url($author->ID, ['size' => 48]);
        if (gca_flag_enabled('staff-profiles')) {
            $profile_url = esc_url(home_url('/profile/' . $author->user_nicename));
        }
        $author_team = trim((string) get_user_meta($author->ID, 'team', true));
    }

    // poll_team overrides author's profile team if set
    $team = $poll_team ?: $author_team;

    $likes_meta   = defined('GCA_LC_POST_LIKES_META') ? GCA_LC_POST_LIKES_META : '_gca_lc_post_likes';
    $comment_type = defined('GCA_LC_COMMENT_TYPE')    ? GCA_LC_COMMENT_TYPE    : 'gca_comment';

    $liked_by = array_filter(array_map('intval', (array) get_post_meta($post->ID, $likes_meta, true)));

    $comment_count = (int) get_comments([
        'post_id' => $post->ID,
        'type'    => $comment_type,
        'status'  => 'approve',
        'count'   => true,
    ]);

    $can_see_voters = (int) $post->post_author === $current_user_id || current_user_can('edit_others_posts');

    return [
        'id'              => $post->ID,
        'type'            => 'poll',
        'poll_title'      => $poll_title,
        'question'        => $post->post_title,
        'options'         => $options_out,
        'total_votes'     => $total,
        'deadline_iso'    => $deadline_ts ? (string) wp_date('c', $deadline_ts) : null,
        'is_open'         => $is_open,
        'time_left'       => gca_poll_time_left($deadline_ts),
        'user_vote'       => $user_vote,
        'author_id'       => (int) $post->post_author,
        'author_name'     => $author instanceof WP_User ? $author->display_name : '',
        'author_avatar'   => $avatar_url,
        'author_profile'  => $profile_url,
        'author_team'     => $team,
        'date_iso'        => (string) get_post_time('c', true, $post),
        'date_formatted'  => (string) get_post_time('j F Y', false, $post),
        'is_own'          => (int) $post->post_author === $current_user_id,
        'can_see_voters'  => $can_see_voters,
        'like_count'      => count($liked_by),
        'user_has_liked'  => in_array($current_user_id, $liked_by, true),
        'comment_count'   => $comment_count,
    ];
}

// ---------------------------------------------------------------------------
// REST routes
// ---------------------------------------------------------------------------

add_action('rest_api_init', function (): void {
    if (!gca_flag_enabled('community-polls')) {
        return;
    }

    register_rest_route('gca/v1', '/polls', [
        [
            'methods'             => 'GET',
            'callback'            => 'gca_poll_list',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'page'     => ['default' => 1,  'sanitize_callback' => 'absint'],
                'per_page' => ['default' => 15, 'sanitize_callback' => 'absint'],
            ],
        ],
        [
            'methods'             => 'POST',
            'callback'            => 'gca_poll_create',
            'permission_callback' => 'is_user_logged_in',
            'args'                => [
                'question' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) >= 3 && mb_strlen($v) <= 500,
                ],
                'options' => [
                    'required'          => true,
                    'validate_callback' => fn ($v) => is_array($v) && count($v) >= 2,
                ],
                'deadline' => [
                    'default'           => null,
                    'sanitize_callback' => fn ($v) => $v ? sanitize_text_field((string) $v) : null,
                    'validate_callback' => fn ($v) => $v === null || (is_string($v) && strtotime($v) > time()),
                ],
                'poll_title' => [
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'poll_team' => [
                    'default'           => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ],
    ]);

    register_rest_route('gca/v1', '/polls/(?P<poll_id>\d+)/vote', [
        'methods'             => 'POST',
        'callback'            => 'gca_poll_vote',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'poll_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
            'option'  => [
                'required'          => true,
                'sanitize_callback' => 'absint',
            ],
        ],
    ]);

    register_rest_route('gca/v1', '/polls/(?P<poll_id>\d+)', [
        'methods'             => 'DELETE',
        'callback'            => 'gca_poll_delete',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'poll_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);

    register_rest_route('gca/v1', '/polls/(?P<poll_id>\d+)/votes', [
        'methods'             => 'GET',
        'callback'            => 'gca_poll_get_voters_rest',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'poll_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);
});

// ---------------------------------------------------------------------------
// Callbacks
// ---------------------------------------------------------------------------

function gca_poll_list(WP_REST_Request $req): WP_REST_Response
{
    $page     = max(1, (int) $req->get_param('page'));
    $per_page = min(50, max(1, (int) $req->get_param('per_page')));
    $uid      = get_current_user_id();

    $query = new WP_Query([
        'post_type'      => 'community_poll',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false,
    ]);

    $polls = [];
    foreach ($query->posts as $post) {
        if ($post instanceof WP_Post) {
            $polls[] = gca_poll_format($post, $uid);
        }
    }

    return new WP_REST_Response([
        'polls'       => $polls,
        'total'       => (int) $query->found_posts,
        'total_pages' => (int) $query->max_num_pages,
        'page'        => $page,
    ]);
}

function gca_poll_create(WP_REST_Request $req): WP_REST_Response
{
    $uid      = get_current_user_id();
    $question = (string) $req->get_param('question');
    $options  = array_values(array_filter(
        array_map(fn ($o) => sanitize_text_field((string) $o), (array) $req->get_param('options')),
        fn ($o) => mb_strlen(trim($o)) > 0
    ));
    $deadline = $req->get_param('deadline');

    if (count($options) < 2) {
        return new WP_REST_Response(['error' => 'At least 2 non-empty options are required'], 422);
    }

    $post_id = wp_insert_post([
        'post_type'    => 'community_poll',
        'post_status'  => 'publish',
        'post_title'   => $question,
        'post_content' => '',
        'post_author'  => $uid,
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(['error' => $post_id->get_error_message()], 500);
    }

    update_post_meta($post_id, GCA_POLL_OPTIONS_META, wp_json_encode(array_values($options)));

    $poll_title = trim((string) $req->get_param('poll_title'));
    $poll_team  = trim((string) $req->get_param('poll_team'));
    if ($poll_title) { update_post_meta($post_id, GCA_POLL_TITLE_META, $poll_title); }
    if ($poll_team)  { update_post_meta($post_id, GCA_POLL_TEAM_META,  $poll_team); }

    if ($deadline) {
        $ts = strtotime($deadline);
        if ($ts) {
            update_post_meta($post_id, GCA_POLL_DEADLINE_META, gmdate('Y-m-d H:i:s', $ts));
        }
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return new WP_REST_Response(['error' => 'Could not retrieve created poll'], 500);
    }

    return new WP_REST_Response(gca_poll_format($post, $uid), 201);
}

function gca_poll_vote(WP_REST_Request $req): WP_REST_Response
{
    $poll_id = (int) $req->get_param('poll_id');
    $opt     = (int) $req->get_param('option');
    $uid     = get_current_user_id();

    $post = get_post($poll_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'community_poll') {
        return new WP_REST_Response(['error' => 'Poll not found'], 404);
    }

    $deadline = (string) get_post_meta($poll_id, GCA_POLL_DEADLINE_META, true);
    if ($deadline && strtotime($deadline) <= time()) {
        return new WP_REST_Response(['error' => 'This poll has closed'], 422);
    }

    $options = (array) (json_decode((string) get_post_meta($poll_id, GCA_POLL_OPTIONS_META, true), true) ?: []);
    if (!array_key_exists($opt, $options)) {
        return new WP_REST_Response(['error' => 'Invalid option'], 422);
    }

    $votes   = gca_poll_get_votes($poll_id);
    $updated = false;

    foreach ($votes as &$v) {
        if ((int) ($v['uid'] ?? 0) === $uid) {
            $v['opt'] = $opt;
            $v['at']  = gmdate('Y-m-d H:i:s');
            $updated  = true;
            break;
        }
    }
    unset($v);

    if (!$updated) {
        $votes[] = ['uid' => $uid, 'opt' => $opt, 'at' => gmdate('Y-m-d H:i:s')];
    }

    update_post_meta($poll_id, GCA_POLL_VOTES_META, wp_json_encode($votes));

    $post = get_post($poll_id);
    return new WP_REST_Response(gca_poll_format($post, $uid));
}

function gca_poll_delete(WP_REST_Request $req): WP_REST_Response
{
    $poll_id = (int) $req->get_param('poll_id');
    $uid     = get_current_user_id();

    $post = get_post($poll_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'community_poll') {
        return new WP_REST_Response(['error' => 'Poll not found'], 404);
    }

    if ((int) $post->post_author !== $uid && !current_user_can('delete_others_posts')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    wp_delete_post($poll_id, true);
    return new WP_REST_Response(['deleted' => true]);
}

function gca_poll_get_voters_rest(WP_REST_Request $req): WP_REST_Response
{
    $poll_id = (int) $req->get_param('poll_id');
    $uid     = get_current_user_id();

    $post = get_post($poll_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'community_poll') {
        return new WP_REST_Response(['error' => 'Poll not found'], 404);
    }

    if ((int) $post->post_author !== $uid && !current_user_can('edit_others_posts')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    $options       = (array) (json_decode((string) get_post_meta($poll_id, GCA_POLL_OPTIONS_META, true), true) ?: []);
    $votes         = gca_poll_get_votes($poll_id);
    $voters_by_opt = array_fill(0, count($options), []);

    foreach ($votes as $v) {
        $opt_idx = (int) ($v['opt'] ?? -1);
        $user    = get_userdata((int) ($v['uid'] ?? 0));

        if ($user instanceof WP_User && isset($voters_by_opt[$opt_idx])) {
            $voters_by_opt[$opt_idx][] = [
                'name'     => $user->display_name,
                'team'     => trim((string) get_user_meta($user->ID, 'team', true)),
                'avatar'   => (string) get_avatar_url($user->ID, ['size' => 32]),
                'voted_at' => $v['at'] ?? '',
            ];
        }
    }

    $result = [];
    foreach ($options as $i => $text) {
        $result[] = [
            'option_text' => (string) $text,
            'voters'      => $voters_by_opt[$i],
        ];
    }

    return new WP_REST_Response(['options' => $result]);
}
