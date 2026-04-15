<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Fetches staff data from the Workday staff list endpoint on Azure APIM.
 *
 * Mirrors the OAuth 2.0 client-credentials pattern used by the CCS MDM API,
 * adapted to use the WordPress HTTP API instead of Guzzle.
 *
 * Required environment variables:
 *   WORKDAY_TOKEN_ENDPOINT   – OAuth 2.0 token URL (POST, client_credentials grant)
 *   WORKDAY_CLIENT_ID        – OAuth client ID
 *   WORKDAY_CLIENT_SECRET    – OAuth client secret
 *   WORKDAY_SCOPE            – OAuth scope
 *   WORKDAY_API_BASE_URL     – Base URL of the APIM gateway (no trailing slash)
 *   WORKDAY_STAFF_RESOURCE   – Resource path, e.g. /paths/invoke/[att].[vw_WorkdayStaffListAPI]/
 *
 * Optional environment variables:
 *   WORKDAY_STAFF_API_PARAMS     – Extra query string appended to the resource URL
 *                                  (e.g. api-version=2016-10-01&sp=...&sv=1.0&SearchQuery=...)
 *   WORKDAY_API_SUBSCRIPTION_KEY – Sent as Ocp-Apim-Subscription-Key header when set
 */
class GCA_Workday_API {

    private string $token_endpoint;
    private string $client_id;
    private string $client_secret;
    private string $scope;
    private string $base_url;
    private string $staff_resource;
    private string $staff_params;
    private string $subscription_key;

    /** @var string|null Cached bearer token. */
    private ?string $access_token = null;

    /** @var int|null Unix timestamp at which the cached token expires. */
    private ?int $token_expiry = null;

    public function __construct() {
        $this->token_endpoint   = (string) ( getenv( 'WORKDAY_TOKEN_ENDPOINT' )       ?: '' );
        $this->client_id        = (string) ( getenv( 'WORKDAY_CLIENT_ID' )            ?: '' );
        $this->client_secret    = (string) ( getenv( 'WORKDAY_CLIENT_SECRET' )        ?: '' );
        $this->scope            = (string) ( getenv( 'WORKDAY_SCOPE' )                ?: '' );
        $this->base_url         = (string) ( getenv( 'WORKDAY_API_BASE_URL' )         ?: '' );
        $this->staff_resource   = (string) ( getenv( 'WORKDAY_STAFF_RESOURCE' )       ?: '' );
        $this->staff_params     = (string) ( getenv( 'WORKDAY_STAFF_API_PARAMS' )     ?: '' );
        $this->subscription_key = (string) ( getenv( 'WORKDAY_API_SUBSCRIPTION_KEY' ) ?: '' );
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Fetch all staff records from the API.
     *
     * @return array<int, array<string, string>> Flat array of staff objects.
     * @throws RuntimeException On configuration error, network failure, or non-200 response.
     */
    public function get_staff(): array {
        $resource = trim( $this->staff_resource, '/' ) . '/';
        $url      = rtrim( $this->base_url, '/' ) . '/' . $resource;

        if ( '' !== $this->staff_params ) {
            $url .= '?' . $this->staff_params;
        }

        return $this->request_resource( $url );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Obtain (or return cached) bearer token using client_credentials grant.
     *
     * Tokens are cached in-memory for the lifetime of the request and
     * refreshed 5 minutes before expiry.
     *
     * @throws RuntimeException If the token endpoint is unreachable or returns an error.
     */
    private function get_bearer_token(): string {
        if ( $this->access_token && $this->token_expiry && time() < $this->token_expiry ) {
            return $this->access_token;
        }

        if ( '' === $this->token_endpoint ) {
            throw new RuntimeException( 'WORKDAY_TOKEN_ENDPOINT is not configured.' );
        }

        $response = wp_remote_post( $this->token_endpoint, [
            'timeout' => 15,
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'body'    => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'scope'         => $this->scope,
            ],
        ] );

        if ( is_wp_error( $response ) ) {
            throw new RuntimeException( 'Failed to obtain bearer token: ' . $response->get_error_message() );
        }

        $status = wp_remote_retrieve_response_code( $response );
        if ( 200 !== (int) $status ) {
            throw new RuntimeException( sprintf( 'Token endpoint returned status %d.', $status ) );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $data['access_token'] ) ) {
            throw new RuntimeException( 'Token response did not contain an access_token.' );
        }

        $this->access_token = $data['access_token'];
        // Subtract 5 minutes (300 s) as a safety buffer, matching the MDM API pattern.
        $this->token_expiry = time() + (int) ( $data['expires_in'] ?? 3600 ) - 300;

        return $this->access_token;
    }

    /**
     * Make an authenticated GET request and return the decoded JSON body.
     *
     * @throws RuntimeException On network failure or non-200 status.
     */
    private function request_resource( string $url ): array {
        if ( '' === $this->base_url ) {
            throw new RuntimeException( 'WORKDAY_API_BASE_URL is not configured.' );
        }

        $bearer_token = $this->get_bearer_token();

        $headers = [
            'Authorization' => 'Bearer ' . $bearer_token,
            'Accept'        => 'application/json',
        ];

        if ( '' !== $this->subscription_key ) {
            $headers['Ocp-Apim-Subscription-Key'] = $this->subscription_key;
        }

        $response = wp_remote_get( $url, [
            'timeout' => 30,
            'headers' => $headers,
        ] );

        if ( is_wp_error( $response ) ) {
            throw new RuntimeException( 'API request failed: ' . $response->get_error_message() );
        }

        $status = wp_remote_retrieve_response_code( $response );

        // Return empty result for 400 responses (no matching records), mirroring MDM API behaviour.
        if ( 400 === (int) $status ) {
            return [];
        }

        if ( 200 !== (int) $status ) {
            $reason = wp_remote_retrieve_response_message( $response );
            throw new RuntimeException( sprintf(
                'API returned status %d: %s',
                $status,
                $reason
            ) );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( JSON_ERROR_NONE !== json_last_error() ) {
            throw new RuntimeException( 'Failed to parse API response: ' . json_last_error_msg() );
        }

        // Support both OData envelope {"value":[...]} and bare array responses.
        if ( isset( $data['value'] ) && is_array( $data['value'] ) ) {
            return $data['value'];
        }

        return is_array( $data ) ? $data : [];
    }
}
