<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$wplc_images='jpg|jpeg|png|gif|bmp';
$wplc_multimedia='mp4|mp3|mpeg|ogg|ogm|ogv|webm|avi|wav|mov';
$wplc_documents='doc|docx|xls|xlsx|pub|pubx|pdf|csv|txt';
//$wplc_others='zip|rar|7z|gz|tgz';
$wplc_allowed_extensions = $wplc_images.'|'.$wplc_multimedia.'|'.$wplc_documents; //.'|'.$wplc_others;

function wplc_log_user_on_page($name,$email,$session, $is_mobile = false) {
    global $wpdb;
    global $wplc_tblname_chats;

    $user_data = array(
        'ip' => wplc_get_user_ip(),
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
    );

    /* user types
     * 1 = new
     * 2 = returning
     * 3 = timed out
     */

     $other = array(
         "user_type" => 1
     );

     if($is_mobile){
        $other['user_is_mobile'] = true;
     } else {
        $other['user_is_mobile'] = false;
     }

    $other = apply_filters("wplc_log_user_on_page_insert_other_data_filter", $other);

    $wplc_chat_session_data = array(
            'status' => '5',
            'timestamp' => current_time('mysql'),
            'name' => $name,
            'email' => $email,
            'session' => $session,
            'ip' => maybe_serialize($user_data),
            'url' => sanitize_text_field($_SERVER['HTTP_REFERER']),
            'last_active_timestamp' => current_time('mysql'),
            'other' => maybe_serialize($other),
    );

    $wplc_chat_session_data = apply_filters("wplc_log_user_on_page_insert_filter", $wplc_chat_session_data);

    $wplc_chat_session_prep_array = array(
                '%s', 
                '%s', 
                '%s', 
                '%s', 
                '%s', 
                '%s', 
                '%s', 
                '%s', 
                '%s'
    );

    if(!empty($wplc_chat_session_data['department_id'])){
        $wplc_chat_session_prep_array[] = '%d';
    }

    $wpdb->insert($wplc_tblname_chats, $wplc_chat_session_data, $wplc_chat_session_prep_array);
    $lastid = $wpdb->insert_id;
    do_action("wplc_log_user_on_page_after_hook", $lastid, $wplc_chat_session_data);
    return $lastid;
}

function wplc_update_user_on_page($cid, $status = 5,$session) {
    global $wpdb;
    global $wplc_tblname_chats;

    $user_data = array(
        'ip' => wplc_get_user_ip(),
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
    );



    $query = $wpdb->update(
        $wplc_tblname_chats,
        array(
            'url' => sanitize_text_field($_SERVER['HTTP_REFERER']),
            'last_active_timestamp' => current_time('mysql'),
            'ip' => maybe_serialize($user_data),
            'status' => $status,
            'session' => $session,
        ),
        array('id' => $cid),
        array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        ),
        array('%d')
    );


    return $query;


}

function wplc_record_chat_msg($from, $cid, $msg, $rest_check = false, $aid = false, $other = false) {
    global $wpdb;
    global $wplc_tblname_msgs;
    $wplc_settings = wplc_get_options();
    $cid = wplc_return_chat_id_by_rel_or_id($cid);

    /**
     * check if this CID even exists, if not, create it
     *
     * If it doesnt exist, it most likely is an agent-to-agent chat that we now need to save.
     */

    global $wplc_tblname_chats;
    $results=false;
    if (!empty($cid)) {
      $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %s LIMIT 1", $cid) 
      );
    }
    if (empty($cid) || !$results) {
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

    if ($from == "2" && $rest_check == false) {
      if (!wplc_user_is_agent()) {
        return "security issue"; 
      }
    }

    if ($from == "1") {
        $fromname = wplc_return_chat_name(sanitize_text_field($cid));
        $fromname = wplc_get_user_name($fromname, $wplc_settings);
        $orig = '2';
    }
    else {
        $fromname = apply_filters("wplc_filter_admin_name","Admin");
        $orig = '1';
    }

    $msg_id = '';

    if ($other !== false) {
        if (!empty($other->msgID)) {
            $msg_id = $other->msgID;
        } else {
            $msg_id = '';
        }
    }

    $msg = apply_filters("wplc_filter_message_control",$msg);

    if (!$aid) {
      if (wplc_user_is_agent()) {
        $other_data = array('aid'=>get_current_user_id());
      } else {
        $other_data = '';
      }
    } else {
      if (wplc_user_is_agent($aid)) {
        $other_data = array('aid'=>$aid);
      } else {
        $other_data = '';
      }
    }

    $wpdb->insert(
        $wplc_tblname_msgs,
        array(
                'chat_sess_id' => $cid,
                'timestamp' => current_time('mysql'),
                'msgfrom' => $fromname,
                'msg' => $msg,
                'status' => 0,
                'originates' => $orig,
                'other' => maybe_serialize( $other_data ),
                'rel' => $msg_id
        ),
        array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s'
        )
    );
    wplc_update_active_timestamp(sanitize_text_field($cid));
    return true;
}

function wplc_update_active_timestamp($cid) {
    global $wpdb;
    global $wplc_tblname_chats;
    $wpdb->update(
        $wplc_tblname_chats,
        array(
            'last_active_timestamp' => current_time('mysql')
        ),
        array('id' => $cid),
        array('%s'),
        array('%d')
    );

    return true;

}

function wplc_return_chat_name($cid) {
    global $wpdb;
    global $wplc_tblname_chats;

    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %s", $cid)
    );
    foreach ($results as $result) {
        return $result->name;
    }

}

/**
 * Find out if we are dealing with a NODE CID and convert it to the WP CID.
 *
 * If it cannot find a relative, then simply return the original CID parsed through.
 *
 * @param  string|int $rel The string CId to compare or numeric CID
 * @return string|int The referring numeric CID
 */
function wplc_return_chat_id_by_rel_or_id($rel) {
  global $wpdb;
  global $wplc_tblname_chats;
  if (is_numeric($rel)) {
    return intval($rel);
  }
  $rel = sanitize_text_field($rel);
  $results = $wpdb->get_results($wpdb->prepare("SELECT id FROM $wplc_tblname_chats WHERE `rel` = %s LIMIT 1", $rel));
  if ($results) {
    foreach ($results as $result) {
      if (isset($result->id)) {
        return intval($result->id);
      }
    }
  } 
  error_log('wplc_return_chat_id_by_rel_or_id cannot find rel='.$rel);
  return 0;
}

function wplc_return_chat_email($cid) {
    global $wpdb;
    global $wplc_tblname_chats;
    $cid = intval($cid);
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %s", $cid));
    foreach ($results as $result) {
        return $result->email;
    }

}

function wplc_time_ago($time_ago)
{
    $time_ago = strtotime($time_ago);
    $cur_time   = current_time('timestamp');
    $time_elapsed   = $cur_time - $time_ago;
    $seconds    = $time_elapsed ;
    $minutes    = round($time_elapsed / 60 );
    $hours      = round($time_elapsed / 3600);
    $days       = round($time_elapsed / 86400 );
    $weeks      = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640 );
    $years      = round($time_elapsed / 31207680 );
    // Seconds
    if($seconds <= 60){
        return "0 min";
    }
    //Minutes
    else if($minutes <=60){
        if($minutes==1){
            return "1 min";
        }
        else{
            return "$minutes min";
        }
    }
    //Hours
    else if($hours <=24){
        if($hours==1){
            return "1 hr";
        }else{
            return "$hours hrs";
        }
    }
    //Days
    else if($days <= 7){
        if($days==1){
            return "1 day";
        }else{
            return "$days days";
        }
    }
    //Weeks
    else if($weeks <= 4.3){
        if($weeks==1){
            return "1 week";
        }else{
            return "$weeks weeks";
        }
    }
    //Months
    else if($months <=12){
        if($months==1){
            return "1 month";
        }else{
            return "$months months";
        }
    }
    //Years
    else{
        if($years==1){
            return "1 year";
        }else{
            return "$years years";
        }
    }
}

add_filter("wplc_filter_list_chats_actions","wplc_filter_control_list_chats_actions",15,3);
/**
 * Only allow agents access
 * @return void
 * @since  6.0.00
 * @version  6.0.04 Updated to ensure those with the correct access can access this function
 */
function wplc_filter_control_list_chats_actions($actions,$result,$post_data) {
  $aid = apply_filters("wplc_filter_aid_in_action","");
  if (wplc_user_is_agent()) {
    switch (intval($result->status)) {
      case 2:
        $url_params = "&action=ac&cid=".$result->id.$aid;
        $url = admin_url( 'admin.php?page=wplivechat-menu'.$url_params);
        $actions = "<a href=\"".$url."\" class=\"wplc_open_chat button button-primary\" window-title=\"WP_Live_Chat_".$result->id."\">". apply_filters("wplc_accept_chat_button_filter", __("Accept Chat",'wp-live-chat-support'), $result->id)."</a>";
        break;

      case 3:
      case 10:
        $url_params = "&action=ac&cid=".$result->id.$aid;
        $url = admin_url( 'admin.php?page=wplivechat-menu'.$url_params);
        if (!isset( $result->agent_id ) || get_current_user_id() == $result->agent_id ) { //Added backwards compat checks
          $actions = "<a href=\"".$url."\" class=\"wplc_open_chat button button-primary\" window-title=\"WP_Live_Chat_".$result->id."\">".__("Open Chat",'wp-live-chat-support')."</a>";
        } else {
          $actions = "<span class=\"wplc-chat-in-progress\">" . __( "In progress with another agent", 'wp-live-chat-support') . "</span>";
        }
        break;

      case 2:
        $url_params = "&action=ac&cid=".$result->id.$aid;
        $url = admin_url( 'admin.php?page=wplivechat-menu'.$url_params);
        $actions = "<a href=\"".$url."\" class=\"wplc_open_chat button button-primary\" window-title=\"WP_Live_Chat_".$result->id."\">".__("Accept Chat",'wp-live-chat-support')."</a>";
        break;
      
      case 12:
        $url_params = "&action=ac&cid=".$result->id.$aid;
        $url = admin_url( 'admin.php?page=wplivechat-menu'.$url_params);
        $actions = "<a href=\"".$url."\" class=\"wplc_open_chat button button-primary\" window-title=\"WP_Live_Chat_".$result->id."\">".__("Open Chat",'wp-live-chat-support')."</a>";
        break;
    }
  } else {
    $actions = "<a href='#'>".__( 'Only chat agents can accept chats', 'wp-live-chat-support')."</a>";
  }
  return $actions;
}

function wplc_list_chats($post_data) {

    global $wpdb;
    global $wplc_tblname_chats;

    $data_array = array();
    $id_list = array();

    $status = 3;
    $wplc_c = 0;

    // Retrieve count of users in same department or in no department
    $user_id = get_current_user_id();
    $user_department = get_user_meta($user_id ,"wplc_user_department", true);

    $wplc_chat_count_sql = "SELECT COUNT(*) FROM $wplc_tblname_chats WHERE status IN (3,2,10,5,8,9,12)";
    if($user_department > 0)
        $wplc_chat_count_sql .= " AND (department_id=0 OR department_id=$user_department)";
    $data_array['visitor_count'] = $wpdb->get_var($wplc_chat_count_sql);

    // Retrieve data
    $wplc_chat_sql = "SELECT * FROM $wplc_tblname_chats WHERE (`status` = 3 OR `status` = 2 OR `status` = 10 OR `status` = 5 or `status` = 8 or `status` = 9 or `status` = 12)";
    $wplc_chat_sql .= apply_filters("wplc_alter_chat_list_sql_before_sorting", ""); // this one forces integers into sql, assume it's ok

    $wplc_chat_sql .= " ORDER BY `timestamp` ASC";

    $results = $wpdb->get_results($wplc_chat_sql);


    if($results) {


        foreach ($results as $result) {
            unset($trstyle);
            unset($actions);

            $user_data = maybe_unserialize($result->ip);
            $browser = @wplc_return_browser_string($user_data['user_agent']);
            $browser_image = wplc_return_browser_image($browser,"16");

            $actions = apply_filters("wplc_filter_list_chats_actions","",$result,$post_data);


            $other_data = maybe_unserialize($result->other);



           $trstyle = "";

           $id_list[intval($result->id)] = true;

           $data_array[$result->id]['name'] = $result->name;
           $data_array[$result->id]['email'] = $result->email;

           $data_array[$result->id]['status'] = $result->status;
           $data_array[$result->id]['action'] = $actions;
           $data_array[$result->id]['timestamp'] = wplc_time_ago($result->timestamp);

           if ((current_time('timestamp') - strtotime($result->timestamp)) < 3600) {
               $data_array[$result->id]['type'] = __("New",'wp-live-chat-support');
           } else {
               $data_array[$result->id]['type'] = __("Returning",'wp-live-chat-support');
           }

           $data_array[$result->id]['image'] = "<img src=\"//www.gravatar.com/avatar/".md5($result->email)."?s=30&d=mm\"  class='wplc-user-message-avatar' />";
           $data_array[$result->id]['data']['browsing'] = $result->url;
           $path = parse_url($result->url, PHP_URL_PATH);

           if (strlen($path) > 20) {
                $data_array[$result->id]['data']['browsing_nice_url'] = substr($path,0,20).'...';
           } else {
               $data_array[$result->id]['data']['browsing_nice_url'] = $path;
           }

           $data_array[$result->id]['data']['browser'] = "<img src='" . WPLC_PLUGIN_URL . "images/$browser_image' alt='$browser' title='$browser' /> ";
           $data_array[$result->id]['data']['ip'] = '';
           $data_array[$result->id]['other'] = $other_data;
        }

        $data_array['ids'] = $id_list;
    }

    return json_encode($data_array);
}



function wplc_return_user_chat_messages($cid, $wplc_settings = false, $cdata = false) {
  global $wpdb;
  global $wplc_tblname_msgs;

  if (!$wplc_settings) {
    $wplc_settings = wplc_get_options();
  }

  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_msgs WHERE `chat_sess_id` = '%d' AND `status` = '0' AND (`originates` = '1' OR `originates` = '0') ORDER BY `timestamp` ASC", $cid));
  if (!$cdata) {
    $cdata = wplc_get_chat_data($cid,__LINE__);
  }

  $msg_hist = array();
  foreach ($results as $result) {
    $system_notification = false;
    $id = $result->id;
    $msg = $result->msg;

    if ( isset( $result->other ) ) { $other_data = maybe_unserialize( $result->other ); } else { $other_data = array(); }
    if ($other_data == '') { $other_data = array(); }
    $timestamp = strtotime( $result->timestamp );
    $other_data['datetime'] = $timestamp;
    $other_data['datetimeUTC'] = strtotime( get_gmt_from_date( $result->timestamp ) );
    if (intval($result->originates) == 0) {
      // system notifications from version 7
      $system_notification = true;
    }

    if (!$system_notification) {
      /* this is a normal message */
      if (function_exists('wplc_encrypt_decrypt_msg')) {
        $msg = wplc_encrypt_decrypt_msg($msg);
      }

      $msg_array = maybe_unserialize($msg);

      if (is_array($msg_array)) {
        $msg = $msg_array['m'];
      }

      $msg = stripslashes($msg); 
      $msg = apply_filters("wplc_filter_message_control_out",$msg);
      $msg = sanitize_text_field(stripslashes($msg)); // TODO: check not optimal sanitization - check all wplc_return_user_chat_messages references too

      $msg_hist[$id]['msg'] = $msg;
      $msg_hist[$id]['originates'] = intval($result->originates);
      $msg_hist[$id]['other'] = $other_data;
    } else {
      /* add the system notification to the list */
      if ( isset( $msg_hist[$id] ) ) { $msg_hist[$id] = array(); }
      $msg_hist[$id]['msg'] = $msg;
      $msg_hist[$id]['other'] = $other_data;
      $msg_hist[$id]['originates'] = intval($result->originates);
    }
  }
  return $msg_hist;
}

function wplc_return_no_answer_string($cid) {

    $wplc_settings = wplc_get_options();
    if (isset($wplc_settings['wplc_user_no_answer'])) {
        $string = stripslashes($wplc_settings['wplc_user_no_answer']);
    } else {
        $string = __("No agent was able to answer your chat request. Please try again.",'wp-live-chat-support');
    }
    $string = apply_filters("wplc_filter_no_answer_string",$string,$cid);
    return "<span class='wplc-system-notification wplc_no_answer wplc-color-4'><center>".$string."</center></span>";
}
add_filter("wplc_filter_no_answer_string","wplc_filter_control_no_answer_string",10,2);

/**
 * Add the "retry chat" button when an agent hasnt answered
 * @param  string $string Original "No Answer" string
 * @param  intval $cid    Chat ID
 * @return string
 */
function wplc_filter_control_no_answer_string($string,$cid) {
    $string = $string. " <br /><button class='wplc_retry_chat wplc-color-bg-1 wplc-color-2' cid='".$cid."'>".__("End Chat",'wp-live-chat-support')."</button>"; // Request new chat
    return $string;
}

function wplc_change_chat_status($id, $status, $aid = 0) {
  global $wpdb;
  global $wplc_tblname_chats;

  $other=array();
  if ($aid>0) {
    /* only run when accepting a chat */
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wplc_tblname_chats." WHERE `id` = %d LIMIT 1", $id));
    foreach ($results as $result) {
      $other = maybe_unserialize($result->other);
      $other['aid'] = $aid;
    }

    $wpdb->update(
      $wplc_tblname_chats,
      array(
        'status' => $status,
        'other' => maybe_serialize($other),
        'agent_id' => $aid
      ),
      array('id' => $id),
      array(
        '%d',
        '%s',
        '%d'
      ),
      array('%d')
      );    
  } else {
    $wpdb->update(
      $wplc_tblname_chats,
      array(
        'status' => $status
      ),
      array('id' => $id),
      array('%d'),
      array('%d')
    );
  }
  do_action("wplc_change_chat_status_hook", $id, $status);
  return true;
}

function wplc_return_chat_messages($cid, $transcript = false, $html = true, $wplc_settings = false, $cdata = false, $display = 'string', $only_read_message = false) {
  if (!$wplc_settings) {
    $wplc_settings = wplc_get_options();
  }

  $results = wplc_get_chat_messages($cid, $only_read_message, $wplc_settings);
  if (!$results) { return; }

  if (!$cdata) {
    $cdata = wplc_get_chat_data($cid,__LINE__);
  }

  $msg_array = array();
  $msg_hist = "";
  foreach ($results as $result) {
    $display_notification = false;
    $system_notification = false;
    
    /* added a control here to see if we should use the NODE ID instead of the SQL ID */
    if (empty($result->rel)) {
      $id = $result->id;
    } else {
      $id = $result->rel;
    }
    $msg = $result->msg;

    if (isset($result->other)) { 
      $other_data = maybe_unserialize( $result->other ); 
    } else { 
      $other_data = array();
    }
    
    if ($other_data == '') { 
      $other_data = array(); 
    }

    $timestamp = strtotime( $result->timestamp );
    $other_data['datetime'] = $timestamp;
    $other_data['datetimeUTC'] = strtotime( get_gmt_from_date( $result->timestamp ) );
    $nice_time = date("d M Y H:i:s",$timestamp);
    $agent_from = false;
    
    if ($result->originates == 1) {
      $agent_from = 'Agent';
    } else if ($result->originates == 2) {
      // nothing
    } else if ($result->originates == 0 || $result->originates == 3) {
      $system_notification = true;
      $cuid = get_current_user_id();
      $is_agent = wplc_user_is_agent($cuid);
    
      if ($is_agent && $result->originates == 3 ) {
        /* this user is an agent and the notification is meant for an agent, therefore display it */
        $display_notification = true;
        
        /* check if this came from the front end.. if it did, then dont display it (if this code is removed, all notifications will be displayed to agents who are logged in and chatting with themselves during testing, which may cause confusion) */
        if (isset($_POST) && isset($_POST['action']) && sanitize_text_field( $_POST['action'] ) == "wplc_call_to_server_visitor") {
          $display_notification = false;
        }
      }
      else if (!$is_agent && $result->originates == 0) {
        /* this user is a not an agent and the notification is meant for a users, therefore display it */
        $display_notification = true;
      } else {
        /* this notification is not intended for this user */
        $display_notification = false;
      }
    }

    if (!$system_notification) {
      if (function_exists('wplc_encrypt_decrypt_msg')) {
        $msg = wplc_encrypt_decrypt_msg($msg);
      }

      $msg = apply_filters("wplc_filter_message_control_out", $msg);

      if (is_serialized($msg)) {
        $msg_array = maybe_unserialize( $msg );
        if (is_array($msg_array)) {
          $msg = $msg_array['m'];
        }
      }

      if ($transcript) {
        $msg = str_replace('img:', '', $msg);
        $msg = str_replace(':img', '', $msg);
        $msg = str_replace('video:', '', $msg);
        $msg = str_replace(':video', '', $msg);
        $msg = str_replace('vid:', '', $msg);
        $msg = str_replace(':vid', '', $msg);
        $msg = str_replace('link:', '', $msg);
        $msg = str_replace(':link', '', $msg);
        $msg = htmlentities($msg);
      }

      if (isset($result->afrom) && intval($result->afrom)>0) {
        $msg_array[$id]['afrom'] = intval($result->afrom); 
        $other_data['aid'] = intval($result->afrom);
      }

      if (isset($result->ato) && intval($result->ato)>0) {
        $msg_array[$id]['ato'] = intval($result->ato); 
      }

      /* use the new  "other" array to get the AID and agent name */
      if ($result->originates == '1' && isset($result->other)) {
        $other_data = maybe_unserialize($result->other);
        if (isset($other_data['aid'])) {
          $user_info = get_userdata(intval($other_data['aid']));
          $agent_from = $user_info->display_name;
        }
      }

      /* get the name of the USER if there is one */
      if ($result->originates == '2' && isset($result->msgfrom)) {
        $user_from = $result->msgfrom;
      } else {
        $user_from = 'User';
      }
      
      $msg_array[$id]['msg'] = $msg;
      
      if ($agent_from !== false) {
        $msg_hist .= $agent_from . ": " . $msg . "<br />";
      } else {
        $msg_hist .= $user_from . ": " . $msg . "<br />";
      }

      $msg_array[$id]['originates'] = $result->originates;
      $msg_array[$id]['other'] = $other_data;
    } else {
      /* this is a system notification */
      if ($display_notification) {
        if (is_serialized($msg)) {
          $tmp_array = maybe_unserialize($msg);
          if (is_array($tmp_array)) {
            $msg = $tmp_array['m'];
          }
        }
        $str = //"<span class='chat_time wplc-color-4'>".$nice_time."</span> <span class='wplc-system-notification wplc-color-4'>".htmlentities($msg)."</span>";
        $str = "<span class='wplc-system-notification wplc-color-4'>".htmlentities($msg)."</span>";
        if ($transcript) {
          $str = "<span class='wplc-system-notification wplc-color-4'>".htmlentities($msg)."</span><br />";
        }
        if (!isset($msg_array[$id])) { 
          $msg_array[$id] = array(); 
        }
        $other_data['preformatted'] = true; // this tells html must not be escaped
        $msg_array[$id]['msg'] = $str;
        $msg_array[$id]['other'] = $other_data;
        $msg_array[$id]['originates'] = $result->originates;
        $msg_hist .= $str;
      }
    }
  }

  if ($display == 'string') { 
    return $msg_hist; 
  } else { 
    return $msg_array; 
  }
}


