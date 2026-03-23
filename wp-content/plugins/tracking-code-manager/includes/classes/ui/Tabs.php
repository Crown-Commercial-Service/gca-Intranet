<?php
class TCMP_Tabs {
	private $tabs = array();

	function __construct() {
	}
	public function init() {
		global $tcmp;
		if ( $tcmp->utils->isAdminUser() ) {
			add_action( 'admin_menu', array( &$this, 'attach_menu' ) );
			add_filter( 'plugin_action_links', array( &$this, 'plugin_actions' ), 10, 2 );
			if ( $tcmp->utils->isPluginPage() ) {
				add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			}
		}
	}

	function attach_menu() {
		add_submenu_page(
			'options-general.php',
			TCMP_PLUGIN_NAME,
			TCMP_PLUGIN_NAME,
			'manage_options',
			TCMP_PLUGIN_SLUG,
			array( &$this, 'showTabPage' )
		);
	}
	function plugin_actions( $links, $file ) {
		global $tcmp;
		if ( TCMP_PLUGIN_SLUG . '/index.php' == $file ) {
			$settings   = array();
			$settings[] = "<a href='" . TCMP_TAB_MANAGER_URI . "'>" . $tcmp->lang->L( 'Settings' ) . '</a>';
			$settings[] = "<a href='" . TCMP_PAGE_PREMIUM . "'>" . $tcmp->lang->L( 'PREMIUM' ) . '</a>';
			$links      = array_merge( $settings, $links );
		}
		return $links;
	}
	function enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jQuery' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		$this->wpEnqueueStyle( 'assets/css/style.css' );
		$this->wpEnqueueStyle( 'assets/css/manager.css' );
		$this->wpEnqueueStyle( 'assets/deps/select2-4.0.13/select2.css' );
		$this->wpEnqueueScript( 'assets/deps/select2-4.0.13/select2.full.js' );
		$this->wpEnqueueScript( 'assets/deps/starrr/starrr.js' );

		$this->wpEnqueueScript( 'assets/js/library.js' );
		$this->wpEnqueueScript( 'assets/js/plugin.js' );

		$this->wpEnqueueScript( 'assets/js/editor.js' );
		$this->wpEnqueueScript( 'assets/js/manager.js' );
		wp_localize_script('TCMP__manager', 'ajax_vars', array ('nonce' => wp_create_nonce('change_order')));
		$this->wpEnqueueScript( 'assets/js/delete-confirm.js' );
		$this->wpEnqueueScript( 'assets/js/ace/ace.js' );

		$this->wpEnqueueStyle( 'assets/css/font-awesome.min.css' );

