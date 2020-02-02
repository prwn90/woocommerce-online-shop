/*
 * Hanldes Message transportation within WPLC
 */
var gifExtensionPattern = /http(s?):([/|.|\w|\s|-])*\.gif/;

var wplc_server_method = null;
var wplc_supress_server_logs = true; //We are now surpressing server logs
var wplc_node_socket = null; //Will not be set unless
var wplc_node_send_queue = new Array();
var wplc_node_message_receiver = null;
var wplc_node_message_restart_handler = null;
var wplc_node_client_event_logger = null;
var wplc_node_sockets_ready = false;
var wplc_transport_prepared = false;

var wplc_node_async_array = new Array(); //Array which will be sent to our async URL for storage
var wplc_node_async_send_rate = 1; //Amount of messages that need to be present before we sent the async request
var wplc_node_async_cookie_check_complete = false;

var wplc_node_port_open = true; //This can be set to false to prevent any future data being sent
var wplc_node_is_client_typing = false;
var wplc_node_is_pair_typing_indicator_visible = false;
var wplc_node_pair_name = "";

var wplc_node_switch_ajax_complete = false;
var wplc_node_retry_count = 0;

var wplc_msg_history = new Array();

var wplc_xhr; //XHR global (allows us to abort previous AJAX request when the agent flips between screens quickly)



