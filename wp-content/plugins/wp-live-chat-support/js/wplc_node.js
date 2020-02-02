var nc_sid;
var nc_name;
var wplc_cid;
var socket;
var FADE_TIME = 150; // ms
var TYPING_TIMER_LENGTH = 1000; // ms
var username = config.wplc_user_default_visitor_name;
var connected = false;
var typing = false;
var lastTypingTime;
var nifty_is_chat_open = false;
var nifty_chat_status = "browsing";
var tcx_show_drag_zone = false;

/* helps us keep track of which messages made it to the server */
var tcx_msg_confirmations = {};

/**
 * Keep track of recent agents
 * @type {array}
 */
var tcx_recent_agents = undefined;
var tcx_recent_agents_data = undefined;

/**
 * Setup Query String, customer ID, and fingerprint
 */
var query_string = "";
var tcx_customerID = null;
var tcx_fingerprint = null;

var wplc_chat_delegates;
var wplc_connect;
var tcx_ping = new Audio(config.message_override);
var tcx_inactive = false;
var tcx_inactive_timeout;
var tcx_timeout_duration = 300000;
var tcxAverageResponseTime = undefined;

/**
 * An array to keep track of agent disconnects
 * @type {array}
 */
var agent_disc_timer = [];

/**
 * Set the default agent_joined variable
 * @type {array}
 */
var agent_joined = [];

/**
 * Used to identify the heartbeat timer
 */
var user_hearbeat;

/**
 * variable to check if the agent is online or not - this is set after the first run to the server
 * @type {Boolean}
 */
var wplc_online = false;

/**
 * Everytime the user clicks the minimize button this is set to true
 * @type {Boolean}
 */
var nifty_is_minimized = false;

/**
 * Used as a set up variables for the text editor
 */
var selectedIndexStart;
var selectedIndexEnd;
var checkSelection = true;

/**
 * Variable to help identfy if we are editing a message
 * @type {Boolean}
 */
var niftyIsEditing = false;

/**
 * Identify the last message sent, so when we press UP we can edit it
 */
var lastmessagesent;

/**
 * Sets default for identifying if the welcome message has been sent yet, or not
 * @type {Boolean}
 */
var nifty_welcome_message_sent = false;

var $inputMessage = '';
var $messages = '';
var tcx_first;
var tcx_current;

/* OS Holder */
var tcx_user_current_os = false; //When false the 'tcx_get_operating_system' function will run all actions to identify the OS

/**
 * Generate a unique ID for the visitor
 *
 * @return {string} guid
 */
function wplc_jsguid() {
  var nav = window.navigator;
  var screen = window.screen;
  var guid = nav.mimeTypes.length;
  guid += nav.userAgent.replace(/\D+/g, '');
  guid += nav.plugins.length;
  guid += screen.height || '';
  guid += screen.width || '';
  guid += screen.pixelDepth || '';
  return guid;
};

/**
 * Setup the socket query variable, which is appended to the socket connection whenever the soket connects to the node servers
 */
function wplc_set_up_query_string() {
  if (typeof wplc_guid !== "undefined") {
    query_string += "&guid=" + wplc_guid;
  }

  if (typeof tcx_user_ip_address !== "undefined") {
    query_string += "&user_ip=" + tcx_user_ip_address;
  }

  tcx_customerID = wplc_getCookie('tcx_customerID');
  if (typeof tcx_customerID !== "undefined" && tcx_customerID !== '' && tcx_customerID !== null) {
    query_string += "&customer_id=" + tcx_customerID;
  }

  tcx_fingerprint = wplc_jsguid();
  if (typeof tcx_fingerprint !== "undefined" && tcx_fingerprint !== '' && tcx_fingerprint !== null) {
    query_string += "&tcx_fingerprint=" + tcx_fingerprint;
  }

  if (typeof window !== "undefined" && typeof window.location !== "undefined" && typeof window.location.href !== "undefined") {
    query_string += "&referer=" + window.location.href;
  }

  query_string = wplc_query_cleanup(query_string);
}