/**
 * Mark all messages sent by an AGENT as read (a user has read them)
 *
 * @param  int      $cid    Chat ID
 * @return string           "ok"
 */
function wplc_mark_as_read_user_chat_messages($cid) {
    global $wpdb;
    global $wplc_tblname_msgs;

    $results = $wpdb->get_results($wpdb->prepare("SELECT *
            FROM $wplc_tblname_msgs
            WHERE `chat_sess_id` = %s AND `status` = '0' AND (`originates` = 1 OR `originates` = 0)
            ORDER BY `timestamp` DESC", $cid));

    foreach ($results as $result) {
        $id = $result->id;

        $wpdb->update(
            $wplc_tblname_msgs,
            array(
                'status' => 1
            ),
            array('id' => $id),
            array('%d'),
            array('%d')
        );

    }
    return "ok";


}
/**
 * Mark all messages sent by a USER as read (an agent has read them)
 *
 * @param  int      $cid    Chat ID
 * @return string           "ok"
 */
function wplc_mark_as_read_agent_chat_messages($cid, $aid) {
    global $wpdb;
    global $wplc_tblname_msgs;

    $results = $wpdb->get_results($wpdb->prepare("SELECT *
            FROM $wplc_tblname_msgs
            WHERE `chat_sess_id` = %s AND `ato` = %d
            ORDER BY `timestamp` DESC", $cid, intval($aid)));

    foreach ($results as $result) {
        $id = $result->id;

        $wpdb->update(
            $wplc_tblname_msgs,
            array(
                'status' => 1
            ),
            array('id' => $id),
            array('%d'),
            array('%d')
        );

    }
    return "ok";

}

function wplc_return_admin_chat_messages($cid) {
  if (wplc_user_is_agent()) {
    global $wpdb;
    global $wplc_tblname_msgs;
    /**
     * `Originates` - codes:
     *     0 - System notification to be delivered to users
     *     1 - Message from an agent
     *     2 - Message from a user
     *     3 - System notification to be delivered to agents
     *
     */
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_msgs WHERE `chat_sess_id` = %s AND `status` = '0' AND (`originates` = '2' OR `originates` = '3') ORDER BY `timestamp` ASC", $cid));
    $msg_hist = array();

    foreach ($results as $result) {
      $system_notification = false;
      $id = $result->id;
      wplc_mark_as_read_admin_chat_messages($id);
      $msg = $result->msg;
      if ( isset( $result->other ) ) { $other_data = maybe_unserialize( $result->other ); } else { $other_data = array(); }
      if ($other_data == '') { $other_data = array(); }
      $timestamp = strtotime( $result->timestamp );
      $other_data['datetime'] = $timestamp;
      $other_data['datetimeUTC'] = strtotime( get_gmt_from_date( $result->timestamp ) );
      if (intval($result->originates) == 3) {
        // system notifications from version 7
        $system_notification = true;
      }
      if (!$system_notification) {
        /* this is a normal message */
        if (function_exists('wplc_encrypt_decrypt_msg')) {
          $msg = wplc_encrypt_decrypt_msg($msg);
        }
        $msg_array = maybe_unserialize( $msg );
        if (is_array( $msg_array)) {
          $msg = $msg_array['m'];
        }
        $msg = stripslashes($msg);
        $msg = apply_filters("wplc_filter_message_control_out",$msg);
        $msg = sanitize_text_field(stripslashes($msg)); // TODO: check not optimal sanitization - check all wplc_return_admin_chat_messages references too

        $msg_hist[$id]['msg'] = $msg;
        $msg_hist[$id]['originates'] = intval($result->originates);
        $msg_hist[$id]['other'] = $other_data;
      } else {
        /* add the system notification to the list */
      if ( isset( $msg_hist[$id] ) ) { $msg_hist[$id] = array(); }
        $msg_hist[$id]['msg'] = $msg;
        $msg_hist[$id]['other'] = $other_data;
        $msg_hist[$id]['originates'] = intval($result->originates);
      }
    }
    return $msg_hist;
  } else {
    return "security issue";
  }
}

/**
 * Mark all messages sent by a USER as read (an agent has read them)
 *
 * @param  int      $cid    Chat ID
 * @return string           "ok"
 */
function wplc_mark_as_read_admin_chat_messages( $mid ) {
  if (wplc_user_is_agent()) {
    global $wpdb;
    global $wplc_tblname_msgs;
    $wpdb->update(
      $wplc_tblname_msgs,
      array(
        'status' => 1
      ),
      array('id' => $mid),
      array('%d'),
      array('%d')
    );
    return "ok";
  }
  return "security issue"; 
}

function wplc_return_chat_session_variable($cid) {
    global $wpdb;
    global $wplc_tblname_chats;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %d", $cid));
    foreach ($results as $result) {
        return $result->session;
    }
}


/**
 * Return chat status as integer
 * @param  cid   chatid
 * @return int
 */
function wplc_return_chat_status($cid) {
    global $wpdb;
    global $wplc_tblname_chats;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %s", $cid));
    foreach ($results as $result) {
        return intval($result->status);
    }
}

function wplc_return_status($status) {
    if ($status == 1) {
        return __("complete",'wp-live-chat-support');
    }
    if ($status == 2) {
        return __("pending", 'wp-live-chat-support');
    }
    if ($status == 3) {
        return __("active", 'wp-live-chat-support');
    }
    if ($status == 4) {
        return __("deleted", 'wp-live-chat-support');
    }
    if ($status == 5) {
        return __("browsing", 'wp-live-chat-support');
    }
    if ($status == 6) {
        return __("requesting chat", 'wp-live-chat-support');
    }
    if($status == 8){
        return __("Chat Ended - User still browsing", 'wp-live-chat-support');
    }
    if($status == 9){
        return __("User is browsing but doesn't want to chat", 'wp-live-chat-support');
    }

}

add_filter("wplc_filter_mail_body","wplc_filter_control_mail_body",10,2);
function wplc_filter_control_mail_body($header,$msg) {
    $primary_bg_color = apply_filters("wplc_mailer_bg_color", "#0596d4"); //Default orange
    $body = '
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">      
    <html>
    
    <body>



        <table id="" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-family: sans-serif;">
        <tbody>
          <tr>
            <td width="100%" style="padding: 30px 20px 100px 20px;">
              <table cellpadding="0" cellspacing="0" class="" width="100%" style="border-collapse: separate;">
                <tbody>
                  <tr>
                    <td style="padding-bottom: 20px;">
                      
                      <p>'.$header.'</p>
                      <hr>
                    </td>
                  </tr>
                </tbody>
              </table>

              <table id="" cellpadding="0" cellspacing="0" class="" width="100%" style="border-collapse: separate; font-size: 14px;">
              <tbody>
                  <tr>
                    <td class="sortable-list ui-sortable">
                        '.nl2br($msg).'
                    </td>
                  </tr>
                </tbody>
              </table>

              <table cellpadding="0" cellspacing="0" class="" width="100%" style="border-collapse: separate; max-width:100%;">
                <tbody>
                  <tr>
                    <td style="padding-top:20px;">
                      <table border="0" cellpadding="0" cellspacing="0" class="" width="100%">
                        <tbody>
                          <tr>
                            <td id="">
                             <p>'.site_url().'</p>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>


        
        </div>
    </body>
</html>
            ';
            return $body;
}


add_filter("wplc_mailer_bg_color","wplc_fitler_mailer_bg_color",10,1);
function wplc_fitler_mailer_bg_color($default_color) {
    $wplc_settings = wplc_get_options();
    $wplc_theme = wplc_get_theme();

    if($wplc_theme == 'theme-1') {
        $default_color = "#DB0000";
    } else if ($wplc_theme == 'theme-2'){
        $default_color = "#000000";
    } else if ($wplc_theme == 'theme-3'){
        $default_color = "#B97B9D";
    } else if ($wplc_theme == 'theme-4'){
        $default_color = "#1A14DB";
    } else if ($wplc_theme == 'theme-5'){
        $default_color = "#3DCC13";
    } else if ($wplc_theme == 'theme-6'){
        //Check what color is selected in palette
        if (isset($wplc_settings["wplc_settings_color1"])) {
            $default_color = "#" . $wplc_settings["wplc_settings_color1"];
        }
    }
    return $default_color;
}

/**
 * Send an email to the admin based on the settings in the settings page
 * @param  string $reply_to      email of the user
 * @param  string $reply_to_name name of the user
 * @param  string $subject       subject
 * @param  string $msg           message being emailed
 * @return void
 * @since  5.1.00
 */
function wplcmail($reply_to,$reply_to_name,$subject,$msg) {
  $wplc_settings = wplc_get_options();
  if (isset($wplc_settings['wplc_pro_chat_email_address'])) {
    $email_address = $wplc_settings['wplc_pro_chat_email_address'];
  } else {
    $email_address = get_option('admin_email');
  }
  $email_address = explode(',', $email_address);
  $headers[] = 'Content-type: text/html';
  $headers[] = 'Reply-To: '.$reply_to_name.'<'.$reply_to.'>';
  if ($email_address) {
    foreach($email_address as $email) {
      /* Send offline message to each email address */
      $overbody = apply_filters("wplc_filter_mail_body",$subject,$msg);
      if (!wp_mail($email, $subject, $overbody, $headers)) {
        $error = date("Y-m-d H:i:s") . " WP-Mail Failed to send \n";
        error_log($error);
      }
    }
  }
  return;
}

/**
 * Sends offline messages to the admin (normally via ajax)
 * @param  string $name  Name of the user
 * @param  string $email Email of the user
 * @param  string $msg   The message being sent to the admin
 * @param  int    $cid   Chat ID
 * @return void
 */
function wplc_send_offline_msg($name,$email,$msg,$cid) {
    $subject = apply_filters("wplc_offline_message_subject_filter", __("WP Live Chat by 3CX - Offline Message from ", 'wp-live-chat-support') );
    $msg = __("Name", 'wp-live-chat-support').": $name \n".
    __("Email", 'wp-live-chat-support').": $email\n".
    __("Message", 'wp-live-chat-support').": $msg\n\n".
    __("Via WP Live Chat by 3CX", 'wp-live-chat-support');
    wplcmail($email,$name, $subject, $msg);
    return;
}


/**
 * Saves offline messages to the database
 * @param  string $name    User name
 * @param  string $email   User email
 * @param  string $message Message being saved
 * @return Void
 * @since  5.1.00
 */
function wplc_store_offline_message($name, $email, $message){
    global $wpdb;
    global $wplc_tblname_offline_msgs;

    $ins_array = array(
        'timestamp' => current_time('mysql'),
        'name' => sanitize_text_field($name),
        'email' => sanitize_email($email),
        'message' => implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $message ) ) ),
        'ip' => wplc_get_user_ip(),
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
    );

    $rows_affected = $wpdb->insert( $wplc_tblname_offline_msgs, $ins_array );
    return;
}



function wplc_user_initiate_chat($name,$email,$cid = null,$session) {

    global $wpdb;
    global $wplc_tblname_chats;

    $user_data = array(
        'ip' => wplc_get_user_ip(),
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
    );
    $wplc_ce_ip = null;

    if(function_exists('wplc_ce_activate')){
        /* Log the chat for statistical purposes as well */
        if(function_exists('wplc_ce_record_initial_chat')){
            wplc_ce_record_initial_chat($name, $email, $cid, $wplc_ce_ip, sanitize_text_field($_SERVER['HTTP_REFERER']));
        }
    }

    if ($cid != null) {
        /* change from a visitor to a chat */

        /**
         * This helps us identify if this user needs to be answered. The user can start typing so long but an agent still needs to answer the chat
         * @var serialized array
         */
        $chat_data = wplc_get_chat_data($cid,__LINE__);

        if (isset($chat_data->other)) {
            $other_data = maybe_unserialize( $chat_data->other );
            $other_data['unanswered'] = true;

            $other_data = apply_filters("wplc_start_chat_hook_other_data_hook", $other_data);
            if (!isset($other_data['welcome'])) {
                $other_data['welcome'] = true;
            }

        } else {
            $other_data = array();
            $other_data['welcome'] = true;

        }


        $wpdb->update(
            $wplc_tblname_chats,
            array(
                'status' => 2,
                'timestamp' => current_time('mysql'),
                'name' => $name,
                'email' => $email,
                'session' => $session,
                'ip' => maybe_serialize($user_data),
                'url' => sanitize_text_field($_SERVER['HTTP_REFERER']),
                'last_active_timestamp' => current_time('mysql'),
                'other' => maybe_serialize($other_data)
            ),
            array('id' => $cid),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ),
            array('%d')
        );

        do_action("wplc_hook_initiate_chat",array("cid" => $cid, "name" => $name, "email" => $email));

        do_action("wplc_start_chat_hook_after_data_insert", $cid);
        return $cid;
    }
    else {
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
                'session' => $session,
                'ip' => maybe_serialize($user_data),
                'url' => sanitize_text_field($_SERVER['HTTP_REFERER']),
                'last_active_timestamp' => current_time('mysql'),
                'other' => maybe_serialize($other_data)
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
                '%s'
            )
        );


        $lastid = $wpdb->insert_id;


        do_action("wplc_start_chat_hook_after_data_insert", $lastid);
        return $lastid;
    }

}


function wplc_update_chat_statuses() {
    global $wpdb;
    global $wplc_tblname_chats;
    $results = $wpdb->get_results(
        "
        SELECT *
        FROM $wplc_tblname_chats
        WHERE `status` = '2' OR `status` = '3' OR `status` = '5' or `status` = '8' or `status` = '9' or `status` = '10' or `status` = 12
        "
    );
    foreach ($results as $result) {
        $id = $result->id;
        $timestamp = strtotime($result->last_active_timestamp);
        $datenow = current_time('timestamp');
        $difference = $datenow - $timestamp;



        if (intval($result->status) == 2) {
            if ($difference >= 30) { // 60 seconds max
                wplc_change_chat_status($id,12);
            }
        }
        else if (intval($result->status) == 12) {
            if ($difference >= 30) { // 30 seconds max
                wplc_change_chat_status($id,0);
            }
        }
        else if (intval($result->status) == 3) {
            if ($difference >= 300) { // 5 minutes
                wplc_change_chat_status($id,1);
            }
        }
        else if (intval($result->status) == 5) {
            if ($difference >= 120) { // 2 minute timeout
                wplc_change_chat_status($id,7); // 7 - timedout
            }
        } else if(intval($result->status) == 8){ // chat is complete but user is still browsing
            if ($difference >= 45) { // 30 seconds
                wplc_change_chat_status($id,1); // 1 - chat is now complete
            }
        } else if(intval($result->status) == 9 || $result->status == 10){
            if ($difference >= 120) { // 120 seconds
                wplc_change_chat_status($id,7); // 7 - timedout
            }
        }
    }
}
function wplc_check_pending_chats(){
    global $wpdb;
    global $wplc_tblname_chats;
    $sql = "SELECT * FROM `$wplc_tblname_chats` WHERE `status` = 2";
    $wpdb->query($sql);
    $results = $wpdb->get_results($sql);
    if($results){
        foreach ($results as $result) {
            $other = maybe_unserialize($result->other);
            if (isset($other['unanswered'])) {
                return true;
            }
        }

    }
    return false;
}


function wplc_return_browser_image($string,$size) {
    switch($string) {

        case "Internet Explorer":
            return "internet-explorer_".$size."x".$size.".png";
            break;
        case "Mozilla Firefox":
            return "firefox_".$size."x".$size.".png";
            break;
        case "Opera":
            return "opera_".$size."x".$size.".png";
            break;
        case "Google Chrome":
            return "chrome_".$size."x".$size.".png";
            break;
        case "Safari":
            return "safari_".$size."x".$size.".png";
            break;
        case "Other browser":
            return "web_".$size."x".$size.".png";
            break;
        default:
            return "web_".$size."x".$size.".png";
            break;
    }


}
function wplc_return_browser_string($user_agent) {
if(strpos($user_agent, 'MSIE') !== FALSE)
   return 'Internet Explorer';
 elseif(strpos($user_agent, 'Trident') !== FALSE) //For Supporting IE 11
    return 'Internet Explorer';
elseif(strpos($user_agent, 'Edge') !== FALSE)
    return 'Internet Explorer';
 elseif(strpos($user_agent, 'Firefox') !== FALSE)
   return 'Mozilla Firefox';
 elseif(strpos($user_agent, 'Chrome') !== FALSE)
   return 'Google Chrome';
 elseif(strpos($user_agent, 'Opera Mini') !== FALSE)
   return "Opera";
 elseif(strpos($user_agent, 'Opera') !== FALSE)
   return "Opera";
 elseif(strpos($user_agent, 'Safari') !== FALSE)
   return "Safari";
 else
   return 'Other browser';
}

function wplc_admin_display_missed_chats() {

  global $wpdb;
  global $wplc_tblname_chats;
	
  $delete_missed_chats_nonce = wp_create_nonce('delete_missed_chats');

  if (isset($_GET['wplc_action']) && $_GET['wplc_action'] == 'remove_missed_cid') {
    if (isset($_GET['cid'])) {
      $cid = "";
      if (!empty($_GET['cid'])) {
        $cid = intval($_GET['cid']);
      }
      
      if (isset($_GET['wplc_confirm']) && isset($_GET['nonce'])) {
		  
		$nonce = $_GET['nonce'];
		  
		if (  wp_verify_nonce( $nonce, 'delete_missed_chats' ) ) {  
			//Confirmed - delete
			$delete_sql = "";
			if (empty($_GET['cid'])) {
			  exit('No CID?');
			}
			$cid = intval($_GET['cid']);
			$delete_sql = "DELETE FROM $wplc_tblname_chats WHERE `id` = '%d' LIMIT 1";
			$delete_sql = $wpdb->prepare($delete_sql, $cid);
			$wpdb->query($delete_sql);
			if ($wpdb->last_error) {
			  echo "<div class='update-nag' style='margin-top: 0px;margin-bottom: 5px;'>".__("Error: Could not delete chat", 'wp-live-chat-support')."<br></div>";
			} else {
			  echo "<div class='update-nag' style='margin-top: 0px;margin-bottom: 5px;border-color:#67d552;'>".__("Chat Deleted", 'wp-live-chat-support')."<br></div>";
			}
		}else {
			wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
		}
		  
		  
      } else {
        //Prompt
        echo "<div class='update-nag' style='margin-top: 0px;margin-bottom: 5px;'>".__("Are you sure you would like to delete this chat?", 'wp-live-chat-support')."<br>
<a class='button' href='?page=wplivechat-menu-missed-chats&wplc_action=remove_missed_cid&cid=".$cid."&wplc_confirm=1&nonce=".$delete_missed_chats_nonce."'>".__("Yes", 'wp-live-chat-support')."</a> <a class='button' href='?page=wplivechat-menu-missed-chats'>".__("No", 'wp-live-chat-support')."</a>
</div>";
      }
    }
  }

  echo "<table class=\"wp-list-table widefat fixed \" cellspacing=\"0\">
<thead>
<tr>
<th class='manage-column column-id'><span>" . __("Date", 'wp-live-chat-support') . "</span></th>
<th scope='col' id='wplc_name_colum' class='manage-column column-id'><span>" . __("Name", 'wp-live-chat-support') . "</span></th>
<th scope='col' id='wplc_email_colum' class='manage-column column-id'>" . __("Email", 'wp-live-chat-support') . "</th>
<th scope='col' id='wplc_url_colum' class='manage-column column-id'>" . __("URL", 'wp-live-chat-support') . "</th>
<th scope='col' id='wplc_url_colum' class='manage-column column-id'>" . __("Action", 'wp-live-chat-support') . "</th>
</tr>
</thead>
<tbody id=\"the-list\" class='list:wp_list_text_link'>";

  $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
  $limit = 20; // number of rows in page
  $offset = ( $pagenum - 1 ) * $limit;
  $total = $wpdb->get_var( "SELECT COUNT(`id`) FROM $wplc_tblname_chats WHERE `status` = 0" );
  $num_of_pages = ceil( $total / $limit );

  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `status` = 0 ORDER BY `timestamp` DESC LIMIT %d OFFSET %d", $limit, $offset));

  if (!$results) {
    echo "<tr><td></td><td>" . __("You have not missed any chat requests.", 'wp-live-chat-support') . "</td></tr>";
  } else {
    foreach ($results as $result) {
      $hist_nonce = wp_create_nonce('wplc_history_nonce');
      $url = admin_url('admin.php?page=wplivechat-menu&action=history&cid=' . $result->id . "&wplc_history_nonce=" . $hist_nonce);
      $url2 = admin_url('admin.php?page=wplivechat-menu&action=download_history&type=csv&cid=' . $result->id . "&wplc_history_nonce=" . $hist_nonce);
      $url3 = "?page=wplivechat-menu-missed-chats&wplc_action=remove_missed_cid&cid=" . $result->id;
      $actions = "<a href='$url' class='button' title='".__('View Chat History', 'wp-live-chat-support')."' target='_BLANK' id=''><i class='fa fa-eye'></i></a> <a href='$url2' class='button' title='".__('Download Chat History', 'wp-live-chat-support')."' target='_BLANK' id=''><i class='fa fa-download'></i></a> <a href='$url3' class='button'><i class='fa fa-trash-alt'></i></a>";

      echo "<tr id=\"record_" . intval($result->id) . "\">";
      echo "<td class='chat_id column-chat_d'>" . sanitize_text_field($result->timestamp) . "</td>";
      echo "<td class='chat_name column_chat_name' id='chat_name_" . intval($result->id) . "'><img src=\"//www.gravatar.com/avatar/" . md5($result->email) . "?s=30&d=mm\"  class='wplc-user-message-avatar' align=\"absmiddle\"/> " . sanitize_text_field($result->name) . "</td>";
      echo "<td class='chat_email column_chat_email' id='chat_email_" . intval($result->id) . "'><a href='mailto:" . sanitize_text_field($result->email) . "' title='Email " . ".$result->email." . "'>" . sanitize_text_field($result->email) . "</a></td>";
      echo "<td class='chat_name column_chat_url' id='chat_url_" . intval($result->id) . "'>" . esc_url($result->url) . "</td>";
      echo "<td class='chat_name column_chat_url'>".$actions."</td>";
      echo "</tr>";
    }
  }
  
  echo "</tbody></table>";

  $page_links = paginate_links(array(
    'base' => add_query_arg( 'pagenum', '%#%' ),
    'format' => '',
    'prev_text' => '&laquo;',
    'next_text' => '&raquo;',
    'total' => $num_of_pages,
    'current' => $pagenum
  ));

  if ( $page_links ) {
    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0;float:none;text-align:center;">' . $page_links . '</div></div>';
  }
}

/**
 * Compares the users IP address to the list in the banned IPs in the settings page
 * @return BOOL
 */
