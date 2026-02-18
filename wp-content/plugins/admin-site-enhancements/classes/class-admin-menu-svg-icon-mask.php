<?php
/**
 * Shared helper for rendering SVG admin menu icons with correct colors on first paint.
 *
 * WordPress renders `menu_icon` SVG data URIs as <img> initially, and then core's
 * `svg-painter` inlines/recolors them, causing a visible "black → scheme color" flash.
 * This helper pre-empts that by forcing WordPress to use the `.wp-menu-image:before`
 * pseudo-element (via a Dashicon placeholder) and then applying the SVG as a CSS mask.
 *
 * @since 7.6.0
 */

namespace ASENHA\Classes;

class Admin_Menu_Svg_Icon_Mask {

	/**
	 * Registered SVG data URIs keyed by post type key.
	 *
	 * @since 7.6.0
	 * @var array<string,string>
	 */
	private static $post_type_svgs = array();

	/**
	 * Registered SVG data URIs keyed by top-level menu slug (from add_menu_page()).
	 *
	 * @since 7.6.0
	 * @var array<string,string>
	 */
	private static $toplevel_menu_slug_svgs = array();

	/**
	 * Registered SVG data URIs keyed by a substring to match against `href`.
	 *
	 * This is useful for menu slugs that are full URLs/queries (e.g. `post.php?post=123&action=edit`).
	 *
	 * @since 7.6.0
	 * @var array<string,string>
	 */
	private static $href_contains_svgs = array();

	/**
	 * Check whether a string is an SVG data URI that WordPress would render as an <img>.
	 *
	 * @since 7.6.0
	 *
	 * @param mixed $value Potential menu icon.
	 * @return bool
	 */
	public static function is_svg_data_uri( $value ) {
		return is_string( $value ) && ( 0 === strpos( $value, 'data:image/svg+xml' ) );
	}

	/**
	 * Register an SVG icon for a post type's admin menu entry.
	 *
	 * @since 7.6.0
	 *
	 * @param string $post_type Post type key.
	 * @param string $svg_data_uri SVG data URI.
	 * @return void
	 */
	public static function register_post_type_svg_icon( $post_type, $svg_data_uri ) {
		$post_type   = sanitize_key( (string) $post_type );
		$svg_data_uri = trim( (string) $svg_data_uri );

		if ( '' === $post_type || '' === $svg_data_uri ) {
			return;
		}

		if ( ! self::is_svg_data_uri( $svg_data_uri ) ) {
			return;
		}

		self::$post_type_svgs[ $post_type ] = $svg_data_uri;
	}

	/**
	 * Register an SVG icon for a top-level menu entry created by add_menu_page().
	 *
	 * @since 7.6.0
	 *
	 * @param string $menu_slug Menu slug passed to add_menu_page().
	 * @param string $svg_data_uri SVG data URI.
	 * @return void
	 */
	public static function register_toplevel_menu_slug_svg_icon( $menu_slug, $svg_data_uri ) {
		$menu_slug   = (string) $menu_slug;
		$svg_data_uri = trim( (string) $svg_data_uri );

		if ( '' === $menu_slug || '' === $svg_data_uri ) {
			return;
		}

		if ( ! self::is_svg_data_uri( $svg_data_uri ) ) {
			return;
		}

		self::$toplevel_menu_slug_svgs[ $menu_slug ] = $svg_data_uri;
	}

	/**
	 * Register an SVG icon for a menu entry by matching a substring within the anchor href.
	 *
	 * @since 7.6.0
	 *
	 * @param string $needle Substring expected in href.
	 * @param string $svg_data_uri SVG data URI.
	 * @return void
	 */
	public static function register_menu_href_contains_svg_icon( $needle, $svg_data_uri ) {
		$needle      = trim( (string) $needle );
		$svg_data_uri = trim( (string) $svg_data_uri );

		if ( '' === $needle || '' === $svg_data_uri ) {
			return;
		}

		if ( ! self::is_svg_data_uri( $svg_data_uri ) ) {
			return;
		}

		self::$href_contains_svgs[ $needle ] = $svg_data_uri;
	}

