<?php
function tcmp_ui_metabox( $post ) {
	global $tcmp;
	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'tcmp_meta_box', 'tcmp_meta_box_nonce' );

	$args = array(
		'metabox' => true,
		'field'   => 'id',
	);
	$ids  = $tcmp->manager->get_codes( -1, $post, $args );

	$all_ids   = array();
	$snippets = $tcmp->manager->values();
	$post_type = $post->post_type;
	foreach ( $snippets as $snippet ) {
		if ( TCMP_TRACK_MODE_CODE == $snippet['trackMode'] ) {
			if ( 0 != $snippet['active'] ) {
				if ( 0 == $snippet[ 'exceptPostsOfType_' . $post_type . '_Active' ]
					|| ! in_array( -1, $snippet[ 'exceptPostsOfType_' . $post_type ] ) ) {
					$all_ids[] = $snippet['id'];
				}
			}
		}
	}
	?>
	<div>
		<?php $tcmp->lang->P( 'Select existing Tracking Code' ); ?>..
	</div>
	<input type="hidden" name="tcmp_all_ids" value="<?php echo implode( ',', $all_ids ); ?>" />

	<div>
		<?php
		foreach ( $snippets as $snippet ) {
			$id = $snippet['id'];
			if ( TCMP_TRACK_MODE_CODE != $snippet['trackMode'] ) {
				continue;
			}

			$disabled = '';
			$checked  = '';

			if ( ! in_array( $id, $all_ids ) ) {
				$disabled = ' DISABLED';
			} elseif ( in_array( $id, $ids ) ) {
				$checked = ' CHECKED';
			}
			?>
			<input type="checkbox" class="tcmp-checkbox" name="tcmp_ids[]" value="<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?> />
			<?php echo esc_attr( $snippet['name'] ); ?>
			<a href="<?php echo TCMP_TAB_EDITOR_URI; ?>&id=<?php echo esc_attr( $id ); ?>" target="_blank">&nbsp;››</a>
			<br/>
		<?php } ?>
	</div>

	<br/>
	<div>
		<label for="tcmp_name"><?php $tcmp->lang->P( 'Or add a name' ); ?></label>
		<br/>
		<input type="text" name="tcmp_name" value="" style="width:100%"/>
	</div>
	<div>
		<label for="code"><?php $tcmp->lang->P( 'and paste HTML code here' ); ?></label>
		<br/>
		<textarea dir="ltr" dirname="ltr" name="tcmp_code" class="tcmp-textarea" style="width:100%; height:175px;"></textarea>
	</div>

	<div style="clear:both"></div>
	<i>Saving the post you'll save the tracking code</i>
	<?php
}

