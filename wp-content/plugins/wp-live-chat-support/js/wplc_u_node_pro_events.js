jQuery(function () {


    /**
     * Handle the 'typing' event
     *
     * PRO ONLY function
     * 
     * @return void
     */
    jQuery(document).on("tcx_typing", function(e) {
        /* TO DO */
        jQuery(".typing_indicator").html("<span id='wplc_user_typing'>"+wplc_safe_html(e.ndata.username+ config.wplc_localized_string_is_typing_single)+"</span>");
        jQuery(".typing_indicator").addClass("typing_indicator_active"); 
    });


    /**
     * Agent has stopped typing.
     *
     * 
     * @return void
     */
    jQuery(document).on("tcx_stop_typing", function(e) {
        /* TO DO */
        jQuery("#wplc_user_typing").fadeOut("slow").remove();
        jQuery(".typing_indicator").removeClass("typing_indicator_active") 
    });

            
    /**
     * The agent has initiated a chat with us, open the chat box.
     * 
     * @return void
     */
    jQuery(document).on("tcx_agent_initiated_chat", function(e) {
        var data = {
                relay_action: 'wplc_get_messages',
                security: wplc_nonce,
                chat_id: wplc_cid,
                limit:50,
                offset:0,
                received_via: 'u',
                wplc_extra_data:wplc_extra_data
            };
        wplc_rest_api('get_messages', data, 12000, function(message_history) {
            if (typeof message_history.data !== "undefined" && typeof message_history.data.messages !== "undefined" && typeof message_history.data.messages === "object") {
                message_history = message_history.data.messages;
                for (var key in message_history) {
                    var the_message = message_history[key];
                    the_message.mid = key;
                    wplc_push_message_to_chatbox(the_message,'u', function() {
                        wplc_scroll_to_bottom();    
                    });
                }
            }
        });
        open_chat(1);
        jQuery.event.trigger({type: "wplc_open_chat_2", wplc_online: wplc_online});
    });


    /**
     * Sends the custom data packet via the socket if it exists
    */
    jQuery(document).on("wplc_send_live_rating", function(e) {
        if(typeof e.rating !== "undefined"){
            if(typeof socket !== "undefined" && typeof socket.emit !== "undefined"){
                //We have a rating and a socket sprocket to send data with
                
                socket.emit('custom data',{action:'wplc_send_live_rating', chatid:wplc_cid, rating_data: e.rating});

            }
        }
    });

    //Handler for edits
    jQuery(document).on("tcx_edited_message", function(e){
      if(typeof e.ndata !== "undefined"){
        if(typeof e.ndata.message !== "undefined" && typeof e.ndata.chatid !== "undefined" && typeof e.ndata.msgID !== "undefined"){
          var current_msg = e.ndata.message;
          var current_cid = e.ndata.chatid;
          var current_msg_id = e.ndata.msgID;

          var data = {
                  chat_id: current_cid,
                  message: current_msg,
                  msg_id: current_msg_id

          };
          wplc_rest_api('edit_message', data, 12000, null);   
        }
      }
    });

    /**
     * Sends minimize event via the socket if it exists
    */
    jQuery(document).on("wplc_minimize_chat", function(e) {
        if(typeof socket !== "undefined" && typeof socket.emit !== "undefined"){
            socket.emit('custom data',{action:'wplc_minimized', chatid:wplc_cid});
        }
    });

    /**
     * Send maximize event via socket if it exists
    */
    jQuery(document).on("wplc_open_chat", function(e) {
        if(typeof socket !== "undefined" && typeof socket.emit !== "undefined"){
            socket.emit('custom data',{action:'wplc_maximized', chatid:wplc_cid});
        }
    });



    jQuery(document).on("mouseleave",".wplc-user-message", function() {
      var tmid = jQuery(this).attr('mid');
      jQuery(".message_"+tmid+ " .tcx-edit-message").hide();
    });
    jQuery(document).on("mouseenter",".wplc-user-message", function() {
      var tmid = jQuery(this).attr('mid');
      jQuery(".message_"+tmid+ " .tcx-edit-message").show();
    });



});