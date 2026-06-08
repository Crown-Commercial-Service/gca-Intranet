<?php

use WP_Mock\Tools\TestCase;

/**
 * Unit tests for GCA_Workflow_Rejection.
 *
 * UNIT-J.1 through UNIT-J.6
 */
class WorkflowRejectionTest extends TestCase {

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // UNIT-J.1 — save_rejection_meta stores comments to post meta
    // -------------------------------------------------------------------------

    public function test_save_stores_rejection_comments(): void {
        $_POST[ GCA_Workflow_Rejection::NONCE_FIELD ] = 'valid_nonce';
        $_POST['gca_rejection_comments']              = 'Please fix the headings.';
        unset( $_POST['gca_submit_rejection'] );

        WP_Mock::userFunction('wp_verify_nonce', [ 'return' => true ]);
        WP_Mock::userFunction('wp_unslash', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_text_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_textarea_field', [
            'args'   => [ 'Please fix the headings.' ],
            'return' => 'Please fix the headings.',
        ]);
        WP_Mock::userFunction('current_user_can', [
            'args'   => [ 'publish_pages', 1 ],
            'return' => true,
        ]);
        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('update_post_meta', [
            'args'  => [ 1, GCA_Workflow_Rejection::META_KEY, 'Please fix the headings.' ],
            'times' => 1,
        ]);

        $post = $this->make_page( 1 );

        GCA_Workflow_Rejection::save_rejection_meta( 1, $post );

