<?php 

/*
 * Handles WPLC department functionality
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wplc_tblname_chat_departments;
$wplc_tblname_chat_departments = $wpdb->prefix . "wplc_departments";

/**
 * Updates/Creates the required tables in order to use roi in WPLC
 *
 * @return void
*/
add_action("wplc_pro_update_db_hook", "wplc_mrg_update_db_department", 10);
function wplc_mrg_update_db_department(){
	global $wpdb;
	global $wplc_tblname_chat_departments;
	global $wplc_tblname_chats;

	$wplc_department_sql = "
        CREATE TABLE " . $wplc_tblname_chat_departments . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name varchar(700) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

    dbDelta($wplc_department_sql);
	


    $department_field_sql = " SHOW COLUMNS FROM $wplc_tblname_chats WHERE `Field` = 'department_id'";
    $results = $wpdb->get_results($department_field_sql);
    if (!$results) {
        $department_field_sql = "
            ALTER TABLE `$wplc_tblname_chats` ADD `department_id` INT(11) NOT NULL ;
        ";
        $wpdb->query($department_field_sql);
    }
}

add_filter("wplc_pro_agent_list_before_button_filter", "wplc_mrg_agent_list_department_span", 10, 2);
/**
 * Adds current users assigned department to the agent information card
 *
 * @return string (html)
*/
function wplc_mrg_agent_list_department_span($content, $user){
	$user_department = get_user_meta($user->ID, "wplc_user_department", true);
	if($user_department){
		$selected_department = wplc_get_department_mrg(intval($user_department));
		if($selected_department){
			$content .= "<small style='height:30px'>" . esc_html($selected_department[0]->name) . "</small>";
		} else {
			$content .= "<small style='height:30px'>" . __("No Department", 'wp-live-chat-support') . "</small>";
		}
	} else {
		//No department set
		$content .= "<small style='height:30px'>" . __("No Department", 'wp-live-chat-support') . "</small>";
	}
 	return $content;
}

add_action("wplc_pro_custom_user_profile_field_after_content_hook", "wplc_mrg_department_user_profile_departments", 10, 1);
/**
 * Outputs department options to the user profile area
 *
 * @return void
*/
function wplc_mrg_department_user_profile_departments($user){
  if (wplc_user_is_agent($user->ID)) {
    $selected_department = intval(get_user_meta($user->ID, "wplc_user_department", true)); 
    if (current_user_can('manage_options', array(null))) {
?>
  <table class="form-table">
  <tr>
    <th>
      <label for="wplc_user_department"><?php _e('Chat Department', 'wp-live-chat-support'); ?></label>
    </th>
    <td>
      <select id="wplc_user_department" name="wplc_user_department"> 
        <option value="-1"><?php _e("No Department", 'wp-live-chat-support'); ?></option>
<?php
  $departments = wplc_get_all_deparments_mrg();
  if ($departments) {
    foreach($departments as $dep) {
  ?>
  <option value="<?php echo $dep->id;?>" <?php echo ($selected_department === intval($dep->id) ? "SELECTED" : "" ); ?> ><?php echo sanitize_text_field($dep->name); ?></option>
  <?php 
    }
  }
  ?>
      </select>
    </td>
  </tr>
  </table>
  <?php
    }
  }
}

add_action("wplc_pro_set_user_hook", "wplc_mrg_department_save", 10, 1);
/**
 * Handles the saving of user department data
 *
*/
function wplc_mrg_department_save($user_id){
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

    if(isset($_POST['wplc_user_department'])){
        update_user_meta( $user_id, 'wplc_user_department', intval($_POST['wplc_user_department']));
    } 
    
}

/**
 * Adds a menu item to WPLC for the roi Goal Area
 *
 * @return void
*/
add_action("wplc_hook_menu_mid","wplc_mrg_department_menu",10,1);
function wplc_mrg_department_menu($cap){
	add_submenu_page('wplivechat-menu', __('Departments', 'wp-live-chat-support'), __('Departments', 'edit_posts').' ('.__('beta').')', $cap[1], 'wplivechat-menu-departments', 'wplc_mrg_departments_page');
}

