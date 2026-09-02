<?php

namespace ASENHA\Classes;

/**
 * Class for Navigation Menu Duplicator module.
 *
 * Adds a Duplicate Menu button on Appearance >> Menus to clone a menu and its items.
 *
 * Based on BCM Duplicate Menu by Bjorn Manintveld (GPLv2).
 *
 * @link https://wordpress.org/plugins/bcm-duplicate-menu/
 * @since 8.9.0
 */
class Navigation_Menu_Duplicator {

	/**
	 * Nonce action for duplicating a navigation menu.
	 *
	 * @var string
	 */
	const DUPLICATE_NONCE_ACTION = 'asenha_duplicate_nav_menu';

	/**
	 * Enqueue scripts on the nav menus screen when a menu is being edited.
	 *
	 * @link https://plugins.svn.wordpress.org/bcm-duplicate-menu/trunk/bcm-duplicate-menu.php
	 * @since 8.9.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'nav-menus.php' !== $hook_suffix ) {
			return;
		}

		$menu_id = $this->get_active_menu_id();

		if ( empty( $menu_id ) ) {
			return;
		}

		wp_enqueue_script(
			'asenha-navigation-menu-duplicator',
			ASENHA_URL . 'assets/js/navigation-menu-duplicator.js',
			array( 'jquery' ),
			ASENHA_VERSION,
			true
		);

		wp_localize_script(
			'asenha-navigation-menu-duplicator',
			'asenhaNavigationMenuDuplicator',
			array(
				'duplicateLabel' => __( 'Duplicate Menu', 'admin-site-enhancements' ),
				'duplicateUrl'   => wp_nonce_url(
					add_query_arg(
						array(
							'asenha_duplicate_menu' => $menu_id,
							'menu'                  => $menu_id,
						),
						admin_url( 'nav-menus.php' )
					),
					self::DUPLICATE_NONCE_ACTION,
					'asenha_duplicate_nonce'
				),
			)
		);
	}

	/**
	 * Handle duplicate menu requests from the nav menus screen.
	 *
	 * @link https://plugins.svn.wordpress.org/bcm-duplicate-menu/trunk/bcm-duplicate-menu.php
	 * @since 8.9.0
	 */
	public function maybe_handle_duplicate_request() {
		if ( ! isset( $_GET['asenha_duplicate_menu'] ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$nonce = isset( $_GET['asenha_duplicate_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['asenha_duplicate_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::DUPLICATE_NONCE_ACTION ) ) {
			return;
		}

		$menu_id = absint( $_GET['asenha_duplicate_menu'] );

		if ( empty( $menu_id ) ) {
			wp_safe_redirect( admin_url( 'nav-menus.php' ) );
			exit;
		}

		$source = wp_get_nav_menu_object( $menu_id );

		if ( ! $source ) {
			wp_safe_redirect( admin_url( 'nav-menus.php' ) );
			exit;
		}

		/* translators: appended to duplicated menu name */
		$new_menu_name = $source->name . ' ' . __( '(Copy)', 'admin-site-enhancements' );
		$new_menu_id   = $this->duplicate_nav_menu( $menu_id, $new_menu_name );

		if ( $new_menu_id ) {
			wp_safe_redirect( admin_url( 'nav-menus.php?action=edit&menu=' . $new_menu_id ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'nav-menus.php' ) );
		exit;
	}

	/**
	 * Get the ID of the menu currently being edited.
	 *
	 * @link https://plugins.svn.wordpress.org/bcm-duplicate-menu/trunk/bcm-duplicate-menu.php
	 * @since 8.9.0
	 *
	 * @return int Menu term ID, or 0 if none selected.
	 */
	private function get_active_menu_id() {
		if ( isset( $_GET['menu'] ) ) {
			return absint( $_GET['menu'] );
		}

		$recent_menu = get_user_option( 'nav_menu_recently_edited' );

		return absint( $recent_menu );
	}

	/**
	 * Duplicate a navigation menu and all of its items.
	 *
	 * @link https://plugins.svn.wordpress.org/bcm-duplicate-menu/trunk/bcm-duplicate-menu.php
	 * @since 8.9.0
	 *
	 * @param int    $menu_id   Source menu term ID.
	 * @param string $menu_name Name for the new menu.
	 * @return int|false New menu term ID on success, false on failure.
	 */
	private function duplicate_nav_menu( $menu_id, $menu_name ) {
		if ( empty( $menu_id ) || empty( $menu_name ) ) {
			return false;
		}

		$menu_id   = absint( $menu_id );
		$menu_name = sanitize_text_field( $menu_name );
		$source    = wp_get_nav_menu_object( $menu_id );

		if ( ! $source ) {
			return false;
		}

		$source_items = wp_get_nav_menu_items( $menu_id );

		if ( false === $source_items ) {
			$source_items = array();
		}

		$menu_exists = wp_get_nav_menu_object( $menu_name );

		if ( $menu_exists ) {
			/* translators: appended when a menu name already exists */
			$menu_name = $menu_name . ' ' . __( '(Copy)', 'admin-site-enhancements' );

			return $this->duplicate_nav_menu( $menu_id, $menu_name );
		}

		$new_id = wp_create_nav_menu( $menu_name );

		if ( is_wp_error( $new_id ) || ! $new_id ) {
			return false;
		}

		// Key is the original db ID, value is the new post ID.
		$rel = array();
		$i   = 1;

		foreach ( $source_items as $menu_item ) {
			$args = array(
				'post_title'     => $menu_item->title,
				'post_content'   => $menu_item->description,
				'post_excerpt'   => $menu_item->attr_title,
				'post_status'    => $menu_item->post_status,
				'post_type'      => 'nav_menu_item',
				'menu_order'     => $i,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'tax_input'      => array(
					'nav_menu' => array( $new_id ),
				),
				'meta_input'     => array(),
			);

			$meta = get_post_meta( $menu_item->ID );

			foreach ( $meta as $meta_key => $meta_values ) {
				if ( '_menu_item_menu_item_parent' === $meta_key ) {
					continue;
				}

				if ( ! empty( $meta_values ) ) {
					$args['meta_input'][ $meta_key ] = maybe_unserialize( $meta_values[0] );
				}
			}

			$parent_id = wp_insert_post( $args );

			if ( is_wp_error( $parent_id ) || ! $parent_id ) {
				continue;
			}

			$rel[ $menu_item->db_id ] = $parent_id;

			if ( $menu_item->menu_item_parent ) {
				$parent_meta = isset( $rel[ $menu_item->menu_item_parent ] ) ? $rel[ $menu_item->menu_item_parent ] : 0;
				update_post_meta( $parent_id, '_menu_item_menu_item_parent', $parent_meta );
			}

			$i++;
		}

		return $new_id;
	}
}
