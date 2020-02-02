<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wplc_triggers_page(){
	wplc_enqueue_admin_styles_mrg();
	$wplc_add_trigger_btn = isset($_GET['wplc_action']) ? "" : "<a href='?page=wplivechat-menu-triggers&wplc_action=add_trigger' class='wplc_add_new_btn'>". __("Add New", 'wp-livechat') ."</a>";

	$content = "<div class='wrap wplc_wrap'>";
    $content .= "<h2>".__('Triggers', 'wp-livechat'). $wplc_add_trigger_btn . "</h2>";
    echo $content;
    if(isset($_GET['wplc_action']) && ($_GET['wplc_action'] == "add_trigger" || $_GET['wplc_action'] == "edit_trigger")){
		wplc_mrg_get_add_trigger_content();
    } else if(isset($_GET['wplc_action']) && ($_GET['wplc_action'] == "delete_trigger")){
    	wplc_mrg_get_delete_trigger_content();
    } else {
    	wplc_mrg_get_trigger_table();
    }

    $content = "</div>"; //Close Wrap
    

    echo $content;
}

function wplc_mrg_get_add_trigger_content(){
	wplc_mrg_trigger_admin_js();
	
	$content = "";

	$wplc_trigger_nonce = wp_create_nonce('wplc_trigger_nonce');

	//Content Vars
	$trigger_name = "";
	$trigger_type = 0;
	$trigger_content = null;
	$trigger_replace = "checked";
	$trigger_enabled = "checked";

	$wplc_submit_label = "Create Trigger"; //Default

	//Header Handler
	$header_array = wplc_mrg_trigger_admin_head();
	if($header_array){
		if(isset($header_array['trigger_name'])){ $trigger_name = $header_array['trigger_name']; }
		if(isset($header_array['trigger_type'])){ $trigger_type = intval($header_array['trigger_type']); }
		if(isset($header_array['trigger_content'])){ $trigger_content = maybe_unserialize($header_array['trigger_content']); }
		if(isset($header_array['trigger_replace'])){ $trigger_replace = "checked"; } else { $trigger_replace = ""; }
		if(isset($header_array['trigger_enabled'])){ $trigger_enabled = "checked"; } else { $trigger_enabled = ""; }
	}

	if(isset($_GET['wplc_action'])){
		if($_GET['wplc_action'] == 'edit_trigger'){
			$wplc_submit_label = "Save Trigger";
		}
	}

	$content .= "<form method='POST'>";
	$content .= "<table class=\"wp-list-table wplc_list_table widefat fixed form-table\" cellspacing=\"0\" style='width:50%'>";

  	$content .= 	"<tr>";
    $content .= 		"<td>".__("Trigger Name", 'wp-live-chat-support').":</td>";
    $content .= 		"<td><input type='text' name='wplc_trigger_name' value='".esc_attr($trigger_name)."'></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr>";
    $content .= 		"<td>".__("Trigger Type", 'wp-live-chat-support').":</td>";
    $content .= 		"<td>";

    $content .= 		"<select name='wplc_trigger_type'>";
    $content .= 			"<option value='0' ".($trigger_type == 0 ? "selected" : "").">".__("Page Trigger", 'wp-live-chat-support')."</option>";
    $content .= 			"<option value='1' ".($trigger_type == 1 ? "selected" : "").">".__("Time Trigger", 'wp-live-chat-support')."</option>";
    $content .= 			"<option value='2' ".($trigger_type == 2 ? "selected" : "").">".__("Scroll Trigger", 'wp-live-chat-support')."</option>";
    $content .= 			"<option value='3' ".($trigger_type == 3 ? "selected" : "").">".__("Page Leave Trigger", 'wp-live-chat-support')."</option>";
    $content .= 		"</select> <i class='fa fa-question-circle' title='".__("Note: When using page trigger with a the basic theme, no hovercard is shown by default. We suggest using the time trigger for this instead.", 'wp-live-chat-support')."'></i>";

    $content .= 		"</td>";
    $content .= 	"</tr>";

    $content .= 	"<tr id='wplc_trigger_page_row'>";
    $content .= 		"<td>".__("Page ID", 'wp-live-chat-support').":</td>";
    $content .= 		"<td><input type='text' name='wplc_trigger_pages' value='".(isset($trigger_content["pages"]) ? esc_attr($trigger_content["pages"]) : "")."'> <i>".__("Note: Leave empty for 'all' pages", 'wp-live-chat-support')."</i></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr id='wplc_trigger_secs_row'>";
    $content .= 		"<td>".__("Show After", 'wp-live-chat-support').":</td>";
    $content .= 		"<td><input type='text' name='wplc_trigger_secs' value='".(isset($trigger_content["secs"]) && intval($trigger_content["secs"]) >= 0 ? intval($trigger_content["secs"]) : 0)."'> <i>".__("Seconds", 'wp-live-chat-support')."</i></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr id='wplc_trigger_scroll_row'>";
    $content .= 		"<td>".__("Show After Scrolled", 'wp-live-chat-support').":</td>";
    $content .= 		"<td><input type='text' name='wplc_trigger_perc' value='".(isset($trigger_content["secs"]) && intval($trigger_content["perc"]) >= 0 ? intval($trigger_content["perc"]) : 0)."'> <i>".__("(%) Percent of page height", 'wp-live-chat-support')."</i></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr>";
    $content .= 		"<td style='vertical-align: top !important;'>".__("Content Replacement", 'wp-live-chat-support').":</td>";
    $content .= 		"<td>";
    
    echo $content; //To Support TinyMCE
    wp_editor( (isset($trigger_content["html"]) ? $trigger_content["html"] : ""),"wplc_trigger_content",array("teeny" => false, "media_buttons" => true, "textarea_name" => "wplc_trigger_content", "textarea_rows" => 5));

    $content .= 		"</td>";
    $content .= 	"</tr>";

    $content = 	"<tr>";
    $content .= 		"<td>".__("Replace Content", 'wp-live-chat-support').":</td>";
    $content .= 		"<td><input type='checkbox' name='wplc_trigger_replace_content' $trigger_replace></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr>";
    $content .= 		"<td>".__("Enable Trigger", 'wp-live-chat-support').":</td>";
    $content .= 		"<td><input type='checkbox' name='wplc_trigger_enable' $trigger_enabled></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr>";
    $content .= 		"<td></td>";
    $content .= 		"<td><input class='button button-primary' type='submit' name='wplc_trigger_submit' value='".__($wplc_submit_label, 'wp-live-chat-support')."'> <a href='".admin_url()."admin.php?page=wplivechat-menu-triggers"."' class='button'>".__("Close", 'wp-live-chat-support')."</a></td>";
    $content .= 	"</tr>";

	$content .= "</table>";
	$content .= "<input name='wplc_trigger_nonce' type='hidden' value='" . $wplc_trigger_nonce . "' >";
	$content .= "</form>";

	if($header_array){
		if(count($header_array["errors"]) >= 1){
			$content .= "<div class='update-nag'>";
			$content .= "<strong>".__("Please review your submission", 'wp-live-chat-support').":</strong>";
			$content .= 	"<ul style='list-style:initial;'>";
			for($i = 0; $i < count($header_array["errors"]); $i++){
				$content .= 	"<li style='margin-left: 25px;'>".__($header_array["errors"][$i], 'wp-live-chat-support')."</li>";
			}
			$content .= 	"</ul>";
			$content .= "</div>";
		}

		if(isset($header_array["success"])){
			$content .= "<div class='update-nag' style='border-color:#67d552;'>";
			$content .= "<strong>".__($header_array["success"], 'wp-live-chat-support')."</strong>";
			$content .= "</div>";
		}
	}

	echo $content;
}

