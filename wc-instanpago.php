<?php
/*
Plugin Name: Instapago Gateway - WooCommerce Gateway
Plugin URI: http://www.gerswin.com/
Description: Extends WooCommerce by Adding the Instapago Gateway.
Version: 1.0
Author: gerswin pineda - parawebs
Author URI: http://www.gerswin.com/
 */

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'instapago_init', 0);
function instapago_init() {
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}

	// If we made it this far, then include our Gateway Class
	include_once 'wc-instanpago-aim.php';

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter('woocommerce_payment_gateways', 'add_instapago_aim_gateway');
	function add_instapago_aim_gateway($methods) {
		$methods[] = 'InstaPago_AIM';
		return $methods;
	}
}

// Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'instapago_aim_action_links');
function instapago_aim_action_links($links) {
	$plugin_links = array(
		'<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'instapago_aim') . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge($plugin_links, $links);
}

add_filter('woocommerce_currencies', 'add_my_currency');

function add_my_currency($currencies) {
	$currencies['VEF'] = __('Bolivares', 'woocommerce');
	return $currencies;
}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol($currency_symbol, $currency) {
	switch ($currency) {
	case 'VEF':$currency_symbol = 'Bs';
		break;
	}
	return $currency_symbol;
}

add_filter('woocommerce_checkout_fields', 'custom_override_billing_fields');

// Our hooked in function - $fields is passed via the filter!
function custom_override_billing_fields($fields) {

	//unset($fields['billing']['billing_first_name']);
	//unset($fields['billing']['billing_last_name']);
	//unset($fields['billing']['billing_company']);
	//unset($fields['billing']['billing_address_2']);
	//unset($fields['billing']['billing_postcode']);
	//unset($fields['billing']['billing_company']);
	//unset($fields['billing']['billing_last_name']);
	//unset($fields['billing']['billing_city']);
	return $fields;

}

add_action('woocommerce_thankyou', 'myfunction');
function myfunction($order_id) {
	//echo 'My message ' . $order_id;
	echo html_entity_decode(wc_get_order_item_meta($order_id, '_voucher'));

}
