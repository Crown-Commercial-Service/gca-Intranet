<?php

namespace ASENHA\Classes;

/**
 * Disable User Account module: block login, list-table UI, profile field.
 *
 * @since 8.7.4
 */
class Disable_User_Account {

	/**
	 * User meta flag when login is disabled.
	 *
	 * @var string
	 */
	const META_KEY = 'asenha_user_account_disabled';

	/**
	 * WP_Error code when login is blocked for a disabled account (shared with Change Login URL redirect).
	 *
	 * @since 8.7.4
	 *
	 * @var string
	 */
	public const ERROR_CODE = 'asenha_account_disabled';

	/**
	 * Set during authenticate when blocking a disabled user; consumed by Change Login URL on wp_login_failed.
	 *
	 * @since 8.7.5
	 *
	 * @var int|null
	 */
	private static $pending_disabled_login_redirect_user_id = null;

	/**
	 * Register WordPress hooks for this module.
	 *
	 * @since 8.7.4
	 */
	public function register_hooks() {
		add_filter( 'authenticate', array( $this, 'block_disabled_user_login' ), 30, 3 );
		add_action( 'init', array( $this, 'maybe_force_logout_disabled_user' ), 1 );
		add_filter( 'wp_is_application_passwords_available_for_user', array( $this, 'filter_application_passwords_for_user' ), 10, 2 );
		add_filter( 'manage_users_columns', array( $this, 'add_status_column' ), PHP_INT_MAX - 10 );
		add_filter( 'manage_users_custom_column', array( $this, 'render_status_column' ), 10, 3 );
		add_filter( 'user_row_actions', array( $this, 'add_user_row_actions' ), 10, 2 );
		add_action( 'wp_ajax_asenha_user_account_toggle_disabled', array( $this, 'ajax_toggle_disabled' ) );
		add_action( 'admin_print_styles-users.php', array( $this, 'print_users_list_column_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_profile_scripts' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile_checkbox' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_checkbox' ) );
	}

