<?php
/**
 * Privacy module
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

require_once (plugin_dir_path(__FILE__) . "gdpr.php");

add_filter("wplc_filter_setting_tabs","wplc_privacy_settings_tab_heading");
/**
 * Adds 'Privacy' tab to settings area
*/
function wplc_privacy_settings_tab_heading($tab_array) {
    $tab_array['privacy'] = array(
      "href" => "#tabs-privacy",
      "icon" => 'fa fa-eye',
      "label" => __("Privacy",'wp-live-chat-support')
    );
    return $tab_array;
}

add_action("wplc_hook_settings_page_more_tabs","wplc_privacy_settings_tab_content");
/**
 * Adds 'Privacy' content to settings area
*/
function wplc_privacy_settings_tab_content() {
 $wplc_settings = wplc_get_options();
 
 ?>
   <div id="tabs-privacy">
	    <h3><?php _e("Privacy", 'wp-live-chat-support') ?></h3>
	     
		 <?php
		 	do_action("wplc_hook_privacy_options_content", $wplc_settings);
		 ?>
 	</div>
 <?php
}