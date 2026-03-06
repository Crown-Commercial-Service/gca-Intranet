<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GCA direct admin access
 *
 * Hidden emergency login route for WordPress administrators only.
 * Route slug is stored in WP Admin as an option.
 */

class GCA_Direct_Admin_Access {

	const QUERY_VAR       = 'gca_direct_admin_access';
	const NONCE_ACTION    = 'gca_direct_admin_login';
	const ERROR_QUERY_ARG = 'gca_admin_login_error';
	const OPTION_SLUG     = 'gca_direct_admin_slug';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'update_option_' . self::OPTION_SLUG, array( __CLASS__, 'maybe_flush_rewrite_on_change' ), 10, 2 );

		if ( ! self::is_enabled() ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'add_rewrite_rule' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_var' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_route' ) );
	}

	private static function is_enabled() {
		return (bool) self::get_slug();
	}

	private static function get_slug() {
		$slug = get_option( self::OPTION_SLUG, '' );

		$slug = sanitize_title( (string) $slug );

		if ( ! self::is_allowed_slug( $slug ) ) {
			return '';
		}

		return $slug;
	}

	private static function is_allowed_slug( $slug ) {
		if ( '' === $slug ) {
			return false;
		}

		$reserved = array(
			'wp-admin',
			'wp-login',
			'wp-login-php',
			'login',
			'admin',
			'dashboard',
			'register',
			'password-reset',
			'lostpassword',
		);

		return ! in_array( $slug, $reserved, true );
	}

	private static function get_path() {
		$slug = self::get_slug();

		if ( '' === $slug ) {
			return '';
		}

		return '/' . $slug . '/';
	}

	private static function get_url( $args = array() ) {
		$path = self::get_path();

		if ( '' === $path ) {
			return home_url( '/' );
		}

		$url = home_url( $path );

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	public static function register_setting() {
		register_setting(
			'general',
			self::OPTION_SLUG,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_slug_setting' ),
				'default'           => '',
			)
		);

		add_settings_field(
			self::OPTION_SLUG,
			'Direct admin login slug',
			array( __CLASS__, 'render_settings_field' ),
			'general'
		);
	}

	public static function sanitize_slug_setting( $value ) {
		$value = sanitize_title( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( ! self::is_allowed_slug( $value ) ) {
			add_settings_error(
				self::OPTION_SLUG,
				'gca_direct_admin_slug_invalid',
				'That slug is not allowed. Choose a different value.'
			);

			return get_option( self::OPTION_SLUG, '' );
		}

		return $value;
	}

	public static function render_settings_field() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$value = get_option( self::OPTION_SLUG, '' );
		?>
		<input
			type="text"
			name="<?php echo esc_attr( self::OPTION_SLUG ); ?>"
			id="<?php echo esc_attr( self::OPTION_SLUG ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
			placeholder="enter-private-slug"
			autocomplete="off"
			spellcheck="false"
			pattern="[a-z0-9-]+"
		/>
		<p class="description">
			Set the hidden route for emergency administrator login. Example: if set to <code>secure-admin-entry</code>, the login URL becomes <code>/secure-admin-entry/</code>.
		</p>
		<p class="description">
			Leave blank to disable this route. Use lowercase letters, numbers, and hyphens only.
		</p>
		<?php
	}

	public static function add_settings_page() {
		// No custom page needed because field is added to Settings > General.
	}

	public static function maybe_flush_rewrite_on_change( $old_value, $value ) {
		$old_value = sanitize_title( (string) $old_value );
		$value     = sanitize_title( (string) $value );

		if ( $old_value !== $value ) {
			flush_rewrite_rules();
		}
	}

	public static function add_rewrite_rule() {
		$slug = self::get_slug();

		if ( '' === $slug ) {
			return;
		}

		add_rewrite_rule(
			'^' . preg_quote( $slug, '/' ) . '/?$',
			'index.php?' . self::QUERY_VAR . '=1',
			'top'
		);
	}

	public static function add_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public static function handle_route() {
		if ( ! get_query_var( self::QUERY_VAR ) ) {
			return;
		}

		self::send_headers();

		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( admin_url() );
			exit;
		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			self::handle_login_post();
		}

		self::render_login_screen();
		exit;
	}

	private static function send_headers() {
		status_header( 200 );
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	private static function handle_login_post() {
		if (
			empty( $_POST['gca_direct_admin_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['gca_direct_admin_nonce'] ) ),
				self::NONCE_ACTION
			)
		) {
			self::redirect_with_error();
		}

		$username = isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : '';
		$password = isset( $_POST['pwd'] ) ? wp_unslash( $_POST['pwd'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );

		if ( '' === $username || '' === $password ) {
			self::redirect_with_error();
		}

		$creds = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $user ) ) {
			self::redirect_with_error();
		}

		if ( ! user_can( $user, 'manage_options' ) ) {
			wp_logout();
			self::redirect_with_error();
		}

		wp_safe_redirect( admin_url() );
		exit;
	}

	private static function redirect_with_error() {
		wp_safe_redirect(
			self::get_url(
				array(
					self::ERROR_QUERY_ARG => 1,
				)
			)
		);
		exit;
	}

	private static function render_login_screen() {
		$has_error = isset( $_GET[ self::ERROR_QUERY_ARG ] );
		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="robots" content="noindex,nofollow,noarchive">
			<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> - Administrator Access</title>
			<?php wp_admin_css( 'login', true ); ?>
			<style>
				body.login {
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					background: #f0f0f1;
				}
				.gca-admin-login-message {
					margin: 0 0 16px;
					padding: 12px;
					border-left: 4px solid #2271b1;
					background: #fff;
					font-size: 14px;
					line-height: 1.5;
				}
				.gca-admin-login-error {
					margin: 0 0 16px;
					padding: 12px;
					border-left: 4px solid #d63638;
					background: #fff;
				}
				.gca-admin-login-footer {
					margin-top: 16px;
					font-size: 12px;
					color: #50575e;
				}
			</style>
		</head>
		<body class="login login-action-login wp-core-ui">
			<div id="login">
				<h1>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
					</a>
				</h1>

				<div class="gca-admin-login-message">
					This administrator access route is for emergency use only. Password recovery is not available here.
					If access is required, retrieve the current credentials via AWS Parameter Store using the agreed platform support process.
				</div>

				<?php if ( $has_error ) : ?>
					<div class="gca-admin-login-error">
						The username or password is incorrect, or this account is not permitted to use this login.
					</div>
				<?php endif; ?>

				<form name="loginform" id="loginform" action="<?php echo esc_url( self::get_url() ); ?>" method="post">
					<p>
						<label for="user_login">Username or Email Address</label>
						<input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" autocomplete="username">
					</p>

					<p>
						<label for="user_pass">Password</label>
						<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="current-password">
					</p>

					<?php wp_nonce_field( self::NONCE_ACTION, 'gca_direct_admin_nonce' ); ?>

					<p class="forgetmenot">
						<label for="rememberme">
							<input name="rememberme" type="checkbox" id="rememberme" value="forever">
							Remember Me
						</label>
					</p>

					<p class="submit">
						<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Log In">
					</p>
				</form>

				<p class="gca-admin-login-footer">
					This route is not intended for general intranet users.
				</p>
			</div>
			<?php wp_print_footer_scripts(); ?>
		</body>
		</html>
		<?php
	}
}

GCA_Direct_Admin_Access::init();