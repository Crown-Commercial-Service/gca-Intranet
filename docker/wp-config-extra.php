<?php
/**
 * Disable plugin/theme installation, updates, and the built-in file editor.
 * Remediates a pentest finding where an admin uploaded a malicious plugin
 * package to gain code execution. No user, including administrators, can
 * install, update, or edit plugins/themes through wp-admin while this is set.
 */
if (!defined('DISALLOW_FILE_MODS')) {
    define('DISALLOW_FILE_MODS', true);
}

/**
 * Allow setting canonical URLs from env (useful for AWS ALB / CloudFront / ECS)
 */
if (getenv('WP_HOME') && !defined('WP_HOME')) {
    define('WP_HOME', getenv('WP_HOME'));
}
if (getenv('WP_SITEURL') && !defined('WP_SITEURL')) {
    define('WP_SITEURL', getenv('WP_SITEURL'));
}
if (getenv('WP_ENVIRONMENT_TYPE') && !defined('WP_ENVIRONMENT_TYPE')) {
    define('WP_ENVIRONMENT_TYPE', getenv('WP_ENVIRONMENT_TYPE'));
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
 * We check the header and define the constant EARLY.
 */
if (isset($_SERVER['HTTP_X_GCA_TEST_SUITE']) && $_SERVER['HTTP_X_GCA_TEST_SUITE'] === 'true') {
    define('DB_NAME', 'wordpress-test');
}

/**
 * WP Mail SMTP — SendGrid native mailer (Web API, not SMTP).
 * WPMS_ON locks these values so they can't be overridden via the WP admin UI.
 */
if (getenv('SMTP_PASSWORD')) {
    define('WPMS_ON', true);
    define('WPMS_MAILER', 'sendgrid');
    define('WPMS_SENDGRID_API_KEY', getenv('SMTP_PASSWORD'));
    define('WPMS_SET_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'GCA Intranet');
    define('WPMS_SET_FROM_EMAIL', getenv('SMTP_FROM_EMAIL'));
}
