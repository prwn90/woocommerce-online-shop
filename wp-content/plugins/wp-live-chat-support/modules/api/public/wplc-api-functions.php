<?php
/* Handles all functions related to the WP Live Chat Support API */

if (!defined('ABSPATH')) {
  exit;
}


/*
 * Ends a chat within the WP Live Chat Support Dashboard
 * Required GET/POST variables:
 * - Token 
 * - Chat ID
 * - Agent ID 
*/
function wplc_api_end_chat(WP_REST_Request $request)
{

  $return_array = array();
  if (isset($request)) {
    if (isset($request['token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {
        if (isset($request['chat_id'])) {

          if (wplc_check_user_request($request['chat_id'])) {

            if (isset($request['agent_id'])) {
              $cid = wplc_return_chat_id_by_rel_or_id($request['chat_id']);
              if (wplc_change_chat_status($cid, 1, intval($request['agent_id']))) {
                do_action("wplc_end_session_chat_id");
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
            $return_array['response'] = "'chat_id' tampering detected";
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
function wplc_api_send_message(WP_REST_Request $request)
{

  $return_array = array();
  if (isset($request)) {
    if (isset($request['server_token'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['server_token'] === $check_token) {
        if (isset($request['chat_id'])) {

          if (wplc_check_user_request($request['chat_id'])) {

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

                if ($action == "wplc_user_send_msg") {
                  wplc_record_chat_msg("1", $chat_id, $message, false, false, $other);
                  wplc_update_active_timestamp($chat_id);
                  set_transient("wplc_user_active_" . wplc_get_user_ip(), 1, 60);
                  $return_array['response'] = "Message sent successfully";
                  $return_array['code'] = "200";
                  $return_array['data'] = array(
                    "chat_id" => intval($request['chat_id']),
                    "agent_id" => intval($request['agent_id'])
                  );

                  do_action("wplc_new_user_message_after_record_hook", $chat_id, $message);
                } else if ($action == "wplc_admin_send_msg") {
                  wplc_record_chat_msg("2", $chat_id, $message, true, sanitize_text_field($request['agent_id']), $other);
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
            error_log('wplc_api_send_message tamper detected '.$request['chat_id']);
            $return_array['response'] = "'chat_id' tamper detected";
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
 * Validate user's request
*/
function wplc_check_user_request($cid)
{
  wplc_start_session();
  if (isset($_SESSION['wplc_session_chat_session_id'])) {
    $sid = $_SESSION['wplc_session_chat_session_id'];
  }
  wplc_close_session();
  if (isset($sid)) {
    $g_chat_id = wplc_return_chat_id_by_rel_or_id($cid);
    if (intval($sid) === $g_chat_id) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
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
function wplc_api_get_messages(WP_REST_Request $request)
{

  $return_array = array();
  if (isset($request)) {
    if (isset($request['token'])) {

      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['token'] === $check_token) {

        if (isset($request['chat_id'])) {

          if (wplc_check_user_request($request['chat_id'])) {

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
            $return_array['response'] = "'chat_id' tampering detected";
            $return_array['code'] = "401";
            $return_array['requirements'] = array(
              "token" => "YOUR_SECRET_TOKEN",
              "chat_id"   => "Chat ID"
            );
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
 * Returns messages from server
*/
function wplc_api_return_messages($cid, $limit, $offset, $received_via = 'u')
{
  $cid = wplc_return_chat_id_by_rel_or_id($cid);
  $messages = wplc_return_chat_messages($cid, false, true, false, false, 'array', false);
  if ($received_via === 'u') {
    wplc_mark_as_read_user_chat_messages($cid);
  } else {
    wplc_mark_as_read_agent_chat_messages($cid, $received_via);
  }
  return $messages;
}


function wplc_rate_limit_check()
{

  $is_user_active = get_transient("wplc_user_active_" . wplc_get_user_ip());

  if ($is_user_active) {
    return false;
  }

  return true;
}


/**
 * Starts a chat 
 * @param  $name string <Visitors Name>
 * @param  $email string <Visitors Email Address>
 * @param  $session string <Visitors Session ID>
 * @param  $wplc_cid int <Current visitor chat ID>
 * @return $return_array array
 */
function wplc_api_call_start_chat(WP_REST_Request $request)
{
  wplc_start_session();
  if (isset($_SESSION['wplc_session_chat_session_id'])) {
    $sid = $_SESSION['wplc_session_chat_session_id'];
  }

  if (!isset($sid) && wplc_rate_limit_check()) {
    $return_array = array();

    if (isset($request)) {

      if (isset($request['server_token'])) {

        if (isset($request['wplc_name']) && isset($request['wplc_email']) && isset($request['session'])) {
          $cid = !empty($request['cid']) ? $request['cid'] : 0;
          $name = substr(strip_tags(sanitize_text_field($request['wplc_name'])), 0, 40);
          $email = substr(strip_tags(sanitize_text_field($request['wplc_email'])), 0, 40);

          global $wpdb;
          global $wplc_tblname_chats;

          $user_data = array(
            'ip' => wplc_get_user_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
          );
          $other_data = array();
          $other_data['unanswered'] = true;

          $other_data = apply_filters("wplc_start_chat_hook_other_data_hook", $other_data);

          $wpdb->insert(
            $wplc_tblname_chats,
            array(
              'status' => 2,
              'timestamp' => current_time('mysql'),
              'name' => $name,
              'email' => $email,
              'session' => $request['session'],
              'ip' => maybe_serialize($user_data),
              'url' => $request['url'],
              'last_active_timestamp' => current_time('mysql'),
              'other' => maybe_serialize($other_data),
              'rel' => $cid
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

          do_action("wplc_set_session_chat_id", $cid);

          set_transient("wplc_user_active_" . wplc_get_user_ip(), 1, 60);

          do_action("wplc_start_chat_hook_after_data_insert", $cid, 2, $name);

          do_action("wplc_change_chat_status_hook", $cid, 2); /* so we fire off onesignal events */

          do_action("wplc_hook_initiate_chat", array("cid" => $request['cid'], "name" => $name, "email" => $email));

          do_action('wplc_log_roi_conversion', $request['cid'], $request['url']);


          $return_array['response'] = "Visitor successfully started chat";
          $return_array['code'] = "200";
          $return_array['data'] = array('wplc_cid' => $cid);
        } else {

          $return_array['response'] = "Missing Parameter";
          $return_array['code'] = "401";
          $return_array['requirements'] = array('wplc_name' => 'VISITORS_NAME', 'wplc_email' => 'VISITORS_EMAIL', 'session' => 'VISITORS_SESSION');
        }
      } else {

        $return_array['response'] = "No 'security' found";
        $return_array['code'] = "401";
        $return_array['requirements'] = array('server_token' => 'SECRET_TOKEN', 'wplc_name' => 'VISITORS_NAME', 'wplc_email' => 'VISITORS_EMAIL', 'session' => 'VISITORS_SESSION');
      }
    } else {

      $return_array['response'] = "No request data found";
      $return_array['code'] = "400";
      $return_array['requirements'] = array('server_token' => 'SECRET_TOKEN', 'wplc_name' => 'VISITORS_NAME', 'wplc_email' => 'VISITORS_EMAIL', 'session' => 'VISITORS_SESSION');
    }
  } else {
    $return_array['response'] = "Rate limit hit. You can only start one chat at a time.";
    $return_array['code'] = "401";
  }

  return $return_array;
}

/*
 * Rest Permission check for restricted end points
*/
function wplc_api_permission_check_start_chat()
{

  return check_ajax_referer('wp_rest', '_wpnonce', false);
}


/*
 * Rest Permission check for restricted end points
*/
function wplc_api_permission_check()
{
  wplc_start_session();
  if (isset($_SESSION['wplc_session_chat_session_id'])) {
    $sid = $_SESSION['wplc_session_chat_session_id'];
  }
  wplc_close_session();
  if (isset($sid) && check_ajax_referer('wp_rest', '_wpnonce', false)) {
    return true;
  } else {
    return false;
  }
}


function wplc_api_is_typing_mrg(WP_REST_Request $request)
{

  $return_array = array();
  if (isset($request)) {
    if (isset($request['security'])) {
      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['security'] === $check_token) {
        if (isset($request['cid'])) {

          if (wplc_check_user_request($request['cid'])) {

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
            $return_array['response'] = "'cid' tamper detected";
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

/*
 * When an agent initiates a chat, start session on behalf of the user.
*/

function wplc_api_start_session(WP_REST_Request $request)
{

  $return_array = array();
  if (isset($request)) {

    if (isset($request['server_token'])) {

      $check_token = get_option('wplc_api_secret_token');
      if ($check_token !== false && $request['server_token'] === $check_token) {

        if (isset($request['cid'])) {
          $cid = wplc_return_chat_id_by_rel_or_id($request['cid']);
          if (!empty($cid)) {
            do_action("wplc_set_session_chat_id", intval($cid));
            set_transient("wplc_user_active_" . wplc_get_user_ip(), 1, 60);
            $return_array['response'] = "Successful";
            $return_array['code'] = "200";
            $return_array['requirements'] = array(
              "server_token" => "YOUR_SECRET_TOKEN",
              "chat_id"   => "Chat ID",
              "message" => "Message"
            );
          } else {
            $return_array['response'] = "'chat_id' tamper detected";
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
 * When an agent closes a chat, remove session on behalf of the user.
*/
function wplc_api_end_session(WP_REST_Request $request)
{
  if (check_ajax_referer('wp_rest', '_wpnonce', false)) {
    wplc_start_session();
    if (isset($_SESSION['wplc_session_chat_session_id'])) {
      $sid = $_SESSION['wplc_session_chat_session_id'];
    }
    wplc_close_session();
    if (isset($sid)) {
      $g_chat_id = wplc_return_chat_id_by_rel_or_id($_POST['cid']);
      if (intval($sid) === $g_chat_id) {
        wplc_clean_session();
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
}
