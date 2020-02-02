var wplc_ajaxurl = wplc_ajaxurl;
var chat_status = 3;
var cid = wplc_cid;
var wplc_poll_delay = 1500;

var wplc_server = null;

wplc_server = new WPLCServer();

var wplc_server_last_loop_data = null;

function wplc_admin_message_receiver(data) {
  if (typeof wplc_loop_response_handler !== "undefined" && typeof wplc_loop_response_handler === "function") {
    wplc_loop_response_handler(data);

    data = JSON.parse(data);
    if (data.keep_alive === true) {
      setTimeout(function() {
        wplc_call_to_server_admin_chat(wplc_server_last_loop_data);
      }, 100);
    }
  }
}

function wplc_admin_retry_handler(data) {
  wplc_retry_interval = setTimeout(function() {
    wplc_server.prepareTransport(function() {
      wplc_call_to_server_admin_chat(wplc_server_last_loop_data);
    }, wplc_admin_message_receiver, wplc_admin_retry_handler, wplc_display_error);
  }, 500);
}

if (typeof wplc_action2 !== "undefined" && wplc_action2 !== "") {

  var data = {
    action: 'wplc_admin_long_poll_chat',
    security: wplc_ajax_nonce,
    cid: cid,
    chat_status: chat_status,
    action_2: wplc_action2,
    wplc_extra_data: wplc_extra_data
  };
} else {
  var data = {
    action: 'wplc_admin_long_poll_chat',
    security: wplc_ajax_nonce,
    cid: cid,
    chat_status: chat_status,
    wplc_extra_data: wplc_extra_data
  };

}
var wplc_run = true;
var wplc_had_error = false;
var wplc_first_run = true;

jQuery(function() {
  //Parse existing data
  var htmlToParse = jQuery(".admin_chat_box_inner").html();
  jQuery(".admin_chat_box_inner").html(wplcFormatParser(htmlToParse));
});

function wplc_call_to_server_admin_chat(data) {
  if (typeof wplc_admin_agent_name !== "undefined") {
    data.msg_from_print = wplc_admin_agent_name;
  }
  data.first_run = wplc_first_run;
  wplc_first_run = false;

  wplc_server_last_loop_data = data;

  wplc_server.send(wplc_ajaxurl, data, "POST", 120000,
    function(response) {
      wplc_poll_delay = 1500;
      wplc_loop_response_handler(response);
    },
    function(jqXHR, exception) {
      wplc_poll_delay = 5000;
      if (jqXHR.status == 404) {
        wplc_display_error('Error: Page not found [404]');
        wplc_run = false;
      } else if (jqXHR.status == 500) {
        wplc_display_error('Error: Internal server error [500]');
        wplc_display_error('Retrying in 5 seconds...');
        wplc_run = true;
      } else if (exception === 'parsererror') {
        wplc_display_error('Error: JSON error');
        wplc_run = false;
      } else if (exception === 'abort') {
        wplc_display_error('Error: Ajax request aborted');
        wplc_run = false;
      } else {
        wplc_display_error('Error: Uncaught Error' + jqXHR.responseText);
        wplc_display_error('Retrying in 5 seconds...');
        wplc_run = true;
      }
    },
    function(response) {
      var wplc_page_action = wplc_findGetParameter('action');
      if ((wplc_page_action !== undefined && wplc_page_action !== false) && wplc_page_action === 'history') {
        //We will no longer allow long polling on history screen
      } else {
        if (wplc_run) {
          setTimeout(function() {
            wplc_call_to_server_admin_chat(data);
          }, wplc_poll_delay);
        }
      }
    }
  );
}

function wplc_findGetParameter(parameterName) {
  var result = null,
    tmp = [];
  var items = location.search.substr(1).split("&");
  for (var index = 0; index < items.length; index++) {
    tmp = items[index].split("=");
    if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
  }
  return result;
}

