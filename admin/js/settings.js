function manage_payment_gateway() {
  var gatewaysStatus = {};
  var is_gateway_key_valid = true;
  var secret_key_array = {};
  Array.from(jQuery('.mywallet-payment-gateway')).forEach(gateway => {
    gatewaysStatus[gateway.id] = gateway.checked;
    var value = '#my_wallet_gateway_value_' + gateway.id;
    value = jQuery(value).val();
    if (gatewaysStatus[gateway.id]) {
      var is_key_tested = jQuery(`#my_wallet_gateway_test_clicked_${gateway.id}`).val();
      is_gateway_key_valid = is_key_tested == 1 ? true : false;
      if (is_gateway_key_valid) {        
        secret_key_array[gateway.id] = value;
      }
    }
  });

  if (is_gateway_key_valid) {
    secret_key_array = JSON.stringify(secret_key_array);
    var my_wallet_data_delete_confirmation = jQuery('#my-wallet-data-delete-confirmation').prop('checked') == true;
    var url = 'admin-ajax.php?action=manage_payment_gateway_ajax_fun';
    jQuery.ajax({
      url,
      type: 'post',
      data: {
        'gatewaysStatus' : gatewaysStatus,
        'secret_key_array' : [secret_key_array],
        'my_wallet_data_delete_confirmation' : my_wallet_data_delete_confirmation
      },
      success: function (result) {
        if (result == "updated") {
          jQuery('#message').css('display', 'block');
        } else {
          alert('error');
        }
      }
    });
  } else {
    jQuery('#my_wallet_verify_key_alert').css('display', 'block');
    setTimeout(() => {
      jQuery('#my_wallet_verify_key_alert').css('display', 'none');
    }, 3000);
  }
}

function updateHiddenFieldValue(id){
  jQuery(`#my_wallet_gateway_test_clicked_${id}`).val('0');
}

function manage_my_wallet_payment_gateways_screen() {
  var url = 'admin-ajax.php?action=manage_payment_gateway_ajax_fun';
  jQuery.ajax({
    url,
    type: 'post',
    data: {
      'gatewaysStatus': gatewaysStatus
    },
    success: function (result) {
      if (result == "updated") {
        jQuery('#message').css('display', 'block');
      } else {
        alert('error');
      }
    }
  });
}

function addAmountToUserAccount(id, currencySymbol) {
  console.log(currencySymbol);
  var amount = jQuery(`#user${id}`).val();
  if (amount != null && amount != "" && amount > 0) {
    var userConfirm = false;
    if (confirm(`Are you sure you want to add ${currencySymbol} ${amount} to user's wallet?`)) {
      userConfirm = true;
    } else {
      jQuery(`#user${id}`).val("");
    }

    if (userConfirm) {
      var url = 'admin-ajax.php?action=add_amount_to_user_account_ajax_fun';
      jQuery.ajax({
        url,
        type: 'post',
        data: 'user_id=' + id + '&amount=' + amount,
        success: function (newAmount) {
          window.location.reload();
          // jQuery('#message').css('display', 'block');
          // jQuery(`#balance${id}`).text(newAmount);
          // jQuery(`#user${id}`).val("");
        }
      });
    }
  } else {
    redBorder(`user${id}`);
  }
}

function redBorder(id) {
  jQuery(`#${id}`).css('border', '1px solid red');
  setTimeout(() => {
    jQuery(`#${id}`).css('border', '1px solid grey');
  }, 3000);
}

function greenBorder(id) {
  jQuery(`#${id}`).css('border', '1px solid green');
  setTimeout(() => {
    jQuery(`#${id}`).css('border', '1px solid grey');
  }, 3000);
}

function changeWalletStatus(status, id) {
  var url = 'admin-ajax.php?action=manage_wallet_status_ajax_fun';
  jQuery.ajax({
    url,
    type: 'post',
    data: 'status=' + status + '&id=' + id,
    success: function (result) {
      if (result) {
        newStatusValue = status == 0 ? 1 : 0;
        newstatus = status == 0 ? "Deactive" : "Active";
        console.log(newstatus, newStatusValue);
        jQuery(`#btn${id}`).text(newstatus);
        jQuery(`#btn${id}`).attr("onclick", "changeWalletStatus(" + newStatusValue + ", " + id + ")");
      }
    }
  });
}

