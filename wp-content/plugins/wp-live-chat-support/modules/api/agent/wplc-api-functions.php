<?php
/* Handles all functions related to the WP Live Chat Support API */

if (!defined('ABSPATH')) {
  exit;
}


/*
 * Accepts a chat within the WP Live Chat Support Dashboard
 * Required GET/POST variables:
 * - Token 
 * - Chat ID
 * - Agent ID 
*/
function wplc_api_accept_chat(WP_REST_Request $request) {

  $return_array = array();
  if (isset($request)) {
    if (isset($request['token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {
        if (isset($request['chat_id'])) {
          if (isset($request['agent_id'])) {
            $cid = wplc_return_chat_id_by_rel_or_id($request['chat_id']);
            if (wplc_change_chat_status($cid, 3, intval($request['agent_id']))) {
              do_action("wplc_hook_update_agent_id", sanitize_text_field($request['cid']), intval($request['agent_id']));
              $return_array['response'] = "Chat accepted successfully";
              $return_array['code'] = "200";
              $return_array['data'] = array(
                "chat_id" => intval($request['chat_id']),
                "agent_id" => intval($request['agent_id'])
              );
            } else {
              $return_array['response'] = "Status could not be changed";
              $return_array['code'] = "404";
            }
          } else {
            $return_array['response'] = "No 'agent_id' found";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "token" => "YOUR_SECRET_TOKEN",
              "chat_id"   => "Chat ID",
              "agent_id"   => "Agent ID"
            );
          }
        } else {
          $return_array['response'] = "No 'chat_id' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "token" => "YOUR_SECRET_TOKEN",
            "chat_id"   => "Chat ID",
            "agent_id"   => "Agent ID"
          );
        }
      } else {
        $return_array['response'] = "Secret token is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No secret 'token' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "token" => "YOUR_SECRET_TOKEN",
        "chat_id"   => "Chat ID",
        "agent_id"   => "Agent ID"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "token" => "YOUR_SECRET_TOKEN",
      "chat_id"   => "Chat ID",
      "agent_id"   => "Agent ID"
    );
  }

  return $return_array;
}

/*
 * Ends a chat within the WP Live Chat Support Dashboard
 * Required GET/POST variables:
 * - Token 
 * - Chat ID
 * - Agent ID 
*/
function wplc_api_agent_end_chat(WP_REST_Request $request) {

  $return_array = array();
  if (isset($request)) {
    if (isset($request['token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {
        if (isset($request['chat_id'])) {
          if (isset($request['agent_id'])) {
            $cid = wplc_return_chat_id_by_rel_or_id($request['chat_id']);
            if (wplc_change_chat_status($cid, 1, intval($request['agent_id']))) {

              do_action('wplc_send_transcript_hook', $cid);

              $return_array['response'] = "Chat ended successfully";
              $return_array['code'] = "200";
              $return_array['data'] = array(
                "chat_id" => $cid,
                "agent_id" => intval($request['agent_id'])
              );
            } else {
              $return_array['response'] = "Status could not be changed";
              $return_array['code'] = "404";
            }
          } else {
            $return_array['response'] = "No 'agent_id' found";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "token" => "YOUR_SECRET_TOKEN",
              "chat_id"   => "Chat ID",
              "agent_id"   => "Agent ID"
            );
          }
        } else {
          $return_array['response'] = "No 'chat_id' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "token" => "YOUR_SECRET_TOKEN",
            "chat_id"   => "Chat ID",
            "agent_id"   => "Agent ID"
          );
        }
      } else {
        $return_array['response'] = "Secret token is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No secret 'token' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "token" => "YOUR_SECRET_TOKEN",
        "chat_id"   => "Chat ID",
        "agent_id"   => "Agent ID"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "token" => "YOUR_SECRET_TOKEN",
      "chat_id"   => "Chat ID",
      "agent_id"   => "Agent ID"
    );
  }

  return $return_array;
}