//si aggancia per creare i metabox in post e page
add_action( 'add_meta_boxes', 'tcmp_add_meta_box' );
function tcmp_add_meta_box() {
	global $tcmp;

	if (!current_user_can('unfiltered_html')) {
		return;
	}

	$free    = array( 'post', 'page' );
	$options = $tcmp->options->getMetaboxPostTypes();
	$screens = array();
	foreach ( $options as $k => $v ) {
		if ( intval( $v ) > 0 ) {
			$screens[] = $k;
		}
	}
	if ( count( $screens ) > 0 ) {
		foreach ( $screens as $screen ) {
			add_meta_box(
				'tcmp_sectionid',
				$tcmp->lang->L( 'Tracking Code by Data443' ),
				'tcmp_ui_metabox',
				$screen,
				'side'
			);
		}
	}
}
function tcmp_edit_snippet_array( $post, &$snippet, $prefix, $diff ) {
	global $tcmp;
	$post_id = $tcmp->utils->get( $post, 'ID', false );
	if ( false === $post_id ) {
		$post_id = $tcmp->utils->get( $post, 'post_ID' );
	}
	$post_type = $tcmp->utils->get( $post, 'post_type' );

	$key_array  = 'PostsOfType_' . $post_type;
	$key_active = $key_array . '_Active';
	if ( 0 == $snippet[ $prefix . $key_active ] ) {
		$snippet[ $prefix . $key_array ] = array();
	}
	$k = $prefix . $key_array;
	if ( $diff ) {
		$snippet[ $k ] = array_diff( $snippet[ $k ], array( $post_id ) );
	} else {
		$snippet[ $k ] = array_merge( $snippet[ $k ], array( $post_id ) );
		if ( in_array( -1, $snippet[ $k ] ) ) {
			$snippet[ $k ] = array( -1 );
		}
	}
	$snippet[ $k ]                   = array_unique( $snippet[ $k ] );
	$snippet[ $prefix . $key_active ] = ( count( $snippet[ $k ] ) > 0 ? 1 : 0 );
	return $snippet;
}
//si aggancia a quando un post viene salvato per salvare anche gli altri dati del metabox
add_action( 'save_post', 'tcmp_save_meta_box_data' );
function tcmp_save_meta_box_data( $post_id ) {
	global $tcmp;

	if (!current_user_can('unfiltered_html')) {
		return;
	}

	//in case of custom post type edit_ does not exist
	//if (!current_user_can('edit_'.$post_type, $post_id)) {
	//    return;
	//}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['tcmp_meta_box_nonce'] ) || ! isset( $_POST['post_type'] ) ) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['tcmp_meta_box_nonce'], 'tcmp_meta_box' ) ) {
		return;
	}

	$args = array(
		'metabox' => true,
		'field'   => 'id',
	);
	$ids  = $tcmp->manager->get_codes( -1, $_POST, $args );
	if ( ! is_array( $ids ) ) {
		$ids = array();
	}

	$all_ids = tcmp_qs( 'tcmp_all_ids' );
	if ( false === $all_ids || '' == $all_ids ) {
		$all_ids = array();
	} else {
		$all_ids = explode( ',', $all_ids );
	}
	$current_ids = tcmp_asqs( 'tcmp_ids', array() );
	if ( ! is_array( $current_ids ) ) {
		$current_ids = array();
	}

	if ( $ids != $current_ids ) {
		foreach ( $all_ids as $id ) {
			$id = intval( $id );
			if ( $id <= 0 ) {
				continue;
			}
			if ( in_array( $id, $current_ids ) && in_array( $id, $ids ) ) {
				//selected now and already selected
				continue;
			}
			if ( ! in_array( $id, $current_ids ) && ! in_array( $id, $ids ) ) {
				//not selected now and not already selected
				continue;
			}

			$snippet = $tcmp->manager->get( $id );
			if ( null == $snippet ) {
				continue;
			}

			$snippet = tcmp_edit_snippet_array( $_POST, $snippet, 'include', true );
			$snippet = tcmp_edit_snippet_array( $_POST, $snippet, 'except', true );
			if ( in_array( $id, $current_ids ) ) {
				$snippet = tcmp_edit_snippet_array( $_POST, $snippet, 'include', false );
			} else {
				$snippet = tcmp_edit_snippet_array( $_POST, $snippet, 'except', false );
			}
			$tcmp->manager->put( $id, $snippet );
		}
	}

	$name = tcmp_sqs( 'tcmp_name' );
	$code = stripslashes( tcmp_qs( 'tcmp_code' ) );
	if ( '' != $name && '' != $code ) {
		$post_type  = tcmp_sqs( 'post_type' );
		$key_array  = 'PostsOfType_' . $post_type;
		$key_active = $key_array . '_Active';

		$snippet                           = array(
			'active'    => 1,
			'name'      => $name,
			'code'      => $code,
			'trackPage' => TCMP_TRACK_PAGE_SPECIFIC,
			'trackMode' => TCMP_TRACK_MODE_CODE,
		);
		$snippet[ 'include' . $key_active ] = 1;
		$snippet[ 'include' . $key_array ]  = array( $post_id );
		$snippet                           = $tcmp->manager->put( '', $snippet );
		$tcmp->log->debug( 'NEW SNIPPET REGISTRED=%s', $snippet );
	}
}
