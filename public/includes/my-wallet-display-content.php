<?php
wp_enqueue_style('my-wallet-admin', MY_WALLET_WOOCOMMERCE_CSS_URL . 'my_wallet_publlic_style.css', false, '1.0', 'all');
wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
wp_localize_script('ajax-script', 'admin_ajax_url', array(
    'url' => admin_url('admin-ajax.php?action='),
));

$error = null;
$success = null;
$user_wallet_balance;
$current_user_id = get_current_user_id();
$amount_of_coupon = 0;
global $wpdb;
if (isset($_POST['add_free_amount']) && isset($_POST['coupon_name']) && !empty($_POST['coupon_name'])) {
    $coupon_name = sanitize_text_field( wp_unslash($_POST['coupon_name']));
    $id = my_wallet_get_coupon_id_by_name($coupon_name);
    //check coupon is valid or not
    try {
        if ($id) {
            $is_coupon_active = my_wallet_get_coupon_meta($id, "is_active");

            //check coupon status
            if (!$is_coupon_active) {
                throw new Exception("Coupon is not active.");
            }

            //check valid from date validity
            $valid_from_date = my_wallet_get_coupon_meta($id, "valid_from");
            if ($valid_from_date != "") {
                $date = new DateTime($valid_from_date);
                $now = new DateTime();
                if ($date > $now) {
                    throw new Exception("Coupon is not valid yet.");
                }
            }

            //check valid till date validity
            $valid_till_date = my_wallet_get_coupon_meta($id, "valid_to");
            if ($valid_till_date != "") {
                $date = new DateTime($valid_till_date);
                $now = new DateTime();
                if ($date < $now) {
                    throw new Exception("Coupon is expired.");
                }
            }

            //check if valid for user or not
            $valid_for_email_ids = my_wallet_get_coupon_meta($id, "coupon_for_users");
            if ($valid_for_email_ids != "All") {
                $user = get_user_by('id', $current_user_id);
                $current_user_email_id = $user->user_email;
                $user_email_ids = json_decode($valid_for_email_ids);
                if (count($user_email_ids) > 0) {
                    if (!in_array($current_user_email_id, $user_email_ids)) {
                        throw new Exception("Coupon is not available for you");
                    }
                } else {
                    throw new Exception("Coupon is not available for you");
                }
            }

            //check if uset already avail coupon
            $already_used_by_users = my_wallet_get_coupon_meta($id, "already_used_by_users");

            if (in_array($current_user_id, explode(",", $already_used_by_users))) {
                throw new Exception("You have already used this coupon.");
            } else {
                $already_used_by_users = $already_used_by_users == "" ? $current_user_id :  $already_used_by_users . ',' . $current_user_id;
            }

            //check redeemption_limit
            $usability = my_wallet_get_coupon_meta($id, "usability");
            $redeemption_count = my_wallet_get_coupon_meta($id, "redeemption_count");

            if ($usability != "Unlimited") {
                if ($redeemption_count >= $usability) {
                    throw new Exception("Coupon use limit is over.");
                }
            }

            $amount_of_coupon = my_wallet_get_coupon_meta($id, "amount");
            update_wallet_balance($current_user_id, $amount_of_coupon);
            update_coupon_meta($id, "redeemption_count", $redeemption_count + 1);
            update_coupon_meta($id, "already_used_by_users", $already_used_by_users);
            // $currency = get_option('woocommerce_currency');
            $currency = get_woocommerce_currency_symbol();
            $table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
            $wpdb->insert($table_name, array(
                'user_id' => $current_user_id,
                'amount' => $amount_of_coupon,
                'currency' => $currency,
                'transaction_type' => 'Coupon Applied',
                'payment_method' => 'Coupon Money',
                'transaction_id' => get_transaction_id(),
                'date' => date('Y-m-d H:i:s'),
                'coupon_id' => $id
            ));
            $success = $coupon_name . " applied successfully.";
        } else {
            //error msg id not exsists.
            throw new Exception("Invalid coupon");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$user_wallet_balance = get_user_meta($current_user_id, "my_wallet")[0] + $amount_of_coupon;

$active_gateways = get_my_wallet_payment_gateways_for_public();

$valid_coupons = get_coupons_by_user_id($current_user_id);
$table_name = 'wp_my_wallet_transaction' . my_wallet_get_suffix();
$transactions = $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $table_name . ' WHERE user_id = %s ORDER BY id DESC', $current_user_id));

if ($error) {
    echo "<div class='error' id='error'>".esc_html($error)."<div class='close-error' onclick='closeErrorBox()'>x</div></div>";
}

if ($success) {
    echo "<div class='success' id='success'>".esc_html($success)."<div class='close-success' onclick='closeSuccessBox()'>x</div></div>";
}
?>

<h2 class="d-inline"><b>MyWallet Details</b></h2>
<span class="f-right wallet-balance">MyWallet Balance <strong><?php echo wc_price($user_wallet_balance) ?></strong></span>
<div class="my_wallet_main_div" id="my_wallet_main_div">
    <div class="my_wallet_header_div mt-5" id="top-up-form-header" onclick="topUpWalletForm(true)">
        <div><span><b>Topup Your Wallet</b></span><span class="carot"><b>
                    <h3 id="top-up-form-carot" class="m-0">+</h3>
                </b></span></div>
    </div>
    <div style="display: none;" class="my_wallet_body_div" id="top-up-form-body">
        <div class="top-up-div">
            <div class="top-up-div-header">
                <h2 class="h2-size top-header-content">Add Fund To Wallet</h2>
            </div>
            <div class="top-up-div-body">
                <div class="prices-buttons">
                    <button class="price-btn" onclick="setAmount('25')"><b><?php echo wc_price(25) ?></b></button>
                    <button class="price-btn" onclick="setAmount('50')"><b><?php echo wc_price(50) ?></b></button>
                    <button class="price-btn" onclick="setAmount('75')"><b><?php echo wc_price(75) ?></b></button>
                </div>
                <input type="number" name="custom_amount" id="custom_amount" placeholder="Custom Amount" onchange="unselectPaymentGatewayRadio()">
                <h4 class="mt-10 p-top">Select Payment Mode</h4>
                <div class="payment-options">
                    <?php
                    foreach ($active_gateways as $gateway) {
                        // echo '<span>' . $gateway->title . '</span>&nbsp;&nbsp;<input type="radio" name="payment-mode" id="' . $gateway->id . '" onclick="managePaymentMethodCheckout(\'' . $gateway->id . '\',null, \'my_account_add_money\')">';
                        echo '<input type="radio" name="payment-mode" id="' . esc_html($gateway->id) . '" onclick="managePaymentMethodCheckout(\'' . esc_html($gateway->id) . '\',null, \'my_account_add_money\')"> &nbsp;&nbsp;<span>' . esc_html($gateway->title) . '</span>';
                    }
                    // echo '&nbsp;&nbsp;<span>PayU</span>&nbsp;&nbsp;<input type="radio" name="payment-mode" id="payu" onclick="managePaymentMethodCheckout(\'payu\')">';
                    if (count($active_gateways) == 0) {
                        echo '<p>No payment option is available at the moment.</p>';
                    }
                    ?>
                </div>
                <button id="add-money-btn" class="price-btn mt-20 add-money-btn"><b>Add Money</b></button>
                <div id="paypal-payment-button"></div>
                <p class="d-none text-danger" id="select-gateway-error">Please select a payment gateway.</p>
            </div>
        </div>
    </div>

    <div class="my_wallet_header_div mt-5" id="add-money-form-header" onclick="addCouponForm(true)">
        <div><span><b>Add Fund By Coupon</b></span><span class="carot"><b>
                    <h3 id="add-coupon-form-carot" class="m-0">+</h3>
                </b></span></div>
    </div>

    <div class="my_wallet_body_div d-none" id="add-money-form-body">
        <div class="coupon-box">
            <div class="coupon-heading"><b><i><?php echo count($valid_coupons) > 0 ? "Available coupons for you." : "No coupon available." ?></i></b></div>
            <div class="coupon">
                <?php
                foreach ($valid_coupons as $coupon) {
                    $amount = my_wallet_get_coupon_meta($coupon->coupon_id, 'amount');
                    echo '<span class="mx-5 coupon-item tooltip" onclick="fillCouponInInput(\'' . esc_html($coupon->coupon_name) . '\')">' . esc_html($coupon->coupon_name) . ' ( '.wc_price($amount).' )</span>';                    
                }
                ?>
            </div>
        </div>
        <div class="add-coupon-div">
            <form method="post">
                <h4>Enter your coupon to avail free amount.</h4>
                <input type="text" name="coupon_name" id="coupon_name" placeholder="Coupon Code">
                <button type="submit" id="add_coupon_money_btn" name="add_free_amount">Get Amount</button>
            </form>
        </div>
    </div>


    <?php
    if (count($transactions) > 0) {
    ?>
        <div class="my_wallet_header_div mt-5" id="wallet-transfer-form-header" onclick="walletTransferForm(true)">
            <div><span><b>All Transactions</b></span><span class="carot"><b>
                        <h3 id="wallet-transfer-form-carot" class="m-0">+</h3>
                    </b></span></div>
        </div>
        <div class="my_wallet_body_div d-none p-20" id="wallet-transfer-form-body">
        <div>            
            <span class="mb-5 f-600">No. of transactions</span>
            <select id="number-of-records" class="f-right" onchange="showNumberOfRecords()">
                <option value="5" selected>5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>
        </div>

            <table class="mt-25">
                <tr>
                    <!-- <th>Id</th> -->
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Narration</th>
                    <th>Transaction type</th>
                </tr>
                <?php
                $count = 1;
                foreach ($transactions as $transaction) {
                    if ($count > 5) {
                        $class = 'd-none';
                    } else {
                        $class = '';
                    }
                    echo '<tr id=\'record' . $count++ . '\' class=\'' . esc_html($class) . ' record\'>              
                    <td>' . esc_html($transaction->transaction_id) . '</td>
                    <td>' . esc_html($transaction->currency . '' . $transaction->amount) . '</td>
                    <td>' . esc_html($transaction->payment_method) . '</td>';
                    if($transaction->order_id){
                        echo '<td>' . esc_html($transaction->transaction_type) . ' ( Order ID :'.esc_html($transaction->order_id) .')</td>';
                    }else{
                        echo '<td>' . esc_html($transaction->transaction_type) . '</td>';
                    }
                echo '</tr>';
                }
                ?>
            </table>
            <?php
            if(count($transactions) > 5){
                ?>
                <nav id="pagination-btn-box">
                    <ul class="pagination">
                        <?php
                        $no_of_btns = ceil(count($transactions) / 5);
                        for ($i = 1; $i <= $no_of_btns; $i++) {
                            echo '<li class="page-item" id="btn-' . esc_html($i) . '" onclick="getPaginationData(' . esc_html($i) . ')"><a class="page-link btn" id="link-' . esc_html($i) . '" href="javascript:void(0)">' . esc_html($i) . '</a></li>';
                        }
                        ?>
                    </ul>
                </nav>
                <?php
            }
            ?>
        </div>

    <?php
    }
    ?>
</div>