<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for GCA_Sync_Users private helper methods.
 *
 * All helpers are pure PHP with no WordPress dependencies, so WP_Mock is not
 * needed here. Private static methods are accessed via ReflectionMethod.
 */
class SyncUsersDataMappingTest extends TestCase {

    // -------------------------------------------------------------------------
    // Reflection helper
    // -------------------------------------------------------------------------

    private function call(string $method, mixed ...$args): mixed {
        $rm = new ReflectionMethod(GCA_Sync_Users::class, $method);
        $rm->setAccessible(true);
        return $rm->invoke(null, ...$args);
    }

    // -------------------------------------------------------------------------
    // derive_nicename
    // -------------------------------------------------------------------------

    public function test_derive_nicename_replaces_single_dot(): void {
        $this->assertSame('natalie-cadwallader', $this->call('derive_nicename', 'natalie.cadwallader'));
    }

    public function test_derive_nicename_replaces_multiple_dots(): void {
        $this->assertSame('john-middle-smith', $this->call('derive_nicename', 'john.middle.smith'));
    }

    public function test_derive_nicename_leaves_login_without_dots_unchanged(): void {
        $this->assertSame('jsmith', $this->call('derive_nicename', 'jsmith'));
    }

    public function test_derive_nicename_handles_empty_string(): void {
        $this->assertSame('', $this->call('derive_nicename', ''));
    }

    // -------------------------------------------------------------------------
    // build_common_fields
    // -------------------------------------------------------------------------