jQuery(document).on('wplc_sockets_ready', function() {

  /**
   * Run Query setup function
   */
  wplc_set_up_query_string();

  wplc_powered_by();

  /**
   * Setup an inactive timer
   */
  tcx_inactive_timeout = setTimeout(function() {
    tcx_inactive = true;
  }, tcx_timeout_duration);

  /*Find nifty object and check if online */
  if (wplc_test_localStorage()) {

    var wplc_d = new Date();
    wplc_d.toUTCString();
    var cdatetime = Math.floor(wplc_d.getTime() / 1000);

    if (localStorage.getItem('tcx_first') === null) {
      localStorage.setItem('tcx_first', cdatetime);
      tcx_first = cdatetime;
    } else {
      tcx_first = localStorage.getItem('tcx_first');
    }

    localStorage.setItem('tcx_current', cdatetime);
    tcx_current = cdatetime;

  }

  if (typeof ns_obj === 'undefined') {
    //Nifty Chat Object not created yet
  } else {
    if (ns_obj.o === '1') {
      wplc_online = true;
    } else {
      wplc_run = false;
      wplc_online = false;
    }
  }

  wplc_check_minimize_cookie = Cookies.get('nifty_minimize');

  /**
   * Builds the socket delegates. This needs to be called everytime a connection is made (i.e. moving from a short poll to a long poll)
   */
  wplc_chat_delegates = function(keepalive) {
    nifty_chat_status_temp = nc_getCookie("nc_status");
    if (typeof nifty_chat_status_temp !== "undefined" && nifty_chat_status_temp === "active") {
      /* leave the cookie untouched as we are already in ACTIVE state and should continue in this state until changed. */
    } else {
      if (keepalive) {
        niftyUpdateStatusCookie("active");
      } else {
        niftyUpdateStatusCookie("browsing");
      }
    }

    nifty_username_temp = nc_getCookie("nc_username");
    if (typeof nifty_username_temp !== "undefined") {
      username = nifty_username_temp;
    }

    // Socket events
    socket.on('connect', function(data) {
      nc_add_user(socket, data);

      nifty_chat_status_temp = nc_getCookie("nc_status");
      if (typeof nifty_chat_status_temp !== "undefined" && nifty_chat_status_temp === "active") {
        if (typeof user_hearbeat === "undefined") {
          user_hearbeat = setInterval(function() {
            if (socket.connected)
              socket.emit('heartbeat');
          }, 5000);
        }
      }
      jQuery.event.trigger({
        type: 'tcx_socket_connected',
        status: nifty_chat_status_temp
      });

    });

    socket.on("force_disconnect", function(data) {

      socket.disconnect({
        test: 'test'
      });

      if (typeof user_heartbeat !== "undefined")
        clearInterval(user_hearbeat);
      user_heartbeat = undefined;
      /* reconnect this socket in 7 seconds to check for a forced chat on the agents end */
      setTimeout(function() {
        wplc_connect(false);
      }, 12000);
      /* its important that this number is less than the TTL of the variable in redis */
    });

    socket.on("blacklisted", function(data) {

      jQuery.event.trigger({
        type: "tcx_blacklisted",
        ndata: data
      });

    });

    socket.on("user blocked", function(data) {
      socket.disconnect({
        blocked: 'blocked'
      });
      CookieDate.setFullYear(CookieDate.getFullYear() + 1);
      Cookies.set('tcx_b', '1', {
        expires: CookieDate,
        path: '/'
      });
      jQuery("#wp-live-chat-4").remove();
      jQuery("#wp-live-chat-wraper").remove();
      keepalive = false;
    });

    socket.on("customerID", function(data) {
      var CookieDate = new Date;
      CookieDate.setFullYear(CookieDate.getFullYear() + 1);
      Cookies.set('tcx_customerID', data.customerID, {
        expires: CookieDate,
        path: '/'
      });

    });

    socket.on("agent initiate", function(data) {
      if (typeof user_hearbeat === "undefined") {
        socket.emit('initiate received', {
          chatid: wplc_cid
        });

        // Start user's session	
        var anti_cache = Date.now();
        var sdata = { cid: wplc_cid, server_token: wplc_restapi_token, _wpnonce: wplc_restapi_nonce };
        wplc_send_url = wplc_restapi_endpoint + "/start_session?nocache=" + anti_cache;
        jQuery.ajax({
          url: wplc_send_url,
          data: sdata,
          type: "POST",
          timeout: 12000,
          success: function(response) {
            console.log(response);
          },
          error: function(error) {
            console.log(error);
          },
        });

        user_hearbeat = setInterval(function() {
          if (socket.connected) {
            socket.emit('heartbeat');

          }
        }, 5000);
      }

      niftyUpdateStatusCookie('active');
      jQuery.event.trigger({
        type: "tcx_agent_initiated_chat",
        ndata: data
      });
    });

    /* Confirm that a message was saved to the db */
    socket.on('message received', function(data) {
      if (typeof data !== 'undefined') {
        if (typeof data.msgID !== 'undefined' && typeof data.outcome !== 'undefined') {
          tcx_msg_confirmations[data.msgID] = data.outcome;
        }
      }

    });

    socket.on('message read received', function(data) {
      jQuery.event.trigger({
        type: "tcx_message_read_received",
        ndata: data
      });
    });

    socket.on('agent to participant ping', function(data) {
      socket.emit('agent to participant ping received', {
        fromsocket: socket.id,
        intendedsocket: data.fromsocket,
        chatid: data.chatid
      });
    });

    socket.on("chat ended", function(data) {
      jQuery.event.trigger({
        type: "tcx_chat_ended_notification",
        ndata: data
      });

      // End user's session
      var sdata = { cid: wplc_cid };
      wplc_rest_api('end_session', sdata, 12000, null);

      jQuery("#tcx_chat_ended").show();
      tcx_end_chat_div_create();
      //$("#wplc_user_message_div").hide();

      if (typeof user_heartbeat !== "undefined") {
        clearInterval(user_heartbeat);
        user_heartbeat = undefined;
      }
      socket.disconnect({
        test: 'test'
      });
      niftyUpdateStatusCookie('browsing');
      // restart connection as a visitor
      if (typeof io !== "undefined") {
        wplc_set_up_query_string();
        socket = io.connect(WPLC_SOCKET_URI, {
          query: query_string,
          transports: ['websocket']
        });
        wplc_chat_delegates();
      }

      if (typeof Cookies !== "undefined") {
        Cookies.remove("wplc_cid");
      }
    });

    socket.on("averageResponse", function(data) {
      jQuery.event.trigger({
        type: "tcx_average_response",
        ndata: data
      });

    });

    socket.on("recent_agents", function(data) {
      if (typeof data !== "undefined" && typeof data.agents !== "undefined") {
        tcx_recent_agents = data.agents;
      }
    });

    socket.on("agent_data", function(data) {
      if ((typeof data !== "undefined" && data !== null) && (typeof data.ndata !== "undefined" && data.ndata !== null) && (typeof data.ndata.aid !== 'undefined' && data.ndata.aid !== null)) {
        if (typeof tcx_recent_agents_data === "undefined") {
          tcx_recent_agents_data = {};
          tcx_recent_agents_data[data.ndata.aid] = data.ndata;
        } else {
          tcx_recent_agents_data[data.ndata.aid] = data.ndata;
        }
      }
    });

    socket.on("transfer chat", function(data) {
      addNotice({
        message: 'You are being transferred to another agent. Please be patient.'
      });
    });

    socket.on("location found", function(data) {
      tcx_location_info = data; //Set the data
    });

    socket.on('chat history', function(data) {
      jQuery.event.trigger({
        type: "tcx_chat_history",
        ndata: data
      });

    });

    // Whenever the server emits 'login', log the login message
    socket.on('login', function(data) {

      connected = true;
      // Display the welcome message

      /**
       * Only show if this is the keepalive session (i.e. we are wanting to chat now)
       */
      if (keepalive) {
        var message = "Connection established";
      }

    });

    // Whenever the server emits 'new message', update the chat body
    socket.on('new message', function(data) {
      socket.emit('message read', data);
      jQuery.event.trigger({
        type: "tcx_new_message",
        ndata: data
      });
      if (!!wplc_enable_ding.value) {
        tcx_ping.play();
      }

      jQuery('#tcx_chat_ended').hide();
    });

    socket.on('user chat notification', function(data) {
      jQuery.event.trigger({
        type: "tcx_user_chat_notification",
        ndata: data
      });
    });

    socket.on('custom data received', function(data) {
      jQuery.event.trigger({
        type: "tcx_custom_data_received",
        ndata: data
      });
    });

    // Whenever the server emits 'new message', update the chat body
    socket.on('socketid', function(socketid) {
      document.cookie = "nc_sid=" + socketid.socketid;
      if (!wplc_online) {}
    });

    socket.on('agent joined', function(data) {
      clearTimeout(agent_disc_timer[data.agent]);
      jQuery.event.trigger({
        type: "tcx_agent_joined",
        ndata: data
      });

      jQuery('.tmp-welcome-msg').remove();
    });

    socket.on('new_socket', function(socketid) {});

    socket.on('agent left', function(data) {
      jQuery.event.trigger({
        type: "tcx_agent_left",
        ndata: data
      });

    });

    socket.on('agent connected', function(data) {
      clearTimeout(agent_disc_timer[data.aid]);
    })

    socket.on('agent disconnected', function(data) {

      agent_disc_timer[data.aid] = setTimeout(function() {
        jQuery.event.trigger({
          type: "tcx_agent_disconnected",
          ndata: data
        });
        removeChatTyping(data);
      }, 8000);

    });

    // Whenever the server emits 'typing', show the typing message
    socket.on('typing', function(data) {
      jQuery.event.trigger({
        type: "tcx_typing",
        ndata: data
      });

    });

    // Whenever the server emits 'stop typing', kill the typing message
    socket.on('stop typing', function(data) {
      jQuery.event.trigger({
        type: "tcx_stop_typing",
        ndata: data
      });
    });

    // Receive CHAT ID from server
    socket.on('chatID', function(data) {
      Cookies.set('wplc_cid', data.chatid, {
        expires: 1,
        path: '/'
      });

      wplc_cid = data.chatid;

      /* is chat box open? */
      if (!nifty_is_chat_open) {
        nifty_init_chat_box_check(data.chatid);
      }
    });

    socket.on("involved check returned", function(data) {
      jQuery.event.trigger({
        type: 'tcx_build_involved_agents_header',
        ndata: data
      });
    });

    socket.on('disconnect', function() {
      if (typeof user_heartbeat !== "undefined")
        clearInterval(user_heartbeat);
      user_heartbeat = undefined;
      /**
       * Only show if this was part of the keepalive session (i.e. an active chat)
       */
      if (keepalive) {
        jQuery.event.trigger({
          type: "tcx_disconnected"
        });
      }
    });

    socket.on('reconnect', function() {
      /**
       * Only show if this was part of the keepalive session (i.e. an active chat)
       */
      if (keepalive) {
        jQuery.event.trigger({
          type: "tcx_reconnect"
        });
      }
      nc_add_user(socket, '');
    });

    socket.on('reconnect_error', function() {
      jQuery.event.trigger({
        type: "tcx_reconnect_error"
      });
    });

    socket.on('a2vping', function(data) {
      socket.emit('a2vping return', {
        fromsocket: socket.id,
        intendedsocket: data.returnsocket,
        chatid: data.chatid
      });
    })
  }

  $messages = jQuery('#wplc_chatbox'); // Messages area
  $inputMessage = jQuery('#wplc_chatmsg'); // Input message input box

  jQuery("#nifty_file_input").on("change", function() {
    var file = this.files[0]; //Last file in array
    wplcShareFile(file, '#nifty_attach_fail_icon', '#nifty_attach_success_icon', '#nifty_attach_uploading_icon', "#nifty_select_file");
    jQuery("#chat_drag_zone").fadeOut();

    this.value = "";
  });

  /** Image pasting functionality */
  try {
    document.getElementById('wplc_chatmsg').onpaste = function(event) {
      // use event.originalEvent.clipboard for newer chrome versions
      var items = (event.clipboardData || event.originalEvent.clipboardData).items;
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
          document.getElementById("wplc_chatmsg").value = "####" + event.target.result + "####";

          jQuery("#wplc_send_msg").click();
        };
        reader.readAsDataURL(blob);
      }
    }
  } catch (ex) {}

  jQuery("#nifty_tedit_b").click(function() {
    niftyTextEdit("b");
  });
  jQuery("#nifty_tedit_i").click(function() {
    niftyTextEdit("i");
  });
  jQuery("#nifty_tedit_u").click(function() {
    niftyTextEdit("u");
  });
  jQuery("#nifty_tedit_strike").click(function() {
    niftyTextEdit("strike");
  });
  jQuery("#nifty_tedit_mark").click(function() {
    niftyTextEdit("mark");
  });
  jQuery("#nifty_tedit_sub").click(function() {
    niftyTextEdit("sub");
  });
  jQuery("#nifty_tedit_sup").click(function() {
    niftyTextEdit("sup");
  });
  jQuery("#nifty_tedit_link").click(function() {
    niftyTextEdit("link");
  });
  /*
  setInterval(function() {
    getText(document.getElementById("wplc_chatmsg"));
  }, 1000);
  */
  /**
   * End of rich text functionality
   */

  /* find out if we have had a chat with this visitor before */
  sid = nc_getCookie("wplc_sid");
  nifty_chat_status_temp = nc_getCookie("nc_status");
  if (nifty_chat_status_temp !== "undefined") {
    nifty_chat_status = nifty_chat_status_temp;
  }
  chatid = nc_getCookie("wplc_cid");
  if (chatid !== "undefined") {
    wplc_cid = chatid;
    nc_name = nc_getCookie("nc_username");
  }

  if (window.console) {
    console.log("[WPLC] Connecting to " + WPLC_SOCKET_URI);
  }

  /* is socket.io ready yet? */

  /* blocked? */
  var tcx_b = wplc_getCookie('tcx_b');
  if (typeof tcx_b !== "undefined" && tcx_b === '1') {
    console.log("[WPLC] You have been blocked from using WP Live Chat by 3CX");
    return;
  } else {
    wplc_wait_for_socketio();
    wplc_chat_delegates();
  }

  function wplc_wait_for_socketio() {
    if (typeof io !== "undefined") {
      wplc_set_up_query_string();
      socket = io.connect(WPLC_SOCKET_URI, {
        query: query_string,
        transports: ['websocket']
      });
    } else {
      setTimeout(wplc_wait_for_socketio(), 100);
    }
  }

  /**
   * Connect the node socket
   *
   * @param {bool} keepalive Keep this connection alive?
   */
  wplc_connect = function(keepalive) {

    if (tcx_inactive === false && wplc_online) {
      if (typeof socket !== "undefined") {
        if (socket.connected) {

          /* already connected */
        } else {
          //opening socket connection
          wplc_set_up_query_string();
          socket = io.connect(WPLC_SOCKET_URI, {
            query: query_string,
            transports: ['websocket']
          });

          wplc_chat_delegates(keepalive);
        }
      } else {
        //opening socket connection2
        wplc_set_up_query_string();
        socket = io.connect(WPLC_SOCKET_URI, {
          query: query_string,
          transports: ['websocket']
        });

        wplc_chat_delegates(keepalive);
      }
    } else {

      /* try again in 7 seconds */
      setTimeout(function() {
        if (socket.connected) {} else {
          wplc_connect(false);
        }
      }, 7000);
    }

  }

  // Initialize variables
  var $window = jQuery(window);

  var message_preview_currently_being_typifcationed;

  /**
   * Detect if the user is active or inactive.
   *
   * This manipulates the shortpoll connection to the server
   *
   * i.e. an inactive user will not send shortpolls.
   */
  jQuery(document).on('mousemove', function() {

    clearTimeout(tcx_inactive_timeout);
    tcx_inactive = false;
    tcx_inactive_timeout = setTimeout(function() {
      tcx_inactive = true;
    }, tcx_timeout_duration);
  });

  document.addEventListener('tcx_send_message', function(e) {

    if (typeof wplc_online !== 'undefined' && wplc_online === true) {
      socket.emit('stop typing', {
        chatid: wplc_cid
      });
    }
    // reset the typing variable
    typing = false;
    // reset the niftyIsEditing variable
    niftyIsEditing = false;
  }, false);

  jQuery(document).on("tcx_send_message", function(e) {
    //sendMessage(e.message);
    if (typeof wplc_online !== 'undefined' && wplc_online === true) {
      socket.emit('stop typing', {
        chatid: wplc_cid
      });
    }
    // reset the typing variable
    typing = false;
    // reset the niftyIsEditing variable
    niftyIsEditing = false;
  });

  // Keyboard events

  jQuery(document).on("keydown", "#wplc_chatmsg", function(event) {
    // When the client hits ENTER on their keyboard
    if (event.which === 13 && !event.shiftKey) {

      if (jQuery(this).val().trim() !== '') {
        event.preventDefault();
        jQuery("#wplc_send_msg").click();
      }
    } else if (event.which === 27 && !event.shiftKey) {
      jQuery("#wplc_chatmsg").val('');
      niftyIsEditing = false;
    }

  });

  $inputMessage.keyup(function(event) {
    // When the client hits ENTER on their keyboard

    if (event.which === 13 && !event.shiftKey) {} else {
      if (config.enable_typing_preview == 1 && typeof wplc_online !== 'undefined' && wplc_online === true) {
        socket.emit('typing_preview', {
          chatid: wplc_cid,
          tempmessage: $inputMessage.val()
        });
      }
    }
  });

  $inputMessage.on('input', function() {
    updateTyping();
  });

  // Click events

  // Focus input when clicking on the message input's border
  $inputMessage.click(function() {
    $inputMessage.focus();
  });

  /*jQuery(document).on("mouseleave", ".message", function () {
    var tmid = jQuery(this).attr('mid');
    jQuery(".message_" + tmid + " .tcx-edit-message").hide();
  });

  jQuery(document).on("mouseenter", ".message", function () {
    var tmid = jQuery(this).attr('mid');
    jQuery(".message_" + tmid + " .tcx-edit-message").show();
  });*/

  jQuery(document).on("click", ".tcx_restart_chat", function() {
    jQuery("#wp-live-chat-header").click();
    jQuery(".wplc_agent_info").html('');
    // jQuery('#wplc_chatbox').html('');
    setTimeout(function() {
      jQuery("#wp-live-chat-header").click();
    }, 100);

    jQuery('#wplc_end_chat_button').show();
    jQuery('#wplc_end_chat_button').removeAttr('wplc_disable');
  });

  jQuery(document).on("click", "#wplc_send_msg", function() {
    var message = $inputMessage.val();
    if (message.length > 2000) {
      message = message.substring(0, 2000);
    }
    sendMessage(message);
  });

  jQuery(document).on("nifty_trigger_open_chat", function(event) {
    open_chat();
    jQuery("#tcx_chat_ended").hide();
  });

  jQuery(document).on("tcx_socket_connected", function(e) {
    if (typeof socket !== "undefined" && typeof nifty_chat_status !== "undefined") {
      if (nifty_chat_status === "active") {
        socket.emit('check involved agents', {
          chatid: chatid
        });
      }
    }
  });

  jQuery(document).on("wplc_animation_done", function(event) {
    if (typeof wdtEmojiBundle !== "undefined") {
      wdtEmojiBundle.defaults.emojiSheets = {
        'apple': wplc_baseurl + 'js/vendor/wdt-emoji/sheets/sheet-apple-64-indexed-128.png',
        'google': wplc_baseurl + 'js/vendor/wdt-emoji/sheets/sheet-google-64-indexed-128.png',
        'twitter': wplc_baseurl + 'js/vendor/wdt-emoji/sheets/sheet-twitter-64-indexed-128.png',
        'emojione': wplc_baseurl + 'js/vendor/wdt-emoji/sheets/sheet-emojione-64-indexed-128.png',
        'facebook': wplc_baseurl + 'js/vendor/wdt-emoji/sheets/sheet-facebook-64-indexed-128.png',
        'messenger': wplc_baseurl + 'js/vendor/wdt-emoji/sheets/sheet-messenger-64-indexed-128.png'
      };
      tcx_attempt_emoji_input_init(0);
    }
  });

  /* Minimize chat window */
  jQuery("#wp-live-chat-minimize").on("click", function() {
    jQuery.event.trigger({
      type: "nifty_minimize_chat"
    });
    Cookies.set('nifty_minimize', "yes", {
      expires: 1,
      path: '/'
    });
    nifty_is_minimized = true;
  });

  /**
   * Click handler for the start chat button
   */
  jQuery("#wplc_start_chat_btn").on("click", function() {
    jQuery("#wplc_name").removeClass('wplc_error_field');
    jQuery("#wplc_email").removeClass('wplc_error_field');
    jQuery("#wplc_chat_gdpr_opt_in").removeClass('incomplete');
    document.getElementById('wplc_name').title = '';
    document.getElementById('wplc_email').title = '';
    var formOk = true;

    var wplc_is_gdpr_enabled = jQuery(this).attr('data-wplc-gdpr-enabled');
    if (typeof wplc_is_gdpr_enabled !== "undefined" && (wplc_is_gdpr_enabled === 'true')) {
      var wplc_gdpr_opt_in_checked = jQuery("#wplc_chat_gdpr_opt_in").is(':checked');
      if (typeof wplc_gdpr_opt_in_checked === "undefined" || wplc_gdpr_opt_in_checked === false) {
        /* GDPR requirements not met */
        jQuery("#wplc_chat_gdpr_opt_in").addClass('incomplete');
        formOk = false;
      }
    }

    var wplc_name = jQuery("#wplc_name").val().replace(/(<([^>]+)>)/ig, "");
    var wplc_email = jQuery("#wplc_email").val().replace(/(<([^>]+)>)/ig, "");

    if (wplc_name.length <= 0) {
      jQuery("#wplc_name").addClass('wplc_error_field');
      document.getElementById('wplc_name').title = wplc_error_messages.please_enter_name;
      formOk = false;
    }
    if (wplc_email.length <= 0) {
      jQuery("#wplc_email").addClass('wplc_error_field');
      document.getElementById('wplc_email').title = wplc_error_messages.please_enter_email;
      formOk = false;
    } else {
      if (jQuery("#wplc_email").attr('wplc_hide') !== "1") {
        var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,6}$/i;
        if (!testEmail.test(wplc_email)) {
          document.getElementById('wplc_email').title = wplc_error_messages.please_enter_valid_email;
          jQuery("#wplc_email").addClass('wplc_error_field');
          formOk = false;
        }      
      }
    }

    if (!formOk) {
      return false;
    }

    jQuery.event.trigger({
      type: "nifty_trigger_start_chat"
    });

    var date = new Date();
    date.setTime(date.getTime() + (2 * 60 * 1000));

    niftyUpdateUserDataCookies(wplc_name, wplc_email);
    niftyUpdateGravCookie(md5(wplc_email));
    niftyUpdateStatusCookie("active");

    wplc_connect(true);
    var request_chat_checker = setInterval(function() {
      if (typeof socket !== "undefined" && typeof socket.connected !== "undefined" && socket.connected === true) {
        clearInterval(request_chat_checker);
        socket.emit("request chat", {
          chatid: wplc_cid,
          name: wplc_name,
          email: wplc_email
        });
      } else {
        //still not connected, trying again
      }
    }, 300);
  });
}); // document.ready

