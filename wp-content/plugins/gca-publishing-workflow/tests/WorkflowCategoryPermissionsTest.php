<?php

use WP_Mock\Tools\TestCase;

/**
 * Unit tests for GCA_Workflow_Category_Permissions.
 *
 * UNIT-CP.1  through UNIT-CP.4  — filter_contributor_page_list
 * UNIT-CP.5  through UNIT-CP.8  — block_contributor_out_of_scope_edit
 * UNIT-CP.9  through UNIT-CP.11 — save_team_assignment
 */
class WorkflowCategoryPermissionsTest extends TestCase {

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
        unset(
            $_POST['_gca_contributor_teams_nonce'],
            $_POST['gca_contributor_teams']
        );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.1 — non-contributor: tax_query left untouched
    // -------------------------------------------------------------------------

    public function test_page_list_filter_skips_non_contributors(): void {
        WP_Mock::userFunction( 'is_admin', [ 'return' => true ] );
        WP_Mock::userFunction( 'get_current_user_id', [ 'return' => 2 ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 2 ],
            'return' => $this->mock_user( 2, [ 'gca_publisher' ] ),
        ] );

        $query = Mockery::mock( 'WP_Query' );
        $query->shouldReceive( 'is_main_query' )->andReturn( true );
        $query->shouldReceive( 'get' )->with( 'post_type' )->andReturn( 'page' );
        $query->shouldNotReceive( 'set' );

        GCA_Workflow_Category_Permissions::filter_contributor_page_list( $query );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.2 — contributor assigned "all": author restriction lifted, no tax_query
    // -------------------------------------------------------------------------