/**
 * Outputs (echo) the departmens page
 * 
 * @return void
*/
function wplc_mrg_departments_page(){

	wplc_enqueue_admin_styles_mrg();
	$wplc_add_department_btn = isset($_GET['wplc_action']) ? "" : "<a href='?page=wplivechat-menu-departments&wplc_action=add_department' class='wplc_add_new_btn'>". __("Add New", 'wp-livechat') ."</a>";

	$wplc_department_settings_btn = "<a href='?page=wplivechat-menu-settings#tabs-departments' class='wplc_add_new_btn'>". __("Department Settings", 'wp-livechat') ."</a>";

	$content = "<div class='wrap wplc_wrap'>";
    $content .= "<h2>".__('Departments', 'wp-livechat'). $wplc_add_department_btn . " " . $wplc_department_settings_btn . "</h2>";

   	if(isset($_GET['wplc_action']) && ($_GET['wplc_action'] == "add_department" || $_GET['wplc_action'] == "edit_department")){
		$content .= wplc_mrg_get_add_department_content();
    } else if(isset($_GET['wplc_action']) && ($_GET['wplc_action'] == "delete_department")){
    	$content .= wplc_mrg_delete_department_content();
    } else {
    	$content .= wplc_mrg_get_departments_table(); 	
    }

    $content .= "</div>"; //Close Wrap
 
    echo $content;
}


/**
 * Returns the department table
 *
 * @return string (html)
*/
function wplc_mrg_get_departments_table(){
	$content = "";

  	$results = wplc_get_all_deparments_mrg();


	$content .= "<table class=\"wp-list-table wplc_list_table widefat fixed \" cellspacing=\"0\" style='width:98%'>";
	$content .= 	"<thead>";
  	$content .= 		"<tr>";
    $content .= 			"<th scope='col'><span>" . __("ID", 'wp-live-chat-support') . "</span></th>";
    $content .= 			"<th scope='col'><span>" . __("Name", 'wp-live-chat-support') . "</span></th>";
    $content .= 			"<th scope='col'><span>" . __("Action", 'wp-live-chat-support') . "</span></th>";
    $content .= 		"</tr>";
  	$content .= 	"</thead>";

  	
  	if($results){
  		foreach ($results as $result) {
  			$department_actions = "<a class='button' href='?page=wplivechat-menu-departments&wplc_action=edit_department&department_id=".$result->id."'>".__("Edit", "wp-livechat")."</a> ";
  			$department_actions .= "<a class='button' href='?page=wplivechat-menu-departments&wplc_action=delete_department&department_id=".$result->id."'>".__("Delete", "wp-livechat")."</a> ";

  			$content .= "<tr>";
  			$content .= 	"<td>".$result->id."</td>";
  			$content .= 	"<td>".esc_html($result->name)."</td>";
  			$content .= 	"<td>".$department_actions."</td>";
  			$content .= "</tr>";
  			
  		}
  	} else {
  		$content .= "<tr><td>".__("No Departments Found...", "wp-livechat")."</td><td></td><td></td></tr>";
  	}

  	$content .= 	"</table>";
	
	return $content;
}

/** 
 * Return all departments from database
 *
 * @return object 
*/
function wplc_get_all_deparments_mrg(){
	global $wpdb;
    global $wplc_tblname_chat_departments;
    
    $sql = "SELECT * FROM $wplc_tblname_chat_departments"; 

    $results =  $wpdb->get_results($sql);
    if($wpdb->num_rows){
    	return $results;
    } else {
    	return false;
    } 
}