function changeCouponStatus(status, id) {
  var url = 'admin-ajax.php?action=manage_coupon_status_ajax_fun';
  jQuery.ajax({
    url,
    type: 'post',
    data: 'status=' + status + '&id=' + id,
    success: function (result) {
      if (result) {
        newStatusValue = status == 0 ? 1 : 0;
        newstatus = status == 0 ? "Deactive" : "Active";
        console.log(newstatus, newStatusValue);
        jQuery(`#btn${id}`).text(newstatus);
        jQuery(`#btn${id}`).attr("onclick", "changeCouponStatus(" + newStatusValue + ", " + id + ")");
      }
    }
  });
}

function userSpecificCoupon() {
  var isChecked = jQuery('#specific_user')[0].checked;
  if (isChecked) {
    jQuery('#user_ids').css('display', 'block');
  } else {
    jQuery('#user_ids').css('display', 'none');
  }
}

function showCouponPopup() {
  jQuery('#background').addClass('background');
  jQuery('#body-div').css('display', 'block');
  jQuery('#carot').text('-');
  jQuery('body').css('overflow', 'hidden');

}

function closeCouponPopup() {
  if (jQuery('#coupon-form-heading').length && jQuery('#coupon-form-heading').text().trim() === "Update Coupon") {
    jQuery("#coupon-form-heading").text("Create Coupon");
    jQuery("#submit-btn").text("Add");
    jQuery('#submit-btn').attr("name", "add_coupon");
    jQuery('#my_wallet_update_coupon_hidden_field').remove();
    jQuery("#coupon_name").val('');
    jQuery("#amount").val('');
    jQuery('#specific_user').prop('checked', false);
    jQuery('#user_ids').css('display', 'none');
    jQuery("#user_ids").val('');
    jQuery("#usability").val('');
    jQuery("#valid_from").val('');
    jQuery("#valid_to").val('');
  }
  jQuery('#background').removeClass('background');
  jQuery('#body-div').css('display', 'none');
  jQuery('body').css('overflow', 'inherit');
}

jQuery("#notice-dismiss").click(function () {
  jQuery('#message').css('display', 'none');
});

jQuery("#notice-dismiss").click(function () {
  jQuery('#my-wallet-alert-box').css('display', 'none');
});

jQuery("#generate_coupon").click(function () {
  jQuery('#coupon_name').val(autoGenerateCoupon());
});

jQuery("#close_coupon_popup").click(function () {
  jQuery("#coupon_name").val('');
  closeCouponPopup();
});

function autoGenerateCoupon() {
  var firstPart = (Math.random() * 46656) | 0;
  var secondPart = (Math.random() * 49656) | 0;
  firstPart = ("000" + firstPart.toString(36)).slice(-3);
  secondPart = ("000" + secondPart.toString(36)).slice(-3);
  return firstPart.toUpperCase() + secondPart.toUpperCase();
}

function edit_coupon(coupon_id) {
  var url = 'admin-ajax.php?action=get_coupon_by_id_ajax_fun';
  jQuery.ajax({
    url,
    type: 'post',
    data: 'coupon_id=' + coupon_id,
    success: function (result) {
      showCouponPopup();
      jQuery("#coupon-form-heading").text("Update Coupon");
      jQuery("#submit-btn").text("Update");
      jQuery('#submit-btn').attr("name", "update_coupon");
      jQuery('#coupon-form').append('<input type="hidden" id="my_wallet_update_coupon_hidden_field" name="coupon_id" value="' + coupon_id + '" />');
      result.forEach(element => {
        if (element.meta_coupon_key == "coupon_name") {
          jQuery("#coupon_name").val(element.meta_coupon_value);
        } else if (element.meta_coupon_key == "amount") {
          jQuery("#amount").val(element.meta_coupon_value);
        } else if (element.meta_coupon_key == "coupon_for_users") {
          if (element.meta_coupon_value != "All") {
            jQuery('#specific_user').prop('checked', true);
            jQuery('#user_ids').css('display', 'block');
            var emailIds = element.meta_coupon_value;
            emailIds = JSON.parse(emailIds);
            emailIds = emailIds.join(", ");
            jQuery("#user_ids").val(emailIds);
          }
        } else if (element.meta_coupon_key == "usability") {
          jQuery("#usability").val(element.meta_coupon_value);
        } else if (element.meta_coupon_key == "valid_from") {
          jQuery("#valid_from").val(element.meta_coupon_value);
        } else if (element.meta_coupon_key == "valid_to") {
          jQuery("#valid_to").val(element.meta_coupon_value);
        }
      })
    }
  });
}