/*
 * Send a message to a chat within the WP Live Chat Support Dashboard
 * Required GET/POST variables:
 * - Token 
 * - Chat ID
 * - Message
*/
function wplc_api_agent_send_message(WP_REST_Request $request) {

  $return_array = array();
  if (isset($request)) {
    if (isset($request['server_token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['server_token'] === $check_token) {
        if (isset($request['chat_id'])) {
          if (isset($request['message'])) {
            if (isset($request['relay_action'])) {

              $chat_id = sanitize_text_field($request['chat_id']);
              $message = $request['message'];
              $action = $request['relay_action'];

              if (!empty($request['msg_id'])) {
                $other = new stdClass();
                $other->msgID = $request['msg_id'];
              } else {
                $other = false;
              }

              if ($action == "wplc_admin_send_msg") {
                wplc_record_chat_msg("2", $chat_id, $message, true, sanitize_text_field($request['agent_id']), $other);
                wplc_update_active_timestamp($chat_id);

                $return_array['response'] = "Message sent successfully";
                $return_array['code'] = "200";
                $return_array['data'] = array(
                  "chat_id" => sanitize_text_field($request['chat_id']),
                  "agent_id" => intval($request['agent_id'])
                );

                do_action("wplc_new_user_message_after_record_hook", $chat_id, $message);
              }
            } else {
              $return_array['request_information'] = __("Action not set", 'wp-live-chat-support');
            }
          } else {
            $return_array['response'] = "No 'message' found";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "server_token" => "YOUR_SECRET_TOKEN",
              "chat_id"   => "Chat ID",
              "message" => "Message"
            );
          }
        } else {
          $return_array['response'] = "No 'chat_id' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "server_token" => "YOUR_SECRET_TOKEN",
            "chat_id"   => "Chat ID",
            "message" => "Message"
          );
        }
      } else {
        $return_array['response'] = "Secret server_token is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No secret 'server_token' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "server_token" => "YOUR_SECRET_TOKEN",
        "chat_id"   => "Chat ID",
        "message" => "Message"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "server_token" => "YOUR_SECRET_TOKEN",
      "chat_id"   => "Chat ID",
      "message" => "Message"
    );
  }

  return $return_array;
}



