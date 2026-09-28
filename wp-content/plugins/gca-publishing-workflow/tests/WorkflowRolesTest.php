<?php

use WP_Mock\Tools\TestCase;

/**
 * Unit tests for GCA_Workflow_Roles.
 *
 * UNIT-R.1 through UNIT-R.9
 */
class WorkflowRolesTest extends TestCase {

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // UNIT-R.1 — creates gca_contributor if absent
    // -------------------------------------------------------------------------

    public function test_init_creates_contributor_role_when_absent(): void {
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_contributor' ],
            'return' => null,
            'times'  => 1,
        ]);
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_publisher' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_community_host' ],
            'return' => null,
        ]);
        // Cleanup steps that run on every init(): publisher-admin retirement,
        // legacy role removal, revisor role deprecation — all no-ops when absent.
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_publisher_admin' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'editor' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'author' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'contributor' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('get_option', [
            'args'   => [ 'wp_user_roles', [] ],
            'return' => [],
        ]);

        WP_Mock::userFunction('add_role')->andReturn( $this->mock_role() );
        WP_Mock::userFunction('add_filter');
        WP_Mock::userFunction('add_action');

        GCA_Workflow_Roles::init();

        // add_role should have been called for gca_contributor.
        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.2 — idempotent: calling init() twice doesn't error or duplicate caps
    // -------------------------------------------------------------------------

    public function test_init_is_idempotent_when_role_exists(): void {
        $role = $this->mock_role();
        $role->shouldReceive('add_cap')->andReturn( null );

        // Catch-all get_role() makes every cleanup branch (publisher-admin retirement,
        // legacy role removal) think its target role "exists", so their downstream
        // calls need mocking too.
        WP_Mock::userFunction('get_role')->andReturn( $role );
        WP_Mock::userFunction('get_users')->andReturn( [] );
        WP_Mock::userFunction('remove_role')->andReturn( true );
        WP_Mock::userFunction('get_option', [
            'args'   => [ 'wp_user_roles', [] ],
            'return' => [],
        ]);
        WP_Mock::userFunction('add_filter');
        WP_Mock::userFunction('add_action');

        // Should not throw.
        GCA_Workflow_Roles::init();
        GCA_Workflow_Roles::init();

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.3 — gca_contributor does NOT have publish_pages
    // -------------------------------------------------------------------------

    public function test_contributor_caps_do_not_include_publish_pages(): void {
        $caps = $this->get_contributor_caps();
        $this->assertArrayNotHasKey( 'publish_pages', $caps );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.4 — map_meta_cap blocks create_pages for contributors
    // -------------------------------------------------------------------------

    public function test_block_contributor_page_creation_returns_do_not_allow(): void {
        $user = $this->mock_user( 1, [ 'gca_contributor' ] );
        WP_Mock::userFunction('get_userdata', [
            'args'   => [ 1 ],
            'return' => $user,
        ]);

        $result = GCA_Workflow_Roles::block_contributor_page_creation(
            [ 'edit_pages' => true ],
            [ 'create_pages' ],
            [],
            $user
        );

        $this->assertTrue( $result['edit_pages'] );
        $this->assertFalse( $result['create_pages'] );
    }

    public function test_block_contributor_page_creation_passes_through_for_publisher(): void {
        $user = $this->mock_user( 2, [ 'gca_publisher' ] );
        WP_Mock::userFunction('get_userdata', [
            'args'   => [ 2 ],
            'return' => $user,
        ]);

        $original = [ 'publish_pages' => true ];
        $result = GCA_Workflow_Roles::block_contributor_page_creation(
            $original,
            [ 'create_pages' ],
            [],
            $user
        );

        $this->assertSame( $original, $result );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.5 — gca_publisher has publish_pages
    // -------------------------------------------------------------------------

    public function test_publisher_caps_include_publish_pages(): void {
        $caps = $this->get_publisher_caps();
        $this->assertArrayHasKey( 'publish_pages', $caps );
        $this->assertTrue( $caps['publish_pages'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.6 — retire_publisher_admin_role migrates existing users to
    // administrator and removes the role. The role was scrapped in favour of a
    // 2-role model (Contributor, Publisher); administrator already covers the
    // same capabilities.
    // -------------------------------------------------------------------------

    public function test_retire_publisher_admin_role_migrates_users_and_removes_role(): void {
        $user = Mockery::mock( 'WP_User' );
        $user->shouldReceive('set_role')->with( 'administrator' )->once();

        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_publisher_admin' ],
            'return' => $this->mock_role(),
        ]);
        WP_Mock::userFunction('get_users', [
            'args'   => [ [ 'role' => 'gca_publisher_admin' ] ],
            'return' => [ $user ],
        ]);
        WP_Mock::userFunction('remove_role', [
            'args'  => [ 'gca_publisher_admin' ],
            'times' => 1,
        ]);

        $method = new ReflectionMethod( GCA_Workflow_Roles::class, 'retire_publisher_admin_role' );
        $method->setAccessible( true );
        $method->invoke( null );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.7 — retire_publisher_admin_role is a no-op once the role is gone
    // -------------------------------------------------------------------------

    public function test_retire_publisher_admin_role_is_noop_when_already_removed(): void {
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_publisher_admin' ],
            'return' => null,
        ]);
        // get_users/remove_role are intentionally left unmocked — WP_Mock will
        // error if either is called, proving the early return.

        $method = new ReflectionMethod( GCA_Workflow_Roles::class, 'retire_publisher_admin_role' );
        $method->setAccessible( true );
        $method->invoke( null );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.8 — migration reassigns editor users to gca_publisher
    // -------------------------------------------------------------------------

    public function test_migration_reassigns_editor_users_to_publisher(): void {
        $user = Mockery::mock( 'WP_User' );
        $user->shouldReceive('set_role')->with( 'gca_publisher' )->once();

        WP_Mock::userFunction('get_users', [
            'args'   => [ [ 'role' => 'editor' ] ],
            'return' => [ $user ],
        ]);
        WP_Mock::userFunction('get_users', [
            'args'   => [ [ 'role' => 'author' ] ],
            'return' => [],
        ]);
        WP_Mock::userFunction('get_users', [
            'args'   => [ [ 'role' => 'contributor' ] ],
            'return' => [],
        ]);

        $role = $this->mock_role_with_caps( [ 'edit_posts' => true ] );
        WP_Mock::userFunction('get_role')->andReturn( $role );
        WP_Mock::userFunction('get_option')->andReturn( [] );
        WP_Mock::userFunction('update_option')->andReturn( true );
        WP_Mock::userFunction('add_role')->andReturn( null );
        WP_Mock::userFunction('remove_role')->andReturn( true );

        // Access the private method via reflection.
        $method = new ReflectionMethod( GCA_Workflow_Roles::class, 'migrate_and_deprecate_legacy_roles' );
        $method->setAccessible( true );
        $method->invoke( null );

        // Mockery verifies set_role was called.
        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.9 — migration removes the legacy role shells entirely (remove_role)
    // rather than manually stripping caps first
    // -------------------------------------------------------------------------

    public function test_migration_strips_caps_from_legacy_editor_role(): void {
        WP_Mock::userFunction('get_users')->andReturn( [] );

        $role = $this->mock_role_with_caps( [ 'edit_posts' => true, 'publish_posts' => true ] );

        WP_Mock::userFunction('get_role')->andReturn( $role );
        WP_Mock::userFunction('get_option')->andReturn( [] );
        WP_Mock::userFunction('update_option')->andReturn( true );
        // Migration iterates all 3 legacy slugs (editor/author/contributor).
        WP_Mock::userFunction('remove_role', [ 'times' => 3 ])->andReturn( true );

        $method = new ReflectionMethod( GCA_Workflow_Roles::class, 'migrate_and_deprecate_legacy_roles' );
        $method->setAccessible( true );
        $method->invoke( null );

        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function mock_role(): \Mockery\MockInterface {
        $role = Mockery::mock( 'WP_Role' );
        $role->capabilities = [];
        $role->shouldReceive('add_cap')->andReturn( null );
        return $role;
    }

    private function mock_role_with_caps( array $caps ): \Mockery\MockInterface {
        $role = Mockery::mock( 'WP_Role' );
        $role->capabilities = $caps;
        $role->shouldReceive('remove_cap')->andReturn( null );
        $role->shouldReceive('add_cap')->andReturn( null );
        return $role;
    }

    private function mock_user( int $id, array $roles ): \Mockery\MockInterface {
        $user = Mockery::mock( 'WP_User' );
        $user->roles = $roles;
        $user->ID    = $id;
        return $user;
    }

    private function get_contributor_caps(): array {
        return (new ReflectionClassConstant( GCA_Workflow_Roles::class, 'CONTRIBUTOR_CAPS' ))->getValue();
    }

    private function get_publisher_caps(): array {
        $base      = (new ReflectionClassConstant( GCA_Workflow_Roles::class, 'CONTRIBUTOR_CAPS' ))->getValue();
        $publisher = (new ReflectionClassConstant( GCA_Workflow_Roles::class, 'PUBLISHER_CAPS' ))->getValue();
        return array_merge( $base, $publisher );
    }

}