/**
 * Add a log to the chat box
 *
 * @param {string} message Log message string
 * @param {object} options Options for the message being added (fade, prepend)
 */
function log(message, options) {
  var $el = jQuery('<li>').addClass('log').text(message);
  addMessageElement($el, options);
}

/**
 * Add a notice to the chat box
 *
 * @param {object} data Chat message data packet
 * @param {object} options Options for the message being added (fade, prepend)
 */
function addNotice(data, options) {
  options = options || {};
  var new_item = "";
  if (options.is_admin) {
    new_item += "<li class='message wplc-admin-notice' />";
  } else {
    new_item += "<li class='message wplc-user-notice' />";
  }

  var $messageBodyDiv = jQuery('<span class="noticeBody">').html(wplcFormatParser(data.message));
  var $messageDiv = jQuery(new_item).append($messageBodyDiv);
  addMessageElement($messageDiv, options);
}

/**
 * Remove any remaining 'typing messages'
 *
 * @param {object} data Data to check
 */
function removeChatTyping(data) {
  getTypingMessages(data).fadeOut(function() {
    jQuery(this).remove();
  });
}

/**
 * Add a message elemtn to the document. Mostly used for events as WPLC will handle message appending
 *
 * @param {element} el The element to add to the chat box
 * @param {object} options Options for the message being added (fade, prepend)
 */
