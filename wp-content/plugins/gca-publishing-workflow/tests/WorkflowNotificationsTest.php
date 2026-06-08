<?php

use WP_Mock\Tools\TestCase;

/**
 * Unit tests for GCA_Workflow_Notifications.
 *
 * UNIT-N.1 through UNIT-N.9
 */
class WorkflowNotificationsTest extends TestCase {

    private const REVIEWER_EMAIL = 'reviewers@example.com';
    private const AUTHOR_EMAIL   = 'author@example.com';

    public function setUp(): void {
        parent::setUp();
    }

    private function mockReviewerEmail( string $email = self::REVIEWER_EMAIL ): void {
        WP_Mock::userFunction('get_option', [
            'args'   => [ GCA_Workflow_Settings::OPTION_REVIEWER_EMAIL, '' ],
            'return' => $email,
        ]);
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // UNIT-N.1 — page → Pending sends reviewer email
    // -------------------------------------------------------------------------

    public function test_pending_new_page_emails_reviewer(): void {
        $this->mockReviewerEmail();
        $post = $this->make_page( 1, 'My Page', 'pending', 10 );

        WP_Mock::userFunction('wp_is_post_revision', [ 'args' => [ 1 ], 'return' => false ]);
        WP_Mock::userFunction('get_the_title', [ 'return' => 'My Page' ]);
        WP_Mock::userFunction('admin_url', [ 'return' => 'http://localhost/wp-admin/post.php?post=1&action=edit' ]);
        WP_Mock::userFunction('delete_post_meta');

        $captured_subject = null;
        $captured_body    = null;
        WP_Mock::userFunction('wp_mail', [
            'times'  => 1,
            'return' => function( $to, $subject, $body ) use ( &$captured_subject, &$captured_body ) {
                $captured_subject = $subject;
                $captured_body    = $body;
                return true;
            },
        ]);

        GCA_Workflow_Notifications::on_status_transition( 'pending', 'draft', $post );

        $this->assertStringContainsString( 'My Page', $captured_subject );
        $this->assertStringContainsString( 'My Page', $captured_body );
    }

    // -------------------------------------------------------------------------
    // UNIT-N.2 — revision → Pending uses correct subject
    // -------------------------------------------------------------------------

    public function test_pending_revision_uses_update_submitted_subject(): void {
        $this->mockReviewerEmail();
        $post = $this->make_page( 5, 'Revision Title', 'pending', 10 );

        WP_Mock::userFunction('wp_is_post_revision', [ 'args' => [ 5 ], 'return' => true ]);
        WP_Mock::userFunction('wp_get_post_parent_id', [ 'args' => [ 5 ], 'return' => 3 ]);
        WP_Mock::userFunction('get_the_title', [ 'args' => [ 3 ], 'return' => 'Parent Page' ]);
        WP_Mock::userFunction('admin_url', [ 'return' => 'http://localhost/wp-admin/post.php?post=5&action=edit' ]);
        WP_Mock::userFunction('delete_post_meta');

        $captured_subject = null;
        WP_Mock::userFunction('wp_mail', [
            'times'  => 1,
            'return' => function( $to, $subject, $body ) use ( &$captured_subject ) {
                $captured_subject = $subject;
                return true;
            },
        ]);

        GCA_Workflow_Notifications::on_status_transition( 'pending', 'draft', $post );

        $this->assertStringContainsString( 'Update submitted for', $captured_subject );
        $this->assertStringContainsString( 'Parent Page', $captured_subject );
        $this->assertStringContainsString( 'for review', $captured_subject );
    }

    // -------------------------------------------------------------------------
    // UNIT-N.3 — page → Published sends contributor email
    // -------------------------------------------------------------------------

    public function test_published_page_emails_contributor(): void {
        $post   = $this->make_page( 2, 'Published Page', 'publish', 10 );
        $author = $this->make_user( 10, self::AUTHOR_EMAIL );

        WP_Mock::userFunction('get_userdata', [ 'args' => [ 10 ], 'return' => $author ]);
        WP_Mock::userFunction('get_the_title', [ 'return' => 'Published Page' ]);
        WP_Mock::userFunction('get_permalink', [ 'return' => 'http://localhost/published-page/' ]);

        $captured_subject = null;
        $captured_body    = null;
        WP_Mock::userFunction('wp_mail', [
            'times'  => 1,
            'return' => function( $to, $subject, $body ) use ( &$captured_subject, &$captured_body ) {
                $captured_subject = $subject;
                $captured_body    = $body;
                return true;
            },
        ]);

        GCA_Workflow_Notifications::on_status_transition( 'publish', 'pending', $post );

        $this->assertStringContainsString( 'Published Page', $captured_subject );
        $this->assertStringContainsString( 'Published Page', $captured_body );
    }

    // -------------------------------------------------------------------------
    // UNIT-N.4 — on_page_rejected sends email with comments in body
    // -------------------------------------------------------------------------

    public function test_rejection_email_contains_reviewer_comments(): void {
        $post   = $this->make_page( 3, 'Rejected Page', 'draft', 10 );
        $author = $this->make_user( 10, self::AUTHOR_EMAIL );

        WP_Mock::userFunction('get_post', [ 'args' => [ 3 ], 'return' => $post ]);
        WP_Mock::userFunction('get_userdata', [
            'args'   => [ 10 ],
            'return' => $author,
        ]);
        WP_Mock::userFunction('get_userdata', [
            'args'   => [ 99 ],
            'return' => $this->make_user( 99, 'reviewer@example.com', 'Reviewer Name' ),
        ]);
        WP_Mock::userFunction('get_the_title', [ 'return' => 'Rejected Page' ]);
        WP_Mock::userFunction('admin_url', [ 'return' => 'http://localhost/wp-admin/post.php?post=3&action=edit' ]);

        $captured_subject = null;
        $captured_body    = null;
        $captured_headers = null;
        WP_Mock::userFunction('wp_mail', [
            'times'  => 1,
            'return' => function( $to, $subject, $body, $headers ) use ( &$captured_subject, &$captured_body, &$captured_headers ) {
                $captured_subject = $subject;
                $captured_body    = $body;
                $captured_headers = $headers;
                return true;
            },
        ]);

        GCA_Workflow_Notifications::on_page_rejected( 3, 99, 'Please fix the headings.' );

        $this->assertStringContainsString( 'Rejected Page', $captured_subject );
        $this->assertStringContainsString( 'Rejected Page', $captured_body );
        $this->assertStringContainsString( 'Please fix the headings.', $captured_body );
        $this->assertNotEmpty( $captured_headers );
        $cc_header = implode( "\n", $captured_headers );
        $this->assertStringContainsString( 'Cc:', $cc_header );
        $this->assertStringContainsString( 'reviewer@example.com', $cc_header );
    }

    // -------------------------------------------------------------------------
    // UNIT-N.5 — notifications skipped for non-page post types
    // -------------------------------------------------------------------------

    public function test_notifications_skipped_for_posts(): void {
        $post = $this->make_post_type( 1, 'post' );

        WP_Mock::userFunction('wp_mail', [ 'times' => 0 ]);

        GCA_Workflow_Notifications::on_status_transition( 'pending', 'draft', $post );
        $this->addToAssertionCount(1);
    }

    // -------------------------------------------------------------------------
    // UNIT-N.6 — notifications skipped when new_status === old_status
    // -------------------------------------------------------------------------

    public function test_notifications_skipped_on_noop_transition(): void {
        $post = $this->make_page( 1, 'My Page', 'pending', 10 );

        WP_Mock::userFunction('wp_mail', [ 'times' => 0 ]);

        GCA_Workflow_Notifications::on_status_transition( 'pending', 'pending', $post );
        $this->addToAssertionCount(1);
    }

    // -------------------------------------------------------------------------
    // UNIT-N.7 — retirement request emails reviewer
    // -------------------------------------------------------------------------

    public function test_retirement_request_emails_reviewer(): void {
        $this->mockReviewerEmail();
        $post = $this->make_page( 4, 'Live Page', 'publish', 10 );

        WP_Mock::userFunction('wp_get_current_user', [
            'return' => $this->make_user( 7, 'contributor@example.com', 'A Contributor' ),
        ]);
        WP_Mock::userFunction('get_the_title', [ 'return' => 'Live Page' ]);
        WP_Mock::userFunction('get_permalink', [ 'return' => 'http://localhost/live-page/' ]);
        WP_Mock::userFunction('admin_url', [ 'return' => 'http://localhost/wp-admin/post.php?post=4&action=edit' ]);
        $captured_subject = null;
        $captured_body    = null;
        WP_Mock::userFunction('wp_mail', [
            'times'  => 1,
            'return' => function( $to, $subject, $body ) use ( &$captured_subject, &$captured_body ) {
                $captured_subject = $subject;
                $captured_body    = $body;
                return true;
            },
        ]);

        GCA_Workflow_Notifications::notify_retirement_request( $post );

        $this->assertStringContainsString( 'Live Page', $captured_subject );
        $this->assertStringContainsString( 'Live Page', $captured_body );
    }

    // -------------------------------------------------------------------------
    // UNIT-N.8 — no email sent when reviewer email option is empty
    // -------------------------------------------------------------------------

    public function test_no_reviewer_email_when_option_is_blank(): void {
        $this->mockReviewerEmail( '' );

        $post = $this->make_page( 1, 'My Page', 'pending', 10 );
        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('wp_mail', [ 'times' => 0 ]);

        GCA_Workflow_Notifications::on_status_transition( 'pending', 'draft', $post );
        $this->addToAssertionCount(1);
    }

    // -------------------------------------------------------------------------
    // UNIT-N.9 — rejection comments cleared on re-submit (draft → pending)
    // -------------------------------------------------------------------------

    public function test_rejection_comments_cleared_on_resubmit(): void {
        $this->mockReviewerEmail();
        $post = $this->make_page( 1, 'My Page', 'pending', 10 );

        WP_Mock::userFunction('wp_is_post_revision', [ 'return' => false ]);
        WP_Mock::userFunction('get_the_title', [ 'return' => 'My Page' ]);
        WP_Mock::userFunction('admin_url', [ 'return' => '' ]);
        WP_Mock::userFunction('wp_mail')->andReturn( true );
        WP_Mock::userFunction('delete_post_meta', [
            'args'  => [ 1, GCA_Workflow_Rejection::META_KEY ],
            'times' => 1,
        ]);

        GCA_Workflow_Notifications::on_status_transition( 'pending', 'draft', $post );
        $this->addToAssertionCount(1);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function make_page( int $id, string $title, string $status, int $author_id ): WP_Post {
        $post              = Mockery::mock( 'WP_Post' );
        $post->ID          = $id;
        $post->post_title  = $title;
        $post->post_status = $status;
        $post->post_author = (string) $author_id;
        $post->post_type   = 'page';
        return $post;
    }

    private function make_post_type( int $id, string $type ): WP_Post {
        $post              = Mockery::mock( 'WP_Post' );
        $post->ID          = $id;
        $post->post_type   = $type;
        $post->post_status = 'pending';
        $post->post_author = '1';
        return $post;
    }

    private function make_user( int $id, string $email, string $display_name = 'Test User' ): \Mockery\MockInterface {
        $user               = Mockery::mock( 'WP_User' );
        $user->ID           = $id;
        $user->user_email   = $email;
        $user->display_name = $display_name;
        return $user;
    }
}
