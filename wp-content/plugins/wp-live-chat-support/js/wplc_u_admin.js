var wplc_ajaxurl = wplc_ajaxurl;
var data = {
    action: 'wplc_admin_long_poll',
    security: wplc_ajax_nonce,
    wplc_list_visitors_data: false,
    wplc_update_admin_chat_table: false,
    wplc_extra_data: wplc_extra_data
};
var wplc_pending_refresh = null;
var current_chat_ids = new Object();
var chat_count = 0;
var wplc_run = true;
var ringer_cnt = 0;
var ringer_count = 0;
var wplc_new_chat_ringer_dismissed = false;
var orig_title = document.getElementsByTagName("title")[0].innerHTML;

var wplc_notification_icon_url = wplc_notification_icon;

var wplc_poll_delay = 1500;

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};


function wplc_notify_agent() {

	 var limit = 4; //Default
	 if (typeof tcx_ringer_count != "undefined") {
	   limit = parseInt(tcx_ringer_count.value);
	 }
	
	
    if (typeof wplc_wav_file !== 'undefined') {
		
	ringer_count ++;
	  if(ringer_count <= limit){
		if(wplc_new_chat_ringer_dismissed !== true){
		  setTimeout(function(){
			if(wplc_new_chat_ringer_dismissed !== true){
			  new Audio(wplc_wav_file).play();
			}
		  }, 3000);
		}
	  } else {
		wplc_new_chat_ringer_dismissed = true; //Set it to dismissed now
	  }
	}	
      
    
    if (ringer_cnt <= 0) {
      wplc_desktop_notification();
    }
    ringer_cnt++;

    if (ringer_cnt > 1) {
        clearInterval(wplc_pending_refresh);
        wplc_title_alerts4 = setTimeout(function () {
            document.title = orig_title;
        }, 4000);
        return;
    }

    document.title = "** CHAT REQUEST **";
    wplc_title_alerts2 = setTimeout(function () {
        document.title = "** CHAT REQUEST **";
    }, 2000);
    wplc_title_alerts4 = setTimeout(function () {
        document.title = orig_title;
    }, 4000);


        
    

}

function wplc_call_to_server(data) {
    if(typeof wplc_pro_admin_long_poll_data !== "undefined" && typeof wplc_pro_admin_long_poll_data === "function"){
        data = wplc_pro_admin_long_poll_data(data);
    }   

    jQuery.ajax({
        url: wplc_ajaxurl,
        data: data,
        type: "POST",
        success: function (response) {
            wplc_poll_delay = 1500;
            //Update your dashboard gauge
            if (response) {
                if (response === "0") { if (window.console) { console.log('WP Live Chat Support Return Error'); } wplc_run = false;  return; }

                response = JSON.parse(response);
                
                if(response.hasOwnProperty("error")){
                    /* stopping due to error */
                    wplc_run = false;
                    if (response['error'] === 1) {
                      window.onbeforeunload = null;
                      window.location.reload();
                    }
                    
                }

                data["wplc_update_admin_chat_table"] = response['wplc_update_admin_chat_table'];
                if (response['action'] === "wplc_update_chat_list") {
                    wplc_handle_chat_output(response['wplc_update_admin_chat_table']);
                    if (response['pending'] === true) {
                        
                        wplc_notify_agent();
                        wplc_pending_refresh = setInterval(function () {
                            
                            wplc_notify_agent();
                        }, 5000);
                    } else {
                        clearInterval(wplc_pending_refresh);
                        ringer_cnt = 0;
                    }
                }
                if (response['action'] === "wplc_update_admin_chat") {
                    jQuery("#wplc_admin_chat_area").html(response['wplc_update_admin_chat_table']);
                    if (response['pending'] === true) {

                        var orig_title = document.getElementsByTagName("title")[0].innerHTML;
                        var ringer_cnt = 0;
                        wplc_pending_refresh = setInterval(function () {

                            if (ringer_cnt <= 0) {
                                wplc_desktop_notification();
                            }

                            ringer_cnt++;

                            if (ringer_cnt > 1) {
                                clearInterval(wplc_pending_refresh);
                                wplc_title_alerts4 = setTimeout(function () {
                                    document.title = orig_title;
                                }, 4000);
                                return;
                            }

                            document.title = "** CHAT REQUEST **";
                            wplc_title_alerts2 = setTimeout(function () {
                                document.title = "** CHAT REQUEST **";
                            }, 2000);
                            wplc_title_alerts4 = setTimeout(function () {
                                document.title = orig_title;
                            }, 4000);


                            //document.getElementById("wplc_sound").innerHTML = "<embed src='"+   +"' hidden=true autostart=true loop=false>";

                        }, 5000);
                    } else {
                        clearInterval(wplc_pending_refresh);
                    }
                }

            }
        },
        error: function (jqXHR, exception) {
            wplc_poll_delay = 5000;
            if (jqXHR.status == 404) {
                wplc_display_error('Connection Error (404)', false);
                wplc_run = false;
            } else if (jqXHR.status == 500) {
                wplc_display_error('Connection Error (500) - Retrying in 5 seconds...', true);
                wplc_run = true;
            } else if (exception === 'parsererror') {
                wplc_display_error('Connection Error (JSON Error)', false);
                wplc_run = false;
            } else if (exception === 'abort') {
                wplc_display_error('Connection Error (Ajax Abort)', false);
                wplc_run = false;
            } else {
                wplc_display_error('Connection Error (Uncaught) - Retrying in 5 seconds...', true);
                wplc_run = true;
            }
        },
        complete: function (response) {
            if (wplc_run) {
                setTimeout(function () {
                    wplc_call_to_server(data);
                }, wplc_poll_delay);
            }
        },
        timeout: 120000
    });
};

