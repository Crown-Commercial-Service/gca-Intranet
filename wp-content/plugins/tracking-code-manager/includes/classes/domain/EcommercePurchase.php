<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class TCMP_EcommercePurchase {
	var $order_id;
	var $currency;

	var $user_id;
	var $fullname;
	var $email;

	var $products = array();

	var $amount;
	var $total;
	var $tax;
}
