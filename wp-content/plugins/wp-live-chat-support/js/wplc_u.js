/*
 * Cookie Status
 *
 * 1 - complete - user has left site
 * 2 - pending - user waiting for chat to be answered by admin
 * 3 - active chat - user and admin are chatting
 * 4 - deleted
 * 5 - browsing - no data has been inputted
 * 6 - requesting chat - admin has requested a chat with user
 * 7 - timed out - visitor has timed out
 * 8 - complete but now browsing again
 * 9 - user closed chat before starting chat
 * 10 - user minimized active chat
 * 11 - user moved on to another page (session variable is different)
 * 12 - user has not been answered after sending chat request and is still active
 *
 */
var wplc_is_chat_open = false;
var wplc_online = false;
var wplc_agent_name = "";
var msg_history = new Array();
var wplc_is_minimized = false; /* global to hold whether or not the chat box is minimized */
var wplc_retry_interval = null;
var wplc_run = true;
var wplc_server = null;
wplc_server = new WPLCServer();
var wplc_server_last_loop_data = null;
var wplc_shown_welcome = false;
var wplc_current_agent = false;

/* node support */
var ns_obj = {};

/* node variable mapping */
var welcome_message = '';

var wplc_session_variable = new Date().getTime();
var wplc_cid;
var wplc_check_hide_cookie;
var wplc_chat_status = "";
var wplc_cookie_name = "";
var wplc_cookie_email = "";
var wplc_init_chat_box_check = true;
var wplc_cid = null;

var initial_data = {};
var wplc_fist_run = true;
var wplc_long_poll_delay = 1500;