function wplc_mrg_trigger_admin_js(){
	?>
	<script>
	//Trigger Admin JS Here

	jQuery(function(){
		jQuery(function(){
			UpdateInputs(jQuery("select[name=wplc_trigger_type]").val());

			jQuery("select[name=wplc_trigger_type]").change(function(){
				UpdateInputs(jQuery("select[name=wplc_trigger_type]").val());				
			});
		});

		function UpdateInputs(theVal){
			switch(parseInt(theVal)){
				case 0:
					jQuery("#wplc_trigger_page_row").show();		
					jQuery("#wplc_trigger_secs_row").hide();
					jQuery("#wplc_trigger_scroll_row").hide();
					break;
				case 1:
					jQuery("#wplc_trigger_page_row").show();		
					jQuery("#wplc_trigger_secs_row").show();
					jQuery("#wplc_trigger_scroll_row").hide();
					break;
				case 2:
					jQuery("#wplc_trigger_page_row").show();		
					jQuery("#wplc_trigger_secs_row").hide();
					jQuery("#wplc_trigger_scroll_row").show();
					break;
				case 3:
					jQuery("#wplc_trigger_page_row").show();		
					jQuery("#wplc_trigger_secs_row").hide();
					jQuery("#wplc_trigger_scroll_row").hide();
					break;
			}
		}
	});
	</script>
	<?php
}

