<?php
/**
 * Plugin Name: Redis Cache Auto-Activate
 * Description: Ensures the Redis Object Cache plugin is always active and the object cache drop-in is enabled. Runs automatically on every request as a must-use plugin.
 */

add_action('init', function () {
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (!is_plugin_active('redis-cache/redis-cache.php')) {
        activate_plugin('redis-cache/redis-cache.php');
    }
});
