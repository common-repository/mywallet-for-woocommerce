<?php
/**
 * Adding styles and js files
 * my-wallet-todo : we have to remove it from here and keep it at one place for whole plugin
 */
wp_enqueue_script('ajax-script', MY_WALLET_WOOCOMMERCE_JS_URL . 'settings.js', false);
wp_localize_script('ajax-script', 'ajax_var', array(
    'license_nonce' => wp_create_nonce('ajax-nonce'),
    'reloadurl' => admin_url('admin.php?page=my_wallet_menu'),
));
?>


<!-- creating layout for license key validation page [Screen no. 01] -->
<div class="main-div-activation">
    <div class="center-activation">
        <form action="" id="activation_form">
            <div><b>Enter your license Key below</b></div>
            <input type="text" id="activation_input" placeholder="xxxx-xxxx-xxxx-xxxx"><br>
            <button class="my-wallet-btn" id="submit-activation-btn" type="submit" name="activate_plugin">Activate</button>
            <p class="red d-none" id="invalid-license-msg"><b>Invalid license key</b></p>
        </form>
        <div id="activation-db-warning" class="d-none pad-0">
            <strong>Move to next step.</strong><br><br>
            <button class="my-wallet-btn"  onclick="manageTables()">Next</button>
        </div>
    </div>
</div>