<?php
/*
 * Adds beta/opt-on options
*/
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

add_filter("wplc_filter_setting_tabs","wplc_beta_settings_tab_heading");
/**
 * Adds 'Advanced Features' tab to settings area
*/
function wplc_beta_settings_tab_heading($tab_array) {
    $tab_array['beta'] = array(
      "href" => "#tabs-beta",
      "icon" => 'fa fa-bolt',
      "label" => __("Advanced Features",'wp-live-chat-support')
    );
    return $tab_array;
}

add_action("wplc_hook_settings_page_more_tabs","wplc_beta_settings_tab_content");
/**
 * Adds 'Advanced Features' content to settings area
*/
function wplc_beta_settings_tab_content() {
  $wplc_settings = wplc_get_options();
  $wplc_node_token = wplc_node_server_token_get();
  $new_token_nonce = wp_create_nonce('generate_new_token');

 ?>
   <div id="tabs-beta">
     <h3><?php _e("Chat Server", 'wp-live-chat-support') ?></h3>
     <?php 
      do_action("wplc_admin_general_node_compat_check");
      ?>
     <table class="wp-list-table wplc_list_table widefat fixed striped pages">
       <tbody>
        <tr>
           <td width="250" valign="top">
             <label for="wplc_use_node_server"><?php _e("Select your chat server",'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e('Choose between 3CX servers or your Wordpress server for chat delivery', 'wp-live-chat-support'); ?>"></i></label>
           </td>
           <td valign="top">
            <input type="radio" name="wplc_use_node_server" value="1" <?php if ($wplc_settings['wplc_use_node_server']) { echo "checked"; } ?>> 3CX High Performance Cloud Servers<br><p></p>
            <input type="radio" name="wplc_use_node_server" value="0" <?php if (!$wplc_settings['wplc_use_node_server']) { echo "checked"; } ?>> OnPremise (This host)
            <div class="wplc_servermode_description">
              <p><?php _e('3CX Servers are high performance secure instances hosted on Google Cloud and are completely free to use. 3CX Servers don\'t log or store messages. Messages are simply forwarded between users and agents. Chat sessions are stored on your Wordpress database only.');?></p>
              <p><?php _e('Using your own Wordpress server as a chat relay may cause performance slowdowns to your website, especially on shared hosts. Due to HTTP long poll mechanism, messages and events could be slightly delayed.');?></p>
            </div>
           </td>
         </tr>
         <tr>
           <td width="250" valign="top">
             <label for="wplc_use_node_server"><?php _e("Chat server token",'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e('Security token for accessing chats on the node server. Changing this will close your currently active chat sessions.', 'wp-live-chat-support'); ?>"></i></label>
           </td>
           <td valign="top">
             <input type="text" value="<?php echo $wplc_node_token; ?>" id="wplc_node_token_input" name="wplc_node_token_input" disabled>
			 <input type="hidden" name="wplc_new_server_token_nonce" id="wplc_new_server_token_nonce" value="<?php echo $new_token_nonce; ?>">
             <div class="button button-secondary" id="wplc_new_server_token_btn"><?php _e("Generate New", 'wp-live-chat-support'); ?></div>
             <p class="wplc_error_message" id="wplc_server_token_error"></p>
           </td>
         </tr>
        <tr>
          <td width="250" valign="top">
          <label for="wplc_node_enable_typing_preview"><?php _e("Enable typing preview",'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e('This option enables the typing preview, which means agents will be able to see what the user is typing in realtime.', 'wp-live-chat-support'); ?>"></i></label>
          </td>
          <td valign="top">
            <?php
              if ($wplc_settings['wplc_use_node_server']) {
            ?>
            <input type="checkbox" value="1" name="wplc_node_enable_typing_preview" <?php if (wplc_get_enable_typing_preview()) { echo "checked"; } if ($wplc_settings['wplc_gdpr_enabled']) { echo ' disabled title="'.__('Typing preview is not available when GDPR is enabled').'"'; } ?>>
            <?php
              } else {
                echo '<p>'.__("This feature is only available when you select 3CX High Performance Cloud Servers in Advanced Features.").'</p>';
              }
            ?>
          </td>
        </tr>
         <tr>
           <td width="250" valign="top">
             <label for="wplc_new_chat_ringer_count"><?php _e("Number of chat rings",'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e('Limit the amount of time the new chat ringer will play', 'wp-live-chat-support'); ?>"></i></label>
           </td>
           <td valign="top">
             <input type="number" value="<?php echo intval($wplc_settings['wplc_new_chat_ringer_count']); ?>" id="wplc_new_chat_ringer_count" name="wplc_new_chat_ringer_count">
           </td>
         </tr>
       </tbody>
     </table>
     <script>
         jQuery(function(){
           jQuery("#wplc_copy_code_btn").click(function(){
             
             jQuery("#wplc_node_token_input").select();
             document.execCommand("copy");
             jQuery("#wplc_node_token_input").blur();
             jQuery(this).html("<i class='fa fa-check'></i>");
           });
         });
     </script>
 <?php
 do_action("wplc_hook_beta_options_content");
 ?>
 </div>
 <?php
}