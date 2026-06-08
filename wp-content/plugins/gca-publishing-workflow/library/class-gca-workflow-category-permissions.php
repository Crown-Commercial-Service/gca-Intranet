<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GCA_Workflow_Category_Permissions {

    const META_KEY = '_gca_contributor_teams';
    const TAXONOMY = 'responsible_team';

    public static function init(): void {
        add_action( 'show_user_profile',          [ __CLASS__, 'render_team_assignment_ui' ] );
        add_action( 'edit_user_profile',          [ __CLASS__, 'render_team_assignment_ui' ] );
        add_action( 'personal_options_update',    [ __CLASS__, 'save_team_assignment' ] );
        add_action( 'edit_user_profile_update',   [ __CLASS__, 'save_team_assignment' ] );
        add_action( 'pre_get_posts',              [ __CLASS__, 'filter_contributor_page_list' ] );
        add_filter( 'views_edit-page',            [ __CLASS__, 'filter_contributor_page_counts' ] );
        add_filter( 'user_has_cap',               [ __CLASS__, 'block_contributor_out_of_scope_edit' ], 10, 4 );
        add_filter( 'user_has_cap',               [ __CLASS__, 'grant_publisher_contributor_edit' ], 5, 4 );
    }

    // -------------------------------------------------------------------------
    // Admin UI — shown on the user edit/profile screen
    // -------------------------------------------------------------------------

    public static function render_team_assignment_ui( WP_User $profile_user ): void {
        $current_user_id = get_current_user_id();

        $can_assign = current_user_can( 'edit_users' )
                   || GCA_Workflow_Roles::user_has_role( $current_user_id, GCA_Workflow_Roles::PUBLISHER );

        if ( ! $can_assign ) {
            return;
        }

        if ( ! GCA_Workflow_Roles::user_has_role( $profile_user->ID, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }

        $terms = get_terms( [
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        if ( is_wp_error( $terms ) ) {
            $terms = [];
        }

        $saved  = self::get_contributor_teams( $profile_user->ID );
        $is_all = in_array( 'all', $saved, true );

        $selected_count = $is_all ? 'All' : ( count( $saved ) > 0 ? count( $saved ) . ' selected' : 'None selected' );

        wp_nonce_field( 'gca_contributor_teams_' . $profile_user->ID, '_gca_contributor_teams_nonce' );
        ?>
        <h2>
            <button type="button"
                    id="gca-teams-toggle"
                    aria-expanded="false"
                    aria-controls="gca-teams-body"
                    style="background:none;border:none;cursor:pointer;font-size:inherit;font-weight:600;padding:0;display:flex;align-items:center;gap:8px;">
                <span id="gca-teams-arrow" style="font-size:0.75em;transition:transform 0.2s;display:inline-block;">&#9654;</span>
                <?php esc_html_e( 'Content Permissions (Responsible Team)', 'gca' ); ?>
                <span id="gca-teams-summary"
                      style="font-size:0.75em;font-weight:400;color:#646970;margin-left:4px;">
                    — <?php echo esc_html( $selected_count ); ?>
                </span>
            </button>
        </h2>
        <div id="gca-teams-body" style="display:none;">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Allowed directorates/teams', 'gca' ); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox"
                                       name="gca_contributor_teams[]"
                                       value="all"
                                       id="gca_team_all"
                                       <?php checked( $is_all ); ?>>
                                <strong><?php esc_html_e( 'All directorates/teams', 'gca' ); ?></strong>
                            </label>
                            <br><br>
                            <?php foreach ( $terms as $term ) : ?>
                                <label>
                                    <input type="checkbox"
                                           name="gca_contributor_teams[]"
                                           value="<?php echo esc_attr( $term->term_id ); ?>"
                                           class="gca-team-checkbox"
                                           <?php checked( $is_all || in_array( $term->term_id, $saved, true ) ); ?>
                                           <?php disabled( $is_all ); ?>>
                                    <?php echo esc_html( $term->name ); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        <script>
        ( function () {
            var toggle    = document.getElementById( 'gca-teams-toggle' );
            var body      = document.getElementById( 'gca-teams-body' );
            var arrow     = document.getElementById( 'gca-teams-arrow' );
            var summary   = document.getElementById( 'gca-teams-summary' );
            var allBox    = document.getElementById( 'gca_team_all' );
            var teamBoxes = document.querySelectorAll( '.gca-team-checkbox' );

            function updateSummary() {
                if ( allBox.checked ) {
                    summary.textContent = '— All';
                    return;
                }
                var count = Array.from( teamBoxes ).filter( function ( cb ) { return cb.checked; } ).length;
                summary.textContent = count > 0 ? '— ' + count + ' selected' : '— None selected';
            }

            toggle.addEventListener( 'click', function () {
                var open = body.style.display !== 'none';
                body.style.display  = open ? 'none' : 'block';
                toggle.setAttribute( 'aria-expanded', String( ! open ) );
                arrow.style.transform = open ? '' : 'rotate(90deg)';
            } );

            if ( allBox ) {
                // On change: sync both disabled and checked state.
                allBox.addEventListener( 'change', function () {
                    teamBoxes.forEach( function ( cb ) {
                        cb.disabled = allBox.checked;
                        cb.checked  = allBox.checked;
                    } );
                    updateSummary();
                } );
                teamBoxes.forEach( function ( cb ) { cb.addEventListener( 'change', updateSummary ); } );

                // On init: only apply disabled state — don't override PHP-rendered checked values.
                teamBoxes.forEach( function ( cb ) { cb.disabled = allBox.checked; } );
                updateSummary();
            }
        } )();
        </script>
        <?php
    }

    // -------------------------------------------------------------------------
    // Save
    // -------------------------------------------------------------------------

    public static function save_team_assignment( int $user_id ): void {
        if ( ! isset( $_POST['_gca_contributor_teams_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_gca_contributor_teams_nonce'] ) ), 'gca_contributor_teams_' . $user_id ) ) {
            return;
        }

        $current_user_id = get_current_user_id();
        $can_save = current_user_can( 'edit_users' )
                 || GCA_Workflow_Roles::user_has_role( $current_user_id, GCA_Workflow_Roles::PUBLISHER );

        if ( ! $can_save ) {
            return;
        }

        if ( ! GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }

        $posted = isset( $_POST['gca_contributor_teams'] ) ? (array) $_POST['gca_contributor_teams'] : [];

        if ( in_array( 'all', $posted, true ) ) {
            update_user_meta( $user_id, self::META_KEY, [ 'all' ] );
            return;
        }

        // Validate each value is a real term ID in the taxonomy.
        $valid_ids = [];
        foreach ( $posted as $raw ) {
            $term_id = (int) $raw;
            if ( $term_id > 0 && term_exists( $term_id, self::TAXONOMY ) ) {
                $valid_ids[] = $term_id;
            }
        }

        update_user_meta( $user_id, self::META_KEY, $valid_ids );
    }

    // -------------------------------------------------------------------------
    // Page list filtering
    // -------------------------------------------------------------------------

    public static function filter_contributor_page_list( WP_Query $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( 'page' !== $query->get( 'post_type' ) ) {
            return;
        }

        $user_id = get_current_user_id();
        if ( ! GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return;
        }

        $teams = self::get_contributor_teams( $user_id );

        // No teams assigned — keep the default author=me restriction.
        if ( empty( $teams ) ) {
            return;
        }

        // Lift the default "author = me" restriction so contributors can see all
        // pages within their assigned team, not just their own authored pages.
        $query->set( 'author', '' );

        if ( in_array( 'all', $teams, true ) ) {
            return;
        }

        $existing = (array) $query->get( 'tax_query' );
        $existing[] = [
            'taxonomy' => self::TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $teams,
            'operator' => 'IN',
        ];
        $query->set( 'tax_query', $existing );
    }

    // -------------------------------------------------------------------------
    // Fix view-filter counts (All / Mine / Published / Drafts / Trash)
    // -------------------------------------------------------------------------

    public static function filter_contributor_page_counts( array $views ): array {
        $user_id = get_current_user_id();
        if ( ! GCA_Workflow_Roles::user_has_role( $user_id, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return $views;
        }
        $teams = self::get_contributor_teams( $user_id );
        if ( empty( $teams ) || in_array( 'all', $teams, true ) ) {
            return $views;
        }

        $tax_query = [ [
            'taxonomy' => self::TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $teams,
            'operator' => 'IN',
        ] ];

        $base = [
            'post_type'              => 'page',
            'tax_query'              => $tax_query,
            'fields'                 => 'ids',
            'posts_per_page'         => 1,
            'no_found_rows'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];

        $non_trash = [ 'publish', 'pending', 'draft', 'future', 'private' ];

        foreach ( $views as $status => $link_html ) {
            $args = $base;
            if ( 'all' === $status ) {
                $args['post_status'] = $non_trash;
            } elseif ( 'mine' === $status ) {
                $args['post_status'] = $non_trash;
                $args['author']      = $user_id;
            } elseif ( 'trash' === $status ) {
                $args['post_status'] = 'trash';
            } else {
                $args['post_status'] = $status;
            }

            $count              = ( new WP_Query( $args ) )->found_posts;
            $views[ $status ]   = preg_replace( '/\([\d,]+\)/', '(' . number_format_i18n( $count ) . ')', $link_html );
        }

        return $views;
    }

    // -------------------------------------------------------------------------
    // Allow publishers to view/edit contributor profile pages
    // -------------------------------------------------------------------------

    public static function grant_publisher_contributor_edit( array $all_caps, array $cap_list, array $args, WP_User $user ): array {
        if ( ! GCA_Workflow_Roles::user_has_role( $user->ID, GCA_Workflow_Roles::PUBLISHER ) ) {
            return $all_caps;
        }

        // Only fire for the edit_user meta capability (editing another user's profile).
        if ( ! isset( $args[0] ) || 'edit_user' !== $args[0] ) {
            return $all_caps;
        }

        $target_user_id = isset( $args[2] ) ? (int) $args[2] : 0;
        if ( ! $target_user_id || $target_user_id === $user->ID ) {
            return $all_caps;
        }

        if ( ! GCA_Workflow_Roles::user_has_role( $target_user_id, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return $all_caps;
        }

        $all_caps['edit_users'] = true;
        return $all_caps;
    }

    // -------------------------------------------------------------------------
    // Block direct edit access to out-of-scope pages
    // -------------------------------------------------------------------------

    public static function block_contributor_out_of_scope_edit( array $all_caps, array $cap_list, array $args, WP_User $user ): array {
        if ( ! GCA_Workflow_Roles::user_has_role( $user->ID, GCA_Workflow_Roles::CONTRIBUTOR ) ) {
            return $all_caps;
        }

        $edit_caps = [ 'edit_post', 'edit_page', 'edit_published_pages' ];
        if ( empty( array_intersect( $cap_list, $edit_caps ) ) ) {
            return $all_caps;
        }

        $post_id = isset( $args[2] ) ? (int) $args[2] : 0;
        if ( ! $post_id ) {
            return $all_caps;
        }

        $teams = self::get_contributor_teams( $user->ID );

        if ( empty( $teams ) || in_array( 'all', $teams, true ) ) {
            return $all_caps;
        }

        $post_terms = wp_get_object_terms( $post_id, self::TAXONOMY, [ 'fields' => 'ids' ] );
        if ( is_wp_error( $post_terms ) ) {
            return $all_caps;
        }

        // If no overlap between the page's terms and the contributor's allowed terms, deny.
        if ( empty( array_intersect( $teams, $post_terms ) ) ) {
            foreach ( $edit_caps as $cap ) {
                $all_caps[ $cap ] = false;
            }
        }

        return $all_caps;
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private static function get_contributor_teams( int $user_id ): array {
        $meta = get_user_meta( $user_id, self::META_KEY, true );
        if ( ! is_array( $meta ) ) {
            return [];
        }
        // Cast term IDs back to int (user meta comes back as strings).
        return array_map( function ( $v ) {
            return $v === 'all' ? 'all' : (int) $v;
        }, $meta );
    }
}