function wplc_is_user_banned(){
    $banned_ip = get_option('WPLC_BANNED_IP_ADDRESSES');
    if($banned_ip){
        $banned_ip = maybe_unserialize($banned_ip);
        $banned = 0;
        if (is_array($banned_ip)) {
            foreach($banned_ip as $ip){

                if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                }

                if(isset($ip_address)){
                    if($ip == $ip_address){
                        $banned++;
                    }
                } else {
                    $banned = 0;
                }
            }
        } else {
            return 0;
        }
    } else {
        $banned = 0;
    }
    return $banned;
}




function wplc_return_animations(){

    $wplc_settings = wplc_get_options();

    if ($wplc_settings["wplc_settings_align"] == 1) {
        $original_pos = "bottom_left";
        $wplc_box_align = "bottom:0px;";
    } else if ($wplc_settings["wplc_settings_align"] == 2) {
        $original_pos = "bottom_right";
        $wplc_box_align = "bottom:0px;";
    } else if ($wplc_settings["wplc_settings_align"] == 3) {
        $original_pos = "left";
        $wplc_box_align = " bottom:100px;";
        $wplc_class = "wplc_left";
    } else if ($wplc_settings["wplc_settings_align"] == 4) {
        $original_pos = "right";
        $wplc_box_align = "bottom:100px;";
        $wplc_class = "wplc_right";
    }

    $animation_data = array();

    if(isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-1'){

        if($original_pos == 'bottom_right'){
            $wplc_starting_point = 'margin-bottom: -350px; right: 20px;';
            $wplc_animation = 'animation-1';
        } else if ($original_pos == 'bottom_left'){
            $wplc_starting_point = 'margin-bottom: -350px; left: 20px;';
            $wplc_animation = 'animation-1';
        } else if ($original_pos == 'left'){
            $wplc_starting_point = 'margin-bottom: -350px; left: 0px;';
            $wplc_box_align = "left:0; bottom:100px;";
            $wplc_animation = 'animation-1';
        } else if ($original_pos == 'right'){
            $wplc_starting_point = 'margin-bottom: -350px; right: 0px;';
            $wplc_animation = 'animation-1';
            $wplc_box_align = "right:0; bottom:100px;";
        }

        $animation_data['animation'] = $wplc_animation;
        $animation_data['starting_point'] = $wplc_starting_point;
        $animation_data['box_align'] =  $wplc_box_align;

    } else if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-2'){

        if($original_pos == 'bottom_right'){
            $wplc_starting_point = 'margin-bottom: 0px; right: -300px;';
            $wplc_animation = 'animation-2-br';
        } else if ($original_pos == 'bottom_left'){
            $wplc_starting_point = 'margin-bottom: 0px; left: -300px;';
            $wplc_animation = 'animation-2-bl';
        } else if ($original_pos == 'left'){
            $wplc_starting_point = 'margin-bottom: 0px; left: -999px;';
            $wplc_animation = 'animation-2-l';
        } else if ($original_pos == 'right'){
            $wplc_starting_point = 'margin-bottom: 0px; right: -999px;';
            $wplc_animation = 'animation-2-r';
        }

        $animation_data['animation'] = $wplc_animation;
        $animation_data['starting_point'] = $wplc_starting_point;
        $animation_data['box_align'] =  $wplc_box_align;

    } else if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-3'){

        $wplc_animation = 'animation-3';

        if($original_pos == 'bottom_right'){
            $wplc_starting_point = 'margin-bottom: 0; right: 20px; display: none;';
        } else if ($original_pos == 'bottom_left'){
            $wplc_starting_point = 'margin-bottom: 0px; left: 20px; display: none;';
        } else if ($original_pos == 'left'){
            $wplc_starting_point = 'margin-bottom: 100px; left: 0px; display: none;';
        } else if ($original_pos == 'right'){
            $wplc_starting_point = 'margin-bottom: 100px; right: 0px; display: none;';
        }

        $animation_data['animation'] = $wplc_animation;
        $animation_data['starting_point'] = $wplc_starting_point;
        $animation_data['box_align'] =  $wplc_box_align;

    } else if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-4'){
        // Dont use an animation

        $wplc_animation = "animation-4";

        if($original_pos == 'bottom_right'){
            $wplc_starting_point = 'margin-bottom: 0; right: 20px; display: none;';
        } else if ($original_pos == 'bottom_left'){
            $wplc_starting_point = 'margin-bottom: 0px; left: 20px; display: none;';
        } else if ($original_pos == 'left'){
            $wplc_starting_point = 'margin-bottom: 100px; left: 0px; display: none;';
        } else if ($original_pos == 'right'){
            $wplc_starting_point = 'margin-bottom: 100px; right: 0px; display: none;';
        }

        $animation_data['animation'] = $wplc_animation;
        $animation_data['starting_point'] = $wplc_starting_point;
        $animation_data['box_align'] =  $wplc_box_align;

    } else {

        if($original_pos == 'bottom_right'){
            $wplc_starting_point = 'margin-bottom: 0; right: 20px; display: none;';
        } else if ($original_pos == 'bottom_left'){
            $wplc_starting_point = 'margin-bottom: 0px; left: 20px; display: none;';
        } else if ($original_pos == 'left'){
            $wplc_starting_point = 'margin-bottom: 100px; left: 0px; display: none;';
        } else if ($original_pos == 'right'){
            $wplc_starting_point = 'margin-bottom: 100px; right: 0px; display: none;';
        }

        $wplc_animation = 'none';

        $animation_data['animation'] = $wplc_animation;
        $animation_data['starting_point'] = $wplc_starting_point;
        $animation_data['box_align'] =  $wplc_box_align;
    }

    return $animation_data;
}


add_action("wplc_advanced_settings_above_performance", "wplc_advanced_settings_above_performance_control", 10, 1);
function wplc_advanced_settings_above_performance_control($wplc_settings) {
  echo "<tr>
<td width='350'>
".__("Open chat window via", 'wp-live-chat-support').":
</td>
<td>
<select name='wplc_elem_trigger_action'>
<option value='0' ".($wplc_settings['wplc_elem_trigger_action'] == 0 ? "selected" : "").">".__("Click", 'wp-live-chat-support')."</option>
<option value='1' ".($wplc_settings['wplc_elem_trigger_action'] == 1 ? "selected" : "").">".__("Hover", 'wp-live-chat-support')."</option>
</select>
".__("element with", 'wp-live-chat-support').": 
<select name='wplc_elem_trigger_type'>
<option value='0' ".($wplc_settings['wplc_elem_trigger_type'] == 0 ? "selected" : "").">".__("Class", 'wp-live-chat-support')."</option>
<option value='1' ".($wplc_settings['wplc_elem_trigger_type'] == 1 ? "selected" : "").">".__("ID", 'wp-live-chat-support')."</option>
</select>
<input type='text' name='wplc_elem_trigger_id' value='".htmlentities($wplc_settings['wplc_elem_trigger_id'])."'>
</td>
</tr>";
}

/**
 * Reverse of wplc_return_chat_id_by_rel
 */
function wplc_return_chat_rel_by_id($cid) {
  global $wpdb;
  global $wplc_tblname_chats;
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %s LIMIT 1", $cid));
  if ($results) {
    foreach ($results as $result) {
      if (isset($result->rel)) {
        return $result->rel;
      }
    }
  }
  error_log('wplc_return_chat_rel_by_id cannot find rel for id='.$cid);
  return null;
}

function wplc_all_avatars() {
  $users = wplc_get_agent_users();
  $avatars = array();
  foreach ($users as $user) {
    $avatars[$user->data->ID] = wplc_get_avatar($user->data->ID);
  }
  return $avatars;
}

function wplc_get_avatar($id) {
    $wplc_settings = wplc_get_options();
    $user = get_user_by( 'id', $id );
    if ($wplc_settings['wplc_avatar_source'] == 'gravatar') {
        return '//www.gravatar.com/avatar/' . md5( strtolower( trim( $user->data->user_email ) ) );
    } elseif ($wplc_settings['wplc_avatar_source'] == 'wp_avatar') {
        if (function_exists('get_wp_user_avatar')) {
            return get_wp_user_avatar_src($id);
        } else {
            return '//www.gravatar.com/avatar/' . md5( strtolower( trim( $user->data->user_email ) ) );
        }
    } else {
        return '//www.gravatar.com/avatar/' . md5( strtolower( trim( $user->data->user_email ) ) );
    }
}

function wplc_get_admin_picture() {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_chat_pic']) {
    return urldecode($wplc_settings['wplc_chat_pic']);
  }
}

/**
 * Decides whether or not to show the chat box based on the include/exclude pages in the settings page
 * @return [type] [description]
 */
function wplc_display_chat_contents() {

  if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
    return false;
  }

  $wplc_settings = wplc_get_options();
  $display_contents = true;

  $post_id = get_the_ID();
  if (!$post_id || $post_id === NULL) {
    $url = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $post_id = url_to_postid($url); 
  }

  if (!empty($wplc_settings['wplc_include_on_pages']) || !empty($wplc_settings['wplc_exclude_from_pages'])) {
    if (!empty($wplc_settings['wplc_include_on_pages'])) {
      $include_on_pages = explode(',', $wplc_settings['wplc_include_on_pages']);
      foreach($include_on_pages as $key => $val) {
        $include_on_pages[$key] = intval($val);
      }
      $include_array = array();
      foreach($include_on_pages as $page){
        $include_array [intval($page)] = intval($page);
      }
      if (!isset($include_array[$post_id])) {
        $display_contents = false;
      }
    } else {
      /* Exclude from the following pages */
      $exclude_from_pages = explode(',', $wplc_settings['wplc_exclude_from_pages']);
      $exclude_array = array();
      foreach($exclude_from_pages as $page) {
        $exclude_array[$page] = intval($page);
      }
      $exclude_from_page = array_search($post_id, $exclude_array);
      if ($exclude_from_page !== false) {
        $display_contents = false;
      }
    }	        
  }

  if ($wplc_settings['wplc_exclude_home'] && (is_home() || is_front_page() || $_SERVER['REQUEST_URI'] == '/')) {
    $display_contents = false;
  }

  if ($wplc_settings['wplc_exclude_archive'] && is_archive()) {
    $display_contents = false;
  }

  if (!empty($wplc_settings['wplc_exclude_post_types'] ) && in_array( get_post_type($post_id), $wplc_settings['wplc_exclude_post_types'] )  ) {
    $display_contents = false;
  }

  return $display_contents;
}

add_action("admin_init","wplc_control_logged_in_mrg");
function wplc_control_logged_in_mrg() {
  if (wplc_user_is_agent()) {
    wplc_update_agent_time();
    wplc_check_agents_timeout();
  }
}

add_action("init","wplc_control_logged_out_mrg");
function wplc_control_logged_out_mrg() {
  if (!isset($_GET['role'])) {
    add_action('pre_get_users', 'wplc_advanced_access_manager_compatibility_mrg', 1000);
  } else {
    return false;
  }
}

/**
 * Advanced Access Manager compatibility.
 */
function wplc_advanced_access_manager_compatibility_mrg($query) {
    $query->query_vars['role__not_in'] = array();
}

add_action('init', 'wplc_mrg_version_control');


function wplc_mrg_version_control() {
  $current_version = get_option("wplc_current_version");
  if (!isset($current_version) || $current_version != WPLC_PLUGIN_VERSION) {
    wplc_mrg_update_db();
  }    
}



function wplc_mrg_update_db( $networkwide = false ) {
    global $wpdb;
    global $wplc_tblname_chats;

    global $wplc_tblname_chat_ratings;
    global $wplc_tblname_chat_triggers;


	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		if ( $networkwide ) {
			$old_blog = $wpdb->blogid;
			$blog_ids =  $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$sql = " SHOW COLUMNS FROM {$wpdb->prefix}wplc_chat_sessions WHERE `Field` = 'agent_id'";
				$results = $wpdb->get_results($sql);
				if (!$results) {
					$sql = "ALTER TABLE {$wpdb->prefix}wplc_chat_sessions ADD `agent_id` INT(11) NOT NULL ;";
					$wpdb->query($sql);
				}

				$department_field_sql = " SHOW COLUMNS FROM {$wpdb->prefix}wplc_chat_sessions WHERE `Field` = 'department_id'";
				$results = $wpdb->get_results($department_field_sql);
				if (!$results) {
					$department_field_sql = "ALTER TABLE {$wpdb->prefix}wplc_chat_sessions ADD `department_id` INT(11) NOT NULL ;";
					$wpdb->query($department_field_sql);
				}
			}
			switch_to_blog( $old_blog );
		}
	} else {
		$sql = " SHOW COLUMNS FROM $wplc_tblname_chats WHERE `Field` = 'agent_id'";
		$results = $wpdb->get_results($sql);
		if (!$results) {
			$sql = "ALTER TABLE `$wplc_tblname_chats` ADD `agent_id` INT(11) NOT NULL ;";
			$wpdb->query($sql);
		}

		$department_field_sql = " SHOW COLUMNS FROM $wplc_tblname_chats WHERE `Field` = 'department_id'";
		$results = $wpdb->get_results($department_field_sql);
		if (!$results) {
			$department_field_sql = "ALTER TABLE `$wplc_tblname_chats` ADD `department_id` INT(11) NOT NULL ;";
			$wpdb->query($department_field_sql);
		}
	}

    $sql2 = "
        CREATE TABLE " . $wplc_tblname_chat_ratings . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          timestamp datetime NOT NULL,
          cid int(11) NOT NULL,
          aid int(11) NOT NULL,
          rating int(11) NOT NULL,
          comment varchar(700) NOT NULL,
          notified tinyint(1) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

     $sql3 = "
        CREATE TABLE " . $wplc_tblname_chat_triggers . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name varchar(700) NOT NULL,
          type int(11) NOT NULL,
          content longtext NOT NULL,
          show_content tinyint(1) NOT NULL,
          status tinyint(1) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql2);
    dbDelta($sql3);

    do_action("wplc_pro_update_db_hook");

    $admins = get_role('administrator');
    $admins->add_cap('wplc_ma_agent');
    $uid = get_current_user_id();
    update_user_meta($uid, 'wplc_ma_agent', 1);
    wplc_update_agent_time($uid);
}


function wplc_mrg_create_macro_post_type() {
    $labels = array(
        'name' => __('Quick Responses', 'wp-live-chat-support'),
        'singular_name' => __('Quick Response', 'wp-live-chat-support'),
        'add_new' => __('New Quick Response', 'wp-live-chat-support'),
        'add_new_item' => __('Add New Quick Response', 'wp-live-chat-support'),
        'edit_item' => __('Edit Quick Response', 'wp-live-chat-support'),
        'new_item' => __('New Quick Response', 'wp-live-chat-support'),
        'all_items' => __('Quick Responses', 'wp-live-chat-support'),
        'view_item' => __('View Quick Responses', 'wp-live-chat-support'),
        'search_items' => __('Search Quick Responses', 'wp-live-chat-support'),
        'not_found' => __('No Quick Responses found', 'wp-live-chat-support'),
        'not_found_in_trash' => __('No Quick Responses found in the Trash', 'wp-live-chat-support'),
        'menu_name' => __('Quick Responses', 'wp-live-chat-support')
    );
    $args = array(
        'labels' => $labels,
        'description' => __('Quick Responses for WP Live Chat by 3CX', 'wp-live-chat-support'),
        'public' => false,
        'menu_position' => 80,
        'show_in_nav_menus' => true,
        'show_in_menu' => 'wplivechat-menu', 
        'hierarchical' => false,
        'rewrite' => array('slug' => 'wplc_quick_response'),
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'supports' => array('title', 'editor', 'revisions', 'author'),
        'has_archive' => true,
        'capabilities' => array(
            'edit_post' => 'edit_wplc_quick_response',
            'edit_posts' => 'edit_wplc_quick_response',
            'edit_others_posts' => 'edit_other_wplc_quick_response',
            'publish_posts' => 'publish_wplc_quick_response',
            'read_post' => 'read_wplc_quick_response',
            'read_private_posts' => 'read_private_wplc_quick_response',
            'delete_post' => 'delete_wplc_quick_response',
            'delete_posts' => 'delete_wplc_quick_response'
        ),
        'register_meta_box_cb' => 'wplc_add_quick_response_metaboxes_mrg'
    );
	
	if ( current_user_can( 'manage_options' ) ) {
        register_post_type('wplc_quick_response', $args);
	}
    
}

add_action( 'add_meta_boxes', 'wplc_add_quick_response_metaboxes_mrg' );
function wplc_add_quick_response_metaboxes_mrg() {
	add_meta_box( 'wplc_quick_response_number', __( 'Sort Order', 'wp-live-chat-support'), 'wplc_quick_response_number_cb_mrg', 'wplc_quick_response', 'side', 'default' );
}

function wplc_quick_response_number_cb_mrg() {
	global $post;

	echo '<input type="hidden" id="wplc_quick_response_number_noncename" name="wplc_quick_response_number_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';

	$wplc_quick_response_number = get_post_meta( $post->ID, 'wplc_quick_response_number', true );
	$wplc_quick_response_number = isset( $wplc_quick_response_number ) && '' !== $wplc_quick_response_number ? intval( $wplc_quick_response_number ) : 1;
	echo '<input type="number" id="wplc_quick_response_number" name="wplc_quick_response_number" min="1" value="' . intval( $wplc_quick_response_number ) . '" />';
}

add_action( 'save_post', 'wplc_quick_response_number_save_mrg', 1, 2 );
function wplc_quick_response_number_save_mrg( $post_id, $post ) {
	if ( ! isset( $_POST['wplc_quick_response_number_noncename'] ) || ! wp_verify_nonce( $_POST['wplc_quick_response_number_noncename'], plugin_basename( __FILE__ ) ) ) {
		return $post->ID;
	}

	if ( ! current_user_can( 'edit_wplc_quick_response' ) ) {
        return $post->ID;
	}

	if ( $post->post_type == 'revision' ) {
		return;
	}

	update_post_meta( $post->ID, 'wplc_quick_response_number', intval( $_POST['wplc_quick_response_number'] ) );

	return $post->ID;
}

add_filter( 'manage_edit-wplc_quick_response_columns', 'wplc_quick_response_number_column_mrg' );
function wplc_quick_response_number_column_mrg( $columns ) {
	$columns = array(
		'cb'     => '<input type="number" />',
		'title'  => __( 'Title', 'wp-live-chat-support'),
		'number' => __( 'Order', 'wp-live-chat-support'),
		'author' => __( 'Author', 'wp-live-chat-support'),
		'date'   => __( 'Date', 'wp-live-chat-support'),
	);

	return $columns;
}

add_action( 'manage_wplc_quick_response_posts_custom_column', 'wplc_quick_response_manage_number_column_mrg', 10, 2 );
function wplc_quick_response_manage_number_column_mrg( $column, $post_id ) {
	global $post;

	switch ( $column ) {
		case 'number':
			$wplc_quick_response_number = get_post_meta( $post_id, 'wplc_quick_response_number', true );
			echo ( empty( $wplc_quick_response_number ) ) ? 1 : intval( $wplc_quick_response_number );
			break;
		default:
			break;
	}
}

function wplc_quick_response_add_agent_caps_mrg() {
	if (wplc_user_is_agent()) {
		$user_id = get_current_user_id();
		$user = new WP_User($user_id);
		$user->add_cap('edit_wplc_quick_response');
		$user->add_cap('edit_wplc_quick_response');
		$user->add_cap('edit_other_wplc_quick_response');
		$user->add_cap('publish_wplc_quick_response');
		$user->add_cap('read_wplc_quick_response');
		$user->add_cap('read_private_wplc_quick_response');
		$user->add_cap('delete_wplc_quick_response');
	}
}
add_action( 'admin_init', 'wplc_quick_response_add_agent_caps_mrg' );

add_action("wplc_hook_admin_below_chat_box","wplc_hook_control_admin_below_chat_box",1,1);
function wplc_hook_control_admin_below_chat_box($result) {
	if ($result->status == 3) {
		$wplc_settings = wplc_get_options();
		$wplc_quick_response_order_by = isset( $wplc_settings['wplc_quick_response_orderby'] ) ? sanitize_text_field( $wplc_settings['wplc_quick_response_orderby'] ) : 'title';
		$wplc_quick_response_order = isset( $wplc_settings['wplc_quick_response_order'] ) ? sanitize_text_field( $wplc_settings['wplc_quick_response_order'] ) : 'DESC';
		echo "<div class='admin_chat_quick_controls'>";
		echo "  <p style=\"text-align:left; font-size:11px;\">".__("Press ENTER to send your message",'wp-live-chat-support')."</p>";
		echo "<div class='wplc_on_premise_chat_quick_response'";
		echo wplc_return_macros_mrg( 0, $wplc_quick_response_order_by, $wplc_quick_response_order  );
		echo "  </div>";
		echo "  </div>";
		echo "</div>";
	}

}



function wplc_return_macros_mrg( $firsttd = 0, $orderby = 'post_title', $order = 'DESC' ) {

    $args = array(
        'posts_per_page' => -1,
        'offset' => 0,
        'category' => '',
        'order' => $order,
        'orderby' => $orderby,
        'include' => '',
        'exclude' => '',
        'meta_key' => '',
        'meta_value' => '',
        'post_type' => 'wplc_quick_response',
        'post_mime_type' => '',
        'post_parent' => '',
        'post_status' => 'publish',
        'suppress_filters' => true);

	if ( 'number' === $orderby ) {
		$args['orderby'] = 'wplc_quick_response_number';
        $args['meta_key'] = 'wplc_quick_response_number';
    }

	$posts_array = get_posts($args);

	$msg = "<table><tr>";
    if ($firsttd == 0) {
        $msg .= "  <td>" . __("Assign Quick Response", 'wp-live-chat-support') . "</td>";
    }
    $msg .= "  <td>";
    if ($firsttd == 1) {
        $msg .= __("Assign Quick Response", 'wp-live-chat-support');
    }
    $msg .= "      <select name='wplc_macros_select' class='wplc_macros_select'>";
    $msg .= "          <option value='0'>" . __("Select", 'wp-live-chat-support') . "</option>";

    foreach ($posts_array as $post) {

        $msg .= "          <option value='" . $post->ID . "'>" . esc_html($post->post_title) . "</option>";
    }
    $msg .= "      </select> <small><a href='https://www.3cx.com/wp-live-chat/docs/quick-responses/' title='What are quick responses?' target='_BLANK'>" . __("What is this?", 'wp-live-chat-support') . "</a></small>";
    $msg .= "  </td>";
    $msg .= "</tr></table>";
    return $msg;
}

add_filter("wplc_filter_microicon","wplc_acbc_filter_control_microicon",10,1);
function wplc_acbc_filter_control_microicon($ret_msg) {
  $wplc_settings = wplc_get_options();
  if (!empty($wplc_settings['wplc_chat_pic'])) {
    /* overwrite the $ret_msg variable */
    $ret_msg = "<div class='wplc_left_logo' style='background:url(".urldecode($wplc_settings['wplc_chat_pic']).") no-repeat; background-size: cover;'></div>";
  }
  return $ret_msg;
}

