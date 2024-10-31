<?php

/**
 * Containing all required and common functions for pulgin.
 */

/**
 *
 * Containing all required and common functions for pulgin.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
function check($result)
{
    echo "<pre>";
    print_r($result);
}

function my_wallet_get_coupon_name_by_id($coupon_id)
{
    global $wpdb;
    $table_name = 'wp_my_wallet_coupon' . my_wallet_get_suffix();
    $result = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE id = %s', $coupon_id));
    return $result[0]->coupon_name;
}

function my_wallet_get_coupon_id_by_name($coupon_name)
{
    global $wpdb;
    $table_name = 'wp_my_wallet_coupon' . my_wallet_get_suffix();
    $result = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE coupon_name = %s', $coupon_name));
    return $result[0]->id;
}

function my_wallet_insert_coupon($coupon_name)
{
    global $wpdb;
    $table_name = 'wp_my_wallet_coupon' . my_wallet_get_suffix();
    $result = $wpdb->insert($table_name, array(
        'coupon_name' => $coupon_name
    ));
    return $result;
}

function my_wallet_get_coupon_meta($coupon_id, $coupon_meta_key)
{
    global $wpdb;
    $result = array();
    $table_name = 'wp_my_wallet_meta_coupon' . my_wallet_get_suffix();
    $result = $wpdb->get_results($wpdb->prepare('SELECT `meta_coupon_value` FROM '. $table_name . ' WHERE meta_coupon_key = %s AND coupon_id=%s', $coupon_meta_key, $coupon_id));
    if (count($result) > 0) {
        return $result[0]->meta_coupon_value;
    }
    return null;
}

function get_coupon_meta_by_id($coupon_id)
{
    global $wpdb;
    $table_name = 'wp_my_wallet_meta_coupon' . my_wallet_get_suffix();
    $result = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE coupon_id = %s', $coupon_id));
    return $result;
}

function update_wallet_balance($user_id, $amount)
{
    global $wpdb;
    $result = $wpdb->get_results("update wp_usermeta set meta_value = meta_value + $amount WHERE user_id = $user_id and meta_key = 'my_wallet'");
    return $result;
}

function get_transaction_id()
{
    return uniqid();
}

function update_coupon_meta($coupon_id, $meta_coupon_key = null, $meta_coupon_value = null)
{
    global $wpdb;

    $table_name = 'wp_my_wallet_coupon' . my_wallet_get_suffix();
    $result = $wpdb->get_results($wpdb->prepare('SELECT `id` FROM '. $table_name . ' WHERE id = %s', $coupon_id));
    $id = $result[0]->id;

    if ($id) {
        $table_name = 'wp_my_wallet_meta_coupon' . my_wallet_get_suffix();
        $sql = "INSERT INTO $table_name (`coupon_id`,`meta_coupon_key`,`meta_coupon_value`)
        VALUES($coupon_id, '$meta_coupon_key', '$meta_coupon_value')
        ON DUPLICATE KEY UPDATE `meta_coupon_value` = '$meta_coupon_value'";
        $wpdb->get_results($sql);
    } else {
        return null;
    }
}

function update_coupon($coupon_name, $coupon_id)
{
    global $wpdb;
    $table_name = 'wp_my_wallet_coupon' . my_wallet_get_suffix();
    $result = $wpdb->get_results($wpdb->prepare('UPDATE '. $table_name . ' SET coupon_name = %s WHERE id = %s', $coupon_name,  $coupon_id));
    return $result;
}

function change_date_format($originalDate)
{
    return date("d-m-Y", strtotime($originalDate));
}

function change_date_time_format($originalDateTime)
{
    if ($originalDateTime != null && $originalDateTime != "") {
        $datetime = explode(' ', $originalDateTime);
        $date = $datetime[0];
        $formattedDate =  change_date_format($date);
        $date_time = $formattedDate . ' ' . $datetime[1];
        return $date_time;
    } else {
        return "";
    }
}

function is_gateway_id_active_in_db($gateway_id)
{
    $gateway_id = "mywallet_" . $gateway_id;
    $is_id_available_and_active = get_option($gateway_id);
    return $is_id_available_and_active;
}

function get_coupons_by_user_id($user_id)
{
    global $wpdb;
    $valid_coupon = array();
    $my_wallet_coupon = 'wp_my_wallet_coupon' . my_wallet_get_suffix();
    $my_wallet_meta_coupon = 'wp_my_wallet_meta_coupon' . my_wallet_get_suffix();
    $allCoupons = $wpdb->get_results($wpdb->prepare('SELECT `coupon_name`, `coupon_id`, `meta_coupon_value` FROM '. $my_wallet_meta_coupon . ' mc  INNER JOIN '. $my_wallet_coupon .' wc ON wc.id = mc.coupon_id WHERE mc.meta_coupon_key=\'coupon_for_users\''));
    foreach ($allCoupons as $coupon) {
        if (!validate_coupon_by_id($coupon->coupon_id)) {
            continue;
        }
        if ($coupon->meta_coupon_value == "All") {
            $valid_coupon[] = $coupon;
        } else {
            $user = get_user_by('id', get_current_user_id());
            $current_user_email_id = $user->user_email;
            $user_email_ids = json_decode($coupon->meta_coupon_value);
            if (count($user_email_ids) > 0) {
                if (in_array($current_user_email_id, $user_email_ids)) {
                    $valid_coupon[] = $coupon;
                }
            } else {
                echo esc_html("Specific key is checked but no email id entered.");
                die;
            }
        }
    }
    return $valid_coupon;
}

function validate_coupon_by_id($coupon_id)
{

    if (!my_wallet_get_coupon_meta($coupon_id, "is_active")) {
        return false;
    }

    //check valid from date validity
    $valid_from_date = my_wallet_get_coupon_meta($coupon_id, "valid_from");
    if ($valid_from_date != "") {
        $date = new DateTime($valid_from_date);
        $now = new DateTime();
        if ($date > $now) {
            return false;
        }
    }

    //check valid from date validity
    $valid_till_date = my_wallet_get_coupon_meta($coupon_id, "valid_to");
    if ($valid_till_date != "") {
        $date = new DateTime($valid_till_date);
        $now = new DateTime();
        if ($date < $now) {
            return false;
        }
    }

    //check if uset already avail coupon
    $current_user_id = get_current_user_id();
    $already_used_by_users = my_wallet_get_coupon_meta($coupon_id, "already_used_by_users");
    if (in_array($current_user_id, explode(",", $already_used_by_users))) {
        return false;
    }

    //check redeemption_limit & count
    $usability = my_wallet_get_coupon_meta($coupon_id, "usability");
    $redeemption_count = my_wallet_get_coupon_meta($coupon_id, "redeemption_count");

    if ($usability === "Unlimited") {
        return true;
    } else {
        if ($redeemption_count >= $usability) {
            return false;
        } else {
            return true;
        }
    }

    return true;
}


function my_wallet_get_suffix()
{
    $domain = !empty($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : home_url();
    $domain = explode('.', $domain)[0];
    return '_' . $domain[strlen($domain) - 1] .
        $domain[strlen($domain) - 2] .
        $domain[0];
    // return "_non";
}

function my_wallet_is_license_valid()
{
    return get_option('my_wallet_lcns_status');
}

function get_my_wallet_supported_payment_gateways()
{
    $gateways = get_option('my_wallet_supported_payment_gateways');
    if ($gateways) {
        return json_decode($gateways);
    } else {
        return [];
    }
}

function get_my_wallet_payment_gateways()
{
    $active_gateways = array();
    require_once plugin_dir_path(__FILE__) . 'custom-payment-gateway.php';
    $my_wallet_supported_payment_gateways = get_my_wallet_supported_payment_gateways();
    foreach ($my_wallet_supported_payment_gateways as $gateway) {
        $my_wallet_gateway = new My_Wallet_Payment_Gateway($gateway);
        array_push($active_gateways, $my_wallet_gateway);
    }

    return $active_gateways;
}

function get_my_wallet_payment_gateways_for_public()
{
    $active_gateways = array();
    require_once plugin_dir_path(__FILE__) . 'custom-payment-gateway.php';
    $my_wallet_supported_payment_gateways = get_my_wallet_supported_payment_gateways();
    foreach ($my_wallet_supported_payment_gateways as $gateway) {
        $my_wallet_gateway = new My_Wallet_Payment_Gateway($gateway);
        $gateway_id = $my_wallet_gateway->id;

        // checking if gateway enabled in plugin or not
        $payment_gateway = 'mywallet_' . $gateway_id;
        $is_active = get_option($payment_gateway) == "1" ? 1 : 0;

        // check for gateway essential detials in db
        $is_key_available = 0;
        $key_for_db = "My_wallet_" . $gateway_id;
        $gateway_value_in_db = get_option($key_for_db);
        if ($gateway_value_in_db) {
                $is_key_available = 1;
        }

        if ($is_active && $is_key_available) {
            array_push($active_gateways, $my_wallet_gateway);
        }
    }

    return $active_gateways;
}
