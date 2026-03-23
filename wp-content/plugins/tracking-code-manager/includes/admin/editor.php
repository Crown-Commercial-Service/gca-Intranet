<?php
function tcmp_notice_pro_features() {
	global $tcmp;
	?>
	<br/>
	<div class="message updated below-h2 iwp">
		<div style="height:10px;"></div>
		<?php
		$i = 1;
		while ( $tcmp->lang->H( 'Notice.ProHeader' . $i ) ) {
			$tcmp->lang->P( 'Notice.ProHeader' . $i );
			echo '<br/>';
			++$i;
		}
		$i = 1;
		?>
		<br/>
		<?php

		/*$options = array('public' => TRUE, '_builtin' => FALSE);
		$q=get_post_types($options, 'names');
		if(is_array($q) && count($q)>0) {
			sort($q);
			$q=implode(', ', $q);
			$q='(<b>'.$q.'</b>)';
		} else {
			$q='';
		}*/
		$q = '';
		while ( $tcmp->lang->H( 'Notice.ProFeature' . $i ) ) {
			?>
			<div style="clear:both; margin-top: 2px;"></div>
			<div style="float:left; vertical-align:middle; height:24px; margin-right:5px; margin-top:-5px;">
				<img src="<?php echo TCMP_PLUGIN_IMAGES_URI; ?>tick.png" />
			</div>
			<div style="float:left; vertical-align:middle; height:24px;">
				<?php $tcmp->lang->P( 'Notice.ProFeature' . $i, $q ); ?>
			</div>
			<?php
			++$i;
		}
		?>
		<div style="clear:both;"></div>
		<div style="height:10px;"></div>
		<div style="float:right;">
			<?php
			$url = TCMP_PAGE_PREMIUM . '?utm_source=free-users&utm_medium=wp-cta&utm_campaign=wp-plugin';
			?>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank">
				<b><?php esc_html( $tcmp->lang->P( 'Notice.ProCTA' ) ); ?></b>
			</a>
		</div>
		<div style="height:10px; clear:both;"></div>
	</div>
	<br/>
	<?php
}
function tcmp_ui_editor_check( $snippet ) {
	global $tcmp;

	$snippet['trackMode'] = intval( $snippet['trackMode'] );
	$snippet['trackPage'] = intval( $snippet['trackPage'] );

	$snippet['includeEverywhereActive'] = 0;
	if ( TCMP_TRACK_PAGE_ALL === $snippet['trackPage'] ) {
		$snippet['includeEverywhereActive'] = 1;
	}
	$snippet = $tcmp->manager->sanitize( $snippet['id'], $snippet );

	if ( '' === $snippet['name'] ) {
		$tcmp->options->pushErrorMessage( 'Please enter a unique name' );
	} else {
		$exist = $tcmp->manager->exists( $snippet['name'] );
		if ( $exist && $exist['id'] !== $snippet['id'] ) {
			//nonostante il tutto il nome deve essee univoco
			$tcmp->options->pushErrorMessage( 'You have entered a name that already exists. IDs are NOT case-sensitive' );
		}
	}
	if ( '' === $snippet['code'] ) {
		$tcmp->options->pushErrorMessage( 'Paste your HTML Tracking Code into the textarea' );
	}

	if ( TCMP_TRACK_MODE_CODE === $snippet['trackMode'] ) {

		$types = $tcmp->utils->query( TCMP_QUERY_POST_TYPES );
		if ( TCMP_TRACK_PAGE_SPECIFIC === $snippet['trackPage'] ) {
			foreach ( $types as $v ) {
				$include_active_key = 'includePostsOfType_' . $v['id'] . '_Active';
				$include_array_key  = 'includePostsOfType_' . $v['id'];
				$except_active_key  = 'exceptPostsOfType_' . $v['id'] . '_Active';
				$except_array_key   = 'exceptPostsOfType_' . $v['id'];

				if ( 1 === $snippet[ $include_active_key ] && 1 === $snippet[ $except_active_key ] ) {
					if ( in_array( -1, $snippet[ $include_array_key ] ) && in_array( -1, $snippet[ $except_array_key ] ) ) {
						$tcmp->options->pushErrorMessage( 'Error.IncludeExcludeAll', $v['name'] );
					}
				}
				if ( 1 === $snippet[ $include_active_key ] && 0 === count( $snippet[ $include_array_key ] ) ) {
					$tcmp->options->pushErrorMessage( 'Error.IncludeSelectAtLeastOne', $v['name'] );
				}
			}

			//second loop to respect the display order
			foreach ( $types as $v ) {
				$include_active_key = 'includePostsOfType_' . $v['id'] . '_Active';
				$include_array_key  = 'includePostsOfType_' . $v['id'];
				$except_active_key  = 'exceptPostsOfType_' . $v['id'] . '_Active';
				$except_array_key   = 'exceptPostsOfType_' . $v['id'];

				if ( 1 === $snippet[ $include_active_key ] && in_array( -1, $snippet[ $include_array_key ] ) ) {
					if ( 1 === $snippet[ $except_active_key ] && 0 === count( $snippet[ $except_array_key ] ) ) {
						$tcmp->options->pushErrorMessage( 'Error.ExcludeSelectAtLeastOne', $v['name'] );
					}
				}
			}
		} else {
			foreach ( $types as $v ) {
				$except_active_key = 'exceptPostsOfType_' . $v['id'] . '_Active';
				$except_array_key  = 'exceptPostsOfType_' . $v['id'];

				if ( isset( $snippet[ $except_active_key ] )
					&& 1 === $snippet[ $except_active_key ]
					&& 0 === count( $snippet[ $except_array_key ] ) ) {
					$tcmp->options->pushErrorMessage( 'Error.ExcludeSelectAtLeastOne', $v['name'] );
				}
			}
		}
	}
}
function tcmp_ui_editor() {
	global $tcmp;

	$tcmp->form->prefix = 'Editor';
	$id                 = tcmp_isqs( 'id', 0 );
	if ( 0 === $id && $tcmp->manager->is_limit_reached( false ) ) {
		$tcmp->utils->redirect( TCMP_TAB_MANAGER_URI );
	}

	$snippet = $tcmp->manager->get( $id, true );
	if ( wp_verify_nonce( tcmp_qs( 'tcmp_nonce' ), 'tcmp_nonce' ) ) {
		foreach ( $snippet as $k => $v ) {
			$snippet[ $k ] = tcmp_qs( $k );
			if ( is_string( $snippet[ $k ] ) ) {
				$snippet[ $k ] = stripslashes( $snippet[ $k ] );
			}
		}

		tcmp_ui_editor_check( $snippet );
		if ( ! $tcmp->options->hasErrorMessages() ) {
			$snippet = $tcmp->manager->put( $snippet['id'], $snippet );
			$id      = $snippet['id'];
			$tcmp->utils->redirect( TCMP_PAGE_MANAGER . '&id=' . $id );        }
	}
	$tcmp->options->writeMessages();

	$tcmp->form->form_starts();
	$tcmp->form->hidden( 'id', $snippet );
	$tcmp->form->hidden( 'order', $snippet );

	$tcmp->form->checkbox( 'active', $snippet );
	$tcmp->form->text( 'name', $snippet );
	$tcmp->form->editor( 'code', $snippet );

	$values = array( TCMP_POSITION_HEAD, TCMP_POSITION_BODY, TCMP_POSITION_FOOTER );
	$tcmp->form->dropdown( 'position', $snippet, $values, false );
	$values = array( TCMP_DEVICE_TYPE_ALL, TCMP_DEVICE_TYPE_DESKTOP, TCMP_DEVICE_TYPE_MOBILE, TCMP_DEVICE_TYPE_TABLET );
	$tcmp->form->dropdown( 'deviceType', $snippet, $values, true );

	$args = array( 'id' => 'box-track-mode' );
	$tcmp->form->div_starts( $args );
	{
		$tcmp->form->p( 'Where do you want to add this code?' );
		$tcmp->form->radio( 'trackMode', $snippet['trackMode'], TCMP_TRACK_MODE_CODE );
		$plugins = $tcmp->ecommerce->getActivePlugins();
	if ( 0 === count( $plugins ) ) {
		$plugins = array(
			'Ecommerce' => array(
				'name'    => 'Ecommerce',
				'id'      => TCMP_PLUGINS_NO_PLUGINS,
				'version' => '',
			),
		);
	}
		$tcmp->form->tag_new = true;
	foreach ( $plugins as $k => $v ) {
		$ecommerce = $v['name'];
		if ( isset( $v['version'] ) && '' !== $v['version'] ) {
			$ecommerce .= ' (v.' . $v['version'] . ')';
		}
		$args = array( 'label' => $tcmp->lang->L( 'Editor.trackMode_1', $ecommerce ) );
		$tcmp->form->radio( 'trackMode', $snippet['trackMode'], $v['id'], $args );
	}
		$tcmp->form->tag_new = false;

	}
	$tcmp->form->div_ends();

	$args = array( 'id' => 'box-track-conversion' );
	$tcmp->form->div_starts( $args );
	{
		$tcmp->form->p( 'ConversionProductQuestion' );
	?>
		<p style="font-style: italic;"><?php $tcmp->lang->P( 'Editor.PositionBlocked' ); ?></p>
		<?php
		foreach ( $plugins as $k => $v ) {
			$args = array(
				'id'    => 'box-track-conversion-' . $v['id'],
				'class' => 'box-track-conversion',
			);
			$tcmp->form->div_starts( $args );
			{
			if ( TCMP_PLUGINS_NO_PLUGINS === $v['id'] ) {
				$plugins   = $tcmp->ecommerce->getPlugins( false );
				$ecommerce = '';
				foreach ( $plugins as $k => $v ) {
					if ( '' !== $ecommerce ) {
						$ecommerce .= ', ';
					}
					$ecommerce .= $k;
				}
				$tcmp->options->pushErrorMessage( 'Editor.NoEcommerceFound', $ecommerce );
				$tcmp->options->writeMessages();
			} else {
				$post_type  = $tcmp->ecommerce->getCustomPostType( $v['id'] );
				$key_active = 'CTC_' . $v['id'] . '_Active';
				$label      = $tcmp->lang->L( 'Editor.EcommerceCheck', $v['name'], $v['version'] );

				if ( '' != $post_type ) {
					$args      = array(
						'post_type' => $post_type,
						'all'       => true,
					);
					$values    = $tcmp->utils->query( TCMP_QUERY_POSTS_OF_TYPE, $args );
					$key_array = 'CTC_' . $v['id'] . '_ProductsIds';
					if ( 0 === count( $snippet[ $key_array ] ) ) {
						//when enabled default selected -1
						$snippet[ $key_array ] = array( -1 );
					}

					$args               = array(
						'label' => $label,
						'class' => 'tcmp-select tcmLineTags',
					);
					$tcmp->form->labels = false;
					$tcmp->form->dropdown( $key_array, $snippet[ $key_array ], $values, true, $args );
					$tcmp->form->labels = true;
				} else {
					$args = array( 'label' => $label );
					$tcmp->form->checkbox( $key_active, $snippet[ $key_active ], 1, $args );
				}
			}
			}
			$tcmp->form->div_ends();

			$tcmp->form->br();
			$tcmp->form->i( 'ConversionDynamicFields' );
			$tcmp->form->br();
			$tcmp->form->br();
		}
		}
		$tcmp->form->div_ends();

		$args = array( 'id' => 'box-track-code' );
		$tcmp->form->div_starts( $args );
		{
		$tcmp->form->p( 'In which page do you want to insert this code?' );
		$tcmp->form->radio( 'trackPage', $snippet['trackPage'], TCMP_TRACK_PAGE_ALL );
		$tcmp->form->radio( 'trackPage', $snippet['trackPage'], TCMP_TRACK_PAGE_SPECIFIC );

		//, 'style'=>'margin-top:10px;'
		$args = array( 'id' => 'tcmp-include-div' );
		$tcmp->form->div_starts( $args );
		{
			$tcmp->form->p( 'Include tracking code in which pages?' );
			tcmp_form_options( 'include', $snippet );
		}
		$tcmp->form->div_ends();

		$args = array( 'id' => 'tcmp-except-div' );
		$tcmp->form->div_starts( $args );
		{
			$tcmp->form->p( 'Do you want to exclude some specific pages?' );
			tcmp_form_options( 'except', $snippet );
		}
		$tcmp->form->div_ends();
		}
		$tcmp->form->div_ends();

		$tcmp->form->nonce( 'tcmp_nonce', 'tcmp_nonce' );
		tcmp_notice_pro_features();
		$tcmp->form->submit( 'Save' );
		$tcmp->form->form_ends();
}

function tcmp_form_options( $prefix, $snippet ) {
	global $tcmp;

	$types = $tcmp->utils->query( TCMP_QUERY_POST_TYPES );
	foreach ( $types as $v ) {
		$args   = array(
			'post_type' => $v['id'],
			'all'       => true,
		);
		$values = $tcmp->utils->query( TCMP_QUERY_POSTS_OF_TYPE, $args );
		//$tcmp->form->premium=!in_array($v['name'], array('post', 'page'));

		$key_active = $prefix . 'PostsOfType_' . $v['id'] . '_Active';
		$key_array  = $prefix . 'PostsOfType_' . $v['id'];
		if ( 0 === $snippet[ $key_active ] && 0 === count( $snippet[ $key_array ] ) && 'except' != $prefix ) {
			//when enabled default selected -1
			$snippet[ $key_array ] = array( -1 );
		}
		$tcmp->form->check_select( $key_active, $key_array, $snippet, $values );
	}
}