        $this->addToAssertionCount(1);
        $this->cleanupPost();
    }

    // -------------------------------------------------------------------------
    // UNIT-J.2 — nonce verified before saving
    // -------------------------------------------------------------------------

    public function test_save_bails_without_valid_nonce(): void {
        $_POST[ GCA_Workflow_Rejection::NONCE_FIELD ] = 'bad_nonce';
        $_POST['gca_rejection_comments']              = 'Some comments.';

        WP_Mock::userFunction('wp_verify_nonce', [ 'return' => false ]);
        WP_Mock::userFunction('wp_unslash', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_text_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('update_post_meta', [ 'times' => 0 ]);

        $post = $this->make_page( 1 );

        GCA_Workflow_Rejection::save_rejection_meta( 1, $post );

        $this->addToAssertionCount(1);
        $this->cleanupPost();
    }

    // -------------------------------------------------------------------------
    // UNIT-J.3 — non-publisher cannot trigger rejection
    // -------------------------------------------------------------------------

    public function test_non_publisher_cannot_trigger_rejection(): void {
        $_POST[ GCA_Workflow_Rejection::NONCE_FIELD ] = 'valid_nonce';
        $_POST['gca_rejection_comments']              = 'Comments.';
        $_POST['gca_submit_rejection']                = '1';

        WP_Mock::userFunction('wp_verify_nonce', [ 'return' => true ]);
        WP_Mock::userFunction('wp_unslash', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_text_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('current_user_can', [
            'args'   => [ 'publish_pages', 1 ],
            'return' => false,
        ]);
        WP_Mock::userFunction('update_post_meta', [ 'times' => 0 ]);
        WP_Mock::userFunction('do_action', [ 'times' => 0 ]);

        $post = $this->make_page( 1 );

        GCA_Workflow_Rejection::save_rejection_meta( 1, $post );

        $this->addToAssertionCount(1);
        $this->cleanupPost();
    }

    // -------------------------------------------------------------------------
    // UNIT-J.4 — Submit Rejection fires gca_workflow_page_rejected action
    // -------------------------------------------------------------------------

    public function test_submit_rejection_fires_action(): void {
        $_POST[ GCA_Workflow_Rejection::NONCE_FIELD ] = 'valid_nonce';
        $_POST['gca_rejection_comments']              = 'Fix the grammar.';
        $_POST['gca_submit_rejection']                = '1';

        WP_Mock::userFunction('wp_verify_nonce', [ 'return' => true ]);
        WP_Mock::userFunction('wp_unslash', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_text_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_textarea_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('current_user_can', [ 'return' => true ]);
        WP_Mock::userFunction('update_post_meta')->andReturn( true );
        WP_Mock::userFunction('remove_action');
        WP_Mock::userFunction('wp_update_post')->andReturn( 1 );
        WP_Mock::userFunction('add_action');
        WP_Mock::userFunction('get_current_user_id', [ 'return' => 5 ]);

        WP_Mock::expectAction( 'gca_workflow_page_rejected', 1, 5, 'Fix the grammar.' );

        $post = $this->make_page( 1 );

        GCA_Workflow_Rejection::save_rejection_meta( 1, $post );

        $this->addToAssertionCount(1);
        $this->cleanupPost();
    }

    // -------------------------------------------------------------------------
    // UNIT-J.5 — save_rejection_meta changes post status to Draft
    // -------------------------------------------------------------------------

    public function test_submit_rejection_sets_post_status_to_draft(): void {
        $_POST[ GCA_Workflow_Rejection::NONCE_FIELD ] = 'valid_nonce';
        $_POST['gca_rejection_comments']              = 'Needs work.';
        $_POST['gca_submit_rejection']                = '1';

        WP_Mock::userFunction('wp_verify_nonce', [ 'return' => true ]);
        WP_Mock::userFunction('wp_unslash', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_text_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('sanitize_textarea_field', [ 'return_arg' => 0 ]);
        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('current_user_can', [ 'return' => true ]);
        WP_Mock::userFunction('update_post_meta')->andReturn( true );
        WP_Mock::userFunction('remove_action');
        WP_Mock::userFunction('add_action');
        WP_Mock::userFunction('get_current_user_id', [ 'return' => 5 ]);
        WP_Mock::userFunction('do_action');

        WP_Mock::userFunction('wp_update_post', [
            'args'  => [ [ 'ID' => 1, 'post_status' => 'draft' ] ],
            'times' => 1,
        ]);

        $post = $this->make_page( 1 );

        GCA_Workflow_Rejection::save_rejection_meta( 1, $post );

        $this->addToAssertionCount(1);
        $this->cleanupPost();
    }

    // -------------------------------------------------------------------------
    // UNIT-J.6 — render_contributor_notice outputs nothing when meta is absent
    // -------------------------------------------------------------------------

    public function test_contributor_notice_empty_when_no_comments(): void {
        WP_Mock::userFunction('get_post_meta', [
            'args'   => [ 1, GCA_Workflow_Rejection::META_KEY, true ],
            'return' => '',
        ]);

        $post     = $this->make_page( 1 );
        ob_start();
        GCA_Workflow_Rejection::render_contributor_notice( $post );
        $output = ob_get_clean();

        // Should only contain the CSS hide rule, no actual feedback content.
        $this->assertStringNotContainsString( 'notice-warning', $output );
    }

    // -------------------------------------------------------------------------
    // UNIT-J.7 — archive_rejection appends an entry to rejection history meta
    // -------------------------------------------------------------------------

    public function test_archive_rejection_appends_to_history(): void {
        $existing = [
            [ 'timestamp' => 1000, 'reviewer_id' => 3, 'comments' => 'First rejection.' ],
        ];

        WP_Mock::userFunction('get_post_meta', [
            'args'   => [ 5, GCA_Workflow_Rejection::HISTORY_META_KEY, true ],
            'return' => $existing,
        ]);

        $saved      = null;
        $before     = time();
        WP_Mock::userFunction('update_post_meta', [
            'times'  => 1,
            'return' => function( $post_id, $key, $value ) use ( &$saved ) {
                $saved = $value;
                return true;
            },
        ]);

        GCA_Workflow_Rejection::archive_rejection( 5, 7, 'Second rejection.' );
        $after = time();

        $this->assertCount( 2, $saved );
        $this->assertSame( 'First rejection.', $saved[0]['comments'] );
        $this->assertSame( 'Second rejection.', $saved[1]['comments'] );
        $this->assertSame( 7, $saved[1]['reviewer_id'] );
        $this->assertGreaterThanOrEqual( $before, $saved[1]['timestamp'] );
        $this->assertLessThanOrEqual( $after, $saved[1]['timestamp'] );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function make_page( int $id ): WP_Post {
        $post     = Mockery::mock( 'WP_Post' );
        $post->ID = $id;
        return $post;
    }

    private function cleanupPost(): void {
        unset(
            $_POST[ GCA_Workflow_Rejection::NONCE_FIELD ],
            $_POST['gca_rejection_comments'],
            $_POST['gca_submit_rejection']
        );
    }
}