/**
 * Create the 'Add new' or 'Edit' Department page
 *
 * @return string (html)
*/
function wplc_mrg_get_add_department_content(){
	$wplc_dept_nonce = wp_create_nonce('wplc_dept_nonce');
	$content = "";

	//Content Vars
	$department_name = "";

	$header_array = wplc_mrg_department_admin_head();
	
	if($header_array){
		if(isset($header_array['wplc_department_name'])){ $department_name = $header_array['wplc_department_name']; }
	}

	$wplc_submit_label = (isset($_GET['wplc_action']) && $_GET['wplc_action'] !== "edit_department" ? "Create Department" : "Edit Department"); //Default

	$content .= "<form method='POST'>";
	$content .= "<table class=\"wp-list-table wplc_list_table widefat fixed form-table\" cellspacing=\"0\" style='width:50%'>";

  	$content .= 	"<tr>";
    $content .= 		"<td>".__("Department Name", "wp-livechat").":</td>";
    $content .= 		"<td><input type='text' name='wplc_department_name' value='$department_name'></td>";
    $content .= 	"</tr>";

    $content .= 	"<tr>";
    $content .= 		"<td></td>";
    $content .=         "<td><input class='button button-primary' type='submit' name='wplc_department_submit' value='".__($wplc_submit_label, "wp-livechat")."'> <a href='".admin_url()."admin.php?page=wplivechat-menu-departments"."' class='button'>".__("Cancel", "wp-livechat")."</a></td>";
    $content .= 	"</tr>";

	$content .= "</table>";
	$content .= "<input name='wplc_dept_nonce' type='hidden' value='" . $wplc_dept_nonce . "' >";
	$content .= "</form>";

	if($header_array){
		if(count($header_array["errors"]) >= 1){
			$content .= "<div class='update-nag'>";
			$content .= "<strong>".__("Please review your submission", "wp-livechat").":</strong>";
			$content .= 	"<ul style='list-style:initial;'>";
			for($i = 0; $i < count($header_array["errors"]); $i++){
				$content .= 	"<li style='margin-left: 25px;'>".__($header_array["errors"][$i], "wp-livechat")."</li>";
			}
			$content .= 	"</ul>";
			$content .= "</div>";
		}

		if(isset($header_array["success"])){
			$content .= "<div class='update-nag' style='border-color:#67d552;'>";
			$content .= "<strong>".__($header_array["success"], "wp-livechat")."</strong>";
			$content .= "</div>";
		}
	}
	

	return $content;
}