function wplc_loop_response_handler(response) {
  if (response) {
    if (response === "0") {
      if (window.console) {
        console.log('WP Live Chat by 3CX Return Error');
      }
      wplc_run = false;
      return;
    }

    response = JSON.parse(response);

    jQuery.event.trigger({
      type: "wplc_admin_chat_loop",
      response: response
    });

    if (response['action'] === "wplc_ma_agant_already_answered") {
      if (wplc_findGetParameter('action') == 'history') {
        wplc_run = false;
      } else {
        jQuery(".end_chat_div").empty();
        jQuery('#admin_chat_box').empty().append("<h2>This chat has already been answered. Please close the chat window</h2>");
        wplc_run = false;
      }
    }
    if (response['action'] === 'wplc_chat_history') {
      for (k in response['chat_history']) {
        the_message = response['chat_history'][k];
        the_message.mid = k;
        wplc_push_message_to_chatbox(the_message, 'a', function() {
          wplc_scroll_to_bottom();
        });

      }
    }

    if (response['action'] === "wplc_update_chat_status") {
      data['chat_status'] = response['chat_status'];
      wplc_display_chat_status_update(response['chat_status'], cid);
    }
    if (response['action'] === "wplc_new_chat_message") {
      jQuery("#wplc_user_typing").fadeOut("slow").remove();

      current_len = jQuery("#admin_chat_box_area_" + cid).html().length;

      if (typeof response['chat_message'] === "object") {
        for (k in response['chat_message']) {
          response['chat_message'][k].mid = k;
          wplc_push_message_to_chatbox(response['chat_message'][k], 'a', function() {
            wplc_scroll_to_bottom();
          });

        }
      } else {
        wplc_push_message_to_chatbox(response['chat_message'], 'a', function() {
          wplc_scroll_to_bottom();
        });

      }

      new_length = jQuery("#admin_chat_box_area_" + cid).html().length;

      if (current_len < new_length) {
        if (!!wplc_enable_ding.value && !(/User is browsing <small/.test(response['chat_message']))) {
          new Audio(wplc_ding_file).play()
        }
      }
    }
    if (response['action'] === "wplc_user_open_chat") {
      data['action_2'] = "";
      window.location.replace(wplc_url);
    }

    if (typeof response['data'] === "object") {
      for (var index in response['data']) {
        if (typeof response['data'][index] === "object") {
          var the_message = response['data'][index];
          the_message.mid = index;
          wplc_push_message_to_chatbox(the_message, 'a', function() {
            wplc_scroll_to_bottom();
          });

        }
      }
    }
  }
}

/**
 * Scrolls the chat box to the bottom
 */
function wplc_scroll_to_bottom() {
  var height = jQuery('#admin_chat_box_area_' + cid)[0].scrollHeight;
  jQuery('#admin_chat_box_area_' + cid).scrollTop(height);
}

function wplc_display_error(error) {
  if (window.console) {
    console.log(error);
  }

  jQuery("#admin_chat_box_area_" + cid).append("<small>" + error + "</small><br>");
  wplc_scroll_to_bottom();
}