function wplc_mrg_trigger_admin_head(){
	if(isset($_GET['wplc_action'])){
		$return_array = array();
		$form_valid = true;
		if(isset($_POST['wplc_trigger_submit'])){
			$return_array["errors"] = array();

			if(isset($_POST['wplc_trigger_name']) && $_POST['wplc_trigger_name'] !== ""){
				$return_array["trigger_name"] = sanitize_text_field($_POST['wplc_trigger_name']);
			} else {
				$return_array["errors"][count($return_array["errors"]) >= 1 ? count($return_array["errors"]) : 0] = "Name cannot be empty";
				$form_valid = false; //No Longer Valid
			}

			if(isset($_POST['wplc_trigger_type']) && $_POST['wplc_trigger_type'] !== ""){
				$return_array["trigger_type"] = intval($_POST['wplc_trigger_type']);
			}

			$serialized_content = array();

			//Validation added here for serialized array
			if(isset($_POST['wplc_trigger_pages']) && $_POST['wplc_trigger_pages'] !== ""){	
				$serialized_content['pages'] = sanitize_text_field($_POST['wplc_trigger_pages']);
			} else {
				$serialized_content['pages'] = "";
			}

			if(isset($_POST['wplc_trigger_secs']) && $_POST['wplc_trigger_secs'] !== ""){	
				$serialized_content['secs'] = intval($_POST['wplc_trigger_secs']);
			}


			if(isset($_POST['wplc_trigger_perc']) && $_POST['wplc_trigger_perc'] !== ""){	
				$serialized_content['perc'] = intval($_POST['wplc_trigger_perc']);
			}

			if(isset($_POST['wplc_trigger_content']) && $_POST['wplc_trigger_content'] !== ""){	
				$supporter_tags = wplc_trigger_get_allowed_tags_mrg();
        $serialized_content['html'] = wp_kses(nl2br($_POST['wplc_trigger_content']), $supporter_tags);
			}

			$return_array["trigger_content"] = serialize($serialized_content);

			if(isset($_POST['wplc_trigger_replace_content'])){
				if(isset($_POST['wplc_trigger_content']) && $_POST['wplc_trigger_content'] !== ""){ } else {
					$return_array["errors"][count($return_array["errors"]) >= 1 ? count($return_array["errors"]) : 0] = "Content cannot be empty (When replace content is enabled)";
					$form_valid = false; //No Longer Valid
				}	

				$return_array["trigger_replace"] = 1;
			}

			if(isset($_POST['wplc_trigger_enable'])){
				$return_array["trigger_enabled"] = 1;
			}
		}

		if($_GET['wplc_action'] == "add_trigger"){
				if($form_valid && isset($_POST['wplc_trigger_submit'])){
					//All good continue
					if (!isset($_POST['wplc_trigger_nonce']) || !wp_verify_nonce($_POST['wplc_trigger_nonce'], 'wplc_trigger_nonce')){
			              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
			        }

					if(wplc_add_trigger_mrg($return_array)){
						//Redirect here
						echo "<script> window.location = '".admin_url()."admin.php?page=wplivechat-menu-triggers"."';</script>";
					}
				} else {
					return $return_array; //Return Posted Data
				}
		} else if ($_GET['wplc_action'] == "edit_trigger"){
			//Editing now
			$edit_array = array();
			$edit_array["errors"] = array();
			if (isset($return_array["errors"])) { $edit_array["errors"] = $return_array["errors"];  }
			//Submit data first
			if($form_valid && isset($_POST['wplc_trigger_submit'])){
				if (!isset($_POST['wplc_trigger_nonce']) || !wp_verify_nonce($_POST['wplc_trigger_nonce'], 'wplc_trigger_nonce')){
		              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
		        }

				//All good continue
				if(isset($_GET['trigger_id'])){
					if(wplc_edit_trigger_mrg($return_array, intval($_GET['trigger_id']))){
						//Show edit message
						$edit_array['success'] = "<div>".__("Trigger has been edited.", 'wp-live-chat-support')."</div>";
					}
				} else {
					//No Trigger ID
					$edit_array["errors"][count($edit_array["errors"]) >= 1 ? count($edit_array["errors"]) : 0] = "Trigger ID not found";
				}
			}

			if(isset($_GET['trigger_id'])){
				$trigger_data = wplc_get_trigger_mrg(intval($_GET['trigger_id']));
				if($trigger_data){
					if($trigger_data !== false && is_array($trigger_data)){					
						//Got the data
						if(isset($trigger_data[0]->name) && $trigger_data[0]->name !== ""){ $edit_array["trigger_name"] = esc_html($trigger_data[0]->name); }
						if(isset($trigger_data[0]->type) && $trigger_data[0]->type !== ""){ $edit_array["trigger_type"] = intval($trigger_data[0]->type); }
						if(isset($trigger_data[0]->content) && $trigger_data[0]->content !== ""){ $edit_array['trigger_content'] = $trigger_data[0]->content ; }
						if(isset($trigger_data[0]->show_content) && intval($trigger_data[0]->show_content) == 1){ $edit_array["trigger_replace"] = 1; }
						if(isset($trigger_data[0]->status) && intval($trigger_data[0]->status) == 1){ $edit_array["trigger_enabled"] = 1; }
					}

				} else{
					$edit_array["errors"][count($edit_array["errors"]) >= 1 ? count($edit_array["errors"]) : 0] = "Trigger ID not found";
				}
			}else{
				$edit_array["errors"][count($edit_array["errors"]) >= 1 ? count($edit_array["errors"]) : 0] = "Trigger ID not set";
			}
			return $edit_array; //Return Server Data
		}else if($_GET['wplc_action'] == "delete_trigger"){
			$delete_array = array();
			if(isset($_GET['trigger_id'])){
				$trigger_data = wplc_get_trigger_mrg(intval($_GET['trigger_id']));
				if($trigger_data){
					$delete_array["name"] = $trigger_data[0]->name;
				}

				if(isset($_POST['delete_confirm'])){
					if (!isset($_POST['wplc_trigger_nonce']) || !wp_verify_nonce($_POST['wplc_trigger_nonce'], 'wplc_trigger_nonce')){
			              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
			        }
			        
					//Delete now
					if(wplc_delete_trigger_mrg(intval($_GET['trigger_id']))){
						//Success
					}
					echo "<script> window.location = '".admin_url()."admin.php?page=wplivechat-menu-triggers"."';</script>";
				}
			}
			return $delete_array;
		}else {
			return false;
		}
	}else{
		return false;
	}
}