/**
 * Handles all the head stuff
 * @return array
*/
function wplc_mrg_department_admin_head(){
	if(isset($_GET['wplc_action'])){
		$return_array = array();
		$form_valid = true;
		if(isset($_POST['wplc_department_submit'])){
			$return_array["errors"] = array();
			if(isset($_POST['wplc_department_name']) && $_POST['wplc_department_name'] !== ""){
				$return_array["wplc_department_name"] = sanitize_text_field($_POST['wplc_department_name']);
			} else {
				$return_array["errors"][count($return_array["errors"]) >= 1 ? count($return_array["errors"]) : 0] = "Name cannot be empty";
				$form_valid = false; //No Longer Valid
			}
		}

		if($_GET['wplc_action'] == "add_department"){
				if($form_valid && isset($_POST['wplc_department_submit'])){
					if (!isset($_POST['wplc_dept_nonce']) || !wp_verify_nonce($_POST['wplc_dept_nonce'], 'wplc_dept_nonce')){
			              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
			        }

					//All good continue
					if(wplc_add_department_mrg($return_array)){
						//Redirect here
						echo "<script> window.location = '".admin_url()."admin.php?page=wplivechat-menu-departments"."';</script>";
					}
				} else {
					return $return_array; //Return Posted Data
				}
		} else if ($_GET['wplc_action'] == "edit_department"){
			//Editing now
			$edit_array = array();
			$edit_array["errors"] = array();
			if (isset($return_array["errors"])) { $edit_array["errors"] = $return_array["errors"];  }

			//Submit data first
			if($form_valid && isset($_POST['wplc_department_submit'])){
				if (!isset($_POST['wplc_dept_nonce']) || !wp_verify_nonce($_POST['wplc_dept_nonce'], 'wplc_dept_nonce')){
		              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
		        }

				//All good continue
				if(isset($_GET['department_id'])){
					if(wplc_edit_department_mrg($return_array, intval($_GET['department_id']))){
						//Show edit message
						$edit_array['success'] = "<div>".__("Department has been edited.", "wp-livechat")."</div>";
					}
				} else {
					$edit_array["errors"][count($edit_array["errors"]) >= 1 ? count($edit_array["errors"]) : 0] = "Department ID not found";
				}
			}

			$data = wplc_get_department_mrg(intval($_GET['department_id']));
			if($data){
				if($data !== false && is_array($data)){					
					//Got the data
					if(isset($data[0]->name) && $data[0]->name !== ""){ $edit_array["wplc_department_name"] = esc_html($data[0]->name); }
				}
			} else{
				$edit_array["errors"][count($edit_array["errors"]) >= 1 ? count($edit_array["errors"]) : 0] = "Department ID not found";
			}

			return $edit_array; //Return Server Data
		}else if($_GET['wplc_action'] == "delete_department"){
			$delete_array = array();
			if(isset($_GET['department_id'])){
				$data = wplc_get_department_mrg(intval($_GET['department_id']));
				if($data){
					$delete_array["name"] = $data[0]->name;
				}

				if(isset($_POST['delete_confirm'])){
					if (!isset($_POST['wplc_dept_nonce']) || !wp_verify_nonce($_POST['wplc_dept_nonce'], 'wplc_dept_nonce')){
			              wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
			        }
					//Delete now
					if(wplc_delete_department_mrg(intval($_GET['department_id']))){
						//Success
					}
					echo "<script> window.location = '".admin_url()."admin.php?page=wplivechat-menu-departments"."';</script>";
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

/**
 * Adds a new Department
 *
 * @return boolean
*/
function wplc_add_department_mrg($data){
	global $wpdb;
    global $wplc_tblname_chat_departments;
	if($data){
		//Validation - 1
		if($data['wplc_department_name'] != ""){ $data_name = $data['wplc_department_name']; } else { return false; }

		//Validation - 2 
		$data_name = sanitize_text_field($data_name);

		$sql = "INSERT INTO $wplc_tblname_chat_departments SET `name` = '%s' ";
		$sql = $wpdb->prepare($sql, $data_name);
       	$wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        } 
	}
}

/**
 * Edit a Department
 *
 * @return boolean
*/
function wplc_edit_department_mrg($data, $id){
	global $wpdb;
    global $wplc_tblname_chat_departments;
	if($data){

		//Validation - 1
		if($data['wplc_department_name'] != ""){ $department_name = $data['wplc_department_name']; } else { return false; }
		
		//Validation - 2 
		$department_name = sanitize_text_field($department_name);

		$id = intval($id);
		$sql = "UPDATE $wplc_tblname_chat_departments SET `name` = '%s' WHERE `id` = '%d' ";
		$sql = $wpdb->prepare($sql, $department_name, $id);
       	$wpdb->query($sql);
        if ($wpdb->last_error) { 
            return false;  
        } else {
            return true;
        } 
	}
}

/**
 * Retrieved one department
*/
function wplc_get_department_mrg($id){
	global $wpdb;
    global $wplc_tblname_chat_departments;
    
    $id = intval($id);

    $sql = "SELECT * FROM $wplc_tblname_chat_departments WHERE `id` = '%d'"; 
    $sql = $wpdb->prepare($sql, $id);
    $results =  $wpdb->get_results($sql);
    if($wpdb->num_rows){
    	return $results;
    } else {
    	return false;
    }
}

/**
 * Returns confirmation form prior to deleting a department
 *
 * @return string (html)
*/
function wplc_mrg_delete_department_content(){
	$wplc_dept_nonce = wp_create_nonce('wplc_dept_nonce');
	$header_array = wplc_mrg_department_admin_head();
	$department_name = "";
	if($header_array){
		if(isset($header_array["name"])){ $department_name = $header_array["name"];}
	}

	$content = "";
	if( (isset($_GET['wplc_action']) & isset($_GET['department_id'])) && ($_GET['wplc_action'] == "delete_department" && $_GET['department_id'] != "")){
		
		$content .= "<form method='POST'>";
		$content .= 	"<table class=\"wp-list-table wplc_list_table widefat fixed form-table\" cellspacing=\"0\" style='width:50%'>";
		$content .= 		"<tr>";
		$content .= 			"<td>";
		$content .= 				__("Are you sure you would like to delete department") . ": <strong>" . esc_html($department_name) . "</strong>";
		$content .= 			"</td>";
		$content .= 		"</tr>";
		$content .= 		"<tr>";
		$content .= 			"<td>";
		$content .= 				"<input type='submit' class='button' name='delete_confirm' value='".__("Delete", "wp-livechat")."'>";
		$content .= 				" <a href='".admin_url()."admin.php?page=wplivechat-menu-departments' class='button'>".__("Cancel", "wp-livechat")."</a>";
		$content .= 			"</td>";
		$content .= 		"</tr>";
	  	$content .= 	"</table>";
	  	$content .= "<input name='wplc_dept_nonce' type='hidden' value='" . $wplc_dept_nonce . "' >";
	  	$content .= "</form>";
	}
    
    return $content;
}

/**
 * Removes a Department
 *
 * @return boolean
*/
function wplc_delete_department_mrg($id){
	global $wpdb;
	global $wplc_tblname_chat_departments;
	$id = intval($id);
	$sql = "DELETE FROM $wplc_tblname_chat_departments WHERE `id` = '%d' ";
	$sql = $wpdb->prepare($sql, $id);
   	$wpdb->query($sql);
    if ($wpdb->last_error) { 
        return false;  
    } else {
		$users = get_users(array('meta_key' => 'wplc_user_department', 'meta_value' => $id));
		if($users){
			foreach($users as $user) {
				delete_user_meta( $user->ID, 'wplc_user_department' );
			}
		}
        return true;
    } 
}

add_filter("wplc_filter_setting_tabs","wplc_mrg_department_settings_tab_heading");
/**
 * Creates settings area tab
 * 
 * @return string (html)
*/
function wplc_mrg_department_settings_tab_heading($tab_array) {
    $tab_array['department'] = array(
      "href" => "#tabs-departments",
      "icon" => 'fa fa-university',
      "label" => __("Departments",'wp-live-chat-support')
    );
    return $tab_array;
}

add_action("wplc_hook_settings_page_more_tabs","wplc_mrg_department_settings_tab_content");
/**
 * Creates settings area content
 * 
 * @return string (html)
*/
function wplc_mrg_department_settings_tab_content() {
    $wplc_settings = wplc_get_options();
    $selected_department = intval($wplc_settings['wplc_default_department']);
    ?>
	<div id="tabs-departments">
		<h3><?php _e("Departments", 'wp-live-chat-support') ?></h3>
		<table class="wp-list-table wplc_list_table widefat fixed striped pages">
			<tbody>
				<tr>
					<td width='300'>
						<?php _e("Default Department", 'wp-live-chat-support') ?> 
						<i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Default department a new chat is assigned to", 'wp-live-chat-support'); ?>"></i>
					</td>
					<td>
						<select id="wplc_default_department" name="wplc_default_department"> 
	                    	<option value="-1"><?php _e("No Department", 'wp-live-chat-support'); ?></option>
	                    	<?php
	                    	$departments = wplc_get_all_deparments_mrg();
	                    	if($departments){
	                    		foreach($departments as $dep){
	                    	?>
	                    		<option value="<?php echo $dep->id;?>" <?php echo ($selected_department == intval($dep->id) ? "SELECTED" : "" ); ?> ><?php echo sanitize_text_field($dep->name); ?></option>
	                    	<?php 
	                    		}
	                    	}
	                    	?>
	                    </select> <a href="<?php echo admin_url('admin.php?page=wplivechat-menu-departments'); ?>" class="button button-secondary" title="<?php __('Create or Edit Departments')  ?>"><i class="fas fa-pencil-alt wplc_light_grey"></i></a>
					</td>
				</tr>

				<tr>
					<td width='300'>
						<?php _e("User Department Selection", 'wp-live-chat-support') ?> 
						<i class="fa fa-question-circle wplc_light_grey wplc_settings_tooltip" title="<?php _e("Allow user to select a department before starting a chat?", 'wp-live-chat-support'); ?>"></i>
					</td>
					<td>
						<input type="checkbox" name="wplc_allow_department_selection" id="wplc_allow_department_selection" value="1" <?php echo(boolval($wplc_settings['wplc_allow_department_selection']) ? "CHECKED" : "") ?> />
					</td>
				</tr>

				<tr>
					<td width='300'>
						<?php _e("Note: Chats will be transferred in the event that agents are not available within the selected department", 'wp-live-chat-support') ?> 
					</td>
					<td>
						
					</td>
				</tr>

				<?php do_action("wplc_pro_departments_settings_inside_table_hook"); ?>

			</tbody>
		</table>
		<p><?php echo sprintf(__("Create departments %s.",'wp-live-chat-support'),'<a href="'.admin_url('admin.php?page=wplivechat-menu-departments').'">'.__('here','wp-live-chat-support').'</a>'); ?></p>
		<br>

		<?php do_action("wplc_pro_departments_settings_below_table_hook"); ?>

	</div>
	
	<?php

}


add_filter("wplc_alter_chat_list_sql_before_sorting", "wplc_mrg_add_department_sql", 10, 1);
/**
 * Adds department SQL to the admin chat list loop
 *
 * @param string $content 
 * @return string 
*/
function wplc_mrg_add_department_sql($content){
	$user_id = get_current_user_id();
	$user_department = get_user_meta($user_id ,"wplc_user_department", true);
	if($user_department && $user_department !== "" && $user_department !== "-1" && $user_department !== -1){
		$content .= " AND (`department_id` = '" . intval($user_department) . "'  || `department_id` = 0) ";
	} else {
		if(isset($_POST['wplc_department_view'])){
			$content .= " AND (`department_id` = '" . intval($_POST['wplc_department_view']) . "' || `department_id` = 0)  ";
		}
	}

	return $content;
}

add_action("wplc_after_chat_visitor_count_hook", "wplc_user_department_label_mrg");
/**
 * Outputs a span which shows the users department in the dashboard
 *
 * @return void
*/
function wplc_user_department_label_mrg(){
	$user_id = get_current_user_id();
	$user_department = get_user_meta($user_id ,"wplc_user_department", true);
	if($user_department && $user_department !== ""){
		if($user_department !== "-1"){
			$selected_department = wplc_get_department_mrg(intval($user_department));
			if($selected_department){
				echo "<span class='wplc_dashboard_additional_label' title='" . __("Department you have been assigned to as an agent", 'wp-live-chat-support') . "'><strong>".__("Department", 'wp-live-chat-support').":</strong> " . esc_html($selected_department[0]->name) . "</span>";
			}
		} else {
			wplc_dashboard_department_selector_mrg();
		}
	} else {
		wplc_dashboard_department_selector_mrg();
	}
}

/**
 * Outputs a selection box for selecting which department you would like to view
 *
 * @return void
*/
function wplc_dashboard_department_selector_mrg(){
	$content = "";
	$departments = wplc_get_all_deparments_mrg();
	$current_selection = isset($_GET['wplc_department_view']) ? $_GET['wplc_department_view'] : -1;
    if($departments){
		$content .= "<span class=\"wplc_dashboard_department_selector_outer\"><select id='wplc_dashboard_department_selector'>";
		$content .= "<option value='0' >" . __("No Department", 'wp-live-chat-support') . "</option>";
   		foreach($departments as $dep){
           // $content .= "<option value='" . $dep->id ."' " . (intval($default_department) === intval($dep->id) ? "SELECTED" : ""). ">" . $dep->name . "</option>";
            $content .= "<option value='" . $dep->id ."' ". (intval($current_selection) === intval($dep->id) ? "SELECTED" : ""). ">" . sanitize_text_field($dep->name) . "</option>";

        }
        $content .= "</select></span>";
    }

    echo $content;
    wplc_dashboard_department_selector_script_mrg();
}

/**
 * Outputs javascript to handle changes from the selection
 *
 * @return void
*/
function wplc_dashboard_department_selector_script_mrg(){
	?>
	<script>
	var wplc_selected_department_view = getQueryVariable("wplc_department_view");

	jQuery(function(){
			jQuery("body").on("change", "#wplc_dashboard_department_selector", function(){
				var base_url = "<?php echo admin_url('admin.php?page=wplivechat-menu')?>";
				window.location = base_url + (jQuery(this).val() !== "0" ? "&wplc_department_view=" + jQuery(this).val() : "");
			});
	});

	function wplc_pro_admin_long_poll_data(data){
		if(wplc_selected_department_view !== false){
			data['wplc_department_view'] = wplc_selected_department_view;
		}

		return data;
	}

	function getQueryVariable(variable){
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable){
            	return pair[1];
            }
       }
       return false;
	}
	</script>
	<?php
}


add_filter("wplc_log_user_on_page_insert_filter", "wplc_log_user_default_department_mrg", 10, 1);
/**
 * Sets the default department when a new chat session is added
 *
 * @param array $insert_data Data from the LogUserOnPage function
 * @return array
*/
function wplc_log_user_default_department_mrg($insert_data){
	$wplc_settings = wplc_get_options();
    $selected_department = isset($wplc_settings['wplc_default_department']) ? $wplc_settings['wplc_default_department'] : -1; 
    if($selected_department && $selected_department !== -1){
    	//Set the default department
    	$insert_data["department_id"] = $selected_department;
    }
	return $insert_data;
}

add_filter("wplc_start_chat_user_form_after_filter", "wplc_allow_department_selection_user_form_mrg", 10, 1);
/**
 * Adds a Department Selection Dropdown Box to the start chat form
 *
 * @param string $content 
 * @return string
*/
function wplc_allow_department_selection_user_form_mrg($content) {
	$wplc_settings = wplc_get_options();
 	$default_department = intval($wplc_settings['wplc_default_department']);

 	if ($wplc_settings['wplc_allow_department_selection'] && $default_department>=0) {
 		$departments = wplc_get_all_deparments_mrg();
	    if ($departments) {
 			$content .= "<select id='wplc_user_selected_department'>";
 			$content .= "<option value='0' >" . __("Select Department", 'wp-live-chat-support') . "</option>";
	   		foreach($departments as $dep){
	            $content .= "<option value='" . $dep->id ."' " . ($default_department == intval($dep->id) ? "SELECTED" : ""). ">" . esc_html($dep->name) . "</option>";
	        }
	        $content .= "</select>";
	    }
 	}
	return $content;
}


add_action("wplc_start_chat_hook_after_data_insert", "wplc_mrg_department_user_selected_department", 10, 1);
/**
 * Updates a chat record to the user selection 
 *
 * @param int $cid  Chat ID
 * @return void
*/
function wplc_mrg_department_user_selected_department($cid){
	if(isset($_POST['wplc_user_selected_department'])){
		wplc_mrg_department_update_department($cid, sanitize_text_field($_POST['wplc_user_selected_department']));
	} else {
		wplc_mrg_department_update_department($cid, null);
	}
}

/**
 * Updates a chat session with a new department id
 *
 * @param int $cid  Chat ID
 * @param int $department_id  Department ID
 * @return void
*/
function wplc_mrg_department_update_department($cid, $department_id){
	global $wpdb;
	global $wplc_tblname_chats;
	$cid = intval($cid);

	if($department_id !== null){
		$department_id = intval($department_id);
	} else {
		$chat_data = wplc_get_chat_data($cid,__LINE__);
		if(isset($chat_data->department_id)){
			$department_id = intval($chat_data->department_id);
		}
	}

	$original_department = $department_id;
	$department_id = apply_filters("wplc_pro_department_update_filter", $department_id, $cid); //Allows us to ensure this department is available

	if($original_department !== $department_id){
		if($cid && $department_id){
			$wpdb->update(
				$wplc_tblname_chats, 
		        array('department_id' => $department_id), 
		        array('id' => $cid), 
		        array('%d'), 
		        array('%d') 
			);
		}
	}
}