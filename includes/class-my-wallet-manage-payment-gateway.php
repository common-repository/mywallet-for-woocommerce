<?php
/**
 * This class defines all code necessary to handle mywallet payment gateways screen'.
 */

/**
 * Fired when user clicks on 'payment gatewayd screen menu'.
 *
 * This class defines all code necessary to handle mywallet payment gateways screen'.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_Wallet_Manage_Payment_Gateway
{

    public $active_gateways = array();

    public function __construct()
    {        
        wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
        $this->setActiveWCPaymentGateways();
        $this->getScreen();
    }

    /**
     * This function sets active payment gateways of woocommerce.
     */
    public function setActiveWCPaymentGateways()
    {            
        $this->active_gateways = get_my_wallet_payment_gateways();
    }
    
    /**
     * This function is responsible for displaying my wallet payment gateways screen.
     */
    public function getScreen()
    {
        echo '
        <div class="my-wallet-settings-header">
        <h1 class="wp-heading-inline">My Wallet <span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        Manage Payment Gateways
        </h1>
		</div>
        <div class="wrap">
        
        <div id="message" class="updated notice is-dismissible mywallet-head ml-0"><p>Payment gateways updated successfully.</p>
        <button type="button" id="notice-dismiss" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
        </button></div>
        
        <div id="my_wallet_verify_key_alert" class="notice notice-error is-dismissible mywallet-head ml-0 d-none"><p>Please verify keys first.</p>
        <button type="button" id="notice-dismiss" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
        </button></div>   

        <div class="checkboxes-div">';

        echo '<div class="left-border-bar my-wallet-payment-gateway-header white-background">
        <div class="my-wallet-payment-gateway-section"><b class="f-700">Payment Gateway</b></div>
        <div class="my-wallet-payment-gateway-checkbox"><b class="f-700">Enable</b></div>
        </div>';
        
        $count = 0;
        $counter = 1;
        foreach ($this->active_gateways as $gateway) {
            $checked = "";
            $my_wallet_payment_gateway_in_db = 'mywallet_' . $gateway->id;
            
            if (get_option($my_wallet_payment_gateway_in_db)) {
                $checked = "checked";
            }

            if ($count++ % 2 == 0) {
                $white_bkgrnd = "";
            } else {
                $white_bkgrnd = "white-background";
            }
            $flag = $gateway->id;
            echo '<div class="my-wallet-payment-gateway-header ' . esc_html($white_bkgrnd) . '">
            <div class="my-wallet-payment-gateway-section left-border-bar"><b>' . esc_html($gateway->title) . '</b></div>
            <div class="my-wallet-payment-gateway-checkbox"><input type="checkbox" ' . esc_html($checked) . ' id="' . esc_html($gateway->id) . '" class="mywallet-payment-gateway"></div>
            <div class="plus" id="my_wallet_gateway_plus_'.esc_html($flag).'" onclick=togglePaymentGateway("'.esc_html($flag).'")>
                <a class="my-wallet-test-paypal-key" id="my_wallet_gateway_div_'.esc_html($flag).'">Enter Keys</a>
            </div>
            </div>';

            //body
            $key_from_db = "My_wallet_".$gateway->id;
            $secret_value_for_payment_gateway = get_option($key_from_db);
            $hidden_field_count = 0;
            if($secret_value_for_payment_gateway != null && $secret_value_for_payment_gateway != ""){
                $hidden_field_count = 1;
            }
            echo '<div class="gateway-info d-none" id="my_wallet_gateway_'.esc_html($gateway->id).'">
            <div class="gateway-info-content">
                <div class="gateway-info-value">
                <input type="text" value="'.esc_html($secret_value_for_payment_gateway).'" class="w-95 p-8" name="my_wallet_gateway_secret" id="my_wallet_gateway_value_'.esc_html($gateway->id).'" placeholder="Enter secret key" onkeypress="updateHiddenFieldValue(\''.esc_html($gateway->id).'\')">
                <input type="hidden" value="'.esc_html($hidden_field_count).'" id="my_wallet_gateway_test_clicked_'.esc_html($gateway->id).'">
                <a href="javascript:void(0)" id="my-wallet-gateway-test-btn" class="my-wallet-redirect w-70 pad-8 ml-15" onclick="testMyWalletGatewayKey(\''.esc_html($gateway->id).'\')">
                <span>Test</span></a>
                    <a id="my-wallet-loader" class="loader d-none"></a>
                    <p id="my-wallet-right" class="right-tick d-none">&#10003;</p>
                    <p id="my-wallet-cancel" class="cancel-tick d-none">&#10006;</p>
                    </div>
                    <div class="mt-10 d-flex just-con-space-bw">
                    <p>
                    <i>Visit 
                    <a href="https://developer.paypal.com/docs/api-basics/manage-apps/" target="blank">here</a> 
                    to get required key for gateway connection</i>                    
                    </p>
                    <p class="red d-none mr-15" id="my_wallet_gateway_error'.esc_html($gateway->id).'"><b>Invalid gateway details.</b></p>
                    <p class="green d-none mr-15" id="my_wallet_gateway_success'.esc_html($gateway->id).'"><b>Valid key.</b></p>
                    </div>
                </div>';
                
        }
        
        /**
         * Showing msg if no payment gateway available.
         */
        if (count($this->active_gateways) == 0) {
            echo '<div class="my-wallet-payment-gateway-header">
            <div class="my-wallet-payment-gateway-section left-border-bar"><i>No payment gateway enabled.</i></div>
            </div>';
        } else {
            /**
             * Save button. 
             */
            $my_wallet_data_delete_confirmation = get_option('my_wallet_data_delete_confirmation');
            if($my_wallet_data_delete_confirmation == "true"){
                $checked = "checked";
            }else{
                $checked = "";
            }
            echo '
            </div>
            <div class="my-wallet-deltion-msg">
            <span>Delete plugin data on deletion ?</span> 
            &nbsp;&nbsp;&nbsp;<input type="checkbox" '.esc_html($checked).' name="my-wallet-data-delete-confirmation" id="my-wallet-data-delete-confirmation">
            </div>
            <div class="my-wallet-notice-button mt-15">
            <a href="javascript:void(0)" class="my-wallet-redirect w-70" onclick="manage_payment_gateway()">
            <span>Save</span></a>
            </div>';
        }

        echo '</div>';
    }
}
