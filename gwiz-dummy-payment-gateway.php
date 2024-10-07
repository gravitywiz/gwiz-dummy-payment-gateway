<?php
/*
 * Plugin Name:  Gravity Forms Dummy Payment Gateway
 * Plugin URI:   http://gravitywiz.com
 * Description:  A dummy payment gateway for testing Gravity Forms add-ons.
 * Author:       Gravity Wiz
 * Version:      1.0-beta-1.0
 * Author URI:   http://gravitywiz.com
 */

defined( 'ABSPATH' ) || die();

define( 'GWIZ_DUMMY_PAYMENT_GATEWAY_VERSION', '1.0-beta-1.0' );

add_action( 'gform_loaded', function() {
	if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'class-gwiz-dummy-payment-gateway.php';

	GFAddOn::register( 'GWiz_Dummy_Payment_Gateway' );
}, 5 );
