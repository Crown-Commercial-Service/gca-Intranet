<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TCMP_Form {
	var $prefix      = 'Form';
	var $labels      = true;
	var $left_labels = true;
	var $newline;

	var $tags         = true;
	var $only_premium = true;
	var $left_tags    = false;
	var $premium      = false;
	var $tag_new      = false;

	public function __construct() {
	}

	//args can be a string or an associative array if you want
	private function get_text_args( $args, $defaults, $excludes = array() ) {
		$result = $args;

		if ( is_string( $excludes ) ) {
			$excludes = explode( ',', $excludes );
		}

		if ( is_array( $result ) && count( $result ) > 0 ) {
			$result = '';
			foreach ( $args as $k => $v ) {
				if ( 0 == count( $excludes ) || ! in_array( $k, $excludes, false ) ) {
					$result .= ' ' . $k . '="' . $v . '"';
				}
			}
		} elseif ( ! $args ) {
			$result = '';
		}
		if ( is_array( $defaults ) && count( $defaults ) > 0 ) {
			foreach ( $defaults as $k => $v ) {
				if ( 0 == count( $excludes ) || ! in_array( $k, $excludes, false ) ) {
					if ( false == stripos( $result, $k . '=' ) ) {
						$result .= ' ' . $k . '="' . $v . '"';
					}
				}
			}
		}
		return $result;
	}

	public function tag( $override_premium = false ) {
		if ( ! $this->tags || ! $this->tag_new ) {
			return;
		}
		?>
		<div style="float:left;" class="tcmp-tag tcmp-tag-free">NEW!</div>
		<?php
	}

	public function label( $name, $options = '' ) {
		global $tcmp;
		$defaults   = array( 'class' => '' );
		$other_text = $this->get_text_args( $options, $defaults, array( 'label', 'id' ) );

		$k = $this->prefix . '.' . $name;
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		if ( isset( $options['label'] ) && $options['label'] ) {
			$k = $options['label'];
		}

		$label = $tcmp->lang->L( $k );
		$for   = ( isset( $options['id'] ) ? $options['id'] : $name );

		//check if is a mandatory field by checking the .txt language file
		$k = $this->prefix . '.' . $name . '.check';
		if ( $tcmp->lang->H( $k ) ) {
			$label .= ' (*)';
		}

		$a_class = '';

		?>
		<label for="<?php echo esc_attr( $for ); ?>" <?php echo wp_kses_post( $other_text ); ?> >
			<?php
			if ( $this->left_tags ) {
				$this->tag();
			}
			?>
			<span style="float:left; margin-right:5px;" class="<?php echo esc_attr( $a_class ); ?>"><?php echo wp_kses_post( $label ); ?></span>
			<?php
			if ( ! $this->left_tags ) {
				$this->tag();
			}
			?>
		</label>
		<?php
	}

	public function left_input( $name, $options = '' ) {
		if ( ! $this->labels ) {
			return;
		}
		if ( $this->left_labels ) {
			$this->label( $name, $options );
		}

		if ( $this->newline ) {
			$this->newline();
		}
	}

	public function newline() {
		?>
		<div class="tcmp-form-newline"></div>
		<?php
	}

	public function right_input( $name, $args = '' ) {
		if ( ! $this->labels ) {
			return;
		}
		if ( ! $this->left_labels ) {
			$this->label( $name, $args );
		}
		$this->newline();
	}

	public function form_starts( $method = 'post', $action = '', $args = null ) {
		$defaults = array( 'class' => 'tcmp-form' );
		$other    = $this->get_text_args( $args, $defaults );
		?>
		<form method="<?php echo esc_attr( $method ); ?>" action="<?php echo esc_attr( $action ); ?>" <?php echo wp_kses( $other, array() ); ?> >
		<?php
	}

	public function form_ends() {
		?>
		</form>
		<?php
	}

	public function div_starts( $args = array() ) {
		$defaults = array();
		$other    = $this->get_text_args( $args, $defaults );
		?>
		<div <?php echo wp_kses( $other, array() ); ?>>
		<?php
	}
	public function div_ends() {
		?>
		</div>
		<div style="clear:both;"></div>
		<?php
	}

	public function p( $message, $v1 = null, $v2 = null, $v3 = null, $v4 = null, $v5 = null ) {
		global $tcmp;
		?>
		<p style="font-weight:bold;">
			<?php
			$tcmp->lang->P( $message, $v1, $v2, $v3, $v4, $v5 );
			if ( $tcmp->lang->H( $message . 'Subtitle' ) ) {
				?>
				<br/>
				<span style="font-weight:normal;">
					<?php $tcmp->lang->P( $message . 'Subtitle', $v1, $v2, $v3, $v4, $v5 ); ?>
				</span>
			<?php } ?>
		</p>
		<?php
	}
	public function i( $message, $v1 = null, $v2 = null, $v3 = null, $v4 = null, $v5 = null ) {
		global $tcmp;
		?>
		<i><?php $tcmp->lang->P( $message, $v1, $v2, $v3, $v4, $v5 ); ?></i>
		<?php
	}

	public function editor( $name, $value = '', $options = null ) {
		global $tcmp;

		$defaults = array(
			'editor'     => 'html',
			'theme'      => 'monokai',
			'ui-visible' => '',
			'height'     => 350,
			'width'      => 700,
		);
		$options  = $tcmp->utils->parseArgs( $options, $defaults );
		$value    = $tcmp->utils->get( $value, $name, $value );

		$args          = array(
			'class' => 'tcmp-label',
			'style' => 'width:auto;',
		);
		$this->newline = true;
		$this->left_input( $name, $args );

		$id = $name;
		switch ( $options['editor'] ) {
			case 'wp':
			case 'WordPress':
				$settings = array(
					'wpautop'          => true,
					'media_buttons'    => true,
					'drag_drop_upload' => false,
					'editor_height'    => $options['height'],
				);
				wp_editor( $value, $id, $settings );
				break;
			case 'html':
			case 'text':
			case 'javascript':
			case 'css':
				$ace  = 'ACE_' . $id;
				$text = $value;
				$text = str_replace( '<', '&lt;', $text );
				$text = str_replace( '>', '&gt;', $text );
				?>
				<div id="<?php echo esc_attr( $id ); ?>Ace" style="height:<?php echo esc_attr( $options['height'] ) + 50; ?>px; width: <?php echo esc_attr( $options['width'] ); ?>px;"><?php echo esc_html( $text ); ?></div>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" ui-visible="<?php echo esc_attr( $options['ui-visible'] ); ?>" style="display: none;"></textarea>

				<?php
				break;
		}
		$this->newline = false;
		$this->right_input( $name, $args );
	}

	public function textarea( $name, $value = '', $args = null ) {
		if ( is_array( $value ) && isset( $value[ $name ] ) ) {
			$value = $value[ $name ];
		}
		$defaults = array(
			'rows'  => 10,
			'class' => 'tcmp-textarea',
		);
		$other    = $this->get_text_args( $args, $defaults );

		$args          = array(
			'class' => 'tcmp-label',
			'style' => 'width:auto;',
		);
		$this->newline = true;
		$this->left_input( $name, $args );
		?>
			<textarea dir="ltr" dirname="ltr" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php echo wp_kses( $other, array() ); ?> ><?php echo wp_kses_post( $value ); ?></textarea>
		<?php
		$this->newline = false;
		$this->right_input( $name, $args );
	}

	public function number( $name, $value = '', $options = null ) {
		if ( ! $options ) {
			$options = array();
		}
		$options['type']         = 'number';
		$options['autocomplete'] = 'off';
		$options['style']        = 'width:100px;';
		if ( ! isset( $options['min'] ) ) {
			$options['min'] = 0;
		}

		return $this->text( $name, $value, $options );
	}

	public function text( $name, $value = '', $options = null ) {
		if ( is_array( $value ) && isset( $value[ $name ] ) ) {
			$value = $value[ $name ];
		}

		$type = 'text';
		if ( isset( $options['type'] ) ) {
			$type = $options['type'];
		}

		$defaults = array( 'class' => 'tcmp-' . $type );
		$other    = $this->get_text_args( $options, $defaults, 'type' );

		$args = array( 'class' => 'tcmp-label' );
		$this->left_input( $name, $args );
		?>
			<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo wp_kses( $other, array() ); ?> />
		<?php
		$this->right_input( $name, $args );
	}

	public function hidden( $name, $value = '', $args = null ) {
		if ( is_array( $value ) && isset( $value[ $name ] ) ) {
			$value = $value[ $name ];
		}
		$defaults = array();
		$other    = $this->get_text_args( $args, $defaults );
		?>
		<input type="hidden" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo wp_kses( $other, array() ); ?> />
		<?php
	}

	public function nonce( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		wp_nonce_field( $action, $name, $referer, $echo );
	}

	public function dropdown( $name, $value, $options, $multiple = false, $args = null ) {
		global $tcmp;
		if ( is_array( $value ) && isset( $value[ $name ] ) ) {
			$value = $value[ $name ];
		}
		$defaults = array( 'class' => 'tcmp-select tcmTags tcmp-dropdown' );
		$other    = $this->get_text_args( $args, $defaults );

		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}
		if ( is_string( $options ) ) {
			$options = explode( ',', $options );
		}
		if ( is_array( $options ) && count( $options ) > 0 ) {
			if ( ! isset( $options[0]['id'] ) ) {
				//this is a normal array so I use the values for "id" field and the "name" into the txt file
				$temp = array();
				foreach ( $options as $v ) {
					$temp[] = array(
						'id'   => $v,
						'name' => $tcmp->lang->L( $this->prefix . '.' . $name . '.' . $v ),
					);
				}
				$options = $temp;
			}
		}

		echo '<div id="' . esc_attr( $name ) . '-box">';
		$args = array( 'class' => 'tcmp-label' );
		$this->left_input( $name, $args );
		?>
			<select id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?><?php echo ( $multiple ? '[]' : '' ); ?>" <?php echo ( $multiple ? 'multiple' : '' ); ?> <?php echo wp_kses( $other, array() ); ?> >
				<?php
				foreach ( $options as $v ) {
					$selected = '';
					if ( in_array( $v['id'], $value, false ) ) {
						$selected = ' selected="selected"';
					}
					?>
					<option value="<?php echo esc_attr( $v['id'] ); ?>" <?php echo wp_kses( $selected, array() ); ?>><?php echo esc_html( $v['name'] ); ?></option>
				<?php } ?>
			</select>
		<?php
		$this->right_input( $name, $args );
		echo '</div>';
	}

	public function br() {
		?>
		<br/>
		<?php
	}

	public function submit( $value = '', $args = null ) {
		global $tcmp;
		$defaults = array();
		$other    = $this->get_text_args( $args, $defaults );
		if ( '' == $value ) {
			$value = 'Send';
		}
		$this->newline();
		?>
			<input type="submit" class="button-primary tcmp-button tcmp-submit" value="<?php $tcmp->lang->P( $value ); ?>" <?php echo wp_kses( $other, array() ); ?>/>
		<?php
	}

	public function delete( $id, $action = 'delete', $args = null ) {
		global $tcmp;
		$defaults = array();
		$other    = $this->get_text_args( $args, $defaults );
		?>
			<input type="button" class="button tcmp-button" value="<?php $tcmp->lang->P( 'Delete?' ); ?>" onclick="if (confirm('<?php $tcmp->lang->P( 'Question.DeleteQuestion' ); ?>') ) window.location='<?php echo TCMP_TAB_MANAGER_URI; ?>&action=<?php echo esc_attr( $action ); ?>&id=<?php echo esc_attr( $id ); ?>&amp;tcmp_nonce=<?php echo esc_attr( wp_create_nonce( 'tcmp_delete' ) ); ?>';" <?php echo wp_kses( $other, array() ); ?> />
			&nbsp;
		<?php
	}

	public function radio( $name, $current = 1, $value = 1, $options = null ) {
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		$options['radio'] = true;
		$options['id']    = $name . '_' . $value;
		return $this->checkbox( $name, $current, $value, $options );
	}
	public function checkbox( $name, $current = 1, $value = 1, $options = null ) {
		global $tcmp;
		if ( is_array( $current ) && isset( $current[ $name ] ) ) {
			$current = $current[ $name ];
		}

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$label = $name;
		$type  = 'checkbox';
		if ( isset( $options['radio'] ) && $options['radio'] ) {
			$type   = 'radio';
			$label .= '_' . $value;
		}

		$defaults          = array(
			'class' => 'tcmp-checkbox',
			'style' => 'margin:0px; margin-right:4px;',
			'id'    => $name,
		);
		$other             = $this->get_text_args( $options, $defaults, array( 'radio', 'label' ) );
		$prev              = $this->left_labels;
		$this->left_labels = false;

		$label   = ( isset( $options['label'] ) ? $options['label'] : $this->prefix . '.' . $label );
		$id      = ( isset( $options['id'] ) ? $options['id'] : $name );
		$options = array(
			'class' => '',
			'style' => 'margin-top:-1px;',
			'label' => $label,
			'id'    => $id,
		);
		$this->left_input( $name, $options );
		?>
			<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo( $current == $value ? 'checked="checked"' : '' ); ?> <?php echo wp_kses( $other, array() ); ?> >
		<?php
		$this->right_input( $name, $options );
		$this->left_labels = $prev;
	}

	public function check_text( $name_active, $name_text, $value ) {
		global $tcmp;

		$args = array(
			'class'           => 'tcmp-hideShow tcmp-checkbox',
			'tcmp-hideIfTrue' => 'false',
			'tcmp-hideShow'   => $name_text . 'Text',
		);
		$this->checkbox( $name_active, $value, 1, $args );
		if ( $this->premium ) {
			return;
		}
		?>
		<div id="<?php echo esc_attr( $name_text ); ?>Text" style="float:left;">
			<?php
			$prev         = $this->labels;
			$this->labels = false;
			$args         = array();
			$this->text( $name_text, $value, $args );
			$this->labels = $prev;
			?>
		</div>
		<?php
	}

	//create a checkbox with a left select visible only when the checkbox is selected
	public function check_select( $name_active, $name_array, $value, $values, $options = null ) {
		global $tcmp;
		?>
		<div id="<?php echo esc_attr( $name_array ); ?>Box" style="float:left;">
			<?php
			$defaults = array(
				'class'           => 'tcmp-hideShow tcmp-checkbox',
				'tcmp-hideIfTrue' => 'false',
				'tcmp-hideShow'   => $name_array . 'Tags',
			);
			$options  = $tcmp->utils->parseArgs( $options, $defaults );
			$this->checkbox( $name_active, $value, 1, $options );
			/*if(!$this->premium || $tcmp->License->hasPremium()) { ?>*/
			if ( true ) {
				?>
				<div id="<?php echo esc_attr( $name_array ); ?>Tags" style="float:left;">
					<?php
					$prev         = $this->labels;
					$this->labels = false;
					$options      = array( 'class' => 'tcmp-select tcmLineTags' );
					$this->dropdown( $name_array, $value, $values, true, $options );
					$this->labels = $prev;
					?>
				</div>
			<?php } ?>
		</div>
		<?php
		$this->newline();
	}
}
