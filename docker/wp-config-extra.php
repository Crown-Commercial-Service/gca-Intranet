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