jQuery(document).on('click', '#submit-activation-btn', function (e) {
  e.preventDefault();
  var license_key = jQuery('input#activation_input').val();
  my_wallet_license_request(license_key);
});

function my_wallet_license_request(license_key) {
  var url = 'admin-ajax.php?action=my_wallet_license_request';
  jQuery.ajax({
    url,
    type: 'POST',
    data: {
      'action': 'my_wallet_license_verify',
      'my_wallet_purchase_code': license_key,
      'my-wallet-license-nonce': ajax_var.license_nonce,
    },
    success: function (data) {
      if (data) {
        jQuery('#invalid-license-msg').css('display', 'none');
        jQuery('#activation-db-warning').css('display', 'block');
        jQuery("#submit-activation-btn").attr("disabled", true);
        jQuery("#submit-activation-btn").css('cursor', 'not-allowed');
        jQuery('#license-activation-alert').css('display', 'none');
      } else {
        jQuery('#invalid-license-msg').css('display', 'block');
        redBorder('activation_input');
      }
    }
  });
}

function manageTables() {
  var url = 'admin-ajax.php?action=my_wallet_manage_db';
  jQuery.ajax({
    url,
    type: 'POST',
    data: {
      'action': 'my_wallet_manage_db'
    },
    success: function (data) {
      if (data) {
        setTimeout(function () {
          window.location = ajax_var.reloadurl;
        }, 500);
      } else {
        alert("error");
      }
    }
  });
}

function validateDate() {
  var valid_from = jQuery('#valid_from').val();
  if (valid_from != null && valid_from != "") {
    valid_from = Date.parse(valid_from);
    var today = new Date();
    today.setDate(today.getDate() - 1);
    if (today > valid_from) {
      redBorder('valid_from');
      jQuery('#valid_from_error').css('display', 'block');
      jQuery('#submit-btn').prop('disabled', true);
      jQuery('#valid_from_error').css('margin', '-10px 0px 5px 0px');
    } else {
      jQuery('#valid_from_error').css('display', 'none');
      jQuery('#submit-btn').prop('disabled', false);
    }
  } else {
    jQuery('#valid_from_error').css('display', 'none');
    jQuery('#submit-btn').prop('disabled', false);
  }
}

jQuery('#coupon_name').on('change keydown paste input', function () {
  var text = jQuery('#coupon_name').val();
  jQuery('#coupon_name').val(text.toUpperCase());
});

function togglePaymentGateway(id) {
  var payment_gateway_content_body_id = '#my_wallet_gateway_' + id;
  var gateway_carot = '#my_wallet_gateway_div_' + id;
  if (jQuery(payment_gateway_content_body_id).css('display') == 'none') {
    jQuery(payment_gateway_content_body_id).css('display', 'block');
    jQuery(gateway_carot).text('Close');
  } else {
    jQuery(payment_gateway_content_body_id).css('display', 'none');
    jQuery(gateway_carot).text('Enter Keys');
  }
}

