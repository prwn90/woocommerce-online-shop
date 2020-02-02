<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

add_action( 'edit_user_profile', 'wplc_user_profile_fields_mrg' );
add_action( 'show_user_profile', 'wplc_user_profile_fields_mrg' );

function wplc_user_profile_fields_mrg( $user ){
  $ret = "";
  $ret .= "<a name='wplc-user-fields'></a><h2>".__( 'WP Live Chat by 3CX - User Fields', 'wp-live-chat-support')."</h2>";
  $ret .= "<table class='form-table'>";
  $ret .= "<tr>";
  $ret .= "<th>";
  $ret .= "<label for='wplc_user_tagline'>".__('User tagline', 'wp-live-chat-support')."</label>";
  $ret .= "</th>";
  $ret .= "<td>";
  $ret .= "<label for='wplc_user_tagline'>";
  $predefined = ""; 
  if ( get_the_author_meta( 'wplc_user_tagline', $user->ID ) != "" ) { 
    $predefined = sanitize_text_field( get_the_author_meta( 'wplc_user_tagline', $user->ID ) ); 
  }
  $ret .= "<textarea name='wplc_user_tagline' id='wplc_user_tagline' rows='6'>".$predefined."</textarea><br/>";
  $ret .= "<small>".__( 'This will show up at the top of the chatbox - Leave blank to disable.', 'wp-live-chat-support')."</small>";
  $ret .= "</label>";
  $ret .= "</td>";
  $ret .= "</tr>";
  $ret .= "</table>";
  echo $ret;
}

add_action('edit_user_profile_update', 'wplc_save_user_profile_data_mrg'); 
add_action('personal_options_update', 'wplc_save_user_profile_data_mrg');

function wplc_save_user_profile_data_mrg( $user_id ){
  if (!current_user_can('edit_user', $user_id)) {
    return false;
  }    
  if( isset( $_POST['wplc_user_tagline'] ) ){
    $predefined_response = wp_kses( nl2br( $_POST['wplc_user_tagline'] ), array( 'br' ) );
    update_user_meta( $user_id, 'wplc_user_tagline', $predefined_response );
  }    
}

add_filter("wplc_filter_further_live_chat_box_above_main_div","wplc_mrg_filter_control_live_chat_box_above_main_div",10,5);

function wplc_mrg_filter_control_live_chat_box_above_main_div( $msg, $wplc_settings, $cid, $chat_data, $agent ) {
  if ($wplc_settings['wplc_newtheme'] == "theme-2") {
    $agent_tagline = '';
    if ( $cid ) {
      if ( isset( $chat_data->agent_id ) ) {
        $agent_id = intval( $chat_data->agent_id );
      } else { 
        $agent_id = get_current_user_id(); 
      }
      if ( $agent_id ) {
        $tagline = get_user_meta( $agent_id, 'wplc_user_tagline', true );
        if( $tagline !== "" ){
          $agent_tagline = '<span class="wplc_agent_infosection wplc_agent_tagline wplc-color-2">'.$tagline.'</span>';
        }
        $msg = $agent_tagline;
      }
    }
  }
  return $msg;
}

add_filter( "wplc_filter_agent_data_agent_tagline", "wplc_mrg_filter_control_agent_data_agent_tagline", 10, 7 );
function wplc_mrg_filter_control_agent_data_agent_tagline(  $agent_tagline, $cid, $chat_data, $agent, $wplc_settings, $user_info, $data ) {
  if( !isset( $data ) ){ $data = false; }
  $tagline = get_user_meta( intval($chat_data->agent_id), 'wplc_user_tagline', true );
  if( $tagline !== "" ){
    return $tagline;
  }
  return '';
}

add_filter( "wplc_filter_simple_agent_data_agent_tagline", "wplc_mrg_filter_simple_control_agent_data_agent_tagline", 10, 2 );
function wplc_mrg_filter_simple_control_agent_data_agent_tagline(  $agent_tagline, $agent_id ) {
  if( !isset( $data ) ){ $data = false; }
  $tagline = get_user_meta( intval($agent_id), 'wplc_user_tagline', true );
  if( $tagline !== "" ){
    return $tagline;
  }
  return '';
}