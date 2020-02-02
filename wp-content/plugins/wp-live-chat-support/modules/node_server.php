<?php
/*
 * Node Code
*/

if (!defined('ABSPATH')) {
  exit;
}

define("tcx_NODE_END_POINTS_ROUTE", "api/v1/");
define("tcx_NODE_END_POINT_TOKEN", "zf6fe1399sdfgsdfg02ad09ab6a8cb7345s");

add_action("wplc_activate_hook", "wplc_node_server_token_get", 10);
add_action("wplc_update_hook", "wplc_node_server_token_get", 10);

/**
 * Checks if a secret key has been created.
 * If not create one for use in the API
 *
 * @return void
 */
function wplc_node_server_token_get($reset = false) { 
  $tk = '';
  if (!$reset) {
    $tk = get_option("wplc_node_server_secret_token");
  }
  if (empty($tk)) {
    $tk = wplc_node_server_token_create();
    update_option("wplc_node_server_secret_token", $tk);
  }
  return $tk;
}

add_action("wplc_admin_dashboard_render", "wplc_admin_dashboard");
function wplc_admin_dashboard() {
  $wplc_node_token = wplc_node_server_token_get();
  $variables = array("node_token" => $wplc_node_token, "action" => "wordpress");
  $variables = apply_filters("wplc_admin_dashboard_layout_node_request_variable_filter", $variables);
  $wplc_settings = wplc_get_options();
  ?>
  <div class='nifty_top_wrapper'>
    <div class="wrap">
      <div class='floating-right-toolbar'>
        <label for="user_list_mobile_control" style="margin-bottom: 0;"><i id="toolbar-item-user_list" class="fa fa-bars fa-fw" title="<?php _e('Toggle user list', 'wp-live-chat-support'); ?>"></i></label>
        <i id="toolbar-item-fullscreen-wp" class="fa fa-clone fa-fw" title="<?php _e('Toggle WordPress Menu for a full screen experience', 'wp-live-chat-support'); ?>."></i>
      </div>
      <div id="page-wrapper" style='position:relative;'>
        <div class='nifty_bg_holder'>
          <div class='nifty_bg_holder_text'><img src='<?php echo WPLC_PLUGIN_URL; ?>images/wplc_loading.png' width='50' /><br /><br />
            <div id='nifty_bg_holder_text_inner'>Connecting...</div>
          </div>
          <div class='tcx_tips_hints'></div>
        </div>

        <div class="nifty_admin_overlay" style="display:none">

        </div>

        <div class="nifty_admin_chat_prompt" style="display:none">
          <div class="nifty_admin_chat_prompt_title" id='nifty_admin_chat_prompt_title'><?php _e('Please Confirm', 'wp-live-chat-support'); ?></div>
          <div class="nifty_admin_chat_prompt_message"></div>
          <div class="nifty_admin_chat_prompt_actions">
            <button class="btn btn-info" id="nifty_admin_chat_prompt_confirm"><?php _e('Confirm', 'wp-live-chat-support'); ?></button>
            <button class="btn btn-secondary" id="nifty_admin_chat_prompt_cancel"><?php _e('Cancel', 'wp-live-chat-support'); ?></button>
          </div>
        </div>

        <div id='nifty_wrapper'>
          <input type="checkbox" id="user_list_mobile_control" name="user_list_mobile_control" checked="checked">
          <div id='user_list' class='col-md-3'>
            <?php if ($wplc_settings['wplc_allow_agents_set_status'] && !empty($variables['aid'])) { ?>
              <div id='choose_online'>
                <div id="wplc_agent_status_text" style="display: inline-block; padding-left: 10px;"></div>
                <input type="checkbox" class="wplc_switchery" name="wplc_agent_status" id="wplc_agent_status" <?php echo (wplc_get_agent_accepting(get_current_user_id()) ? ' checked' : ''); ?> />
              </div>
            <?php } ?>
            <div id='user_count'>
              <span id='active_count'>... </span>
              <span id='active_count_string'><?php _e('Active visitors', 'wp-live-chat-support'); ?></span>
            </div>
            <div class='exp_cols'>
              <div class='col1'><?php _e('Name', 'wp-live-chat-support'); ?></div>
              <div class='col3'><?php _e('Agent', 'wp-live-chat-support'); ?></div>
            </div>
            <div class='userListBox_Wrapper'>
              <div class='userListBox'></div>
            </div>
            <div id='agent_list'>
              <h4 id='nifty_agent_heading'><?php _e('Agents', 'wp-live-chat-support'); ?></h4>
              <ul class='online_agent_list'>
              </ul>
            </div>
          </div>

          <div id='chat_area' class='col-md-9'>
            <?php if ($variables['include_media_sharing']) { ?>
              <div id="chat_drag_zone" style="display:none;">
                <div id="chat_drag_zone_inner"><span id='drag_zone_inner_text'><?php _e('Drag Files Here', 'wp-live-chat-support'); ?></span></div>
              </div>
            <?php } ?>
            <div class="chatArea" style='display:none;'>
              <div class="chatInfoArea">
                <span class="minChat btn" id="tcx_min_chat" title="<?php _e('Minimize Chat', 'wp-live-chat-support'); ?>"><i class="fas fa-window-close"></i></span>
                <div class="dropdown pull-right"></div>

                <div class="btn-group inchat-menu pull-right">
                  <button class="btn dropdown-toggle" type="button" id="inchat_drop_down" data-toggle="dropdown">
                    <em class="fas fa-bars"></em>
                    <span class="caret"></span>
                  </button>
                  <div class="dropdown-menu" aria-labelledby='inchat_drop_down'>
                    <?php if ($variables['include_transfers']) { ?>
                      <a href="javascript:void(0);" class='dropdown-item chatTransfer' id='chatTransferLink'><?php _e('Invite Agent', 'wp-live-chat-support'); ?></a>
                      <a href="javascript:void(0);" class='dropdown-item chatTransferDepartment' id='chatTransferDepLink'><?php _e('Invite Department', 'wp-live-chat-support'); ?></a>
                      <a href="javascript:void(0);" class='dropdown-item chatDirectUserToPagePrompt' id='chatDirectUserToPageLink'><?php _e('Direct User To Page', 'wp-live-chat-support'); ?></a>
                    <?php } ?>
                    <a href="javascript:void(0);" class='dropdown-item chatTranscript' id='chatTranscriptTitle' style='display:none;'><?php _e('Transcript', 'wp-live-chat-support'); ?></a>
                    <a href="javascript:void(0);" class='dropdown-item chatClose' id='chatCloseTitle' style="display:none;"><?php _e('Leave chat', 'wp-live-chat-support'); ?></a>
                    <a href="javascript:void(0);" class='dropdown-item endChat' id='chatEndTitle'><?php _e('End chat', 'wp-live-chat-support'); ?></a>
                  </div>
                </div>


                <div class='user_header_wrapper_img'>
                  <div class='user_gravatar'></div>
                </div>
                <div class='user_header_wrapper_info'>
                  <h3><span class='chatInfoArea-Name'><?php _e('Name', 'wp-live-chat-support'); ?></span></h3>
                  <h4><span class='chatInfoArea-Email'><?php _e('Email', 'wp-live-chat-support'); ?></span></h4>
                  <p><span class='chatInfoArea-Info1'><?php _e('Something', 'wp-live-chat-support'); ?></span></p>
                </div>

              </div>

              <ul class="messages" id="messages"></ul>

              <?php if ($variables['include_quick_responses']) { ?>
                <div id="quick_response_drawer_handle"><i class="fa fa-bolt" title="<?php _e('Quick Responses', 'wp-live-chat-support'); ?>"></i></div>
              <?php } ?>
              <div class='typing_preview_wplc' style='display:none;'></div>
              <div class='tcx_join_chat_div'><button class='tcx_join_chat_btn btn btn-success' id='nifty_join_chat_button'><?php _e('Join chat', 'wp-live-chat-support'); ?></button></div>
              <input class="inputMessage wdt-emoji-bundle-enabled" id="inputMessage" placeholder="<?php _e('Type here...', 'wp-live-chat-support'); ?>" />
              <span class="editing_hints"><strong>*<?php _e('bold', 'wp-live-chat-support'); ?>*</strong> <em>_<?php _e('italics', 'wp-live-chat-support'); ?>_</em> <code>`<?php _e('code', 'wp-live-chat-support'); ?>`</code> <code>```<?php _e('preformatted', 'wp-live-chat-support'); ?>```</code></span>
              <img id="wplc_send_msg" class='nifty_send_arrow' style="display:none;" src='<?php echo WPLC_PLUGIN_URL; ?>images/arrow.png' />

              <?php if ($variables['include_media_sharing']) { ?>
                <label for="nifty_file_input" class='nifty_add_media_button'>
                  <i class="nifty_tedit_icon fa fa-paperclip" id="nifty_attach"></i>
                  <i class="nifty_attach_icon far fa-circle-notch fa-spin" id="nifty_attach_uploading_icon" style="display:none;"></i>
                  <i class="nifty_attach_icon fa fa-check-circle" id="nifty_attach_success_icon" style="display:none;"></i>
                  <i class="nifty_attach_icon fa fa-minus-circle" id="nifty_attach_fail_icon" style="display:none;"></i>
                </label>

                <input type="file" id="nifty_file_input" name="nifty_file_input" style="display:none">
              <?php } else { ?>
                <label for='nifty_add_media' class="nifty_add_media_button"><i class="fa fa-plus"></i></label>
                <input type="checkbox" id="nifty_add_media" />
              <?php } ?>

            </div>
            <div class="infoArea">
              <div class="dropdown filter-menu pull-right">
                <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" style='margin-right:25px;' title="<?php _e('Filter the user list based on activity.', 'wp-live-chat-support'); ?>" id='nifty_filter_button'>
                  Filters
                  <span class="caret"></span>
                </button>
                <div class="dropdown-menu">
                  <a href="javascript:void(0);" class='dropdown-item filter-new-visitors' id='nifty_new_visitor_item'><?php _e('New Visitors (3 Min)', 'wp-live-chat-support'); ?></a>
                  <a href="javascript:void(0);" class='dropdown-item filter-active-chats' id='nifty_active_chats_item'><?php _e('Active Chats', 'wp-live-chat-support'); ?></a>
                  <a href="javascript:void(0);" class="dropdown-item filter-referer" id="nifty_referer_item"><?php _e('Page URL', 'wp-live-chat-support'); ?></a>
                  <a href="javascript:void(0);" class='dropdown-item filter-clear' id='nifty_clear_filters_item'><?php _e('Clear Filters', 'wp-live-chat-support'); ?></a>
                </div>
                <div class='filter-active-tag-container' style='display:none;'>
                  <i class='fa fa-times-circle filter-clear' style="cursor:pointer;"></i>
                  <span class='filter-active-tag-inner'></span>
                </div>
              </div>

              <div id="nifty_referer_options" style="display:none;float:right;margin-right:10px;">
                <input placeholder="Page URL" type="text" id="nifty_referer_url" style="width:100%;float:right;">
                <label style="font-weight:normal;">
                  <input type="checkbox" id="nifty_referer_contains" style="margin:0;"> <?php _e('Contains', 'wp-live-chat-support'); ?>
                </label>
              </div>

              <h2 id='nifty_active_chats_heading'><?php _e('Active visitors', 'wp-live-chat-support'); ?></h2>

              <div class='visitorListBoxHeader'>
                <div class='vcol visCol' id='nifty_vis_col_heading'><?php _e('Visitor', 'wp-live-chat-support'); ?></div>
                <div class='vcol visStatusCol' id='nifty_vis_info_heading'><?php _e('Info', 'wp-live-chat-support'); ?></div>
                <div class='vcol visPageCol' id='nifty_vis_page_heading'><?php _e('Page', 'wp-live-chat-support'); ?></div>
                <div class='vcol visChatStatusCol' id='nifty_vis_status_heading'><?php _e('Chat Status', 'wp-live-chat-support'); ?></div>
                <?php if ($variables['include_departments']) { ?>
                  <div class='vcol visChatDepCol' id='nifty_vis_dep_heading'><?php _e('Department', 'wp-live-chat-support'); ?></div>
                <?php } ?>
                <div class='vcol visActionCol'></div>
              </div>
              <?php if ($wplc_settings["wplc_settings_enabled"] == 1) { ?>
                <div class='visitorListBox'>

                </div>
              <?php } ?>

            </div>
          </div>
        </div>
        <div class="wdt-emoji-popup">
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
                <input id="wdt-emoji-search" type="text" placeholder="<?php _e('Search', 'wp-live-chat-support'); ?>">
                <h3 id="wdt-emoji-search-result-title"><?php _e('Search Results', 'wp-live-chat-support'); ?></h3>
                <div class="wdt-emoji-sections"></div>
                <div id="wdt-emoji-no-result"><?php _e('No emoji found', 'wp-live-chat-support'); ?></div>
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
        </div>

        <script>
          jQuery(document).ready(function() {
            jQuery("#wplc_footer_loading_icon").hide();
            jQuery("#wplc_footer_message").fadeIn();
          });
        </script>
      </div>
    </div>
  <?php
  }

  /**
   * Generates a new Secret Token
   *
   * @return string
   */
  function wplc_node_server_token_create() {
    $the_code = rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000);
    $the_time = time();
    $token = md5($the_code . $the_time);
    return $token;
  }

  /**
   * Post to the NODE server -
   *
   * @param string $route Route you would like to use for the node server
   * @param string $form_data data to send
   * @return string (or false on fail)
   */
  function wplc_node_server_post($route, $form_data) {

    $url = trailingslashit(WPLC_CHAT_SERVER) . trailingslashit(tcx_NODE_END_POINTS_ROUTE) . $route;

    if (!isset($form_data['token'])) {
      $form_data['token'] = tcx_NODE_END_POINT_TOKEN; //Add the security token
    }

    if (!isset($form_data['api_key'])) {
      $form_data['api_key'] = wplc_node_server_token_get(); //Add the security token
    }

    if (!isset($form_data['origin_url'])) {
      $ajax_url = admin_url('admin-ajax.php');
      $origin_url = str_replace("/wp-admin/admin-ajax.php", "", $ajax_url);
      $form_data['origin_url'] = $origin_url; //Add the security token
    }

    // $options = array();
    // $context  = @stream_context_create($options);
    // $result = @file_get_contents($url . "?" . http_build_query($form_data), false, $context);

    $result = wp_remote_get($url . "?" . http_build_query($form_data));
    if (is_array($result)) {
      $result = $result['body']; // use the content
    }

    if ($result === FALSE) {
      return false;
    } else {
      return $result;
    }
  }

  /**
   * Loads remote dashboard
   *
   * @return void
   */
  function wplc_admin_dashboard_layout_node($location = 'dashboard') {
    if ($location == 'dashboard') {
      if (!wplc_user_is_agent()) {
        echo "<div class='error below-h1'><h2>" . __("Error", 'wp-live-chat-support') . "</h2><p>" . __("Only chat agents can access this page.", 'wp-live-chat-support') . "</p></div>";
        return;
      }
    }
    do_action("wplc_admin_remote_dashboard_above");
    echo "<div id='tcx_content_wrapper'></div>";
    if ($location == 'dashboard') {
      if (!isset($_GET['action']) || 'history' !== $_GET['action']) {
        echo "<div class='wplc_remote_dash_below_contianer'>";
        do_action("wplc_admin_dashboard_render");
        do_action("wplc_admin_remote_dashboard_below");
        echo "</div>";
      }
    } else {
      if (!empty($_GET['page']) && 'wplivechat-menu' === $_GET['page']) { // This div is also hidden by js under the same conditions
        echo "<div class='wplc_remote_dash_below_contianer'>";
        do_action("wplc_admin_remote_dashboard_below");
        do_action("wplc_admin_dashboard_render");
        echo "</div>";
      } else {
        $wplc_settings = wplc_get_options();
        if ($wplc_settings['wplc_use_node_server']) {
          if (isset($_GET['page']) && $_GET['page'] === 'wplivechat-menu') { } else {
            if ($wplc_settings['wplc_enable_all_admin_pages']) {
              echo "<div class='wplc_remote_dash_below_contianer'>";
              do_action("wplc_admin_dashboard_render");
              echo "</div>";
            }
          }
        }
      }
    }
  }

  add_action('admin_enqueue_scripts', 'wplc_enqueue_dashboard_popup_scripts');
  /**
   * Enqueues the scripts for the admin dashboard popup icon and chat box
   * @return void
   */
  function wplc_enqueue_dashboard_popup_scripts() {
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('wplc-admin-popup', WPLC_PLUGIN_URL . 'js/wplc_admin_popup.js', array('jquery-ui-draggable'), WPLC_PLUGIN_VERSION);

    wp_button_pointers_load_scripts('toplevel_page_wplivechat-menu');
  }

  add_action("admin_footer", "wplc_dashboard_display_decide");
  /**
   * Decide whether or not to display the dashboard layout on an admin page
   * @return  void
   */
  function wplc_dashboard_display_decide() {
    $wplc_settings = wplc_get_options();
    if ($wplc_settings['wplc_use_node_server']) {
      //Node in use, load remote dashboard
      if (isset($_GET['page']) && $_GET['page'] === 'wplivechat-menu') { } else {
        /**
         * Check to see if we have enabled "Enable chat dashboard and notifications on all admin pages"
         */

        if ($wplc_settings['wplc_enable_all_admin_pages']) {
          wplc_admin_dashboard_layout_node('other');
          echo '<div class="floating-right-toolbar">';
          echo '<label for="user_list_tcx_control" style="margin-bottom: 0; display:none;"></label>';
          echo '<i id="toolbar-item-open-tcx" class="fa fa-fw" style="background:url(\'' . plugins_url('../images/tcx48px.png', __FILE__) . '\') no-repeat; background-size: cover;"></i>';
          echo '</div>';
        }
      }
    }
  }

  /**
   * Loads remote dashboard scripts and styles
   *
   * @return void
   */
  function wplc_admin_remote_dashboard_scripts($wplc_settings) {
    $wplc_current_user = get_current_user_id();
    if (wplc_user_is_agent($wplc_current_user)) {
      $user_info = get_userdata($wplc_current_user);
      $user_array = wplc_get_agent_users();
      $a_array = array();
      if ($user_array) {
        foreach ($user_array as $user) {
          $a_array[$user->ID] = array();
          $a_array[$user->ID]['name'] = $user->display_name;
          $a_array[$user->ID]['display_name'] = $user->display_name;
          $a_array[$user->ID]['md5'] = md5($user->user_email);
          $a_array[$user->ID]['email'] = md5($user->user_email);
        }
      }

      wp_register_script('wplc-admin-js-sockets', WPLC_PLUGIN_URL . "js/vendor/sockets.io/socket.io.slim.js", false, WPLC_PLUGIN_VERSION, true);
      wp_enqueue_script('wplc-admin-js-sockets');

      wp_register_script('wplc-admin-js-bootstrap', WPLC_PLUGIN_URL . "js/vendor/bootstrap/dist/js/bootstrap.js", array("wplc-admin-js-sockets"), WPLC_PLUGIN_VERSION, true);
      wp_enqueue_script('wplc-admin-js-bootstrap');

      wplc_register_common_node();

      // NB: This causes Failed to initVars ReferenceError: wplc_show_date is not defined when uncommented and enabled
      if (!$wplc_settings['wplc_disable_emojis']) {
        wp_register_script('wplc-admin-js-emoji', WPLC_PLUGIN_URL . "js/vendor/wdt-emoji/emoji.min.js", array("wplc-admin-js-sockets"), WPLC_PLUGIN_VERSION, false);
        wp_enqueue_script('wplc-admin-js-emoji');
        wp_register_script('wplc-admin-js-emoji-bundle', WPLC_PLUGIN_URL . "js/vendor/wdt-emoji/wdt-emoji-bundle.min.js", array("wplc-admin-js-emoji"), WPLC_PLUGIN_VERSION, false);
        wp_enqueue_script('wplc-admin-js-emoji-bundle');
      }

      wp_register_script('md5', WPLC_PLUGIN_URL . 'js/md5.js', array("wplc-admin-js-sockets"), WPLC_PLUGIN_VERSION, true);
      wp_enqueue_script('md5');

      $dependencies = array();
      if (!$wplc_settings['wplc_disable_emojis']) {
        $dependencies[] = "wplc-admin-js-emoji-bundle";
      }
      wp_register_script('wplc-admin-js-agent', WPLC_PLUGIN_URL . 'js/wplc_agent_node.js', $dependencies, WPLC_PLUGIN_VERSION, true);
      wp_localize_script('wplc-admin-js-agent', "tcx_ping_sound_notification_enabled", array('value' => boolval($wplc_settings['wplc_enable_msg_sound'])));

      wp_register_script('my-wplc-admin-chatbox-ui-events', WPLC_PLUGIN_URL . 'js/wplc_u_admin_chatbox_ui_events.js', array('jquery'), WPLC_PLUGIN_VERSION, true);
      wp_enqueue_script('my-wplc-admin-chatbox-ui-events');



      $wplc_et_ajax_nonce = wp_create_nonce("wplc_et_nonce");
      wp_register_script('wplc_transcript_admin', WPLC_PLUGIN_URL . 'js/wplc_transcript.js', array(), WPLC_PLUGIN_VERSION, true);
      $wplc_transcript_localizations = array(
        'ajax_nonce'          => $wplc_et_ajax_nonce,
        'string_loading'      => __("Sending transcript...", 'wp-live-chat-support'),
        'string_title'        => __("Chat Transcript", 'wp-live-chat-support'),
        'string_close'        => __("Close", 'wp-live-chat-support'),
        'string_chat_emailed' => __("The chat transcript has been emailed.", 'wp-live-chat-support'),
        'string_error1'       => __("There was a problem emailing the chat.", 'wp-live-chat-support')
      );
      wp_localize_script('wplc_transcript_admin', 'wplc_transcript_nonce', $wplc_transcript_localizations);
      wp_enqueue_script('wplc_transcript_admin');

      $wplc_node_token = wplc_node_server_token_get();

      if (isset($_GET['page']) && $_GET['page'] === 'wplivechat-menu') {
        wp_localize_script('wplc-admin-js-agent', 'tcx_in_dashboard', array('value' => true));
      } else {
        wp_localize_script('wplc-admin-js-agent', 'tcx_in_dashboard', array('value' => false));
      }

      $inline_error_message = "<div class='error below-h1' style='display:none;' id='tcx_inline_connection_error'>
	                				<p>" . __("Connection Error", 'wp-live-chat-support') . "<br /></p>
	                				<p>" . __("We are having some trouble contacting the server. Please try again later.", 'wp-live-chat-support') . "</p>
	            				</div>";
      wp_localize_script('wplc-admin-js-agent', 'tcx_remote_form_error', $inline_error_message);


      wp_localize_script('wplc-admin-js-agent', 'tcx_enable_visitor_sound', array('value' => $wplc_settings['wplc_enable_visitor_sound']));

      $agent_display_name = $user_info->display_name;

      wp_localize_script('wplc-admin-js-agent', 'agent_id', "" . $wplc_current_user);
      wp_localize_script('wplc-admin-js-agent', 'tcx_agent_name', apply_filters("wplc_agent_display_name_filter", $agent_display_name));
      wp_localize_script('wplc-admin-js-agent', 'nifty_api_key', wplc_node_server_token_get());

      //For node verification
      wplc_check_guid();

      wp_localize_script('wplc-admin-js-agent', 'wplc_guid', get_option('WPLC_GUID', ''));
      wp_localize_script('wplc-admin-js-agent', 'tcx_agent_verification_end_point', rest_url('wp_live_chat_support/v1/validate_agent'));
      wp_localize_script('wplc-admin-js-agent', 'tcx_disable_add_message', array('value' => true));
      wp_localize_script('wplc-admin-js-agent', 'wplc_nonce', wp_create_nonce("wplc"));
      wp_localize_script('wplc-admin-js-agent', 'wplc_cid', null);
      wp_localize_script('wplc-admin-js-agent', 'wplc_chat_name', null);

      $wplc_chat_detail = array(
        'name' => $wplc_settings['wplc_show_name'],
        'avatar' => $wplc_settings['wplc_show_avatar'],
        'date' => $wplc_settings['wplc_show_date'],
        'time' =>  $wplc_settings['wplc_show_time'],
      );
      wp_localize_script('wplc-admin-js-agent', 'wplc_show_chat_detail', $wplc_chat_detail);
      wp_localize_script('wplc-admin-chat-server', 'wplc_show_chat_detail', $wplc_chat_detail);

      wp_localize_script('wplc-admin-js-agent', 'wplc_agent_data', $a_array);
      wp_localize_script('wplc-admin-js-agent', 'all_agents', $a_array);

      wp_localize_script('wplc-admin-js-agent', 'wplc_url', plugins_url('', dirname(__FILE__)));

      if (isset($wplc_settings['wplc_settings_enabled']) && intval($wplc_settings["wplc_settings_enabled"]) == 2) {
        $wplc_disabled_html = __("Chat is disabled in settings area, re-enable", 'wp-live-chat-support');
        $wplc_disabled_html .= " <a href='?page=wplivechat-menu-settings'>" . __("here", 'wp-live-chat-support') . "</a>";
        wp_localize_script('wplc-admin-js-agent', 'wplc_disabled', array('value' => true));
        wp_localize_script('wplc-admin-js-agent', 'wplc_disabled_html',  $wplc_disabled_html);
      } else {
        wp_localize_script('wplc-admin-js-agent', 'wplc_disabled', array('value' => false));
      }

      //Added rest nonces
      if (class_exists("WP_REST_Request")) {
        wp_localize_script('wplc-admin-js-agent', 'wplc_restapi_enabled', array('value' => true));
        wp_localize_script('wplc-admin-js-agent', 'wplc_restapi_token', get_option('wplc_api_secret_token'));
        wp_localize_script('wplc-admin-js-agent', 'wplc_restapi_endpoint', rest_url('wp_live_chat_support/v1'));
        wp_localize_script('wplc-admin-js-agent', 'tcx_override_upload_url', rest_url('wp_live_chat_support/v1/remote_upload'));
        wp_localize_script('wplc-admin-js-agent', 'wplc_restapi_nonce', wp_create_nonce('wp_rest'));
      } else {
        wp_localize_script('wplc-admin-js-agent', 'wplc_restapi_enabled', array('value' => false));
        wp_localize_script('wplc-admin-js-agent', 'wplc_restapi_nonce', '');
      }

      // $agent_tagline = apply_filters( "wplc_filter_simple_agent_data_agent_tagline", '', get_current_user_id() );
      $agent_tagline = get_user_meta(intval(get_current_user_id()), 'wplc_user_tagline', true);
      $head_data = array(
        'tagline' => $agent_tagline
      );
      wp_localize_script('wplc-admin-js-agent', 'wplc_head_data', $head_data);
      wp_localize_script('wplc-admin-js-agent', 'wplc_user_chat_notification_prefix', __("User received notification:", 'wp-live-chat-support'));

      wp_localize_script('wplc-admin-js-agent', 'tcx_valid_direct_to_page_array', wplc_node_pages_posts_array());

      wp_localize_script('wplc-admin-js-agent', 'tcx_ringer_count', array('value' => intval($wplc_settings['wplc_new_chat_ringer_count'])));

      wp_localize_script('wplc-admin-js-agent', 'tcx_new_chat_notification_title', __('New chat received', 'wp-live-chat-support'));
      wp_localize_script('wplc-admin-js-agent', 'tcx_new_chat_notification_text', __("A new chat has been received. Please go the 'Live Chat' page to accept the chat", 'wp-live-chat-support'));

      $wplc_notification_icon = plugin_dir_url(dirname(__FILE__)) . 'images/wplc_notification_icon.png';
      wp_localize_script('wplc-admin-js-agent', 'tcx_new_chat_notification_icon', $wplc_notification_icon);

      do_action("wplc_admin_remoter_dashboard_scripts_localizer");  //For pro localization of agents list, and departments

      wp_enqueue_script('wplc-admin-js-agent');

      wp_register_script('wplc-admin-chat-server', WPLC_PLUGIN_URL . 'js/wplc_server.js', array("wplc-admin-js-agent", "wplc-admin-js-sockets"), WPLC_PLUGIN_VERSION, true); //Added this for async storage calls
      wp_enqueue_script('wplc-admin-chat-server');

      wp_localize_script('wplc-admin-chat-server', 'wplc_datetime_format', array(
        'date_format' => get_option('date_format'),
        'time_format' => get_option('time_format'),
      ));

      wp_register_script('wplc-admin-chat-events', WPLC_PLUGIN_URL . 'js/wplc_u_admin_events.js', array("wplc-admin-js-agent", "wplc-admin-js-sockets", "wplc-admin-chat-server"), WPLC_PLUGIN_VERSION, true); //Added this for async storage calls
      wp_enqueue_script('wplc-admin-chat-events');
    }
  }


  /**
   * Loads remote dashboard styles
   *
   * @return void
   */
  function wplc_admin_remote_dashboard_styles() {
    $wplc_settings = wplc_get_options();

    wp_register_style('wplc-admin-style', WPLC_PLUGIN_URL . "css/chat_dashboard/admin_style.css", array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style('wplc-admin-style');

    if (!isset($wplc_settings['wplc_show_avatar']) || (isset($wplc_settings['wplc_show_avatar']) && intval($wplc_settings['wplc_show_avatar']) == 0)) {
      wp_add_inline_style('wplc-admin-style', ".wplc-user-message, .wplc-admin-message { padding-left: 0 !important; }");
    } else if (!isset($wplc_settings['wplc_show_name']) || (isset($wplc_settings['wplc_show_name']) && intval($wplc_settings['wplc_show_name']) == 0)) {
      //User has enabled the gravatar, but has chosen to hide the user name.
      //This causes some issues with admin display so let's just add some different styling to get around this
      $inline_identity_css =
        "
            .wplc-admin-message-avatar, .wplc-user-message-avatar {
			    max-width:28px !important;
			    max-height:28px !important;
			}

			.wplc-admin-message, .wplc-user-message{
			    padding-left:25px !important;
			}

			.wplc-admin-message::before, .wplc-user-message::before  {
			    content: ' ';
			    width: 7px;
			    height: 7px;
			    background:#343434;
			    position: absolute;
			    left: 12px;
			    border-radius: 2px;
			    z-index: 1;
			}

			.wplc-user-message::before {
			    background:#2b97d2;
			}
            ";

      wp_add_inline_style('wplc-admin-style', $inline_identity_css);
    }

    wp_register_style('wplc-admin-style-bootstrap', WPLC_PLUGIN_URL . "css/bootstrap.css", array(), WPLC_PLUGIN_VERSION);
    wp_enqueue_style('wplc-admin-style-bootstrap');

    if (!$wplc_settings['wplc_disable_emojis']) {
      wp_register_style('wplc-admin-style-emoji', WPLC_PLUGIN_URL . "js/vendor/wdt-emoji/wdt-emoji-bundle.css", array(), WPLC_PLUGIN_VERSION);
      wp_enqueue_style('wplc-admin-style-emoji');
    }

    do_action("wplc_admin_remote_dashboard_styles_hook");
  }

  /*
 * Add action for notice checks
*/
  if (isset($_GET['page']) && $_GET['page'] === "wplivechat-menu") {
    add_action("wplc_admin_remote_dashboard_above", "wplc_active_chat_box_notices");
  }

  add_action("admin_notices", "wplc_node_v8_plus_notice_dismissable");
  /*
 * Displays an admin notice (which can be dismissed), to notify any V8+ users of the node option (if not already checked)
*/
  function wplc_node_v8_plus_notice_dismissable() {
    $page = '';
    if (isset($_GET['page'])) {
      $page = preg_replace('/[^a-z0-9-]/', '', sanitize_text_field($_GET['page']));
    }
    if (!empty($page) && strpos($page, 'wplivechat') === 0) { // only if it begins with wplivechat
      if (isset($_GET['wplc_dismiss_notice_v8']) && $_GET['wplc_dismiss_notice_v8'] === "true") {
        update_option("wplc_node_v8_plus_notice_dismissed", 'true');
      }

      $wplc_settings = wplc_get_options();
      if (!$wplc_settings['wplc_use_node_server']) {
        //User is not on node, let's check if they have seen this notice before, if not, let's show a notice
        $wplc_has_notice_been_dismissed = get_option("wplc_node_v8_plus_notice_dismissed", false);
        if ($wplc_has_notice_been_dismissed === false) {
          //Has not been dismissed
          $output = "<div class='notice notice-warning' style='border-color: #0180bc;'>";
          $output .=     "<p><strong>" . __('Welcome to V8 of WP Live Chat by 3CX', 'wp-live-chat-support') . "</strong></p>";
          $output .=  "<p>" . __('Did you know, this version features high speed message delivery, agent to agent chat, and a single window layout?', 'wp-live-chat-support') . "</p>";
          $output .=  "<p>" . __('To activate this functionality please navigate to Live Chat -> Settings -> Advanced Features -> And enable 3CX High Performance Chat Cloud Servers.', 'wp-live-chat-support') . "</p>";

          $output .=  "<p>";
          $output .=    "<a href='?page=wplivechat-menu-settings#tabs-beta' class='button button-primary'>" . __("Show me!", 'wp-live-chat-support') . "</a> ";
          $output .=    "<a href='?page=" . $page . "&wplc_dismiss_notice_v8=true' id='wplc_v8_dismiss_node_notice' class='button'>" . __("Don't Show This Again", 'wp-live-chat-support') . "</a>";
          $output .=  "</p>";
          $output .= "</div>";
          echo $output;
        }
      }
    }
  }

  add_filter('rest_url', 'wplc_node_rest_url_ssl_fix');
  /**
   * Changes the REST URL to include the SSL version if we are using SSL
   * See https://core.trac.wordpress.org/ticket/36451
   */
  function wplc_node_rest_url_ssl_fix($url) {
    if (is_ssl()) {
      $url = set_url_scheme($url, 'https');
      return $url;
    }
    return $url;
  }

  /**
   * Returns an array of pages/posts available on the site
   */
  function wplc_node_pages_posts_array() {
    $r = array(
      'depth'   => 0,
      'child_of'   => 0,
      'echo'     => false,
      'id'     => '',
      'class'   => '',
      'show_option_none'     => '',
      'show_option_no_change' => '',
      'option_none_value'   => '',
      'value_field'       => 'ID',
    );

    $pages = get_pages($r);
    $posts = get_posts(array('posts_per_page' => -1));

    $posts_pages = array_merge($pages, $posts);

    $return_array = array();

    foreach ($posts_pages as $key => $value) {
      $post_page_id = $value->ID;
      $post_page_title = $value->post_title;

      $return_array[get_permalink($post_page_id)] = $post_page_title;
    }

    return $return_array;
  }



  add_action("wplc_admin_remoter_dashboard_scripts_localizer", "wplc_admin_remote_dashboard_dynamic_translation_handler");
  /*
 * Localizes an array of strings and ids in the dashboard which need to be replaced
 * Loads the custom JS file responsible for replacing the content dynamically.
*/
  function wplc_admin_remote_dashboard_dynamic_translation_handler() {

    wp_register_script('wplc-admin-dynamic-translation', WPLC_PLUGIN_URL . 'js/wplc_admin_dynamic_translations.js', array("wplc-admin-js-agent", "wplc-admin-js-sockets", "jquery"), WPLC_PLUGIN_VERSION, true); //Added this for async storage calls

    $wplc_dynamic_translation_array = array(
      'nifty_bg_holder_text_inner' => __('Connecting...', 'wp-live-chat-support'),
      'nifty_admin_chat_prompt_title' => __('Please Confirm', 'wp-live-chat-support'),
      'nifty_admin_chat_prompt_confirm' => __('Confirm', 'wp-live-chat-support'),
      'nifty_admin_chat_prompt_cancel' => __('Cancel', 'wp-live-chat-support'),
      'active_count_string' => __('Active visitors', 'wp-live-chat-support'),
      'wplc-agent-info' => __('Agent(s) Online', 'wp-live-chat-support'),
      'wplc_history_link' => __('Chat History', 'wp-live-chat-support'),
      'nifty_agent_heading' => __('Agents', 'wp-live-chat-support'),
      'drag_zone_inner_text' => __('Drag Files Here', 'wp-live-chat-support'),
      'chatTransferLink' => __('Invite Agent', 'wp-live-chat-support'),
      'chatTransferDepLink' => __('Invite Department', 'wp-live-chat-support'),
      'chatDirectUserToPageLink' => __('Direct User To Page', 'wp-live-chat-support'),
      'chatCloseTitle' => __('Leave chat', 'wp-live-chat-support'),
      'chatEndTitle' => __('End chat', 'wp-live-chat-support'),
      'chatTransferUps' => __('Transfer', 'wp-live-chat-support'),
      'chatDirectUserToPageUps' => __('Direct User To Page', 'wp-live-chat-support'),
      'nifty_event_heading' => __('Events', 'wp-live-chat-support'),
      'nifty_join_chat_button' => __('Join chat', 'wp-live-chat-support'),
      'nifty_filter_button' => __('Filters', 'wp-live-chat-support'),
      'nifty_new_visitor_item' => __('New Visitors (3 Min)', 'wp-live-chat-support'),
      'nifty_active_chats_item' => __('Active Chats', 'wp-live-chat-support'),
      'nifty_clear_filters_item' => __('Clear Filters', 'wp-live-chat-support'),
      'nifty_active_chats_heading' => __('Active visitors', 'wp-live-chat-support'),
      'nifty_vis_col_heading' => __('Visitor', 'wp-live-chat-support'),
      'nifty_vis_info_heading' => __('Info', 'wp-live-chat-support'),
      'nifty_vis_page_heading' => __('Page', 'wp-live-chat-support'),
      'nifty_vis_status_heading' => __('Chat Status', 'wp-live-chat-support'),
      'nifty_vis_dep_heading' => __('Department', 'wp-live-chat-support'),
      'wdt-emoji-search-result-title' => __('Search Results', 'wp-live-chat-support'),
      'wdt-emoji-no-result' => __('No emoji found', 'wp-live-chat-support')
    );

    apply_filters("wplc_adming_dynamic_translation_filter", $wplc_dynamic_translation_array);


    wp_localize_script('wplc-admin-dynamic-translation', 'wplc_dynamic_translation_array', $wplc_dynamic_translation_array);
    wp_enqueue_script('wplc-admin-dynamic-translation');
  }




  add_action("wplc_admin_remoter_dashboard_scripts_localizer", "wplc_admin_remote_dashboard_localize_variables");
  /*
* Localizes all the admin variables
*/
  function wplc_admin_remote_dashboard_localize_variables() {
    $wplc_settings = wplc_get_options();
    $user_id = get_current_user_id();
    $user_department = get_user_meta($user_id, "wplc_user_department", true);
    $department_array = array();
    $departments = wplc_get_all_deparments_mrg();
    if ($departments) {
      foreach ($departments as $dep) {
        $department_array[$dep->id] = $dep->name;
      }
    }
    $departments['any'] = __("None", 'wp-live-chat-support');
    $default_department = $wplc_settings['wplc_default_department'];
    wp_localize_script('wplc-admin-js-agent', 'tcx_departments', $department_array);

    if ($wplc_settings['wplc_allow_department_selection'] && !empty($department_array)) {
      if (intval($default_department) >= 0) {
        wp_localize_script('wplc-admin-js-agent', 'tcx_default_department_tag', $department_array[$default_department]);
      } else {
        wp_localize_script('wplc-admin-js-agent', 'tcx_default_department_tag', $departments['any']);
      }
    } else {
      wp_localize_script('wplc-admin-js-agent', 'tcx_default_department_tag', 'any');
    }

    if (!empty($user_department)) {
      wp_localize_script('wplc-admin-js-agent', 'tcx_agent_department', $user_department);
    }
    wp_register_script('wplc-admin-chat-events-pro', WPLC_PLUGIN_URL . 'js/wplc_admin_pro_events.js', array("wplc-admin-js-agent", "wplc-admin-chat-events", "wplc-admin-chat-server"), WPLC_PLUGIN_VERSION, true); //Added this for async storage calls
    wp_enqueue_script('wplc-admin-chat-events-pro');
    wp_register_script('wplc-admin-tcx-event-tracking-pro', WPLC_PLUGIN_URL . 'js/wplc_tcx_admin_events.js', array("wplc-admin-js-agent", "wplc-admin-chat-events", "wplc-admin-chat-server"), WPLC_PLUGIN_VERSION, true); //Added this for async storage calls
    wp_enqueue_script('wplc-admin-tcx-event-tracking-pro');
  }

  add_filter("wplc_admin_dashboard_layout_node_request_variable_filter", "wplc_admin_dashboard_layout_node_request_add_mrg_variables", 10, 1);
  /*
 * Adds the Pro request variables to our node request
*/
  function wplc_admin_dashboard_layout_node_request_add_mrg_variables($variables) {
    if (is_array($variables)) {
      $variables['include_filters'] = true;
      $variables['include_transfers'] = true;
      $variables['include_media_sharing'] = true;
      $variables['include_quick_responses'] = true;
      $variables['include_departments'] = true;
      $variables['aid'] = get_current_user_id();
      $variables['agent_count'] = wplc_get_online_agent_users_count();
    }
    return $variables;
  }

  add_filter("wplc_admin_remote_dashboard_localize_tips_array", "wplc_admin_remote_dashboard_localize_tips_array_mrg_handler", 1, 1);
  /*
 * Overrides the tip array in the Pro add-on
*/
  function wplc_admin_remote_dashboard_localize_tips_array_mrg_handler($tip_array) {
    $tip_array = array(
      "0" => "<p>" . __("You can transfer chats from within a chat by clicking on the in chat menu, and selecting Transfer Chat or Transfer Department", 'wp-live-chat-support') . "</p>",
      "1" => "<p>" . __("You can share files quickly when in a chat, by simply dragging a file into the chat window!", 'wp-live-chat-support') . "</p>",
      "2" => "<p>" . __("You can now move between chats without ending/closing an open chat", 'wp-live-chat-support') . "</p>"
    );
    return $tip_array;
  }


  /**
   * Return a count of unread messages for a specific agent from a specific agent
   *
   * @param  [intval] $ato   Agent ID 
   * @param  [intval] $afrom Agent ID
   * @return [intval]        Count
   */
  function wplc_return_unread_agent_messages_mrg($ato = 0, $afrom = 0) {
    global $wpdb;
    global $wplc_tblname_msgs;
    $count = $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM $wplc_tblname_msgs WHERE ato = %d AND afrom = %d AND status = 0", $ato, $afrom));
    return $count;
  }

  add_action("wplc_admin_remote_dashboard_above", "wplc_admin_remote_dashboard_quick_responses_container_mrg");
  /*
 * Adds the quick response container
*/
  function wplc_admin_remote_dashboard_quick_responses_container_mrg() {

    $wplc_settings = wplc_get_options();
    $wplc_quick_response_order_by = isset($wplc_settings['wplc_quick_response_orderby']) ? sanitize_text_field($wplc_settings['wplc_quick_response_orderby']) : 'title';
    $wplc_quick_response_order = isset($wplc_settings['wplc_quick_response_order']) ? sanitize_text_field($wplc_settings['wplc_quick_response_order']) : 'DESC';
    $args = array(
      'posts_per_page' => -1,
      'offset' => 0,
      'category' => '',
      'order' => $wplc_quick_response_order,
      'orderby' => $wplc_quick_response_order_by != 'number' ? $wplc_quick_response_order_by : 'meta_value_num',
      'include' => '',
      'exclude' => '',
      'meta_key' => $wplc_quick_response_order_by != 'number' ? '' : 'wplc_quick_response_number',
      'meta_value' => '',
      'post_type' => 'wplc_quick_response',
      'post_mime_type' => '',
      'post_parent' => '',
      'post_status' => 'publish',
      'suppress_filters' => true
    );
    $posts_array = get_posts($args);
    echo '<div id="quick_response_drawer_container" style="display:none;">';
    echo     '<h5>' . __("Quick Responses", 'wp-live-chat-support') . '</h5>';
    echo    '<hr>';
    //Add quick responses
    if ($posts_array) {
      foreach ($posts_array as $post) {
        echo '<div class="quick_response_item">' . $post->post_content . '</div>';
      }
    } else {
      echo "<div style='position: absolute; top: 23px; bottom: 0; left: 10px; right: 0; margin: auto; height: 20px;'>";
      echo __("No quick responses found", 'wp-live-chat-support') . " - <a target='_blank' href='" . admin_url('post-new.php?post_type=wplc_quick_response') . "'>" . __("Add New Quick Response", 'wp-live-chat-support') . "</a>";
      echo "</div>";
    }
    echo '</div>';
  }


  add_filter("wplc_agent_display_name_filter", "wplc_agent_display_name_filter_control_mrg", 10, 1);
  /*
 * Filters the agent display name 
*/
  function wplc_agent_display_name_filter_control_mrg($wplc_display_name) {
    $wplc_settings = wplc_get_options();
    if (!empty($wplc_settings['wplc_use_wp_name'])) {
      return $wplc_display_name;
    } else {
      if (!empty($wplc_settings['wplc_chat_name'])) {
        $wplc_display_name = $wplc_settings['wplc_chat_name'];
      }
    }
    return $wplc_display_name;
  }

  add_action("wplc_admin_remote_dashboard_styles_hook", "wplc_admin_remote_dashboard_styles_mrg");
  /**
   * Loads remote dashboard styles
   *
   * @return void
   */
  function wplc_admin_remote_dashboard_styles_mrg() {
    $wplc_settings = wplc_get_options();

    if (!$wplc_settings['wplc_use_node_server'] || !$wplc_settings['wplc_enable_initiate_chat']) {
      $initiate_chat_inline_styles = ".init_chat {display:none !important;}";
      wp_add_inline_style('wplc-admin-style', $initiate_chat_inline_styles);
    }
  }
