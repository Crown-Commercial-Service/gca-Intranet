<?php

use WP_Mock\Tools\TestCase;

/**
 * Integration-style tests for GCA_Sync_Users::run().
 *
 * The entire flow is exercised through the public run() entry point.
 * HTTP calls made by GCA_Workday_API are intercepted via WP_Mock so no real
 * network traffic occurs. WordPress user functions are also mocked.
 *
 * Env vars required by GCA_Workday_API are written with putenv() and cleared
 * after each test.
 */
class SyncUsersSyncTest extends TestCase {

    // -------------------------------------------------------------------------
    // Setup / teardown
    // -------------------------------------------------------------------------

    public function setUp(): void {
        parent::setUp();
        $this->setApiEnv();
        putenv('WORKDAY_SYNC_ENABLE_DELETE=false');

        // The run() entry point checks/sets a transient to prevent concurrent runs.
        WP_Mock::userFunction('get_transient', ['return' => false]);
        WP_Mock::userFunction('set_transient',  ['return' => true]);

        // current_time() is called by soft_delete_user() to record deleted_at.
        WP_Mock::userFunction('current_time', ['return' => date('Y-m-d H:i:s')]);
    }

    public function tearDown(): void {
        parent::tearDown();
        foreach ([
            'WORKDAY_TOKEN_ENDPOINT', 'WORKDAY_CLIENT_ID', 'WORKDAY_CLIENT_SECRET',
            'WORKDAY_SCOPE', 'WORKDAY_API_BASE_URL', 'WORKDAY_STAFF_RESOURCE',
            'WORKDAY_STAFF_API_PARAMS', 'WORKDAY_API_SUBSCRIPTION_KEY',
            'WORKDAY_SYNC_ENABLE_DELETE',
        ] as $key) {
            putenv($key);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function setApiEnv(): void {
        putenv('WORKDAY_TOKEN_ENDPOINT=https://login.example.com/token');
        putenv('WORKDAY_CLIENT_ID=test-client');
        putenv('WORKDAY_CLIENT_SECRET=test-secret');
        putenv('WORKDAY_SCOPE=https://api.example.com/.default');
        putenv('WORKDAY_API_BASE_URL=https://apim.example.com');
        putenv('WORKDAY_STAFF_RESOURCE=/paths/invoke/staff');
        putenv('WORKDAY_STAFF_API_PARAMS=');
        putenv('WORKDAY_API_SUBSCRIPTION_KEY=');
    }

    private function staffRecord(array $overrides = []): array {
        return array_merge([
            'Email'          => 'jane.smith@gca.gov.uk',
            'EmployeeName'   => 'Jane Smith',
            'EmployeeKey'    => '42',
            'ItemInternalId' => 'wd-item-001',
            'JobTitle'       => 'Senior Engineer',
            'Team'           => 'Engineering (Bob Manager)',
            'Directorate'    => 'Digital',
            'Manager'        => 'Bob Manager',
            'ManagerEmail'   => 'bob.manager@gca.gov.uk',
        ], $overrides);
    }

    /**
     * Stubs the GCA_Workday_API HTTP round-trip to return $staff.
     *
     * is_wp_error is NOT set up here so each test can control its own sequence.
     * The caller must mock is_wp_error separately.
     */
    private function mockApiReturning(array $staff): void {
        $tokenBody = json_encode(['access_token' => 'test-token', 'expires_in' => 3600]);
        $staffBody = json_encode($staff);

        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_res_']]);
        WP_Mock::userFunction('wp_remote_get',  ['return' => ['_staff_res_']]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$tokenBody, $staffBody],
        ]);
    }

    /**
     * Sets up is_wp_error to return false for every call (happy-path API +
     * WP user operations all succeed).
     */
    private function mockIsWpErrorFalse(): void {
        WP_Mock::userFunction('is_wp_error', ['return' => false]);
    }

    /**
     * Sets up is_wp_error to return false for the first two API HTTP calls
     * and then true for a subsequent WP user operation (insert/update failure).
     */
    private function mockIsWpErrorApiOkThenFail(): void {
        WP_Mock::userFunction('is_wp_error', [
            'return_in_order' => [false, false, true],
        ]);
    }

