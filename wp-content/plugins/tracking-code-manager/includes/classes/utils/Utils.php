<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TCMP_Utils {
	const FORMAT_DATETIME         = 'd/m/Y H:i';
	const FORMAT_COMPACT_DATETIME = 'd/m H:i';
	const FORMAT_DATE             = 'd/m/Y';
	const FORMAT_TIME             = 'H:i';

	const FORMAT_SQL_DATETIME = 'Y-m-d H:i:s';
	const FORMAT_SQL_DATE     = 'Y-m-d';
	const FORMAT_SQL_TIME     = 'H:i:s';

	private $color_index;
	private $default_currency_symbol;

	public function __construct() {
		$this->color_index = 0;
	}

	public function setDefaultCurrencySymbol( $value ) {
		$this->default_currency_symbol = $value;
	}
	public function getDefaultCurrencySymbol() {
		return ( '' == $this->default_currency_symbol ? 'USD' : $this->default_currency_symbol );
	}
	function format( $message, $v1 = null, $v2 = null, $v3 = null, $v4 = null, $v5 = null ) {
		if ( $v1 || $v2 || $v3 || $v4 || $v5 ) {
			$message = sprintf( $message, $v1, $v2, $v3, $v4, $v5 );
		}
		return $message;
	}
	function starts_with( $haystack, $needle ) {
		$length = strlen( $needle );
		return ( substr( $haystack, 0, $length ) === $needle );
	}

	function ends_with( $haystack, $needle ) {
		$length = strlen( $needle );
		$start  = $length * -1; //negative
		return ( substr( $haystack, $start ) === $needle );
	}
	function substr( $text, $start = 0, $end = -1 ) {
		if ( $end < 0 ) {
			$end = strlen( $text );
		}
		$length = $end - $start;
		return substr( $text, $start, $length );
	}

	function shortcode_args( $args, $defaults ) {
		$args     = $this->sanitize_shortcode_keys( $args );
		$defaults = $this->sanitize_shortcode_keys( $defaults );
		$args     = shortcode_atts( $defaults, $args );
		return $args;
	}
	function sanitize_shortcode_keys( $array ) {
		$result = array();
		foreach ( $array as $k => $v ) {
			if ( is_string( $k ) ) {
				$k = strtolower( $k );
			}
			$result[ $k ] = $v;
		}
		return $result;
	}

	//WOW! $end is passed as reference due to we can change it if we found \n character after
	//substring to avoid having these characters after or before
	function substrln( $text, $start = 0, &$end = -1 ) {
		if ( $end < 0 ) {
			$end = strlen( $text );
		}

		do {
			$loop = false;
			$c    = substr( $text, $end, 1 );
			if ( "\n" == $c || "\r" == $c || '.' == $c ) {
				$end += 1;
				$loop = true;
			}
		} while ( $loop );

		$length = $end - $start;
		return substr( $text, $start, $length );
	}

	function toCommaArray( $array, $is_numeric = true, $is_trim = true ) {
		if ( is_string( $array ) ) {
			if ( trim( $array ) == '' ) {
				$array = array();
			} else {
				$array = explode( ',', $array );
			}
		} elseif ( is_numeric( $array ) ) {
			$array = array( $array );
		}
		if ( ! is_array( $array ) ) {
			$array = array();
		}
		for ( $i = 0; $i < count( $array ); $i++ ) {
			if ( $is_trim ) {
				$array[ $i ] = trim( $array[ $i ] );
			}
			if ( $is_numeric ) {
				$array[ $i ] = floatval( $array[ $i ] );
			}
		}
		return $array;
	}
	function in_all_array( $search, $where ) {
		return ( $this->inArray( -1, $where ) || $this->inArray( $search, $where ) );
	}
	function inArray( $search, $where ) {
		$result = false;
		$where  = $this->to_array( $where );
		$search = $this->to_array( $search );
		if ( 0 == count( $where ) || 0 == count( $search ) ) {
			return false;
		}

		foreach ( $where as $v ) {
			$v .= '';
			foreach ( $search as $c ) {
				$c .= '';
				if ( $v == $c ) {
					$result = true;
					break;
				}
			}

			if ( $result ) {
				break;
			}
		}
		return $result;
	}

	function is( $name, $compare, $default = '', $ignore_case = true ) {
		$what   = $this->qs( $name, $default );
		$result = false;
		if ( is_string( $compare ) ) {
			$compare = explode( ',', $compare );
		}
		if ( $ignore_case ) {
			$what = strtolower( $what );
		}

		foreach ( $compare as $v ) {
			if ( $ignore_case ) {
				$v = strtolower( $v );
			}
			if ( $what == $v ) {
				$result = true;
				break;
			}
		}
		return $result;
	}

	public function twitter( $name ) {
		?>
		<a href="https://twitter.com/<?php echo esc_attr( $name ); ?>" class="twitter-follow-button" data-show-count="false" data-dnt="true">Follow @<?php echo esc_attr( $name ); ?></a>
		<?php
	}

	public function sort( $is_associative, $a1, $a2 = null, $a3 = null, $a4 = null, $a5 = null ) {
		$array = $this->merge( $is_associative, $a1, $a2, $a3, $a4, $a5 );
		ksort( $array );
		return $array;
	}
	public function merge( $is_associative, $a1, $a2 = null, $a3 = null, $a4 = null, $a5 = null ) {
		$result = array();
		if ( $is_associative ) {
			$array = array( $a1, $a2, $a3, $a4, $a5 );
			foreach ( $array as $a ) {
				if ( ! is_array( $a ) ) {
					continue;
				}

				foreach ( $a as $k => $v ) {
					if ( ! isset( $result[ $k ] ) ) {
						$result[ $k ] = $v;
					}
				}
			}
		} else {
			$result = array_merge( $a1, $a2, $a3, $a4, $a5 );
		}
		return $result;
	}

	function bget( $instance, $name, $index = -1 ) {
		$v = $this->get( $instance, $name, false, $index );
		$v = $this->isTrue( $v );
		return $v;
	}
	function dget( $instance, $name, $index = -1 ) {
		$v = $this->get( $instance, $name, false, $index );
		$v = $this->parse_date_to_time( $v );
		return $v;
	}
	function aget( $instance, $name, $index = -1 ) {
		$v = $this->get( $instance, $name, false, $index );
		$v = $this->to_array( $v );
		return $v;
	}
	function get( $instance, $name, $default = '', $index = -1 ) {
		if ( $this->is_empty( $instance ) ) {
			return $default;
		}
		$options = array();
		//assolutamente da non fare altrimenti succede un disastro in quanto i metodi del inputComponent
		//gli passano come name il valore...insomma un disastro!
		//$name=$this->to_array($name);
		//$name=implode('.', $name);

		$result = $default;
		if ( is_array( $instance ) || is_object( $instance ) ) {
			if ( $this->propertyReflect( $instance, $name, $options ) ) {
				$result = $options['get'];
			}
		}
		if ( $index > -1 ) {
			$result = $this->to_array( $result );
			if ( isset( $result[ $index ] ) ) {
				$result = $result[ $index ];
			} else {
				$result = $default;
			}
		}
		return $result;
	}
	function has( $instance, $name ) {
		return $this->propertyReflect( $instance, $name );
	}
	function set( &$instance, $name, $value ) {
		$options = array( 'set' => $value );
		$result  = $this->propertyReflect( $instance, $name, $options );
		if ( ! $result ) {
		}
		return $result;
	}
	function iget( $array, $name, $default = '' ) {
		return intval( $this->get( $array, $name, $default ) );
	}

	private function propertyReflect( &$instance, $name, &$options = array() ) {
		if ( ! is_object( $instance ) && ! is_array( $instance ) ) {
			return false;
		}

		if ( false === $options || ! is_array( $options ) ) {
			$options = array();
		}
		$options['has'] = false;
		$options['get'] = false;

		$current = $instance;
		$names   = explode( '.', $name );
		$value   = false;
		$result  = true;
		for ( $i = 0; $i < count( $names ); $i++ ) {
			$name = $names[ $i ];
			if ( ! is_object( $current ) && ! is_array( $current ) ) {
				return false;
			}
			if ( is_null( $current ) ) {
				return false;
			}

			if ( is_object( $current ) ) {
				if ( get_class( $current ) == 'stdClass' ) {
					if ( isset( $current->$name ) ) {
						$value = $current->$name;
					} else {
						$result = false;
					}
				} else {
					$r = new ReflectionClass( $current );
					try {
						if ( $r->getProperty( $name ) !== false ) {
							$value = $current->$name;
						} else {
							$result = false;
						}
					} catch ( Exception $ex ) {
						if ( isset( $current->$name ) ) {
							$value = $current->$name;
						} else {
							$result = false;
						}
					}
				}
			} elseif ( is_array( $current ) ) {
				if ( isset( $current[ $name ] ) ) {
					$value = $current[ $name ];
				} else {
					$result = false;
				}
			}

			if ( ! $result ) {
				break;
			} elseif ( $i < ( count( $names ) - 1 ) ) {
				$current = $value;
			} else {
				$options['get'] = $value;
				if ( isset( $options['set'] ) ) {
					if ( is_object( $current ) ) {
						$current->$name = $options['set'];
					} elseif ( is_array( $current ) ) {
						$current[ $name ] = $options['set'];
					}
				}
			}
		}
		return $result;
	}
	function isTrue( $value ) {
		$result = false;
		if ( is_bool( $value ) ) {
			$result = (bool) $value;
		} elseif ( is_numeric( $value ) ) {
			$result = floatval( $value ) > 0;
		} else {
			$result = strtolower( $value );
			if ( 'ok' == $result || 'yes' == $result || 'true' == $result || 'on' == $result ) {
				$result = true;
			} else {
				$result = false;
			}
		}
		return $result;
	}
	function aqs( $prefix, $remove_prefix = true ) {
		$result = array();
		$array  = $this->merge( true, $_POST, $_GET );
		foreach ( $array as $k => $v ) {
			if ( $this->starts_with( $k, $prefix ) ) {
				if ( $remove_prefix ) {
					$k = substr( $k, strlen( $prefix ) );
				}
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
	function iqs( $name, $default = 0, $min = 0, $max = 0 ) {
		$result = floatval( $this->qs( $name, $default ) );
		if ( $min != $max ) {
			if ( $result < $min ) {
				$result = $min;
			} elseif ( $result > $max ) {
				$result = $max;
			}
		}
		return $result;
	}
	function dqs( $name, $default = 0 ) {
		$result = ( $this->qs( $name, $default ) );
		$result = $this->parse_date_to_time( $result );
		if ( 0 == $result ) {
			$result = $default;
		}
		return $result;
	}
	//per ottenere un campo dal $_GET oppure dal $_POST
	function qs( $name, $default = '' ) {
		global $tcmp_allowed_html_tags;
		$result = $default;
		if ( isset( $_POST[ $name ] ) ) {
			$result = $this->sanitize_post_or_get( $_POST[ $name ] );
		} elseif ( isset( $_GET[ $name ] ) ) {
			$result = $this->sanitize_post_or_get( $_GET[ $name ] );
		}

		if ( is_string( $result ) ) {
			//The superglobals $_GET and $_REQUEST are already decoded.
			//Using urldecode() on an element in $_GET or $_REQUEST
			//could have unexpected and dangerous results.
			//$result=urldecode($result);
			$result = trim( $result );
		}
		return $result;
	}

	private function sanitize_post_or_get( $array ) {
		global $tcmp_allowed_html_tags;
		if ( is_array( $array ) ) {
			foreach ( $array as $k => &$v ) {
				if ( 'code' == $k ) {
					$v = wp_kses( $v, $tcmp_allowed_html_tags );
				} elseif ( is_string( $v ) ) {
					$v = sanitize_text_field( $v );
				}
			}
		}
		return $array;
	}

	var $_taxonomyType;

	function query( $query, $options = null ) {
		global $tcmp, $wpdb;

		$parent   = '';
		$defaults = array(
			'post_type' => '',
			'all'       => false,
			'select'    => false,
			'taxonomy'  => '',
		);
		$options  = wp_parse_args( $options, $defaults );

		if ( ! isset( $options['type'] ) ) {
			if ( '' != $options['post_type'] ) {
				$options['type'] = $options['post_type'];
			} elseif ( '' != $options['taxonomy'] ) {
				$options['type'] = $options['taxonomy'];
			} else {
				$options['type'] = '';
			}
		}

		if ( TCMP_QUERY_CONVERSION_PLUGINS == $query ) {
			$array  = $tcmp->ecommerce->getPlugins( false );
			$result = array();
			foreach ( $array as $k => $v ) {
				$result[] = $v;
			}
		} else {
			$key    = array( 'Query', $query . '_' . $options['type'] );
			$result = $tcmp->options->getCache( $key );
			if ( ! is_array( $result ) || 0 == count( $result ) ) {
				$q        = null;
				$id       = 'ID';
				$name     = 'post_title';
				$function = '';
				switch ( $query ) {
					case TCMP_QUERY_POSTS_OF_TYPE:
						//$options=array('posts_per_page'=>-1, 'post_type'=>$args['post_type']);
						//$q=get_posts($options);
						$sql      = 'SELECT ID, post_title FROM ' . $wpdb->prefix . "posts WHERE post_status='publish' AND post_type='" . $options['type'] . "' ORDER BY post_title";
						$q        = $wpdb->get_results( $sql );
						$function = 'get_permalink';
						break;
					case TCMP_QUERY_CATEGORIES:
						break;
					case TCMP_QUERY_TAGS:
						break;
					case TCMP_QUERY_TAXONOMIES_OF_TYPE:
						break;
				}

				$result = array();
				if ( $q ) {
					if ( ! is_wp_error( $q ) ) {
						foreach ( $q as $v ) {
							$item = array(
								'id'   => $v->$id,
								'name' => $v->$name,
							);
							if ( '' != $parent ) {
								$item['parent'] = $v->$parent;
							}
							$result[] = $item;
						}
					}
				} elseif ( TCMP_QUERY_POST_TYPES == $query ) {
					global $wp_post_types;
					$result = array();
					foreach ( $wp_post_types as $k => $v ) {
						$is_public = $tcmp->utils->bget( $v, 'public' );
						if ( $is_public && 'attachment' != $k ) {
							$v = $tcmp->utils->get( $v, 'labels.singular_name' );
							if ( 'post' == $k || 'page' == $k ) {
								$result[ $k ] = $v;
							}
						}
					}
					$result = $tcmp->utils->toFormatListArrayFromListObjects( $result, false, '{text} ({id})' );
				} elseif ( TCMP_QUERY_TAXONOMY_TYPES == $query ) {

				}

				if ( $this->functionExists( $function ) ) {
					for ( $i = 0; $i < count( $result ); $i++ ) {
						$v            = $result[ $i ];
						$v['url']     = $this->functionCall( $function, array( $v['id'] ) );
						$result[ $i ] = $v;
					}
				}
				$tcmp->options->setCache( $key, $result );
			}
		}

		if ( $options['all'] ) {
			$first   = array();
			$first[] = array(
				'id'   => -1,
				'name' => '[' . $tcmp->lang->L( 'All' ) . ']',
				'url'  => '',
			);
			$result  = array_merge( $first, $result );
		}
		if ( $options['select'] ) {
			$first   = array();
			$first[] = array(
				'id'   => 0,
				'name' => '[' . $tcmp->lang->L( 'Select' ) . ']',
				'url'  => '',
			);
			$result  = array_merge( $first, $result );
		}
		$result              = $this->sortOptions( $result );
		$this->_taxonomyType = '';
		return $result;
	}

	//wp_parse_args with null correction
	function parseArgs( $options, $defaults ) {
		if ( is_null( $options ) ) {
			$options = array();
		} elseif ( is_object( $options ) ) {
			$options = (array) $options;
		} elseif ( ! is_array( $options ) ) {
			$options = array();
		}
		if ( is_null( $defaults ) ) {
			$defaults = array();
		} elseif ( is_object( $defaults ) ) {
			$defaults = (array) $defaults;
		} elseif ( ! is_array( $defaults ) ) {
			$defaults = array();
		}

		foreach ( $defaults as $k => $v ) {
			if ( is_null( $v ) ) {
				unset( $defaults[ $k ] );
			}
		}

		foreach ( $options as $k => $v ) {
			if ( isset( $defaults[ $k ] ) ) {
				if ( is_null( $v ) ) {
					//so can take the default value
					unset( $options[ $k ] );
				} elseif ( is_string( $v ) && ( '' === $v ) && isset( $defaults[ $k ] ) && is_array( $defaults[ $k ] ) ) {
					//a very strange case, i have a blank string for rappresenting an empty array
					unset( $options[ $k ] );
				} else {
					unset( $defaults[ $k ] );
				}
			}
		}
		foreach ( $defaults as $k => $v ) {
			$options[ $k ] = $v;
		}
		return $options;
	}

	function redirect( $location ) {
		if ( '' == $location ) {
			return;
		}
		?>
		<div id="tcmpRedirect" href="<?php echo esc_attr( $location ); ?>"></div>
		<?php
		die();
	}

	//return the element inside array with the specified key
	function getArrayValue( $key, $array, $value = '' ) {
		$result = false;
		if ( isset( $array[ $key ] ) ) {
			$result         = $array[ $key ];
			$result['name'] = $key;
		}
		if ( false !== $result && '' != $value ) {
			if ( isset( $result[ $value ] ) ) {
				$result = $result[ $value ];
			}
		}
		return $result;
	}

	var $_sort_field;
	var $_ignore_case;
	function aksort( &$array, $sort_field = 'name', $ignore_case = true ) {
		$this->_sort_field  = $sort_field;
		$this->_ignore_case = $ignore_case;
		usort( $array, array( $this, 'aksortCompare' ) );
	}
	//not thread-safe!
	private function aksortCompare( $a, $b ) {
		if ( $a === $b || $a == $b ) {
			return 0;
		}

		$result = 0;
		$a      = $a[ $this->_sort_field ];
		$b      = $b[ $this->_sort_field ];
		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			$result = ( $a < $b ) ? -1 : 1;
		} else {
			$a .= '';
			$b .= '';
			if ( $this->_ignore_case ) {
				$result = strcasecmp( $a, $b );
			} else {
				$result = strcmp( $a . '', $b );
			}
		}
		return $result;
	}

	public function formatCustomDate( $time, $format ) {
		$time = $this->parse_date_to_time( $time );
		if ( $time > 0 ) {
			$time = date( $format, $time );
		} else {
			$time = '';
		}
		return $time;
	}

	public function formatDatetime( $time = 'now' ) {
		return $this->formatCustomDate( $time, TCMP_Utils::FORMAT_DATETIME );
	}
	public function formatCompactDatetime( $time = 'now' ) {
		return $this->formatCustomDate( $time, TCMP_Utils::FORMAT_COMPACT_DATETIME );
	}
	public function formatDate( $time = 'date' ) {
		return $this->formatCustomDate( $time, TCMP_Utils::FORMAT_DATE );
	}
	public function formatSmartDatetime( $time = 'now' ) {
		$time   = $this->parse_date_to_time( $time );
		$result = '';
		if ( $time > 0 ) {
			$h = intval( date( 'H', $time ) );
			$i = intval( date( 'i', $time ) );
			$s = intval( date( 's', $time ) );
			if ( 0 == $h && 0 == $i && 0 == $s ) {
				$result = $this->formatDate( $time );
			} else {
				$result = $this->formatDatetime( $time );
			}
		}
		return $result;
	}
	public function formatTime( $time = 'now' ) {
		return $this->formatCustomTime( $time, TCMP_Utils::FORMAT_TIME );
	}
	public function formatSqlDatetime( $time = 'now' ) {
		return $this->formatCustomDate( $time, TCMP_Utils::FORMAT_SQL_DATETIME );
	}
	public function formatSqlDate( $time = 'date' ) {
		return $this->formatCustomDate( $time, TCMP_Utils::FORMAT_SQL_DATE );
	}
	public function formatSqlTime( $time = 'now' ) {
		return $this->formatCustomTime( $time, TCMP_Utils::FORMAT_SQL_TIME );
	}

	private function formatCustomTime( $time, $format ) {
		$time = $this->parse_date_to_time( $time );
		if ( $time > 86400 ) {
			$h    = date( 'H', $time );
			$i    = date( 'i', $time );
			$s    = date( 's', $time );
			$time = $h * 3600 + $i * 60 + $s;
		}

		$s      = $time % 60;
		$time   = ( $time - $s ) / 60;
		$i      = $time % 60;
		$h      = ( $time - $i ) / 60;
		$s      = str_pad( $s, 2, '0', STR_PAD_LEFT );
		$i      = str_pad( $i, 2, '0', STR_PAD_LEFT );
		$h      = str_pad( $h, 2, '0', STR_PAD_LEFT );
		$format = str_replace( 'H', $h, $format );
		$format = str_replace( 'i', $i, $format );
		$format = str_replace( 's', $s, $format );
		return $format;
	}

	public function parseNumber( $what, $default = 0 ) {
		$result = $default;
		if ( is_array( $what ) ) {
			if ( count( $what ) > 0 ) {
				$result = doubleval( $what[0] );
			}
		} elseif ( is_numeric( $what ) ) {
			$result = doubleval( $what );
		} elseif ( is_string( $what ) || is_bool( $what ) ) {
			$result = ( $this->isTrue( $what ) ? 1 : 0 );
		}
		return $result;
	}
	public function parseDateToArray( $date ) {
		global $tcmp;

		$pm   = false;
		$date = strtoupper( trim( $date ) );
		if ( $tcmp->utils->ends_with( $date, 'AM' ) ) {
			$date = substr( $date, 0, strlen( $date ) - 2 );
			$date = trim( $date );
		} elseif ( $tcmp->utils->ends_with( $date, 'PM' ) ) {
			$date = substr( $date, 0, strlen( $date ) - 2 );
			$date = trim( $date );
			$pm   = true;
		}

		$date = explode( ' ', $date );
		if ( 1 == count( $date ) ) {
			$result = array();
			$date   = $date[0];
			$date   = str_replace( '/', '-', $date );
			if ( strpos( $date, '-' ) !== false ) {
				$date = explode( '-', $date );
				if ( count( $date ) >= 3 ) {
					$d = intval( $date[0] );
					$m = intval( $date[1] );
					$y = intval( $date[2] );
					if ( $d > 1900 ) {
						$t = $d;
						$d = $y;
						$y = $t;
					}
					if ( $y > 0 && $m > 0 && $d > 0 ) {
						$result['y'] = $y;
						$result['m'] = $m;
						$result['d'] = $d;
					}
				}
			} elseif ( strpos( $date, ':' ) !== false ) {
				$date = explode( ':', $date );
				if ( 2 == count( $date ) ) {
					$date[] = 0;
				}
				if ( count( $date ) >= 3 ) {
					$h = intval( $date[0] );
					$i = intval( $date[1] );
					$s = intval( $date[2] );
					if ( $h >= 0 && $i >= 0 && $s >= 0 ) {
						$result['h'] = $h;
						$result['i'] = $i;
						$result['s'] = $s;
					}
				}
			}
		} else {
			$a1     = $this->parseDateToArray( $date[0] );
			$a2     = $this->parseDateToArray( $date[1] );
			$result = $tcmp->utils->parseArgs( $a1, $a2 );
		}

		if ( $pm && isset( $result['h'] ) ) {
			$result['h'] = intval( $result['h'] ) + 12;
		}
		return $result;
	}
	public function parse_date_to_time( $date ) {
		global $tcmp;
		if ( is_null($date) ) {
			$date = 'now';
		}
		if ( is_numeric( $date ) || trim( $date ) == '' ) {
			$date = intval( $date );
			return $date;
		}

		$date = strtolower( $date );
		if ( 'now' == $date ) {
			$date = time();
			return $date;
		} elseif ( 'date' == $date ) {
			$date = strtotime( date( 'Y-m-d', time() ) );
			return $date;
		} elseif ( 'time' == $date ) {
			$date = date( 'H:i:s', time() );
		}
		$result   = $this->parseDateToArray( $date );
		$defaults = array(
			'y' => 0,
			'm' => 0,
			'd' => 0,
			'h' => 0,
			'i' => 0,
			's' => 0,
		);
		$a        = $tcmp->utils->parseArgs( $result, $defaults );
		if ( 0 == $a['y'] && 0 == $a['m'] && 0 == $a['d'] ) {
			$result = $a['h'] * 3600 + $a['i'] * 60 + $a['s'];
		} else {
			$result = mktime( $a['h'], $a['i'], $a['s'], $a['m'], $a['d'], $a['y'] );
		}
		if ( $result < 0 ) {
			$result = 0;
		}
		return $result;
	}
	public function getIntDate( $time, $separator = '' ) {
		$time = $this->parse_date_to_time( $time );
		if ( $time > 0 ) {
			if ( '' == $separator ) {
				$time = date( 'Ymd', $time );
				$time = intval( $time );
			} else {
				$time = date( 'Y', $time ) . $separator . date( 'm', $time ) . $separator . date( 'd', $time );
			}
		}

		return $time;
	}
	public function getIntMinute( $h, $m, $separator = '' ) {
		$h = intval( $h );
		$m = intval( $m );
		if ( $m < 10 ) {
			$m = '0' . $m;
		}
		$result = $h . $separator . $m;
		if ( '' == $separator ) {
			$result = intval( $result );
		}
		return $result;
	}

	//args can be a string or an associative array if you want
	public function get_text_args( $args, $defaults = array(), $excludes = array() ) {
		$result   = $args;
		$excludes = $this->to_array( $excludes );
		if ( is_array( $result ) && count( $result ) > 0 ) {
			$result = '';
			foreach ( $args as $k => $v ) {
				if ( is_array( $v ) || is_object( $v ) ) {
					continue;
				}

				if ( 0 == count( $excludes ) || ! in_array( $k, $excludes ) ) {
					$v       = trim( $v );
					$result .= ' ' . $k . '="' . $v . '"';
				}
			}
		} elseif ( ! $args ) {
			$result = '';
		}
		if ( is_array( $defaults ) && count( $defaults ) > 0 ) {
			foreach ( $defaults as $k => $v ) {
				if ( 0 == count( $excludes ) || ! in_array( $k, $excludes ) ) {
					if ( ! isset( $args[ $k ] ) ) {
						$v       = trim( $v );
						$result .= ' ' . $k . '="' . $v . '"';
					}
				}
			}
		}
		return $result;
	}

	public function iuarray( $ids, $positive = false ) {
		$array = $this->iarray( $ids, $positive );
		$array = array_unique( $array );
		sort( $array );
		return $array;
	}
	public function iarray( $ids, $positive = false ) {
		if ( is_string( $ids ) ) {
			$ids = explode( ',', $ids );
		} elseif ( is_numeric( $ids ) ) {
			$ids = array( $ids );
		} elseif ( ! is_array( $ids ) ) {
			$ids = array();
		}

		$array = array();
		foreach ( $ids as $v ) {
			$v = trim( $v );
			if ( '' != $v ) {
				$v = intval( $v );
				if ( ! $positive || $v > 0 ) {
					$array[] = $v;
				}
			}
		}
		return $array;
	}
	public function dbarray( $ids ) {
		if ( is_string( $ids ) ) {
			$ids = explode( ',', $ids );
		} elseif ( is_numeric( $ids ) ) {
			$ids = array( $ids );
		} elseif ( ! is_array( $ids ) ) {
			$ids = array();
		}

		$array = array();
		foreach ( $ids as $v ) {
			$v = trim( $v );
			if ( '' != $v ) {
				if ( is_numeric( $v ) ) {
					$v = intval( $v );
				}
				$array[] = $v;
			}
		}
		return $array;
	}

	function is_associativeArray( $array ) {
		if ( ! is_array( $array ) ) {
			return false;
		}

		$isArray = true;
		$i       = 0;
		foreach ( $array as $k => $v ) {
			if ( $k !== $i ) {
				$isArray = false;
				break;
			}
			++$i;
		}
		return ! $isArray;
	}
	function trim( $value ) {
		if ( is_null( $value ) ) {

		} elseif ( is_string( $value ) ) {
			$value = trim( $value );
		} elseif ( is_numeric( $value ) ) {

		} elseif ( $this->is_associativeArray( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $this->trim( $v );
			}
		} elseif ( is_object( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value->$k = $this->trim( $v );
			}
		} elseif ( is_array( $value ) ) {
			for ( $i = 0; $i < count( $value ); $i++ ) {
				$v = $value[ $i ];
				$this->trim( $v );
				$value[ $i ] = $v;
			}
		}
		return $value;
	}
	function implode( $open, $close, $join, $array ) {
		$result = '';
		foreach ( $array as $v ) {
			if ( '' != $result ) {
				$result .= $join;
			}
			$result .= $open . $v . $close;
		}
		return $result;
	}
	function to_array( $text, $index = -1, $default = '' ) {
		if ( is_array( $text ) ) {
			if ( is_string( $index ) ) {
				$array = array();
				foreach ( $text as $v ) {
					$v = $this->get( $v, $index, false );
					if ( false !== $v ) {
						$array[] = $v;
					}
				}
			} else {
				$array = $text;
			}
			return $array;
		} elseif ( is_numeric( $text ) ) {
			return array( $text );
		} elseif ( is_bool( $text ) || '' === $text ) {
			return array();
		}

		if ( ( $this->starts_with( $text, '[' ) && $this->ends_with( $text, ']' ) )
			|| ( $this->starts_with( $text, '{' ) && $this->ends_with( $text, '}' ) ) ) {
			$text = substr( $text, 1, strlen( $text ) - 2 );
		}
		$text = str_replace( '|', ',', $text );
		$text = explode( ',', $text );

		//exclude empty string
		$array = array();
		foreach ( $text as $t ) {
			if ( '' !== $t ) {
				$array[] = $t;
			}
		}
		$text = $array;
		if ( $index > -1 ) {
			$result = $default;
			if ( isset( $text[ $index ] ) ) {
				$result = $text[ $index ];
			}
			$text = $result;
		}
		return $text;
	}
	function dirToFlatArray( $dir, &$output ) {
		if ( ! isset( $output['dirs'] ) ) {
			$output['dirs'] = array();
		}
		if ( ! isset( $output['files'] ) ) {
			$output['files'] = array();
		}

		$cdir = scandir( $dir );
		foreach ( $cdir as $k => $v ) {
			if ( ! in_array( $v, array( '.', '..' ) ) ) {
				if ( is_dir( $dir . DIRECTORY_SEPARATOR . $v ) ) {
					$i = $dir . DIRECTORY_SEPARATOR . $v;
					array_push( $output['dirs'], $i );
					$this->dirToFlatArray( $i, $output );
				} else {
					$i = $this->getFileInfo( $dir . DIRECTORY_SEPARATOR . $v );
					array_push( $output['files'], $i );
				}
			}
		}
	}
	function dirToArray( $dir ) {
		$result = array();
		if ( ! is_string( $dir ) ) {
			return $result;
		}

		$cdir = scandir( $dir );
		foreach ( $cdir as $k => $v ) {
			if ( ! in_array( $v, array( '.', '..' ) ) ) {
				if ( is_dir( $dir . DIRECTORY_SEPARATOR . $v ) ) {
					$result[ $v ] = $this->dirToArray( $dir . DIRECTORY_SEPARATOR . $v );
				} else {
					$result[] = $this->getFileInfo( $dir . DIRECTORY_SEPARATOR . $v );
				}
			}
		}
		return $result;
	}
	function getFileInfo( $source ) {
		$source = $this->toDirectory( $source );
		if ( ! file_exists( $source ) ) {
			return false;
		}

		$array     = explode( DIRECTORY_SEPARATOR, $source );
		$size      = filesize( $source );
		$source    = array_pop( $array );
		$directory = implode( DIRECTORY_SEPARATOR, $array ) . DIRECTORY_SEPARATOR;

		$pos = strrpos( $source, '.' );
		$ext = '';
		if ( false !== $pos ) {
			$name = substr( $source, 0, $pos );
			$ext  = strtolower( substr( $source, $pos ) );
		}
		$array = array(
			'directory' => $directory,
			'name'      => $name,
			'file'      => $source,
			'size'      => $size,
			'textSize'  => $this->getFileTextSize( $size ),
			'ext'       => $ext,
			'textExt'   => $this->getFileTextExt( $source ),
		);
		return $array;
	}
	function getFileTextSize( $size ) {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		for ( $i = 0; $i < count( $units ); $i++ ) {
			if ( $size < 1024 ) {
				break;
			} else {
				$size /= 1024;
			}
		}
		return intval( $size ) . ' ' . $units[ $i ];
	}
	function getFileTextExt( $source ) {
		$ext = strrpos( $source, '.' );
		if ( false !== $ext ) {
			$ext = strtolower( substr( $source, $ext + 1 ) );
		} else {
			$ext = $source;
		}
		$ext  = strtolower( $ext );
		$text = 'text';
		switch ( $ext ) {
			case 'doc':
			case 'docx':
			case 'odt':
				$text = 'word';
				break;
			case 'xls':
			case 'xlsx':
			case 'ods':
				$text = 'excel';
				break;
			case 'ppt':
			case 'pptx':
			case 'odp':
				$text = 'powerpoint';
				break;
			case 'zip':
			case 'tar':
			case 'gzip':
			case 'rar':
			case '7z':
				$text = 'archive';
				break;
			case 'mp3':
			case 'wav':
				$text = 'audio';
				break;
			case 'mpeg':
			case 'mpg':
			case 'avi':
			case 'mp4':
				$text = 'video';
				break;
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'bmp':
				$text = 'image';
				break;
			case 'pdf':
				$text = 'pdf';
				break;
		}
		return $text;
	}
	function match( $value, $array, $default = '', $ignore_case = true ) {
		$result = $default;
		if ( $ignore_case ) {
			$value = strtolower( $value );
		}
		foreach ( $array as $k => $v ) {
			$v = $this->to_array( $v );
			foreach ( $v as $c ) {
				if ( $ignore_case ) {
					$c = strtolower( $c );
				}
				if ( $value == $c || strpos( $value, $c ) !== false ) {
					$result = $k;
					break;
				}
			}

			if ( $result !== $default ) {
				break;
			}
		}
		return $result;
	}

	function pickColor() {
		$names  = explode( '|', 'primary|success|warning|danger|info|alert|system|dark' );
		$colors = explode( '|', '3498db|70ca63|f6bb42|df5640|3bafda|967adc|37bc9b|666' );

		$i      = ( $this->color_index % count( $colors ) );
		$names  = $names[ $i ];
		$colors = $colors[ $i ];
		++$this->color_index;
		return array( $names, '#' . $colors );
	}
	function upperUnderscoreCase( $text ) {
		$text = $this->arrayCase( $text );
		$text = implode( '_', $text );
		$text = strtoupper( $text );
		return $text;
	}
	function lowerUnderscoreCase( $text ) {
		$text = $this->upperUnderscoreCase( $text );
		$text = strtolower( $text );
		return $text;
	}
	function toDirectory( $file, $mkdirs = false ) {
		$file = str_replace( '\\', DIRECTORY_SEPARATOR, $file );
		$file = str_replace( '/', DIRECTORY_SEPARATOR, $file );
		$file = str_replace( DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $file );

		if ( is_dir( $file ) && ! file_exists( $file ) && $mkdirs ) {
			mkdir( $file, 0777, true );
		}
		return $file;
	}
	function getUploadName( $name ) {
		if ( '' == $name ) {
			return '';
		}

		$name = $this->toDirectory( $name );
		$name = explode( DIRECTORY_SEPARATOR, $name );
		$name = $name[ count( $name ) - 1 ];
		$ext  = '';
		$pos  = strpos( $name, '.' );
		if ( false !== $pos ) {
			$ext  = substr( $name, $pos );
			$name = substr( $name, 0, $pos );
		}

		$buffer = '';
		$name   = str_split( strtolower( $name ) );
		for ( $i = 0; $i < count( $name ); $i++ ) {
			if ( $name[ $i ] >= 'a' && $name[ $i ] <= 'z' ) {
				$buffer .= $name[ $i ];
			} else {
				$buffer .= ' ';
			}
		}
		while ( strpos( $buffer, '  ' ) !== false ) {
			$buffer = str_replace( '  ', ' ', $buffer );
		}
		$buffer  = trim( $buffer );
		$buffer  = str_replace( ' ', '-', $buffer );
		$buffer .= '-' . date( 'Ymd-His', time() ) . $ext;
		return $buffer;
	}
	function toListArrayFromClass( $array, $id = false, $value = false ) {
		global $tcmp;
		$result = array();
		if ( false !== $array && count( $array ) > 0 ) {
			foreach ( $array as $k => $v ) {
				if ( false !== $id ) {
					$k = $tcmp->utils->get( $v, $id );
				}
				if ( false !== $value ) {
					$v = $tcmp->utils->get( $v, $value );
				}

				if ( '' != $k && '' != $v ) {
					$result[] = array(
						'id'   => $k,
						'text' => $v,
						'name' => $v,
					);
				}
			}
		}
		return $result;
	}
	function toFormatListArrayFromListObjects( $array, $id_field, $textFormat ) {
		global $tcmp;
		$result = array();
		if ( false !== $array && count( $array ) > 0 ) {
			foreach ( $array as $i => $e ) {
				$text      = $textFormat;
				$id_exists = false;
				if ( is_array( $e ) || is_object( $e ) ) {
					foreach ( $e as $k => $v ) {
						if ( 'id' == $k ) {
							$id_exists = true;
						}
						if ( is_array( $v ) ) {
							$v = implode( ', ', $v );
						}
						$text = str_replace( '{' . $k . '}', $v, $text );
					}
				} else {
					$text = str_replace( '{text}', $e, $text );
				}

				$id = $i;
				if ( false !== $id_field && '' !== $id_field ) {
					$id = $tcmp->utils->get( $e, $id_field, '' );
				}

				if ( ! $id_exists ) {
					$text = str_replace( '{id}', $id, $text );
				}
				if ( '' != $id ) {
					$result[] = array(
						'id'   => $id,
						'text' => $text,
						'name' => $text,
					);
				}
			}
		}
		return $result;
	}
	function toListArrayFromListObjects( $array, $id_from = false, $textFrom = 'name', $idTo = 'id', $textTo = 'text' ) {

		$result = array();
		foreach ( $array as $v ) {
			$s_id   = $v;
			$s_text = $v;
			if ( false !== $id_from ) {
				$s_id   = $this->get( $v, $id_from, false );
				$s_text = $this->get( $v, $textFrom, false );
			}
			if ( false !== $s_id && '' != $s_text ) {
				if ( '' != $s_id ) {
					$result[] = array(
						$idTo   => $s_id,
						$textTo => $s_text,
					);
				}
			}
		}
		return $result;
	}
	function toColorListArrayFromListObjects( $array, $colors, $id = 'id', $text = 'name' ) {
		global $tcmp;
		$result = array();
		foreach ( $array as $instance ) {
			$s_id   = $this->get( $instance, $id, false );
			$s_text = $this->get( $instance, $text, false );
			foreach ( $colors as $color => $when ) {
				$success = false;
				foreach ( $when['conditions'] as $conditionKey => $condition_value ) {
					$condition_value = $tcmp->utils->to_array( $condition_value );
					$c               = $this->get( $instance, $conditionKey, false );
					if ( false !== $c ) {
						$c .= '';
						foreach ( $condition_value as $v ) {
							$v .= '';
							if ( $c === $v ) {
								$success = true;
								break;
							}
						}
					}
					if ( $success ) {
						break;
					}
				}

				if ( $success ) {
					$style = 'color:' . $color . '; ';
					if ( isset( $when['bold'] ) && $when['bold'] ) {
						$style .= 'font-weight:bold; ';
					}
					$s_text = '<span style="' . $style . '">' . $s_text . '</span>';
				}
			}
			if ( '' != $s_id && false !== $s_text ) {
				$result[] = array(
					'id'   => $s_id,
					'text' => $s_text,
					'name' => $s_text,
				);
			}
		}
		return $result;
	}
	function md5() {
		$array  = func_get_args();
		$buffer = '';
		foreach ( $array as $v ) {
			$buffer .= ':)' . $v;
		}
		$buffer = md5( $buffer );
		return $buffer;
	}
	function arrayCase( $text ) {
		$buffer     = '';
		$array      = array();
		$text       = str_split( $text );
		$prev_upper = false;
		$next_upper = false;
		foreach ( $text as $c ) {
			if ( $c >= 'a' && $c <= 'z' ) {
				if ( $next_upper ) {
					if ( '' != $buffer ) {
						$array[] = $buffer;
						$buffer  = '';
					}
					$c = strtoupper( $c );
				}
				$buffer    .= $c;
				$next_upper = false;
				$prev_upper = false;
			} elseif ( $c >= '0' && $c <= '9' ) {
				$buffer    .= $c;
				$next_upper = true;
			} elseif ( $c >= 'A' && $c <= 'Z' ) {
				if ( ! $prev_upper ) {
					if ( '' != $buffer ) {
						$array[] = $buffer;
						$buffer  = '';
					}
				}
				$buffer    .= $c;
				$next_upper = false;
				$prev_upper = true;
			} else {
				if ( '' != $buffer ) {
					$array[] = $buffer;
					$buffer  = '';
				}
				$next_upper = true;
				$prev_upper = false;
			}
		}
		if ( '' != $buffer ) {
			$array[] = $buffer;
		}
		return $array;
	}
	function lowerCamelCase( $text ) {
		$buffer = '';
		if ( strpos( $text, '_' ) !== false || strpos( $text, '-' ) !== false ) {
			$text = strtolower( $text );
		}

		$text       = str_split( $text );
		$all_upper  = true;
		$next_upper = false;
		foreach ( $text as $c ) {
			if ( $c >= 'a' && $c <= 'z' ) {
				$all_upper = false;
				if ( $next_upper ) {
					$c = strtoupper( $c );
				}
				$buffer    .= $c;
				$next_upper = false;
			} elseif ( $c >= '0' && $c <= '9' ) {
				$buffer    .= $c;
				$next_upper = true;
			} elseif ( $c >= 'A' && $c <= 'Z' ) {
				$buffer    .= $c;
				$next_upper = false;
			} else {
				$next_upper = true;
			}
		}
		if ( $all_upper ) {
			$buffer = strtolower( $buffer );
		} else {
			$buffer = lcfirst( $buffer );
		}
		return $buffer;
	}
	function upperCamelCase( $text ) {
		$text = $this->lowerCamelCase( $text );
		$text = ucfirst( $text );
		return $text;
	}

	function castStdClass( $a ) {
		$a = (array) $a;
		$r = new stdClass();
		foreach ( $a as $k => $v ) {
			$r->$k = $v;
		}
		return $r;
	}
	function castArray( $a ) {
		$r = $a;
		if ( is_object( $a ) ) {
			$r = (array) $a;
		}

		if ( ! is_array( $r ) ) {
			$r = array();
		}
		return $r;
	}
	public function copyArray( $array ) {
		$temp = array();
		foreach ( $array as $k => $v ) {
			$temp[ $k ] = $v;
		}
		return $temp;
	}
	public function isObject( $v ) {
		return ( false !== $v && ! is_null( $v ) && is_object( $v ) );
	}
	public function isArray( $v ) {
		return ( false !== $v && ! is_null( $v ) && is_array( $v ) );
	}
	public function getConstants( $class, $prefix, $reverse = false ) {
		global $tcmp;
		if ( is_object( $class ) ) {
			$class = get_class( $class );
		}
		$class  = str_replace( 'Search', '', $class );
		$class  = str_replace( 'Constants', '', $class );
		$class .= 'Constants';
		if ( ! class_exists( $class ) ) {
			$class = TCMP_PLUGIN_PREFIX . $class;
		}

		$result = array();
		if ( class_exists( $class ) ) {
			$reflection = new ReflectionClass( $class );
			$array      = $reflection->getConstants();
			foreach ( $array as $k => $v ) {
				$pos = 0;
				if ( '' != $prefix ) {
					$pos = stripos( $k, $prefix );
				}
				if ( 0 === $pos ) {
					if ( $reverse ) {
						$result[ $v ] = $k;
					} else {
						$result[ $k ] = $v;
					}
				}
			}
		}
		return $result;
	}
	public function getConstantValue( $class, $prefix, $name, $default = false ) {
		/* @var $ec TCMP_Singleton */
		global $ec;
		$result = $default;
		if ( is_object( $class ) ) {
			$class = get_class( $class );
		}
		$class  = str_replace( 'Search', '', $class );
		$class  = str_replace( 'Constants', '', $class );
		$class .= 'Constants';
		if ( ! class_exists( $class ) ) {
			$class = TCMP_PLUGIN_PREFIX . $class;
		}

		if ( class_exists( $class ) ) {
			$name       = $prefix . '_' . $name;
			$name       = $ec->utils->upperUnderscoreCase( $name );
			$reflection = new ReflectionClass( $class );
			$result     = $reflection->getConstant( $name );
		}
		return $result;
	}
	public function getConstantName( $class, $prefix, $value, $default = false ) {
		/* @var $ec TCMP_Singleton */
		$constants = $this->getConstants( $class, $prefix, true );
		$result    = $default;
		if ( isset( $constants[ $value ] ) ) {
			$result = $constants[ $value ];
		}
		return $result;
	}
	public function daysDiff( $dt1, $dt2 ) {
		$dt1    = $this->parse_date_to_time( $dt1 );
		$dt2    = $this->parse_date_to_time( $dt2 );
		$result = ( $dt2 - $dt1 ) / 86400;
		$result = intval( $result );
		return $result;
	}

	public function getText( $text, $args ) {
		if ( false === $args || 0 == count( $args ) ) {
			return $text;
		}

		foreach ( $args as $k => $v ) {
			$text = str_replace( '{' . $k . '}', $v, $text );
		}
		return $text;
	}
	public function arrayExtends( $options, $defaults ) {
		global $tcmp;
		$options = $tcmp->utils->parseArgs( $options, $defaults );
		foreach ( $options as $k => $v ) {
			if ( is_bool( $v ) ) {
				$v = ( $v ? 1 : 0 );
			}
			if ( isset( $defaults[ $k ] ) ) {
				if ( $this->is_associativeArray( $v ) ) {
					$v = $this->arrayExtends( $v, $defaults[ $k ] );
				} else {
					$v   = $tcmp->utils->to_array( $v );
					$old = $defaults[ $k ];
					$old = $tcmp->utils->to_array( $old );
					if ( ! $this->is_associativeArray( $old ) ) {
						$v = array_merge( $v, $old );
						$v = array_unique( $v );
					}
				}
			} else {
				$v = $tcmp->utils->to_array( $v );
			}
			$options[ $k ] = $v;
		}
		return $options;
	}
	//send remote request to our server to store tracking and feedback
	function remotePost( $action, $data = '' ) {
		global $tcmp;

		$data['secret'] = 'WYSIWYG';
		$response       = wp_remote_post(
			TCMP_INTELLYWP_ENDPOINT . '?iwpm_action=' . $action,
			array(
				'method'      => 'POST',
				'timeout'     => 20,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'body'        => $data,
				'user-agent'  => TCMP_PLUGIN_NAME . '/' . TCMP_PLUGIN_VERSION . '; ' . get_bloginfo( 'url' ),
			)
		);
		$data           = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200
			|| ! isset( $data['success'] ) || ! $data['success']
		) {
			$tcmp->log->error( 'ERRORS SENDING REMOTE-POST ACTION=%s DUE TO REASON=%s', $action, $response );
			$data = false;
		} else {
			$tcmp->log->debug( 'SUCCESSFULLY SENT REMOTE-POST ACTION=%s RESPONSE=%s', $action, $data );
		}
		return $data;
	}

	function isAdminUser() {
		//https://wordpress.org/support/topic/how-to-check-admin-right-without-include-pluggablephp
		return true;
	}

	function isPluginPage() {
		global $tcmp;
		$page   = tcmp_sqs( 'page' );
		$result = ( $this->starts_with( $page, TCMP_PLUGIN_SLUG ) );
		return $result;
	}

	public function arrayPush( &$array, $another ) {
		if ( ! is_array( $another ) ) {
			array_push( $array, $another );
		} elseif ( is_array( $another ) ) {
			foreach ( $another as $v ) {
				array_push( $array, $v );
			}
		}
		return $array;
	}

	public function getConstantsValues( $class, $prefix = '', $glue = false ) {
		$array  = $this->getConstants( $class, $prefix );
		$result = array_values( $array );
		if ( false !== $glue ) {
			$result = implode( $glue, $result );
		}
		return $result;

	}
	public function getValue( $array, $index, $default = false ) {
		$result = $this->get_index( $array, $index, $default );
		if ( $result !== $default ) {
			$result = $result['v'];
		}
		return $result;
	}
	public function get_key( $array, $index, $default = false ) {
		$result = $this->get_index( $array, $index, $default );
		if ( $result !== $default ) {
			$result = $result['k'];
		}
		return $result;
	}
	public function get_index( $array, $index, $default = false ) {
		$result = $default;
		if ( is_array( $array ) && count( $array ) > 0 ) {
			if ( $this->is_associativeArray( $array ) ) {
				$i = 0;
				foreach ( $array as $k => $v ) {
					if ( $index == $i ) {
						$result = array(
							'k' => $k,
							'v' => $v,
						);
						break;
					}
					$i++;
				}
			} else {
				if ( $index < count( $array ) && $index >= 0 ) {
					$result = $array[ $index ];
				}
			}
		}
		return $result;
	}
	public function is_empty( $v ) {
		if ( ! $v ) {
			return true;
		}

		$result = false;
		if ( is_string( $v ) ) {
			$result = ( '' == $v );
		} elseif ( is_array( $v ) ) {
			$result = 0 == count( $v );
		} elseif ( is_object( $v ) ) {
			$result = true;
			foreach ( $v as $k => $w ) {
				if ( ! is_null( $w ) && '' !== $w ) {
					$result = false;
					break;
				}
			}
		}
		return $result;
	}
	public function httpEncode( $v ) {
		$v = gzcompress( $v );
		$v = bin2hex( $v );
		return $v;
	}
	public function httpDecode( $v ) {
		$v = hex2bin( $v );
		$v = gzuncompress( $v );
		return $v;
	}
	public function trimHttp( $uri ) {
		$uri = str_replace( 'http://', '', $uri );
		$uri = str_replace( 'https://', '', $uri );
		return $uri;
	}
	function getClientIpAddress() {
		$ipaddress = '';
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} elseif ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}
		$ipaddress = ( '::1' == $ipaddress ) ? '192.168.0.1' : $ipaddress;
		return $ipaddress;
	}
	public function isMail( $mail ) {
		$at     = strpos( $mail, '@' );
		$dot    = strrpos( $mail, '.' );
		$result = false;
		if ( false !== $at && false !== $dot && $at < $dot ) {
			$result = true;
		}
		return $result;
	}
	public function getNameFromListArray( $array, $id, $default = false ) {
		$result = $default;
		foreach ( $array as $v ) {
			if ( $v['id'] == $id ) {
				if ( isset( $v['text'] ) ) {
					$result = $v['text'];
					break;
				} elseif ( isset( $v['name'] ) ) {
					$result = $v['name'];
					break;
				}
			}
		}
		return $result;
	}
	function bqs( $name, $default = false ) {
		$v      = $this->qs( $name, '' );
		$result = $default;
		if ( '' != $v ) {
			if ( is_numeric( $v ) ) {
				$v      = intval( $v );
				$result = ( $v > 0 );
			} else {
				$result = $this->isTrue( $v );
			}
		}
		return $result;
	}

	function getFunctionName( $function ) {
		$result = false;
		if ( is_string( $function ) ) {
			$result = $function;
		} elseif ( is_array( $function ) ) {
			$result = $function[1];
		}
		return $result;
	}
	function functionExists( $function ) {
		$result = false;
		if ( is_string( $function ) ) {
			$result = function_exists( $function );
		} elseif ( is_array( $function ) ) {
			$result = method_exists( $function[0], $function[1] );
		} elseif ( is_callable( $function ) ) {
			$result = true;
		}
		return $result;
	}
	function functionCall() {
		$args = func_get_args();
		if ( false === $args || 0 == count( $args ) ) {
			return;
		}

		$function = array_shift( $args );
		$result   = null;
		if ( $this->functionExists( $function ) ) {
			$result = call_user_func_array( $function, $args );
		}
		return $result;
	}

	public function contains( $v1, $v2, $ignore_case = true ) {
		$result = false;
		if ( $ignore_case ) {
			$result = stripos( $v1, $v2 ) !== false;
		} else {
			$result = strpos( $v1, $v2 ) !== false;
		}
		return $result;
	}

	private function getHtmlCode( $value ) {
		$value = str_replace( '\"', '', $value );
		$value = str_replace( '"', '', $value );
		return $value;
	}

	public function dequeueScripts( $array ) {
		if ( ! function_exists( 'wp_scripts' ) || function_exists( 'wp_dequeue_script' ) ) {
			return;
		}

		$array   = $this->to_array( $array );
		$scripts = wp_scripts();
		/* @var $v _WP_Dependency */
		foreach ( $scripts->registered as $k => $v ) {
			foreach ( $array as $pattern ) {
				if ( $this->contains( $v->src, $pattern ) || $this->contains( $v->handle, $pattern ) ) {
					wp_dequeue_script( $v->handle );
					break;
				}
			}
		}
	}
	public function dequeueStyles( $array ) {
		if ( ! function_exists( 'wp_styles' ) || function_exists( 'wp_dequeue_style' ) ) {
			return;
		}

		$array  = $this->to_array( $array );
		$styles = wp_styles();
		/* @var $v _WP_Dependency */
		foreach ( $styles->registered as $k => $v ) {
			foreach ( $array as $pattern ) {
				if ( $this->contains( $v->src, $pattern ) || $this->contains( $v->handle, $pattern ) ) {
					wp_dequeue_style( $v->handle );
					break;
				}
			}
		}
	}
	public function formatSeconds( $time ) {
		if ( '' === $time ) {
			return '';
		}

		$time    = intval( $time );
		$seconds = ( $time % 60 );
		$time    = ( ( $time - $seconds ) / 60 );
		$minutes = ( $time % 60 );
		$time    = ( ( $time - $minutes ) / 60 );
		$hours   = ( $time % 24 );
		$time    = ( ( $time - $hours ) / 24 );
		$days    = $time;

		$array = array();
		if ( $seconds > 0 ) {
			$array[] = $seconds . 's';
		}
		if ( $minutes > 0 ) {
			$array[] = $minutes . 'm';
		}
		if ( $hours > 0 ) {
			$array[] = $hours . 'h';
		}
		if ( $days > 0 ) {
			$array[] = $days . 'd';
		}
		$array = array_reverse( $array );
		$text  = implode( ' ', $array );
		return $text;
	}

	function formatPercentage( $value, $options = array() ) {
		if ( is_bool( $options ) ) {
			$options = array( 'symbol' => $options );
		}
		$defaults = array( 'symbol' => true );
		$options  = $this->parseArgs( $options, $defaults );

		$value = floatval( $value );
		$value = round( $value, 3 );
		$value = number_format( $value, 3, ',', '' );
		if ( $options['symbol'] ) {
			$value .= ' %';
		}
		return $value;
	}
	function formatCurrencyMoney( $value, $options = array() ) {
		$defaults = array( 'currency' => $this->getDefaultCurrencySymbol() );
		$options  = $this->parseArgs( $options, $defaults );

		$value = $this->formatMoney( $value, $options );
		return $value;
	}
	function formatMoney( $value, $options = array() ) {
		if ( is_string( $options ) ) {
			$options = array( 'currency' => $options );
		}
		$defaults = array( 'currency' => false );
		$options  = $this->parseArgs( $options, $defaults );

		$value = floatval( $value );
		$value = round( $value, 3 );
		$value = number_format( $value, 3, ',', '.' );
		if ( '' != $options['currency'] ) {
			$symbol = $options['currency'];
			if ( strlen( $symbol ) > 1 ) {
				$symbol = $this->getCurrencySymbol( $symbol );
			}
			$value .= ' ' . $symbol;
		}
		return $value;
	}
	function sortOptions( &$options ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}

		usort( $options, array( $this, 'sortOptions_Compare' ) );
		return $options;
	}
	public function sortOptions_Compare( $o1, $o2 ) {
		global $tcmp;
		$v1 = $tcmp->utils->get( $o1, 'text', false );
		if ( false == $v1 ) {
			$v1 = $tcmp->utils->get( $o1, 'name', false );
		}
		$v2 = $tcmp->utils->get( $o2, 'text', false );
		if ( false == $v2 ) {
			$v2 = $tcmp->utils->get( $o2, 'name', false );
		}

		//to order properly
		if ( $tcmp->utils->starts_with( $v1, '[' ) ) {
			$v1 = ' ' . $v1;
		}
		if ( $tcmp->utils->starts_with( $v2, '[' ) ) {
			$v2 = ' ' . $v2;
		}
		return strcasecmp( $v1, $v2 );
	}

	private function validate_ip( $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}
		return '';
	}

	public function getVisitorIpAddress() {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = validate_ip( $_SERVER['HTTP_CLIENT_IP'] );
		}

		if ( '' == $ip && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = validate_ip( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		}

		if ( '' == $ip ) {
			$ip = validate_ip( $_SERVER['REMOTE_ADDR'] );
		}
		return $ip;
	}

	function getCurrencySymbol( $currency ) {
		// Create a NumberFormatter
		$locale    = 'en_US';
		$formatter = new NumberFormatter( $locale, NumberFormatter::CURRENCY );

		// Figure out what 0.00 looks like with the currency symbol
		$withCurrency = $formatter->formatCurrency( 0, $currency );

		// Figure out what 0.00 looks like without the currency symbol
		$formatter->setPattern( str_replace( '¤', '', $formatter->getPattern() ) );
		$without_currency = $formatter->formatCurrency( 0, $currency );

		// Extract just the currency symbol from the first string
		return str_replace( $without_currency, '', $withCurrency );
	}
	function encodeUri( $string ) {
		$entities     = array( '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D' );
		$replacements = array( '!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '#', '[', ']' );
		$result       = urlencode( $string );
		//$result=str_replace($replacements, $entities, $result);
		return $result;
	}

	public function formatTimer( $time ) {
		if ( ! is_int( $time ) ) {
			if ( is_string( $time ) ) {
				$time = str_replace( ' ', ':', $time );
				$time = str_replace( '.', ':', $time );
				$time = str_replace( '/', ':', $time );
				$time = explode( ':', $time );

				$length  = count( $time );
				$days    = 0;
				$hours   = 0;
				$minutes = 0;
				$secs    = intval( $time[ $length - 1 ] );

				if ( $length > 1 ) {
					$minutes = intval( $time[ $length - 2 ] );
					if ( $length > 2 ) {
						$hours = intval( $time[ $length - 3 ] );
						if ( $length > 3 ) {
							$days = intval( $time[ $length - 4 ] );
						}
					}
				}
				$time = $days * 86400 + $hours * 3600 + $minutes * 60 + $secs;
			} else {
				$time = 0;
			}
		} else {
			$time = intval( $time );
		}

		$secs    = $time % 60;
		$time    = ( $time - $secs ) / 60;
		$minutes = $time % 60;
		$time    = ( $time - $minutes ) / 60;
		$hours   = $time % 24;
		$days    = ( $time - $hours ) / 24;

		$result   = array();
		$result[] = $days;
		$result[] = ( $hours < 10 ? '0' : '' ) . $hours;
		$result[] = ( $minutes < 10 ? '0' : '' ) . $minutes;
		$result[] = ( $secs < 10 ? '0' : '' ) . $secs;
		$result   = implode( ':', $result );
		return $result;
	}
	public function parseTimer( $time ) {
		$time   = $this->formatTimer( $time );
		$time   = explode( ':', $time );
		$result = intval( $time[0] ) * 86400 + intval( $time[1] ) * 3600 + intval( $time[2] ) * 60 + intval( $time[3] );
		return $result;
	}
}