function testMyWalletGatewayKey(id) {
  jQuery('#my-wallet-loader').css('display', 'block');
  jQuery('#my-wallet-gateway-test-btn').css('display','none');
  var value = '#my_wallet_gateway_value_' + id;
  value = jQuery(value).val();
  fetch(`https://www.paypal.com/sdk/js?client-id=${value}&disable-funding=credit,card`)
    .then(r => {
      if (r.status == 200) {
        jQuery(`#my_wallet_gateway_test_clicked_${id}`).val('1');
        setTimeout(() => {
          jQuery('#my-wallet-loader').css('display', 'none');
          jQuery('#my-wallet-right').css('display', 'block');
          greenBorder(`my_wallet_gateway_value_${id}`);
          var success_box = '#my_wallet_gateway_success' + id;
          jQuery(success_box).css('display', 'block');
          setTimeout(() => {
            jQuery(success_box).css('display', 'none');
            jQuery('#my-wallet-right').css('display', 'none');
            jQuery('#my-wallet-gateway-test-btn').css('display','flex');
          }, 2000);
        }, 1000);
      } else {
        jQuery(`#my_wallet_gateway_test_clicked_${id}`).val('0');
        setTimeout(() => {
          jQuery('#my-wallet-loader').css('display', 'none');
          jQuery('#my-wallet-cancel').css('display', 'block');
          redBorder(`my_wallet_gateway_value_${id}`);
          var error_box = '#my_wallet_gateway_error' + id;
          jQuery(error_box).css('display', 'block');
          setTimeout(() => {
            jQuery(error_box).css('display', 'none');
            jQuery('#my-wallet-cancel').css('display', 'none');
            jQuery('#my-wallet-gateway-test-btn').css('display','flex');
          }, 2000);
        }, 1000);
      }
    });
}

// function add_my_wallet_gateway_content_input(id){
//   var gateway_info_content = document.createElement('div');
//   gateway_info_content.classList.add('gateway-info-content');
//   gateway_info_content.innerHTML = '<div class="gateway-info-key"><input type="text" class="w-95 p-8" name="" id="" placeholder="Key"></div><div class="gateway-info-value"><input type="text" class="w-95 p-8" name="" id="" placeholder="Value"></div>';
//   var btns_div = '#my_wallet_gateway_submit_btn_'+id;
//   jQuery(gateway_info_content).insertBefore(btns_div);
// }

function managePaymentGatewayKeys(id) {
  var key = '#my_wallet_gateway_key_' + id;
  var value = '#my_wallet_gateway_value_' + id;
  key = jQuery(key).val();
  value = jQuery(value).val();
  if (key != null && key != "" && value != null && value != "") {
    fetch(`https://www.paypal.com/sdk/js?client-id=${value}&disable-funding=credit,card`)
      .then(r => {
        if (r.status == 200) {
          var url = 'admin-ajax.php?action=manage_my_wallet_payment_gateways';
          jQuery.ajax({
            url,
            type: 'post',
            data: 'gateway_id=' + id + '&key=' + key + '&value=' + value,
            success: function (result) {
              if (result === "key_value_updated") {
                jQuery("#message").css('display', 'block');
                setTimeout(() => {
                  jQuery("#message").css('display', 'none');
                }, 2000);
              } else {
                alert("Oops");
              }
            }
          });
        } else {
          redBorder(`my_wallet_gateway_value_${id}`);
          var error_box = '#my_wallet_gateway_error' + id;
          jQuery(error_box).css('display', 'block');
          setTimeout(() => {
            jQuery(error_box).css('display', 'none');
          }, 2000);
        }
      });
  } else {
    if ((key == null || key == "") && (value != null && value != "") || (key != null && key != "") && (value == null || value == "")) {
      redBorder(`my_wallet_gateway_value_${id}`);
      var error_box = '#my_wallet_gateway_error' + id;
      jQuery(error_box).text("Please fill the required inputs.");
      jQuery(error_box).css('display', 'block');
      setTimeout(() => {
        jQuery(error_box).css('display', 'none');
        jQuery(error_box).text("Invalid gateway details.");
      }, 2000);
    } else {
      var url = 'admin-ajax.php?action=manage_my_wallet_payment_gateways';
      jQuery.ajax({
        url,
        type: 'post',
        data: 'gateway_id=' + id + '&key=' + key + '&value=' + value,
        success: function (result) {
          if (result === "key_value_updated") {
            jQuery("#message").css('display', 'block');
            setTimeout(() => {
              jQuery("#message").css('display', 'none');
            }, 2000);
          } else {
            alert("Oops");
          }
        }
      });
    }
  }
}

