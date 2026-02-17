<?php
/**
 * Plugin Name:       Google Apps Login Enterprise
 * Plugin URI:        https://wp-glogin.com/
 * Description:       Simple secure login for WordPress through users' Google Apps accounts (uses secure OAuth2, and MFA if enabled)
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Version:           3.5.0
 * Author:            WP Glogin Team
 * Author URI:        https://wp-glogin.com/
 * Text Domain:       googleappslogin-enterprise
 * Domain Path:       /lang
 * Network:           true
 *
 * Google Apps Login is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Google Apps Login is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Google Apps Login. If not, see <https://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'commercial_google_apps_login' ) ) {
	global $gal_core_already_exists;
	$gal_core_already_exists = true;
} else {
	require_once( plugin_dir_path( __FILE__ ) . '/core/commercial_google_apps_login.php' );
}

class enterprise_google_apps_login extends commercial_google_apps_login {

	protected $PLUGIN_VERSION        = '3.5.0';
	protected $WPGLOGIN_GA_STORE_URL = 'https://wp-glogin.com';
	protected $WPGLOGIN_GA_ITEM_NAME = 'Google Apps Login for WordPress Enterprise';
	protected $WPGLOGIN_GA_ITEM_ID   = 926;

	// Singleton
	private static $instance = null;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function ga_activation_hook( $network_wide ) {

		parent::ga_activation_hook( $network_wide );

		// Populate ga_grouproles with current admins
		$options = $this->get_option_galogin();
		if ( ! isset( $options['ga_grouproles'] ) ) {
			$adminrole = 'administrator';
			$admins    = [];

			if ( is_multisite() ) {
				$adminrole = '_gal_superadmin';
				foreach ( get_super_admins() as $username ) {
					$usr = get_user_by( 'login', $username );
					if ( $usr ) {
						array_push( $admins, $usr );
					}
				}
			} else {
				$admins = get_users( array( 'role' => $adminrole ) );
			}

			if ( is_array( $admins ) && count( $admins ) > 0 ) {
				$grouproles = array();
				foreach ( $admins as $usr ) {
					$grouproles[ $usr->user_email ] = $adminrole;
				}

				$options['ga_grouproles'] = $grouproles;
				$this->save_option_galogin( $options );
			}
		}
	}

	// Register for GAL service account

	public function gad_gather_serviceacct_reqs( $reqs_array ) {

		$reqs_array[] = [
			'Google Apps Login Enterprise',
			[
				'https://www.googleapis.com/auth/admin.directory.group.readonly' => __( 'Access Google Groups information on your domain (for Enterprise Group Roles)', 'googleappslogin-enterprise' ),
				'https://www.googleapis.com/auth/admin.directory.user.readonly' => __( 'Obtain Organizational Unit details for users on your domain (for Enterprise Group Roles)', 'googleappslogin-enterprise' ),
			],
		];

		return $reqs_array;
	}

	protected function add_actions() {

		parent::add_actions();
		add_filter( 'gal_gather_serviceacct_reqs', array( $this, 'gad_gather_serviceacct_reqs' ) );
	}

	// Enterprise functions

	protected function check_groups( $client, $userinfo, $user, $userdidnotexist ) {

		$options = $this->get_option_galogin();
		if ( ! property_exists( $userinfo, 'id' ) || ! isset( $options['ga_defaultrole'] ) ) {
			return;
		}

		// There is a setting to check either every login or only when a new user is auto-created
		if ( ! $options['ga_groupsonlogin'] && ! $userdidnotexist ) {
			return;
		}

		$saoptions = $this->get_sa_option();

		$want_role = $options['ga_defaultrole'];

		$in_groups = array( strtolower( $userinfo->email ) => true ); // Can use emails instead of group

		if ( isset( $options['ga_grouproles'] ) && is_array( $options['ga_grouproles'] ) ) {
			$grouproles = $options['ga_grouproles'];

			if (
				! isset( $saoptions['ga_serviceemail'] ) || $saoptions['ga_serviceemail'] == '' ||
				! isset( $saoptions['ga_sakey'] ) || $saoptions['ga_sakey'] == '' ||
				$options['ga_domainadmin'] == ''
			) {
				$this->logTransError( LOG_WARNING, $userinfo->email, 'Skipping Google Groups check because Service Account options are not yet set' );
			} else {
				// Can turn off the call to Google Groups to save bandwidth
				if ( apply_filters( 'gal_make_groups_call', true ) ) {
					try {
						$cred = $this->get_Auth_AssertionCredentials( [ 'https://www.googleapis.com/auth/admin.directory.group.readonly' ] );

						$serviceclient = $this->get_Google_Client();

						$serviceclient->setAssertionCredentials( $cred );

						// Include paths were set when client was created
						if ( ! class_exists( 'GoogleGAL_Service_Directory' ) ) {
							require_once( 'Google/Service/Directory.php' );
						}

						$groupservice = new GoogleGAL_Service_Directory( $serviceclient );

						$nextToken = '';

						do {
							$groupsresult = $groupservice->groups->listGroups(
								[
									'userKey'   => $userinfo->id,
									'pageToken' => $nextToken,
								]
							);

							$groupsdata = $groupsresult->getGroups();

							foreach ( $groupsdata as $g ) {
								$group_email               = strtolower( $g->email );
								$in_groups[ $group_email ] = true;
							}

							$nextToken = $groupsresult->getNextPageToken();
						} while ( $nextToken );
					} catch ( GoogleGAL_Service_Exception $ge ) {
						error_log( "Google Service Error fetching Groups: " . $ge->getMessage() );

						$errors  = $ge->getErrors();
						$doneerr = false;
						if ( is_array( $errors ) && count( $errors ) > 0 ) {
							if ( isset( $errors[0]['reason'] ) ) {
								switch ( $errors[0]['reason'] ) {
									case 'insufficientPermissions':
										$this->logTransError( LOG_WARNING, $userinfo->email, 'User had insufficient permission to fetch Google Group memberships', false );
										$doneerr = true;
										break;

									case 'accessNotConfigured':
										$this->logTransError( LOG_ERR, $userinfo->email, 'You need to enable Admin SDK for your project in Google Cloud Console', false );
										$doneerr = true;
										break;
								}
							}
						}

						if ( ! $doneerr ) {
							$this->logTransError( LOG_WARNING, $userinfo->email, 'Service Error fetching Google Groups: ' . $ge->getMessage(), false );
						}
					} catch ( GoogleGAL_Auth_Exception $ge ) {
						$error = $ge->getMessage();

						if ( preg_match( '/access_denied.+Requested scopes not allowed.+admin\.directory\.group\.readonly/s', $error ) ) {
							// Need to go in GA Admin: Security -> Advanced Settings -> Manage API client access and add
							// https://www.googleapis.com/auth/admin.directory.group.readonly scope next to Service Account client ID
							$this->logTransError( LOG_ERR, $userinfo->email, 'Auth error fetching Groups - have you enabled domain-wide delegation' );
						} elseif ( preg_match( '/refreshing the OAuth2 token.*invalid_grant.*Not a valid email/s', $error ) ) {
							$this->logTransError( LOG_ERR, $userinfo->email, 'Auth error invalid grant - Google Apps domain admin email is not correct in Settings -> Google Apps Login (Service Account settings)' );
						} elseif ( preg_match( '/refreshing the OAuth2 token.*invalid_grant/s', $error ) ) {
							$this->logTransError( LOG_ERR, $userinfo->email, 'Auth error invalid grant - does your JSON file match a valid Service Account key in a Google Cloud Console project' );
						} else {
							$this->logTransError( LOG_WARNING, $userinfo->email, 'Auth Error fetching Google Groups: ' . $ge->getMessage() );
						}
					} catch ( Exception $e ) {
						$this->logTransError( LOG_ERR, $userinfo->email, 'General Error fetching Google Groups: ' . $e->getMessage() );
					}
				}
			}

			// We need to also fetch the user's OrgUnitPath, but only if there are OrgUnitPaths in use
			// $in_groups is passed by reference
			$this->add_orgunitpaths_as_groups( $grouproles, $userinfo, $in_groups );

			foreach ( $grouproles as $g => $r ) {
				if ( isset( $in_groups[ strtolower( $g ) ] ) ) {
					$want_role = $r;
					break;
				}
			}
		}

		// Adjust roles if needed
		$this->apply_user_roles( $want_role, $user, $in_groups );
	}

	protected function add_orgunitpaths_as_groups( $grouproles, $userinfo, &$in_groups ) {

		// If GAL Multisite roles plugin is active, always get OrgUnitPath for the user in case subsites need it
		if ( ! apply_filters( 'gal_multisiteroles_active', false ) ) {
			$contains_orgunitpaths = false;
			foreach ( $grouproles as $g => $r ) {
				if ( substr( $g, 0, 1 ) === '/' ) {
					$contains_orgunitpaths = true;
					break;
				}
			}

			if ( ! $contains_orgunitpaths ) {
				return;
			}
		}

		$orgunitpath = '';
		$msg         = '';

		try {
			$gal = GoogleAppsLogin();

			$cred = $gal->get_Auth_AssertionCredentials(
				array( 'https://www.googleapis.com/auth/admin.directory.user.readonly' )
			);

			$serviceclient = $gal->get_Google_Client();

			$serviceclient->setAssertionCredentials( $cred );

			// Include paths were set when client was created
			if ( ! class_exists( 'GoogleGAL_Service_Directory' ) ) {
				require_once( 'Google/Service/Directory.php' );
			}

			$userservice = new GoogleGAL_Service_Directory( $serviceclient );

			$u = $userservice->users->get( $userinfo->email );

			if ( $u ) {
				$orgunitpath = $u->getOrgUnitPath();
			}
		} catch ( GoogleGAL_Service_Exception $ge ) {
			$errors  = $ge->getErrors();
			$doneerr = false;
			if ( is_array( $errors ) && count( $errors ) > 0 ) {
				if ( isset( $errors[0]['reason'] ) ) {
					switch ( $errors[0]['reason'] ) {
						case 'insufficientPermissions':
							$msg     = __( 'User had insufficient permission to fetch Google User data', 'googleappslogin-enterprise' );
							$doneerr = true;
							break;

						case 'accessNotConfigured':
							$msg     = __( 'You need to enable Admin SDK for your project in Google Cloud Console', 'googleappslogin-enterprise' );
							$doneerr = true;
							break;

						case 'forbidden':
							$msg     = __( 'Forbidden - are you sure the user you entered in Service Account settings is a Google Apps admin?', 'googleappslogin-enterprise' );
							$doneerr = true;
							break;

						case 'invalid':
							$msg     = isset( $errors[0]['message'] ) ? $errors[0]['message'] : 'Invalid search input';
							$doneerr = true;
							break;
					}
				}
			}

			if ( ! $doneerr ) {
				$msg = __( 'Service Error fetching Google Users:', 'googleappslogin-enterprise' ) . ' ' . $ge->getMessage();
			}
		} catch ( GoogleGAL_Auth_Exception $ge ) {
			$error = $ge->getMessage();
			if ( preg_match( '/Error refreshing the OAuth2 token.+invalid_grant/s', $error ) ) {
				/*
				 * When keys don't match etc
				* Error refreshing the OAuth2 token, message: '{ "error" : "invalid_grant" }'
				*/
				$msg = __( 'Error - please check your JSON key and service account email are still valid in Settings -> Google Apps Login (Service Account settings)', 'googleappslogin-enterprise' );
			} elseif ( preg_match( '/Error refreshing the OAuth2 token.+unauthorized_client/s', $error ) ) {
				/*
				 * When sub is wrong
				* Error refreshing the OAuth2 token, message: '{ "error" : "unauthorized_client", "error_description" : "Unauthorized client or scope in request." }'
				*/
				$msg = __( 'Error - please check Service Account config in Settings -> Google Apps Login and admin.google.com', 'googleappslogin-enterprise' );
			} elseif ( preg_match( '/Error refreshing the OAuth2 token.+access_denied/s', $error ) ) {
				/*
				 * When scope not entered
				* Google Auth Error fetching Users: Error refreshing the OAuth2 token, message: '{
				 * "error" : "access_denied", "error_description" : "Requested client not authorized."}'
				*/
				$msg = __( 'Error - please check you have added the required permissions scope to your Google Cloud Console project. See Settings -> Google Apps Login (Service Account settings).', 'googleappslogin-enterprise' );
			} else {
				$msg = __( "Google Auth Error fetching Users:", 'googleappslogin-enterprise' ) . ' ' . $ge->getMessage();
			}
		} catch ( GAL_Service_Exception $e ) {
			$msg = __( "GAL Error fetching Google Users:", 'googleappslogin-enterprise' ) . ' ' . $e->getMessage();
		} catch ( Exception $e ) {
			$msg = __( "General Error fetching Google Users:", 'googleappslogin-enterprise' ) . ' ' . $e->getMessage();
		}

		if ( $msg !== '' ) {
			$this->logTransError( LOG_ERR, $userinfo->email, $msg );
		}

		if ( $orgunitpath !== '' && $orgunitpath !== '/' ) {
			$cum_oup = '';
			foreach ( explode( '/', strtolower( substr( $orgunitpath, 1 ) ) ) as $oupcomponent ) {
				$cum_oup               .= '/' . $oupcomponent;
				$in_groups[ $cum_oup ] = true;
			}
		}
	}

	// Consider applying roles for user on all blogs
	protected function apply_user_roles( $want_role, $user, $in_groups ) {

		$options = $this->get_option_galogin();

		if ( is_multisite() ) {
			// Super Admin is a special case: _gal_superadmin
			if ( $want_role === '_gal_superadmin' ) {
				if ( ! is_super_admin( $user->ID ) ) {
					if ( ! function_exists( 'grant_super_admin' ) ) {
						require_once ABSPATH . 'wp-admin/includes/ms.php';
					}

					$rv = grant_super_admin( $user->ID );
					$this->logTransError( LOG_INFO, $user->user_email, 'Promoting user to Network Super Admin: ' . ( $rv ? 'success' : 'failed' ) );
				}

				// For sub-site purposes, set role to normal sub-site admin
				$want_role = 'administrator';
			} elseif ( $options['ga_demotesupers'] ) {
				// If they are an existing super admin, we should demote them
				if ( is_super_admin( $user->ID ) ) {
					if ( ! function_exists( 'revoke_super_admin' ) ) {
						require_once ABSPATH . 'wp-admin/includes/ms.php';
					}

					$rv = revoke_super_admin( $user->ID );
					$this->logTransError( LOG_INFO, $user->user_email, 'Demoted from Network Super Admin: ' . ( $rv ? 'success' : 'failed' ) );
				}
			}

			// Examine membership and roles of sub-sites
			// Only need user's current blogs if we're not going to add them to any more blogs anyway
			$blogs = $options['ga_addsubsites']
				? $this->get_all_blogids()
				: $this->get_user_blogids( $user->ID );

			foreach ( $blogs as $blogid ) {
				$is_user_member = is_user_member_of_blog( $user->ID, $blogid );
				if ( $is_user_member || $options['ga_addsubsites'] ) {
					$this->set_user_role( $want_role, $user, $blogid, $is_user_member, $in_groups );
				}
			}
		} else {
			$this->set_user_role( $want_role, $user, get_current_blog_id(), true, $in_groups );
		}
	}

	// Actually set role for user on one specific blog
	// Filter gal_user_new_role can change the outcome
	// Will do nothing if user role is already as desired
	protected function set_user_role( $want_role, $user, $blogid, $is_user_member, $in_groups ) {

		$old_want_role = $want_role;
		$want_role     = apply_filters( 'gal_user_new_role', $want_role, $user, $blogid, $is_user_member, $in_groups );

		if ( is_multisite() ) {
			if ( $want_role == '' ) {
				if ( $is_user_member ) {
					remove_user_from_blog( $user->ID, $blogid );
					$this->logTransError( LOG_INFO, $user->user_email, 'Removed user from sub-site ID ' . $blogid );
				}
			} else {
				// But what was the old role?
				switch_to_blog( $blogid );
				$blog_user = get_userdata( $user->ID );
				restore_current_blog();

				$prev_role = '';
				if ( $is_user_member && $blog_user ) {
					if ( is_array( $blog_user->roles ) && count( $blog_user->roles ) == 1 ) {
						$rolelist  = array_values( $blog_user->roles );
						$prev_role = $rolelist[0];
					}
				}

				if ( $prev_role != $want_role ) {
					add_user_to_blog( $blogid, $user->ID, $want_role );
					$this->logTransError(
						LOG_INFO,
						$user->user_email,
						'Setting role to ' . ( $want_role === '' ? '<no access>' : $want_role ) . ' from role '
						. ( $prev_role === '' ? '<undefined>' : $prev_role ) . ' on sub-site ID ' . $blogid
					);
				}
			}
		} else {
			$prev_role = '';
			if ( is_array( $user->roles ) && count( $user->roles ) == 1 ) {
				$rolelist  = array_values( $user->roles );
				$prev_role = $rolelist[0];
			}
			if ( $prev_role !== $want_role ) {
				// Can this be blank to mean no privileges?
				$user->set_role( $want_role );

				$this->logTransError(
					LOG_INFO,
					$user->user_email,
					'Changed role to ' . ( $want_role === '' ? '<no access>' : $want_role )
					. ' from ' . ( $prev_role === '' ? '<undefined>' : $prev_role )
				);
			}
		}

		do_action( 'gal_user_role_changed', $want_role, $user, $blogid, $is_user_member, $in_groups );
	}

	private function get_all_blogids() {

		global $wpdb;
		$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d AND archived = '0' AND spam = '0' AND deleted = '0'", $wpdb->siteid ) );

		return is_array( $blogids ) ? $blogids : [];
	}

	private function get_user_blogids( $userid ) {

		$blogobjs = get_blogs_of_user( $userid );
		$blogids  = array();
		foreach ( $blogobjs as $blogobj ) {
			$blogids[] = $blogobj->userblog_id;
		}

		return $blogids;
	}

	// Just log user creation
	protected function createUser( $userinfo, $parts, $options ) {

		$user = parent::createUser( $userinfo, $parts, $options );
		if ( ! is_wp_error( $user ) ) {
			$this->logTransError( LOG_INFO, $userinfo->email, 'Auto-created new user with role ' . $options['ga_defaultrole'] );
		}

		return $user;
	}

	public function enqueue_ruleeditor_js_and_css() {

		wp_enqueue_script( 'gal_ruleeditor_admin_js', $this->my_plugin_url() . 'js/enterprise/ruleeditor.js', array( 'jquery-ui-sortable' ) );
		wp_enqueue_style( 'gal_ruleeditor_admin_css', $this->my_plugin_url() . 'css/enterprise/ruleeditor.css' );
		wp_enqueue_style( 'gal_jquery_ui_css', $this->my_plugin_url() . 'css/enterprise/jquery-ui.css' );
	}

	protected function groupsection_text() {

		$options = $this->get_option_galogin();

		$grouproles = isset( $options['ga_grouproles'] ) && is_array( $options['ga_grouproles'] ) ? $options['ga_grouproles'] : array();
		if ( count( $grouproles ) == 0 ) {
			$grouproles[''] = ''; // Provide a first blank row to kick things off
		}

		$this->enqueue_ruleeditor_js_and_css();

		echo '<h3>' . esc_html__( 'Enterprise Role Mapping Rules', 'googleappslogin-enterprise' ) . '</h3>';

		echo '<div id="gal-ent-rulestable"><ul class="ui-sortable">';

		foreach ( $grouproles as $g => $r ) {
			echo "<li data-group='" . esc_attr( $g ) . "' data-role='" . esc_attr( $r ) . "' class='ui-state-default ui-sortable-handle' />";
		}

		echo '</ul></div>';

		?>

		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {

				$( '#gal-ent-rulestable' ).makeRuleEditor( {
					'roleslist_selector': '#ga_defaultrole_ent',
					'options_name': '<?php echo esc_attr( $this->get_options_name() ); ?>',
				} );

			} );
		</script>

		<?php

		//echo '<br class="clear">';
		echo '<label for="ga_defaultrole_ent" class="textinput">' . __( 'Default role', 'googleappslogin-enterprise' ) . '</label>';
		echo "<select name='" . esc_attr( $this->get_options_name() ) . "[ga_defaultrole_ent]' id='ga_defaultrole_ent' class='select'>";
		$this->output_roles_dropdown( $options['ga_defaultrole_ent'] );
		echo "</select>";

		echo '<br class="clear">';

		echo "<input id='input_ga_groupsonlogin' name='" . esc_attr( $this->get_options_name() ) . "[ga_groupsonlogin]' type='checkbox' " . ( $options['ga_groupsonlogin'] ? 'checked' : '' ) . " class='checkbox' />";
		echo '<label for="input_ga_groupsonlogin" class="checkbox plain">' . __( 'Check and reset roles on every login', 'googleappslogin-enterprise' ) . '</label>';

		if ( is_multisite() ) {
			echo '<br class="clear">';

			echo "<input id='input_ga_addsubsites' name='" . esc_attr( $this->get_options_name() ) . "[ga_addsubsites]' type='checkbox' " . ( $options['ga_addsubsites'] ? 'checked' : '' ) . " class='checkbox' />";
			echo '<label for="input_ga_addsubsites" class="checkbox plain">' . __( 'Add users to sub-sites if they are not yet members', 'googleappslogin-enterprise' ) . '</label>';

			echo '<br class="clear">';

			echo "<input id='input_ga_demotesupers' name='" . esc_attr( $this->get_options_name() ) . "[ga_demotesupers]' type='checkbox' " . ( $options['ga_demotesupers'] ? 'checked' : '' ) . " class='checkbox' />";
			echo '<label for="input_ga_demotesupers" class="checkbox plain">' . __( 'Demote existing Super Admins who do not have a Super Admin mapping above', 'googleappslogin-enterprise' ) . '</label>';
		}

		echo '<br class="clear">';

		?>

		<p>Where a user matches multiple groups or Organizational Units, the first match will be used. You can specify a user's own email address
			instead of a Group email in order to target an individual user.</p>

		<p>Don't forget to enable the Admin SDK and also ensure your account will remain an Administrator when the above rules are applied to your own account.</p>

		<br class="clear">

		<p>In order for all users to have permissions to access their Group information from Google, you will need to create
			a Service Account. Please see our
			<a href="https://wp-glogin.com/docs/google-apps-login/configuration/enterprise-setup/?utm_source=EntServiceAccount&utm_medium=freemium&utm_campaign=EnterpriseLogin" target="_blank">extended instructions here</a>
			or email support@wp-glogin.com to arrange a walkthrough of this process.</p>

		<?php

		if ( is_multisite() ) {
			echo '<p>If you require some sub-sites to have different role-mapping rules that override the rules set above, please get in touch - we have an extension plugin called Google Apps Multisite Roles.</p>';
		}
	}

	private function output_roles_dropdown( $default_role ) {

		if ( is_multisite() ) {
			echo "<option " . ( $default_role === '_gal_superadmin' ? "selected='selected'" : "" ) . " value='_gal_superadmin'>" . esc_html__( '-- Super Admin --', 'googleappslogin-enterprise' ) . "</option>";
		}
		$this->wp_dropdown_roles_safe( $default_role );
		echo "<option " . ( $default_role === '' ? "selected='selected'" : "" ) . " value=''>" . esc_html__( '-- No Access --', 'googleappslogin-enterprise' ) . "</option>";
	}

	protected function want_premium_default_role() {

		return false;
	}

	public function ga_options_validate( $input ) {

		$newinput = parent::ga_options_validate( $input );

		$newgrouproles = array();
		if ( isset( $input['ga_grouproles'] ) && is_array( $input['ga_grouproles'] ) ) {
			// If being called just to sanitize the options

			foreach ( $input['ga_grouproles'] as $g => $r ) {
				if ( sanitize_email( $g ) != '' || $this->sanitize_orgunitpath( $g ) != '' ) {
					$newgrouproles[ $g ] = (string) $r;
				}
			}
		} else {
			// If being called on form submission

			if ( isset( $input['groups'] ) && isset( $input['roles'] ) && is_array( $input['groups'] ) && is_array( $input['roles'] ) ) {
				for ( $i = 0; $i < count( $input['groups'] ); ++ $i ) {
					$g = trim( $input['groups'][ $i ] );
					if ( $g != '' ) {
						// These can now be emails or OrgUnitPaths
						$newg = sanitize_email( strtolower( $g ) );
						if ( $newg == '' ) {
							$newg = $this->sanitize_orgunitpath( $g );
							if ( $newg == '' ) {
								add_settings_error(
									'ga_grouproles',
									'email_error',
									self::get_error_string( 'ga_grouproles|email_error' ),
									'error'
								);
							}
						}

						if ( $newg != '' ) {
							$g = $newg;
						}

						$newgrouproles[ $g ] = $input['roles'][ $i ];
					}
				}
			}
		}

		$newinput['ga_grouproles'] = $newgrouproles;

		$newinput['ga_groupsonlogin'] = isset( $input['ga_groupsonlogin'] ) ? (boolean) $input['ga_groupsonlogin'] : false;

		$newinput['ga_addsubsites']  = isset( $input['ga_addsubsites'] ) ? (boolean) $input['ga_addsubsites'] : false;
		$newinput['ga_demotesupers'] = isset( $input['ga_demotesupers'] ) ? (boolean) $input['ga_demotesupers'] : false;

		return $newinput;
	}

	protected function sanitize_orgunitpath( $oup ) {

		if ( preg_match( '|(\/[^/]+)+|', $oup ) ) {
			return $oup;
		}

		return '';
	}

	protected function get_error_string( $field_error ) {

		$premium_local_error_strings = array(
			'ga_grouproles|email_error' => __( 'Rule names must be valid email addresses or OrgUnitPaths', 'googleappslogin-enterprise' ),
		);
		if ( isset( $premium_local_error_strings[ $field_error ] ) ) {
			return $premium_local_error_strings[ $field_error ];
		}

		return parent::get_error_string( $field_error );
	}

	protected function get_default_options() {

		return array_merge(
			parent::get_default_options(),
			[
                'ga_groupsonlogin' => false,
                'ga_addsubsites'   => false,
                'ga_demotesupers'  => false,
			]
		);
	}

	// Error Logging

	protected function logTransError( $level, $useremail, $msg, $echo_to_errlog = true ) {

		$trans = $this->getTransErrors();
		if ( $trans === false || ! is_array( $trans ) ) {
			$trans = array();
		}
		array_unshift( $trans, array( 'level' => $level, 'time' => time(), 'user' => $useremail, 'msg' => $msg ) );
		$trans = array_slice( $trans, 0, 100 );
		set_site_transient( 'gal_enterprise_logs', $trans, 0 );

		if ( $echo_to_errlog ) {
			error_log( "For user " . $useremail . ": " . $msg );
		}
	}

	protected function getTransErrors() {

		return get_site_transient( 'gal_enterprise_logs' );
	}

	protected function draw_more_tabs() {

		parent::draw_more_tabs();
		?>
		<a href="#logs" id="logs-tab" class="nav-tab"><?php esc_html_e( 'Logs', 'googleappslogin-enterprise' ); ?></a>
		<?php
	}

	protected function ga_moresection_text() {

		parent::ga_moresection_text();

		$errors = $this->getTransErrors();

		echo '<div id="logs-section" class="galtab">';

		if ( $errors === false || ! is_array( $errors ) ) {
			echo '<p>';
			_e( 'There are no logs to view.', 'googleappslogin-enterprise' );
			echo '</p>';
		} else {
			echo '<table>';

			foreach ( $errors as $err ) {
				echo '<tr class="gal_log_level' . $err['level'] . '"> ';
				echo '<td>' . htmlentities( date( "Y-m-d H:i:s", $err['time'] ) ) . '</td> ';
				echo '<td>' . htmlentities( $err['user'] ) . '</td> ';
				echo '<td>' . htmlentities( $err['msg'] ) . '</td> ';
				echo '</tr>';
			}

			echo '</table>';
		}

		echo '</div>';
	}

	// AUX FUNCTIONS

	protected function get_eddsl_optname() {

		return 'eddsl_gal_enterprise_ls';
	}

	public function my_plugin_basename() {

		$basename = plugin_basename( __FILE__ );
		if ( '/' . $basename == __FILE__ ) { // Maybe due to symlink.
			$basename = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
		}

		return $basename;
	}

	protected function my_plugin_url() {

		$basename = plugin_basename( __FILE__ );
		if ( '/' . $basename == __FILE__ ) { // Maybe due to symlink.
			return plugins_url() . '/' . basename( dirname( __FILE__ ) ) . '/';
		}

		// Normal case (non symlink).
		return plugin_dir_url( __FILE__ );
	}

}

// Global accessor function to singleton.
function galentGoogleAppsLogin() {

	return enterprise_google_apps_login::get_instance();
}

// Initialise at least once.
galentGoogleAppsLogin();

if ( ! function_exists( 'GoogleAppsLogin' ) ) {
	function GoogleAppsLogin() {

		return galentGoogleAppsLogin();
	}
}