jQuery(function() {

  /* Gutenberg functions */
  jQuery('.wp-block-wp-live-chat-support-wplc-chat-box').on('click', function() {
    jQuery("#wplc_hovercard").fadeOut("fast");
    jQuery("#wplc-chat-alert").removeClass('is-active');
    wplc_is_chat_open = true;
    jQuery.event.trigger({ type: "wplc_open_chat" });
  });

  wplc_map_node_variables();

  /* preload the images */
  wplc_preload();

  wplc_cid = Cookies.get('wplc_cid');

  if (typeof wplc_cid === 'undefined') {
    wplc_cid = null;
  } else {
    wplc_cid = Cookies.get('wplc_cid');
  }

  wplc_check_hide_cookie = Cookies.get('wplc_hide');
  wplc_check_minimize_cookie = Cookies.get('wplc_minimize');
  wplc_chat_status = Cookies.get('wplc_chat_status');
  wplc_cookie_name = Cookies.get('wplc_name');
  wplc_cookie_email = Cookies.get('wplc_email');
  // Always start on 5 - ajax will then return chat status if active

  Cookies.set('wplc_chat_status', 5, { expires: 1, path: '/' });
  wplc_chat_status = 5;
  //if (typeof wplc_chat_status !== "undefined" && parseInt(wplc_chat_status) === 3) { } else {

  //}

  //Preflight check for WPML integration
  var wplc_wpml_body_language = jQuery("html").attr("lang");
  if (typeof wplc_wpml_body_language !== "undefined") {
    if (wplc_wpml_body_language.indexOf("-") !== -1) {
      wplc_wpml_body_language = wplc_wpml_body_language.substr(0, wplc_wpml_body_language.indexOf("-"));
    }
    Cookies.set('_icl_current_language', wplc_wpml_body_language, { expires: 1, path: '/' });
  }

  var data = {
    action: 'wplc_get_chat_box',
    security: wplc_nonce,
    cid: wplc_cid
  };

  jQuery.ajax({
    url: wplc_ajaxurl_site,
    data: data,
    type: "POST",
    success: function(response) {
      /* inject html */
      if (response) {
        if (response === "0") {
          if (window.console) { console.log('WP Live Chat Support Return Error'); }
          wplc_run = false;
          return;
        }
        response = JSON.parse(response);
        jQuery("body").append(response['cbox']);

        wplc_listenForScrollEvent(jQuery("#wplc_chatbox"));

        if (typeof wplc_cookie_name == 'undefined' || typeof wplc_cookie_email == 'undefined') {
          try {
            var wplc_cookie_name = jQuery(jQuery.parseHTML(response['cbox'])).find("#wplc_name").val().replace(/(<([^>]+)>)/ig, "");
          } catch (e) {}
          try {
            var wplc_cookie_email = jQuery(jQuery.parseHTML(response['cbox'])).find("#wplc_email").val().replace(/(<([^>]+)>)/ig, "");
          } catch (e) {}
        }

        /* is an agent online? */
        if (response['online'] === false) {
          wplc_run = false;
          wplc_online = false;
          ns_obj.o = '0';

        } else {
          wplc_online = true;
          ns_obj.o = '1';
        }

        if (!wplc_filter_run_override.value || wplc_online === false) { wplc_run = false; } else { /* we can run */ }

        /* has this user been assigned an agent? */
        if (typeof response['type'] === "undefined") {
          wplc_shown_welcome = false;
        } else {
          if (response['type'] === "returning") {
            wplc_shown_welcome = true;
            /* set the agent vars so we can access them on the fly */
            if (typeof response['agent_data'] !== "undefined") {
              wplc_current_agent = response['agent_data'];
            }
          } else {
            wplc_shown_welcome = false;
          }

        }


        /*Support mobile loggin*/
        var wplc_mobile_check = false;
        if (typeof wplc_is_mobile !== "undefined" && (wplc_is_mobile === "true" || wplc_is_mobile === true)) {
          wplc_mobile_check = true;
        }

        /* start long polling */
        var data = {
          action: 'wplc_call_to_server_visitor',
          security: wplc_nonce,
          cid: wplc_cid,
          wplc_name: wplc_cookie_name,
          wplc_email: wplc_cookie_email,
          status: wplc_chat_status,
          wplcsession: wplc_session_variable,
          wplc_is_mobile: wplc_mobile_check,
          wplc_extra_data: wplc_extra_data
        };

        if (wplc_server.browserIsSocketReady()) {
          data.socket = true;

          /* Event added for start chat binding in wplc_node.js */
          jQuery.event.trigger({ type: 'wplc_sockets_ready' });
        }

        initial_data = data;
        // ajax long polling function
        if (!wplc_filter_run_override.value || wplc_online === false) {

          wplc_call_to_server_chat(data, true, true);
        } else {

          wplc_call_to_server_chat(data, true, false);
        }

        if (wplc_cid !== null && wplc_init_chat_box_check == true && wplc_init_chat_box !== false) {

          wplc_init_chat_box(wplc_cid, wplc_chat_status);

        } else {
          //Node and offline
          if (config.wplc_use_node_server) {

            if (wplc_check_hide_cookie != "yes") {
              wplc_dc = setTimeout(function() {
                wplc_cbox_animation();
              }, parseInt(window.wplc_misc_strings.wplc_delay));
            }
          }
        }


      }

    }

  });

  /**
   * Preload images from the localized variable
   * @return void
   */
  function wplc_preload() {
    var images = [];
    if (typeof wplc_preload_images !== "undefined" && typeof wplc_preload_images === "object") {
      var wplc_i = 0;
      for (var key in wplc_preload_images) {
        if (wplc_preload_images.hasOwnProperty(key)) {
          images[wplc_i] = new Image();
          images[wplc_i].src = wplc_preload_images[key];
          wplc_i++;
        }
      }
    }
  }


  /**
   * Detect if the chatbox is being scrolled.
   *
   * This had to be created as jQuery does not allow the scroll event to bubble up the DOM tree.
   *
   * Thank you Bikal Basnet (https://stackoverflow.com/questions/16505182/bind-scroll-event-to-dynamic-div)
   *
   * @param  object el The element in question
   */
  function wplc_listenForScrollEvent(el) {
    el.on("scroll", function() {
      el.trigger("wplc-custom-scroll");
    })

  }



  jQuery("body").on('keyup', '#wplc_email, #wplc_name', function(e) {
    if (e.keyCode == 13) {
      jQuery("#wplc_start_chat_btn").trigger("click")
    }
  });

  jQuery("body").on("click", "#wplc_end_chat_button", function(e) {
    var data = {
      security: wplc_nonce,
      chat_id: wplc_cid,
      agent_id: 0
    };

    jQuery(this).hide();
    // deleting these cookies will empty chat window on reload
    Cookies.remove('wplc_chat_status');
    Cookies.remove('wplc_cid');
    Cookies.remove('nc_status');
    if (jQuery(this).attr('wplc_disable') === undefined && jQuery(this).attr('wplc_disable') !== 'true') {
      wplc_rest_api('end_chat', data, 12000, null);
      jQuery.event.trigger({ type: "wplc_end_chat_as_user" });
    }
  });

  jQuery(document).on("wplc_update_gdpr_last_chat_id", function(e) {
    jQuery('#wplc_gdpr_remove_data_button,#wplc_gdpr_download_data_button').attr('data-wplc-last-cid', wplc_cid);
  });

  // Fix conflict with Responsive Lighbox plugin
  setTimeout(function() {
    if (jQuery('html').hasClass('nivo-lightbox-notouch') || jQuery('a[rel*="lightbox"]').length) {
      jQuery("body").on("keyup", function(event) {
        if (event.keyCode === 13) {
          jQuery("#wplc_send_msg").trigger("click");
        }
      });
    }
  }, 5000);

  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    setTimeout(function() {
      var liveChat4 = jQuery('#wp-live-chat-4');
      var liveChat = jQuery('#wp-live-chat');
      if (!liveChat.hasClass('classic')) {
        jQuery('body').on('click', function(event) {
          if (liveChat.hasClass('mobile-active')) {
            if (event.target.id !== 'wplc_chatmsg' && event.target.className !== 'wdt-emoji-picker') {
              liveChat4.removeClass('is-full-height');
            } else {
              liveChat4.addClass('is-full-height');
            }
          }
        });
      } else {
        jQuery('body').on('click', function(event) {
          if (liveChat.hasClass('mobile-active')) {
            if (event.target.id !== 'wplc_chatmsg' && event.target.className !== 'wdt-emoji-picker') {
              liveChat.removeClass('is-full-height');
            } else {
              liveChat.addClass('is-full-height');
            }
          }
        });
      }
    }, 500);
  }
});