if (!function_exists("wplc_acbc_filter_control_chaticon")) {
	add_filter("wplc_filter_chaticon","wplc_acbc_filter_control_chaticon",10,1);
	function wplc_acbc_filter_control_chaticon($icon) {
		$wplc_settings = wplc_get_options();
		if (!empty($wplc_settings['wplc_chat_icon'])) {
			$icon = urldecode($wplc_settings['wplc_chat_icon']);
		}
		return $icon;
	}
}

function wplc_determine_admin_email($wplc_settings) {
  $email_address = "";
	if (!empty($wplc_settings['wplc_pro_chat_email_address'])) { $email_address = $wplc_settings['wplc_pro_chat_email_address']; }
  if (empty($email_address)) { $email_address = get_option('admin_email'); }
  return $email_address;
}

/**
 * Notify the admin that someone wants to chat
 * @param  array   $array CID, Name, Email
 * @return bool
 */
function wplc_mrg_notify_via_email($data) {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_pro_chat_notification']) {
		$subject = sprintf( __( 'Incoming chat from %s (%s) on %s', 'wp-live-chat-support'),
			$data['name'],
			$data['email'],
			get_option('blogname')
    );
    
		$msg=sprintf( __( '%s (%s) wants to chat with you.', 'wp-live-chat-support'),
			$data['name'],
			$data['email']
		).'<br/><br/>';

		$msg.=sprintf( __('Log in: %s', 'wp-live-chat-support'),
			wp_login_url()
		);
		wplcmail(wplc_determine_admin_email($wplc_settings), "WP Live Chat by 3CX", $subject, $msg);
	}
	return true;
}

if (!function_exists("wplc_acbc_admin_scripts")) { 
	add_action('admin_print_scripts', 'wplc_acbc_admin_scripts');
	function wplc_acbc_admin_scripts() {
	    global $wplc_acbc_version;
	    if (isset($_GET['page']) && $_GET['page'] == 'wplivechat-menu-settings') {

	        wp_enqueue_media();
	        wp_register_script('my-wplc-upload', plugins_url('js/media.js', __FILE__), array('jquery'), $wplc_acbc_version, true);
	        wp_enqueue_script('my-wplc-upload');
	    }
	}
}

