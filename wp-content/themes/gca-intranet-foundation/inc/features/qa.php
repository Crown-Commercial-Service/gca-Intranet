<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Community Q&A
//
// Custom post type + REST API for the Questions & Answers tab in Community Hub.
//
// Routes (all require authentication):
//
//   GET    /wp-json/gca/v1/qa/questions                    – paginated feed
//   POST   /wp-json/gca/v1/qa/questions                    – submit a question
//   POST   /wp-json/gca/v1/qa/questions/{question_id}/answer – add/edit answer
//   DELETE /wp-json/gca/v1/qa/questions/{question_id}      – delete question
// ---------------------------------------------------------------------------

gca_register_feature_flag('community-qa', [
    'label'       => 'Community Q&A',
    'description' => 'Enables the Questions & Answers tab on the Community Hub.',
    'default'     => true,
    'tags'        => ['social', 'community'],
]);

const GCA_QA_ANSWER_META      = '_gca_qa_answer';
const GCA_QA_ANSWERED_BY_META = '_gca_qa_answered_by';
const GCA_QA_ANSWERED_AT_META = '_gca_qa_answered_at';

// ---------------------------------------------------------------------------
// Custom post type
// ---------------------------------------------------------------------------

add_action('init', function (): void {
    // CPT and roles always register so admins can manage questions in wp-admin
    // regardless of whether the frontend flag is on or off.

    register_post_type('qa_question', [
        'label'          => 'Q&A Questions',
        'labels'         => [
            'name'               => 'Q&A Questions',
            'singular_name'      => 'Q&A Question',
            'add_new_item'       => 'Add New Question',
            'edit_item'          => 'Answer Question',
            'search_items'       => 'Search Questions',
            'not_found'          => 'No questions found',
            'not_found_in_trash' => 'No questions in Trash',
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

    gca_qa_setup_roles();
});

// ---------------------------------------------------------------------------
// Admin – menu item (forced, because automatic CPT menu registration is
// unreliable when show_in_menu is combined with a non-public CPT on some
// WordPress/plugin configurations).
// ---------------------------------------------------------------------------

add_action('admin_menu', function (): void {
    $pending_count = (int) (new WP_Query([
        'post_type'      => 'qa_question',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'meta_query'     => [[
            'relation' => 'OR',
            ['key' => GCA_QA_ANSWER_META, 'compare' => 'NOT EXISTS'],
            ['key' => GCA_QA_ANSWER_META, 'value' => '', 'compare' => '='],
        ]],
    ]))->found_posts;

    $label = 'Q&amp;A Questions';
    if ($pending_count > 0) {
        $label .= sprintf(
            ' <span class="awaiting-mod count-%d"><span class="pending-count">%d</span></span>',
            $pending_count,
            $pending_count
        );
    }

    add_menu_page(
        'Q&A Questions',
        $label,
        'edit_posts',
        'edit.php?post_type=qa_question',
        '',
        'dashicons-format-chat',
        26
    );
}, 5);

add_action('admin_head', function (): void {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'qa_question') {
        echo '<style>.page-title-action { display: none !important; }</style>';
    }
});

// ---------------------------------------------------------------------------
// Role + capability setup
// ---------------------------------------------------------------------------

function gca_qa_setup_roles(): void
{
    // Create the Q&A Moderator role if it doesn't exist.
    // Uses standard post caps so they can manage qa_question posts in wp-admin,
    // plus a single custom cap used as the REST API answer-permission gate.
    if (!get_role('qa_moderator')) {
        add_role('qa_moderator', 'Q&A Moderator', [
            'read'                => true,
            'edit_posts'          => true,
            'edit_others_posts'   => true,
            'publish_posts'       => true,
            'delete_posts'        => true,
            'delete_others_posts' => true,
            'qa_answer_questions' => true,
        ]);
    }

    // Grant the answer-permission cap to admins (one-time DB write, idempotent).
    $admin_role = get_role('administrator');
    if ($admin_role instanceof WP_Role && !$admin_role->has_cap('qa_answer_questions')) {
        $admin_role->add_cap('qa_answer_questions', true);
    }
}

// ---------------------------------------------------------------------------
// Admin – metaboxes
// ---------------------------------------------------------------------------

add_action('add_meta_boxes', function (): void {
    remove_meta_box('authordiv', 'qa_question', 'normal');
    remove_meta_box('authordiv', 'qa_question', 'side');

    if (!gca_flag_enabled('community-qa')) {
        return;
    }

    add_meta_box(
        'gca-qa-question-view',
        'Question Details',
        'gca_qa_question_view_metabox',
        'qa_question',
        'normal',
        'high'
    );

    add_meta_box(
        'gca-qa-answer',
        'Official Answer',
        'gca_qa_answer_metabox',
        'qa_question',
        'normal',
        'default'
    );
});

function gca_qa_question_view_metabox(WP_Post $post): void
{
    $asker  = get_userdata((int) $post->post_author);
    $name   = $asker instanceof WP_User ? esc_html($asker->display_name) : '—';
    $team   = $asker instanceof WP_User ? trim((string) get_user_meta($asker->ID, 'team', true)) : '';
    $date   = esc_html((string) get_post_time('j F Y \a\t g:ia', false, $post));
    ?>
    <table class="form-table" style="margin-top:0">
        <tr>
            <th style="width:130px;padding-top:8px">Asked by</th>
            <td style="padding-top:8px">
                <?php echo $name; ?>
                <?php if ($team) : ?>
                    <em style="color:#666">(<?php echo esc_html($team); ?>)</em>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th style="padding-top:8px">Date asked</th>
            <td style="padding-top:8px"><?php echo $date; ?></td>
        </tr>
        <tr>
            <th style="padding-top:10px;vertical-align:top">Question</th>
            <td>
                <div style="background:#f6f7f7;border-left:4px solid #1d70b8;padding:12px 16px;border-radius:0 4px 4px 0;font-style:italic;line-height:1.6">
                    <?php echo nl2br(esc_html($post->post_content)); ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
}

function gca_qa_answer_metabox(WP_Post $post): void
{
    wp_nonce_field('gca_qa_save_answer', 'gca_qa_answer_nonce');

    $answer    = (string) get_post_meta($post->ID, GCA_QA_ANSWER_META, true);
    $by_id     = (int) get_post_meta($post->ID, GCA_QA_ANSWERED_BY_META, true);
    $at        = (string) get_post_meta($post->ID, GCA_QA_ANSWERED_AT_META, true);
    $answerer  = $by_id ? get_userdata($by_id) : null;
    $answered  = !empty(trim($answer));
    ?>
    <?php if ($answered && $at) : ?>
        <p style="color:#555;font-size:12px;margin:0 0 10px">
            Last answered by
            <strong><?php echo $answerer instanceof WP_User ? esc_html($answerer->display_name) : 'Unknown'; ?></strong>
            on <?php echo esc_html(gmdate('j F Y', (int) strtotime($at))); ?>
        </p>
    <?php endif; ?>

    <label for="gca_qa_answer_text" style="font-weight:600;display:block;margin-bottom:6px">
        Answer text
        <span style="color:#767676;font-weight:normal">(leave blank to keep the question pending)</span>
    </label>
    <textarea
        id="gca_qa_answer_text"
        name="gca_qa_answer"
        rows="8"
        style="width:100%;font-size:14px;font-family:inherit"
        placeholder="Type the official answer here…"
    ><?php echo esc_textarea($answer); ?></textarea>

    <?php if ($answered) : ?>
        <p style="color:#007017;font-weight:600;margin-top:8px">✓ This question has an official answer and is visible to all staff.</p>
    <?php else : ?>
        <p style="color:#b1091a;margin-top:8px">⏳ No answer yet — this question is pending review.</p>
    <?php endif; ?>
    <?php
}

// Save metabox answer
add_action('save_post_qa_question', function (int $post_id): void {
    static $updating = false;
    if ($updating) {
        return;
    }

    if (
        !isset($_POST['gca_qa_answer_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gca_qa_answer_nonce'])), 'gca_qa_save_answer')
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || !current_user_can('qa_answer_questions')
    ) {
        return;
    }

    $raw    = isset($_POST['gca_qa_answer']) ? wp_unslash($_POST['gca_qa_answer']) : '';
    $answer = sanitize_textarea_field($raw);

    if (!empty(trim($answer))) {
        update_post_meta($post_id, GCA_QA_ANSWER_META, $answer);
        update_post_meta($post_id, GCA_QA_ANSWERED_BY_META, get_current_user_id());
        update_post_meta($post_id, GCA_QA_ANSWERED_AT_META, (string) current_time('mysql'));

        $updating = true;
        wp_update_post([
            'ID'            => $post_id,
            'post_date'     => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', true),
        ]);
        $updating = false;
    } else {
        delete_post_meta($post_id, GCA_QA_ANSWER_META);
        delete_post_meta($post_id, GCA_QA_ANSWERED_BY_META);
        delete_post_meta($post_id, GCA_QA_ANSWERED_AT_META);
    }
}, 10, 1);

