<?php
/*
  Plugin Name: WP-Live Chat by 3CX
  Plugin URI: https://www.3cx.com/wp-live-chat/
  Description: The easiest to use website live chat plugin. Let your visitors chat with you and increase sales conversion rates with WP-Live Chat by 3CX.
  Version: 8.1.6
  Author: 3CX
  Author URI: https://www.3cx.com/wp-live-chat/
  Domain Path: /languages
  License: GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html  
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wplc_p_version;
global $wplc_tblname;
global $wpdb;
global $wplc_tblname_chats;
global $wplc_tblname_msgs;
global $wplc_tblname_offline_msgs;
global $current_chat_id; 
global $wplc_tblname_chat_ratings; 
global $wplc_tblname_chat_triggers;

/**
 * This stores the admin chat data once so that we do not need to keep sourcing it via the WP DB or Cloud DB
 */
global $admin_chat_data;
$admin_chat_data = false;

$wplc_tblname_offline_msgs = $wpdb->prefix . "wplc_offline_messages";
$wplc_tblname_chats = $wpdb->prefix . "wplc_chat_sessions";
$wplc_tblname_msgs = $wpdb->prefix . "wplc_chat_msgs";
$wplc_tblname_chat_ratings = $wpdb->prefix . "wplc_chat_ratings";
$wplc_tblname_chat_triggers = $wpdb->prefix . "wplc_chat_triggers";

$wplc_default_settings_array = array();

function wplc_set_defaults() {
  global $wplc_default_settings_array;
  if (empty($wplc_default_settings_array)) {
    $wplc_default_settings_array = array(
      'wplc_allow_department_selection' => false,
      'wplc_allow_agents_set_status' => true,
      'wplc_animation' => '',
      'wplc_auto_pop_up' => 0,
      'wplc_auto_pop_up_online' => false,
      'wplc_auto_pop_up_mobile' => true,
      'wplc_avatar_source' => '',
      'wplc_bh_days' => '0111110',
      'wplc_bh_enable' => false,
      'wplc_bh_schedule' => array(
        0 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
        1 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
        2 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
        3 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
        4 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
        5 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
        6 => array(
          0 => array('hs'=>9, 'ms'=>0, 'he'=>13, 'me'=>0),
          1 => array('hs'=>14, 'ms'=>0, 'he'=>18, 'me'=>0)
        ),
      ),
      'wplc_chat_delay' => 2,
      'wplc_chat_icon' => plugins_url( '/', __FILE__ ).'images/chaticon.png',
      'wplc_chat_logo' => '',
      'wplc_chat_name' => __( 'Admin', 'wp-live-chat-support'),
      'wplc_chat_pic' => plugins_url( '/', __FILE__ ).'images/picture-for-chat-box.jpg',
      'wplc_chatbox_height' => 70,
      'wplc_chatbox_absolute_height' => 400,
      'wplc_close_btn_text' => __("close", 'wp-live-chat-support'),
      'wplc_default_department' => -1,
      'wplc_delay_between_loops' => 500,
      'wplc_delete_db_on_uninstall' => true,
      'wplc_disable_emojis' => false,
      'wplc_display_to_loggedin_only' => false,
      'wplc_elem_trigger_action' => 0,
      'wplc_elem_trigger_id' => '',
      'wplc_elem_trigger_type' => 0,
      'wplc_enable_all_admin_pages' => false,
      'wplc_enable_encryption' => false,
      'wplc_enable_font_awesome' => true,
      'wplc_enable_initiate_chat' => false,
      'wplc_enable_msg_sound' => true,
      'wplc_enable_transcripts' => true,
      'wplc_enable_visitor_sound' => true,
      'wplc_enable_voice_notes_on_admin' => false,
      'wplc_enable_voice_notes_on_visitor' => false,
      'wplc_enabled_on_mobile' => true,
      'wplc_encryption_key' => '',
      'wplc_environment' => 2,
      'wplc_et_email_body' => wplc_transcript_return_default_email_body(),
      'wplc_et_email_footer' => "<span style='font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal;'>" . __( 'Thank you for chatting with us.') . "</span>",
      'wplc_et_email_header' => '<a title="' . get_bloginfo( 'name' ) . '" href="' . get_bloginfo( 'url' ) . '" style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: bold; text-decoration: underline;">' . get_bloginfo( 'name' ) . '</a>',
      'wplc_exclude_from_pages' => '',
      'wplc_exclude_home' => false,
      'wplc_exclude_archive' => false,
      'wplc_exclude_post_types' => '',
      'wplc_gdpr_custom' => false,
      'wplc_gdpr_enabled' => true,
      'wplc_gdpr_notice_company' => get_bloginfo('name'),
      'wplc_gdpr_notice_retention_period' => 30,
      'wplc_gdpr_notice_retention_purpose' => __('Chat/Support', 'wp-live-chat-support'),
      'wplc_gdpr_notice_text' => '',
      'wplc_hide_when_offline' => false,
      'wplc_include_on_pages' => '',
      'wplc_iterations' => 55,
      'wplc_loggedin_user_info' => true,
      'wplc_messagetone' => '',
      'wplc_new_chat_ringer_count' => 4,
      'wplc_newtheme' => 'theme-2',
      'wplc_node_enable_typing_preview' => false,
      'wplc_powered_by_link' => '',
      'wplc_pro_auto_first_response_chat_msg' => '',
      'wplc_pro_chat_email_address' => get_option('admin_email'),
      'wplc_pro_chat_email_offline_subject' => '',
      'wplc_pro_chat_notification' => false,
      'wplc_pro_cta_anim' => false,
      'wplc_pro_fst1' => __("Questions?", 'wp-live-chat-support'),
      'wplc_pro_fst2' => __("Chat with us", 'wp-live-chat-support'),
      'wplc_pro_fst3' => __("Start live chat", 'wp-live-chat-support'),
      'wplc_pro_intro' => __("Complete the fields below to proceed.", 'wp-live-chat-support'),
      'wplc_pro_na' => __("Leave a message", 'wp-live-chat-support'),
      'wplc_pro_offline1' => __("Please leave a message and we'll get back to you as soon as possible.", 'wp-live-chat-support'),
      'wplc_pro_offline2' => __("Sending message...", 'wp-live-chat-support'),
      'wplc_pro_offline3' => __("Thank you for your message. We will be in contact soon.", 'wp-live-chat-support'),
      'wplc_pro_offline_btn' => __("Leave a message", 'wp-live-chat-support'),
      'wplc_pro_offline_btn_send' => __("Send message", 'wp-live-chat-support'),
      'wplc_pro_sst1' => __("Start Chat", 'wp-live-chat-support'),
      'wplc_pro_sst2' => __("Connecting...", 'wp-live-chat-support'),
      'wplc_pro_tst1' => __("Reactivating your previous chat...", 'wp-live-chat-support'),
      'wplc_quick_response_order' => 'DESC',
      'wplc_quick_response_orderby' => 'title',
      'wplc_record_ip_address' => false,
      'wplc_redirect_thank_you_url' => '',
      'wplc_redirect_to_thank_you_page' => false,
      'wplc_require_user_info' => 'both',
      'wplc_ringtone' => '',
      'wplc_send_transcripts_to' => 'user',
      'wplc_send_transcripts_when_chat_ends' => false,
      'wplc_settings_align' => 2,
      'wplc_settings_bg' => 'cloudy.jpg',
      'wplc_settings_color1' => '0596d4',
      'wplc_settings_color2' => 'FFFFFF',
      'wplc_settings_color3' => 'EEEEEE',
      'wplc_settings_color4' => '373737',
      'wplc_settings_enabled' => 1,
      'wplc_settings_fill' => '0596d4',
      'wplc_settings_font' => 'FFFFFF',
      'wplc_show_avatar' => true,
      'wplc_show_date' => true,
      'wplc_show_name' => true,
      'wplc_show_time' => true,
      'wplc_social_fb' => '',
      'wplc_social_tw' => '',
      'wplc_text_chat_ended' => __("The chat has been ended by the agent.", 'wp-live-chat-support'),
      'wplc_theme' => 'theme-default',
      'wplc_typing_enabled' => true,
      'wplc_use_geolocalization' => false,
      'wplc_use_node_server' => true,
      'wplc_use_wp_name' => true,
      'wplc_user_alternative_text' => __("Please click 'Start Chat' to initiate a chat with an agent", 'wp-live-chat-support'),
      'wplc_user_default_visitor_name' => __( "Guest", 'wp-live-chat-support'),
      'wplc_user_enter' => __("Press ENTER to send your message", 'wp-live-chat-support'),
      'wplc_user_no_answer' => __("No answer. Try again later.", 'wp-live-chat-support'),
      'wplc_user_welcome_chat' => __("Welcome. How may I help you?", 'wp-live-chat-support'),
      'wplc_using_localization_plugin' => false,
      'wplc_ux_exp_rating' => true,
      'wplc_ux_file_share' => true,
      'wplc_welcome_msg' => __("Please standby for an agent. Send your message while you wait.",'wp-live-chat-support')
    );
  }
}

wplc_set_defaults();