add_action("wplc_hook_push_js_to_front","wplc_acbc_hook_control_push_js_to_front");
function wplc_acbc_hook_control_push_js_to_front() {
  $wplc_settings = wplc_get_options();

  $wpc_misc_js_strings = array(
    'typing_enabled' => $wplc_settings['wplc_typing_enabled'],
    'wplc_delay' => intval($wplc_settings['wplc_chat_delay'])*1000
  );
  if ($wplc_settings['wplc_typing_enabled']) {
    $wpc_misc_js_strings['typingimg'] = plugins_url('/images/comment.svg', __FILE__);
  }
  wp_localize_script('wplc-user-script', 'wplc_misc_strings', $wpc_misc_js_strings);

  if ($wplc_settings['wplc_use_node_server']) {
    wp_register_script('wplc-user-pro-events-script', plugins_url('/js/wplc_u_node_pro_events.js', __FILE__), array('jquery', 'wplc-server-script'), WPLC_PLUGIN_VERSION, true);
    wp_register_script('tcx-action-script', plugins_url('/js/tcx_action_events.js', __FILE__), false, WPLC_PLUGIN_VERSION, true);
    wp_enqueue_script('tcx-action-script');
  } else {
    /* not using the node server, load traditional event handler JS */
    wp_register_script('wplc-user-pro-events-script', plugins_url('/js/wplc_u_pro_events.js', __FILE__), array('jquery', 'wplc-server-script'), WPLC_PLUGIN_VERSION, true);
  }

  wp_register_script('wplc-user-pro-features', plugins_url('/js/wplc_pro_features.js', __FILE__), array('wplc-user-script'), WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script( 'wplc-user-pro-features' );
  wp_enqueue_script( 'wplc-user-pro-events-script' );

  wp_register_script('wplc-user-pro-editor', plugins_url('/js/wplc_u_editor.js', __FILE__), array('wplc-user-script', 'jquery'), WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-user-pro-editor');

  if (!empty($wplc_settings["wplc_pro_auto_first_response_chat_msg"])) {
    wp_localize_script('wplc-user-pro-features', 'wplc_pro_auto_resp_chat_msg', strval(apply_filters('wplc_pro_auto_first_response_chat_msg_filter', strval($wplc_settings["wplc_pro_auto_first_response_chat_msg"]))));
  }

  wp_enqueue_style('wplc-user-pro-styles', plugins_url('/css/wplc_styles_pro.css', __FILE__));

  $post_id = get_the_ID();
  if (!$post_id || $post_id === NULL) {
    $url = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $post_id = url_to_postid($url); 
  }
  if (wplc_check_trigger_filters_mrg($post_id)) {
    wplc_tirggers_enqueue_user_styles_scripts(wplc_check_trigger_filters_mrg($post_id));
  }
}

/**
 * Let the basic know that we are using a premium add-on
 * @param  int $count
 * @return int
 */
if (!function_exists("wplc_acbc_filter_control_menu_api")) { 
	add_filter("wplc_filter_menu_api","wplc_acbc_filter_control_menu_api",1,1);
	function wplc_acbc_filter_control_menu_api($count) {
	    $count++;
	    return $count;
	}
}


add_filter("wplc_filter_live_chat_box_above_main_div","wplc_acbc_filter_control_live_chat_box_above_main_div");
function wplc_acbc_filter_control_live_chat_box_above_main_div($ret_msg) {
  $wplc_settings = wplc_get_options();
  if (!empty($wplc_settings['wplc_chat_logo'])) {
    $ret_msg .= "<div id=\"wplc_logo\">";
    $ret_msg .= "    <img class=\"wplc_logo_class\" src=\"".urldecode(sanitize_text_field(stripslashes($wplc_settings['wplc_chat_logo'])))."\" style=\"display:block; margin-bottom:5px; margin-left:auto; margin-right:auto;\" alt=\"".get_bloginfo('name')."\" title=\"".get_bloginfo('name')."\" />";
    $ret_msg .= "</div>";
  }
  return $ret_msg;
}

add_filter("wplc_filter_chat_header_under","wplc_acbc_filter_control_chat_header_under");
function wplc_acbc_filter_control_chat_header_under($ret_msg) {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_chat_pic']) {
    $ret_msg .= "<div id=\"wp-live-chat-image\">";
    $ret_msg .= "<div id=\"wp-live-chat-inner-image-div\">";
    $ret_msg .= "<img src=\"".urldecode(esc_url($wplc_settings['wplc_chat_pic']))."\" width=\"40px\"/>";
    $ret_msg .= "</div>";
    $ret_msg .= "</div>";
  }
  return $ret_msg;
}

/**
* Check if this is the first time the user has run the plugin. If yes, set the default settings
* @since        1.0.2
* @return       string
*/
add_filter("wplc_filter_admin_name","wplc_acbc_filter_admin_name");
function wplc_acbc_filter_admin_name($fromname) {
  $wplc_settings = wplc_get_options();
  if (!empty($wplc_settings['wplc_chat_name'])) {
    $fromname = esc_html($wplc_settings['wplc_chat_name']);
  }
  return $fromname;
}

add_filter( "wplc_admin_dashboard_layout_node_request_variable_filter", "wplc_admin_mrg_filter_control_dashboard_layout_node_request_variable_filter", 10, 1);
function wplc_admin_mrg_filter_control_dashboard_layout_node_request_variable_filter( $form_data ) {
  $wplc_settings = wplc_get_options();
  $form_data['choose'] = $wplc_settings['wplc_allow_agents_set_status'];
	return $form_data;
}

/**
 * Display the switchery button at the top of the chat dashboard
 * @return void
 * @since  1.0.00
 */
add_action("wplc_hook_chat_dashboard_above","wplc_choose_hook_control_chat_dashboard_above");
function wplc_choose_hook_control_chat_dashboard_above() {
  $wplc_settings = wplc_get_options();
  if (!$wplc_settings['wplc_allow_agents_set_status']) {
    return;
  }
  ?>
  <div class="wplc_admin_chat_switchery_a">
        <input type="checkbox" class="wplc_switchery" name="wplc_agent_status" id="wplc_agent_status" <?php if (wplc_get_agent_accepting(get_current_user_id()))  { echo 'checked'; } ?> />
        <div id="wplc_agent_status_text"></div>
    </div>
  <?php
}

/**
 * Latch onto the set transient filter and decide to show the agent online or not.
 * @param  bool    $set_transient  Should we set the transient or not?
 * @return bool
 * @since  1.0.00
 */
add_filter("wplc_filter_control_set_transient","wplc_choose_filter_control_set_set_transient",10,1);
function wplc_choose_filter_control_set_set_transient($set_transient) {
  return wplc_get_agent_accepting(get_current_user_id());
}

function wplc_return_online_agents_array() {
  $result = array(
    'result' => true,
    'agents' => wplc_get_online_agent_users()
  );
  return json_encode($result);
}

/**
 * Latch onto the original callback for this plugin's ajax requests
 * @return void
 */
function wplc_choose_hook_control_action_callback() {
  if ($_POST['action'] == 'wplc_choose_accepting') {
    $user_id = get_current_user_id();
    wplc_set_agent_accepting($user_id, true);
    do_action("wplc_hook_set_transient");
    echo wplc_return_online_agents_array();
    return;
  }

  if ($_POST['action'] == 'wplc_choose_not_accepting') {
    $user_id = get_current_user_id();
    wplc_set_agent_accepting($user_id, false);
    delete_user_meta($user_id, "wplc_chat_agent_online");
    echo wplc_return_online_agents_array();
    return;
  }

  if ($_POST['action'] == 'wplc_agent_list') {
    echo wplc_return_online_agents_array();
    return;
  }  

  if ($_POST['action'] == "wplc_typing") {
    if (isset($_POST['cid']) && isset($_POST['user']) && isset($_POST['type'])) {
      if ((!wplc_user_is_agent() && wplc_check_user_request($_POST['cid'])) || wplc_user_is_agent()) {
        echo wplc_typing_mrg(sanitize_text_field($_POST['user']), intval($_POST['cid']), sanitize_text_field($_POST['type']));
      }
    }
    return;
  }

  if ($_POST['action'] == 'wplc_upload_file') {
    $upload_dir = wp_upload_dir();
    $user_dirname = $upload_dir['basedir'];
    $cid=0;
    $u_id_check=0;
    if (isset($_POST['cid'])) {
      if (!wplc_user_is_agent()) {
        if (wplc_check_user_request($_POST['cid'])) {
          $u_id_check = 1;
        }
      } else {
        $u_id_check = 1;
      }
      if ($u_id_check === 1) {
        $cid = wplc_return_chat_id_by_rel_or_id($_POST['cid']);
        if (!file_exists( $user_dirname."/wp_live_chat/")) {
          @mkdir($user_dirname.'/wp_live_chat/');
        }  
        if (!realpath($user_dirname."/wp_live_chat/" . $cid)) {
          @mkdir( $user_dirname.'/wp_live_chat/'. $cid );
        }              
        if (isset($_FILES['file']) && isset($_POST['timestamp'])) {
          $file_name = strtolower( sanitize_file_name($_FILES['file']['name']) );
          $file_name = basename($file_name); //This prevents traversal
          if (wplc_check_file_name_for_safe_extension($file_name) && wplc_check_file_mime_type($_FILES['file']['tmp_name'])) {
            $upload_file_name = $file_name;
            while (file_exists($user_dirname . "/wp_live_chat/" . $cid . "/" . $upload_file_name)) {
              $upload_file_name = substr($file_name,0,strrpos($file_name, '.')).mt_rand(1000,9999).substr($file_name,strrpos($file_name, '.'));
            } 
            if (move_uploaded_file($_FILES['file']['tmp_name'], $user_dirname."/wp_live_chat/" . $cid . "/" . $upload_file_name)) {
              //File has been uploaded, let's now go ahead and check the mime type
              $response = $upload_dir['baseurl']."/wp_live_chat/" . $cid . "/" . $upload_file_name;
              echo $response;
            } else {
              echo "ERROR: Upload failed";
            } 
          } else {
            echo "ERROR: Security Violation";
          }
        }
      } else {
        echo "ERROR: Chat ID Tampering Detected.";  
      }	
    } else {
      echo "ERROR: Upload failed. Invalid Chat ID.";
    }
    wp_die();
  }
}

function wplc_typing_mrg($user,$cid,$type) {

  $cid=intval($cid);
  $cdata = wplc_get_chat_data($cid,__LINE__);
  $other = maybe_unserialize($cdata->other);
  
  if (isset($other['typing'][$user]) && $other['typing'][$user] == $type) {
    /* same state, ignore */
    return "already";
  } else {
    global $wpdb;
    global $wplc_tblname_chats;
    $other['typing'][$user] = $type;
    $wpdb->update( 
      $wplc_tblname_chats, 
      array( 
        'other' => maybe_serialize($other)
      ), 
      array('id' => $cid), 
      array( 
        '%s'
      ), 
      array('%d') 
    );

    $cdata = wplc_get_chat_data($cid,__LINE__);
    $other = maybe_unserialize($cdata->other);
    return $cid;
  }
}

/**
 * Add switchery JS & CSS button to the chat dashboard
 * @since  1.0.00
 * @return void
 */
add_action('admin_print_scripts', 'wplc_choose_admin_scripts');
function wplc_choose_admin_scripts() {
  wp_register_script('wplc_switchery', plugins_url('js/switchery.min.js', __FILE__), array('jquery'),WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc_switchery'); 
  wp_register_style('wplc_switchery_css', plugins_url('css/switchery.min.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
  wp_enqueue_style('wplc_switchery_css');
  wp_register_script('wplc-choose-script', plugins_url('/js/wplc_choose.js', __FILE__),array('jquery'),WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-choose-script');

  $wpc_admin_js_strings = array(
    'accepting_status' => __('Status (Online)', 'wp-live-chat-support'),
    'accepting_chats' => __('Online', 'wp-live-chat-support'),
    'not_accepting_chats' => __('Offline', 'wp-live-chat-support'),
    'not_accepting_status' => __('Status (Offline)', 'wp-live-chat-support'),
    'agent_online_singular' => __('Chat Agent Online', 'wp-live-chat-support'),
    'agent_online_plural' => __('Chat Agents Online', 'wp-live-chat-support')
  );

  wp_localize_script('wplc-choose-script', 'wplc_choose_admin_strings', $wpc_admin_js_strings);
  wplc_register_common_node();

  $wplc_current_user_id = get_current_user_id();

  if (wplc_get_agent_accepting($wplc_current_user_id))  {
    /* user is online */
    wp_localize_script('wplc-choose-script', 'wplc_choose_accept_chats', array('value'=>true));
  } else {
    wp_localize_script('wplc-choose-script', 'wplc_choose_accept_chats', array('value'=>false));
    $offline_string = "<p><span class='offline-status'>".__("You have set your status to offline. To view visitors and accept chats please set your status to online using the switch above.",'wp-live-chat-support')."</span></p>";
    $quote_string = "<p><span class='offline-quote'>".wplc_random_quote_mrg()."</span></p>";
    wp_localize_script('wplc-choose-script', 'wplc_localized_offline_string', $offline_string);
    wp_localize_script('wplc-choose-script', 'wplc_localized_quote_string', $quote_string);
  }

  wp_register_script('wplc-qr-script', plugins_url('/js/quick_responses.js', __FILE__),array('jquery'),WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-qr-script');
  wp_register_script('wplc-triggers', plugins_url('/js/triggers.js', __FILE__),array('jquery'),WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-triggers');
  wp_register_script('wplc-admin-editor', plugins_url('/js/wplc_admin_editor.js', __FILE__),array('jquery'),WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-admin-editor');
}

/**
 * Remove default transient renewer if option is enabled
 * @since  1.0.00
 * @return void
 */
if (!function_exists("wplc_choose_hook_control_head")) { 
	add_action("wplc_maa_hook_head","wplc_choose_hook_control_head",1);
	function wplc_choose_hook_control_head() {
    $wplc_settings = wplc_get_options();
    if (!$wplc_settings['wplc_allow_agents_set_status']) {
      remove_action("wplc_maa_hook_head","wplc_maa_hook_control_head");
    }
	}
}

/**
 * Encrypt the message via the filter
 * @param  string  $msg The message
 * @return string
 * @since  1.0.00
 */
if (!function_exists("wplc_encrypt_filter_control_message_control")) { 
    add_filter("wplc_filter_message_control","wplc_encrypt_filter_control_message_control",10,1);
    function wplc_encrypt_filter_control_message_control($msg) {
        $msg = wplc_encrypt_encrypt_msg($msg);
        return $msg;
    }
}

/**
 * Decrypt the message via the filter
 * @param  string  $msg The message
 * @return string
 * @since  1.0.00
 */
if (!function_exists("wplc_encrypt_filter_control_out_message_control")) { 
    add_filter("wplc_filter_message_control_out","wplc_encrypt_filter_control_out_message_control",10,1);
    function wplc_encrypt_filter_control_out_message_control($msg) {
        $msg = wplc_encrypt_decrypt_msg($msg);
        return $msg;
    }
}


/**
 * Add to the tabs filter
 * @return void
 * @since  1.0.00
 */

if (!function_exists("wplc_business_hours_filter_control_setting_tabs")) { 
	add_filter("wplc_filter_setting_tabs","wplc_business_hours_filter_control_setting_tabs");
	function wplc_business_hours_filter_control_setting_tabs($tab_array) {
        $tab_array[9] = array(
          "href" => "#tabs-9",
          "icon" => 'fa fa-lock',
          "label" => __("Encryption",'wp-live-chat-support')
        );

		$tab_array['business-hours'] = array(
		'href' 	=> '#wplc-business-hours',
		'icon' 	=> 'far fa-clock',
		'label' => __("Business Hours", 'wp-live-chat-support')
	);
		
	    return $tab_array;
	}
}

function wplc_generate_encryption_key() {
  return md5(mt_rand()).md5(mt_rand());
}

/**
* Encrypt the message
* @since        1.0.0
* @return       void
*/
function wplc_encrypt_encrypt_msg($plaintext) {
  $message = array(
    'e' => 0,
    'm' => $plaintext
  );
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_enable_encryption'] && !empty($wplc_settings['wplc_encryption_key'])) {
    $key = substr($wplc_settings['wplc_encryption_key'].$wplc_settings['wplc_encryption_key'],0,64);
    $inputData = cryptoHelpers::convertStringToByteArray($plaintext);
    $keyAsNumbers = cryptoHelpers::toNumbers(bin2hex($key));
    $keyLength = count($keyAsNumbers);
    $iv = cryptoHelpers::generateSharedKey(16);

    $encrypted = AES::encrypt(
      $inputData,
      AES::modeOfOperation_CBC,
      $keyAsNumbers,
      $keyLength,
      $iv
    );

    $retVal = $encrypted['originalsize']." ".cryptoHelpers::toHex($iv)." ".cryptoHelpers::toHex($encrypted['cipher']);

    $message = array(
      'e' => 1,
      'm' => $retVal
    );
  }
  return maybe_serialize($message);
}

/**
* Decrypt the message
* Updated in 8.0.32 for backwards compatibility
* @since        1.0.0
* @return       void
*/
function wplc_encrypt_decrypt_msg($input){
  $messages = maybe_unserialize($input);
  if (is_array($messages)) {
    /** Check already in place to determine if a message was previously encrypted */
    if ($messages['e'] == 1) {
      /* This message was encrypted */
      $wplc_settings = wplc_get_options();
      $key = substr($wplc_settings['wplc_encryption_key'].$wplc_settings['wplc_encryption_key'],0,64);
      $cipherSplit = explode( " ", $messages['m']);
      $originalSize = intval($cipherSplit[0]);
      $iv = cryptoHelpers::toNumbers($cipherSplit[1]);
      $cipherText = $cipherSplit[2];
      $cipherIn = cryptoHelpers::toNumbers($cipherText);
      $keyAsNumbers = cryptoHelpers::toNumbers(bin2hex($key));
      $keyLength = count($keyAsNumbers);

      $decrypted = AES::decrypt(
        $cipherIn,
        $originalSize,
        AES::modeOfOperation_CBC,
        $keyAsNumbers,
        $keyLength,
        $iv
      );

      $hexDecrypted = cryptoHelpers::toHex($decrypted);
      return pack("H*" , $hexDecrypted);
    } else {
      return $messages['m'];
    }  
  } else {
    return $input;
  }          
}

add_action('wplc_hook_admin_chatbox_javascript','wplc_ic_admin_javascript');
add_action('wplc_hook_admin_menu_layout_display' , 'wplc_hook_control_admin_menu_layout_display', 1, 3);
add_action('wplc_hook_wplc_draw_chat_area', 'wplc_hook_control_wplc_draw_chat_area',1, 1);
add_filter('wplc_filter_list_chats_actions', 'wplc_ic_initiate_chat_button', 14, 3);
add_filter('wplc_filter_wplc_call_to_server_visitor_new_status_check', 'wplc_filter_control_wplc_call_to_server_visitor_new_status_check', 1, 1);
add_filter("wplc_filter_run_override","wplc_ic_filter_control_run_override");

function wplc_ic_filter_control_run_override($ret) {
  $wplc_settings = wplc_get_options();
  return !$wplc_settings['wplc_enable_initiate_chat'];
}

/**
* Add an item to the data_array ajax variable
*
* @since        1.0.0
* @param       
* @return       void
*
*/
add_filter("wplc_filter_admin_javascript","wplc_ic_filter_control_admin_javascript");
function wplc_ic_filter_control_admin_javascript($data_array) {
  if (wplc_user_is_agent()) {
    $data_array['agent_id'] = get_current_user_id();
  }
  return $data_array;
}

/**
 * Initiate chat button
 * @param  string $default The default text that gets passed through
 * @param  int    $id2     ID of the chat
 * @return string
 * @since  1.0.00
 */
function wplc_ic_initiate_chat_button($actions, $result, $post_data) {
  if (intval($result->status) == 5 ) {
    $aid = "";
    if ($post_data['wplc_extra_data']['agent_id']) {
      if (is_numeric($post_data['wplc_extra_data']['agent_id'])) {
        $aid = "&aid=".$post_data['wplc_extra_data']['agent_id'];
      }
    }
    $url = admin_url( 'admin.php?page=wplivechat-menu&action=rc&cid='.$result->id.$aid);
    $wplc_settings = wplc_get_options();
    if ($wplc_settings['wplc_use_node_server'] && $wplc_settings['wplc_enable_initiate_chat']) {
      $actions = "<a href=\"".$url."\" class=\"wplc_open_chat button wplc_initiate_chat button-secondary\" window-title=\"WP_Live_Chat_".$result->id."\">".__("Initiate Chat",'wp-live-chat-support')."</a>"; 
    }
  }
  return $actions;
}

/**
 * Localize the action2 variable that is required
 * @return void
 * @since  1.0.00
 */
function wplc_ic_admin_javascript() {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_typing_enabled']) {
    $wpc_misc_js_strings = array(
    'typing_enabled' => true,
    'typingimg' => plugins_url('/images/comment.svg', __FILE__)
    );
  } else {
    $wpc_misc_js_strings = array(
      'typing_enabled' => false
    );
  }

  wp_localize_script('wplc-admin-chat-js', 'wplc_misc_strings', $wpc_misc_js_strings);

  if (isset($_GET['action']) && $_GET['action'] == 'rc') { 
    wp_localize_script( 'wplc-admin-chat-js', 'wplc_action2', 'wplc_long_poll_check_user_opened_chat' );
  } else {
    return;
  }
}

if (!function_exists("wplc_hook_control_intiate_check")) { 
	add_action("wplc_hook_initiate_check","wplc_hook_control_intiate_check",10,3);
	function wplc_hook_control_intiate_check($action,$cid,$aid) {
	    
	    wplc_change_chat_status($cid, 6); // 6 = request chat
	    
	    wplc_draw_chat_area($cid);


	}
}

/**
 * Decide what to show when an initialization is happening on the admin side
 * @param  string   $action    The action GET param
 * @return void
 * @since  1.0.00
 */
if (!function_exists("wplc_hook_control_admin_menu_layout_display")) { 
	function wplc_hook_control_admin_menu_layout_display($action, $cid,$aid) {
	    if ($action == 'rc') {
	        do_action("wplc_hook_initiate_check",$action,$cid,$aid);
	    }
	}
}

/**
 * Add the new status code to the JS array
 * @param  array   $array
 * @return array
 * @since  1.0.00
 */
if (!function_exists("wplc_filter_control_wplc_call_to_server_visitor_new_status_check")) { 
	function wplc_filter_control_wplc_call_to_server_visitor_new_status_check($array) {

	    if (intval($array['status']) == 6) {
	        $array['check'] = true;
	        $array['status'] = 3;
	        $array['wplc_name'] = "You";
	        wplc_change_chat_status(sanitize_text_field($array['cid']),3);
	    }
	    return $array;
	}
}

/**
 * Decide what to show when an initialization is happening on the admin side
 * @param  array   $array
 * @return array
 * @since  1.0.00
 */
function wplc_hook_control_wplc_draw_chat_area($result) {
  $wplc_settings = wplc_get_options();
  wp_localize_script('wplc-admin-chat-js', 'wplc_chat_name', wplc_get_user_name($result->name, $wplc_settings));
  if (intval($result->status) == 6) {
    echo "<strong>" . __("Attempting to open the chat window... Please be patient.", "wp-live-chat-support-initiate-chats") . "</strong>";
    $result->continue = false;
    return $result;
  }
  return $result;
}

/**
 * Checks if user is an agent while on the chat dashboard page
 * @return void
 * @since  1.0.00
 */
if (!function_exists("wplc_ma_hook_control_admin_meny_layout_display_top")) {
	function wplc_ma_hook_control_admin_meny_layout_display_top() {
    if (!wplc_user_is_agent()) {
      $warning = "<p style='color: red;'><b>".__('You are not a chat agent. Please make yourself a chat agent before trying to chat to visitors', 'wp-live-chat-support-multiple-agents')."</b></p>";
      echo $warning;
    }
    return;
	}
}

/**
* Assign the chat to the agent
*
* @since        1.0.0
* @param       
* @return       void
*
*/
if (!function_exists("wplc_maa_update_agent_id")) {
	function wplc_maa_update_agent_id($cid, $aid){
	    global $wpdb;
	    global $wplc_tblname_chats;
	    $sql = "SELECT * FROM `$wplc_tblname_chats` WHERE `id` = '%d'";
        $sql = $wpdb->prepare($sql, intval($cid));
	    $result = $wpdb->get_row($sql); 
	    if ($result) {
		    if(intval($result->status) != 3){
		        $update = "UPDATE `$wplc_tblname_chats` SET `agent_id` = '%d' WHERE `id` = '%d'";
                $update = $wpdb->prepare($update, intval($aid), intval($cid));
		        $wpdb->query($update);
		    }
		} else {
			return false;
		}
	}
}

/**
* Check if the chat has been answered by another chat agent
* @since        1.0.0
* @param        int   $cid  Chat ID
* @param        int   $aid  Agent ID
* @return       bool
*/
if (!function_exists("wplc_maa_check_if_chat_answered_by_other_agent")) {
	function wplc_maa_check_if_chat_answered_by_other_agent($cid, $aid){
	    do_action("wplc_hook_ma_check_if_answered_by_another_agent",$cid,$aid);
	    
	}
}


if (!function_exists("wplc_hook_control_ma_check_if_answered_by_another_agent")) {
	add_action("wplc_hook_ma_check_if_answered_by_another_agent","wplc_hook_control_ma_check_if_answered_by_another_agent",10,2);
	function wplc_hook_control_ma_check_if_answered_by_another_agent($cid,$aid) {
	    global $wpdb;
	    global $wplc_tblname_chats;
	    $cid = intval($cid);
	    $sql = "SELECT * FROM `$wplc_tblname_chats` WHERE `id` = '%d'";
        $sql = $wpdb->prepare($sql, $cid);
	    $result = $wpdb->get_row($sql); 
	    if(intval($result->agent_id) == intval($aid)){
	        return false;
	    } else {
	        return true;
	    }
	}
}


/**
 * Hook for updating the agent id
 * @param        int   $cid   Chat ID
 * @param        int   $aid   Agent ID
 * @since        1.0.0
 * @return       void
 */
if (!function_exists("wplc_ma_hook_control_update_agent_id")) {
	add_action("wplc_hook_update_agent_id","wplc_ma_hook_control_update_agent_id",10,2);
	function wplc_ma_hook_control_update_agent_id($cid,$aid) {
	    wplc_maa_update_agent_id(sanitize_text_field($cid), sanitize_text_field($aid));

	}
}



/**
* On first installation activation, set current admin as chat agent
* @since        1.0.0
* @param       
* @return       void
*/
if (!function_exists("wplc_ma_first_time_install")) {
	function wplc_ma_first_time_install() {
	    global $wpdb;
	    global $wplc_tblname_chats;


	    $sql = " SHOW COLUMNS FROM ".$wplc_tblname_chats." WHERE `Field` = 'agent_id'";
	    $results = $wpdb->get_results($sql);
	    if (!$results) {
	        $sql = "ALTER TABLE `$wplc_tblname_chats` ADD `agent_id` INT(11) NOT NULL;";
	        $wpdb->query($sql);
	    }



	    $admins = get_role('administrator');
	    $admins->add_cap('wplc_ma_agent');
	    $uid = get_current_user_id();
	    update_user_meta($uid, 'wplc_ma_agent', 1);
	    wplc_update_agent_time($uid);
	}
}




/**
* Set the user as an agent
* @since        1.0.0
* @param       
* @return       void
*/
function wplc_maa_set_user_as_agent( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
  if ( current_user_can( 'manage_options' ) ) {
    if (isset($_POST['wplc_ma_agent'])) {
      update_user_meta( $user_id, 'wplc_ma_agent', sanitize_text_field($_POST['wplc_ma_agent']));
    } else {
      delete_user_meta( $user_id, 'wplc_ma_agent');
    }
    if ($_POST['wplc_ma_agent'] == '1') {
      $wplc_ma_user = new WP_User( $user_id );
      $wplc_ma_user->add_cap( 'wplc_ma_agent' );
      wplc_update_agent_time($user_id);
    } else {
      $wplc_ma_user = new WP_User( $user_id );
      $wplc_ma_user->remove_cap( 'wplc_ma_agent' );
      delete_user_meta($user_id, "wplc_ma_agent");
      delete_user_meta($user_id, "wplc_chat_agent_online");
    }
  }
  do_action("wplc_pro_set_user_hook", $user_id);
}

/**
* Add the custom fields to the user profile page
*
* @since        1.0.0
* @param       
* @return       void
*
*/
function wplc_maa_custom_user_profile_fields($user) {
?>
    <table class="form-table">
      <tr>
        <th>
          <label for="wplc_ma_agent"><?php _e('Chat Agent', 'wp-live-chat-support'); ?></label>
        </th>
        <td>
<?php
  if (current_user_can('manage_options', array(null))) {
    ?>
      <label for="wplc_ma_agent">
      <input name="wplc_ma_agent" type="checkbox" id="wplc_ma_agent" value="1" <?php if (sanitize_text_field( get_the_author_meta( 'wplc_ma_agent', $user->ID ) ) == "1") { echo "checked=\"checked\""; } ?>>
      <?php _e("Make this user a chat agent",'wp-live-chat-support'); ?></label>
    <?php
  } else {
    echo "<p>".__("Your user role does not allow you to set agent attribute.",'wp-live-chat-support')."</p>"; 
    echo "<p>".__("Please contact the administrator of this website to change this.", 'wp-live-chat-support')."</p>";
  }
?>
  </td>
  </tr>
</table>
<?php
  do_action("wplc_pro_custom_user_profile_field_after_content_hook", $user);
}

if (!function_exists("wplc_ma_hook_control_accept_chat")) {
	add_action('wplc_hook_accept_chat','wplc_ma_hook_control_accept_chat',1,2);
	function wplc_ma_hook_control_accept_chat($get_data,$aid) {
	    if (wplc_maa_check_if_chat_answered_by_other_agent(intval($get_data['cid']), intval($aid)) === true) {
	        echo "<p>".__("This chat has already been answered by another agent.", 'wp-live-chat-support')."</p>";
	        exit();
	        if (function_exists("wplc_hook_control_accept_chat")) {
	            remove_action("wplc_hook_accept_chat","wplc_hook_control_accept_chat", 10, 2);
	        }
	    }
	}
}


function wplc_is_typing_mrg($user,$cid) {
	$cdata = wplc_get_chat_data($cid,__LINE__);
	
	if (isset($cdata->other)) {
	    $other = maybe_unserialize($cdata->other);

	    if (isset($other['typing'][$user]) && $other['typing'][$user] > 0) {
	    	
	    	return true;	
		} else {

			return false;
		}
	} else {
		return false;
	}

}


/**
* Modify the loop iteration
*
* @since        1.0.0
* @param       
* @return       array
*/

if (!function_exists("wplc_ma_filter_control_wplc_admin_long_poll_chat_iteration")) {
	add_filter("wplc_filter_admin_long_poll_chat_loop_iteration","wplc_ma_filter_control_wplc_admin_long_poll_chat_iteration", 11, 4);
	function wplc_ma_filter_control_wplc_admin_long_poll_chat_iteration($array,$post_data,$i,$cdata = false) {
		

		if (!$cdata) {
			$cdata = wplc_get_chat_data($post_data['cid'],__LINE__);
		}

		/* check to see if the user is typing and notify the agent */
		$previous_is_typing = get_option("wplc_previous_is_typing");
		
	    /* run checks for old chats and remove them from this variable */
	    if ($previous_is_typing) {
	    	foreach ($previous_is_typing as $cid => $val) {
	    		$cdata = wplc_get_chat_data($cid,__LINE__);
	    		
	    		if ($cdata->status != "3") {
	    			unset($previous_is_typing[$cid]);
	    			update_option("wplc_previous_is_typing",$previous_is_typing);
	    			
	    		}
	    		
	    	}
	    }

		
		if (!isset($previous_is_typing[$post_data['cid']])) { $previous_is_typing[$post_data['cid']] = false; }

		if (!isset($previous_is_typing[$post_data['cid']]['user'])) { $previous_is_typing[$post_data['cid']]['user'] = false; }
		/* 0 = user, 1 = agent */
		$is_typing_check = wplc_is_typing_mrg('user',$post_data['cid'],$cdata);

		if ($is_typing_check !== $previous_is_typing[$post_data['cid']]['user']) {
			

			$array['is_typing_check'] = $is_typing_check;
			$array['previous_is_typing'] = $previous_is_typing[$post_data['cid']]['user'];

			if ($is_typing_check) { $checker = "1"; }
			else { $checker = "0"; }

			$array['typing'] = $checker;

			/* 0 = user, 1 = agent */
			$previous_is_typing[$post_data['cid']]['user'] = $is_typing_check;
			update_option("wplc_previous_is_typing",$previous_is_typing);

		} else {
			
		}

		


	    if (isset($post_data['extra_data']['agent_id'])) {
	        if (wplc_maa_check_if_chat_answered_by_other_agent(intval($post_data['cid']), intval($post_data['extra_data']['agent_id'])) === true) {
	            /* chat already answered by another agent */
	            $array['action'] = "wplc_ma_agant_already_answered";
	        }
	    }
		
		//Update Chat Experience Rating*/ 
	   	$rating_checker = nifty_get_rating_data_mrg($post_data['cid']);
	   	if( isset( $array['chat_rating'] ) ){
	   		$previous_rating = $array['chat_rating'];	
	   	} else {
	   		$previous_rating = "";
	   	}
	   	
        if ($rating_checker != $previous_rating) {
      		$array['chat_rating'] = $rating_checker;
        }
	    
	    return $array;

	}
}




/**
* Modify the loop iteration
*
* @since        1.0.0
* @param       
* @return       array
*/

if (!function_exists("wplc_ma_filter_control_wplc_user_long_poll_chat_iteration")) {
	add_filter("wplc_filter_user_long_poll_chat_loop_iteration","wplc_ma_filter_control_wplc_user_long_poll_chat_iteration", 11, 4);
	function wplc_ma_filter_control_wplc_user_long_poll_chat_iteration($array,$post_data,$i,$cdata = false) {

		if (!$cdata) {
			$cdata = wplc_get_chat_data($post_data['cid'],__LINE__);
		}

		$previous_is_typing = get_option("wplc_previous_is_typing");
		
		/* check to see if the agent is typing and notify the user */
		if (!isset($previous_is_typing[$post_data['cid']])) { $previous_is_typing[$post_data['cid']] = false; }

		if (!isset($previous_is_typing[$post_data['cid']]['admin'])) { $previous_is_typing[$post_data['cid']]['admin'] = false; }
		/* 0 = user, 1 = agent */
		$is_typing_check = wplc_is_typing_mrg('admin',$post_data['cid'],$cdata);



		if ($is_typing_check !== $previous_is_typing[$post_data['cid']]['admin']) {
				
			$array['is_typing_check'] = $is_typing_check;
			$array['previous_is_typing'] = $previous_is_typing[$post_data['cid']]['admin'];

			if ($is_typing_check) { $checker = "1"; }
			else { $checker = "0"; }

			$array['typing'] = $checker;
			$array['check'] = true; /* this forces the array to be returned in the loop */
			$array['status'] = $post_data['status']; /* this is required to carry through the loop */

			/* 0 = user, 1 = agent */
			$previous_is_typing[$post_data['cid']]['admin'] = $is_typing_check;
			update_option("wplc_previous_is_typing",$previous_is_typing);

		} else {
			$array['is_typing_check'] = $is_typing_check;
			$array['previous_is_typing'] = $previous_is_typing[$post_data['cid']]['admin'];
		}

	    return $array;

	}
}




/**
* Hook to accept chat
*
* @since        1.0.0
* @param       
* @return       void
*
*/
if (!function_exists("wplc_ma_hook_control_accept_chat_url")) {
	add_action( 'wplc_hook_accept_chat_url' , 'wplc_ma_hook_control_accept_chat_url', 10, 2);
	function wplc_ma_hook_control_accept_chat_url($get_data, $chat_data = false) {
	    if (!isset($get_data['agent_id'])) {
	        /* something went wrong :/ */
	    } else {
	        wplc_maa_update_agent_id(sanitize_text_field($get_data['cid']), sanitize_text_field($get_data['agent_id']));    
	    }
	    
	}
}

if (!function_exists("wplc_filter_control_aid_in_action")) {
  add_filter("wplc_filter_aid_in_action","wplc_filter_control_aid_in_action");
  function wplc_filter_control_aid_in_action() {
    $aid = "";
    if (wplc_user_is_agent()) {
      $aid = "&aid=".get_current_user_id();
    }
    return $aid;
  }
}

/**
* 
*
* @since        1.0.0
* @param       
* @return       void
*
*/
if (!function_exists("wplc_ma_filter_control_admin_javascript")) {
	add_filter("wplc_filter_admin_javascript","wplc_ma_filter_control_admin_javascript");
	function wplc_ma_filter_control_admin_javascript($data_array) {
    if (wplc_user_is_agent()) {
      $data_array['agent_id'] = get_current_user_id();
    }
    return $data_array;
	}
}

/**
 * Control the content below the visitor count
 * @return void
 */
if (!function_exists("wplc_ma_filter_control_chat_dashboard_visitors_online_bottom")) {
	add_action("wplc_filter_chat_dahsboard_visitors_online_bottom","wplc_ma_filter_control_chat_dashboard_visitors_online_bottom",11);
	function wplc_ma_filter_control_chat_dashboard_visitors_online_bottom($text) {
	  $text = "      <span class='wplc_agents_online'>".wplc_get_online_agent_users_count()."</span> ";
	  $text .=      __("Agent(s) online",'wp-live-chat-support');
	  return $text;
	}
}

add_action('admin_bar_menu', 'wplc_maa_online_agents', 100);
function wplc_maa_online_agents() {
  if (!current_user_can('wplc_ma_agent', array(null))) {
    return; //if user doesn't have permissions for chat agent, do not show admin bar.
  }
?>
<style>
.wplc_circle {
  width: 10px !important;
  height: 10px !important;
  display: inline-block !important;
  border-radius: 100% !important;
  margin-right: 5px !important;
}
.wplc_red_circle{
  background: red;
}
.wplc_green_circle{
  background:rgb(103, 213, 82);
}
.wplc_online_topbar_switch:before {
  color: #000 !important;
}

.wplc_online_topbar_switch {
  width: 9px !important;
  height: 14px !important;
  margin-top: -1px !important;
  border: 1px solid #dedede !important;
}
.wplc_online_topbar_switch_online {
  background-color: rgb(96, 202, 76) !important;
}
.wplc_online_topbar_switch_offline {
  background-color: #de1b1b !important;
}

</style>
<?php 
  $user_array = wplc_get_online_agent_users();
  $agent_count = count($user_array);
  if ($agent_count>0) {
    $circle_class = "wplc_green_circle";
    if ($agent_count==1) {
      $chat_agents = __('Chat Agent Online', 'wp-live-chat-support');
    } else {
      $chat_agents = __('Chat Agents Online', 'wp-live-chat-support');
    }
  } else {
    $circle_class = "wplc_red_circle";
    $chat_agents = __('Chat Agents Online', 'wp-live-chat-support');
  }

  global $wp_admin_bar;
  $wp_admin_bar->add_menu( array(
    'id' => 'wplc_ma_online_agents',
    'title' => '<span class="wplc_circle '.$circle_class.'" id="wplc_ma_online_agents_circle"></span><span id="wplc_ma_online_agents_count">'.$agent_count.'</span> <span id="wplc_ma_online_agents_label">'.$chat_agents.'</span>',
    'href' => false
  ));

  foreach($user_array as $user) {
    $wp_admin_bar->add_menu(array(
      'id' => 'wplc_user_online_'.$user->ID,
      'parent' => 'wplc_ma_online_agents',
      'title' => $user->display_name,
      'href' => false,
    ));
  }

  if (is_admin()) {
    $wp_admin_bar->add_node( array(
      'id' => 'wplc_ma_online_switch',
      'meta' => array('class' => 'wplc_online_switch_'.(wplc_get_agent_accepting() ? 'online' : 'offline')),
      'title' => '<input type="checkbox" id="wplc_online_topbar_switch" '.(wplc_get_agent_accepting() ? 'checked="checked"' : '').' class="wplc_online_topbar_switch wplc_online_topbar_switch_'.(wplc_get_agent_accepting() ? 'online' : 'offline').'" />
      <span id="wplc_ma_online_agent_text">'.(wplc_get_agent_accepting() ? __('Online', 'wp-live-chat-support') : __('Offline','wp-live-chat-support')).'
      </span>',
      'href' => false
    ));  
  }
}

/**
 * Transient renewer to refresh the agent's online status
 * @return void
 */
if (!function_exists("wplc_maa_hook_control_head")) {
	add_action("admin_enqueue_scripts","wplc_maa_hook_control_head",10);
	function wplc_maa_hook_control_head() {
	    if (isset($_GET['page']) && $_GET['page'] == "wplivechat-menu" && (!isset($_GET['action']))) {
	        global $wplc_multiple_agents_version;
	        @wp_register_script("wplc-ma-transient-js", plugins_url('js/wplc_ma_transient.js', __FILE__), array(), $wplc_multiple_agents_version, true);
	        @wp_enqueue_script('wplc-ma-transient-js');
	    }
	}
}

/**
 * Logs the agent out
 * @return void
 * @since  1.0.00
 */
if (!function_exists("wplc_maa_agent_logout")) {
	add_action('wp_logout', 'wplc_maa_agent_logout');
	function wplc_maa_agent_logout(){
    delete_user_meta(get_current_user_id(), "wplc_chat_agent_online");
	}
}

/**
 * Add the JS file to the admin screen
 * @return void
 * @since  1.0.00
 */
if (!function_exists("wplc_control_admin_javascript")) {
	function wplc_control_admin_javascript() {
	        global $wplc_multiple_agents_version;
	        wp_register_script("wplc-ma-js", plugins_url('js/wplc_ma.js', __FILE__), array(), $wplc_multiple_agents_version, true);
	        wp_enqueue_script('wplc-ma-js');
	        
	        $ajax_url = admin_url('admin-ajax.php');
	        $wplc_ajax_url = apply_filters("wplc_filter_ajax_url",$ajax_url);
	        wp_localize_script('wplc-ma-js', 'wplc_ajaxurl', $wplc_ajax_url);

	        $wpc_ma_js_strings = array(
	        'remove_agent' => __('Remove', 'wp-live-chat-support'),
	        'nonce' => wp_create_nonce("wplc"),
	        'user_id' => get_current_user_id(),
	        'typing_string' => __('Typing...', 'wp-live-chat-support')
	        );
	        wp_localize_script('wplc-ma-js', 'wplc_admin_strings', $wpc_ma_js_strings);
	    }
	    do_action("wplc_maa_hook_head");
}

/**
 * AJAX callback control
 * @return void
 * @since  1.0.00
 */
add_action("wplc_hook_action_callback","wplc_ma_hook_control_action_callback");
function wplc_ma_hook_control_action_callback() {

  if ($_POST['action'] == "wplc_add_agent") {
    if (current_user_can("manage_options")) {
      $uid = sanitize_text_field(intval($_POST['uid']));
      if (isset($uid)) {
        update_user_meta($uid, 'wplc_ma_agent', true);
        echo "1";
      } else {
        echo "0";
      }
    }
  }
  
  if ($_POST['action'] == "wplc_remove_agent") {
    if (current_user_can("manage_options")) {
      $uid = sanitize_text_field(intval($_POST['uid']));
      if (isset($uid)) {
        delete_user_meta($uid, 'wplc_ma_agent');
        echo "1";
      } else {
        echo "0";
      }
    }
  }

  if ($_POST['action'] == "wplc_macro") {
    if (wplc_user_is_agent()) {
      if (isset($_POST['postid'])) {
        $post_id = sanitize_text_field($_POST['postid']);
      } else {
        return false;
      }
      if ($post_id > 0) {
        $post_details = get_post($post_id);
        if ($post_details) {
          echo json_encode(nl2br($post_details->post_content));
        } else {
          echo json_encode("No post with that ID");
          die();
        }
      } else {
        echo json_encode("No macro with that ID");
        die();
      }
    } else {
      echo json_encode("You do not have permission to perform this action.");
      die();
    }
  }

  if ($_POST['action'] == 'wplc_record_chat_rating' ) {
    $rating_id = sanitize_text_field($_POST['cid']);
    $rating_score = sanitize_text_field($_POST['rating']);
    $u_id_check=0;
    if (wplc_check_user_request($_POST['cid'])) {
      $u_id_check = 1;
    }
    if ($u_id_check === 1) {
      $rating_id = wplc_return_chat_id_by_rel_or_id($rating_id);
      $nifty_record_rating = nifty_record_rating_mrg($rating_id, $rating_score);
      if ($nifty_record_rating) {
        echo 'rating added';
      } else {
        echo "There was an error sending your chat message. Please contact support";
      }
      wp_die();
    } else {
      echo "There was an error sending your chat rating. Please contact support";
      wp_die();
    }
  }
}

/* WPLC Social Icons Filter*/
// add_filter("wplc_filter_chat_header_above","wplc_chat_social_div_mrg",5,2);
// add_filter("wplc_filter_chat_4th_layer_below_input","wplc_chat_social_div_mrg",5,2);
add_filter("wplc_filter_chat_social_div_hook","wplc_chat_social_div_mrg",5,2);
function wplc_chat_social_div_mrg($msg, $wplc_settings=false) {
  if (!$wplc_settings) {
    $wplc_settings = wplc_get_options();
  } 
  if (!$wplc_settings["wplc_ux_exp_rating"] && $wplc_settings['wplc_newtheme'] == "theme-2") {
    //$top_offset = "bottom:-4px !important;";
  }
  $msg .= "<div id='wplc_social_holder' ".($wplc_settings['wplc_newtheme'] == "theme-2" ? "class='wplc_modern'" : "" )." >";
  if (!empty($wplc_settings['wplc_social_fb'])) {
    $msg .= "<a class='wplc-color-1 wplc_social_icon_link' href=".urldecode($wplc_settings['wplc_social_fb'])." target='_blank'><i class='wplc_social_icon fab fa-facebook-square' id='wplc_social_fb' ></i></a>";
  }
  if (!empty($wplc_settings['wplc_social_tw'])) {
    $msg .= "<a class='wplc-color-1 wplc_social_icon_link' href=".urldecode($wplc_settings['wplc_social_tw'])." target='_blank'><i class='wplc_social_icon fab fa-twitter-square' id='wplc_social_tw' ></i></a>";
  }
  $msg .= "</div>";
  return $msg;
}

/*Upload Div*/
add_filter("wplc_filter_chat_upload","nifty_file_upload_div_mrg",1,1);
function nifty_file_upload_div_mrg($msg){
	$wplc_settings = wplc_get_options();
	if ($wplc_settings["wplc_ux_file_share"]) {
		// $editor_enabled = !$wplc_settings["wplc_ux_exp_rating"] ? "bottom: 35px !important;" : "";
      $editor_enabled = "";
	    $msg .= "<div id='nifty_file_holder'>";
	    $msg .=   "<label for='nifty_file_input' id='nifty_select_file'><i class='nifty_attach_icon nifty_attach_open_icon fa fa-paperclip' id='nifty_attach' style='$editor_enabled' ></i></label>";
	    $msg .=   "<i class='nifty_attach_icon fa far-circle-notch fa-spin' id='nifty_attach_uploading_icon' style='display:none; $editor_enabled' ></i>";
	    $msg .=   "<i class='nifty_attach_icon fa fa-check-circle' id='nifty_attach_success_icon' style='display:none; $editor_enabled' ></i>";
	    $msg .=   "<i class='nifty_attach_icon fa fa-minus-circle' id='nifty_attach_fail_icon' style='display:none; $editor_enabled'></i>";
	    $msg .=   "<input type='file' id='nifty_file_input' name='nifty_file_input' style='display:none'>";
	    $msg .= "</div>";
    }
    return $msg;
}

// add_filter("wplc_filter_chat_header_above","nifty_chat_ratings_div_mrg",1,2);
add_filter("wplc_filter_chat_4th_layer_below_input","nifty_chat_ratings_div_mrg",1,2);
function nifty_chat_ratings_div_mrg($msg, $wplc_setting){ 
  $wplc_settings = wplc_get_options();
	if ($wplc_settings["wplc_ux_exp_rating"]) {
		$msg .= "<div id='nifty_ratings_holder' style='display:none;'>";
		$msg .=   "<i class='nifty_rating_icon far fa-thumbs-up' id='nifty_rating_pos' ></i>";
		$msg .=    "";
		$msg .=   "<i class='nifty_rating_icon far fa-thumbs-down' id='nifty_rating_neg' ></i>";
		$msg .=   "<div id='nifty_ratings_form' style='display:none;' class='wplc-color-bg-1 wplc-color-1 wplc-color-border-1'>";
		$msg .=     "<input type='text' id='nifty_ratings_comment' placeholder='Comments...'>";
		$msg .=     "<button id='nifty_rating_button_cancel' tabindex='-1' class='wplc-color-bg-2 wplc-color-1'>x</button>";
        $msg .=     "<button nifty-rating='' id='nifty_rating_button' class='wplc-color-bg-2 wplc-color-1'>Rate</button>";
		$msg .=   "</div>";
		$msg .=   "<i class='fa fa-spinner fa-spin' id='nifty_recording' style='display:none;'></i>";
		$msg .=   "<div id='nifty_rating_thanks' style='display:none;'>";
		$msg .=     "<p>Rating Recorded...</p>";
		$msg .=   "</div>";
		$msg .= "</div>";
  }
  return $msg;
}

/*Handles adding a rating*/
function nifty_record_rating_mrg($cid, $rating){
    global $wpdb;
    global $wplc_tblname_chat_ratings;
    
    //Cleanup here
    $cid = intval($cid);
    $rating = intval($rating);
    $comment = "";

    if(nifty_chat_has_rating_mrg($cid)){ //Update
        $sql = "UPDATE $wplc_tblname_chat_ratings SET `aid` = '%d', `rating` = '%d', `comment` = '%s', `timestamp` = '%s', `notified` = 0 WHERE `cid` = '%d' LIMIT 1";
        $sql = $wpdb->prepare($sql, wplc_get_chat_data($cid)->agent_id, $rating, $comment, date("Y-m-d H:i:s"), $cid);
        $wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        }
    }else{ 
    //Insert
       $sql = "INSERT INTO $wplc_tblname_chat_ratings SET `cid` = '%d', `aid` = '%d', `rating` = '%d', `comment` = '%s', `timestamp` = '%s', `notified` = 0";
       $sql = $wpdb->prepare($sql, $cid, wplc_get_chat_data($cid)->agent_id, $rating, $comment, date("Y-m-d H:i:s"));
       $wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        } 
    }
}

function nifty_chat_has_rating_mrg($cid){
    global $wpdb;
    global $wplc_tblname_chat_ratings;
    $sql ="SELECT `id` FROM $wplc_tblname_chat_ratings WHERE `cid` = '%d'";
    $sql = $wpdb->prepare($sql, $cid);
    $wpdb->query($sql);
    if($wpdb->num_rows){
        return true;
    } else {
        return false;
    }     
}

function nifty_get_rating_data_mrg($cid,$force = false){
    global $wpdb;
    global $wplc_tblname_chat_ratings;
    if ($force) {
    	$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chat_ratings WHERE `cid` = %d LIMIT 1", $cid));
    } else { 
    	$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chat_ratings WHERE `cid` = %d AND `notified` = 0 LIMIT 1", $cid));
    }
    if ($wpdb->num_rows) {
      foreach ($results as $result) {
        $id = $result->id;
        $wpdb->Query($wpdb->prepare("UPDATE $wplc_tblname_chat_ratings SET `notified` = 1 WHERE `id` = %d LIMIT 1", $id));
        return array("rating" => $result->rating, "comment" => $result->comment);
	  	}
    } else {
      return false;
    }
}

add_filter("wplc_filter_advanced_info","nifty_rating_advanced_info_control_mrg",1,4);
function nifty_rating_advanced_info_control_mrg($msg, $cid, $name, $chat_data){
	$rating = apply_filters("wplc_rating_data_filter","",$cid,$name);
	if ($rating) {

		$msg .= "<div class='admin_visitor_advanced_info admin_agent_rating wplc_on_premise_chat_box_user_info_rating'>
                	<div class='rating " .(isset($rating['rating']) && $rating['rating'] == '1' ? "rating-good" : ($rating['rating'] == '0' ? "rating-bad" : "" ))."'>".(isset($rating['rating']) && $rating['rating'] == '1' ? "Satisfied" : ($rating['rating'] == '0' ? "Unsatisfied" : "No Rating" ))."</div>
            	</div>";
    } else{
    	//No Rating Provided
    	$msg .= "<div class='admin_visitor_advanced_info admin_agent_rating'>
                	<div class='rating'>No Rating</div>
            	</div>";
    }
	return $msg;
}


add_filter("wplc_rating_data_filter","wplc_filter_control_get_rating_data_mrg",10,3);
function wplc_filter_control_get_rating_data_mrg($msg,$cid,$name) {
	return nifty_get_rating_data_mrg($cid, true);
}
/* Gets an agents percentage based on filter
 * Filters:
 *    0 - All
 *    1 - 7 Days
 *    2 - 14 Days
 *    3 - 30 Days
 *    4 - 60 Days
 *    5 - 90 Days
*/
function nifty_get_rating_report_mrg($uid, $filter = 0){
    //Some snazzy code here
    global $wpdb;
    global $wplc_tblname_chat_ratings;
    
    $sql = "SELECT * FROM $wplc_tblname_chat_ratings WHERE `aid` = %s"; 
    if($filter == 1){
        $sql = "SELECT * FROM $wplc_tblname_chat_ratings WHERE `aid` = %s AND `timestamp` > DATE_SUB(NOW(), INTERVAL 7 DAY)"; 
    }else if($filter == 2){
        $sql = "SELECT * FROM $wplc_tblname_chat_ratings WHERE `aid` = %s AND `timestamp` > DATE_SUB(NOW(), INTERVAL 14 DAY)"; 
    }else if($filter == 3){
        $sql = "SELECT * FROM $wplc_tblname_chat_ratings WHERE `aid` = %s AND `timestamp` > DATE_SUB(NOW(), INTERVAL 30 DAY)";  
    }else if($filter == 4){
        $sql = "SELECT * FROM $wplc_tblname_chat_ratings WHERE `aid` = %s AND `timestamp` > DATE_SUB(NOW(), INTERVAL 60 DAY)"; 
    }else if($filter == 5){
        $sql = "SELECT * FROM $wplc_tblname_chat_ratings WHERE `aid` = %s AND `timestamp` > DATE_SUB(NOW(), INTERVAL 90 DAY)";  
    }

    $sql .= " ORDER BY `cid` DESC"; //Sort
     
    $total = 0;
    $pos = 0;
    $neg = 0;

    $data_array = array();
    $current_index = 0;
    $results =  $wpdb->get_results($wpdb->prepare($sql, $uid));
    if($wpdb->num_rows){
        foreach ($results as $res) {
        	$data_array[$uid][$current_index] = array(
                "cid" => $res->cid,
                "rating"  =>  $res->rating,
        		"comment" =>  $res->comment,
        		"time"    =>  $res->timestamp
            );

            $current_index++;

            if($res->rating  == "1"){
               $pos ++; //Count
            }else if($res->rating == "0"){
               $neg ++; //Count
            } 
            $total ++;
        }     

         $total = $total > 0 ? $total : 1; //Cannot devide by 0
		$perc = intval(($pos * 100) / $total);

		$data_array["percentage"]    = $perc;
		$data_array["total_ratings"] = $total;
		$data_array["bad_count"]     = $neg;
		$data_array["good_count"]    = $pos;

		return $data_array;
    } else {
    	return false;
    }
    
}

add_filter('wplc_reporting_tabs', 'wplc_reporting_tabs_filter_experience_ratings_control_mrg', 10, 1);
function wplc_reporting_tabs_filter_experience_ratings_control_mrg($tabs_array){
	$tabs_array['ux_ratings'] = __("User Experience Ratings", 'wp-live-chat-support');
	return $tabs_array;
}


add_filter('wplc_reporting_tab_content', 'wplc_reporting_tab_content_filter_experience_ratings_control_mrg', 10, 1);
function wplc_reporting_tab_content_filter_experience_ratings_control_mrg($tabs_array) {
  $ratings = "<h3>".__("Agent Statistics", 'wp-live-chat-support')."</h3>";
  $user_array = wplc_get_agent_users();
  $ratings .= "<style>";
  $ratings .= ".wplc_agent_grav_report { display:inline-block; }";
  $ratings .= ".wplc_agent_card_details { display:inline-block; margin-left: 5px;}";
  $ratings .= ".wplc_agent_card { width:100%; }";
  $ratings .= ".wplc_agent_container {
width: 30%;
padding: 5px;
border-radius: 7px;
box-shadow: 0px 0px 5px 2px #9999992b;
display: inline-block;
margin-right: 13px;
vertical-align: top;
margin-bottom:10px;
}";
  $ratings .= ".wplc_agent_data {
max-height: 200px;
overflow-y: auto;
padding: 5px;
border: 1px lightgrey solid;
}";
  $ratings .= "</style>";

  foreach ($user_array as $user) {
    $ratings .= "<div class='wplc_agent_container'>";
    $rating_stats = nifty_get_rating_report_mrg($user->ID);
    $ratings .= "<div class='wplc_agent_card'>";
    $ratings .= 	"<img class='wplc_agent_grav_report' src=\"//www.gravatar.com/avatar/" . md5($user->user_email) . "?s=80&d=mm\" />";
    $ratings .= 	"<div class='wplc_agent_card_details'>";
    $ratings .= 		"<strong>" . $user->display_name . "</strong><br>";
    $ratings .= 		"<small>" . $user->user_email . "</small><br>";
    $ratings .= 		"<hr>";
    $ratings .= 		"<small><strong>".__("Satisfaction Rating", 'wp-live-chat-support').":</strong> " . (is_bool($rating_stats) &&  $rating_stats == false ? "--" : $rating_stats['percentage'] ). "%</small><br>";
    $ratings .= 		"<small><strong>".__("Rating Count", 'wp-live-chat-support').":</strong> " . (is_bool($rating_stats) &&  $rating_stats == false ? "0" : $rating_stats['total_ratings'])." (".__("Good",'wp-live-chat-support').": ".(is_bool($rating_stats) &&  $rating_stats == false ? "0" : $rating_stats['good_count']). " || ".__("Bad",'wp-live-chat-support').": ".(is_bool($rating_stats) &&  $rating_stats == false ? "0" : $rating_stats['bad_count']). ")</small>";
    $ratings .= 	"</div>";
    $ratings .= "</div>";
    $ratings .= "</div>";
  }

  $rating_stats = nifty_get_rating_report_mrg(0);
  if (!empty($rating_stats[0]) && $rating_stats[0] !== false) {
    $ratings .= "<div class='wplc_agent_container'>";
    $ratings .= "<div class='wplc_agent_card'>";
    $ratings .=     "<div class='wplc_agent_card_details'>";
    $ratings .=         "<strong>No Agent</strong><br>";
    $ratings .=         "<small>Reviewed before agent joined, or with multiple agents</small><br>";
    $ratings .=         "<hr>";
    $ratings .=         "<small><strong>".__("Satisfaction Rating", 'wp-live-chat-support').":</strong> " . (is_bool($rating_stats) &&  $rating_stats == false ? "--" : $rating_stats['percentage'] ). "%</small><br>";
    $ratings .=         "<small><strong>".__("Rating Count", 'wp-live-chat-support').":</strong> " . (is_bool($rating_stats) &&  $rating_stats == false ? "0" : $rating_stats['total_ratings'])." (".__("Good",'wp-live-chat-support').": ".(is_bool($rating_stats) &&  $rating_stats == false ? "0" : $rating_stats['good_count']). " || ".__("Bad",'wp-live-chat-support').": ".(is_bool($rating_stats) &&  $rating_stats == false ? "0" : $rating_stats['bad_count']). ")</small>";
    $ratings .=     "</div>";
    $ratings .= "</div>";
    $ratings .= "</div>";
  }

  $tabs_array['ux_ratings'] = $ratings;
  return $tabs_array;
}

add_action('admin_enqueue_scripts', 'wplc_reporting_scripts_mrg');
function wplc_reporting_scripts_mrg(){
    if( isset( $_GET['page'] ) && $_GET['page'] == 'wplivechat-menu-reporting' ){
        $statistics = json_encode( return_statistics_mrg() );
        wp_register_script('wplc-google-charts', plugins_url('/js/vendor/charts/loader.js', __FILE__), array('jquery'), WPLC_PLUGIN_VERSION, true);
        wp_enqueue_script('wplc-google-charts');
        
        wp_register_style('wplc-ui-style-stats', plugins_url('/js/vendor/jquery-ui/jquery-ui.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-ui-style-stats');
        wp_register_script('wplc-statistics', plugins_url('/js/reporting.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-tabs'), WPLC_PLUGIN_VERSION, true);
        if (empty($statistics)) { $statistics = ''; }
        wp_localize_script('wplc-statistics', 'wplc_reporting_statistics', $statistics);
        wp_enqueue_script('wplc-statistics');
    }
}
function wplc_reporting_page(){
    $stats = return_statistics_mrg();
    $tabs = "";
    $styling_reporting = ".ui-tabs-vertical {  }
						  .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; max-width: 20%; min-width: 190px; }
						  .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%;border:none; margin: 0 -1px .2em 0; }
						  .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
						  .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; }
						  .ui-tabs-vertical .ui-tabs-panel { float: left;  min-width: 67%; max-width: 67%;}
						  textarea, input[type='text'], input[type='email'], input[type='password']{ width: 100% !important; }
						  .ui-tabs-anchor { width: 80%; }
						  .ui-widget-header { border: none; background: none; }
						  .ui-tabs-anchor:focus { box-shadow: none !important; }";

	$tabs .= "<style>" . $styling_reporting . "</style>";

    $tabs .= "<div class='wrap wplc_wrap'><h2>".__('Reports', 'wp-livechat')."</h2><div id='reporting_tabs'>";
    $tabs .= "<ul>";
    $tab = array(
        'overview' => __('Overview', 'wp-livechat'),
        'popular_pages' => __('Popular Pages', 'wp-livechat')
    );
    $tab = apply_filters('wplc_reporting_tabs', $tab);
    
    foreach( $tab as $key => $val ){
        $tabs .= "<li><a href='#$key'>$val</a></li>";
    }
    $tabs .= "</ul>";
    
    if( isset( $stats['individual_urls_counted'] ) ){
        $popular_pages_list = "<ul>";
        $urls = $stats['individual_urls_counted'];
        foreach( $urls as $key => $val ){
            $popular_pages_list .= "<li>$key ($val)</li>";
        }
        $popular_pages_list .= "</ul>";
    }
    $overview = "";
    $overview .= "<div style='width: 33%; display: inline-block; vertical-align: top; text-align: center;'><h2>".__('Total Agents', 'wp-livechat')."</h2><p>".$stats['total_agents_counted']."</p><small>".__('Total number of agents that used the live chat', 'wp-livechat')."</small></div>";
    $overview .= "<div style='width: 33%; display: inline-block; vertical-align: top; text-align: center;'><h2>".__('Total Chats', 'wp-livechat')."</h2><p>".$stats['total_chats']."</p><small>".__('Total number of chats received', 'wp-livechat')."</small></div>";
    $overview .= "<div style='width: 33%; display: inline-block; vertical-align: top; text-align: center;'><h2>".__('Total URLs', 'wp-livechat')."</h2><p>".$stats['total_urls_counted']."</p><small>".__('Total number of URLs a chat was initiated on', 'wp-livechat')."</small></div>";
    $overview .= '<h2>'.__('Chats per day', 'wp-livechat').'</h2><div id="columnchart_material" style="width: 100%; height: 350px;"></div>';
    $popular_pages = "<h2>".__('Popular pages a chat was initiated on', 'wp-livechat')."</h2>";
    $popular_pages .= "<div style='width: 50%; display: inline-block; vertical-align: top;'><div id='popular_pages_graph' style='width: 100%; height: 300px;'></div></div>";
    $popular_pages .= "<div style='width: 50%; display: inline-block; vertical-align: top;'>$popular_pages_list</div>";
    $tab_content = array(
        'overview' => $overview,
        'popular_pages' => $popular_pages,
    );
    $tab_content = apply_filters('wplc_reporting_tab_content', $tab_content);
    foreach( $tab_content as $key => $val ){
        $tabs .= "<div id='$key'>$val</div>";
    }
    $tabs .= "</div></div>";
    
    echo $tabs;
    
}
function return_statistics_mrg(){
    global $wpdb;
    global $wplc_tblname_chats;
    $sql = "SELECT * FROM `$wplc_tblname_chats` WHERE `agent_id` <> 0 ORDER BY `timestamp` DESC LIMIT 90";
    $results = $wpdb->get_results( $sql );
    $popular_user_agent = array();
    $chats_on_days = array();
    $popular_url = array();
    $chat_durations = array();
    $popular_chat_agent = array();
    $agent_data = array();
    foreach( $results as $result ){
        $ip_data = maybe_unserialize( $result->ip );
        $popular_chat_agent[] = $result->agent_id;
        $popular_user_agent[] = isset($ip_data['user_agent']) ? $ip_data['user_agent'] : __("Unknown", 'wp-live-chat-support');
        $chats_on_days[] = date('Y-m-d', strtotime( $result->timestamp ) );
        $popular_url[] = $result->url;
    }
    /* Agent Data */
    
    $agent_totals = array_count_values($popular_chat_agent );   
    arsort($agent_totals);
    $total_agent_count = count( $agent_totals );
    /* User Agent Data */   
    $user_agent_totals = array_count_values( $popular_user_agent );
    arsort($user_agent_totals);
    $total_user_agent = count( $user_agent_totals );
    /* Daily Chat Totals */
    $daily_chat_totals = array_count_values( $chats_on_days );
    // arsort($daily_chat_totals);
    $total_chats = count( $daily_chat_totals );
    /* URL Data */
    $popular_urls = array_count_values( $popular_url );
    arsort($popular_urls);
    $total_urls = count( $popular_urls );
    if( $agent_totals ){
        foreach( $agent_totals as $key => $val ){
            $user = get_user_by( 'id', intval( $key ) );
            if( $user ){
                $display_name = $user->data->display_name;
            } else {
                $display_name = '';
            }
            $agent_data[intval($key)] = $display_name;
        }
    }
    return array(
        'total_agents_counted' => $total_agent_count,
        'individual_agents_count' => $agent_data,
        'total_user_agents_counted' => $total_user_agent,
        'individual_user_agent_count' => $user_agent_totals,
        'daily_chat_totals' => $daily_chat_totals,
        'total_chats' => $total_chats,
        'total_urls_counted' => $total_urls,
        'individual_urls_counted' => $popular_urls,
        'other_data' => apply_filters('wplc_additional_statistic_data', '')
    );
}
function return_stats_strings_mrg() {
    return array(
        'url' => __('URL', 'wp-livechat'),
        'count' => __('Count', 'wp-livechat')
    );
}