// Sortin script start from here

/*
  SortTable
  version 2
  7th April 2007
  Stuart Langridge, http://www.kryogenix.org/code/browser/sorttable/

  Instructions:
  Download this file
  Add <script src="sorttable.js"></script> to your HTML
  Add class="sortable" to any table you'd like to make sortable
  Click on the headers to sort

  Thanks to many, many people for contributions and suggestions.
  Licenced as X11: http://www.kryogenix.org/code/browser/licence.html
  This basically means: do what you want with it.
*/


var stIsIE = /*@cc_on!@*/ false;

sorttable = {
  init: function () {
    // quit if this function has already been called
    if (arguments.callee.done) return;
    // flag this function so we don't do the same thing twice
    arguments.callee.done = true;
    // kill the timer
    if (_timer) clearInterval(_timer);

    if (!document.createElement || !document.getElementsByTagName) return;

    sorttable.DATE_RE = /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/;

    forEach(document.getElementsByTagName('table'), function (table) {
      if (table.className.search(/\bsortable\b/) != -1) {
        sorttable.makeSortable(table);
      }
    });

  },

  makeSortable: function (table) {
    if (table.getElementsByTagName('thead').length == 0) {
      // table doesn't have a tHead. Since it should have, create one and
      // put the first table row in it.
      the = document.createElement('thead');
      the.appendChild(table.rows[0]);
      table.insertBefore(the, table.firstChild);
    }
    // Safari doesn't support table.tHead, sigh
    if (table.tHead == null) table.tHead = table.getElementsByTagName('thead')[0];

    if (table.tHead.rows.length != 1) return; // can't cope with two header rows

    // Sorttable v1 put rows with a class of "sortbottom" at the bottom (as
    // "total" rows, for example). This is B&R, since what you're supposed
    // to do is put them in a tfoot. So, if there are sortbottom rows,
    // for backwards compatibility, move them to tfoot (creating it if needed).
    sortbottomrows = [];
    for (var i = 0; i < table.rows.length; i++) {
      if (table.rows[i].className.search(/\bsortbottom\b/) != -1) {
        sortbottomrows[sortbottomrows.length] = table.rows[i];
      }
    }
    if (sortbottomrows) {
      if (table.tFoot == null) {
        // table doesn't have a tfoot. Create one.
        tfo = document.createElement('tfoot');
        table.appendChild(tfo);
      }
      for (var i = 0; i < sortbottomrows.length; i++) {
        tfo.appendChild(sortbottomrows[i]);
      }
      delete sortbottomrows;
    }

    // work through each column and calculate its type
    headrow = table.tHead.rows[0].cells;
    for (var i = 0; i < headrow.length; i++) {
      // manually override the type with a sorttable_type attribute
      if (!headrow[i].className.match(/\bsorttable_nosort\b/)) { // skip this col
        mtch = headrow[i].className.match(/\bsorttable_([a-z0-9]+)\b/);
        if (mtch) {
          override = mtch[1];
        }
        if (mtch && typeof sorttable["sort_" + override] == 'function') {
          headrow[i].sorttable_sortfunction = sorttable["sort_" + override];
        } else {
          headrow[i].sorttable_sortfunction = sorttable.guessType(table, i);
        }
        // make it clickable to sort
        headrow[i].sorttable_columnindex = i;
        headrow[i].sorttable_tbody = table.tBodies[0];
        dean_addEvent(headrow[i], "click", sorttable.innerSortFunction = function (e) {

          if (this.className.search(/\bsorttable_sorted\b/) != -1) {
            // if we're already sorted by this column, just
            // reverse the table, which is quicker
            sorttable.reverse(this.sorttable_tbody);
            this.className = this.className.replace('sorttable_sorted',
              'sorttable_sorted_reverse');
            this.removeChild(document.getElementById('sorttable_sortfwdind'));
            sortrevind = document.createElement('span');
            sortrevind.id = "sorttable_sortrevind";
            sortrevind.innerHTML = stIsIE ? '&nbsp<font face="webdings">5</font>' : '&nbsp;&#x25B4;';
            this.appendChild(sortrevind);
            return;
          }
          if (this.className.search(/\bsorttable_sorted_reverse\b/) != -1) {
            // if we're already sorted by this column in reverse, just
            // re-reverse the table, which is quicker
            sorttable.reverse(this.sorttable_tbody);
            this.className = this.className.replace('sorttable_sorted_reverse',
              'sorttable_sorted');
            this.removeChild(document.getElementById('sorttable_sortrevind'));
            sortfwdind = document.createElement('span');
            sortfwdind.id = "sorttable_sortfwdind";
            sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
            this.appendChild(sortfwdind);
            return;
          }

          // remove sorttable_sorted classes
          theadrow = this.parentNode;
          forEach(theadrow.childNodes, function (cell) {
            if (cell.nodeType == 1) { // an element
              cell.className = cell.className.replace('sorttable_sorted_reverse', '');
              cell.className = cell.className.replace('sorttable_sorted', '');
            }
          });
          sortfwdind = document.getElementById('sorttable_sortfwdind');
          if (sortfwdind) {
            sortfwdind.parentNode.removeChild(sortfwdind);
          }
          sortrevind = document.getElementById('sorttable_sortrevind');
          if (sortrevind) {
            sortrevind.parentNode.removeChild(sortrevind);
          }

          this.className += ' sorttable_sorted';
          sortfwdind = document.createElement('span');
          sortfwdind.id = "sorttable_sortfwdind";
          sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
          this.appendChild(sortfwdind);

          // build an array to sort. This is a Schwartzian transform thing,
          // i.e., we "decorate" each row with the actual sort key,
          // sort based on the sort keys, and then put the rows back in order
          // which is a lot faster because you only do getInnerText once per row
          row_array = [];
          col = this.sorttable_columnindex;
          rows = this.sorttable_tbody.rows;
          for (var j = 0; j < rows.length; j++) {
            row_array[row_array.length] = [sorttable.getInnerText(rows[j].cells[col]), rows[j]];
          }
          /* If you want a stable sort, uncomment the following line */
          //sorttable.shaker_sort(row_array, this.sorttable_sortfunction);
          /* and comment out this one */
          row_array.sort(this.sorttable_sortfunction);

          tb = this.sorttable_tbody;
          for (var j = 0; j < row_array.length; j++) {
            tb.appendChild(row_array[j][1]);
          }

          delete row_array;
        });
      }
    }
  },

  guessType: function (table, column) {
    // guess the type of a column based on its first non-blank row
    sortfn = sorttable.sort_alpha;
    for (var i = 0; i < table.tBodies[0].rows.length; i++) {
      text = sorttable.getInnerText(table.tBodies[0].rows[i].cells[column]);
      if (text != '') {
        if (text.match(/^-?[£$¤]?[\d,.]+%?$/)) {
          return sorttable.sort_numeric;
        }
        // check for a date: dd/mm/yyyy or dd/mm/yy
        // can have / or . or - as separator
        // can be mm/dd as well
        possdate = text.match(sorttable.DATE_RE)
        if (possdate) {
          // looks like a date
          first = parseInt(possdate[1]);
          second = parseInt(possdate[2]);
          if (first > 12) {
            // definitely dd/mm
            return sorttable.sort_ddmm;
          } else if (second > 12) {
            return sorttable.sort_mmdd;
          } else {
            // looks like a date, but we can't tell which, so assume
            // that it's dd/mm (English imperialism!) and keep looking
            sortfn = sorttable.sort_ddmm;
          }
        }
      }
    }
    return sortfn;
  },

  getInnerText: function (node) {
    // gets the text we want to use for sorting for a cell.
    // strips leading and trailing whitespace.
    // this is *not* a generic getInnerText function; it's special to sorttable.
    // for example, you can override the cell text with a customkey attribute.
    // it also gets .value for <input> fields.

    if (!node) return "";

    hasInputs = (typeof node.getElementsByTagName == 'function') &&
      node.getElementsByTagName('input').length;

    if (node.getAttribute("sorttable_customkey") != null) {
      return node.getAttribute("sorttable_customkey");
    } else if (typeof node.textContent != 'undefined' && !hasInputs) {
      return node.textContent.replace(/^\s+|\s+$/g, '');
    } else if (typeof node.innerText != 'undefined' && !hasInputs) {
      return node.innerText.replace(/^\s+|\s+$/g, '');
    } else if (typeof node.text != 'undefined' && !hasInputs) {
      return node.text.replace(/^\s+|\s+$/g, '');
    } else {
      switch (node.nodeType) {
        case 3:
          if (node.nodeName.toLowerCase() == 'input') {
            return node.value.replace(/^\s+|\s+$/g, '');
          }
          case 4:
            return node.nodeValue.replace(/^\s+|\s+$/g, '');
            break;
          case 1:
          case 11:
            var innerText = '';
            for (var i = 0; i < node.childNodes.length; i++) {
              innerText += sorttable.getInnerText(node.childNodes[i]);
            }
            return innerText.replace(/^\s+|\s+$/g, '');
            break;
          default:
            return '';
      }
    }
  },

  reverse: function (tbody) {
    // reverse the rows in a tbody
    newrows = [];
    for (var i = 0; i < tbody.rows.length; i++) {
      newrows[newrows.length] = tbody.rows[i];
    }
    for (var i = newrows.length - 1; i >= 0; i--) {
      tbody.appendChild(newrows[i]);
    }
    delete newrows;
  },

  /* sort functions
     each sort function takes two parameters, a and b
     you are comparing a[0] and b[0] */
  sort_numeric: function (a, b) {
    aa = parseFloat(a[0].replace(/[^0-9.-]/g, ''));
    if (isNaN(aa)) aa = 0;
    bb = parseFloat(b[0].replace(/[^0-9.-]/g, ''));
    if (isNaN(bb)) bb = 0;
    return aa - bb;
  },
  sort_alpha: function (a, b) {
    if (a[0] == b[0]) return 0;
    if (a[0] < b[0]) return -1;
    return 1;
  },
  sort_ddmm: function (a, b) {
    mtch = a[0].match(sorttable.DATE_RE);
    y = mtch[3];
    m = mtch[2];
    d = mtch[1];
    if (m.length == 1) m = '0' + m;
    if (d.length == 1) d = '0' + d;
    dt1 = y + m + d;
    mtch = b[0].match(sorttable.DATE_RE);
    y = mtch[3];
    m = mtch[2];
    d = mtch[1];
    if (m.length == 1) m = '0' + m;
    if (d.length == 1) d = '0' + d;
    dt2 = y + m + d;
    if (dt1 == dt2) return 0;
    if (dt1 < dt2) return -1;
    return 1;
  },
  sort_mmdd: function (a, b) {
    mtch = a[0].match(sorttable.DATE_RE);
    y = mtch[3];
    d = mtch[2];
    m = mtch[1];
    if (m.length == 1) m = '0' + m;
    if (d.length == 1) d = '0' + d;
    dt1 = y + m + d;
    mtch = b[0].match(sorttable.DATE_RE);
    y = mtch[3];
    d = mtch[2];
    m = mtch[1];
    if (m.length == 1) m = '0' + m;
    if (d.length == 1) d = '0' + d;
    dt2 = y + m + d;
    if (dt1 == dt2) return 0;
    if (dt1 < dt2) return -1;
    return 1;
  },

  shaker_sort: function (list, comp_func) {
    // A stable sort function to allow multi-level sorting of data
    // see: http://en.wikipedia.org/wiki/Cocktail_sort
    // thanks to Joseph Nahmias
    var b = 0;
    var t = list.length - 1;
    var swap = true;

    while (swap) {
      swap = false;
      for (var i = b; i < t; ++i) {
        if (comp_func(list[i], list[i + 1]) > 0) {
          var q = list[i];
          list[i] = list[i + 1];
          list[i + 1] = q;
          swap = true;
        }
      } // for
      t--;

      if (!swap) break;

      for (var i = t; i > b; --i) {
        if (comp_func(list[i], list[i - 1]) < 0) {
          var q = list[i];
          list[i] = list[i - 1];
          list[i - 1] = q;
          swap = true;
        }
      } // for
      b++;

    } // while(swap)
  }
}

