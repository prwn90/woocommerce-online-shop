<?php 
/*
 * Handles Modal Content Creation
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the HTML for a modal window button, which will open the respective modal
 *
 * @return string (html)
*/
function wplc_create_modal_trigger_button_open($unique_id, $text){
	return "<a href='javascript:void(0);' class='button wplc_modal_trigger_open' id='wplc_modal_trigger_open_" . $unique_id . "' modal_id='" . $unique_id . "'>" . $text . "</a> ";
}

/**
 * Return the HTML for a modal window
 *
 * @return string (html)
*/
function wplc_create_modal_window($unique_id, $title, $content){
	$the_modal = "";

	if($title === null){
		$title =  __("Please Confirm", 'wp-live-chat-support');
	}

	if($content === null){
		$content =  __("Are you sure?", 'wp-live-chat-support');
	}

	$the_modal .= "<div class='wplc_modal' id='wplc_modal_".$unique_id."' style='display:none;'>";
	$the_modal .= 	"<div class='wplc_modal_inner' id='wplc_modal_inner_".$unique_id."'>";
	$the_modal .= 		"<div class='wplc_modal_inner_title' id='wplc_modal_inner_title_".$unique_id."'>" . $title . "</div>";
	$the_modal .= 		"<div class='wplc_modal_inner_content' id='wplc_modal_inner_content_".$unique_id."'>" . $content . "</div>";
	$the_modal .= 		"<div class='wplc_modal_inner_actions' id='wplc_modal_inner_actions_".$unique_id."'>";
	$the_modal .= 		"<a href='javascript:void(0);' class='button wplc_modal_cancel' id='wplc_modal_cancel_".$unique_id."' modal_id='" . $unique_id . "'>" . __("Cancel", 'wp-live-chat-support') . "</a> ";
	$the_modal .= 		"<a href='javascript:void(0);' class='button button-primary wplc_modal_confirm' id='wplc_modal_confirm_".$unique_id."' modal_id='" . $unique_id . "'>" . __("Confirm", 'wp-live-chat-support') . "</a> ";
	$the_modal .= 		"</div>";
	$the_modal .= 	"</div>";
	$the_modal .= "</div>";

	return $the_modal;
}

add_action('admin_print_scripts', 'wplc_admin_modal_javascript');
/**
 * Enqueues modal javascript
 *
 * @return void
*/
function wplc_admin_modal_javascript(){
	if(isset($_GET['page']) && $_GET['page'] == 'wplivechat-menu'){
		wp_register_script('wplc-admin-modal-js', plugins_url('../js/wplc-admin-modal.js', __FILE__), array(), WPLC_PLUGIN_VERSION, true);
    	wp_enqueue_script('wplc-admin-modal-js');
    }
}