add_filter( 'wplc_filter_message_sound', 'wplc_mrg_filter_control_message_sound', 10);
function wplc_mrg_filter_control_message_sound() {
  $wplc_settings = wplc_get_options();
  if (isset($wplc_settings['wplc_messagetone'])) {
    return wplc_get_messagetone_url($wplc_settings['wplc_messagetone']);
  }
  return wplc_get_messagetone_url('');  
}

add_filter("wplc_filter_wav_file","wplc_filter_control_wav_file",10);
function wplc_filter_control_wav_file() {
  $wplc_settings = wplc_get_options();
  if (isset($wplc_settings['wplc_ringtone'])) {
    return wplc_get_ringtone_url($wplc_settings['wplc_ringtone']);
  }
  return wplc_get_ringtone_url('');
}

add_action("wplc_advanced_settings_settings","wplc_hook_control_advanced_settings_settings",10);
function wplc_hook_control_advanced_settings_settings() {
  $wplc_settings = wplc_get_options();
	echo "<tr>";
  echo "	<td>".__("Enable Manual Chat Initiation:",'wp-live-chat-support')." <i class=\"fa fa-question-circle wplc_light_grey wplc_settings_tooltip\" title=\"".__("Enabling this feature will allow agents to start a chat with website visitors. This feature increases server load while enabled.",'wp-live-chat-support')."\"></i></td>";
  if ($wplc_settings['wplc_use_node_server']) {
    echo "	<td><input type='checkbox' name='wplc_enable_initiate_chat' value='1'".($wplc_settings['wplc_enable_initiate_chat'] ? ' checked' : '')."/></td>";
  } else {
    echo "	<td>".__("This feature is only available when you select 3CX High Performance Cloud Servers in Advanced Features.")."</td>";
  }
	echo "</tr>";
}

