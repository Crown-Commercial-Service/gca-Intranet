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
				<style>
					.gca-flags-table {
						width: 100%;
						border-collapse: collapse;
						background: #fff;
						box-shadow: 0 1px 1px rgba(0,0,0,.04);
						border: 1px solid #c3c4c7;
					}
					.gca-flags-table th {
						text-align: left;
						padding: 12px 16px;
						border-bottom: 2px solid #c3c4c7;
						font-size: 13px;
					}
					.gca-flags-table td {
						padding: 14px 16px;
						border-bottom: 1px solid #f0f0f1;
						vertical-align: middle;
					}
					.gca-flags-table tbody tr:last-child td {
						border-bottom: none;
					}
					.gca-flags-table tbody tr:hover td {
						background: #f6f7f7;
					}
					.gca-flag-id {
						font-family: monospace;
						font-size: 11px;
						color: #8c8f94;
						margin-top: 4px;
					}
					.gca-flag-desc {
						color: #50575e;
						font-size: 13px;
						margin-top: 3px;
					}
					/* Toggle switch */
					.gca-toggle {
						position: relative;
						display: inline-block;
						width: 46px;
						height: 26px;
						vertical-align: middle;
					}
					.gca-toggle input {
						opacity: 0;
						width: 0;
						height: 0;
					}
					.gca-toggle-slider {
						position: absolute;
						cursor: pointer;
						inset: 0;
						background-color: #c3c4c7;
						border-radius: 26px;
						transition: background-color 0.15s ease;
					}
					.gca-toggle-slider::before {
						content: "";
						position: absolute;
						height: 20px;
						width: 20px;
						left: 3px;
						bottom: 3px;
						background-color: #fff;
						border-radius: 50%;
						transition: transform 0.15s ease;
						box-shadow: 0 1px 3px rgba(0,0,0,.25);
					}
					.gca-toggle input:checked + .gca-toggle-slider {
						background-color: #2271b1;
					}
					.gca-toggle input:checked + .gca-toggle-slider::before {
						transform: translateX(20px);
					}
					.gca-toggle input:focus-visible + .gca-toggle-slider {
						outline: 2px solid #2271b1;
						outline-offset: 2px;
					}
					.gca-flags-submit {
						margin-top: 20px;
					}
					.gca-flags-search {
						margin-bottom: 16px;
					}
					.gca-flags-search input[type="search"] {
						width: 100%;
						max-width: 400px;
						padding: 6px 10px;
						font-size: 13px;
					}
					#gca-flags-no-results {
						padding: 20px 16px;
						color: #50575e;
						font-style: italic;
						display: none;
					}
					.gca-flag-tags {
						display: flex;
						flex-wrap: wrap;
						gap: 4px;
						margin-top: 5px;
					}
					.gca-flag-tag {
						display: inline-block;
						padding: 1px 8px;
						background: #e5f0fb;
						color: #2271b1;
						border-radius: 10px;
						font-size: 11px;
						font-weight: 500;
					}
					.gca-flags-controls {
						display: flex;
						align-items: center;
						gap: 12px;
						margin-bottom: 16px;
					}
					.gca-flags-controls .gca-flags-search {
						margin-bottom: 0;
					}
					.gca-tag-filter {
						position: relative;
					}
					.gca-tag-filter-btn {
						padding: 6px 10px;
						font-size: 13px;
						cursor: pointer;
						background: #fff;
						border: 1px solid #c3c4c7;
						border-radius: 3px;
						white-space: nowrap;
						line-height: 1.4;
					}
					.gca-tag-filter-btn:hover {
						border-color: #2271b1;
					}
					.gca-tag-filter-count {
						display: inline-block;
						background: #2271b1;
						color: #fff;
						border-radius: 10px;
						padding: 0 6px;
						font-size: 11px;
						margin-left: 4px;
						vertical-align: middle;
					}
					.gca-tag-filter-dropdown {
						position: absolute;
						top: calc(100% + 4px);
						left: 0;
						z-index: 100;
						background: #fff;
						border: 1px solid #c3c4c7;
						border-radius: 3px;
						box-shadow: 0 2px 6px rgba(0,0,0,.15);
						min-width: 160px;
						padding: 6px 0;
					}
					.gca-tag-filter-dropdown label {
						display: flex;
						align-items: center;
						gap: 7px;
						padding: 5px 12px;
						cursor: pointer;
						font-size: 13px;
						margin: 0;
					}
					.gca-tag-filter-dropdown label:hover {
						background: #f6f7f7;
					}
					.gca-tag-filter-clear {
						display: block;
						width: 100%;
						border: none;
						border-top: 1px solid #f0f0f1;
						background: none;
						padding: 6px 12px;
						text-align: left;
						font-size: 12px;
						color: #646970;
						cursor: pointer;
						margin-top: 4px;
					}
					.gca-tag-filter-clear:hover {
						color: #d63638;
					}
				</style>

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

				<script>
				(function () {
					var searchInput    = document.getElementById('gca-flag-search');
					var noResults      = document.getElementById('gca-flags-no-results');
					var filterBtn      = document.getElementById('gca-tag-filter-btn');
					var filterDropdown = document.getElementById('gca-tag-filter-dropdown');
					var filterCount    = document.getElementById('gca-tag-filter-count');
					var clearBtn       = document.getElementById('gca-tag-filter-clear');
					var filterWrap     = document.getElementById('gca-tag-filter');
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

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<input type="hidden" name="action" value="gca_feature_flags_save">

					<table class="gca-flags-table">
						<thead>
							<tr>
								<th>Feature</th>
								<th style="width: 80px; text-align: center;">Enabled</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $flags as $id => $flag ) : ?>
								<?php $tags = ! empty( $flag['tags'] ) ? (array) $flag['tags'] : array(); ?>
								<tr data-tags="<?php echo esc_attr( implode( ',', array_map( 'sanitize_key', $tags ) ) ); ?>">
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
											>
											<span class="gca-toggle-slider"></span>
										</label>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div class="gca-flags-submit">
						<?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?>
					</div>
				</form>
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
