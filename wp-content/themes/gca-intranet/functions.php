<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue parent theme styles
 */
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'gca-intranet-foundation',
		get_template_directory_uri() . '/style.css',
		[],
		wp_get_theme(get_template())->get('Version')
	);
});