    /**
     * Mocks the $wpdb global so the update path's direct DB write succeeds.
     */
    private function mockWpdb(): void {
        global $wpdb;
        $wpdb           = \Mockery::mock('wpdb');
        $wpdb->users    = 'wp_users';
        $wpdb->posts    = 'wp_posts';
        $wpdb->comments = 'wp_comments';
        $wpdb->shouldReceive('update')->andReturn(1);
    }

    /**
     * Stubs update_user_meta to succeed for all 7 meta keys.
     */
    private function mockUpdateUserMeta(): void {
        WP_Mock::userFunction('update_user_meta', ['return' => true]);
    }

    /**
     * Builds a stdClass mock that looks enough like a WP_User for the code.
     */
    private function mockWpUser(int $id, string $email, string $login, array $roles = ['subscriber']): object {
        $u             = new stdClass();
        $u->ID         = $id;
        $u->user_email = $email;
        $u->user_login = $login;
        $u->roles      = $roles;
        return $u;
    }

    // =========================================================================
    // 1. CREATE — user does not exist in WordPress yet
    // =========================================================================

    public function test_run_creates_user_when_not_found_in_wordpress(): void {
        $this->mockApiReturning([$this->staffRecord()]);
        $this->mockIsWpErrorFalse();

        WP_Mock::userFunction('get_user_by',       ['return' => false]);
        WP_Mock::userFunction('username_exists',    ['return' => false]);
        WP_Mock::userFunction('wp_generate_password', ['return' => 'pass']);
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');
        WP_Mock::userFunction('get_users', ['return' => []]); // offboard loop — no users absent from API

        $capturedData = null;
        WP_Mock::userFunction('wp_insert_user', [
            'return' => function (array $data) use (&$capturedData): int {
                $capturedData = $data;
                return 101;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertSame('jane.smith',             $capturedData['user_login']);
        $this->assertSame('jane.smith@gca.gov.uk',  $capturedData['user_email']);
        $this->assertSame('jane-smith',             $capturedData['user_nicename']);
        $this->assertSame('Jane Smith',             $capturedData['display_name']);
        $this->assertSame('Jane',                   $capturedData['first_name']);
        $this->assertSame('Smith',                  $capturedData['last_name']);
        $this->assertSame('subscriber',             $capturedData['role']);
    }

    public function test_run_assigns_subscriber_role_to_new_users(): void {
        $this->mockApiReturning([$this->staffRecord()]);
        $this->mockIsWpErrorFalse();

        WP_Mock::userFunction('get_user_by',       ['return' => false]);
        WP_Mock::userFunction('username_exists',   ['return' => false]);
        WP_Mock::userFunction('wp_generate_password', ['return' => 'pass']);
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');
        WP_Mock::userFunction('get_users', ['return' => []]);

        $capturedRole = null;
        WP_Mock::userFunction('wp_insert_user', [
            'return' => function (array $data) use (&$capturedRole): int {
                $capturedRole = $data['role'] ?? null;
                return 101;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertSame('subscriber', $capturedRole);
    }

    // =========================================================================
    // 2. UPDATE — user already exists, matched by email
    // =========================================================================

    public function test_run_updates_existing_user_when_found_by_email(): void {
        $wpUser = $this->mockWpUser(55, 'jane.smith@gca.gov.uk', 'jane.smith');
        $this->mockApiReturning([$this->staffRecord()]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by', ['return' => $wpUser]);
        WP_Mock::userFunction('get_users',   ['return' => []]);

        $capturedData = null;
        WP_Mock::userFunction('wp_update_user', [
            'return' => function (array $data) use (&$capturedData): int {
                $capturedData = $data;
                return 55;
            },
        ]);

        WP_Mock::userFunction('get_user_meta', ['return' => '']);

        GCA_Sync_Users::run();

        $this->assertSame(55,                       $capturedData['ID']);
        $this->assertSame('jane.smith@gca.gov.uk',  $capturedData['user_email']);
        $this->assertSame('Jane Smith',             $capturedData['display_name']);
        // user_login and user_nicename are stripped and written via $wpdb->update.
        $this->assertArrayNotHasKey('user_login',    $capturedData);
        $this->assertArrayNotHasKey('user_nicename', $capturedData);
    }

    public function test_run_saves_all_user_meta_on_update(): void {
        $record = $this->staffRecord([
            'ItemInternalId' => 'wd-999',
            'EmployeeKey'    => '77',
            'JobTitle'       => 'Principal Architect',
            'Team'           => 'Platform (Alice Lead)',
            'Directorate'    => 'Technology',
            'Manager'        => 'Alice Lead',
            'ManagerEmail'   => 'alice.lead@gca.gov.uk',
        ]);
        $wpUser = $this->mockWpUser(55, $record['Email'], 'jane.smith');

        $this->mockApiReturning([$record]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $wpUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => 55]);
        WP_Mock::userFunction('get_users',      ['return' => []]);

        $capturedMeta = [];
        WP_Mock::userFunction('update_user_meta', [
            'return' => function (int $userId, string $key, mixed $value) use (&$capturedMeta): bool {
                $capturedMeta[$key] = $value;
                return true;
            },
        ]);

        WP_Mock::userFunction('get_user_meta', ['return' => '']);

        GCA_Sync_Users::run();

        $this->assertSame('wd-999',                $capturedMeta['workday_item_id']);
        $this->assertSame('77',                    $capturedMeta['employee_key']);
        $this->assertSame('Principal Architect',   $capturedMeta['job_title']);
        $this->assertSame('Platform',              $capturedMeta['team']);  // "(Alice Lead)" stripped
        $this->assertSame('Technology',            $capturedMeta['directorate']);
        $this->assertSame('Alice Lead',            $capturedMeta['manager']);
        $this->assertSame('alice.lead@gca.gov.uk', $capturedMeta['manager_email']);
    }

    // =========================================================================
    // 3. USERNAME COLLISION — login already taken by a different user
    // =========================================================================

    public function test_run_appends_counter_when_username_is_already_taken(): void {
        $this->mockApiReturning([$this->staffRecord()]);
        $this->mockIsWpErrorFalse();

        WP_Mock::userFunction('get_user_by',      ['return' => false]);
        WP_Mock::userFunction('wp_generate_password', ['return' => 'pass']);
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');
        WP_Mock::userFunction('get_users', ['return' => []]);

        // 'jane.smith' taken, 'jane.smith2' taken, 'jane.smith3' free.
        WP_Mock::userFunction('username_exists', [
            'return_in_order' => [true, true, false],
        ]);

        $capturedLogin = null;
        WP_Mock::userFunction('wp_insert_user', [
            'return' => function (array $data) use (&$capturedLogin): int {
                $capturedLogin = $data['user_login'];
                return 102;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertSame('jane.smith3', $capturedLogin);
    }

    // =========================================================================
    // 4. ERROR HANDLING
    // =========================================================================

    public function test_run_logs_error_and_skips_save_when_user_creation_fails(): void {
        $this->mockApiReturning([$this->staffRecord()]);

        $wpError = \Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_message')->andReturn('Username already registered.');

        // is_wp_error returns false for API HTTP responses, then true for the WP_Error result.
        $this->mockIsWpErrorApiOkThenFail();

        WP_Mock::userFunction('get_user_by',      ['return' => false]);
        WP_Mock::userFunction('username_exists',  ['return' => false]);
        WP_Mock::userFunction('wp_generate_password', ['return' => 'pass']);
        WP_Mock::userFunction('wp_insert_user',   ['return' => $wpError]);
        WP_Mock::passthruFunction('do_action');
        WP_Mock::userFunction('get_users', ['return' => []]);

        // update_user_meta should NOT be called because the insert failed.
        // (No expectation set — WP_Mock strict mode will fail if it IS called.)

        GCA_Sync_Users::run();

        // If we reach here without TypeError the error path was correctly taken.
        $this->assertTrue(true);
    }

    public function test_run_logs_error_and_skips_meta_when_user_update_fails(): void {
        $wpUser = $this->mockWpUser(55, 'jane.smith@gca.gov.uk', 'jane.smith');
        $this->mockApiReturning([$this->staffRecord()]);

        $wpError = \Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_message')->andReturn('Invalid email.');

        // false×2 for HTTP, true for wp_update_user result.
        $this->mockIsWpErrorApiOkThenFail();

        WP_Mock::userFunction('get_user_by',    ['return' => $wpUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => $wpError]);
        WP_Mock::passthruFunction('do_action');
        WP_Mock::userFunction('get_users', ['return' => []]);

        // update_user_meta must NOT be called — update errored out.
        // (Not registered → WP_Mock strict mode enforces this.)

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 5. OFFBOARD LOOP — users present in the API are not offboarded
    // =========================================================================

    public function test_run_skips_purge_step_when_deletion_is_disabled(): void {
        $wpUser = $this->mockWpUser(55, 'jane.smith@gca.gov.uk', 'jane.smith');
        $this->mockApiReturning([$this->staffRecord()]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $wpUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => 55]);

        // The offboard loop always runs, but jane.smith is in the API so she
        // is skipped immediately. wp_delete_user must never be called.
        WP_Mock::userFunction('get_users',    ['return' => [$wpUser]]);
        WP_Mock::userFunction('get_user_meta', ['return' => '']);

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 6. PURGE — deletion enabled via WORKDAY_SYNC_ENABLE_DELETE=true
    // =========================================================================

    public function test_run_hard_deletes_user_after_grace_period(): void {
        putenv('WORKDAY_SYNC_ENABLE_DELETE=true');

        $apiRecord  = $this->staffRecord(['Email' => 'active@gca.gov.uk']);
        $activeUser = $this->mockWpUser(10, 'active@gca.gov.uk', 'active');
        $staleUser  = $this->mockWpUser(99, 'leaver@gca.gov.uk', 'leaver', ['subscriber']);

        $formerEmployeeUser     = $this->mockWpUser(50, 'former-employee@gca.co.uk', 'former-employee');
        $formerEmployeeUser->ID = 50;

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        // get_user_by: upsert path returns $activeUser; former-employee lookup returns $formerEmployeeUser.
        WP_Mock::userFunction('get_user_by', [
            'return' => function (string $field, string $value) use ($activeUser, $formerEmployeeUser) {
                return $value === 'former-employee' ? $formerEmployeeUser : $activeUser;
            },
        ]);
        WP_Mock::userFunction('wp_update_user', ['return' => 10]);

        WP_Mock::userFunction('get_users',   ['return' => [$activeUser, $staleUser]]);
        WP_Mock::userFunction('get_userdata', ['return' => $staleUser]);

        // User was soft-deleted 6 days ago — grace period has elapsed.
        // Active user (ID 10) has no deleted_at; only the stale user (ID 99) does.
        $deletedAtDate = date('Y-m-d H:i:s', strtotime('-6 days'));
        WP_Mock::userFunction('get_user_meta', [
            'return' => function (int $userId) use ($deletedAtDate): string {
                return $userId === 99 ? $deletedAtDate : '';
            },
        ]);

        // Track every $wpdb->update call so we can assert posts and comments were reassigned.
        global $wpdb;
        $wpdb           = \Mockery::mock('wpdb');
        $wpdb->users    = 'wp_users';
        $wpdb->posts    = 'wp_posts';
        $wpdb->comments = 'wp_comments';

        $wpdbCalls = [];
        $wpdb->shouldReceive('update')
            ->andReturnUsing(function () use (&$wpdbCalls): int {
                $wpdbCalls[] = func_get_args();
                return 1;
            });

        $deletedId = null;
        WP_Mock::userFunction('wp_delete_user', [
            'return' => function (int $id) use (&$deletedId): bool {
                $deletedId = $id;
                return true;
            },
        ]);

        GCA_Sync_Users::run();

        // User 99 should have been permanently deleted.
        $this->assertSame(99, $deletedId);

        // Posts reassigned: post_author changed from 99 → 50 (former-employee).
        $postCall = $wpdbCalls[array_search('wp_posts', array_column($wpdbCalls, 0))];
        $this->assertSame('wp_posts',              $postCall[0], 'posts table');
        $this->assertSame(['post_author' => 50],   $postCall[1], 'new post_author');
        $this->assertSame(['post_author' => 99],   $postCall[2], 'where post_author');

        // Comments reassigned: user_id changed from 99 → 50, author fields updated.
        $commentCall = $wpdbCalls[array_search('wp_comments', array_column($wpdbCalls, 0))];
        $this->assertSame('wp_comments',            $commentCall[0], 'comments table');
        $this->assertSame(50,                       $commentCall[1]['user_id'],              'new comment user_id');
        $this->assertSame('Former Employee',        $commentCall[1]['comment_author'],       'comment_author name');
        $this->assertSame('former-employee@gca.co.uk', $commentCall[1]['comment_author_email'], 'comment_author_email');
        $this->assertSame(['user_id' => 99],        $commentCall[2], 'where user_id');
    }

    public function test_run_skips_protected_login_during_purge(): void {
        putenv('WORKDAY_SYNC_ENABLE_DELETE=true');

        $this->mockApiReturning([]);
        $this->mockIsWpErrorFalse();
        WP_Mock::passthruFunction('do_action');

        $adminUser = $this->mockWpUser(1, 'admin@site.local', 'admin', ['subscriber']);

        WP_Mock::userFunction('get_users', [
            'return' => function (array $args) use ($adminUser): array {
                return isset($args['role']) ? [] : [$adminUser];
            },
        ]);

        // 'admin' login is in PROTECTED_LOGINS — wp_delete_user must not be called.
        // get_userdata must not be called either (protected check is done first).
        // Not registering them → strict mode enforces the "never called" constraint.

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    public function test_run_skips_administrator_role_during_purge(): void {
        putenv('WORKDAY_SYNC_ENABLE_DELETE=true');

        $this->mockApiReturning([]);
        $this->mockIsWpErrorFalse();
        WP_Mock::passthruFunction('do_action');

        $superAdmin = $this->mockWpUser(2, 'super@gca.gov.uk', 'superadmin', ['administrator']);

        WP_Mock::userFunction('get_users', [
            'return' => function (array $args) use ($superAdmin): array {
                return isset($args['role']) ? [] : [$superAdmin];
            },
        ]);

        WP_Mock::userFunction('get_userdata', ['return' => $superAdmin]);

        // administrator role → wp_delete_user must not be called.

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 7. STAT COUNTERS — assert via function call counts (created/updated/deleted)
    // =========================================================================

    public function test_run_creates_one_and_updates_one_for_two_api_records(): void {
        $newRecord      = $this->staffRecord(['Email' => 'new@gca.gov.uk',      'EmployeeName' => 'New User',      'EmployeeKey' => '42']);
        $existingRecord = $this->staffRecord(['Email' => 'existing@gca.gov.uk', 'EmployeeName' => 'Existing User', 'EmployeeKey' => '43']);
        $existingWpUser = $this->mockWpUser(10, 'existing@gca.gov.uk', 'existing');

        $this->mockApiReturning([$newRecord, $existingRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        // First email → no WP user; second email → existing WP user.
        WP_Mock::userFunction('get_user_by', [
            'return_in_order' => [false, $existingWpUser],
        ]);

        WP_Mock::userFunction('username_exists',    ['return' => false]);
        WP_Mock::userFunction('wp_generate_password', ['return' => 'pass']);

        $insertCount = 0;
        WP_Mock::userFunction('wp_insert_user', [
            'return' => function (array $data) use (&$insertCount): int {
                $insertCount++;
                return 99;
            },
        ]);

        $updateCount = 0;
        WP_Mock::userFunction('wp_update_user', [
            'return' => function (array $data) use (&$updateCount): int {
                // Count upsert updates only; the offboard path is not triggered
                // because both API records are present in WordPress.
                if (($data['display_name'] ?? '') !== 'Former Employee') {
                    $updateCount++;
                }
                return $data['ID'] ?? 10;
            },
        ]);

        // Employee-key lookup returns nothing so new user is created, not matched.
        // Offboard loop: existing user is in the API so she is skipped.
        WP_Mock::userFunction('get_users', [
            'return' => function (array $args) use ($existingWpUser): array {
                return isset($args['meta_key']) ? [] : [$existingWpUser];
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertSame(1, $insertCount, 'Expected exactly one user created');
        $this->assertSame(1, $updateCount, 'Expected exactly one user updated');
    }

    // =========================================================================
    // 8. EXCEPTION HANDLING — API failure must not crash the process
    // =========================================================================

    public function test_run_does_not_throw_when_api_is_misconfigured(): void {
        putenv('WORKDAY_API_BASE_URL=');

        // No WP user functions should be called — the API throws before sync starts.
        WP_Mock::passthruFunction('do_action');

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 9. EDGE CASES
    // =========================================================================

    public function test_run_ignores_api_records_with_empty_email(): void {
        $this->mockApiReturning([
            ['Email' => '', 'EmployeeName' => 'No Email Person'],
        ]);
        $this->mockIsWpErrorFalse();
        WP_Mock::passthruFunction('do_action');

        // get_user_by and wp_insert_user must not be called (empty email skipped).
        // The offboard loop still runs but finds no WP users.
        WP_Mock::userFunction('get_users', ['return' => []]);

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    public function test_run_normalises_email_to_lowercase_before_lookup(): void {
        $record = $this->staffRecord(['Email' => 'Jane.Smith@GCA.GOV.UK']);
        $wpUser = $this->mockWpUser(55, 'jane.smith@gca.gov.uk', 'jane.smith');

        $this->mockApiReturning([$record]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        // Primary lookup is now by EmployeeKey meta; email fallback is not reached.
        // Normalisation is verified by checking what user_email is passed to wp_update_user.
        WP_Mock::userFunction('get_users', [
            'return' => function (array $args) use ($wpUser): array {
                return isset( $args['meta_key'] ) ? [ $wpUser ] : [];
            },
        ]);

        $capturedEmail = null;
        WP_Mock::userFunction('wp_update_user', [
            'return' => function (array $data) use (&$capturedEmail): int {
                $capturedEmail = $data['user_email'] ?? null;
                return 55;
            },
        ]);

        WP_Mock::userFunction('get_user_meta', ['return' => '']);

        GCA_Sync_Users::run();

        $this->assertSame('jane.smith@gca.gov.uk', $capturedEmail);
    }

    public function test_run_handles_empty_staff_list_without_errors(): void {
        $this->mockApiReturning([]);
        $this->mockIsWpErrorFalse();
        WP_Mock::passthruFunction('do_action');

        // No upsert functions should be called for an empty staff list.
        // The offboard loop still runs but finds no WP users to process.
        WP_Mock::userFunction('get_users', ['return' => []]);

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 10. OFFBOARD — users absent from the API are soft-deleted
    // =========================================================================

    public function test_run_soft_deletes_user_absent_from_api(): void {
        $apiRecord  = $this->staffRecord(['Email' => 'active@gca.gov.uk']);
        $activeUser = $this->mockWpUser(10, 'active@gca.gov.uk', 'active');
        $staleUser  = $this->mockWpUser(99, 'leaver@gca.gov.uk', 'leaver', ['subscriber']);

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $activeUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => function (array $d): int { return $d['ID'] ?? 0; }]);

        WP_Mock::userFunction('get_users', ['return' => [$activeUser, $staleUser]]);

        WP_Mock::userFunction('get_userdata', ['return' => $staleUser]);
        WP_Mock::userFunction('get_user_meta', ['return' => '']); // not yet soft-deleted

        // Capture update_user_meta calls for the stale user only.
        $offboardMeta = [];
        WP_Mock::userFunction('update_user_meta', [
            'return' => function (int $userId, string $key, mixed $value) use (&$offboardMeta): bool {
                if ($userId === 99) {
                    $offboardMeta[$key] = $value;
                }
                return true;
            },
        ]);

        // wp_delete_user must NOT be called — soft-delete only sets deleted_at.

        GCA_Sync_Users::run();

        $this->assertArrayHasKey('deleted_at', $offboardMeta, 'deleted_at must be written on soft-delete');
        $this->assertNotEmpty($offboardMeta['deleted_at'],     'deleted_at must not be empty');
        // Workday meta and display name must NOT be touched during soft-delete.
        $this->assertArrayNotHasKey('job_title',       $offboardMeta, 'job_title must not be cleared on soft-delete');
        $this->assertArrayNotHasKey('former_employee', $offboardMeta, 'former_employee flag must not be written');
    }

    public function test_run_sets_deleted_at_on_soft_delete(): void {
        $apiRecord  = $this->staffRecord(['Email' => 'active@gca.gov.uk']);
        $activeUser = $this->mockWpUser(10, 'active@gca.gov.uk', 'active');
        $staleUser  = $this->mockWpUser(99, 'leaver@gca.gov.uk', 'leaver', ['subscriber']);

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $activeUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => function (array $d): int { return $d['ID'] ?? 0; }]);

        WP_Mock::userFunction('get_users', ['return' => [$activeUser, $staleUser]]);

        WP_Mock::userFunction('get_userdata', ['return' => $staleUser]);
        WP_Mock::userFunction('get_user_meta', ['return' => '']);

        $offboardMeta = [];
        WP_Mock::userFunction('update_user_meta', [
            'return' => function (int $userId, string $key, mixed $value) use (&$offboardMeta): bool {
                if ($userId === 99) {
                    $offboardMeta[$key] = $value;
                }
                return true;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertArrayHasKey('deleted_at', $offboardMeta, 'deleted_at must be set');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $offboardMeta['deleted_at'], 'deleted_at must be a MySQL datetime');
        $this->assertArrayNotHasKey('former_employee', $offboardMeta, 'no former_employee flag written');
        $this->assertArrayNotHasKey('job_title',       $offboardMeta, 'job_title not cleared at soft-delete time');
    }

    public function test_run_skips_soft_delete_for_already_soft_deleted_user(): void {
        $apiRecord  = $this->staffRecord(['Email' => 'active@gca.gov.uk']);
        $activeUser = $this->mockWpUser(10, 'active@gca.gov.uk', 'active');
        $formerUser = $this->mockWpUser(99, 'leaver@gca.gov.uk', 'leaver', ['subscriber']);

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $activeUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => function (array $d): int { return $d['ID'] ?? 0; }]);

        WP_Mock::userFunction('get_users', ['return' => [$activeUser, $formerUser]]);

        WP_Mock::userFunction('get_userdata', ['return' => $formerUser]);

        // deleted_at already set — user was soft-deleted on a previous run.
        // Active user (ID 10) has no deleted_at; only the former user (ID 99) does.
        $deletedAtDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        WP_Mock::userFunction('get_user_meta', [
            'return' => function (int $userId) use ($deletedAtDate): string {
                return $userId === 99 ? $deletedAtDate : '';
            },
        ]);

        // deleted_at must NOT be written again for an already-soft-deleted user.
        $metaWrittenForFormer = [];
        WP_Mock::userFunction('update_user_meta', [
            'return' => function (int $userId, string $key, mixed $value) use (&$metaWrittenForFormer): bool {
                if ($userId === 99) {
                    $metaWrittenForFormer[$key] = $value;
                }
                return true;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertArrayNotHasKey('deleted_at', $metaWrittenForFormer, 'deleted_at must not be re-written for an already soft-deleted user');
    }

    public function test_run_skips_hard_delete_within_grace_period(): void {
        putenv('WORKDAY_SYNC_ENABLE_DELETE=true');

        $apiRecord  = $this->staffRecord(['Email' => 'active@gca.gov.uk']);
        $activeUser = $this->mockWpUser(10, 'active@gca.gov.uk', 'active');
        $staleUser  = $this->mockWpUser(99, 'leaver@gca.gov.uk', 'leaver', ['subscriber']);

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $activeUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => 10]);

        WP_Mock::userFunction('get_users', ['return' => [$activeUser, $staleUser]]);

        WP_Mock::userFunction('get_userdata', ['return' => $staleUser]);

        // Soft-deleted only 2 days ago — within the 5-day grace period.
        // Active user (ID 10) has no deleted_at; only the stale user (ID 99) does.
        $deletedAtDate = date('Y-m-d H:i:s', strtotime('-2 days'));
        WP_Mock::userFunction('get_user_meta', [
            'return' => function (int $userId) use ($deletedAtDate): string {
                return $userId === 99 ? $deletedAtDate : '';
            },
        ]);

        // wp_delete_user must NOT be called.

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 11. REINSTATEMENT — user returns to Workday feed within grace period
    // =========================================================================

    public function test_run_clears_deleted_at_when_user_returns_within_grace_period(): void {
        // User was soft-deleted 2 days ago but has now reappeared in the Workday feed.
        $apiRecord   = $this->staffRecord(['Email' => 'returning@gca.gov.uk']);
        $returnUser  = $this->mockWpUser(77, 'returning@gca.gov.uk', 'returning');

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $returnUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => 77]);
        WP_Mock::userFunction('get_users',      ['return' => []]);

        // deleted_at is set — user was soft-deleted on a previous sync.
        WP_Mock::userFunction('get_user_meta', [
            'return' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ]);

        WP_Mock::userFunction('update_user_meta', ['return' => true]);

        // delete_user_meta must be called to remove the deleted_at flag.
        $deletedMetaKey = null;
        WP_Mock::userFunction('delete_user_meta', [
            'return' => function (int $userId, string $key) use (&$deletedMetaKey): bool {
                if ($userId === 77) {
                    $deletedMetaKey = $key;
                }
                return true;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertSame('deleted_at', $deletedMetaKey, 'deleted_at meta must be removed when user returns to the feed');
    }

    public function test_run_does_not_call_delete_user_meta_when_deleted_at_not_set(): void {
        // Normal active user with no deleted_at — delete_user_meta must never be called.
        $apiRecord  = $this->staffRecord(['Email' => 'active@gca.gov.uk']);
        $activeUser = $this->mockWpUser(10, 'active@gca.gov.uk', 'active');

        $this->mockApiReturning([$apiRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $activeUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => 10]);
        WP_Mock::userFunction('get_users',      ['return' => []]);

        // No deleted_at set — get_user_meta returns empty string (WP default).
        WP_Mock::userFunction('get_user_meta',    ['return' => '']);
        WP_Mock::userFunction('update_user_meta', ['return' => true]);

        // delete_user_meta must NOT be registered — WP_Mock strict mode will fail if called.

        GCA_Sync_Users::run();

        $this->assertTrue(true);
    }

    // =========================================================================
    // 12. DUPLICATE RECORDS — same EmployeeKey appears twice in the API feed
    // =========================================================================

    public function test_run_deduplicates_by_employee_key_keeping_last_record(): void {
        // The API map is keyed by EmployeeKey, so a second record with the same key
        // silently overwrites the first.  wp_update_user is therefore called exactly
        // once, using the data from the last (amended) record.
        $firstRecord  = $this->staffRecord();
        $secondRecord = $this->staffRecord(['Team' => 'Engineering (Bob Manager)test']);
        $wpUser       = $this->mockWpUser(55, 'jane.smith@gca.gov.uk', 'jane.smith');

        $this->mockApiReturning([$firstRecord, $secondRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        $this->mockUpdateUserMeta();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',   ['return' => $wpUser]);
        WP_Mock::userFunction('get_users',     ['return' => []]);
        WP_Mock::userFunction('get_user_meta', ['return' => '']); // no deleted_at

        $updateCount = 0;
        WP_Mock::userFunction('wp_update_user', [
            'return' => function (array $data) use (&$updateCount): int {
                $updateCount++;
                return 55;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertSame(1, $updateCount, 'duplicate emails must be deduplicated — only one update per email');
    }

    public function test_run_team_meta_reflects_last_duplicate_record(): void {
        // Second record appends 'test' to Team — the final stored value must contain it.
        $firstRecord  = $this->staffRecord();
        $secondRecord = $this->staffRecord(['Team' => 'Engineering (Bob Manager)test']);
        $wpUser       = $this->mockWpUser(55, 'jane.smith@gca.gov.uk', 'jane.smith');

        $this->mockApiReturning([$firstRecord, $secondRecord]);
        $this->mockIsWpErrorFalse();
        $this->mockWpdb();
        WP_Mock::passthruFunction('do_action');

        WP_Mock::userFunction('get_user_by',    ['return' => $wpUser]);
        WP_Mock::userFunction('wp_update_user', ['return' => 55]);
        WP_Mock::userFunction('get_users',      ['return' => []]);
        WP_Mock::userFunction('get_user_meta',  ['return' => '']); // no deleted_at

        // Each write overwrites the previous — the last call for 'team' wins.
        $capturedTeam = null;
        WP_Mock::userFunction('update_user_meta', [
            'return' => function (int $userId, string $key, mixed $value) use (&$capturedTeam): bool {
                if ($key === 'team') {
                    $capturedTeam = $value;
                }
                return true;
            },
        ]);

        GCA_Sync_Users::run();

        $this->assertNotNull($capturedTeam, 'team meta must be written');
        $this->assertStringContainsString('test', $capturedTeam, 'team meta must reflect the last (amended) duplicate record');
    }
}