/* ******************************************************************
   Supporting functions: bundled here to avoid depending on a library
   ****************************************************************** */

// Dean Edwards/Matthias Miller/John Resig

/* for Mozilla/Opera9 */
if (document.addEventListener) {
  document.addEventListener("DOMContentLoaded", sorttable.init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
    document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
    var script = document.getElementById("__ie_onload");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
            sorttable.init(); // call the onload handler
        }
    };
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
  var _timer = setInterval(function () {
    if (/loaded|complete/.test(document.readyState)) {
      sorttable.init(); // call the onload handler
    }
  }, 10);
}

/* for other browsers */
window.onload = sorttable.init;

// written by Dean Edwards, 2005
// with input from Tino Zijdel, Matthias Miller, Diego Perini

// http://dean.edwards.name/weblog/2005/10/add-event/

function dean_addEvent(element, type, handler) {
  if (element.addEventListener) {
    element.addEventListener(type, handler, false);
  } else {
    // assign each event handler a unique ID
    if (!handler.$$guid) handler.$$guid = dean_addEvent.guid++;
    // create a hash table of event types for the element
    if (!element.events) element.events = {};
    // create a hash table of event handlers for each element/event pair
    var handlers = element.events[type];
    if (!handlers) {
      handlers = element.events[type] = {};
      // store the existing event handler (if there is one)
      if (element["on" + type]) {
        handlers[0] = element["on" + type];
      }
    }
    // store the event handler in the hash table
    handlers[handler.$$guid] = handler;
    // assign a global event handler to do all the work
    element["on" + type] = handleEvent;
  }
};
// a counter used to create unique IDs
dean_addEvent.guid = 1;

