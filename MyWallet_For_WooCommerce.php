<?php

/**
 * @wordpress-plugin
 * Plugin Name:       MyWallet For WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/mywallet-for-woocommerce
 * Description:       MyWallet System For WooCommerce is the plugin that facilitates WooCommerce store owners to provide e-wallet functionalities.
 * Version:           1.0.0
 * Author:            Netleon Technologies Private Limited
 * Author URI:        https://wordpress.org/plugins/mywallet-for-woocommerce
 * Text Domain:       my-wallet-for-woocommerce
 * Domain Path:       /languages
 *
 * License:           GNU General Public License v3.0
 */

use ParagonIE\Sodium\Core\Curve25519\Ge\P2;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
	die;
}

// Check if woocommerce is installed and active.
$activated = true;
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	$activated = false;
}


//code to show settings hyperlink in plugin section.
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'my_plugin_settings_link');
function my_plugin_settings_link($links)
{
	$my_wallet_is_license_valid = get_option('my_wallet_lcns_status');
	if ($my_wallet_is_license_valid) {
		$settings_link = '<a href="admin.php?page=my_wallet_menu">Settings</a>';
	} else {
		$settings_link = '<a href="admin.php?page=my_wallet_activation">Settings</a>';
	}
	array_unshift($links, $settings_link);
	return $links;
}

