<?php

use WP_Mock\Tools\TestCase as WPTestCase;

/**
 * Tests for GCA_Workday_API.
 *
 * All WordPress HTTP functions (wp_remote_post, wp_remote_get, etc.) are
 * intercepted via WP_Mock so no real network calls are made.
 *
 * Env vars are written with putenv() in setUp() and cleared in tearDown().
 */
class WorkdayApiTest extends WPTestCase {

    // -------------------------------------------------------------------------
    // Setup / teardown
    // -------------------------------------------------------------------------

    private array $envVarKeys = [
        'WORKDAY_TOKEN_ENDPOINT',
        'WORKDAY_CLIENT_ID',
        'WORKDAY_CLIENT_SECRET',
        'WORKDAY_SCOPE',
        'WORKDAY_API_BASE_URL',
        'WORKDAY_STAFF_RESOURCE',
        'WORKDAY_STAFF_API_PARAMS',
        'WORKDAY_API_SUBSCRIPTION_KEY',
    ];

    public function setUp(): void {
        parent::setUp();
        $this->setDefaultEnv();
    }

    public function tearDown(): void {
        parent::tearDown();
        foreach ($this->envVarKeys as $key) {
            putenv($key); // unset
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function setDefaultEnv(array $overrides = []): void {
        $defaults = [
            'WORKDAY_TOKEN_ENDPOINT'       => 'https://login.example.com/oauth2/token',
            'WORKDAY_CLIENT_ID'            => 'test-client',
            'WORKDAY_CLIENT_SECRET'        => 'test-secret',
            'WORKDAY_SCOPE'                => 'https://api.example.com/.default',
            'WORKDAY_API_BASE_URL'         => 'https://apim.example.com',
            'WORKDAY_STAFF_RESOURCE'       => '/paths/invoke/staff',
            'WORKDAY_STAFF_API_PARAMS'     => '',
            'WORKDAY_API_SUBSCRIPTION_KEY' => '',
        ];
        foreach (array_merge($defaults, $overrides) as $key => $value) {
            putenv("{$key}={$value}");
        }
    }

    private function tokenResponseBody(string $token = 'mock-access-token', int $expiresIn = 3600): string {
        return json_encode(['access_token' => $token, 'expires_in' => $expiresIn]);
    }

    /**
     * Set up WP_Mock stubs for a successful token + staff response.
     */
    private function mockSuccessfulApiCall(array $staff, bool $odata = false): void {
        $tokenBody = $this->tokenResponseBody();
        $staffBody = $odata ? json_encode(['value' => $staff]) : json_encode($staff);

        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get',  ['return' => ['_staff_response_']]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$tokenBody, $staffBody],
        ]);
    }

    // -------------------------------------------------------------------------
    // Happy-path tests
    // -------------------------------------------------------------------------

    public function test_get_staff_returns_bare_array_on_success(): void {
        $staff = [
            ['Email' => 'alice@example.com', 'EmployeeName' => 'Alice'],
            ['Email' => 'bob@example.com',   'EmployeeName' => 'Bob'],
        ];
        $this->mockSuccessfulApiCall($staff);

        $result = (new GCA_Workday_API())->get_staff();

        $this->assertCount(2, $result);
        $this->assertSame('alice@example.com', $result[0]['Email']);
        $this->assertSame('bob@example.com',   $result[1]['Email']);
    }

    public function test_get_staff_handles_odata_envelope(): void {
        $staff = [['Email' => 'carol@example.com', 'EmployeeName' => 'Carol']];
        $this->mockSuccessfulApiCall($staff, odata: true);

        $result = (new GCA_Workday_API())->get_staff();

        $this->assertCount(1, $result);
        $this->assertSame('carol@example.com', $result[0]['Email']);
    }

    public function test_get_staff_returns_empty_array_when_api_responds_with_400(): void {
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get',  ['return' => ['_staff_response_']]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', [
            'return_in_order' => [200, 400],
        ]);
        WP_Mock::userFunction('wp_remote_retrieve_body', ['return' => $this->tokenResponseBody()]);

        $result = (new GCA_Workday_API())->get_staff();