function removeEvent(element, type, handler) {
  if (element.removeEventListener) {
    element.removeEventListener(type, handler, false);
  } else {
    // delete the event handler from the hash table
    if (element.events && element.events[type]) {
      delete element.events[type][handler.$$guid];
    }
  }
};

function handleEvent(event) {
  var returnValue = true;
  // grab the event object (IE uses a global event object)
  event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
  // get a reference to the hash table of event handlers
  var handlers = this.events[event.type];
  // execute each event handler
  for (var i in handlers) {
    this.$$handleEvent = handlers[i];
    if (this.$$handleEvent(event) === false) {
      returnValue = false;
    }
  }
  return returnValue;
};

function fixEvent(event) {
  // add W3C standard event methods
  event.preventDefault = fixEvent.preventDefault;
  event.stopPropagation = fixEvent.stopPropagation;
  return event;
};
fixEvent.preventDefault = function () {
  this.returnValue = false;
};
fixEvent.stopPropagation = function () {
  this.cancelBubble = true;
}

// Dean's forEach: http://dean.edwards.name/base/forEach.js
/*
  forEach, version 1.0
  Copyright 2006, Dean Edwards
  License: http://www.opensource.org/licenses/mit-license.php
*/

// array-like enumeration
if (!Array.forEach) { // mozilla already supports this
  Array.forEach = function (array, block, context) {
    for (var i = 0; i < array.length; i++) {
      block.call(context, array[i], i, array);
    }
  };
}

// generic enumeration
Function.prototype.forEach = function (object, block, context) {
  for (var key in object) {
    if (typeof this.prototype[key] == "undefined") {
      block.call(context, object[key], key, object);
    }
  }
};

// character enumeration
String.forEach = function (string, block, context) {
  Array.forEach(string.split(""), function (chr, index) {
    block.call(context, chr, index, string);
  });
};

// globally resolve forEach enumeration
var forEach = function (object, block, context) {
  if (object) {
    var resolve = Object; // default
    if (object instanceof Function) {
      // functions have a "length" property
      resolve = Function;
    } else if (object.forEach instanceof Function) {
      // the object implements a custom forEach method so use that
      object.forEach(block, context);
      return;
    } else if (typeof object == "string") {
      // the object is a string
      resolve = String;
    } else if (typeof object.length == "number") {
      // the object is array-like
      resolve = Array;
    }
    resolve.forEach(object, block, context);
  }
};

// sorting ends here