function wplc_map_node_variables() {
  if (typeof wplc_welcome_msg !== "undefined") {
    welcome_message = wplc_welcome_msg;
  }
}

/**
 * This instantly sends the user the welcome message (i.e. to wait for any agent and start typing so long)
 *
 *
 */
function wplc_send_welcome_message() {
  if (wplc_welcome_msg !== "" && !wplc_shown_welcome) {
    message_class = "wplc-system-notification wplc-color-4 tmp-welcome-msg";
    var concatenated_message = "<span class='" + message_class + "'>";
    concatenated_message += wplc_safe_html(wplc_welcome_msg);
    concatenated_message += "</span>";
    jQuery("#wplc_chatbox").append(concatenated_message);
    wplc_scroll_to_bottom();
    wplc_shown_welcome = true;
  }
}

jQuery(function() {
  if (wplc_online) {
    jQuery(document).on('click', '#wp-live-chat-header', function() {
      jQuery('#speeching_button').html(wplc_pro_sst1);
      jQuery('#wplc_name').val(wplc_user_default_visitor_name);
    })
  } else {
    jQuery('#wplc_na_msg_btn').val(wplc_pro_offline_btn_send);
  }
})

/**
 * Scrolls the chat box to the bottom
 *
 */
function wplc_scroll_to_bottom() {
  var height = jQuery('#wplc_chatbox')[0].scrollHeight;
  jQuery('#wplc_chatbox').scrollTop(height);
}