function wplc_display_chat_status_update(new_chat_status, cid) {
  if (new_chat_status !== "0") {} else {
    if (chat_status !== new_chat_status) {
      previous_chat_status = chat_status;
      chat_status = new_chat_status;

      if ((previous_chat_status === "2" && chat_status === "3") || (previous_chat_status === "5" && chat_status === "3")) {
        //jQuery("#admin_chat_box_area_" + cid).append("<em>"+wplc_string1+"</em><br />");
        wplc_scroll_to_bottom();

      } else if (chat_status == "10" && previous_chat_status == "3") {
        //jQuery("#admin_chat_box_area_" + cid).append("<em>"+wplc_string2+"</em><br />");
        the_message = {};
        the_message.originates = 3;
        the_message.msg = wplc_string2;
        the_message.other = {};
        var wplc_d = new Date();
        the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
        wplc_push_message_to_chatbox(the_message, 'a', function() {
          wplc_scroll_to_bottom();
        });

      } else if (chat_status === "3" && previous_chat_status === "10") {
        //jQuery("#admin_chat_box_area_" + cid).append("<em>"+wplc_string3+"</em><br />");
        the_message = {};
        the_message.originates = 3;
        the_message.msg = wplc_string3;
        the_message.other = {};
        var wplc_d = new Date();
        the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
        wplc_push_message_to_chatbox(the_message, 'a', function() {
          wplc_scroll_to_bottom();
        });
      } else if (chat_status === "1" || chat_status === "8") {
        wplc_run = false;
        the_message = {};
        the_message.originates = 3;
        the_message.msg = wplc_string4;
        the_message.other = {};
        var wplc_d = new Date();
        the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
        wplc_push_message_to_chatbox(the_message, 'a', function() {
          wplc_scroll_to_bottom();
          document.getElementById('wplc_admin_chatmsg').disabled = true;
        });
        //jQuery("#admin_chat_box_area_" + cid).append("<em>"+wplc_string4+"</em><br />");
        wplc_scroll_to_bottom();

        jQuery(".admin_chat_box_inner_bottom").hide();
        jQuery(".admin_chat_quick_controls").hide();
        jQuery(".end_chat_div").hide();
      }
    }
  }
}

jQuery(function() {

  jQuery("#nifty_file_input").on("change", function() {

    var file = this.files[0]; //Last file in array
    wplcShareFile(file, '#nifty_attach_fail_icon', '#nifty_attach_success_icon', '#nifty_attach_uploading_icon', "#nifty_select_file");

  });

  jQuery("#wplc_admin_chatmsg").focus();

  wplc_server.prepareTransport(function() {
    wplc_call_to_server_admin_chat(data);
  }, wplc_admin_message_receiver, wplc_admin_retry_handler, wplc_display_error);

  if (config.wplc_use_node_server) {
    var firstRunData = data;
    firstRunData.first_run = "true";
    WPLCServer.Ajax.send(wplc_ajaxurl, firstRunData, "POST", 120000,
      function(response) {
        wplc_poll_delay = 1500; //This section is not really relevant as this wont run again, but copy and paste haha
        wplc_loop_response_handler(response);
      }
    );
  }

  if (typeof wplc_action2 !== "undefined" && wplc_action2 !== "") {
    return;
  }

  if (jQuery('#wplc_admin_cid').length) {
    var wplc_cid = jQuery("#wplc_admin_cid").val();
    wplc_scroll_to_bottom();
  }

  jQuery(".wplc_admin_accept").on("click", function() {
    wplc_title_alerts3 = setTimeout(function() {
      document.title = "WP Live Chat by 3CX";
    }, 2500);
    var cid = jQuery(this).attr("cid");

    var data = {
      action: 'wplc_admin_accept_chat',
      cid: cid,
      security: wplc_ajax_nonce
    };
    jQuery.post(wplc_ajaxurl, data, function(response) {
      wplc_refresh_chat_boxes[cid] = setInterval(function() {
        wpcl_admin_update_chat_box(cid);
      }, 3000);
      jQuery("#admin_chat_box_" + cid).show();
    });
  });

  jQuery("#wplc_admin_chatmsg").keyup(function(event) {
    if (event.keyCode == 13) {
      jQuery("#wplc_admin_send_msg").click();
    }
  });

  jQuery("#wplc_admin_close_chat").on("click", function() {
    var wplc_cid = jQuery("#wplc_admin_cid").val();
    var data = {
      action: 'wplc_admin_close_chat',
      security: wplc_ajax_nonce,
      cid: wplc_cid,
      wplc_extra_data: wplc_extra_data

    };
    jQuery.post(wplc_ajaxurl, data, function(response) {

      window.close();
    });

  });

  jQuery("#wplc_admin_send_msg").on("click", function() {
    var wplc_cid = jQuery("#wplc_admin_cid").val();
    var wplc_chat = document.getElementById('wplc_admin_chatmsg').value;
    var wplc_name = "a" + "d" + "m" + "i" + "n";

    if (typeof wplc_name_override !== "undefined" && wplc_name_override !== "") {
      wplc_name = "<strong>" + wplc_name_override + ": </strong>";
    } else if (typeof wplc_show_chat_detail.name !== 'undefined') {
      if (wplc_show_chat_detail.name !== '') {
        wplc_name = "<strong>" + wplc_show_chat_detail.name + ": </strong>";
      } else {
        wplc_name = "";
      }
    } else {
      wplc_name = wplc_name;
    }

    jQuery("#wplc_admin_chatmsg").val('');

    if (wplc_chat !== "") {
      the_message = {};
      the_message.originates = 1;
      the_message.aid = wplc_extra_data.agent_id;
      the_message.msg = wplc_chat;
      the_message.other = {};
      var wplc_d = new Date();
      the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
      wplc_push_message_to_chatbox(the_message, 'a', function() {
        wplc_scroll_to_bottom();
      });

      wplc_extra_data.msg_data = {};
      if (typeof wplc_admin_agent_name !== "undefined")
        wplc_extra_data.msg_data.aname = wplc_admin_agent_name;
      if (typeof wplc_admin_agent_email !== "undefined")
        wplc_extra_data.msg_data.aemail = wplc_admin_agent_email;

      var data = {
        action: 'wplc_admin_send_msg',
        security: wplc_ajax_nonce,
        cid: wplc_cid,
        msg: wplc_chat,
        wplc_extra_data: wplc_extra_data
      };

      if (typeof wplc_admin_agent_name !== "undefined") {
        data.msg_from_print = wplc_admin_agent_name;
      }

      wplc_server.sendMessage(wplc_ajaxurl, data, "POST", 120000,
        function() {
          //Success
          wplc_server.asyncStorage(wplc_ajaxurl, data, 120000);
        },
        function() {
          //Fail
        },
        function() {
          //Complete
        }
      );
    }

  });

});