        $this->assertSame([], $result);
    }

    public function test_staff_params_are_appended_to_url_as_query_string(): void {
        $this->setDefaultEnv([
            'WORKDAY_STAFF_API_PARAMS' => 'api-version=2024-01-01&sp=%2Frun',
        ]);

        $capturedUrl = null;
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get', [
            'return' => function (string $url) use (&$capturedUrl): array {
                $capturedUrl = $url;
                return ['_staff_response_'];
            },
        ]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$this->tokenResponseBody(), json_encode([])],
        ]);

        (new GCA_Workday_API())->get_staff();

        $this->assertStringContainsString('api-version=2024-01-01', (string) $capturedUrl);
        $this->assertStringContainsString('sp=%2Frun', (string) $capturedUrl);
    }

    public function test_subscription_key_header_is_sent_when_configured(): void {
        $this->setDefaultEnv(['WORKDAY_API_SUBSCRIPTION_KEY' => 'my-sub-key']);

        $capturedArgs = null;
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get', [
            'return' => function (string $_url, array $args) use (&$capturedArgs): array {
                $capturedArgs = $args;
                return ['_staff_response_'];
            },
        ]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$this->tokenResponseBody(), json_encode([])],
        ]);

        (new GCA_Workday_API())->get_staff();

        $this->assertSame('my-sub-key', $capturedArgs['headers']['Ocp-Apim-Subscription-Key'] ?? null);
    }

    public function test_subscription_key_header_is_omitted_when_not_configured(): void {
        $capturedArgs = null;
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get', [
            'return' => function (string $_url, array $args) use (&$capturedArgs): array {
                $capturedArgs = $args;
                return ['_staff_response_'];
            },
        ]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$this->tokenResponseBody(), json_encode([])],
        ]);

        (new GCA_Workday_API())->get_staff();

        $this->assertArrayNotHasKey('Ocp-Apim-Subscription-Key', $capturedArgs['headers'] ?? []);
    }

    public function test_token_is_reused_on_second_call_within_same_instance(): void {
        $staffBody = json_encode([['Email' => 'alice@example.com']]);

        // Track call counts manually to avoid Mockery times() strict expectations.
        $tokenCalls = 0;
        $staffCalls = 0;

        WP_Mock::userFunction('wp_remote_post', [
            'return' => function () use (&$tokenCalls): array {
                $tokenCalls++;
                return ['_token_response_'];
            },
        ]);
        WP_Mock::userFunction('wp_remote_get', [
            'return' => function () use (&$staffCalls): array {
                $staffCalls++;
                return ['_staff_response_'];
            },
        ]);
        WP_Mock::userFunction('is_wp_error', ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$this->tokenResponseBody(), $staffBody, $staffBody],
        ]);

        $api = new GCA_Workday_API();
        $api->get_staff();
        $api->get_staff(); // should reuse the cached token — no second wp_remote_post

        $this->assertSame(1, $tokenCalls, 'Token endpoint should only be called once');
        $this->assertSame(2, $staffCalls, 'Staff endpoint should be called once per get_staff()');
    }

    // -------------------------------------------------------------------------
    // Configuration error tests
    // -------------------------------------------------------------------------

    public function test_get_staff_throws_when_base_url_is_not_configured(): void {
        $this->setDefaultEnv(['WORKDAY_API_BASE_URL' => '']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/WORKDAY_API_BASE_URL/');

        (new GCA_Workday_API())->get_staff();
    }

    public function test_get_staff_throws_when_token_endpoint_is_not_configured(): void {
        $this->setDefaultEnv(['WORKDAY_TOKEN_ENDPOINT' => '']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/WORKDAY_TOKEN_ENDPOINT/');

        (new GCA_Workday_API())->get_staff();
    }

    // -------------------------------------------------------------------------
    // Network / HTTP error tests
    // -------------------------------------------------------------------------

    public function test_get_staff_throws_on_token_endpoint_network_error(): void {
        $wpError = \Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_message')->andReturn('cURL error 6: Could not resolve host');

        WP_Mock::userFunction('wp_remote_post', ['return' => $wpError]);
        WP_Mock::userFunction('is_wp_error', [
            'return' => function ($val) use ($wpError): bool {
                return $val === $wpError;
            },
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/bearer token/i');

        (new GCA_Workday_API())->get_staff();
    }

    public function test_get_staff_throws_when_token_endpoint_returns_non_200(): void {
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 401]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/401/');

        (new GCA_Workday_API())->get_staff();
    }

    public function test_get_staff_throws_when_token_response_missing_access_token(): void {
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return' => json_encode(['error' => 'invalid_client']),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/access_token/');

        (new GCA_Workday_API())->get_staff();
    }

    public function test_get_staff_throws_on_staff_api_network_error(): void {
        $wpError = \Mockery::mock('\WP_Error');
        $wpError->shouldReceive('get_error_message')->andReturn('cURL error 28: Connection timed out');

        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get',  ['return' => $wpError]);
        WP_Mock::userFunction('is_wp_error', [
            'return_in_order' => [false, true],
        ]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', ['return' => $this->tokenResponseBody()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/API request failed/');

        (new GCA_Workday_API())->get_staff();
    }

    public function test_get_staff_throws_when_staff_api_returns_non_200(): void {
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get',  ['return' => ['_staff_response_']]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', [
            'return_in_order' => [200, 503],
        ]);
        WP_Mock::userFunction('wp_remote_retrieve_response_message', ['return' => 'Service Unavailable']);
        WP_Mock::userFunction('wp_remote_retrieve_body', ['return' => $this->tokenResponseBody()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/503/');

        (new GCA_Workday_API())->get_staff();
    }

    public function test_get_staff_throws_on_invalid_json_response(): void {
        WP_Mock::userFunction('wp_remote_post', ['return' => ['_token_response_']]);
        WP_Mock::userFunction('wp_remote_get',  ['return' => ['_staff_response_']]);
        WP_Mock::userFunction('is_wp_error',    ['return' => false]);
        WP_Mock::userFunction('wp_remote_retrieve_response_code', ['return' => 200]);
        WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return_in_order' => [$this->tokenResponseBody(), 'not-valid-json{{'],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/parse/i');

        (new GCA_Workday_API())->get_staff();
    }
}
