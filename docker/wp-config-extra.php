<?php
define('DISALLOW_FILE_EDIT', true);

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
 */
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    // Optional (safe behind proxy): uncomment if you want admin forced to SSL when proxy says https
    // if (!defined('FORCE_SSL_ADMIN')) define('FORCE_SSL_ADMIN', true);
}