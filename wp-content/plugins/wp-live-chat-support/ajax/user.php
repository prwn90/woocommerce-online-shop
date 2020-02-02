<?php

if (!defined('ABSPATH')) {
  die();
}

add_action('wp_ajax_wplc_call_to_server_visitor', 'wplc_init_ajax_callback');
add_action('wp_ajax_wplc_user_close_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_wplc_user_minimize_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_wplc_user_maximize_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_wplc_user_send_msg', 'wplc_init_ajax_callback');
add_action('wp_ajax_wplc_start_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_start_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_call_to_server_visitor', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_user_close_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_user_minimize_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_user_maximize_chat', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_user_send_msg', 'wplc_init_ajax_callback');
add_action('wp_ajax_wplc_get_chat_box', 'wplc_init_ajax_callback');
add_action('wp_ajax_nopriv_wplc_get_chat_box', 'wplc_init_ajax_callback');


function wplc_init_ajax_callback()
{

  $check = check_ajax_referer('wplc', 'security');

  if ($check == 1) {
    $wplc_settings = wplc_get_options();
    $iterations = $wplc_settings['wplc_iterations'];

    /* time in microseconds between long poll loop (lower number = higher resource usage) */
    define('WPLC_DELAY_BETWEEN_LOOPS', $wplc_settings['wplc_delay_between_loops']);
    /* this needs to take into account the previous constants so that we dont run out of time, which in turn returns a 503 error */
    define('WPLC_TIMEOUT', ((WPLC_DELAY_BETWEEN_LOOPS * $iterations) / 1000000) * 2);

    /* we're using PHP 'sleep' which may lock other requests until our script wakes up. Call this function to ensure that other requests can run without waiting for us to finish */
    session_write_close();

    // check input vars and sanitize once

    $cid = 0;
    if (!empty($_POST['cid'])) {
      $cid = sanitize_text_field($_POST['cid']);
    }

    $action = '';
    if (!empty($_POST['action'])) {
      $action = sanitize_text_field($_POST['action']);
    }

    $name = wplc_get_user_name('', $wplc_settings);
    if (isset($_POST['name']) && !empty($_POST['name'])) {
      $name = wplc_get_user_name(trim(strip_tags(sanitize_text_field($_POST['name']))), $wplc_settings);
    }

    $email = "no email set";
    if (isset($_POST['email']) && !empty($_POST['email'])) {
      $email = strip_tags(sanitize_text_field($_POST['email']));
    }

    $status = 0;
    if (!empty($_POST['status'])) {
      $status = intval($_POST['status']);
    }

    $is_mobile = false;
    if (isset($_POST['wplc_is_mobile']) && ($_POST['wplc_is_mobile'] === 'true' || $_POST['wplc_is_mobile'] === true)) {
      $is_mobile = true;
    }

    $chat_status = 0;
    if (!empty($_POST['chat_status'])) {
      $chat_status = intval($_POST['chat_status']);
    }

    $wplcsession = '';
    if (!empty($_POST['wplcsession'])) {
      $wplcsession = sanitize_text_field($_POST['wplcsession']);
    }

    $first_run = isset($_POST['first_run']) && intval($_POST['first_run']) == 1;
    $short_poll = isset($_POST['short_poll']) && sanitize_text_field($_POST['short_poll']) == "true";

    // check actions

    switch ($action) {

      case 'wplc_get_chat_box':
        $new_user=false;
        $continue = apply_filters("wplc_version_check_continue", true);
        if ($continue === true) {
          wplc_start_session();
          $sessid=session_id();
          if (empty($_SESSION['browsing'])) {
            $new_user=true;
            $_SESSION['browsing']=1;
          }
          wplc_close_session();
          wplc_output_box_5100($cid);
          if ($new_user) {
            do_action("wplc_log_user_on_page_after_hook", $sessid, array());
          }
        } else {
          echo boolval($continue);
        }
        break;

      case 'wplc_call_to_server_visitor':

        $wplc_settings = wplc_get_options();
        if (defined('WPLC_TIMEOUT')) {
          @set_time_limit(WPLC_TIMEOUT);
        } else {
          @set_time_limit(120);
        }
        $i = 1;
        $array = array("check" => false);
        $array['debug'] = "";

        $cdata = false;

        if (!empty($cid)) {
          /* get agent ID */
          $cdata = wplc_get_chat_data($cid, __LINE__);
          $from = __("Admin", 'wp-live-chat-support'); /* set default */
          $array['aname'] = apply_filters("wplc_filter_admin_from", $from, $cid, $cdata);
        }

        while ($i <= $iterations) {

          if ($i % round($iterations / 2) == 0) {
            wplc_update_chat_statuses();
          }

          if (empty($cid)) {
            $cid = wplc_log_user_on_page($name, $email, $wplcsession, $is_mobile);
            $array['cid'] = $cid;
            $array['status'] = wplc_return_chat_status($cid);
            $array['wplc_name'] = $name;
            $array['wplc_email'] = $email;
            $array['check'] = true;
            wplc_start_session();
            $_SESSION['wplc_session_chat_session_id'] = intval($cid);
            wplc_close_session();
          } else {
            // echo 2;
            $new_status = wplc_return_chat_status($cid);
            $array['wplc_name'] = $name;
            $array['wplc_email'] = $email;
            $array['cid'] = $cid;
            $array['aid'] = $cid;
            $array = apply_filters("wplc_filter_user_long_poll_chat_loop_iteration", $array, $_POST, $i, $cdata);

            if ($new_status == $status) { // if status matches do the following
              if ($status != 2) {

                /* check if session_variable is different? if yes then stop this script completely. */
                if (!empty($wplcsession) && $i > 1) {
                  $wplc_session_variable = $wplcsession;
                  $current_session_variable = wplc_return_chat_session_variable($cid);
                  if ($current_session_variable != "" && $current_session_variable != $wplc_session_variable) {
                    /* stop this script */
                    $array['status'] = 11;
                    echo json_encode($array);
                    die();
                  }
                }

                if ($i == 1) {
                  if ($status != 12) {
                    /* we dont want to update the time if the user was not answered by the agent as this needs to eventually time out and become a "missed chat" - status: 0 */
                    wplc_update_user_on_page($cid, $status, $wplcsession);
                  }
                }
              }

              if ($status == 0 || $status == 12) { // browsing - user tried to chat but admin didn't answer so turn back to browsing
                $array['status'] = 12;
              } else if ($status == 3 || $status == 10) {
                $messages = wplc_return_user_chat_messages($cid, $wplc_settings, $cdata);
                if ($status == 10) {
                  $array['alert'] = true;
                }
                if ($messages) {
                  wplc_mark_as_read_user_chat_messages($cid);
                  $array['status'] = 3;
                  $array['data'] = $messages;
                  $array['check'] = true;
                }
              } else if ($status == 2) {
                $messages = wplc_return_user_chat_messages($cid, $wplc_settings, $cdata);
                $array['debug'] = "we are here " . __LINE__;
                if ($messages) {
                  wplc_mark_as_read_user_chat_messages($cid);
                  $array['status'] = 2;
                  $array['data'] = $messages;
                  $array['check'] = true;
                }
              } else if ($new_status == 12) { // no answer from admin, for the second+ time.
                $array['data'] = wplc_return_no_answer_string($cid);
                $array['check'] = true;
                $array['preformatted'] = true;
                @do_action("wplc_hook_missed_chat", array("cid" => $cid, "name" => $name, "email" => $email));
              }

              /* check if this is part of the first run */
              if ($first_run) {
                /* if yes, then send data now and dont wait for all iterations to complete */
                if (!isset($array['status'])) {
                  $array['status'] = $new_status;
                }
                $array['check'] = true;
              } else if ($short_poll) {
                /* if yes, then send data now and dont wait for all iterations to complete */
                if (!isset($array['status'])) {
                  $array['status'] = $new_status;
                }
                $array['check'] = true;
              }
            } else { // statuses do not match
              $array['debug'] = $array['debug'] . ' Doesnt match ' . $new_status . ' ' . $status;
              $array['status'] = $new_status;
              if ($new_status == 1) { // completed
                wplc_update_user_on_page($cid, 8, $wplcsession);
                $array['check'] = true;
                $array['status'] = 8;
                $array['data'] = array();
                $array['data'][9999] = array();
                $array['data'][9999]['msg'] =  __("Admin has closed and ended the chat", 'wp-live-chat-support');
              } else if ($new_status == 2) { // pending
                $array['debug'] = "we are here " . __LINE__;
                $array['check'] = true;
                $array['wplc_name'] = wplc_return_chat_name($cid);
                $array['wplc_email'] = wplc_return_chat_email($cid);
                $messages = wplc_return_chat_messages($cid, false, true, $wplc_settings, $cdata, 'array', false);
                if ($messages) {
                  $array['data'] = $messages;
                }
              } else if ($new_status == 3) { // active
                $array['data'] = null;
                $array['check'] = true;

                if ($status == 5 || $status == 3) {
                  $array['sound'] = false;
                  $messages = wplc_return_chat_messages($cid, false, true, $wplc_settings, $cdata, 'array', false);
                  if ($messages) {
                    $array['data'] = $messages;
                  }
                }
              } else if ($new_status == 7) { // timed out
                wplc_update_user_on_page($cid, 5, $wplcsession);
              } else if ($new_status == 9) { // user closed chat without inputting or starting a chat
                $array['check'] = true;
              } else if ($new_status == 12) { // no answer from admin
                $array['data'] = wplc_return_no_answer_string($cid);
                $array['check'] = true;
                $array['preformatted'] = true;
                wplc_update_user_on_page($cid, 12, $wplcsession);
                @do_action("wplc_hook_missed_chat", array("cid" => $cid, "name" => $name, "email" => $email));
              } else if ($new_status == 10) { // minimized active chat
                $array['check'] = true;
                if ($status == 5) {
                  $messages = wplc_return_chat_messages($cid, false, true, $wplc_settings, $cdata, 'array', false);
                  if ($messages) {
                    $array['data'] = $messages;
                  }
                }
              }

              /* check if this is part of the first run */
              if ($first_run) {
                /* if yes, then send data now and dont wait for all iterations to complete */
                if (!isset($array['status'])) {
                  $array['status'] = $new_status;
                }
                $array['check'] = true;
              } else if ($short_poll) {
                /* if yes, then send data now and dont wait for all iterations to complete */
                if (!isset($array['status'])) {
                  $array['status'] = $new_status;
                }
                $array['check'] = true;
              }
              $array = apply_filters("wplc_filter_wplc_call_to_server_visitor_new_status_check", $array);
            }
          }
          if ($array['check'] == true) {
            echo json_encode($array);
            break;
          }
          $i++;
          if (defined('WPLC_DELAY_BETWEEN_LOOPS')) {
            usleep(WPLC_DELAY_BETWEEN_LOOPS);
          } else {
            usleep(500000);
          }
        }

        break;

      case 'wplc_user_close_chat':
        if (wplc_check_user_request($cid)) {
          if ($status == 5) {
            wplc_change_chat_status($cid, 9);
          } else if ($status == 3) {
            wplc_change_chat_status($cid, 8);
          }
          do_action("wplc_end_session_chat_id");
        }
        break;

      case 'wplc_user_minimize_chat':
        if (wplc_check_user_request($cid)) {
          wplc_change_chat_status($cid, 10);
        }
        break;

      case 'wplc_user_maximize_chat':
        if (wplc_check_user_request($cid)) {
          wplc_change_chat_status($cid, $chat_status);
        }
        break;

      case 'wplc_user_send_msg':
        if (wplc_check_user_request($cid)) {
          $chat_msg = $_POST['msg'];
          $wplc_rec_msg = wplc_record_chat_msg("1", $cid, $chat_msg);
          if ($wplc_rec_msg) {
            echo 'sent';
          } else {
            echo "There was an error sending your chat message. Please contact support";
          }
        } else {
          echo "Invalid Chat ID";
        }
        break;

      case 'wplc_start_chat':
        wplc_start_session();
        $check = isset($_SESSION['wplc_session_chat_session_id']);
        wplc_close_session();
        if ($check) {
          if (!empty($cid)) {
            if ($name && $email) {
              $new_cid = wplc_user_initiate_chat($name, $email, $cid, $wplcsession); // echo the chat session id
              do_action("wplc_set_session_chat_id", $new_cid);
              echo $new_cid;
            } else {
              echo "error2";
            }
          } else {
            if ($name && $email) {
              echo wplc_user_initiate_chat($name, $email, null, $wplcsession); // echo the chat session id
            } else {
              echo "error2";
            }
          }
        }
        break;
    } // switch
  } // if
  die();
}