function addMessageElement(el, options) {
  var $el = jQuery(el);

  // Setup default options
  if (!options) {
    options = {};
  }
  if (typeof options.fade === 'undefined') {
    options.fade = true;
  }
  if (typeof options.prepend === 'undefined') {
    options.prepend = false;
  }

  // Apply options
  if (options.fade) {
    $el.hide().fadeIn(FADE_TIME);
  }
  if (options.prepend) {
    $messages.prepend($el);
  } else {
    $messages.append($el);
  }
  $messages[0].scrollTop = $messages[0].scrollHeight;
}

/**
 * Update the typing statu on the socket
 */
function updateTyping() {
  if (connected) {
    if (!niftyIsEditing) {
      if (!typing) {
        typing = true;
        socket.emit('typing', {
          chatid: wplc_cid
        });
      }
      lastTypingTime = (new Date()).getTime();

      setTimeout(function() {
        var typingTimer = (new Date()).getTime();
        var timeDiff = typingTimer - lastTypingTime;
        if (timeDiff >= TYPING_TIMER_LENGTH && typing) {
          if (typeof wplc_online !== 'undefined' && wplc_online === true) {
            socket.emit('stop typing', {
              chatid: wplc_cid
            });
          }
          typing = false;
        }
      }, TYPING_TIMER_LENGTH);
    }
  }
}