function wplc_user_message_receiver(data) {
  if (typeof wplc_loop_response_handler !== "undefined" && typeof wplc_loop_response_handler === "function") {
    wplc_loop_response_handler(data, wplc_server_last_loop_data);
    data = JSON.parse(data);
    if (typeof data['status'] !== "undefined") {
      delete wplc_server_last_loop_data.status;
    }

    if (data.keep_alive === true) {

      setTimeout(function() {
        wplc_server_last_loop_data.status = wplc_chat_status;
        wplc_call_to_server_chat(wplc_server_last_loop_data);
      }, 100);
    }
  }
}

function wplc_user_retry_handler(data) {
  var tstatus = Cookies.get("wplc_chat_status");

  if (tstatus !== "undefined") {
    if (tstatus !== 8 || tstatus !== 1) {
      wplc_retry_interval = setTimeout(function() {

        wplc_server.prepareTransport(function() {
          //Transport ready...
          wplc_server_last_loop_data.status = parseInt(tstatus); //Set to existing status
          wplc_call_to_server_chat(wplc_server_last_loop_data);
        }, wplc_user_message_receiver, wplc_user_retry_handler, wplc_log_connection_error);


      }, 500);
    }

  }
}

function wplc_call_to_server_chat(data, first_run, short_poll) {

  if (config.wplc_use_node_server) {
    //not sending to WP
    return;
  } else {
    if (typeof first_run === "undefined") { first_run = false; };
    if (typeof short_poll === "undefined") { short_poll = false; };
    data.first_run = first_run;
    data.short_poll = short_poll;
    if (typeof Cookies.get('wplc_name') !== "undefined") {
      data.msg_from_print = Cookies.get('wplc_name');
    }
    wplc_server_last_loop_data = data;

    wplc_server.send(wplc_ajaxurl, data, "POST", 120000,
      function(response) {
        wplc_long_poll_delay = 1500;
        wplc_loop_response_handler(response, data);
      },
      function(jqXHR, exception) {
        wplc_long_poll_delay = 5000;
        if (jqXHR.status == 404) {
          wplc_log_connection_error('Error: Requested page not found. [404]');
          wplc_run = false;
        } else if (jqXHR.status == 500) {
          wplc_log_connection_error('Error: Internal Server Error [500].');
          wplc_log_connection_error('Retrying in 5 seconds...');
          wplc_run = true;
        } else if (exception === 'parsererror') {
          wplc_log_connection_error('Error: Requested JSON parse failed.');
          wplc_run = false;
        } else if (exception === 'abort') {
          wplc_log_connection_error('Error: Ajax request aborted.');
          wplc_run = false;
        } else {
          wplc_log_connection_error('Error: Uncaught Error.\n' + jqXHR.responseText);
          wplc_log_connection_error('Retrying in 5 seconds...');
          wplc_run = true;
        }
      },
      function(response) {
        if (wplc_run) {
          if (wplc_server.isInSocketMode() === false && wplc_server.isPreparingSocketMode() === false) {
            setTimeout(function() { wplc_call_to_server_chat(data, false, false); }, wplc_long_poll_delay);
          } else if ((wplc_server.isInSocketMode() === false && wplc_server.isPreparingSocketMode() === true) && (typeof wplc_transport_prepared !== "undefined" && wplc_transport_prepared === false)) {
            /* Allows for initiate chat to work on the node server */
            if (config.wplc_use_node_server) {
              /* do not run this if using not the node jedi */
              setTimeout(function() {
                wplc_call_to_server_chat(data, false, true);
              }, 7500);
            }
          } else {
            if (typeof response !== "undefined" && typeof response.responseText !== "undefined" && response.responseText !== "") {
              var response_data = JSON.parse(response.responseText);
              if (typeof wplc_transport_prepared !== "undefined") {
                if (wplc_transport_prepared !== true && (parseInt(response_data.status) === 3 || parseInt(response_data.status) === 2)) {
                  //Transport is unprepared and the user has returned to the page with a status 3/2
                  wplc_server.prepareTransport(function() { wplc_call_to_server_chat(data, false, false); }, wplc_user_message_receiver, wplc_user_retry_handler, wplc_log_connection_error);
                }
              }
            }
          }
        }
      });
  }
};