function wplc_get_options($sanitize=false) {
  global $wplc_default_settings_array;
  $current = get_option('WPLC_SETTINGS');
  $res = array();
  if (empty($current) || !is_array($current)) {
    $current = array();
    $sanitize = true;
  }
  if ($sanitize) {
    wplc_set_defaults();
    foreach($wplc_default_settings_array as $k=>$v) {
      if (!isset($current[$k])) {
        $res[$k]=$v;
      } else {
        $res[$k]=$current[$k];
        if (is_bool($v)) {
          $res[$k]=boolval($current[$k]);
        } elseif (is_int($v)) {
          $res[$k]=intval($current[$k]);
        }
      }
    }
    
    // format changes between versions / migrations

    if (isset($current['wplc_require_user_info'])) {
      if ($current['wplc_require_user_info']=='1') {
        $res['wplc_require_user_info']='both';
      }
      if ($current['wplc_require_user_info']=='0') {
        $res['wplc_require_user_info']='none';
      }    
    }

    if (isset($current['wplc_bh_interval'])) {
      switch($current['wplc_bh_interval']) {
        case 0:
          $res['wplc_bh_days']='1111111';
          break;
        case 1:
          $res['wplc_bh_days']='0111110';
        break;
        case 2:
          $res['wplc_bh_days']='1000001';
          break;
      }
    }

    // business hours new params
    if (isset($current['wplc_bh_hours_start'])) {
      if (!is_array($current['wplc_bh_hours_start']) && !empty($current['wplc_bh_hours_start'])) {
        $res['wplc_bh_hours_start']=explode(' ',trim(str_repeat($current['wplc_bh_hours_start'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_minutes_start'])) {
      if (!is_array($current['wplc_bh_minutes_start']) && !empty($current['wplc_bh_minutes_start'])) {
        $res['wplc_bh_minutes_start']=explode(' ',trim(str_repeat($current['wplc_bh_minutes_start'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_hours_end'])) {
      if (!is_array($current['wplc_bh_hours_end']) && !empty($current['wplc_bh_hours_end'])) {
        $res['wplc_bh_hours_end']=explode(' ',trim(str_repeat($current['wplc_bh_hours_end'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_minutes_end'])) {
      if (!is_array($current['wplc_bh_minutes_end']) && !empty($current['wplc_bh_minutes_end'])) {
        $res['wplc_bh_minutes_end']=explode(' ',trim(str_repeat($current['wplc_bh_minutes_end'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_hours_start2'])) {
      if (!is_array($current['wplc_bh_hours_start2']) && !empty($current['wplc_bh_hours_start2'])) {
        $res['wplc_bh_hours_start2']=explode(' ',trim(str_repeat($current['wplc_bh_hours_start2'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_minutes_start2'])) {
      if (!is_array($current['wplc_bh_minutes_start2']) && !empty($current['wplc_bh_minutes_start2'])) {
        $res['wplc_bh_minutes_start2']=explode(' ',trim(str_repeat($current['wplc_bh_minutes_start2'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_hours_end2'])) {
      if (!is_array($current['wplc_bh_hours_end2']) && !empty($current['wplc_bh_hours_end2'])) {
        $res['wplc_bh_hours_end2']=explode(' ',trim(str_repeat($current['wplc_bh_hours_end2'].' ',7)));
      }
    }
    if (isset($current['wplc_bh_minutes_end2'])) {
      if (!is_array($current['wplc_bh_minutes_end2']) && !empty($current['wplc_bh_minutes_end2'])) {
        $res['wplc_bh_minutes_end2']=explode(' ',trim(str_repeat($current['wplc_bh_minutes_end2'].' ',7)));
      }
    }
  } else {
    $res = $current;
  }
  // override
  $res['wplc_newtheme']='theme-2';
  return $res;
}

// Load Config
require_once (plugin_dir_path(__FILE__) . "config.php");
require_once (plugin_dir_path(__FILE__) . "functions.php");
// User
require_once (plugin_dir_path(__FILE__) . "ajax/user.php");
// Agent
require_once (plugin_dir_path(__FILE__) . "ajax/agent.php");

// Check if PRO plugin is active
function wplc_is_pro_active( $plugin ) {

	// Check if it's a WP network
	if ( ! is_multisite() ) {
		$active_plugins = get_option( 'active_plugins', array() );		
		return in_array( $plugin, (array) $active_plugins );
	}

	$plugins = get_site_option( 'active_sitewide_plugins' );
	if ( isset( $plugins[ $plugin ] ) ) {
		return true;
	}else {
		return false;
	}
	
}

// Check if PRO plugin is present
function wplc_is_pro_present() {

	if ( is_readable( WP_PLUGIN_DIR . "/wp-live-chat-support-pro/wp-live-chat-support-pro.php" ) ) {
		return true;
	} else {
		return false;
	}
}

		
/*
 * Load Includes
 */
	
require_once (plugin_dir_path(__FILE__) . "includes/notification_control.php");
require_once (plugin_dir_path(__FILE__) . "includes/modal_control.php");
require_once (plugin_dir_path(__FILE__) . "includes/wplc_data_triggers.php");
require_once (plugin_dir_path(__FILE__) . "includes/wplc_roi.php");
require_once (plugin_dir_path(__FILE__) . "includes/wplc_departments.php");
require_once (plugin_dir_path(__FILE__) . "includes/wplc_transfer_chats.php");
require_once (plugin_dir_path(__FILE__) . "includes/wplc_custom_fields.php");
require_once (plugin_dir_path(__FILE__) . "includes/wplc_agent_data.php");



/*
 * Load Modules
 */
require_once (plugin_dir_path(__FILE__) . "modules/google_analytics.php");
require_once (plugin_dir_path(__FILE__) . "modules/api/public/wplc-api.php");
require_once (plugin_dir_path(__FILE__) . "modules/advanced_features.php");
require_once (plugin_dir_path(__FILE__) . "modules/node_server.php");
require_once (plugin_dir_path(__FILE__) . "modules/webhooks_manager.php");
require_once (plugin_dir_path(__FILE__) . "modules/privacy.php");
require_once (plugin_dir_path(__FILE__) . "modules/cta_animations.php"); 
require_once (plugin_dir_path(__FILE__) . "modules/advanced_tools.php"); 




/*
 * Added back for backwards compat decrypt
*/
if (class_exists("AES")) { } else { require( 'includes/aes_fast.php'); }
if (class_exists("cryptoHelpers")) { } else { require( 'includes/cryptoHelpers.php'); }

// Gutenberg Blocks
require_once (plugin_dir_path(__FILE__) . "includes/blocks/wplc-chat-box/index.php");
require_once (plugin_dir_path(__FILE__) . "includes/blocks/wplc-inline-chat-box/index.php");

// Shortcodes
require_once (plugin_dir_path(__FILE__) . "includes/shortcodes.php");

add_action("wplc_set_session_chat_id", 'wplc_set_u_session' );
add_action("wplc_end_session_chat_id", 'wplc_clean_session' );
add_action('admin_init', 'wplc_detect_old_pro_plugin');
add_action("wp_login",'wplc_check_guid');
add_action('init', 'wplc_version_control');
add_action('init', 'wplc_init');
add_action('init', 'wplc_mrg_create_macro_post_type',100); 
add_action('admin_init', 'wplc_head');
add_action('wp_ajax_wplc_admin_set_transient', 'wplc_action_callback');
add_action('wp_ajax_wplc_admin_remove_transient', 'wplc_action_callback');
add_action('wp_ajax_wplc_hide_ftt', 'wplc_action_callback');
add_action('wp_ajax_nopriv_wplc_user_send_offline_message', 'wplc_action_callback');
add_action('wp_ajax_wplc_user_send_offline_message', 'wplc_action_callback');
add_action('wp_ajax_delete_offline_message', 'wplc_action_callback');
add_action('wp_ajax_wplc_a2a_dismiss', 'wplc_action_callback');
add_action('activated_plugin', 'wplc_redirect_on_activate');
add_action('wp_ajax_wplc_choose_accepting','wplc_action_callback');
add_action('wp_ajax_wplc_choose_not_accepting','wplc_action_callback'); 
add_action('wp_ajax_wplc_agent_list','wplc_action_callback'); 
add_action('wplc_hook_action_callback','wplc_choose_hook_control_action_callback'); 
add_action('wplc_hook_admin_menu_layout_display_top','wplc_ma_hook_control_admin_meny_layout_display_top'); 
add_action('edit_user_profile_update', 'wplc_maa_set_user_as_agent'); 
add_action('personal_options_update', 'wplc_maa_set_user_as_agent'); 
add_action('edit_user_profile', 'wplc_maa_custom_user_profile_fields');
add_action('show_user_profile', 'wplc_maa_custom_user_profile_fields');
add_action('admin_enqueue_scripts', 'wplc_control_admin_javascript');
add_action('wp_ajax_wplc_add_agent', 'wplc_action_callback'); 
add_action('wp_ajax_wplc_remove_agent', 'wplc_action_callback'); 
add_action('wp_ajax_wplc_macro', 'wplc_action_callback'); 
add_action('wp_ajax_wplc_typing', 'wplc_action_callback'); 
add_action('wp_ajax_nopriv_wplc_typing', 'wplc_action_callback');
add_action('wp_ajax_wplc_upload_file', 'wplc_action_callback'); 
add_action('wp_ajax_nopriv_wplc_upload_file', 'wplc_action_callback'); 
add_action('wp_ajax_wplc_record_chat_rating', 'wplc_action_callback'); 
add_action('wp_ajax_nopriv_wplc_record_chat_rating', 'wplc_action_callback');
add_action('wp_enqueue_scripts', 'wplc_add_user_stylesheet');
add_action('admin_enqueue_scripts', 'wplc_add_admin_stylesheet');
add_action('admin_menu', 'wplc_admin_menu', 4);
add_action("wplc_hook_admin_chatbox_javascript","wplc_features_admin_js");
add_action('admin_head', 'wplc_superadmin_javascript');
add_action( 'admin_notices', 'wplc_deprecated_standalone_pro' );
add_action( 'wp_ajax_wplc_generate_new_node_token', 'wplc_ajax_generate_new_tokens' );
add_action( 'wp_ajax_wplc_new_secret_key', 'wplc_ajax_generate_new_tokens' );
add_action( 'wp_ajax_wplc_generate_new_encryption_key', 'wplc_ajax_generate_new_tokens' );

// Activation Hook
register_activation_hook(__FILE__, 'wplc_activate');

// Uninstall Hook
register_uninstall_hook(__FILE__, 'wplc_uninstall');

function wplc_uninstall() {
	global $wpdb;
	global $wplc_tblname_offline_msgs;
	global $wplc_tblname_chats;
	global $wplc_tblname_msgs;
	global $wplc_tblname_chat_ratings;
	global $wplc_tblname_chat_triggers;
	
	$wplc_settings = wplc_get_options();
	
	if ($wplc_settings['wplc_delete_db_on_uninstall']) {

		$options = array('WPLC_ACBC_SETTINGS','wplc_advanced_settings','wplc_api_key_valid','wplc_api_secret_token','WPLC_AUTO_RESPONDER_SETTINGS','WPLC_BANNED_IP_ADDRESSES','wplc_bh_settings','WPLC_CHOOSE_ACCEPTING','WPLC_CHOOSE_FIRST_RUN','WPLC_CHOOSE_SETTINGS','wplc_current_version','WPLC_CUSTOM_CSS','WPLC_CUSTOM_JS','wplc_db_version','wplc_dismiss_notice_bn','WPLC_DOC_SUGG_SETTINGS','WPLC_ENCRYPT_DEPREC_NOTICE_DISMISSED','WPLC_ENCRYPT_FIRST_RUN','WPLC_ENCRYPT_SETTINGS','wplc_end_point_override','WPLC_ET_FIRST_RUN','WPLC_ET_SETTINGS','WPLC_FIRST_TIME_TUTORIAL','WPLC_GA_SETTINGS','WPLC_GDPR_DISABLED_WARNING_DISMISSED','WPLC_GDPR_ENABLED_AT_LEAST_ONCE','WPLC_GUID','WPLC_GUID_CHECK','WPLC_GUID_URL','wplc_gutenberg_settings','WPLC_HIDE_CHAT','WPLC_IC_FIRST_RUN','WPLC_IC_SETTINGS','WPLC_INEX_FIRST_RUN','WPLC_INEX_SETTINGS','WPLC_ma_FIRST_RUN','WPLC_ma_SETTINGS','wplc_mail_host','wplc_mail_password','wplc_mail_port','wplc_mail_type','wplc_mail_username','WPLC_MOBILE_FIRST_RUN','WPLC_MOBILE_SETTINGS','wplc_node_server_secret_token','wplc_node_v8_plus_notice_dismissed','WPLC_POWERED_BY','wplc_previous_is_typing','wplc_pro_current_version','WPLC_PRO_SETTINGS','WPLC_SETTINGS','wplc_stats','WPLC_V8_FIRST_TIME');

		foreach($options as $option){
			delete_option($option);
		}
		
		unregister_post_type('wplc_quick_response');
		
		$wpdb->query("DROP TABLE IF EXISTS {$wplc_tblname_offline_msgs}");
		$wpdb->query("DROP TABLE IF EXISTS {$wplc_tblname_chats }");
		$wpdb->query("DROP TABLE IF EXISTS {$wplc_tblname_msgs }");
		$wpdb->query("DROP TABLE IF EXISTS {$wplc_tblname_chat_ratings }");
		$wpdb->query("DROP TABLE IF EXISTS {$wplc_tblname_chat_triggers }");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wplc_webhooks");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wplc_roi_goals");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wplc_roi_conversions");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wplc_devices");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wplc_departments");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wplc_custom_fields");
		
		$admins = get_role('administrator');
		if( $admins !== null ) {
			$admins->remove_cap('edit_wplc_quick_response');
			$admins->remove_cap('edit_wplc_quick_response');
			$admins->remove_cap('edit_other_wplc_quick_response');
			$admins->remove_cap('publish_wplc_quick_response');
			$admins->remove_cap('read_wplc_quick_response');
			$admins->remove_cap('read_private_wplc_quick_response');
			$admins->remove_cap('delete_wplc_quick_response');
			$admins->remove_cap('wplc_ma_agent');
		}
		
		$users = wplc_get_agent_users();
    foreach($users as $user) {
      delete_user_meta( $user->ID, 'wplc_user_department' );
      delete_user_meta( $user->ID, 'wplc_ma_agent' );
      delete_user_meta( $user->ID, 'wplc_user_bio' );
      delete_user_meta( $user->ID, 'wplc_user_facebook' );
      delete_user_meta( $user->ID, 'wplc_user_linkedin' );
      delete_user_meta( $user->ID, 'wplc_user_tagline' );
      delete_user_meta( $user->ID, 'wplc_user_twitter' );
    }
	}
}

// Plugin initialisation
function wplc_init() {
	// Load Languages
    $plugin_dir = basename(dirname(__FILE__)) . "/languages/";
    load_plugin_textdomain('wp-live-chat-support', false, $plugin_dir);
	// Load Agent API
	require_once (plugin_dir_path(__FILE__) . "modules/api/agent/wplc-api.php");
}

// Show notice if PRO plugin is present in versions < 8.0.27
function wplc_deprecated_standalone_pro() {
	if (wplc_is_pro_present()) {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'The additional WP Live Chat Support PRO plugin which you have installed is no longer needed, please uninstall it.', 'wp-live-chat-support'); ?></p>
    </div>
    <?php
	}
}

// If an old PRO plugin is detected disable it as it is not needed anymore.
function wplc_detect_old_pro_plugin() {
    if (wplc_is_pro_active("wp-live-chat-support-pro/wp-live-chat-support-pro.php")) {
        deactivate_plugins("wp-live-chat-support-pro/wp-live-chat-support-pro.php");
    }
}

/**
 * Redirect the user to the welcome page on plugin activate
 * @param  string $plugin
 * @return void
 */
function wplc_redirect_on_activate( $plugin ) {
  if( $plugin == plugin_basename( __FILE__ ) ) {
    if (get_option("WPLC_V8_FIRST_TIME") == true) {
      update_option("WPLC_V8_FIRST_TIME",false);
      // clean the output header and redirect the user
      @ob_flush();
      @ob_end_flush();
      @ob_end_clean();
      wp_redirect(admin_url('admin.php?page=wplivechat-menu&action=wplivechat-menu-dashboard'));
      die;
    }
  }
}
add_action( 'admin_init', 'wplc_redirect_on_activate' );


function wplc_version_control() {
  $current_version = get_option("wplc_current_version");
  if (!isset($current_version) || $current_version != WPLC_PLUGIN_VERSION) {
    $wplc_settings = wplc_get_options(true); // force sanitize on version change
    /**
    * Are we updating from a version before version 8?
    * If yes, set NODE to enabled
    */
    if ( isset( $current_version ) ) {
      $main_ver = intval( $current_version[0] );
      if ( $main_ver < 8 ) {
        if ( $wplc_settings ) {
          $wplc_settings['wplc_use_node_server'] = true;
          update_option( "WPLC_V8_FIRST_TIME", true );
        }
      }
    }

    /** 
    * Added for security cleanup prior to version 8.0.31
    */
    if( isset( $current_version )){
      if(intval(str_replace('.', '', $current_version)) < 8031 || intval(str_replace('.', '', $current_version)) < 8032){
        // Remove all custom JS if previous version was less than 8.0.31 or 8.0.32
        update_option( "WPLC_CUSTOM_JS", '');
        update_option( "WPLC_CUSTOM_CSS", '');
      }
    }

    $wplc_settings = wplc_cleanup_old_options($wplc_settings);

    // adjust wp caps
    $admins = get_role('administrator');
    if ($admins !== null) {
      $admins->add_cap('wplc_ma_agent');
    }

    $uid = get_current_user_id();
    update_user_meta($uid, 'wplc_ma_agent', 1);
    wplc_update_agent_time($uid);

    /* add caps to admin */
    if (current_user_can('manage_options')) {
      global $user_ID;
      $user = new WP_User($user_ID);
      foreach ($user->roles as $urole) {
        if ($urole == "administrator") {
          $admins = get_role('administrator');
          $admins->add_cap('edit_wplc_quick_response');
          $admins->add_cap('edit_wplc_quick_response');
          $admins->add_cap('edit_other_wplc_quick_response');
          $admins->add_cap('publish_wplc_quick_response');
          $admins->add_cap('read_wplc_quick_response');
          $admins->add_cap('read_private_wplc_quick_response');
          $admins->add_cap('delete_wplc_quick_response');
        }
      }
    }

    wplc_handle_db();
    update_option("wplc_current_version", WPLC_PLUGIN_VERSION);

    $wplc_settings = apply_filters('wplc_update_settings_between_versions_hook', $wplc_settings); //Added in 8.0.09
    ksort($wplc_settings);
    update_option("WPLC_SETTINGS", $wplc_settings);
    do_action("wplc_update_hook");
  }
}

function wplc_parameter_bool($settings, $name) {
    $param=0;
    if (!empty($settings) && isset($settings[$name])){
        $param=intval($settings[$name]);
        if ($param!=0){
        $param=1;
        }
    }
    return $param;
}

function wplc_check_guid() {
  $guid=get_option('WPLC_GUID');
  $guid_fqdn=get_option('WPLC_GUID_URL');
  $guid_lastcheck=intval(get_option('WPLC_GUID_CHECK'));
  if (empty($guid_lastcheck) || time()-$guid_lastcheck>86400) { // check at least once per day to ensure guid is updated properly
    $guid='';
  }
  if (empty($guid) || $guid_fqdn!=get_option('siteurl')) { // guid not assigned or fqdn is changed since last assignment
    $wplc_settings = wplc_get_options();
    $server = wplc_parameter_bool($wplc_settings, 'wplc_use_node_server');
    $gdpr = wplc_parameter_bool($wplc_settings, 'wplc_gdpr_enabled');
    $data_array = array(
      'method' => 'POST',
      'body' => array(
        'method' => 'get_guid',
        'url' => get_option('siteurl'),
        'server' => $server,
        'gdpr' => $gdpr,
        'version' => WPLC_PLUGIN_VERSION
      )
    );
    $response = wp_remote_post(WPLC_ACTIVATION_SERVER.'/api/v1', $data_array);
    if (is_array($response)) {
      if ( $response['response']['code'] == "200" ) {
        $data = json_decode($response['body'],true);
        if ($data && isset($data['guid'])){
          update_option('WPLC_GUID', sanitize_text_field($data["guid"]));
          update_option('WPLC_GUID_URL', get_option('siteurl'));
          update_option('WPLC_GUID_CHECK', time());
        }
      }
    }       
  }
}

function wplc_action_callback() {
  global $wpdb;
  $check = check_ajax_referer('wplc', 'security');

  if ($check == 1) {
    if ($_POST['action'] == 'wplc_a2a_dismiss') {
      $uid = get_current_user_id();
      update_user_meta($uid, 'wplc_a2a_upsell', 1);
    } else if ($_POST['action'] == 'delete_offline_message') {
      global $wplc_tblname_offline_msgs;
      $mid = intval( $_POST['mid'] );
      $query = $wpdb->Query($wpdb->prepare("DELETE FROM `$wplc_tblname_offline_msgs` WHERE `id` = %d", $mid));
      if( $query ){
        echo 1;
      }
    } else if ($_POST['action'] == "wplc_user_send_offline_message") {
      $cid = wplc_return_chat_id_by_rel_or_id($_POST['cid']);
      $name=sanitize_text_field($_POST['name']);
      $email=sanitize_text_field($_POST['email']);
      $msg=sanitize_text_field($_POST['msg']);
      if (function_exists('wplc_send_offline_msg')){ wplc_send_offline_msg($name, $email, $msg, $cid); }
      if (function_exists('wplc_store_offline_message')){ wplc_store_offline_message($name, $email, $msg); }
      do_action("wplc_hook_offline_message",array(
        "cid"=>$cid,
        "name"=>$name,
        "email"=>$email,
        "url"=>get_site_url(),
        "msg"=>$msg
      ));
    } else if ($_POST['action'] == "wplc_admin_set_transient") {
      do_action("wplc_hook_set_transient");
      echo wplc_return_online_agents_array();
    } else if ($_POST['action'] == "wplc_admin_remove_transient") {
      do_action("wplc_hook_remove_transient");
      echo wplc_return_online_agents_array();
    } else if ($_POST['action'] == 'wplc_hide_ftt') {
      update_option("WPLC_FIRST_TIME_TUTORIAL",true);
    }

    do_action("wplc_hook_action_callback");
  }
  die(); // this is required to return a proper result
}

/**
 * Decide who gets to see the various main menus (left navigation)
 * @return array
 * @since  6.0.00
 */
add_filter("wplc_ma_filter_menu_control","wplc_filter_control_menu_control",10,1);
function wplc_filter_control_menu_control() {
	    if (current_user_can('wplc_ma_agent')) {
	        $array = array(
	          0 => 'wplc_ma_agent', /* main menu */
	          1 => 'manage_options', /* settings */
	          2 => 'wplc_ma_agent', /* history */
	          3 => 'wplc_ma_agent', /* missed chats */
	          4 => 'wplc_ma_agent', /* offline messages */
	          5 => 'manage_options', /* feedback */
	          );
	    } else if (current_user_can('manage_options')) {
	        $array = array(
	          0 => 'manage_options', /* main menu */
	          1 => 'manage_options', /* settings */
	          2 => 'manage_options', /* history */
	          3 => 'manage_options', /* missed chats */
	          4 => 'manage_options', /* offline messages */
	          5 => 'manage_options', /* feedback */
	          );
	    } else {
	    	$array = array(
	          0 => 'manage_options', /* main menu */
	          1 => 'manage_options', /* settings */
	          2 => 'manage_options', /* history */
	          3 => 'manage_options', /* missed chats */
	          4 => 'manage_options', /* offline messages */
	          5 => 'manage_options', /* feedback */
	          );
	    }
    return $array;
}

add_action('admin_init', 'wplc_metric_dashboard_redirect');
function wplc_metric_dashboard_redirect(){
    try{
        $cap = apply_filters("wplc_ma_filter_menu_control",array());
        if(current_user_can($cap[1])){
            if (isset($_GET['page'])) {
                if ($_GET['page'] === 'wplivechat-menu') {
                    // check if we are overriding this redirect because the user pressed the "Chat now" button in the dashboard
                    if (isset($_GET['subaction']) && $_GET['subaction'] == 'override') { } else {
                        if(!isset($_COOKIE['wplcfirstsession'])) {
                            @setcookie("wplcfirstsession", true, time() + (60 * 60 * 24)); // 60s * 60m * 24h (Life span of cookie)
                            @Header("Location: ./admin.php?page=wplivechat-menu-dashboard");
                            exit();
                        }
                    }

                }
            }
        }
    } catch (Exception $ex){

    }
}

function wplc_admin_menu() {
  $cap = apply_filters("wplc_ma_filter_menu_control",array());
  /* If user is either an agent or an admin, access the page. */
  if(wplc_user_is_agent() || current_user_can("wplc_ma_agent")) {
    add_menu_page('WP Live Chat', __('Live Chat', 'wp-live-chat-support'), $cap[0], 'wplivechat-menu', 'wplc_admin_menu_layout', 'dashicons-format-chat');
    add_submenu_page('wplivechat-menu', __('Dashboard', 'wp-live-chat-support'), __('Dashboard', 'wp-live-chat-support'), $cap[1], 'wplivechat-menu-dashboard', 'wplc_admin_dashboard_layout');
    add_submenu_page('wplivechat-menu', __('Settings', 'wp-live-chat-support'), __('Settings', 'wp-live-chat-support'), $cap[1], 'wplivechat-menu-settings', 'wplc_admin_settings_layout');
  }

  /* only if user is both an agent and an admin that has the cap assigned, can they access these pages */
  if(wplc_user_is_agent() && current_user_can("wplc_ma_agent")) {
    add_submenu_page('wplivechat-menu', __('History', 'wp-live-chat-support'), __('History', 'wp-live-chat-support'), $cap[2], 'wplivechat-menu-history', 'wplc_admin_history_layout');
    add_submenu_page('wplivechat-menu', __('Missed Chats', 'wp-live-chat-support'), __('Missed Chats', 'wp-live-chat-support'), $cap[3], 'wplivechat-menu-missed-chats', 'wplc_admin_missed_chats');
    add_submenu_page('wplivechat-menu', __('Offline Messages', 'wp-live-chat-support'), __('Offline Messages', 'wp-live-chat-support'), $cap[4], 'wplivechat-menu-offline-messages', 'wplc_admin_offline_messages');
    do_action("wplc_hook_menu_mid",$cap);
    add_submenu_page('wplivechat-menu', __('Support', 'wp-live-chat-support'), __('Support', 'wp-live-chat-support'), 'manage_options', 'wplivechat-menu-support-page', 'wplc_support_menu');
  }
  do_action("wplc_hook_menu");
}


add_action("wplc_hook_menu","wplc_hook_control_menu");
function wplc_hook_control_menu() {
  $check = apply_filters("wplc_filter_menu_api",0);
}

/**
 * Allow agent to access the menu
 * @return void
 * @since  6.0.00
 */
add_action("wplc_hook_menu_mid","wplc_mid_hook_control_menu",10,1);
function wplc_mid_hook_control_menu($cap) {
	add_submenu_page('wplivechat-menu', __('Reports', 'wp-live-chat-support'), __('Reports', 'edit_posts'), $cap[1], 'wplivechat-menu-reporting', 'wplc_reporting_page');
	add_submenu_page('wplivechat-menu', __('Triggers', 'wp-live-chat-support'), __('Triggers', 'edit_posts'), $cap[1], 'wplivechat-menu-triggers', 'wplc_triggers_page');
	add_submenu_page('wplivechat-menu', __('Custom Fields', 'wp-live-chat-support'), __('Custom Fields', 'edit_posts'), $cap[1], 'wplivechat-menu-custom-fields', 'wplc_custom_fields_page');
}


add_action("wp_head","wplc_load_user_js",0);


function wplc_load_user_js () {
  if (!is_admin()) {
    if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
      return false;
    }

    if (function_exists('wplc_is_user_banned')) {
      $user_banned = wplc_is_user_banned();
    } else {
      $user_banned = 0;
    }
    
    $display_contents = wplc_display_chat_contents();

    if ($display_contents && $user_banned == 0) {
      if (!class_exists('Mobile_Detect')) {
        require_once (plugin_dir_path(__FILE__) . 'includes/Mobile_Detect.php');
      }
      $wplc_detect_device = new Mobile_Detect;
      $wplc_is_mobile = $wplc_detect_device->isMobile();
      wplc_push_js_to_front();
    }
  }
}

function wplc_push_js_to_front() {
  global $wplc_is_mobile;
  global $wplc_default_settings_array;

	wp_register_script('wplc-user-jquery-cookie', plugins_url('/js/jquery-cookie.js', __FILE__), array('jquery'), WPLC_PLUGIN_VERSION, true);
	wp_enqueue_script('wplc-user-jquery-cookie');

  wp_enqueue_script('jquery');

  wplc_register_common_node();

  $wplc_settings = wplc_get_options();
	$wplc_ga_enabled = get_option("WPLC_GA_SETTINGS");

  if ($wplc_settings['wplc_display_to_loggedin_only']) {
    /* Only show to users that are logged in */
    if (!is_user_logged_in()) {
      return;
    }
  }

  /* is the chat enabled? */
  if ($wplc_settings["wplc_settings_enabled"] == 2) { return; }

  wp_register_script('wplc-md5', plugins_url('/js/md5.js', __FILE__),array('wplc-user-script'),WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-md5');

  $ajax_nonce = wp_create_nonce("wplc");
  $ajaxurl = admin_url('admin-ajax.php');
  $wplc_ajaxurl = $ajaxurl;

	wp_register_script('wplc-server-script', plugins_url('/js/wplc_server.js', __FILE__), array('jquery'), WPLC_PLUGIN_VERSION, true);
 	wp_enqueue_script('wplc-server-script');

 	wp_localize_script( 'wplc-server-script', 'wplc_datetime_format', array(
 		'date_format' => get_option( 'date_format' ),
		'time_format' => get_option( 'time_format' ),
	) );

  if ($wplc_settings['wplc_use_node_server']) {
    wp_localize_script('wplc-server-script', 'tcx_api_key', wplc_node_server_token_get());
		wp_register_script('wplc-node-server-script', WPLC_PLUGIN_URL."js/vendor/sockets.io/socket.io.slim.js", array('jquery'), WPLC_PLUGIN_VERSION, true);
 		wp_enqueue_script('wplc-node-server-script');
 		wp_register_script('wplc-user-events-script', plugins_url('/js/wplc_u_node_events.js', __FILE__),array('jquery', 'wplc-server-script'),WPLC_PLUGIN_VERSION, true);
 		/* Not used in front-end
		wp_localize_script('wplc-server-script', 'tcx_override_upload_url', rest_url( 'wp_live_chat_support/v1/remote_upload' ) );
		*/

    //For node verification
    wp_localize_script('wplc-server-script', 'wplc_guid', get_option('WPLC_GUID', ''));

 		//Emoji Libs
		if (!$wplc_settings['wplc_disable_emojis']) {
			wp_register_script('wplc-user-js-emoji-concat', WPLC_PLUGIN_URL."js/vendor/wdt-emoji/wdt-emoji-concat.min.js", array("wplc-server-script", "wplc-server-script"), WPLC_PLUGIN_VERSION, false);
			wp_enqueue_script('wplc-user-js-emoji-concat');
			wp_register_style( 'wplc-admin-style-emoji', WPLC_PLUGIN_URL."js/vendor/wdt-emoji/wdt-emoji-bundle.css", array(), WPLC_PLUGIN_VERSION);
			wp_enqueue_style( 'wplc-admin-style-emoji' );
		}
    wp_register_script('wplc-user-node-node-primary', plugins_url('/js/wplc_node.js', __FILE__),array('jquery', 'wplc-server-script', 'wplc-user-script'), WPLC_PLUGIN_VERSION, true);
    wp_enqueue_script('wplc-user-node-node-primary');
  } else {
  	/* not using the node server, load traditional event handler JS */
  	wp_register_script('wplc-user-events-script', plugins_url('/js/wplc_u_events.js', __FILE__),array('jquery', 'wplc-server-script'), WPLC_PLUGIN_VERSION, true);
  }

  wp_register_script('wplc-user-script', plugins_url('/js/wplc_u.js', __FILE__),array('jquery', 'wplc-server-script'), WPLC_PLUGIN_VERSION, true);

  wp_enqueue_script('wplc-user-script');
  wp_enqueue_script('wplc-user-events-script');

  switch ($wplc_settings['wplc_newtheme']) {
    case 'theme-1':
      wp_register_script('wplc-theme-classic', plugins_url('/js/themes/classic.js', __FILE__),array('wplc-user-script'), WPLC_PLUGIN_VERSION, true);
      wp_enqueue_script('wplc-theme-classic');
      $avatars = wplc_all_avatars();
      wp_localize_script('wplc-theme-classic', 'wplc_user_avatars', $avatars);
      break;

    default:  
      wp_register_script('wplc-theme-modern', plugins_url('/js/themes/modern.js', __FILE__),array('wplc-user-script'), WPLC_PLUGIN_VERSION, true);
      wp_enqueue_script('wplc-theme-modern');
      $avatars = wplc_all_avatars();
      wp_localize_script('wplc-theme-modern', 'wplc_user_avatars', $avatars);
      break;
  }
  
  $ajax_url = admin_url('admin-ajax.php');
  $home_ajax_url = $ajax_url;

  $wplc_ajax_url = apply_filters("wplc_filter_ajax_url",$ajax_url);
  wp_localize_script('wplc-admin-chat-js', 'wplc_ajaxurl', $wplc_ajax_url);
  wp_localize_script('wplc-ma-js', 'wplc_home_ajaxurl', $home_ajax_url);

  //Added rest security nonces
  if(class_exists("WP_REST_Request")) {
    wp_localize_script('wplc-user-script', 'wplc_restapi_enabled', array('value'=>true));
    wp_localize_script('wplc-user-script', 'wplc_restapi_token', get_option('wplc_api_secret_token'));
    wp_localize_script('wplc-user-script', 'wplc_restapi_endpoint', rest_url('wp_live_chat_support/v1'));
    wp_localize_script('wplc-user-script', 'wplc_restapi_nonce', wp_create_nonce( 'wp_rest' ));
  } else {
    wp_localize_script('wplc-user-script', 'wplc_restapi_enabled', array('value'=>false));
    wp_localize_script('wplc-user-script', 'wplc_restapi_nonce', null);
  }

  if (isset($wplc_ga_enabled['wplc_enable_ga']) && $wplc_ga_enabled['wplc_enable_ga'] == '1') {
    //wp_localize_script('wplc-user-script', 'wplc_enable_ga', '1'); TODO: check and re-enable
  }

  wp_localize_script( 'wplc-user-script', 'tcx_message_override', apply_filters( 'wplc_filter_message_sound', '' ));

  $wplc_detect_device = new Mobile_Detect;
  $wplc_is_mobile = $wplc_detect_device->isMobile() ? 'true' : 'false';
  wp_localize_script('wplc-user-script', 'wplc_is_mobile', $wplc_is_mobile);

  wp_localize_script('wplc-user-script', 'wplc_ajaxurl', $wplc_ajax_url);
  wp_localize_script('wplc-user-script', 'wplc_ajaxurl_site', admin_url('admin-ajax.php'));
  wp_localize_script('wplc-user-script', 'wplc_nonce', $ajax_nonce);
  wp_localize_script('wplc-user-script', 'wplc_plugin_url', WPLC_PLUGIN_URL);

  $wplc_images = apply_filters( 'wplc_get_images_to_preload', array(), $wplc_settings );
  wp_localize_script( 'wplc-user-script', 'wplc_preload_images', $wplc_images );

   $wplc_chat_detail = array(
    'name' => $wplc_settings['wplc_show_name'],
    'avatar' => $wplc_settings['wplc_show_avatar'],
    'date' => $wplc_settings['wplc_show_date'],
    'time' => $wplc_settings['wplc_show_time']
  );   
   
   wp_localize_script( 'wplc-user-script', 'wplc_show_chat_detail', $wplc_chat_detail );

  /**
   * Create a JS object for all Agent ID's and Gravatar MD5's
   */
  $user_array = wplc_get_agent_users();
  $a_array = array();
  foreach ($user_array as $user) {
    $a_array[$user->ID] = array();
    $a_array[$user->ID]['name'] = apply_filters( "wplc_decide_agents_name", sanitize_text_field($user->display_name), $wplc_settings );
    $a_array[$user->ID]['md5'] = md5( $user->user_email );
    $a_array[$user->ID]['tagline'] = get_user_meta($user->ID, 'wplc_user_tagline', true);
  }
	wp_localize_script('wplc-user-script', 'wplc_agent_data', $a_array);

	$wplc_error_messages = array(
    'please_enter_name'     => __( "Please enter your name", 'wp-live-chat-support'),
    'please_enter_email'     => __( "Please enter your email address", 'wp-live-chat-support'),
    'please_enter_valid_email'     => __( "Please enter a valid email address", 'wp-live-chat-support'),
    'server_connection_lost' => __("Connection to Server Lost. Please Reload This Page. Error:", 'wp-live-chat-support').' ',
    'chat_ended_by_operator' => ( empty( $wplc_settings['wplc_text_chat_ended'] ) ) ? __("The chat has been ended by the agent.", 'wp-live-chat-support') : sanitize_text_field( $wplc_settings['wplc_text_chat_ended'] ) ,
    'empty_message' => __( "Please Enter a Message", 'wp-live-chat-support'),
    'disconnected_message' => __("Disconnected, Attempting to Reconnect...", 'wp-live-chat-support'),
  );

  $wplc_error_messages = apply_filters( "wplc_user_error_messages_filter", $wplc_error_messages );

  wp_localize_script('wplc-user-script', 'wplc_error_messages', $wplc_error_messages);
  wp_localize_script('wplc-user-script', 'wplc_enable_ding', array('value'=>boolval($wplc_settings['wplc_enable_msg_sound'])));
  $wplc_run_override = apply_filters("wplc_filter_run_override",$wplc_run_override=0);
  wp_localize_script('wplc-user-script', 'wplc_filter_run_override', array('value'=>boolval($wplc_run_override)));

  if (!isset($wplc_settings['wplc_pro_offline1'])) { $wplc_settings["wplc_pro_offline1"] = $wplc_default_settings_array['wplc_pro_offline1']; }
  if (!isset($wplc_settings['wplc_pro_offline2'])) { $wplc_settings["wplc_pro_offline2"] = $wplc_default_settings_array['wplc_pro_offline2']; }
  if (!isset($wplc_settings['wplc_pro_offline3'])) { $wplc_settings["wplc_pro_offline3"] = $wplc_default_settings_array['wplc_pro_offline3']; }

  wp_localize_script('wplc-user-script', 'wplc_offline_msg', __(stripslashes($wplc_settings['wplc_pro_offline2']), 'wp-live-chat-support'));
  wp_localize_script('wplc-user-script', 'wplc_offline_msg3',__(stripslashes($wplc_settings['wplc_pro_offline3']), 'wp-live-chat-support'));
  wp_localize_script('wplc-user-script', 'wplc_welcome_msg', __(stripslashes($wplc_settings['wplc_welcome_msg']), 'wp-live-chat-support'));
 	wp_localize_script('wplc-user-script', 'wplc_pro_sst1', __(stripslashes($wplc_settings['wplc_pro_sst1']), 'wp-live-chat-support') );
 	wp_localize_script('wplc-user-script', 'wplc_pro_offline_btn_send', __(stripslashes($wplc_settings['wplc_pro_offline_btn_send']), 'wp-live-chat-support') );
 	wp_localize_script('wplc-user-script', 'wplc_user_default_visitor_name', wplc_get_user_name('', $wplc_settings));

  if ($wplc_settings['wplc_use_wp_name']) {
    if (isset( $_COOKIE['wplc_cid'])) {
      $chat_data = wplc_get_chat_data( $_COOKIE['wplc_cid'] );
      if (isset($chat_data->agent_id)) {
        $user_info = get_userdata( intval( $chat_data->agent_id ) );
        if ($user_info) {
          $agent = $user_info->display_name;
        } else {
          $agent = "agent";
        }
      } else {
        $agent = 'agent';
      }
    } else {
      $agent = 'agent';
    }
  } else {
    if (!empty($wplc_settings['wplc_chat_name'])) {
      $agent = $wplc_settings['wplc_chat_name'];
    } else {
      $agent = 'agent';
    }
  }
  wp_localize_script('wplc-user-script', 'wplc_localized_string_is_typing', sanitize_text_field($agent) . ' '.__("is typing...",'wp-live-chat-support'));

  $tcx_string_array = array(
  	' '.__("has joined.",'wp-live-chat-support'),
  	' '.__("has left.",'wp-live-chat-support'),
  	' '.__("has ended the chat.", 'wp-live-chat-support'),
  	' '.__("has disconnected.", 'wp-live-chat-support'),
  	__("(edited)", 'wp-live-chat-support'),
  	__("Type here",'wp-live-chat-support')
  );

	wp_localize_script('wplc-user-script', 'tcx_localized_strings', $tcx_string_array );

  if (!empty($wplc_settings['wplc_elem_trigger_id'])) {
    $wplc_elem_trigger = array(
      'action'=>intval($wplc_settings['wplc_elem_trigger_action']),
      'type'=>intval($wplc_settings['wplc_elem_trigger_type']),
      'id'=>htmlentities($wplc_settings['wplc_elem_trigger_id'])
    );
    wp_localize_script( 'wplc-user-script', 'wplc_elem_trigger', $wplc_elem_trigger);
  }

  $extra_data_array = array("object_switch" => true);
  $extra_data_array = apply_filters("wplc_filter_front_js_extra_data",$extra_data_array);
  wp_localize_script('wplc-user-script', 'wplc_extra_data',$extra_data_array);

  if (isset($_COOKIE['wplc_email']) && $_COOKIE['wplc_email'] != "") { $wplc_user_gravatar = sanitize_text_field(md5(strtolower(trim($_COOKIE['wplc_email'])))); } else {$wplc_user_gravatar = ""; }

  if ($wplc_user_gravatar != "") { $wplc_grav_image = "<img src='//www.gravatar.com/avatar/$wplc_user_gravatar?s=30&d=mm' class='wplc-user-message-avatar' />";} else { $wplc_grav_image = "";}

	if ( ! empty( $wplc_grav_image ) ) {
		wp_localize_script('wplc-user-script', 'wplc_gravatar_image', $wplc_grav_image);
  }

  if(isset($wplc_settings['wplc_redirect_to_thank_you_page']) && isset($wplc_settings['wplc_redirect_thank_you_url']) && $wplc_settings['wplc_redirect_thank_you_url'] !== "" && $wplc_settings['wplc_redirect_thank_you_url'] !== " "){
    wp_localize_script('wplc-user-script', 'wplc_redirect_thank_you', urldecode($wplc_settings['wplc_redirect_thank_you_url']));
  }

  wp_enqueue_script('jquery-ui-core',false,array('wplc-user-script'),false,false);
  wp_enqueue_script('jquery-ui-draggable',false,array('wplc-user-script'),false,false);

  do_action("wplc_hook_push_js_to_front");
}

add_action('wp_head', 'wplc_user_top_js');


/**
 * Add to the array to determine which images need to be preloaded via JS on the front end.
 *
 * @param  array $images Array of images to be preloaded
 * @return array
 */
add_filter( "wplc_get_images_to_preload", "wplc_filter_control_get_images_to_preload", 10, 2 );
function wplc_filter_control_get_images_to_preload( $images, $wplc_settings ) {
	$icon = plugins_url('images/iconRetina.png', __FILE__);
	$close_icon = plugins_url('images/iconCloseRetina.png', __FILE__);
	array_push( $images, $icon );
	array_push( $images, $close_icon );
	return $images;
}

function wplc_user_top_js() {
  $display_contents = wplc_display_chat_contents();

  if ($display_contents) {
    $ajax_nonce = wp_create_nonce("wplc");
    $wplc_settings = wplc_get_options();
    $ajax_url = admin_url('admin-ajax.php');
    $wplc_ajax_url = apply_filters("wplc_filter_ajax_url",$ajax_url);
    $wplc_theme = wplc_get_theme();
  ?>
  <script type="text/javascript">
    var wplc_ajaxurl = '<?php echo $wplc_ajax_url; ?>';
    var wplc_nonce = '<?php echo $ajax_nonce; ?>';
  </script>
  <?php

    if ($wplc_theme == 'theme-6') {
      /* custom */
      if (isset($wplc_settings["wplc_settings_color1"])) { $wplc_settings_color1 = sanitize_text_field($wplc_settings["wplc_settings_color1"]); } else { $wplc_settings_color1 = "0596d4"; }
      if (isset($wplc_settings["wplc_settings_color2"])) { $wplc_settings_color2 = sanitize_text_field($wplc_settings["wplc_settings_color2"]); } else { $wplc_settings_color2 = "FFFFFF"; }
      if (isset($wplc_settings["wplc_settings_color3"])) { $wplc_settings_color3 = sanitize_text_field($wplc_settings["wplc_settings_color3"]); } else { $wplc_settings_color3 = "EEEEEE"; }
      if (isset($wplc_settings["wplc_settings_color4"])) { $wplc_settings_color4 = sanitize_text_field($wplc_settings["wplc_settings_color4"]); } else { $wplc_settings_color4 = "373737"; }
  ?>
  <style>
    .wplc-color-1 { color: #<?php echo $wplc_settings_color1; ?> !important; }
    .wplc-color-2 { color: #<?php echo $wplc_settings_color2; ?> !important; }
    .wplc-color-3 { color: #<?php echo $wplc_settings_color3; ?> !important; }
    .wplc-color-4 { color: #<?php echo $wplc_settings_color4; ?> !important; }
    .wplc-color-bg-1 { background-color: #<?php echo $wplc_settings_color1; ?> !important; }
    .wplc-color-bg-2 { background-color: #<?php echo $wplc_settings_color2; ?> !important; }
    .wplc-color-bg-3 { background-color: #<?php echo $wplc_settings_color3; ?> !important; }
    .wplc-color-bg-4 { background-color: #<?php echo $wplc_settings_color4; ?> !important; }
    .wplc-color-border-1 { border-color: #<?php echo $wplc_settings_color1; ?> !important; }
    .wplc-color-border-2 { border-color: #<?php echo $wplc_settings_color2; ?> !important; }
    .wplc-color-border-3 { border-color: #<?php echo $wplc_settings_color3; ?> !important; }
    .wplc-color-border-4 { border-color: #<?php echo $wplc_settings_color4; ?> !important; }
    .wplc-color-border-1:before { border-color: transparent #<?php echo $wplc_settings_color1; ?> !important; }
    .wplc-color-border-2:before { border-color: transparent #<?php echo $wplc_settings_color2; ?> !important; }
    .wplc-color-border-3:before { border-color: transparent #<?php echo $wplc_settings_color3; ?> !important; }
    .wplc-color-border-4:before { border-color: transparent #<?php echo $wplc_settings_color4; ?> !important; }
  </style>
  <?php
    }
  }
}

/**
 * Detect if the user is using blocked in the live chat settings 'blocked IP' section
 * @return void
 * @since  6.0.00
 */
function wplc_hook_control_banned_users() {
    if (function_exists('wplc_is_user_banned')){
        $user_banned = wplc_is_user_banned();
    } else {
        $user_banned = 0;
    }
    if ($user_banned) {
      remove_action("wplc_hook_output_box_body","wplc_hook_control_show_chat_box");
    }
}

/**
 * Detect if the user is using a mobile phone or not and decides to show the chat box depending on the admins settings
 * @return void
 * @since  6.0.00
 */
function wplc_hook_control_check_mobile() {
  $wplc_settings = wplc_get_options();
  $draw_box = true;

  if (!class_exists('Mobile_Detect')) {
      require_once (plugin_dir_path(__FILE__) . 'includes/Mobile_Detect.php');
  }
  $wplc_detect_device = new Mobile_Detect;
  $wplc_is_mobile = $wplc_detect_device->isMobile();

  if ($wplc_is_mobile && !$wplc_settings['wplc_enabled_on_mobile']) { // if wplc_enabled_on_mobile and user is mobile, hide
    $draw_box = false;
  }

  if ($wplc_settings['wplc_hide_when_offline'] && !wplc_agent_is_available()) { // if wplc_hide_when_offline and no agents online, hide
    $draw_box = false;
  }

  if (!$draw_box) {
    remove_action("wplc_hook_output_box_body","wplc_hook_control_show_chat_box");
  }
}

/**
 * Decides whether or not to show the chat box based on the main setting in the settings page
 * @return void
 * @since  6.0.00
 */
function wplc_hook_control_is_chat_enabled() {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings["wplc_settings_enabled"] == 2) {
      remove_action("wplc_hook_output_box_body","wplc_hook_control_show_chat_box");
  }
}

/**
 * Backwards compatibility for the control of the chat box
 * @return string
 * @since  6.0.00
 */
function wplc_hook_control_show_chat_box($cid) {
      echo wplc_output_box_ajax($cid);
}

/* basic */
add_action("wplc_hook_output_box_header","wplc_hook_control_banned_users");
add_action("wplc_hook_output_box_header","wplc_hook_control_check_mobile");
add_action("wplc_hook_output_box_header","wplc_hook_control_is_chat_enabled");

add_action("wplc_hook_output_box_body","wplc_hook_control_show_chat_box",10,1);

/**
 * Build the chat box
 * @return void
 * @since  6.0.00
 */
function wplc_output_box_5100($cid = null) {
   do_action("wplc_hook_output_box_header",$cid);
   do_action("wplc_hook_output_box_body",$cid);
   do_action("wplc_hook_output_box_footer",$cid);
}

/**
 * Filter to control the top MAIN DIV of the chat box
 * @param  array   $wplc_settings Live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_main_div_top($wplc_settings, $logged_in, $wplc_using_locale) {
    $ret_msg = "";
   $wplc_class = "";

    if ($wplc_settings["wplc_settings_align"] == 1) {
        $original_pos = "bottom_left";
    } else if ($wplc_settings["wplc_settings_align"] == 2) {
        $original_pos = "bottom_right";
    } else if ($wplc_settings["wplc_settings_align"] == 3) {
        $original_pos = "left";
        $wplc_class = "wplc_left";
    } else if ($wplc_settings["wplc_settings_align"] == 4) {
        $original_pos = "right";
        $wplc_class = "wplc_right";
    }


    $animations = wplc_return_animations();
    if ($animations) {
      isset($animations['animation']) ? $wplc_animation = $animations['animation'] : $wplc_animation = 'animation-4';
      isset($animations['starting_point']) ? $wplc_starting_point = $animations['starting_point'] : $wplc_starting_point = 'display: none;';
      isset($animations['box_align']) ? $wplc_box_align = $animations['box_align'] : $wplc_box_align = '';
    }
    else {

      if ($wplc_settings["wplc_settings_align"] == 1) {
          $original_pos = "bottom_left";
          $wplc_box_align = "left:20px; bottom:0px;";
      } else if ($wplc_settings["wplc_settings_align"] == 2) {
          $original_pos = "bottom_right";
          $wplc_box_align = "right:20px; bottom:0px;";
      } else if ($wplc_settings["wplc_settings_align"] == 3) {
          $original_pos = "left";
          $wplc_box_align = "left:0; bottom:100px;";
          $wplc_class = "wplc_left";
      } else if ($wplc_settings["wplc_settings_align"] == 4) {
          $original_pos = "right";
          $wplc_box_align = "right:0; bottom:100px;";
          $wplc_class = "wplc_right";
      }
    }

  $wplc_extra_attr = apply_filters("wplc_filter_chat_header_extra_attr","");

  switch($wplc_settings['wplc_newtheme']) {
    case 'theme-1':
      $wplc_theme_type = "classic";
      $hovercard_content = "<div class='wplc_hovercard_content_right'>".apply_filters("wplc_filter_live_chat_box_html_1st_layer",wplc_filter_control_live_chat_box_html_1st_layer($wplc_settings, $logged_in, $wplc_using_locale, "wplc-color-2"))."</div>";
      $hovercard_content = apply_filters("wplc_filter_hovercard_content", $hovercard_content);
      $ret_msg .= "<div id='wplc_hovercard' style='display:none' class='".$wplc_theme_type."'>";
      $ret_msg .= "<div id='wplc_hovercard_content'>".apply_filters("wplc_filter_live_chat_box_pre_layer1","").$hovercard_content."</div>";
      $ret_msg .= "<div id='wplc_hovercard_bottom'>".apply_filters("wplc_filter_hovercard_bottom_before","").apply_filters("wplc_filter_live_chat_box_hover_html_start_chat_button","",$wplc_settings,$logged_in,$wplc_using_locale)."</div>";
      $ret_msg .= "</div>";
      break;

    default:
      $wplc_theme_type = "modern";
      $hovercard_content = "";
      $msg_left = apply_filters("wplc_filter_modern_theme_hovercard_content_left","");
      if (!empty($msg_left)) {
        $hovercard_content.= "<div class='wplc_hovercard_content_left'>".$msg_left."</div>";
      }
      $hovercard_content.= "<div class='wplc_hovercard_content_right'>".apply_filters("wplc_filter_live_chat_box_html_1st_layer",wplc_filter_control_live_chat_box_html_1st_layer($wplc_settings, $logged_in, $wplc_using_locale, 'wplc-color-4'))."</div>";
      $hovercard_content = apply_filters("wplc_filter_hovercard_content", $hovercard_content);
      $ret_msg .= "<div id='wplc_hovercard' style='display:none' class='".$wplc_theme_type."'>";
      $ret_msg .= "<div id='wplc_hovercard_content'>".apply_filters("wplc_filter_live_chat_box_pre_layer1","").$hovercard_content."</div>";
      $ret_msg .= "<div id='wplc_hovercard_bottom'>".apply_filters("wplc_filter_hovercard_bottom_before","").apply_filters("wplc_filter_live_chat_box_hover_html_start_chat_button","",$wplc_settings,$logged_in,$wplc_using_locale)."</div>";
      $ret_msg .= "</div>";

  }

  $additional_class = 'emoji_only';
  if ($wplc_settings["wplc_ux_file_share"] && $wplc_settings["wplc_ux_exp_rating"]) {
  	$additional_class = 'file_sharing_ratings_enabled';
  } else if ($wplc_settings["wplc_ux_file_share"]) {
  	$additional_class = 'file_sharing_enabled';
  } else if ($wplc_settings["wplc_ux_exp_rating"]) {
  	$additional_class = 'rating_enabled';	
  }

  $ret_msg .= "<div id=\"wp-live-chat\" wplc_animation=\"".$wplc_animation."\" style=\"".$wplc_starting_point." ".$wplc_box_align.";\" class=\"".$wplc_theme_type." ".$wplc_class." ".$additional_class." wplc_close\" original_pos=\"".$original_pos."\" ".$wplc_extra_attr." > ";
  return $ret_msg;
}



add_filter("wplc_filter_modern_theme_hovercard_content_left","wplc_filter_control_modern_theme_hovercard_content_left",10,1);
function wplc_filter_control_modern_theme_hovercard_content_left($msg) {
  $msg = apply_filters("wplc_filter_microicon",$msg);
  return $msg;
}

/**
 * Filter to control the top HEADER DIV of the chat box
 * @param  array   $wplc_settings Live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_header_div_top($wplc_settings) {
  $ret_msg = "";
  if ($wplc_settings['wplc_newtheme'] == "theme-1") {
  	$ret_msg .= apply_filters("wplc_filter_chat_header_above","", $wplc_settings); //Ratings/Social Icon Filter
  }
  $ret_msg .= "<div id=\"wp-live-chat-header\" class='wplc-color-bg-1 wplc-color-2'>";
  $ret_msg .= apply_filters("wplc_filter_chat_header_under","",$wplc_settings);
  return $ret_msg;
}

add_filter("wplc_filter_chat_header_under","wplc_filter_control_chat_header_under",1,2);
function wplc_filter_control_chat_header_under($ret_msg,$wplc_settings) {
	if($wplc_settings['wplc_newtheme'] == "theme-2") {
    remove_filter("wplc_filter_chat_header_under","wplc_acbc_filter_control_chat_header_under");
	}
	return $ret_msg;
}

/**
 * Filter to control the user details section - custom fields coming soon
 * @param  array   $wplc_settings Live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_ask_user_detail($wplc_settings) {
  $ret_msg = "";

	if ($wplc_settings['wplc_require_user_info'] == 'both' || $wplc_settings['wplc_require_user_info'] == 'name' ) {
		$wplc_ask_user_details = 1;
	} else {
		$wplc_ask_user_details = 0;
	}

  $wplc_loggedin_user_name = "";
  $wplc_loggedin_user_email = "";

  if ($wplc_settings['wplc_loggedin_user_info'] && is_user_logged_in()) {
      global $current_user;

      if ($current_user->data != null) {
          //Logged in. Get name and email
          $wplc_loggedin_user_name = esc_attr($current_user->display_name);
          $wplc_loggedin_user_email = esc_attr($current_user->user_email);
      }
  } else {
	  if ( $wplc_ask_user_details == 0 ) {
		  $wplc_loggedin_user_name = stripslashes(wplc_get_user_name('', $wplc_settings));
	  }
  }

  switch($wplc_settings['wplc_require_user_info']) {

    case 'both':
      $ret_msg .= "<input type=\"text\" maxlength=\"40\" name=\"wplc_name\" id=\"wplc_name\" value='" . $wplc_loggedin_user_name . "' placeholder=\"" . __( "Name", 'wp-live-chat-support') . "\" />";
      $ret_msg .= "<input type=\"text\" maxlength=\"40\" name=\"wplc_email\" id=\"wplc_email\" wplc_hide=\"0\" value=\"" . $wplc_loggedin_user_email . "\" placeholder=\"" . __( "Email", 'wp-live-chat-support') . "\"  />";
      $ret_msg .= apply_filters( "wplc_start_chat_user_form_after_filter", "" );
      break;

    case 'email':
      if ($wplc_loggedin_user_name != '') {
        $wplc_lin = $wplc_loggedin_user_name; 
      } else {
        $wplc_lin = 'user' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9); 
      }
      $ret_msg .= "<input type=\"hidden\" maxlength=\"40\" name=\"wplc_name\" id=\"wplc_name\" value=\"".$wplc_lin."\" />";
      $ret_msg .= "<input type=\"text\" maxlength=\"40\" name=\"wplc_email\" id=\"wplc_email\" wplc_hide=\"0\" value=\"" . $wplc_loggedin_user_email . "\" placeholder=\"" . __( "Email", 'wp-live-chat-support') . "\"  />";
      $ret_msg .= apply_filters("wplc_start_chat_user_form_after_filter", "");
      break;

    case 'name':
      if ($wplc_loggedin_user_email != '' && $wplc_loggedin_user_email != null) {
        $wplc_lie = $wplc_loggedin_user_email; 
      } else {
        $wplc_random_user_number = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $wplc_lie = $wplc_random_user_number . '@' . $wplc_random_user_number . '.com';
      }
      $ret_msg .= "<input type=\"text\" maxlength=\"40\" name=\"wplc_name\" id=\"wplc_name\" value='" . $wplc_loggedin_user_name . "' placeholder=\"" . __( "Name", 'wp-live-chat-support') . "\" />";
      $ret_msg .= "<input type=\"hidden\" maxlength=\"40\" name=\"wplc_email\" id=\"wplc_email\" wplc_hide=\"1\" value=\"".$wplc_lie."\" />";
      $ret_msg .= apply_filters("wplc_start_chat_user_form_after_filter", "");
      break;

    case 'none':
      $ret_msg .= "<div style=\"padding: 7px; text-align: center; font-size:12pt;\">";
      if (isset($wplc_settings['wplc_user_alternative_text'])) {
          $ret_msg .= html_entity_decode( stripslashes($wplc_settings['wplc_user_alternative_text']) );
      }
      $ret_msg .= '</div>';
      $wplc_random_user_number = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
      if ($wplc_loggedin_user_name != '') { $wplc_lin = $wplc_loggedin_user_name; } else {  $wplc_lin = 'user' . $wplc_random_user_number; }
      if ($wplc_loggedin_user_email != '' && $wplc_loggedin_user_email != null) { $wplc_lie = $wplc_loggedin_user_email; } else { $wplc_lie = $wplc_random_user_number . '@' . $wplc_random_user_number . '.com'; }
      $ret_msg .= "<input type=\"hidden\" maxlength=\"40\" name=\"wplc_name\" id=\"wplc_name\" value=\"".$wplc_lin."\" />";
      $ret_msg .= "<input type=\"hidden\" maxlength=\"40\" name=\"wplc_email\" id=\"wplc_email\" wplc_hide=\"1\" value=\"".$wplc_lie."\" />";
      $ret_msg .= apply_filters("wplc_start_chat_user_form_after_filter", "");
      break;
  }

  return $ret_msg;
}


/**
 * Filter to control the start chat button
 * @param  array   $wplc_settings Live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_start_chat_button($wplc_settings,$wplc_using_locale ) {
    $wplc_sst_1 = __('Start chat', 'wp-live-chat-support');
    if (!isset($wplc_settings['wplc_pro_sst1']) || $wplc_settings['wplc_pro_sst1'] == "") { $wplc_settings['wplc_pro_sst1'] = $wplc_sst_1; }
    $text = ($wplc_using_locale ? $wplc_sst_1 : stripslashes($wplc_settings['wplc_pro_sst1']));
    $custom_attr = apply_filters('wplc_start_button_custom_attributes_filter', "", $wplc_settings);
  	return "<button id=\"wplc_start_chat_btn\" type=\"button\" class='wplc-color-bg-1 wplc-color-2' $custom_attr>$text</button>";
}




/**
 * Filter to control the hover card start chat button
 * @param  array   $wplc_settings Live chat settings array
 * @return string
 * @since  6.1.00
 */
add_filter("wplc_filter_live_chat_box_hover_html_start_chat_button","wplc_filter_control_live_chat_box_html_hovercard_chat_button",10,4);
function wplc_filter_control_live_chat_box_html_hovercard_chat_button($content,$wplc_settings,$logged_in,$wplc_using_locale ) {
    if ($logged_in) {
      $wplc_sst_1 = __('Start chat', 'wp-live-chat-support');

      if (!isset($wplc_settings['wplc_pro_sst1']) || $wplc_settings['wplc_pro_sst1'] == "") { $wplc_settings['wplc_pro_sst1'] = $wplc_sst_1; }
      $text = ($wplc_using_locale ? $wplc_sst_1 : stripslashes($wplc_settings['wplc_pro_sst1']));
      return "<button id=\"speeching_button\" type=\"button\"  class='wplc-color-bg-1 wplc-color-2'>$text</button>";
    } else {
      $wplc_sst_1 = stripslashes($wplc_settings['wplc_pro_offline_btn']);
      return "<button id=\"speeching_button\" type=\"button\"  class='wplc-color-bg-1 wplc-color-2'>$wplc_sst_1</button>";

    }
}

/**
 * Filter to control the offline message button
 * @param  array   $wplc_settings Live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_send_offline_message_button($wplc_settings) {
$wplc_settings = wplc_get_options();
$wplc_theme = wplc_get_theme();
if (!empty($wplc_settings['theme'])) {
    if($wplc_theme == 'theme-1') {
        $wplc_settings_fill = "#DB0000";
        $wplc_settings_font = "#FFFFFF";
    } else if ($wplc_theme == 'theme-2'){
        $wplc_settings_fill = "#000000";
        $wplc_settings_font = "#FFFFFF";
    } else if ($wplc_theme == 'theme-3'){
        $wplc_settings_fill = "#DB30B3";
        $wplc_settings_font = "#FFFFFF";
    } else if ($wplc_theme == 'theme-4'){
        $wplc_settings_fill = "#1A14DB";
        $wplc_settings_font = "#F7FF0F";
    } else if ($wplc_theme == 'theme-5'){
        $wplc_settings_fill = "#3DCC13";
        $wplc_settings_font = "#FF0808";
    } else if ($wplc_theme == 'theme-6'){
        if ($wplc_settings["wplc_settings_fill"]) {
            $wplc_settings_fill = "#" . $wplc_settings["wplc_settings_fill"];
        } else {
            $wplc_settings_fill = "#ec832d";
        }
        if ($wplc_settings["wplc_settings_font"]) {
            $wplc_settings_font = "#" . $wplc_settings["wplc_settings_font"];
        } else {
            $wplc_settings_font = "#FFFFFF";
        }
    } 
  } else {
    if ($wplc_settings["wplc_settings_fill"]) {
        $wplc_settings_fill = "#" . $wplc_settings["wplc_settings_fill"];
    } else {
        $wplc_settings_fill = "#ec832d";
    }
    if ($wplc_settings["wplc_settings_font"]) {
        $wplc_settings_font = "#" . $wplc_settings["wplc_settings_font"];
    } else {
        $wplc_settings_font = "#FFFFFF";
    }
  }
  $custom_attr = apply_filters('wplc_offline_message_button_custom_attributes_filter', "", $wplc_settings);
  $ret_msg = "<input id=\"wplc_na_msg_btn\" class='wplc-color-bg-1 wplc-color-2' type=\"button\" value=\"".stripslashes($wplc_settings['wplc_pro_offline_btn_send'])."\" $custom_attr/>";
  return $ret_msg;
}



/**
 * Filter to control the 2nd layer of the chat window (online/offline)
 * @param  array   $wplc_settings Live chat settings array
 * @param  bool    $logged_in     Is the user logged in or not
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_2nd_layer($wplc_settings,$logged_in,$wplc_using_locale, $cid) {
  global $wplc_default_settings_array;

  if ($logged_in) {
    $wplc_intro = $wplc_default_settings_array['wplc_pro_intro'];
    if (!isset($wplc_settings['wplc_pro_intro']) || $wplc_settings['wplc_pro_intro'] == "") { $wplc_settings['wplc_pro_intro'] = $wplc_intro; }
    $text = ($wplc_using_locale ? $wplc_intro : stripslashes($wplc_settings['wplc_pro_intro']));

    $ret_msg = "<div id=\"wp-live-chat-2-inner\">";
    $ret_msg .= " <div id=\"wp-live-chat-2-info\" class='wplc-color-4'>";
    $ret_msg .= apply_filters("wplc_filter_intro_text_heading", $text, $wplc_settings);
    $ret_msg .= " </div>";
    $ret_msg .= apply_filters("wplc_filter_live_chat_box_html_ask_user_details",wplc_filter_control_live_chat_box_html_ask_user_detail($wplc_settings));
    $ret_msg .= apply_filters("wplc_filter_live_chat_box_html_start_chat_button",wplc_filter_control_live_chat_box_html_start_chat_button($wplc_settings,$wplc_using_locale ), $cid);
    $ret_msg .= "</div>";
  } else {
    $wplc_loggedin_user_name  = '';
    $wplc_loggedin_user_email = '';
    if ($wplc_settings['wplc_loggedin_user_info']) {
      global $current_user;

      if ( $current_user->data != null ) {
        if ( is_user_logged_in() ) {
          //Logged in. Get name and email
          $wplc_loggedin_user_name = esc_attr($current_user->display_name);
          $wplc_loggedin_user_email = esc_attr($current_user->user_email);
        } else {
          $wplc_loggedin_user_name = stripslashes( $wplc_settings['wplc_user_default_visitor_name'] );
        }
      }
    }

    /* admin not logged in, show offline messages */
    $ret_msg = "<div id=\"wp-live-chat-2-info\" class=\"wplc-color-bg-1 wplc-color-2\">";
    $ret_msg .= "</div>";
    $ret_msg .= "<div id=\"wplc_message_div\">";
    $ret_msg .= "<input type=\"text\" name=\"wplc_name\" id=\"wplc_name\" value=\"$wplc_loggedin_user_name\" placeholder=\"".__("Name", 'wp-live-chat-support')."\" />";
    $ret_msg .= "<input type=\"text\" name=\"wplc_email\" id=\"wplc_email\" value=\"$wplc_loggedin_user_email\"  placeholder=\"".__("Email", 'wp-live-chat-support')."\" />";
    $ret_msg .= "<textarea name=\"wplc_message\" id=\"wplc_message\" placeholder=\"".__("Message", 'wp-live-chat-support')."\" maxlength='700'></textarea>";
    $ret_msg .= "<span class='wplc_char_counter'></span>";

    $offline_ip_address = esc_attr(wplc_get_user_ip());

    $ret_msg .= "<input type=\"hidden\" name=\"wplc_ip_address\" id=\"wplc_ip_address\" value=\"".$offline_ip_address."\" />";
    $ret_msg .= "<input type=\"hidden\" name=\"wplc_domain_offline\" id=\"wplc_domain_offline\" value=\"".site_url()."\" />";
    $ret_msg .= apply_filters("wplc_filter_live_chat_box_html_send_offline_message_button",wplc_filter_control_live_chat_box_html_send_offline_message_button($wplc_settings));
    $ret_msg .= "</div>";
  }

  $data = array(
    'ret_msg' => $ret_msg,
    'wplc_settings' => $wplc_settings,
    'logged_in' => $logged_in,
    'wplc_using_locale' => $wplc_using_locale
  );

  $ret_msg = apply_filters( "wplc_filter_2nd_layer_modify" , $data );
  if( is_array( $ret_msg ) ){
    /* if nothing uses this filter is comes back as an array, so return the original message in that array */
    return $ret_msg['ret_msg'];
  } else {
    return $ret_msg;
  }
}

/**
 * Filter to control the 3rd layer of the chat window
 * @param  array $wplc_settings live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_3rd_layer($wplc_settings,$wplc_using_locale) {
  global $wplc_default_settings_array;

  if (!isset($wplc_settings['wplc_pro_sst2']) || $wplc_settings['wplc_pro_sst2'] == "") { $wplc_settings['wplc_pro_sst2'] = $wplc_default_settings_array['wplc_pro_sst2']; }
  $text = ($wplc_using_locale ? $wplc_default_settings_array['wplc_pro_sst2'] : stripslashes($wplc_settings['wplc_pro_sst2']));

  $ret_msg = "<p class=''wplc-color-4>".$text."</p>";
  return $ret_msg;
}

add_filter("wplc_filter_intro_text_heading", "wplc_filter_control_intro_text_heading", 10, 2);
/**
 * Filters intro text
*/
function wplc_filter_control_intro_text_heading($content, $wplc_settings) {
	if ($wplc_settings['wplc_require_user_info'] == 'none') {
    $content = "";
  }
	return $content;
}

add_filter("wplc_filter_live_chat_box_above_main_div","wplc_filter_control_live_chat_box_above_main_div",10,3);
function wplc_filter_control_live_chat_box_above_main_div( $msg, $wplc_settings, $cid ) {
  if ($wplc_settings['wplc_newtheme'] == "theme-2") {
    $agent_string = '';

    if (!$wplc_settings['wplc_use_node_server']) {
      if ($cid) {
        $cid = wplc_return_chat_id_by_rel_or_id($cid);
        $chat_data = wplc_get_chat_data( $cid );
        if (isset( $chat_data->agent_id)) {
          $agent_id = intval( $chat_data->agent_id );
        } else {
          $agent_id = get_current_user_id();
        }
        if ($agent_id) {
          $user_info = get_userdata( $agent_id );
          if ($wplc_settings['wplc_use_wp_name']) {
            $agent = $user_info->display_name;
          } else {
            if (!empty($wplc_settings['wplc_chat_name'])) {
              $agent = $wplc_settings['wplc_chat_name'];
            } else {
              $agent = 'Admin';
            }
          }
          $extra = apply_filters( "wplc_filter_further_live_chat_box_above_main_div", '', $wplc_settings, $cid, $chat_data, $agent );
          $title = __("Minimize Chat", 'wp-live-chat-support');
          $agent_string = '<p style="text-align:center;">
  <img class="img-thumbnail img-circle wplc_thumb32 wplc_agent_involved" style="max-width:inherit;" id="agent_grav_'.$agent_id.'" title="'.esc_attr($agent).'" src="https://www.gravatar.com/avatar/'.md5($user_info->user_email).'?s=60&d=mm" /><br />
  <span class="wplc_agent_name wplc-color-2" title="' . esc_attr($agent) . '">'.esc_html($agent).'</span>
  '.$extra.'
  <button class="tcx_pullup down" title="'.esc_attr($title).'"><i class="fas fa-angle-down" ></i></button>';
  $title = __("End Chat", 'wp-live-chat-support');
  $text = "<i class='fas fa-angle-down'></i><i class='fas fa-angle-up'></i>";
  $text = '<i class="fa fa-times"></i>';
  $agent_string.='<button id="wplc_end_chat_button" title="'.$title.'">'.$text.'</button></p>';
        }
      }
    }
    $msg .= "<div id='wplc_chatbox_header_bg'><div id='wplc_chatbox_header' class='wplc-color-bg-1 wplc-color-4'><div class='wplc_agent_info'>".$agent_string."</div></div></div>";
  }
  return $msg;
}

/**
 * Filter to control the 4th layer of the chat window
 * @param  array $wplc_settings live chat settings array
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_4th_layer($wplc_settings, $wplc_using_locale, $cid) {
  $ret_msg = "";
  if ($wplc_settings['wplc_newtheme'] == 'theme-1') {
  	$ret_msg .= apply_filters("wplc_filter_typing_control_div","");
  }
  $ret_msg .= apply_filters("wplc_filter_inner_live_chat_box_4th_layer","", $wplc_settings);
  $ret_msg .= "<div id=\"wplc_sound_update\" style=\"height:0; width:0; display:none; border:0;\"></div>";
  $ret_msg .= apply_filters("wplc_filter_live_chat_box_above_main_div","",$wplc_settings, $cid);
  $ret_msg .= "<div id=\"wplc_chatbox\">";
  $ret_msg .= "</div>";
  $ret_msg .= "<div id='tcx_chat_ended' style='display:none;'></div>";
  $ret_msg .= "<div id='wplc_user_message_div'>";
  $ret_msg .= "<div>";
  $placeholder = __('Type here','wp-live-chat-support');
  $ret_msg .= "<textarea type=\"text\" name=\"wplc_chatmsg\" id=\"wplc_chatmsg\" placeholder=\"".$placeholder."\" class='wdt-emoji-bundle-enabled' maxlength='2000'></textarea>";

  $ret_msg .= '<div class="wplc_usericons_container">';
  if ($wplc_settings['wplc_newtheme'] == 'theme-2') {
  	$ret_msg .= apply_filters("wplc_filter_typing_control_div_theme_2","");
  }
  $ret_msg .= apply_filters("wplc_filter_chat_4th_layer_below_input","", $wplc_settings); //Ratings/Social Icon Filter

  //Upload Controls
  $ret_msg .= apply_filters("wplc_filter_chat_upload","");
  $ret_msg .= "<input type=\"hidden\" name=\"wplc_cid\" id=\"wplc_cid\" value=\"\" />";
  $ret_msg .= "<input id=\"wplc_send_msg\" type=\"button\" value=\"".__("Send", 'wp-live-chat-support')."\" style=\"display:none;\" />";
  $ret_msg .= "</div>";
  $ret_msg .= function_exists("wplc_emoji_selector_div") ? wplc_emoji_selector_div() : "";
  $ret_msg .= "</div>"; // wplc_usericons_container
  $ret_msg .= "</div>";
  $ret_msg .= "</div>";
  return $ret_msg;
}

/**
 * Filter to control the 1st layer of the chat window
 * @param  array $wplc_settings        live chat settings array
 * @param  bool  $logged_in            An agent is available or not
 * @param  bool  $wplc_using_locale    Are they using a localization plugin
 * @return string
 * @since  6.0.00
 */
function wplc_filter_control_live_chat_box_html_1st_layer($wplc_settings, $logged_in, $wplc_using_locale, $class_override = false) {
  global $wplc_default_settings_array;
  $ret_msg = "<div id='wplc_first_message'>";
  if ($logged_in) {
    if ($wplc_settings['wplc_newtheme'] == "theme-2") {
      $coltheme = "wplc-color-4";
    } else {
      $coltheme = "wplc-color-2";
    }
    if ($class_override) {
    	//Override color class
    	$coltheme = $class_override;
    }
    $wplc_tl_msg = "<div class='$coltheme'><strong>" . ($wplc_using_locale ? $wplc_default_settings_array['wplc_pro_fst1']: stripslashes($wplc_settings['wplc_pro_fst1'])) . "</strong> " . ( $wplc_using_locale ? $wplc_default_settings_array['wplc_pro_fst2'] : stripslashes($wplc_settings['wplc_pro_fst2'])) ."</div>";
    $ret_msg .= $wplc_tl_msg;
  } else {
    $wplc_tl_msg = "<div class='wplc_offline'><strong>" . ($wplc_using_locale ? $wplc_default_settings_array['wplc_pro_na'] : stripslashes($wplc_settings['wplc_pro_na'])) . "</strong> " . ( $wplc_using_locale ? $wplc_default_settings_array['wplc_pro_offline1'] : stripslashes($wplc_settings['wplc_pro_offline1'])) ."</div>";
    $ret_msg .= $wplc_tl_msg;
  }
  $ret_msg .= "</div>";
  return $ret_msg;
}

function wplc_shortenurl($url) {
	if ( strlen($url) > 45) {
		return substr($url, 0, 30)."[...]".substr($url, -15);
	} else {
		return $url;
	}
}

/**
 * The function that builds the chat box
 * @since  6.0.00
 * @return JSON encoded HTML
 */
function wplc_output_box_ajax($cid = null) {
  $ret_msg = array();
  $wplc_settings = wplc_get_options();

  if (isset($wplc_settings['wplc_using_localization_plugin']) && $wplc_settings['wplc_using_localization_plugin'] == 1){ $wplc_using_locale = true; } else { $wplc_using_locale = false; }
  $logged_in = wplc_agent_is_available();
  $ret_msg['cbox'] = apply_filters("wplc_theme_control",$wplc_settings, $logged_in, $wplc_using_locale, $cid);
  $ret_msg['online'] = $logged_in;

  if ($cid !== null && $cid !== '') {
    $ret_msg['cid'] = $cid;
    $chat_data = wplc_get_chat_data($cid);

    $referer = $_SERVER['HTTP_REFERER'];
    if (parse_url($referer, PHP_URL_HOST) == $_SERVER['SERVER_NAME']) { // keep referers only from site itself
      wplc_record_chat_notification('user_loaded', $cid, array('uri' => $referer, 'chat_data' => $chat_data ));
    }
    if (!isset($chat_data) || !$chat_data->agent_id ) {
      $ret_msg['type'] = 'new';
    } else {
      $ret_msg['type'] = 'returning';

      if ($wplc_settings['wplc_use_node_server']) {
        //This is using node, we shouldn't generate the header data as part of the chat box.
        //We will do this dynamically on the front end
      } else {
        /* build the AGENT DATA array */
        $user_info = get_userdata( intval( $chat_data->agent_id ) );
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

        if (!isset($data)) { $data = false; }
        $agent_tagline = apply_filters( "wplc_filter_agent_data_agent_tagline", $agent_tagline, $cid, $chat_data, $agent, $wplc_settings, $user_info, $data );
        $ret_msg['agent_data'] = array(
          'email' => md5($user_info->user_email),
          'name' => esc_html($agent),
          'aid' => $user_info->ID,
          'agent_tagline' => $agent_tagline,
        );
      }   
    }
  } else {
    $ret_msg['type'] = 'new';
  }
  return json_encode($ret_msg);
}

function wplc_return_default_theme($wplc_settings, $logged_in, $wplc_using_locale, $cid) {
  $wplc_settings = wplc_get_options();
  $wplc_theme = wplc_get_theme();
  $ret_msg = apply_filters("wplc_filter_live_chat_box_html_main_div_top",wplc_filter_control_live_chat_box_html_main_div_top($wplc_settings, $logged_in, $wplc_using_locale));
  $ret_msg .= "<div class=\"wp-live-chat-wraper\">";
  $ret_msg .= 	"<div id='tcx_bell' class='wplc-color-bg-1 wplc-color-2' style='display:none;'><i class='fa fa-bell'></i></div>";
  $ret_msg .=   apply_filters("wplc_filter_live_chat_box_html_header_div_top",wplc_filter_control_live_chat_box_html_header_div_top($wplc_settings));
  $ret_msg .= " <i id=\"wp-live-chat-minimize\" class=\"fa fa-minus wplc-color-bg-2 wplc-color-1\" style=\"display:none;\"></i>";
  $ret_msg .= " <i id=\"wp-live-chat-close\" class=\"fa fa-times\" style=\"display:none;\" ></i>";
  $ret_msg .= " <div id=\"wp-live-chat-1\" >";
  $ret_msg .=     apply_filters("wplc_filter_live_chat_box_html_1st_layer",wplc_filter_control_live_chat_box_html_1st_layer($wplc_settings, $logged_in, $wplc_using_locale, 'wplc-color-2'));
  $ret_msg .= " </div>";
  $ret_msg .= '<div id="wplc-chat-alert" class="wplc-chat-alert wplc-chat-alert--' . $wplc_theme  . '"></div>';
  $ret_msg .= " </div>";
  $ret_msg .= " <div id=\"wp-live-chat-2\" style=\"display:none;\">";
  $ret_msg .=     apply_filters("wplc_filter_live_chat_box_html_2nd_layer",wplc_filter_control_live_chat_box_html_2nd_layer($wplc_settings, $logged_in, $wplc_using_locale, $cid), $cid);
  $ret_msg .= " </div>";
  $ret_msg .= " <div id=\"wp-live-chat-3\" style=\"display:none;\">";
  $ret_msg .=     apply_filters("wplc_filter_live_chat_box_html_3rd_layer",wplc_filter_control_live_chat_box_html_3rd_layer($wplc_settings, $wplc_using_locale));
  $ret_msg .= " </div>";
  $ret_msg .= " <div id=\"wp-live-chat-react\" style=\"display:none;\">";
  $ret_msg .= "   <p>".__("Reactivating your previous chat...", 'wp-live-chat-support')."</p>";
  $ret_msg .= " </div>";
  $ret_msg .= " <div id=\"wplc-extra-div\" style=\"display:none;\">";
  $ret_msg .=     apply_filters("wplc_filter_wplc_extra_div","",$wplc_settings,$wplc_using_locale);
  $ret_msg .= "</div>";
  $ret_msg .= "</div>";
  $ret_msg .= " <div id=\"wp-live-chat-4\" style=\"display:none;\">";
  $ret_msg .=     apply_filters("wplc_filter_live_chat_box_html_4th_layer",wplc_filter_control_live_chat_box_html_4th_layer($wplc_settings, $wplc_using_locale, $cid));
  $ret_msg .= apply_filters("wplc_filter_chat_social_div_hook","", $wplc_settings); //Ratings/Social Icon Filter
  $ret_msg .= "</div>";
  return $ret_msg;
}

add_filter("wplc_theme_control","wplc_theme_control_function",10,4);

function wplc_theme_control_function($wplc_settings, $logged_in, $wplc_using_locale, $cid) {
  $cid=intval($cid);
  $wplc_settings = wplc_get_options();
  $wplc_theme = wplc_get_theme();
  if (!$wplc_settings) { return ""; }
  $default_theme = wplc_return_default_theme($wplc_settings, $logged_in, $wplc_using_locale, $cid);
  switch ($wplc_settings['wplc_newtheme']) {
    case 'theme-1':
      $ret_msg = $default_theme;
      break;

    default:
      $ret_msg = apply_filters("wplc_filter_live_chat_box_html_main_div_top",wplc_filter_control_live_chat_box_html_main_div_top($wplc_settings, $logged_in, $wplc_using_locale));
      $ret_msg .= "<div class=\"wp-live-chat-wraper\">";
      $ret_msg .= 	"<div id='tcx_bell' class='wplc-color-bg-1  wplc-color-2' style='display:none;'><i class='fa fa-bell'></i></div>";
      $ret_msg .=   apply_filters("wplc_filter_live_chat_box_html_header_div_top",wplc_filter_control_live_chat_box_html_header_div_top($wplc_settings));
      $ret_msg .= " </div>";
      $ret_msg .= '<div id="wplc-chat-alert" class="wplc-chat-alert wplc-chat-alert--' . $wplc_theme . '"></div>';
      $ret_msg .= " <div id=\"wp-live-chat-2\" style=\"display:none;\">";
      $ret_msg .= " 	<i id=\"wp-live-chat-minimize\" class=\"fa fa-minus wplc-color-bg-2 wplc-color-1\" style=\"display:none;\" ></i>";
      $ret_msg .= " 	<i id=\"wp-live-chat-close\" class=\"fa fa-times\" style=\"display:none;\" ></i>";
      $ret_msg .= " 	<div id=\"wplc-extra-div\" style=\"display:none;\">";
      $ret_msg .=     	apply_filters("wplc_filter_wplc_extra_div","",$wplc_settings,$wplc_using_locale);
      $ret_msg .= "	</div>";
      $ret_msg .= " 	<div id='wp-live-chat-inner-container'>";
      $ret_msg .= " 		<div id='wp-live-chat-inner'>";
      $ret_msg .= "   		<div id=\"wp-live-chat-1\" class=\"wplc-color-2 wplc-color-bg-1\" >";
      $ret_msg .=       			apply_filters("wplc_filter_live_chat_box_html_1st_layer",wplc_filter_control_live_chat_box_html_1st_layer($wplc_settings, $logged_in, $wplc_using_locale, 'wplc-color-2'));
      $ret_msg .= "   		</div>";
      $ret_msg .=     		apply_filters("wplc_filter_live_chat_box_html_2nd_layer",wplc_filter_control_live_chat_box_html_2nd_layer($wplc_settings, $logged_in, $wplc_using_locale, $cid), $cid);
      $ret_msg .= " 		</div>";
      $ret_msg .= " 		<div id=\"wp-live-chat-react\" style=\"display:none;\">";
      $ret_msg .= "   		<p>".__("Reactivating your previous chat...", 'wp-live-chat-support')."</p>";
      $ret_msg .= " 		</div>";
      $ret_msg .= " 	</div>";
      $ret_msg .= "   <div id=\"wp-live-chat-3\" style=\"display:none;\">";
      $ret_msg .=     	apply_filters("wplc_filter_live_chat_box_html_3rd_layer",wplc_filter_control_live_chat_box_html_3rd_layer($wplc_settings,$wplc_using_locale));
      $ret_msg .= "   </div>";
      $ret_msg .= " </div>";
      $ret_msg .= "   <div id=\"wp-live-chat-4\" style=\"display:none;\">";
      $ret_msg .=       apply_filters("wplc_filter_live_chat_box_html_4th_layer",wplc_filter_control_live_chat_box_html_4th_layer($wplc_settings,$wplc_using_locale, $cid));
      $ret_msg .= "   </div>";
      $ret_msg .= apply_filters("wplc_filter_chat_social_div_hook","", $wplc_settings); //Ratings/Social Icon Filter
      $ret_msg .= "</div>";
      break;
  }
  return $ret_msg;
}


function wplc_admin_accept_chat($cid) {
    $user_ID = get_current_user_id();
    wplc_change_chat_status(sanitize_text_field($cid), 3,$user_ID);
    return true;
}

add_action('admin_head', 'wplc_update_chat_statuses');


function wplc_superadmin_javascript() {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_use_node_server'] && (!isset($_GET['action']) || $_GET['action'] !== "history")) {
    //Using node, load remote scripts
    if ($wplc_settings['wplc_enable_all_admin_pages']) {
      /* Run admin JS on all admin pages */
      if ( isset( $_GET['action'] ) && $_GET['action'] == "history" ) {
      } else {
        wplc_admin_remote_dashboard_scripts($wplc_settings);
      }
    } else {
      /* Only run admin JS on the chat dashboard page */
      if (isset($_GET['page']) && $_GET['page']=='wplivechat-menu' && !isset( $_GET['action'])) {
        wplc_admin_remote_dashboard_scripts($wplc_settings);
      }
    }

    if (isset($_GET['page']) && $_GET['page'] == "wplivechat-menu-offline-messages") {
      wplc_admin_output_js();
    }
  } else {
    do_action("wplc_hook_superadmin_head");
    if (isset($_GET['page']) && isset($_GET['action']) && $_GET['page'] == "wplivechat-menu" && ($_GET['action'] != 'welcome' && $_GET['action'] != 'credits')) {
      /* admin chat box page */
      /** set the global chat data here so we dont need to keep getting it from the DB or Cloud server */
      global $admin_chat_data;
      $admin_chat_data = wplc_get_chat_data($_GET['cid'], __LINE__);
      wplc_return_admin_chat_javascript(sanitize_text_field($_GET['cid']));
      do_action("wplc_hook_admin_javascript_chat");
    } else {
      /* load this on every other admin page */
      wplc_admin_javascript();
      do_action("wplc_hook_admin_javascript");
    }
  ?>
  <script type="text/javascript">
    function wplc_desktop_notification() {
      if (typeof Notification !== 'undefined') {
        if (!Notification) {
          return;
        }
        if (Notification.permission !== "granted") {
          Notification.requestPermission();
        }
        var wplc_desktop_notification = new Notification('<?php _e('New chat received', 'wp-live-chat-support'); ?>', {
          icon: wplc_notification_icon_url,
          body: "<?php _e("A new chat has been received. Please go the 'Live Chat' page to accept the chat", 'wp-live-chat-support'); ?>"
        });
      }
    }
  </script>
  <?php
  }

  if (isset($_GET['page']) && $_GET['page'] == 'wplivechat-menu-settings') {
    wp_enqueue_script('wplc-admin-js-settings', plugins_url('js/wplc_u_admin_settings.js', __FILE__), false, WPLC_PLUGIN_VERSION, false);
	wp_localize_script('wplc-admin-js-settings', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
  }

}


/**
 * Admin JS set up
 * @return void
 * @since  6.0.00
 */
function wplc_admin_javascript() {
	$wplc_settings = wplc_get_options();

  if ($wplc_settings['wplc_enable_all_admin_pages']) {
    /* Run admin JS on all admin pages */
    if (isset( $_GET['action'] ) && $_GET['action'] == "history" ) { 
      return; 
    } else {
      wplc_admin_output_js();
    }
  } else {
    /* Only run admin JS on the chat dashboard page */
    if (isset( $_GET['page'] ) && ($_GET['page'] == 'wplivechat-menu') && !isset( $_GET['action'])) {
      wplc_admin_output_js();
    }
  }
}

/**
 * Outputs the admin JS on to the relevant pages, controlled by wplc_admin_javascript();
 *
 * @return void
 * @since  7.1.00
 */
function wplc_admin_output_js() {
  $ajax_nonce = wp_create_nonce("wplc");
  if (wplc_user_is_agent()) {
    $wplc_settings = wplc_get_options();
    wp_register_script('wplc-admin-js', plugins_url('js/wplc_u_admin.js', __FILE__), array(), WPLC_PLUGIN_VERSION, true);
    wp_enqueue_script('wplc-admin-js');
    wp_localize_script( 'wplc-admin-js', 'tcx_ringer_count', array('value'=>intval($wplc_settings['wplc_new_chat_ringer_count'])));
    $not_icon = plugins_url('/images/wplc_notification_icon.png', __FILE__);
    $wplc_wav_file = apply_filters("wplc_filter_wav_file", '');
    wp_localize_script('wplc-admin-js', 'wplc_wav_file', $wplc_wav_file);
    wp_localize_script('wplc-admin-js', 'wplc_ajax_nonce', $ajax_nonce);
    wp_localize_script('wplc-admin-js', 'wplc_notification_icon', $not_icon);
    wp_localize_script('wplc-admin-js', 'tcx_favico_noti', WPLC_PLUGIN_URL . 'images/tcx48px_n.png');
    wp_localize_script('wplc-admin-js', 'tcx_favico', WPLC_PLUGIN_URL . 'images/tcx48px.png');
    $extra_data = apply_filters("wplc_filter_admin_javascript",array());
    wp_localize_script('wplc-admin-js', 'wplc_extra_data', $extra_data);
    $ajax_url = admin_url('admin-ajax.php');
    $wplc_ajax_url = apply_filters("wplc_filter_ajax_url",$ajax_url);
    wp_localize_script('wplc-admin-js', 'wplc_ajaxurl', $wplc_ajax_url);
    wp_localize_script('wplc-admin-js', 'wplc_ajaxurl_home', admin_url( 'admin-ajax.php' ) );
    $wpc_ma_js_strings = array(
      'remove_agent' => __('Remove', 'wp-live-chat-support'),
      'nonce' => wp_create_nonce("wplc"),
      'user_id' => get_current_user_id(),
      'typing_string' => __('Typing...', 'wp-live-chat-support')
    );
    wp_localize_script('wplc-admin-js', 'wplc_admin_strings', $wpc_ma_js_strings);
  }
}

function wplc_admin_menu_layout() {
  do_action("wplc_hook_admin_menu_layout");
  update_option('WPLC_V8_FIRST_TIME', false);
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_use_node_server']) {
    //Node in use, load remote dashboard
    if ( $_GET['page'] === 'wplivechat-menu') {
      wplc_admin_dashboard_layout_node('dashboard');
      if (isset($_GET['action'])) {
        wplc_admin_menu_layout_display();
      }
    } else {
      // we'll control this in admin_footer
    }
  } else {
    wplc_admin_menu_layout_display();
  }
}

function wplc_first_time_tutorial() {
  if (!get_option('WPLC_FIRST_TIME_TUTORIAL')) {
  ?>
  <div id="wplcftt" style='margin-top:30px; margin-bottom:20px; width: 65%; background-color: #FFF; box-shadow: 1px 1px 3px #ccc; display:block; padding:10px; text-align:center; margin-left:auto; margin-right:auto;'>
  <img src='<?php echo WPLC_PLUGIN_URL; ?>images/wplc_notification_icon.png' width="130" align="center" />
  <h1 style='font-weight: 300; font-size: 50px; line-height: 50px;'><strong style="color: #0596d4;"><?php _e("Congratulations",'wp-live-chat-support'); ?></strong></h1>
  <h2><strong><?php _e("You are now accepting live chat requests on your site.",'wp-live-chat-support'); ?></strong></h2>
  <p><?php _e("The live chat box has automatically been enabled.",'wp-live-chat-support'); ?></p>
  <p><?php _e("Chat notifications will start appearing once visitors send a request.",'wp-live-chat-support'); ?></p>
  <p><?php echo sprintf(__("You may modify your chat box settings %s",'wp-live-chat-support'),'<a href="?page=wplivechat-menu-settings" target="_BLANK">'.__('here','wp-live-chat-support').'</a>'); ?></p>
  <p><?php _e("Experiencing issues?",'wp-live-chat-support'); ?> <a href="https://www.3cx.com/wp-live-chat/docs/" target="_BLANK" title=""><?php _e("Take a look at our how-to guides.",'wp-live-chat-support'); ?></a></p>
  <p><button id="wplc_close_ftt" class="button button-secondary"><?php _e("Hide",'wp-live-chat-support'); ?></button></p>
  </div>
  <?php }
}

/**
 * Control the content below the visitor count
 * @return void
 * @since  6.0.00
 */
add_filter("wplc_filter_chat_dahsboard_visitors_online_bottom","wplc_filter_control_chat_dashboard_visitors_online_bottom",10);
function wplc_filter_control_chat_dashboard_visitors_online_bottom($text) {
  $text = "<hr />";
  $text .= "<p class='wplc-agent-info' id='wplc-agent-info'>";
  $text .= "  <span class='wplc_agents_online'>1</span>";
  $text .= "  <a href='javascript:void(0);'>".__("Agent(s) online",'wp-live-chat-support')."</a>";
  $text .= "</p>";
  return $text;
}

function wplc_admin_menu_layout_display() {
  if (wplc_user_is_agent()) {
    do_action("wplc_hook_admin_menu_layout_display_top");
    wplc_stats("chat_dashboard");
    if (!isset($_GET['action'])) {
  ?>
  <div class='wplc_network_issue' style='display:none;'></div>
    <div class='wplc_page_title'>
    <?php wplc_first_time_tutorial(); ?>
    </div>
    <div id="wplc_sound"></div>
    <div id="wplc_admin_chat_holder">
    <div class="wplc_admin_chat_on_premise_header">
    <div class="wplc_admin_chat_on_premise_header_left">
    <img src="data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAABkAAD/4QMvaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0MiA3OS4xNjA5MjQsIDIwMTcvMDcvMTMtMDE6MDY6MzkgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE4IChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpERkVFRTcwRUFENDkxMUU5QjUzM0I0QThEMzhGNzc5MyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpERkVFRTcwRkFENDkxMUU5QjUzM0I0QThEMzhGNzc5MyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkRGRUVFNzBDQUQ0OTExRTlCNTMzQjRBOEQzOEY3NzkzIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkRGRUVFNzBEQUQ0OTExRTlCNTMzQjRBOEQzOEY3NzkzIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+/+4AJkFkb2JlAGTAAAAAAQMAFQQDBgoNAAAH7QAADocAABIGAAAWAv/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8IAEQgARgBGAwERAAIRAQMRAf/EAQ8AAAICAgMBAAAAAAAAAAAAAAgJAAcGCgEDBAUBAQACAgMBAAAAAAAAAAAAAAAGCAEHAgMFBBAAAQMCBQMDBAMBAAAAAAAABQMEBgACEAEVFgcRFwggFDUwQBI2JicYKBEAAQMCAgUGBwsKBwAAAAAAAgEDBBEFABIhMRMUBhBBUSIjFSBxgTJCchZhoVLSMySU1DU2pvBikqJDU2OTNLRkJZWl1Sb2EgABAgIDCQoLCQAAAAAAAAABAgMAESESBCAxQVFx0ZIzNDCBkbEigtIToxQQQGGhcqKy4iOzBcEyUlNjk/P0JRMBAQACAQMDBAEFAQAAAAAAAREAITFBUWEQIHHwgZHBoTBAsdHh8f/aAAwDAQACEQMRAAABeIQoUAgw4I4P4+sQgscSyeUhC/TYhLKBwNdOSQwnLJU27OXXyzCv4XsizK9232DhMIvKdaubDtvQNyxiZzHLllcMwgAOViuxs1imANZ1q5sm2tB3FGZjgnr+DWHvRYVuWAvq1eR8pTgq2WQI5rN0p+/833479vlYv6Hk17AdqjfWK7W0edZrImFZ4kbYOpdQam3ziMVnfGMtUZcoQGUQ0U12dPX193oGWjpz2EIfMFpFYB7BMkIf/9oACAEBAAEFAsJfyXD4RkU8o8vzS8ojuV8c8koeUvYv2RNrjzNzVfH1Vl1nK2MD5GkMAIROViJmErlWabGhwxDVS1gQZ00UfWiDq0QfUhCsbB/BU4UikvrygL3qGoz81B5VPAojuFy9XcLl6u4PLtT10/fVbnnbnuhbtn5K2XW8gRn5qEZcqaP/ANBUek3M0ZR7s8g1Lnzskzr2bv8Azl5PgL70ALlJoVHyA0NR3jLqIGjJXLrUldIoixzByVIbea7WlcbZS2PSKPk4sZqLFclmponcTfdc8PHXjtVw9w5I4wC8iMJfx/KYQ5tvuswSSVXU408fiRRZu3QaIYvPZ+1ktnjPe6Ra+Lad8E7VdMf/2gAIAQIAAQUC+zENLHxHISLty0sXWli60sXR4OxtH4xv5gsPEOnOixutGjlaNHKJJpJR/GN/MFtv+6/htNGMYf3bdDUXSTQCYg3CbYoo1Zuq0wbSLVs3wkblFEZ6YyTyWbGCOZB51z9dt11n1//aAAgBAwABBQL7OQEFBgjM+cvu105WuG61w5UVkJS8tjMv10CWkLFnuOaVuOZVuOZUGVcLyzGZfroLdvsv7HogUnAuzd8kqPLLOpNjJmiz0Ii/IMq1ozTl89dZfllUPZuHBr0dMqmgS5s9jwi0QO6Zeu+yxTL63//aAAgBAgIGPwLxNuzO6sznvCf2RLqGuARqWdERqGdERqWdEQu0sNpQ8iV6iYnT5LhrnewYDlue6t6rerpTRTgVG19o3mjau1bzRtXat5oW3ZzWZDYkZzmKMNw1zvZMf6e0Vf1L3Moj+eCiyJrKAnfdHGRGp9ZfShxloSbSgAcIuGnXjJukTygiA66224ZXykGjLGzs6Cc0Hu7aET/CAOLwLaUR1q5ADDf4ro2R4/EaFHoe7xSguJ1AoTkx79/cOSZUS4d3/9oACAEDAgY/AvE3razrkgSyqITPenOK3eX6cSiOKiNptGmrPG02jTVnjabRpqzwix2t1TtndmOVTIymCDfwZJHHcP5UfMTBZ+l2brrPXJn1a1UyFE0nJGwdi90o2HsHulGw9g90obetaalpU8oqEqsjI0Sz03D+VHzEwT9En3OufyvvSp1lOLyeeP60Jct66iFGQMmTTzQY2j1G+hDNotCqzy3SSeabh6z2cVnpAyx1VA8MhBZYdeZE6QlSk05BhjarT+4vPA72866E3qyiZTxT8DT7aT1DUypWAUGjKcW/dD6hZk/BfVIgYHPev5ZwllQHeVcpfpYsib3nw7hJYBAIO+KRwbv/AP/aAAgBAQEGPwLkUL3dQ37LnC1Qh3u5mipUVWO2tI4nTQTxNgvTghsvCSk16D90uWRwvWiRI5iH85cIr/C1pcb5xamTGT8hmL4p+jhti+Qp/Djx0TbnS524VXRQpEZtuWOnnWPlTnVMMzrdLjzocgc7EqI8D7Do9IOtqQl4D/CnCTwd8ImS7XYaGlqzJ/Rw0VFArjReuepjV5/ybsiS87IkPmTrz77hOvPOGuY3HXDUjcMyWqqulfAGRbHyftzjiLcLM+4W4zg0IS5etu0tBTqvCmZKacw1FYt9sz2eNI6jrJ5UkQpQU20OWAquzfZzeIhVCSoqi8k+6sKPeclRttnEqL8/lCeV/KtcyQ2AN2mpVCnPhsJbrjiyXXX5LhmROvFlN9xTcWpqbpJpXXpxQbbGJE/gCa+UlRVVfHj7LjfRQ+Lj7LjfRQ+Lj7LjfRQ+Lh6SxHbjvR8hdkmzEhziJiYJ1V0FXpxHtsh1UsvErrNtlgS9RiaZKFtmpUhEFB89ma6tk4q+inJw1YULsodsfurgJqV24SSiNqX5zYW4qdCH7uInikf27uHYnDXDiXaAs5145KWa5zssk2mENlX4DzTdRbAVovWTN0Ux9yPw1xD9bx9yPw1xD9cx9yPw1xD9cxxDLukbdLjIfdemxt3OLu8gpA7Rrd3O0byr8LrdOnThCFVEhVFEk0KippRUXpTHtjma3z2K7+rl7Lfu5t8y5Oje9GXyYiEXmucM282/VSddW1/XBcRfFI/tncGvBmfulZruaq2DLvmzZ22RLsu3RMmTzepX3a4/8XhmRfJiwmJDqsMubtwvJQnUFTyfNGH1Fcic9Mfb/wDtdm/47F4uE58pEuYqyJDxZUVxxx0FJcoIICnQiIgomhOTYdbbew2+a1rumTvDX8HcPexw5xO0FQYORZZpIlcqPfPICrTUCE2+ir0kmIjz5ZG6uARrqHatG2il0ChFpwrVqvV0gR3T2yt2+4y4zDhkKDtVGO8AERCCJXoTH3p4i/1q4/WcNJdLtcrkLCkrSTp0mUjSuUQ1bR9xxAUkFK05JDRGO1kIANN5uuXaCpLTSuURH8q4hWyE2rsu4S48KM0ms35TostD5TPHspnLce4PZ7aUTPuvd3duenm5tji6cPXDQxcYyto6goRxpAqjkWW2i0q5GkAJonPSmrE6xXdhWJsB5Wz17N5vWzJjkqJtI8huhAvQvTyFCfPtIg5gIlpmjJ7q/uNXq0wbqKuwb7KOOn5NF8+nwnF0+9zY18nt5dWFGHD2zHD7bgf1UskJmTcUza2YgKTYLTS6qqiorenkFJHzC9RGyG23httCNpFXNu0puo71CI9OWqKCrUVSq1Jm+21wI6nlj3OOhP2uV0bGWgoImSfszyOJzjhVAlFVEgXKtKiSZSHxEK8jbLDbjzzpi2000BOOOGS0EGwBFIyJdSJiPeON2XbXahUHW7ISq3c7h6SBMQVQ7bGL0kXt10pQNBYZixWWo8aO0DEdhkBaZZZaFAbaabBEEGwFKIiaETwH+8N23LZrvO+bLddl6W323ZbP1tGCGe7Z2ZNVq5w4l7WOnTT2dbdtnvYzFcpshK12bwcaoHq9hAYOiePH/Q/ZneNn1tx2PfGy/wARvP8Am2T1/A//2gAIAQEDAT8h9ApEay4ynrWWF4ctOGJ5WfGfWwb19JFjN9oJRDKrgFsWAQu0/wBNodMdOnfsaae2xsYFZKGotcNkDRnW4hJRr7OJuPyH0At0gOKY4RgkzrUKVQqbJIjoDckFThEeUKETOUFL3dk25BGYV/E1h1LnT/yvoBf9XFC0Y9QKk0mAQ3KYF8BmqHga3YhY46OjHjNXuYj0S+OsyD3lAtIb0H/Xj8jN534sKHDDSXkPWjb21KX+FCnAmxCmfXRX8cofjVNzRFA1wPSyXhunwLgTVorx+74v/ebPqwnUPypIYx9CwxMvCIE3RxAAB6fWQx3eLjjxj3GnF5OJRqXlMKgLBLvuga4DedPMyqSs6qodjNf7H9MFuj74mxrQob4wHn6+9w2yFV0fNEV0WFozuSHFb6AG2oGfTqePlTi4WFbgE0FMAb2zkxBMNzssaKXcFBXu/nBVE++5OoFtdV2Obha0gO2Um16gmHkfly5DbIsGN0GEj18EwQsEwnKk1SnBy/TpRa2EBYi4MSQgES6klYE6j6G1UMecmgAVXWBiOxZEAaCg+RxCyqeSSdxgEPZ9AED8tgmvVKLxVr5+OuG6x0fZE6S8985/zZj/AC/bb1vs/9oACAECAwE/If7OklVHKNP30++DtQ7t/LV++fXf6z6h/WfXP6ykJQ6oAIdjRlobns/jZdfWhwRhgu1Smtd/QXx8Pj4YdXdYIY0/bXbXs/jZ1ImT+Vl8l53PE9Lfd4cDi+Z6SGKNHYO5v87evshklOgmr4qV6G8U4KO8xEOt3tv0GJJfntJxYL6RFnqbRWdgO+LrlPbXBjl6Xn6I/wCzG+eH8Ozu9uvB0zye9RWSlGaET4TT4/r/AP/aAAgBAwMBPyH+zDkgXwBI69B1kecbd0YB8CB8Bnw3oPHblgYYCVYNBpsCXY1p7N8FDpSa90hGg0dl7Z4mQ6+/oId7NR8tpsn+xWr6mwxZ2y5NdGnQnRye/oh6c+PFmya7zCuxMizFG6rXiAdgADgOPYupBjmjDukh1ddc6C9q9KqHQDq6DKFyo4gPys2MWdMOLeKytEeCK4okqyxB9tOTGFMycn+tHc9xnOeb5HX2NOnPUzxGQOPcMGgpYlDygR6O/wCv/9oADAMBAAIRAxEAABACCQAAAACAG3YgSYaQCQsPiAiEeSQ37gCCgQACSAAf/9oACAEBAwE/EPTZAiVTGSn5I0xvyNaXEqvn+MPnZBsRaVlN/rxKjMF1OUeYIjab+01oGWUETQAh6frf4ws4bFEhjLuGPRoSVKgcqLkiq+x/xnjSegtnRlWRrktTJah6HuWhSgCvBVSxt5WFu5KH9ShV1W6Ic2IgNC+WqG1bvJ8opeNb1H/nNu/wE1BdNbyGpm9rfn44e3qeZFb1DWhINclIUoP7I9BtHfowB8jjxWpkKhQOX6aMpj7mTCTR0PwN8CTLnw/xvgnJJzGPuoc49pQbrL3Z4zRa2mNi0zyDh1jYNmdKCyGxM+QBL5b+XS8plLgRuvOzRGm67UwKJrfXRpHcehjjKTMp2NYCxFtkbdfIDrul7y33Rla/44+SI4dazqdG8UDUwtPQ18Qfl7Hn63ngv8lm2Zt1cMB1wukRgoJALdJiGkVVStMHFokQP5JLIO1AL8K8JzmcofHfFzdOQgZAQWoJAk3tVi9Nr2OXEniYEEtP+DwQZga8hIS4UAqoC584L7hH9C+MTA58/wAQLlI8aXIAoAm75wwVmDIEHQQfjjH474JZiotgAzkCH1tHJa2upQlBn0J+8UtVXutfy4rIhoHm445UE/M+d/nv6VP0vaWRJhZI+xqjaT+x5tAorUdG+pqWPpAk9ApkJ/Cg8FgFcTW/BedAhnm+A0zpzm02zAAE9n6hmT4JPzM35/R+AvVErU6IeTM+RdgrF1qvU0+l+b74PbznsP/aAAgBAgMBPxD+zUIHCiWKInNSIJGhjFEYVWa2Ku6leuX3uw68LfvZSbAJHCuraATNiPqgd4zYC3fY0MNRAla2Whw9NyY5LRB/0vSRTYzMCQHsdp4fKF/Jh8sxZjY6YKANVxpwB2DSpSm8uaGcrqxVAQVKdNUoqlV9g8lY0qwYBuGWWjH5sjXSg03Qjsnrkt0/Lg+lA1zUNAKoNC5E6fxhvlzHDEVFjOguB9gehQ+cRsCOhxV1tGUBRY8D76rAZ2UllCBVZzyPy4qtdvueJaIlj5ypB0pHT/X/AP/aAAgBAwMBPxD+zbVewK0FqkkIiEWEd6aMO4IZwAI40h+Tfb0nZa8gtkVuM1YqtufQSEepRHL/AD4K5eitKO1CSRbqCbNHfMk4vBf1wqbu+x/LB99YDTbHDvnmck1Z9gYWj/jTBsEYXBXSQNZaeTZug+vlxkbJuC0uIUSGMWYIctv6X11MjN1QXkAAdAIAAHqXkFlIXgsBxTDsYLfBiVCXAWNCOADSpeN/36Exn6gqgVpwBoUC8GI4G9X/AHaf4M69EzAZmheRime1RQX4wBGhV7IDUgF2Mji4gAQdLTawTVIUQz/xDAEAHuENFEAwkYU2wBsH+v8A/9k=" align="middle"> <h2>WP Live Chat by 3CX</h2>
    <?php do_action("wplc_hook_chat_dashboard_above"); ?>
    </div>
    <div id="wplc_admin_chat_info_new" class="wplc_admin_chat_on_premise_header_right">
    <div class="wplc_admin_chat_on_premise_switchery_label">
    <b><?php _e("Keep this window open to get notified of new chats.",'wp-live-chat-support'); ?></b>
    </div>
    <div class='wplc_chat_vis_count_box'>
    <?php do_action("wplc_hook_chat_dahsboard_visitors_online_top"); ?>
    <div class="wplc_chat_dashboard_on_premise_stats">
    <span class='wplc_vis_online'>0</span> <?php _e("Visitor(s) online",'wp-live-chat-support'); ?> 
    </div>
    <div class="wplc_chat_dashboard_on_premise_stats">
    <?php echo apply_filters("wplc_filter_chat_dahsboard_visitors_online_bottom",""); ?>
    </div>
    <?php do_action("wplc_after_chat_visitor_count_hook"); ?>
    </div>
    </div>
  </div>
  <div id="wplc_admin_chat_area_new">
  <div style="display:block;">
  <div class='wplc_chat_on_premise_header'>
  <div class='wplc_header_vh wplc_header_v'><?php _e("Visitor",'wp-live-chat-support'); ?></div>
  <div class='wplc_header_vh wplc_header_t'><?php _e("Time",'wp-live-chat-support'); ?></div>
  <div class='wplc_header_vh wplc_header_nr'><?php _e("Type",'wp-live-chat-support'); ?></div>
  <div class='wplc_header_vh wplc_header_dev'><?php _e("Device",'wp-live-chat-support'); ?></div>
  <div class='wplc_header_vh wplc_header_d'><?php _e("Data",'wp-live-chat-support'); ?></div>
  <div class='wplc_header_vh wplc_header_s'><?php _e("Status",'wp-live-chat-support'); ?></div>
  <div class='wplc_header_vh wplc_header_a'><?php _e("Action",'wp-live-chat-support'); ?></div>
  </div>
  <div id='wplc_chat_ul'></div>
  </div>
  </div>
  </div>
  <?php
    } else {
      if (isset($_GET['aid'])) { $aid = intval($_GET['aid']); } else { $aid = null; }
      if (!is_null($aid)) {
        do_action("wplc_hook_update_agent_id", intval($_GET['cid']), $aid);
      }
      if ($_GET['action'] == 'ac') {
        do_action('wplc_hook_accept_chat',$_GET, $aid);
      }
      do_action("wplc_hook_admin_menu_layout_display", $_GET['action'], intval($_GET['cid']), $aid);
    }
  } else {
  ?>
  <h1><?php _e("Chat Dashboard",'wp-live-chat-support'); ?></h1>
  <div id="welcome-panel" class="welcome-panel">
  <div class="welcome-panel-content">
  <h2><?php _e("Oh no!",'wp-live-chat-support'); ?></h2>
  <p class="about-description">
  <?php echo sprintf(__("You do not have access to this page as %s.",'wp-live-chat-support'),'<strong>'.__('you are not a chat agent','wp-live-chat-support').'</strong>'); ?>
  </p>
  </div>
  </div>
  <?php
  }
}

add_action("wplc_hook_change_status_on_answer","wplc_hook_control_change_status_on_answer",10,2);
function wplc_hook_control_change_status_on_answer($get_data, $chat_data = false) {

  $user_ID = get_current_user_id();
  wplc_change_chat_status(sanitize_text_field($get_data['cid']), 3,$user_ID );
  wplc_record_chat_notification("joined",$get_data['cid'],array("aid"=>$user_ID));
}


add_action('wplc_hook_accept_chat','wplc_hook_control_accept_chat',10,2);
function wplc_hook_control_accept_chat($get_data,$aid) {

	global $admin_chat_data;

	if (!$admin_chat_data) {
		$chat_data = wplc_get_chat_data($get_data['cid'], __LINE__);
	} else {
		if (isset($admin_chat_data->id) && intval($admin_chat_data->id) === intval($get_data['cid'])) {
			$chat_data = $admin_chat_data;
		} else {
			/* chat ID's dont match, get the data for the correct chat ID */
			$chat_data = wplc_get_chat_data($get_data['cid'], __LINE__);
		}
	}


	do_action("wplc_hook_accept_chat_url",$get_data, $chat_data);
  	do_action("wplc_hook_change_status_on_answer",$get_data, $chat_data);
	do_action("wplc_hook_draw_chat_area",$get_data, $chat_data);


}

/**
* Hook to accept chat
*
* @since        7.1.00
* @param
* @return       void
*
*/
add_action( 'wplc_hook_accept_chat_url' , 'wplc_b_hook_control_accept_chat_url', 5, 2);
function wplc_b_hook_control_accept_chat_url($get_data, $chat_data = false) {
    if (!isset($get_data['agent_id'])) {
    	/* free version */
        wplc_b_update_agent_id(sanitize_text_field($get_data['cid']), get_current_user_id());
    } else {
        wplc_b_update_agent_id(sanitize_text_field($get_data['cid']), sanitize_text_field($get_data['agent_id']));
    }

}

/**
* Assign the chat to the agent
*
* Replaces the same function of a different name in the older pro version
*
* @since        7.1.00
* @param
* @return       void
*
*/
function wplc_b_update_agent_id($cid, $aid){
    global $wpdb;
    global $wplc_tblname_chats;
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$wplc_tblname_chats` WHERE `id` = '%d' LIMIT 1", $cid));
    if ($result) {
	    if(intval($result->status) != 3){
        $wpdb->query($wpdb->prepare("UPDATE `$wplc_tblname_chats` SET `agent_id` = %d WHERE `id` = %d LIMIT 1", $aid, $cid));
	    }
	}
}


add_action("wplc_hook_draw_chat_area","wplc_hook_control_draw_chat_area",10,2);
function wplc_hook_control_draw_chat_area($get_data, $chat_data = false) {

 wplc_draw_chat_area(sanitize_text_field($get_data['cid']), $chat_data);
}

function wplc_draw_chat_area($cid, $chat_data = false) {

    global $wpdb;
    global $wplc_tblname_chats;

    $results = $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM $wplc_tblname_chats
        WHERE `id` = %s
        LIMIT 1
        ", $cid)
    );

	if($results) {

    $result = apply_filters("wplc_filter_chat_area_data", $results[0], $cid);

    ?>
    <style>

        .wplc-clear-float-message{
            clear: both;
        }

        .rating{
            background-color: lightgray;
            width: 80px;
            padding: 2px;
            border-radius: 4px;
            text-align: center;
            color: white;
            float: right;
        }

        .rating-bad {
            background-color: #AF0B0B !important;
        }

        .rating-good {
            background-color: #368437 !important;
        }
		#wpcontent{
			margin-left:0!important;
			padding:15px;
		}
		#wpbody-content{
			padding-bottom:0;
		}
		html.wp-toolbar{
			padding-top:0!important;
		}
		@media screen and (max-width: 600px){
		#wpbody {
			padding-top: 0px!important;
		}
		}

    </style>
    <?php

      $user_data = maybe_unserialize($result->ip);
      $browser = isset($user_data['user_agent']) ? wplc_return_browser_string($user_data['user_agent']) : "Unknown";
      $browser_image = wplc_return_browser_image($browser, "16");

      if ($result->status == 1) {
          $status = __("Previous", 'wp-live-chat-support');
      } else {
          $status = __("Active", 'wp-live-chat-support');
      }

	echo "<div class=\"wplc_on_premise_chat_window\">";
	echo "<div class=\"wplc_on_premise_chat_window_header\">";
	echo "<h2>$status " . __( 'Chat with', 'wp-live-chat-support') . " " . esc_html($result->name) . "</h2>";
	
	if ( isset( $_GET['action'] ) && $_GET['action'] === "ac" ) {
	 echo "<div class=\"end_chat_div\">";

      do_action("wplc_admin_chat_area_before_end_chat_button", $result);

      echo "<a href=\"javascript:void(0);\" class=\"wplc_admin_close_chat button\" id=\"wplc_admin_close_chat\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i> " . __("End chat", 'wp-live-chat-support') . "</a>";

      do_action("wplc_admin_chat_area_after_end_chat_button", $result);
	  do_action("wplc_hook_admin_visitor_info_display_after",$cid);

      echo "</div>";
	}
	 echo "</div>";
	
	if ( isset( $_GET['action'] ) && 'history' === $_GET['action'] ) {
		echo "<span class='wplc-history__date'><strong>" . __( 'Starting Time:', 'wp-live-chat-support') . ' </strong>' . date( 'Y-m-d H:i:s', current_time( strtotime( $result->timestamp ) ) ) . "</span>";
		echo "<span class='wplc-history__date wplc-history__date-end'><strong>" . __( 'Ending Time:', 'wp-live-chat-support') . ' </strong>' . date( 'Y-m-d H:i:s', current_time( strtotime( $result->last_active_timestamp ) ) ) . "</span>";	
	}
      echo "<style>#adminmenuwrap { display:none; } #adminmenuback { display:none; } #wpadminbar { display:none; } #wpfooter { display:none; } .update-nag { display:none; }</style>";

    if (empty($chat_data)) {
      $chat_data = wplc_get_chat_data($cid);
    }
    do_action("wplc_add_js_admin_chat_area", $cid, $chat_data);

      echo "<div id='admin_chat_box'>";

      $result->continue = true;

      //do_action("wplc_hook_wplc_draw_chat_area",$result);

      if (!$result->continue) { return; }

	  echo "<div class='wplc_on_premise_chat_box_user_info'>";
	  echo "<div class='wplc_on_premise_chat_box_user_info_avatar'><img src=\"//www.gravatar.com/avatar/" . md5($result->email) . "?d=mm\" class=\"admin_chat_img\" width=\"50px\" /></div>";
	  echo sprintf("<div class='wplc_on_premise_chat_box_user_info_details'>%s, (%s)<br>",sanitize_text_field($result->name),sanitize_text_field($result->email));
      echo "<span class='part1'><b>" . __("Chat initiated on:", 'wp-live-chat-support') . "</b></span> <span class='part2'>" . esc_url($result->url) . "</span><br>";
	  echo "<span class='part1'><b>" . __("Browser:", 'wp-live-chat-support') . "</b></span><span class='part2'> $browser <img src='" . WPLC_PLUGIN_URL . "images/$browser_image' alt='$browser' title='$browser' align='absmiddle' /></span>";

	  echo (apply_filters("wplc_filter_advanced_info","", sanitize_text_field($result->id), sanitize_text_field($result->name), $result));
	  echo "</div>";
      echo "</div>";
	
    echo "<div class='admin_chat_box'><div class='admin_chat_box_inner' id='admin_chat_box_area_" . intval($result->id) . "'>";
    if ( isset( $_GET['action'] ) && 'history' === $_GET['action'] ) {
      echo wplc_return_chat_messages($cid, true);
    }
    echo "</div>";
	
	 if ( isset( $_GET['action'] ) && $_GET['action'] === "ac" ) {
       echo"<div class='admin_chat_box_inner_bottom'>" . wplc_return_chat_response_box($cid, $result) . "</div>";
	 }

      echo "</div>";

	  echo "</div>";

      if ($result->status != 1) {

          do_action("wplc_hook_admin_below_chat_box",$result);
      }
	
	}else {
		wp_die(__("Invalid Chat ID", 'wp-live-chat-support'));
	}

}

function wplc_return_chat_response_box($cid, $chat_data = false) {
  $ret = "<div class=\"chat_response_box\">";
  $ret .= apply_filters("wplc_filter_typing_control_div","");
  $ret .= "<input type='text' name='wplc_admin_chatmsg' id='wplc_admin_chatmsg' value='' placeholder='" . __("type here...", 'wp-live-chat-support') . "' />";

  $ret .= apply_filters("wplc_filter_chat_upload","");

  $ret .= "<input id='wplc_admin_cid' type='hidden' value='".esc_attr($cid)."' />";
  $ret .= "<input id='wplc_admin_send_msg' type='button' value='" . __("Send", 'wp-live-chat-support') . "' style=\"display:none;\" />";
  $ret .= "</div>";
  return $ret;
}

function wplc_return_admin_chat_javascript($cid) {
  $ajax_nonce = wp_create_nonce("wplc");
  $wplc_settings = wplc_get_options();

  wp_register_script('wplc-admin-chat-server', plugins_url('js/wplc_server.js', __FILE__), array(), WPLC_PLUGIN_VERSION, true);
  wp_enqueue_script('wplc-admin-chat-server');

  wp_localize_script( 'wplc-admin-chat-server', 'wplc_datetime_format', array(
    'date_format' => get_option( 'date_format' ),
    'time_format' => get_option( 'time_format' ),
  ));

  global $admin_chat_data;
  if (!$admin_chat_data) {
    $cdata = wplc_get_chat_data($cid, __LINE__);
  } else {
    /* copy the stored admin chat data variable - more efficient */
    $cdata = $admin_chat_data;
  }

  if (empty($cdata)) {
    wp_die(__("Invalid Chat ID", 'wp-live-chat-support'));
  }

  $other = maybe_unserialize($cdata->other);

  if ($wplc_settings['wplc_use_node_server']) {
    if (isset($other['socket']) && ($other['socket'] == true || $other['socket'] == "true")) {
      wp_localize_script('wplc-admin-chat-server', 'tcx_api_key',  wplc_node_server_token_get());
    }
  }

  /**
  * Create a JS object for all Agent ID's and Gravatar MD5's
  */
  $user_array = wplc_get_agent_users();
  $a_array = array();
  foreach ($user_array as $user) {
    $a_array[$user->ID] = array();
    $a_array[$user->ID]['name'] = sanitize_text_field($user->display_name);
    $a_array[$user->ID]['md5'] = md5( $user->user_email );
  }
  wp_localize_script('wplc-admin-chat-server', 'wplc_agent_data', $a_array);

  /**
  * Get the CURRENT agent's data
  */
  if (isset($_GET['aid'])) {
    $agent_data = get_user_by('ID', intval($_GET['aid']));
    wp_localize_script('wplc-admin-chat-server', 'wplc_admin_agent_name', sanitize_text_field($agent_data->display_name));
    wp_localize_script('wplc-admin-chat-server', 'wplc_admin_agent_email', md5($agent_data->user_email));
  } else {
    $agent_data = get_user_by('ID', intval(get_current_user_id()));
    wp_localize_script('wplc-admin-chat-server', 'wplc_admin_agent_name', sanitize_text_field($agent_data->display_name));
    wp_localize_script('wplc-admin-chat-server', 'wplc_admin_agent_email', md5($agent_data->user_email));
  }

  if (!empty($_REQUEST['action']) && $_REQUEST['action']=='history') {
    // do not load if displaying chat history
  } else {
    wp_register_script('wplc-admin-chat-js', plugins_url('js/wplc_u_admin_chat.js', __FILE__), array('wplc-admin-chat-server'), WPLC_PLUGIN_VERSION, false);
    wp_enqueue_script('wplc-admin-chat-js');
  }


  $wplc_theme = wplc_get_theme();

  switch($wplc_theme) {
    case 'theme-default':
      wp_register_style('wplc-theme-palette-default', plugins_url('/css/themes/theme-default.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
      wp_enqueue_style('wplc-theme-palette-default');
      break;
    case 'theme-1':
    case 'theme-2':
    case 'theme-3':
    case 'theme-4':
    case 'theme-5':
      $tid = substr($wplc_theme,-1,1);
      wp_register_style('wplc-theme-palette-'.$tid, plugins_url('/css/themes/theme-'.$tid.'.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
      wp_enqueue_style('wplc-theme-palette-'.$tid);
      break;
    case 'theme-6':
      /* custom */
      /* handled elsewhere */
      break;
  }

  if (isset($cdata->agent_id)) {
    $wplc_agent_data = get_user_by( 'id', intval( $cdata->agent_id ) );
  }

  $wplc_chat_detail = array(
    'name' => $wplc_settings['wplc_show_name'],
    'avatar' => $wplc_settings['wplc_show_avatar'],
    'date' => $wplc_settings['wplc_show_date'],
    'time' => $wplc_settings['wplc_show_time']
  );

  wp_enqueue_script('wplc-admin-chat-js');
  wp_localize_script( 'wplc-admin-chat-js', 'wplc_show_chat_detail', $wplc_chat_detail );

  if (!empty($wplc_agent_data)) {
    wp_localize_script( 'wplc-admin-chat-js', 'wplc_agent_name', sanitize_text_field($wplc_agent_data->display_name) );
    wp_localize_script( 'wplc-admin-chat-js', 'wplc_agent_email', md5( $wplc_agent_data->user_email ) );
  } else {
    wp_localize_script( 'wplc-admin-chat-js', 'wplc_agent_name', null );
    wp_localize_script( 'wplc-admin-chat-js', 'wplc_agent_email', null );
  }

  wp_localize_script('wplc-admin-chat-js', 'wplc_chat_name', sanitize_text_field($cdata->name));
  wp_localize_script('wplc-admin-chat-js', 'wplc_chat_email', md5($cdata->email));

  if (class_exists("WP_REST_Request")) {
    wp_localize_script('wplc-admin-chat-js', 'wplc_restapi_enabled', array('value'=>true));
    wp_localize_script('wplc-admin-chat-js', 'wplc_restapi_token', get_option('wplc_api_secret_token'));
    wp_localize_script('wplc-admin-chat-js', 'wplc_restapi_endpoint', rest_url('wp_live_chat_support/v1'));
  } else {
    wp_localize_script('wplc-admin-chat-js', 'wplc_restapi_enabled', array('value'=>false));
  }

  $src = wplc_get_admin_picture();
  if ($src) {
    $image = "<img src=" . $src . " width='20px' id='wp-live-chat-2-img'/>";
  } else {
    $image = " ";
  }

  $admin_pic = $image;
  wp_localize_script('wplc-admin-chat-js', 'wplc_localized_string_is_typing', __("is typing...",'wp-live-chat-support'));
  wp_localize_script('wplc-user-script', 'wplc_localized_string_admin_name', apply_filters( 'wplc_filter_admin_name', 'Admin' ) );
  wp_localize_script('wplc-admin-chat-js', 'wplc_ajax_nonce', $ajax_nonce);
  wp_localize_script('wplc-admin-chat-js', 'admin_pic', $admin_pic);
  wp_localize_script('wplc-admin-chat-js', 'wplc_ding_file', apply_filters('wplc_filter_message_sound', ''));

  $extra_data = apply_filters("wplc_filter_admin_javascript",array());
  wp_localize_script('wplc-admin-chat-js', 'wplc_extra_data', $extra_data);
  wp_localize_script('wplc-admin-chat-js', 'wplc_enable_ding', array('value'=>boolval($wplc_settings['wplc_enable_msg_sound'])));

  $ajax_url = admin_url('admin-ajax.php');
  wp_localize_script('wplc-admin-chat-js', 'wplc_ajaxurl', apply_filters("wplc_filter_ajax_url",$ajax_url));
  wp_localize_script('wplc-admin-chat-js', 'wplc_home_ajaxurl', $ajax_url);
  wp_localize_script('wplc-admin-chat-js', 'wplc_url', admin_url('admin.php?page=wplivechat-menu&action=ac&cid=' . $cid));

  $wplc_string1 = __("User has opened the chat window", 'wp-live-chat-support');
  $wplc_string2 = __("User has minimized the chat window", 'wp-live-chat-support');
  $wplc_string3 = __("User has maximized the chat window", 'wp-live-chat-support');
  $wplc_string4 = __("The chat has been ended", 'wp-live-chat-support');
  wp_localize_script('wplc-admin-chat-js', 'wplc_string1', $wplc_string1);
  wp_localize_script('wplc-admin-chat-js', 'wplc_string2', $wplc_string2);
  wp_localize_script('wplc-admin-chat-js', 'wplc_string3', $wplc_string3);
  wp_localize_script('wplc-admin-chat-js', 'wplc_string4', $wplc_string4);
  wp_localize_script('wplc-admin-chat-js', 'wplc_cid', $cid);
  do_action("wplc_hook_admin_chatbox_javascript");
}

function wplc_activate() {
  wplc_set_defaults();
  wplc_check_guid();
  wplc_handle_db();

  $wplc_settings = wplc_get_options(true);
  if (current_user_can('manage_options')) {
    global $user_ID;
    $user = new WP_User($user_ID);
    foreach ($user->roles as $urole) {
      if ($urole == "administrator") {
        $admins = get_role('administrator');
        $admins->add_cap('edit_wplc_quick_response');
        $admins->add_cap('edit_wplc_quick_response');
        $admins->add_cap('edit_other_wplc_quick_response');
        $admins->add_cap('publish_wplc_quick_response');
        $admins->add_cap('read_wplc_quick_response');
        $admins->add_cap('read_private_wplc_quick_response');
        $admins->add_cap('delete_wplc_quick_response');
      }
    }
  }
  $wplc_settings = wplc_cleanup_old_options($wplc_settings);
  update_option('WPLC_SETTINGS', $wplc_settings);
  $uid = get_current_user_id();
  wplc_set_agent_accepting($uid, true);
  update_user_meta($uid, 'wplc_ma_agent', 1);
  wplc_update_agent_time($uid);
  
  $admins = get_role('administrator');
  if( $admins !== null ) { $admins->add_cap('wplc_ma_agent'); }
  add_option("WPLC_HIDE_CHAT", "true");
  do_action("wplc_activate_hook");
}


/**
 * Activation of the plugin - set the accepting chat variable to true
 * @return void
 * @since  1.0.00
 */
if (!function_exists("wplc_choose_activate")) {
	register_activation_hook(__FILE__, 'wplc_choose_activate');
	function wplc_choose_activate( $networkwide ) {
    wplc_set_agent_accepting(get_current_user_id(),true);
    wplc_mrg_update_db( $networkwide ); //Run update db
	}
}

/**
 * Deactivate of the plugin - set the accepting chat variable to false
 * @return void
 * @since  1.0.00
 */
if (!function_exists("wplc_choose_deactivate")) {
	register_deactivation_hook(__FILE__, 'wplc_choose_deactivate');
	function wplc_choose_deactivate() {
    wplc_set_agent_accepting(get_current_user_id(),false);
	}
}

function wplc_handle_db() {
    global $wpdb;
    global $wplc_tblname_chats;
    global $wplc_tblname_msgs;
    global $wplc_tblname_offline_msgs;

    $sql = "
        CREATE TABLE " . $wplc_tblname_chats . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          timestamp datetime NOT NULL,
          name varchar(700) NOT NULL,
          email varchar(700) NOT NULL,
          ip varchar(700) NOT NULL,
          status int(11) NOT NULL,
          session varchar(100) NOT NULL,
          url varchar(700) NOT NULL,
          last_active_timestamp datetime NOT NULL,
          agent_id INT(11) NOT NULL,
          other LONGTEXT NOT NULL,
          rel varchar(40) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
	
	

    $sql = '
        CREATE TABLE ' . $wplc_tblname_msgs . ' (
          id int(11) NOT NULL AUTO_INCREMENT,
          chat_sess_id int(11) NOT NULL,
          msgfrom varchar(150) CHARACTER SET utf8 NOT NULL,
          msg LONGTEXT CHARACTER SET utf8 NOT NULL,
          timestamp datetime NOT NULL,
          status INT(3) NOT NULL,
          originates INT(3) NOT NULL,
          other LONGTEXT NOT NULL,
          rel varchar(40) NOT NULL,
          afrom INT(10) NOT NULL,
          ato INT(10) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    @dbDelta($sql);

    /* check for previous versions containing 'from' instead of 'msgfrom' */
    $results = $wpdb->get_results("DESC $wplc_tblname_msgs");
    $founded = 0;
    foreach ($results as $row ) {
        if ($row->Field == "from") {
            $founded++;
        }
    }

    if ($founded>0) { $wpdb->query("ALTER TABLE ".$wplc_tblname_msgs." CHANGE `from` `msgfrom` varchar(150)"); }


    $sql2 = "
        CREATE TABLE " . $wplc_tblname_offline_msgs . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          timestamp datetime NOT NULL,
          name varchar(700) NOT NULL,
          email varchar(700) NOT NULL,
          message varchar(700) NOT NULL,
          ip varchar(700) NOT NULL,
          user_agent varchar(700) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    @dbDelta($sql2);

    add_option("wplc_db_version", WPLC_PLUGIN_VERSION);
    update_option("wplc_db_version", WPLC_PLUGIN_VERSION);
}

function wplc_add_user_stylesheet() {
  $show_chat_contents = wplc_display_chat_contents();
  $wplc_settings = wplc_get_options();

  if ($show_chat_contents) {
    if ($wplc_settings['wplc_enable_font_awesome']) {
      wp_register_style( 'wplc-font-awesome', plugins_url( '/css/fontawesome-all.min.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
      wp_enqueue_style( 'wplc-font-awesome' );
    }
    wp_register_style('wplc-style', plugins_url('/css/wplcstyle.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style('wplc-style');

    if (!empty($wplc_settings['wplc_elem_trigger_id'])) {
      $wplc_elem_style_prefix = ".";
      if (!empty($wplc_settings['wplc_elem_trigger_type'])) {
        $wplc_elem_style_prefix = "#";
      }
      $wplc_elem_inline_style = $wplc_elem_style_prefix.stripslashes($wplc_settings['wplc_elem_trigger_id']).":hover { cursor:pointer; }";
      wp_add_inline_style( 'wplc-style', stripslashes( $wplc_elem_inline_style ) );
    }   

    // Serve the icon up over HTTPS if needs be
    $icon = plugins_url('images/iconRetina.png', __FILE__);
    $close_icon = plugins_url('images/iconCloseRetina.png', __FILE__);

    $bg='';
    $bg_string = '';
    if (!empty($wplc_settings['wplc_settings_bg'])) {
      $bg = sanitize_text_field($wplc_settings['wplc_settings_bg']); 
    }
    if ($bg) {
      $bg = plugins_url('images/bg/'.htmlentities($bg), __FILE__);
      $bg_string = "#wp-live-chat-4 { background:url('".htmlentities($bg)."') repeat; background-size: cover; }";
    } else { 
      $bg_string = "#wp-live-chat-4 { background-color: #fff; }"; 
    }
    if ($wplc_settings['wplc_chatbox_height']!=70) {
      if ($wplc_settings['wplc_chatbox_height']==0) {
        $bg_string.= "#wp-live-chat-4 { height: ".$wplc_settings['wplc_chatbox_absolute_height']."px !important; }"; 
      } else {
        $bg_string.= "#wp-live-chat-4 { height: ".$wplc_settings['wplc_chatbox_height']."% !important; }"; 
      }
    }    

    if (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ){ $icon = preg_replace('/^http:\/\//', 'https:\/\/', $icon); }
    if (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ){ $close_icon = preg_replace('/^http:\/\//', 'https:\/\/', $close_icon); }
    $icon = apply_filters("wplc_filter_chaticon", $icon);
    $close_icon = apply_filters("wplc_filter_chaticon_close", $close_icon);
    $wplc_elem_inline_style = "#wp-live-chat-header { background:url('$icon') no-repeat; background-size: cover; }  #wp-live-chat-header.active { background:url('$close_icon') no-repeat; background-size: cover; } $bg_string";
    wp_add_inline_style( 'wplc-style', stripslashes( $wplc_elem_inline_style ) );

    $wplc_theme = wplc_get_theme();
    switch ($wplc_theme) {
      case 'theme-default':
        wp_register_style('wplc-theme-palette-default', plugins_url('/css/themes/theme-default.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-palette-default');
        break;
      case 'theme-1':
      case 'theme-2':
      case 'theme-3':
      case 'theme-4':
      case 'theme-5':
        $tid = substr($wplc_theme,-1,1);
        wp_register_style('wplc-theme-palette-'.$tid, plugins_url('/css/themes/theme-'.$tid.'.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-palette-'.$tid);
        break;
      case 'theme-6':
        /* custom */
        /* handled elsewhere */
        break;
    }

    switch($wplc_settings['wplc_newtheme']) {
      case 'theme-1':
        wp_register_style('wplc-theme-classic', plugins_url('/css/themes/classic.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-classic');
        break;

      default:
        wp_register_style('wplc-theme-modern', plugins_url('/css/themes/modern.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-modern');
        break;
    }

    switch($wplc_settings["wplc_settings_align"]) {
      case 1:
        wp_register_style('wplc-theme-position', plugins_url('/css/themes/position-bottom-left.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-position');
        break;
      case 2:
        wp_register_style('wplc-theme-position', plugins_url('/css/themes/position-bottom-right.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-position');
        break;
      case 3:
        wp_register_style('wplc-theme-position', plugins_url('/css/themes/position-left.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-position');
        break;
      case 4:
        wp_register_style('wplc-theme-position', plugins_url('/css/themes/position-right.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-position');
        break;
      default:
        wp_register_style('wplc-theme-position', plugins_url('/css/themes/position-bottom-right.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
        wp_enqueue_style('wplc-theme-position');
        break;
    }

    // Gutenberg template styles - user
    wp_register_style( 'wplc-gutenberg-template-styles-user', plugins_url( '/includes/blocks/wplc-chat-box/wplc_gutenberg_template_styles.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style( 'wplc-gutenberg-template-styles-user' );
  }

}

add_action( 'init', 'wplc_online_check_script', 10 );
/**
 * Load the JS that allows us to integrate into the WP Heartbeat
 * @return void
 */
function wplc_online_check_script() {
  if (wplc_user_is_agent()) {
    $ajax_nonce = wp_create_nonce("wplc");
    wp_register_script( 'wplc-heartbeat', plugins_url( 'js/wplc_heartbeat.js', __FILE__ ), array( 'jquery' ), WPLC_PLUGIN_VERSION, true );
    wp_enqueue_script( 'wplc-heartbeat' );
    wp_localize_script( 'wplc-heartbeat', 'wplc_transient_nonce', $ajax_nonce );
    $wplc_ajax_url = apply_filters("wplc_filter_ajax_url", admin_url('admin-ajax.php'));
    wp_localize_script('wplc-heartbeat', 'wplc_ajaxurl', $wplc_ajax_url);
  }
}

/**
 * Heartbeat integrations
 *
 */
add_filter( 'heartbeat_received', 'wplc_heartbeat_receive', 10, 2 );
add_filter( 'heartbeat_nopriv_received', 'wplc_heartbeat_receive', 10, 2 );
function wplc_heartbeat_receive( $response, $data ) {
  if ( array_key_exists('client',$data) && $data['client'] == 'wplc_heartbeat' ) {
    if (wplc_user_is_agent()) {
      wplc_update_agent_time();
    }
  }
  return $response;
}

/**
 * Loads the admin stylesheets for the chat dashboard and settings pages
 * @return void
 */
function wplc_add_admin_stylesheet() {
  wp_register_style( 'wplc-ace-styles', plugins_url( '/css/ace.min.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
  wp_enqueue_style( 'wplc-ace-styles' );

  wp_register_style( 'wplc-fontawesome-iconpicker', plugins_url( '/css/fontawesome-iconpicker.min.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
  wp_enqueue_style( 'wplc-fontawesome-iconpicker' );

  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_use_node_server'] && (!isset($_GET['action']) || $_GET['action'] != "history") ) {
    //Using node, remote styles 
    //Using node, remote scripts
    if ($wplc_settings['wplc_enable_all_admin_pages']) {
      /* Run admin JS on all admin pages */
      wplc_admin_remote_dashboard_styles();
    } else {
      /* Only run admin JS on the chat dashboard page */
      if ( isset( $_GET['page'] ) && $_GET['page'] == 'wplivechat-menu' && !isset( $_GET['action'] ) ) {
        wplc_admin_remote_dashboard_styles();
      }
    }

    wp_register_style( 'wplc-admin-remote-addition-styles', plugins_url( '/css/remote_dash_styles.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION );
    wp_enqueue_style( 'wplc-admin-remote-addition-styles' );
  }

  //Special new check to see if we need to add the node history styling
  if ($wplc_settings['wplc_use_node_server'] && isset($_GET['action']) && $_GET['action'] == 'history') {
    wp_register_style( 'wplc-admin-node-history-styles', plugins_url( '/css/node_history_styles.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style( 'wplc-admin-node-history-styles' );
  }

  if (isset($_GET['page']) && $_GET['page'] == 'wplivechat-menu' && isset($_GET['action']) && ($_GET['action'] == "ac" || $_GET['action'] == "history" ) ) {
    wp_register_style('wplc-admin-chat-box-style', plugins_url('/css/admin-chat-box-style.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style('wplc-admin-chat-box-style');
  }

  wp_register_style( 'wplc-font-awesome', plugins_url('css/fontawesome-all.min.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
  wp_enqueue_style( 'wplc-font-awesome' );

  if (isset($_GET['page']) && ($_GET['page'] == 'wplivechat-menu' || $_GET['page'] == 'wplivechat-menu-settings' || $_GET['page'] == 'wplivechat-menu-offline-messages' || $_GET['page'] == 'wplivechat-menu-history' || $_GET['page'] == 'wplivechat-menu-missed-chats' || $_GET['page'] == 'wplivechat-menu-dashboard')) {
    wp_register_style( 'wplc-jquery-ui', plugins_url('/js/vendor/jquery-ui/jquery-ui.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style( 'wplc-jquery-ui' );

    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-effects-core' );

    // Gutenberg template styles - admin
    wp_register_style( 'wplc-gutenberg-template-styles', plugins_url( '/includes/blocks/wplc-chat-box/wplc_gutenberg_template_styles.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style( 'wplc-gutenberg-template-styles' );

    wp_register_style( 'wplc-admin-styles', plugins_url( '/css/admin_styles.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style( 'wplc-admin-styles' );

    if ($wplc_settings['wplc_use_node_server']) {
      wp_register_style( 'wplc-admin-chat-style', plugins_url( '/css/admin-chat-style.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
      wp_enqueue_style( 'wplc-admin-chat-style' );
    } else {
      wp_register_style( 'wplc-chat-style', plugins_url( '/css/chat-style.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
      wp_enqueue_style( 'wplc-chat-style' );
    }
  }

  // This loads the chat styling on all admin pages as we are using the popout dashboard
  if ($wplc_settings['wplc_use_node_server'] && $wplc_settings['wplc_enable_all_admin_pages']) {
    wp_register_style( 'wplc-admin-chat-style', plugins_url( '/css/admin-chat-style.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style( 'wplc-admin-chat-style' );
  }

  if (isset($_GET['page']) && $_GET['page'] == "wplivechat-menu-support-page") {
    wp_register_style('fontawesome', plugins_url('css/fontawesome-all.min.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style('fontawesome');
    wp_register_style('wplc-support-page-css', plugins_url('css/support-css.css', __FILE__ ), array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style('wplc-support-page-css');
  }

  if (isset($_GET['immersive_mode'])) {
    wp_add_inline_style( 'wplc-admin-style', "#wpcontent { margin-left: 0px !important;} #wpadminbar, #wpfooter, #adminmenumain {display: none !important;}" );
  }
}

if (isset($_GET['page']) && $_GET['page'] == 'wplivechat-menu-settings') {
    add_action('admin_print_scripts', 'wplc_admin_scripts');
}

/**
 * Loads the admin scripts for the chat dashboard and settings pages
 * @return void
 */
function wplc_admin_scripts() {

$gutenberg_default_html = '<!-- Default HTML -->
<div class="wplc_block">
	<span class="wplc_block_logo">{wplc_logo}</span>
	<span class="wplc_block_text">{wplc_text}</span>
	<span class="wplc_block_icon">{wplc_icon}</span>
</div>';
    
    if (isset($_GET['page']) && $_GET['page'] == "wplivechat-menu-settings") {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_script('jquery-ui-tabs');
        wp_register_script('my-wplc-tabs', plugins_url('js/wplc_tabs.js', __FILE__), array('jquery-ui-core'), WPLC_PLUGIN_VERSION, true);
        wp_enqueue_script('my-wplc-tabs');
        wp_enqueue_media();
        wp_register_script('wplc-fontawesome-iconpicker', plugins_url('js/fontawesome-iconpicker.js', __FILE__), array('jquery'), WPLC_PLUGIN_VERSION, true);
        wp_enqueue_script('wplc-fontawesome-iconpicker');
        wp_register_script('wplc-gutenberg', plugins_url('js/wplc_gutenberg.js', __FILE__), array('jquery'), WPLC_PLUGIN_VERSION, true);
        wp_enqueue_script('wplc-gutenberg');
        wp_localize_script( 'wplc-gutenberg', 'default_html', $gutenberg_default_html );
    }
}

/**
 * Loads basic version's settings page
 * @return void
 */
function wplc_admin_settings_layout() {
    wplc_settings_page();
}

/**
 * Loads the dashboard page
 * @return void
 */
function wplc_admin_dashboard_layout() {
    include 'includes/dashboard_page.php';
}

add_action("wplc_hook_history_draw_area","wplc_hook_control_history_draw_area",10,1);
/**
 * Display normal history page
 * @param  int   $cid Chat ID
 * @return void
 * @since  6.1.00
 */
function wplc_hook_control_history_draw_area($cid) {
    wplc_draw_chat_area($cid);
}

/**
 * What to display for the chat history
 * @param  int   $cid Chat ID
 * @return void
 * @since  6.1.00
 */
function wplc_admin_view_chat_history($cid) {
  do_action("wplc_hook_history_draw_area",$cid);
}


add_action( 'wplc_hook_admin_menu_layout_display' , 'wplc_hook_control_history_get_control', 1, 3);
/**
 * Control history GET calls
 * @param  string $action The GET action
 * @param  int    $cid    The chat id
 * @param  int    $aid    AID
 * @return void
 * @since  6.1.00
 */
function wplc_hook_control_history_get_control($action,$cid,$aid) {

  if ($action == 'history') {
      if (!isset($_GET['wplc_history_nonce']) || !wp_verify_nonce($_GET['wplc_history_nonce'], 'wplc_history_nonce')){
          wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
      }
      wplc_admin_view_chat_history(sanitize_text_field($cid));
  } else if ($action == 'download_history'){
    
  }


}


add_action("wplc_hook_chat_history","wplc_hook_control_chat_history");
/**
 * Renders the chat history content
 * @return string
 */
function wplc_hook_control_chat_history() {
  if (is_admin()) {
    global $wpdb;
    global $wplc_tblname_chats;
    global $wplc_tblname_msgs;
    if (isset($_GET['wplc_action']) && $_GET['wplc_action'] == 'remove_cid') {
      if (isset($_GET['cid'])) {
        if (isset($_GET['wplc_confirm'])) {
          //Confirmed - delete
          if (!isset($_GET['wplc_history_nonce']) || !wp_verify_nonce($_GET['wplc_history_nonce'], 'wplc_history_nonce')) {
            wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
          }
          $wplc_was_error = false;
          $wpdb->query($wpdb->prepare("DELETE FROM $wplc_tblname_chats WHERE `id` = %d", intval($_GET['cid'])));
          $wplc_was_error = $wpdb->last_error || $wplc_was_error;
          $wpdb->query($wpdb->prepare("DELETE FROM $wplc_tblname_msgs WHERE `chat_sess_id` = %d", intval($_GET['cid'])));
          $wplc_was_error = $wpdb->last_error || $wplc_was_error;

          if ($wplc_was_error) {
            echo "<div class='update-nag' style='margin-top: 0px;margin-bottom: 5px;'>".__("Error: Could not delete chat", 'wp-live-chat-support')."<br></div>";
          } else {
            echo "<div class='update-nag' style='margin-top: 0px;margin-bottom: 5px;border-color:#67d552;'>".__("Chat Deleted", 'wp-live-chat-support')."<br></div>";
          }
        } else {
          //Prompt
          $hist_nonce = wp_create_nonce('wplc_history_nonce');
          echo "<div class='update-nag' style='margin-top: 0px;margin-bottom: 5px;'>".__("Are you sure you would like to delete this chat?", 'wp-live-chat-support');
          echo "<br><a class='button' href='?page=wplivechat-menu-history&wplc_action=remove_cid&cid=".esc_attr( $_GET['cid'] );
          echo "&wplc_confirm=1&wplc_history_nonce=".$hist_nonce."'>".__("Yes", 'wp-live-chat-support')."</a> <a class='button' href='?page=wplivechat-menu-history'>";
          echo __("No", 'wp-live-chat-support')."</a></div>";
        }     
      }
    }

    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $limit = 20; // number of rows in page
    $offset = ( $pagenum - 1 ) * $limit;
    $total = $wpdb->get_var( "SELECT COUNT(`id`) FROM $wplc_tblname_chats" );
    $num_of_pages = ceil( $total / $limit );

    $results = $wpdb->get_results($wpdb->prepare("
      SELECT * FROM $wplc_tblname_chats
      WHERE `name` NOT LIKE 'agent-to-agent chat'
      AND (`status` = 1 OR `status` = 2 OR `status` = 3 OR `status` = 6 OR `status` = 8) 
      ORDER BY `timestamp` DESC
      LIMIT %d OFFSET %d
      ", $limit, $offset)
    );
    echo "<form method=\"post\" >
  <input type=\"submit\" value=\"".__('Delete History', 'wp-live-chat-support')."\" class='button' name=\"wplc-delete-chat-history\" /><br /><br />
  </form>

  <table class=\"wp-list-table wplc_list_table widefat fixed \" cellspacing=\"0\">
  <thead>
  <tr>
  <th scope='col' id='wplc_id_colum' class='manage-column column-id sortable desc'  style=''><span>" . __("Date", 'wp-live-chat-support') . "</span></th>
  <th scope='col' id='wplc_name_colum' class='manage-column column-name_title sortable desc'  style=''><span>" . __("Name", 'wp-live-chat-support') . "</span></th>
  <th scope='col' id='wplc_email_colum' class='manage-column column-email' style=\"\">" . __("Email", 'wp-live-chat-support') . "</th>
  <th scope='col' id='wplc_url_colum' class='manage-column column-url' style=\"\">" . __("URL", 'wp-live-chat-support') . "</th>
  <th scope='col' id='wplc_status_colum' class='manage-column column-status'  style=\"\">" . __("Status", 'wp-live-chat-support') . "</th>
  <th scope='col' id='wplc_action_colum' class='manage-column column-action sortable desc'  style=\"\"><span>" . __("Action", 'wp-live-chat-support') . "</span></th>
  </tr>
  </thead>
  <tbody id=\"the-list\" class='list:wp_list_text_link'>";
    if (!$results) {
      echo "<tr><td></td><td>" . __("No chats available at the moment", 'wp-live-chat-support') . "</td></tr>";
    } else {
      foreach ($results as $result) {
        unset($trstyle);
        unset($actions);
        $tcid = sanitize_text_field( $result->id );
        $hist_nonce = wp_create_nonce('wplc_history_nonce');
        $url = admin_url('admin.php?page=wplivechat-menu&action=history&cid='.$tcid."&wplc_history_nonce=".$hist_nonce);
        $url2 = admin_url('admin.php?page=wplivechat-menu&action=download_history&type=csv&cid='.$tcid."&wplc_history_nonce=".$hist_nonce);
        $url3 = "?page=wplivechat-menu-history&wplc_action=remove_cid&cid=".$tcid;
        $actions = "<a href='$url' class='button' title='".__('View Chat History', 'wp-live-chat-support')."' target='_BLANK' id=''>";
        $actions.="<i class='fa fa-eye'></i></a> <a href='$url2' class='button' title='".__('Download Chat History', 'wp-live-chat-support')."' target='_BLANK' id=''>";
        $actions.="<i class='fa fa-download'></i></a> <a href='$url3' class='button'><i class='far fa-trash-alt'></i></a>";
        $trstyle = "style='height:30px;'";

        echo "<tr id=\"record_" . $tcid . "\" $trstyle>";
        echo "<td class='chat_id column-chat_d'>" . date("Y-m-d H:i:s", current_time( strtotime( $result->timestamp ) ) ) . "</td>";
        echo "<td class='chat_name column_chat_name' id='chat_name_" . $tcid . "'><img src=\"//www.gravatar.com/avatar/" . md5($result->email) . "?s=40&d=mm\" align=\"absmiddle\"/> " . sanitize_text_field($result->name) . "</td>";
        echo "<td class='chat_email column_chat_email' id='chat_email_" . $tcid . "'><a href='mailto:" . esc_attr($result->email) . "' title='Email " . esc_attr($result->email) . "'>" . sanitize_text_field ($result->email) . "</a></td>";
        echo "<td class='chat_name column_chat_url' id='chat_url_" . $tcid . "'>" . esc_url($result->url) . "</td>";
        echo "<td class='chat_status column_chat_status' id='chat_status_" . $tcid . "'><strong>" . wplc_return_status($result->status) . "</strong></td>";
        echo "<td class='chat_action column-chat_action' id='chat_action_" . $tcid . "'>$actions</td>";
        echo "</tr>";
      }
    }
    echo "</table>";

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
}

/**
 * Loads the chat history layout
 * @return string
 */
function wplc_admin_history_layout() {
    wplc_stats("history");
    echo"<div class=\"wrap wplc_wrap\"><h2>" . __("History", 'wp-live-chat-support') . "</h2>";
    do_action("wplc_before_history_table_hook");
    do_action("wplc_hook_chat_history");
}


add_action("wplc_hook_chat_missed","wplc_hook_control_missed_chats",10);
/**
 * Loads missed chats contents
 * @return string
 */
function wplc_hook_control_missed_chats() {
  if (function_exists('wplc_admin_display_missed_chats')) { wplc_admin_display_missed_chats(); }
}

/**
 * Loads the missed chats page wrapper
 * @return string
 */
function wplc_admin_missed_chats() {
    wplc_stats("missed");
    echo "<div class=\"wrap wplc_wrap\"><h2>" . __("Missed Chats", 'wp-live-chat-support') . "</h2>";
    do_action("wplc_hook_chat_missed");
}

add_action("wplc_hook_offline_messages_display","wplc_hook_control_offline_messages_display",10);
/**
 * Loads the offline messages page contents
 * @return string
 */
function wplc_hook_control_offline_messages_display() {
   wplc_admin_display_offline_messages(); 
}

/**
 * Control who should see the offline messages
 * @return void
 */
function wplc_admin_offline_messages() {
    wplc_stats("offline_messages");
    echo"<div class=\"wrap wplc_wrap\"><h2>" . __("Offline Messages", 'wp-live-chat-support') . "</h2>";
    do_action("wplc_hook_offline_messages_display");
}

/**
 * Output the offline messages in an HTML table
 * @return void
 */
function wplc_admin_display_offline_messages() {

    global $wpdb;
    global $wplc_tblname_offline_msgs;

    echo "
        <table class=\"wp-list-table wplc_list_table widefat \" cellspacing=\"0\">
            <thead>
                <tr>
                    <th class='manage-column column-id' style='width: 15%'><span>" . __("Date", 'wp-live-chat-support') . "</span></th>
                    <th scope='col' id='wplc_name_colum' class='manage-column column-id' style='width: 10%'><span>" . __("Name", 'wp-live-chat-support') . "</span></th>
                    <th scope='col' id='wplc_email_colum' class='manage-column column-id' style='width: 15%'>" . __("Email", 'wp-live-chat-support') . "</th>
                    <th scope='col' id='wplc_message_colum' class='manage-column column-id' style='width: 40%'>" . __("Message", 'wp-live-chat-support') . "</th>
                    <th scope='col' id='wplc_message_colum' class='manage-column column-id' style='width: 10%'>" . __("Actions", 'wp-live-chat-support') . "</th>
                </tr>
            </thead>
            <tbody id=\"the-list\" class='list:wp_list_text_link'>";

	$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
	$limit = 20; // number of rows in page
	$offset = ( $pagenum - 1 ) * $limit;
	$total = $wpdb->get_var( "SELECT COUNT(`id`) FROM $wplc_tblname_offline_msgs" );
	$num_of_pages = ceil( $total / $limit );

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_offline_msgs ORDER BY `timestamp` DESC LIMIT %d OFFSET %d", $limit, $offset));

    if (!$results) {
        echo "<tr><td></td><td>" . __("You have not received any offline messages.", 'wp-live-chat-support') . "</td></tr>";
    } else {
        foreach ($results as $result) {
            echo "<tr id=\"record_" . intval($result->id) . "\">";
            echo "<td class='chat_id column-chat_d'>" . sanitize_text_field($result->timestamp) . "</td>";
            echo "<td class='chat_name column_chat_name' id='chat_name_" . intval($result->id) . "'><img src=\"//www.gravatar.com/avatar/" . md5($result->email) . "?s=30&d=mm\" align=\"absmiddle\"/> " . sanitize_text_field($result->name) . "</td>";
            echo "<td class='chat_email column_chat_email' id='chat_email_" . intval($result->id) . "'><a href='mailto:" . sanitize_email($result->email) . "' title='Email " . ".$result->email." . "'>" . sanitize_email($result->email) . "</a></td>";
            echo "<td class='chat_name column_chat_url' id='chat_url_" . intval($result->id) . "'>" . nl2br(sanitize_text_field($result->message)) . "</td>";
            echo "<td class='chat_name column_chat_delete'><button class='button wplc_delete_message' title='".__('Delete Message', 'wp-live-chat-support')."' class='wplc_delete_message' mid='".intval($result->id)."'><i class='fa fa-times'></i></button></td>";
            echo "</tr>";
        }
    }

    echo "
            </tbody>
        </table>";

	$page_links = paginate_links( array(
		'base' => add_query_arg( 'pagenum', '%#%' ),
		'format' => '',
		'prev_text' => '&laquo;',
		'next_text' => '&raquo;',
		'total' => $num_of_pages,
		'current' => $pagenum
	) );

	if ( $page_links ) {
		echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0;float:none;text-align:center;">' . $page_links . '</div></div>';
	}
}

/**
 * Loads the settings pages
 * @return string
 */
function wplc_settings_page() {
    include 'includes/settings_page.php';
}

/**
 * Updates chat statistics
 * @param  string $sec Specify which array key of the stats you'd like access to
 * @return void
 */
function wplc_stats($sec) {
    $wplc_stats = get_option("wplc_stats");
    if ($wplc_stats) {
        if (isset($wplc_stats[$sec]["views"])) {
            $wplc_stats[$sec]["views"] = $wplc_stats[$sec]["views"] + 1;
            $wplc_stats[$sec]["last_accessed"] = date("Y-m-d H:i:s");
        } else {
            $wplc_stats[$sec]["views"] = 1;
            $wplc_stats[$sec]["last_accessed"] = date("Y-m-d H:i:s");
            $wplc_stats[$sec]["first_accessed"] = date("Y-m-d H:i:s");
        }


    } else {

        $wplc_stats[$sec]["views"] = 1;
        $wplc_stats[$sec]["last_accessed"] = date("Y-m-d H:i:s");
        $wplc_stats[$sec]["first_accessed"] = date("Y-m-d H:i:s");


    }
    update_option("wplc_stats",$wplc_stats);

}


add_action("wplc_hook_head","wplc_hook_control_head");
/**
 * Deletes the chat history on submission of POST
 * @return bool
 */
function wplc_hook_control_head() {
    if (isset($_POST['wplc-delete-chat-history'])) {
        wplc_del_history();
    }
}

/**
 * Deletes all chat history
 * @return bool
 */
function wplc_del_history(){
    global $wpdb;
    global $wplc_tblname_chats;
    global $wplc_tblname_msgs;
    $wpdb->query("TRUNCATE TABLE $wplc_tblname_chats");
    $wpdb->query("TRUNCATE TABLE $wplc_tblname_msgs");
}

add_filter("wplc_filter_chat_header_extra_attr","wplc_filter_control_chat_header_extra_attr",10,1);
/**
 * Controls if the chat window should popup or not
 * @param  array $wplc_extra_attr Extra chat data passed
 * @return string
 */
function wplc_filter_control_chat_header_extra_attr($wplc_extra_attr) {
  $wplc_settings = wplc_get_options();
  $do_popup=false;
  if ($wplc_settings['wplc_auto_pop_up']>0) {
    if ($wplc_settings['wplc_auto_pop_up_online']) {
      $do_popup=wplc_agent_is_available();
    } else {
      $do_popup=true;
    }
  }
  if ($do_popup && !$wplc_settings['wplc_auto_pop_up_mobile']) {
    if (!class_exists('Mobile_Detect')) {
      require_once (plugin_dir_path(__FILE__) . 'includes/Mobile_Detect.php');
    }
    $wplc_detect_device = new Mobile_Detect;
    $do_popup  = !$wplc_detect_device->isMobile();
  }
  if ($do_popup) {
    $wplc_extra_attr .= " wplc-auto-pop-up=\"".intval($wplc_settings['wplc_auto_pop_up'])."\""; 
  }
  return $wplc_extra_attr;
}

/**
 * Admin side headers used to save settings
 * @return string
 */
function wplc_head() {
  global $wplc_default_settings_array;

  do_action("wplc_hook_head");
  if (isset($_POST['wplc_save_settings'])) {
    if (!isset($_POST['wplc_save_settings_nonce']) || !wp_verify_nonce($_POST['wplc_save_settings_nonce'], 'wplc_save_settings')) {
?>
<div class='notice notice-warning wplc_settings_save_notice'>
<?php _e("You do not have permission to save settings.", 'wp-live-chat-support'); ?>
</div>
<?php
    return false;
    } 

    do_action("wplc_hook_admin_settings_save");
    $wplc_data = $wplc_default_settings_array;
    $wplc_settings = wplc_get_options(); // current settings

    $wplc_data['wplc_allow_agents_set_status'] = wplc_force_bool($_POST, 'wplc_allow_agents_set_status');

    $wplc_data['wplc_include_on_pages'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_include_on_pages')));
    if (!empty($wplc_data['wplc_include_on_pages'])) {
      $wplc_data['wplc_include_on_pages']=implode(',', explode(',', $wplc_data['wplc_include_on_pages']));
    }
    $wplc_data['wplc_exclude_from_pages'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_exclude_from_pages')));
    if (!empty($wplc_data['wplc_exclude_from_pages'])) {
      $wplc_data['wplc_exclude_from_pages']=implode(',', explode(',', $wplc_data['wplc_exclude_from_pages']));
    }
    if (isset($_POST['wplc_exclude_post_types']) && ! empty($_POST['wplc_exclude_post_types'])) {
      $wplc_data['wplc_exclude_post_types'] = array();
      foreach ( $_POST['wplc_exclude_post_types'] as $post_type ) { $wplc_data['wplc_exclude_post_types'][] = sanitize_text_field($post_type); } 
    }
    $wplc_data['wplc_exclude_home'] = wplc_force_bool($_POST, 'wplc_exclude_home');
    $wplc_data['wplc_exclude_archive'] = wplc_force_bool($_POST, 'wplc_exclude_archive');

    $wplc_data['wplc_enable_transcripts'] = wplc_force_bool($_POST, 'wplc_enable_transcripts');
    $wplc_data['wplc_send_transcripts_to'] = trim(stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_send_transcripts_to'))));
    if (empty($wplc_data['wplc_send_transcripts_to'])) {
      $wplc_data['wplc_send_transcripts_to']=$wplc_default_settings_array['wplc_send_transcripts_to'];
    }
    $wplc_data['wplc_send_transcripts_when_chat_ends'] = wplc_force_bool($_POST, 'wplc_send_transcripts_when_chat_ends');

    $wplc_data['wplc_et_email_header'] = trim(stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_et_email_header'))));
    if (empty($wplc_data['wplc_et_email_header'])) {
      $wplc_data['wplc_et_email_header']=$wplc_default_settings_array['wplc_et_email_header'];
    }

    $wplc_data['wplc_et_email_footer'] = trim(stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_et_email_footer'))));
    if (empty($wplc_data['wplc_et_email_footer'])) {
      $wplc_data['wplc_et_email_footer']=$wplc_default_settings_array['wplc_et_email_footer'];
    }

    $wplc_data['wplc_et_email_body'] = trim(wp_filter_post_kses(wplc_force_string($_POST, 'wplc_et_email_body')));
    if (empty($wplc_data['wplc_et_email_body'])) {
      $wplc_data['wplc_et_email_body']=$wplc_default_settings_array['wplc_et_email_body'];
    }

    $wplc_data['wplc_quick_response_orderby'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_quick_response_orderby', $wplc_settings)));
    $wplc_data['wplc_quick_response_order'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_quick_response_order', $wplc_settings)));
    $wplc_data['wplc_enable_voice_notes_on_admin'] = wplc_force_bool($_POST, 'wplc_enable_voice_notes_on_admin');
    $wplc_data['wplc_enable_voice_notes_on_visitor'] = wplc_force_bool($_POST, 'wplc_enable_voice_notes_on_visitor');

    $wplc_data['wplc_settings_align'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_align', $wplc_settings)));
    $wplc_data['wplc_settings_bg'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_bg', $wplc_settings)));
    $wplc_data['wplc_environment'] = wplc_force_int($_POST, 'wplc_environment', $wplc_settings, 0, 4);
    $wplc_data['wplc_settings_fill'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_fill', $wplc_settings)));
    $wplc_data['wplc_settings_font'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_font', $wplc_settings)));
    $wplc_data['wplc_settings_color1'] = str_replace('#','',stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_color1', $wplc_settings))));
    $wplc_data['wplc_settings_color2'] = str_replace('#','',stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_color2', $wplc_settings))));
    $wplc_data['wplc_settings_color3'] = str_replace('#','',stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_color3', $wplc_settings))));
    $wplc_data['wplc_settings_color4'] = str_replace('#','',stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_settings_color4', $wplc_settings))));
    $wplc_data['wplc_settings_enabled'] = wplc_force_int($_POST, 'wplc_settings_enabled', $wplc_settings, 1, 0, 1);
    $wplc_data['wplc_powered_by_link'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_powered_by_link', $wplc_settings)));
    $wplc_data['wplc_auto_pop_up'] = wplc_force_int($_POST, 'wplc_auto_pop_up', $wplc_default_settings_array, 0, 2);
    $wplc_data['wplc_auto_pop_up_online'] = wplc_force_bool($_POST ,'wplc_auto_pop_up_online');
    $wplc_data['wplc_auto_pop_up_mobile'] = wplc_force_bool($_POST ,'wplc_auto_pop_up_mobile');
    $wplc_data['wplc_enable_encryption'] = wplc_force_bool($_POST ,'wplc_enable_encryption');
    $wplc_data['wplc_use_geolocalization'] = wplc_force_bool($_POST ,'wplc_use_geolocalization');

    $wplc_data['wplc_require_user_info'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_require_user_info', $wplc_settings)));
    if (!in_array($wplc_data['wplc_require_user_info'],array('both','none','email','name'))) { $wplc_data['wplc_require_user_info'] ='both'; }

    $wplc_data['wplc_user_default_visitor_name'] = substr(stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_user_default_visitor_name'))),0,25);

    $wplc_data['wplc_loggedin_user_info'] = wplc_force_bool($_POST, 'wplc_loggedin_user_info');
    $wplc_data['wplc_enabled_on_mobile'] = wplc_force_bool($_POST, 'wplc_enabled_on_mobile');
    $wplc_data['wplc_user_alternative_text'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_user_alternative_text', $wplc_settings)));
    if (empty($wplc_data['wplc_user_alternative_text'])) {$wplc_data['wplc_user_alternative_text'] = $wplc_default_settings_array['wplc_user_alternative_text'];}

    $wplc_data['wplc_display_to_loggedin_only'] = wplc_force_bool($_POST, 'wplc_display_to_loggedin_only');
    $wplc_data['wplc_redirect_to_thank_you_page'] = wplc_force_bool($_POST, 'wplc_redirect_to_thank_you_page');
    $wplc_data['wplc_redirect_thank_you_url'] = esc_url(wplc_force_url($_POST, 'wplc_redirect_thank_you_url'));
    $wplc_data['wplc_disable_emojis'] = wplc_force_bool($_POST, 'wplc_disable_emojis');
    $wplc_data['wplc_chatbox_height'] = wplc_force_int($_POST, 'wplc_chatbox_height', $wplc_default_settings_array, 0, 80);
    $wplc_data['wplc_chatbox_absolute_height'] = wplc_force_int($_POST, 'wplc_chatbox_absolute_height', $wplc_default_settings_array, 100, 1000);
    $wplc_data['wplc_record_ip_address'] = "0";
    $wplc_data['wplc_enable_msg_sound'] = wplc_force_bool($_POST, 'wplc_enable_msg_sound');
    $wplc_data['wplc_enable_visitor_sound'] = wplc_force_bool($_POST, 'wplc_enable_visitor_sound');
    $wplc_data['wplc_enable_font_awesome'] = wplc_force_bool($_POST, 'wplc_enable_font_awesome');
    $wplc_data['wplc_enable_all_admin_pages'] = wplc_force_bool($_POST, 'wplc_enable_all_admin_pages');
    $wplc_data['wplc_delete_db_on_uninstall'] = wplc_force_bool($_POST, 'wplc_delete_db_on_uninstall');
    $wplc_data['wplc_pro_na'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_na', $wplc_settings)));
    $wplc_data['wplc_hide_when_offline'] = wplc_force_bool($_POST, 'wplc_hide_when_offline');
    $wplc_data['wplc_pro_chat_email_address'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_chat_email_address', $wplc_settings)));
    $wplc_data['wplc_pro_chat_email_offline_subject'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_chat_email_offline_subject', $wplc_settings)));
    $wplc_data['wplc_pro_offline1'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_offline1', $wplc_settings)));
    $wplc_data['wplc_pro_offline2'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_offline2', $wplc_settings)));
    $wplc_data['wplc_pro_offline3'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_offline3', $wplc_settings)));
    $wplc_data['wplc_pro_offline_btn'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_offline_btn', $wplc_settings)));
    $wplc_data['wplc_pro_offline_btn_send'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_offline_btn_send', $wplc_settings)));
    $wplc_data['wplc_using_localization_plugin'] = wplc_force_bool($_POST, 'wplc_using_localization_plugin');
    $wplc_data['wplc_pro_fst1'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_fst1', $wplc_settings)));
    $wplc_data['wplc_pro_fst2'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_fst2', $wplc_settings)));
    $wplc_data['wplc_pro_fst3'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_fst3', $wplc_settings)));
    $wplc_data['wplc_pro_sst1'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_sst1', $wplc_settings)));
    $wplc_data['wplc_pro_sst2'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_sst2', $wplc_settings)));
    $wplc_data['wplc_pro_tst1'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_tst1', $wplc_settings)));
    $wplc_data['wplc_pro_intro'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_intro', $wplc_settings)));
    $wplc_data['wplc_user_enter'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_user_enter', $wplc_settings)));
    $wplc_data['wplc_text_chat_ended'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_text_chat_ended', $wplc_settings)));
    $wplc_data['wplc_close_btn_text'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_close_btn_text', $wplc_settings)));
    $wplc_data['wplc_user_welcome_chat'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_user_welcome_chat', $wplc_settings)));
    $wplc_data['wplc_welcome_msg'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_welcome_msg', $wplc_settings)));
    $wplc_data['wplc_typing_enabled'] = wplc_force_bool($_POST, 'wplc_typing_enabled');
    $wplc_data['wplc_ux_file_share'] = wplc_force_bool($_POST, 'wplc_ux_file_share');
    $wplc_data['wplc_ux_exp_rating'] = wplc_force_bool($_POST, 'wplc_ux_exp_rating');
    $wplc_data['wplc_enable_initiate_chat'] = wplc_force_bool($_POST, 'wplc_enable_initiate_chat');
    $wplc_data['wplc_node_enable_typing_preview'] = wplc_force_bool($_POST, 'wplc_node_enable_typing_preview');
    $wplc_data['wplc_gdpr_enabled'] = wplc_force_bool($_POST, 'wplc_gdpr_enabled');
    $wplc_data['wplc_ringtone'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_ringtone', $wplc_settings)));
    $wplc_data['wplc_new_chat_ringer_count'] = wplc_force_int($_POST, 'wplc_new_chat_ringer_count', $wplc_settings, 0, 100);
    $wplc_data['wplc_messagetone'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_messagetone', $wplc_settings)));
    $wplc_data['wplc_animation'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_animation', $wplc_settings)));
    $wplc_data['wplc_theme'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_theme', $wplc_settings)));
    $wplc_data['wplc_newtheme'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_newtheme', $wplc_settings)));
    $wplc_data['wplc_elem_trigger_action'] = wplc_force_int($_POST, 'wplc_elem_trigger_action', $wplc_settings, 0, 1);
    $wplc_data['wplc_elem_trigger_type'] = wplc_force_int($_POST, 'wplc_elem_trigger_type', $wplc_settings, 0, 1);
    $wplc_data['wplc_elem_trigger_id'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_elem_trigger_id', $wplc_settings)));
    $wplc_data['wplc_show_date'] = wplc_force_bool($_POST, 'wplc_show_date');
    $wplc_data['wplc_show_time'] = wplc_force_bool($_POST, 'wplc_show_time');
    $wplc_data['wplc_show_name'] = wplc_force_bool($_POST, 'wplc_show_name');
    $wplc_data['wplc_show_avatar'] = wplc_force_bool($_POST, 'wplc_show_avatar');
    $wplc_data['wplc_user_no_answer'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_user_no_answer', $wplc_settings)));
    $wplc_data['wplc_pro_auto_first_response_chat_msg'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_auto_first_response_chat_msg', $wplc_settings)));

    $wplc_data['wplc_gdpr_enabled'] = wplc_force_bool($_POST, 'wplc_gdpr_enabled');
    if ($wplc_settings['wplc_gdpr_enabled']!=$wplc_data['wplc_gdpr_enabled']) {
      if ($wplc_data['wplc_gdpr_enabled']) {
        do_action('wplc_gdpr_reg_cron_hook');
        update_option('WPLC_GDPR_DISABLED_WARNING_DISMISSED', 'false');
      } else {
        do_action('wplc_gdpr_de_reg_cron_hook');
      }
    }

    $wplc_data['wplc_gdpr_notice_company'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_gdpr_notice_company', $wplc_settings)));
    $wplc_data['wplc_gdpr_notice_retention_purpose'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_gdpr_notice_retention_purpose', $wplc_settings)));
    $wplc_data['wplc_gdpr_notice_retention_period'] = wplc_force_int($_POST, 'wplc_gdpr_notice_retention_period', $wplc_settings, 1, 730);
    $wplc_data['wplc_gdpr_notice_text'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_gdpr_notice_text')));
    $wplc_data['wplc_gdpr_custom'] = wplc_force_bool($_POST, 'wplc_gdpr_custom');

    $wplc_data['wplc_use_node_server'] = wplc_force_bool($_POST, 'wplc_use_node_server');
    $wplc_data['wplc_default_department'] = wplc_force_int($_POST, 'wplc_default_department', $wplc_settings);
    $wplc_data['wplc_allow_department_selection'] = wplc_force_bool($_POST, 'wplc_allow_department_selection');
    $wplc_data['wplc_pro_cta_anim'] = wplc_force_bool($_POST, 'wplc_pro_cta_anim');

    $wplc_data['wplc_chat_name'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_chat_name', $wplc_settings)));
    $wplc_data['wplc_use_wp_name'] = wplc_force_bool($_POST, 'wplc_use_wp_name');

    $wplc_data['wplc_chat_pic'] = $wplc_settings['wplc_chat_pic'];
    $wplc_data['wplc_chat_logo'] = $wplc_settings['wplc_chat_logo'];
    $wplc_data['wplc_chat_icon'] = $wplc_settings['wplc_chat_icon'];

    $wplc_upload_pic = wplc_force_string($_POST, 'wplc_upload_pic', '');
    $wplc_upload_logo = wplc_force_string($_POST, 'wplc_upload_logo', '');
    $wplc_upload_icon = wplc_force_string($_POST, 'wplc_upload_icon', '');

    if (!empty($wplc_upload_pic)) {
      if ($wplc_upload_pic=='remove') {
        $wplc_data['wplc_chat_pic'] = ''; //$wplc_default_settings_array['wplc_chat_pic']; // cannot be empty, set to default
      } else {
        $wplc_data['wplc_chat_pic'] = esc_url(base64_decode($wplc_upload_pic));
      }
    }
    if (!empty($wplc_upload_logo)) {
      if ($wplc_upload_logo=='remove') {
        $wplc_data['wplc_chat_logo'] = '';
      } else {
        $wplc_data['wplc_chat_logo'] = esc_url(base64_decode($wplc_upload_logo));
      }
    }
    if (!empty($wplc_upload_icon)) {
      if ($wplc_upload_icon=='remove') {
        $wplc_data['wplc_chat_icon'] = '';
      } else {
        $wplc_data['wplc_chat_icon'] = esc_url(base64_decode($wplc_upload_icon));
      }

    }

    $wplc_data['wplc_chat_delay'] = wplc_force_int($_POST, 'wplc_chat_delay', $wplc_default_settings_array, 0, 1000);
    $wplc_data['wplc_pro_chat_notification'] = wplc_force_bool($_POST, 'wplc_pro_chat_notification');
    $wplc_data['wplc_pro_chat_email_address'] = stripslashes(sanitize_text_field(wplc_force_string($_POST, 'wplc_pro_chat_email_address', $wplc_settings)));
    $wplc_data['wplc_social_fb'] = esc_url(wplc_force_url($_POST, 'wplc_social_fb'));
    $wplc_data['wplc_social_tw'] = esc_url(wplc_force_url($_POST, 'wplc_social_tw'));

    $wplc_data['wplc_bh_enable'] = wplc_force_bool($_POST, 'wplc_bh_enable');

    $wplc_data['wplc_bh_days'] = '0000000';
    if (isset($_POST['wplc_bh_days']) && is_array($_POST['wplc_bh_days'])) {
      foreach($_POST['wplc_bh_days'] as $k=>$v) {
        if ($k>=0 && $k<7) {
          $wplc_data['wplc_bh_days'][$k] = '1';
        }
      }
    }

    $wplc_data['wplc_bh_schedule'] = $wplc_default_settings_array['wplc_bh_schedule'];
    foreach($wplc_data['wplc_bh_schedule'] as $k=>$v) {
      if (isset($_POST['bh_hs1'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][0]['hs']=wplc_force_int_range(intval($_POST['bh_hs1'][$k]), 0, 23);
      }
      if (isset($_POST['bh_ms1'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][0]['ms']=wplc_force_int_range(intval($_POST['bh_ms1'][$k]), 0, 59);
      }
      if (isset($_POST['bh_he1'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][0]['he']=wplc_force_int_range(intval($_POST['bh_he1'][$k]), 0, 23);
      }
      if (isset($_POST['bh_me1'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][0]['me']=wplc_force_int_range(intval($_POST['bh_me1'][$k]), 0, 59);
      }
      if (isset($_POST['bh_hs2'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][1]['hs']=wplc_force_int_range(intval($_POST['bh_hs2'][$k]), 0, 23);
      }
      if (isset($_POST['bh_ms2'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][1]['ms']=wplc_force_int_range(intval($_POST['bh_ms2'][$k]), 0, 59);
      }
      if (isset($_POST['bh_he2'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][1]['he']=wplc_force_int_range(intval($_POST['bh_he2'][$k]), 0, 23);
      }
      if (isset($_POST['bh_me2'][$k])) {
        $wplc_data['wplc_bh_schedule'][$k][1]['me']=wplc_force_int_range(intval($_POST['bh_me2'][$k]), 0, 59);
      }    
    }

    $wplc_data['wplc_iterations'] = wplc_force_int($_POST, 'wplc_iterations', $wplc_default_settings_array, 10, 200);
    $wplc_data['wplc_delay_between_loops'] = wplc_force_int($_POST, 'wplc_delay_between_loops', $wplc_default_settings_array['wplc_delay_between_loops']/1000, 250, 1000)*1000;

    if (isset($_POST['wplc_agent_select']) && $_POST['wplc_agent_select'] != "") {
      $user_array = wplc_get_agent_users();
      foreach ($user_array as $user) {
        $uid = $user->ID;
        $wplc_ma_user = new WP_User( $uid );
        $wplc_ma_user->remove_cap( 'wplc_ma_agent' );
        delete_user_meta($uid, "wplc_ma_agent");
        delete_user_meta($uid, "wplc_chat_agent_online");
      }

      $uid = intval($_POST['wplc_agent_select']);
      $wplc_ma_user = new WP_User( $uid );
      $wplc_ma_user->add_cap( 'wplc_ma_agent' );
      update_user_meta($uid, "wplc_ma_agent", 1);
      wplc_update_agent_time($uid);
    }

    if (isset($_POST['wplc_ban_users_ip'])) {
      $wplc_banned_ip_addresses = explode('<br />', stripslashes(nl2br($_POST['wplc_ban_users_ip'])));
      $data = array();
      foreach($wplc_banned_ip_addresses as $key => $value) {
        $clean_val = trim(sanitize_text_field($value));
        if(!empty($clean_val)){
          $data[$key] = $clean_val;
        }
      }
      $wplc_banned_ip_addresses = maybe_serialize($data);
      update_option('WPLC_BANNED_IP_ADDRESSES', $wplc_banned_ip_addresses);
    }

    ksort($wplc_data);
    $wplc_data['wplc_encryption_key'] = $wplc_settings['wplc_encryption_key'];
    update_option('WPLC_SETTINGS', $wplc_data);

    add_action( 'admin_notices', 'wplc_save_settings_action' );
  }

  if( isset( $_GET['override'] ) && $_GET['override'] == '1' ){
    update_option( "WPLC_V8_FIRST_TIME", false);
  }
}

function wplc_save_settings_action() { ?>
    <div class='notice notice-success updated wplc_settings_save_notice'>
		<?php _e("Your settings have been saved.", 'wp-live-chat-support'); ?>
    </div>
<?php }

/**
 * Error checks used to ensure the user's resources meet the plugin's requirements
 */
if(isset($_GET['page']) && $_GET['page'] == 'wplivechat-menu-settings'){
    if(is_admin()){
    	
    	// Only show these warning messages to Legacy users as they will be affected, not Node users.
    	$wplc_settings = wplc_get_options();
    	if (!$wplc_settings['wplc_use_node_server']) {

	        $wplc_error_count = 0;
	        $wplc_admin_warnings = "<div class='error'>";
	        if(!function_exists('set_time_limit')){
	            $wplc_admin_warnings .= "
	                <p>".__("WPLC: set_time_limit() is not enabled on this server. You may experience issues while using WP Live Chat by 3CX as a result of this. Please get in contact your host to get this function enabled.", 'wp-live-chat-support')."</p>
	            ";
	            $wplc_error_count++;
	        }
	        if(ini_get('safe_mode')){
	            $wplc_admin_warnings .= "
	                <p>".__("WPLC: Safe mode is enabled on this server. You may experience issues while using WP Live Chat by 3CX as a result of this. Please contact your host to get safe mode disabled.", 'wp-live-chat-support')."</p>
	            ";
	            $wplc_error_count++;
	        }
	        $wplc_admin_warnings .= "</div>";
	        if($wplc_error_count > 0){
	            echo $wplc_admin_warnings;
	        }
	    }
    }
}

/**
 * Loads the contents of the support menu item
 * @return string
 */
function wplc_support_menu() {
        wplc_stats("support");
?>
<div class="wrap wplc_wrap">
    <h2><?php _e("Support",'wp-live-chat-support'); ?></h2>
    <div class="wplc_row_support">
        <div class='wplc_row_col_support' style='background-color:#FFF;'>
			<div class="wplc_panel_heading"><i class="far fa-check-circle"></i> <?php _e("Plugin Features",'wp-live-chat-support'); ?></div>
			<div class="wplc_row_col_support_inner">
				<?php _e("Check out these features and get up to speed with what you can do with WP Live Chat:", 'wp-live-chat-support'); ?>
				<ul>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/features/' target='_BLANK' title='<?php _e("Plugin Features",'wp-live-chat-support'); ?>'><?php _e("Plugin Features",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/features/#h.qquuf2facgvf' target='_BLANK' title='<?php _e("Reporting",'wp-live-chat-support'); ?>'><?php _e("Reporting",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/translation/' target='_BLANK' title='<?php _e("Localization",'wp-live-chat-support'); ?>'><?php _e("Localization",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/triggers/' target='_BLANK' title='<?php _e("Triggers",'wp-live-chat-support'); ?>'><?php _e("Triggers",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/features/#h.8yn2kwmystj2' target='_BLANK' title='<?php _e("Web Hooks",'wp-live-chat-support'); ?>'><?php _e("Web Hooks",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/features/#h.7t9i9vdx2z96' target='_BLANK' title='<?php _e("ROI Goals",'wp-live-chat-support'); ?>'><?php _e("ROI Goals",'wp-live-chat-support'); ?></a></li>
				</ul>
			</div>
        </div>
        <div class='wplc_row_col_support' style='background-color:#FFF;'>
			<div class="wplc_panel_heading"><i class="fa fa-book"></i> <?php _e("Chat FAQs",'wp-live-chat-support'); ?></div>
			<div class="wplc_row_col_support_inner">
            	<?php _e("Learn quickly the ins and outs of Chat and start chatting with visitors and agents:", 'wp-live-chat-support'); ?>
				<ul>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/settings/' target='_BLANK' title='<?php _e("Chat with Visitors",'wp-live-chat-support'); ?>'><?php _e("Chat with Visitors",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/features/#h.q5e6o9g62hlm' target='_BLANK' title='<?php _e("Chat with Agents",'wp-live-chat-support'); ?>'><?php _e("Chat with Agents",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/quick-responses/' target='_BLANK' title='<?php _e("Quick Responses",'wp-live-chat-support'); ?>'><?php _e("Quick Responses",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/offline-chat/' target='_BLANK' title='<?php _e("Offline Messages",'wp-live-chat-support'); ?>'><?php _e("Offline Messages",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/settings/#h.w4ystw3nh379' target='_BLANK' title='<?php _e("Chat History",'wp-live-chat-support'); ?>'><?php _e("Chat History",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/features/#h.7cm70gxsp8c6' target='_BLANK' title='<?php _e("Chat Invites",'wp-live-chat-support'); ?>'><?php _e("Chat Invites",'wp-live-chat-support'); ?></a></li>
				</ul>
			</div>
        </div>
        <div class='wplc_row_col_support' style='background-color:#FFF;'>
			<div class="wplc_panel_heading"><i class="fas fa-sliders-h"></i> <?php _e("Settings & Customization",'wp-live-chat-support'); ?></div>
			<div class="wplc_row_col_support_inner">
				<?php _e("Use these guides to learn how to configure and customize WP Live Chat:", 'wp-live-chat-support'); ?>
				<ul>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/chatbox-customization/#h.3y0oqa82jfpo' target='_BLANK' title='<?php _e("General Settings",'wp-live-chat-support'); ?>'><?php _e("General Settings",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/chatbox-customization/#h.1u24onjmgol1' target='_BLANK' title='<?php _e("Chat Box Settings",'wp-live-chat-support'); ?>'><?php _e("Chat Box Settings",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/chatbox-customization/#h.xwu4yozf3o4v' target='_BLANK' title='<?php _e("Agent Settings",'wp-live-chat-support'); ?>'><?php _e("Agent Settings",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/chatbox-customization/#h.skw0v5uc8avz' target='_BLANK' title='<?php _e("Business Hours",'wp-live-chat-support'); ?>'><?php _e("Business Hours",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/gutenberg/' target='_BLANK' title='<?php _e("Gutenberg Blocks",'wp-live-chat-support'); ?>'><?php _e("Gutenberg Blocks",'wp-live-chat-support'); ?></a></li>
				</ul>
			</div>
        </div>
        <div class='wplc_row_col_support' style='background-color:#FFF;'>
			<div class="wplc_panel_heading"><i class="fa fa-info-circle"></i> <?php _e("Troubleshooting",'wp-live-chat-support'); ?></div>
			<div class="wplc_row_col_support_inner">
				<?php _e("Reference these troubleshooting guides to quickly resolve any WP Live Chat issues:", 'wp-live-chat-support'); ?>
				<ul>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/chat-box-not-showing/' target='_BLANK' title='<?php _e("My Chat Box Is Not Showing",'wp-live-chat-support'); ?>'><?php _e("My Chat Box Is Not Showing",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/not-receiving-notifications-of-new-chats/' target='_BLANK' title='<?php _e("Not Receiving Notifications of New Chats",'wp-live-chat-support'); ?>'><?php _e("Not Receiving Notifications of New Chats",'wp-live-chat-support'); ?></a></li>
					<li><a href='https://www.3cx.com/wp-live-chat/docs/javascript-errors/' target='_BLANK' title='<?php _e("Check for JavaScript Errors",'wp-live-chat-support'); ?>'><?php _e("Check for JavaScript Errors",'wp-live-chat-support'); ?></a></li>
				</ul>
			</div>
        </div>
    </div>
</div>
<?php
}

//if (!function_exists("wplc_ic_initiate_chat_button")) { TODO: check why
    add_action('admin_enqueue_scripts', 'wp_button_pointers_load_scripts');
//}
/**
 * Displays the pointers on the live chat dashboard for the initiate chat functionality
 * @param  string $hook returns the page name we're on
 * @return string       contents of the pointers and their scripts
 */
function wp_button_pointers_load_scripts($hook) {
  $wplcrun = false;
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_enable_all_admin_pages']) {
    /* Run admin JS on all admin pages */
    $wplcrun = true;
  } else {
    if ($hook === 'toplevel_page_wplivechat-menu') { $wplcrun = true; } // stop if we are not on the right page
  }
  if ( $wplcrun ) {
    $pointer_localize_strings = array(
      "initiate" => "<h3>".__("Initiate Chats",'wp-live-chat-support')."</h3>",
      "chats" => "<h3>".__("Multiple Chats",'wp-live-chat-support')."</h3>",
      "agent_info" => "<h3>".__("Add unlimited agents",'wp-live-chat-support')."</h3>",
      "transfer" => "<h3>".__("Transfer Chats",'wp-live-chat-support')."</h3>",
      "direct_to_page" => "<h3>".__("Direct User To Page",'wp-live-chat-support')."</h3>"
    );
    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_script( 'wp-pointer' );
    wp_register_script('wplc-user-admin-pointer', plugins_url('/js/wplc-admin-pointers.js', __FILE__), array('wp-pointer'), WPLC_PLUGIN_VERSION, true);
    wp_enqueue_script('wplc-user-admin-pointer');
    wp_localize_script('wplc-user-admin-pointer', 'pointer_localize_strings', $pointer_localize_strings);
  }
}

add_filter( 'admin_footer_text', 'wplc_footer_mod' );
/**
 * Adds the WP Live Chat by 3CX footer contents to the relevant pages
 * @param  string $footer_text current footer text available to us
 * @return string              footer contents with our branding in it
 */
function wplc_footer_mod( $footer_text ) {
    if (isset($_GET['page']) && ($_GET['page'] == 'wplivechat-menu' || $_GET['page'] == 'wplivechat-menu-settings' || $_GET['page'] == 'wplivechat-menu-offline-messages' || $_GET['page'] == 'wplivechat-menu-history')) {
        $footer_text_mod = sprintf( __( 'Thank you for using %s! Please %s on %s', 'wp-live-chat-support'),'<a href="https://www.3cx.com/wp-live-chat/?utm_source=plugin&utm_medium=link&utm_campaign=footer" target="_blank">WP Live Chat by 3CX</a>','<a href="https://wordpress.org/support/view/plugin-reviews/wp-live-chat-support?filter=5#postform" target="_blank">'.__('rate us','wp-live-chat-support').'</a>','<a href="https://wordpress.org/support/view/plugin-reviews/wp-live-chat-support?filter=5#postform" target="_blank">WordPress.org</a>');
        return str_replace( '</span>', '', $footer_text ) . ' | ' . $footer_text_mod;
    } else {
        return $footer_text;
    }

}

add_filter("wplc_filter_admin_long_poll_chat_loop_iteration","wplc_filter_control_wplc_admin_long_poll_chat_iteration", 1, 3);
/**
 * Alters the admin's long poll chat iteration
 * @param  array $array     current chat data available to us
 * @param  array $post_data current post data available to us
 * @param  int 	 $i         count for each chat available
 * @return array            additional contents added to the chat data
 */
function wplc_filter_control_wplc_admin_long_poll_chat_iteration($array,$post_data,$i) {
  if(isset($post_data['action_2']) && $post_data['action_2'] == "wplc_long_poll_check_user_opened_chat"){
      $chat_status = wplc_return_chat_status(sanitize_text_field($post_data['cid']));
      if(intval($chat_status) == 3){
          $array['action'] = "wplc_user_open_chat";
      }
  } else {

  	  if ($post_data['first_run'] === "true") {
  	  	/* get the chat messages for the first run */
  	  	$array['chat_history'] = wplc_return_chat_messages($post_data['cid'], false, true, false, false, 'array', false);
  	  	$array['action'] = "wplc_chat_history";

  	  } else {

	      $new_chat_status = wplc_return_chat_status(sanitize_text_field($post_data['cid']));
	      if($new_chat_status != $post_data['chat_status']){
	          $array['chat_status'] = $new_chat_status;
	          $array['action'] = "wplc_update_chat_status";
	      }
	      $new_chat_message = wplc_return_admin_chat_messages(sanitize_text_field($post_data['cid']));

	      if($new_chat_message){

	          $array['chat_message'] = $new_chat_message;
	          $array['action'] = "wplc_new_chat_message";
	      }
	  }
  }

  return $array;
}



/**
 * Returns chat data specific to a chat ID
 * @param  int 		$cid  Chat ID
 * @return array    	  Contents of the chat based on the ID provided
 */
function wplc_get_chat_data($cid) {
  global $wpdb;
  global $wplc_tblname_chats;
  $cid = wplc_return_chat_id_by_rel_or_id($cid);
  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wplc_tblname_chats WHERE `id` = %d LIMIT 1", intval($cid)));
  if (isset($results[0])) { $result = $results[0]; } else {  $result = null; }
  return $result;
}

/**
 * Returns chat messages specific to a chat ID
 * @param  int 		$cid  Chat ID
 * @return array 		  Chat messages based on the ID provided
 */
function wplc_get_chat_messages($cid, $only_read_messages = false, $wplc_settings = false) {
  global $wpdb;
  global $wplc_tblname_msgs;

  if (!$wplc_settings) {
      $wplc_settings = wplc_get_options();
  }

  /**
   * Identify if the user is using the node server and if they are, display all messages. Otherwise display read only messages (non-node users)
   */
  if ($wplc_settings['wplc_use_node_server']) {

          $sql = "
            SELECT * FROM (
                SELECT *
                FROM $wplc_tblname_msgs
                WHERE `chat_sess_id` = %s
                ORDER BY `timestamp` DESC LIMIT 200
            ) sub 
            ORDER BY `timestamp` ASC
            ";
    } else {
        if ($only_read_messages) {
        // only show read messages
              $sql =
                "
                SELECT * FROM (
                    SELECT *
                    FROM $wplc_tblname_msgs
                    WHERE `chat_sess_id` = %s AND `status` = 1
                    ORDER BY `timestamp` DESC LIMIT 200
                ) sub 
                ORDER BY `timestamp` ASC
                ";
        } else {
            $sql =
                "
                SELECT * FROM (
                    SELECT *
                    FROM $wplc_tblname_msgs
                    WHERE `chat_sess_id` = %s
                    ORDER BY `timestamp` DESC LIMIT 200
                ) sub 
                ORDER BY `timestamp` ASC
                ";
        }

    }
    $results = $wpdb->get_results($wpdb->prepare($sql, $cid));

  if (isset($results[0])) {  } else {  $results = null; }
  $results = apply_filters("wplc_filter_get_chat_messages",$results,$cid);

  if ($results == "null") {
    return false;
  } else {
    return $results;
  }
}

add_action('admin_init', 'wplc_admin_download_chat_history');
/**
 * Downloads the chat history and adds it to a CSV file
 * @return file
 */
function wplc_admin_download_chat_history(){
	if (!is_user_logged_in() || !wplc_user_is_agent()) {
    return;
	}

	if (isset($_GET['action']) && $_GET['action'] == "download_history") {

        global $wpdb;   

        if (!isset($_GET['wplc_history_nonce']) || !wp_verify_nonce($_GET['wplc_history_nonce'], 'wplc_history_nonce')){
              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
        }

        $chat_id = sanitize_text_field( $_GET['cid'] );
        $fileName = 'live_chat_history_'.md5($chat_id).'.csv';

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}");
        header("Expires: 0");
        header("Pragma: public");

        $fh = @fopen( 'php://output', 'w' );

        global $wpdb;
	    global $wplc_tblname_msgs;

	    $results = $wpdb->get_results($wpdb->prepare("
	        SELECT *
	        FROM $wplc_tblname_msgs
	        WHERE `chat_sess_id` = %s
	        ORDER BY `timestamp` ASC
	        LIMIT 0,1000
	        ", $chat_id)
	    );

	    $fields[] = array(
	        'id' => __('Chat ID', 'wp-live-chat-support'),
	        'msgfrom' => __('From', 'wp-live-chat-support'),
	        'msg' => __('Message', 'wp-live-chat-support'),
	        'time' => __('Timestamp', 'wp-live-chat-support'),
	        'orig' => __('Origin', 'wp-live-chat-support'),
	    );

	    foreach ($results as $result => $key) {
	        if($key->originates == 2){
	            $user = __('user', 'wp-live-chat-support');
	        } else {
	            $user = __('agent', 'wp-live-chat-support');
	        }

	        $fields[] = array(
	            'id' => $key->chat_sess_id,
	            'msgfrom' => $key->msgfrom,
	            'msg' => apply_filters("wplc_filter_message_control_out",$key->msg),
	            'time' => $key->timestamp,
	            'orig' => $user,
	        );
	    }

        foreach($fields as $field){
	    	fputcsv($fh, $field, ",", '"');
	    }
        // Close the file
        fclose($fh);
        // Make sure nothing else is sent, our file is done
        exit;

    }
}

/**
 * Retrieves the data to start downloadling the chat history
 * @param  string $type Chat history output type
 * @param  string $cid  Chat ID
 * @return void
 */
function wplc_admin_download_history($type, $cid){
  if (!is_user_logged_in() || !wplc_user_is_agent(get_current_user_id())) {
    return;
	}

    global $wpdb;
    global $wplc_tblname_msgs;

    $results = $wpdb->get_results($wpdb->prepare(
        "
        SELECT *
        FROM $wplc_tblname_msgs
        WHERE `chat_sess_id` = %d
        ORDER BY `timestamp` ASC
        LIMIT 0, 100
        "
    	, intval($cid))
    );

    $fields[] = array(
        'id' => __('Chat ID', 'wp-live-chat-support'),
        'msgfrom' => __('From', 'wp-live-chat-support'),
        'msg' => __('Message', 'wp-live-chat-support'),
        'time' => __('Timestamp', 'wp-live-chat-support'),
        'orig' => __('Origin', 'wp-live-chat-support'),
    );

    foreach ($results as $key) {
        if($key->originates == 2){
            $user = __('user', 'wp-live-chat-support');
        } else {
            $user = __('agent', 'wp-live-chat-support');
        }

        $fields[] = array(
            'id' => $key->chat_sess_id,
            'msgfrom' => $key->msgfrom,
            'msg' => apply_filters("wplc_filter_message_control_out",$key->msg),
            'time' => $key->timestamp,
            'orig' => $user,
        );
    }

    ob_end_clean();

    wplc_convert_to_csv($fields, 'live_chat_history_'.$cid.'.csv', ',');

    exit();
}

/**
 * Converts contents into a CSV file
 * @param  string $in  Contents of file, array of arrays
 * @param  string $out Output of file
 * @param  string $del Delimiter for content
 * @return void
 */
function wplc_convert_to_csv($in, $out, $del) {
  $f = fopen('php://memory', 'w');

  foreach ($in as $arr) {
      wplc_fputcsv_eol($f, $arr, $del, "\r\n");
  }
  fseek($f, 0);
  header('Content-Type: application/csv');
  header('Content-Disposition: attachement; filename="' . $out . '";');
  fpassthru($f);
}

/**
 * Parses content to add to a CSV file
 * @param  string $fp    The open file
 * @param  array $array The content to be added to the file
 * @param  string $del   Delimiter to use in the file
 * @param  string $eol   Content to be written to the file
 * @return void
 */
function wplc_fputcsv_eol($fp, $array, $del, $eol) {
  fputcsv($fp, $array, $del);
  if("\n\r" != $eol && 0 === fseek($fp, -1, SEEK_CUR)) {
    fwrite($fp, $eol);
  }
}

add_filter("wplc_filter_typing_control_div","wplc_filter_control_return_chat_response_box_before",2,1);
function wplc_filter_control_return_chat_response_box_before($string) {
    $string = $string. "<div class='typing_indicator wplc-color-1'></div>";

    return $string;
}
add_filter("wplc_filter_typing_control_div_theme_2","wplc_filter_control_return_chat_response_box_before_theme2",2,1);
function wplc_filter_control_return_chat_response_box_before_theme2($string) {
    $string = $string. "<div class='typing_indicator wplc-color-1'></div>";

    return $string;
}


add_action("wplc_hook_admin_settings_main_settings_after","wplc_hook_control_admin_settings_chat_box_settings_after",2);
/**
 * Adds the settings to allow the user to change their server environment variables
 * @return sring */
function wplc_hook_control_admin_settings_chat_box_settings_after() {
  $wplc_settings = wplc_get_options();
  $wplc_environment=intval($wplc_settings["wplc_environment"]);
	?>
	<h4><?php _e("Advanced settings", 'wp-live-chat-support') ?></h4>
	<table class='wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
          <?php do_action("wplc_advanced_settings_above_performance", $wplc_settings); ?>
      </table>
	<table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
          <tr>
          	<td colspan='2'>
          		<p><em><small><?php _e("Only change these settings if you are experiencing performance issues.",'wp-live-chat-support'); ?></small></em></p>
          	</td>
          </tr>
          </tr>
          <?php do_action("wplc_advanced_settings_settings"); ?>
          <tr>
              <td valign='top'>
                  <?php _e("Website hosting type:",'wp-live-chat-support'); ?>
              </td>
              <td valign='top'>
                  <select name='wplc_environment' id='wplc_environment'>
                    <option value='0' <?php if ($wplc_environment==0) {echo 'selected';}?>><?php _e("Custom parameters",'wp-live-chat-support'); ?></option>
                    <option value='1' <?php if ($wplc_environment==1) {echo 'selected';}?>><?php _e("Shared hosting - low level plan",'wp-live-chat-support'); ?></option>
                    <option value='2' <?php if ($wplc_environment==2) {echo 'selected';}?>><?php _e("Shared hosting - normal plan",'wp-live-chat-support'); ?></option>
                    <option value='3' <?php if ($wplc_environment==3) {echo 'selected';}?>><?php _e("VPS",'wp-live-chat-support'); ?></option>
                    <option value='4' <?php if ($wplc_environment==4) {echo 'selected';}?>><?php _e("Dedicated server",'wp-live-chat-support'); ?></option>
                  </select>
              </td>
          </tr>
          <tr>
              <td valign='top'>
                  <?php _e("Long poll setup",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Only change these if you are an experienced developer or if you have received these figures from the WP Live Chat by 3CX team.", 'wp-live-chat-support') ?>"></i>
              </td>
              <td valign='top'>
                  <table>
                    <tr>
                      <td><?php _e("Iterations",'wp-live-chat-support'); ?></td>
                      <td><input id="wplc_iterations" name="wplc_iterations" type="number" max='20000' min='1' <?php if ($wplc_environment>0) {echo 'readonly';}?> value="<?php echo $wplc_settings['wplc_iterations']; ?>" /></td>
                    </tr>
                    <tr>
                      <td><?php _e("Sleep between loops",'wp-live-chat-support'); ?></td>
                      <td>
                        <input id="wplc_delay_between_loops" name="wplc_delay_between_loops" type="number" max='1000000' min='1' <?php if ($wplc_environment>0) {echo 'readonly';}?> value="<?php echo floor($wplc_settings['wplc_delay_between_loops']/1000); ?>" />
                        <small><em><?php _e("milliseconds",'wp-live-chat-support'); ?></em></small>
                      </td>
                    </tr>                    
                  </table>

              </td>
          </tr>

      </table>
  <?php
}

add_action('wplc_hook_admin_settings_main_settings_after','wplc_powered_by_link_settings_page',2);
/**
 * Adds the necessary checkbox to enable/disable the 'Powered by' link
 * @return string
 */
function wplc_powered_by_link_settings_page() {
    $wplc_powered_by = get_option("WPLC_POWERED_BY");
  ?>
    <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
        <tr>
            <td width='350' valign='top'>
                <?php _e("Show 'Powered by' in chat box", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Checking this will display a 'Powered by WP Live Chat by 3CX' caption at the bottom of your chatbox.", 'wp-live-chat-support'); ?>"></i>
            </td>
            <td>
                <input type="checkbox" value="1" name="wplc_powered_by" <?php if ( $wplc_powered_by && $wplc_powered_by == 1 ) { echo "checked"; } ?> />
            </td>
        </tr>
    </table>
  <?php
}

add_action( "wplc_hook_head", "wplc_powered_by_link_save_settings" );
/**
 * Saves the 'Powered by' link settings
 * @return void
 */
function wplc_powered_by_link_save_settings(){

	if( isset( $_POST['wplc_save_settings'] ) ){

			if( isset( $_POST['wplc_powered_by'] ) && $_POST['wplc_powered_by'] == '1' ){
				update_option( "WPLC_POWERED_BY", 1 );
			} else {
				update_option( "WPLC_POWERED_BY", 0 );
			}

	}

}

add_filter( "wplc_start_chat_user_form_after_filter", "wplc_powered_by_link_in_chat", 12, 1 );
/**
 * Appends the 'Powered by' link to the chat window
 * @param  string 	$string the current contents of the chat box
 * @param  int 		$cid    the current chat ID
 * @return string         	the chat contents, with the 'Powered by' link appended to it
 */
function wplc_powered_by_link_in_chat( $string ){

	$show_powered_by = get_option( "WPLC_POWERED_BY" );

	if( $show_powered_by == 1){

		$ret = "<i style='text-align: center; display: block; padding: 5px 0; font-size: 10px;'><a href='https://www.3cx.com/wp-live-chat/?utm_source=poweredby&utm_medium=click&utm_campaign=".wp_filter_post_kses(site_url())."'' target='_BLANK' rel='nofollow'>".__("Powered by WP Live Chat by 3CX", 'wp-live-chat-support')."</a></i>";

	} else {

		$ret = "";

	}

	return $string . $ret;

}

add_action( "admin_enqueue_scripts", "wplc_custom_scripts_scripts" );
/**
 * Loads the Ace.js editor for the custom scripts
 * @return void
 */
function wplc_custom_scripts_scripts(){

	if ( isset( $_GET['page'] ) ) {
		if ( $_GET['page'] == 'wplivechat-menu-settings' ) {
			wp_enqueue_script( "wplc-custom-script-tab-ace", WPLC_PLUGIN_URL.'js/vendor/ace/ace.js', array('jquery'),WPLC_PLUGIN_VERSION );
		} else if ( $_GET['page'] === 'wplivechat-menu-dashboard' || $_GET['page'] === 'wplivechat-menu' ) {
      wplc_register_common_node();
			wp_enqueue_script( 'wplc-custom-script-dashboard', WPLC_PLUGIN_URL.'js/wplc_dashboard.js', array('jquery'), WPLC_PLUGIN_VERSION, true );
			wp_localize_script('wplc-custom-script-dashboard', 'nifty_api_key', wplc_node_server_token_get());
		}
		
	}

}

add_filter( "wplc_offline_message_subject_filter", "wplc_change_offline_message", 10, 1 );
/**
 * Adds a filter to change the email address to the user's preference
 * @param  string $subject The default subject
 * @return string
 */
function wplc_change_offline_message( $subject ){

	$wplc_settings = wplc_get_options();

	if( isset( $wplc_settings['wplc_pro_chat_email_offline_subject'] ) ){
		$subject = stripslashes( $wplc_settings['wplc_pro_chat_email_offline_subject'] );
	}

	return $subject;

}

add_filter( 'wplc_filter_active_chat_box_notification', 'wplc_active_chat_box_notice' );
add_action( "wplc_hook_chat_dashboard_above", "wplc_active_chat_box_notices" );
function wplc_active_chat_box_notices() {
  echo apply_filters( 'wplc_filter_active_chat_box_notice', '' );
}

/*
 * Returns the WDT emoji selector
*/
function wplc_emoji_selector_div(){
	$wplc_settings = wplc_get_options();

	if ($wplc_settings['wplc_disable_emojis'] || !$wplc_settings['wplc_use_node_server']) {
		return;
  }

  return '<div class="wdt-emoji-popup">
<a href="#" class="wdt-emoji-popup-mobile-closer"> &times; </a>
<div class="wdt-emoji-menu-content">
<div id="wdt-emoji-menu-header">
  <a class="wdt-emoji-tab" data-group-name="People"></a>
  <a class="wdt-emoji-tab" data-group-name="Nature"></a>
  <a class="wdt-emoji-tab" data-group-name="Foods"></a>
  <a class="wdt-emoji-tab" data-group-name="Activity"></a>
  <a class="wdt-emoji-tab" data-group-name="Places"></a>
  <a class="wdt-emoji-tab" data-group-name="Objects"></a>
  <a class="wdt-emoji-tab" data-group-name="Symbols"></a>
  <a class="wdt-emoji-tab" data-group-name="Flags"></a>
</div>
<div class="wdt-emoji-scroll-wrapper">
  <div id="wdt-emoji-menu-items">
    <input id="wdt-emoji-search" type="text" placeholder="Search">
    <h3 id="wdt-emoji-search-result-title">Search Results</h3>
    <div class="wdt-emoji-sections"></div>
    <div id="wdt-emoji-no-result">No emoji found</div>
  </div>
</div>
<div id="wdt-emoji-footer">
  <div id="wdt-emoji-preview">
    <span id="wdt-emoji-preview-img"></span>
    <div id="wdt-emoji-preview-text">
      <span id="wdt-emoji-preview-name"></span><br>
      <span id="wdt-emoji-preview-aliases"></span>
    </div>
  </div>
  <div id="wdt-emoji-preview-bundle">
    <span></span>
  </div>
  <span class="wdt-credit">WDT Emoji Bundle</span>
</div>
</div>
</div>';
}

add_action( 'admin_notices', 'wplc_browser_notifications_admin_warning' );
/**
 * Displays browser notifications warning.
 *
 * Only displays if site is insecure (no SSL).
 */
function wplc_browser_notifications_admin_warning() {

    if ( ! is_ssl() && isset( $_GET['page'] ) && $_GET['page'] === 'wplivechat-menu-settings' ) {

        if ( isset( $_GET['wplc_dismiss_notice_bn'] ) && 'true' === $_GET['wplc_dismiss_notice_bn'] ) {

            update_option( 'wplc_dismiss_notice_bn', 'true' );

        }

        if ( get_option( 'wplc_dismiss_notice_bn') !== 'true' ) {

            ?>
            <div class="notice notice-warning is-dismissible">
                <p><img src="<?php echo sanitize_text_field( plugins_url( 'images/wplc-logo.png', __FILE__ ) ); ?>" style="width:260px;height:auto;max-width:100%;"></p>
                <p><strong><?php _e( 'Browser notifications will no longer function on insecure (non-SSL) sites.', 'wp-live-chat-support'); ?></strong></p>
                <p><?php _e( 'Please add an SSL certificate to your site to continue receiving chat notifications in your browser.', 'wp-live-chat-support'); ?></p>
                <p><a href="?page=<?php echo sanitize_text_field( $_GET['page'] ); ?>&wplc_dismiss_notice_bn=true" id="wplc_dismiss_notice_bn" class="button"><?php _e( "Don't Show This Again", 'wp-live-chat-support'); ?></a></p>
            </div>
            <?php

        }
    }
}

if ( function_exists( 'wplc_et_first_run_check' ) ) {
	add_action( 'admin_notices', 'wplc_transcript_admin_notice' );
}
function wplc_transcript_admin_notice() {
	printf( '<div class="notice notice-info">%1$s</div>', __( 'Please deactivate WP Live Chat Suport - Email Transcript plugin. Since WP Live Chat Support 8.0.05 there is build in support for Email Transcript.', 'wp-live-chat-support') );
}

add_action( 'wplc_hook_admin_visitor_info_display_after', 'wplc_transcript_add_admin_button' );
function wplc_transcript_add_admin_button( $cid ) {
  $wplc_settings = wplc_get_options();
	if ($wplc_settings['wplc_enable_transcripts']) {
		echo "<a href=\"javascript:void(0);\" cid='" . esc_attr( $cid ) . "' class=\"wplc_admin_email_transcript button button-secondary\" id=\"wplc_admin_email_transcript\">" . __( "Email transcript to user", 'wp-live-chat-support') . "</a>";
	}
}

add_action( 'wplc_hook_admin_javascript_chat', 'wplc_transcript_admin_javascript' );
function wplc_transcript_admin_javascript() {
	$wplc_et_ajax_nonce = wp_create_nonce( "wplc_et_nonce" );
	wp_register_script( 'wplc_transcript_admin', plugins_url( '/js/wplc_transcript.js', __FILE__ ), array(), WPLC_PLUGIN_VERSION, true);
	$wplc_transcript_localizations = array(
		'ajax_nonce'          => $wplc_et_ajax_nonce,
		'string_loading'      => __( "Sending transcript...", 'wp-live-chat-support'),
		'string_title'        => __( "Sending Transcript", 'wp-live-chat-support'),
		'string_close'        => __( "Close", 'wp-live-chat-support'),
		'string_chat_emailed' => __( "The chat transcript has been emailed.", 'wp-live-chat-support'),
		'string_error1'       => __( "There was a problem emailing the chat.", 'wp-live-chat-support')
	);
	wp_localize_script( 'wplc_transcript_admin', 'wplc_transcript_nonce', $wplc_transcript_localizations );
	wp_enqueue_script( 'wplc_transcript_admin' );
}

add_action( 'wp_ajax_wplc_et_admin_email_transcript', 'wplc_transcript_callback' );
function wplc_transcript_callback() {
  $check = check_ajax_referer( 'wplc_et_nonce', 'security' );
  if ( $check == 1 ) {
    if ( isset( $_POST['el'] ) && $_POST['el'] === 'endChat' ) {
      $wplc_settings = wplc_get_options();
      if ( !$wplc_settings['wplc_send_transcripts_when_chat_ends']) { // TODO: check if this works, should execute next block to send transcript
        wp_die();
      }
    }

    if ( $_POST['action'] == "wplc_et_admin_email_transcript" ) {
      if (isset( $_POST['cid'])) {
        $cid = wplc_return_chat_id_by_rel_or_id($_POST['cid']);
        echo json_encode( wplc_send_transcript( $cid ) );
      } else {
        echo json_encode( array( "error" => "no CID" ) );
      }
      wp_die();
    }
    wp_die();
  }
  wp_die();
}

function wplc_send_transcript( $cid ) {
  if ( ! $cid ) {
    return array( "error" => "no CID", "cid" => $cid );
  }
  $cid = wplc_return_chat_id_by_rel_or_id($cid);
  $email = false;
  $wplc_settings = wplc_get_options();

  if ($wplc_settings['wplc_enable_transcripts']) {
    if ( function_exists( "wplc_get_chat_data" ) ) {
      $cdata = wplc_get_chat_data( $cid );
      if ( $cdata ) {
        if ( $wplc_settings['wplc_send_transcripts_to'] === 'admin' ) {
          $user = wp_get_current_user();
          $email = $user->user_email;
        } else {
          $email = $cdata->email;
        }
        if ( ! $email ) {
          return array( "error" => "no email" );
        }
      } else {
        return array( "error" => "no chat data" );
      }
    } else {
      return array( "error" => "basic function missing" );
    }

    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    $subject = sprintf( __( 'Your chat transcript from %1$s', 'wp-live-chat-support'), get_bloginfo('url'));
    wp_mail( $email, $subject, wplc_transcript_return_chat_messages($cid), $headers );
  }
  return array( "success" => 1 );
}
add_action('wplc_send_transcript_hook', 'wplc_send_transcript', 10, 1);

function wplc_transcript_return_chat_messages( $cid ) {
	global $current_chat_id;
  $current_chat_id  = $cid;
  $wplc_settings = wplc_get_options();
	$body = html_entity_decode(stripslashes($wplc_settings['wplc_et_email_body']));

	if (empty($body)) {
		$body = do_shortcode( wplc_transcript_return_default_email_body() );
	} else {
		$body = do_shortcode( $body );
	}
	return $body;
}

function wplc_transcript_return_default_email_body() {
	$body = '
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">		
	<html>
	
	<body>



		<table id="" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-family: Georgia, serif;">
	    <tbody>
	      <tr>
	        <td width="100%" style="padding: 30px 20px 100px 20px;">
	          <table cellpadding="0" cellspacing="0" class="" width="100%" style="border-collapse: separate;">
	            <tbody>
	              <tr>
	                <td style="padding-bottom: 20px;">
	                  
	                  <p>[wplc_et_transcript_header_text]</p>
	                </td>
	              </tr>
	            </tbody>
	          </table>

	          <table id="" cellpadding="0" cellspacing="0" class="" width="100%" style="border-collapse: separate; font-size: 12px; color: rgb(51, 62, 72);">
	          <tbody>
	              <tr>
	                <td class="sortable-list ui-sortable" >
	                    [wplc_et_transcript]
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
	                         <p>[wplc_et_transcript_footer_text]</p>
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


add_action( 'wplc_hook_admin_settings_main_settings_after', 'wplc_hook_admin_transcript_settings' );
function wplc_hook_admin_transcript_settings() {
	$wplc_settings = wplc_get_options();
	echo "<h3>" . __( "Chat Transcript Settings", 'wp-live-chat-support') . "</h3>";
	echo "<table class='form-table wp-list-table widefat fixed striped pages' width='700'>";
	echo "	<tr>";
	echo "		<td width='400' valign='top'>" . __( "Enable chat transcripts:", 'wp-live-chat-support') . "</td>";
	echo "		<td>";
	echo "			<input type=\"checkbox\" value=\"1\" name=\"wplc_enable_transcripts\" ".($wplc_settings['wplc_enable_transcripts'] ? ' checked' : '');
	echo " />";
	echo "		</td>";
	echo "	</tr>";

	echo "	<tr>";
	echo "		<td width='400' valign='top'>" . __( "Send transcripts to:", 'wp-live-chat-support') . "</td>";
	echo "		<td>";
	echo "			<select name=\"wplc_send_transcripts_to\">";
	echo "			    <option value=\"user\" ";
	if ( $wplc_settings['wplc_send_transcripts_to'] == 'user' ) {
	    echo "selected";
    }
    echo ">" . __( "User", 'wp-live-chat-support') . "</option>";
	echo "			    <option value=\"admin\" ";
	if ( $wplc_settings['wplc_send_transcripts_to'] == 'admin' ) {
		echo "selected";
	}
	echo ">" . __( "Admin", 'wp-live-chat-support') . "</option>";
	echo "          </select>";
	echo "		</td>";
	echo "	</tr>";

	echo "	<tr>";
	echo "		<td width='400' valign='top'>" . __( "Send transcripts when chat ends:", 'wp-live-chat-support') . "</td>";
	echo "		<td>";
	echo "			<input type=\"checkbox\" value=\"1\" name=\"wplc_send_transcripts_when_chat_ends\" ".($wplc_settings['wplc_send_transcripts_when_chat_ends'] ? ' checked' : '');
	echo " />";
	echo "		</td>";
	echo "	</tr>";

	echo "	<tr>";
	echo "		<td width='400' valign='top'>" . __( "Email body", 'wp-live-chat-support') . "</td>";
	echo "		<td>";
	echo "			<textarea cols='85' rows='15' name=\"wplc_et_email_body\">";
  echo trim(html_entity_decode( stripslashes( $wplc_settings['wplc_et_email_body'] ) ));
	echo " </textarea>";
	echo "		</td>";
	echo "	</tr>";


	echo "	<tr>";
	echo "		<td width='400' valign='top'>" . __( "Email header", 'wp-live-chat-support') . "</td>";
	echo "		<td>";
	echo "			<textarea cols='85' rows='5' name=\"wplc_et_email_header\">";
  echo trim(stripslashes( $wplc_settings['wplc_et_email_header'] ));
	echo " </textarea>";
	echo "		</td>";
	echo "	</tr>";

	echo "	<tr>";
	echo "		<td width='400' valign='top'>" . __( "Email footer", 'wp-live-chat-support') . "</td>";
	echo "		<td>";
	echo "			<textarea cols='85' rows='5' name=\"wplc_et_email_footer\">";
  echo trim(stripslashes( $wplc_settings['wplc_et_email_footer'] ));
	echo " </textarea>";
	echo "		</td>";
	echo "	</tr>";
	echo "</table>";
}

add_shortcode('wplc_et_transcript', 'wplc_transcript_get_transcript');
function wplc_transcript_get_transcript() {
	global $current_chat_id;
	$cid = $current_chat_id;

	if ( intval( $cid ) > 0 ) {
		$output_html = wplc_return_chat_messages( intval( $cid ), true );

		// Remove empty documentation suggestions, and unneeded list items
		$output_html = str_replace('<li></li>', '', $output_html);

		return $output_html;
	} else {
		return "0";
	}
}

add_shortcode( 'wplc_et_transcript_footer_text', 'wplc_transcript_get_footer_text' );
function wplc_transcript_get_footer_text() {
  $wplc_settings = wplc_get_options();
	return html_entity_decode(stripslashes($wplc_settings['wplc_et_email_footer']));
}

add_shortcode( 'wplc_et_transcript_header_text', 'wplc_transcript_get_header_text' );
function wplc_transcript_get_header_text() {
  $wplc_settings = wplc_get_options();

	global $current_chat_id;
	$cid = $current_chat_id;

	$from_email = "Unknown@unknown.com";
	$from_name = "User";
	if ( intval( $cid ) > 0 ) {
		$chat_data = wplc_get_chat_data( $cid );
		if ( isset( $chat_data->email ) ) {
			$from_email = $chat_data->email;
		}
		if ( isset( $chat_data->name ) ) {
			$from_name = $chat_data->name;
		}
	}
	return "<h3>".$from_name." (".$from_email.")"."</h3>".html_entity_decode(stripslashes($wplc_settings['wplc_et_email_header']));
}

function wplc_features_admin_js() {
	wp_register_script('wplc-admin-features', plugins_url('/js/wplc_admin_pro_features.js', __FILE__), array('wplc-admin-chat-js'), WPLC_PLUGIN_VERSION, true);
	wp_enqueue_script('wplc-admin-features');
}

// add_action('admin_notices', 'wplc_encryption_deprecated_notice');
/**
 * Notice of doom
*/
function wplc_encryption_deprecated_notice(){
  if(isset($_GET['wplc_encryption_dismiss_notice'])){
  	if(wp_verify_nonce($_GET['wplc_dismiss_nonce'], 'wplc_encrypt_note_nonce')){
    	update_option('WPLC_ENCRYPT_DEPREC_NOTICE_DISMISSED', 'true');
    }
  }

  if(isset($_GET['page'])){
  	if($_GET['page'] === 'wplivechat-menu-settings'){
        $encrypt_deprec_notice_dismissed = get_option('WPLC_ENCRYPT_DEPREC_NOTICE_DISMISSED', false);

        if($encrypt_deprec_notice_dismissed === false || $encrypt_deprec_notice_dismissed === 'false'){
          $dismiss_nonce = wp_create_nonce('wplc_encrypt_note_nonce');
          $encrypt_note = __('Please note, local message encryption and local server options will be deprecated in the next major release. All encryption and message delivery will handled by our external servers in future.', 'wp-live-chat-support');

          $output = "<div class='update-nag' style='margin-bottom: 5px;'>";
          $output .=     "<strong>" . __("Deprecation Notice - Message Encryption & Local Server", 'wp-live-chat-support') . "</strong><br>";
          $output .=     "<p>" . $encrypt_note . "</p>";
          $output .=     "<a class='button' href='?page=" . htmlspecialchars(sanitize_text_field($_GET['page'])) ."&wplc_encryption_dismiss_notice=true&wplc_dismiss_nonce=$dismiss_nonce'>" . __("Dismiss", 'wp-live-chat-support') . "</a>";
          $output .= "</div>";
          echo $output;
        }
    }
  }
}