function wplc_add_trigger_mrg($trigger_data){
	global $wpdb;
    global $wplc_tblname_chat_triggers;
	if($trigger_data){
		$trigger_name = null;
		$trigger_type = null;
		$trigger_content = null;
		$trigger_replace = null;
		$trigger_enabled = null;
		//Validation - 1
		if($trigger_data['trigger_name'] != ""){ $trigger_name = $trigger_data['trigger_name']; } else { return false; }
		if($trigger_data['trigger_content'] != ""){ $trigger_content = $trigger_data['trigger_content']; } else { return false; }
		if($trigger_data['trigger_replace'] != ""){ $trigger_replace = 1; } else { $trigger_replace = 0; }
		if($trigger_data['trigger_enabled'] != ""){ $trigger_enabled = 1; }else{ $trigger_enabled = 0; }

		$trigger_type = intval($trigger_data['trigger_type']);
		
		
		//Validation - 2 
		$trigger_name = sanitize_text_field($trigger_name);

		$sql = "INSERT INTO $wplc_tblname_chat_triggers SET `name` = '%s', `type` = '%s', `content` = '%s', `show_content` = '%d', `status` = '%d' ";
		$sql = $wpdb->prepare($sql, $trigger_name, $trigger_type, $trigger_content, $trigger_replace, $trigger_enabled);
       	$wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        } 
	}
}


