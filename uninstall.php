<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 *
 * @link       https://netleon.com/
 * @since      1.0.0
 *
 * @package    MyWallet For WooCommerce
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
$my_wallet_data_delete_confirmation = get_option('my_wallet_data_delete_confirmation');
if ($my_wallet_data_delete_confirmation == "true") {
	$tables = ['my_wallet_transaction', 'my_wallet_coupon', 'my_wallet_meta_coupon'];
	global $wpdb;
	foreach ($tables as $table) {
		$table = $wpdb->prefix . $table . my_wallet_get_suffix();
		$wpdb->query('DROP TABLE IF EXISTS ' . $table);
	}


	$all_user_ids = get_users('fields=ID');
	// Delete my_wallet and my_wallet_active records from user meta table.
	foreach ($all_user_ids as $user_id) {
		delete_user_meta($user_id, 'my_wallet');
		delete_user_meta($user_id, 'my_wallet_active');
	}

	delete_option('my_wallet_lcns_key');
	delete_option('my_wallet_lcns_status');
	delete_option('my_wallet_data_delete_confirmation');

	// Delete payment gateway settings and secret keys.
	$active_gateways = get_my_wallet_payment_gateways();
	foreach ($active_gateways as $gateway) {
		$payment_gateway = 'mywallet_' . $gateway->id;
		delete_option($payment_gateway);

		$payment_gateway_secret_data = 'My_wallet_' . $gateway->id;
		delete_option($payment_gateway_secret_data);
	}

	// deleting payment gateways from db.
	delete_option('my_wallet_supported_payment_gateways');
}
