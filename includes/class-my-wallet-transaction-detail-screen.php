<?php

/**
 * This class defines all code necessary to handle mywallet transaction detail page screen'.
 */

/**
 * Fired when user clicks on 'transaction detail page screen link'.
 *
 * This class defines all code necessary to handle mywallet transaction detail page screen'.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_Wallet_Transaction_Detail_Screen
{

    public $transaction_id;
    public $user_id;
    public $transaction_data;
    public $user;

    public function __construct($user_id = 0, $transaction_id = 0)
    {
        /**
         * Adding styles and js files
         * my-wallet-todo : we have to remove it from here and keep it at one place for whole plugin
         */
        wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
        $this->transaction_id = $transaction_id;
        $this->user_id = $user_id;
        global $wpdb;
        $table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
        // $result = $wpdb->get_results("SELECT * FROM $table_name as mw INNER JOIN wp_usermeta as um ON mw.user_id = um.user_id WHERE transaction_id='$transaction_id' AND mw.user_id=$user_id AND um.meta_key = 'my_wallet'");
        $result = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' as mw INNER JOIN wp_usermeta as um ON mw.user_id = um.user_id WHERE transaction_id=%s AND mw.user_id=%s AND um.meta_key = \'my_wallet\' ', $transaction_id, $user_id));
        $this->transaction_data = $result[0];
        $this->user = get_user_by('id', $user_id);
        $this->getScreen();
    }

    /**
     * This function responsible for displaying all transactions.
     */
    public function getScreen()
    {
        echo '
        <div class="my-wallet-settings-header">
        <h1 class="wp-heading-inline"><a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_menu")) . '">My Wallet </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        Transaction details
        </h1>
		</div>
        <div class="wrap">
        <div class="my-wallet-user-details">
        <div class="left-border-bar white-background pad-10"><b>Transaction ID&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->transaction_data->transaction_id ). '</b></div>
        <div class="left-border-bar pad-10"><b>Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->user->display_name) . '</b></div>        
        <div class="left-border-bar white-background pad-10"><b>Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->user->user_email) . '</b></div>
        <div class="left-border-bar pad-10"><b>Mobile&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->user->billing_phone) . '</b></div>';
        if ($this->transaction_data->order_id) {
            $order_id = $this->transaction_data->order_id;
            echo '<div class="left-border-bar white-background pad-10"><b>Transaction Type&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->transaction_data->transaction_type) . ' (Order ID : <a class="text-deco-none" href="' . esc_url(admin_url("post.php?post=$order_id&action=edit")) . '">'.$this->transaction_data->order_id.'</a> )</b></div>';
        } else {
            echo '<div class="left-border-bar white-background pad-10"><b>Transaction Type&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->transaction_data->transaction_type) . '</b></div>';
        }
        echo '<div class="left-border-bar pad-10"><b>Amount&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($this->transaction_data->currency . $this->transaction_data->amount) . '</b></div>';
        if ($this->transaction_data->coupon_id != null) {
            $coupon_name = my_wallet_get_coupon_name_by_id($this->transaction_data->coupon_id);
            echo '<div class="left-border-bar white-background pad-10"><b>Coupon Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($coupon_name) . '</b></div>';
        }
        echo '</div>
        </div>';
    }
}