	/**
	 * Whether the user is marked as login-disabled.
	 *
	 * @since 8.7.4
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public function is_user_disabled( $user_id ) {
		$user_id = (int) $user_id;
		if ( $user_id <= 0 ) {
			return false;
		}
		$flag = get_user_meta( $user_id, self::META_KEY, true );

		return ( '1' === $flag || true === $flag || 1 === $flag || 'true' === $flag );
	}

	/**
	 * Whether the current user may enable/disable login for the target user via UI or AJAX.
	 *
	 * @since 8.7.4
	 *
	 * @param int $target_user_id User ID being toggled.
	 * @return bool
	 */
	public function current_user_may_toggle_user( $target_user_id ) {
		$target_user_id = (int) $target_user_id;
		if ( $target_user_id <= 0 ) {
			return false;
		}
		if ( get_current_user_id() === $target_user_id ) {
			return false;
		}
		if ( ! current_user_can( 'edit_user', $target_user_id ) ) {
			return false;
		}
		if ( is_multisite() && is_super_admin( $target_user_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return and clear the pending disabled-login redirect user ID (same request as authenticate).
	 *
	 * @since 8.7.5
	 *
	 * @return int|null User ID if login was blocked for a disabled account; otherwise null.
	 */
	public static function consume_pending_disabled_login_redirect_user_id() {
		$id = self::$pending_disabled_login_redirect_user_id;
		self::$pending_disabled_login_redirect_user_id = null;
		if ( null === $id || (int) $id <= 0 ) {
			return null;
		}

		return (int) $id;
	}

	/**
	 * Block login for disabled accounts (after username/password checks yield a user).
	 *
	 * @since 8.7.4
	 *
	 * @param \WP_User|\WP_Error|null $user     User, error, or null.
	 * @param string                  $password Password (unused).
	 * @param string                  $username Username (unused).
	 * @return \WP_User|\WP_Error|null
	 */
	public function block_disabled_user_login( $user, $password, $username ) {
		if ( null === $user || is_wp_error( $user ) || ! ( $user instanceof \WP_User ) ) {
			return $user;
		}
		if ( $this->is_user_disabled( $user->ID ) ) {
			self::$pending_disabled_login_redirect_user_id = (int) $user->ID;

			return new \WP_Error(
				self::ERROR_CODE,
				__( 'ERROR: Your account has been disabled.', 'admin-site-enhancements' )
			);
		}

		return $user;
	}

	/**
	 * Log out immediately if the logged-in account is disabled (stale cookie/session).
	 *
	 * @since 8.7.4
	 */
	public function maybe_force_logout_disabled_user() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user_id = get_current_user_id();
		if ( ! $this->is_user_disabled( $user_id ) ) {
			return;
		}

		wp_logout();
		wp_safe_redirect( wp_login_url() );
		exit;
	}

	/**
	 * Hide Application Passwords for disabled users.
	 *
	 * @since 8.7.4
	 *
	 * @param bool    $available Whether App Passwords are available for the user.
	 * @param mixed   $user      \WP_User|int|null.
	 * @return bool
	 */
	public function filter_application_passwords_for_user( $available, $user ) {
		$user_id = 0;
		if ( $user instanceof \WP_User ) {
			$user_id = (int) $user->ID;
		} elseif ( is_numeric( $user ) ) {
			$user_id = (int) $user;
		}
		if ( $user_id > 0 && $this->is_user_disabled( $user_id ) ) {
			return false;
		}

		return $available;
	}

	/**
	 * Append Status column (last).
	 *
	 * @since 8.7.4
	 *
	 * @param string[] $columns Column headers.
	 * @return string[]
	 */
	public function add_status_column( $columns ) {
		$columns['asenha_user_account_status'] = __( 'Status', 'admin-site-enhancements' );

		return $columns;
	}

	/**
	 * Output Status cell content.
	 *
	 * @since 8.7.4
	 *
	 * @param string $output      Output.
	 * @param string $column_name Column key.
	 * @param int    $user_id     User ID.
	 * @return string
	 */
	public function render_status_column( $output, $column_name, $user_id ) {
		if ( 'asenha_user_account_status' !== $column_name ) {
			return $output;
		}
		if ( $this->is_user_disabled( $user_id ) ) {
			return esc_html__( 'Disabled', 'admin-site-enhancements' );
		}

		return '';
	}

	/**
	 * Row actions: Disable / Enable.
	 *
	 * @since 8.7.4
	 *
	 * @param string[]  $actions Row actions.
	 * @param \WP_User $user    User object.
	 * @return string[]
	 */
	public function add_user_row_actions( $actions, $user ) {
		if ( ! $user instanceof \WP_User ) {
			return $actions;
		}
		if ( ! current_user_can( 'list_users' ) ) {
			return $actions;
		}
		if ( ! $this->current_user_may_toggle_user( $user->ID ) ) {
			return $actions;
		}

		$disabled = $this->is_user_disabled( $user->ID );
		if ( $disabled ) {
			/* translators: verb: enable user login */
			$label = __( 'Enable', 'admin-site-enhancements' );
			$set_disabled = 0;
		} else {
			/* translators: verb: disable user login */
			$label = __( 'Disable', 'admin-site-enhancements' );
			$set_disabled = 1;
		}

		$actions['asenha_disable_user_account'] = sprintf(
			'<a href="#" class="asenha-user-account-toggle" role="button" data-user-id="%d" data-set-disabled="%d">%s</a>',
			(int) $user->ID,
			(int) $set_disabled,
			esc_html( $label )
		);

		return $actions;
	}

	/**
	 * AJAX: set or clear disabled meta for a user.
	 *
	 * @since 8.7.4
	 */
	public function ajax_toggle_disabled() {
		check_ajax_referer( 'asenha_user_account_toggle', 'nonce' );

		if ( ! current_user_can( 'list_users' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, you are not allowed to do this.', 'admin-site-enhancements' ) ), 403 );
		}

		$user_id       = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$set_disabled  = isset( $_POST['set_disabled'] ) ? (bool) filter_var( wp_unslash( $_POST['set_disabled'] ), FILTER_VALIDATE_BOOLEAN ) : false;

		if ( $user_id <= 0 || ! $this->current_user_may_toggle_user( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, you are not allowed to modify this user.', 'admin-site-enhancements' ) ), 403 );
		}

		if ( $set_disabled ) {
			update_user_meta( $user_id, self::META_KEY, '1' );
		} else {
			delete_user_meta( $user_id, self::META_KEY );
		}

		$is_disabled = $this->is_user_disabled( $user_id );

		if ( $is_disabled ) {
			$action_label = __( 'Enable', 'admin-site-enhancements' );
		} else {
			$action_label = __( 'Disable', 'admin-site-enhancements' );
		}

		wp_send_json_success(
			array(
				'is_disabled'       => $is_disabled,
				'status_html'       => $is_disabled ? esc_html__( 'Disabled', 'admin-site-enhancements' ) : '',
				'action_label'      => $action_label,
				'next_set_disabled' => $is_disabled ? 0 : 1,
			)
		);
	}

	/**
	 * Styles for Users list Status column.
	 *
	 * @since 8.7.4
	 */
	public function print_users_list_column_style() {
		?>
		<style>.column-asenha_user_account_status { width: 100px; }</style>
		<?php
	}

	/**
	 * Enqueue profile script to inject Account Management row (core has no hook after Password Reset).
	 *
	 * @since 8.7.4
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_profile_scripts( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'user-edit.php', 'profile.php' ), true ) ) {
			return;
		}

		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : get_current_user_id(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( get_current_user_id() === $user_id ) {
			return;
		}
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		if ( is_multisite() && is_super_admin( $user_id ) ) {
			return;
		}

		$disabled = $this->is_user_disabled( $user_id );

		$row_html  = '<tr class="asenha-disable-user-account-wrap">';
		$row_html .= '<th scope="row">' . esc_html__( 'Disable User Account', 'admin-site-enhancements' ) . '</th>';
		$row_html .= '<td>';
		$row_html .= '<label for="asenha_user_account_disabled">';
		$row_html .= '<input type="checkbox" name="asenha_user_account_disabled" id="asenha_user_account_disabled" value="1"' . checked( $disabled, true, false ) . ' />';
		$row_html .= ' ' . esc_html__( 'Disable login for this account.', 'admin-site-enhancements' );
		$row_html .= '</label></td></tr>';

		// Inject before user-profile.js snapshots the form (avoids false "Leave site?" warnings).
		wp_enqueue_script( 'user-profile' );

		wp_localize_script(
			'user-profile',
			'asenhaDisableUserAccountProfile',
			array(
				'rowHtml' => $row_html,
			)
		);

		wp_add_inline_script(
			'user-profile',
			$this->get_profile_row_injection_script(),
			'before'
		);
	}

	/**
	 * Inline JS to place the disable-account row before user-profile.js captures form state.
	 *
	 * @since 8.8.0
	 *
	 * @return string
	 */
	private function get_profile_row_injection_script() {
		return <<<'JS'
( function ( $ ) {
	'use strict';

	$( function () {
		if (
			typeof asenhaDisableUserAccountProfile === 'undefined' ||
			! asenhaDisableUserAccountProfile.rowHtml
		) {
			return;
		}

		var $table = $( 'table.form-table' )
			.filter( function () {
				return $( this ).find( '#password' ).length > 0;
			} )
			.first();

		if ( ! $table.length ) {
			return;
		}

		var $rows = $(
			$.parseHTML(
				asenhaDisableUserAccountProfile.rowHtml,
				document,
				true
			)
		);

		var $sessions = $table.find( 'tr.user-sessions-wrap' ).first();
		if ( $sessions.length ) {
			$sessions.before( $rows );
			return;
		}

		var $reset = $table.find( 'tr.user-generate-reset-link-wrap' ).first();
		if ( $reset.length ) {
			$reset.after( $rows );
			return;
		}

		var $pwWeak = $table.find( 'tr.pw-weak' ).first();
		if ( $pwWeak.length ) {
			$pwWeak.after( $rows );
		}
	} );
} )( jQuery );
JS;
	}

	/**
	 * Save the profile checkbox on user update.
	 *
	 * @since 8.7.4
	 *
	 * @param int $user_id User ID being saved.
	 */
	public function save_profile_checkbox( $user_id ) {
		$user_id = (int) $user_id;
		if ( $user_id <= 0 ) {
			return;
		}
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
			return;
		}
		if ( get_current_user_id() === $user_id ) {
			return;
		}
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}
		if ( ! $this->current_user_may_toggle_user( $user_id ) ) {
			return;
		}

		if ( isset( $_POST['asenha_user_account_disabled'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['asenha_user_account_disabled'] ) ) ) {
			update_user_meta( $user_id, self::META_KEY, '1' );
		} else {
			delete_user_meta( $user_id, self::META_KEY );
		}
	}

}
