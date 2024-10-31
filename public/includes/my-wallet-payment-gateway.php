<?php
class My_wallet_wc_payment_gateway extends WC_Payment_Gateway
{

    public function __construct()
    {

        $this->id = 'my_wallet'; // payment gateway plugin ID
        $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = false; // in case you need a custom credit card form
        $this->method_title = 'MyWallet Gateway';
        $this->method_description = 'Description of MyWallet payment gateway'; // will be displayed on the options page
        $this->supports = array(
            'products'
        );
        $this->title = 'My Wallet';
        $this->description = 'Pay with My Wallet and get exciting cashbacks.';
    }

    /**
     * Intialize mywallet payment gateway form fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array();
    }

    /**
     * Validate fields during checkout
     */
    public function validate_fields()
    {
        if (empty(sanitize_text_field( wp_unslash($_POST['billing_first_name'])))) {
            wc_add_notice('First name is required!', 'error');
            return false;
        }
        return true;
    }

    /**
     * code defines functionality which required for payment through mywallet payment gateway.
     */
    public function process_payment($order_id)
    {
        global $woocommerce;
        global $wpdb;
        $order = wc_get_order($order_id);
        $current_user_id = get_current_user_id();
        $user_displayname = get_user_by( 'id', $current_user_id )->display_name ;
        $my_wallet_cart_total = WC()->cart->total;
        $wallet_amount = get_user_meta($current_user_id, 'my_wallet', true);

        if ($my_wallet_cart_total > $wallet_amount) {
            wc_add_notice('Insufficient funds available in MyWallet.', 'error');
            return;
        }

        $amount = $my_wallet_cart_total;
        $currency = get_woocommerce_currency_symbol();
        $transaction_type = 'Order placed by '. $user_displayname;
        $payment_method = 'My Wallet';
        $transaction_id = get_transaction_id();
        $table_name = 'wp_my_wallet_transaction'.my_wallet_get_suffix();
        $wpdb->insert($table_name, array(
            'user_id' => $current_user_id,
            'amount' => $amount,
            'currency' => $currency,
            'transaction_type' => $transaction_type,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'order_id' => $order_id,
            'date' => date('Y-m-d H:i:s')
        ));

        update_wallet_balance($current_user_id, ($amount * -1));

        $order->payment_complete();
        $order->wc_reduce_stock_levels;
        // $order->add_order_note('Hey, your order is paid by MyWallet! Thank you!', true);
        $woocommerce->cart->empty_cart();
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
}
