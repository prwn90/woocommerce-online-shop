<?php
if (!defined('ABSPATH')) {
  exit;
}

function wplc_record_chat_notification($type, $cid, $data)
{
  if ($cid) {
    do_action("wplc_hook_chat_notification", $type, $cid, $data);
  }
  return true;
}

add_action("wplc_hook_chat_notification", "wplc_filter_control_chat_notification_user_loaded", 10, 3);
function wplc_filter_control_chat_notification_user_loaded($type, $cid, $data)
{
  if ($type == "user_loaded") {
    // Only run if the chat status is not 1 or 5 (complete or browsing)
    if (isset($data['chat_data']) && isset($data['chat_data']->status) && intval($data['chat_data']->status) != 5) {
      global $wpdb;
      global $wplc_tblname_msgs;
      $msg = __("User is browsing", 'wp-live-chat-support') . ' ' . wp_filter_post_kses($data['uri']);

      // if chat server, send msg to agent

      $cid = wplc_return_chat_id_by_rel_or_id($cid);

      $wpdb->insert(
        $wplc_tblname_msgs,
        array(
          'chat_sess_id' => $cid,
          'timestamp' => current_time('mysql'),
          'msgfrom' => __('System notification', 'wp-live-chat-support'),
          'msg' => $msg,
          'status' => 0,
          'originates' => 3 // for agent
        ),
        array(
          '%s',
          '%s',
          '%s',
          '%s',
          '%d',
          '%d'
        )
      );
    }
  }
  return $type;
}

add_action("wplc_hook_chat_notification", "wplc_filter_control_chat_notification_await_agent", 10, 3);
function wplc_filter_control_chat_notification_await_agent($type, $cid, $data)
{
  $wplc_settings = wplc_get_options();
  if (!$wplc_settings['wplc_use_node_server']) {

    if ($type == "await_agent") {
      global $wpdb;
      global $wplc_tblname_msgs;
      $wpdb->insert(
        $wplc_tblname_msgs,
        array(
          'chat_sess_id' => $cid,
          'timestamp' => current_time('mysql'),
          'msgfrom' => __('System notification', 'wp-live-chat-support'),
          'msg' => $data['msg'],
          'status' => 0,
          'originates' => 0
        ),
        array(
          '%s',
          '%s',
          '%s',
          '%s',
          '%d',
          '%d'
        )
      );
    }
  }
  return $type;
}


add_action("wplc_hook_chat_notification", "wplc_filter_control_chat_notification_agent_joined", 10, 3);
function wplc_filter_control_chat_notification_agent_joined($type, $cid, $data)
{
  if ($type == "joined") {
    $chat_data = wplc_get_chat_data($cid);
    $wplc_settings = wplc_get_options();
    $user_info = get_userdata(intval($data['aid']));
    $agent_tagline = '';

    if ($wplc_settings['wplc_use_wp_name']) {
      $agent = $user_info->display_name;
    } else {
      if (!empty($wplc_settings['wplc_chat_name'])) {
        $agent = $wplc_settings['wplc_chat_name'];
      } else {
        $agent = 'Admin';
      }
    }

    $agent_tagline = apply_filters("wplc_filter_agent_data_agent_tagline", $agent_tagline, $cid, $chat_data, $agent, $wplc_settings, $user_info, $data);
    $msg = $agent . " " . __("has joined the chat.", 'wp-live-chat-support');

    $data_array = array(
      'chat_sess_id' => $cid,
      'timestamp' => current_time('mysql'),
      'msgfrom' => __('System notification', 'wp-live-chat-support'),
      'msg' => $msg,
      'status' => 0,
      'originates' => 0,
      'other' => maybe_serialize(array(
        'ntype' => 'joined',
        'email' => md5($user_info->user_email),
        'name' => $agent,
        'aid' => $user_info->ID,
        'agent_tagline' => $agent_tagline
      ))
    );

    $type_array = array(
      '%s',
      '%s',
      '%s',
      '%s',
      '%d',
      '%d',
      '%s'
    );

    do_action("wplc_store_agent_joined_notification", $data_array, $type_array);
  }
  return $type;
}

add_action("wplc_store_agent_joined_notification", "wplc_hook_control_store_agent_joined_notification", 10, 2);
function wplc_hook_control_store_agent_joined_notification($data_array, $type_array)
{
  global $wpdb;
  global $wplc_tblname_msgs;

  $wpdb->insert(
    $wplc_tblname_msgs,
    $data_array,
    $type_array
  );
  return;
}