function wplc_display_error(error, dismiss) {
    if (window.console) { console.log(error); }
    jQuery(".wplc_network_issue").html("<span>" + error + "</span>");
    jQuery(".wplc_network_issue").fadeIn();
    if(dismiss){
        setTimeout(function(){
            jQuery(".wplc_network_issue").fadeOut();
        }, 5000);
    }
}

function wplc_handle_chat_output(response) {
	var obj = jQuery.parseJSON(response);
    if (obj === false || obj === null) {
			jQuery("#wplc_chat_ul").html("");
			current_chat_ids = {};
			wplc_handle_count_change(0);
			
	} else {
		// NB: Perry: this block didn't appear to do anything
		//var size = Object.size(current_chat_ids);
		//wplc_handle_count_change(size);
		
		if (size < 1) {
			/* no prior visitor information, update without any checks */
			current_chat_ids = obj["ids"];
			if(current_chat_ids)
				wplc_update_chat_list(false,obj);
		} else {
			/* we have had visitor information prior to this call, update systematically */
			if (obj === null) {
				jQuery("#wplc_chat_ul").html("");
			} else {
				current_chat_ids = obj["ids"];
				if(current_chat_ids)
					wplc_update_chat_list(true,obj);
			}
		}


	}

	if(obj !== null && typeof obj !== "undefined" && obj["visitor_count"]) {
		var size = obj["visitor_count"];
        if (parseInt(size) === 0) {
            jQuery("#wplc_chat_ul").html("");
            current_chat_ids = {};
            wplc_handle_count_change(0);
        } else {
		  wplc_handle_count_change(size);
        }
	}

}
function wplc_handle_count_change(qty) {
if (parseInt(qty) !== parseInt(chat_count)) {
    jQuery(".wplc_vis_online").html(qty);
} else if (parseInt(qty) === parseInt(chat_count)) {
    jQuery(".wplc_vis_online").html(qty);
} else {
    jQuery(".wplc_vis_online").html(qty);
}
chat_count = qty;

}


function wplc_get_status_name(status) {
    if (status === 1) { return "<span class='wplc_status_box wplc_status_"+status+"'>complete</span>"; }
    if (status === 2) { return "<span class='wplc_status_box wplc_status_"+status+"'>pending</span>"; }
    if (status === 3) { return "<span class='wplc_status_box wplc_status_"+status+"'>active</span>"; }
    if (status === 4) { return "<span class='wplc_status_box wplc_status_"+status+"'>deleted</span>"; }
    if (status === 5) { return "<span class='wplc_status_box wplc_status_"+status+"'>browsing</span>"; }
    if (status === 6) { return "<span class='wplc_status_box wplc_status_"+status+"'>requesting chat</span>"; }
    if (status === 8){ return "<span class='wplc_status_box wplc_status_"+status+"'>chat ended</span></span>"; }
    if (status === 9){ return "<span class='wplc_status_box wplc_status_"+status+"'>chat closed</span>"; }
    if (status === 10){ return "<span class='wplc_status_box wplc_status_8'>minimized</span>"; }
    if (status === 12) { return "<span class='wplc_status_box wplc_status_8'>missed chat</span>"; }
}
function wplc_get_type_box(type) {
    if (type === "New") {
        return "<span class='wplc_status_box wplc_type_new'>New</span>";
    }
    if (type === "Returning") {
        return "<span class='wplc_status_box wplc_type_returning'>Returning</span>";
    }
}