/*
 * Fetch a chat status within the WP Live Chat Support Dashboard
 * Required GET/POST variables:
 * - Token 
 * - Chat ID
 * Optional:
 * - Limit (Defaults to 50/Max Limit of 50)
 * - Offset (Defaults to 0)
*/
function wplc_api_agent_get_messages(WP_REST_Request $request) {

  $return_array = array();
  if (isset($request)) {
    if (isset($request['token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {
        if (isset($request['chat_id'])) {
          $limit = 50;
          $offset = 0;
          if (isset($request['limit'])) {
            $limit = intval($request['limit']);
          }
          if (isset($request['offset'])) {
            $offset = intval($request['offset']);
          }

          if (isset($request['received_via'])) {
            $received_via = sanitize_text_field($request['received_via']);
          } else {
            $received_via = 'u';
          }

          $message_data = wplc_api_return_messages($request['chat_id'], $limit, $offset, $received_via);

          if ($message_data) {
            $return_array['response'] = "Message data returned";
            $return_array['code'] = "200";
            $return_array['data'] = array("messages" => $message_data);
          } else {
            $return_array['response'] = "Messages not found";
            $return_array['code'] = "404";
            $return_array['data'] = array("chat_id" => intval($request['chat_id']));
          }
        } else {
          $return_array['response'] = "No 'chat_id' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "token" => "YOUR_SECRET_TOKEN",
            "chat_id"   => "Chat ID"
          );
        }
      } else {
        $return_array['response'] = "Secret token is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No secret 'token' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "token" => "YOUR_SECRET_TOKEN",
        "chat_id"   => "Chat ID"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "token" => "YOUR_SECRET_TOKEN",
      "chat_id"   => "Chat ID"
    );
  }

  return $return_array;
}


/*
 * Records an admin message via the API
*/
function wplc_api_agent_record_admin_message($cid, $msg) {

  global $wpdb;
  global $wplc_tblname_msgs;

  $fromname = apply_filters("wplc_filter_admin_name", "Admin");
  $orig = '1';

  $msg = apply_filters("wplc_filter_message_control", $msg);

  $wpdb->insert(
    $wplc_tblname_msgs,
    array(
      'chat_sess_id' => $cid,
      'timestamp' => current_time('mysql'),
      'msgfrom' => $fromname,
      'msg' => $msg,
      'status' => 0,
      'originates' => $orig
    ),
    array(
      '%s',
      '%s',
      '%s',
      '%s',
      '%d',
      '%s'
    )
  );

  wplc_update_active_timestamp(sanitize_text_field($cid));
  wplc_change_chat_status(sanitize_text_field($cid), 3);

  return true;
}

/*
 * Returns messages from server
*/
function wplc_api_agent_return_messages($cid, $limit, $offset, $received_via = 'u') {
  $cid = wplc_return_chat_id_by_rel_or_id($cid);
  $messages = wplc_return_chat_messages($cid, false, true, false, false, 'array', false);
  if ($received_via === 'u') {
    wplc_mark_as_read_user_chat_messages($cid);
  } else {
    wplc_mark_as_read_agent_chat_messages($cid, $received_via);
  }
  return $messages;
}


function wplc_api_agent_return_sessions() {
  global $wpdb;
  global $wplc_tblname_chats;

  $results = $wpdb->get_results("SELECT * FROM $wplc_tblname_chats WHERE `status` = 3 OR `status` = 2 OR `status` = 10 OR `status` = 5 or `status` = 8 or `status` = 9 ORDER BY `timestamp` ASC");

  $session_array = array();

  if ($results) {
    foreach ($results as $result) {
      $ip_info = maybe_unserialize($result->ip);
      $user_ip = $ip_info['ip'];
      if ($user_ip == "") {
        $user_ip = __('IP Address not recorded', 'wp-live-chat-support');
      }

      $browser = 'Unknown';
      $browser_image = '';
      if (!empty($ip_info['user_agent'])) {
        $browser = wplc_return_browser_string($ip_info['user_agent']);
        $browser_image = wplc_return_browser_image($browser, "16");
      }


      $session_array[$result->id] = array();

      $session_array[$result->id]['name'] = $result->name;
      $session_array[$result->id]['email'] = $result->email;

      $session_array[$result->id]['status'] = $result->status;
      $session_array[$result->id]['timestamp'] = wplc_time_ago($result->timestamp);

      if ((current_time('timestamp') - strtotime($result->timestamp)) < 3600) {
        $session_array[$result->id]['type'] = __("New", 'wp-live-chat-support');
      } else {
        $session_array[$result->id]['type'] = __("Returning", 'wp-live-chat-support');
      }

      $session_array[$result->id]['image'] = "//www.gravatar.com/avatar/" . md5($result->email) . "?s=30&d=mm";
      $session_array[$result->id]['data']['browsing'] = $result->url;
      $path = parse_url($result->url, PHP_URL_PATH);

      if (strlen($path) > 20) {
        $session_array[$result->id]['data']['browsing_nice_url'] = substr($path, 0, 20) . '...';
      } else {
        $session_array[$result->id]['data']['browsing_nice_url'] = $path;
      }

      $session_array[$result->id]['data']['browser'] = WPLC_PLUGIN_URL . "images/$browser_image";
      $session_array[$result->id]['data']['ip'] = $user_ip;
    }
  }

  return $session_array;
}


/*
 * Function Removed: wplc_api_call_to_server_visitor
 * Reason: Not in use unless manual override of AJAX path is added
 * This is not possible for users, and was purely a conceptual piece of code
*/

/*
 * Upload end point
*/
function wplc_api_remote_upload(WP_REST_Request $request) {
  $return_array = array();
  $return_array['response'] = 'false';
  $return_array = apply_filters("wplc_api_remote_upload_filter", $return_array, $request);
  return $return_array;
}

/*
 * Rest Permission check for restricted end points
*/
function wplc_api_agent_permission_check() {
  if (is_user_logged_in() && wplc_user_is_agent()) {
    return true;
  } else {
    return false;
  }
}

function wplc_agent_validate_agent_check(WP_REST_Request $request) {
  $return_array = array();
  if (isset($request)) {
    if (isset($request['agent_id'])) {
      if (wplc_user_is_agent(intval($request['agent_id']))) {
        $return_array['response'] = "true";
        $return_array['code'] = "200";
      } else {
        $return_array['response'] = "false";
        $return_array['code'] = "200";
      }
    } else {
      $return_array['response'] = "No Agent ID found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array("agent_id" => "Agent ID");
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array("agent_id" => "Agent ID");
  }

  return $return_array;
}


# PRO API FUNCTIONS
function wplc_api_send_agent_message_mrg(WP_REST_Request $request) {
  $return_array = array();
  if (isset($request)) {
    if (isset($request['server_token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['server_token'] === $check_token) {
        if (isset($request['chat_id'])) {
          if (isset($request['message'])) {
            if (isset($request['relay_action'])) {

              $chat_id = sanitize_text_field($request['chat_id']);
              $message = $request['message'];
              $action = $request['relay_action'];

              if (!empty($request['msg_id'])) {
                $other = new stdClass();
                $other->msgID = $request['msg_id'];
              } else {
                $other = false;
              }

              if ($action == "wplc_admin_send_msg") {
                $message = sanitize_text_field($message);
                $ato = intval($request['ato']);
                if (isset($request['orig_override'])) {
                  wplc_api_record_agent_chat_msg_mrg(sanitize_text_field($request['agent_id']), $chat_id, $message, true, $ato, $other, $request['orig_override']);
                } else {
                  wplc_api_record_agent_chat_msg_mrg(sanitize_text_field($request['agent_id']), $chat_id, $message, true, $ato, $other);
                }
                wplc_update_active_timestamp($chat_id);

                $return_array['response'] = "Message sent successfully";
                $return_array['code'] = "200";
                $return_array['data'] = array(
                  "chat_id" => intval($request['chat_id']),
                  "agent_id" => intval($request['agent_id'])
                );

                do_action("wplc_new_user_message_after_record_hook", $chat_id, $message);
              }
            } else {
              $return_array['request_information'] = __("Action not set", 'wp-live-chat-support');
            }
          } else {
            $return_array['response'] = "No 'message' found";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "server_token" => "YOUR_SECRET_TOKEN",
              "chat_id"   => "Chat ID",
              "message" => "Message"
            );
          }
        } else {
          $return_array['response'] = "No 'chat_id' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "server_token" => "YOUR_SECRET_TOKEN",
            "chat_id"   => "Chat ID",
            "message" => "Message"
          );
        }
      } else {
        $return_array['response'] = "Secret server_token is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No secret 'server_token' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "server_token" => "YOUR_SECRET_TOKEN",
        "chat_id"   => "Chat ID",
        "message" => "Message"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "server_token" => "YOUR_SECRET_TOKEN",
      "chat_id"   => "Chat ID",
      "message" => "Message"
    );
  }

  return $return_array;
}



function wplc_api_get_agent_unread_message_counts_mrg(WP_REST_Request $request) {
  $return_array = array();
  if (isset($request)) {
    if (isset($request['token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {

        $current_agent = intval(sanitize_text_field($request['agent_id']));

        /**
         * Get all agents
         * @var [type]
         */
        $user_array = wplc_get_agent_users();
        $a_array = array();
        if ($user_array) {
          foreach ($user_array as $user) {
            $unread = wplc_return_unread_agent_messages_mrg($current_agent, $user->ID);
            $a_array[$user->ID] = $unread;
          }
        }
        $return_array['response'] = "Unread count agents"; /* needs to be exactly this for the JS to fire correctly */
        $return_array['code'] = "200";
        $return_array['data'] = $a_array;
      } else {
        $return_array['response'] = "Secret token is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No secret 'token' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array("token" => "YOUR_SECRET_TOKEN");
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array("token" => "YOUR_SECRET_TOKEN");
  }

  return $return_array;
}

function wplc_api_initiate_chat_mrg(WP_REST_REQUEST $request) {
  $wplc_settings = wplc_get_options();
  $return_array = array();
  if (isset($request)) {
    if (isset($request['security'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {

        if (isset($request['rel']) || isset($request['cid'])) {

          if (isset($request['aid'])) {

            $aid = intval($request['aid']);

            if (isset($request['rel'])) {
              $cid = $request['rel'];
            } else {
              $cid = $request['cid'];
            }


            global $wplc_tblname_chats;
            global $wpdb;
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `rel` = %s OR `id` = %s LIMIT 1", $cid, $cid));
            if (!$results) {
              /* it doesnt exist, lets put it in the table */

              $wpdb->insert(
                $wplc_tblname_chats,
                array(
                  'status' => 3,
                  'timestamp' => current_time('mysql'),
                  'name' => wplc_get_user_name('', $wplc_settings),
                  'email' => 'none',
                  'session' => '1',
                  'ip' => wplc_get_user_ip(),
                  'url' => '',
                  'last_active_timestamp' => current_time('mysql'),
                  'other' => '',
                  'agent_id' => $aid,
                  'rel' => $cid,
                ),
                array(
                  '%s',
                  '%s',
                  '%s',
                  '%s',
                  '%s',
                  '%s',
                  '%s',
                  '%s',
                  '%s',
                  '%d',
                  '%s'
                )
              );


              $cid = $wpdb->insert_id;
              do_action("wplc_hook_update_agent_id", $cid, $aid);
            }
          } else {
            $return_array['response'] = "No 'AID' found (base64 encoded)";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "security" => "YOUR_SECRET_TOKEN",
              "cid"   => "Chat ID",
              "aid"   => "agent ID"
            );
          }
        } else {
          $return_array['response'] = "No 'REL' or 'CID' found (base64 encoded)";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "security" => "YOUR_SECRET_TOKEN",
            "cid"   => "Chat ID",
            "rel/cid"   => "related ID or Chat ID"
          );
        }
      } else {
        $return_array['response'] = "Nonce is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No 'security' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "security" => "YOUR_SECRET_TOKEN",
        "cid"   => "Chat ID",
        "wplc_extra_data"   => "Data array"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "security" => "YOUR_SECRET_TOKEN",
      "cid"   => "Chat ID",
      "wplc_extra_data"   => "Data array"
    );
  }

  return $return_array;
}

function wplc_api_agent_email_notification_mrg(WP_REST_Request $request) { // TODO: this function is never referenced
  $return_array = array();
  if (isset($request)) {
    if (isset($request['security'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['security'] === $check_token) {
        if (isset($request['cid'])) {
          if (isset($request['wplc_extra_data'])) {
            // TODO: this code does nothing useful
            $data = $request['wplc_extra_data'];
            $wplc_settings = wplc_get_options();
            $email_address = wplc_determine_admin_email($wplc_settings);
            return true;
          } else {
            $return_array['response'] = "No 'Data' array found (base64 encoded)";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "security" => "YOUR_SECRET_TOKEN",
              "cid"   => "Chat ID",
              "wplc_extra_data"   => "Data array"
            );
          }
        } else {
          $return_array['response'] = "No 'CID' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "security" => "YOUR_SECRET_TOKEN",
            "cid"   => "Chat ID",
            "wplc_extra_data"   => "Data array"
          );
        }
      } else {
        $return_array['response'] = "Nonce is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No 'security' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "security" => "YOUR_SECRET_TOKEN",
        "cid"   => "Chat ID",
        "wplc_extra_data"   => "Data array"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "security" => "YOUR_SECRET_TOKEN",
      "cid"   => "Chat ID",
      "wplc_extra_data"   => "Data array"
    );
  }
  return $return_array;
}

function wplc_api_agent_is_typing_mrg(WP_REST_Request $request) {

  $return_array = array();
  if (isset($request)) {
    if (isset($request['security'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['security'] === $check_token) {
        if (isset($request['cid'])) {
          if (isset($request['user'])) {
            if (isset($request['type'])) {
              if (wplc_typing_mrg($request['user'], sanitize_text_field($request['cid']), sanitize_text_field($request['type']))) {

                $return_array['response'] = "Successful";
                $return_array['code'] = "200";
                $return_array['data'] = array(
                  "cid" => intval($request['cid']),
                  "user" => intval($request['user']),
                  "type" => intval($request['type'])
                );
              } else {
                $return_array['response'] = "Failed to send typing indicaator";
                $return_array['code'] = "401";
                $return_array['requirements'] = array(
                  "security" => "YOUR_SECRET_TOKEN",
                  "cid"   => "Chat ID",
                  "user"   => "User type",
                  'type' => "TYPE"
                );
              }
            } else {

              $return_array['response'] = "No 'type' found";
              $return_array['code'] = "401";
              $return_array['requirements'] = array(
                "security" => "YOUR_SECRET_TOKEN",
                "cid"   => "Chat ID",
                "user"   => "User type",
                'type' => "TYPE"
              );
            }
          } else {
            $return_array['response'] = "No 'user' found";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "security" => "YOUR_SECRET_TOKEN",
              "cid"   => "Chat ID",
              "user"   => "User type",
              'type' => "TYPE"
            );
          }
        } else {
          $return_array['response'] = "No 'cid' found";
          $return_array['code'] = "401";
          $return_array['requirements'] = array(
            "security" => "YOUR_SECRET_TOKEN",
            "cid"   => "Chat ID",
            "user"   => "User type",
            'type' => "TYPE"
          );
        }
      } else {
        $return_array['response'] = "Nonce is invalid";
        $return_array['code'] = "401";
      }
    } else {
      $return_array['response'] = "No 'security' found";
      $return_array['code'] = "401";
      $return_array['requirements'] = array(
        "security" => "YOUR_SECRET_TOKEN",
        "cid"   => "Chat ID",
        "user"   => "User type",
        'type' => "TYPE"
      );
    }
  } else {
    $return_array['response'] = "No request data found";
    $return_array['code'] = "400";
    $return_array['requirements'] = array(
      "security" => "YOUR_SECRET_TOKEN",
      "cid"   => "Chat ID",
      "user"   => "User type",
      'type' => "TYPE"
    );
  }

  return $return_array;
}


function wplc_api_record_agent_chat_msg_mrg($from, $cid, $msg, $rest_check = false, $ato = false, $other = false, $orig_override = false) {
  global $wpdb;
  global $wplc_tblname_msgs;

  $cid = wplc_return_chat_id_by_rel_or_id($cid);
  /**
   * check if this CID even exists, if not, create it
   *
   * If it doesnt exist, it most likely is an agent-to-agent chat that we now need to save.
   */

  global $wplc_tblname_chats;
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `rel` = %s OR `id` = %s LIMIT 1", $cid, $cid));
  if (!$results) {
    /* it doesnt exist, lets put it in the table */

    $wpdb->insert(
      $wplc_tblname_chats,
      array(
        'status' => 3,
        'timestamp' => current_time('mysql'),
        'name' => 'agent-to-agent chat',
        'email' => 'none',
        'session' => '1',
        'ip' => wplc_get_user_ip(),
        'url' => '',
        'last_active_timestamp' => current_time('mysql'),
        'other' => '',
        'rel' => $cid,
      ),
      array(
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
      )
    );


    $cid = $wpdb->insert_id;
  }

  $msg_id = '';
  if ($other !== false) {
    if (!empty($other->msgID)) {
      $msg_id = $other->msgID;
    } else {
      $msg_id = '';
    }
  }

  $user_info = get_userdata($from);
  if ($user_info) {
    $fromname = $user_info->display_name;
  } else {
    $fromname = 'agent';
  }
  $orig = '1';
  if ($orig_override !== false) {
    $orig = sanitize_text_field($orig_override);
  }

  $orig_msg = $msg;

  $msg = apply_filters("wplc_filter_message_control", $msg);


  $wpdb->insert(
    $wplc_tblname_msgs,
    array(
      'chat_sess_id' => $cid,
      'timestamp' => current_time('mysql'),
      'msgfrom' => $fromname,
      'msg' => $msg,
      'status' => 0,
      'originates' => $orig,
      'other' => '',
      'rel' => $msg_id,
      'ato' => $ato,
      'afrom' => $from
    ),
    array(
      '%s',
      '%s',
      '%s',
      '%s',
      '%d',
      '%s',
      '%s',
      '%s',
      '%d',
      '%d'
    )
  );

  $data = array(
    'cid' => $cid,
    'from' => $from,
    'msg' => $orig_msg,
    'orig' => $orig
  );
  //do_action("wplc_hook_message_sent",$data);

  wplc_update_active_timestamp(sanitize_text_field($cid));


  return true;
}

add_filter("wplc_api_remote_upload_filter", "wplc_api_agent_remote_upload_handler_mrg", 10, 2);
/*
 * Processes remote uploads, from app or main agent files as an example
*/
function wplc_api_agent_remote_upload_handler_mrg($return_array, $request) {
  $remote_files = $request->get_file_params();
  if (is_array($remote_files)) {
    $upload_dir = wp_upload_dir();
    $user_dirname = $upload_dir['basedir'];
    $cid = 0;
    if (isset($request['cid'])) {
      $cid = wplc_return_chat_id_by_rel_or_id($request['cid']);
    }
    if (!file_exists($user_dirname . "/wp_live_chat/")) {
      @mkdir($user_dirname . '/wp_live_chat/');
    }
    if (!realpath($user_dirname . "/wp_live_chat/" . $cid)) {
      @mkdir($user_dirname . '/wp_live_chat/' . $cid);
    }
    if (isset($remote_files['file'])) {
      $file_name = strtolower(sanitize_file_name($remote_files['file']['name']));
      $file_name = basename($file_name); //This prevents traversal
      $file_name = str_replace(" ", "_", $file_name);
      if (wplc_check_file_name_for_safe_extension($file_name)) {
        if (realpath($user_dirname . "/wp_live_chat/" . $cid . "/" .  $file_name) == false) {
          $file_name = rand(0, 10) . "-" . $file_name;
        }
        if (move_uploaded_file($remote_files['file']['tmp_name'], $user_dirname . "/wp_live_chat/" . $cid . "/" . $file_name)) {
          $response = $upload_dir['baseurl'] . "/wp_live_chat/" . $cid  . "/" . $file_name;
          $return_array['response'] = wp_filter_post_kses(strip_tags($response));
        } else {
          $return_array['response'] = __('Upload error', 'wp-live-chat-support');
        }
      } else {
        $return_array['response'] = __('Security Violation - File unsafe', 'wp-live-chat-support');
      }
    } else {
      $return_array['response'] = '0';
    }
  } else {
    $return_array['response'] = '0';
  }
  return $return_array;
}

/**
 * Cleanup all REST Params
 */
function wplc_api_agent_sanitize_request_params(WP_REST_Request $request) {
  global $wpdb;

  $params = $request->get_params();
  foreach ($params as $key => $value) {
    if (is_string($value)) {
      $request->set_param($key, $wpdb->_real_escape($value));
    }
  }

  return $request;
}