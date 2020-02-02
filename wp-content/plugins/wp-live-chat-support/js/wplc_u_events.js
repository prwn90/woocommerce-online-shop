jQuery(function() {

  //placeholder text fix for IE
  jQuery('#wp-live-chat [placeholder]').focus(function() {
    var input = jQuery(this);
    if (input.val() == input.attr('placeholder')) {
      input.val('');
      input.removeClass('placeholder');
    }
  }).blur(function() {
    var input = jQuery(this);
    if (input.val() == '' || input.val() == input.attr('placeholder')) {
      input.addClass('placeholder');
      input.val(input.attr('placeholder'));
    }
  }).blur();

  /* minimize chat window */
  jQuery("body").on("click", "#wp-live-chat-minimize", function() {
    jQuery.event.trigger({
      type: "wplc_minimize_chat"
    });
  });

  /* close chat window */
  jQuery("body").on("click", "#wp-live-chat-close", function() {

    jQuery("#wp-live-chat").hide();
    jQuery("#wp-live-chat-1").hide();
    jQuery("#wp-live-chat-2").hide();
    jQuery("#wp-live-chat-3").hide();
    jQuery("#wp-live-chat-4").hide();
    jQuery("#wplc_social_holder").hide();
    jQuery("#nifty_ratings_holder").hide();
    jQuery("#wp-live-chat-react").hide();
    jQuery("#wp-live-chat-minimize").hide();
    var wplc_expire_date = new Date();
    var minutes = 2;
    wplc_expire_date.setTime(wplc_expire_date.getTime() + (minutes * 60 * 1000));
    Cookies.set('wplc_hide', "yes", {
      expires: wplc_expire_date,
      path: '/'
    });
    var data = {
      action: 'wplc_user_close_chat',
      security: wplc_nonce,
      cid: wplc_cid,
      status: wplc_chat_status
    };
    jQuery.post(wplc_ajaxurl, data, function(response) {

    });
  });

  //allows for a class to open chat window now
  jQuery("body").on("click", ".wp-live-chat-now", function() {
    open_chat(0);
  });

  jQuery(document).on("wplc_minimize_chat", function() {
    wplc_is_minimized = true;

    Cookies.set('wplc_minimize', "yes", {
      expires: 1,
      path: '/'
    });
    wplc_chat_status = Cookies.get('wplc_chat_status');

    if (typeof wplc_start_chat_pro_custom_fields_filter !== "undefined" && typeof wplc_start_chat_pro_custom_fields_filter === "function") {
      wplc_extra_data = wplc_start_chat_pro_custom_fields_filter(wplc_extra_data);
    }

    if (wplc_chat_status != 5 && wplc_chat_status != 10 && wplc_chat_status != 9 && wplc_chat_status != 8) {
      if (wplc_online) {
        var data = {
          action: 'wplc_user_minimize_chat',
          security: wplc_nonce,
          cid: wplc_cid,
          wplc_extra_data: wplc_extra_data
        };

        jQuery.post(wplc_ajaxurl, data, function(response) {

        });
      }
    }

    if (typeof wplc_enable_ga !== "undefined" && wplc_enable_ga === '1' && wplc_online) {
      if (typeof ga !== "undefined") {
        ga('send', {
          hitType: 'event',
          eventCategory: 'WP_Live_Chat_Support',
          eventAction: 'Event',
          eventLabel: 'Minimize Chat'
        });
      }
    }
  });
  jQuery(document).on("wplc_start_chat", function() {
    if (typeof wplc_enable_ga !== "undefined" && wplc_enable_ga === '1') {
      if (typeof ga !== "undefined") {
        ga('send', {
          hitType: 'event',
          eventCategory: 'WP_Live_Chat_Support',
          eventAction: 'Event',
          eventLabel: 'Start Chat'
        });
      }
    }
  });
  jQuery(document).on("wplc_open_chat_1", function() {
    if (typeof wplc_enable_ga !== "undefined" && wplc_enable_ga === '1') {
      if (typeof ga !== "undefined") {
        ga('send', {
          hitType: 'event',
          eventCategory: 'WP_Live_Chat_Support',
          eventAction: 'Event',
          eventLabel: 'Start Chat - Step 1'
        });
      }
    }
  });
  jQuery(document).on("wplc_open_chat_2", function() {
    if (typeof wplc_enable_ga !== "undefined" && wplc_enable_ga === '1') {
      if (typeof ga !== "undefined") {
        ga('send', {
          hitType: 'event',
          eventCategory: 'WP_Live_Chat_Support',
          eventAction: 'Event',
          eventLabel: 'Start Chat - Step 2'
        });
      }
    }
  });

  jQuery(document).on("wplc_agent_joined", function(e) {
    var temail = '';
    var tname = '';
    var taid = '';
    var ta_tagline = '';

    if (typeof e.ndata.other.email !== "undefined") {
      temail = e.ndata.other.email;
    }
    if (typeof e.ndata.other.name !== "undefined") {
      tname = e.ndata.other.name;
    }
    if (typeof e.ndata.other.aid !== "undefined") {
      taid = e.ndata.other.aid;
    }
    if (typeof e.ndata.other.agent_tagline !== "undefined") {
      ta_tagline = e.ndata.other.agent_tagline;
    }
    wplc_current_agent = e.ndata.other;

    jQuery(".wplc_no_answer").remove();

    jQuery(".admin_chat_name").html(tname);
    wplc_node_pair_name = tname;
    wplc_agent_name = tname;
  });

  jQuery("body").on("click", "#wplc_start_chat_btn", function() {
    var wplc_is_gdpr_enabled = jQuery(this).attr('data-wplc-gdpr-enabled');
    if (typeof wplc_is_gdpr_enabled !== "undefined" && (wplc_is_gdpr_enabled === 'true')) {
      var wplc_gdpr_opt_in_checked = jQuery("#wplc_chat_gdpr_opt_in").is(':checked');
      if (typeof wplc_gdpr_opt_in_checked === "undefined" || wplc_gdpr_opt_in_checked === false) {
        /* GDPR requirements not met */
        jQuery("#wplc_chat_gdpr_opt_in").addClass('incomplete');
        return false;
      }
      jQuery("#wplc_chat_gdpr_opt_in").removeClass('incomplete');
    }

    var wplc_name = jQuery("#wplc_name").val().replace(/(<([^>]+)>)/ig, "");
    var wplc_email = jQuery("#wplc_email").val().replace(/(<([^>]+)>)/ig, "");

    if (wplc_name.length <= 0) {
      alert(wplc_error_messages.please_enter_name);
      return false;
    }
    if (wplc_email.length <= 0) {
      alert(wplc_error_messages.please_enter_email);
      return false;
    }

    if (jQuery("#wplc_email").attr('wplc_hide') !== "1") {
      var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,12}$/i;

      //var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
      if (!testEmail.test(wplc_email)) {
        alert(wplc_error_messages.please_enter_valid_email);
        return false;
      }
    }
    document.getElementById('wplc_chatmsg').disabled = false;

    /* start the long polling */
    wplc_run = true;

    wplc_send_welcome_message();
    wplc_scroll_to_bottom();

    jQuery.event.trigger({
      type: "wplc_start_chat"
    });

    var date = new Date();
    date.setTime(date.getTime() + (2 * 60 * 1000));

    wplc_cid = Cookies.get('wplc_cid');

    if (typeof wplc_start_chat_pro_custom_fields_filter !== "undefined" && typeof wplc_start_chat_pro_custom_fields_filter === "function") {
      wplc_extra_data = wplc_start_chat_pro_custom_fields_filter(wplc_extra_data);
    }

    if (typeof wplc_cid !== "undefined" && wplc_cid !== null) {
      /* we've already recorded a cookie for this person */
      var data = {
        action: 'wplc_start_chat',
        security: wplc_nonce,
        name: wplc_name,
        email: wplc_email,
        cid: wplc_cid,
        wplcsession: wplc_session_variable,
        wplc_extra_data: wplc_extra_data
      };

      if (typeof wplc_start_chat_pro_data !== "undefined" && typeof wplc_start_chat_pro_data === "function") {
        data = wplc_start_chat_pro_data(data);
      }
    } else { // no cookie recorded yet for this visitor
      var data = {
        action: 'wplc_start_chat',
        security: wplc_nonce,
        name: wplc_name,
        email: wplc_email,
        wplcsession: wplc_session_variable,
        wplc_extra_data: wplc_extra_data
      };

      if (typeof wplc_start_chat_pro_data !== "undefined" && typeof wplc_start_chat_pro_data === "function") {
        data = wplc_start_chat_pro_data(data);
      }
    }

    Cookies.set('wplc_name', wplc_name, {
      path: '/'
    });
    Cookies.set('wplc_email', wplc_email, {
      path: '/'
    });

    wplc_server.send(wplc_ajaxurl, data, "POST", 120000,
      function(response) {
        wplc_chat_status = 2;
        Cookies.set('wplc_chat_status', 2, {
          expires: date,
          path: '/'
        });
        wplc_cid = jQuery.trim(response);

        //All sorted, let's check for message transport mode
        wplc_server.prepareTransport(function() {
          //Transport ready...
          wplc_server_last_loop_data.status = 2; //Set to waiting
          if (wplc_filter_run_override.value || wplc_online === false) {} else {
            wplc_call_to_server_chat(wplc_server_last_loop_data);
          }
        }, wplc_user_message_receiver, wplc_user_retry_handler, wplc_log_connection_error);
      },
      function() {
        //Fails
      },
      function(response, wplc_send_data) {
        //Complete
        if (typeof wplc_send_data !== "undefined" && typeof wplc_send_data['action'] !== "undefined" && wplc_send_data['action'] == 'wplc_start_chat') {
          /* we got here because the short poll  (when disabling the initiate chat feature) comes back on the "complete" callback. This check makes sure we restart the longpoll */
          wplc_chat_status = 2;
          Cookies.set('wplc_chat_status', 2, {
            expires: date,
            path: '/'
          });
          wplc_cid = jQuery.trim(response);

          //All sorted, let's check for message transport mode
          wplc_server.prepareTransport(function() {
            //Transport ready...
            wplc_server_last_loop_data.status = 2; //Set to waiting
            if (wplc_filter_run_override.value || wplc_online === false) {} else {
              wplc_call_to_server_chat(wplc_server_last_loop_data);
            }
          }, wplc_user_message_receiver, wplc_user_retry_handler, wplc_log_connection_error);
        }

      }
    );
  });

  jQuery("body").on("keyup", "#wplc_chatmsg", function(event) {
    if (event.keyCode === 13 && jQuery.trim(document.getElementById('wplc_chatmsg').value) != '') {
      jQuery("#wplc_send_msg").trigger("click");
    }
  });

  jQuery("body").on("click", "#wplc_send_msg", function() {
    var wplc_cid = jQuery("#wplc_cid").val();
    if (wplc_cid.length < 1) {
      /* failover for wplc_cid */
      var wplc_cid = Cookies.get('wplc_cid');
    }
    var wplc_chat = document.getElementById('wplc_chatmsg').value;

    if (wplc_chat !== "") {
      var wplc_name = jQuery("#wplc_name").val().replace(/(<([^>]+)>)/ig, "");
      if (typeof wplc_name == "undefined" || wplc_name == null || wplc_name == "") {
        wplc_name = Cookies.get('wplc_name');
      }
      if (typeof wplc_name == "undefined") {
        wplc_name = config.wplc_user_default_visitor_name;
      }

      var wplc_email = jQuery("#wplc_email").val().replace(/(<([^>]+)>)/ig, "");
      if (typeof wplc_email == "undefined" || wplc_email == null || wplc_email == "") {
        wplc_email = Cookies.get('wplc_email');
      }
      if (typeof wplc_email == "undefined") {
        wplc_email = '';
      }
      var wplc_chat_parsed = wplc_chat;

      the_message = {};
      the_message.originates = 2;
      the_message.msg = wplc_chat_parsed;
      the_message.other = {};
      var wplc_d = new Date();
      the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
      wplc_push_message_to_chatbox(the_message, 'u', function() {
        wplc_scroll_to_bottom();
      });

      wplc_scroll_to_bottom();

      var data = {
        action: 'wplc_user_send_msg',
        security: wplc_nonce,
        cid: wplc_cid,
        msg: wplc_chat_parsed,
        wplc_extra_data: wplc_extra_data
      };

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

      jQuery.event.trigger({
        type: "wplc_update_gdpr_last_chat_id"
      });

      if (typeof wplc_enable_ga !== "undefined" && wplc_enable_ga === '1') {
        if (typeof ga !== "undefined") {
          ga('send', {
            hitType: 'event',
            eventCategory: 'WP_Live_Chat_Support',
            eventAction: 'Event',
            eventLabel: 'User Send Message'
          });
        }
      }
    }

    jQuery("#wplc_chatmsg").val('');

  });

  jQuery(document).on("wplc_open_chat", function(event) {
    /* what is the current status? */
    wplc_chat_status = Cookies.get('wplc_chat_status');
    if (typeof wplc_chat_status === 'undefined') {
      Cookies.set('wplc_chat_status', 5, {
        expires: 1,
        path: '/'
      });
    }
    var wplc_tmp_checker = wplc_pre_open_check_status(status, function() {
      open_chat();
    });
  });

  jQuery(document).on("wplc_end_chat", function() {
    /* Clear Cookies */
    Cookies.remove('wplc_chat_status');
    Cookies.remove('wplc_cid');
    Cookies.remove('nc_status');
    //Cookies.remove('wplc_name');
    //Cookies.remove('wplc_email');

    /* Close ports if applicable*/
    wplc_server.forceClosePort();

    /* Check if we should redirect */
    if (typeof wplc_redirect_thank_you !== "undefined" && wplc_redirect_thank_you !== null && wplc_redirect_thank_you !== "") {
      window.location = wplc_redirect_thank_you;
    }

    if (jQuery('#wplc_gdpr_end_chat_notice_container').length > 0) {
      jQuery("#wplc_gdpr_end_chat_notice_container").fadeIn('fast');
    }
  });

  /** End Chat from User Side */
  jQuery(document).on("wplc_end_chat_as_user", function(e) {
    jQuery.event.trigger({
      type: "wplc_end_chat"
    });
  });

  function wplc_pre_open_check_status(status, callback) {
    if (typeof wplc_chat_status !== 'undefined' && (typeof wplc_chat_status.length !== 'undefined' && wplc_chat_status.length > 0)) {
      if (parseInt(wplc_chat_status) === 10 || parseInt(wplc_chat_status) === 7) {
        /* it was minimized or timedout, now we need to open it - set status to 3 (back to open chat) */
        Cookies.set('wplc_chat_status', 3, {
          expires: 1,
          path: '/'
        });

      }
      if (parseInt(wplc_chat_status) === 0 || parseInt(wplc_chat_status) === 12) {
        /* no answer from agent previously */
        // Cookies.set('wplc_chat_status', 5, { expires: 1, path: '/' });                    
      }
      if (parseInt(wplc_chat_status) === 8) {
        /* no answer from agent previously */
        cnonsole.log("now setting it to 5");
        Cookies.set('wplc_chat_status', 5, {
          expires: 1,
          path: '/'
        });
      }

    }
    callback();
  }

  if (typeof wplc_elem_trigger != 'undefined') {
    if (wplc_elem_trigger.id != "") {
      try {
        var wplc_click_or_hover = parseInt(wplc_elem_trigger.action);
        var wplc_class_or_id = parseInt(wplc_elem_trigger.type);
        jQuery((wplc_class_or_id == 1 ? "#" : ".") + wplc_elem_trigger.id).on((wplc_click_or_hover == 1 ? "mouseenter" : "click"), function() {
          open_chat(0);
        });
      } catch (e) {
        console.log("WPLC Error: \"" + (wplc_class_or_id == 1 ? "#" : ".") + wplc_elem_trigger.id + "\" is not a valid selector");
      }
    }
  }


});

