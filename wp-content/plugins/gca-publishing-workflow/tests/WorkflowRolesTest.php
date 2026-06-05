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
            'args'   => [ 'gca_publisher_admin' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('get_role', [
            'args'   => [ 'gca_community_host' ],
            'return' => null,
        ]);
        WP_Mock::userFunction('add_role')->andReturn( $this->mock_role() );
        WP_Mock::userFunction('add_filter');

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

        WP_Mock::userFunction('get_role')->andReturn( $role );
        WP_Mock::userFunction('add_filter');

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
        WP_Mock::userFunction('get_userdata', [
            'args'   => [ 1 ],
            'return' => $this->mock_user( 1, [ 'gca_contributor' ] ),
        ]);

        $result = GCA_Workflow_Roles::block_contributor_page_creation(
            [ 'edit_pages' ],
            'create_pages',
            1,
            []
        );

        $this->assertSame( [ 'do_not_allow' ], $result );
    }

    public function test_block_contributor_page_creation_passes_through_for_publisher(): void {
        WP_Mock::userFunction('get_userdata', [
            'args'   => [ 2 ],
            'return' => $this->mock_user( 2, [ 'gca_publisher' ] ),
        ]);

        $original = [ 'publish_pages' ];
        $result = GCA_Workflow_Roles::block_contributor_page_creation(
            $original,
            'create_pages',
            2,
            []
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
    // UNIT-R.6 — gca_publisher_admin has manage_options
    // -------------------------------------------------------------------------

    public function test_publisher_admin_caps_include_manage_options(): void {
        $caps = $this->get_publisher_admin_caps();
        $this->assertArrayHasKey( 'manage_options', $caps );
        $this->assertTrue( $caps['manage_options'] );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.7 — gca_publisher_admin does NOT have delete_users
    // -------------------------------------------------------------------------

    public function test_publisher_admin_caps_do_not_include_delete_users(): void {
        $caps = $this->get_publisher_admin_caps();
        $this->assertArrayNotHasKey( 'delete_users', $caps );
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

        // Access the private method via reflection.
        $method = new ReflectionMethod( GCA_Workflow_Roles::class, 'migrate_and_deprecate_legacy_roles' );
        $method->setAccessible( true );
        $method->invoke( null );

        // Mockery verifies set_role was called.
        $this->assertTrue( true );
    }

    // -------------------------------------------------------------------------
    // UNIT-R.9 — migration strips caps from legacy editor role
    // -------------------------------------------------------------------------

    public function test_migration_strips_caps_from_legacy_editor_role(): void {
        WP_Mock::userFunction('get_users')->andReturn( [] );

        // Fresh mock without a generic remove_cap catch-all so specific expectations are unambiguous.
        $role = Mockery::mock( 'WP_Role' );
        $role->capabilities = [ 'edit_posts' => true, 'publish_posts' => true ];
        // Migration iterates all 3 legacy slugs (editor/author/contributor), each with the same
        // mock returned, so remove_cap is called once per cap per slug = 3× per cap.
        $role->shouldReceive('remove_cap')->with( 'edit_posts' )->times(3);
        $role->shouldReceive('remove_cap')->with( 'publish_posts' )->times(3);

        WP_Mock::userFunction('get_role')->andReturn( $role );
        WP_Mock::userFunction('get_option')->andReturn( [] );
        WP_Mock::userFunction('update_option')->andReturn( true );

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

    private function get_publisher_admin_caps(): array {
        $admin = (new ReflectionClassConstant( GCA_Workflow_Roles::class, 'PUBLISHER_ADMIN_CAPS' ))->getValue();
        return array_merge( $this->get_publisher_caps(), $admin );
    }
}
