<?php
/**
 * MU-shim manager.
 *
 * Keeps wp-content/mu-plugins/aioi-installing-gate.php in sync with the
 * canonical source shipped alongside this file. The shim closes a
 * /wp-activate.php content-leak surface on installs where WP_INSTALLING
 * prevents the main plugin from loading — see the shim file's own header for
 * the full explanation.
 *
 * Idempotent and safe to call on every request: the sync check is a cheap
 * stat compare (existence + size + mtime) with no file reads, and a no-op when
 * the installed copy already matches the shipped source.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'core_aioi_mu_shim' ) ) {
	return;
}

class core_aioi_mu_shim {

	/**
	 * Absolute path of the canonical MU shim shipped inside this plugin.
	 *
	 * @return string
	 */
	public static function source_path() {

		return __DIR__ . '/aioi-installing-gate.php';
	}

	/**
	 * Absolute path where the MU shim is installed.
	 *
	 * @return string Empty string when WPMU_PLUGIN_DIR is unavailable.
	 */
	public static function target_path() {

		if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
			return '';
		}

		return WPMU_PLUGIN_DIR . '/aioi-installing-gate.php';
	}

	/**
	 * Make sure the installed MU shim matches the canonical source.
	 *
	 * Hooked on `plugins_loaded` priority 0 and called directly from the
	 * plugin's activation hook. Fails silently when the target directory is
	 * not writable; the main plugin's other auth-wall gates still apply.
	 */
	public static function ensure() {

		$src = self::source_path();
		$dst = self::target_path();

		if ( '' === $dst || ! is_file( $src ) ) {
			return;
		}

		if ( self::is_synced( $src, $dst ) ) {
			return;
		}

		self::install( $src, $dst );
	}

	/**
	 * Remove the installed MU shim. Called from the plugin's deactivation hook.
	 */
	public static function remove() {

		$dst = self::target_path();

		if ( '' === $dst || ! is_file( $dst ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink, WordPress.PHP.NoSilencedErrors.Discouraged
		@unlink( $dst );
	}

	/**
	 * Whether the installed copy already matches the shipped source.
	 *
	 * Stat-only check — no file reads — so it is cheap to run on every request.
	 * A plugin update ships a source file with a different size and/or a newer
	 * mtime than the installed copy, and install() stamps the freshly written
	 * copy with the current time, so once synced the copy's mtime stays at or
	 * past the source's. A same-size edit that also keeps an older-or-equal
	 * mtime would slip past, but the installed file is a managed drop-in
	 * ("do not edit") and any real update advances its size or mtime.
	 *
	 * @param string $src Source path (already known to exist).
	 * @param string $dst Target path.
	 *
	 * @return bool
	 */
	private static function is_synced( $src, $dst ) {

		if ( ! is_file( $dst ) ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$src_size = @filesize( $src );
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$dst_size = @filesize( $dst );

		if ( false === $src_size || $src_size !== $dst_size ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return (int) @filemtime( $dst ) >= (int) @filemtime( $src );
	}

	/**
	 * Copy the source to the destination via a unique per-request temp file.
	 *
	 * @param string $src Source path.
	 * @param string $dst Destination path.
	 */
	private static function install( $src, $dst ) {

		$dir = dirname( $dst );

		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
		if ( ! is_writable( $dir ) ) {
			return;
		}

		// Unique temp name per request, kept inside the target dir so the final
		// rename stays on one filesystem. Concurrent requests right after a
		// plugin update must not share one temp path and rename a half-written
		// file into place: MU plugins load on every request, so a truncated
		// drop-in would fatal the whole site. wp_rand() plus uniqid() stays
		// distinct across separate processes and threads of one process alike.
		$tmp = $dst . '.' . wp_rand() . '-' . uniqid( '', true ) . '.tmp';

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( ! @copy( $src, $tmp ) ) {
			// A failed copy may still leave a partial temp file; clean it up.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink, WordPress.PHP.NoSilencedErrors.Discouraged
			@unlink( $tmp );

			return;
		}

		// rename() is atomic within a filesystem, so readers see either the old
		// drop-in or the complete new one, never a partial write.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename, WordPress.PHP.NoSilencedErrors.Discouraged
		if ( ! @rename( $tmp, $dst ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink, WordPress.PHP.NoSilencedErrors.Discouraged
			@unlink( $tmp );
		}
	}
}
