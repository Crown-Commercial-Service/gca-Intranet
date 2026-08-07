<?php
/**
 * Plugin Name: All-In-One Intranet - wp-activate.php gate
 * Description: All-In-One Intranet security companion: blocks a /wp-activate.php content leak when the main plugin can't load. Auto-managed; safe to delete if you removed the plugin manually (e.g. via FTP).
 * Version:     1.10.0
 * Author:      WP-Glogin
 * License:     GPL-3.0-or-later
 *
 * Canonical source lives inside the main plugin at
 * wp-content/plugins/all-in-one-intranet/core/mu-shim/aioi-installing-gate.php
 * (and the same path under AllInOneIntranet-premium/ for the Premium edition).
 * The main plugin keeps this copy in wp-content/mu-plugins/ in sync with the
 * source, re-copying whenever their size or modification time differs. Edit
 * only the source; changes made directly to the copy may be overwritten.
 *
 * Self-contained by design: it calls only WordPress core functions and never
 * references the main plugin, so it loads and runs without error even when
 * All-In-One Intranet is deactivated (plugins screen, WP-CLI, or the
 * active_plugins filter) or its files are removed. The plugin deletes this
 * file on deactivation; a copy left behind by a manual/FTP removal is harmless
 * and can be deleted by hand.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only act while wp-activate.php is bootstrapping (WP_INSTALLING true). In
// every other context the main plugin's normal auth gate handles the request.
if ( ! function_exists( 'wp_installing' ) || ! wp_installing() ) {
	return;
}

add_action(
	'parse_request',
	static function ( $wp_obj ) {

		// $pagenow is set by wp-includes/vars.php before this hook fires. It is
		// derived from PHP_SELF via a regex that strips PATH_INFO and survives
		// percent-encoded dots, so it matches 'wp-activate.php' for /wp-activate.php,
		// /wp-activate.php/?feed=rss2, and /wp-activate%2Ephp?feed=rss2 alike — a
		// REQUEST_URI string compare would let the last two through.
		global $pagenow;
		if ( ! isset( $pagenow ) || 'wp-activate.php' !== $pagenow ) {
			return;
		}

		// Use WP's own recognized-query-var list as the source of truth. By the
		// time the `parse_request` action fires, `$wp_obj->public_query_vars` has
		// already been extended via the `query_vars` filter — that's how WP core's
		// REST API adds `rest_route`, and how any MU/network-activated plugin
		// registers its own routing vars. Anything NOT on that list (the activation
		// key, marketing tracking, ad-click IDs, custom params) passes through.
		// Hooked at priority 1 to run before `rest_api_loaded()` (priority 10)
		// dispatches a REST request and before template-loader.php runs do_feed().
		$public_vars = ( is_object( $wp_obj ) && isset( $wp_obj->public_query_vars ) && is_array( $wp_obj->public_query_vars ) )
			? $wp_obj->public_query_vars
			: [];
		$query_vars  = ( is_object( $wp_obj ) && isset( $wp_obj->query_vars ) && is_array( $wp_obj->query_vars ) )
			? $wp_obj->query_vars
			: [];

		// WordPress populates routing query vars from $_POST as well as $_GET (see
		// WP::parse_request() in wp-includes/class-wp.php), so a $_GET-only check is
		// bypassable with a POST body — match the keys of both superglobals.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		$request_keys = array_merge( array_keys( $_GET ), array_keys( $_POST ) );
		$is_attack    = (bool) array_intersect( $request_keys, $public_vars );

		// feed and rest_route are the only content-read leaks on this endpoint:
		// wp_using_themes() is false here, so template-loader.php skips theme
		// rendering and runs only its unconditional feed block, while REST
		// dispatches from rest_api_loaded(). Reading the parsed query vars also
		// catches pretty-permalink routing (/wp-activate.php/feed/rss2/ or
		// /wp-activate.php/wp-json/...) that never appears in $_GET/$_POST.
		if ( ! $is_attack && ( ! empty( $query_vars['feed'] ) || ! empty( $query_vars['rest_route'] ) ) ) {
			$is_attack = true;
		}

		if ( ! $is_attack ) {
			return;
		}

		// Mirror the main plugin's default-hydration in get_option_aioi() /
		// get_default_options(): when the option row is missing or the
		// aioi_privatesite key is absent, the plugin treats the site as private.
		// Bailing on `empty($options['aioi_privatesite'])` would leave a fresh
		// install (no aioi_dsl row yet) unprotected.
		$options    = get_site_option( 'aioi_dsl' );
		$is_private = true;

		if ( is_array( $options ) && isset( $options['aioi_privatesite'] ) ) {
			$is_private = (bool) $options['aioi_privatesite'];
		}

		if ( ! $is_private ) {
			return;
		}

		// Anonymous visitors are never allowed past the installing gate.
		$allowed = function_exists( 'is_user_logged_in' ) && is_user_logged_in();

		// Logged-in users must clear the same role / sub-site-membership bar the
		// main plugin's gate enforces, so a no-role or non-member account cannot
		// read a single feed/REST view here before the main plugin loads on the
		// next request. Duplicated rather than shared: this shim runs under
		// WP_INSTALLING, before the main plugin and its helpers exist, so it must
		// stay dependency-free. Past the logged-in check the auth layer is loaded,
		// so wp_get_current_user()/get_blogs_of_user() are safe to call unguarded.
		if ( $allowed ) {
			if ( is_multisite() ) {
				// aioi_ms_requiremember defaults to true (see get_default_options());
				// treat a missing key as on, matching the aioi_privatesite default above.
				$require_member = ! is_array( $options )
					|| ! isset( $options['aioi_ms_requiremember'] )
					|| (bool) $options['aioi_ms_requiremember'];

				if ( $require_member && ! is_network_admin() ) {
					$blogs   = get_blogs_of_user( get_current_user_id() );
					$allowed = (bool) wp_list_filter( $blogs, [ 'userblog_id' => get_current_blog_id() ] );
				}
			} else {
				$user    = wp_get_current_user();
				$allowed = $user && is_array( $user->roles ) && count( $user->roles ) > 0;
			}
		}

		if ( $allowed ) {
			return;
		}

		// Deny: auth_redirect() sends anonymous visitors to the login screen and
		// is a harmless no-op for a logged-in user; the exit then stops any feed
		// or REST body from rendering.
		if ( function_exists( 'auth_redirect' ) ) {
			auth_redirect();
		}
		exit;
	},
	1
);
