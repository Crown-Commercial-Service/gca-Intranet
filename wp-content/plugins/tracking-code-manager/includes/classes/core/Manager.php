<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TCMP_Manager {
	public function __construct() {
	}
	public function init() {
		add_action( 'wp_ajax_TCMP_changeOrder', array( &$this, 'change_order' ) );
	}
	public function is_limit_reached( $notice = true ) {
		global $tcmp;
		$cnt    = $this->codes_count();
		$result = ( $cnt >= TCMP_SNIPPETS_LIMIT );
		if ( $result && $notice ) {
			$tcmp->options->pushWarningMessage( 'SnippetsLimitReached', TCMP_SNIPPETS_LIMIT, TCMP_PAGE_PREMIUM );
		} elseif ( $notice && $cnt > 0 ) {
			$tcmp->options->pushSuccessMessage( 'SnippetsLimitNotice', $cnt, TCMP_SNIPPETS_LIMIT, TCMP_PAGE_PREMIUM );
		}
		return $result;
	}
	public function change_order() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'change_order' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! current_user_can( 'edit_plugins' ) ) {
			return;
		}

		if ( ! isset( $_POST['order'] ) ) {
			return;
		}

		$data = array();
		parse_str( tcmp_sqs( 'order' ), $data );

		if ( isset( $data['row'] ) ) {
			$snippets = $this->values();
			foreach ( $snippets as $id => $v ) {
				$v['order']      = 0;
				$snippets[ $id ] = $v;
			}

			$index = 1;
			foreach ( $data['row'] as $order => $id ) {
				$v               = $snippets[ $id ];
				$v['order']      = $index;
				$snippets[ $id ] = $v;
				++$index;
			}

			foreach ( $snippets as $id => $v ) {
				$this->put( $id, $v );
			}
		}
		echo 'OK';
		wp_die();
	}

	public function match_device_type( $snippet ) {
		global $tcmp;
		$device_type = $tcmp->utils->get( $snippet, 'deviceType', false );
		$device_type = $tcmp->utils->to_array( $device_type );
		if ( false == $device_type || 0 == count( $device_type ) ) {
			return true;
		}

		$detect = new TCMP_Mobile_Detect();
		if ( $detect->isMobile() ) {
			$type = TCMP_DEVICE_TYPE_MOBILE;
		} elseif ( $detect->isTablet() ) {
			$type = TCMP_DEVICE_TYPE_TABLET;
		} else { //if(!$detect->isMobile() && !$detect->isTablet()) {
			$type = TCMP_DEVICE_TYPE_DESKTOP;
		}

		$result = false;
		if ( in_array( TCMP_DEVICE_TYPE_ALL, $device_type ) || in_array( $type, $device_type ) ) {
			$result = true;
		}
		return $result;
	}
	public function is_mode_script( $snippet ) {
		global $tcmp;
		$result = $tcmp->utils->iget( $snippet, 'trackMode', 0 );
		return ( 0 == $result );
	}
	public function is_mode_conversion( $snippet ) {
		global $tcmp;
		$result = $tcmp->utils->iget( $snippet, 'trackMode', 0 );
		return ( 0 != $result );
	}
	public function is_page_everywhere( $snippet ) {
		global $tcmp;
		if ( ! $this->is_mode_script( $snippet ) ) {
			return false;
		}

		$result = $tcmp->utils->iget( $snippet, 'trackPage', 0 );
		return ( TCMP_TRACK_PAGE_ALL == $result );
	}
	public function is_page_specific( $snippet ) {
		global $tcmp;
		if ( ! $this->is_mode_script( $snippet ) ) {
			return false;
		}

		$result = $tcmp->utils->iget( $snippet, 'trackPage', 0 );
		return ( TCMP_TRACK_PAGE_SPECIFIC == $result );
	}

	public function exists( $name ) {
		$snippets = $this->values();
		$result   = null;
		$name     = strtoupper( $name );
		if ( isset( $snippets[ $name ] ) ) {
			$result = $snippets[ $name ];
		}
		return $result;
	}

	//get a code snippet
	public function get( $id, $new = false ) {
		global $tcmp;

		$snippet = $tcmp->options->getSnippet( $id );
		if ( ! $snippet && $new ) {
			$snippet              = array();
			$snippet['active']    = 1;
			$snippet['trackMode'] = -1;
			$snippet['trackPage'] = -1;
		}

		$snippet = $this->sanitize( $id, $snippet );
		return $snippet;
	}

	public function sanitize( $id, $snippet ) {
		global $tcmp;
		if ( null == $snippet || ! is_array( $snippet ) ) {
			return null;
		}

		$page = 0;
		if ( isset( $snippet['includeEverywhereActive'] ) ) {
			$page = ( intval( 1 == $snippet['includeEverywhereActive'] ) ? 0 : 1 );
		}
		$defaults = array(
			'id'                      => $id,
			'active'                  => 0,
			'name'                    => '',
			'code'                    => '',
			'order'                   => 1000,
			'position'                => TCMP_POSITION_HEAD,
			'trackMode'               => TCMP_TRACK_MODE_CODE,
			'trackPage'               => $page,
			'includeEverywhereActive' => 0,
			'includeCategoriesActive' => 0,
			'includeCategories'       => array(),
			'includeTagsActive'       => 0,
			'includeTags'             => array(),
			'exceptCategoriesActive'  => 0,
			'exceptCategories'        => array(),
			'exceptTagsActive'        => 0,
			'exceptTags'              => array(),
			'deviceType'              => TCMP_DEVICE_TYPE_ALL,
		);

		$types = $tcmp->utils->query( TCMP_QUERY_POST_TYPES );
		foreach ( $types as $v ) {
			$defaults[ 'includePostsOfType_' . $v['id'] . '_Active' ] = 0;
			$defaults[ 'includePostsOfType_' . $v['id'] ]             = array();
			$defaults[ 'exceptPostsOfType_' . $v['id'] . '_Active' ]  = 0;
			$defaults[ 'exceptPostsOfType_' . $v['id'] ]              = array();
		}

		$types = $tcmp->utils->query( TCMP_QUERY_CONVERSION_PLUGINS );
		foreach ( $types as $v ) {
			//CP stands for ConversionTrackingCode
			//$defaults['CTC_'.$v['id'].'_Active']=0;
			$defaults[ 'CTC_' . $v['id'] . '_ProductsIds' ]   = array();
			$defaults[ 'CTC_' . $v['id'] . '_CategoriesIds' ] = array();
			$defaults[ 'CTC_' . $v['id'] . '_TagsIds' ]       = array();
		}
		$snippet = $tcmp->utils->parseArgs( $snippet, $defaults );

		foreach ( $snippet as $k => $v ) {
			if ( stripos( $k, 'active' ) != false ) {
				$snippet[ $k ] = intval( $v );
			} elseif ( is_array( $v ) ) {
				switch ( $k ) {
					/*
					case 'includePostsTypes':
					case 'excludePostsTypes':
						//keys are string and not number
						$result=$this->uarray($snippet, $k, FALSE);
						break;
					*/
					default:
						//keys are number
						$result = $this->uarray( $snippet, $k, true );
						break;
				}
			}
		}
		$snippet['name']     = sanitize_text_field( $snippet['name'] );
		$snippet['code']     = trim( $snippet['code'] );
		$snippet['position'] = intval( $snippet['position'] );
		if ( '' == $snippet['trackMode'] ) {
			$snippet['trackMode'] = TCMP_TRACK_MODE_CODE;
		} else {
			$snippet['trackMode'] = intval( $snippet['trackMode'] );
		}
		if ( '' == $snippet['trackPage'] ) {
			$snippet['trackPage'] = $page;
		} else {
			$snippet['trackPage'] = intval( $snippet['trackPage'] );
		}

		$snippet['includeEverywhereActive'] = 0;
		if ( TCMP_TRACK_PAGE_ALL == $snippet['trackPage'] ) {
			$snippet['includeEverywhereActive'] = 1;
		}

		$code = strtolower( $snippet['code'] );
		$cnt  = substr_count( $code, '<iframe' ) + substr_count( $code, '<script' );
		if ( $cnt <= 0 ) {
			$cnt = 1;
		}
		$snippet['codes_count'] = $cnt;
		return $snippet;
	}
	private function uarray( $snippet, $key, $is_integer = true ) {
		$array = $snippet[ $key ];
		if ( ! is_array( $array ) ) {
			$array = explode( ',', $array );
		}

		if ( $is_integer ) {
			for ( $i = 0; $i < count( $array ); $i++ ) {
				if ( isset( $array[ $i ]) ) {
					$array[ $i ] = intval( $array[ $i ] );
				}
			}
		}

		$array           = array_unique( $array );
		$snippet[ $key ] = $array;
		return $snippet;
	}

	//add or update a snippet (html tracking code)
	public function put( $id, $snippet ) {
		global $tcmp;

		if ( '' == $id || intval( $id ) <= 0 ) {
			//if is a new code create a new unique id
			$id            = $this->get_last_id() + 1;
			$snippet['id'] = $id;
		}
		$snippet = $this->sanitize( $id, $snippet );
		$tcmp->options->setSnippet( $id, $snippet );

		$keys = $this->keys();
		if ( is_array( $keys ) && ! in_array( $id, $keys ) ) {
			$keys[] = $id;
			$this->keys( $keys );
		}
		return $snippet;
	}

	//remove the id snippet
	public function remove( $id ) {
		global $tcmp;
		$tcmp->options->remove_snippet( $id );
		$keys   = $this->keys();
		$result = false;
		if ( is_array( $keys ) && in_array( $id, $keys ) ) {
			$keys = array_diff( $keys, array( $id ) );
			$this->keys( $keys );
			$result = true;
		}
		return $result;
	}

	//verify if match with this snippet
	private function match_snippet( $post_id, $post_type, $categories_ids, $tags_ids, $prefix, $snippet ) {
		global $tcmp;
		if ( ! $this->match_device_type( $snippet ) ) {
			return false;
		}

		$include = false;
		$post_id = intval( $post_id );
		if ( $post_id > 0 ) {
			$what = $prefix . 'PostsOfType_' . $post_type;
			//echo '<textarea cols="100" rows="4">DEBUG:'.'Post Id = '.$post_id .' $what = '. $what .' What = '.$snippet[$what.'_Active'] .' Active = '.$snippet[$what.'_Active']. ' InAllArray = ' . $tcmp->utils->in_all_array( $post_id, $snippet[$what] ) . '</textarea>';
			if ( isset( $snippet[ $what . '_Active' ] ) && isset( $snippet[ $what ] ) && $snippet[ $what . '_Active' ] && $tcmp->utils->in_all_array( $post_id, $snippet[ $what ] ) ) {
				$tcmp->log->debug(
					'MATCH=%s SNIPPET=%s[%s] DUE TO POST=%s OF TYPE=%s IN [%s]',
					$prefix,
					$snippet['id'],
					$snippet['name'],
					$post_id,
					$post_type,
					$snippet[ $what ]
				);
				$include = true;
				//echo '<p>DEBUG: snippet matched</p>';
			}
		}

		return $include;
	}

	public function write_codes( $position ) {
		global $tcmp;

		$text = '';
		$position_text = '';
		switch ( $position ) {
			case TCMP_POSITION_HEAD:
				$position_text = 'HEAD';
				break;
			case TCMP_POSITION_BODY:
				$position_text = 'BODY';
				break;
			case TCMP_POSITION_FOOTER:
				$position_text = 'FOOTER';
				break;
			case TCMP_POSITION_CONVERSION:
				$position_text = 'CONVERSION';
				break;
		}

		$post  = $tcmp->options->getPostShown();
		$args  = array( 'field' => 'code' );
		$codes = $tcmp->manager->get_codes( $position, $post, $args );
		if ( is_array( $codes ) && count( $codes ) > 0 ) {
			$version = TCMP_PLUGIN_VERSION;
			$text = "\n<!--BEGIN: TRACKING CODE MANAGER (v$version) BY INTELLYWP.COM IN $position_text//-->";
			foreach ( $codes as $v ) {
				$text .= "\n$v";
			}
			$text .= "\n<!--END: https://wordpress.org/plugins/tracking-code-manager IN $position_text//-->";

			$purchase = $tcmp->options->getEcommercePurchase();
			if ( false != $purchase && intval( $tcmp->options->getLicenseSiteCount() ) > 0 ) {
				$text = $this->insert_dynamic_conversion_values( $purchase, $text );
			}
			echo $this->esc_js_code( $text );
		}
	}

	private function esc_js_code( $text ) {
		global $tcmp;
		global $tcmp_allowed_html_tags;

		if ( ! $tcmp->options->getSkipCodeSanitization() ) {
			$text = wp_kses( $text, $tcmp_allowed_html_tags );
		}
		$text = str_replace( '&lt;', '<', $text );
		$text = str_replace( '&gt;', '>', $text );
		$text = str_replace( '&amp;', '&', $text );
		$text = str_replace( '&quot;', '"', $text );
		$text = str_replace( '&#039;', "'", $text );
		return $text;
	}

	private function insert_dynamic_conversion_values( $purchase, $text ) {
		global $tcmp;
		$purchase->user_id = intval( $purchase->user_id );
		if ( $purchase->user_id > 0 ) {
			$user = get_user_by( 'id', $purchase->user_id );
			if ( ! is_null( $user ) && false != $user && get_class( $user ) == 'WP_User' ) {
				/* @var $user WP_User */
				$purchase->email    = $user->user_email;
				$purchase->fullname = $user->user_firstname;
				if ( '' != $user->user_lastname ) {
					$purchase->fullname .= ' ' . $user->user_lastname;
				}
			}
		}

		$purchase->total  = floatval( $purchase->total );
		$purchase->amount = floatval( $purchase->amount );
		$purchase->tax    = floatval( $purchase->tax );

		$fields = array(
			'ORDERID'  => $purchase->order_id,
			'CURRENCY' => $purchase->currency,
			'FULLNAME' => $purchase->fullname,
			'EMAIL'    => $purchase->email,
			'PRODUCTS' => $purchase->products,
			'AMOUNT'   => $purchase->amount,
			'TOTAL'    => $purchase->total,
			'TAX'      => $purchase->tax,
		);

		$sep      = '@@';
		$buffer   = '';
		$previous = 0;
		$start    = strpos( $text, $sep );
		if ( false == $start ) {
			$buffer = $text;
		} else {
			while ( false != $start ) {
				$buffer .= $tcmp->utils->substr( $text, $previous, $start );
				$end     = strpos( $text, $sep, $start + strlen( $sep ) );
				if ( false != $end ) {
					$code = $tcmp->utils->substr( $text, $start + strlen( $sep ), $end );
					$code = $tcmp->utils->to_array( $code );
					if ( 1 == count( $code ) ) {
						$code[] = '';
					}

					$v = false;
					if ( isset( $fields[ $code[0] ] ) ) {
						$v = $fields[ $code[0] ];
					}
					if ( is_null( $v ) || false == $v ) {
						$v = $code[1];
					}
					if ( is_numeric( $v ) ) {
						$v = floatval( $v );
						$v = round( $v, 2 );
						switch ( $code[0] ) {
							case 'TOTAL':
							case 'AMOUNT':
							case 'TAX':
								$v = number_format( $v, 2, '.', '' );
								break;
							default:
								$v = intval( $v );
								break;
						}
					} elseif ( is_array( $v ) ) {
						$a = '';
						foreach ( $v as $t ) {
							$t = str_replace( ',', '', $t );
							if ( '' != $a ) {
								$a .= ',';
							}
							$a .= $t;
						}
						$v = $a;
					}
					$v       = str_replace( "'", '', $v );
					$v       = str_replace( '"', '', $v );
					$buffer .= $v;

					$previous = $end + strlen( $sep );
					$start    = strpos( $text, $sep, $previous );
				} else {
					$buffer  .= $tcmp->utils->substr( $text, $start );
					$previous = false;
					$start    = false;
				}
			}

			if ( false != $previous && $previous < strlen( $text ) ) {
				$code    = $tcmp->utils->substr( $text, $previous );
				$buffer .= $code;
			}
		}
		return $buffer;
	}

	//return snippets that match with options
	public function get_conversion_snippets( $options = null ) {
		global $tcmp;

		$defaults = array(
			'pluginId'      => 0,
			'categoriesIds' => array(),
			'productsIds'   => array(),
			'tagsIds'       => array(),
		);
		$options  = $tcmp->utils->parseArgs( $options, $defaults );

		$result    = array();
		$plugin_id = intval( $options['pluginId'] );
		$values    = $this->values();

		foreach ( $values as $snippet ) {
			$snippet['trackMode'] = intval( $snippet['trackMode'] );
			if ( $snippet && $snippet['trackMode'] > 0 && $snippet['trackMode'] == $plugin_id ) {
				$match = false;

				$match = ( $match || $this->match_conversion( $snippet, $plugin_id, 'ProductsIds', $options['productsIds'] ) );
				$match = ( $match && $this->match_device_type( $snippet ) );
				if ( ! $match ) {
					//no selected so..all match! :)
					if ( 0 == count( $snippet[ 'CTC_' . $plugin_id . '_ProductsIds' ] )
						&& 0 == count( $snippet[ 'CTC_' . $plugin_id . '_CategoriesIds' ] )
						&& 0 == count( $snippet[ 'CTC_' . $plugin_id . '_TagsIds' ] ) ) {
						$match = true;
					}
				}

				if ( $match ) {
					$result[] = $snippet;
				}
			}
		}
		return $result;
	}
	private function match_conversion( $snippet, $plugin_id, $suffix, $current_ids ) {
		global $tcmp;

		$settings_ids = 'CTC_' . $plugin_id . '_' . $suffix;
		if ( isset( $snippet[ $settings_ids ] ) ) {
			$settings_ids = $snippet[ $settings_ids ];
		} else {
			$settings_ids = array();
		}

		$result = $tcmp->utils->in_all_array( $current_ids, $settings_ids );
		return $result;
	}

	//from a post retrieve the html code that is needed to insert into the page code
	public function get_codes( $position, $post, $args = array() ) {
		global $tcmp;

		$defaults = array( 'field' => 'code' );
		$args     = $tcmp->utils->parseArgs( $args, $defaults );

		$post_id       = 0;
		$post_type      = 'page';
		$tags_ids      = array();
		$categories_ids = array();
		if ( $post ) {
			$post_id = $tcmp->utils->get( $post, 'ID', false );
			if ( false == $post_id ) {
				$post_id = $tcmp->utils->get( $post, 'post_ID' );
			}
			$post_type = $tcmp->utils->get( $post, 'post_type' );

			$options = array(
				'orderby' => 'name',
				'order'   => 'ASC',
				'fields'  => 'ids',
			);
			if ( isset( $post->ID ) ) {
				$tags_ids      = wp_get_post_tags( $post->ID, $options );
				$categories_ids = wp_get_post_categories( $post->ID );
			} else {
				$tags_ids      = array();
				$categories_ids = array();
			}
		}

		$tcmp->options->clearSnippetsWritten();
		if ( TCMP_POSITION_CONVERSION == $position ) {
			//write snippets previously appended
			$ids = $tcmp->options->get_conversion_snippet_ids();
			if ( false != $ids && count( $ids ) > 0 ) {
				foreach ( $ids as $id ) {
					$snippet = $tcmp->manager->get( $id );
					if ( $snippet ) {
						$tcmp->options->pushSnippetWritten( $snippet );
					}
				}
			}
		} else {
			$snippets = $this->values();
			foreach ( $snippets as $v ) {
				if ( ! $v || ( $position > -1 && $v['position'] != $position ) || '' == $v['code'] || ! $v['active'] ) {
					continue;
				}
				if ( TCMP_TRACK_MODE_CODE != $v['trackMode'] ) {
					continue;
				}
				if ( $tcmp->options->hasSnippetWritten( $v ) ) {
					$tcmp->log->debug( 'SKIPPED SNIPPET=%s[%s] DUE TO ALREADY WRITTEN', $v['id'], $v['name'] );
					continue;
				}

				$match = false;
				if ( ! $match && ( TCMP_TRACK_PAGE_ALL == $v['trackPage'] || $v['includeEverywhereActive'] ) ) {
					$tcmp->log->debug( 'INCLUDED SNIPPET=%s[%s] DUE TO EVERYWHERE', $v['id'], $v['name'] );
					$match = true;
				}
				if ( ! $match && $post_id > 0 && $this->match_snippet( $post_id, $post_type, $categories_ids, $tags_ids, 'include', $v ) ) {
					$match = true;
				}

				if ( $match && $post_id > 0 ) {
					//echo '<textarea cols="100" rows="4">check for exclude'  . print_r($v, true) . '</textarea>';
					if ( $this->match_snippet( $post_id, $post_type, $categories_ids, $tags_ids, 'except', $v ) ) {
						//echo '<textarea cols="100" rows="4">exclude '  . print_r($v, true) . '</textarea>';
						$tcmp->log->debug( 'FOUND AT LEAST ONE EXCEPT TO EXCLUDE SNIPPET=%s [%s]', $v['id'], $v['name'] );
						$match = false;
					}
				}

				if ( $match ) {
					$tcmp->options->pushSnippetWritten( $v );
				}
			}
		}

		//obtain result as snippets or array of one field (tipically "id")
		$result = $tcmp->options->getSnippetsWritten();
		if ( 'all' != $args['field'] ) {
			$array = array();
			foreach ( $result as $k => $v ) {
				$k = $args['field'];
				if ( isset( $v[ $k ] ) ) {
					$array[] = $v[ $k ];
				} else {
					$tcmp->log->error( 'SNIPPET=%s [%s] WITHOUT FIELD=%s', $v['id'], $v['name'], $k );
				}
			}
			$result = $array;
		}
		return $result;
	}

	//ottiene o salva tutte le chiavi dei tracking code utilizzati ordinati per id
	public function keys( $keys = null ) {
		global $tcmp;

		if ( is_array( $keys ) ) {
			$tcmp->options->setSnippetList( $keys );
			$result = $keys;
		} else {
			$result = $tcmp->options->getSnippetList();
		}

		if ( ! is_array( $result ) ) {
			$result = array();
		} else {
			sort( $result );
		}
		return $result;
	}

	//ottiene il conteggio attuale dei tracking code
	public function count() {
		$result = count( $this->keys() );
		return $result;
	}
	public function codes_count() {
		$result = 0;
		$ids    = $this->keys();
		foreach ( $ids as $id ) {
			$snippet = $this->get( $id );
			if ( $snippet ) {
				$result += 1;
			}
		}
		return $result;
	}
	public function get_last_id() {
		$result = 0;
		$list   = $this->keys();
		foreach ( $list as $v ) {
			$v = intval( $v );
			if ( $v > $result ) {
				$result = $v;
			}
		}
		return $result;
	}

	public function values() {
		$keys  = $this->keys();
		$array = array();
		foreach ( $keys as $k ) {
			$v       = $this->get( $k );
			$array[] = $v;
		}
		usort( $array, array( $this, 'values_compare' ) );

		$result = array();
		foreach ( $array as $v ) {
			$id            = $v['id'];
			$result[ $id ] = $v;
		}
		return $result;
	}
	public function values_compare( $o1, $o2 ) {
		global $tcmp;
		$v1     = $tcmp->utils->iget( $o1, 'order', false );
		$v2     = $tcmp->utils->iget( $o2, 'order', false );
		$result = ( $v1 - $v2 );
		if ( 0 == $result ) {
			$v1     = $tcmp->utils->get( $o1, 'name', false );
			$v2     = $tcmp->utils->get( $o2, 'name', false );
			$result = strcasecmp( $v1, $v2 );
		}
		return $result;
	}
}