function wplc_edit_trigger_mrg($trigger_data, $trigger_id){
	global $wpdb;
    global $wplc_tblname_chat_triggers;
	if($trigger_data){
		$trigger_name = null;
		$trigger_type = null;
		$trigger_content = null;
		$trigger_replace = null;
		$trigger_enabled = null;
		//Validation - 1
		if($trigger_data['trigger_name'] != ""){ $trigger_name = $trigger_data['trigger_name']; } else { return false; }
		if($trigger_data['trigger_content'] != ""){ $trigger_content = $trigger_data['trigger_content']; } else { return false; }
		if(isset($trigger_data['trigger_replace']) && $trigger_data['trigger_replace'] != ""){ $trigger_replace = 1; } else { $trigger_replace = 0; }
		if(isset($trigger_data['trigger_enabled']) && $trigger_data['trigger_enabled'] != ""){ $trigger_enabled = 1; }else{ $trigger_enabled = 0; }
		
		$trigger_type = intval($trigger_data['trigger_type']);
		//Validation - 2 
		$trigger_name = sanitize_text_field($trigger_name);

		$trigger_id = intval($trigger_id);
		$sql = "UPDATE $wplc_tblname_chat_triggers SET `name` = '%s', `type` = '%s', `content` = '%s', `show_content` = '%d', `status` = '%d' WHERE `id` = '%d' ";
		$sql = $wpdb->prepare($sql, $trigger_name, $trigger_type, $trigger_content, $trigger_replace, $trigger_enabled, $trigger_id);
       	$wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        } 
	}
}

function wplc_delete_trigger_mrg($trigger_id){
	global $wpdb;
	global $wplc_tblname_chat_triggers;
	$trigger_id = intval($trigger_id);
	$sql = "DELETE FROM $wplc_tblname_chat_triggers WHERE `id` = '%d' ";
	$sql = $wpdb->prepare($sql, $trigger_id);
   	$wpdb->query($sql);
    if ($wpdb->last_error) { 
        return false;  
    } else {
        return true;
    } 
}

function wplc_change_trigger_status_mrg($trigger_id, $trigger_status){
	global $wpdb;
    global $wplc_tblname_chat_triggers;
	if(isset($trigger_id) && isset($trigger_status)){
		$trigger_id = intval($trigger_id);
		$trigger_status = intval($trigger_status);
		$sql = "UPDATE $wplc_tblname_chat_triggers SET `status` = '%d' WHERE `id` = '%d' ";
		$sql = $wpdb->prepare($sql, $trigger_status, $trigger_id);
       	$wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        } 
	}
}

