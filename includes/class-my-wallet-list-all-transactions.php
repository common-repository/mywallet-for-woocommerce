<?php

/**
 * This class defines all code necessary to handle mywallet 'all transactions screen'.
 */

/**
 * Fired when user clicks on 'all transactions screen menu'.
 *
 * This class defines all code necessary to handle mywallet 'all transactions screen'.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_Wallet_List_All_Transaction_Screen
{

    public $results;
    public function __construct($type = null)
    {
        global $wpdb;
        $table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
        // Getting all transactional records from database.  
        if ($type == null) { //for all users
            $this->results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        } else if ($type == 'email') { //for search by email
            $email = sanitize_text_field( wp_unslash($_POST['search_text']));
            $object = get_user_by('email', $email);
            if ($object != null) {
                $user_id = $object->data->ID;
                $this->results = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE user_id = %s ORDER BY id DESC', $user_id));
            }
        } else if ($type == 'transaction_id') {
            $transaction_id = sanitize_text_field( wp_unslash($_POST['search_text']));
            $this->results = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE transaction_id = %s ORDER BY id DESC', $transaction_id));
        } else { //search with phone number
            $phone = sanitize_text_field( wp_unslash($_POST['search_text']));
            $object = get_users(array(
                'meta_key' => 'billing_phone',
                'meta_value' => $phone
            ));
            if ($object != null) {
                $user_id = $object[0]->data->ID;
                $this->results = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE user_id = %s ORDER BY id DESC', $user_id));
            }
        }

        /**
         * Adding styles and js files
         * my-wallet-todo : we have to remove it from here and keep it at one place for whole plugin
         */
        wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
        $this->getScreen();
    }


    /**
     * This function is responsible for displaying all transactions screen.
     */
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
        Transactions
        </h1>
		</div>';
        if (count($this->results) > 0) {
            echo '<div class="wrap">
            <table class="widefat fixed mywallet-table sortable" cellspacing="0">
        <thead>
            <tr>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Sr. No</b></th>    
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>User Name</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Email</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Phone</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Transaction ID</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Amount</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Transaction details</b></th>    
            </tr>
        </thead>
        <tbody>';


            $user_ids = array();
            foreach ($this->results as $result) {
                if (array_key_exists($result->user_id, $user_ids)) {
                    $user = $user_ids[$result->user_id];
                } else {
                    $user = get_user_by('id', $result->user_id);
                    $user_ids[$result->user_id] = $user;
                }
                $name = $user->display_name;
                $email = $user->user_email;
                $phone = null;
                if(isset($user->billing_phone)){
                    $phone = $user->billing_phone;
                }
                if ($count % 2 == 0) {
                    $class = "alternate";
                } else {
                    $class = "";
                }
                echo '<tr class=' . esc_html($class) . '>
                <td class="column-columnname">' . esc_html($count) . '</td>
                <td class="column-columnname"><a href="' . esc_url(admin_url("admin.php?page=my-wallet-user-detail-page&user_id=$result->user_id")) . '">' . $name . '</a></td>
                <td class="column-columnname">' . esc_html($email) . '</td>
                <td class="column-columnname">' . esc_html($phone) . '</td>
                <td class="column-columnname">' . esc_html($result->transaction_id) . '</td>
                <td class="column-columnname">' . wc_price($result->amount) . '</td>
                <td class="column-columnname">
                    <div class="">
                        <span><a href="' . esc_url(admin_url("admin.php?page=transaction-detail-page&transaction_id=$result->transaction_id&user_id=$result->user_id")) . '">View</a></span>                        
                    </div>
                </td>
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
            </table> 
            </div>';
    }
}
