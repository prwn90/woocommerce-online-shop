<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(class_exists("WP_REST_Request")){
	//The request class was found, move one
	include_once "wplc-api-routes.php";
	include_once "wplc-api-functions.php";
	
}else{
	//No Rest Request class
}

/*
 * Checks if a secret key has been created. 
 * If not create one for use in the API
*/
add_action("wplc_activate_hook", "wplc_api_s_key_check", 10);
add_action("wplc_update_hook", "wplc_api_s_key_check", 10);
function wplc_api_s_key_check(){
	if (!get_option("wplc_api_secret_token")) {
		$user_token = wplc_api_s_key_create();
        add_option("wplc_api_secret_token", $user_token);
    }
}


/*
 * Generates a new Secret Token
*/
function wplc_api_s_key_create(){
	$the_code = rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000);
	$the_time = time();
	$token = md5($the_code . $the_time);
	return $token;
}

/*
 * Adds 'Rest API' tab to settings area
*/
add_filter("wplc_filter_setting_tabs","wplc_api_settings_tab_heading");
function wplc_api_settings_tab_heading($tab_array) {
    $tab_array['api'] = array(
      "href" => "#tabs-api",
      "icon" => 'fa fa-plug',
      "label" => __("REST API",'wp-live-chat-support')
    );
    return $tab_array;
}

/*
 * Adds 'Rest API' content to settings area
*/
add_action("wplc_hook_settings_page_more_tabs","wplc_api_settings_tab_content");
function wplc_api_settings_tab_content() {
    ?>
		<div id="tabs-api">
	<?php

	if(!class_exists("WP_REST_Request")){
		?>
		 	<div class="update-nag">
		 		<?php _e("To make use of the REST API, please ensure you are using a version of WordPress with the REST API included.", 'wp-live-chat-support');?>
		 		<br><br>
		 		<?php _e("Alternatively, please install the official Rest API plugin from WordPress.", 'wp-live-chat-support');?>
		 	</div>
		<?php
	} else {

		$secret_token = get_option("wplc_api_secret_token"); //Checks for token
		$new_secret_token_nonce = wp_create_nonce('generate_new_secret_token');
		?>
			<h3><?php _e("REST API", 'wp-live-chat-support') ?></h3>
			<table class=" form-table wp-list-table wplc_list_table widefat fixed striped pages">
				<tbody>
					<tr>
						<td width='200'>
							<?php _e("Secret Token", 'wp-live-chat-support') ?>
						</td>
						<td>
							<input style="max-width:60%; width:100%" type="text" id="wplc_secret_token_input" value="<?php echo ($secret_token === false ? __('No secret token found', 'wp-live-chat-support') : $secret_token) ?>" readonly>
							<input type="hidden" name="wplc_new_secret_token_nonce" id="wplc_new_secret_token_nonce" value="<?php echo $new_secret_token_nonce; ?>">
              <div class="button-secondary" id="wplc_new_secret_token_btn"><?php _e("Generate New", 'wp-live-chat-support') ?></div>
              <p class="wplc_error_message" id="wplc_secret_token_error"></p>
						</td>
					</tr>
				</tbody>
			</table>
			<br>

			<?php do_action("wplc_api_below_table_hook"); ?>

		</div>
		
		<?php
	}

}