function wplc_mrg_get_trigger_table(){
	$content = "";

	wplc_mrg_trigger_table_head();

	//Now Get the Contents
  	$results = wplc_get_all_triggers_mrg();
	
	$conflict_array = wplc_triggers_check_for_conflicts_mrg($results);
	
	$wplc_trigger_status_nonce = wp_create_nonce('wplc_trigger_status_nonce');

	if(count($conflict_array) > 0){
		$content .= "<div class='update-nag' style='margin-top:0px;margin-bottom:10px;padding-top:8px;padding-bottom:8px;font-size:12px;'>";
		$content .= "<strong>".__("Conflict with page") . ":</strong>";
		foreach ($conflict_array as $conflict) {
			$content .= "<br>".__("Trigger ID: ", 'wp-live-chat-support').$conflict;
			$content .= "<br>".__("It is possible that this trigger may override another trigger, or be overridden by another trigger.", 'wp-live-chat-support');
		}
		$content .= "</div>";
	}

	$conflictations = implode(",", $conflict_array);

	$content .= "<table class=\"wp-list-table wplc_list_table widefat fixed \" cellspacing=\"0\" style='width:98%'>";
	$content .= 	"<thead>";
  	$content .= 		"<tr>";
    $content .= 			"<th scope='col'><span>" . __("ID", 'wp-live-chat-support') . "</span></th>";
    $content .= 			"<th scope='col'><span>" . __("Name", 'wp-live-chat-support') . "</span></th>";
    $content .= 			"<th scope='col'>" . __("Type", 'wp-live-chat-support') . "</th>";
    $content .= 			"<th scope='col'>" . __("Page", 'wp-live-chat-support') . "</th>";
    $content .= 			"<th scope='col'>" . __("Content", 'wp-live-chat-support') . "</th>";
    $content .= 			"<th scope='col'>" . __("Status", 'wp-live-chat-support') . "</th>";
    $content .= 			"<th scope='col'><span>" . __("Action", 'wp-live-chat-support') . "</span></th>";
    $content .= 		"</tr>";
  	$content .= 	"</thead>";

  	
  	if($results){
  		foreach ($results as $result) {
  			$trigger_actions = "<a class='button' href='?page=wplivechat-menu-triggers&wplc_action=edit_trigger&trigger_id=".$result->id."'>".__("Edit", 'wp-live-chat-support')."</a> ";
  			$trigger_actions .= "<a class='button' href='?page=wplivechat-menu-triggers&wplc_action=delete_trigger&trigger_id=".$result->id."'>".__("Delete", 'wp-live-chat-support')."</a> ";

  			$trigger_content = maybe_unserialize($result->content);

  			$content .= "<tr>";
  			$content .= 	"<td style='".(!is_bool(strpos($conflictations, $result->id)) && strpos($conflictations, $result->id) >= 0 ? "border-left:4px solid #ffba00" : "")."'>".$result->id."</td>";
  			$content .= 	"<td>".$result->name."</td>";
  			$content .= 	"<td>".__(wplc_get_type_from_code_mrg($result->type), 'wp-live-chat-support')."</td>";
  			$content .= 	"<td>".(sanitize_text_field($trigger_content["pages"]) == "" ? __("All", 'wp-live-chat-support') : sanitize_text_field($trigger_content["pages"]))."</td>";
  			$content .= 	"<td>".trim(substr(htmlentities($trigger_content["html"]), 0, 120))."...</td>";
  			$content .= 	"<td>";
  			$content .=			"<div class='wplc_trigger_status ".($result->status == 1 ? "wplc_trigger_enabled" : "wplc_trigger_disabled")."'>";
  			$content .=			  "<a href='?page=wplivechat-menu-triggers&wplc_action=trigger_status_change&trigger_id=".$result->id."&trigger_status=".($result->status == 1 ? "0" : "1")."&wplc_trigger_status_nonce=".$wplc_trigger_status_nonce."' title='".__("Click to change trigger status", 'wp-live-chat-support')."'>";
  			$content .=				__(($result->status == 1 ? "Enabled" : "Disabled"), 'wp-live-chat-support');
  			$content .=			  "</a>";
  			$content .=			"</div>";
  			$content .=		"</td>";
  			$content .= 	"<td>".$trigger_actions."</td>";
  			$content .= "</tr>";
  			
  		}
  	} else {
  		$content .= "<tr><td>".__("No Triggers Found...", 'wp-live-chat-support')."</td><td></td><td></td><td></td><td></td></tr>";
  	}

  	$content .= 	"</table>";
	echo $content;
}

function wplc_mrg_trigger_table_head(){
	if(isset($_GET['wplc_action']) && isset($_GET['trigger_id']) && isset($_GET['trigger_status'])){
		
		if (!isset($_GET['wplc_trigger_status_nonce']) || !wp_verify_nonce($_GET['wplc_trigger_status_nonce'], 'wplc_trigger_status_nonce')){
	              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
	    }
		
		if($_GET['wplc_action'] == 'trigger_status_change'){
			$trigger_id = intval($_GET['trigger_id']);
			if(isset($trigger_id)){
				$trigger_status = intval($_GET['trigger_status']);
				if($trigger_status > 1){ $trigger_status = 1; } else if($trigger_status < 0){ $trigger_status = 0; } //Check if status in range
				//All good execute
				if(wplc_change_trigger_status_mrg($trigger_id, $trigger_status)){

				}
			}
		}
	}
}

function wplc_get_all_triggers_mrg(){
    global $wpdb;
    global $wplc_tblname_chat_triggers;
    
    $sql = "SELECT * FROM $wplc_tblname_chat_triggers"; 
    $sql .= " ORDER BY `status` DESC"; //Sort

    $results =  $wpdb->get_results($sql);
    if($wpdb->num_rows){
    	return $results;
    } else {
    	return false;
    }
    
}

function wplc_get_trigger_mrg($trigger_id){
	global $wpdb;
    global $wplc_tblname_chat_triggers;
    
    $trigger_id = intval($trigger_id);

    $sql = "SELECT * FROM $wplc_tblname_chat_triggers WHERE `id` = '$trigger_id'"; 

    $results =  $wpdb->get_results($sql);
    if($wpdb->num_rows){
    	return $results;
    } else {
    	return false;
    }
}

