if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

function topUpWalletForm($flag) {
    if ($flag) {
        jQuery('#top-up-form-body').css('display', 'flex');
        jQuery('#top-up-form-carot').text('-');
        jQuery('#top-up-form-header').attr("onclick", "topUpWalletForm(false)");
    } else {
        jQuery('#top-up-form-body').css('display', 'none');
        jQuery('#top-up-form-carot').text('+');
        jQuery('#top-up-form-header').attr("onclick", "topUpWalletForm(true)");
    }
}

function addCouponForm($flag) {
    if ($flag) {
        jQuery('#add-money-form-body').css('display', 'flex');
        jQuery('#add-coupon-form-carot').text('-');
        jQuery('#add-money-form-header').attr("onclick", "addCouponForm(false)");
    } else {
        jQuery('#add-money-form-body').css('display', 'none');
        jQuery('#add-coupon-form-carot').text('+');
        jQuery('#add-money-form-header').attr("onclick", "addCouponForm(true)");
    }
}

function walletTransferForm($flag) {
    if ($flag) {
        jQuery('#wallet-transfer-form-body').css('display', 'flex');
        jQuery('#wallet-transfer-form-carot').text('-');
        jQuery('#wallet-transfer-form-header').attr("onclick", "walletTransferForm(false)");
    } else {
        jQuery('#wallet-transfer-form-body').css('display', 'none');
        jQuery('#wallet-transfer-form-carot').text('+');
        jQuery('#wallet-transfer-form-header').attr("onclick", "walletTransferForm(true)");
    }
}

function closeErrorBox() {
    jQuery('#error').css('display', 'none');
}

function closeSuccessBox() {
    jQuery('#success').css('display', 'none');
}

function setAmount(amount) {
    jQuery("#custom_amount").val(amount);
}

function unselectPaymentGatewayRadio() {
    jQuery('#ppec_paypal').prop('checked', false);
}

function paypalPaymentGateway(amount, location) {
    if (amount == null || amount == 0 || amount == "") {
        //we have top show alert to user.
    }
    var script = document.createElement('script');
    script.id = 'payment-script';
    document.getElementsByTagName('body')[0].appendChild(script);
    script.onload = () => {
        paypal.Buttons({
            style: {
                color: 'white',
                shape: 'rect',
            },
            createOrder: function (data, actions) {
                // amount = amount == null ? jQuery('#custom_amount').val() : amount;
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount
                        }
                    }]
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    if (details.status == "COMPLETED") {
                        details = JSON.stringify(details);
                        switch (location) {
                            case "my_account_add_money":
                                var url = admin_ajax_url.url + "add_remaining_money_to_user_wallet";
                                jQuery.ajax({
                                    url,
                                    type: 'post',
                                    data: 'data=' + details,
                                    success: function (result) {
                                        window.location.reload();
                                    }
                                });
                                break;
                            case "checkout":
                                var url = admin_ajax_url.url + "add_remaining_money_to_user_wallet";
                                jQuery.ajax({
                                    url,
                                    type: 'post',
                                    data: 'data=' + details,
                                    success: function (result) {
                                        jQuery("#payment").css('display', 'block');
                                        jQuery("#my-wallet-box").css("display", "none");
                                        // jQuery(document.body).trigger("update_checkout");
                                        window.location.reload();
                                    }
                                });
                                break;
                            default:
                                console.log("Switch statement default block.");
                                break;
                        }
                    }
                });
            },
            onCancel: function (data) {
                jQuery('#error').text("Oops money is not added, please try again.");
                jQuery('#error').css('display', 'block');
            }
        }).render('#paypal-payment-button');
    };
    // script.src = 'https://www.paypal.com/sdk/js?client-id=Af9CkV8u4YgXVhCQ0bqc6warYmzHFkqiPTJip8LriY9ozHnPo970crXSW3D6q1z6NOiLA69lr-47pqYh&disable-funding=credit,card';

    var url = admin_ajax_url.url + "get_client_id_for_paypal_transaction";
    jQuery.ajax({
        url,
        type: 'post',
        data: "",
        success: function (result) {
            if(result != "id not found"){
                script.src = `https://www.paypal.com/sdk/js?client-id=${result}&disable-funding=credit,card`;
            }else{
                alert(result);
            }
        }
    });
}

function managePaymentMethodCheckout(payment_gateway_id, amount = null, location = null) {
    var is_payment_script_available = jQuery('#payment-script').length != 0;

    if (is_payment_script_available) {
        jQuery('#payment-script').remove();
    }
    if (amount == null) {
        amount = jQuery('#custom_amount').val();
    }
    switch (payment_gateway_id) {
        case 'ppec_paypal':
            paypalPaymentGateway(amount, location);
            jQuery('#add-money-btn').css('display', 'none');
            break;
        default:
            console.log('Default');
    }
}