// ---------------------------------------------------------------------------
// Admin – list columns
// ---------------------------------------------------------------------------

add_filter('manage_qa_question_posts_columns', function (array $cols): array {
    return [
        'cb'     => $cols['cb'] ?? '<input type="checkbox">',
        'title'  => 'Question',
        'asker'  => 'Asked by',
        'status' => 'Status',
        'date'   => 'Date asked',
    ];
});

add_action('manage_qa_question_posts_custom_column', function (string $col, int $post_id): void {
    switch ($col) {
        case 'asker':
            $post  = get_post($post_id);
            $asker = $post instanceof WP_Post ? get_userdata((int) $post->post_author) : null;
            echo $asker instanceof WP_User ? esc_html($asker->display_name) : '—';
            break;

        case 'status':
            $answer = get_post_meta($post_id, GCA_QA_ANSWER_META, true);
            if (!empty(trim((string) $answer))) {
                echo '<span style="color:#007017;font-weight:600">✓ Answered</span>';
            } else {
                echo '<span style="color:#b1091a;font-weight:600">⏳ Pending</span>';
            }
            break;
    }
}, 10, 2);

// Quick filter link: "Awaiting answer"
add_filter('views_edit-qa_question', function (array $views): array {
    $pending_count = (new WP_Query([
        'post_type'      => 'qa_question',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'meta_query'     => [[
            'relation' => 'OR',
            ['key' => GCA_QA_ANSWER_META, 'compare' => 'NOT EXISTS'],
            ['key' => GCA_QA_ANSWER_META, 'value' => '', 'compare' => '='],
        ]],
    ]))->found_posts;

    $is_active = isset($_GET['filter_status']) && sanitize_key($_GET['filter_status']) === 'pending';
    $url       = admin_url('edit.php?post_type=qa_question&filter_status=pending');

    $views['pending_answers'] = sprintf(
        '<a href="%s"%s>Awaiting answer <span class="count">(%d)</span></a>',
        esc_url($url),
        $is_active ? ' class="current" aria-current="page"' : '',
        (int) $pending_count
    );

    return $views;
});