/**
 * Get the username of the person who is typing (Example: Agent is typing)
 *
 * @param {object} data Packet to check
 */
function getTypingMessages(data) {
  return jQuery('.typing.message').filter(function(i) {
    return jQuery(this).data('username') === data.username;
  });
}

/**
 * Send a chat message using the socket. Also checks if this is a new message, or an edit to an existing message.
 *
 * @param {string} message Message to be sent
 */
function sendMessage(message) {
  if (typeof tcx_convert_colon_to_uni !== "undefined") {
    message = tcx_convert_colon_to_uni(message);
  }

  var randomNum = Math.floor((Math.random() * 100) + 1);

  var msgID = Date.now() + randomNum;
  lastmessagesent = msgID;

  var ndata = {
    username: username,
    message: message,
    aoru: 'u',
    msgID: msgID,
    is_admin: false
  }

  jQuery.event.trigger({
    type: "tcx_send_message",
    message: message,
    msg_id: msgID
  });

  jQuery.event.trigger({
    type: "tcx_new_message",
    ndata: ndata,
    msgID: msgID
  });

  // tell server to execute 'new message' and send along one parameter
  var msgObject = {
    message: message,
    chatid: wplc_cid,
    msgID: msgID,
    aoru: 'u'
  };
  socket.emit('new message', msgObject);

  /* run timer to check if message was delivered! */
  tcxConfirmDelivery(msgID, msgObject);

}

/**
 * Update the chat status cookie
 *
 * @param {string} new_status The status you would like to store
 */
function niftyUpdateStatusCookie(new_status) {
  Cookies.set('nc_status', new_status, {
    expires: 1,
    path: '/'
  });
}

/**
 * Update the visitors gravatar cookie
 *
 * @param {string} grav_hash Gravatar Hash (MD5 of email address)
 */
