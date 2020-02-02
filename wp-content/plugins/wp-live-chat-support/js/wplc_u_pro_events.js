/**
 * Traditional JS handlers for pro features (Non-node based)
 *  
 */


jQuery(function () {


    /**
     * Hook into the user chat loop to identify if an agent is typing
     * 
     */
    jQuery(document).on( "wplc_user_chat_loop", function( e ) {
      if(typeof wplc_node_sockets_ready === "undefined" || wplc_node_sockets_ready === false){
          if (wplc_misc_strings.typing_enabled) {
              wplc_cid = Cookies.get('wplc_cid');
              if (typeof e.response['typing'] === "undefined") {
                  jQuery("#wplc_user_typing").fadeOut("slow").remove();
              }
              if (e.response['status'] === 8) {
                  jQuery('#nifty_text_editor_holder').css('display', 'none');
                  jQuery('#nifty_file_holder').css('display', 'none');
              }
              if (e.response['typing'] === "1") {
                  if (jQuery("#wplc_user_typing").length>0) { } else {
                if(typeof wplc_localized_string_is_typing !== "undefined"){
                  if (typeof wplc_agent_name === "undefined" || wplc_agent_name === "") { 
                    jQuery(".typing_indicator").html("<span id='wplc_user_typing'>"+ wplc_localized_string_is_typing + "</span>");
                    jQuery(".typing_indicator").addClass("typing_indicator_active");
                  } else {
                    jQuery(".typing_indicator").html("<span id='wplc_user_typing'>"+wplc_safe_html(wplc_agent_name+config.wplc_localized_string_is_typing_single) + "</span>");
                    jQuery(".typing_indicator").addClass("typing_indicator_active");                    
                  }
                } else {
                  /* Backwards compat */
                      jQuery("#wplc_chatbox").append("<img id='wplc_user_typing' src='"+wplc_misc_strings.typingimg+"' />");
                      jQuery("#wplc_user_typing").fadeIn("fast");
                      var height = jQuery('#wplc_chatbox')[0].scrollHeight;
                      jQuery('#wplc_chatbox').scrollTop(height);
                }
                }
                
            } else if (e.response['typing'] === "0") {
                if (jQuery("#wplc_user_typing").length>0) {
                    jQuery("#wplc_user_typing").fadeOut("slow").remove();
                    jQuery(".typing_indicator").removeClass("typing_indicator_active");
              }
            }
            }
      }
    });


    jQuery("body").on("change", "#nifty_file_input", function(evt){       
        var file = this.files[0]; //Last file in array
        wplcShareFile(file,'#nifty_attach_fail_icon', '#nifty_attach_success_icon', '#nifty_attach_uploading_icon',  "#nifty_select_file");    
        evt.stopImmediatePropagation(); 

        this.value = "";
    });

    /**
     * Use WP REST API to notify the system that we are typing
     * 
     */
    jQuery("body").on("keydown","#wplc_chatmsg", function(e) {
        if(typeof wplc_node_sockets_ready === "undefined" || wplc_node_sockets_ready === false){
              if (wplc_misc_strings.typing_enabled) {
                if (e.which <= 90 && e.which >= 48) {
                    if (wplc_is_typing) { 

                        wplc_renew_typing();
                        return; /* user already typing */
                    }
                    wplc_is_typing = true;
                    
                    wplc_searchTimeout = setTimeout(wplc_clear_typing, 3000);
                    wplc_cid = Cookies.get('wplc_cid');
                    wplc_usertyping('user',Math.floor(Date.now() / 1000),wplc_cid);
                }
            }
        }
    });
    
    /**
     * Clear typing
     * 
     */
    jQuery("body").on("click", "#wplc_send_msg", function() {
        if (wplc_misc_strings.typing_enabled) {
            if (wplc_is_typing) { wplc_clear_typing(); }
        }
    });


    function wplc_renew_typing() {
        clearTimeout(wplc_searchTimeout);
        wplc_searchTimeout = setTimeout(wplc_clear_typing, 3000);
    }
    function wplc_clear_typing() {
        wplc_is_typing = false;
        clearTimeout(wplc_searchTimeout);
        wplc_cid = Cookies.get('wplc_cid');
        wplc_usertyping('user',0,wplc_cid);
    }


    /**
     * Notify WP REST API of user typing
     * 
     */
    function wplc_usertyping(wplc_typing_user,wplc_typing_type,wplc_typing_cid) {
      if (typeof wplc_cid !== "undefined" && wplc_cid !== null) { 
        var data = {
                action: 'wplc_typing',
                security: wplc_nonce,
                user: wplc_typing_user,
                type: wplc_typing_type,
                cid: wplc_typing_cid,
                wplc_extra_data:wplc_extra_data
        };

        /*if (!!wplc_restapi_enabled.value && typeof wplc_using_cloud === "undefined") {
          data.security = (typeof wplc_restapi_token !== "undefined" ? wplc_restapi_token : false);
          jQuery.post(wplc_restapi_endpoint+"/typing/", data, function(response) {});
        } else {*/
          jQuery.post(wplc_ajaxurl, data, function(response) {});
        //}
        
        } else {
           /* no cid? */
        }
    }



});