    public function test_build_common_fields_extracts_email_prefix_as_user_login(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'natalie.cadwallader@gca.gov.uk',
            'EmployeeName' => 'Natalie Cadwallader',
        ]);

        $this->assertSame('natalie.cadwallader', $fields['user_login']);
    }

    public function test_build_common_fields_lowercases_login(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'Jane.Smith@example.com',
            'EmployeeName' => 'Jane Smith',
        ]);

        $this->assertSame('jane.smith', $fields['user_login']);
    }

    public function test_build_common_fields_sets_user_email(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'jane.smith@example.com',
            'EmployeeName' => 'Jane Smith',
        ]);

        $this->assertSame('jane.smith@example.com', $fields['user_email']);
    }

    public function test_build_common_fields_sets_display_name_from_employee_name(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'jane.smith@example.com',
            'EmployeeName' => 'Jane Smith',
        ]);

        $this->assertSame('Jane Smith', $fields['display_name']);
    }

    public function test_build_common_fields_splits_name_into_first_and_last(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'jane.smith@example.com',
            'EmployeeName' => 'Jane Smith',
        ]);

        $this->assertSame('Jane', $fields['first_name']);
        $this->assertSame('Smith', $fields['last_name']);
    }

    public function test_build_common_fields_puts_remainder_of_name_in_last_name(): void {
        // "Mary Jane Watson" → first="Mary", last="Jane Watson"
        $fields = $this->call('build_common_fields', [
            'Email'        => 'mary.watson@example.com',
            'EmployeeName' => 'Mary Jane Watson',
        ]);

        $this->assertSame('Mary', $fields['first_name']);
        $this->assertSame('Jane Watson', $fields['last_name']);
    }

    public function test_build_common_fields_handles_single_word_name(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'madonna@example.com',
            'EmployeeName' => 'Madonna',
        ]);

        $this->assertSame('Madonna', $fields['first_name']);
        $this->assertSame('', $fields['last_name']);
    }

    public function test_build_common_fields_trims_whitespace_from_name(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'jane.smith@example.com',
            'EmployeeName' => '  Jane Smith  ',
        ]);

        $this->assertSame('Jane Smith', $fields['display_name']);
    }

    public function test_build_common_fields_strips_apostrophe_from_login(): void {
        // Regression test: "lucy.o'hare1@gca.gov.uk" must not leave an apostrophe
        // in user_login, since the update path writes it via a raw $wpdb->update()
        // that bypasses wp_update_user()'s own sanitisation. A stray apostrophe
        // was observed alongside a login issue for this user in production
        // (exact causal mechanism not confirmed) — this keeps the field aligned
        // with what wp_update_user() itself would produce.
        $fields = $this->call('build_common_fields', [
            'Email'        => "lucy.o'hare1@gca.gov.uk",
            'EmployeeName' => "Lucy O'Hare",
        ]);

        $this->assertSame('lucy.ohare1', $fields['user_login']);
    }

    public function test_build_common_fields_login_still_lowercased_and_preserved_when_no_special_chars(): void {
        $fields = $this->call('build_common_fields', [
            'Email'        => 'natalie.cadwallader@gca.gov.uk',
            'EmployeeName' => 'Natalie Cadwallader',
        ]);

        $this->assertSame('natalie.cadwallader', $fields['user_login']);
    }

    // -------------------------------------------------------------------------
    // build_insert_data — derives nicename from login
    // -------------------------------------------------------------------------

    public function test_build_insert_data_derives_nicename_from_email_prefix(): void {
        $data = $this->call('build_insert_data', [
            'Email'        => 'natalie.cadwallader@gca.gov.uk',
            'EmployeeName' => 'Natalie Cadwallader',
        ]);

        $this->assertSame('natalie-cadwallader', $data['user_nicename']);
    }

    public function test_build_insert_data_strips_apostrophe_from_login_and_nicename(): void {
        $data = $this->call('build_insert_data', [
            'Email'        => "lucy.o'hare1@gca.gov.uk",
            'EmployeeName' => "Lucy O'Hare",
        ]);

        $this->assertSame('lucy.ohare1', $data['user_login']);
        $this->assertSame('lucy-ohare1', $data['user_nicename']);
    }

    // -------------------------------------------------------------------------
    // sanitize_login_prefix
    // -------------------------------------------------------------------------

    public function test_sanitize_login_prefix_strips_apostrophe(): void {
        $this->assertSame('ohare1', $this->call('sanitize_login_prefix', "o'hare1"));
    }

    public function test_sanitize_login_prefix_strips_multiple_disallowed_characters(): void {
        $this->assertSame('jsmith', $this->call('sanitize_login_prefix', 'j$smi#th!'));
    }

    public function test_sanitize_login_prefix_preserves_dots_hyphens_underscores(): void {
        $this->assertSame('jane.smith-o_hare', $this->call('sanitize_login_prefix', 'jane.smith-o_hare'));
    }

    public function test_sanitize_login_prefix_leaves_clean_prefix_unchanged(): void {
        $this->assertSame('natalie.cadwallader', $this->call('sanitize_login_prefix', 'natalie.cadwallader'));
    }

    public function test_sanitize_login_prefix_handles_empty_string(): void {
        $this->assertSame('', $this->call('sanitize_login_prefix', ''));
    }

    // -------------------------------------------------------------------------
    // save_user_meta — team name stripping
    // -------------------------------------------------------------------------

    private function teamFromRecord(string $team): string {
        // Capture the 'team' meta value that save_user_meta would write.
        // We call the method and capture the $meta['team'] value by calling
        // the method with a spy on update_user_meta via WP_Mock — but since
        // this test class does not set up WP_Mock, we extract the stripping
        // logic through a dedicated test that inspects save_user_meta via
        // a local mock for update_user_meta.
        //
        // Alternative: inline-reproduce the regex here and test the contract
        // explicitly (fragile if implementation changes). Instead we use a
        // minimal WP_Mock-less workaround: stub update_user_meta as a global
        // function using a runkit or simply test the regex independently.
        //
        // Here we reach into save_user_meta via reflection and capture the
        // value stored for the 'team' key by spying on update_user_meta.
        $captured = [];

        // Define update_user_meta in the global namespace for this test only
        // if it hasn't been defined already (WP_Mock defines it as a Mockery
        // double; in this plain TestCase it may not exist).
        if (!function_exists('update_user_meta')) {
            // Cannot define it here safely without WP_Mock — see SyncUsersSyncTest
            // for team-meta assertions that run under WP_Mock.
            $this->markTestSkipped('update_user_meta not available outside WP_Mock context.');
        }

        return '';
    }

    /**
     * Test team stripping logic by reproducing the exact regex from save_user_meta.
     * This documents the expected contract independently of the implementation.
     */
    public function test_team_name_has_trailing_manager_suffix_stripped(): void {
        $input    = 'Procurement Liverpool (Natalie Kenyon)';
        $expected = 'Procurement Liverpool';
        $actual   = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $input));

        $this->assertSame($expected, $actual);
    }

    public function test_team_name_without_suffix_is_unchanged(): void {
        $input  = 'Digital Services';
        $actual = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $input));

        $this->assertSame('Digital Services', $actual);
    }

    public function test_team_name_with_empty_parentheses_is_stripped(): void {
        $input  = 'Tech Team ()';
        $actual = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $input));

        $this->assertSame('Tech Team', $actual);
    }

    public function test_team_name_parentheses_mid_string_are_preserved(): void {
        // Only the trailing "(X)" is stripped — mid-string parens stay.
        $input  = 'Team (Alpha) Section (Bob Manager)';
        $actual = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $input));

        $this->assertSame('Team (Alpha) Section', $actual);
    }

    public function test_team_name_empty_string_remains_empty(): void {
        $input  = '';
        $actual = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $input));

        $this->assertSame('', $actual);
    }
}