function wplc_create_chat_ul_element_after_eating_vindaloo(obj,key) {

var v_img = obj[key]['image'];
var v_name = obj[key]['name'];
var v_email = obj[key]['email'];
var v_browser = obj[key]['data']['browser'];
var v_browsing = obj[key]['data']['browsing_nice_url'];
var v_browsing_url = obj[key]['data']['browsing'];
var v_status = obj[key]['status'];
var v_time = obj[key]['timestamp'];
var v_type = obj[key]['type'];
var v_action = obj[key]['action'];
var v_status_string = wplc_get_status_name(parseInt(v_status));
var v_ip_address = obj[key]['data']['ip'];

if (typeof obj[key]['other'] !== "undefined" && typeof obj[key]['other']['user_is_mobile'] !== "undefined") { var v_is_mobile = obj[key]['other']['user_is_mobile']; } else { var v_is_mobile = false; }

var v_vis_html = "<span class='wplc_header_v'><span class='wplc_header_v_m'>Visitor:</span> "+v_name+"</span>";
var v_nr_html = "<span class='wplc_header_nr'><span class='wplc_header_nr_m'>Type:</span> "+wplc_get_type_box(v_type)+"</span>";
var v_time_html = "<span class='wplc_header_t'><span class='wplc_header_t_m'>Time:</span> <span class='wplc_status_box wplc_status_1'>"+v_time+"</span></span>";
var v_nr_device = "<span class='wplc_header_t'><span class='wplc_header_dev_m'>Device:</span> <span class='wplc_status_box wplc_status_1'>"+(v_is_mobile ? "Mobile" : "PC")+"</span></span>";

var additional_data = "";

if(typeof obj[key] !== "undefined" && typeof obj[key]['other'] !== "undefined" && typeof obj[key]['other']['wplc_extra_data'] !== "undefined" && typeof obj[key]['other']['wplc_extra_data']['custom_fields'] !== "undefined"){
    additional_data = obj[key]['other']['wplc_extra_data']['custom_fields'];
}


if( typeof additional_data !== 'undefined' && additional_data != "" ) {
    additional_data = additional_data.replace(/\\/g, '');
    //additional_data = additional_data.replace(/\"/g, '');
    
    additional_data = JSON.parse( additional_data );

    var data_column_html = "";
    jQuery.each( additional_data, function( key, val){        
        var field_name = val[0];
        var field_value = val[1];

        data_column_html += "<span class='wplc-sub-item-header'>"+field_name+":</span> "+field_value+"<br/>";

    });
} else {
    data_column_html = "";
}

var v_nr_data = "<span class='wplc_header_d'><span class='wplc_header_d_m'>Data:</span> <span class='wplc-sub-item-header'>Page:</span> <a href='"+v_browsing_url+"' target='_BLANK'>"+v_browsing+"</a><br />";
if(v_email && v_email!='no email set') {
	v_nr_data += "<span class='wplc-sub-item-header'>Email:</span> <a href='mailto:"+v_email+"' target='_BLANK' class='wplc-sub-item-email-string'>"+v_email+"</a>";	
}
v_nr_data += data_column_html+"</span>";
	
var v_nr_status_html = "<span class='wplc_header_s'><span class='wplc_header_s_m'>Status:</span> <span class='browser-tag'>"+v_browser+"</span> "+v_status_string+"</span>";
var v_nr_action_html = "<span class='wplc_header_a'>"+v_action+"</span>";

var wplc_v_html = "\
    <div id='wplc_p_ul_"+key+"' class='wplc_p_cul' cid='"+key+"'>\n"
            +v_vis_html
            +v_time_html
            +v_nr_html
            +v_nr_device
            +v_nr_data
            +v_nr_status_html
            +v_nr_action_html+" <div>";
return wplc_v_html;


}

