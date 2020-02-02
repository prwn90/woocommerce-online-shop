<?php
/**
 * BLOCK: WP Live Chat Support Chat box
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wplc_get_gutenberg_options() {
  global $get_gutenberg_options;
  if (!$get_gutenberg_options) {
    $get_gutenberg_options = get_option('wplc_gutenberg_settings');
  }
}

function wplc_gutenberg_block_settings() {
	add_filter('wplc_filter_setting_tabs','wplc_gutenberg_setting_tabs');
	add_action("wplc_hook_settings_page_more_tabs","wplc_gutenberg_settings_content");
	global $get_gutenberg_options;
	wplc_get_gutenberg_options();
}

add_action('admin_init', 'wplc_gutenberg_block_settings');

function wplc_gutenberg_setting_tabs($tab_array) {
    $tab_array['gutenberg'] = array(
      'href' => '#tabs-gutenberg',
      'icon' => 'far fa-comment-dots',
      'label' => __('Gutenberg Blocks', 'wp-live-chat-support')
    );
    return $tab_array;
}

function wplc_gutenberg_settings_content() {
	$gutenberg_settings = get_option('wplc_gutenberg_settings');

	$gutenberg_enable = ( $gutenberg_settings['wplc_gutenberg_enable'] !== null ) ? $gutenberg_settings['wplc_gutenberg_enable'] : 1;
	$checked = ( @$gutenberg_enable == 1 ? 'checked' : '' );
	$gutenberg_size = ( $gutenberg_settings['wplc_gutenberg_size'] ) ? $gutenberg_settings['wplc_gutenberg_size'] : 2;
	$gutenberg_defail_logo = WPLC_PLUGIN_URL.'images/wplc_loading.png';
	$gutenberg_logo = ( $gutenberg_settings['wplc_gutenberg_logo'] == '' ) ? esc_url($gutenberg_defail_logo) : $gutenberg_settings['wplc_gutenberg_logo'];
	$gutenberg_text = ( $gutenberg_settings['wplc_gutenberg_text'] ) ? sanitize_text_field($gutenberg_settings['wplc_gutenberg_text']) : 'Live Chat';
	$gutenberg_icon = ( $gutenberg_settings['wplc_gutenberg_icon'] ) ? sanitize_text_field($gutenberg_settings['wplc_gutenberg_icon']) : 'fa-comment-dots';
	$gutenberg_enable_icon = ( $gutenberg_settings['wplc_gutenberg_enable_icon'] !== null ) ? intval($gutenberg_settings['wplc_gutenberg_enable_icon']) : 1;
	$icon_checked = ( @$gutenberg_enable_icon == 1 ? 'checked' : '' );
	$gutenberg_custom_html = ( $gutenberg_settings['wplc_custom_html'] ) ? $gutenberg_settings['wplc_custom_html'] : '';
	?>

	<div id="tabs-gutenberg">      
		<h3><?php _e('Gutenberg Blocks', 'wp-live-chat-support') ?></h3>
		<table class='form-table wp-list-table wplc_list_table widefat fixed striped pages'>

		    <tr>
		        <td width='300' valign='top'><?php _e('Enable Gutenberg Blocks', 'wp-live-chat-support') ?>:</td> 

		        <td>
		            <input type="checkbox" id="activate_block" name="activate_block" <?php echo $checked ?>/>
		        </td>
		    </tr>

		    <tr>
		        <td width='300' valign='top'><?php _e('Block size', 'wp-live-chat-support') ?>:</td> 
		        <td>
		            <select id="wplc_gutenberg_size" name="wplc_gutenberg_size" value="1">
		            	<option <?php echo ($gutenberg_size == 1) ? 'selected' : ''; ?> value="1">Small</option>
		            	<option <?php echo ($gutenberg_size == 2) ? 'selected' : ''; ?> value="2">Medium</option>
		            	<option <?php echo ($gutenberg_size == 3) ? 'selected' : ''; ?> value="3">Large</option>
		       		</select>
		        </td>
		    </tr>

		    <tr>
		        <td width='300' valign='top'><?php _e('Set block logo', 'wp-live-chat-support') ?>:</td>

		        <td>
		            <input type="button" id="wplc_gutenberg_upload_logo" class="button button-primary" value="Upload Logo"/>
		            <input type="button" id="wplc_gutenberg_remove_logo" class="button button-default" value="Reset Logo"/>
		            <input type="hidden" id="wplc_gutenberg_default_logo" value="<?php echo $gutenberg_defail_logo; ?>" />
		            <input type="hidden" id="wplc_gutenberg_logo" name="wplc_gutenberg_logo" value="<?php echo $gutenberg_logo; ?>"/>
		        </td>
		    </tr>

		    <tr>
		        <td width='300' valign='top'><?php _e('Text in block', 'wp-live-chat-support') ?>:</td>

		        <td>
		            <input type="text" id="wplc_gutenberg_text" name="wplc_gutenberg_text" placeholder="Block text" value="<?php echo $gutenberg_text ?>"/>
		        </td>
		    </tr>

		    <tr>
		        <td width='300' valign='top'><?php _e('Use icon', 'wp-live-chat-support') ?>:<td>
		            <input type="checkbox" id="wplc_gutenberg_enable_icon" name="wplc_gutenberg_enable_icon" <?php echo $icon_checked; ?>/>
		        </td>
		    </tr>

		    <tr>
		        <td width='300' valign='top'><?php _e('Icon in block', 'wp-live-chat-support') ?>:</td>

		        <td>
		            <input type="text" id="wplc_gutenberg_icon" name="wplc_gutenberg_icon" placeholder="Block icon" value="<?php echo $gutenberg_icon ?>"/>
		        </td>
		    </tr>

		    <tr>
		        <td width='300' valign='top'><?php _e("Preview block", 'wp-live-chat-support') ?>:</td>

		        <td>
		            <div id="wplc-chat-box" class="wplc_gutenberg_preview"></div>
		        </td>
		    </tr>
	
			<tr>
		        <td width='300' valign='top'><?php _e('Custom HTML Template', 'wp-live-chat-support') ?>:
					<small><p><i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip"></i> You can use the following placeholders to add content dynamically:</p>
					<p><code class="wplc_code" title="Click to copy text">{wplc_logo}</code> - <?php _e('Displays the chosen logo', 'wp-live-chat-support'); ?></p>
					<p><code class="wplc_code" title="Click to copy text">{wplc_text}</code> - <?php _e('Displays the chosen custom text', 'wp-live-chat-support'); ?></p>
					<p><code class="wplc_code" title="Click to copy text">{wplc_icon}</code> - <?php _e('Displays the chosen icon', 'wp-live-chat-support'); ?></p></small>
		        </td>

		        <td>
		            <div id='wplc_custom_html_editor'></div>
		            <textarea name='wplc_custom_html' id='wplc_custom_html' style='display: none;' data-editor='css' rows='12'>
		            	<?php echo trim($gutenberg_custom_html); ?>
		            </textarea>
		           
		        	
		        	<input type="button" id="wplc_gutenberg_reset_html" class="button button-default" value="Reset Default"/>
		        	<select id="wplc_custom_templates">
		        		<option selected value="0">Select a Template</option>
		        		<option value="template_default">Default - Dark</option>
		        		<option value="template_default_light">Default - Light</option>
		        		<option value="template_default_tooltip">Default - Tooltip</option>
		        		<option value="template_circle">Circle - Default</option>
		        		<option value="template_tooltip">Circle - Tooltip</option>
		        		<option value="template_circle_rotate">Circle - Rotating</option>
		        		<option value="template_chat_bubble">Chat Bubble</option>
		        		
		        	</select>
		        </td>
		    </tr>
		</table>
	</div>

	<?php 
}

add_action('wplc_hook_admin_settings_save','wplc_gutenberg_save_settings');

function wplc_gutenberg_save_settings() {
	
    if (isset($_POST['wplc_save_settings'])) {

        if (isset($_POST['activate_block'])) {
            $wplc_gutenberg_data['wplc_gutenberg_enable'] = 1;
        } else {
            $wplc_gutenberg_data['wplc_gutenberg_enable'] = 0;
        }

        if (isset($_POST['wplc_gutenberg_logo']) && $_POST['wplc_gutenberg_logo'] !== '0') {
            $wplc_gutenberg_data['wplc_gutenberg_logo'] = esc_url($_POST['wplc_gutenberg_logo']);
        } else {
            $wplc_gutenberg_data['wplc_gutenberg_logo'] = WPLC_PLUGIN_URL.'images/wplc_loading.png';
        }

        if (isset($_POST['wplc_gutenberg_size']) && $_POST['wplc_gutenberg_size'] !== '0') {
            $wplc_gutenberg_data['wplc_gutenberg_size'] = sanitize_text_field($_POST['wplc_gutenberg_size']);
        } else {
            $wplc_gutenberg_data['wplc_gutenberg_size'] = '2';
        }

        if (isset($_POST['wplc_gutenberg_text']) && $_POST['wplc_gutenberg_text'] !== '0') {
            $wplc_gutenberg_data['wplc_gutenberg_text'] = stripslashes(sanitize_text_field($_POST['wplc_gutenberg_text']));
        } else {
            $wplc_gutenberg_data['wplc_gutenberg_text'] = 'Live Chat';
        }

        if (isset($_POST['wplc_gutenberg_icon']) && $_POST['wplc_gutenberg_icon'] !== '0') {
            $wplc_gutenberg_data['wplc_gutenberg_icon'] = stripslashes(sanitize_text_field($_POST['wplc_gutenberg_icon']));
        } else {
            $wplc_gutenberg_data['wplc_gutenberg_icon'] = 'fa-comment-dots';
        }

        if (isset($_POST['wplc_gutenberg_enable_icon'])) {
            $wplc_gutenberg_data['wplc_gutenberg_enable_icon'] = 1;
        } else {
            $wplc_gutenberg_data['wplc_gutenberg_enable_icon'] = 0;
        }

        if (isset($_POST['wplc_custom_html']) && $_POST['wplc_custom_html'] !== '0') {
            $wplc_gutenberg_data['wplc_custom_html'] = stripslashes(wp_filter_post_kses($_POST['wplc_custom_html']));
        } else {
        	$default_html = '\n<div class="wplc_block">\n\t<span class="wplc_block_logo">{wplc_logo}</span>\n\t<span class="wplc_block_text">{wplc_text}</span>\n\t<span class="wplc_block_icon">{wplc_icon}</span>\n</div>';

            $wplc_gutenberg_data['wplc_custom_html'] = $default_html;
        }
        
        update_option('wplc_gutenberg_settings', $wplc_gutenberg_data);
    }
}
	
add_action( 'enqueue_block_editor_assets', 'wplc_chat_box_block_editor_assets' );

function wplc_chat_box_block_editor_assets() {
		global $get_gutenberg_options;
	 	$gutenberg_settings = $get_gutenberg_options;
		if($get_gutenberg_options['wplc_gutenberg_enable']) {
			// Scripts
			wp_enqueue_script(
				'wplc_chat_box',
				plugins_url( 'block.js', __FILE__ ),
				array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
			);

			$gutenberg_logo = $gutenberg_settings['wplc_gutenberg_logo'];
			$settings['wplc_typing'] = __("Type here",'wp-live-chat-support');
			$settings['wplc_enabled'] = $gutenberg_settings['wplc_gutenberg_enable'];
			$settings['wplc_size'] = ( $gutenberg_settings['wplc_gutenberg_size'] ? sanitize_text_field( $gutenberg_settings['wplc_gutenberg_size'] ) : 2 );
			$settings['wplc_logo'] = $gutenberg_logo;
			$settings['wplc_text'] = ( $gutenberg_settings['wplc_gutenberg_text'] ? sanitize_text_field( $gutenberg_settings['wplc_gutenberg_text'] ) : __( 'Live Chat', 'wp-live-chat-support') );

			$settings['wplc_icon'] = ( $gutenberg_settings['wplc_gutenberg_icon'] ? sanitize_text_field( $gutenberg_settings['wplc_gutenberg_icon'] ) : 'fa-comment-dots' );
			$settings['wplc_icon_enabled'] = $gutenberg_settings['wplc_gutenberg_enable_icon'];
			$settings['wplc_custom_html'] = $gutenberg_settings['wplc_custom_html'];

			wp_localize_script( 'wplc_chat_box', 'wplc_settings', $settings );

			// Styles
			wp_enqueue_style(
				'wplc_chat_box-editor',
				plugins_url( 'editor.css', __FILE__ ),
				array( 'wp-edit-blocks' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'editor.css' )
			);
	}
}


add_action( 'enqueue_block_assets', 'wplc_chat_box_block_block_assets' );

function wplc_chat_box_block_block_assets() {
	global $get_gutenberg_options;
	if($get_gutenberg_options['wplc_gutenberg_enable']) {
		// Styles for front-end
		wp_enqueue_style(
			'wplc_chat_box-front-end',
			plugins_url( 'style.css', __FILE__ ),
			array( 'wp-blocks' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
		);
		wp_enqueue_style(
			'wplc_chat_box-front-end-template', plugins_url( 'wplc_gutenberg_template_styles.css', __FILE__ ), array( 'wp-blocks' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'wplc_gutenberg_template_styles.css' )
		);
	}
}