function wplc_get_active_triggers_mrg(){
    global $wpdb;
    global $wplc_tblname_chat_triggers;
    
    $sql = "SELECT * FROM $wplc_tblname_chat_triggers WHERE `status` = '1'"; 

    $results =  $wpdb->get_results($sql);
    if($wpdb->num_rows){
    	return $results;
    } else {
    	return false;
    }
    
}

function wplc_get_type_from_code_mrg($code){
	$type_string = "";
	switch($code){
		case 0:
		$type_string = "Page Trigger";
		break;
		case 1:
		$type_string = "Time Trigger";
		break;
		case 2:
		$type_string = "Scroll Trigger";
		break;
		case 3:
		$type_string = "Page Leave Trigger";
		break;
	}

	return $type_string;
}

function wplc_mrg_get_delete_trigger_content(){
	$header_array = wplc_mrg_trigger_admin_head();
	$wplc_trigger_nonce = wp_create_nonce('wplc_trigger_nonce');

	$trigger_name = "";
	if($header_array){
		if(isset($header_array["name"])){ $trigger_name = $header_array["name"];}
	}

	$content = "";
	if( (isset($_GET['wplc_action']) & isset($_GET['trigger_id']))&& ($_GET['wplc_action'] == "delete_trigger" && $_GET['trigger_id'] != "")){
		
		$content .= "<form method='POST'>";
		$content .= 	"<table class=\"wp-list-table wplc_list_table widefat fixed form-table\" cellspacing=\"0\" style='width:50%'>";
		$content .= 		"<tr>";
		$content .= 			"<td>";
		$content .= 				__("Are you sure you would like to delete trigger") . ": <strong>" . esc_html($trigger_name) . "</strong>";
		$content .= 			"</td>";
		$content .= 		"</tr>";
		$content .= 		"<tr>";
		$content .= 			"<td>";
		$content .= 				"<input type='submit' class='button' name='delete_confirm' value='".__("Delete", 'wp-live-chat-support')."'>";
		$content .= 				" <a href='".admin_url()."admin.php?page=wplivechat-menu-triggers' class='button'>".__("Cancel", 'wp-live-chat-support')."</a>";
		$content .= 			"</td>";
		$content .= 		"</tr>";
	  	$content .= 	"</table>";
	  	$content .= "<input name='wplc_trigger_nonce' type='hidden' value='" . $wplc_trigger_nonce ."' >";
	  	$content .= "</form>";
	}
    
    echo $content;
}

add_filter("wplc_filter_hovercard_content","wplc_filter_control_modern_theme_hovercard_content_triggers_mrg",10,1);
function wplc_filter_control_modern_theme_hovercard_content_triggers_mrg($msg) {
  $unaltered_msg = $msg;
  if (!empty($_SERVER['HTTP_REFERER'])) {
    $post_id = url_to_postid($_SERVER['HTTP_REFERER']); //User Admin-Ajax Referrer
    $matched_trigger = wplc_check_trigger_filters_mrg($post_id); //Find first matching trigger for page
    if ($matched_trigger != false) {
      $trigger_data = wplc_get_trigger_mrg($matched_trigger);
      if ($trigger_data !== false && is_array($trigger_data)) {
        $trigger_data = $trigger_data[0];
        if (intval($trigger_data->show_content) == 1) {
          $unserialized_content = maybe_unserialize($trigger_data->content);
          if ($unserialized_content) {
            $msg = do_shortcode($unserialized_content['html'], true);
          }
        }
      }
    } else {
      $msg = $unaltered_msg;
    }
  }
  return $msg;
}

function wplc_check_trigger_filters_mrg($page){
	$triggers = wplc_get_active_triggers_mrg();
	$match_found = false; //Break control
	if ( $triggers ) {
		for($i = 0; ($i < count($triggers) && $match_found == false); $i++){
			if (isset($triggers[$i])) {
				$serialized_content = maybe_unserialize($triggers[$i]->content);
				if($serialized_content){
					if(isset($serialized_content['pages']) && $serialized_content['pages'] != ""){
						$pages_array = explode(",", trim($serialized_content['pages'])); //Trim pages and explode	
						if(is_array($pages_array)){
							foreach($pages_array as $trigger_page){
								if($match_found == false){
									//Only continue if match not found
									if(intval($trigger_page) == intval($page)){
										//They match
										$match_found = $triggers[$i]->id;
									}
								}
							}
						}
					} else {
						//Assume all pages
						$match_found = $triggers[$i]->id;
					}	
				}
			}
		}
	}
	return $match_found;
}

