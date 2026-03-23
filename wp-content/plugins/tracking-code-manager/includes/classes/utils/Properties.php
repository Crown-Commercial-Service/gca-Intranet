<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TCMP_Properties {
	var $data;
	var $autoPush;

	function __construct() {
		$this->data     = array();
		$this->autoPush = true;
	}

	public function hasKeys() {
		return ( count( $this->data ) > 0 );
	}
	public function existsKey( $key ) {
		return ( isset( $this->data[ $key ] ) && '' != $this->data[ $key ] );
	}

	public function load( $file ) {
		$bundle = array();
		if ( ! file_exists( $file ) ) {
			return $bundle;
		}
		$file = file_get_contents( $file );
		if ( null != $file && strlen( $file ) > 0 ) {
			$file = str_replace( "\r\n", "\n", $file );
			$file = str_replace( "\n\n", "\n", $file );
			$file = explode( "\n", $file );

			foreach ( $file as $row ) {
				$index = strpos( $row, '=' );
				if ( false == $index ) {
					continue;
				}

				$k = trim( substr( $row, 0, $index ) );
				$v = trim( substr( $row, $index + 1 ) );
				if ( '' != $v ) {
					$bundle[ $k ] = $v;
				}
			}
		}
		$this->data = $bundle;
		return $bundle;
	}
	public function store( $file ) {
		ksort( $this->data );
		$buffer = '';
		foreach ( $this->data as $k => $v ) {
			if ( '' != $buffer ) {
				$buffer .= "\r\n";
			}
			$buffer .= $k . '=' . $v;
		}
		$bytes = file_put_contents( $file, $buffer );
		return ( $bytes > 0 );
	}

	public function pushValue( $t, $k, $v ) {
		$v                = $this->encode( $t, $v );
		$this->data[ $k ] = $v;
	}
	public function pushString( $k, $v ) {
		$this->pushValue( 's', $k, $v );
	}
	public function pushBoolean( $k, $v ) {
		$this->pushValue( 'b', $k, $v );
	}
	public function pushInt( $k, $v ) {
		$this->pushValue( 'i', $k, $v );
	}
	public function pushFloat( $k, $v ) {
		$this->pushValue( 'f', $k, $v );
	}
	public function pushDate( $k, $v ) {
		$this->pushValue( 'd', $k, $v );
	}
	public function pushArray( $k, $v ) {
		$this->pushValue( 'a', $k, $v );
	}
	public function pushAssocArray( $k, $v ) {
		$this->pushValue( 'aa', $k, $v );
	}

	private function encode( $t, $v ) {
		global $tcmp;
		switch ( strtolower( $t ) ) {
			case 's':
				break;
			case 'i':
				$v = intval( $v );
				break;
			case 'f':
				$v = round( floatval( $v ), 2 );
				break;
			case 'd':
				$v = $tcmp->utils->formatDatetime( $v );
				break;
			case 'b':
				$v = ( $tcmp->utils->isTrue( $v ) ? 'true' : 'false' );
				break;
			case 'a':
				$v = $tcmp->utils->to_array( $v );
				$v = implode( '|', $v );
				break;
			case 'aa':
				$array  = $tcmp->utils->to_array( $v );
				$buffer = '';
				foreach ( $array as $k => $v ) {
					if ( '' != $buffer ) {
						$buffer .= '|';
					}
					$buffer .= $k . '=' . $v;
				}
				$v = $array;
				break;
		}
		return $v;
	}
	private function decode( $t, $v ) {
		global $tcmp;
		$v = trim( $v );
		switch ( strtolower( $t ) ) {
			case 's':
				break;
			case 'i':
				$v = intval( $v );
				break;
			case 'f':
				$v = round( floatval( $v ), 2 );
				break;
			case 'd':
				$v = $tcmp->utils->parse_date_to_time( $v );
				break;
			case 'b':
				$v = $tcmp->utils->isTrue( $v );
				break;
			case 'a':
				$v = $tcmp->utils->to_array( $v );
				break;
			case 'aa':
				$v     = $tcmp->utils->to_array( $v );
				$array = implode( '|', $v );
				$t     = array();
				foreach ( $array as $v ) {
					$v = explode( '=', $v );
					if ( 1 == count( $v ) ) {
						$v[] = '';
					}
					$t[ trim( $v[0] ) ] = trim( $v[1] );
				}
				$v = $t;
				break;
		}
		return $v;
	}

	protected function getValue( $t, $key, $default ) {
		$v = $default;
		if ( isset( $this->data[ $key ] ) ) {
			$v = $this->data[ $key ];
		} elseif ( $this->autoPush ) {
			$this->pushValue( $t, $key, $default );
		}
		$v = $this->decode( $t, $v );
		return $v;
	}
	public function getString( $key, $default = '' ) {
		return $this->getValue( 's', $key, $default );
	}
	public function getFile( $key, $default = false ) {
		global $tcmp;
		$file = $this->getString( $key, $default );
		if ( $file !== $default && '' !== $file && false !== $file ) {
			if ( ! file_exists( $file ) ) {
				$file = $default;
			}
		}
		if ( is_dir( $file ) ) {
			$file = str_replace( '\\', DIRECTORY_SEPARATOR, $file );
			$file = str_replace( '/', DIRECTORY_SEPARATOR, $file );
			if ( ! $tcmp->utils->endsWith( $file, DIRECTORY_SEPARATOR ) ) {
				$file .= DIRECTORY_SEPARATOR;
			}
		}
		return $file;
	}
	public function getInt( $key, $default = 0 ) {
		return $this->getValue( 'i', $key, $default );
	}
	public function getFloat( $key, $default = 0 ) {
		return $this->getValue( 'f', $key, $default );
	}
	public function getBoolean( $key, $default = false ) {
		return $this->getValue( 'b', $key, $default );
	}
	public function getDate( $key, $default = false ) {
		return $this->getValue( 'd', $key, $default );
	}
	public function getArray( $key, $default = false ) {
		return $this->getValue( 'a', $key, $default );
	}
	public function getAssocArray( $key, $default = false ) {
		return $this->getValue( 'aa', $key, $default );
	}
}