function niftyUpdateGravCookie(grav_hash) {
  Cookies.set('wplc_grav_hash', grav_hash, {
    expires: 1,
    path: '/'
  });

  wplc_cookie_grav_hash = grav_hash;
}

/**
 * Update the name and email cookies
 *
 * @param {string} name Name of the visitor
 * @param {string} email Email of the visitor
 */
function niftyUpdateUserDataCookies(name, email) {
  Cookies.set('wplc_name', name, {
    expires: 1,
    path: '/'
  });
  Cookies.set('wplc_email', email, {
    expires: 1,
    path: '/'
  });

  wplc_cookie_name = name;
  wplc_cookie_email = email;
}

/**
 * Open the chat box
 *
 * @param {bool} force Force open regardless of state
 */
var open_chat = function(force) {
  var tmp_cookie_val = nc_getCookie('nifty_minimize');
  nifty_is_minimized = tmp_cookie_val == '' || tmp_cookie_val == 'false' || tmp_cookie_val == false ? false : true;

  nifty_chat_status_temp = nc_getCookie("nc_status");
  wplc_chat_status_temp = nc_getCookie("wplc_chat_status");

  if (nifty_chat_status_temp === "active") {
    niftyUpdateStatusCookie("active");
    wplc_connect(true);

    if (!nifty_is_minimized) {
      jQuery.event.trigger({
        type: "nifty_trigger_open_chat_2",
        wplc_online: wplc_online
      });
      nifty_is_chat_open = true;
    }
    if (!jQuery("#wp-live-chat-header").hasClass("active")) {
      jQuery("#wp-live-chat-header").click();
    }
  } else if (nifty_chat_status_temp === "browsing" || wplc_chat_status_temp === "5") { //Added 11 here for usability
    if (jQuery("#wp-live-chat-2").is(":visible") === false && jQuery("#wp-live-chat-4").is(":visible") === false) {
      jQuery("#wp-live-chat-2").show();
      jQuery("#wp-live-chat-header").addClass("active");
    }
  }
}

/**
 * Get the selection range on the current element
 *
 * @param {element} elem The element you would like to check
 */
function getText(elem) {
  if (checkSelection) {
    if (selectedIndexStart !== elem.selectionStart) {
      selectedIndexStart = elem.selectionStart;
    }
    if (selectedIndexEnd !== elem.selectionEnd) {
      selectedIndexEnd = elem.selectionEnd;
    }
  }

}

/**
 * Legacy code for the hidden text editor which autmatically adds tags like 'link:' or 'mark:'
 *
 * Depracated, but supported for legacy users
 *
 * @param {string} insertContent Tag to insert
 */
function niftyTextEdit(insertContent) {
  if (typeof selectedIndexStart !== "undefined" && typeof selectedIndexEnd !== "undefined") {
    checkSelection = false;
    /*Text editor Code here*/

    jQuery("#wplc_chatmsg").focus();

    var current = jQuery("#wplc_chatmsg").val();

    var pre = current.substr(0, (selectedIndexStart > 0) ? selectedIndexStart : 0);
    var selection = current.substr(selectedIndexStart, selectedIndexEnd - selectedIndexStart);
    var post = current.substr(((selectedIndexEnd < current.length) ? selectedIndexEnd : current.length), current.length);

    current = pre + insertContent + ":" + selection + ":" + insertContent + post;
    jQuery("#wplc_chatmsg").val(current);

    checkSelection = true;
  }
}

/**
 * Handles uploading a file within the chat
 *
 * @param {file} fileToUpload The file to upload
 * @param {string} failedID The id of the div to show when upload fails
 * @param {string} successID The id of the div to show when upload succeeds
 * @param {string} uploadingID The id of the div to show when upload is in progress
 * @param {string} originalID The id of the div to show when upload final div to show after evething is complete
 */
function wplcShareFile(fileToUpload, failedID, successID, uploadingID, originalID) {
  if (fileToUpload == undefined || fileToUpload == false || fileToUpload == null) {
    return;
  }

  var formData = new FormData();
  formData.append('file', fileToUpload);
  formData.append('timestamp', Date.now());
  formData.append('_wpnonce', wplc_restapi_nonce);
  jQuery(uploadingID).show();
  jQuery(originalID).hide();
  jQuery(successID).hide();
  jQuery(failedID).hide();

  var uploadUrl = '';
  uploadUrl = (typeof tcx_override_upload_url !== "undefined" && tcx_override_upload_url !== "") ? tcx_override_upload_url : uploadUrl;

  if (fileToUpload.name.match(new RegExp('^.*\\.(' + config.allowed_upload_extensions + ')$', 'i'))) {
    //Files allowed - continue
    if (fileToUpload.size < 8000000) {
      jQuery.ajax({
        url: uploadUrl,
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

            //All good post the link to file
            var fileLinkUrl = false;
            if (!tcxIsJson(data)) {
              //This is not a parsable JSON string
              if (typeof data !== "object") {
                fileLinkUrl = data;
              } else {
                if (typeof data.response !== "undefined") {
                  //Our url is in response index
                  fileLinkUrl = data.response;
                } else {
                  fileLinkUrl = data;
                }
              }

            } else {
              //This is a parsable JSON string which will now be converted into an object
              var dataPacket = JSON.parse(data);
              if (typeof dataPacket.response !== "undefined") {
                //Our url is in response index
                fileLinkUrl = dataPacket.response;
              } else {
                fileLinkUrl = data;
              }
            }

            if (fileLinkUrl !== false) {
              if (fileLinkUrl !== 'Security Violation') {
                tag = 'link';
                jQuery("#wplc_chatmsg").val(tag + ":" + fileLinkUrl + ":" + tag); //Add to input field
                jQuery("#wplc_send_msg").trigger("click"); //Send message
                setTimeout(function() {
                  $messages[0].scrollTop = $messages[0].scrollHeight;
                }, 1000);
              } else {
                alert('Security Violation: File Not Allowed.');
              }
            }
          } else {
            jQuery(uploadingID).hide();
            jQuery(failedID).show();
            setTimeout(function() {
              jQuery(failedID).hide();
              jQuery(originalID).show();
            }, 2000);

          }
        },
        error: function() {
          jQuery(uploadingID).hide();
          jQuery(failedID).show();
          setTimeout(function() {
            jQuery(failedID).hide();
            jQuery(originalID).show();
          }, 2000);

        }
      });
    } else {
      alert("File limit is 8mb");
      jQuery(uploadingID).hide();
      jQuery(failedID).show();
      setTimeout(function() {
        jQuery(failedID).hide();
        jQuery(originalID).show();
      }, 2000);
    }
  } else {
    alert("File type not supported.");
    jQuery(uploadingID).hide();
    jQuery(failedID).show();
    setTimeout(function() {
      jQuery(failedID).hide();
      jQuery(originalID).show();
    }, 2000);
  }
}