function WPLCServer() {
  var wplc_server_ref = this;
  //Default to ajax until chat starts
  wplc_server_method = WPLCServer.Ajax;
  wplc_server_ref.send = wplc_server_method.send;

  wplc_server_ref.isInSocketMode = wplc_server_method.isInSocketMode;
  wplc_server_ref.isPreparingSocketMode = wplc_server_method.isPreparingSocketMode;
  wplc_server_ref.transportPrepared = wplc_server_method.transportPrepared;
  wplc_server_ref.asyncStorage = wplc_server_method.asyncStorage;
  wplc_server_ref.forceClosePort = wplc_server_method.forceClosePort;
  wplc_server_ref.sendMessage = wplc_server_method.sendMessage;


  wplc_server_ref.prepareTransport = function(callback, messageHandler, restartHandler, clientEventLog) {
    wplc_server_log("-------------------");
    wplc_server_log("Preparing Transport");
    if (config.wplc_use_node_server) {
      if (window.WebSocket) {
        //Sockets are supported
        wplc_server_method = WPLCServer.Socket;
        wplc_server_log("Socket Mode");
      } else {
        wplc_server_method = WPLCServer.Ajax;
        wplc_server_log("Ajax Mode");
      }
    } else {
      wplc_server_method = WPLCServer.Ajax;
      wplc_server_log("Ajax Mode");
    }

    wplc_server_method.init(function() {
      wplc_server_ref.send = wplc_server_method.send;
      wplc_server_ref.isInSocketMode = wplc_server_method.isInSocketMode;
      wplc_server_ref.isPreparingSocketMode = wplc_server_method.isPreparingSocketMode;
      wplc_server_ref.transportPrepared = wplc_server_method.transportPrepared;
      wplc_server_ref.asyncStorage = wplc_server_method.asyncStorage;
      wplc_server_ref.forceClosePort = wplc_server_method.forceClosePort;
      wplc_server_ref.sendMessage = wplc_server_method.sendMessage;

      if (typeof callback === "function") {
        callback();
      }
    }, messageHandler, function() {
      wplc_server_method = WPLCServer.Ajax;
      wplc_server_log("Ajax Mode - Fail Over");

      wplc_server_ref.send = wplc_server_method.send;
      wplc_server_ref.isInSocketMode = wplc_server_method.isInSocketMode;
      wplc_server_ref.isPreparingSocketMode = function() { return false; };
      wplc_server_ref.transportPrepared = wplc_server_method.transportPrepared;
      wplc_server_ref.asyncStorage = wplc_server_method.asyncStorage;
      wplc_server_ref.forceClosePort = wplc_server_method.forceClosePort;
      wplc_server_ref.sendMessage = wplc_server_method.sendMessage;

      if (typeof wplc_ajaxurl !== "undefined" && typeof wplc_nonce !== "undefined" && typeof wplc_cid !== "undefined") {
        var wplc_fail_over_data = {
          action: 'wplc_node_switch_to_ajax',
          security: wplc_nonce,
          cid: wplc_cid
        };

        jQuery.ajax({
          url: wplc_ajaxurl,
          data: wplc_fail_over_data,
          type: "POST",
          timeout: 120000,
          success: function(response) {
            wplc_server_log("Ajax Mode Enabled");
          },
          error: function(error, exception) {
            wplc_server_log("Chat Fail Over Could Not Be Setup");
          },
          complete: function(response) {
            if (typeof callback === "function") {
              callback();
            }
          }
        });
      }
    }, restartHandler, clientEventLog);

    wplc_server_log("Transport Prepared");
    wplc_server_log("-------------------");
    wplc_transport_prepared = true;
  }

  wplc_server_ref.browserIsSocketReady = function() {
    if (config.wplc_use_node_server) {
      if (window.WebSocket) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
}

WPLCServer.Socket = {};

WPLCServer.Ajax = {
  init: function(callback, messageHandler, failOver, restartHandler, clientEventLog) {
    wplc_server_log("Ajax Init");
    if (typeof callback === "function") {
      callback();
    }
  },
  send: function(wplc_send_url, wplc_send_data, wplc_send_type, wplc_send_timeout, wplc_send_success_callback, wplc_send_fail_callback, wplc_send_complete_callback) {
    jQuery.ajax({
      url: wplc_send_url,
      data: wplc_send_data,
      type: wplc_send_type,
      timeout: wplc_send_timeout,
      success: function(response) {
        if (typeof wplc_send_success_callback === "function") {
          if (typeof wplc_send_data['action'] !== "undefined" && wplc_send_data['action'] !== "wplc_start_chat") { //Is this the start?
            wplc_send_success_callback(response);
          } else {
            //Check if we are going to go into socket mode after this?
            if (config.wplc_use_node_server) {
              if (window.WebSocket) {
                wplc_send_success_callback(response); //Send the data if we are going to sockets after this
              }
            }
          }
        }
      },
      error: function(error, exception) {
        if (typeof wplc_send_fail_callback === "function") {
          wplc_send_fail_callback(error, exception);
        }
      },
      complete: function(response) {
        if (typeof wplc_send_complete_callback === "function") {
          wplc_send_complete_callback(response, wplc_send_data);
        }
      }
    });
  },
  isInSocketMode: function() {
    return wplc_node_sockets_ready;
  },
  isPreparingSocketMode: function() {
    var preparing = false;
    if (config.wplc_use_node_server) {
      if (window.WebSocket) {
        preparing = true;
      }
    }

    return preparing;
  },
  transportPrepared: function() {
    return wplc_transport_prepared;
  },
  asyncStorage: function(wplc_send_url, wplc_send_data, wplc_send_timeout) {
    //Do nothing -> Ajax handles
  },
  forceClosePort: function() {
    //Do Nothing ajax doesnt use socket ports
  },
  sendMessage: function(wplc_send_url, wplc_send_data, wplc_send_type, wplc_send_timeout, wplc_send_success_callback, wplc_send_fail_callback, wplc_send_complete_callback) {
    WPLCServer.Ajax.send(wplc_send_url, wplc_send_data, wplc_send_type, wplc_send_timeout, wplc_send_success_callback, wplc_send_fail_callback, wplc_send_complete_callback);
  }
};


function wplc_server_log(msg) {
  if (wplc_supress_server_logs !== true && window.console) {
    console.log("WPLC SERVER: " + msg);
  }
}

function wplc_server_error(msg) {
  if (window.console) {
    console.error("WPLC SERVER ERROR: " + msg);
  }
}

function wplc_socket_send(data, success, fail, complete) {
  if (wplc_node_port_open) {
    wplc_socket_add_to_queue(data, success, fail, complete);

    //if(data.action !== "wplc_user_send_msg" && data.action !== "wplc_admin_send_msg"){
    var wplc_current_queue_item = wplc_socket_get_next_in_queue();
    if (wplc_current_queue_item !== false) {
      if (typeof wplc_node_socket !== "undefined" && wplc_node_socket !== null) {
        if (wplc_node_socket.readyState !== WebSocket.CONNECTING && wplc_node_socket.readyState !== WebSocket.CLOSING && wplc_node_socket.readyState !== WebSocket.CLOSED) {
          wplc_current_queue_item.data.is_typing = typeof wplc_node_is_client_typing !== "undefined" ? wplc_node_is_client_typing : false;
          wplc_node_socket.send(JSON.stringify(wplc_current_queue_item.data));

          if (typeof wplc_current_queue_item.success === "function") {
            wplc_current_queue_item.success();
          }
        } else {
          //Try again in a sec just now -> Add it to the queue
          setTimeout(function() {
            wplc_socket_send(data, success, fail, complete);
          }, 500);
        }
      } else {
        setTimeout(function() {
          //Try again in a sec just now -> Add it to the queue
          wplc_socket_send(data, success, fail, complete);
        }, 500);
      }
    }
    //}
  }
}

function wplc_sanitize_attributes(str) {
  var wplc_allowed_attributes = "onload,onclick,alert,onerror,dalert";
  var allowed_attr = wplc_allowed_attributes.split(",");
  for (i = 0; i < allowed_attr.length; i++) {
    str = str.replace(allowed_attr[i], '');
  }

  return str;
}

function wplc_socket_add_to_queue(data, success, fail, complete) {
  if (typeof data.server_token === "undefined") {
    if (typeof tcx_api_key !== "undefined") {
      data.server_token = tcx_api_key;
    } else {
      wplc_server_error("No Server Token Present, Something will go wrong");
    }
  }


  var queue_item = {
    data: data,
    success: success,
    fail: fail,
    complete: complete
  }

  if (wplc_node_send_queue.length > 0) {
    var last_item = wplc_node_send_queue[wplc_node_send_queue.length - 1];
    if (JSON.stringify(last_item.data) !== JSON.stringify(data)) {
      wplc_node_send_queue.push(queue_item);
    }
  } else {
    wplc_node_send_queue.push(queue_item);
  }
}

function wplc_socket_get_next_in_queue() {
  if (wplc_node_send_queue.length > 0) {
    return wplc_node_send_queue.shift();
  } else {
    return false;
  }
}

function wplc_rest_api(type, wplc_send_data, wplc_send_timeout, next) {

  if (typeof wplc_xhr !== "undefined" && typeof wplc_xhr.abort() !== "undefined") { wplc_xhr.abort(); }
  if (typeof next === "undefined" || next === null) { next = function() {}; }

  var wplc_node_ajax_action = type;

  //Send the data to the Async
  if (!!wplc_restapi_enabled.value && typeof wplc_restapi_endpoint !== "undefined") {
    //REST API is ready to rumble
    var anti_cache = Date.now();
    wplc_send_url = wplc_restapi_endpoint + "/" + type + "?nocache=" + anti_cache;
    var prepared_data = wplc_send_data;
    var x = {
      action: wplc_node_ajax_action,
      relay_action: wplc_send_data.action,
      chat_id: wplc_send_data.cid,
      security: wplc_send_data.security,
      message: wplc_send_data.msg,
      server_token: wplc_restapi_token,
      wplc_extra_data: document.wplc_extra_data,
      wplc_data: wplc_send_data
    };

    prepared_data.server_token = wplc_restapi_token;
    prepared_data.token = wplc_restapi_token; /* backwards compat */
    if (typeof prepared_data.wplc_extra_data === "undefined") {
      prepared_data.wplc_extra_data = document.wplc_extra_data;
    }
    prepared_data._wpnonce = wplc_restapi_nonce;

    wplc_xhr = jQuery.ajax({
      url: wplc_send_url,
      data: prepared_data,
      type: "POST",
      timeout: wplc_send_timeout,
      success: function(response) {
        wplc_server_log("REST SEND = SUCCESS");
        next(response);
      },
      error: function(error, exception) {
        wplc_server_log("REST SEND = FAIL");
        next();
      },
    });
  } else {
    wplc_server_log("NO REST API :(");
    next();
  }
}

function wplc_json_validator(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}

function wplc_node_parse_async_from_object(obj, complete) {
  for (var i in obj) {
    if (obj.hasOwnProperty(i)) {
      wplc_node_async_array.push(obj[i]);
    }
  }

  if (typeof complete === "function") {
    complete();
  }
}

function wplc_node_global_message_receiver(data) {
  if (data) {
    if (typeof data !== "object") {
      data = JSON.parse(data);
    }
  }

  if (typeof data['pair_name'] !== "undefined") {
    if (data['pair_name'] !== wplc_node_pair_name) {
      wplc_node_pair_name = data['pair_name'];
    }
  }

  if (typeof data['pair_typing'] !== "undefined") {

    if (data['pair_typing'] === true || data['pair_typing'] === "true") {
      if (wplc_node_is_pair_typing_indicator_visible === false) {
        if (jQuery("#wplc_user_typing").length > 0) {} else {
          jQuery(".typing_indicator").html("<span id='wplc_user_typing'>" + wplc_safe_html(wplc_node_pair_name + config.wplc_localized_string_is_typing_single) + "</span>");
          jQuery(".typing_indicator").addClass("typing_indicator_active");
        }
      }
      wplc_node_is_pair_typing_indicator_visible = true;
    } else {
      if (wplc_node_is_pair_typing_indicator_visible === true) {
        if (jQuery("#wplc_user_typing").length > 0) {
          jQuery("#wplc_user_typing").fadeOut("slow").remove();
          jQuery(".typing_indicator").removeClass("typing_indicator_active");
        }
      }
      wplc_node_is_pair_typing_indicator_visible = false;
    }

  }
}

function wplc_add_date_and_time(the_message, originates) {

  if (parseInt(originates) === 1 || parseInt(originates) === 2) {

    var time_msg = '';

    /* identfy the timestamp */
    if (typeof the_message.other === "undefined" || typeof the_message.other.datetime === "undefined" || the_message.other === false) {
      /* only run if it hasnt got a timestamp in the .other.datetime key */
      if (typeof the_message.timestamp !== "undefined") {
        /* most likely came from node as node */

        if (typeof the_message.other !== "object") { the_message.other = {}; }
        the_message.other.datetime = the_message.timestamp;

      }
    }

    if (typeof the_message.other === "undefined" || typeof the_message.other.datetime === "undefined") {
      /* there is no datetime so return nothing */
      return '';
    } else {
      if (typeof wplc_show_chat_detail !== "undefined") {

        var datetimestamp = the_message.other.hasOwnProperty('datetimeUTC') ? the_message.other.datetimeUTC : the_message.other.datetime;
        var dateTime = new Date(parseInt(datetimestamp) * 1000);

        if (typeof wplc_show_chat_detail.date !== 'undefined' && '1' === wplc_show_chat_detail.date) {

          var date_format = typeof wplc_datetime_format !== 'undefined' && wplc_datetime_format.hasOwnProperty('date_format') ? wplc_datetime_format.date_format : 'n/d';
          var date_month = dateTime.getMonth();
          var date_day = dateTime.getDate();
          var date_day_no = dateTime.getDay();
          var date_year = dateTime.getFullYear();

          time_msg += date_format
            .replace(/S/g, '%S%')
            .replace(/D/g, '%D%')
            .replace(/l/g, '%l%')
            .replace(/F/g, '%F%')
            .replace(/M/g, '%M%')
            .replace(/j/g, date_day)
            .replace(/d/g, date_day < 10 ? '0' + date_day : date_day)
            .replace(/w/g, date_day_no)
            .replace(/n/g, date_month + 1)
            .replace(/m/g, date_month + 1 < 10 ? '0' + (date_month + 1) : date_month + 1)
            .replace(/Y/g, date_year)
            .replace(/y/g, date_year.toString().substr(2, 2))
            .replace(/%S%/g, 1 === date_day ? 'st' : (2 === date_day ? 'nd' : (3 === date_day ? 'rd' : 'th')))
            .replace(/%D%/g, config.date_days[date_day_no].substr(0, 3))
            .replace(/%l%/g, config.date_days[date_day_no])
            .replace(/%F%/g, config.date_months[date_month])
            .replace(/%M%/g, config.date_months[date_month].substr(0, 3)) + ' ';

        }
        if (typeof wplc_show_chat_detail.time !== "undefined" && '1' === wplc_show_chat_detail.time) {

          var time_format = typeof wplc_datetime_format !== 'undefined' && wplc_datetime_format.hasOwnProperty('time_format') ? wplc_datetime_format.time_format : 'H:i';
          var time_hours_12 = dateTime.getHours() > 12 ? dateTime.getHours() - 12 : (dateTime.getHours() < 1 ? 12 : dateTime.getHours());
          var time_hours_24 = dateTime.getHours();
          var time_am_pm = time_hours_24 > 11 ? 'pm' : 'am';
          var time_minutes = dateTime.getMinutes();

          time_msg += time_format
            .replace(/g/g, time_hours_12)
            .replace(/h/g, time_hours_12 < 10 ? '0' + time_hours_12 : time_hours_12)
            .replace(/G/g, time_hours_24)
            .replace(/H/g, time_hours_24 < 10 ? '0' + time_hours_24 : time_hours_24)
            .replace(/i/g, time_minutes < 10 ? '0' + time_minutes : time_minutes)
            .replace(/a/g, time_am_pm.toLowerCase())
            .replace(/A/g, time_am_pm.toUpperCase());

        }
        if (time_msg !== '') {
          if (parseInt(originates) === 1) { aoru_class = 'wplc-msg-float-left'; } else { aoru_class = 'wplc-msg-float-right'; }
          time_msg = '<span class="timedate ' + aoru_class + '">' + time_msg + '</span>';
        }
      }


      return time_msg;
    }
  } else {
    return '';
  }

}

function wplc_get_chat_person_name_msg_field(name) {
  try {
    return '<span class="wplc-chat-person-name">' + name + ': </span>';
  } catch (err) {
    //console.log("Could not wplc_get_chat_person_name_msg_field() " + err);
  }
}

/**
 * Pushes the message object to the chat box
 *
 * @param  {object} the_message The message object
 * @param  {string} aoru        a for Agent, u for User
 * @return void
 */
function wplc_push_message_to_chatbox(the_message, aoru, next) {
  /**
   * First check if we have processed this message already, by comparing the ID
   *
   * Some system notifications may not come through with an ID so we can accept those.
   */
  var add_message = true;

  if (typeof the_message.mid !== "undefined" && aoru === "u") {
    if (parseInt(the_message.mid) != 0 && the_message.mid != null) {
      if (typeof wplc_msg_history[the_message.mid] !== "undefined") {
        /* we have this message already */
        add_message = false;
      } else {
        /* add this to our history */
        wplc_msg_history[the_message.mid] = true;
      }
    }
  }

  if (add_message) {
    add_message = typeof the_message.originates !== "undefined" && the_message.originates !== null && the_message.originates !== "null";
  }

  if (add_message) {
    var message_class = "";
    var message_grav = "";
    var message_from = "";
    var message_content = "";
    var message_aid;
    var audioPattern = new RegExp(/blob.wav/);
    var isAudioPattern = false;

    var msgType = 'user';
    if (parseInt(the_message.originates) === 1) {
      msgType = 'admin';
    } else {
      if (parseInt(the_message.originates) === 0 || parseInt(the_message.originates) === 3) {
        msgType = 'system';
      }
    }

    switch (msgType) {

      case 'admin':
        {
          //From Admin
          /* Define which agent it was sent from  */
          message_aid = false;
          if (typeof the_message.other !== "undefined" && typeof the_message.other.aid !== "undefined") {
            message_aid = the_message.other.aid.toString(); /* set it to a string because wp_localize doesnt know how to set keys as integers */
          } else if (typeof the_message.other !== "undefined" && typeof the_message.other.agent_id !== "undefined") {
            /* cloud server uses "agent_id" instead of "aid" */
            message_aid = the_message.other.agent_id.toString();
          }
          message_class = "wplc-admin-message wplc-color-bg-4 wplc-color-2 wplc-color-border-4";

          // If it is audio message
          isAudioPattern = audioPattern.test(the_message.msg);
          if (isAudioPattern) {
            message_class += " wplc-user-message-audio";
          }

          if (aoru === 'u') {
            /* message came from admin, intended for user */
            if (message_aid !== false && typeof wplc_agent_data !== "undefined" && typeof wplc_agent_data[message_aid] !== "undefined") {
              /* we know who the agent was that sent this message (v7.1.00+) */
              if (typeof wplc_show_chat_detail !== "undefined") {
                if (typeof wplc_show_chat_detail.avatar !== "undefined" && wplc_show_chat_detail.avatar === "1") {
                  message_grav = (typeof wplc_agent_data[message_aid].md5 !== "undefined" ? "<img src='" + wplc_user_avatars[message_aid] + "?s=80&d=mm' class='wplc-admin-message-avatar' />" : "");
                }
                if (typeof wplc_show_chat_detail.name !== "undefined" && wplc_show_chat_detail.name === "1") {
                  message_from = (typeof wplc_agent_data[message_aid].name !== "undefined" ? wplc_get_chat_person_name_msg_field(wplc_agent_data[message_aid].name) : "");
                }
              }
            } else {
              /* we do'nt know which agent sent this message, so lets set it as the current user instead (backwards compat) */
              if (typeof wplc_show_chat_detail !== "undefined") {
                if (typeof wplc_show_chat_detail.avatar !== "undefined" && wplc_show_chat_detail.avatar === "1") {
                  message_grav = (typeof wplc_current_agent.email !== "undefined" ? "<img src='" + wplc_user_avatars[message_aid] + "?s=80&d=mm' class='wplc-admin-message-avatar' />" : "");
                }
                if (typeof wplc_show_chat_detail.name !== "undefined" && wplc_show_chat_detail.name === "1") {
                  message_from = (typeof wplc_current_agent.name !== "undefined" ? wplc_get_chat_person_name_msg_field(wplc_current_agent.name) : "");
                }
              }
            }
          } else {
            if (message_aid !== false && typeof wplc_agent_data !== "undefined" && typeof wplc_agent_data[message_aid] !== "undefined") {
              /* we know who the agent was that sent this message (v7.1.00+) */
              if (typeof wplc_show_chat_detail !== "undefined") {
                if (typeof wplc_show_chat_detail.avatar !== "undefined" && wplc_show_chat_detail.avatar === "1") {
                  message_grav = (typeof wplc_agent_data[message_aid].md5 !== "undefined" ? "<img src='//www.gravatar.com/avatar/" + wplc_agent_data[message_aid].md5 + "?s=80&d=mm'  class='wplc-admin-message-avatar' />" : "");
                }
                if (typeof wplc_show_chat_detail.name !== "undefined" && wplc_show_chat_detail.name === "1") {
                  message_from = (typeof wplc_agent_data[message_aid].name !== "undefined" ? wplc_get_chat_person_name_msg_field(wplc_agent_data[message_aid].name) : "");
                }
              }
            } else {
              if (typeof wplc_show_chat_detail.avatar !== "undefined" && wplc_show_chat_detail.avatar === "1") {
                message_grav = (typeof wplc_admin_agent_email !== "undefined" ? "<img src='//www.gravatar.com/avatar/" + wplc_admin_agent_email + "?s=80&d=mm'  class='wplc-admin-message-avatar' />" : "");
              }
              if (typeof wplc_show_chat_detail.name !== "undefined" && wplc_show_chat_detail.name === "1") {
                message_from = (typeof wplc_admin_agent_name !== "undefined" ? wplc_get_chat_person_name_msg_field(wplc_admin_agent_name) : "");
              }
            }
          }

          message_content = the_message.msg;
          wplc_new_message_sound = true;
          break;
        }

      case 'system':
        {
          //System Notification
          message_class = "wplc-system-notification wplc-color-4";
          message_content = the_message.msg;
          if (typeof the_message.other.ntype !== "undefined") {
            if (the_message.other.ntype === "joined") {
              jQuery.event.trigger({ type: "wplc_agent_joined", ndata: the_message });
            }
          }
          break;
        }

      case 'user':
        {
          /* most likely from the user */
          message_class = "wplc-user-message wplc-color-bg-1 wplc-color-2 wplc-color-border-1";
          isAudioPattern = audioPattern.test(the_message.msg);
          if (isAudioPattern) {
            message_class += " wplc-user-message-audio";
          }

          if (aoru === 'u') {
            if (jQuery(wplc_email).val() !== "") {
              message_grav = md5(jQuery(wplc_email).val());
            } else {
              if (typeof visitor_list !== "undefined" && typeof visitor_list[active_chatid] !== "undefined" && typeof visitor_list[active_chatid].email !== "undefined") {
                message_grav = md5(visitor_list[active_chatid].email);
              } else {
                if (typeof wplc_cookie_email !== "undefined" && wplc_cookie_email !== "") {
                  message_grav = md5(wplc_cookie_email);
                } else {
                  message_grav = 'x';
                }
              }
            }

            message_grav = "<img src='//www.gravatar.com/avatar/" + message_grav + "?s=80&d=mm'  class='wplc-user-message-avatar' />";
            if (typeof Cookies.get("wplc_name") === "undefined") {
              message_from = wplc_get_chat_person_name_msg_field(config.wplc_user_default_visitor_name);
            } else {
              message_from = wplc_get_chat_person_name_msg_field(Cookies.get("wplc_name"));
            }
          } else {
            if (typeof wplc_chat_email !== "undefined") {
              message_grav = wplc_chat_email;
            } else {
              if (typeof visitor_list !== "undefined" && typeof visitor_list[active_chatid] !== "undefined" && typeof visitor_list[active_chatid].email !== "undefined") {
                message_grav = md5(visitor_list[active_chatid].email);
              } else {
                if (typeof wplc_cookie_email !== "undefined" && wplc_cookie_email !== "") {
                  message_grav = md5(wplc_cookie_email);
                } else {
                  message_grav = 'x';
                }
              }
            }
            message_grav = "<img src='//www.gravatar.com/avatar/" + message_grav + "?s=80&d=mm'  class='wplc-user-message-avatar' />";
            if (typeof wplc_chat_name !== "undefined") {
              message_from = wplc_get_chat_person_name_msg_field(wplc_chat_name);
            }
          }
          message_content = the_message.msg;
          break;
        }

    }

    if (typeof the_message.other !== "undefined" && typeof the_message.other.from_an_agent !== "undefined") {
      if (the_message.other.from_an_agent === true || the_message.other.from_an_agent === "true") {
        message_class += ' agent_to_agent';
      }
    }

    if (typeof the_message.afrom !== 'undefined' && typeof the_message.ato !== 'undefined') {
      if (the_message.afrom > 0 && the_message.ato > 0) {
        if (parseInt(agent_id) !== the_message.afrom) {
          message_class += ' agent_to_agent';
        }
      }
    }

    message_class += " message_" + the_message.mid;

    if (message_content !== "") {
      //message_content = wplc_sanitize_attributes(message_content);
      // If it is audio message
      isAudioPattern = audioPattern.test(message_content);

      // Open the HTML of a message
      var concatenated_message = "<span class='" + message_class + "' mid='" + the_message.mid + "'>";

      if (typeof wplc_show_chat_detail !== "undefined") {
        if (typeof wplc_show_chat_detail.avatar !== "undefined" && wplc_show_chat_detail.avatar === "1") {
          concatenated_message += message_grav;
        }

        // Add a wrapper for the person name and the message, this wrapper is necessary to implement the UI of the admin chat	
        if (isAudioPattern) {
          concatenated_message += "<div class='wplc-msg-content wplc-msg-content-audio' mid='" + the_message.mid + "'>";
        } else {
          concatenated_message += "<div class='wplc-msg-content' mid='" + the_message.mid + "'>";
        }

        if (isAudioPattern) {
          concatenated_message += "<span class='wplc-msg-content-audio-icon'></span>";
        } else if (typeof wplc_show_chat_detail.name !== "undefined" && wplc_show_chat_detail.name === "1") {
          concatenated_message += message_from;
        }
      } else {
        // Add a wrapper for the person name and the message, this wrapper is necessary to implement the UI of the admin chat	
        concatenated_message += "<div class='wplc-msg-content'>";
      }

      if (isAudioPattern) {
        message_content = "<a href='" + message_content + "' target='_blank'>" + (typeof wplc_visitor_voice !== 'undefined' && typeof wplc_visitor_voice.play_sound !== 'undefined' ? wplc_visitor_voice.play_sound : 'Open Voice Note') + "</a>";
      } else {
        if (!!the_message.decorateurl) {
          message_content = wp_url_decorator(message_content); // only decorate urls, assume html is already escaped
        } else {
          if (!!the_message.other && the_message.other.preformatted) {
            // if message is preformatted, do nothing, escaping is up to formatting routine
          } else {
            message_content = wplcFormatParser(message_content); // does all parsing
          }
        }
      }

      // If it is a GIF message
      if (message_content.match(gifExtensionPattern)) {
        concatenated_message += "<span class='messageBody'><img src='" + wplc_get_clean_gifurl(message_content) + "' class='gif-img'/></span>";
      } else if (isAudioPattern) {
        // If it is audio pattern
        concatenated_message += "<span class='messageBody'>" + message_content + "</span>";
      } else {
        // If it is a regular message
        concatenated_message += "<span class='messageBody'>" + message_content + "</span>";
      }

      // Close the person name/message wrapper, if it was added
      concatenated_message += "</div>";

      // Close the HTML of a message
      concatenated_message += "</span>";
      concatenated_message += wplc_add_date_and_time(the_message, the_message.originates);

      if (aoru === 'u') {
        wplc_chat_box_elemn = "#wplc_chatbox";
      } else {
        if (config.wplc_use_node_server) {
          wplc_chat_box_elemn = "#messages";
        } else {
          wplc_chat_box_elemn = "#admin_chat_box_area_" + wplc_cid;
        }
      }

      jQuery(wplc_chat_box_elemn).append(concatenated_message);
    }
  }
  next();
}

jQuery(function() {
  jQuery(function() {
    var wplc_node_searchTimeout;

    jQuery("body").on("keydown", "#wplc_chatmsg", function(e) {
      if (e.which == 13 && jQuery.trim(document.getElementById('wplc_chatmsg').value) == '') {
        // sink this event
        return false;
      }
    });

    jQuery("body").on("keydown", "#wplc_chatmsg, #wplc_admin_chatmsg", function(e) {
      if (typeof wplc_node_sockets_ready !== "undefined" && wplc_node_sockets_ready === true) {
        if (typeof wplc_node_is_client_typing !== "undefined") {
          if (e.which <= 90 && e.which >= 48) {
            if (wplc_node_is_client_typing) {
              wplc_node_renew_typing();
              return;
            }
            wplc_node_is_client_typing = true;

            wplc_node_searchTimeout = setTimeout(wplc_node_clear_typing, 1000);
          }
        }
      }
    });

    jQuery("body").on("click", "#wplc_na_msg_btn", function() {
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
      var wplc_msg = jQuery("#wplc_message").val();
      var wplc_domain = jQuery("#wplc_domain_offline").val();
      var ip_address = jQuery("#wplc_ip_address").val();

      if (wplc_name.length <= 0) { alert(wplc_error_messages.please_enter_name); return false; }
      if (wplc_email.length <= 0) { alert(wplc_error_messages.please_enter_email); return false; }
      var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,12}$/i;
      if (!testEmail.test(wplc_email)) {
        alert(wplc_error_messages.please_enter_valid_email);
        return false;
      }
      if (wplc_msg.length <= 0) { alert(wplc_error_messages.empty_message); return false; }
      jQuery("#wp-live-chat.classic #wp-live-chat-2-info").hide();
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
        domain: wplc_domain,
        wplc_extra_data: wplc_extra_data
      };

      jQuery.post(wplc_ajaxurl_site, data, function(response) {
        jQuery("#wplc_message_div").html(wplc_offline_msg3);
      });
      if (typeof wplc_enable_ga !== "undefined" && wplc_enable_ga === '1') {
        if (typeof ga !== "undefined") {
          ga('send', {
            hitType: 'event',
            eventCategory: 'WP_Live_Chat_Support',
            eventAction: 'Event',
            eventLabel: 'User Send Offline Message'
          });
        }
      }
    });


    function wplc_node_renew_typing() {
      clearTimeout(wplc_node_searchTimeout);
      wplc_node_searchTimeout = setTimeout(wplc_node_clear_typing, 1000);
    }

    function wplc_node_clear_typing() {
      wplc_node_is_client_typing = false;
      clearTimeout(wplc_node_searchTimeout);
    }
  });
});

var wplc_generate_system_notification_object = function(msg, other, originates) {
  the_message = {};
  the_message.originates = originates;
  the_message.msg = msg;
  the_message.other = other;
  var wplc_d = new Date();
  the_message.other.datetime = Math.round(wplc_d.getTime() / 1000);
  return the_message;
}

function wplc_display_system_notification(message) {
  message_output = '<div id="tcx_notification_message" class="wplc-color-bg-1 wplc-color-2">' + message.msg + '</div>';

  if (jQuery('#tcx_notification_message').length == 0) {
    jQuery('#wp-live-chat-4').prepend(message_output);
  }
}

function wplc_clear_system_notification() {
  if (jQuery('#tcx_notification_message').length !== 0) {
    jQuery('#tcx_notification_message').remove();
  }
}