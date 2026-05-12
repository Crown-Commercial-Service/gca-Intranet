<?php

/*
Plugin Name: GCA Feature Flags
Description: Register and toggle feature flags for the GCA Intranet from the WordPress admin.
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCA_Feature_Flags {

	const OPTION_NAME  = 'gca_feature_flags';
	const MENU_SLUG    = 'gca-feature-flags';
	const NONCE_ACTION = 'gca_feature_flags_save';

	private static $flags = array();

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ) );
		add_action( 'admin_post_gca_feature_flags_save', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_notices', array( __CLASS__, 'show_notices' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_gca_toggle_single_flag', array( __CLASS__, 'handle_toggle_single' ) );
	}

	public static function handle_toggle_single() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions.', 403 );
		}

		check_ajax_referer( 'gca_toggle_single_flag' );

		$flag_id = isset( $_POST['flag_id'] ) ? sanitize_key( $_POST['flag_id'] ) : '';

		if ( ! $flag_id || ! isset( self::$flags[ $flag_id ] ) ) {
			wp_send_json_error( 'Unknown flag.', 400 );
		}

		$options = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$options[ $flag_id ] = ! self::is_enabled( $flag_id );
		update_option( self::OPTION_NAME, $options );

		wp_send_json_success( array( 'enabled' => $options[ $flag_id ] ) );
	}

	public static function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		$css_file = get_template_directory() . '/assets/dist/feature-flags-admin.css';

		wp_register_style( 'gca-feature-flags-admin', false, array(), null );
		wp_enqueue_style( 'gca-feature-flags-admin' );

		if ( file_exists( $css_file ) ) {
			wp_add_inline_style( 'gca-feature-flags-admin', file_get_contents( $css_file ) ); // phpcs:ignore
		}
	}

	/**
	 * Register a feature flag.
	 *
	 * Call this from your theme's functions.php or a plugin to define a flag.
	 *
	 * @param string $id   Unique slug, e.g. 'new-homepage-layout'
	 * @param array  $args {
	 *     @type string $label       Human-readable name shown in the admin UI.
	 *     @type string $description Short explanation of what the flag controls.
	 *     @type bool   $default     Default state when no saved value exists. Default false.
	 * }
	 */
	public static function register( $id, $args = array() ) {
		$id = sanitize_key( $id );

		if ( '' === $id ) {
			return;
		}

		self::$flags[ $id ] = wp_parse_args(
			$args,
			array(
				'label'       => ucwords( str_replace( array( '-', '_' ), ' ', $id ) ),
				'description' => '',
				'default'     => false,
				'tags'        => array(),
				'parent'      => '',
			)
		);

		if ( ! is_array( self::$flags[ $id ]['tags'] ) ) {
			self::$flags[ $id ]['tags'] = array();
		}
	}

	/**
	 * Check whether a feature flag is enabled.
	 *
	 * @param string $id Flag slug.
	 * @return bool
	 */
	public static function is_enabled( $id ) {
		$id      = sanitize_key( $id );
		$options = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		if ( array_key_exists( $id, $options ) ) {
			return (bool) $options[ $id ];
		}

		// Fall back to the registered default.
		if ( isset( self::$flags[ $id ] ) ) {
			return (bool) self::$flags[ $id ]['default'];
		}

		return false;
	}

	/**
	 * Return all registered flags.
	 *
	 * @return array
	 */
	public static function get_all() {
		return self::$flags;
	}

	// -------------------------------------------------------------------------
	// Admin page
	// -------------------------------------------------------------------------

	public static function add_admin_page() {
		add_options_page(
			'Feature Flags',
			'Feature Flags',
			'manage_options',
			self::MENU_SLUG,
			array( __CLASS__, 'render_admin_page' )
		);
	}

	public static function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		check_admin_referer( self::NONCE_ACTION );

		$options   = get_option( self::OPTION_NAME, array() );
		$submitted = ( isset( $_POST['gca_flags'] ) && is_array( $_POST['gca_flags'] ) )
			? $_POST['gca_flags']
			: array();

		// Only persist values for flags that are currently registered.
		foreach ( self::$flags as $id => $args ) {
			$options[ $id ] = isset( $submitted[ $id ] ) ? true : false;
		}

		update_option( self::OPTION_NAME, $options );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::MENU_SLUG,
					'updated' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	public static function show_notices() {
		$screen = get_current_screen();

		if ( ! $screen || 'settings_page_' . self::MENU_SLUG !== $screen->id ) {
			return;
		}

		if ( ! empty( $_GET['updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>Feature flags saved.</strong></p></div>';
		}
	}

	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$flags    = self::$flags;
		$all_tags = array();
		foreach ( $flags as $flag ) {
			foreach ( $flag['tags'] as $tag ) {
				$all_tags[ $tag ] = true;
			}
		}
		$all_tags = array_keys( $all_tags );
		sort( $all_tags );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Feature Flags', 'gca' ); ?></h1>
			<p>Enable or disable features for the GCA Intranet. Changes take effect immediately on save.</p>

			<?php if ( empty( $flags ) ) : ?>
				<div class="notice notice-info">
					<p>
						No feature flags are registered yet.
						Use <code>gca_register_feature_flag( 'flag-id', [ 'label' => '...', 'description' => '...' ] )</code>
						in your theme or plugin to add flags here.
					</p>
				</div>
			<?php else : ?>
				<div class="gca-flags-controls">
					<div class="gca-flags-search">
						<input
							type="search"
							id="gca-flag-search"
							placeholder="Search flags&hellip;"
							aria-label="Search feature flags"
						>
					</div>
					<?php if ( ! empty( $all_tags ) ) : ?>
					<div class="gca-tag-filter" id="gca-tag-filter">
						<button type="button" class="gca-tag-filter-btn" id="gca-tag-filter-btn" aria-haspopup="listbox" aria-expanded="false">
							Filter by tag <span class="gca-tag-filter-count" id="gca-tag-filter-count" style="display:none"></span>
						</button>
						<div class="gca-tag-filter-dropdown" id="gca-tag-filter-dropdown" style="display:none">
							<?php foreach ( $all_tags as $tag ) : ?>
							<label>
								<input type="checkbox" class="gca-tag-checkbox" value="<?php echo esc_attr( $tag ); ?>">
								<?php echo esc_html( $tag ); ?>
							</label>
							<?php endforeach; ?>
							<button type="button" class="gca-tag-filter-clear" id="gca-tag-filter-clear">Clear filter</button>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<p id="gca-flags-no-results">No flags match your search.</p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<input type="hidden" name="action" value="gca_feature_flags_save">

					<table class="gca-flags-table">
						<thead>
							<tr>
								<th>Feature</th>
								<th style="width: 80px; text-align: center;">
							<button type="button" id="gca-toggle-all-btn" class="button gca-toggle-all-btn">Enable All</button>
						</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// Group flags: root flags in order, children keyed by parent id.
							$root_flags     = array();
							$children_flags = array();
							foreach ( $flags as $id => $flag ) {
								$parent = ! empty( $flag['parent'] ) ? sanitize_key( $flag['parent'] ) : '';
								if ( $parent && isset( $flags[ $parent ] ) ) {
									$children_flags[ $parent ][ $id ] = $flag;
								} else {
									$root_flags[ $id ] = $flag;
								}
							}

							foreach ( $root_flags as $id => $flag ) :
								$tags         = ! empty( $flag['tags'] ) ? (array) $flag['tags'] : array();
								$has_children = ! empty( $children_flags[ $id ] );
							?>
								<tr
									data-tags="<?php echo esc_attr( implode( ',', array_map( 'sanitize_key', $tags ) ) ); ?>"
									<?php if ( $has_children ) : ?>
										data-flag-id="<?php echo esc_attr( $id ); ?>"
									<?php endif; ?>
								>
									<td>
										<strong><?php echo esc_html( $flag['label'] ); ?></strong>
										<?php if ( ! empty( $flag['description'] ) ) : ?>
											<div class="gca-flag-desc"><?php echo esc_html( $flag['description'] ); ?></div>
										<?php endif; ?>
										<?php if ( ! empty( $tags ) ) : ?>
											<div class="gca-flag-tags">
												<?php foreach ( $tags as $tag ) : ?>
													<span class="gca-flag-tag"><?php echo esc_html( $tag ); ?></span>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
										<div class="gca-flag-id">gca_flag_enabled( '<?php echo esc_html( $id ); ?>' )</div>
									</td>
									<td style="text-align: center;">
										<label class="gca-toggle" aria-label="Toggle <?php echo esc_attr( $flag['label'] ); ?>">
											<input
												type="checkbox"
												name="gca_flags[<?php echo esc_attr( $id ); ?>]"
												<?php checked( self::is_enabled( $id ) ); ?>
												<?php if ( $has_children ) : ?>
													data-parent-toggle="<?php echo esc_attr( $id ); ?>"
												<?php endif; ?>
											>
											<span class="gca-toggle-slider"></span>
										</label>
									</td>
								</tr>

								<?php if ( $has_children ) :
									$parent_enabled = self::is_enabled( $id );
									$last_child_id  = array_key_last( $children_flags[ $id ] );
									foreach ( $children_flags[ $id ] as $child_id => $child_flag ) :
										$child_tags = ! empty( $child_flag['tags'] ) ? (array) $child_flag['tags'] : array();
										$is_last    = ( $child_id === $last_child_id );
									?>
										<tr
											class="gca-flag-child<?php echo $parent_enabled ? '' : ' gca-flag-child-hidden'; ?><?php echo $is_last ? ' gca-flag-child-last' : ''; ?>"
											data-child-of="<?php echo esc_attr( $id ); ?>"
											data-tags="<?php echo esc_attr( implode( ',', array_map( 'sanitize_key', $child_tags ) ) ); ?>"
										>
											<td>
												<div class="gca-child-inner">
													<strong><?php echo esc_html( $child_flag['label'] ); ?></strong>
													<?php if ( ! empty( $child_flag['description'] ) ) : ?>
														<div class="gca-flag-desc"><?php echo esc_html( $child_flag['description'] ); ?></div>
													<?php endif; ?>
													<?php if ( ! empty( $child_tags ) ) : ?>
														<div class="gca-flag-tags">
															<?php foreach ( $child_tags as $tag ) : ?>
																<span class="gca-flag-tag"><?php echo esc_html( $tag ); ?></span>
															<?php endforeach; ?>
														</div>
													<?php endif; ?>
													<div class="gca-flag-id">gca_flag_enabled( '<?php echo esc_html( $child_id ); ?>' )</div>
												</div>
											</td>
											<td style="text-align: center;">
												<div class="gca-child-inner" style="display:flex;align-items:center;justify-content:center;">
													<label class="gca-toggle" aria-label="Toggle <?php echo esc_attr( $child_flag['label'] ); ?>">
														<input
															type="checkbox"
															name="gca_flags[<?php echo esc_attr( $child_id ); ?>]"
															<?php checked( self::is_enabled( $child_id ) ); ?>
														>
														<span class="gca-toggle-slider"></span>
													</label>
												</div>
											</td>
										</tr>
									<?php endforeach;
								endif;

							endforeach; ?>
						</tbody>
					</table>

					<div class="gca-flags-submit">
						<?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?>
					</div>
				</form>

				<script>
				(function () {
					var searchInput    = document.getElementById('gca-flag-search');
					var noResults      = document.getElementById('gca-flags-no-results');
					var filterBtn      = document.getElementById('gca-tag-filter-btn');
					var filterDropdown = document.getElementById('gca-tag-filter-dropdown');
					var filterCount    = document.getElementById('gca-tag-filter-count');
					var clearBtn       = document.getElementById('gca-tag-filter-clear');
					var filterWrap     = document.getElementById('gca-tag-filter');
					var toggleAllBtn   = document.getElementById('gca-toggle-all-btn');
					var selectedTags   = [];

					function applyFilters() {
						var query   = searchInput ? searchInput.value.toLowerCase().trim() : '';
						var rows    = document.querySelectorAll('.gca-flags-table tbody tr');
						var visible = 0;

						rows.forEach(function (row) {
							var textMatch = !query || row.textContent.toLowerCase().indexOf(query) !== -1;
							var tagsAttr  = row.getAttribute('data-tags') || '';
							var rowTags   = tagsAttr ? tagsAttr.split(',') : [];
							var tagMatch  = selectedTags.length === 0 ||
								selectedTags.some(function (t) { return rowTags.indexOf(t) !== -1; });
							var match = textMatch && tagMatch;
							row.style.display = match ? '' : 'none';
							if (match) visible++;
						});

						noResults.style.display = visible === 0 ? 'block' : 'none';
					}

					if (searchInput) {
						searchInput.addEventListener('input', applyFilters);
					}

					function updateToggleAllBtn() {
						if (!toggleAllBtn) return;
						var checkboxes = document.querySelectorAll('.gca-flags-table tbody input[type="checkbox"]');
						var allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(function (cb) { return cb.checked; });
						toggleAllBtn.textContent = allChecked ? 'Disable All' : 'Enable All';
					}

					if (toggleAllBtn) {
						toggleAllBtn.addEventListener('click', function () {
							var checkboxes = document.querySelectorAll('.gca-flags-table tbody input[type="checkbox"]');
							var allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(function (cb) { return cb.checked; });
							checkboxes.forEach(function (cb) { cb.checked = !allChecked; });
							updateToggleAllBtn();
							syncAllChildRows();
						});

						document.querySelectorAll('.gca-flags-table tbody input[type="checkbox"]').forEach(function (cb) {
							cb.addEventListener('change', updateToggleAllBtn);
						});

						updateToggleAllBtn();
					}

					// Parent toggle → show/hide child rows
					function syncChildRows(parentId, enabled) {
						document.querySelectorAll('[data-child-of="' + parentId + '"]').forEach(function (row) {
							if (enabled) {
								row.classList.remove('gca-flag-child-hidden');
							} else {
								row.classList.add('gca-flag-child-hidden');
							}
						});
					}

					function syncAllChildRows() {
						document.querySelectorAll('[data-parent-toggle]').forEach(function (cb) {
							syncChildRows(cb.getAttribute('data-parent-toggle'), cb.checked);
						});
					}

					document.querySelectorAll('[data-parent-toggle]').forEach(function (cb) {
						cb.addEventListener('change', function () {
							syncChildRows(cb.getAttribute('data-parent-toggle'), cb.checked);
						});
					});

					if (filterBtn && filterDropdown) {
						filterBtn.addEventListener('click', function (e) {
							e.stopPropagation();
							var isOpen = filterDropdown.style.display !== 'none';
							filterDropdown.style.display = isOpen ? 'none' : 'block';
							filterBtn.setAttribute('aria-expanded', String(!isOpen));
						});

						document.addEventListener('click', function (e) {
							if (filterWrap && !filterWrap.contains(e.target)) {
								filterDropdown.style.display = 'none';
								filterBtn.setAttribute('aria-expanded', 'false');
							}
						});

						document.querySelectorAll('.gca-tag-checkbox').forEach(function (cb) {
							cb.addEventListener('change', function () {
								selectedTags = Array.from(
									document.querySelectorAll('.gca-tag-checkbox:checked')
								).map(function (el) { return el.value; });

								if (filterCount) {
									if (selectedTags.length > 0) {
										filterCount.textContent = selectedTags.length;
										filterCount.style.display = 'inline';
									} else {
										filterCount.style.display = 'none';
									}
								}
								applyFilters();
							});
						});

						if (clearBtn) {
							clearBtn.addEventListener('click', function () {
								document.querySelectorAll('.gca-tag-checkbox').forEach(function (cb) {
									cb.checked = false;
								});
								selectedTags = [];
								if (filterCount) filterCount.style.display = 'none';
								applyFilters();
							});
						}
					}
				})();
				</script>

			<?php endif; ?>
		</div>
		<?php
	}
}

// -------------------------------------------------------------------------
// Global helper functions
// -------------------------------------------------------------------------

/**
 * Register a feature flag.
 *
 * Typically called on 'init' or directly in functions.php before any flag
 * checks run.
 *
 * @param string $id   Unique slug, e.g. 'new-homepage-layout'
 * @param array  $args Optional. label, description, default.
 */
if ( ! function_exists( 'gca_register_feature_flag' ) ) {
	function gca_register_feature_flag( $id, $args = array() ) {
		GCA_Feature_Flags::register( $id, $args );
	}
}

/**
 * Check whether a feature flag is currently enabled.
 *
 * @param string $id Flag slug.
 * @return bool
 */
if ( ! function_exists( 'gca_flag_enabled' ) ) {
	function gca_flag_enabled( $id ) {
		return GCA_Feature_Flags::is_enabled( $id );
	}
}

GCA_Feature_Flags::init();
