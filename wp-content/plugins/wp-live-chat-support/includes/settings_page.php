<?php
/** Settings page */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $wplc_default_settings_array;

function wplc_render_select_time($hname, $mname, $hvalue, $mvalue) {
  $wplc_times_array = wplc_return_times_array_mrg();
  $html=" <select name='$hname' id='$hname'>";
  foreach($wplc_times_array['hours'] as $hour) {
    $hh = intval($hour);
    $html .= "<option value='$hh'".($hvalue == $hh ? ' selected' : '').">$hour</option>";
  }
  $html .= "</select>:";
  $html .= "<select name='$mname' id='$mname'>";
  foreach( $wplc_times_array['minutes'] as $minute ){
    $mm = intval($minute);
    $html .= "<option value='$mm'".($mvalue == $mm ? ' selected' : '').">$minute</option>";
  }
  $html .= "</select>";
  return $html;
}

?>
  <style>
  .ui-tabs-vertical {  }
  .ui-tabs-vertical .ui-tabs-nav {
      padding: .2em .1em .2em .2em;
      float: left;
      max-width: 20%;
      min-width: 190px;
  }
  .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 1px !important; margin: 0 -1px .2em 0; }
  .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
  .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; }
  .ui-tabs-vertical .ui-tabs-panel {
	  padding-top:4px;
      float: left;
      min-width: 67%;
      max-width: 67%;
  }
  textarea, input[type='text'], input[type='email'], input[type='password']{ width: 100% !important; }
	.ui-tabs-panel >  h3, .ui-tabs-panel >  h4{    display: block;
    background: #0596d4;
    margin-top: 0;
    padding: 6px;
    color: #FFF;
	 margin-bottom:3px;
	  border-radius:2px;
	  font-weight:normal;
		font-size:16px;
	  }

  </style>

  <?php
  /**
   * Removes the ajax loader and forces the settings page to load as is after 3 seconds.
   * 
   * This has been put here to counter any PHP fatal warnings that may be experienced on the settings page.
   *
   * Putting this in the wplc_tabs.js file will not work as that file is not loaded if there is a PHP fatal error
   */
  ?>
  <script>
     setTimeout( function() {
        jQuery("#wplc_settings_page_loader").remove();
        jQuery(".wrap").css('display','block');
        jQuery(".wplc_settings_save_notice").css('display','block');
   },3000);
 </script>

<?php wplc_stats("settings");

$wplc_settings = wplc_get_options();

 ?>

<img src='<?php echo WPLC_PLUGIN_URL.'images/ajax-loader.gif'; ?>' id='wplc_settings_page_loader' style='display: block; margin: 20px auto;' />
<style>
        .wplc_light_grey{
            color: #373737;
        }
