<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replaces the native WP author meta box on blog and work_update post types
 * with a searchable Select2 dropdown that shows each user's profile image.
 *
 * All users are pre-loaded as inline JSON (no AJAX needed for the dropdown).
 * The selected value is stored as the standard `post_author` field so
 * WordPress handles saving natively.
 */
class GCA_Author_Selector {

	const POST_TYPES = [ 'blog', 'work_update', 'news', 'event' ];
	const META_KEY   = '_gca_author_explicitly_set';

	public static function init(): void {
		add_action( 'add_meta_boxes', [ __CLASS__, 'register_meta_box' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_action( 'save_post', [ __CLASS__, 'save_meta' ] );
	}

	// -------------------------------------------------------------------------
	// Meta box
	// -------------------------------------------------------------------------

	public static function register_meta_box(): void {
		foreach ( self::POST_TYPES as $post_type ) {
			// Remove the default WP author meta box.
			remove_meta_box( 'authordiv', $post_type, 'normal' );

			add_meta_box(
				'gca-author-selector',
				__( 'Author', 'gca-custom' ),
				[ __CLASS__, 'render_meta_box' ],
				$post_type,
				'side',
				'default'
			);
		}
	}

	public static function render_meta_box( WP_Post $post ): void {
		$explicitly_set = get_post_meta( $post->ID, self::META_KEY, true );
		$author_id      = $explicitly_set ? (int) $post->post_author : 0;
		$author         = $author_id ? get_userdata( $author_id ) : null;
		$name           = $author ? $author->display_name : '';
		$image          = $author_id ? self::get_user_image_url( $author_id ) : '';
		?>
		<div class="gca-author-selector-wrap">
			<select name="post_author" id="gca-author-select" style="width:100%">
				<option value=""></option>
				<?php if ( $author_id && $author ) : ?>
					<option value="<?php echo esc_attr( $author_id ); ?>"
					        data-img="<?php echo esc_attr( $image ); ?>"
					        selected="selected">
						<?php echo esc_html( $name ); ?>
					</option>
				<?php endif; ?>
			</select>
		</div>
		<?php
	}

	public static function save_meta( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! in_array( get_post_type( $post_id ), self::POST_TYPES, true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['post_author'] ) ) {
			return;
		}

		$selected = (int) $_POST['post_author'];
		if ( $selected > 0 && get_userdata( $selected ) ) {
			update_post_meta( $post_id, self::META_KEY, '1' );
		} else {
			delete_post_meta( $post_id, self::META_KEY );
		}
	}

	// -------------------------------------------------------------------------
	// Assets
	// -------------------------------------------------------------------------

	public static function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, self::POST_TYPES, true ) ) {
			return;
		}

		// Register Select2 from ACF's bundled files if not already registered.
		// ACF only registers it when a select/user field is present; since our
		// field groups only have an image field we must do it ourselves.
		if ( ! wp_script_is( 'select2', 'registered' ) ) {
			$acf_base = plugins_url( 'advanced-custom-fields-pro/assets/inc/select2/4/' );
			wp_register_script( 'select2', $acf_base . 'select2.full.min.js', [ 'jquery' ], '4.0', true );
			wp_register_style( 'select2', $acf_base . 'select2.min.css', [], '4.0' );
		}
		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2' );

		$base = plugin_dir_url( GCA_CUSTOM_FILE );

		wp_enqueue_script(
			'gca-author-selector',
			$base . 'assets/js/admin-author-selector.js',
			[ 'jquery', 'select2' ],
			GCA_CUSTOM_VERSION,
			true
		);

		wp_enqueue_style(
			'gca-author-selector',
			$base . 'assets/css/admin-author-selector.css',
			[ 'select2' ],
			GCA_CUSTOM_VERSION
		);

		// Pre-load all users so Select2 can filter locally without AJAX.
		$users = get_users( [
			'fields'  => [ 'ID', 'display_name' ],
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'number'  => -1, // all users
		] );

		$user_data = array_map(
			function ( $user ) {
				return [
					'id'   => $user->ID,
					'text' => $user->display_name,
					'img'  => self::get_user_image_url( (int) $user->ID ),
				];
			},
			$users
		);

		global $post;
		$explicitly_set = $post ? get_post_meta( $post->ID, self::META_KEY, true ) : false;
		wp_localize_script( 'gca-author-selector', 'gcaAuthorSelector', [
			'users'           => array_values( $user_data ),
			'currentAuthorId' => ( $explicitly_set && $post ) ? (string) $post->post_author : '',
		] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the best available profile image URL for a user.
	 *
	 * get_avatar_url() is filtered by the google-profile-picture feature file
	 * to return the locally-cached Google photo when that flag is enabled, so
	 * calling it here covers all cases: Google pic → Gravatar → WP default.
	 */
	public static function get_user_image_url( int $user_id ): string {
		return get_avatar_url( $user_id, [ 'size' => 48 ] );
	}
}