function wplc_update_chat_list(update,obj) {

/* first compare existing elements with the elements on the page */
if (update === false) {
    jQuery( ".wplc_chat_ul" ).html("");

    for (var key in obj) {
        if (obj.hasOwnProperty(key) && key !== "ids") {
            wplc_v_html = wplc_create_chat_ul_element_after_eating_vindaloo(obj,key);
            jQuery( "#wplc_chat_ul" ).append(wplc_v_html).hide().fadeIn(2000);
            
        }
    }
    current_chat_ids = obj["ids"];

} else {
    
    for (var key in current_chat_ids) {
        current_id = key;
        if (document.getElementById("wplc_p_ul_"+current_id) !== null) {
            /* element is already there */
            /* update element */
            if (typeof obj[current_id] !== "undefined") { /* if this check isnt here, it will throw an error. This check is here incase the item has been deleted. If it has, it will be handled futher down */
                jQuery("#wplc_p_ul_"+current_id).remove();
                wplc_v_html = wplc_create_chat_ul_element_after_eating_vindaloo(obj,current_id);
                jQuery( "#wplc_chat_ul" ).append(wplc_v_html);
                //jQuery( ".wplc_chats_container" ).append(obj[current_id]['content']);
            }


        } else {
            jQuery("#nifty_c_none").hide();
            /* new element to be created */
            if (typeof obj[current_id] !== "undefined") { /* if this check isnt here, it will throw an error. This check is here incase the item has been deleted. If it has, it will be handled futher down */
                
                wplc_v_html = wplc_create_chat_ul_element_after_eating_vindaloo(obj,current_id);
                jQuery( "#wplc_chat_ul" ).append(wplc_v_html);
                
                jQuery("#wplc_p_ul_"+current_id).hide().fadeIn(2000);
                
            }
        }


    }

}

    /* compare new elements to old elements and delete where neccessary */


    jQuery(".wplc_p_cul").each(function(n, i) {
        var cid = jQuery(this).attr("cid");
        if (typeof cid !== "undefined") {
            if (typeof current_chat_ids[cid] !== "undefined") { /* element still there dont delete */ }
            else {
                jQuery("#wplc_p_ul_"+cid).fadeOut(2000).delay(2000).remove();
                
            }
            var size = Object.size(current_chat_ids);
            wplc_handle_count_change(size);
        }
        // do something with it
    });
    if(jQuery('.wplc_p_cul').length < 1) {
        wplc_handle_count_change(0);
        current_chat_ids = {};
    }





}





jQuery(function () {
    jQuery('body').on("click", "a", function (event) {
        if (jQuery(this).hasClass('wplc_open_chat')) {
            if (event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false;
            }
            window.open(jQuery(this).attr("href"), jQuery(this).attr("window-title"), "width=800,height=600,scrollbars=yes", false);
        }
    });
    
    jQuery('body').on("click", "#wplc_close_ftt", function (event) {
        jQuery("#wplcftt").fadeOut(1000);
        var data = {
            action: 'wplc_hide_ftt',
            security: wplc_ajax_nonce,
        };
        jQuery.ajax({
            url: wplc_ajaxurl_home,
            data: data,
            type: "POST",
            success: function (response) {

            }
        });
       
    });

    var visitorNameRow = jQuery('.wplc-user-default-visitor-name__row'),
        requireUserInfo = jQuery('input[name=wplc_require_user_info]:checked').val();
    if ( 'none' === requireUserInfo || 'email' === requireUserInfo ) {
        visitorNameRow.show();
    } else {
        visitorNameRow.hide();
    }

    jQuery('body').on("click", "input[name=wplc_require_user_info]", function (event) {
        if ( 'none' === jQuery(this).val() || 'email' === jQuery(this).val() ) {
            visitorNameRow.show();
        } else {
            visitorNameRow.hide();
        }
    });

    if (!!wplc_choose_accept_chats.value) {
      wplc_call_to_server(data);
    } else {
      /* do nothing as they do not want to accept chats - kill the whole system! */
      jQuery("#wplc_admin_chat_area_new").html("<div class='wplc_chat_area_temp'>"+ " " + wplc_localized_quote_string+"</div>");
      jQuery("#wplc_admin_chat_holder").append(wplc_localized_offline_string)
    }

    jQuery("body").on("click", ".wplc_delete_message", function(e){

        var message_id = jQuery(this).attr('mid');

        var data = {
            action: 'delete_offline_message',
            security: wplc_ajax_nonce,
            mid: message_id
        }

        jQuery.post( wplc_ajaxurl, data, function( response ){

            if( response ){

                jQuery('#record_'+message_id).fadeOut(700);

            }


        });

    });


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

jQuery(function(){

    if( typeof ace !== 'undefined' ){

        jQuery(function($) {

            $('textarea[data-editor]').each(function() {

                var textarea = $(this);
                var mode = textarea.data('editor');                
                var editDiv = $('<div>', {
                    position: 'absolute',
                    width: '100%',
                    height: '250px',
                    'class': textarea.attr('class')
                }).insertBefore(textarea);
                textarea.css('display', 'none');
                var editor = ace.edit(editDiv[0]);            
                editor.getSession().setValue(textarea.val());
                editor.getSession().setMode("ace/mode/" + mode);
                editor.setTheme("ace/theme/twilight");
                textarea.closest('form').submit(function() {
                    textarea.val(editor.getSession().getValue());
                })

            });

        });

    }

});