<?php

class core_all_in_one_intranet {

	protected $aioi_options = null;

	protected function __construct() {
		$this->add_actions();
	}

	/**
	 * Hook into WordPress.
	 */
	protected function add_actions() {

		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, 'aioi_admin_init' ], 5, 0 );

			add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', [ $this, 'aioi_admin_menu' ] );

			if ( is_multisite() ) {
				add_action( 'network_admin_edit_' . $this->get_options_menuname(), [ $this, 'aioi_save_network_options' ] );
				add_filter( 'network_admin_plugin_action_links', [ $this, 'aioi_plugin_action_links' ], 10, 2 );
			} else {
				add_filter( 'plugin_action_links', [ $this, 'aioi_plugin_action_links' ], 10, 2 );
			}
		}

		add_action( 'template_redirect', [ $this, 'aioi_template_redirect' ] );
		add_filter( 'robots_txt', [ $this, 'aioi_robots_txt' ], 0, 2 );
		add_filter( 'option_ping_sites', [ $this, 'aioi_option_ping_sites' ], 0, 1 );
		add_filter( 'rest_pre_dispatch', [ $this, 'aioi_rest_pre_dispatch' ], 0, 1 );

		add_filter( 'login_redirect', [ $this, 'aioi_login_redirect' ], 10, 3 );

		add_action( 'wp_login', [ $this, 'aioi_wp_login' ], 10, 2 );
		add_action( 'init', [ $this, 'aioi_check_activity' ], 1 );

		if ( is_multisite() ) {
			add_action( 'wpmu_new_user', [ $this, 'aioi_wpmu_new_user' ], 10, 1 );
			add_action( 'wpmu_new_blog', [ $this, 'aioi_wpmu_new_blog' ], 10, 6 );
		}
	}

	/**
	 * The list of plugin options and their default values.
	 *
	 * @return array
	 */
	protected function get_default_options() {

		return [
			'aioi_version'          => $this->PLUGIN_VERSION,
			'aioi_privatesite'      => true,
			'aioi_ms_requiremember' => true,
			'aioi_autologout_time'  => 0,
			'aioi_autologout_units' => 'minutes',
			'aioi_loginredirect'    => '',
			'aioi_ms_membersrole'   => '',
		];
	}

	// PRIVATE SITE

	/**
	 * Process the request based on whether the site is private or not.
	 */
	public function aioi_template_redirect() {

		$options = $this->get_option_aioi();

		// Do nothing if private site is off.
		if ( ! $options['aioi_privatesite'] ) {
			return;
		}

		$allow_access = false;

		// Allow certain URLs.
		if (
			substr( $_SERVER['REQUEST_URI'], 0, 16 ) === '/wp-activate.php' ||
			substr( $_SERVER['REQUEST_URI'], 0, 11 ) === '/robots.txt'
		) {
			$allow_access = true;
		}

		$allow_access = (bool) apply_filters( 'aioi_allow_public_access', $allow_access );

		if ( $allow_access ) {
			return;
		}

		// We do want a private site.
		if ( ! is_user_logged_in() ) {
			auth_redirect();
			exit;
		}

		if ( is_multisite() ) {
			$this->handle_private_loggedin_multisite( $options );
		} else {
			// Restrict access to users with no role.
			$user = wp_get_current_user();
			if ( ! $user || ! is_array( $user->roles ) || count( $user->roles ) == 0 ) {
				wp_logout();
				wp_die(
					'<p>' . esc_html__( 'You attempted to login to the site, but you do not have any permissions. If you believe you should have access, please contact your administrator.', 'all-in-one-intranet' ) . '</p>'
				);
			}
		}
	}

	/**
	 * Handle private site for logged-in users in a multisite.
	 *
	 * @param array $options
	 */
	protected function handle_private_loggedin_multisite( $options ) {

		if ( ! is_multisite() ) {
			return;
		}

		if ( ! $options['aioi_ms_requiremember'] ) {
			return;
		}

		if ( is_network_admin() ) {
			return;
		}

		// Need to check logged-in user is a member of this sub-site.
		$blogs = get_blogs_of_user( get_current_user_id() );

		if ( ! wp_list_filter( $blogs, [ 'userblog_id' => get_current_blog_id() ] ) ) {
			// So the user is not a member, let's proceed.

			$blog_name = get_bloginfo( 'name' );

			$output = '<p>' . esc_html(
				sprintf( /* translators: %s - name of the site. */
					__( 'You attempted to access the "%1$s" sub-site, but you are not currently a member of this site. If you believe you should be able to access "%1$s", please contact your network administrator.', 'all-in-one-intranet' ),
					$blog_name
				)
				) . '</p>';

			if ( ! empty( $blogs ) ) {

				$output .= '<p>' . esc_html__( 'You are a member of the following sites:', 'all-in-one-intranet' ) . '</p>';

				$output .= '<table>';

				foreach ( $blogs as $blog ) {
					$output .= "<tr>";
					$output .= "<td valign='top'>";
					$output .= "<a href='" . esc_url( get_home_url( $blog->userblog_id ) ) . "'>" . esc_html( $blog->blogname ) . "</a>";
					$output .= "</td>";
					$output .= "</tr>";
				}
				$output .= '</table>';
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die( $output );
		}
	}

	/**
	 * Handler for robots.txt - just disallow everything if private.
	 *
	 * @param string $output The robots.txt output.
	 * @param bool   $public Whether the site is considered "public".
	 *
	 * @return string
	 */
	public function aioi_robots_txt( $output, $public ) {

		$options = $this->get_option_aioi();

		if ( $options['aioi_privatesite'] ) {
			return "Disallow: /\n";
		}

		return $output;
	}

	/*
	 * Don't allow pingbacks if private.
	 */
	public function aioi_option_ping_sites( $sites ) {

		$options = $this->get_option_aioi();

		if ( $options['aioi_privatesite'] ) {
			return '';
		}

		return $sites;
	}

	/**
	 * Disable REST API.
	 *
	 * @return WP_Error
	 */
	public function aioi_rest_pre_dispatch( $result ) {

		$options      = $this->get_option_aioi();
		$allow_access = ! $options['aioi_privatesite'] || is_user_logged_in();
		$allow_access = (bool) apply_filters( 'aioi_allow_public_access', $allow_access );

		if ( ! $allow_access ) {
			return new WP_Error( 'not-logged-in', 'REST API Requests must be authenticated because All-In-One Intranet is active', [ 'status' => 401 ] );
		}

		return $result;
	}

	/**
	 * Redirect on login event.
	 *
	 * @param string  $redirect_to
	 * @param string  $requested_redirect_to
	 * @param WP_User $user
	 */
	public function aioi_login_redirect( $redirect_to, $requested_redirect_to = '', $user = null ) {

		if ( ! is_null( $user ) && isset( $user->user_login ) ) {
			$options = $this->get_option_aioi();

			if ( $options['aioi_loginredirect'] !== '' && admin_url() === $redirect_to ) {
				return $options['aioi_loginredirect'];
			}
		}

		return $redirect_to;
	}

	/**
	 * AUTO-LOGOUT.
	 * Reset timer on login.
	 *
	 * @param string $username
	 * @param WP_User $user
	 */
	public function aioi_wp_login( $username, $user ) {

		try {
			if ( $user->ID ) {
				update_user_meta( $user->ID, 'aioi_last_activity_time', time() );
			}
		} catch ( Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * AUTO-LOGOUT.
	 * Check whether user should be auto-logged out this time.
	 */
	public function aioi_check_activity() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id            = get_current_user_id();
		$last_activity_time = (int) get_user_meta( $user_id, 'aioi_last_activity_time', true );
		$logout_time_in_sec = $this->get_autologout_time_in_seconds();

		if (
			$logout_time_in_sec > 0 &&
			$last_activity_time + $logout_time_in_sec < time()
		) {
			$current_url = 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

			wp_logout();
			// Should hit the Login wall if site is private.
			wp_redirect( $current_url );
			exit;
		}

		update_user_meta( $user_id, 'aioi_last_activity_time', time() );
	}

	protected function get_autologout_time_in_seconds() {

		$options = $this->get_option_aioi();

		$options['aioi_autologout_time'] = (int) $options['aioi_autologout_time'];

		if ( $options['aioi_autologout_time'] === 0 ) {
			return 0;
		}

		switch ( $options['aioi_autologout_units'] ) {
			case 'days':
				return $options['aioi_autologout_time'] * DAY_IN_SECONDS;

			case 'hours':
				return $options['aioi_autologout_time'] * HOUR_IN_SECONDS;

			case 'minutes':
			default:
				return $options['aioi_autologout_time'] * MINUTE_IN_SECONDS;
		}
	}

	// MEMBERSHIP

	public function aioi_wpmu_new_user( $user_id ) {

		// Add this user to all default sub-sites, if required.
		$options      = $this->get_option_aioi();
		$default_role = $options['aioi_ms_membersrole'];

		if ( $default_role === '' ) {
			return;
		}

		$blogs = $this->get_all_blogids();

		foreach ( $blogs as $blogid ) {
			if ( ! is_user_member_of_blog( $user_id, $blogid ) ) {
				add_user_to_blog( $blogid, $user_id, $default_role );
			}
		}
	}

	public function aioi_wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Add all other users to this new sub-site, if required.
		$options      = $this->get_option_aioi();
		$default_role = $options['aioi_ms_membersrole'];

		if ( $default_role === '' ) {
			return;
		}

		foreach ( $this->get_all_userids() as $auserid ) {
			// Assume only the blog creator has been added so far.
			if ( $auserid !== $user_id ) {
				add_user_to_blog( $blog_id, $auserid, $default_role );
			}
		}
	}

	private function get_all_blogids() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d AND archived = '0' AND spam = '0' AND deleted = '0'", $wpdb->siteid ) );

		return is_array( $blogids ) ? $blogids : [];
	}

	private function get_all_userids() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$userids = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );

		return is_array( $userids ) ? $userids : [];
	}

	// PUT SETTINGS MENU ON PLUGINS PAGE

	protected function get_options_name() {
		return 'aioi_dsl';
	}

	protected function get_options_menuname() {
		return 'aioi_list_options';
	}

	protected function get_options_pagename() {
		return 'aioi_options';
	}

	protected function get_settings_url() {

		return is_multisite()
			? network_admin_url( 'settings.php?page=' . $this->get_options_menuname() )
			: admin_url( 'options-general.php?page=' . $this->get_options_menuname() );
	}

	/**
	 * Register plugin settings.
	 */
	public function aioi_admin_init() {

		register_setting(
			$this->get_options_pagename(),
			$this->get_options_name(),
			[ $this, 'aioi_options_validate' ]
		);
	}

	/**
	 * ADMIN AREA.
	 * Put settings menu on the plugins page.
	 *
	 * @param array  $links
	 * @param string $file
	 *
	 * @return array
	 */
	public function aioi_plugin_action_links( $links, $file ) {

		if ( $file === $this->my_plugin_basename() ) {
			$settings_link = '<a href="' . esc_url( $this->get_settings_url() ) . '">' . esc_html__( 'Settings', 'all-in-one-intranet' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Process values before saving to DB.
	 *
	 * @param array $input Values to save.
	 *
	 * @return array Validated values.
	 */
	public function aioi_options_validate( $input ) {

		$newinput                          = [];
		$newinput['aioi_version']          = $this->PLUGIN_VERSION;
		$newinput['aioi_privatesite']      = isset( $input['aioi_privatesite'] ) && (bool) $input['aioi_privatesite'];
		$newinput['aioi_ms_requiremember'] = isset( $input['aioi_ms_requiremember'] ) && (bool) $input['aioi_ms_requiremember'];
		$newinput['aioi_autologout_time']  = isset( $input['aioi_autologout_time'] ) ? (int) $input['aioi_autologout_time'] : 0;

		if ( ! preg_match( '/^[0-9]*$/i', $newinput['aioi_autologout_time'] ) ) {
			add_settings_error(
				'aioi_autologout_time',
				'nan_texterror',
				$this->get_error_string( 'aioi_autologout_time|nan_texterror' ),
				'error'
			);
			$newinput['aioi_autologout_time'] = 0;
		} else {
			$newinput['aioi_autologout_time'] = (int) $newinput['aioi_autologout_time'];
		}

		$newinput['aioi_autologout_units'] = isset( $input['aioi_autologout_units'] ) ? $input['aioi_autologout_units'] : '';
		if ( ! in_array( $newinput['aioi_autologout_units'], [ 'minutes', 'hours', 'days' ], true ) ) {
			$newinput['aioi_autologout_units'] = 'minutes';
		}

		$newinput['aioi_loginredirect'] = isset( $input['aioi_loginredirect'] ) ? sanitize_text_field( $input['aioi_loginredirect'] ) : '';

		$newinput['aioi_ms_membersrole'] = isset( $input['aioi_ms_membersrole'] ) ? sanitize_text_field( $input['aioi_ms_membersrole'] ) : '';

		return $newinput;
	}

	/**
	 * ADMIN AREA.
	 * Register plugin Settings in the admin area.
	 */
	public function aioi_admin_menu() {

		if ( is_multisite() ) {
			add_submenu_page(
				'settings.php',
				esc_html__( 'All-In-One Intranet settings', 'all-in-one-intranet' ),
				esc_html__( 'All-In-One Intranet', 'all-in-one-intranet' ),
				'manage_network_options',
				$this->get_options_menuname(),
				[ $this, 'aioi_options_do_page' ]
			);
		} else {
			add_options_page(
				esc_html__( 'All-In-One Intranet settings', 'all-in-one-intranet' ),
				esc_html__( 'All-In-One Intranet', 'all-in-one-intranet' ),
				'manage_options',
				$this->get_options_menuname(),
				[ $this, 'aioi_options_do_page' ]
			);
		}
	}

	/**
	 * ADMIN AREA.
	 * Render a plugin settings page.
	 */
	public function aioi_options_do_page() {

		wp_enqueue_script( 'aioi_admin', $this->my_plugin_url() . 'js/aioi-admin.js', [ 'jquery' ], $this->PLUGIN_VERSION, true );
		wp_enqueue_style( 'aioi_admin', $this->my_plugin_url() . 'css/style.css', [], $this->PLUGIN_VERSION );

		$submit_page = is_multisite() ? 'edit.php?action=' . $this->get_options_menuname() : 'options.php';
		?>

		<h1><?php esc_html_e( 'All-In-One Intranet', 'all-in-one-intranet' ); ?></h1>

		<div id="gal-tablewrapper">

			<div id="gal-tableleft" class="gal-tablecell">
				<hr/>

				<?php
				if ( is_multisite() ) {
					$this->aioi_options_do_network_errors();
				}
				?>

				<form action="<?php echo esc_attr( $submit_page ); ?>" method="post">

					<?php
					settings_fields( $this->get_options_pagename() );

					$this->aioi_privacysection_text();
					$this->aioi_memberssection_text();
					$this->aioi_loginredirectsection_text();
					$this->aioi_autologoutsection_text();
					$this->aioi_licensesection_text();

					submit_button();
					?>

				</form>
			</div>
			<?php $this->ga_options_do_sidebar(); ?>
		</div>

		<?php
	}

	/**
	 * ADMIN AREA.
	 * Render the privacy checkbox section.
	 */
	protected function aioi_privacysection_text() {

		$options = $this->get_option_aioi();
		?>

		<h3><?php esc_html_e( 'Privacy', 'all-in-one-intranet' ); ?></h3>

		<input id='input_aioi_privatesite' name='<?php echo esc_attr( $this->get_options_name() ); ?>[aioi_privatesite]' type='checkbox' <?php checked( (bool) $options['aioi_privatesite'] ); ?> class='checkbox' />
		<label for="input_aioi_privatesite" class="checkbox plain">
			<?php esc_html_e( 'Force site to be entirely private', 'all-in-one-intranet' ); ?>
		</label>

		<br />

		<?php if ( is_multisite() ) : ?>
			<input id='input_aioi_ms_requiremember' name='<?php echo esc_attr( $this->get_options_name() ); ?>[aioi_ms_requiremember]' type='checkbox' <?php checked( (bool) $options['aioi_ms_requiremember'] ); ?> class='checkbox' />
			<label for="input_aioi_ms_requiremember" class="checkbox plain">
			<?php esc_html_e( 'Require logged-in users to be members of a sub-site to view it', 'all-in-one-intranet' ); ?>
			</label>

			<br />
		<?php endif; ?>

		<p><?php esc_html_e( 'Note that your media uploads (e.g. photos) will still be accessible to anyone who knows their direct URLs.', 'all-in-one-intranet' ); ?></p>

		<?php $this->display_registration_warning(); ?>

		<br />

		<?php
	}

	/**
	 * ADMIN AREA.
	 * Render the warning message that anyone can register.
	 */
	protected function display_registration_warning() {

		if ( ! is_multisite() ) {

			if ( get_option( 'users_can_register' ) ) : ?>
				<div class="notice error" style="margin-left: 0">
					<p>
						<strong><?php esc_html_e( 'Warning:', 'all-in-one-intranet' ); ?></strong>
						<?php esc_html_e( 'Your site is set so that "Anyone can register" themselves.', 'all-in-one-intranet' ); ?>
						<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>">
							<?php esc_html_e( 'Change settings here.', 'all-in-one-intranet' ); ?>
						</a>
					</p>
				</div>
			<?php endif;

			return;
		}

		// We are in a multisite.

		if ( in_array( get_site_option( 'registration' ), [ 'all', 'user' ] ) ) {
			$limited_domains = get_site_option( 'limited_email_domains' );

			echo '<div class="notice error" style="margin-left: 0"><p>' .
				 '<strong>' . esc_html__( 'Warning:', 'all-in-one-intranet' ) . '</strong>';

			if ( is_array( $limited_domains ) && count( $limited_domains ) > 0 ) {
				 esc_html_e( 'Your site is set so that "Anyone can register" themselves, provided they are members of one of the following domains:', 'all-in-one-intranet' );
				 esc_html_e( ' ' . implode( ', ', $limited_domains ) );
			} else {
				esc_html_e( 'Warning: Your site is set so that "Anyone can register" themselves.', 'all-in-one-intranet' );
			}

			echo ' <a href="' . esc_url( network_admin_url( 'settings.php' ) ) . '">' . esc_html__( 'Change settings here.', 'all-in-one-intranet' ) . '</a>';
			echo '</p></div>';
		}
	}

	/**
	 * ADMIN AREA.
	 * Deal with members of sub-sites in a multisite.
	 */
	protected function aioi_memberssection_text() {

		$options = $this->get_option_aioi();

		if ( ! is_multisite() ) {
			return;
		}
		?>

		<h3><?php esc_html_e( 'Sub-site Membership', 'all-in-one-intranet' ); ?></h3>

		<label for="input_aioi_ms_membersrole" class="textbox plain">
			<?php esc_html_e( 'Users should default to the following role in all sub-sites', 'all-in-one-intranet' ); ?>
		</label>

		<select name="<?php echo esc_attr( $this->get_options_name() ); ?>[aioi_ms_membersrole]" id="input_aioi_ms_membersrole">
			<option value="">-- <?php esc_html_e( 'None', 'all-in-one-intranet' ); ?> --</option>
			<?php wp_dropdown_roles( $options['aioi_ms_membersrole'] ); ?>
		</select>

		<p><?php esc_html_e( 'Changing the default role here will not affect existing sub-sites and users.', 'all-in-one-intranet' ); ?></p>
		<br />

		<?php
	}

	/**
	 * ADMIN AREA.
	 * Render the login redirect section.
	 */
	protected function aioi_loginredirectsection_text() {

		$options = $this->get_option_aioi();
		?>

		<h3><?php esc_html_e( 'Login Redirect', 'all-in-one-intranet' ); ?></h3>

		<label for="input_aioi_loginredirect" class="textbox plain">
			<?php esc_html_e( 'Redirect after login to URL: ', 'all-in-one-intranet' ); ?>
		</label>

		<input id='input_aioi_loginredirect' name='<?php echo esc_attr( $this->get_options_name() ); ?>[aioi_loginredirect]' type='text' value='<?php echo esc_attr( $options['aioi_loginredirect'] ); ?>' size='60' />

		<br />

		<p><?php esc_html_e( 'Effective when users login via /wp-login.php directly. Otherwise, they will be taken to the page they were trying to access before being required to login.', 'all-in-one-intranet' ); ?></p>

		<br />
		<?php
	}

	/**
	 * ADMIN AREA.
	 * Render the auto logout section.
	 */
	protected function aioi_autologoutsection_text() {

		$options = $this->get_option_aioi();
		?>

		<h3><?php esc_html_e( 'Auto Logout', 'all-in-one-intranet' ); ?></h3>

		<label for="input_aioi_autologout_time" class="textbox plain">
			<?php esc_html_e( 'Auto logout inactive users after ', 'all-in-one-intranet' ); ?>
		</label>

		<input id='input_aioi_autologout_time' name='<?php echo esc_attr( $this->get_options_name() ); ?>[aioi_autologout_time]' type='number' value='<?php echo (int) $options['aioi_autologout_time'] === 0 ? '' : (int) $options['aioi_autologout_time']; ?>' class="small-text" />

		<select name='<?php echo esc_attr( $this->get_options_name() ); ?>[aioi_autologout_units]'>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->list_options( [ 'minutes', 'hours', 'days' ], $options['aioi_autologout_units'] );
			?>
		</select>
		<?php esc_html_e( "(leave blank to turn off auto-logout)", 'all-in-one-intranet' ); ?>.

		<br />
		<?php
	}

	/**
	 * Helper function to render the options for autologout time units.
	 *
	 * @param array $list     List of options keys.
	 * @param string $current Current option from DB.
	 *
	 * @return string
	 */
	protected function list_options( $list, $current ) {

		$output = '';
		$labels = [
			'minutes' => __( 'Minutes', 'all-in-one-intranet' ),
			'hours'   => __( 'Hours', 'all-in-one-intranet' ),
			'days'    => __( 'Days', 'all-in-one-intranet' ),
		];

		foreach ( $list as $option ) {
			$output .= '<option value="' . esc_attr( $option ) . '" ' . selected( $current, $option ) . '>' . esc_html( $labels[ $option ] ) . '</option>';
		}

		return $output;
	}

	/**
	 * Override in Premium.
	 */
	protected function aioi_licensesection_text() {
	}

	protected function ga_options_do_sidebar() {

		$drivelink   = 'https://wp-glogin.com/drive/?utm_source=aioiplugin&utm_campaign=liteplugin&utm_medium=Admin%20Sidebar&utm_content=GDrive';
		$gloginlink  = 'https://wp-glogin.com/glogin/?utm_source=aioiplugin&utm_campaign=liteplugin&utm_medium=Admin%20Sidebar&utm_content=GLogin';
		$avatarslink = 'https://wp-glogin.com/avatars/?utm_source=aioiplugin&utm_campaign=liteplugin&utm_medium=Admin%20Sidebar&utm_content=GAvatars';

		$adverts = [];

		$adverts[] = '<div>'
		             . '<a href="' . esc_url( $gloginlink ) . '" target="_blank">'
		             . '<img alt="Google Apps Login plugin" src="' . esc_url( $this->my_plugin_url() ) . 'img/basic_loginupgrade.png" />'
		             . '</a>'
		             . '<span>Try our <a href="' . esc_url( $gloginlink ) . '" target="_blank">premium Google Apps Login plugin</a> to revolutionize user management</span>'
		             . '</div>';

		$adverts[] = '<div>'
		             . '<a href="' . esc_url( $drivelink ) . '" target="_blank">'
		             . '<img alt="Google Drive Embedder plugin" src="' . esc_url( $this->my_plugin_url() ) . 'img/basic_driveplugin.png" />'
		             . '</a>'
		             . '<span>Check our <a href="' . esc_url( $drivelink ) . '" target="_blank">Google Drive Embedder</a> plugin to embed files from Drive</span>'
		             . '</div>';

		$adverts[] = '<div>'
		             . '<a href="' . esc_url( $avatarslink ) . '" target="_blank">'
		             . '<img alt="Google Profile Avatars Plugin" src="' . esc_url( $this->my_plugin_url() ) . 'img/basic_avatars.png" />'
		             . '</a>'
		             . '<span>Bring your site to life with <a href="' . esc_url( $avatarslink ) . '" target="_blank">Google Profile Avatars</a></span>'
		             . '</div>';

		$startnum = (int) gmdate( 'j' );

		echo '<div id="gal-tableright" class="gal-tablecell">';

		for ( $i = 0; $i < 2; $i ++ ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $adverts[ ( $startnum + $i ) % 3 ];
		}

		echo '</div>';
	}

	/**
	 * Retrieve a custom error message based on the provided error key.
	 *
	 * @param string $fielderror Error key.
	 *
	 * @return string
	 */
	protected function get_error_string( $fielderror ) {

		$local_error_strings = [
			'aioi_autologout_time|nan_texterror' => esc_html__( 'Auto logout time should be blank or a whole number', 'all-in-one-intranet' ),
		];

		if ( isset( $local_error_strings[ $fielderror ] ) ) {
			return $local_error_strings[ $fielderror ];
		}

		return esc_html__( 'Unspecified error', 'all-in-one-intranet' );
	}

	/**
	 * Save the network options.
	 */
	public function aioi_save_network_options() {

		check_admin_referer( $this->get_options_pagename() . '-options' );

		if ( isset( $_POST[ $this->get_options_name() ] ) && is_array( $_POST[ $this->get_options_name() ] ) ) {
			$inoptions  = $_POST[ $this->get_options_name() ];
			$outoptions = $this->aioi_options_validate( $inoptions );

			$error_code    = [];
			$error_setting = [];
			foreach ( get_settings_errors() as $e ) {
				if ( is_array( $e ) && isset( $e['code'] ) && isset( $e['setting'] ) ) {
					$error_code[]    = $e['code'];
					$error_setting[] = $e['setting'];
				}
			}

			update_site_option( $this->get_options_name(), $outoptions );

			// Redirect to settings page in network.
			wp_redirect(
				add_query_arg(
					[
						'page'          => $this->get_options_menuname(),
						'updated'       => true,
						'error_setting' => $error_setting,
						'error_code'    => $error_code,
					],
					network_admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Display a network error message upon settings save, if any.
	 */
	protected function aioi_options_do_network_errors() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		if ( isset( $_REQUEST['updated'] ) && $_REQUEST['updated'] ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error">
				<p>
					<strong><?php esc_html_e( 'Settings saved', 'all-in-one-intranet' ); ?></strong>
				</p>
			</div>
			<?php
		}

		if (
			isset( $_REQUEST['error_setting'], $_REQUEST['error_code'] ) &&
			is_array( $_REQUEST['error_setting'] ) && is_array( $_REQUEST['error_code'] )
		) {
			$error_code       = $_REQUEST['error_code'];
			$error_setting    = $_REQUEST['error_setting'];
			$error_code_count = count( $error_code );

			if ( $error_code_count > 0 && $error_code_count === count( $error_setting ) ) {
				for ( $i = 0; $i < $error_code_count; ++ $i ) {
					?>
					<div id="setting-error-settings_<?php echo (int) $i; ?>" class="error settings-error">
						<p>
							<strong><?php echo esc_html( htmlentities2( $this->get_error_string( $error_setting[ $i ] . '|' . $error_code[ $i ] ) ) ); ?></strong>
						</p>
					</div>
					<?php
				}
			}
		}

		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get the options from the database.
	 *
	 * @return array
	 */
	protected function get_option_aioi() {

		if ( $this->aioi_options !== null ) {
			return $this->aioi_options;
		}

		$option = get_site_option( $this->get_options_name(), [] );

		$default_options = $this->get_default_options();

		// Hydrate currently saved options with their default values, if missing.
		foreach ( $default_options as $k => $v ) {
			if ( ! isset( $option[ $k ] ) ) {
				$option[ $k ] = $v;
			}
		}

		$this->aioi_options = $option;

		return $this->aioi_options;
	}
}