function payViaOtherOptions() {
    jQuery("#my-wallet-box").css("display", "none");
    jQuery("#payment").css("display", "block");
}

function addRemainingAmountToWallet(amount) {
    var url = admin_ajax_url.url + "get_payment_gateways_during_checkout";
    jQuery.ajax({
        url,
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {},
        success: function (gatways) {
            jQuery("#loader").removeClass('loader');
            if (gatways.length > 0) {
                window.scrollTo(0, 0);
                jQuery('body').css('overflow', 'hidden');
                var backgroundDiv = document.createElement('div');
                backgroundDiv.classList.add('pop-up-background');
                backgroundDiv.id = 'background';
                document.body.appendChild(backgroundDiv);
                var mainDiv = document.createElement('div');
                mainDiv.id = "my-wallet-checkout-popup-main-div";
                mainDiv.classList.add('checkout-popup-main-div');
                var htmlString = "";
                gatways.forEach(gateway => {
                    htmlString += `<input type="radio" checked name="payment-mode" id="${gateway.id}" onclick="managePaymentMethodCheckout('${gateway.id}','${amount}','checkout')">&nbsp;&nbsp;<span>${gateway.title} <img class="my-wallet-paypal-icon" src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" border="0" alt="PayPal Logo"></span><br>`;
                    
                });
                // htmlString += `<input type="radio" name="payment-mode"> &nbsp;Stripe </span>`;
                var html = `
                <div>
                    <span class="mywallet-checkout-close-btn" onclick="closeMyWalletCheckoutPopup()">x</span>
                    <div class="checkout-popup-head"><b>Choose payment gateway</b></div><br>
                    ${htmlString}
                </div>
                <div id="paypal-payment-button"></div>
                `;
                mainDiv.innerHTML = html;
                document.body.appendChild(mainDiv);
                managePaymentMethodCheckout('ppec_paypal', amount, 'checkout'); //calling for showing paypal default.
            } else {
                //Todo: empty box with msg

            }
        }
    });
}

function closeMyWalletCheckoutPopup() {
    jQuery('#background').removeClass('pop-up-background');
    jQuery('#my-wallet-checkout-popup-main-div').css('display', 'none');
    jQuery('body').css('overflow', 'inherit');
    window.location.reload();
}

function fillCouponInInput(coupon) {
    jQuery('#coupon_name').val(coupon.trim());
}


function showNumberOfRecords() {
    var showNumOfRecords = jQuery('#number-of-records').val();
    var allRecords = jQuery('.record');

    if (showNumOfRecords === "all") {
        Array.from(allRecords).forEach(record => {
            record.classList.remove("d-none");
        });
        jQuery('#pagination-btn-box').remove();
    } else {
        Array.from(allRecords).forEach(record => {
            if (parseInt(record.id.replace("record", "").trim()) <= showNumOfRecords) {
                record.classList.remove("d-none");
            } else {
                record.classList.add("d-none");
            }
        });

        jQuery('#pagination-btn-box').remove();
        var nav = document.createElement('nav');
        nav.id = 'pagination-btn-box';
        var buttons = `<ul class="pagination">`;

        var no_of_btns = Math.ceil(Array.from(allRecords).length / showNumOfRecords);
        for (var i = 1; i <= no_of_btns; i++) {
            buttons += `<li class="page-item" id="btn-${i}" onclick="getPaginationData(${i})"><a class="page-link btn"  id="link-${i}" href="javascript:void(0)">${i}</a></li>`;
        }

        buttons += `</ul>`;
        nav.innerHTML = buttons;
        jQuery("#wallet-transfer-form-body").append(nav);
    }
}

function getPaginationData(pageno) {
    let x = document.getElementsByClassName("active");
    if (x.length > 0) {
        x[0].classList.remove("active");
    }
    jQuery(`#link-${pageno}`).addClass('active');
    var numberOfRecords = jQuery('#number-of-records').val();
    if (numberOfRecords === "all") {
        Array.from(allRecords).forEach(record => {
            record.classList.remove("d-none");
        });
    } else {
        showingFromId = (numberOfRecords * pageno) - numberOfRecords;
        showingTillId = showingFromId + parseInt(numberOfRecords);
        var allRecords = jQuery('.record');
        Array.from(allRecords).forEach(record => {
            var id = parseInt(record.id.replace("record", "").trim());
            if (id > showingFromId && id <= showingTillId) {
                record.classList.remove("d-none");
            } else {
                record.classList.add("d-none");
            }
        });
    }
}

jQuery('#add-money-btn').on('click', () => {
    jQuery("#select-gateway-error").css('display', 'block');
    setTimeout(() => {
        jQuery("#select-gateway-error").css('display', 'none');
    }, 2000);
});