jQuery(function() {
    jQuery(".wplc_macros_select").change(function () {

        var wplc_id = this.value;
        if (parseInt(wplc_id) === 0) {
            return;
        }
        var data = {
            action: 'wplc_macro',
            dataType: "json",
            postid: wplc_id,
            security: wplc_ajax_nonce
        };
        jQuery.post(wplc_home_ajaxurl, data, function (response) {
            var post_content = jQuery.parseJSON(response);
            jQuery("#wplc_admin_chatmsg").val(jQuery("#wplc_admin_chatmsg").val() + post_content).focus();

        });

    });


});