// Apply pending filter to admin query
add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() || !$q->is_main_query() || $q->get('post_type') !== 'qa_question') {
        return;
    }

    $filter = isset($_GET['filter_status']) ? sanitize_key($_GET['filter_status']) : '';
    if ($filter === 'pending') {
        $q->set('meta_query', [[
            'relation' => 'OR',
            ['key' => GCA_QA_ANSWER_META, 'compare' => 'NOT EXISTS'],
            ['key' => GCA_QA_ANSWER_META, 'value' => '', 'compare' => '='],
        ]]);
    }
});

// ---------------------------------------------------------------------------
// REST routes
// ---------------------------------------------------------------------------

add_action('rest_api_init', function (): void {
    if (!gca_flag_enabled('community-qa')) {
        return;
    }

    register_rest_route('gca/v1', '/qa/questions', [
        'methods'             => 'GET',
        'callback'            => 'gca_qa_get_questions',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'page'     => ['default' => 1,  'sanitize_callback' => 'absint'],
            'per_page' => ['default' => 15, 'sanitize_callback' => 'absint'],
        ],
    ]);

    register_rest_route('gca/v1', '/qa/questions', [
        'methods'             => 'POST',
        'callback'            => 'gca_qa_create_question',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'content' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) > 0 && mb_strlen($v) <= 500,
            ],
        ],
    ]);

    register_rest_route('gca/v1', '/qa/questions/(?P<question_id>\d+)/answer', [
        'methods'             => 'POST',
        'callback'            => 'gca_qa_save_answer_rest',
        'permission_callback' => fn () => current_user_can('qa_answer_questions'),
        'args'                => [
            'question_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
            'answer'      => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) > 0 && mb_strlen($v) <= 5000,
            ],
        ],
    ]);

    register_rest_route('gca/v1', '/qa/questions/(?P<question_id>\d+)', [
        'methods'             => 'DELETE',
        'callback'            => 'gca_qa_delete_question',
        'permission_callback' => 'is_user_logged_in',
        'args'                => [
            'question_id' => ['validate_callback' => fn ($v) => is_numeric($v)],
        ],
    ]);
});

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function gca_qa_format_question(WP_Post $post, int $current_user_id): array
{
    $answer     = (string) get_post_meta($post->ID, GCA_QA_ANSWER_META, true);
    $is_answered = !empty(trim($answer));

    $asker      = get_userdata((int) $post->post_author);
    $asker_name = $asker instanceof WP_User ? html_entity_decode($asker->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';

    $answerer_id      = (int) get_post_meta($post->ID, GCA_QA_ANSWERED_BY_META, true);
    $answered_at      = (string) get_post_meta($post->ID, GCA_QA_ANSWERED_AT_META, true);
    $answerer_name    = '';
    $answerer_avatar  = '';
    $answerer_team    = '';
    $answerer_profile = '';

    if ($is_answered && $answerer_id) {
        $answerer = get_userdata($answerer_id);
        if ($answerer instanceof WP_User) {
            $answerer_name   = html_entity_decode($answerer->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $local           = trim((string) get_user_meta($answerer_id, 'google_profile_picture_local_url', true));
            $answerer_avatar = $local ?: (string) get_avatar_url($answerer_id, ['size' => 48]);
            $answerer_team   = trim((string) get_user_meta($answerer_id, 'team', true));
            if (gca_flag_enabled('staff-profiles')) {
                $answerer_profile = esc_url(home_url('/profile/' . $answerer->user_nicename));
            }
        }
    }

    $likes_meta   = defined('GCA_LC_POST_LIKES_META') ? GCA_LC_POST_LIKES_META : '_gca_lc_post_likes';
    $comment_type = defined('GCA_LC_COMMENT_TYPE')    ? GCA_LC_COMMENT_TYPE    : 'gca_comment';

    $liked_by = array_filter(array_map('intval', (array) get_post_meta($post->ID, $likes_meta, true)));

    $comment_count = $is_answered ? (int) get_comments([
        'post_id' => $post->ID,
        'type'    => $comment_type,
        'status'  => 'approve',
        'count'   => true,
    ]) : 0;

    $answered_at_iso = $answered_at ? (string) wp_date('c', (int) strtotime($answered_at)) : null;

    return [
        'id'               => $post->ID,
        'question_html'    => nl2br(esc_html($post->post_content)),
        'question_raw'     => $post->post_content,
        'status'           => $is_answered ? 'answered' : 'pending',
        'author_id'        => (int) $post->post_author,
        'author_name'      => $asker_name,
        'date_iso'         => (string) get_post_time('c', true, $post),
        'date_formatted'   => (string) get_post_time('j F Y', false, $post),
        'answer_html'      => $is_answered ? nl2br(esc_html($answer)) : null,
        'answer_raw'       => $is_answered ? $answer : null,
        'answered_at_iso'  => $answered_at_iso,
        'answerer_id'      => $answerer_id ?: null,
        'answerer_name'    => $answerer_name,
        'answerer_avatar'  => $answerer_avatar,
        'answerer_team'    => $answerer_team,
        'answerer_profile' => $answerer_profile,
        'is_own'           => (int) $post->post_author === $current_user_id,
        'can_moderate'     => current_user_can('qa_answer_questions'),
        'like_count'       => count($liked_by),
        'user_has_liked'   => in_array($current_user_id, $liked_by, true),
        'comment_count'    => $comment_count,
    ];
}

// ---------------------------------------------------------------------------
// Callbacks
// ---------------------------------------------------------------------------

function gca_qa_get_questions(WP_REST_Request $req): WP_REST_Response
{
    $page     = max(1, (int) $req->get_param('page'));
    $per_page = min(50, max(1, (int) $req->get_param('per_page')));
    $uid      = get_current_user_id();

    // Answered questions (paginated, newest-answered first)
    $answered_query = new WP_Query([
        'post_type'      => 'qa_question',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'meta_value',
        'meta_key'       => GCA_QA_ANSWERED_AT_META,
        'order'          => 'DESC',
        'no_found_rows'  => false,
        'meta_query'     => [[
            'key'     => GCA_QA_ANSWER_META,
            'value'   => '',
            'compare' => '!=',
        ]],
    ]);

    $answered = [];
    foreach ($answered_query->posts as $post) {
        if ($post instanceof WP_Post) {
            $answered[] = gca_qa_format_question($post, $uid);
        }
    }

    // Current user's own pending questions (all, shown separately)
    $pending_query = new WP_Query([
        'post_type'      => 'qa_question',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'author'         => $uid,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'meta_query'     => [[
            'relation' => 'OR',
            ['key' => GCA_QA_ANSWER_META, 'compare' => 'NOT EXISTS'],
            ['key' => GCA_QA_ANSWER_META, 'value' => '', 'compare' => '='],
        ]],
    ]);

    $my_pending = [];
    foreach ($pending_query->posts as $post) {
        if ($post instanceof WP_Post) {
            $my_pending[] = gca_qa_format_question($post, $uid);
        }
    }

    return new WP_REST_Response([
        'answered'    => $answered,
        'my_pending'  => $my_pending,
        'total'       => (int) $answered_query->found_posts,
        'total_pages' => (int) $answered_query->max_num_pages,
        'page'        => $page,
    ]);
}

function gca_qa_create_question(WP_REST_Request $req): WP_REST_Response
{
    $uid     = get_current_user_id();
    $content = (string) $req->get_param('content');

    $post_id = wp_insert_post([
        'post_type'    => 'qa_question',
        'post_status'  => 'publish',
        'post_title'   => wp_trim_words($content, 12, '…'),
        'post_content' => $content,
        'post_author'  => $uid,
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_REST_Response(['error' => $post_id->get_error_message()], 500);
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return new WP_REST_Response(['error' => 'Could not retrieve created question'], 500);
    }

    return new WP_REST_Response(gca_qa_format_question($post, $uid), 201);
}

function gca_qa_save_answer_rest(WP_REST_Request $req): WP_REST_Response
{
    $question_id = (int) $req->get_param('question_id');
    $answer      = (string) $req->get_param('answer');
    $uid         = get_current_user_id();

    $post = get_post($question_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'qa_question') {
        return new WP_REST_Response(['error' => 'Question not found'], 404);
    }

    update_post_meta($question_id, GCA_QA_ANSWER_META, $answer);
    update_post_meta($question_id, GCA_QA_ANSWERED_BY_META, $uid);
    update_post_meta($question_id, GCA_QA_ANSWERED_AT_META, (string) current_time('mysql'));

    wp_update_post([
        'ID'            => $question_id,
        'post_date'     => current_time('mysql'),
        'post_date_gmt' => current_time('mysql', true),
    ]);

    $post = get_post($question_id);
    return new WP_REST_Response(gca_qa_format_question($post, $uid));
}

function gca_qa_delete_question(WP_REST_Request $req): WP_REST_Response
{
    $question_id = (int) $req->get_param('question_id');
    $uid         = get_current_user_id();

    $post = get_post($question_id);
    if (!$post instanceof WP_Post || $post->post_type !== 'qa_question') {
        return new WP_REST_Response(['error' => 'Question not found'], 404);
    }

    if ((int) $post->post_author !== $uid && !current_user_can('delete_others_posts')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    wp_delete_post($question_id, true);
    return new WP_REST_Response(['deleted' => true]);
}
