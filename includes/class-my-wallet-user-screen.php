<?php

/**
 * This class defines all code necessary to display all users and to perform actions like
 * adding money in wallet, displaying wallet money etc...
 */

/**
 * Fired when user clicks on 'my wallet user screen'.
 *
 *  This class defines all code necessary to display all users and to perform actions like
 *  adding money in wallet, displaying wallet money etc...
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_Wallet_User_Screen {

    public $users = [];
    public $type;
    public $phone_number = null;
    public function __construct($type = null)
    {
        
        if($type == null){ //for all users
            $this->users = get_users();
        }else if($type == 'email'){ //for search by email
            $email = sanitize_text_field( wp_unslash($_POST['search_text']));
            $object = get_user_by('email', $email);
            if($object != null){
                $object = $object->data;
                $this->users = [$object];
            }            
        }else{ // for search by phone
            $phone= sanitize_text_field( wp_unslash($_POST['search_text']));
            $this->users = get_users(array(
                'meta_key' => 'billing_phone',
                'meta_value' => $phone
            ));
        }
        $this->type = $type;
        // $this->users = array_reverse($this->users);
        $this->getScreen();
        wp_enqueue_script( 'ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false );       
    }
    
    /**
     * This function responsible to display mywallet user screen.
     */
    public function getScreen() {                
        $count = 0;
        echo '<div id="message" class="updated notice is-dismissible mywallet-head"><p>Amount added successfully.</p>
        <button type="button" id="notice-dismiss" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
        </button></div>
        <div class="mywallet-head my-wallet-search">
            <form method="post">
            <label class="my-wallet-font-search"><b>Search By</b></label>&nbsp;
            <select name="search_type" id="search">
                <option selected="selected" value="email">Email</option>
                <option value="phone">Phone Number</option>
            </select>
            <input type="search" id="" name="search_text" value="" class="va-1">
            <button type="submit" class="button" name="search">Search</button>
            </form>
        </div>        
        <div class="my-wallet-settings-header">
        <h1 class="wp-heading-inline"><a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_menu")) . '">My Wallet </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        Manage Users
        </h1>
		</div>
        <div class="wrap">
        <table class="widefat fixed mywallet-table sortable" cellspacing="0">
        <thead>
            <tr>  
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>User ID</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Name</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Email</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Phone</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Balance</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Status</b></th>    
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Credit Amount</b></th>    
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Wallet Transactions</b></th>
            </tr>
        </thead>
        <tbody>';
        $my_wallet_currency = get_option('woocommerce_currency');        
        foreach($this->users as $user){
            $balance = get_user_meta( $user->ID, 'my_wallet', true );
            $is_wallet_active = get_user_meta( $user->ID, 'my_wallet_active', true );            
            $phone = $this->phone_number == null ? $user->billing_phone : $this->phone_number;
            if($count % 2 == 0){
                $class = "alternate";
            }else{
                $class = "";
            }
                echo '<tr class='.esc_html($class).'>
                <td class="column-columnname">'.esc_html($user->ID).'</td>
                <td class="column-columnname">'.esc_html($user->display_name).'</td>
                <td class="column-columnname">'.esc_html($user->user_email).'</td>
                <td class="column-columnname">'.esc_html($phone).'</td>
                <td class="column-columnname" id="balance'.esc_html($user->ID).'">'.wc_price($balance).'</td>
                <td class="column-columnname">
                    <div class="">
                        <span>';
                        echo $is_wallet_active ? '<div class="my-wallet-redirect pad-5-24 w-50 cursor-pointer" id="btn'.esc_html($user->ID).'" onclick="changeWalletStatus(0, '.esc_html($user->ID).')">Active</div>' : '<div class="my-wallet-redirect pad-5-24 w-50 cursor-pointer" onclick="changeWalletStatus(1, '.esc_html($user->ID).')">Deactive</div>';
                        echo '</span>                        
                    </div>
                </td>
                <td class="column-columnname">
                    <div class="">
                        <span class="d-flex flex-dir-row">
                        <input type="text" id="user'.esc_html($user->ID).'" class="add-amount-input" placeholder="">
                        <div class="my-wallet-redirect ml-5 w-50 cursor-pointer pad-4-5" onclick="addAmountToUserAccount('.esc_html($user->ID).','. "'$my_wallet_currency'" .')">Add</div>
                        </span>                        
                    </div>
                </td>
                <td class="column-columnname text-center">
                    <div class="">
                        <span><a href="'.esc_url( admin_url( "admin.php?page=user-transaction-detail-page&user_id=$user->ID")).'">View</a></span>                        
                    </div>
                </td>
            </tr>';                
            ++$count;
        }

        if(count($this->users) == 0){
            echo '<tr>
            <td colspan="6" class="text-center">No user available</td>
            </tr>';
        }

        echo '</tbody>           
            </table>
            </div>';      
    }
}
?>