function wplc_random_quote_mrg() {
  $array = array(
    0 => "&ldquo;The true entrepreneur is a doer, not a dreamer.&rdquo; &ndash; Unknown",
    1 => "&ldquo;Please think about your legacy, because you're writing it every day.&rdquo; &ndash; Gary Vaynerchuck",
    2 => "&ldquo;A man should never neglect his family for business.&rdquo; &ndash; Walt Disney",
    3 => "&ldquo;Every accomplishment starts with a decision to try.&rdquo; &ndash; Unknown",
    4 => "&ldquo;There's no shortage of remarkable ideas, what's missing is the will to execute them.&rdquo; &ndash; Seth Godin",
    5 => "&ldquo;You only have to do a very few things right in your life so long as you don't do too many things wrong.&rdquo; &ndash; Warren Buffett",
    6 => "&ldquo;One finds limits by pushing them.&rdquo; &ndash; Herbert Simon",
    7 => "&ldquo;Wishes cost nothing unless you want them to come true&rdquo; &ndash; Steve Jobs",
    8 => "&ldquo;Hire character. Train skill.&rdquo; &ndash; Peter Schutz",
    9 => "&ldquo;A calm sea does not make a skilled sailor.&rdquo; &ndash; Unknown",
    10 => "&ldquo;Leadership is doing what is right when no one is watching.&rdquo; &ndash; George Van Valkenburg",
    11 => "&ldquo;Genius is one percent inspiration and ninety-nine percent perspiration.&rdquo; &ndash; Thomas A. Edison",
    12 => "&ldquo;Are you a serial idea-starting person? The goal is to be an idea-shipping person.&rdquo; &ndash; Seth Godin",
    13 => "&ldquo;To win without risk is to triumph without glory.&rdquo; &ndash; Pierre Corneille",
    14 => "&ldquo;The golden rule for every business man is this: Put yourself in your customer's place.&rdquo; &ndash; Orison Swett Marden",
    15 => "&ldquo;Darmok and Jalad at Tanagra.&rdquo; &ndash; Captain Dathon"
  );
  return $array[mt_rand(0,count($array)-1)];
}

function wplc_enqueue_admin_styles_mrg(){
	wp_enqueue_style('wplc-admin-pro-styles', plugins_url('/css/wplc_styles_admin_pro.css', __FILE__));
}

add_filter("wplc_filter_admin_from","wplc_filter_control_admin_from_mrg",10,3);
function wplc_filter_control_admin_from_mrg($content, $cid, $chat_data = false) {
  $wplc_settings = wplc_get_options();

  if (!$chat_data) {
    $chat_data = wplc_get_chat_data($cid,__LINE__);
  }
  if (isset($chat_data->agent_id)) {
    if ($wplc_settings['wplc_use_wp_name']) {
      $user_object = get_user_by("id", $chat_data->agent_id);
      if (is_object($user_object)) {
        $content = $user_object->data->display_name;
      }
    }
  }
  return $content;
}

add_action("wplc_add_js_admin_chat_area", "wplc_add_js_admin_chat_area_control_mrg", 10, 2);
function wplc_add_js_admin_chat_area_control_mrg($cid, $chat_data = false){
  $wplc_settings = wplc_get_options();
  if (!$chat_data) { $chat_data = wplc_get_chat_data($cid,__LINE__); }
  if ($chat_data) {
    if ($wplc_settings['wplc_use_wp_name']) {
      $tuser = get_user_by('id', $chat_data->agent_id);
      if ($tuser) {
        $tname = $tuser->display_name;
      } else {
        $tname = stripslashes($wplc_settings['wplc_chat_name']);
      }
  ?>
  <script>
  var wplc_name_override = "<?php echo sanitize_text_field($tname); ?>";
  </script>
  <?php
    } else if (!empty($wplc_settings['wplc_chat_name'])) {
  ?>
  <script>
  var wplc_name_override = "<?php echo sanitize_text_field(stripslashes($wplc_settings['wplc_chat_name'])) ?>";
  </script>
  <?php
    }
  } else {
    exit("Error connecting to the cloud server. Please refresh this page");
  }
}

register_activation_hook( __FILE__, 'wplc_auto_responder_activate_mrg' );
function wplc_auto_responder_activate_mrg(){

	$options = get_option( "WPLC_AUTO_RESPONDER_SETTINGS" );

	if( !is_array( $options ) ){

		$wplc_data['wplc_ar_from_name'] = get_bloginfo( 'name' );
	    $wplc_data['wplc_ar_from_email'] = get_bloginfo( 'admin_email' );
	    $wplc_data['wplc_ar_subject'] = __("Thank you for inquiry. We will get back to you shortly", 'wp-live-chat-support');
	    $wplc_data['wplc_ar_body'] = "
	    <p>Hi {wplc-user-name}</p>
	    <p>Thank you for getting in touch with us.</p>
	    <p>Your email has been received. We will get back to you shortly.</p>
	    ";

	    update_option( "WPLC_AUTO_RESPONDER_SETTINGS", $wplc_data );

	}
    
}

add_action("wplc_hook_offline_message", "wplc_offline_message_autoresponder_mrg");
function wplc_offline_message_autoresponder_mrg( $data ){
	
	$wplc_user_name = $data['name'];
	$wplc_user_email = $data['email'];

	$wplc_ar_settings = get_option("WPLC_AUTO_RESPONDER_SETTINGS");	

	if( isset( $wplc_ar_settings['wplc_ar_enable'] ) && $wplc_ar_settings['wplc_ar_enable'] == '1' ) { $wplc_ar_enable = $wplc_ar_settings['wplc_ar_enable']; } else { $wplc_ar_enable = FALSE; }
	if( isset( $wplc_ar_settings['wplc_ar_from_name'] ) ) { $wplc_ar_from_name = $wplc_ar_settings['wplc_ar_from_name']; } else { $wplc_ar_from_name = ""; }
	if( isset( $wplc_ar_settings['wplc_ar_from_email'] ) ) { $wplc_ar_from_email = $wplc_ar_settings['wplc_ar_from_email']; } else { $wplc_ar_from_email = ""; }
	if( isset( $wplc_ar_settings['wplc_ar_subject'] ) ) { $wplc_ar_subject = $wplc_ar_settings['wplc_ar_subject']; } else { $wplc_ar_subject = ""; }
	if( isset( $wplc_ar_settings['wplc_ar_body'] ) ) { $wplc_ar_body = $wplc_ar_settings['wplc_ar_body']; } else { $wplc_ar_body = ""; }
	
	if( $wplc_ar_enable ){
		
		preg_match_all('/{(.*?)}/', $wplc_ar_body, $matches);	

	    if (isset($matches[1])) {
	    	
	        foreach ($matches[1] as $key => $match) {

	            if ($wplc_ar_body) {
	            	
	            	if( $matches[1][$key] == 'wplc-user-name' ){
					
						$wplc_ar_body = str_replace( $matches[0][$key],$wplc_user_name,$wplc_ar_body );
	        		
	        		} else if ( $matches[1][$key] == 'wplc-email-address' ){
	        		
	        			$wplc_ar_body = str_replace( $matches[0][$key],$wplc_user_email,$wplc_ar_body );
	        		
	        		}
	            
	            }
	            
	        }
	                
	    }	    
	    /**
	     * Using contents of wplcmail function as we need additional functionality for this	     
	     */  	   
        $headers[] = 'Content-type: text/html';
        if (!empty($wplc_ar_from_name) && !empty($wplc_ar_from_email)) {
          $headers[] = 'Reply-To: '.$wplc_ar_from_name.' <'.$wplc_ar_from_email.'>';	                         
        }
        $overbody = apply_filters("wplc_filter_mail_body",$wplc_ar_subject, htmlspecialchars_decode(stripslashes( $wplc_ar_body ) ) );            
        if (!wp_mail($wplc_user_email, $wplc_ar_subject, $overbody, $headers)) {
            $error = date("Y-m-d H:i:s") . " WP-Mail Failed to send \n";
            error_log($error);
        }
        return;
	    
	}  
    
}

add_action( 'wplc_hook_head', 'wplc_auto_responder_settings_mrg' );

function wplc_auto_responder_settings_mrg(){

	if (isset($_POST['wplc_save_settings'])) {
		$wplc_data = array();
		if (isset($_POST['wplc_ar_enable'])) { $wplc_data['wplc_ar_enable'] = sanitize_text_field($_POST['wplc_ar_enable']); }
        if (isset($_POST['wplc_ar_from_name'])) { $wplc_data['wplc_ar_from_name'] = stripslashes(sanitize_text_field($_POST['wplc_ar_from_name'])); }
        if (isset($_POST['wplc_ar_from_email'])) { $wplc_data['wplc_ar_from_email'] = filter_var(sanitize_text_field($_POST['wplc_ar_from_email']), FILTER_SANITIZE_EMAIL); }
        if (isset($_POST['wplc_ar_subject'])) { $wplc_data['wplc_ar_subject'] = stripslashes(sanitize_text_field($_POST['wplc_ar_subject'])); }
        if (isset($_POST['wplc_ar_body'])) { $wplc_data['wplc_ar_body'] = stripslashes(sanitize_text_field($_POST['wplc_ar_body'])); }

        update_option( "WPLC_AUTO_RESPONDER_SETTINGS", $wplc_data );

	}

}

function wplc_return_times_array_mrg(){
	$hours = array();
	for( $i = 0; $i <= 23; $i++){
		$hours[] = sprintf('%02d', $i );
	}
	$minutes = array();	
	for( $i = 0; $i <= 59; $i++){
		$minutes[] = sprintf('%02d', $i );
	}
	$time = array(
		'hours' 	=> $hours,
		'minutes' 	=> $minutes
	);
	return $time;
}

function wplc_check_chatbox_enabled_business_hours() {
  $wplc_settings = wplc_get_options();
	if ($wplc_settings['wplc_bh_enable']) {
    $now_wp = current_time('timestamp'); // unix timestamp adjusted to wp timezone, considering also DST
    $now = (new DateTime("now", new DateTimeZone("UTC")))->getTimestamp(); // for sure UTC, no DST, bypassing PHP timezone configuration
    $skew = $now-$now_wp; // difference from wordpress time and UTC
    $now_dayofweek = gmdate('w', $now_wp);
    if (!empty($wplc_settings['wplc_bh_days'][$now_dayofweek])) {
      $now_day = gmdate('d', $now_wp);
      $now_month = gmdate('m', $now_wp);
      $now_year = gmdate('Y', $now_wp);
      // calculate time in UTC then add skew, so comparison is between UTC timestamps
      $t1 = $skew + gmmktime($wplc_settings['wplc_bh_schedule'][$now_dayofweek][0]['hs'], $wplc_settings['wplc_bh_schedule'][$now_dayofweek][0]['ms'], 0, $now_month, $now_day, $now_year);
      $t2 = $skew + gmmktime($wplc_settings['wplc_bh_schedule'][$now_dayofweek][0]['he'], $wplc_settings['wplc_bh_schedule'][$now_dayofweek][0]['me'], 0, $now_month, $now_day, $now_year);
      $t3 = $skew + gmmktime($wplc_settings['wplc_bh_schedule'][$now_dayofweek][1]['hs'], $wplc_settings['wplc_bh_schedule'][$now_dayofweek][1]['ms'], 0, $now_month, $now_day, $now_year);
      $t4 = $skew + gmmktime($wplc_settings['wplc_bh_schedule'][$now_dayofweek][1]['he'], $wplc_settings['wplc_bh_schedule'][$now_dayofweek][1]['me'], 0, $now_month, $now_day, $now_year);
      if (($now>=$t1 && $now<=$t2) || ($now>=$t3 && $now<=$t4)) {
        return true;
      }
    }
    return false;
  }
  return true;
}

add_filter( "wplc_filter_active_chat_box_notice", "wplc_active_business_hours_chat_box_notice" );
function wplc_active_business_hours_chat_box_notice() {
  $wplc_settings = wplc_get_options();
  $notice='';
  if ( $wplc_settings["wplc_settings_enabled"] == 1 && !wplc_check_chatbox_enabled_business_hours()) {
    $notice = '<div class="wplc-chat-box-notification wplc-chat-box-notification--disabled">';
    $notice .= '<p>' . __( 'The Live Chat box is currently disabled on your website due to:', 'wp-livechat' );
    $notice .= ' <a href="'.admin_url( 'admin.php?page=wplivechat-menu-settings#wplc-business-hours' ).'">' . __( 'Business Hours Settings', 'wp-livechat' ) . '</a></p>';
    $notice .= '</div>';
  }
  return $notice;
}

/**
 * Add to the array to determine which images need to be preloaded via JS on the front end.
 * 
 * @param  array $images Array of images to be preloaded
 * @return array
 */
add_filter( "wplc_get_images_to_preload", "wplc_mrg_get_images_to_preload", 10, 2);
function wplc_mrg_get_images_to_preload( $images, $wplc_settings ) {
    if ( isset($wplc_settings) && $wplc_settings['wplc_chat_pic'] ) {
		array_push( $images, urldecode($wplc_settings['wplc_chat_pic']) );
	}
	return $images;
}



/**
 * Identify which name we need to use for this agent
 *
 * This can either be the agents display name in WordPress, or the name set in the settings section
 * 
 * @param  string $name     Name
 * @param  array  $settings Array of ACBC settings
 * @return string           
 */
add_filter( "wplc_decide_agents_name", "wplc_filter_control_decide_agents_name_mrg", 10, 2 );
function wplc_filter_control_decide_agents_name_mrg( $name, $wplc_settings ) {
	if (!$wplc_settings['wplc_use_wp_name'] && !empty($wplc_settings['wplc_chat_name'])) {
    $name = $wplc_settings['wplc_chat_name'];
	}   
	return $name;
}

add_filter("wplc_pro_agent_list_before_button_filter", "wplc_mrg_agent_list_edit_profile_link_span", 10, 2);
/**
 * Adds an edit profile link to the agent section
 *
 * @return string (html)
*/
function wplc_mrg_agent_list_edit_profile_link_span($content, $user){
    $content .= "<small style='height:30px'><a href='".admin_url('user-edit.php?user_id=') . $user->ID . "#wplc-user-fields'>" . __("Edit Profile", 'wp-live-chat-support') . "</a></small>";
    return $content;
}

add_filter("wplc_filter_live_chat_box_above_main_div","wplc_mrg_drag_and_drop_div_frontend");
/**
 * Adds drag and drop front end container
 *
 * @return string (html)
*/
function wplc_mrg_drag_and_drop_div_frontend($ret_msg) {
    $ret_msg .= '<div id="chat_drag_zone" style="display:none;"><div id="chat_drag_zone_inner"><span>' . __("Drag Files Here", 'wp-live-chat-support') . '</span></div></div>';
    return $ret_msg;
}

/*
 * Add voice notes scripts to admin side
 */
if ( isset( $_GET['page'] ) && $_GET['page'] === 'wplivechat-menu' ) {
	add_action( 'wplc_admin_remoter_dashboard_scripts_localizer', 'wplc_voice_notes_admin_javascript' );
	add_action( 'wplc_hook_admin_javascript_chat', 'wplc_voice_notes_admin_javascript' );
	function wplc_voice_notes_admin_javascript() {
		$wplc_settings = wplc_get_options();
		if (false) { // TODO: check and reactivate $wplc_settings['wplc_enable_voice_notes_on_admin']) {
			wp_register_script( 'wplc-user-voice-notes-audio-recorder-wav', plugins_url( '/js/WebAudioRecorderWav.min.js', __FILE__ ), array(), WPLC_PLUGIN_VERSION, true);
			wp_register_script( 'wplc-user-voice-notes-audio-recorder', plugins_url( '/js/WebAudioRecorder.min.js', __FILE__ ),  array(), WPLC_PLUGIN_VERSION, true);
			wp_register_script( 'wplc-user-voice-notes-audio-wave-surfer', plugins_url( '/js/wavesurfer.min.js', __FILE__ ),  array(), WPLC_PLUGIN_VERSION, true);
			wp_register_script( 'wplc-user-voice-notes', plugins_url( '/js/wplc_voice_notes.js', __FILE__ ), array(
				'wplc-user-voice-notes-audio-recorder-wav',
				'wplc-user-voice-notes-audio-recorder',
				'wplc-user-voice-notes-audio-wave-surfer'
			), WPLC_PLUGIN_VERSION, true);
			wp_localize_script( 'wplc-user-voice-notes', 'wplc_visitor_voice', array(
				'plugin_url' => __( plugins_url( '/js/', __FILE__ ), 'wp-live-chat-support'),
				'str_delete' => __( 'Delete', 'wp-live-chat-support'),
				'str_save'   => __( 'Send...', 'wp-live-chat-support'),
				'play_sound' => __( 'Play voice note', 'wp-live-chat-support'),
				'ajax_url'   => admin_url( 'admin-ajax.php' )
			) );
			wp_enqueue_script( 'wplc-user-voice-notes' );

			wp_enqueue_style('wplc-admin-voice-note-styles', plugins_url( '/css/voice_note_admin.css', __FILE__ ));
		}
	}
}

/*
 * Add voice notes wrapper to admin remote dashboard
 */
if ( ! function_exists( 'wplc_admin_remote_voice_notes' ) ) {
	add_action( 'wplc_admin_remote_dashboard_below', 'wplc_admin_remote_voice_notes' );
	function wplc_admin_remote_voice_notes() { ?>
        <div class="wplc-voice-notes" style='display: none'>
            <span class="wplc-voice-notes__close fa fa-close"></span>
            <div id="wplc-voice-notes__recording-list"></div>
        </div>
		<?php
	}
}


/*
 * Add voice notes scripts to front end
 */
if ( ! function_exists( 'wplc_voice_notes_front_scripts' ) ) {
	add_action( 'wplc_hook_push_js_to_front', 'wplc_voice_notes_front_scripts' );
	function wplc_voice_notes_front_scripts() {
		$wplc_settings = wplc_get_options();
		if (false) { // TODO: check and re-enable $wplc_settings['wplc_enable_voice_notes_on_visitor']) {
			wp_register_script( 'wplc-visitor-voice-notes-audio-recorder-wav', plugins_url( '/js/WebAudioRecorderWav.min.js', __FILE__ ), array(), WPLC_PLUGIN_VERSION, true);
			wp_register_script( 'wplc-visitor-voice-notes-audio-recorder', plugins_url( '/js/WebAudioRecorder.min.js', __FILE__ ), array(), WPLC_PLUGIN_VERSION, true);
			wp_register_script( 'wplc-user-voice-notes-audio-wave-surfer', plugins_url( '/js/wavesurfer.min.js', __FILE__ ), array(), WPLC_PLUGIN_VERSION, true);
			wp_register_script( 'wplc-visitor-voice-notes', plugins_url( '/js/wplc_visitor_voice_notes.js', __FILE__ ), array(
				'wplc-visitor-voice-notes-audio-recorder-wav',
				'wplc-visitor-voice-notes-audio-recorder',
				'wplc-user-voice-notes-audio-wave-surfer'
			), WPLC_PLUGIN_VERSION, true);
			wp_localize_script( 'wplc-visitor-voice-notes', 'wplc_visitor_voice', array(
				'plugin_url' => __( plugins_url( '/js/', __FILE__ ), 'wp-live-chat-support'),
				'str_delete' => __( 'Delete', 'wp-live-chat-support'),
				'str_save'   => __( 'Save...', 'wp-live-chat-support'),
				'play_sound' => __( 'Play voice note', 'wp-live-chat-support'),
                'ajax_url'   => admin_url( 'admin-ajax.php' )
			) );
			wp_enqueue_script( 'wplc-visitor-voice-notes' );

			wp_enqueue_style('wplc-visitor-voice-note-styles', plugins_url( '/css/voice_notes_user.css', __FILE__ ));

		}
	}
}

/**
 * Checks if the file contains an extension which is allowed safe
*/
function wplc_check_file_name_for_safe_extension($filename) {
  global $wplc_allowed_extensions;
  return preg_match('/^.*\\.('.$wplc_allowed_extensions.')$/i', $filename);
}

/**
 * Check the mime type if possible on this server
*/
function wplc_check_file_mime_type($filepath) {

  $mime = false;
  if (file_exists($filepath)){
    if (class_exists('finfo')) { // best option is using fileinfo class
      $result = new finfo();
      if (is_resource($result) === true) {
        $mime = $result->file(realpath($filepath), FILEINFO_MIME_TYPE);
      }
    } 
    if ($mime === false)  { // fallback on mime_content_type()
      if (function_exists('mime_content_type')) {
        $mime = mime_content_type($filepath);
      }
    }
  }

  if ($mime !== false) {
    //We have managed to pull the mime type
    $allowed_mime_types = array(
      'image/gif', 'image/jpeg', 'image/tiff', 'image/bmp','image/png', 
      'audio/aac', 'video/x-msvideo', 'video/mpeg','audio/wav', 'audio/mpeg', 'video/webm', 'audio/ogg', 'video/ogg',
      'application/pdf', 'text/csv', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
      'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain',
      'application/zip', 'application/gzip', 'application/x-rar-compressed', 'application/x-7z-compressed'
    );
    return in_array($mime, $allowed_mime_types);
  }
  return false; 
}

