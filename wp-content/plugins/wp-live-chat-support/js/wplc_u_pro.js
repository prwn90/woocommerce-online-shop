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
 * 
 */

jQuery(function() {

  var wplc_session_variable = new Date().getTime();
  var wplc_cid;
  var wplc_check_hide_cookie;
  var wplc_chat_status = "";
  var wplc_cookie_name = "";
  var wplc_cookie_email = "";
  var wplc_init_chat_box_check = true;
  var wplc_cid = null;
  var wplc_domain = null;

  var initial_data;

  var wplc_run = true;
  var wplc_had_error = false;
  var wplc_longpolltimer;
  window.wplc_long_poll_active = true;
  var wplc_fist_run = true;

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

  var data = {
    action: 'wplc_get_chat_box',
    security: wplc_nonce
  };
  jQuery.ajax({
    url: wplc_ajaxurl_own_site,
    data: data,
    type: "POST",
    success: function(response) {
      /* inject html */
      if (response) {
        response = JSON.parse(response);
        jQuery("body").append(response);

        wplc_domain = jQuery("#wplc_domain").val();

        var data = {
          action: 'wplc_call_to_server_visitor',
          security: wplc_nonce,
          cid: wplc_cid,
          wplc_name: wplc_cookie_name,
          wplc_email: wplc_cookie_email,
          status: wplc_chat_status,
          wplcsession: wplc_session_variable,
          first_run: 1,
          domain: wplc_domain,
          api: wplc_api
        };
        // ajax long polling function
        wplc_call_to_server_chat(data, true, false);

        if (wplc_cid !== null && wplc_init_chat_box_check == true) {
          wplc_auto_popup_do(parseInt(jQuery("#wp-live-chat").attr('wplc-auto-pop-up')));
        }
      }
    }
  });


  function wplc_call_to_server_chat(data, first_run, short_poll) {
    var wplc_had_error = false;

    clearTimeout(wplc_longpolltimer);

    data['short_poll'] = short_poll;

    if (window.wplc_long_poll_active === true || short_poll) {
      jQuery.ajax({
        url: wplc_ajaxurl,
        data: data,
        type: "POST",
        success: function(response) {

          if (response) {
            response = JSON.parse(jQuery.trim(response));
            // set vars and cookies
            data['wplc_name'] = response['wplc_name'];
            data['wplc_email'] = response['wplc_email'];

            data['wplc_domain'] = response['wplc_domain'];

            data['action_2'] = "";
            data['cid'] = response['cid'];
            Cookies.set('wplc_cid', response['cid'], { expires: 1, path: '/' });
            Cookies.set('wplc_name', response['wplc_name'], { expires: 1, path: '/' });
            Cookies.set('wplc_email', response['wplc_email'], { expires: 1, path: '/' });
            wplc_cid = jQuery.trim(response['cid']);
            wplc_chat_status = response['status'];
            Cookies.set('wplc_chat_status', null, { path: '/' });
            Cookies.set('wplc_chat_status', wplc_chat_status, { expires: 1, path: '/' });
            // handle response
            if (data['status'] == response['status']) {
              if (parseInt(data['status']) == 5 && wplc_init_chat_box_check == true) { // open chat box on load
                if (wplc_init_chat_box) { wplc_init_chat_box(data['cid'], data['status']); }
              }
              if (parseInt(response['status']) == 3 && response['data'] != null) { // if active and data is returned
                jQuery("#wplc_chatbox").append(response['data']);
                if (response['data']) {
                  var height = jQuery('#wplc_chatbox')[0].scrollHeight;
                  jQuery('#wplc_chatbox').scrollTop(height);
                  if (!!wplc_enable_ding.value) {
                    new Audio(wplc_plugin_url + 'wp-live-chat-support/includes/sounds/general/ding.mp3').play()
                  }

                }
              }
            } else {
              if (parseInt(data['status']) === 5 && parseInt(response['status']) === 3) {
                /* user originally had the chat window open, no need to wait for the delay to open the chat box */
                short_poll = false;
                open_chat();
              }

              data['status'] = wplc_chat_status;
              Cookies.set('wplc_chat_status', wplc_chat_status, { expires: 1, path: '/' });
              if (parseInt(response['status']) == 0) { // no answer from admin
                jQuery("#wp-live-chat-3").hide();
                jQuery("#wp-live-chat-react").show().empty().append("<center>" + response['data'] + "</center>");
              } else if (parseInt(response['status']) == 8) { // chat has been ended by admin
                var height = jQuery('#wplc_chatbox')[0].scrollHeight;
                jQuery('#wplc_chatbox').scrollTop(height);
                jQuery("#wp-live-chat-minimize").hide();
                document.getElementById('wplc_chatmsg').disabled = true;
                if (wplc_ce_active === 'yes' && typeof wplc_ce_enable_experience != 'undefined' && wplc_ce_enable_experience === 'yes') {

                  jQuery("#wp-live-chat-4").css('width', '280');
                  jQuery("#wp-live-chat-4").html("<center><p>Please rate the service you received.</p></center><div id='wplc_star_rating'></div>");
                  jQuery('#wplc_star_rating').raty({
                    path: wplc_ce_url,
                    size: 16,
                    click: function(score, evt) {
                      var data = {
                        action: 'wplc_submit_chat_experience_rating',
                        security: wplc_nonce,
                        rating: score,
                        cid: response['cid']
                      }
                      jQuery.ajax({
                        url: wplc_ajaxurl,
                        data: data,
                        type: "POST",
                        success: function(response) {
                          if (typeof wplc_ce_enable_additional_feedback != 'undefined') {
                            if (wplc_ce_enable_additional_feedback === 'yes') {
                              var wplc_ce_new_contents = "<div class='wplc-star-rating-comments'><div class='wplc-star-rating-text'><p>" + wplc_ce_feedback_text + "</p></div><div class='wplc-star-rating-textarea'><textarea id='wplc_ce_rating_text' ></textarea></div><div class='wplc-star-rating-send-buttom'><button type='button' class='btn btn-primary' id='wplc_ce_send_feedback' >" + wplc_ce_button_text + "</button</div></div>";
                            } else {
                              var wplc_ce_new_contents = "<div class='wplc-ce-thank-you'><p>" + wplc_ce_thank_you + "</p></div>";
                            }
                            jQuery("#wp-live-chat-4").html(wplc_ce_new_contents);
                          }
                        }
                      })
                    }
                  });
                } else {
                  if (typeof response['data'] !== 'undefined') {
                    jQuery("#wplc_chatbox").append("<em>" + response['data'] + "</em><br />");
                  }
                }
              } else if (parseInt(response['status']) == 11) { /* use moved on to another page (perhaps in another tab so close this instance */
                jQuery("#wp-live-chat").css({ "display": "none" });
                wplc_run = false;
              } else if (parseInt(response['status']) == 3 || response['status'] == 10) { // re-initialize chat
                short_poll = false;
                jQuery("#wplc_cid").val(wplc_cid);
                if (wplc_ce_active !== 'yes') {
                  document.getElementById('wplc_chatmsg').disabled = false;
                }
                if (response['status'] == 3) { // only if not minimized open aswell
                  open_chat();
                  if (jQuery('#wp-live-chat').hasClass('wplc_left') === true || jQuery('#wp-live-chat').hasClass('wplc_right') === true) {
                    jQuery('#wp-live-chat').height("400px");
                  }
                }
                if (response['data'] != null) { // append messages to chat area
                  jQuery("#wplc_chatbox").append(response['data']);
                  if (response['data']) {
                    var height = jQuery('#wplc_chatbox')[0].scrollHeight;
                    jQuery('#wplc_chatbox').scrollTop(height);
                  }
                }
              }
            }
            initial_data = data;
            if (first_run) {
              if (parseInt(response['status']) === 3 || parseInt(response['status']) === 10 || parseInt(response['status']) === 2) {
                /* long poll should initialize as they had a chat window open before */
                window.wplc_long_poll_active = true;
                initial_data['first_run'] = 0;
                wplc_longpolltimer = setTimeout(function() { wplc_call_to_server_chat(initial_data, false, false); }, 1500);
              } else {
                window.wplc_long_poll_active = false;
                initial_data['first_run'] = 0;
                wplc_longpolltimer = setTimeout(function() { wplc_call_to_server_chat(initial_data, false, true); }, 25000);
              }

            }
          }
        },
        error: function(jqXHR, exception) {
          if (jqXHR.status == 404) {
            if (window.console) { console.log('Requested page not found. [404]'); }
            wplc_run = false;
          } else if (jqXHR.status == 500) {
            if (window.console) { console.log('Internal Server Error [500].'); }
            wplc_run = true;
            wplc_had_error = true;
            wplc_longpolltimer = setTimeout(function() {
              wplc_call_to_server_chat(data, false, false);
            }, 10000);
          } else if (exception === 'parsererror') {
            if (window.console) { console.log('Requested JSON parse failed.'); }
            wplc_run = false;
          } else if (exception === 'abort') {
            if (window.console) { console.log('Ajax request aborted.'); }
            wplc_run = false;
          } else {
            if (window.console) { console.log('Uncaught Error.\n' + jqXHR.responseText); }
            wplc_run = true;
            wplc_had_error = true;

            wplc_longpolltimer = setTimeout(function() {
              wplc_call_to_server_chat(data, false, false);
            }, 10000);
          }
        },
        complete: function(response) {
          if (wplc_run && !wplc_had_error && first_run === false) {
            if (short_poll) {
              /* ping the server every 7.5 seconds to see if there is an initiate */
              wplc_longpolltimer = setTimeout(function() { wplc_call_to_server_chat(initial_data, false, true); }, 25000);
            } else {
              window.wplc_long_poll_active = true;
              wplc_longpolltimer = setTimeout(function() { wplc_call_to_server_chat(data, false, false); }, 3500);
            }

          }
        },
        timeout: 120000
      });
    } else {
      /* nothing here */
    }
  };

  function wplc_init_chat_box(cid, status) {
    if (wplc_chat_status == 9 && wplc_check_hide_cookie == "yes") {

    } else {
      if (wplc_check_hide_cookie != "yes") {
        wplc_dc = setTimeout(function() {
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
              jQuery(wplc_window_id).animate({ left: '100px' }, 1000);
              break;
            case 'animation-2-br':
              // Slide Accross from right
              jQuery(wplc_window_id).animate({ 'right': '100px' }, 1000);
              break;
            case 'animation-2-l':
              // Slide Accross from left
              jQuery(wplc_window_id).animate({ left: '0px' }, 1000);
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
        }, parseInt(window.wplc_misc_strings.wplc_delay));
      }
    }
    wplc_init_chat_box = false;
  }




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
    jQuery('#wp-live-chat').height("");
    if (jQuery("#wp-live-chat").attr("original_pos") === "bottom_right") {
      jQuery("#wp-live-chat").css("left", "");
      jQuery("#wp-live-chat").css("bottom", "0");
      jQuery("#wp-live-chat").css("right", "100px");
    } else if (jQuery("#wp-live-chat").attr("original_pos") === "bottom_left") {
      jQuery("#wp-live-chat").css("left", "100px");
      jQuery("#wp-live-chat").css("bottom", "0");
      jQuery("#wp-live-chat").css("right", "");
    } else if (jQuery("#wp-live-chat").attr("original_pos") === "left") {
      jQuery("#wp-live-chat").css("left", "0");
      jQuery("#wp-live-chat").css("bottom", "100px");
    } else if (jQuery("#wp-live-chat").attr("original_pos") === "right") {
      jQuery("#wp-live-chat").css("left", "");
      jQuery("#wp-live-chat").css("right", "0");
      jQuery("#wp-live-chat").css("bottom", "100px");
      jQuery("#wp-live-chat").css("width", "");
    }
    jQuery('#wp-live-chat').addClass("wplc_close");
    jQuery('#wp-live-chat').removeClass("wplc_open");
    //jQuery("#wp-live-chat").css(jQuery("#wp-live-chat").attr("original_pos"), "100px");
    jQuery("#wp-live-chat").css("top", "");
    wplc_chat_status = Cookies.get('wplc_chat_status');
    jQuery("#wp-live-chat-1").show();
    jQuery("#wp-live-chat-1").css('cursor', 'pointer');
    jQuery("#wp-live-chat-2").hide();
    jQuery("#wp-live-chat-3").hide();
    jQuery("#wp-live-chat-4").hide();
    jQuery("#wp-live-chat-react").hide();
    jQuery("#wp-live-chat-minimize").hide();
    Cookies.set('wplc_minimize', "yes", { expires: 1, path: '/' });
    if (wplc_chat_status != 5 && wplc_chat_status != 10 && wplc_chat_status != 9 && wplc_chat_status != 8) {
      var data = {
        action: 'wplc_user_minimize_chat',
        security: wplc_nonce,
        cid: wplc_cid
      };

      jQuery.post(wplc_ajaxurl, data, function(response) {

      });
    }

  });
  /* close chat window */
  jQuery("body").on("click", "#wp-live-chat-close", function() {
    var data = {
      action: 'wplc_user_close_chat',
      security: wplc_nonce,
      cid: wplc_cid,
      status: wplc_chat_status
    };
    jQuery.post(wplc_ajaxurl, data, function(response) {


    });

    if (wplc_ce_active === 'yes' && typeof wplc_ce_enable_experience_visitor != 'undefined' && wplc_ce_enable_experience_visitor === 'yes') {
      jQuery("#wp-live-chat-4").css('width', '280');
      jQuery("#wp-live-chat-4").html("<center><p>Please rate the service you received.</p></center><div id='wplc_star_rating'></div>");

      jQuery('#wplc_star_rating').raty({
        path: wplc_ce_url,
        size: 16,
        click: function(score, evt) {
          var data = {
            action: 'wplc_submit_chat_experience_rating',
            security: wplc_nonce,
            rating: score,
            cid: wplc_cid
          }
          jQuery.ajax({
            url: wplc_ajaxurl,
            data: data,
            type: "POST",
            success: function(response) {
              if (typeof wplc_ce_enable_additional_feedback != 'undefined') {
                if (wplc_ce_enable_additional_feedback === 'yes') {
                  var wplc_ce_new_contents = "<div class='wplc-star-rating-comments'><div class='wplc-star-rating-text'><p>" + wplc_ce_feedback_text + "</p></div><div class='wplc-star-rating-textarea'><textarea id='wplc_ce_rating_text' ></textarea></div><div class='wplc-star-rating-send-buttom'><button type='button' class='btn btn-primary' id='wplc_ce_send_feedback' >" + wplc_ce_button_text + "</button</div></div>";
                } else {
                  var wplc_ce_new_contents = "<div class='wplc-ce-thank-you'><p>" + wplc_ce_thank_you + "</p></div>";
                }
                jQuery("#wp-live-chat-4").html(wplc_ce_new_contents);
              }
            }
          });
        }
      });
    } else {
      jQuery("#wp-live-chat").hide();
      jQuery("#wp-live-chat-1").hide();
      jQuery("#wp-live-chat-2").hide();
      jQuery("#wp-live-chat-3").hide();
      jQuery("#wp-live-chat-4").hide();
      jQuery("#wp-live-chat-react").hide();
      jQuery("#wp-live-chat-image").hide();
      jQuery("#wp-live-chat-wraper").hide();
      jQuery("#wp-live-chat-minimize").hide();
      Cookies.set('wplc_hide', wplc_hide_chat, { expires: 1, path: '/' });
    }
  });
  //open chat window function

  function open_chat() {
    jQuery('#wp-live-chat').removeClass("wplc_close");
    jQuery('#wp-live-chat').addClass("wplc_open");
    //jQuery("#wp-live-chat-1").hide();
    jQuery("#wp-live-chat-react").hide();
    jQuery("#wp-live-chat-header").css('cursor', 'all-scroll');
    jQuery("#wp-live-chat-1").css('cursor', 'all-scroll');
    Cookies.set('wplc_hide', "", { expires: 1, path: '/' });
    jQuery("#wp-live-chat-minimize").show();
    jQuery("#wp-live-chat-close").show();
    jQuery(function() {
      jQuery("#wp-live-chat").draggable({
        handle: "#wp-live-chat-header",
        drag: function(event, ui) {
          jQuery(this).css("right", "");
          jQuery(this).css("bottom", "inherit");
        }
      });
    });

    wplc_chat_status = parseInt(Cookies.get('wplc_chat_status'));
    if (wplc_chat_status == 3 || wplc_chat_status == 10) {
      jQuery("#wp-live-chat").show();
      jQuery("#wp-live-chat-4").show();
      jQuery("#wplc_chatmsg").focus();
      jQuery("#wp-live-chat-2").hide();
      jQuery("#wp-live-chat-3").hide();
      Cookies.set('wplc_minimize', "", { expires: 1, path: '/' });

      var data = {
        action: 'wplc_user_maximize_chat',
        security: wplc_nonce,
        cid: wplc_cid
      };
      jQuery.post(wplc_ajaxurl, data, function(response) {
        //log("user maximized chat success");
      });
    } else if (wplc_chat_status == 5 || wplc_chat_status == 9 || wplc_chat_status == 8 || wplc_chat_status == 11) {

      //if(jQuery("#wp-live-chat-2").is(":visible") === false && jQuery("#wp-live-chat-4").is(":visible") === false){
      jQuery("#wp-live-chat-2").show();
      if (Cookies.get('wplc_email') !== "no email set") {
        //                        jQuery("#wplc_name").val(jQuery.cookie('wplc_name'));
        //                        jQuery("#wplc_email").val(jQuery.cookie('wplc_email'));
      }

      //}
    } else if (wplc_chat_status == 2) {
      jQuery("#wp-live-chat-3").show();
    } else if (wplc_chat_status == 1) {
      jQuery("#wp-live-chat-4").show();
      jQuery("#wplc_chatbox").append("The chat has been ended by the agent.<br />");
      var height = jQuery('#wplc_chatbox')[0].scrollHeight;
      jQuery('#wplc_chatbox').scrollTop(height);
      jQuery("#wp-live-chat-minimize").hide();
      document.getElementById('wplc_chatmsg').disabled = true;
    }

  }
  //opens chat when clicked on top bar
  jQuery("body").on("click", "#wp-live-chat-1", function() {
    open_chat();
  });
  //allows for a class to open chat window now
  jQuery("body").on("click", ".wp-live-chat-now", function() {
    open_chat();
  });

  jQuery("body").on("click", "#wplc_start_chat_btn", function() {
    window.wplc_long_poll_active = true;
    wplc_call_to_server_chat(initial_data, false, false);

    var wplc_name = jQuery("#wplc_name").val().replace(/(<([^>]+)>)/ig, "");
    var wplc_email = jQuery("#wplc_email").val().replace(/(<([^>]+)>)/ig, "");


    if (jQuery("#wplc_email").attr('wplc_hide') !== "1") {
      if (wplc_name.length <= 0) { alert("Please Enter Your Name"); return false; }
      if (wplc_email.length <= 0) { alert("Please enter Your Email Address"); return false; }
      var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
      if (!testEmail.test(wplc_email)) {
        alert("Please Enter a Valid Email Address");
        return false;
      }
    }

    jQuery("#wp-live-chat-2").hide();
    jQuery("#wp-live-chat-3").show();
    jQuery("#wp-live-chat-3").css('width', '280');

    var date = new Date();
    date.setTime(date.getTime() + (2 * 60 * 1000));

    wplc_cid = Cookies.get('wplc_cid');

    if (typeof wplc_cid !== "undefined" && wplc_cid !== null) { // we've already recorded a cookie for this person
      var data = {
        action: 'wplc_start_chat',
        security: wplc_nonce,
        name: wplc_name,
        email: wplc_email,
        cid: wplc_cid,
        domain: wplc_domain,
        api: wplc_api,
        wplcsession: wplc_session_variable

      };
    } else { // no cookie recorded yet for this visitor
      var data = {
        action: 'wplc_start_chat',
        security: wplc_nonce,
        name: wplc_name,
        email: wplc_email,
        domain: wplc_domain,
        api: wplc_api,
        wplcsession: wplc_session_variable
      };
    }
    //changed ajax url so wp_mail function will work and not stop plugin from alerting admin there is a pending chat            
    jQuery.post(wplc_2_ajax_url, data, function(response) {

      Cookies.set('wplc_chat_status', 2, { expires: date, path: '/' });
      Cookies.set('wplc_name', wplc_name, { path: '/' });
      Cookies.set('wplc_email', wplc_email, { path: '/' });
      jQuery("#wplc_name").val(wplc_name);
      jQuery("#wplc_email").val(wplc_email);

      wplc_cid = jQuery.trim(response);
    });
  });

  jQuery("body").on("click", "#wplc_na_msg_btn", function() {
    var wplc_name = jQuery("#wplc_name").val();
    var wplc_email = jQuery("#wplc_email").val();
    var wplc_msg = jQuery("#wplc_message").val();
    var wplc_domain = jQuery("#wplc_domain_offline").val();
    var ip_address = jQuery("#wplc_ip_address").val();

    if (wplc_name.length <= 0) { alert("Please Enter Your Name"); return false; }
    if (wplc_email.length <= 0) { alert("Please Enter Your Email Address"); return false; }
    var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
    if (!testEmail.test(wplc_email)) {
      alert("Please Enter a Valid Email Address");
      return false;
    }
    if (wplc_msg.length <= 0) { alert("Please Enter a Message"); return false; }
    // jQuery("#wp-live-chat-2-info").hide();
    jQuery("#wplc_message_div").html(wplc_offline_msg);

    wplc_cid = Cookies.get('wplc_cid');

    var data = {
      action: 'wplc_user_send_offline_message',
      security: wplc_nonce,
      cid: wplc_cid,
      name: wplc_name,
      email: wplc_email,
      msg: wplc_msg,
      ip: ip_address,
      domain: wplc_domain
    };

    jQuery.post(wplc_2_ajax_url, data, function(response) {
      jQuery("#wplc_message_div").html(wplc_offline_msg3);
    });
  });

  jQuery("body").on("keyup", "#wplc_chatmsg", function(event) {
    if (event.keyCode === 13 && jQuery.trim(document.getElementById('wplc_chatmsg').value) != '') {
      jQuery("#wplc_send_msg").trigger("click");
    }
  });

  jQuery("body").on("click", "#wplc_send_msg", function() {
    var wplc_cid = jQuery("#wplc_cid").val();
    var wplc_chat = document.getElementById('wplc_chatmsg').value;
    var wplc_name = jQuery("#wplc_name").val();
    if (typeof wplc_name == "undefined" || wplc_name == null || wplc_name == "" || wplc_name === "Name") {
      wplc_name = Cookies.get('wplc_name');
    }
    jQuery("#wplc_chatmsg").val('');

    jQuery("#wplc_chatbox").append("<span class='wplc-user-message'>" + wplc_gravatar_image + " <strong>" + wplc_name + "</strong>: " + wplc_chat + "</span><br /><div class='wplc-clear-float-message'></div>");

    var height = jQuery('#wplc_chatbox')[0].scrollHeight;
    jQuery('#wplc_chatbox').scrollTop(height);

    var data = {
      action: 'wplc_user_send_msg',
      security: wplc_nonce,
      cid: wplc_cid,
      msg: wplc_chat,
      domain: wplc_domain,
      api: wplc_api

    };
    jQuery.post(wplc_ajaxurl, data, function(response) {

    });

  });

  jQuery('body').on('click', '#wplc_ce_send_feedback', function() {
    var wplc_ce_rating_text = jQuery("#wplc_ce_rating_text").val();

    var data = {
      action: 'wplc_record_chat_experience_message',
      security: wplc_nonce,
      cid: wplc_cid,
      message: wplc_ce_rating_text
    };

    jQuery.post(wplc_ajaxurl, data, function(response) {
      var wplc_ce_new_contents = "<div class='wplc-ce-thank-you'><p>" + wplc_ce_thank_you + "</p></div>";
      jQuery("#wp-live-chat-4").css('width', '280');
      jQuery("#wp-live-chat-4").html(wplc_ce_new_contents);
    });

  });

});