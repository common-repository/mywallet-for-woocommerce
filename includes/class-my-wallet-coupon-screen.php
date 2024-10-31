<?php

/**
 * This class defines all code necessary to handle mywallet coupon screen.
 */

/**
 * Fired when user clicks on coupon screen menu.
 *
 * This class defines all code necessary to handle mywallet coupon screen.
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
class My_Wallet_Coupon_Screen
{

    public $coupons = [];
    public $msg = null;
    public $msg_type = null;
    public function __construct($msg = null, $msg_type = null)
    {
        global $wpdb;
        /**
         * Adding styles and js files
         * my-wallet-todo : we have to remove it from here and keep it at one place for whole plugin
         */
        wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);

        $this->msg = $msg;
        $this->msg_type = $msg_type;
        $table_name = 'wp_my_wallet_coupon' . my_wallet_get_suffix();

        /**
         * Getting all coupons details from databse.
         */
        // $this->coupons = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        $this->coupons = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' ORDER BY id DESC'));
        $this->getScreen();
    }

    /**
     * This function is responsible for displaying coupon screen content.
     */
    public function getScreen()
    {
        echo '<div class="my-wallet-settings-header">
        <h1 class="wp-heading-inline"><a class="text-deco-none" href="' . esc_url(admin_url("admin.php?page=my_wallet_menu")) . '">My Wallet </a><span class="give-settings-heading-sep dashicons dashicons-arrow-right-alt2"></span> 
        Coupons
        </h1>
		</div>';

        /**
         * showing error alert.
         */
        if ($this->msg_type == 'error' && $this->msg != null) {
            echo '<div id="my-wallet-alert-box" class="notice notice-error is-dismissible mywallet-head ml-0"><p>' . esc_html($this->msg). '</p>
           <button type="button" id="notice-dismiss" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
           </button></div>';
        }

        if ($this->msg_type == 'success' && $this->msg != null) {
            echo '<div id="my-wallet-alert-box" class="notice updated is-dismissible mywallet-head ml-0"><p>' . esc_html($this->msg) . '</p>
            <button type="button" id="notice-dismiss" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span>
            </button></div>';
        }


        //Coupon pop-up
        echo '<div id="background"></div>        
        <div class="main-box">
            <div class="upper-box">
                <div class="header w-150 my-wallet-redirect add-border-shadow add-coupon-btn" id="header-div" onclick="showCouponPopup()"><h3 class="d-inline color-white m-10">Create Coupon</h3></div>
                <div id="body-div" class="body d-none form-div">
                        <h2 id="coupon-form-heading" class="mt-0">Create Coupon</h2>
                        <form id="coupon-form" method="post">
                            <span>
                            <input type="search" class="w-90" name="coupon_name" id="coupon_name" placeholder="Enter your coupon here">                               
                            <div class="my-wallet-redirect cursor-pointer w-100 padding-8-5 mt-7" onclick="return false" id="generate_coupon"">Generate</div>
                            </span>
                            <br>
                            <input type="number" class="w-90" name="amount" id="amount" placeholder="Amount"><br><br>
                            <input type="number" class="w-90" name="usability" id="usability" placeholder="Usability">&nbsp;&nbsp;
                            <span class="tooltip"><b><i>i</i></b>
                            <span class="tooltiptext">How many time coupon can be reedem by users ?</span>
                            </span><br><br>
                            <div><label><b>&nbsp;For Specific user/s ? </b></label><input type="checkbox" class="ml-35" name="specific_user" id="specific_user" onclick="userSpecificCoupon()"></div>
                            <input type="search" class="mt-15 w-90 d-none" name="user_ids" id="user_ids" placeholder="Enter comma seprated email ids."><br>
                            <p><i><b>Note - </b>You can keep dates empty as per your requirement.</i></p>
                            <span>Valid from <input type="date" onchange="validateDate()" name="valid_from" id="valid_from"/></span><br><br>
                            <label for="valid_from" id="valid_from_error" class="d-none red mb-5"><i>Coupon start date should be greater than/equal to today.</i></label>                             
                            <span>Valid to &nbsp;&nbsp;&nbsp;&nbsp;<input type="date" name="valid_to" id="valid_to"/></span><br><br><br> 
                            <span class="my-wallet-redirect  cursor-pointer w-100 padding-8-5 mt-7 b-grey d-inline-flex" onclick="return false" id="close_coupon_popup"">Close</span>
                            <button class="my-wallet-redirect cursor-pointer b-none w-100 padding-8-5 mt-7 f-right" id="submit-btn" type="submit" name="add_coupon">Create</button>
                        </form>
                        <script>
                        </script>
                </div>
            </div>';

        if (count($this->coupons) != 0) {
            echo '<div class="table-box">
            <table class="widefat fixed mywallet-table sortable" cellspacing="0">
            <thead>
            <tr>  
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Coupon</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Amount</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Redemption limit</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Redemption count</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Valid for Users</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Valid from</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Valid till</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Created On</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Status</b></th>
                <th id="columnname" class="manage-column column-columnname" scope="col"><b>Action</b></th>
            </tr>
        </thead>
        <tbody>';
            $count = 0;
            /**
             * Generating individual coupon row.
             */
            foreach ($this->coupons as $coupon) {
                $class = "";
                $coupon_id = $coupon->id;
                $amount = my_wallet_get_coupon_meta($coupon_id, 'amount');
                $usability = my_wallet_get_coupon_meta($coupon_id, 'usability');
                $coupon_for_users = my_wallet_get_coupon_meta($coupon_id, 'coupon_for_users');
                $valid_from = my_wallet_get_coupon_meta($coupon_id, 'valid_from');
                $valid_to = my_wallet_get_coupon_meta($coupon_id, 'valid_to');
                $is_active = my_wallet_get_coupon_meta($coupon_id, 'is_active');
                $redeemption_count = my_wallet_get_coupon_meta($coupon_id, 'redeemption_count');

                if ($usability == "") {
                    $usability = "Unlimited";
                }

                if ($coupon_for_users != "All") {
                    $coupon_for_users = json_decode($coupon_for_users);
                    if ($coupon_for_users != null || $coupon_for_users != "") {
                        $coupon_for_users = implode(',<br>', $coupon_for_users);
                    }
                }

                $valid_from = $valid_from != null ? change_date_format($valid_from) : "-";
                $valid_to = $valid_to != null ? change_date_format($valid_to) : "-";
                $created_on = change_date_time_format($coupon->created_on);
                if ($count % 2 == 0) {
                    $class = "alternate";
                }
                echo '<tr class=' . esc_attr($class) . '>
                    <td class="column-columnname"><a href="' . esc_url(admin_url("admin.php?page=my-wallet-coupon-detail-page&coupon_id=$coupon_id")) . '">' . $coupon->coupon_name . '</a></td>
                    <td class="column-columnname">' . wc_price($amount) . '</td>
                    <td class="column-columnname">' . esc_html($usability) . '</td>
                    <td class="column-columnname">' . esc_html($redeemption_count) . '</td>
                    <td class="column-columnname">' . esc_html($coupon_for_users) . '</td>
                    <td class="column-columnname">' . esc_html($valid_from) . '</td>
                    <td class="column-columnname">' . esc_html($valid_to) . '</td>
                    <td class="column-columnname">' . esc_html($created_on) . '</td>
                    <td class="column-columnname">
                    <div class="">
                        <span>';
                echo $is_active ? '<div class="my-wallet-redirect pad-5-24 w-50 cursor-pointer" id="btn' . esc_html($coupon->id) . '" onclick="changeCouponStatus(0, ' . esc_html($coupon->id) . ')">Active</div>' : '<div class="my-wallet-redirect pad-5-24 w-50 cursor-pointer" id="btn' . esc_html($coupon->id) . '" onclick="changeCouponStatus(1, ' . esc_html($coupon->id) . ')">Deactive</div>';
                echo '</span>                        
                    </div>
                    </td>
                    <td class="column-columnname">
                    <div class="">
                        <span><a class="my-wallet-redirect pad-5-24 w-50 cursor-pointer b-grey" href=javascript:void(0); onclick="edit_coupon(\'' . esc_html($coupon->id) . '\')">Edit</a></span>                        
                    </div>
                    </td>
                </tr>';
                ++$count;
            }

            echo '</tbody>           
        </table>
            </div>
        </div>';
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
