var init_chat_success_timeout = {};

//jQuery(function(){
jQuery(document).on("tcx_dom_ready", function(e) {


	/**
	 * Get unread message counts for agent to agent chats
	 */
    wplc_get_unread_agent_message_counts();


    /**
     * Build the list of agents
     */
    for (l in wplc_agent_data) {
        if (parseInt(l) === parseInt(agent_id)) { 
            wplc_c = 'online';
            t_ag_cid = nifty_api_key+''+parseInt(agent_id)+''+parseInt(agent_id);
            t_ag_cid = md5(t_ag_cid);
            
        } else { 
            wplc_c = 'offline';
            t_ag_cid = nifty_api_key+''+Math.min(parseInt(agent_id), parseInt(l))+''+Math.max(parseInt(agent_id), parseInt(l));
            t_ag_cid = md5(t_ag_cid);

        }
        jQuery('<li/>', {
          'class': 'online_agent online_agent_'+l,
          'id': t_ag_cid,
          'aid': l,
          html: '<span class="online_offline '+wplc_c+'"></span> <span class="ag_t_span_'+l+'">'+wplc_agent_data[l].name+'</span> <br/>'
        }).appendTo(".online_agent_list");

        //socket.emit('chat accepted',{chatid: t_ag_cid,agent_id:l,agent_name:wplc_agent_data[l].name});
        
      
    }

    jQuery(document).on("tcx_messages_added", function(e) {
        var new_chat_id = typeof e.ndata.cid !== "undefined" ? e.ndata.cid : false;

        var data = {
            security: wplc_nonce,
            cid: new_chat_id
        };
        if(new_chat_id !== false){
          setTimeout(function(){
            wplc_rest_api('get_custom_field_info', data, 12000, function(content_returned) {
              if(typeof content_returned !== "undefined"){
                if(typeof content_returned['code'] !== "undefined" && content_returned['code'] === "200"){
                  if(typeof content_returned['data'] !== "undefined"){
                    var returned_html = content_returned["data"];
                    the_message = wplc_generate_system_notification_object(returned_html, {}, 0);
                    if (!!the_message.other) {
                      the_message.other.preformatted =  true;
                    } else {
                      the_message.other = {preformatted: true};
                    }
                    wplc_push_message_to_chatbox(the_message,'a', function() {
                        //jQuery.event.trigger({type: "tcx_scroll_bottom"});
                    });
                    
                  }
                }
              }
            });
          },1000);
        }
    });

    jQuery(document).on("tcx_initiate_chat", function(e) {
    	if (typeof e.ndata === "object" && typeof e.ndata.chatid !== "undefined" && typeof e.ndata.agent !== "undefined") {
    		var data = {
	            aid: e.ndata.agent,
	            rel: e.ndata.chatid,
	            security: wplc_nonce,
		    };
		    wplc_rest_api('initiate_chat', data, 12000, function (results) {}); 


	    	the_message = wplc_generate_system_notification_object("Attempting to open the chat box on the visitor's side.", {}, 0);
	        
	        tcx_add_message_to_sessionStorage(e.ndata.chatid, the_message);

	        if (e.ndata.chatid === active_chatid) {
	          wplc_push_message_to_chatbox(the_message,'a', function() {
	              jQuery.event.trigger({type: "tcx_scroll_bottom"});
	          });
	        }
	        init_chat_success_timeout[e.ndata.chatid] = setTimeout(function() {
	        	the_message = wplc_generate_system_notification_object("The chat box could not be opened on the visitor's side. The user may have already left.", {}, 0);
	        
		        tcx_add_message_to_sessionStorage(e.ndata.chatid, the_message);

		        if (e.ndata.chatid === active_chatid) {
		          wplc_push_message_to_chatbox(the_message,'a', function() {
		              jQuery.event.trigger({type: "tcx_scroll_bottom"});
		          });
		        }
	        },12000)
    	}
    });

    jQuery(document).on("tcx_notify_agent_initiate_received", function(e) {

    	clearTimeout(init_chat_success_timeout[e.ndata.chatid]);
    	the_message = wplc_generate_system_notification_object("The chat box has been successfully opened on the visitor's side.", {}, 0);
        
        tcx_add_message_to_sessionStorage(e.ndata.chatid, the_message);

        if (e.ndata.chatid === active_chatid) {
          wplc_push_message_to_chatbox(the_message,'a', function() {
              jQuery.event.trigger({type: "tcx_scroll_bottom"});
          });
        }

        socket.emit('chat accepted',{chatid: e.ndata.chatid,agent_id:e.ndata.agent,agent_name:tcx_agent_name});
        jQuery.event.trigger({type: "tcx_send_get_chat_history", ndata:{chatid:e.ndata.chatid,agent_name:tcx_agent_name,agent_id:e.ndata.agent}});
    });

	jQuery(document).on("tcx_add_initiate_button", function(e) {

    var tmp_chatid = typeof e.cid !== "undefined" ? e.cid : false;

    //nc_clear_action_col_visitor_row(tmp_chatid);
		
		if(tmp_chatid !== false){
			jQuery('<div/>', {
		      'class': 'vcol visActionCol',
		      html: "<a href='javascript:void(0);' class='init_chat init_chat_"+tmp_chatid+" btn btn-info pull-right' cid='"+ tmp_chatid +"'>Initiate Chat</a>"
		    }).appendTo('#vis'+ tmp_chatid);  
		}
	});

  jQuery("body").on("change","#wplc_field_type", function() {

    var selection = jQuery(this).val();
    
    if( selection == '1' ){
        jQuery("#wplc_field_value_dropdown_row").show();
        jQuery("#wplc_field_value_row").hide();
    } else {
        jQuery("#wplc_field_value_dropdown_row").hide();
        jQuery("#wplc_field_value_row").show();
    }

});

  jQuery(document).on("tcx_add_initiate_button_no_col", function(e) {
    if (typeof e.cid !== "undefined") {
      nc_clear_action_col_visitor_row(e.cid);
      jQuery('<div/>', {
        'class': '',
        html: "<a href='javascript:void(0);' class='init_chat btn btn-info pull-right' cid='"+ e.cid +"'>Initiate Chat</a>"
      }).appendTo('#vis'+e.cid + ' .visActionCol');
    }
  });

    /**
    * Handles the pasting of images inside the text area
    *
    */
    jQuery(document).on("tcx_input_paste", function(e) {
        
        var event = e.event;
        // use event.originalEvent.clipboard for newer chrome versions
        var items = (e.event.clipboardData  || e.event.originalEvent.clipboardData).items;
        // find pasted image among pasted items
        var blob = null;
        for (var i = 0; i < items.length; i++) {
          if (items[i].type.indexOf("image") === 0) {
            blob = items[i].getAsFile();
          }
        }
        // load image if there is a pasted image
        if (blob !== null) {
          var reader = new FileReader();
          reader.onload = function(event) {
            document.getElementById("inputMessage").value = "####"+event.target.result+"####";
            jQuery("#wplc_send_msg").click();
            
          };
          reader.readAsDataURL(blob);
        }
    });    


	/**
	 * Default typing preview functionality
	 * 
	 * @return void
	 */
  jQuery(document).on("tcx_typing_preview_received", function (e) {
    if (config.enable_typing_preview==1) {
      if (typeof e.ndata === "object" && typeof e.ndata.chatid !== "undefined" && typeof e.ndata.preview !== "undefined" && e.ndata.preview.tempmessage !== "undefined") {
        if (active_chatid === e.ndata.chatid) {
          message_preview_currently_being_typifcationed = e.ndata.preview.tempmessage;
          var tmsg = e.ndata.username + " is typing: " + message_preview_currently_being_typifcationed;


          if (jQuery(".typing_preview_" + e.ndata.chatid).length) {
            /* typing element exists, lets modify it */
            jQuery(".typing_preview_" + e.ndata.chatid).html(tmsg);
            //jQuery.event.trigger({type: "tcx_scroll_bottom"});

          } else {
            /* create the typing element and add contents to it */
            if (message_preview_currently_being_typifcationed.trim() !== "") {
              jQuery("#messages").append("<span class='wplc-user-message wplc-color-bg-1 wplc-color-2 wplc-color-border-1 typing_preview_" + e.ndata.chatid + "'>" + tmsg + "</span>");
              //jQuery.event.trigger({type: "tcx_scroll_bottom"});
            }
          }
        }
      }
    }

  });

	/**
	 * Clearing of the typing preview
	 * 
	 * @return void
	 */
	jQuery(document).on("tcx_clear_typing_preview", function(e) {
		jQuery(".typing_preview_"+e.cid).html('');
		jQuery(".typing_preview_"+e.cid).remove();
		//jQuery.event.trigger({type: "tcx_scroll_bottom"});
	});


	/**
	 * Default typing display functionality
	 * 
	 * @return void
	 */
	jQuery(document).on("tcx_typing", function(e) {
    var tmsg = wplc_safe_html(decodeURI(e.ndata.username)+ config.wplc_localized_string_is_typing_single);
    
	    if (jQuery(".typing_preview_"+e.ndata.chatid).length) {
	    	/* typing element exists, lets modify it */
	    	jQuery(".typing_preview_"+e.ndata.chatid).html(tmsg);
	    	//jQuery.event.trigger({type: "tcx_scroll_bottom"});

	    } else {
	    	/* create the typing element and add contents to it */
	    	
	    	jQuery("#messages").append("<span class='wplc-user-message wplc-color-bg-1 wplc-color-2 wplc-color-border-1 typing_preview_"+e.ndata.chatid+"'>"+tmsg+"</span>");
	    	//jQuery.event.trigger({type: "tcx_scroll_bottom"});
	    }
	});

	/**
	 * Clearing of the typing message
	 * 
	 * @return void
	 */
	jQuery(document).on("tcx_stop_typing", function(e) {
		jQuery(".typing_preview_"+e.chatid).html('');
		jQuery(".typing_preview_"+e.chatid).remove();
		//jQuery.event.trigger({type: "tcx_scroll_bottom"});
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

    jQuery(document).on("mouseleave",".wplc-admin-message", function() {
        var tmid = jQuery(this).attr('mid');
        jQuery(".message_"+tmid+ " .tcx-edit-message").hide();
    });
    jQuery(document).on("mouseenter",".wplc-admin-message", function() {
        var tmid = jQuery(this).attr('mid');
        jQuery(".message_"+tmid+ " .tcx-edit-message").show();
    });


});


var wplc_get_unread_agent_message_counts = function() {
    var data = {
            agent_id: agent_id,
            security: wplc_nonce,
    };
    wplc_rest_api('get_agent_unread_message_counts', data, 12000, function (results) {
    	if (typeof results === "object" && results.response === "Unread count agents") {
    		for (k in results.data) {
    			if (parseInt(results.data[k]) > 0) {
	    			jQuery('<p/>', {
				      'class': 'unread_count',
				      html: ""+results.data[k]+""
				    }).appendTo('.online_agent_'+ k);
				    jQuery(".online_agent_"+k).addClass('newmessage');

				    /**
				     * Add the qty to the unread_count variable
				     */
				    var tcid = jQuery('.online_agent_'+ k).attr('id');
				    unread_count[tcid] = parseInt( results.data[k] );
	    		}

    			
    		}
    	}
    }); 

}