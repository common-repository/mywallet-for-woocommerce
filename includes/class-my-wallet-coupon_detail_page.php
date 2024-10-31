<?php

/**
 * This class defines all code necessary to handle mywallet coupon detail page screen'.
 */

/**
 * Fired when user clicks on 'coupon detail page screen link'.
 *
 * This class defines all code necessary to handle mywallet coupon detail page screen'.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_wallet_coupon_detail
{
    public $coupon_transactions = array();
    public $coupon_id = null;
    public $coupon_name = "";
    public function __construct($coupon_id, $type = null)
    {
        global $wpdb;

        /**
         * Adding styles and js files
         * my-wallet-todo : we have to remove it from here and keep it at one place for whole plugin
         */
        wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
        $this->coupon_id = $coupon_id;
        $table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();

        // Getting all transactional records from database with where coupon id clause.  
        if ($type == null) { //for all users
            $this->coupon_transactions = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE coupon_id = %s ORDER BY id DESC', $coupon_id));    
        } else if ($type == 'email') { //for search by email
            $email = sanitize_text_field( wp_unslash($_POST['search_text']));
            $object = get_user_by('email', $email);
            if ($object != null) {
                $user_id = $object->data->ID;
                $this->coupon_transactions = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE coupon_id = %s AND user_id=%s ORDER BY id DESC', $coupon_id, $user_id));    
            }
        } else if ($type == 'transaction_id') {
            $transaction_id = sanitize_text_field( wp_unslash($_POST['search_text']));
            $this->coupon_transactions = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE coupon_id = %s AND transaction_id=%s ORDER BY id DESC', $coupon_id, $transaction_id));
        } else { //search with phone number
            $phone= sanitize_text_field( wp_unslash($_POST['search_text']));
            $object = get_users(array(
                'meta_key' => 'billing_phone',
                'meta_value' => $phone
            ));
            if($object != null){
                $user_id = $object[0]->data->ID;
                $this->coupon_transactions = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE coupon_id = %s AND user_id=%s ORDER BY id DESC', $coupon_id, $user_id));
            }
        }

        $this->coupon_name = my_wallet_get_coupon_name_by_id($coupon_id);
        $this->getScreen();
    }

    public function getScreen()
    {
        $count = 1;
        echo '
        <div class="mywallet-head my-wallet-search">
            <form method="post">
            <label class="my-wallet-font-search"><b>Search By</b></label>&nbsp;
            <select name="search_type" id="search">
                <option selected="selected" value="transaction_id">Transaction ID</option>
                <option value="email">Email</option>
                <option value="phone">Phone No.</option>
            </select>
            <input type="search" id="" name="search_text" value="" class="va-1">
            <button type="submit" class="button" name="search">Search</button>
            </form>
        </div>
        <div class="my-wallet-settings-header">        
        <h1 class="wp-heading-inline"><a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_menu")) . '">My Wallet </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        <a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_coupons")) . '">Coupons </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        Transactions
        </h1>
		</div>
        <div class="my-wallet-coupon-details">
        <div class="left-border-bar white-background pad-20 add-border-shadow"><b>Coupon ID&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . $this->coupon_id . '</b></div>        
        <div class="left-border-bar white-background pad-20 ml-20 add-border-shadow"><b>Coupon Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . $this->coupon_name . '</b></div>
        </div>
        ';

        if (count($this->coupon_transactions) > 0) {
            echo '<table class="widefat fixed mywallet-table sortable" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Sr. No.</b></th> 
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Name</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Email</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Phone</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Transaction ID</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Transaction Type</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Amount</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Date</b></th> 
            </tr>
        </thead>
        <tbody>';

            foreach ($this->coupon_transactions as $transaction) {
                $user_id = $transaction->user_id;
                $user = get_user_by( 'id', $user_id );
                $user_email = $user->data->user_email;
                $user_name = $user->data->display_name;;
                $phone = $this->phone_number == null ? $user->billing_phone : $this->phone_number;
                $date = change_date_time_format($transaction->date);
                if ($count % 2 == 0) {
                    $class = "alternate";
                } else {
                    $class = "";
                }
                echo '<tr class=' . esc_attr($class) . '>
                <td class="column-columnname">' . esc_html($count) . '</td>
                <td class="column-columnname"> <a href="' . esc_url(admin_url("admin.php?page=my-wallet-user-detail-page&user_id=$transaction->user_id")) . '">' . $user_name . '</a></td>
                <td class="column-columnname">' . esc_html($user_email) . '</td>
                <td class="column-columnname">' . esc_html($phone) . '</td>
                <td class="column-columnname">' . esc_html($transaction->transaction_id) . '</td>
                <td class="column-columnname">' . esc_html($transaction->transaction_type) . '</td>
                <td class="column-columnname">' . wc_price($transaction->amount) . '</td>
                <td class="column-columnname">' . esc_html($date) . '</td>                    
                </tr>';
                ++$count;
            }
        } else {
            echo '
            <div class="d-flex justify-center mt-50">
            <div class="my-wallet-no-transaction-card">
            <h1>No transaction history available.</h1>
            </div>
            </div>
            ';
        }
        echo '</tbody>           
            </table> ';
    }
}