/**
 * Fire off needed events to confirm message delivry event
 *
 * @param {string} msgID Message ID
 * @param {object} msgObject Message object
 */
function tcxConfirmDelivery(msgID, msgObject) {
  jQuery.event.trigger({
    type: 'tcx_trigger_check_message_received',
    msgID: msgID,
    msgObject: msgObject
  });
}

/**
 * Create the end chat div, which holds the restart chat button
 */
function tcx_end_chat_div_create() {
  jQuery('<a/>', {
    'class': 'tcx_restart_chat',
    href: "javascript:void(0);",
    title: "Restart chat",
    html: "Restart chat",
  }).appendTo('#tcx_chat_ended');

}

/**
 * Render emojis within a message string
 *
 * @param {string} msg The chat message
 * @return {string} The chat message with emojis
 */
function wplc_emoji_render(msg) {
  if (typeof wdtEmojiBundle !== "undefined") {
    msg = wdtEmojiBundle.render(msg);
  }
  return msg;
}

/**
 * Add the user/socket as a user by sending all needed data to the server
 *
 * @param {socket} socket The current users socket
 * @param {data} data The visitor data packet
 */
function nc_add_user(socket, data) {
  var data = {};
  /* recurring visitor */
  /* find out if we have had a chat with this visitor before */
  chatid = nc_getCookie("wplc_cid");
  if (typeof chatid !== "undefined") {
    wplc_cid = chatid;
    nc_name = nc_getCookie("nc_username");
    wplc_name = nc_getCookie("wplc_name");
  }

  var tcx_customerID = wplc_getCookie('tcx_customerID');
  if (typeof tcx_customerID !== "undefined" && tcx_customerID !== '' && tcx_customerID !== null) {
    data.customerID = tcx_customerID;
  }

  /* blocked? */
  var tcx_b = wplc_getCookie('tcx_b');
  if (typeof tcx_b !== "undefined" && tcx_b === '1') {
    console.log("[WPLC] You have been blocked from using WP Live Chat by 3CX");
    return;

  } else {

    if (typeof chatid !== "undefined") {
      if (typeof nc_name !== "undefined") {
        data.username = nc_name;
      } else {
        if (typeof wplc_name !== 'undefined') {
          data.username = wplc_name;
        } else {
          data.username = config.wplc_user_default_visitor_name;
        }
      }
      data.api_key = tcx_api_key;
      data.wplc_cid = chatid;
    } else {
      /* first time user */
      data.username = config.wplc_user_default_visitor_name;
      data.api_key = tcx_api_key;
      data.wplc_cid = null;
    }

    data.date_first = tcx_first;
    data.date_current = tcx_current;

    data.timezoneUTC = tcx_get_timezone();
    data.device_in_use = tcx_get_device_in_use();
    data.operating_system = tcx_get_operating_system();
    data.location_info = { code: config.country_code, name: config.country_name };

    if (typeof wplc_extra_data !== 'undefined' && typeof wplc_extra_data['wplc_user_selected_department'] !== 'undefined') {
      data.department = wplc_extra_data['wplc_user_selected_department'];
    }

    /**
     * Let's identify if this user is a visitor. If they are, lets set the connection type to "SHORT" so that the connection can be dropped immediately once data is received.
     * This negates the need for having the socket stay open for visitors.
     */
    nc_status = nc_getCookie("nc_status");

    if (typeof nc_status === "undefined" || nc_status === "browsing") {
      data.connectiontype = "short";
    }

    socket.emit('add user', data);
  }

}

/**
 * Get a specific cookie's value
 *
 * @param  {string} name Cookie key
 * @return {string} Cookie value
 */
function nc_getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2)
    return parts.pop().split(";").shift();
}

/**
 * Checks to see if the init chat box function has been loaded (via another JS file). If not, it will recursively keep trying until it has been loaded
 *
 * @param   {int} cid Chat ID
 * @return  {void}
 */
function nifty_init_chat_box_check(cid) {
  if (typeof wplc_init_chat_box === "function") {
    wplc_init_chat_box(cid);
  } else {
    if (typeof wplc_init_chat_box !== "undefined" && wplc_init_chat_box !== false) {
      setTimeout(function() {
        /* keep checking every 500ms to see if that function exists */
        nifty_init_chat_box_check(cid);
      }, 500);
    }
  }
}

/**
 * Check if string is JSON object
 *
 * @param {string} str String to check
 * @return {bool} True if string is JSON
 */
function tcxIsJson(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}

/**
 * Test to make sure localStorage exists and is enabled
 *
 * @return {bool} True if locale storage is available, false if not
 */
function wplc_test_localStorage() {
  if (typeof localStorage !== 'undefined') {
    try {
      localStorage.setItem('tcx_test', 'yes');
      if (localStorage.getItem('tcx_test') === 'yes') {
        localStorage.removeItem('tcx_test');
        return true;
        // localStorage is enabled
      } else {
        return false;
        // localStorage is disabled
      }
    } catch (e) {
      return false;
      // localStorage is disabled
    }
  } else {
    return false;
    // localStorage is not available
  }
}

/*
 * Returns a user readable timezone difference from UTC (ex: +2 which is UTC+2)
 *
 * @return {string} The best guess of the users timezone.
 */
function tcx_get_timezone() {
  var offsetFromUTC = new Date().getTimezoneOffset();
  var offsetInHours = Math.floor(offsetFromUTC / 60);
  if (offsetInHours > 0) {
    //before standard UTD (-)
    return "-" + offsetInHours;
  } else if (offsetInHours < 0) {
    //Negative amount so this is after UTC (+)
    return offsetInHours.toString().replace("-", "+");
  } else {
    //is a zero
    return "0";
  }
}

/*
 * Returns a users estimated device (Desktop or Mobile) based on screen width
 *
 * @return {string} Device type (mobile|desktop)
 */
function tcx_get_device_in_use() {
  if (jQuery(window).width() < 900) {
    //Width is less than 900
    return "mobile";
  }
  return "desktop";
}

