<?php

function tcmp_ui_manager() {
	global $tcmp;

	$id = tcmp_isqs( 'id', 0 );
	if ( tcmp_sqs( 'action' ) === 'delete' && $id > 0 && wp_verify_nonce( tcmp_qs( 'tcmp_nonce' ), 'tcmp_delete' ) ) {
		$snippet = $tcmp->manager->get( $id );
		if ( $tcmp->manager->remove( $id ) ) {
			$tcmp->options->pushSuccessMessage( 'CodeDeleteNotice', $id, esc_html( $snippet['name'] ) );
		}
	} elseif ( '' != $id && 0 != $id ) {
		$snippet = $tcmp->manager->get( $id );
		if ( $tcmp->utils->is( 'action', 'toggle' ) && $id > 0 && wp_verify_nonce( tcmp_qs( 'tcmp_nonce' ), 'tcmp_toggle' ) ) {
			$snippet['active'] = ( 0 === $snippet['active'] ? 1 : 0 );
			$tcmp->manager->put( $snippet['id'], $snippet );
		}
		$tcmp->options->pushSuccessMessage( 'CodeUpdateNotice', $id, esc_html( $snippet['name'] ) );
	}

	$tcmp->manager->is_limit_reached( true );
	$tcmp->options->writeMessages();

	//controllo che faccio per essere retrocompatibile con la prima versione
	//dove non avevo un id e salvavo tutto con il con il nome quindi una stringa
	$snippets = $tcmp->manager->keys();
	foreach ( $snippets as $v ) {
		$snippet = $tcmp->manager->get( $v, false, true );
		if ( ! $snippet ) {
			$tcmp->manager->remove( $v );
		} elseif ( ! is_numeric( $v ) ) {
			$tcmp->manager->remove( $v );
			$tcmp->manager->put( '', $snippet );
		}
	}
	$snippets = $tcmp->manager->values();
	if ( count( $snippets ) > 0 ) { ?>
		<div style="float:left;">
			<form method="get" action="" style="margin:5px; float:left;">
				<input type="hidden" name="page" value="<?php echo TCMP_PLUGIN_SLUG; ?>" />
				<input type="hidden" name="tab" value="<?php echo TCMP_TAB_EDITOR; ?>" />
				<input type="submit" class="button-primary" value="<?php $tcmp->lang->P( 'Button.Add' ); ?>" />
			</form>
		</div>
		<div style="clear:both;"></div>

		<table class="widefat fixed" style="width:100%" id="tblSortable">
			<thead>
				<tr>
					<th style="width:30px;">#N</th>
					<th style="width:50px; text-align:center;"><?php $tcmp->lang->P( 'Active?' ); ?></th>
					<th><?php $tcmp->lang->P( 'Name' ); ?></th>
					<th><?php $tcmp->lang->P( 'Where?' ); ?></th>
					<th style="text-align:center;"><?php $tcmp->lang->P( 'Shortcode' ); ?></th>
					<th style="text-align:center;"><?php $tcmp->lang->P( 'Actions' ); ?></th>
				</tr>
			</thead>
			<tbody class="table-body">
			<?php
			$i = 1;
			foreach ( $snippets as $snippet ) {
				global $tcmp_allowed_html_tags;
				$bClass = ( ( $i % 2 ) === 1 ? 'odd' : 'even' );
				?>
				<tr class="<?php echo esc_attr( $bClass ); ?>" id="row_<?php echo esc_attr( $snippet['id'] ); ?>">
					<td>#<?php echo esc_html( $i++ ); ?></td>
					<td style="text-align:center;">
						<?php
						$color    = 'red';
						$text     = 'No';
						$question = 'QuestionActiveOn';
						if ( 1 == $snippet['active'] ) {
							$color    = 'green';
							$text     = 'Yes';
							$question = 'QuestionActiveOff';
						}
						$text = '<span style="font-weight:bold; color:' . $color . '">' . $tcmp->lang->L( $text ) . '</span>';
						?>
						<a onclick="return confirm('<?php echo esc_attr( $tcmp->lang->L( $question ) ); ?>');" href="<?php echo TCMP_TAB_MANAGER_URI; ?>&tcmp_nonce=<?php echo esc_attr( wp_create_nonce( 'tcmp_toggle' ) ); ?>&action=toggle&id=<?php echo esc_attr( $snippet['id'] ); ?>">
							<?php echo wp_kses( $text, $tcmp_allowed_html_tags ); ?>
						</a>
					</td>
					<td><?php echo esc_html( $snippet['name'] ); ?></td>
					<td>
						<?php
						if ( $tcmp->manager->is_mode_script( $snippet ) ) {
							if ( $tcmp->manager->is_page_everywhere( $snippet ) ) {
								$text = 'Everywhere';
							} else {
								$text = 'Specific Pages';
							}
						} else {
							$text = 'Conversion';
						}
						esc_html( $tcmp->lang->P( $text ) );
						?>
					</td>
					<td style="text-align:center;">
						<input type="text" style="width:110px; text-align:center;" value='[tcm id="<?php echo esc_html( $snippet['id'] ); ?>"]' readonly="readonly" class="tcmp-select-onfocus" />
					</td>
					<td style="text-align:center;">
						<input type="button" class="button button-secondary" value="<?php $tcmp->lang->P( 'Edit' ); ?>" onclick="location.href='<?php echo TCMP_TAB_EDITOR_URI; ?>&id=<?php echo esc_attr( $snippet['id'] ); ?>';"/>
						<input type="button" class="button button-secondary" value="<?php $tcmp->lang->P( 'Delete?' ); ?>" onclick="TCMP_btnDeleteClick(<?php echo esc_attr( $snippet['id'] ); ?>)"/>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php
		tcmp_notice_pro_features();
	} else {
		?>
		<h2><?php $tcmp->lang->P( 'EmptyTrackingList', TCMP_TAB_EDITOR_URI ); ?></h2>
		<?php
	}
}