//open chat window function

function open_chat(force) {
  jQuery.event.trigger({
    type: "wplc_open_chat_1"
  });

  wplc_chat_status = Cookies.get('wplc_chat_status');

  /**
   *  double check we have a cookie. If not, set to 5 so that the chat box can atleast restart
   */
  if (wplc_chat_status == null || wplc_chat_status == 'null') {
    Cookies.set('wplc_chat_status', 5, {
      expires: 1,
      path: '/'
    });
    wplc_chat_status = 5;
  }

  if (parseInt(wplc_chat_status) == 3 || parseInt(wplc_chat_status) == 2 || parseInt(wplc_chat_status) == 0 || parseInt(wplc_chat_status) == 12) {

    jQuery.event.trigger({
      type: "wplc_open_chat_2",
      wplc_online: wplc_online
    });

    Cookies.set('wplc_had_chat', true, {
      path: '/'
    });

    wplc_send_welcome_message();

    if (parseInt(wplc_chat_status) == 0 || parseInt(wplc_chat_status) == 11 || parseInt(wplc_chat_status) == 12) {
      /* user was a missed chat, now lets change them back to "pending" */
      wplc_chat_status = 2;
    }
    if (typeof wplc_start_chat_pro_custom_fields_filter !== "undefined" && typeof wplc_start_chat_pro_custom_fields_filter === "function") {
      wplc_extra_data = wplc_start_chat_pro_custom_fields_filter(wplc_extra_data);
    }

    if (wplc_online) {
      var data = {
        action: 'wplc_user_maximize_chat',
        security: wplc_nonce,
        cid: wplc_cid,
        chat_status: parseInt(wplc_chat_status),
        wplc_extra_data: wplc_extra_data
      };
      jQuery.post(wplc_ajaxurl, data, function(response) {

        //log("user maximized chat success");
      });
    }

  } else if (parseInt(wplc_chat_status) == 10) {
    jQuery("#wp-live-chat-minimize").trigger("click");

  } else if (wplc_chat_status == 5 || wplc_chat_status == 9 || wplc_chat_status == 8) {
    if (jQuery("#wp-live-chat-2").is(":visible") === false && jQuery("#wp-live-chat-4").is(":visible") === false) {
      jQuery("#wp-live-chat-2").show();
      jQuery("#wp-live-chat-2-inner").show();
      var wplc_visitor_name = Cookies.get('wplc_name');
      if (Cookies.get('wplc_email') !== "no email set" && typeof wplc_visitor_name !== "undefined") {
        jQuery("#wplc_name").val(Cookies.get('wplc_name'));
        jQuery("#wplc_email").val(Cookies.get('wplc_email'));
      }
      jQuery("#wp-live-chat-header").addClass("active");
    }
  }
  /*else if (wplc_chat_status == 2){
      jQuery("#wp-live-chat-3").show();
  } */
  else if (wplc_chat_status == 1) {
    jQuery("#wp-live-chat-4").show();
    jQuery("#wplc_social_holder").show();
    jQuery("#nifty_ratings_holder").show();
    jQuery.event.trigger({
      type: "wplc_animation_done"
    });
    jQuery("#wplc_chatbox").append(wplc_error_messages.chat_ended_by_operator + "<br />");
    wplc_scroll_to_bottom();
    jQuery("#wp-live-chat-minimize").hide();
    document.getElementById('wplc_chatmsg').disabled = true;
  }

  wplc_is_chat_open = true;

  if (!jQuery("#wp-live-chat-header").hasClass("active")) {
    jQuery("#wp-live-chat-header").click();
  }

}