    function onResize() {
        if (jQuery(window).width() <= 640) {
            jQuery('.payMethodList').addClass('mobile');
        } else {
            jQuery('.payMethodList').removeClass('mobile');
        }
    }

    onResize();
    jQuery(window).resize(function () {
        onResize();
    });

    function setP24method(method) {
        method = parseInt(method);
        jQuery('input[name=p24_method]').val(method > 0 ? method : "");
    }

    function choosePaymentMethod() {
        var checkedPayment = false;
        jQuery('.bank-box').click(function () {
            jQuery('.bank-box').removeClass('selected').addClass('inactive');
            jQuery(this).addClass('selected').removeClass('inactive');
            if (jQuery(this).parents('.payMethodList').hasClass('checkoutView')) {
                var inputs = jQuery(this).parents('.checkoutView').find('input[type="checkbox"]');
                jQuery(inputs).removeAttr('checked');
                jQuery(inputs).prop('checked', false);

                var input = jQuery(this).find('input[type="checkbox"]');
                jQuery(input).attr('checked', 'checked');
                jQuery(input).prop('checked', true);
                checkedPayment = true;
                jQuery('#payment_method_przelewy24_payment').trigger('change');
            }
            setP24method(jQuery(this).attr('data-id'));
            setP24recurringId(jQuery(this).attr('data-cc'));
        });

        jQuery('.bank-item input').change(function () {
            setP24method(jQuery(this).closest('.bank-item').attr('data-id'));
            setP24recurringId(jQuery(this).attr('data-cc'));
        });

        jQuery('#payment_method_przelewy24_payment').trigger('change');

        jQuery('input[name=payment_method_id]:checked:first').closest('.input-box.bank-item').each(function () {
            setP24method(jQuery(this).attr('data-id'));
        });

    }
    jQuery('<style>.moreStuff.translated:before{content:"' + p24_payment_php_vars.payments_msg4js + '"} </style>').appendTo('head');
    jQuery('.moreStuff').toggleClass('translated');

        jQuery(document).ready(function () {
            choosePaymentMethod();
        });

var sessionId = false;
var sign = false;
var payInShopScriptRequested = false;

function requestJsAjaxCard() {
    jQuery.ajax(jQuery('#p24_ajax_url').val(), {
        method: 'POST', type: 'POST',
        data: {
            action: 'trnRegister',
            p24_session_id: jQuery('[name=p24_session_id]').val(),
            order_id: jQuery('#p24_woo_order_id').val()
        },
        error: function () {
            payInShopFailure();
        },
        success: function (response) {
            try {
                var data = JSON.parse(response);
                sessionId = data.sessionId;
                sign = data.p24_sign;
                jQuery('#P24FormArea').html("");
                jQuery("<div></div>")
                    .attr('id', 'P24FormContainer')
                    .attr('data-sign', jQuery('[name=p24_sign]').val())
                    .attr('data-successCallback', 'payInShopSuccess')
                    .attr('data-failureCallback', 'payInShopFailure')
                    .attr('data-client-id', data.client_id)
                    .attr('data-dictionary', jQuery('#p24_dictionary').val())
                    .appendTo('#P24FormArea')
                    .parent().slideDown()
                ;
                if (document.createStyleSheet) {
                    document.createStyleSheet(data.p24cssURL);
                } else {
                    jQuery('head').append('<link rel="stylesheet" type="text/css" href="' + data.p24cssURL + '" />');
                }
                if (!payInShopScriptRequested) {
                    jQuery.getScript(data.p24jsURL, function () {
                        P24_Transaction.init();
                        jQuery('#P24FormContainer');
                        payInShopScriptRequested = false;
                        window.setTimeout(function () {
                            jQuery('#P24FormContainer button').show().on('click', function () {
                                if (P24_Card.validate()) {
                                    jQuery(this).hide().after("<div class='loading' />");
                                }
                            });
                        }, 500);
                    });
                }
                payInShopScriptRequested = true;

            } catch (e) {
                window.location.reload();
            }
        }
    });

}

function showPayJsPopup() {
    if (jQuery('#P24FormAreaHolder:visible').length == 0) {
        setP24method("");
        jQuery('#P24FormAreaHolder').appendTo('body');
        jQuery('#proceedPaymentLink').closest('a').fadeOut();

        jQuery('#P24FormAreaHolder').fadeIn();
        if (typeof P24_Transaction != 'object') {
            requestJsAjaxCard();
        }
    }
}

function hidePayJsPopup() {
    jQuery('#P24FormAreaHolder').fadeOut();
    jQuery('#proceedPaymentLink:not(:visible)').closest('a').fadeIn();
}

function payInShopSuccess(orderId, oneclickOrderId) {

        jQuery.ajax(jQuery('#p24_ajax_url').val(), {
            method: 'POST', type: 'POST',
            data: {
                action: 'rememberOrderId',
                sessionId: jQuery('[name=p24_session_id]').val(),
                orderId: orderId,
                oneclickOrderId: oneclickOrderId,
                sign: jQuery('[name=p24_sign]').val()
            }
        });
    window.setTimeout(function () {
        window.location = jQuery('[name=p24_url_return]').val();
    }, 1000);
}

    function setP24recurringId(id,name) {
        id = parseInt(id);
        if (name ==  undefined) name = jQuery('[data-cc='+id+'] .bank-name').text().trim() + ' - ' + jQuery('[data-cc='+id+'] .bank-logo span').text().trim();
        jQuery('input[name=p24_cc]').val( id > 0 ? id : "" );
        if (id > 0) setP24method(0);
    }

    function p24_processPayment() {
        console.log('processPayment');
        var ccid = parseInt(jQuery('input[name=p24_cc]').val());
        if (isNaN(ccid) || ccid == 0) return true;


        // recuring
        if (ccid > 0) {
            jQuery('#przelewy24FormRecuring').submit();
        }

        return false;
    }

    function removecc(ccid) {
        jQuery('form#cardrm input[name=cardrm]').val(ccid).closest('form').submit();
    }

    // payinshop

function payInShopFailure() {

    //wyświetlamy odpowiedź
    jQuery('#P24FormArea').html("<span class='info'>" + p24_payment_php_vars.error_msg4js + "</span>");  //'Wystąpił błąd. Spróbuj ponownie lub wybierz inną metodę płatności.'
    P24_Transaction = undefined;
    window.location = jQuery('[name=p24_url_return]').val();
}
    var selector = '#P24_registerCard';

    var waitForEl = function(selector, callback) {
        if (jQuery(selector).length) {
            jQuery('#P24_registerCard').prop('checked', (p24_payment_php_vars.forget_card == 1 ? false : true));
        } else {
            setTimeout(function() {
                waitForEl(selector, callback);
            }, 100);
        }
    };

    waitForEl(selector, function() {
    });