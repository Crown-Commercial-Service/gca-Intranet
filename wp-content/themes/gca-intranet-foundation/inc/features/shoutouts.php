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

    register_taxonomy('shoutout_category', 'community_shoutout', [
        'label'        => 'Shout-out Categories',
        'labels'       => [
            'name'          => 'Shout-out Categories',
            'singular_name' => 'Shout-out Category',
            'add_new_item'  => 'Add New Category',
            'edit_item'     => 'Edit Category',
            'search_items'  => 'Search Categories',
            'not_found'     => 'No categories found',
        ],
        'hierarchical' => false,
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'edit.php?post_type=community_shoutout',
        'show_in_rest' => false,
        'rewrite'      => false,
        'query_var'    => false,
    ]);

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

add_action('admin_head', function (): void {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'community_shoutout') {
        echo '<style>.page-title-action { display: none !important; }</style>';
    }
});

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

    $recipient_name    = $recipient instanceof WP_User ? html_entity_decode($recipient->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
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
        $giver_team = trim((string) get_user_meta($giver->ID, 'business_title', true));
    }

    $cat_terms    = wp_get_post_terms($post->ID, 'shoutout_category');
    $category     = (!is_wp_error($cat_terms) && !empty($cat_terms)) ? $cat_terms[0]->name : '';

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
        'content_html'      => nl2br(htmlspecialchars($post->post_content, ENT_NOQUOTES, 'UTF-8', false)),
        'content_raw'       => $post->post_content,
        'giver_id'          => (int) $post->post_author,
        'giver_name'        => $giver instanceof WP_User ? html_entity_decode($giver->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '',
        'giver_avatar'      => $avatar_url,
        'giver_profile'     => $profile_url,
        'giver_team'        => $giver_team,
        'recipient_id'      => $recipient_id,
        'recipient_name'    => $recipient_name,
        'recipient_profile' => $recipient_profile,
        'date_iso'          => (string) get_post_time('c', true, $post),
        'date_formatted'    => (string) get_post_time('j F Y', false, $post),
        'category'          => $category,
        'is_own'            => (int) $post->post_author === $current_user_id,
        'can_delete'        => (int) $post->post_author === $current_user_id || current_user_can('manage_options'),
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

    // Category list
    register_rest_route('gca/v1', '/shoutouts/categories', [
        'methods'             => 'GET',
        'callback'            => 'gca_shoutout_get_categories',
        'permission_callback' => 'is_user_logged_in',
    ]);

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
                'page'         => ['default' => 1,  'sanitize_callback' => 'absint'],
                'per_page'     => ['default' => 15, 'sanitize_callback' => 'absint'],
                'recipient_id' => ['default' => 0,  'sanitize_callback' => 'absint'],
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
                    'validate_callback' => fn ($v) => is_string($v) && mb_strlen(trim($v)) > 0 && mb_strlen($v) <= 500,
                ],
                'category_id' => [
                    'required'          => false,
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
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

function gca_shoutout_get_categories(): WP_REST_Response
{
    $terms = get_terms(['taxonomy' => 'shoutout_category', 'hide_empty' => false]);
    if (is_wp_error($terms)) {
        return new WP_REST_Response([]);
    }
    return new WP_REST_Response(array_map(
        fn (WP_Term $t) => ['id' => $t->term_id, 'name' => $t->name],
        $terms
    ));
}

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
            'name'   => html_entity_decode($user->display_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'team'   => $team,
            'avatar' => $avatar_url,
        ];
    }

    return new WP_REST_Response($results);
}

function gca_shoutout_list(WP_REST_Request $req): WP_REST_Response
{
    $page         = max(1, (int) $req->get_param('page'));
    $per_page     = min(50, max(1, (int) $req->get_param('per_page')));
    $uid          = get_current_user_id();
    $recipient_id = (int) $req->get_param('recipient_id');

    $query_args = [
        'post_type'      => 'community_shoutout',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false,
    ];

    if ($recipient_id > 0) {
        $query_args['meta_query'] = [[
            'key'     => GCA_SHOUTOUT_RECIPIENT_META,
            'value'   => $recipient_id,
            'type'    => 'NUMERIC',
            'compare' => '=',
        ]];
    }

    $query = new WP_Query($query_args);

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
    $category_id  = (int) $req->get_param('category_id');

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

    if ($category_id > 0) {
        wp_set_post_terms($post_id, [$category_id], 'shoutout_category');
    }

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

    if ((int) $post->post_author !== $uid && !current_user_can('manage_options')) {
        return new WP_REST_Response(['error' => 'Forbidden'], 403);
    }

    wp_delete_post($shoutout_id, true);
    return new WP_REST_Response(['deleted' => true]);
}

// ---------------------------------------------------------------------------
// Gravity Forms integration — Shout-out Category management
// ---------------------------------------------------------------------------

