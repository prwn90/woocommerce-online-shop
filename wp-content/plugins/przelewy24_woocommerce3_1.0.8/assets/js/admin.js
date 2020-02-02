jQuery(document).ready(function ($) {
    function getBanksList() {
        var banksList = [];
        $('#woocommerce_przelewy24_p24_paymethods_all option').each(function () {
            if (parseInt($(this).val()) > 0) {
                banksList.push({id: $(this).val(), name: $(this).text()});
            }
        });

        if (banksList.length == 0) {
            banksList.push({id: 25, name: ""});
            banksList.push({id: 31, name: ""});
            banksList.push({id: 112, name: ""});
            banksList.push({id: 20, name: ""});
            banksList.push({id: 65, name: ""});
        }
        return banksList;
    }

    function getBankBox(id, name) {
        if (name == undefined) name = '';
        return '<a class="bank-box" data-id="' + id + '"><div class="bank-logo bank-logo-' + id + '"></div><div class="bank-name">' + name + '</div></a>';
    }

    function toggleSomething(toggle, selector) {
        if (toggle) {
            $(selector).show();
        } else {
            $(selector).hide();
        }
    }

    function updatePaymethods() {
        $('.bank-box').removeClass('ui-helper-unrotate');
        var maxNo = parseInt($('.paymethod .selected').attr('data-max'));
        if (maxNo > 0) {
            if ($('.paymethod .selected a[data-id]').length > maxNo) {
                var i = 0;
                $('.paymethod .selected a[data-id]').each(function () {
                    i++;
                    if (i > maxNo) {
                        $('.paymethod .available')
                            .prepend($(this))
                            .append($('#clear'));
                    }
                });
            }
        }
        $('#woocommerce_przelewy24_p24_paymethods_first').val('');
        $('.paymethod .selected a[data-id]').each(function () {
            $('#woocommerce_przelewy24_p24_paymethods_first').val(
                $('#woocommerce_przelewy24_p24_paymethods_first').val() +
                ($('#woocommerce_przelewy24_p24_paymethods_first').val().length ? ',' : '') +
                $(this).attr('data-id')
            );
        });
        $('#woocommerce_przelewy24_p24_paymethods_second').val('');
        $('.paymethod .available a[data-id]').each(function () {
            $('#woocommerce_przelewy24_p24_paymethods_second').val(
                $('#woocommerce_przelewy24_p24_paymethods_second').val() +
                ($('#woocommerce_przelewy24_p24_paymethods_second').val().length ? ',' : '') +
                $(this).attr('data-id')
            );
        });
    }

    $(document).ready(function () {

        $('#woocommerce_przelewy24_p24_paymethods_first').hide();
        $('#woocommerce_przelewy24_p24_paymethods_second').closest('tr').hide();
        $('#woocommerce_przelewy24_p24_paymethods_all').closest('tr').hide();

        $('#woocommerce_przelewy24_p24_paymethods_first').closest('td').append(
            '<div class="paymethod">' +
            '<div style="margin: 0.5em 0">' + p24_payment_script_vars.php_msg1 + '</div>' +
            '<div class="sortable selected" data-max="5" style="width: 730px; border: 5px dashed lightgray; height: 80px; padding: 0.5em; overflow: hidden;"></div>' +
            '<div style="clear:both"></div>' +
            '<div style="margin: 0.5em 0">' + p24_payment_script_vars.php_msg2 + '</div>' +
            '<div class="sortable available"></div>' +
            '</div>' +
            ''
        );

        $('#woocommerce_przelewy24_p24_paymethods').change(function () {
            var showed = $(this).is(':checked');
            toggleSomething(showed, $('.paymethod').closest('tr'));
            toggleSomething(showed, $('#woocommerce_przelewy24_p24_graphics').closest('tr'));
        }).trigger('change');

        $.each(getBanksList(), function () {
            $('.sortable.available').append(getBankBox(this.id, this.name));
        });
        $('.sortable.available').append('<div style="clear:both" id="clear"></div>');

        if ($('#woocommerce_przelewy24_p24_paymethods_first').val().length > 0) {
            $.each($('#woocommerce_przelewy24_p24_paymethods_first').val().split(','), function (i, v) {
                $('.bank-box[data-id=' + v + ']').appendTo('.paymethod .selected');
            });
        }
        if ($('#woocommerce_przelewy24_p24_paymethods_second').val().length > 0) {
            $.each($('#woocommerce_przelewy24_p24_paymethods_second').val().split(',').reverse(), function (i, v) {
                $('.bank-box[data-id=' + v + ']').prependTo('.paymethod .available');
            });
        }
        updatePaymethods();

        $(".sortable.selected,.sortable.available").sortable({
            connectWith: ".sortable.selected,.sortable.available",
            placeholder: "bank-box bank-placeholder",
            stop: function () {
                updatePaymethods();
            },
            revert: true,
            start: function (e, ui) {
                window.setTimeout(function () {
                    $('.bank-box.ui-sortable-helper').on('mouseup', function () {
                        $(this).addClass('ui-helper-unrotate');
                    });
                }, 100);
            },
        }).disableSelection();

        if ($('#p24_no_api_key_provided').length) {
            $('#woocommerce_przelewy24_p24_paymethods,#woocommerce_przelewy24_p24_graphics,#woocommerce_przelewy24_p24_paymethods_first,#woocommerce_przelewy24_p24_paymethods_second').closest('tr').hide();
        }


    });
});