	/**
	 * Enqueue inline CSS masks for all registered menu SVG icons.
	 *
	 * @since 7.6.0
	 *
	 * @return void
	 */
	public static function enqueue_mask_css() {
		if ( empty( self::$post_type_svgs ) && empty( self::$toplevel_menu_slug_svgs ) && empty( self::$href_contains_svgs ) ) {
			return;
		}

		$css = '';

		foreach ( self::$post_type_svgs as $post_type => $svg_data_uri ) {
			$post_type   = sanitize_key( (string) $post_type );
			$svg_data_uri = trim( (string) $svg_data_uri );

			if ( '' === $post_type || '' === $svg_data_uri || ! self::is_svg_data_uri( $svg_data_uri ) ) {
				continue;
			}

			$selector = '#adminmenu .menu-icon-' . $post_type . ' .wp-menu-image:before';
			$css     .= self::build_mask_rule( $selector, $svg_data_uri );
		}

		foreach ( self::$toplevel_menu_slug_svgs as $menu_slug => $svg_data_uri ) {
			$menu_slug   = (string) $menu_slug;
			$svg_data_uri = trim( (string) $svg_data_uri );

			if ( '' === $menu_slug || '' === $svg_data_uri || ! self::is_svg_data_uri( $svg_data_uri ) ) {
				continue;
			}

			$selectors = array(
				'#toplevel_page_' . sanitize_title( $menu_slug ) . ' .wp-menu-image:before',
				'#adminmenu a[href*="' . self::escape_css_attr_value( $menu_slug ) . '"] .wp-menu-image:before',
			);
			$css      .= self::build_mask_rule( implode( ',' . PHP_EOL, $selectors ), $svg_data_uri );
		}

		foreach ( self::$href_contains_svgs as $needle => $svg_data_uri ) {
			$needle      = trim( (string) $needle );
			$svg_data_uri = trim( (string) $svg_data_uri );

			if ( '' === $needle || '' === $svg_data_uri || ! self::is_svg_data_uri( $svg_data_uri ) ) {
				continue;
			}

			$selector = '#adminmenu a[href*="' . self::escape_css_attr_value( $needle ) . '"] .wp-menu-image:before';
			$css     .= self::build_mask_rule( $selector, $svg_data_uri );
		}

		if ( '' === $css ) {
			return;
		}

		wp_add_inline_style( 'asenha-wp-admin', $css );
	}

	/**
	 * Build a CSS rule that masks a WP admin menu icon pseudo-element.
	 *
	 * @since 7.6.0
	 *
	 * @param string $selector CSS selector(s).
	 * @param string $svg_data_uri SVG data URI.
	 * @return string
	 */
	private static function build_mask_rule( $selector, $svg_data_uri ) {
		$selector    = trim( (string) $selector );
		$svg_data_uri = trim( (string) $svg_data_uri );

		if ( '' === $selector || '' === $svg_data_uri ) {
			return '';
		}

		return $selector . '{' . PHP_EOL .
			"\tcontent:\"\";" . PHP_EOL .
			"\tbackground-color:currentColor;" . PHP_EOL .
			"\t-webkit-mask-image:url(\"" . $svg_data_uri . "\");" . PHP_EOL .
			"\tmask-image:url(\"" . $svg_data_uri . "\");" . PHP_EOL .
			"\t-webkit-mask-repeat:no-repeat;" . PHP_EOL .
			"\tmask-repeat:no-repeat;" . PHP_EOL .
			"\t-webkit-mask-position:center;" . PHP_EOL .
			"\tmask-position:center;" . PHP_EOL .
			"\t-webkit-mask-size:20px 20px;" . PHP_EOL .
			"\tmask-size:20px 20px;" . PHP_EOL .
			'}' . PHP_EOL;
	}

	/**
	 * Escape a value used inside a double-quoted CSS attribute selector.
	 *
	 * @since 7.6.0
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function escape_css_attr_value( $value ) {
		return str_replace(
			array( '\\', '"' ),
			array( '\\\\', '\"' ),
			(string) $value
		);
	}
}