</style>
<div class="wrap wplc_wrap" style='display: none;'>
    <h2><?php _e("Settings",'wp-live-chat-support')?></h2>
    <?php
        if (isset($wplc_settings["wplc_settings_align"])) { $wplc_settings_align[intval($wplc_settings["wplc_settings_align"])] = "SELECTED"; }
        if (isset($wplc_settings["wplc_settings_enabled"])) { $wplc_settings_enabled[intval($wplc_settings["wplc_settings_enabled"])] = "SELECTED"; }
        if (isset($wplc_settings["wplc_settings_fill"])) { $wplc_settings_fill = $wplc_settings["wplc_settings_fill"]; } else { $wplc_settings_fill = "0596d4"; }
        if (isset($wplc_settings["wplc_settings_font"])) { $wplc_settings_font = $wplc_settings["wplc_settings_font"]; } else { $wplc_settings_font = "FFFFFF"; }
        if (isset($wplc_settings["wplc_settings_color1"])) { $wplc_settings_color1 = $wplc_settings["wplc_settings_color1"]; } else { $wplc_settings_color1 = "0596d4"; }
        if (isset($wplc_settings["wplc_settings_color2"])) { $wplc_settings_color2 = $wplc_settings["wplc_settings_color2"]; } else { $wplc_settings_color2 = "FFFFFF"; }
        if (isset($wplc_settings["wplc_settings_color3"])) { $wplc_settings_color3 = $wplc_settings["wplc_settings_color3"]; } else { $wplc_settings_color3 = "EEEEEE"; }
        if (isset($wplc_settings["wplc_settings_color4"])) { $wplc_settings_color4 = $wplc_settings["wplc_settings_color4"]; } else { $wplc_settings_color4 = "373737"; }
        
		$wplc_auto_responder_settings = get_option( "WPLC_AUTO_RESPONDER_SETTINGS" );
		
		$wplc_quick_response_order_by = isset( $wplc_settings['wplc_quick_response_orderby'] ) ? sanitize_text_field( $wplc_settings['wplc_quick_response_orderby'] ) : 'title';
		$wplc_quick_response_order = isset( $wplc_settings['wplc_quick_response_order'] ) ? sanitize_text_field( $wplc_settings['wplc_quick_response_order'] ) : 'DESC'; 
		
 		$wplc_pro_auto_first_response_chat_msg = isset($wplc_settings['wplc_pro_auto_first_response_chat_msg']) ? $wplc_settings['wplc_pro_auto_first_response_chat_msg'] : '';
     ?>
    <form action='' name='wplc_settings' method='POST' id='wplc_settings'>
    <?php wp_nonce_field( 'wplc_save_settings', 'wplc_save_settings_nonce' ); ?>
    <div id="wplc_tabs">
      <ul>
        <?php 
          $tab_array = array(
            0 => array(
              "href" => "#tabs-1",
              "icon" => 'fas fa-cog',
              "label" => __("General Settings",'wp-live-chat-support')
            ),
            1 => array(
              "href" => "#tabs-2",
              "icon" => 'fa fa-envelope',
              "label" => __("Chat Box",'wp-live-chat-support')
            ),
            2 => array(
              "href" => "#tabs-3",
              "icon" => 'fa fa-book',
              "label" => __("Offline Messages",'wp-live-chat-support')
            ),
            3 => array(
              "href" => "#tabs-4",
              "icon" => 'fas fa-pencil-alt',
              "label" => __("Styling",'wp-live-chat-support')
            ),
            4 => array(
              "href" => "#tabs-5",
              "icon" => 'fa fa-users',
              "label" => __("Agents",'wp-live-chat-support')
            ),
            5 => array(
              "href" => "#tabs-7",
              "icon" => 'fa fa-gavel',
              "label" => __("Blocked Visitors",'wp-live-chat-support')
            )
          );
          $tabs_top = apply_filters("wplc_filter_setting_tabs",$tab_array);

          foreach ($tabs_top as $tab) {
            echo "<li><a href=\"".$tab['href']."\"><i class=\"".$tab['icon']."\"></i> ".$tab['label']."</a></li>";
          }

        ?>
       
      </ul>
      <div id="tabs-1">
          <h3><?php _e("General Settings",'wp-live-chat-support')?></h3>
          <table class='wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
              <tr>
                  <td width='350' valign='top'><?php _e("Chat enabled",'wp-live-chat-support')?>: </td>
                  <td>
                      <select id='wplc_settings_enabled' name='wplc_settings_enabled'>
                          <option value="1" <?php if (isset($wplc_settings_enabled[1])) { echo $wplc_settings_enabled[1]; } ?>><?php _e("Yes",'wp-live-chat-support'); ?></option>
                          <option value="2" <?php if (isset($wplc_settings_enabled[2])) { echo $wplc_settings_enabled[2]; }?>><?php _e("No",'wp-live-chat-support'); ?></option>
                      </select>
                  </td>
              </tr>


                  <tr>
                  <td width='300' valign='top'>
                      <?php _e("Required Chat Box Fields",'wp-live-chat-support')?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Set default fields that will be displayed when users starting a chat", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <div class="wplc-require-user-info__item">
                          <input type="radio" value="both" name="wplc_require_user_info" id="wplc_require_user_info_both"<?php echo ($wplc_settings['wplc_require_user_info'] == 'both' ? ' checked' : '');?> />
                          <label for="wplc_require_user_info_both"><?php _e( 'Name and email', 'wp-live-chat-support'); ?></label>
                      </div>
                      <div class="wplc-require-user-info__item">
                          <input type="radio" value="email" name="wplc_require_user_info" id="wplc_require_user_info_email"<?php echo ($wplc_settings['wplc_require_user_info'] == 'email' ? ' checked' : '');?> />
                          <label for="wplc_require_user_info_email"><?php _e( 'Email', 'wp-live-chat-support'); ?></label>
                      </div>
                      <div class="wplc-require-user-info__item">
                          <input type="radio" value="name" name="wplc_require_user_info" id="wplc_require_user_info_name"<?php echo ($wplc_settings['wplc_require_user_info'] == 'name' ? ' checked' : '');?> />
                          <label for="wplc_require_user_info_name"><?php _e( 'Name', 'wp-live-chat-support'); ?></label>
                      </div>
                      <div class="wplc-require-user-info__item">
                          <input type="radio" value="none" name="wplc_require_user_info" id="wplc_require_user_info_none"<?php echo ($wplc_settings['wplc_require_user_info'] == 'none' ? ' checked' : '');?> />
                          <label for="wplc_require_user_info_none"><?php _e( 'No fields', 'wp-live-chat-support'); ?></label>
                      </div>
                  </td>
              </tr>
              <tr class="wplc-user-default-visitor-name__row">
                  <td width='300' valign='top'>
		              <?php _e("Default visitor name",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("This name will be displayed for all not logged in visitors", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <input type="text" name="wplc_user_default_visitor_name" maxlength="25" id="wplc_user_default_visitor_name" value="<?php echo esc_attr( $wplc_settings['wplc_user_default_visitor_name']); ?>" />
                  </td>
              </tr>
              <tr>
                  <td width='300' valign='top'>
                      <?php _e("Input Field Replacement Text",'wp-live-chat-support')?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("This is the text that will show in place of the Name And Email fields", 'wp-live-chat-support') ?>"></i>                      
                  </td>
                  <td valign='top'>
                      <textarea cols="45" rows="5" name="wplc_user_alternative_text" ><?php echo esc_textarea($wplc_settings['wplc_user_alternative_text']); ?></textarea>
                </td>
              </tr>
              <tr>
                  <td width='200' valign='top'>
                      <?php _e("Enable On Mobile Devices","wplivechat"); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Disabling this will mean that the Chat Box will not be displayed on mobile devices. (Smartphones and Tablets)", "wplivechat") ?>"></i>                      
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_enabled_on_mobile"<?php echo ($wplc_settings['wplc_enabled_on_mobile'] ? ' checked' : '');?> />
                  </td>
              </tr>              

              <?php if ($wplc_settings['wplc_use_node_server']) { ?>
              <tr>
                  <td width='300' valign='top'>
                      <?php _e("Play a sound when there is a new visitor",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Disable this to mute the sound that is played when a new visitor arrives", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_enable_visitor_sound"<?php echo ($wplc_settings['wplc_enable_visitor_sound'] ? ' checked' : '');?> />                      
                  </td>
              </tr>
              <?php } ?>
              <tr>
                  <td width='300' valign='top'>
                      <?php _e("Play a sound when a new message is received",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Disable this to mute the sound that is played when a new chat message is received", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_enable_msg_sound"<?php echo ($wplc_settings['wplc_enable_msg_sound'] ? ' checked' : '');?> />                      
                  </td>
              </tr>
              
              <tr>
                  <td width='300' valign='top'>
    			          <?php _e("Enable Font Awesome set",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Disable this if you have Font Awesome set included with your theme", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_enable_font_awesome"<?php echo ($wplc_settings['wplc_enable_font_awesome'] ? ' checked' : '');?>/>
                  </td>
              </tr>
              <tr>
                  <td width='300' valign='top'>
                    <?php _e("Enable chat dashboard and notifications on all admin pages",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("This will load the chat dashboard on every admin page.", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_enable_all_admin_pages"<?php echo ($wplc_settings['wplc_enable_all_admin_pages'] ? ' checked' : '');?> />
                  </td>
              </tr>    
              <tr>
                  <td width='300' valign='top'>
                    <?php _e("Delete database entries on uninstall",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("This will delete all WP Live Chat by 3CX related database entries such as options and chats on uninstall.", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_delete_db_on_uninstall"<?php echo ($wplc_settings['wplc_delete_db_on_uninstall'] ? ' checked' : '');?>/>
                  </td>
              </tr>  
            </table>
		  
		  	<table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
	        <tr>
	            <td width='350' valign='top'>
	                <?php _e("Agents can set their online status", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php echo __('Checking this will allow you to change your status to Online or Offline on the Live Chat page.', 'wp-live-chat-support').' '.__('If this option is disabled, agents will be always automatically online.', 'wp-live-chat-support'); ?>"></i>                     
	            </td>  
	            <td>
	                <input type="checkbox" value="1" name="wplc_allow_agents_set_status"<?php echo ($wplc_settings['wplc_allow_agents_set_status'] ? ' checked' : '');?> />
	            </td>
	        </tr>
	    </table>
		
		  	    <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
	    	<tr>
	            <td width='350' valign='top'>
	                <?php _e("Exclude chat from 'Home' page:", 'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Leaving this unchecked will allow the chat window to display on your home page.", 'wp-live-chat-support') ?>"></i>
	            </td>
	            <td valign='top'>
	                <input type="checkbox" name="wplc_exclude_home"<?php echo ($wplc_settings['wplc_exclude_home'] ? ' checked' : ''); ?> value='1' />                      
	            </td>
            </tr>
            <tr>
	            <td width='350' valign='top'>
	                <?php _e("Exclude chat from 'Archive' pages:", 'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Leaving this unchecked will allow the chat window to display on your archive pages.", 'wp-live-chat-support') ?>"></i>
	            </td>
	            <td valign='top'>
	                <input type="checkbox" name="wplc_exclude_archive"<?php echo ($wplc_settings['wplc_exclude_archive'] ? ' checked' : ''); ?> value='1' />                      
	            </td>
            </tr>
	        <tr>
	            <td width='350' valign='top'>
	                <?php _e("Include chat window on the following pages:", 'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Show the chat window on the following pages. Leave blank to show on all. (Use comma-separated Page ID's)", 'wp-live-chat-support') ?>"></i>
	            </td>
	            <td valign='top'>
	                <input type="text" name="wplc_include_on_pages" value="<?php echo esc_attr($wplc_settings['wplc_include_on_pages']); ?>" />                      
	            </td>
            </tr>
            <tr>
	            <td width='350' valign='top'>
	                <?php _e("Exclude chat window on the following pages:", 'wp-live-chat-support'); ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Do not show the chat window on the following pages. Leave blank to show on all. (Use comma-separated Page ID's)", 'wp-live-chat-support') ?>"></i>                      
	            </td>
	            <td valign='top'>
	                <input type="text" name="wplc_exclude_from_pages" value="<?php echo esc_attr($wplc_settings['wplc_exclude_from_pages']); ?>" />                      
	            </td>
	        </tr>
            <tr class="wplc-exclude-post-types__row">
                <td width='200' valign='top'>
				    <?php _e("Exclude chat window on selected post types",'wp-live-chat-support'); ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Do not show the chat window on the following post types pages.", 'wp-live-chat-support') ?>"></i>
                </td>
                <td valign='top'><?php
				    $wplc_posts_types = get_post_types(
					    array(
                            '_builtin' => false,
                            'public' => true
                        ),
                        'objects'
				    );
				    if ( ! empty( $wplc_posts_types ) ) {
					    foreach ( $wplc_posts_types as $posts_type ) { ?>
                            <div class="wplc-exclude-post-types__item">
                                <input type="checkbox" value="<?php echo $posts_type->name; ?>" id="wplc_exclude_post_types_<?php echo $posts_type->name; ?>" name="wplc_exclude_post_types[]" <?php echo ( ! empty( $wplc_settings['wplc_exclude_post_types'] ) && in_array( $posts_type->name, $wplc_settings['wplc_exclude_post_types'] ) ) ? 'checked' : ''; ?> />
                                <label for="wplc_exclude_post_types_<?php echo $posts_type->name; ?>"><?php _e( $posts_type->label, 'wp-live-chat-support') ?></label>
                            </div>
					    <?php
					    }
				    } else {
					    _e( 'No post types found.', 'wp-live-chat-support');
                    } ?>
                </td>
            </tr>
	    </table>
		  
		<h4><?php _e( "Quick Response", 'wp-live-chat-support'); ?></h4>
        <table class="wp-list-table wplc_list_table widefat fixed striped pages">
            <tbody>
            <tr>
                <td width="350" valign="top">
                    <label for="wplc_quick_response_orderby"><?php _e( "Order by", 'wp-live-chat-support'); ?></label>
                </td>
                <td valign="top">
                    <select id='wplc_quick_response_orderby' name='wplc_quick_response_orderby'>
                        <option value="title" <?php selected( $wplc_quick_response_order_by, 'title' ) ?>><?php _e( "Title", 'wp-live-chat-support'); ?></option>
                        <option value="date" <?php selected( $wplc_quick_response_order_by, 'date' ) ?>><?php _e( "Date", 'wp-live-chat-support'); ?></option>
                        <option value="number" <?php selected( $wplc_quick_response_order_by, 'number' ) ?>><?php _e( "Number", 'wp-live-chat-support'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="350" valign="top">
                    <label for="wplc_quick_response_order"><?php _e( "Sort", 'wp-live-chat-support'); ?></label>
                </td>
                <td valign="top">
                    <select id='wplc_quick_response_order' name='wplc_quick_response_order'>
                        <option value="DESC" <?php selected( $wplc_quick_response_order, 'DESC' ) ?>><?php _e( "Descending", 'wp-live-chat-support'); ?></option>
                        <option value="ASC" <?php selected( $wplc_quick_response_order, 'ASC' ) ?>><?php _e( "Ascending", 'wp-live-chat-support'); ?></option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>

      <h4><?php _e("Geolocalization", 'wp-live-chat-support') ?></h4>
      <table class='wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
            <tr>
                <td width='350' valign='top'>
					<?php _e( "Detect Visitors Country", 'wp-live-chat-support'); ?>: 
                </td>
                <td valign='top'>
                    <input type="checkbox" value="1" name="wplc_use_geolocalization"<?php echo ($wplc_settings['wplc_use_geolocalization'] ? ' checked' : '' ) ?> />
                    &nbsp;&nbsp;(<?php echo sprintf(__("This feature requires the use of the GeoIP Detection plugin. Install it by going %s", 'wp-live-chat-support'),'<a style="text-decoration: underline" href="https://wordpress.org/plugins/geoip-detect/" target="_blank">'.__('here','wp-live-chat-support').'</a>');?>)
                    <div class="wplc_servermode_description"><?php _e("This feature is only available when '3CX High Performance Cloud Servers' is ticked in the 'Settings > Advanced Features section'.");?></div>
                </td>
            </tr>
        </table>        

		  <div style="display:none">
		  <h4><?php _e( "Voice Notes", 'wp-live-chat-support'); ?></h4>
		   <table class='wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
            <tr>
                <td width='350' valign='top'>
					<?php _e( "Enable Voice Notes on admin side", 'wp-live-chat-support'); ?>: <i
                            class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip"
                            title="<?php _e( "Enabling this will allow you to record the voice during the chat and send it to visitor once you hold on CTRL + SPACEBAR in main chat window", 'wp-live-chat-support') ?>"></i>
                </td>
                <td valign='top'>
                    <input type="checkbox" value="1" name="wplc_enable_voice_notes_on_admin"<?php echo ($wplc_settings['wplc_enable_voice_notes_on_admin'] ? ' checked' : '' ) ?> />
                </td>
            </tr>
            <tr>
                <td width='350' valign='top'>
					<?php _e( "Enable Voice Notes on visitor side", 'wp-live-chat-support'); ?>: <i
                            class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip"
                            title="<?php _e( "Enabling this will allow the visitors to record the voice during the chat and send it to agent once they hold on CTRL + SPACEBAR", 'wp-live-chat-support') ?>"></i>
                </td>
                <td valign='top'>
                    <input type="checkbox" value="1" name="wplc_enable_voice_notes_on_visitor"<?php echo ($wplc_settings['wplc_enable_voice_notes_on_visitor'] ? ' checked' : '' ) ?> />
                </td>
            </tr>
        </table>  
        </div>
		  
            <?php do_action('wplc_hook_admin_settings_main_settings_after'); ?>
            
          
      </div>
      <div id="tabs-2">
          <h3><?php _e("Chat Box Settings",'wp-live-chat-support')?></h3>
          <table class='wp-list-table wplc_list_table widefat fixed striped pages'>
              <tr>
                  <td width='300' valign='top'><?php _e("Alignment",'wp-live-chat-support')?>:</td>
                  <td>
                      <select id='wplc_settings_align' name='wplc_settings_align'>
                          <option value="1" <?php if (isset($wplc_settings_align[1])) { echo $wplc_settings_align[1]; } ?>><?php _e("Bottom left",'wp-live-chat-support'); ?></option>
                          <option value="2" <?php if (isset($wplc_settings_align[2])) { echo $wplc_settings_align[2]; } ?>><?php _e("Bottom right",'wp-live-chat-support'); ?></option>
                          <option value="3" <?php if (isset($wplc_settings_align[3])) { echo $wplc_settings_align[3]; } ?>><?php _e("Left",'wp-live-chat-support'); ?></option>
                          <option value="4" <?php if (isset($wplc_settings_align[4])) { echo $wplc_settings_align[4]; } ?>><?php _e("Right",'wp-live-chat-support'); ?></option>
                      </select>
                  </td>
              </tr>
              <tr>
              <td>
                <?php
                _e('Chat box height (percent of the page)', 'wp-live-chat-support');
                ?>
              </td>
              <td>
                <select id='wplc_chatbox_height' name='wplc_chatbox_height'>
                  <option value="0"><?php _e('Use absolute height','wp-live-chat-support');?></option>
                  <?php
                    for($i=30;$i<90;$i=$i+10) {
                      echo '<option value="'.$i.'" '.($wplc_settings['wplc_chatbox_height']==$i ? 'selected' : '').'>'.$i.'%</option>';
                    }
                  ?>
                </select>        
                <span <?php echo ($wplc_settings['wplc_chatbox_height']>0) ? 'style="display:none" ': '' ?>id="wplc_chatbox_absolute_height_span"><input type="number" id="wplc_chatbox_absolute_height" style="width:70px" name="wplc_chatbox_absolute_height" min="100" max="1000" value="<?php echo $wplc_settings['wplc_chatbox_absolute_height'];?>" />px</span>
              </tr>          
              <tr>
                  <td width='300'>
                      <?php _e("Automatic Chatbox Pop-Up",'wp-live-chat-support') ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Expand the chat box automatically (prompts the user to enter their name and email address).",'wp-live-chat-support') ?>"></i>
                  </td>
                  <td>
                      <select id='wplc_auto_pop_up' name='wplc_auto_pop_up'>
                          <option value="0" <?php if ($wplc_settings['wplc_auto_pop_up']==0) {echo 'selected'; } ?>><?php _e("Disabled",'wp-live-chat-support'); ?></option>
                          <option value="1" <?php if ($wplc_settings['wplc_auto_pop_up']==1) {echo 'selected'; } ?>><?php _e("No Forms - Only show 'Start Chat' button",'wp-live-chat-support'); ?></option>
                          <option value="2" <?php if ($wplc_settings['wplc_auto_pop_up']==2) {echo 'selected'; } ?>><?php _e("All Forms - Show chatbox forms and fields",'wp-live-chat-support'); ?></option>
                      </select>         
                      &nbsp;&nbsp;&nbsp;<input type="checkbox" name="wplc_auto_pop_up_online" value="1"<?php echo ($wplc_settings['wplc_auto_pop_up_online'] ? ' checked' : '');?>/> <label><?php _e("Pop-up only when agents are online", 'wp-live-chat-support'); ?></label>
                      &nbsp;&nbsp;&nbsp;<input type="checkbox" name="wplc_auto_pop_up_mobile" value="1"<?php echo ($wplc_settings['wplc_auto_pop_up_mobile'] ? ' checked' : '');?>/> <label><?php _e("Pop-up for mobile users", 'wp-live-chat-support'); ?></label><br/>
                  </td>
              </tr>
             
              <tr>
                  <td>
                      <?php _e("Display for chat message:", 'wp-live-chat-support') ?>
                  </td>
                  <td> 
                      <input type="checkbox" name="wplc_show_name" value="1"<?php echo ($wplc_settings['wplc_show_name'] ? ' checked' : '');?>/> <label><?php _e("Name", 'wp-live-chat-support'); ?></label><br/>
                      <input type="checkbox" name="wplc_show_avatar" value="1"<?php echo ($wplc_settings['wplc_show_avatar'] ? ' checked' : '');?>/> <label><?php _e("Avatar", 'wp-live-chat-support'); ?></label><br/>
                  </td>
              </tr>
		<tr>
		<td width='300'>
				<?php echo __("Display typing indicator", 'wp-live-chat-support');?> <i class='fa fa-question-circle wplc_light_grey wplc_settings_tooltip' title="<?php _e("Display the 'typing...' animation in the chat window as soon as an agent or visitor is typing.", 'wp-live-chat-support');?>"></i>
			</td>
			<td>
				<input type="checkbox" name="wplc_typing_enabled" value="1"<?php echo ($wplc_settings['wplc_typing_enabled'] ? ' checked' : '');?>/>
				<small><em><?php echo __("For on premise server chat users, please note that this will increase the amount of resources required on your server.", 'wp-live-chat-support'); ?> </em></small>
			</td>
		</tr>
              <tr>
                  <td>
                      <?php _e("Chat box for logged in users only:", 'wp-live-chat-support') ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("By checking this, only users that are logged in will be able to chat with you.", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td>
                      <input type="checkbox" name="wplc_display_to_loggedin_only" value="1"<?php echo ($wplc_settings['wplc_display_to_loggedin_only'] ? ' checked' : ''); ?>/>
                  </td>
              </tr>   
              <tr>
                  <td width='300' valign='top'>
                      <?php _e("Use Logged In User Details",'wp-live-chat-support')?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("A user's Name and Email Address will be used by default if they are logged in.", 'wp-live-chat-support') ?>"></i>                      
                  </td>
                  <td valign='top'>
                      <input type="checkbox" value="1" name="wplc_loggedin_user_info"<?php echo ($wplc_settings['wplc_loggedin_user_info'] ? ' checked' : '');?> />
                  </td>
              </tr>
              <tr>
                  <td>
                      <?php _e("Display a timestamp in the chat window:", 'wp-live-chat-support') ?>
                  </td>
                  <td>  
                      <input type="checkbox" name="wplc_show_date" value="1"<?php echo ($wplc_settings['wplc_show_date'] ? ' checked' : ''); ?>/> <label><?php _e("Date", 'wp-live-chat-support'); ?></label><br/>
                      <input type="checkbox" name="wplc_show_time" value="1"<?php echo ($wplc_settings['wplc_show_time'] ? ' checked' : ''); ?>/> <label><?php _e("Time", 'wp-live-chat-support'); ?></label>
                  </td>
              </tr>  
              <tr>
                  <td>
                     <?php _e("Redirect to “Thank You” page on chat end:", 'wp-live-chat-support') ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("By checking this, users will be redirected to your thank you page when a chat is completed.", 'wp-live-chat-support') ?>"></i>
                  </td>
                  <td>
                      <input type="checkbox" name="wplc_redirect_to_thank_you_page" value="1"<?php echo ($wplc_settings['wplc_redirect_to_thank_you_page'] ? ' checked' : ''); ?>/>
                      <input type="text" name="wplc_redirect_thank_you_url" value="<?php echo (!empty($wplc_settings['wplc_redirect_thank_you_url']) ?  urldecode($wplc_settings['wplc_redirect_thank_you_url']) : '' ); ?>" placeholder="<?php _e('Thank You Page URL', 'wp-live-chat-support'); ?>" class='wplc_check_url' />
                  </td>
              </tr> 
				<?php
				if(defined('WPLC_PLUGIN'))
				{
					?>
					<tr>
						<td>
							<?php
							_e('Disable Emojis', 'wp-live-chat-support');
							?>
						</td>
						<td>
						<input type="checkbox" name="wplc_disable_emojis"<?php if ($wplc_settings['wplc_disable_emojis']) {echo ' checked="checked"';} ?>/>
          </tr>
					<?php
				}
				?>
          </table>     
		  
		<?php
	    echo "<table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='700'>";
	    ?>
	                    
	        <tr>
	            <td width='420' valign='top'>
	                <?php _e("User / Agent name", 'wp-live-chat-support') ?>:
	            </td>
	            <td>
	                <input id='wplc_chat_name' name='wplc_chat_name' type='text' size='50' maxlength='50' class='regular-text' value='<?php echo stripslashes($wplc_settings['wplc_chat_name']); ?>' />
	            </td>
	        </tr>
	        <tr>
	            <td width='420' valign='top'>
	                - <?php _e("Use WordPress name", 'wp-live-chat-support') ?>:
	            </td>
	            <td>
	                <input id='wplc_use_wp_name' name='wplc_use_wp_name' value='1' type='checkbox'<?php echo ($wplc_settings['wplc_use_wp_name'] ? ' checked' : '');?>/> <small><em><?php _e("Note: 'Name' field will be ignored", 'wp-live-chat-support')?></em></small>
	            </td>
	        </tr>

          <?php 
          
          $wplc_ringtones = wplc_sounds_get_array(WPLC_PLUGIN_DIR."/includes/sounds/");
          $wplc_messagetones = wplc_sounds_get_array(WPLC_PLUGIN_DIR."/includes/sounds/message/");
          $wplc_ringtone_selected = '';
          $wplc_messagetone_selected = '';
          if (isset($wplc_settings['wplc_ringtone'])) {
            $wplc_ringtone_selected = wplc_get_ringtone_name($wplc_settings['wplc_ringtone']);
          }
          if (isset($wplc_settings['wplc_messagetone'])) {
            $wplc_messagetone_selected = wplc_get_messagetone_name($wplc_settings['wplc_messagetone']);
          }

          /* Thank you to the following authors of the sound files used in WP Live Chat Support

          (1) Mike Koenig Sound - Store door chime, Mario jump, ringing phone, Rooster, ship bell
          (2) Freesfx.com
          (3) Corsica - Elevator ding sound, Air plane ding
          (4) Brian Rocca - Pin dropping
          (5) Marianne Gagnon - Robot blip
          (6) Caroline Ford - Drop metal thing
          (7) bennstir - Doorbell

          */

	        ?>
	        <tr>
            <td width='420' valign='top'>
                <?php _e("Incoming chat ring tone", 'wp-live-chat-support') ?>:
            </td>
            <td>
              <select name='wplc_ringtone' id='wplc_ringtone'>
                <?php
                  foreach($wplc_ringtones as $k=>$v) {
                    echo '<option playurl="'.wplc_get_ringtone_url($k).'" value="'.$k.'" '.(($k==$wplc_ringtone_selected) ? 'selected' : '').'>'.$v.'</option>';
                  }
                ?>
              </select>
              <button type='button' id='wplc_sample_ring_tone'><i class='fa fa-play'></i></button>
            </td>
          </tr>

	        <tr>
            <td width='420' valign='top'>
              <?php _e("Incoming message tone", 'wp-live-chat-support') ?>:
            </td>
            <td>
              <select name='wplc_messagetone' id='wplc_messagetone'>
                <?php
                  foreach($wplc_messagetones as $k=>$v) {
                    echo '<option playurl="'.wplc_get_messagetone_url($k).'" value="'.$k.'" '.(($k==$wplc_messagetone_selected) ? 'selected' : '').'>'.$v.'</option>';
                  }
                ?>
              </select>
              <button type='button' id='wplc_sample_message_tone'><i class='fa fa-play'></i></button>
            </td>
          </tr>          

        <!-- Chat Icon-->
        <tr class='wplc-icon-area'>
            <td width='300' valign='top'>
				<?php _e("Icon", 'wp-live-chat-support') ?>:
            </td>
            <td>
                    <div class="wplc_default_chat_icon_selector" style="display:block;max-height:50px;background-color:#<?php echo $wplc_settings['wplc_settings_color1'];?>" id="wplc_icon_area">
                        <img src="<?php echo trim(urldecode($wplc_settings['wplc_chat_icon'])); ?>" width="50px"/>
                    </div>
                <input id="wplc_upload_icon" name="wplc_upload_icon" type="hidden" size="35" class="regular-text" maxlength="700" value=""/>
                <br/>
                <input id="wplc_btn_upload_icon" name="wplc_btn_upload_icon" type="button" value="<?php _e("Upload Icon", 'wp-live-chat-support') ?>" />
                <input id="wplc_btn_select_default_icon" name="wplc_btn_select_default_icon" type="button" value="<?php _e("Select Default Icon", 'wp-live-chat-support') ?>" />
                <br/>
				<?php _e("Recommended Size 50px x 50px", 'wp-live-chat-support') ?>
				
				<div id="wplc_default_chat_icons" style="display: none">
					<strong><?php _e("Select Default Icon", 'wp-live-chat-support'); ?></strong>
					<img class="wplc_default_chat_icon_selector" src="<?php echo WPLC_PLUGIN_URL . 'images/chaticon.png'; ?>">	
					<img class="wplc_default_chat_icon_selector" src="<?php echo WPLC_PLUGIN_URL . 'images/default_icon_1.png'; ?>">	
					<img class="wplc_default_chat_icon_selector" src="<?php echo WPLC_PLUGIN_URL . 'images/default_icon_2.png'; ?>">
					<img class="wplc_default_chat_icon_selector" src="<?php echo WPLC_PLUGIN_URL . 'images/default_icon_3.png'; ?>">
				</div>

				<style type="text/css">
					#wplc_default_chat_icons {
					    margin-top: 10px;
					    padding: 5px;
					    border: 1px solid #eee;
					    border-radius: 5px; 
					}
					#wplc_default_chat_icons strong {
						display: block;
						margin-bottom: 5px;
					}
					.wplc_default_chat_icon_selector {
						background-color: #ccc;
						display: inline-block;
						vertical-align: top;
						margin-right: 5px;
						max-width: 50px;
						border-radius: 100px;
					}
					.wplc_default_chat_icon_selector:hover {
						cursor: pointer;
						background-color: #bbb;
					}

          .wplc_theme_single, .wplc_animation_image {
            cursor: pointer;
          }
				</style>
            </td>
        </tr>

	        <tr class='wplc-pic-area'>
	            <td width='300' valign='top'>
	                <?php _e("Picture", 'wp-live-chat-support') ?>:
	            </td>
	            <td>
	                <div style="display:block" id="wplc_pic_area" default="<?php echo $wplc_default_settings_array['wplc_chat_pic'];?>">
	                    <img src="<?php echo urldecode($wplc_settings['wplc_chat_pic']); ?>" width="60px"/>
	                </div>
	                <input id="wplc_upload_pic" name="wplc_upload_pic" type="hidden" size="35" class="regular-text" maxlength="700" value=""/> 
	                <br/>
                  <input id="wplc_btn_upload_pic" name="wplc_btn_upload_pic" type="button" value="<?php _e("Upload Image", 'wp-live-chat-support') ?>" />
                  <input id="wplc_btn_select_default_pic" name="wplc_btn_select_default_pic" type="button" value="<?php _e("Select Default Image", 'wp-live-chat-support') ?>" />
                  
	                <br/>
	                <input id="wplc_btn_remove_pic" name="wplc_btn_remove_pic" type="button" value="<?php _e("Remove Image", 'wp-live-chat-support') ?>" /><br/>
	                <?php _e("Recommended Size 60px x 60px", 'wp-live-chat-support') ?>
	            </td>
            </tr>

            <!-- Chat Logo-->
            <tr class='wplc-logo-area'>
                <td width='300' valign='top'>
                    <?php _e("Logo", 'wp-live-chat-support') ?>:
                </td>
                <td>
                    <div style="display:block" id="wplc_logo_area">
                        <img src="<?php echo urldecode($wplc_settings['wplc_chat_logo']); ?>" width="100px"/>
                    </div> 
                    <input id="wplc_upload_logo" name="wplc_upload_logo" type="hidden" size="35" class="regular-text" maxlength="700" value=""/>
                    <br/>
                    <input id="wplc_btn_upload_logo" name="wplc_btn_upload_logo" type="button" value="<?php _e("Upload Logo", 'wp-live-chat-support') ?>" />
                    <br/>
                    <input id="wplc_btn_remove_logo" name="wplc_btn_remove_logo" type="button" value="<?php _e("Remove Logo", 'wp-live-chat-support') ?>" /><br/>
                    <?php _e("Recommended Size 250px x 40px", 'wp-live-chat-support') ?>
                </td>
            </tr>
	                    
            <tr>
                <td width='300' valign='top'>
                    <?php _e("Chat button delayed startup (seconds)", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("How long to delay showing the Live Chat button on a page", 'wp-live-chat-support') ?>"></i>
                </td>
                <td>
                    <input id="wplc_chat_delay" name="wplc_chat_delay" type="text" size="6" maxlength="4" value="<?php echo intval($wplc_settings['wplc_chat_delay']); ?>" />
                </td>
            </tr>
            <!-- Chat Notification if want to chat -->
            <tr>
                <td width='300' valign='top'>
                    <?php _e("Chat notifications", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Alert me via email as soon as someone wants to chat (while online only)", 'wp-live-chat-support'); ?>"></i>
                </td>
                <td>
                    <input id="wplc_pro_chat_notification" name="wplc_pro_chat_notification" type="checkbox" value="1"<?php echo($wplc_settings['wplc_pro_chat_notification'] ? ' checked' : '') ?> />                    
                </td>
            </tr>

        </table>

        <h3><?php _e("User Experience", 'wp-live-chat-support') ?></h3>
        <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='100%'>
            <tbody>
                <tr>
                    <td width='300' valign='top'><?php _e("Share files", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Adds file sharing to your chat box!", 'wp-live-chat-support') ?>"></i></td> 
                    <td><input id='wplc_ux_file_share' name='wplc_ux_file_share' type='checkbox'<?php echo($wplc_settings['wplc_ux_file_share'] ? ' checked' : '') ?> /> </td>   
                </tr>
                <tr>
                    <td width='300' valign='top'><?php _e("Visitor experience ratings", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Allows users to rate the chat experience with an agent.", 'wp-live-chat-support') ?>"></i></td> 
                    <td><input id='wplc_ux_exp_rating' name='wplc_ux_exp_rating' type='checkbox'<?php echo($wplc_settings['wplc_ux_exp_rating'] ? ' checked' : '') ?> /> </td>   
                </tr>
            </tbody>
        </table>

	        <h3><?php _e("Social", 'wp-live-chat-support') ?></h3>
	        <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='100%'>
	            <tbody>
	                <tr>
	                    <td width='300' valign='top'><?php _e("Facebook URL", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Link your Facebook page here. Leave blank to hide", 'wp-live-chat-support') ?>"></i></td> 
	                    <td><input id='wplc_social_fb' class='wplc_check_url' name='wplc_social_fb' placeholder="<?php _e("Facebook URL...", 'wp-live-chat-support') ?>" type='text' value="<?php echo urldecode($wplc_settings['wplc_social_fb']); ?>" />     

                      <?php 
                      if(!empty($wplc_settings['wplc_social_fb']) && !filter_var($wplc_settings['wplc_social_fb'], FILTER_VALIDATE_URL)) {
                        ?><br><strong>Note: </strong>This does not appear to be a valid URL<?php
                      }
                      ?>

                      </td>
	                </tr>
	                <tr>
	                    <td width='300' valign='top'><?php _e("Twitter URL", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Link your Twitter page here. Leave blank to hide", 'wp-live-chat-support') ?>"></i></td> 
	                    <td><input id='wplc_social_tw' class='wplc_check_url' name='wplc_social_tw' placeholder="<?php _e("Twitter URL...", 'wp-live-chat-support') ?>" type='text' value="<?php echo urldecode($wplc_settings['wplc_social_tw']); ?>" />  

                      <?php 
                      if (!empty($wplc_settings['wplc_social_tw']) && !filter_var($wplc_settings['wplc_social_tw'], FILTER_VALIDATE_URL)) {
                        ?><br><strong>Note: </strong>This does not appear to be a valid URL<?php
                      }
                      ?>
                      </td>   

	                </tr>
	            </tbody>
	        </table>
		  

          <?php do_action('wplc_hook_admin_settings_chat_box_settings_after'); ?>

      </div>
                  <div id="tabs-3">
                <h3><?php _e("Offline Messages", 'wp-live-chat-support') ?></h3> 
                <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='100%'>
                    <tr>
                        <td width='300'>
<?php _e("Disable offline messages", 'wp-live-chat-support') ?> <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("The chat window will be hidden when it is offline. Users will not be able to send offline messages to you", 'wp-live-chat-support') ?>"></i>
                        </td>
                        <td>
                            <input type="checkbox" name="wplc_hide_when_offline" value="1" <?php echo ($wplc_settings['wplc_hide_when_offline'] ? ' checked' : '');?>/>
                        </td>
                    </tr>
                    <tr>
                        <td width="300" valign="top"><?php _e("Offline Form Title", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_na" name="wplc_pro_na" type="text" size="50" maxlength="50" class="regular-text" value="<?php if (isset($wplc_settings['wplc_pro_na'])) { echo esc_attr($wplc_settings['wplc_pro_na']); } ?>" /> <br />


                        </td>
                    </tr>
                    <tr>
                        <td width="300" valign="top"><?php _e("Offline form initial message", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_offline1" name="wplc_pro_offline1" type="text" size="50" maxlength="150" class="regular-text" value="<?php if (isset($wplc_settings['wplc_pro_offline1'])) { echo esc_attr($wplc_settings['wplc_pro_offline1']); } ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="300" valign="top"><?php _e("Offline form message on send", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_offline2" name="wplc_pro_offline2" type="text" size="50" maxlength="50" class="regular-text" value="<?php if (isset($wplc_settings['wplc_pro_offline2'])) { echo esc_attr($wplc_settings['wplc_pro_offline2']); } ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="300" valign="top"><?php _e("Offline form finish message", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_offline3" name="wplc_pro_offline3" type="text" size="50" maxlength="150" class="regular-text" value="<?php if (isset($wplc_settings['wplc_pro_offline3'])) { echo esc_attr($wplc_settings['wplc_pro_offline3']); } ?>" /> 
                        </td>
                    </tr>
                    <tr>
                        <td width="300" valign="top"><?php _e("Offline Button Text", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_offline_btn" name="wplc_pro_offline_btn" type="text" size="50" maxlength="50" class="regular-text" value="<?php if (isset($wplc_settings['wplc_pro_offline_btn'])) { echo esc_attr($wplc_settings['wplc_pro_offline_btn']); } ?>" /> <br />
                        </td>
                    </tr>
                    <tr>
                        <td width="300" valign="top"><?php _e("Offline Send Button Text", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_offline_btn_send" name="wplc_pro_offline_btn_send" type="text" size="50" maxlength="50" class="regular-text" value="<?php if (isset($wplc_settings['wplc_pro_offline_btn_send'])) { echo esc_attr($wplc_settings['wplc_pro_offline_btn_send']); } ?>" /> <br />
                        </td>
                    </tr>

                </table>

                <h4><?php _e("Email settings", 'wp-live-chat-support') ?></h4> 


                <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages'>
                    <tr>
                        <td width='300' valign='top'>
<?php _e("Send to agent(s)", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Email address where offline messages are delivered to. Use comma separated email addresses to send to more than one email address", 'wp-live-chat-support') ?>"></i>
                        </td>
                        <td>
                            <input id="wplc_pro_chat_email_address" name="wplc_pro_chat_email_address" class="regular-text" type="text" value="<?php if (isset($wplc_settings['wplc_pro_chat_email_address'])) {
    echo $wplc_settings['wplc_pro_chat_email_address']; } ?>" />
                        </td>
                    </tr>

                     <tr>
                        <td width='300' valign='top'>
                            <?php _e("Subject", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("User name will be appended to the end of the subject.", 'wp-live-chat-support') ?>"></i>
                        </td>
                        <td>
                            <input id="wplc_pro_chat_email_offline_subject" name="wplc_pro_chat_email_offline_subject" class="regular-text" type="text" value="<?php echo(isset($wplc_settings['wplc_pro_chat_email_offline_subject']) ? $wplc_settings['wplc_pro_chat_email_offline_subject'] : ""); ?>" placeholder="<?php echo __("WP Live Chat by 3CX - Offline Message from ", 'wp-live-chat-support'); ?>"/>
                        </td>
                    </tr>

                </table>

                
					  
				<table class='form-table wp-list-table wplc_list_table widefat fixed striped pages'>
					<tr>
						<td width="300" valign="top"><?php _e("Auto-respond to visitor", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Send your visitors an email as soon as they send you an offline message", 'wp-live-chat-support') ?>"></i></td>
						<td>
							<input id="wplc_ar_enable" name="wplc_ar_enable" type="checkbox" value="1" <?php if( isset( $wplc_auto_responder_settings['wplc_ar_enable'] ) ) { echo "checked"; } ?> /> <br />
						</td>
					</tr>
					<tr>
						<td width="300" valign="top"><?php _e("Auto-responder 'From' name", 'wp-live-chat-support') ?>: </td>
						<td>
							<input type="text" name="wplc_ar_from_name" id="wplc_ar_from_name" class="regular-text" value="<?php if( isset( $wplc_auto_responder_settings['wplc_ar_from_name'] ) ) { echo stripslashes($wplc_auto_responder_settings['wplc_ar_from_name']); } ?>" />
						</td>
					</tr>
					<tr>
						<td width="300" valign="top"><?php _e("Auto-responder 'From' email", 'wp-live-chat-support') ?>: </td>
						<td>
							<input type="text" name="wplc_ar_from_email" id="wplc_ar_from_email" class="regular-text" value="<?php if( isset( $wplc_auto_responder_settings['wplc_ar_from_email'] ) ) { echo $wplc_auto_responder_settings['wplc_ar_from_email']; } ?>" /> 
						</td>
					</tr>
					<tr>
						<td width="300" valign="top"><?php _e("Auto-responder subject", 'wp-live-chat-support') ?>: </td>
						<td>
							<input type="text" name="wplc_ar_subject" id="wplc_ar_subject" class="regular-text" value="<?php if( isset( $wplc_auto_responder_settings['wplc_ar_subject'] ) ) { echo $wplc_auto_responder_settings['wplc_ar_subject']; } ?>" />
						</td>
					</tr>
					<tr>
						<td width="300" valign="top"><?php _e("Auto-responder body", 'wp-live-chat-support') ?>: <br/></td>
						<td>
							<textarea name="wplc_ar_body" id="wplc_ar_body" rows="6" style="width:50%;"><?php if( isset( $wplc_auto_responder_settings['wplc_ar_body'] ) ) { echo esc_textarea( $wplc_auto_responder_settings['wplc_ar_body'] ); } ?></textarea>
							<p class="description"><small><?php _e("HTML and the following shortcodes can be used", 'wp-live-chat-support'); ?>: <?php _e("User's name", 'wp-live-chat-support'); ?>: {wplc-user-name} <?php _e("User's email address", 'wp-live-chat-support'); ?>: {wplc-email-address}</small></p>
						</td>
					</tr>
				</table> 
					  
            </div>

      
      
      <div id="tabs-4">
                <style>
                    .wplc_theme_block img{
                        border: 1px solid #CCC;
                        border-radius: 5px;
                        padding: 5px;
                        margin: 5px;
                    }         
                    .wplc_theme_single{
                        width: 162px;
                        height: 162px;
                        text-align: center;
                        display: inline-block;
                        vertical-align: top;
                        margin: 5px;
                    }
                                            .wplc_animation_block div{
                            display: inline-block;
                            width: 150px;
                            height: 150px;
                            border: 1px solid #CCC;
                            border-radius: 5px;
                            text-align: center;  
                            margin: 10px;
                        }
                        .wplc_animation_block i{
                            font-size: 3em;
                            line-height: 150px;
                        }
                        .wplc_animation_block .wplc_red{
                            color: #0596d4;
                        }
                        .wplc_animation_block .wplc_orange{
                            color: #0596d4;
                        }
                        .wplc_animation_active, .wplc_theme_active {
                            border: 2px solid #616161 !important;
                        }
                </style>
                <style>
                  .wplc_animation_block div{
                      display: inline-block;
                      width: 150px;
                      height: 150px;
                      border: 1px solid #CCC;
                      border-radius: 5px;
                      text-align: center;  
                      margin: 10px;
                  }
                  .wplc_animation_block i{
                      font-size: 3em;
                      line-height: 150px;
                  }
                  .wplc_animation_block .wplc_red{
                      color: #0596d4;
                  }
                  .wplc_animation_block .wplc_orange{
                      color: #0596d4;
                  }
              </style>
          <h3><?php _e("Styling",'wp-live-chat-support')?></h3>
          <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages'>
              <?php
                $wplc_theme = wplc_get_theme();
              ?>

              <tr style='margin-bottom: 10px;'>
                <td style="width:300px;"><label for=""><?php _e('Color scheme', 'wp-live-chat-support'); ?></label></td>
                <td>    
                    <div class='wplc_theme_block'>
                        <div class='wplc_palette'>
                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-default') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_default'>
                                  <div class='wplc-palette-top' style='background-color:#0596d4;'></div>
                                  <div class='wplc-palette-top' style='background-color:#FFF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#06B4FF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#373737;'></div>
                                </div>
                            </div>

                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-1') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_1'>
                                  <div class='wplc-palette-top' style='background-color:#DB0000;'></div>
                                  <div class='wplc-palette-top' style='background-color:#FFF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#000;'></div>
                                  <div class='wplc-palette-top' style='background-color:#373737;'></div>
                                </div>
                            </div>
                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-2') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_2'>
                                  <div class='wplc-palette-top' style='background-color:#000;'></div>
                                  <div class='wplc-palette-top' style='background-color:#FFF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#888;'></div>
                                  <div class='wplc-palette-top' style='background-color:#373737;'></div>
                                </div>
                            </div>
                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-3') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_3'>
                                  <div class='wplc-palette-top' style='background-color:#B97B9D;'></div>
                                  <div class='wplc-palette-top' style='background-color:#FFF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#EEE;'></div>
                                  <div class='wplc-palette-top' style='background-color:#5A0031;'></div>
                                </div>
                            </div>
                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-4') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_4'>
                                  <div class='wplc-palette-top' style='background-color:#1A14DB;'></div>
                                  <div class='wplc-palette-top' style='background-color:#FDFDFF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#7F7FB3;'></div>
                                  <div class='wplc-palette-top' style='background-color:#373737;'></div>
                                </div>
                            </div>
                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-5') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_5'>
                                  <div class='wplc-palette-top' style='background-color:#3DCC13;'></div>
                                  <div class='wplc-palette-top' style='background-color:#FDFDFF;'></div>
                                  <div class='wplc-palette-top' style='background-color:#EEE;'></div>
                                  <div class='wplc-palette-top' style='background-color:#373737;'></div>
                                </div>
                            </div>                            
                            <div class='wplc_palette_single'>
                                <div class='wplc-palette-selection <?php if ($wplc_theme == 'theme-6') { echo 'wplc_theme_active'; } ?>' id='wplc_theme_6'>
                                  <div class='wplc-palette-top' style='padding-top:3px'>&nbsp;</div>
                                  <div class='wplc-palette-top' style='padding-top:18px'><?php _e("Custom Scheme",'wp-live-chat-support'); ?></div>
                                  <div class='wplc-palette-top' style='padding-top:3px'>&nbsp;</div>
                                </div>
                            </div> 


                        </div>
                    </div>
                    <input type="radio" name="wplc_theme" value="theme-default" class="wplc_hide_input" id="wplc_rb_theme_default" <?php if ($wplc_theme == 'theme-default') { echo 'checked'; } ?>/>
                    <input type="radio" name="wplc_theme" value="theme-1" class="wplc_hide_input" id="wplc_rb_theme_1" <?php if ($wplc_theme == 'theme-1') { echo 'checked'; } ?>/>
                    <input type="radio" name="wplc_theme" value="theme-2" class="wplc_hide_input" id="wplc_rb_theme_2" <?php if ($wplc_theme == 'theme-2') { echo 'checked'; } ?>/>
                    <input type="radio" name="wplc_theme" value="theme-3" class="wplc_hide_input" id="wplc_rb_theme_3" <?php if ($wplc_theme == 'theme-3') { echo 'checked'; } ?>/>
                    <input type="radio" name="wplc_theme" value="theme-4" class="wplc_hide_input" id="wplc_rb_theme_4" <?php if ($wplc_theme == 'theme-4') { echo 'checked';  } ?>/>
                    <input type="radio" name="wplc_theme" value="theme-5" class="wplc_hide_input" id="wplc_rb_theme_5" <?php if ($wplc_theme == 'theme-5') { echo 'checked'; } ?>/>
                    <input type="radio" name="wplc_theme" value="theme-6" class="wplc_hide_input" id="wplc_rb_theme_6" <?php if ($wplc_theme == 'theme-6') { echo 'checked'; } ?>/>

                </td>
              </tr>

              <tr class='wplc_custom_pall_rows' style='<?php echo (($wplc_theme == 'theme-6') ? '' : 'display:none;'); ?>' >
                  <td width='200' valign='top'><?php _e("Palette Color 1",'wp-live-chat-support')?>:</td>
                  <td>
                      <input id="wplc_settings_color1" name="wplc_settings_color1" type="color" value="#<?php if (isset($wplc_settings_color1)) { echo $wplc_settings_color1; } else { echo '0596d4'; } ?>" />
                  </td>
              </tr>
              <tr class='wplc_custom_pall_rows' style='<?php echo (($wplc_theme == 'theme-6') ? '' : 'display:none;'); ?>' >
                  <td width='200' valign='top'><?php _e("Palette Color 2",'wp-live-chat-support')?>:</td>
                  <td>
                      <input id="wplc_settings_color2" name="wplc_settings_color2" type="color" value="#<?php if (isset($wplc_settings_color2)) { echo $wplc_settings_color2; } else { echo 'FFFFFF'; } ?>" />
                  </td>
              </tr>
              <tr class='wplc_custom_pall_rows' style='<?php echo (($wplc_theme == 'theme-6') ? '' : 'display:none;'); ?>' >
                  <td width='200' valign='top'><?php _e("Palette Color 3",'wp-live-chat-support')?>:</td>
                  <td>
                      <input id="wplc_settings_color3" name="wplc_settings_color3" type="color" value="#<?php if (isset($wplc_settings_color3)) { echo $wplc_settings_color3; } else { echo 'EEEEEE'; } ?>" />
                  </td>
              </tr>
              <tr class='wplc_custom_pall_rows' style='<?php echo (($wplc_theme == 'theme-6') ? '' : 'display:none;'); ?>' >
                  <td width='200' valign='top'><?php _e("Palette Color 4",'wp-live-chat-support')?>:</td>
                  <td>
                      <input id="wplc_settings_color4" name="wplc_settings_color4" type="color" value="#<?php if (isset($wplc_settings_color4)) { echo $wplc_settings_color4; } else { echo '373737'; } ?>" />
                  </td>
              </tr>

              <tr>
                  <td width='300' valign='top'><?php _e("Chat background",'wp-live-chat-support')?>:</td>
                  <td>
                      
                      <select id='wplc_settings_bg' name='wplc_settings_bg'>
                          <option value="cloudy.jpg" <?php if (!isset($wplc_settings['wplc_settings_bg']) || ($wplc_settings['wplc_settings_bg'] == "cloudy.jpg") ) { echo "selected"; } ?>><?php _e("Cloudy",'wp-live-chat-support'); ?></option>
                          <option value="geometry.jpg" <?php if (isset($wplc_settings['wplc_settings_bg']) && $wplc_settings['wplc_settings_bg'] == "geometry.jpg") { echo "selected"; } ?>><?php _e("Geometry",'wp-live-chat-support'); ?></option>
                          <option value="tech.jpg" <?php if (isset($wplc_settings['wplc_settings_bg']) && $wplc_settings['wplc_settings_bg'] == "tech.jpg") { echo "selected"; } ?>><?php _e("Tech",'wp-live-chat-support'); ?></option>
                          <option value="social.jpg" <?php if (isset($wplc_settings['wplc_settings_bg']) && $wplc_settings['wplc_settings_bg'] == "social.jpg") { echo "selected"; } ?>><?php _e("Social",'wp-live-chat-support'); ?></option>
                          <option value="0" <?php if (isset($wplc_settings['wplc_settings_bg']) && $wplc_settings['wplc_settings_bg'] == "0") { echo "selected"; } ?>><?php _e("None",'wp-live-chat-support'); ?></option>
                      </select>
                  </td>
              </tr>  

                    <tr>
                        <td width="200" valign="top"><?php _e("Use localization plugin", 'wp-live-chat-support') ?></td>
                        <td>
                            <input type="checkbox" name="wplc_using_localization_plugin" id="wplc_using_localization_plugin" value="1"<?php echo ($wplc_settings['wplc_using_localization_plugin'] ? ' checked' : ''); ?> />
                            <br/><small><?php echo sprintf( __("Enable this if you are using a localization plugin. Should you wish to change the below strings with this option enabled, please visit %sthe documentation%s", 'wp-live-chat-support'), "<a href='https://www.3cx.com/wp-live-chat/docs/localization/' target='_BLANK'>", '</a>'); ?></small>
                        </td>
                    </tr>

                  <tr style='height:30px;'><td></td><td></td></tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Chat box title", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_fst1" name="wplc_pro_fst1" type="text" size="50" maxlength="50" class="regular-text" value="<?php echo esc_attr($wplc_settings['wplc_pro_fst1']) ?>" /> <br />
                        </td>
                    </tr>
                      <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Chat box sub-title", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_fst2" name="wplc_pro_fst2" type="text" size="50" maxlength="50" class="regular-text" value="<?php echo esc_attr($wplc_settings['wplc_pro_fst2']) ?>" /> <br />
                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Chat box intro", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_intro" name="wplc_pro_intro" type="text" size="50" maxlength="150" class="regular-text" value="<?php echo esc_attr($wplc_settings['wplc_pro_intro']) ?>" /> <br />
                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Start chat button label", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_sst1" name="wplc_pro_sst1" type="text" size="50" maxlength="30" class="regular-text" value="<?php echo esc_attr($wplc_settings['wplc_pro_sst1']) ?>" /> <br />
                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Start chat status message", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_sst2" name="wplc_pro_sst2" type="text" size="50" maxlength="70" class="regular-text" value="<?php echo esc_attr($wplc_settings['wplc_pro_sst2']) ?>" /> <br />
                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Re-activate chat message", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_pro_tst1" name="wplc_pro_tst1" type="text" size="50" maxlength="50" class="regular-text" value="<?php echo esc_attr($wplc_settings['wplc_pro_tst1']) ?>" /> <br />


                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Welcome message", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_welcome_msg" name="wplc_welcome_msg" type="text" size="50" maxlength="350" class="regular-text" value="<?php echo stripslashes($wplc_settings['wplc_welcome_msg']) ?>" /> <span class='description'><?php _e('This text is shown as soon as a user starts a chat and waits for an agent to join', 'wp-live-chat-support'); ?></span><br />
                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Agent no answer message", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_user_no_answer" name="wplc_user_no_answer" type="text" size="50" maxlength="150" class="regular-text" value="<?php echo (isset($wplc_settings['wplc_user_no_answer']) ? stripslashes($wplc_settings['wplc_user_no_answer']) : $wplc_default_settings_array['wplc_user_no_answer']); ?>" /> <span class='description'><?php _e('This text is shown to the user when an agent has failed to answer a chat', 'wp-live-chat-support'); ?></span><br />
                        </td>
                    </tr>
                    <tr class="wplc_localization_strings">
                        <td width="200" valign="top"><?php _e("Other text", 'wp-live-chat-support') ?>:</td>
                        <td>
                            <input id="wplc_user_enter" name="wplc_user_enter" type="text" size="50" maxlength="150" class="regular-text" value="<?php echo stripslashes($wplc_settings['wplc_user_enter']) ?>" /><br />
                            <input id="wplc_text_chat_ended" name="wplc_text_chat_ended" type="text" size="50" maxlength="150" class="regular-text" value="<?php echo ( empty( $wplc_settings['wplc_text_chat_ended'] ) ) ? stripslashes(__("The chat has been ended by the agent.", 'wp-live-chat-support')) : stripslashes( $wplc_settings['wplc_text_chat_ended'] ) ?>" /> <br />
                        </td>
                    </tr>
                        
                    <tr>
                        <td><label for=""><?php _e('Chat box animation', 'wp-live-chat-support'); ?></label></td>

                        <td>    
                            <div class='wplc_animation_block'>
                                <div class='wplc_animation_image <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-1') {
                    echo 'wplc_animation_active';
                } ?>' id='wplc_animation_1'>
                                    <i class="fa fa-arrow-circle-up wplc_orange"></i>
                                    <p><?php _e('Slide Up', 'wp-live-chat-support'); ?></p>
                                </div>
                                <div class='wplc_animation_image <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-2') {
                    echo 'wplc_animation_active';
                } ?>' id='wplc_animation_2'>
                                    <i class="fa fa-arrows-alt-h wplc_red"></i>
                                    <p><?php _e('Slide From The Side', 'wp-live-chat-support'); ?></p>
                                </div>
                                <div class='wplc_animation_image <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-3') {
                    echo 'wplc_animation_active';
                } ?>' id='wplc_animation_3'>
                                    <i class="fa fa-arrows-alt wplc_orange"></i>
                                    <p><?php _e('Fade In', 'wp-live-chat-support'); ?></p>
                                </div>
                                <div class='wplc_animation_image <?php if ((isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-4') || !isset($wplc_settings['wplc_animation'])) {
                    echo 'wplc_animation_active';
                } ?>' id='wplc_animation_4'>
                                    <i class="fa fa-thumbtack wplc_red"></i>
                                    <p><?php _e('No Animation', 'wp-live-chat-support'); ?></p>
                                </div>
                            </div>
                            <input type="radio" name="wplc_animation" value="animation-1" class="wplc_hide_input" id="wplc_rb_animation_1" class='wplc_hide_input' <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-1') {
                    echo 'checked';
                } ?>/>
                            <input type="radio" name="wplc_animation" value="animation-2" class="wplc_hide_input" id="wplc_rb_animation_2" class='wplc_hide_input' <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-2') {
                    echo 'checked';
                } ?>/>
                            <input type="radio" name="wplc_animation" value="animation-3" class="wplc_hide_input" id="wplc_rb_animation_3" class='wplc_hide_input' <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-3') {
                    echo 'checked';
                } ?>/>
                            <input type="radio" name="wplc_animation" value="animation-4" class="wplc_hide_input" id="wplc_rb_animation_4" class='wplc_hide_input' <?php if (isset($wplc_settings['wplc_animation']) && $wplc_settings['wplc_animation'] == 'animation-4') {
                    echo 'checked';
                } ?>/>
                        </td>
                    </tr>
					<tr>
						<td width='300' valign='top'><?php _e("Auto-response to first message",'wp-live-chat-support')?>:</td>
						<td>  
							<input type="text" name="wplc_pro_auto_first_response_chat_msg" value="<?php echo $wplc_pro_auto_first_response_chat_msg; ?>">
							<span class='description'><?php _e('This message will be sent automatically after the first message is sent from the user side. Leave empty to disable.', 'wp-live-chat-support'); ?></span>
						</td>   
					</tr>

          </table>
      </div>
        <div id="tabs-5">


        <?php
				    $user_array = wplc_get_agent_users();

	    echo "<h3>".__('Chat Agents', 'wp-live-chat-support')."</h3>";

	    $wplc_agents = "<div class='wplc_agent_container'><ul>";

        foreach ($user_array as $user) {

            $wplc_agents .= "<li id=\"wplc_agent_li_".$user->ID."\">";
            $wplc_agents .= "<p><img src=\"//www.gravatar.com/avatar/" . md5($user->user_email) . "?s=60&d=mm\" /></p>";
            if (wplc_agent_is_online($user->ID)) {
              $wplc_agents .= "<span class='wplc_status_box wplc_type_returning'>".__("Logged In",'wp-live-chat-support')."</span>";
            }
            $wplc_agents .= "<h3>" . esc_html($user->display_name) . "</h3>";
            
            $wplc_agents .= "<small>" . esc_html($user->user_email) . "</small>";

            $wplc_agents .= apply_filters("wplc_pro_agent_list_before_button_filter", "", $user);

            if (get_current_user_id() == $user->ID) {
            } else { 
                $wplc_agents .= "<p><button class='button button-secondary wplc_remove_agent' id='wplc_remove_agent_".$user->ID."' uid='".$user->ID."'>".__("Remove",'wp-live-chat-support')."</button></p>";
            }
            $wplc_agents .= "</li>";
        }
	    echo $wplc_agents;
	    ?>
	    <li style='width:150px;' id='wplc_add_new_agent_box'>
	        <p><i class='fa fa-plus-circle fa-4x' style='color:#ccc;' ></i></p>
	        <h3><?php _e("Add New Agent",'wp-live-chat-support'); ?></h3>
	        <select id='wplc_agent_select'>
	            <option value=''><?php _e("Select",'wp-live-chat-support'); ?></option>
	        <?php 
	            $blogusers = get_users( array( 'role' => 'administrator', 'fields' => array( 'display_name','ID','user_email' ) ) );
	            // Array of stdClass objects.
	            foreach ( $blogusers as $user ) {
	                if (wplc_user_is_agent($user->ID)) {echo '<option id="wplc_selected_agent_'. intval( $user->ID ) .'" em="' . md5(sanitize_email( $user->user_email )) . '" uid="' . intval( $user->ID ) . '" em2="' . esc_attr( $user->user_email ) . '"  name="' . esc_attr( $user->display_name ) . '" value="' . intval( $user->ID ) . '">' . esc_html( $user->display_name ) . ' ('.__('Administrator','wp-live-chat-support').')</option>'; }
	            }
	            $blogusers = get_users( array( 'role' => 'editor', 'fields' => array( 'display_name','ID','user_email' ) ) );
	            // Array of stdClass objects.
	            foreach ( $blogusers as $user ) {
	                if (wplc_user_is_agent($user->ID)) { echo '<option id="wplc_selected_agent_'. intval( $user->ID ) .'" em="' . md5(sanitize_email( $user->user_email )) . '" uid="' . intval( $user->ID ) . '" em2="' . esc_attr( $user->user_email ) . '"  name="' . esc_attr( $user->display_name ) . '" value="' . intval( $user->ID ) . '">' . esc_html( $user->display_name ) . ' ('.__('Editor','wp-live-chat-support').')</option>'; }
	            }
	            $blogusers = get_users( array( 'role' => 'author', 'fields' => array( 'display_name','ID','user_email' ) ) );
	            // Array of stdClass objects.
	            foreach ( $blogusers as $user ) {
	                if (wplc_user_is_agent($user->ID)) { echo '<option id="wplc_selected_agent_'. intval( $user->ID ) .'" em="' . md5(sanitize_email( $user->user_email )) . '" uid="' . intval( $user->ID ) . '" em2="' . esc_attr( $user->user_email ) . '"  name="' . esc_attr( $user->display_name ) . '" value="' . intval( $user->ID ) . '">' . esc_html( $user->display_name ) . ' ('.__('Author','wp-live-chat-support').')</option>'; }
	            }
	        ?>
	        </select>
	        <p><button class='button button-secondary' id='wplc_add_agent' style="display: none;"><?php _e("Add Agent",'wp-live-chat-support'); ?></button></p>
	    </li>
	</ul>
	</div>
	    
	                <hr/>
	                <p class="description"><?php echo sprintf(__("Should you wish to add a user that has a role less than 'Author', please go to the %s page, select the relevant user, click Edit and scroll to the bottom of the page and enable the 'Chat Agent' checkbox.", 'wp-live-chat-support'), "<a href='./users.php'>".__("Users",'wp-live-chat-support')."</a>"); ?></p>
	                <p class="description"><?php _e("If there are no chat agents online, the chat will show as offline", 'wp-live-chat-support'); ?></p>

            
        </div>
        <div id="tabs-7">           
            <h3><?php _e("Blocked Visitors / IP Addresses", 'wp-live-chat-support') ?></h3>
            <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='100%'>                       
              <tr>
                <td>
                  <textarea name="wplc_ban_users_ip" style="width: 50%; min-height: 200px;" placeholder="<?php _e('Enter each IP Address you would like to block on a new line', 'wp-live-chat-support'); ?>" autocomplete="false"><?php
                      $ip_addresses = get_option('WPLC_BANNED_IP_ADDRESSES'); 
                      if($ip_addresses){
                          $ip_addresses = maybe_unserialize($ip_addresses);
                          if ($ip_addresses && is_array($ip_addresses)) {
                              foreach($ip_addresses as $ip){
                                  echo esc_textarea($ip)."\n";
                              }
                          }
                      }
                  ?></textarea>  
                  <p class="description"><?php _e('Blocking a user\'s IP Address here will hide the chat window from them, preventing them from chatting with you. Each IP Address must be on a new line', 'wp-live-chat-support'); ?></p>
                </td>
              </tr>
            </table>
        </div>

		<?php

	$content = "";

	$content .= "<div id='wplc-business-hours'>";
	$content .= "<h3>".__("Business Hours", 'wp-live-chat-support')."</h3>";
	$content .= "<table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' >";
	$content .= "<tr>";
	$content .= "<td width='200'>".__("Enable Business Hours", 'wp-live-chat-support')."</td>";
	$content .= "<td><input type='checkbox' name='wplc_bh_enable' id='wplc_bh_enable' value='1' ".($wplc_settings['wplc_bh_enable'] ? ' checked' : '')." /></td>";
	$content .= "</tr>";
	$content .= "<tr>";
	$content .= "<td width='200'>".__("Working days", 'wp-live-chat-support')."</td>";
  $content .= "<td><table class='form-table wp-list-table wplc_business_list_table'>
  <tr><th style='width:120px'>".__("Week Day", 'wp-live-chat-support')."</th><th>".__("Morning Schedule", 'wp-live-chat-support')."</th><th>".__("Afternoon Schedule", 'wp-live-chat-support')."</th></tr>";
  for ($day=0;$day<7;$day++) {
    $content.='<tr><td style="text-align:left"><label><input type="checkbox" '.(!empty($wplc_settings['wplc_bh_days'][$day]) ? 'checked' : '').' name="wplc_bh_days['.$day.']" id="wplc_bh_days['.$day.']">'.ucfirst(date_i18n('l', gmmktime(12,0,0,1,2+$day,2000))).'</label></td>'; // 2000-01-02 is sunday
    $content .= '<td>'.__("From", 'wp-live-chat-support');
    $content .= wplc_render_select_time('bh_hs1['.$day.']','bh_ms1['.$day.']', $wplc_settings['wplc_bh_schedule'][$day][0]['hs'], $wplc_settings['wplc_bh_schedule'][$day][0]['ms']);
    $content .=' '.__("to", 'wp-live-chat-support').' ';
    $content .= wplc_render_select_time('bh_he1['.$day.']','bh_me1['.$day.']', $wplc_settings['wplc_bh_schedule'][$day][0]['he'], $wplc_settings['wplc_bh_schedule'][$day][0]['me']);
    $content .='</td>';
    $content .= '<td>'.__("From", 'wp-live-chat-support');
    $content .= wplc_render_select_time('bh_hs2['.$day.']','bh_ms2['.$day.']', $wplc_settings['wplc_bh_schedule'][$day][1]['hs'], $wplc_settings['wplc_bh_schedule'][$day][1]['ms']);
    $content .=' '.__("to", 'wp-live-chat-support').' ';
    $content .= wplc_render_select_time('bh_he2['.$day.']','bh_me2['.$day.']', $wplc_settings['wplc_bh_schedule'][$day][1]['he'], $wplc_settings['wplc_bh_schedule'][$day][1]['me']);
    $content .= "</td></tr>";

    $t1 = gmmktime($wplc_settings['wplc_bh_schedule'][$day][0]['hs'],$wplc_settings['wplc_bh_schedule'][$day][0]['ms'],0,1,1,2000);
    $t2 = gmmktime($wplc_settings['wplc_bh_schedule'][$day][0]['he'],$wplc_settings['wplc_bh_schedule'][$day][0]['me'],0,1,1,2000);
    $t3 = gmmktime($wplc_settings['wplc_bh_schedule'][$day][1]['hs'],$wplc_settings['wplc_bh_schedule'][$day][1]['ms'],0,1,1,2000);
    $t4 = gmmktime($wplc_settings['wplc_bh_schedule'][$day][1]['he'],$wplc_settings['wplc_bh_schedule'][$day][1]['me'],0,1,1,2000);
  
    if ($t1>$t2 || $t2>$t3 || $t3>$t4) {
      $content.='<tr><td colspan="3"><p class="notice notice-warning">'.__('Time intervals are incorrect or overlapping. Please fix your settings or you might get unexpected behavior.', 'wp--live-chat-support').'</p></td></tr>';
    }

  }

	$content .= "</table></td>";
  $content .= "</tr>";
  

	$content .= "<tr>";
	$content .= "<td width='200'>".__("Current Site Time", 'wp-live-chat-support')."</td>";
	$content .= "<td>";
	$content .= $current_time = current_time('mysql');
	$content .= "</td>";
	$content .= "</tr>";

	$content .= "</table>";
	$content .= "</div>";

  echo $content;
  $new_key_nonce = wp_create_nonce('generate_new_encryption_key');

  ?>

    <div id="tabs-9">            
      <h3><?php _e("Chat Encryption", 'wp-live-chat-support') ?></h3>
      <table class='form-table wp-list-table wplc_list_table widefat fixed striped pages' width='700'>
          <tr>
              <td width='300' valign='top'><?php _e("Enable Encryption", 'wp-live-chat-support') ?>: <i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e('All messages will be encrypted when being sent to and from the user and agent.', 'wp-live-chat-support'); ?>"></i></td> 
              <td>
                  <input type="checkbox" name="wplc_enable_encryption" id="wplc_enable_encryption" value="1"<?php echo ($wplc_settings['wplc_enable_encryption'] ? ' checked' : ''); ?>/>
                  <p class='notice notice-error' style="margin-top:24px">
                      <?php _e('Once enabled, all messages sent will be encrypted. This cannot be undone.', 'wp-live-chat-support'); ?>
                  </p>
              </td>
          </tr>
          <tr>
           <td width="250" valign="top">
             <label for="wplc_encryption_key"><?php _e("Encryption key",'wp-live-chat-support'); ?></label>
           </td>
           <td valign="top">
             <input type="text" value="<?php echo $wplc_settings['wplc_encryption_key']; ?>" id="wplc_encryption_key" name="wplc_encryption_key" disabled>
			 <input type="hidden" name="wplc_encryption_key_nonce" id="wplc_encryption_key_nonce" value="<?php echo $new_key_nonce; ?>">
             <div class="button button-secondary" id="wplc_new_encryption_key_btn"><?php _e("Generate New", 'wp-live-chat-support'); ?></div>
             <p class="wplc_error_message" id="wplc_new_encryption_key_error"></p>
             <p class='notice notice-warning' style="margin-top:24px">
                      <?php _e('If you change encryption key, all previously encrypted messages will be lost. This cannot be undone.', 'wp-live-chat-support'); ?>
                  </p>
           </td>
         </tr>          
      </table>
  </div>

        <?php do_action("wplc_hook_settings_page_more_tabs"); ?>
        
    </div>
    <p class='submit'><input type='submit' name='wplc_save_settings' class='button-primary' value='<?php _e("Save Settings",'wp-live-chat-support')?>' /></p>
    </form>
    
    </div>