/* Handles Uploading and sharing a file within chat*/
function wplcShareFile(fileToUpload, failedID, successID, uploadingID, originalID) {
  if (fileToUpload == undefined || fileToUpload == false || fileToUpload == null) {
    return;
  }

  var afterFailedUpload = function() {
    jQuery(uploadingID).hide();
    jQuery(failedID).show();
    setTimeout(function() {
      jQuery(failedID).hide();
      jQuery(originalID).show();
    }, 2000);
  }

  var formData = new FormData();

  formData.append('action', 'wplc_upload_file');
  formData.append('cid', cid);
  formData.append('file', fileToUpload);
  formData.append('timestamp', Date.now());
  formData.append('security', wplc_ajax_nonce);

  /*Handle jQuery Elements*/
  jQuery(uploadingID).show();
  jQuery(originalID).hide();
  jQuery(successID).hide();
  jQuery(failedID).hide();
  if (fileToUpload.name.match(new RegExp('^.*\\.(' + config.allowed_upload_extensions + ')$', 'i'))) {
    // Files allowed - continue
    if (fileToUpload.size < 8000000) { //Max size of 4MB
      jQuery.ajax({
        url: wplc_home_ajaxurl,
        type: 'POST',
        data: formData,
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
          if (parseInt(data) !== 0) {
            jQuery(uploadingID).hide();
            jQuery(successID).show();
            setTimeout(function() {
              jQuery(successID).hide();
              jQuery(originalID).show();
            }, 2000);
            if (data.substring(0, 7) !== 'ERROR: ') {
              var tag = 'link';
              jQuery("#wplc_admin_chatmsg").val(tag + ":" + data + ":" + tag); //Add to input field
              jQuery("#wplc_admin_send_msg").trigger("click"); //Send message
            } else {
              alert('Upload error: ' + data.substring(7));
              afterFailedUpload();
            }
          } else {
            afterFailedUpload();
          }
        },
        error: function() {
          afterFailedUpload();
        }
      });
    } else {
      alert("File limit is 4mb");
      afterFailedUpload();
    }
  } else {
    alert("File type not supported.");
    afterFailedUpload();
  }
}