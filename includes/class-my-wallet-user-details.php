<?php

/**
 * This class defines all code necessary to display all transactions of a specific user'.
 */

/**
 * Fired when user clicks on 'view transaction details linik'.
 *
 *  This class defines all code necessary to display all transactions of a specific user'.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_Wallet_User_Detail_Page_Screen
{

    public $results;
    public $user_id = null; 
    public function __construct($user_id)
    {
        global $wpdb;
        $this->user_id = $user_id;
        $table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
        if (isset($_POST['search']) && !empty($_POST['search_text'])) {
            $transaction_id = sanitize_text_field( wp_unslash($_POST['search_text']));
            $this->results = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE user_id = %s AND transaction_id = %s ORDER BY id DESC', $user_id, $transaction_id));
        }else{
            $this->results = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE user_id = %s ORDER BY id DESC', $user_id));
        }
        wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
        $this->getScreen();
    }

    /**
     * This function responsible for displaying all transactions by specific user.
     */
    public function getScreen()
    {
        $count = 0;
        echo '
        <div class="mywallet-head my-wallet-search">
            <form method="post">            
            <input type="search" id="" name="search_text" value="" class="va-1" placeholder="Transaction ID">
            <button type="submit" class="button" name="search">Search</button>
            </form>
        </div>
        <div class="my-wallet-settings-header">
        <h1 class="wp-heading-inline"><a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_menu")) . '">My Wallet </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        <a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_coupons")) . '">Coupons </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        <a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_list_all_transactions")) . '">Transactions </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        User Details 
        </h1>
		</div>';
        if (isset($this->results[0]) && isset($this->results[0]->id)) {
            $user_id = $this->results[0]->user_id;
            $user = get_user_by('id', $user_id);
            echo '<div class="my-wallet-coupon-details">
            <div class="left-border-bar white-background pad-20 add-border-shadow"><b>User ID&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($user_id) . '</b></div>
            <div class="left-border-bar white-background pad-20 ml-20 add-border-shadow"><b>Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($user->display_name) . '</b></div>
            <div class="left-border-bar white-background pad-20 ml-20 add-border-shadow"><b>Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . esc_html($user->user_email) . '</b></div>
            </div>';
            
            echo '<table class="widefat fixed mywallet-table sortable" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>SR.No.</b></th>    
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Transaction ID</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Transaction type</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Amount</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Date</b></th>    
            </tr>
        </thead>
        <tbody>';

            $user_ids = array();
            $count = 1;
            foreach ($this->results as $result) {
                if ($count % 2 == 0) {
                    $class = "alternate";
                } else {
                    $class = "";
                }
                $date = change_date_time_format($result->date);
                echo '<tr class=' . esc_html($class) . '>
                <td class="column-columnname">' . esc_html($count) . '</td>
                <td class="column-columnname">' . esc_html($result->transaction_id) . '</td>';
                if($result->order_id){
                    echo '<td class="column-columnname">' . esc_html($result->transaction_type) . ' ( Order ID : <a class="text-deco-none" href="' . esc_url(admin_url("post.php?post=$result->order_id&action=edit")) . '">'.esc_html($result->order_id).'</a> )</td>';
                }else{
                    echo '<td class="column-columnname">' . esc_html($result->transaction_type) . '</td>';
                }
                echo '<td class="column-columnname">' . wc_price($result->amount) . '</td>
                <td class="column-columnname">' . esc_html($date) . '</td>
            </tr>';
                ++$count;
            }
            echo '</tbody>           
            </table> ';
        } else {
            echo '
            <div class="d-flex justify-center mt-50">
            <div class="my-wallet-no-transaction-card">
            <h1>No transaction history available.</h1>
            </div>
            </div>
            ';
        }
    }
}