// if woocommerce is activated.
if ($activated) {
	require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

	//admin specific code
	if (is_admin()) {

		/**
		 * Define plugin admin side constants.
		 *
		 * @since             1.0.0
		 */
		function define_my_wallet_admin_constants()
		{
			my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_DIR_URL', plugin_dir_url(__FILE__));
			my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_CSS_URL', plugin_dir_url(__FILE__) . "admin/css/");
			my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_JS_URL', plugin_dir_url(__FILE__) . "admin/js/");
		}

		//to define constants
		function my_wallet_woocommerce_constants($key, $value)
		{
			if (!defined($key)) {
				define($key, $value);
			}
		}

		// calling function to define constants.
		define_my_wallet_admin_constants();

		/**
		 * Adding menu for MyWallet.
		 *
		 * @since    1.0.0
		 */
		add_action("admin_menu", "add_my_wallet_menu");
		function add_my_wallet_menu()
		{
			$my_wallet_is_license_valid = get_option('my_wallet_lcns_status');
			//if license is not valid than redirect to license registration page;
			if ($my_wallet_is_license_valid) {
				add_menu_page(
					"MyWallet",
					"MyWallet",
					"manage_options",
					"my_wallet_menu",
					"manage_payment_gateway",
					'dashicons-money-alt',
					30
				);

				add_submenu_page(
					null,
					'Transaction details', //page title
					'Transaction details', //menu title
					'manage_options', //capability,
					'transaction-detail-page', //menu slug
					'get_transaction_detail_page' //callback function
				);

				add_submenu_page(
					'my_wallet_menu',
					'Coupons', //page title
					'Coupons', //menu title
					'manage_options', //capability,
					'my_wallet_coupons', //menu slug
					'my_wallet_coupons' //callback function
				);

				add_submenu_page(
					'my_wallet_menu',
					'Users', //page title
					'Users', //menu title
					'manage_options', //capability,
					'my_wallet_users', //menu slug
					'my_wallet_users_screen' //callback function
				);

				add_submenu_page(
					'my_wallet_menu',
					'Transactions', //page title
					'Transactions', //menu title
					'manage_options', //capability,
					'my_wallet_list_all_transactions', //menu slug
					'my_wallet_list_all_transactions' //callback function
				);

				add_submenu_page(
					null,
					'User Transaction details', //page title
					'User Transaction details', //menu title
					'manage_options', //capability,
					'user-transaction-detail-page', //menu slug
					'user_transaction_detail_page' //callback function
				);

				add_submenu_page(
					null,
					'Coupon Detail Page', //page title
					'Coupon Detail Page', //menu title
					'manage_options', //capability,
					'my-wallet-coupon-detail-page', //menu slug
					'my_wallet_coupon_detail_page' //callback function
				);

				add_submenu_page(
					null,
					'User Detail Page', //page title
					'User Detail Page', //menu title
					'manage_options', //capability,
					'my-wallet-user-detail-page', //menu slug
					'my_wallet_user_detail_page' //callback function
				);
			} else {
				add_menu_page(
					"MyWallet",
					"MyWallet",
					"manage_options",
					"my_wallet_activation",
					"my_wallet_activation",
					'dashicons-money-alt',
					30
				);
			}
		}

		// Update CSS within in Admin
		add_action('admin_enqueue_scripts', 'admin_style');
		function admin_style()
		{
			wp_enqueue_style('admin-styles', MY_WALLET_WOOCOMMERCE_CSS_URL . 'my-wallet-admin.css');
		}

		// showing alert bar on top of all windows if mywallet plugin not activated using license key.
		$my_wallet_is_license_valid = my_wallet_is_license_valid();
		if (!$my_wallet_is_license_valid) {
			register_activation_hook(__FILE__, 'fx_admin_notice_example_activation_hook');
			function fx_admin_notice_example_activation_hook()
			{
				set_transient('fx-admin-notice-example', true, 5);
			}

			add_action('admin_notices', 'fx_admin_notice_example_notice');
			function fx_admin_notice_example_notice()
			{
				$my_wallet_is_license_valid = my_wallet_is_license_valid();
				/* Check transient, if available display notice */
				if ($my_wallet_is_license_valid && get_transient('fx-admin-notice-example')) {
?>
					<div class="updated notice is-dismissible">
						<p>Thank you for using MyWallet! <strong>You are awesome</strong>.</p>
					</div>
				<?php
				} else {
				?>
					<div class="wrap">
						<div class="notice-error notice is-dismissible">
							<p>In order to use plugin you need to activate the plugin using license key. Click
								<a href="https://netleon.com/plugin/auth" target="blank">here</a> to get license key.
							</p>
						</div>
					</div>
					<?php
				}
				/* Delete transient, only display this notice once. */
				delete_transient('fx-admin-notice-example');
			}
		}

		/**
		 * The code that runs to manage license key validation screen of mywallet plugin.
		 */
		function my_wallet_activation()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-activation-page.php';
		}

		/**
		 * The code that runs to manage payment gateways(enabling or disabling) screen of mywallet plugin.
		 * This action is documented in includes/class-my-wallet-manage-payment-gateway.php
		 */
		function manage_payment_gateway()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-manage-payment-gateway.php';
			new My_Wallet_Manage_Payment_Gateway();
		}

		/**
		 * The code that runs to manage coupon screen of mywallet plugin.
		 * This action is documented in includes/class-my-wallet-coupon-screen.php
		 */
		function my_wallet_coupons()
		{
			$msg = null;
			$msg_type = null;
			if (isset($_POST['add_coupon'])) {
				if (isset($_POST['coupon_name']) && !empty($_POST['coupon_name']) && isset($_POST['amount']) && !empty($_POST['amount'])) {
					$coupon_name = sanitize_text_field( wp_unslash($_POST['coupon_name']));
					$coupon_amount_value = sanitize_text_field( wp_unslash($_POST['amount']));

					try {

						$coupon_usability = "";
						if ((isset($_POST['usability']) && !empty($_POST['usability'])) || $_POST['usability'] == "0") {
							$coupon_usability = sanitize_text_field( wp_unslash($_POST['usability']));
							if ($coupon_usability < 1) {
								throw new Exception("Usability could be either greater than 0 or Empty [For all users.]");
							}
						} else {
							$coupon_usability = "Unlimited";
						}

						$specific_user = "";
						if (isset($_POST['specific_user']) && !empty($_POST['specific_user'])) {
							$specific_user = sanitize_text_field( wp_unslash($_POST['specific_user']));
						}

						$valid_from = "";
						if (isset($_POST['valid_from']) && !empty($_POST['valid_from'])) {
							$valid_from = sanitize_text_field( wp_unslash($_POST['valid_from']));
						}

						$valid_to = "";
						if (isset($_POST['valid_to']) && !empty($_POST['valid_to'])) {
							$valid_to = sanitize_text_field( wp_unslash($_POST['valid_to']));
						}

						$user_email_ids = "All";
						if ($specific_user == 'on') {
							$user_email_ids = array();
							$email_ids = sanitize_text_field( wp_unslash($_POST['user_ids']));
							if ($email_ids != null || $email_ids != "") {
								$email_ids = explode(',', $email_ids);
								foreach ($email_ids as $email_id) {
									$email_id = trim($email_id);
									array_push($user_email_ids, $email_id);
								}
								$user_email_ids = json_encode($user_email_ids);
							} else {
								$user_email_ids = "All";
							}
						}

						if ($valid_from != "" && $valid_to != "") {
							if (strtotime($valid_from) > strtotime($valid_to)) {
								throw new Exception("Start date is later than end date.");
							}
						}

						$is_inserted = my_wallet_insert_coupon($coupon_name);
						if ($is_inserted) {
							$coupon_id = my_wallet_get_coupon_id_by_name($coupon_name);
							update_coupon_meta($coupon_id, "amount", $coupon_amount_value);
							update_coupon_meta($coupon_id, "usability", $coupon_usability);
							update_coupon_meta($coupon_id, "redeemption_count", 0);
							update_coupon_meta($coupon_id, "already_used_by_users", "");
							update_coupon_meta($coupon_id, "coupon_for_users", $user_email_ids);
							update_coupon_meta($coupon_id, "is_active", 1);
							update_coupon_meta($coupon_id, "valid_from", $valid_from);
							update_coupon_meta($coupon_id, "valid_to", $valid_to);
							$msg = "Coupon added successfully.";
							$msg_type = "success";
						} else {
							throw new Exception("Coupon not added due to some problem.");
						}
					} catch (Exception $e) {
						$msg = $e->getMessage();
						$msg_type = "error";
					}
				} else {
					$msg = "Oops ! Please enter all required details.";
					$msg_type = "error";
				}
			}

			if (isset($_POST['update_coupon'])) {
				if (isset($_POST['coupon_name']) && !empty($_POST['coupon_name']) && isset($_POST['amount']) && !empty($_POST['amount'])) {
					$coupon_id = sanitize_text_field( wp_unslash($_POST['coupon_id']));
					$coupon_name = sanitize_text_field( wp_unslash($_POST['coupon_name']));
					$coupon_amount_value = sanitize_text_field( wp_unslash($_POST['amount']));
					$coupon_usability = sanitize_text_field( wp_unslash($_POST['usability']));
					if ($coupon_usability == "" || $coupon_usability == null) {
						$coupon_usability = "Unlimited";
					}
					$specific_user = "";
					if (isset($_POST['specific_user']) && !empty($_POST['specific_user'])) {
						$specific_user = sanitize_text_field( wp_unslash($_POST['specific_user']));
					}
					$valid_from = sanitize_text_field( wp_unslash($_POST['valid_from']));
					$valid_to = sanitize_text_field( wp_unslash($_POST['valid_to']));
					$email_ids = 'All';
					if ($specific_user == 'on') {
						$user_email_ids = array();
						$email_ids = sanitize_text_field( wp_unslash($_POST['user_ids']));
						if ($email_ids != null || $email_ids != "") {
							$email_ids = explode(',', $email_ids);
							foreach ($email_ids as $email_id) {
								$email_id = trim($email_id);
								array_push($user_email_ids, $email_id);
							}
							$email_ids = json_encode($user_email_ids);
						} else {
							$email_ids = "All";
						}
					}
					try {
						if ($valid_from != "" && $valid_to != "") {
							if (strtotime($valid_from) > strtotime($valid_to)) {
								throw new Exception("Start date is later than end date.");
							}
						}
						update_coupon($coupon_name, $coupon_id);
						update_coupon_meta($coupon_id, "amount", $coupon_amount_value);
						update_coupon_meta($coupon_id, "usability", $coupon_usability);
						update_coupon_meta($coupon_id, "coupon_for_users", $email_ids);
						update_coupon_meta($coupon_id, "valid_from", $valid_from);
						update_coupon_meta($coupon_id, "valid_to", $valid_to);
						$msg = "Coupon updated successfully.";
						$msg_type = "success";
					} catch (Exception $e) {
						$msg = $e->getMessage();
						$msg_type = "error";
					}
				} else {
					$msg = "Oops ! Please enter all required details.";
					$msg_type = "error";
				}
			}

			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-coupon-screen.php';
			new My_Wallet_Coupon_Screen($msg, $msg_type);
		}


		/**
		 * The code that runs to manage user screen of mywallet plugin.
		 * This action is documented in includes/class-my-wallet-user-screen.php
		 */
		function my_wallet_users_screen()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-user-screen.php';
			if (isset($_POST['search']) && !empty($_POST['search_text'])) {
				$type = sanitize_text_field( wp_unslash($_POST['search_type']));
				new My_Wallet_User_Screen($type);
			} else {
				new My_Wallet_User_Screen();
			}
		}

		/**
		 * The code that runs to display all transactions of specific user mywallet plugin.
		 * This action is documented in includes/class-my-wallet-list-all-transactions.php
		 */
		function my_wallet_list_all_transactions()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-list-all-transactions.php';
			if (isset($_POST['search']) && !empty($_POST['search_text'])) {
				$type = sanitize_text_field( wp_unslash($_POST['search_type']));
				new My_Wallet_List_All_Transaction_Screen($type);
			} else {
				new My_Wallet_List_All_Transaction_Screen();
			}
		}


		/**
		 * The code that runs to display all details of a transaction.
		 * This action is documented in includes/class-my-wallet-transaction-detail-screen.php
		 */
		function get_transaction_detail_page()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-transaction-detail-screen.php';
			$transaction_id = sanitize_text_field( wp_unslash($_GET['transaction_id']));
			$user_id = sanitize_text_field( wp_unslash($_GET['user_id']));
			new My_Wallet_Transaction_Detail_Screen($user_id, $transaction_id);
		}

		/**
		 * The code that runs to display all transactions of a specific user.
		 * This action is documented in includes/class-my-wallet-user-list-all-transactions.php
		 */
		function user_transaction_detail_page()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-user-list-all-transactions.php';
			new My_Wallet_User_List_All_Transaction_Screen();
		}

		function my_wallet_coupon_detail_page()
		{
			if (isset($_GET['coupon_id'])) {
				$coupon_id = sanitize_text_field( wp_unslash($_GET['coupon_id']));
				require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-coupon_detail_page.php';
				if (isset($_POST['search']) && !empty($_POST['search_text'])) {
					$type = sanitize_text_field( wp_unslash($_POST['search_type']));
					new My_wallet_coupon_detail($coupon_id, $type);
				} else {
					new My_wallet_coupon_detail($coupon_id);
				}
			}
		}

		function my_wallet_user_detail_page()
		{
			if (isset($_GET['user_id'])) {
				$user_id = sanitize_text_field( wp_unslash($_GET['user_id']));
				require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-user-details.php';
				new My_Wallet_User_Detail_Page_Screen($user_id);
			}
		}

		/**
		 * The code that runs during plugin activation.
		 * This action is documented in includes/class-my-wallet-woocommerce-activator.php
		 */
		function activate_my_wallet_for_woocommerce()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-woocommerce-activator.php';
			My_Wallet_Woocommerce_Activator::my_wallet_woocommerce_activate();
			$wsfw_active_plugin = get_option('my_plugins_active', false);
			if (is_array($wsfw_active_plugin) && !empty($wsfw_active_plugin)) {
				$wsfw_active_plugin['my-wallet-woocommerce'] = array(
					'plugin_name' => __('My Wallet For WooCommerce', 'my-wallet-woocommerce'),
					'active'      => '1',
				);
			} else {
				$wsfw_active_plugin = array();
				$wsfw_active_plugin['my-wallet-woocommerce'] = array(
					'plugin_name' => __('My Wallet For WooCommerce', 'my-wallet-woocommerce'),
					'active'      => '1',
				);
			}
			update_option('my_plugins_active', $wsfw_active_plugin);
		}

		/**
		 * The code that runs during plugin deactivation.
		 * This action is documented in includes/class-my-wallet-woocommerce-deactivator.php
		 */
		function deactivate_my_wallet_for_woocommerce()
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class-my-wallet-woocommerce-deactivator.php';
			My_Wallet_Woocommerce_Deactivator::my_wallet_woocommerce_deactivate();
			$mwb_wsfw_deactive_plugin = get_option('my_plugins_active', false);
			if (is_array($mwb_wsfw_deactive_plugin) && !empty($mwb_wsfw_deactive_plugin)) {
				foreach ($mwb_wsfw_deactive_plugin as $mwb_wsfw_deactive_key => $mwb_wsfw_deactive) {
					if ('my-wallet-woocommerce' === $mwb_wsfw_deactive_key) {
						$mwb_wsfw_deactive_plugin[$mwb_wsfw_deactive_key]['active'] = '0';
					}
				}
			}
			update_option('my_plugins_active', $mwb_wsfw_deactive_plugin);
		}

		/*
		 * Admin specific ajax call handling code starts from here. 
		*/

		//This code handles the functionality to enable and disable payment gateway for users.
		function manage_payment_gateway_ajax_fun()
		{
			if (isset($_POST['gatewaysStatus'])) {
				if (isset($_POST['secret_key_array'])) {
					$secret_key_data = sanitize_text_field( wp_unslash(stripslashes(html_entity_decode($_POST['secret_key_array'][0]))));
					$secret_key_obj = (array) json_decode($secret_key_data);
					$array_keys = array_keys($secret_key_obj);
					foreach ($array_keys as $key => $value) {
						$secret_value = $secret_key_obj[$value];
						$secret_key = 'My_wallet_' . $value;
						update_option($secret_key, $secret_value);
					}
				}

				$gatewaysStatus = array_map( 'sanitize_text_field', $_POST['gatewaysStatus'] );
				foreach ($gatewaysStatus as $payment_gateway => $is_checked) {
					$payment_gateway = 'mywallet_' . $payment_gateway;
					$is_checked = $is_checked == "true" ? 1 : 0;
					update_option($payment_gateway, $is_checked);
				}
				
				if(isset($_POST['my_wallet_data_delete_confirmation'])){
					$my_wallet_data_delete_confirmation = sanitize_text_field( wp_unslash($_POST['my_wallet_data_delete_confirmation']));
					update_option('my_wallet_data_delete_confirmation', $my_wallet_data_delete_confirmation);	
				}
				echo "updated";
			} else {
				echo "error";
			}
			wp_die();
		}

		function manage_my_wallet_payment_gateways()
		{
			if (isset($_POST['gateway_id']) && isset($_POST['key']) && isset($_POST['value'])) {
				$gateway_id = sanitize_text_field( wp_unslash($_POST['gateway_id']));
				$key = sanitize_text_field( wp_unslash($_POST['key']));
				$value = sanitize_text_field( wp_unslash($_POST['value']));

				$key_for_db = "My_wallet_" . $gateway_id;
				$key_value_array = json_encode([$key => $value]);
				update_option($key_for_db, $key_value_array);
				echo "key_value_updated";
			} else {
				echo "key_value_updation_failed";
			}
			wp_die();
		}

		//This code handles the functionality to active/deactive mywallet for users.
		function manage_wallet_status_ajax_fun()
		{
			if (isset($_POST['status']) && isset($_POST['id'])) {
				$status = sanitize_text_field( wp_unslash($_POST['status']));
				$user_id = sanitize_text_field( wp_unslash($_POST['id']));
				update_user_meta($user_id, 'my_wallet_active', $status);
				echo "1";
			} else {
				echo "0";
			}
			wp_die();
		}

		// This code handles the functionality to active/deactive coupon.
		function manage_coupon_status_ajax_fun()
		{
			if (isset($_POST['status']) && isset($_POST['id'])) {
				$status = sanitize_text_field( wp_unslash($_POST['status']));
				$user_id = sanitize_text_field( wp_unslash($_POST['id']));
				update_coupon_meta($user_id, 'is_active', $status);
				echo "1";
			} else {
				echo "0";
			}
			wp_die();
		}

		// This code handles the functionality to add amount to user's account.
		function add_amount_to_user_account_ajax_fun()
		{
			if (isset($_POST['user_id']) && isset($_POST['amount']) && $_POST['amount'] > 0) {
				$user_id = sanitize_text_field( wp_unslash($_POST['user_id']));
				$amount = sanitize_text_field( wp_unslash($_POST['amount']));
				$user_balance_before_updation = get_user_meta($user_id, 'my_wallet', true);
				$user_balance_after_updation = $user_balance_before_updation + $amount;
				update_user_meta($user_id, 'my_wallet', abs($user_balance_after_updation));
				$currency = get_woocommerce_currency_symbol();
				global $wpdb;
				$table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
				$wpdb->insert($table_name, array(
					'user_id' => $user_id,
					'amount' => $amount,
					'currency' => $currency,
					'transaction_type' => 'Credited by admin',
					'payment_method' => 'Admin',
					'transaction_id' => get_transaction_id(),
					'date' => date('Y-m-d H:i:s')
				));
				// update transaction table cz admin credited money in user account.
				echo esc_html($user_balance_after_updation);
			} else {
				// echo "0";
			}
			wp_die();
		}

		// This code handles the functionality to get coupon details by coupon id.
		function get_coupon_by_id_ajax_fun()
		{
			if (isset($_POST['coupon_id'])) {
				$coupon_id = sanitize_text_field( wp_unslash($_POST['coupon_id']));
				$coupon_name = my_wallet_get_coupon_name_by_id($coupon_id);
				$result = get_coupon_meta_by_id($coupon_id);
				array_push($result, ["meta_coupon_key" => "coupon_name", "meta_coupon_value" => $coupon_name]);
				return wp_send_json($result);
			} else {
				echo "Wrong";
			}
			wp_die();
		}	

		//server side function - here for temporary purpose
		function validate_key_server_side_function($license_key, $registered_domain)
		{
			//CODE TO HIT OUR SERVER
			$url = "https://netleon.com/plugin/activate.php";
			$body = array(
				'registered_domain' => $registered_domain,
				'purchase_code' => $license_key
			);		
			$args = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'body'        => $body,
			);
			$response = wp_remote_post($url, $args);
			// print_r($response['body']); die;
			// $response_code = wp_remote_retrieve_response_code($response);
			if($response){
				return $response['body'];
			}else{
				return null;
			}
			// $result = null;
			// if($response_code == 200){
			// 	$result = $response['body'];
			// }else{
			// 	echo $response_code;
			// 	die;
			// }
		}

		//this function verifies the license key entered by user and proceed further if the key is right.
		function my_wallet_license_verify()
		{
			if (!wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['my-wallet-license-nonce'])), 'ajax-nonce')) {
				die('Busted!');
			}

			if (isset($_POST['my_wallet_purchase_code']) && !empty($_POST['my_wallet_purchase_code'])) {
				$my_wallet_license_key = sanitize_text_field(wp_unslash($_POST['my_wallet_purchase_code']));
				$registered_domain = !empty($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : home_url();				
				$resp = validate_key_server_side_function($my_wallet_license_key, $registered_domain);
				if($resp == null){
					echo false;
					wp_die();
				}
				$response = json_decode($resp);
				if ($response->status == 'valid') {
					echo create_json_file($resp);
				} else {
					echo false;
				}
			}
			wp_die();
		}

		/**
		 * This funciton responsible for license validation purpose.
		 */
		function create_json_file($response)
		{
			$upload_dir = wp_upload_dir(); // Set upload folder
			// Check folder permission and define file location
			if (wp_mkdir_p($upload_dir['basedir'] . '/mywallet')) { //check here to not flood the server
				//add stream as a json file
				$file = $upload_dir['basedir'] . '/mywallet/response-data.json';
				@mkdir(dirname($file));
				$ret = @file_put_contents($file, $response);
				if ($ret !== false) {
					return 1;
				}
			} else {
				return 0;
			}
		}

		// add_action('pre_user_query', 'change_user_order');

		// function change_user_order($query)
		// {
		// 	$query->query_orderby = ' ORDER BY ID DESC';
		// }

		/**
		 * This function responsible to generate tables dynamically and other validation purposes.
		 */
		function create_tables()
		{
			$upload_dir = wp_upload_dir();
			$file = $upload_dir['basedir'] . '/mywallet/response-data.json';
			$myfile = fopen($file, "r") or die("Unable to open file!");
			$response = json_decode(fread($myfile, filesize($file)));
			fclose($myfile);

			global $wpdb;
			$wpdb_collate = $wpdb->collate;
			$allTables = $response->tables;
			$table_names = array_keys((array)$allTables);
			$queries = array();
			// creating tables from json.
			foreach ($table_names as $table_name) {
				$table_data = $allTables->$table_name; //table complete data
				$table_columns = $table_data->column_name; //table colums with data
				$table_constraints = $table_data->constraint; //table constraints with data
				$column_names = array_keys((array)$table_columns); //All column names of table
				$constraint_names = array_keys((array)$table_constraints); //All constraints names of table
				$table_name_with_wp_prefix = $wpdb->prefix . $table_name;
				$sql = "CREATE TABLE IF NOT EXISTS $table_name_with_wp_prefix ( ";
				//adding colums and attributes
				foreach ($column_names as $column_name) {
					$sql .= $column_name . ' ' . $table_columns->$column_name->type;
					if (isset($table_columns->$column_name->length)) {
						$sql .= '(' . $table_columns->$column_name->length . ') ';
					}
					if (isset($table_columns->$column_name->attribute)) {
						$sql .= ' ' . $table_columns->$column_name->attribute . ' ';
					}
					$sql .= ', ';
				}

				//adding constraint to query
				foreach ($constraint_names as $constraint_name) {
					$sql .= '' . $constraint_name . ' ' . $table_constraints->$constraint_name;
					$sql .= ', ';
				}
				$sql = rtrim($sql, ", ");
				$sql .= ' ) ';
				$sql .= 'COLLATE ' . $wpdb_collate;
				array_push($queries, $sql);
			}

			if (count($queries) > 0) {
				// create wallet metakey in usermeta of users.
				$users = get_users();
				foreach ($users as $user) {
					$user_id = $user->ID;
					$wallet  = get_user_meta($user_id, 'my_wallet', true);
					if (empty($wallet)) {
						$wallet = update_user_meta($user_id, 'my_wallet', 0);
					}

					$is_wallet_active  = get_user_meta($user_id, 'my_wallet_active', true);
					if (empty($is_wallet_active)) {
						update_user_meta($user_id, 'my_wallet_active', true); //by default all wallets active.
					}
				}

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				foreach ($queries as $query) {
					dbDelta($query);
				}
				update_option('my_wallet_lcns_key', $response->licence_key);
				update_option('my_wallet_lcns_status', $response->is_licence_active);

				$my_wallet_supported_payment_gateways = json_encode(['paypal']);
				update_option('my_wallet_supported_payment_gateways', $my_wallet_supported_payment_gateways);
				return 1;
			}
			return 0;
		}

		/**
		 * Delete the json file
		 */
		function delete_json()
		{
			$upload_dir	= wp_upload_dir(); //Set upload folder
			// Check folder permission and define file location
			if (wp_mkdir_p($upload_dir['basedir'] . '/mywallet')) {
				$file = $upload_dir['basedir'] . '/mywallet/response-data.json';
				if (file_exists($file)) { //delete file
					return unlink($file);
				}
			}
			return false;
		}

		/**
		 * This function is responsible to create tables.
		 */
		function my_wallet_manage_db()
		{
			echo create_tables();
			delete_json();
			wp_die();
		}

		add_action('wp_ajax_manage_payment_gateway_ajax_fun', 'manage_payment_gateway_ajax_fun');
		add_action('wp_ajax_manage_my_wallet_payment_gateways', 'manage_my_wallet_payment_gateways');
		add_action('wp_ajax_add_amount_to_user_account_ajax_fun', 'add_amount_to_user_account_ajax_fun');
		add_action('wp_ajax_manage_wallet_status_ajax_fun', 'manage_wallet_status_ajax_fun');
		add_action('wp_ajax_manage_coupon_status_ajax_fun', 'manage_coupon_status_ajax_fun');
		add_action('wp_ajax_get_coupon_by_id_ajax_fun', 'get_coupon_by_id_ajax_fun');
		add_action('wp_ajax_my_wallet_license_verify', 'my_wallet_license_verify');
		add_action('wp_ajax_my_wallet_manage_db', 'my_wallet_manage_db');
		//ajax functions ends here.

		register_activation_hook(__FILE__, 'activate_my_wallet_for_woocommerce');
		register_deactivation_hook(__FILE__, 'deactivate_my_wallet_for_woocommerce');
	} else {
		// public side 



		/**
		 *  Register new endpoint to use for My Account page.
		 */
		function add_menu_in_my_account()
		{
			//checking if license is valid and wallet is active
			$is_wallet_active  = (int)get_user_meta(get_current_user_id(), 'my_wallet_active', true);
			get_option('my_wallet_lcns_status');
			if ((int)get_option('my_wallet_lcns_status') && $is_wallet_active) {
				global $wp_rewrite;
				add_rewrite_endpoint('my-wallet', EP_ROOT | EP_PAGES);
				$wp_rewrite->flush_rules();
				add_filter('woocommerce_account_menu_items', 'my_wallet_add_menu_link_my_account');

				// Reordering "My Wallet" position in woocommerce my-account page. 
				function affiliate_home_link($menu_links)
				{
					// Remove the logout menu item, will re-add later
					$logout_link = $menu_links['customer-logout'];
					unset($menu_links['customer-logout']);
					$menu_links['my-wallet'] = __('My Wallet', 'my-wallet');
					// Insert back the logout item.
					$menu_links['customer-logout'] = $logout_link;
					return $menu_links;
				}
				add_filter('woocommerce_account_menu_items', 'affiliate_home_link', 10, 1);
			}
		}

		/**
		 * Insert the new endpoint into the My Account menu.
		 *
		 * @param array $items    All the items of the my account page.
		 */
		function my_wallet_add_menu_link_my_account($items)
		{
			$items['my-wallet'] = 'My Wallet';
			return $items;
		}

		add_action('init', 'add_menu_in_my_account');

		// checking if user using his own customized templates instead of myWallet default templates.
		$isUserCustomizedTplAvailable = file_exists(get_stylesheet_directory() . "/mywallet");

		if ($isUserCustomizedTplAvailable) { //checking if user customized templates available.

			add_action('woocommerce_account_my-wallet_endpoint', 'my_wallet_account_content_user_tpl');

			/**
			 * Define plugin public side constants.
			 *
			 * @since             1.0.0
			 */
			function define_my_wallet_public_constants()
			{
				my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_DIR_URL', get_stylesheet_directory() . "/mywallet");
				my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_CSS_URL', get_stylesheet_directory() . "/mywallet/css/");
				my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_JS_URL', get_stylesheet_directory() . "/mywallet/js/");
			}
		} else { // myWallet default templates

			add_action('woocommerce_account_my-wallet_endpoint', 'my_wallet_account_content_default', 20);

			/**
			 * Define plugin public side constants.
			 *
			 * @since             1.0.0
			 */
			function define_my_wallet_public_constants()
			{
				my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_DIR_URL', plugin_dir_url(__FILE__));
				my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_CSS_URL', plugin_dir_url(__FILE__) . "public/css/");
				my_wallet_woocommerce_constants('MY_WALLET_WOOCOMMERCE_JS_URL', plugin_dir_url(__FILE__) . "public/js/");
			}
		}

		function my_wallet_woocommerce_constants($key, $value)
		{
			if (!defined($key)) {
				define($key, $value);
			}
		}

		/**
		 * Add content to the new endpoint. (for default templates)
		 */
		function my_wallet_account_content_default()
		{
			include_once plugin_dir_path(__FILE__) . 'public/includes/my-wallet-display-content.php';
		}

		/**
		 * Add content to the new endpoint. (for user custom templates)
		 */
		function my_wallet_account_content_user_tpl()
		{
			include_once get_stylesheet_directory() . "/mywallet/my-wallet-display-content.php";
		}


		/**
		 * Disabling my_wallet payment gateway
		 */
		function woo_disable_my_wallet($available_gateways)
		{
			if (isset($available_gateways['my_wallet'])) {
				unset($available_gateways['my_wallet']);
			}
			return $available_gateways;
		}

		/**
		 * Show myWallet as discount in review order table during checkout
		 *
		 * @return void
		 */
		function show_mywallet_option_checkout_review_order()
		{ //************************************************************************************* */
			/* adding css and js for temporary purpose - we have to remove these from here */
			//************************************************************************************* */
			wp_enqueue_style('my-wallet-admin', MY_WALLET_WOOCOMMERCE_CSS_URL . 'my_wallet_publlic_style.css', false, '1.0', 'all');
			wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
			wp_localize_script('ajax-script', 'admin_ajax_url', array(
				'url' => admin_url('admin-ajax.php?action='),
			));
			/* */
			$is_wallet_active  = (int)get_user_meta(get_current_user_id(), 'my_wallet_active', true);
			$my_wallet_is_license_valid = get_option('my_wallet_lcns_status');
			if ($is_wallet_active && $my_wallet_is_license_valid) {
				$my_wallet_cart_total = WC()->cart->total;
				$user_id = get_current_user_id();
				if ($user_id) {
					$wallet_amount = get_user_meta($user_id, 'my_wallet', true);
					$wallet_amount = empty($wallet_amount) ? 0 : $wallet_amount;
					if ($wallet_amount > 0 && ($wallet_amount < $my_wallet_cart_total) || (WC()->session->__isset('enable_discount') && WC()->session->get('enable_discount') == 1)) { ?>
						<tr class="my-wallet-price-checkout">
							<td><?php echo esc_html('Pay by MyWallet ( ') . wc_price($wallet_amount) . ' )'; ?></td>
							<td>
								<p>
									<?php
									if (WC()->session->__isset('enable_discount') && WC()->session->get('enable_discount') == 1) {
										echo '<input type="checkbox" name="myWallet_discount" id="myWallet_discount" checked>';
									} else {
										echo '<input type="checkbox" name="myWallet_discount" id="myWallet_discount">';
									}
									?>
								</p>
							</td>
						</tr>
					<?php
					}

					// $active_gateways = array();
					// $gateways = WC()->payment_gateways->get_available_payment_gateways();
					// $only_required_gateways = ['ppec_paypal'];

					// if ($gateways) {
					// 	foreach ($gateways as $gateway) {
					// 		if ($gateway->enabled == 'yes' && in_array($gateway->id, $only_required_gateways) && is_gateway_id_active_in_db($gateway->id)) {
					// 			array_push($active_gateways, $gateway);
					// 		}
					// 	}
					// }

					$active_gateways = get_my_wallet_payment_gateways_for_public();

					// if (abs($my_wallet_cart_total) > $wallet_amount && !WC()->session->__isset('enable_discount') || WC()->session->get('enable_discount') == 0) {
					if (count($active_gateways) > 0 && abs($my_wallet_cart_total) > $wallet_amount && (!WC()->session->__isset('enable_discount') || WC()->session->get('enable_discount') == 0)) {
					?>
						<tr>
							<td class="my-wallet-price-checkout">
								<p class="m-0"><?php echo esc_html('MyWallet ( ') . wc_price($wallet_amount) . ' )'; ?></p>
							</td>
							<td class="my-wallet-checkout-add-money">
								<!-- <a href="javascript:void(0)" id="" onclick="addRemainingAmountToWallet(<?php echo $my_wallet_cart_total - $wallet_amount ?>)"><?php echo esc_html('Add ') . wc_price($my_wallet_cart_total - $wallet_amount) . ' & Pay'; ?></a> -->
								<a href="javascript:void(0)" id="" onclick="addRemainingAmountToWallet(<?php echo $my_wallet_cart_total - $wallet_amount ?>)"><?php echo esc_html('Add ') . wc_price($my_wallet_cart_total - $wallet_amount) . ' & Pay'; ?></a>
							</td>
						</tr>
						<div id="loader"></div>
			<?php
					}

					// if (($my_wallet_cart_total != $wallet_amount) || (WC()->session->__isset('enable_discount') && WC()->session->get('enable_discount') == 1)) {
					// 	add_filter('woocommerce_available_payment_gateways', 'woo_disable_my_wallet', 99, 1);
					// }

					if (($my_wallet_cart_total > $wallet_amount) || (WC()->session->__isset('enable_discount') && WC()->session->get('enable_discount') == 1)) {
						add_filter('woocommerce_available_payment_gateways', 'woo_disable_my_wallet', 99, 1);
					} else {
						add_filter('woocommerce_gateway_title', 'change_cheque_payment_gateway_title', 100, 2);
						function change_cheque_payment_gateway_title($title, $payment_id)
						{
							if ($payment_id === 'my_wallet') {
								$wallet_amount = get_user_meta(get_current_user_id(), 'my_wallet');
								$name = 'My Wallet ( ' . wc_price($wallet_amount[0]) . ' )';
								$title = $name;
							}
							return $title;
						}
					}
				}
			} else {
				add_filter('woocommerce_available_payment_gateways', 'woo_disable_my_wallet', 99, 1);
			}
		}

		add_action('woocommerce_review_order_after_order_total', 'show_mywallet_option_checkout_review_order');
		define_my_wallet_public_constants();
	}

	/**
	 * getting active payment gateways to show on checkout page
	 */
	function get_payment_gateways_during_checkout()
	{
		// $active_gateways = array();
		// $gateways = WC()->payment_gateways->get_available_payment_gateways();
		// // $not_required_gateways = ['Direct bank transfer', 'Check payments', 'Cash on delivery', 'Wallet Payment'];
		// $only_required_gateways = ['ppec_paypal'];
		// if ($gateways) {
		// 	foreach ($gateways as $gateway) {
		// 		if ($gateway->enabled == 'yes' && in_array($gateway->id, $only_required_gateways) && is_gateway_id_active_in_db($gateway->id)) {
		// 			array_push($active_gateways, $gateway);
		// 		}
		// 	}
		// }

		$active_gateways = get_my_wallet_payment_gateways_for_public();
		echo json_encode($active_gateways);
		wp_die();
	}

	/** 
	 * Add money to walllet
	 */
	function add_remaining_money_to_user_wallet()
	{
		if (isset($_POST['data']) && !empty($_POST['data'])) {
			$jsonData = sanitize_text_field( wp_unslash(stripslashes(html_entity_decode($_POST['data'])))); //success data from paypal
			$data = json_decode($jsonData, true);
			$current_user_id = get_current_user_id();
			$amount = $data['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
			// $currency = $data['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
			$currency = get_woocommerce_currency_symbol();
			$transaction_type = 'Added by User';
			$payment_method = 'Paypal';
			$transaction_id = $data['purchase_units'][0]['payments']['captures'][0]['id'];
			global $wpdb;
			$table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
			//inserting record in transaction table
			$wpdb->insert($table_name, array(
				'user_id' => $current_user_id,
				'amount' => $amount,
				'currency' => $currency,
				'transaction_type' => $transaction_type,
				'payment_method' => $payment_method,
				'transaction_id' => $transaction_id,
				'date' => date('Y-m-d H:i:s')
			));

			//updating wallet balance
			update_wallet_balance($current_user_id, $amount);
			$current_amount = get_user_meta($current_user_id, 'my_wallet', true);
			echo esc_html($current_amount);
		} else {
			echo "something went wrong";
		}
		wp_die();
	}

	function get_client_id_for_paypal_transaction()
	{
		$paypal_client_id = get_option('My_wallet_ppec_paypal');
		if ($paypal_client_id) {
			echo esc_html($paypal_client_id);
		} else {
			echo "id not found";
		}
		wp_die();
	}

	add_action('wp_footer', 'checkout_toggle_discount_script');

	/**
	 * 
	 * Ajax call fires when user pay partially by myWallet on checkout page
	 * (hit when user checks the checkbox).
	 * 
	 */
	function checkout_toggle_discount_script()
	{
		if (is_checkout() && !is_wc_endpoint_url()) :

			if (WC()->session->__isset('enable_discount')) {
				WC()->session->__unset('enable_discount');
			}
			?>
			<script type="text/javascript">
				jQuery(function($) {
					if (typeof wc_checkout_params === 'undefined')
						return false;

					$('form.checkout').on('change', 'input[name="myWallet_discount"]', function() {
						// var toggle = $(this).prop('checked') === true ? '1' : '0';
						console.log(wc_checkout_params);
						var toggle = $(this).is(':checked') ? '1' : '0';
						$.ajax({
							type: 'POST',
							url: wc_checkout_params.ajax_url,
							data: {
								'action': 'enable_discount',
								'discount_toggle': toggle,
							},
							success: function(result) {
								console.log(result);
								$('body').trigger('update_checkout');
							},
						});
					});
				});
			</script>
		<?php
		endif;
	}

	// Ajax receiver: Set a WC_Session variable
	add_action('wp_ajax_enable_discount', 'checkout_enable_discount_ajax');
	add_action('wp_ajax_nopriv_enable_discount', 'checkout_enable_discount_ajax');
	function checkout_enable_discount_ajax()
	{
		if (isset($_POST['discount_toggle'])) {
			WC()->session->set('enable_discount', esc_attr(sanitize_text_field( wp_unslash($_POST['discount_toggle']))) == 1);
			if (esc_attr($_POST['discount_toggle']) == 0) {
				WC()->session->__unset('enable_discount');
			}
			echo esc_attr(sanitize_text_field( wp_unslash($_POST['discount_toggle']))) == 1;
		}
		wp_die();
	}

	// Set the discount
	add_action('woocommerce_cart_calculate_fees', 'checkout_set_discount', 20, 1);
	function checkout_set_discount($cart)
	{
		if ((is_admin() && !defined('DOING_AJAX')) || !is_checkout())
			return;

		if (WC()->session->get('enable_discount') == 1) {
			$current_user_id = get_current_user_id();
			$wallet_amount = get_user_meta($current_user_id, 'my_wallet', true);
			$cart->add_fee("MyWallet Discount", -$wallet_amount);
		}
	}

	add_action('woocommerce_thankyou', 'deduct_mywallet_money_on_payment_complete', 10, 1);
	/**
	 * Deduct money from myWallet when user make partial payment through myWallet.
	 */
	function deduct_mywallet_money_on_payment_complete($order_id)
	{
		$order = wc_get_order($order_id);
		$myWalletAmount = 0;
		$myWalletDiscountAvailable = false;
		foreach (array_values($order->get_fees()) as $fee_obj) {
			if ($fee_obj->get_name() == 'MyWallet Discount') {
				$myWalletAmount = $fee_obj->get_total();
				$myWalletDiscountAvailable = true;
				break;
			}
		}
		if ($myWalletDiscountAvailable) {
			$current_user_id = get_current_user_id();
			$current_balance = get_user_meta($current_user_id, 'my_wallet', true);
			if ($current_balance != null && $current_balance > 0) {
				update_wallet_balance($current_user_id, $myWalletAmount);
				global $wpdb;
				$table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
				$wpdb->insert($table_name, array(
					'user_id' => $current_user_id,
					'amount' => ($myWalletAmount * -1),
					'currency' => get_woocommerce_currency_symbol(),
					'transaction_type' => "Debited from Mywallet",
					'payment_method' => "MyWallet",
					'transaction_id' => get_transaction_id(),
					'order_id' => $order_id,
					'date' => date('Y-m-d H:i:s')
				));
			}
		}
	}

	// Create wallet for newly registered user.
	function myplugin_registration_save($user_id)
	{
		update_user_meta($user_id, 'my_wallet', 0);
		update_user_meta($user_id, 'my_wallet_active', true);
	}

	add_action('wp_ajax_get_payment_gateways_during_checkout', 'get_payment_gateways_during_checkout');
	add_action('wp_ajax_nopriv_get_payment_gateways_during_checkout', 'get_payment_gateways_during_checkout');

	add_action('wp_ajax_add_remaining_money_to_user_wallet', 'add_remaining_money_to_user_wallet');
	add_action('wp_ajax_nopriv_add_remaining_money_to_user_wallet', 'add_remaining_money_to_user_wallet');

	add_action('wp_ajax_get_client_id_for_paypal_transaction', 'get_client_id_for_paypal_transaction');
	add_action('wp_ajax_nopriv_get_client_id_for_paypal_transaction', 'get_client_id_for_paypal_transaction');

	add_action('user_register', 'myplugin_registration_save', 10, 1);


	/**
	 * myWallet custom payment gateway code
	 */
	function my_wallet_payment_gateway_init()
	{
		require_once plugin_dir_path(__FILE__) . 'public/includes/my-wallet-payment-gateway.php';
		new My_wallet_wc_payment_gateway();
	}

	function my_wallet_add_to_gateways($gateways)
	{
		$gateways[] = 'My_wallet_wc_payment_gateway';
		return $gateways;
	}

	add_filter('woocommerce_payment_gateways', 'my_wallet_add_to_gateways');
	add_action('plugins_loaded', 'my_wallet_payment_gateway_init', 11);
} else {
	// To deactivate plugin if woocommerce is not installed.
	add_action('admin_init', 'my_wallet_plugin_deactivate');

	/**
	 * Call Admin notices
	 *
	 * @name my_wallet_plugin_deactivate()
	 */
	function my_wallet_plugin_deactivate()
	{
		deactivate_plugins(plugin_basename(__FILE__), true);
		unset($_GET['activate']);
		add_action('admin_notices', 'my_wallet_plugin_error_notice');
	}

	function my_wallet_plugin_error_notice()
	{
		?>
		<div class="error notice is-dismissible">
			<p>
				<?php esc_html_e('WooCommerce is not activated, Please activate WooCommerce first to install MyWallet For WooCommerce.', 'my-wallet-for-woocommerce'); ?>
			</p>
		</div>
<?php
	}
}
