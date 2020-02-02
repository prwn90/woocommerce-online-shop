var wplc_is_typing = false;
var wplc_searchTimeout;


jQuery(function () {


    
    jQuery("body").on("keydown","#wplc_admin_chatmsg", function(e) {
    if(typeof wplc_node_sockets_ready === "undefined" || wplc_node_sockets_ready === false){
        if (e.which <= 90 && e.which >= 48) {
            if (wplc_is_typing) { 
                wplc_renew_typing();
                return; /* user already typing */
            }
            wplc_is_typing = true;
            
            wplc_searchTimeout = setTimeout(wplc_clear_typing, 3000);
            wplc_usertyping('admin',Math.floor(Date.now() / 1000),cid);
        }
    }
    });

    jQuery("body").on("click", "#wplc_admin_send_msg", function() {
        if (wplc_is_typing) { wplc_clear_typing(); }
    });

    function wplc_renew_typing() {
        clearTimeout(wplc_searchTimeout);
        wplc_searchTimeout = setTimeout(wplc_clear_typing, 3000);
    }
    function wplc_clear_typing() {
        wplc_is_typing = false;
        clearTimeout(wplc_searchTimeout);
        wplc_usertyping('admin',0,cid);
    }

    function wplc_usertyping(wplc_typing_user,wplc_typing_type,wplc_typing_cid) {
      if (typeof wplc_cid !== "undefined" && wplc_cid !== null) { 
        var data = {
                action: 'wplc_typing',
                security: wplc_ajax_nonce,
                user: wplc_typing_user,
                type: wplc_typing_type,
                cid: wplc_typing_cid,
                wplc_extra_data:wplc_extra_data
        };

        /*if (!!wplc_restapi_enabled.value && typeof wplc_using_cloud === "undefined") {
          data.security = (typeof wplc_restapi_token !== "undefined" ? wplc_restapi_token : false);
          console.log('twplc_usertyping', wplc_restapi_endpoint, data);
          jQuery.post(wplc_restapi_endpoint+"/typing/", data, function(response) {});
        } else {*/
          jQuery.post(wplc_ajaxurl, data, function(response) {});
        //}
        
        } else {
           /* no cid? */
        }
    }

});