function wplc_loop_response_handler(response, data) {
  if (!response) {
    return;
  }
  if (response === "0") {
    if (window.console) { console.log('WP Live Chat Support Return Error'); }
    wplc_run = false;
    return;
  }
  if (typeof response !== "object") {
    response = JSON.parse(response);
  }
  data['action_2'] = "";
  if (typeof response['wplc_name'] !== "undefined") { data['wplc_name'] = response['wplc_name']; }
  if (typeof response['wplc_email'] !== "undefined") { data['wplc_email'] = response['wplc_email']; }
  if (typeof response['cid'] !== "undefined") {
    data['cid'] = response['cid'];
    Cookies.set('wplc_cid', response['cid'], { expires: 1, path: '/' });
  }
  if (typeof response['aname'] !== "undefined") { wplc_agent_name = response['aname']; }
  if (typeof response['cid'] !== "undefined" && wplc_cid !== jQuery.trim(response['cid'])) {
    wplc_cid = jQuery.trim(response['cid']);
    jQuery("#wplc_cid").val(wplc_cid);
  }
  if (typeof response['status'] !== "undefined" && parseInt(wplc_chat_status) !== parseInt(response['status'])) {
    wplc_chat_status = response['status'];
    Cookies.set('wplc_chat_status', null, { path: '/' });
    Cookies.set('wplc_chat_status', wplc_chat_status, { expires: 1, path: '/' });
  }

  /* Trigger for handling responses */
  jQuery.event.trigger({ type: "wplc_user_chat_loop", response: response });

  /* Process status changes */
  if (data['status'] == response['status']) {
    if (data['status'] == 5 && wplc_init_chat_box_check === true && wplc_init_chat_box !== false) { // open chat box on load
      wplc_init_chat_box(data['cid'], data['status']);
    }
    if ((response['status'] == 3 || response['status'] == 2) && response['data'] != null) { // if active and data is returned
      wplc_run = true;
      var wplc_new_message_sound = false;
      if (typeof response['data'] === "object") {
        for (var index in response['data']) {
          if (typeof response['data'][index] !== "object") {
            if (typeof msg_history[index] === "undefined") {
              //Not from node
              /* we dont have this message */
              msg_history[index] = true;
              jQuery("#wplc_chatbox").append(wplcFormatParser(response['data'][index]));
              wplc_new_message_sound = true;
            } else {
              /* we already have this message */
            }
          } else {
            var the_message = response['data'][index];
            the_message.mid = index;
            wplc_push_message_to_chatbox(the_message, 'u', function() { wplc_scroll_to_bottom(); });
            wplc_new_message_sound = true;
          }
        }
      } else {
        /* backwards compatibility - response['data'] is a string */
        jQuery("#wplc_chatbox").append(wplcFormatParser(response['data']));
        wplc_new_message_sound = true;
      }

      if (wplc_new_message_sound) {
        wplc_scroll_to_bottom();
        if (!!wplc_enable_ding.value) {
          new Audio(wplc_plugin_url + 'includes/sounds/general/ding.mp3').play();
        }
      }
    }
  } else { // data['status'] != response['status']
    data['status'] = wplc_chat_status;
    Cookies.set('wplc_chat_status', wplc_chat_status, { expires: 1, path: '/' });
    if (response['status'] == 0 || response['status'] == 12) { // no answer from admin
      jQuery("#wp-live-chat-3").hide();
      if (typeof response['data'] !== "undefined") {
        if (!!response['preformatted']) {
          jQuery("#wplc_chatbox").append(response['data'] + '<hr/>');
        } else {
          jQuery("#wplc_chatbox").append(wplc_safe_html(response['data']) + '<hr/>');
        }
      }
    } else if (response['status'] == 8) { // chat has been ended by admin
      wplc_run = false;
      document.getElementById('wplc_chatmsg').disabled = true;
      wplc_shown_welcome = false;
      the_message = wplc_generate_system_notification_object(wplc_error_messages.chat_ended_by_operator, {}, 0);
      wplc_push_message_to_chatbox(the_message, 'u', function() { wplc_scroll_to_bottom(); });
      jQuery.event.trigger({ type: "wplc_end_chat" });
    } else if (parseInt(response['status']) == 11) { /* use moved on to another page (perhaps in another tab so close this instance */
      jQuery("#wp-live-chat").css({ "display": "none" });
      wplc_run = false;
    } else if (parseInt(response['status']) == 3 || parseInt(response['status']) == 2 || parseInt(response['status']) == 10) { // re-initialize chat
      wplc_run = true;
      if (parseInt(response['status']) == 3) { // only if not minimized open aswell
        /* HERE NODE */
        if (config.wplc_use_node_server) {
          /* do not run this if using not the node */
          if (typeof wplc_transport_prepared !== "undefined" && wplc_transport_prepared === false) {
            wplc_server.prepareTransport(function() { wplc_call_to_server_chat(wplc_server_last_loop_data, false, false); }, wplc_user_message_receiver, wplc_user_retry_handler, wplc_log_connection_error);
          }
        }
        if (!wplc_is_minimized) {
          if (!wplc_is_chat_open) {
            wplc_cbox_animation();
            setTimeout(function() { open_chat(0); }, 1500);
          }
        }
        if (jQuery('#wp-live-chat').hasClass('wplc_left') === true || jQuery('#wp-live-chat').hasClass('wplc_right') === true) {
          //jQuery('#wp-live-chat').height("400px");
        }
      }
      if (parseInt(response['status']) == 10) { // only if not minimized open aswell
        wplc_run = true;
        open_chat(0);
      }
      if (response['data'] != null) { // append messages to chat area
        if (typeof response['data'] === "object") {
          for (var index in response['data']) {
            wplc_new_message_sound = false;
            if (typeof response['data'][index] !== "object") {
              if (typeof msg_history[index] === "undefined") {
                /* we dont have this message */
                msg_history[index] = true;
                jQuery("#wplc_chatbox").append(wplcFormatParser(response['data'][index]));
                wplc_new_message_sound = true;
              } else {
                /* we already have this message */
              }
            } else {
              var the_message = response['data'][index];
              the_message.mid = index;
              wplc_push_message_to_chatbox(the_message, 'u', function() { wplc_scroll_to_bottom(); });
            }
            if (wplc_new_message_sound) {
              if (response['alert']) {
                jQuery('#wplc-chat-alert').addClass('is-active');
              }
              wplc_scroll_to_bottom();
              if (!!wplc_enable_ding.value) {
                new Audio(wplc_plugin_url + 'includes/sounds/general/ding.mp3').play();
              }
            }
          }
        } else {
          /* backwards compatibility - response['data'] is a string */
          jQuery("#wplc_chatbox").append(wplcFormatParser(response['data']));
        }
        wplc_scroll_to_bottom();
      }
    }
  }
}