if (class_exists('GFForms')) {

    /**
     * Returns the ID of the "Shout-out Categories" GF form, creating it once
     * if it hasn't been created yet.
     */
    function gca_shoutout_category_form_id(): int
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = (int) get_option('gca_shoutout_category_form_id', 0);
        if ($stored > 0 && GFAPI::get_form($stored) !== false) {
            $cached = $stored;
            return $cached;
        }

        $form_id = GFAPI::add_form([
            'title'  => 'Shout-out Categories',
            'fields' => [
                [
                    'type'              => 'text',
                    'id'                => 1,
                    'label'             => 'Category name',
                    'isRequired'        => true,
                    'allowsPrepopulate' => true,
                    'inputName'         => 'shoutout_term_name',
                ],
                [
                    'type'              => 'hidden',
                    'id'                => 2,
                    'label'             => 'Term ID',
                    'defaultValue'      => '0',
                    'allowsPrepopulate' => true,
                    'inputName'         => 'shoutout_term_id',
                ],
            ],
            'button'        => ['type' => 'text', 'text' => 'Save category'],
            'confirmations' => [
                1 => [
                    'id'        => 1,
                    'name'      => 'Default Confirmation',
                    'isDefault' => true,
                    'type'      => 'message',
                    'message'   => 'Category saved.',
                ],
            ],
        ]);

        if (is_wp_error($form_id)) {
            $cached = 0;
            return $cached;
        }

        update_option('gca_shoutout_category_form_id', $form_id);
        $cached = (int) $form_id;
        return $cached;
    }

    // Populate the name field when editing an existing term via ?shoutout_term_id=N
    add_filter('gform_field_value_shoutout_term_name', function (): string {
        $term_id = absint($_GET['shoutout_term_id'] ?? 0);
        if (!$term_id) {
            return '';
        }
        $term = get_term($term_id, 'shoutout_category');
        return ($term instanceof WP_Term) ? $term->name : '';
    });

    // Populate the hidden term ID field when editing
    add_filter('gform_field_value_shoutout_term_id', function (): string {
        return (string) absint($_GET['shoutout_term_id'] ?? 0);
    });

    // Create or update term on submission
    add_action('gform_after_submission', function (array $entry, array $form): void {
        $category_form_id = (int) get_option('gca_shoutout_category_form_id', 0);
        if ($category_form_id === 0 || (int) $form['id'] !== $category_form_id) {
            return;
        }

        if (!current_user_can('manage_categories')) {
            return;
        }

        $name    = sanitize_text_field((string) rgar($entry, '1'));
        $term_id = (int) rgar($entry, '2');

        if ($name === '') {
            return;
        }

        if ($term_id > 0 && get_term($term_id, 'shoutout_category') instanceof WP_Term) {
            wp_update_term($term_id, 'shoutout_category', [
                'name' => $name,
                'slug' => sanitize_title($name),
            ]);
        } else {
            wp_insert_term($name, 'shoutout_category');
        }
    }, 10, 2);

    // Admin submenu page
    add_action('admin_menu', function (): void {
        if (!gca_flag_enabled('community-shoutouts')) {
            return;
        }
        add_submenu_page(
            'edit.php?post_type=community_shoutout',
            'Shout-out Categories',
            'Categories',
            'manage_categories',
            'gca-shoutout-categories',
            'gca_shoutout_category_admin_page'
        );
    });

    function gca_shoutout_category_admin_page(): void
    {
        $base_url = admin_url('admin.php?page=gca-shoutout-categories');

        // Handle delete
        if (
            isset($_GET['action'], $_GET['term_id'], $_GET['_wpnonce'])
            && $_GET['action'] === 'delete'
            && wp_verify_nonce(sanitize_key((string) $_GET['_wpnonce']), 'gca_del_scat_' . (int) $_GET['term_id'])
            && current_user_can('manage_categories')
        ) {
            wp_delete_term((int) $_GET['term_id'], 'shoutout_category');
            echo '<div class="notice notice-success is-dismissible"><p>Category deleted.</p></div>';
        }

        $terms        = get_terms(['taxonomy' => 'shoutout_category', 'hide_empty' => false]);
        $form_id      = gca_shoutout_category_form_id();
        $edit_term_id = absint($_GET['shoutout_term_id'] ?? 0);
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Shout-out Categories</h1>
            <hr class="wp-header-end">

            <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
            <table class="wp-list-table widefat fixed striped" style="margin-top:16px;margin-bottom:32px">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width:80px">Used</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($terms as $term) :
                        $edit_url   = esc_url(add_query_arg('shoutout_term_id', $term->term_id, $base_url));
                        $delete_url = esc_url(wp_nonce_url(
                            add_query_arg(['action' => 'delete', 'term_id' => $term->term_id], $base_url),
                            'gca_del_scat_' . $term->term_id
                        ));
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($term->name); ?></strong></td>
                        <td><?php echo (int) $term->count; ?></td>
                        <td>
                            <a href="<?php echo $edit_url; ?>">Edit</a>
                            &nbsp;|&nbsp;
                            <a href="<?php echo $delete_url; ?>"
                               style="color:#b32d2e"
                               onclick="return confirm('Delete &ldquo;<?php echo esc_js($term->name); ?>&rdquo;? Existing shout-outs will lose this category.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else : ?>
            <p style="color:#646970;margin-top:16px">No categories yet — add one below.</p>
            <?php endif; ?>

            <h2><?php echo $edit_term_id > 0 ? 'Edit Category' : 'Add New Category'; ?></h2>

            <?php if ($edit_term_id > 0) : ?>
            <p><a href="<?php echo esc_url($base_url); ?>">&larr; Cancel and add new instead</a></p>
            <?php endif; ?>

            <?php if ($form_id > 0) :
                echo do_shortcode('[gravityforms id="' . $form_id . '" field_values="shoutout_term_id=' . $edit_term_id . '" ajax="true"]');
            else : ?>
                <p>Gravity Forms is not active &mdash; please activate it to manage categories here.</p>
            <?php endif; ?>
        </div>
        <?php
    }
}
