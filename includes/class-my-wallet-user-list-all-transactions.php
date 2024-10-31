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
class My_Wallet_User_List_All_Transaction_Screen
{

    public $results;
    public $user_name = "";
    public function __construct()
    {
        global $wpdb;
        $user_id = sanitize_text_field( wp_unslash($_GET['user_id']));
        $table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
        // $this->results = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id ORDER BY id DESC");
        $this->results = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE user_id = %s ORDER BY id DESC', $user_id));
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
        <div class="my-wallet-settings-header">
        <h1 class="wp-heading-inline"><a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_menu")) . '">My Wallet </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        <a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_users")) . '">Users </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        All Transactions 
        </h1>
		</div>';
        // <div class="mywallet-head my-wallet-redirect w-70"><a class="sanatize-link" href="' . esc_url(admin_url("admin.php?page=my_wallet_users")) . '">All Users</a></div>';
        if (isset($this->results[0]) && isset($this->results[0]->id)) {
            $user_id = $this->results[0]->user_id;
            $user = get_user_by('id', $user_id);
            echo '<div class="wrap">
            <div class="my-wallet-coupon-details">
            <div class="left-border-bar white-background pad-20 add-border-shadow"><b>User ID&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . $user_id . '</b></div>
            <div class="left-border-bar white-background pad-20 ml-20 add-border-shadow"><b>Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . $user->display_name . '</b></div>
            <div class="left-border-bar white-background pad-20 ml-20 add-border-shadow"><b>Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;' . $user->user_email . '</b></div>
            </div>';
            
            echo '<table class="widefat fixed mywallet-table sortable" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Sr. No.</b></th>    
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

                $is_coupon_available = 0;
                $coupon_name = "";
                if($result->coupon_id != null){
                    $coupon_name = my_wallet_get_coupon_name_by_id($result->coupon_id);
                    $is_coupon_available = 1;
                }

                $transaction_type = $is_coupon_available ? $result->transaction_type .' ( <a href="' . esc_url(admin_url("admin.php?page=my-wallet-coupon-detail-page&coupon_id=$result->coupon_id")) . '">'. $coupon_name . '</a> )' : $result->transaction_type;

                $date = change_date_time_format($result->date);
                echo '<tr class=' . esc_html($class) . '>
                <td class="column-columnname">' . esc_html($count) . '</td>
                <td class="column-columnname">' . esc_html($result->transaction_id) . '</td>';
                if($result->order_id){
                    echo '<td class="column-columnname">' . esc_html($transaction_type) . ' ( Order ID : <a class="text-deco-none" href="' . esc_url(admin_url("post.php?post=$result->order_id&action=edit")) . '">'.esc_html($result->order_id).'</a> )</td>';
                }else{
                    echo '<td class="column-columnname">' . esc_html($transaction_type) . '</td>';
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
        echo '</div>';
    }
}