/**
 * Adds compatibility for start chat notifications back into the node system
*/
function wplc_mrg_chat_notification_extender($data) {
  return wplc_mrg_notify_via_email($data);
}
add_action('wplc_hook_initiate_chat', 'wplc_mrg_chat_notification_extender', 99, 1);

/*
* registers wplc_common_node.js passing WPLC_PLUGIN_URL and WPLC_CHAT_SERVER
*/
function wplc_register_common_node() {
  global $wplc_allowed_extensions;
  $wplc_settings = wplc_get_options();
  wp_register_script('wplc-admin-js-agent-common', WPLC_PLUGIN_URL.'js/wplc_common_node.js', null, WPLC_PLUGIN_VERSION, true);
  $current_user=wp_get_current_user();
  $date_days=array();
  for($i=0;$i<7;$i++) {
    $date_days[] = ucfirst(date_i18n('l', gmmktime(12,0,0,1,2+$i,2000))); // 2000-01-02 is sunday
  }
  $date_months=array();
  for($i=0;$i<12;$i++) {
    $date_months[] = date_i18n('F', gmmktime(12,0,0,1+$i,10,2000));
  }

  $country_code = '';
  $country_name = '';
  if ($wplc_settings['wplc_use_geolocalization'] && function_exists('geoip_detect2_get_info_from_ip')) {
    $record = geoip_detect2_get_info_from_current_ip();
    if ($record && $record->country) {
      $country_code = strtolower($record->country->isoCode);
      $country_name = $record->country->names['en'];
      if ($record->city) {
        $country_name.=' - '.$record->city->names['en'];
      }
    }
  }

  $node_config=array(
    'baseurl'=>WPLC_PLUGIN_URL,
    'serverurl'=>WPLC_CHAT_SERVER,
    'enable_typing_preview'=>wplc_get_enable_typing_preview(),
    'ring_override'=>wplc_get_ringtone_url_from_config(),
    'message_override'=>wplc_get_messagetone_url_from_config(),
    'current_wpuserid'=>$current_user->ID,
    'allowed_upload_extensions'=>$wplc_allowed_extensions,
    'wplc_use_node_server'=>$wplc_settings['wplc_use_node_server'],
    'wplc_localized_string_is_typing_single'=>' '.__("is typing...",'wp-live-chat-support'),
    'wplc_user_default_visitor_name' => wplc_get_user_name('', $wplc_settings),
    'wplc_text_no_visitors' => __("There are no visitors on your site at the moment",'wp-live-chat-support'),
    'wplc_text_connection_lost' => __("Connection to the server lost, reconnecting...",'wp-live-chat-support'),
    'wplc_text_not_accepting_chats' => __("Agent offline - not accepting chats", 'wp-live-chat-support'),
    'wplc_text_minimizechat' => __("Minimize Chat", 'wp-live-chat-support'),
    'wplc_text_endchat' => __("End Chat", 'wp-live-chat-support'),
    'country_code' => $country_code,
    'country_name' => $country_name,
    'date_days' => $date_days,
    'date_months' => $date_months,
  );
  wp_localize_script( 'wplc-admin-js-agent-common', 'config', $node_config );
  wp_enqueue_script('wplc-admin-js-agent-common');
}

/*
* Fetches news feed from the WPLC website
*/
function wplc_fetch_feed(){
	$response = wp_remote_get('https://feed.wp-livechat.com',	array( 'timeout' => 2, 'httpversion' => '1.1', 'user-agent'  => 'WordPress'));
    $response_code = wp_remote_retrieve_response_code($response);
	
	if ( is_wp_error( $response ) || !is_array( $response ) || $response_code !== 200 ) {
   		_e("An error has occured while fetching the news feed.",'wp-live-chat-support');
	} else {
		echo $response['body']; 
	}
	
}

/*
* Get Visitor's IP
*/
function wplc_get_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    	$ip = "0";
	} 
	
	return sanitize_text_field($ip);
}

function wplc_get_theme() {
  $wplc_theme = "theme-default"; 
  $wplc_settings = wplc_get_options();
  if (isset($wplc_settings['wplc_theme'])) {
    $wplc_theme = $wplc_settings['wplc_theme'];
  }
  return $wplc_theme;
}

function wplc_get_enable_typing_preview() {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_gdpr_enabled'] || !$wplc_settings['wplc_use_node_server']) {
    return false;
  }
  return $wplc_settings['wplc_node_enable_typing_preview'];
}

function wplc_get_tone_name($tone, $path) {
  $tonename = '';
  if (!empty($tone)) {
    $tonename = basename($tone);
  }
  if (!file_exists($path.$tonename)) {
    $tonename='';
  }
  return $tonename;
}

function wplc_get_tone_url($tone, $path, $url, $default) {
  $tonename=wplc_get_tone_name($tone, $path);
  if (empty($tonename)){
    return $default;
  }
  return $url.$tonename;
}

function wplc_get_ringtone_name($ringtone) {
  return wplc_get_tone_name($ringtone, WPLC_PLUGIN_DIR."/includes/sounds/");
}

function wplc_get_messagetone_name($messagetone) {
  return wplc_get_tone_name($messagetone, WPLC_PLUGIN_DIR."/includes/sounds/message/");
}

function wplc_get_ringtone_url($ringtone) {
  return wplc_get_tone_url($ringtone,  WPLC_PLUGIN_DIR."/includes/sounds/", WPLC_PLUGIN_URL.'includes/sounds/', WPLC_PLUGIN_URL.'includes/sounds/general/ring.wav');
}

function wplc_get_messagetone_url($messagetone) {
  return wplc_get_tone_url($messagetone,  WPLC_PLUGIN_DIR."/includes/sounds/message/", WPLC_PLUGIN_URL.'includes/sounds/message/', WPLC_PLUGIN_URL.'includes/sounds/general/ding.mp3');
}

function wplc_get_ringtone_url_from_config() {
  $wplc_settings = wplc_get_options();
  $ringtone='';
  if (isset($wplc_settings['wplc_ringtone'])) {
    $ringtone=$wplc_settings['wplc_ringtone'];
  }
  return wplc_get_ringtone_url($ringtone);
}

function wplc_get_messagetone_url_from_config() {
  $wplc_settings = wplc_get_options();
  $messagetone='';
  if (isset($wplc_settings['wplc_messagetone'])) {
    $messagetone=$wplc_settings['wplc_messagetone'];
  }
  return wplc_get_messagetone_url($messagetone);
}

function wplc_sounds_get_array($path) {
  $wplc_ringtones = array('' => __("Default",'wp-live-chat-support'));
  $files = scandir($path);
  foreach ($files as $value) {
    if (empty($value) || is_dir($path.$value)) {
      continue;
    }
    $value=basename($value);
    $wplc_ringtones[$value] = str_replace('_', ' ', pathinfo($value, PATHINFO_FILENAME));
  }
  return $wplc_ringtones;
}

if (!function_exists('wplc_clean_session')) {
	function wplc_clean_session() {
    wplc_start_session();
    $s = $_SESSION;
    foreach($s as $k=>$v) {
      if (substr($k,0,5)=='wplc_') {
        unset($_SESSION[$k]);
      }
    }
		session_regenerate_id(); // is this really needed?
		wplc_close_session();
	}
}

function wplc_set_u_session($cid) {
	if (!empty($cid)) {
		wplc_clean_session();
    $rest_token = get_option("wplc_api_secret_token");
    $wplc_node_token = wplc_node_server_token_get();
    wplc_start_session();
    $_SESSION['wplc_session_chat_session_id'] = intval($cid);
    $_SESSION['wplc_session_chat_session_active'] = 1;	
    if ($rest_token) {
      $_SESSION['wplc_rest_token'] = $rest_token;
    }
    if ($wplc_node_token) {
      $_SESSION['wplc_cloud_token'] = $wplc_node_token;
    }
    wplc_close_session();
    return $cid;
	}
}

function wplc_start_session() {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
}

function wplc_close_session() {
	if (session_status() == PHP_SESSION_ACTIVE) {
		session_write_close();
	}
}

function wplc_force_bool($arr, $key, $default=false) {
  if (isset($arr[$key])) {
    return boolval($arr[$key]);
  }
  if (is_array($default)) {
    return $default[$key];
  }
  return $default;
}

function wplc_force_int_range($i, $min=null, $max=null) {
  if ($min!=null && $i<$min) {
    $i = $min;
  }
  if ($max!=null && $i>$max) {
    $i = $max;
  }
  return $i;
}

function wplc_force_int($arr, $key, $default=0, $min=null, $max=null) {
  $res = $default;
  if (isset($arr[$key])) {
    $res = intval($arr[$key]);
  } else {
    if (is_array($default)) {
      $res = $default[$key];
    }
  }
  $res = wplc_force_int_range($res, $min, $max);
  return $res;
}

function wplc_force_string($arr, $key, $default='') {
  if (isset($arr[$key])) {
    return trim(strval($arr[$key]));
  }
  if (is_array($default)) {
    return $default[$key];
  }
  return $default;
}

function wplc_force_url($arr, $key, $default='') {
  $url='';
  if (isset($arr[$key])) {
    $url = strtolower(trim($arr[$key]));
  }
  if (!empty($url) && !(substr($url,0,7)=='http://' || substr($url,0,8)=='https://')) {
    $url = 'https://'.$url;
  }
  if (filter_var($url, FILTER_VALIDATE_URL)) {
    return $url;
  }
  if (is_array($default)) {
    return $default[$key];
  }
  return $default;
}

function wplc_cleanup_old_options($wplc_settings) {
  // parameters migration from WPLC_ACBC_SETTINGS to WPLC_SETTINGS
  $wplc_acbc_settings = get_option('WPLC_ACBC_SETTINGS');
  if (!empty($wplc_acbc_settings)) {
    if (isset($wplc_acbc_settings['wplc_chat_delay'])) {
      $wplc_settings['wplc_chat_delay'] = intval($wplc_acbc_settings['wplc_chat_delay']);
    }
    if (isset($wplc_acbc_settings['wplc_chat_icon'])) {
      $wplc_settings['wplc_chat_icon'] = strval($wplc_acbc_settings['wplc_chat_icon']);
    }
    if (isset($wplc_acbc_settings['wplc_chat_logo'])) {
      $wplc_settings['wplc_chat_logo'] = strval($wplc_acbc_settings['wplc_chat_logo']);
    }
    if (isset($wplc_acbc_settings['wplc_chat_name'])) {
      $wplc_settings['wplc_chat_name'] = strval($wplc_acbc_settings['wplc_chat_name']);
    }
    if (isset($wplc_acbc_settings['wplc_chat_pic'])) {
      $wplc_settings['wplc_chat_pic'] = strval($wplc_acbc_settings['wplc_chat_pic']);
    }
    if (isset($wplc_acbc_settings['wplc_pro_chat_email_address'])) {
      $wplc_settings['wplc_pro_chat_email_address'] = strval($wplc_acbc_settings['wplc_pro_chat_email_address']);
    }
    if (isset($wplc_acbc_settings['wplc_pro_chat_notification'])) {
      $wplc_settings['wplc_pro_chat_notification'] = strval($wplc_acbc_settings['wplc_pro_chat_notification']);
    }
    if (isset($wplc_acbc_settings['wplc_social_fb'])) {
      $wplc_settings['wplc_social_fb'] = strval($wplc_acbc_settings['wplc_social_fb']);
    }
    if (isset($wplc_acbc_settings['wplc_social_tw'])) {
      $wplc_settings['wplc_social_tw'] = strval($wplc_acbc_settings['wplc_social_tw']);
    }
    if (isset($wplc_acbc_settings['wplc_use_wp_name'])) {
      $wplc_settings['wplc_use_wp_name'] = boolval($wplc_acbc_settings['wplc_use_wp_name']);
    }
    delete_option('WPLC_ACBC_SETTINGS');
  }

  $wplc_encrypt_data = get_option("WPLC_ENCRYPT_SETTINGS");
  if (!empty($wplc_encrypt_data)) {
    $wplc_settings['wplc_enable_encryption'] = boolval($wplc_encrypt_data['wplc_enable_encryption']);
    delete_option('WPLC_ENCRYPT_SETTINGS');
  }

  $wplc_inex_data = get_option("WPLC_INEX_SETTINGS");
  if (!empty($wplc_inex_data)) {
    if (isset($wplc_inex_data['wplc_exclude_from_pages'])) {
      $wplc_settings['wplc_exclude_from_pages'] = strval($wplc_inex_data['wplc_exclude_from_pages']);
    }
    if (isset($wplc_inex_data['wplc_include_on_pages'])) {
      $wplc_settings['wplc_include_on_pages'] = strval($wplc_inex_data['wplc_include_on_pages']);
    }
    if (isset($wplc_inex_data['wplc_exclude_home'])) {
      $wplc_settings['wplc_exclude_home'] = boolval($wplc_inex_data['wplc_exclude_home']);
    }
    if (isset($wplc_inex_data['wplc_exclude_archive'])) {
      $wplc_settings['wplc_exclude_archive'] = strval($wplc_inex_data['wplc_exclude_archive']);
    }
    if (isset($wplc_inex_data['wplc_exclude_post_types'])) {
      $wplc_settings['wplc_exclude_post_types'] = strval($wplc_inex_data['wplc_exclude_post_types']);
    }
    delete_option('WPLC_INEX_SETTINGS');
  }  

  $wplc_choose_data = get_option("WPLC_CHOOSE_SETTINGS");
  if (!empty($wplc_choose_data)) {
    if (isset($wplc_choose_data['wplc_auto_online'])) {
      $wplc_settings['wplc_allow_agents_set_status'] = boolval($wplc_choose_data['wplc_auto_online']);
    }
    delete_option('WPLC_CHOOSE_SETTINGS');
  }

  $wplc_et_data = get_option("WPLC_ET_SETTINGS");
  if (!empty($wplc_et_data)) {
    if (isset($wplc_et_data['wplc_enable_transcripts'])) {
      $wplc_settings['wplc_enable_transcripts'] = boolval($wplc_et_data['wplc_enable_transcripts']);
    }
    if (isset($wplc_et_data['wplc_send_transcripts_when_chat_ends'])) {
      $wplc_settings['wplc_send_transcripts_when_chat_ends'] = boolval($wplc_et_data['wplc_send_transcripts_when_chat_ends']);
    }
    if (isset($wplc_et_data['wplc_send_transcripts_to'])) {
      $wplc_settings['wplc_send_transcripts_to'] = strval($wplc_et_data['wplc_send_transcripts_to']);
    }
    if (isset($wplc_et_data['wplc_et_email_body'])) {
      $wplc_settings['wplc_et_email_body'] = strval($wplc_et_data['wplc_et_email_body']);
    }
    if (isset($wplc_et_data['wplc_et_email_header'])) {
      $wplc_settings['wplc_et_email_header'] = strval($wplc_et_data['wplc_et_email_header']);
    }
    if (isset($wplc_et_data['wplc_et_email_footer'])) {
      $wplc_settings['wplc_et_email_footer'] = strval($wplc_et_data['wplc_et_email_footer']);
    }
    delete_option('WPLC_ET_SETTINGS');
  }

  $wplc_bh_settings = get_option("WPLC_BH_SETTINGS");
  if (!empty($wplc_bh_settings)) {
    if (isset($wplc_bh_settings['enabled'])) {
      $wplc_settings['wplc_bh_enable'] = boolval($wplc_bh_settings['enabled']);
    }
    if (isset($wplc_bh_settings['interval'])) {
      $wplc_settings['wplc_bh_interval'] = intval($wplc_bh_settings['interval']);
    }
    if (isset($wplc_bh_settings['start'])) {
      if (isset($wplc_bh_settings['start']['h'])) {
        $wplc_settings['wplc_bh_hours_start'] = intval($wplc_bh_settings['start']['h']);
      }
      if (isset($wplc_bh_settings['start']['m'])) {
        $wplc_settings['wplc_bh_minutes_start'] = intval($wplc_bh_settings['start']['m']);
      }
    }
    if (isset($wplc_bh_settings['end'])) {
      if (isset($wplc_bh_settings['end']['h'])) {
        $wplc_settings['wplc_bh_hours_end'] = intval($wplc_bh_settings['end']['h']);
      }
      if (isset($wplc_bh_settings['end']['m'])) {
        $wplc_settings['wplc_bh_minutes_end'] = intval($wplc_bh_settings['end']['m']);
      }
    }
    delete_option('WPLC_BH_SETTINGS');
  }  

  $wplc_advanced_settings = get_option("WPLC_ADVANCED_SETTINGS");
  if (!empty($wplc_advanced_settings)) {
    if (isset($wplc_advanced_settings['wplc_iterations'])) {
      $wplc_settings['wplc_iterations'] = intval($wplc_advanced_settings['wplc_iterations']);
    }
    if (isset($wplc_advanced_settings['wplc_delay_between_loops'])) {
      $wplc_settings['wplc_delay_between_loops'] = intval($wplc_advanced_settings['wplc_delay_between_loops']);
    }
    delete_option('WPLC_ADVANCED_SETTINGS');
  }
  
  if (empty($wplc_settings['wplc_encryption_key'])) {
    $wplc_settings['wplc_encryption_key']=wplc_generate_encryption_key();
  }
    
  return $wplc_settings;
}

function wplc_ajax_generate_new_tokens() {
  if (isset($_POST['action']) && isset($_POST['nonce']) && wplc_user_is_agent()) {
    $nonce = $_POST['nonce'];
    $action = $_POST['action'];
    switch($action) {
      case 'wplc_generate_new_node_token':
        if (wp_verify_nonce( $nonce, 'generate_new_token')) {
          $res = wplc_node_server_token_get(true);
          echo json_encode(array(
            'key'=>$res,
            'error'=>''
          ));
          wp_die();          
        }
        break;

      case 'wplc_new_secret_key':
        if (wp_verify_nonce( $nonce, 'generate_new_secret_token')) {
          $user_token = wplc_api_s_key_create();
          update_option("wplc_api_secret_token", $user_token);
          echo json_encode(array(
            'key'=>$user_token,
            'error'=>''
          ));
          wp_die();
        }
        break;

      case 'wplc_generate_new_encryption_key':
        if (wp_verify_nonce( $nonce, 'generate_new_encryption_key')) {
          $wplc_settings = wplc_get_options();
          $key=wplc_generate_encryption_key();
          $wplc_settings['wplc_encryption_key']=$key;
          update_option('WPLC_SETTINGS', $wplc_settings);
          echo json_encode(array(
            'key'=>$wplc_settings['wplc_encryption_key'],
            'error'=>''
          ));
          wp_die();
        }        
        break;
    }
  }
  echo json_encode(array(
    'key'=>'',
    'error'=>__("You do not have permission to perform this action",'wp-live-chat-support')
  ));
  wp_die();
}

function wplc_get_user_name($name, $wplc_settings) {
  global $wplc_default_settings_array;
  $name = trim($name);
  if (empty($name)) {
    $name = $wplc_settings['wplc_user_default_visitor_name'];
  }
  if (empty($name)) {
    $name = __($wplc_default_settings_array['wplc_user_default_visitor_name'], 'wp-live-chat-support');
  }
  return $name;
}

// **********************************
// Agents functions
// **********************************

/**
 * Checks if specified user is an agent. Default is get_current_user_id
 * @return bool
 */
function wplc_user_is_agent($uid=0) {
  if (empty($uid)) {
    $user=wp_get_current_user();
  } else {
    $user=get_user_by('id', $uid);
  }
  if ($user) {
    return $user->has_cap('wplc_ma_agent');
  }
  return false;
}

/**
 * Returns a list of users with agent attribute
 * @return array(WPuser)
 */
function wplc_get_agent_users() {
  return get_users(array('meta_key' => 'wplc_ma_agent'));
}


function wplc_update_agent_time($uid=0) {
  if (empty($uid)) {
    $uid=get_current_user_id();
  }
  update_user_meta($uid, "wplc_chat_agent_online", time());
}

/**
 * Checks if a specified agent is online. Default is get_current_user_id
 * @return bool
 */
function wplc_agent_is_online($uid) {
  $agents = wplc_get_online_agent_users();
  foreach($agents as $agent) {
    if ($agent->ID == $uid) {
      return true;
    }
  }
  return false;
}

// return number of online agents
function wplc_get_online_agent_users_count() {
  return count(wplc_get_online_agent_users());
}

/**
 * Checks if there is at least one agent online
 * @return bool
 */
function wplc_one_agent_is_online() {
  return wplc_get_online_agent_users_count()>0;
}

/**
 * Checks if there is at least one agent available for chat
 * @return bool
 */
function wplc_agent_is_available() {
  return wplc_one_agent_is_online() && wplc_check_chatbox_enabled_business_hours();
}

// check if agents are really online by checking heartbeat on wplc_chat_agent_online meta
function wplc_check_agents_timeout() {
  wplc_internal_get_all_agents_users_online();
}

/**
 * Returns a list of online agents
 * @return array(WPuser)
 */
function wplc_get_online_agent_users() {
  $agents = wplc_internal_get_all_agents_users_online();
  $online_agents = array();
  foreach($agents as $v) {
    if (wplc_get_agent_accepting($v->ID)) {
      $online_agents[]=$v;
    }
  }
  return $online_agents;
}

function wplc_get_agent_accepting($uid=0) {
  if (empty($uid)) {
    $uid=get_current_user_id();
  }  
  $wplc_settings = wplc_get_options();
  $choose_array = wplc_internal_get_all_agents_accepting();
  if (!isset($choose_array[$uid])) {
    $choose_array[$uid]=true;
    update_option("WPLC_CHOOSE_ACCEPTING", $choose_array);
  }
  if (!$wplc_settings['wplc_allow_agents_set_status'] && !$choose_array[$uid]) { // force online if agents cannot set status
    $choose_array[$uid]=true;
    update_option("WPLC_CHOOSE_ACCEPTING", $choose_array);
  }
  return $choose_array[$uid];
}

function wplc_set_agent_accepting($uid, $online) {
  $choose_array = wplc_internal_get_all_agents_accepting();
  if (!isset($choose_array[$uid]) || $choose_array[$uid]!=boolval($online)) {
    $choose_array[$uid]=boolval($online);
    update_option("WPLC_CHOOSE_ACCEPTING", $choose_array);
  }
}

function wplc_internal_get_all_agents_accepting() {
  $choose_array = get_option("WPLC_CHOOSE_ACCEPTING");
  if (!is_array($choose_array)) {
    $choose_array = array();
  }
  return $choose_array;
}

function wplc_internal_get_all_agents_users_online() {
  $result=array();
  $agents =  get_users(array('meta_key'=> 'wplc_chat_agent_online'));
  if (!is_array($agents)) {
    $agents = array();
  }
  foreach($agents as $agent) {
    $check = get_user_meta($agent->ID, "wplc_chat_agent_online");
    if ($check && $check[0] && time()-$check[0]<120) {
      $result[]=$agent;
    } else {
      delete_user_meta($agent->ID, "wplc_chat_agent_online"); // force offline
    }
  }
  return $result;
}