    public function test_page_list_filter_lifts_author_for_all_teams_contributor(): void {
        WP_Mock::userFunction( 'is_admin', [ 'return' => true ] );
        WP_Mock::userFunction( 'get_current_user_id', [ 'return' => 1 ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $this->mock_user( 1, [ 'gca_contributor' ] ),
        ] );
        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ 1, '_gca_contributor_teams', true ],
            'return' => [ 'all' ],
        ] );

        $query = Mockery::mock( 'WP_Query' );
        $query->shouldReceive( 'is_main_query' )->andReturn( true );
        $query->shouldReceive( 'get' )->with( 'post_type' )->andReturn( 'page' );
        $query->shouldReceive( 'set' )->with( 'author', '' )->once();
        $query->shouldNotReceive( 'set' )->with( 'tax_query', Mockery::any() );

        GCA_Workflow_Category_Permissions::filter_contributor_page_list( $query );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.3 — contributor with no assignment: author restriction lifted, no tax_query
    // -------------------------------------------------------------------------

    public function test_page_list_filter_lifts_author_for_contributor_with_no_teams(): void {
        WP_Mock::userFunction( 'is_admin', [ 'return' => true ] );
        WP_Mock::userFunction( 'get_current_user_id', [ 'return' => 1 ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $this->mock_user( 1, [ 'gca_contributor' ] ),
        ] );
        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ 1, '_gca_contributor_teams', true ],
            'return' => [],
        ] );

        $query = Mockery::mock( 'WP_Query' );
        $query->shouldReceive( 'is_main_query' )->andReturn( true );
        $query->shouldReceive( 'get' )->with( 'post_type' )->andReturn( 'page' );
        $query->shouldReceive( 'set' )->with( 'author', '' )->once();
        $query->shouldNotReceive( 'set' )->with( 'tax_query', Mockery::any() );

        GCA_Workflow_Category_Permissions::filter_contributor_page_list( $query );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.4 — contributor with specific teams: tax_query appended
    // -------------------------------------------------------------------------

    public function test_page_list_filter_adds_tax_query_for_scoped_contributor(): void {
        WP_Mock::userFunction( 'is_admin', [ 'return' => true ] );
        WP_Mock::userFunction( 'get_current_user_id', [ 'return' => 1 ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $this->mock_user( 1, [ 'gca_contributor' ] ),
        ] );
        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ 1, '_gca_contributor_teams', true ],
            'return' => [ 15 ],
        ] );

        $query = Mockery::mock( 'WP_Query' );
        $query->shouldReceive( 'is_main_query' )->andReturn( true );
        $query->shouldReceive( 'get' )->with( 'post_type' )->andReturn( 'page' );
        $query->shouldReceive( 'get' )->with( 'tax_query' )->andReturn( [] );
        $query->shouldReceive( 'set' )->with( 'author', '' )->once();
        $query->shouldReceive( 'set' )->with(
            'tax_query',
            Mockery::on( function ( $tq ) {
                return isset( $tq[0]['taxonomy'] )
                    && 'responsible_team' === $tq[0]['taxonomy']
                    && in_array( 15, $tq[0]['terms'], true );
            } )
        )->once();

        GCA_Workflow_Category_Permissions::filter_contributor_page_list( $query );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.5 — non-contributor: caps returned unchanged
    // -------------------------------------------------------------------------

    public function test_edit_block_skips_non_contributors(): void {
        $user = $this->mock_user( 2, [ 'gca_publisher' ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 2 ],
            'return' => $user,
        ] );

        $caps   = [ 'edit_post' => true ];
        $result = GCA_Workflow_Category_Permissions::block_contributor_out_of_scope_edit(
            $caps,
            [ 'edit_post' ],
            [ 'edit_post', 2, 42 ],
            $user
        );

        $this->assertSame( $caps, $result );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.6 — contributor with "all" teams: caps returned unchanged
    // -------------------------------------------------------------------------

    public function test_edit_block_skips_all_teams_contributor(): void {
        $user = $this->mock_user( 1, [ 'gca_contributor' ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $user,
        ] );
        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ 1, '_gca_contributor_teams', true ],
            'return' => [ 'all' ],
        ] );

        $caps   = [ 'edit_post' => true ];
        $result = GCA_Workflow_Category_Permissions::block_contributor_out_of_scope_edit(
            $caps,
            [ 'edit_post' ],
            [ 'edit_post', 1, 42 ],
            $user
        );

        $this->assertSame( $caps, $result );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.7 — contributor, page IS in their team: caps unchanged
    // -------------------------------------------------------------------------

    public function test_edit_block_allows_in_scope_page(): void {
        $user = $this->mock_user( 1, [ 'gca_contributor' ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $user,
        ] );
        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ 1, '_gca_contributor_teams', true ],
            'return' => [ 15 ],
        ] );
        WP_Mock::userFunction( 'wp_get_object_terms', [
            'args'   => [ 42, 'responsible_team', [ 'fields' => 'ids' ] ],
            'return' => [ 15 ],
        ] );
        WP_Mock::userFunction( 'is_wp_error', [ 'return' => false ] );

        $caps   = [ 'edit_post' => true, 'edit_page' => true ];
        $result = GCA_Workflow_Category_Permissions::block_contributor_out_of_scope_edit(
            $caps,
            [ 'edit_post' ],
            [ 'edit_post', 1, 42 ],
            $user
        );

        $this->assertTrue( $result['edit_post'] );
        $this->assertTrue( $result['edit_page'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.8 — contributor, page NOT in their team: edit caps denied
    // -------------------------------------------------------------------------

    public function test_edit_block_denies_out_of_scope_page(): void {
        $user = $this->mock_user( 1, [ 'gca_contributor' ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $user,
        ] );
        WP_Mock::userFunction( 'get_user_meta', [
            'args'   => [ 1, '_gca_contributor_teams', true ],
            'return' => [ 15 ],
        ] );
        WP_Mock::userFunction( 'wp_get_object_terms', [
            'args'   => [ 42, 'responsible_team', [ 'fields' => 'ids' ] ],
            'return' => [ 99 ], // different team
        ] );
        WP_Mock::userFunction( 'is_wp_error', [ 'return' => false ] );

        $caps   = [ 'edit_post' => true, 'edit_page' => true ];
        $result = GCA_Workflow_Category_Permissions::block_contributor_out_of_scope_edit(
            $caps,
            [ 'edit_post' ],
            [ 'edit_post', 1, 42 ],
            $user
        );

        $this->assertFalse( $result['edit_post'] );
        $this->assertFalse( $result['edit_page'] );
        $this->assertFalse( $result['edit_published_pages'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.9 — invalid nonce: update_user_meta never called
    // -------------------------------------------------------------------------

    public function test_save_bails_on_bad_nonce(): void {
        $_POST['_gca_contributor_teams_nonce'] = 'bad_nonce';

        WP_Mock::userFunction( 'wp_unslash', [ 'return_arg' => 0 ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return_arg' => 0 ] );
        WP_Mock::userFunction( 'wp_verify_nonce', [ 'return' => false ] );
        WP_Mock::userFunction( 'update_user_meta', [ 'times' => 0 ] );

        GCA_Workflow_Category_Permissions::save_team_assignment( 1 );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.10 — "all" posted: saves ['all'] sentinel
    // -------------------------------------------------------------------------

    public function test_save_all_stores_all_sentinel(): void {
        $_POST['_gca_contributor_teams_nonce'] = 'valid_nonce';
        $_POST['gca_contributor_teams']        = [ 'all' ];

        WP_Mock::userFunction( 'wp_unslash', [ 'return_arg' => 0 ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return_arg' => 0 ] );
        WP_Mock::userFunction( 'wp_verify_nonce', [ 'return' => true ] );
        WP_Mock::userFunction( 'get_current_user_id', [ 'return' => 10 ] );
        WP_Mock::userFunction( 'current_user_can', [
            'args'   => [ 'edit_users' ],
            'return' => true,
        ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $this->mock_user( 1, [ 'gca_contributor' ] ),
        ] );
        WP_Mock::userFunction( 'update_user_meta', [
            'args'  => [ 1, '_gca_contributor_teams', [ 'all' ] ],
            'times' => 1,
        ] );

        GCA_Workflow_Category_Permissions::save_team_assignment( 1 );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.11 — valid and invalid term IDs: only real terms saved
    // -------------------------------------------------------------------------

    public function test_save_validates_and_stores_only_valid_term_ids(): void {
        $_POST['_gca_contributor_teams_nonce'] = 'valid_nonce';
        $_POST['gca_contributor_teams']        = [ '15', '0', 'banana', '99' ];

        WP_Mock::userFunction( 'wp_unslash', [ 'return_arg' => 0 ] );
        WP_Mock::userFunction( 'sanitize_text_field', [ 'return_arg' => 0 ] );
        WP_Mock::userFunction( 'wp_verify_nonce', [ 'return' => true ] );
        WP_Mock::userFunction( 'get_current_user_id', [ 'return' => 10 ] );
        WP_Mock::userFunction( 'current_user_can', [
            'args'   => [ 'edit_users' ],
            'return' => true,
        ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 1 ],
            'return' => $this->mock_user( 1, [ 'gca_contributor' ] ),
        ] );
        // term 15 exists, term 99 does not; 0 and 'banana' cast to 0 so term_exists is never called
        WP_Mock::userFunction( 'term_exists', [
            'args'   => [ 15, 'responsible_team' ],
            'return' => [ 'term_id' => 15 ],
        ] );
        WP_Mock::userFunction( 'term_exists', [
            'args'   => [ 99, 'responsible_team' ],
            'return' => null,
        ] );
        WP_Mock::userFunction( 'update_user_meta', [
            'args'  => [ 1, '_gca_contributor_teams', [ 15 ] ],
            'times' => 1,
        ] );

        GCA_Workflow_Category_Permissions::save_team_assignment( 1 );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.12 — non-publisher: grant skipped
    // -------------------------------------------------------------------------

    public function test_publisher_grant_skips_non_publishers(): void {
        $user   = $this->mock_user( 2, [ 'gca_publisher_admin' ] );
        $caps   = [ 'edit_users' => false ];
        $result = GCA_Workflow_Category_Permissions::grant_publisher_contributor_edit(
            $caps,
            [ 'edit_users' ],
            [ 'edit_user', 2, 5 ],
            $user
        );

        $this->assertFalse( $result['edit_users'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.13 — publisher editing self: grant skipped
    // -------------------------------------------------------------------------

    public function test_publisher_grant_skips_editing_self(): void {
        $user   = $this->mock_user( 3, [ 'gca_publisher' ] );
        $caps   = [ 'edit_users' => false ];
        $result = GCA_Workflow_Category_Permissions::grant_publisher_contributor_edit(
            $caps,
            [ 'edit_users' ],
            [ 'edit_user', 3, 3 ],
            $user
        );

        $this->assertFalse( $result['edit_users'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.14 — publisher editing non-contributor: grant skipped
    // -------------------------------------------------------------------------

    public function test_publisher_grant_skips_non_contributor_target(): void {
        $publisher      = $this->mock_user( 3, [ 'gca_publisher' ] );
        $otherPublisher = $this->mock_user( 7, [ 'gca_publisher' ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 3 ],
            'return' => $publisher,
        ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 7 ],
            'return' => $otherPublisher,
        ] );

        $caps   = [ 'edit_users' => false ];
        $result = GCA_Workflow_Category_Permissions::grant_publisher_contributor_edit(
            $caps,
            [ 'edit_users' ],
            [ 'edit_user', 3, 7 ],
            $publisher
        );

        $this->assertFalse( $result['edit_users'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-CP.15 — publisher editing contributor: edit_users granted
    // -------------------------------------------------------------------------

    public function test_publisher_grant_allows_editing_contributor(): void {
        $publisher   = $this->mock_user( 3, [ 'gca_publisher' ] );
        $contributor = $this->mock_user( 5, [ 'gca_contributor' ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 3 ],
            'return' => $publisher,
        ] );
        WP_Mock::userFunction( 'get_userdata', [
            'args'   => [ 5 ],
            'return' => $contributor,
        ] );

        $caps   = [ 'edit_users' => false ];
        $result = GCA_Workflow_Category_Permissions::grant_publisher_contributor_edit(
            $caps,
            [ 'edit_users' ],
            [ 'edit_user', 3, 5 ],
            $publisher
        );

        $this->assertTrue( $result['edit_users'] );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function mock_user( int $id, array $roles ): \Mockery\MockInterface {
        $user        = Mockery::mock( 'WP_User' );
        $user->roles = $roles;
        $user->ID    = $id;
        return $user;
    }
}