function wplc_log_connection_error(error) {
  if (window.console) { console.log(error); }

  jQuery("#wplc_chatbox").append("<small>" + error + "</small><br>");
  wplc_scroll_to_bottom();
}

function wplc_display_error(error) {

  the_message = {};
  the_message.originates = 2;
  the_message.msg = error;
  the_message.other = {};
  var wplc_d = new Date();
  the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
  wplc_push_message_to_chatbox(the_message, 'u', function() {
    wplc_scroll_to_bottom();
  });



}

var wplc_init_chat_box = function(cid, status) {
  if (wplc_chat_status == 9 && wplc_check_hide_cookie == "yes") {} else if (wplc_chat_status === 3) {
    wplc_cbox_animation();
  } else {

    if (wplc_check_hide_cookie != "yes") {
      wplc_dc = setTimeout(function() {
        wplc_cbox_animation();
      }, parseInt(window.wplc_misc_strings.wplc_delay));
    }
  }
  wplc_init_chat_box = false;
  jQuery.event.trigger({ type: "wplc_init_complete" });
}

function wplc_cbox_animation() {
  /*
   * 1- Slide Up
   * 2- Slide Across (Left/Right)
   * 3- Slide Down
   * 4- Fade In
   */


  var wplc_window_id = jQuery("#wp-live-chat");

  var wplc_theme_chosen = jQuery(wplc_window_id).attr('wplc_animation');

  switch (wplc_theme_chosen) {
    case 'none':
      jQuery(wplc_window_id).css('display', 'block');
      break;
    case 'animation-1':
      // Slide Up
      jQuery(wplc_window_id).animate({ 'marginBottom': '0px' }, 1000);
      break;
    case 'animation-2-bl':
      // Slide Accross from left
      jQuery(wplc_window_id).animate({ 'left': '20px' }, 1000);
      break;
    case 'animation-2-br':
      // Slide Accross from right
      jQuery(wplc_window_id).animate({ 'right': '20px' }, 1000);
      break;
    case 'animation-2-l':
      // Slide Accross from left
      jQuery(wplc_window_id).animate({ "left": '0px' }, 1000);
      break;
    case 'animation-2-r':

      // Slide Accross from right
      jQuery(wplc_window_id).animate({ 'right': '0px' }, 1000);
      break;
    case 'animation-3':
      // Fade In
      jQuery(wplc_window_id).fadeIn('slow');
    case 'animation-4':
      jQuery(wplc_window_id).css('display', 'block');
      break;
    default:
      jQuery(wplc_window_id).css('display', 'block');
      break;
  }

  //jQuery("#wp-live-chat").css({ "display" : "block" });
  var wplc_auto_popup = parseInt(jQuery("#wp-live-chat").attr('wplc-auto-pop-up'));
  if (wplc_auto_popup > 0) {
    var wplc_force_must_min = Cookies.get('wplc_minimize');
    if (wplc_force_must_min === 'yes') {
      /* User has actively chosen to minimize the chat, leave it alone */
    } else {
      wplc_auto_popup_do(wplc_auto_popup);
    }
  }

  jQuery.event.trigger({ type: "wplc_animation_done" });
}


