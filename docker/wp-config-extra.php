<?php
/**
 * Allow setting canonical URLs from env (useful for AWS ALB / CloudFront / ECS)
 */
if (getenv('WP_HOME') && !defined('WP_HOME')) {
    define('WP_HOME', getenv('WP_HOME'));
}
if (getenv('WP_SITEURL') && !defined('WP_SITEURL')) {
    define('WP_SITEURL', getenv('WP_SITEURL'));
}

/**
 * Respect HTTPS when behind a reverse proxy / load balancer
 * - Prevents redirects to http://...:8080 when ALB terminates TLS.
 */
$xfp = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$xfport = $_SERVER['HTTP_X_FORWARDED_PORT'] ?? '';

if (($xfp && stripos($xfp, 'https') === 0) || $xfport === '443') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
}

/**
 * WordPress memory limits
 * - WP_MEMORY_LIMIT: frontend
 * - WP_MAX_MEMORY_LIMIT: wp-admin (imports, updates, etc.)
 */
if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '256M');
}
if (!defined('WP_MAX_MEMORY_LIMIT')) {
    define('WP_MAX_MEMORY_LIMIT', '1024M');
}

/**
 * Playwright Test Database Switcher
 */
if (isset($_SERVER['HTTP_X_GCA_TEST_SUITE']) && $_SERVER['HTTP_X_GCA_TEST_SUITE'] === 'true') {
    // If DB_NAME isn't defined yet, set it to the test DB
    if (!defined('DB_NAME')) {
        define('DB_NAME', 'wordpress-test');
    }
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );