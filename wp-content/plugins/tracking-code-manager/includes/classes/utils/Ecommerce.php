<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TCMP_Ecommerce {
	var $order_id;

	function __construct() {
		add_action( 'woocommerce_thankyou', array( &$this, 'wooCommerceThankYou' ), -10 );
		//add_action('woocommerce_thankyou_order_id', array(&$this, 'wooCommerceThankYou'), -10);

		add_action( 'edd_payment_receipt_after_table', array( &$this, 'eddThankYou' ) );
		add_action( 'wpsc_transaction_result_cart_item', array( &$this, 'eCommerceThankYou' ) );
	}

	public function getCustomPostType( $pluginId ) {
		$result = '';
		switch ( intval( $pluginId ) ) {
			case TCMP_PLUGINS_WOOCOMMERCE:
				$result = 'product';
				break;
			case TCMP_PLUGINS_EDD:
				$result = 'download';
				break;
			case TCMP_PLUGINS_WP_ECOMMERCE:
				$result = 'wpsc-product';
				break;
		}
		return $result;
	}

	//WPSC_Purchase_Log_Customer_HTML_Notification
	function eCommerceThankYou( $order ) {
		global $tcmp;
		$purchase = new TCMP_EcommercePurchase();

		$order_id           = intval( $order['purchase_id'] );
		$purchase->order_id = $order_id;
		$tcmp->log->debug( 'Ecommerce: ECOMMERCE THANKYOU' );
		$tcmp->log->debug( 'Ecommerce: NEW ECOMMERCE ORDERID=%s', $order_id );

		$order       = new WPSC_Purchase_Log( $order_id );
		$items       = $order->get_cart_contents();
		$productsIds = array();
		foreach ( $items as $v ) {
			if ( isset( $v->prodid ) ) {
				$k = intval( $v->prodid );
				if ( $k ) {
					$v                    = $v->name;
					$purchase->products[] = $v;
					$productsIds[]        = $k;
					$tcmp->log->debug( 'Ecommerce: ITEM %s=%s IN CART', $k, $v );
				}
			}
		}

		$args = array(
			'pluginId'      => TCMP_PLUGINS_WP_ECOMMERCE,
			'productsIds'   => $productsIds,
			'categoriesIds' => array(),
			'tagsIds'       => array(),
		);
		$tcmp->options->pushConversionSnippets( $args, $purchase );
		return '';
	}

	function eddThankYou( $payment, $edd_receipt_args = null ) {
		global $tcmp;
		if ( ! class_exists( 'EDD_Customer' ) ) {
			return;
		}

		/* @var $payment WP_Post */
		$purchase          = new TCMP_EcommercePurchase();
		$purchase->order_id = $tcmp->utils->get( $payment, 'ID' );
		$purchase->user_id  = $tcmp->utils->get( $payment, 'post_author', false );

		$settings = edd_get_settings();
		if ( isset( $settings['currency'] ) ) {
			$purchase->currency = $settings['currency'];
		}

		$tcmp->log->debug( 'Ecommerce: EDD THANKYOU' );
		$tcmp->log->debug( 'Ecommerce: NEW EDD ORDERID=%s', $purchase->order_id );
		$cart             = edd_get_payment_meta_cart_details( $purchase->order_id, true );
		$productsIds      = array();
		$purchase->amount = 0;
		$purchase->total  = 0;
		foreach ( $cart as $key => $item ) {
			if ( isset( $item['id'] ) ) {
				$k = intval( $item['id'] );
				if ( $k ) {
					$v                    = $item['name'];
					$purchase->products[] = $v;
					$productsIds[]        = $k;
					$tcmp->log->debug( 'Ecommerce: ITEM %s=%s IN CART', $k, $v );
				}
			}
		}

		$args = array(
			'pluginId'      => TCMP_PLUGINS_EDD,
			'productsIds'   => $productsIds,
			'categoriesIds' => array(),
			'tagsIds'       => array(),
		);
		$tcmp->options->pushConversionSnippets( $args, $purchase );
	}
	function wooCommerceThankYou( $order_id ) {
		global $tcmp;
		if ( ! $order_id ) {
			return;
		}
		if ( $this->order_id === $order_id ) {
			return;
		}

		$this->order_id    = $order_id;
		$purchase          = new TCMP_EcommercePurchase();
		$purchase->order_id = $order_id;
		$tcmp->log->debug( 'Ecommerce: WOOCOMMERCE THANKYOU' );

		$order              = new WC_Order( $order_id );
		$purchase->email    = $order->get_billing_email();
		$purchase->fullname = $order->get_billing_first_name();
		if ( $order->get_billing_last_name() != '' ) {
			$purchase->fullname .= ' ' . $order->get_billing_last_name();
		}

		$items = $order->get_items();
		$tcmp->log->debug( 'Ecommerce: NEW WOOCOMMERCE ORDERID=%s', $order_id );
		$productsIds = array();
		foreach ( $items as $k => $v ) {
			$k = intval( $v['product_id'] );
			if ( $k > 0 ) {
				$v                    = $v['name'];
				$purchase->products[] = $v;
				$tcmp->log->debug( 'Ecommerce: ITEM %s=%s IN CART', $k, $v );
				$productsIds[] = $k;
			}
		}

		$args = array(
			'pluginId'      => TCMP_PLUGINS_WOOCOMMERCE,
			'productsIds'   => $productsIds,
			'categoriesIds' => array(),
			'tagsIds'       => array(),
		);
		$tcmp->options->pushConversionSnippets( $args, $purchase );
	}

	function getActivePlugins() {
		return $this->getPlugins( true );
	}
	function getPlugins( $onlyActive = true ) {
		global $tcmp;

		$array   = array();
		$array[] = TCMP_PLUGINS_WOOCOMMERCE;
		$array[] = TCMP_PLUGINS_EDD;
		$array[] = TCMP_PLUGINS_WP_ECOMMERCE;
		/*
		$array[]=TCMP_PLUGINS_WP_SPSC;
		$array[]=TCMP_PLUGINS_S2MEMBER;
		$array[]=TCMP_PLUGINS_MEMBERS;
		$array[]=TCMP_PLUGINS_CART66;
		$array[]=TCMP_PLUGINS_ESHOP;
		$array[]=TCMP_PLUGINS_JIGOSHOP;
		$array[]=TCMP_PLUGINS_MARKETPRESS;
		$array[]=TCMP_PLUGINS_SHOPP;
		$array[]=TCMP_PLUGINS_SIMPLE_WP_ECOMMERCE;
		$array[]=TCMP_PLUGINS_CF7;
		$array[]=TCMP_PLUGINS_GRAVITY;
		*/

		$array = $tcmp->plugin->getPlugins( $array, $onlyActive );
		return $array;
	}
}