function wplc_sound(source, volume, loop) {
  this.source = source;
  this.volume = volume;
  this.loop = loop;
  var son;
  this.son = son;
  this.finish = false;
  this.stop = function() {
    document.body.removeChild(this.son);
  }
  this.start = function() {
    if (this.finish) return false;
    this.son = document.createElement("embed");
    this.son.setAttribute("src", this.source);
    this.son.setAttribute("hidden", "true");
    this.son.setAttribute("volume", this.volume);
    this.son.setAttribute("autostart", "true");
    this.son.setAttribute("loop", this.loop);
    document.body.appendChild(this.son);
  }
  this.remove = function() {
    document.body.removeChild(this.son);
    this.finish = true;
  }
  this.init = function(volume, loop) {
    this.finish = false;
    this.volume = volume;
    this.loop = loop;
  }
}

(function($) {

  $(function(event) {

    if (!window.wdtEmojiBundle)
      return;

    $(document.body).on("click", function(event) {

      // If click event isn't on the emoji window, or the emoji open button, close the emoji window
      if ($(event.target).closest(".wdt-emoji-picker, .wdt-emoji-popup").length == 0 && !(
          event.target.parentNode == null && $(event.target).hasClass("fa-smile")
        ))
        wdtEmojiBundle.close();

    });

    // Close emoji window on scroll
    $(window).scroll(function(event) {
      wdtEmojiBundle.close();
    });
  });

})(jQuery);