function wplc_tirggers_enqueue_user_styles_scripts($trigger_id){
	wp_register_script("wplc_trigger_js",  plugins_url('/js/wplc_user_triggers.js', __FILE__), array('jquery'), WPLC_PLUGIN_VERSION, true);
	/*Localize vars here*/
	$trigger_id = intval($trigger_id);

	$trigger_data = wplc_get_trigger_mrg($trigger_id);
	if($trigger_data !== false && is_array($trigger_data)){
		$trigger_data = $trigger_data[0];
		//Only Load styles if content is being overriden
		if(intval($trigger_data->show_content) == 1){
			wp_enqueue_style("wplc_trigger_styles",  plugins_url('/css/wplc_trigger_styles.css', __FILE__), array(), WPLC_PLUGIN_VERSION);
		}

		if($trigger_data){
			$unserialized_content = maybe_unserialize($trigger_data->content);

			$localized_array = array();
			$localized_array["type"] = $trigger_data->type;
			if(isset($unserialized_content['secs'])){
				$localized_array["secs"] = intval($unserialized_content['secs']);
			}
			if(isset($unserialized_content['perc'])){
				$localized_array["perc"] = intval($unserialized_content['perc']);
			}

			wp_localize_script("wplc_trigger_js", "wplc_trigger_data", $localized_array);
		}
		wp_enqueue_script("wplc_trigger_js");
	}
}

function wplc_trigger_get_allowed_tags_mrg(){
	$tags = wp_kses_allowed_html("post");
	$tags['iframe'] = array(
	    	'src'    		  => true,
	        'width'  		  => true,
	        'height' 		  => true,
	        'align'  		  => true,
	        'class'  		  => true,
	        'style'    		  => true,
	        'name'   		  => true,
	        'id'     		  => true,
	        'frameborder' 	  => true,
	        'seamless'    	  => true,
	        'srcdoc'      	  => true,
	        'sandbox'     	  => true,
	        'allowfullscreen' => true
		);
	$tags['input'] = array(
	    	'type'    		  => true,
	        'value'  		  => true,
	        'placeholder' 	  => true,
	        'class'  		  => true,
	        'style'    		  => true,
	        'name'   		  => true,
	        'id'     		  => true,
	        'checked' 	      => true,
	        'readonly'    	  => true,
	        'disabled'        => true,
	        'enabled'     	  => true
		);
	$tags['select'] = array(
	    	'value'    		  => true,
	        'class'  		  => true,
	        'style'    		  => true,
	        'name'   		  => true,
	        'id'     		  => true
		);
	$tags['option'] = array(
	    	'value'    		  => true,
	        'class'  		  => true,
	        'style'    		  => true,
	        'name'   		  => true,
	        'id'     		  => true,
	        'selected' 	      => true
		);
	return $tags;
}

function wplc_triggers_check_for_conflicts_mrg($triggers){
	$all_page_array = array();
	$tmp_page_array = array();
	if(!is_bool($triggers)){
		if(count($triggers) > 1){
			foreach($triggers as $trigger){
				if(isset($trigger->content)){
					$unserialized_content = maybe_unserialize($trigger->content);
					if(isset($unserialized_content['pages'])){
						if($unserialized_content['pages'] != ""){
							$pages_array = explode(",", trim($unserialized_content['pages'])); //Trim pages and explode	
							if(is_array($pages_array)){
								foreach($pages_array as $page){
									$imploded_pages = implode(",", $all_page_array);
									$check = strpos($imploded_pages, $page);
									if(is_bool($check) && $check == false){
										$all_page_array[count($all_page_array)] = intval($page);
									}else{
										//Conflict
										$tmp_page_array[count($tmp_page_array)] = intval($trigger->id);
									}			
								}
							}
						} else{
							//Assume All pages- Conflict
							$tmp_page_array[count($tmp_page_array)] = intval($trigger->id);
						}
					}
				}
			}
		}
	}
	return $tmp_page_array;

}