/*
 * Returns the users OS - will only run once as we have a check in place to see if the variable has been set.
 * This is done to prevent Regular Expression from being performed more often than we need it
 */
function tcx_get_operating_system() {
  if (tcx_user_current_os === false && typeof navigator !== "undefined" && navigator.userAgent !== "undefined") {
    var current_user_agent = navigator.userAgent;
    var possibleOsList = [{
      s: 'Windows 10',
      r: /(Windows 10.0|Windows NT 10.0)/
    }, {
      s: 'Windows 8.1',
      r: /(Windows 8.1|Windows NT 6.3)/
    }, {
      s: 'Windows 8',
      r: /(Windows 8|Windows NT 6.2)/
    }, {
      s: 'Windows 7',
      r: /(Windows 7|Windows NT 6.1)/
    }, {
      s: 'Windows Vista',
      r: /Windows NT 6.0/
    }, {
      s: 'Windows Server 2003',
      r: /Windows NT 5.2/
    }, {
      s: 'Windows XP',
      r: /(Windows NT 5.1|Windows XP)/
    }, {
      s: 'Windows 2000',
      r: /(Windows NT 5.0|Windows 2000)/
    }, {
      s: 'Windows ME',
      r: /(Win 9x 4.90|Windows ME)/
    }, {
      s: 'Windows 98',
      r: /(Windows 98|Win98)/
    }, {
      s: 'Windows 95',
      r: /(Windows 95|Win95|Windows_95)/
    }, {
      s: 'Windows NT 4.0',
      r: /(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/
    }, {
      s: 'Windows CE',
      r: /Windows CE/
    }, {
      s: 'Windows 3.11',
      r: /Win16/
    }, {
      s: 'Android',
      r: /Android/
    }, {
      s: 'Open BSD',
      r: /OpenBSD/
    }, {
      s: 'Sun OS',
      r: /SunOS/
    }, {
      s: 'Linux',
      r: /(Linux|X11)/
    }, {
      s: 'iOS',
      r: /(iPhone|iPad|iPod)/
    }, {
      s: 'Mac OS X',
      r: /Mac OS X/
    }, {
      s: 'Mac OS',
      r: /(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/
    }, {
      s: 'QNX',
      r: /QNX/
    }, {
      s: 'UNIX',
      r: /UNIX/
    }, {
      s: 'BeOS',
      r: /BeOS/
    }, {
      s: 'OS/2',
      r: /OS\/2/
    }, {
      s: 'Search Bot',
      r: /(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/
    }];

    for (var id in possibleOsList) {
      var current_os = possibleOsList[id];
      if (current_os.r.test(current_user_agent)) {
        tcx_user_current_os = current_os.s;
        return tcx_user_current_os; //Return and kill loop as we have a match
      }
    }

    //Made it past our loop - This shouldn't happen. But if it does the OS is unknown
    tcx_user_current_os = "Unknown"; //Prevent loop from running again
    return tcx_user_current_os;

  } else {
    return tcx_user_current_os; //Just return the OS
  }
}

/**
 * Test to make sure sessionStorage exists and is enabled
 *
 * @return {bool} True on success, and false on fail
 */
function wplc_test_sessionStorage() {
  if (typeof sessionStorage !== 'undefined') {
    try {
      sessionStorage.setItem('tcx_test', 'yes');
      if (sessionStorage.getItem('tcx_test') === 'yes') {
        sessionStorage.removeItem('tcx_test');
        return true;
        // sessionStorage is enabled
      } else {
        return false;
        // sessionStorage is disabled
      }
    } catch (e) {
      return false;
      // sessionStorage is disabled
    }
  } else {
    return false;
    // sessionStorage is not available
  }
}

/**
 * Recusively initialize the input field for emoji support. If an error occurs it will attempt again.
 *
 * Will try to initialize up to 5 times.
 * @param {int} attempt Attempt number
 */
function tcx_attempt_emoji_input_init(attempt) {
  try {
    wdtEmojiBundle.init('.wdt-emoji-bundle-enabled');
  } catch (err) {
    if (attempt < 5) {
      attempt++;
      setTimeout(function() {
        tcx_attempt_emoji_input_init(attempt);
      }, 1000);
    }
  }
}

/**
 * Clean up the query string
 *
 * @param {string} current_query Current Query String
 * @return {string} Modified Query String
 */
function wplc_query_cleanup(current_query) {
  if (current_query.charAt(0) === "&") {
    current_query = current_query.substring(1);
  }

  return current_query;
}

/**
 * Powered By Link for WPLC, which is appended to the chat box content
 */
function wplc_powered_by() {
  if (typeof tcx_force_powered_by !== 'undefined' && tcx_force_powered_by === true) {
    var html = '<span class="wplc_powered_by"><i title="Powered by" class="fa fa-bolt"></i> <a title="Powered by WP Live Chat by 3CX" href="https://www.3cx.com/wp-live-chat/?utm_source=powered&utm_medium=poweredby&utm_campaign=' + window.location.hostname + '" target="_BLANK" rel="nofollow" class="wplc-color-1">WP Live Chat by 3CX</a><span></span></span>';

    if (jQuery("#wp-live-chat-4").length) {
      jQuery("#wp-live-chat-4").append(html);
    }

    if (jQuery("#wp-live-chat-2").length) {
      jQuery("#wp-live-chat-2").append(html);
    }

    jQuery(".wplc_powered_by").css('position', 'absolute');
    jQuery(".wplc_powered_by").css('padding-left', '10px');
    jQuery(".wplc_powered_by").css('bottom', '-82px');
    jQuery(".wplc_powered_by").css('font-size', '10px');
    jQuery(".wplc_powered_by").css('font-family', 'Roboto, sans-serif');
    jQuery(".wplc_powered_by a").css('color', '#adadad');

    jQuery(".wplc_powered_by a").css('font-weight', '700');
    jQuery(".wplc_powered_by a").css('color', '#989898');

    /* If this is classic theme */
    jQuery(".classic .wplc_powered_by").css('bottom', '0px');
    jQuery(".classic #wplc_user_message_div").css('margin-bottom', '10px');

    jQuery("#wp-live-chat-2 .wplc_powered_by").css('bottom', '0px');

    jQuery(".wplc_powered_by").css('left', '0px');
  }

}

/**
 * Get a specific cookie by name

 * @param {string} name Name of the cookie
 * @return {string} Value of store cookie
 */
function wplc_getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2)
    return parts.pop().split(";").shift();
}