		global $tcmp;
		wp_add_inline_script( 'TCMP__delete-confirm', 'const delete_data = ' . json_encode( array(
			'confirm' => $tcmp->lang->L( 'Question.DeleteQuestion' ),
			'href' => TCMP_TAB_MANAGER_URI,
			'nonce' => wp_create_nonce( 'tcmp_delete' ),
		) ), 'before' );
	}
	function wpEnqueueStyle( $uri, $name = '' ) {
		if ( '' == $name ) {
			$name = explode( '/', $uri );
			$name = $name[ count( $name ) - 1 ];
			$dot  = strrpos( $name, '.' );
			if ( false != $dot ) {
				$name = substr( $name, 0, $dot );
			}
			$name = TCMP_PLUGIN_PREFIX . '_' . $name;
		}

		$v = '?v=' . TCMP_PLUGIN_VERSION;
		wp_enqueue_style( $name, TCMP_PLUGIN_URI . $uri . $v );
	}
	function wpEnqueueScript( $uri, $name = '', $version = false ) {
		if ( '' == $name ) {
			$name = explode( '/', $uri );
			$name = $name[ count( $name ) - 1 ];
			$dot  = strrpos( $name, '.' );
			if ( false != $dot ) {
				$name = substr( $name, 0, $dot );
			}
			$name = TCMP_PLUGIN_PREFIX . '_' . $name;
		}

		$v    = '?v=' . TCMP_PLUGIN_VERSION;
		$deps = array();
		wp_enqueue_script( $name, TCMP_PLUGIN_URI . $uri . $v, $deps, $version, false );
	}

	function showTabPage() {
		global $tcmp;

		$v = $tcmp->options->getShowWhatsNewSeenVersion();
		if ( TCMP_WHATSNEW_VERSION != $v ) {
			$tcmp->options->setShowWhatsNew( true );
		}

		$hwb = tcmp_isqs( 'hwb', '' );
		if ( '' != $hwb ) {
			$tcmp->options->setShowWhatsNew( false );
		}

		$id          = tcmp_isqs( 'id', 0 );
		$default_tab = TCMP_TAB_MANAGER;
		$tab         = tcmp_sqs( 'tab', $default_tab );

		if ( $tcmp->options->isShowWhatsNew() ) {
			$tab                              = TCMP_TAB_WHATS_NEW;
			$default_tab                      = $tab;
			$this->tabs[ TCMP_TAB_WHATS_NEW ] = $tcmp->lang->L( 'What\'s New' );
			//$this->tabs[TCMP_TAB_MANAGER]=$tcmp->lang->L('Start using the plugin!');
		} else {
			if ( $id > 0 || ! $tcmp->manager->is_limit_reached( false ) ) {
				$this->tabs[ TCMP_TAB_EDITOR ] = $tcmp->lang->L( $id > 0 && TCMP_TAB_EDITOR == $tab ? 'Edit Script' : 'Add New Script' );
			} elseif ( TCMP_TAB_EDITOR == $tab ) {
				$tab = TCMP_TAB_MANAGER;
			}

			$this->tabs[ TCMP_TAB_MANAGER ]       = $tcmp->lang->L( 'Manager' );
			$this->tabs[ TCMP_TAB_ADMIN_OPTIONS ] = $tcmp->lang->L( 'Admin Options' );
			$this->tabs[ TCMP_TAB_SETTINGS ]      = $tcmp->lang->L( 'Settings' );
			$this->tabs[ TCMP_TAB_DOCS ]          = $tcmp->lang->L( 'Docs & FAQ' );
		}

		?>

		<div class="wrap" style="margin: 5px;">
			<?php
			$this->showTabs( $default_tab );
			$header = '';
			switch ( $tab ) {
				case TCMP_TAB_EDITOR:
					$header = ( $id > 0 ? 'Edit' : 'Add' );
					break;
				case TCMP_TAB_WHATS_NEW:
					$header = '';
					break;
				case TCMP_TAB_MANAGER:
					$header = 'Manager';
					break;
				case TCMP_TAB_ADMIN_OPTIONS:
					$header = 'Admin Options';
					break;
				case TCMP_TAB_SETTINGS:
					$header = 'Settings';
					break;
			}

			if ( $tcmp->lang->H( $header . 'Title' ) ) {
				?>
				<h2><?php $tcmp->lang->P( $header . 'Title', TCMP_PLUGIN_VERSION ); ?></h2>
				<?php if ( $tcmp->lang->H( $header . 'Subtitle' ) ) { ?>
					<div><?php $tcmp->lang->P( $header . 'Subtitle' ); ?></div>
				<?php } ?>
				<br/>
				<?php
			}

			tcmp_ui_first_time();
			?>
			<div style="float:left; margin:5px;">
				<?php
				$styles   = array();
				$styles[] = 'float:left';
				$styles[] = 'margin-right:20px';
				if ( TCMP_TAB_WHATS_NEW != $tab ) {
					$styles[] = 'max-width:750px';
				}
				$styles = implode( '; ', $styles );
				?>
				<div id="tcmp-page" style="<?php echo esc_attr( $styles ); ?>">
					<?php
					if (is_multisite())
					{
						if (!current_user_can('unfiltered_html'))
						{
							// Only the Super-Admin role has unfiltered_html by default
							echo '<div class="tcmp-box-error">';
							echo '<p>For a multisite only, changing plugin settings requires the unfiltered_html capability.</p>';
							echo '</div>';
							die;
						}
					}
					switch ( $tab ) {
						case TCMP_TAB_WHATS_NEW:
							tcmp_ui_whats_new();
							break;
						case TCMP_TAB_EDITOR:
							tcmp_ui_editor();
							break;
						case TCMP_TAB_MANAGER:
							tcmp_ui_manager();
							break;
						case TCMP_TAB_ADMIN_OPTIONS:
							tcmp_ui_admin_options();
							break;
						case TCMP_TAB_SETTINGS:
							tcmp_ui_track();
							tcmp_ui_settings();
							break;
					}
					?>
				</div>
				<?php if ( TCMP_TAB_WHATS_NEW != $tab ) { ?>
					<div id="tcmp-sidebar" style="float:left; max-width: 250px;">
						<?php
						$count   = $this->getPluginsCount();
						$plugins = array();
						while ( count( $plugins ) < 2 ) {
							$id = rand( 1, $count );
							if ( ! isset( $plugins[ $id ] ) ) {
								$plugins[ $id ] = $id;
							}
						}

						$this->drawContactUsWidget();
						foreach ( $plugins as $id ) {
							$this->drawPluginWidget( $id );
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>
		<div style="clear:both"></div>
		<?php
	}
	function getPluginsCount() {
		global $tcmp;
		$index = 1;
		while ( $tcmp->lang->H( 'Plugin' . $index . '.Name' ) ) {
			$index++;
		}
		return $index - 1;
	}
	function drawPluginWidget( $id ) {
		global $tcmp;
		?>
		<div class="tcmp-plugin-widget">
			<b><?php $tcmp->lang->P( 'Plugin' . $id . '.Name' ); ?></b>
			<br>
			<i><?php $tcmp->lang->P( 'Plugin' . $id . '.Subtitle' ); ?></i>
			<br>
			<ul style="list-style: circle;">
				<?php
				$index = 1;
				while ( $tcmp->lang->H( 'Plugin' . $id . '.Feature' . $index ) ) {
					?>
					<li><?php $tcmp->lang->P( 'Plugin' . $id . '.Feature' . $index ); ?></li>
					<?php
					$index++;
				}
				?>
			</ul>
			<a style="float:right;" class="button-primary" href="<?php $tcmp->lang->P( 'Plugin' . $id . '.Permalink' ); ?>" target="_blank">
				<?php $tcmp->lang->P( 'PluginCTA' ); ?>
			</a>
			<div style="clear:both"></div>
		</div>
		<br>
		<?php
	}
	function drawContactUsWidget() {
		global $tcmp;
		?>
		<b><?php $tcmp->lang->P( 'Sidebar.Title' ); ?></b>
		<ul style="list-style: circle;">
			<?php
			$index = 1;
			while ( $tcmp->lang->H( 'Sidebar' . $index . '.Name' ) ) {
				?>
				<li>
					<a href="<?php $tcmp->lang->P( 'Sidebar' . $index . '.Url' ); ?>" target="_blank">
						<?php $tcmp->lang->P( 'Sidebar' . $index . '.Name' ); ?>
					</a>
				</li>
				<?php
				$index++;
			}
			?>
		</ul>
		<?php
	}
	function showTabs( $default_tab ) {
		global $tcmp;
		$tab = $tcmp->check->of( 'tab', $default_tab );
		if ( $tcmp->options->isShowWhatsNew() ) {
			$tab = TCMP_TAB_WHATS_NEW;
		}

		?>
		<h2 class="nav-tab-wrapper" style="float:left; width:97%;">
			<?php

			foreach ( $this->tabs as $k => $v ) {
				$active = ( $tab == $k ? 'nav-tab-active' : '' );
				$style  = '';
				$target = '_self';
				if ( $tcmp->options->isShowWhatsNew() && TCMP_TAB_MANAGER == $k ) {
					$active = '';
					$style  = 'background-color:#F2E49B';
				}
				if ( TCMP_TAB_DOCS == $k ) {
					$target = '_blank';
					$style  = 'background-color:#F2E49B';
					?>
					<a href="<?php echo esc_url(TCMP_TAB_DOCS_URI) ?>" class="nav-tab <?php echo esc_attr( $active ); ?>" style="float:left; margin-left:10px;background-color:#F2E49B;" target="_blank">
					<?php echo esc_attr( $v ); ?>
					</a>
					<?php
				} else {
					?>
					<a style="float:left; margin-left:10px; <?php echo esc_attr( $style ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>" target="<?php echo esc_attr( $target ); ?>" href="?page=<?php echo TCMP_PLUGIN_SLUG; ?>&tab=<?php echo esc_attr( $k ); ?>"><?php echo esc_attr( $v ); ?></a>
					<?php
				}
			}
			?>
			<div style="float:right; display:none;" id="rate-box">
				<span style="font-weight:700; font-size:13px; color:#555;"><?php $tcmp->lang->P( 'Rate us' ); ?></span>
				<div id="tcmp-rate" class="starrr" data-connected-input="tcmp-rate-rank"></div>
				<input type="hidden" id="tcmp-rate-rank" name="tcmp-rate-rank" value="5" />
				<?php $tcmp->utils->twitter( 'data443risk' ); ?>
			</div>
		</h2>
		<div style="clear:both;"></div>
		<?php
	}
}

