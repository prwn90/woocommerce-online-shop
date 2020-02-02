<?php

if (!defined('ABSPATH')) {
  exit;
}

global $wplc_webhook_table, $wplc_webhook_events;
$wplc_webhook_table = $wpdb->prefix . "wplc_webhooks";

//Use these when sending a payload 
$wplc_webhook_events = array(
  0 => __("Agent Login", 'wp-live-chat-support'),
  3 => __("New Visitor", 'wp-live-chat-support'),
  4 => __("Chat Request", 'wp-live-chat-support'),
  5 => __("Agent Accept", 'wp-live-chat-support'),
  6 => __("Settings Changed", 'wp-live-chat-support')
);

add_action("wplc_activate_hook", "wplc_webhook_db_setup", 10);
add_action("wplc_update_hook", "wplc_webhook_db_setup", 10);
/*
 * Updates/Creates the required tables in order to use devices on node
*/
function wplc_webhook_db_setup() {
  global $wplc_webhook_table;

  $wplc_webhooks_sql = "
        CREATE TABLE " . $wplc_webhook_table . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          url varchar(700) NULL,
          action int(11) NULL, 
          method varchar(70) NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

  dbDelta($wplc_webhooks_sql);
}

add_action("wplc_hook_menu_mid", "wplc_webhooks_manager_menu", 11, 1);
/*
 * Adds a Webhooks menu item to admin menu
*/
function wplc_webhooks_manager_menu($cap) {
  $wplc_settings = wplc_get_options();
  if ($wplc_settings['wplc_use_node_server']) {
    //Only add this menu item if user is using node. 
    if (current_user_can("manage_options")) {
      add_submenu_page('wplivechat-menu', __('Webhooks', 'wp-live-chat-support'), __('Webhooks', 'edit_posts'), $cap[0], 'wplivechat-menu-webhooks-page', 'wplc_webhooks_page');
    }
  }
}

/*
 * Draws the webhooks page
*/
function wplc_webhooks_page() {
  wplc_webhook_header_check();

  $add_webhook_btn = isset($_GET["wplc_action"]) && $_GET["wplc_action"] !== "delete_webhook_confirmed" ? "" : "<a href='?page=wplivechat-menu-webhooks-page&wplc_action=add_webhook' class='button button-secondary'>" . __("Add New", 'wp-livechat') . "</a>";

  $content = "<div class='wrap wplc_wrap'>";
  $content .= "<h2>" . __('Web Hooks', 'wp-live-chat-support') . " " . $add_webhook_btn . "</h2>";

  if (isset($_GET['wplc_action']) && $_GET['wplc_action'] === 'add_webhook') {
    $content .= wplc_webhooks_add_form();
  } else if (isset($_GET['wplc_action']) && $_GET['wplc_action'] === 'edit_webhook') {
    $content .= wplc_webhooks_add_form(true, $_GET['id']);
  } else if (isset($_GET['wplc_action']) && $_GET['wplc_action'] === 'delete_webhook') {
    $content .= wplc_webhooks_confirm_delete_prompt();
  } else {
    $content .= wplc_webhooks_table();
  }

  $content .= "</div>"; //Close Wrap


  echo $content;
}

function wplc_webhook_header_check() {
  if (isset($_POST['add_webhook'])) {
    //User is trying to add a webhook
    if (wplc_webhook_add_webhook()) {
      echo "<p class='notice notice-success' style='max-width:300px'>" . __("Webhook created", 'wp-live-chat-support') . "</p>";
    } else {
      echo "<p class='notice notice-error' style='max-width:300px'>" . __("Webhook could not be created", 'wp-live-chat-support') . "</p>";
    }
  }

  if (isset($_POST['edit_webhook'])) {
    //User is trying to edit a webhook
    if (wplc_webhook_edit_webhook()) {
      echo "<p class='notice notice-success' style='max-width:300px'>" . __("Webhook edited", 'wp-live-chat-support') . "</p>";
    } else {
      echo "<p class='notice notice-error' style='max-width:300px'>" . __("Webhook could not be edited", 'wp-live-chat-support') . "</p>";
    }
  }

  if (isset($_GET['wplc_action']) && $_GET['wplc_action'] === 'delete_webhook_confirmed') {
    if (!isset($_GET['wplc_webhook_nonce']) || !wp_verify_nonce($_GET['wplc_webhook_nonce'], 'wplc_webhook_nonce')) {
      wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
    }

    $webhook_id = isset($_GET['id']) ? intval($_GET['id']) : false;
    if ($webhook_id !== false) {
      if (wplc_webhook_delete_webhook($webhook_id)) {
        echo "<p class='notice notice-success' style='max-width:300px'>" . __("Webhook deleted", 'wp-live-chat-support') . "</p>";
      } else {
        echo "<p class='notice notice-error' style='max-width:300px'>" . __("Webhook could not be delete", 'wp-live-chat-support') . "</p>";
      }
    }
  }
}

function wplc_webhook_add_webhook() {
  global $wpdb;
  global $wplc_webhook_table;

  if (isset($_POST['add_webhook_event'])) {
    if (!isset($_POST['wplc_webhook_nonce']) || !wp_verify_nonce($_POST['wplc_webhook_nonce'], 'wplc_webhook_nonce')) {
      wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
    }

    $event = intval($_POST['add_webhook_event']);
    $action_url = sanitize_text_field($_POST['add_webhook_domain']);
    $method = isset($_POST['add_webhook_method']) ? sanitize_text_field($_POST['add_webhook_method']) : "GET";

    $sql = "INSERT INTO $wplc_webhook_table SET `url` = '%s', `action` = '%s', `method` = '%s' ";
    $sql = $wpdb->prepare($sql, $action_url, $event, $method);
    $wpdb->query($sql);
    if ($wpdb->last_error) {
      return false;
    } else {
      return true;
    }
  } else {
    return false;
  }
}

function wplc_webhook_edit_webhook() {
  global $wpdb;
  global $wplc_webhook_table;

  $webhook_id = isset($_POST['edit_webhook_id']) ? intval($_POST['edit_webhook_id']) : false;
  if (isset($_POST['add_webhook_event']) && $webhook_id !== false) {
    if (!isset($_POST['wplc_webhook_nonce']) || !wp_verify_nonce($_POST['wplc_webhook_nonce'], 'wplc_webhook_nonce')) {
      wp_die(__("You do not have permission do perform this action", 'wp-live-chat-support'));
    }

    $event = intval($_POST['add_webhook_event']);
    $action_url = sanitize_text_field($_POST['add_webhook_domain']);
    $method = isset($_POST['add_webhook_method']) ? sanitize_text_field($_POST['add_webhook_method']) : "GET";

    $sql = "UPDATE $wplc_webhook_table SET `url` = '%s', `action` = '%s', `method` = '%s' WHERE `id` = '%d' LIMIT 1";
    $sql = $wpdb->prepare($sql, $action_url, $event, $method, $webhook_id);
    $wpdb->query($sql);
    if ($wpdb->last_error) {
      return false;
    } else {
      return true;
    }
  } else {
    return false;
  }
}

function wplc_webhook_delete_webhook($webhook_id = false) {
  global $wpdb;
  global $wplc_webhook_table;

  if ($webhook_id !== false) {
    $sql = "DELETE FROM $wplc_webhook_table WHERE `id` = '%d' LIMIT 1";
    $sql = $wpdb->prepare($sql, $webhook_id);
    $wpdb->query($sql);
    if ($wpdb->last_error) {
      return false;
    } else {
      return true;
    }
  } else {
    return false;
  }
}

/*
 * Return all webhooks from database
*/
function wplc_webhooks_get_all_db($where = false, $id = false) {
  global $wpdb;
  global $wplc_webhook_table;

  $where = ($where === false) ? "" : $where;

  $sql = "SELECT * FROM $wplc_webhook_table ";

  if ($where !== "") {
    $results =  $wpdb->get_results($wpdb->prepare($sql . $where, intval($id)));
  } else {
    $results =  $wpdb->get_results($sql);
  }

  if ($wpdb->num_rows) {
    return $results;
  } else {
    return false;
  }
}

/*
 * Return specific webhook data
*/
function wplc_webhook_get_data($webhook_id) {
  $results = wplc_webhooks_get_all_db("WHERE `id` = %d", $webhook_id);
  if ($results !== false) {
    return $results[0];
  } else {
    return false;
  }
}

/*
 * Return all matched webhook
*/
function wplc_webhook_get_matched_hooks($event_code) {
  $results = wplc_webhooks_get_all_db("WHERE `action` = %d", $event_code);
  if ($results !== false) {
    return $results;
  } else {
    return false;
  }
}

/*
 * Renders webhooks table
*/
function wplc_webhooks_table() {
  global $wpdb, $wplc_webhook_table, $wplc_webhook_events;
  $webhooks = wplc_webhooks_get_all_db();

  $content = "";
  if ($webhooks !== false) {

    $content .= "<table id='webhooks' class='wp-list-table wplc_list_table widefat' cellspacing='0' width='100%'>";
    $content .=     "<thead>";
    $content .=         "<tr>";
    $content .=             "<th scope='col' id='event'  style='width:30%;'><span>" . __("Event", 'wp-live-chat-support') . "</span></th>";
    $content .=             "<th scope='col' id='target'  style='width:30%;'><span>" . __("Target URL", 'wp-live-chat-support') . "</span></th>";
    $content .=             "<th scope='col' id='method'  style='width:10%;'><span>" . __("Method", 'wp-live-chat-support') . "</span></th>";
    $content .=             "<th scope='col' id='action'><span>" . __("Action", 'wp-live-chat-support') . "</span></th>";
    $content .=         "</tr>";
    $content .=     "</thead>";
    $content .=     "<tbody>";

    foreach ($webhooks as $key => $webhook) {
      $content .= "<tr>";
      $content .=     "<td>" . $wplc_webhook_events[$webhook->action] . "</td>";
      $content .=     "<td>" . $webhook->url . "</td>";
      $content .=     "<td>" . $webhook->method . "</td>";
      $content .=     "<td>";
      $content .=         "<a href='?page=wplivechat-menu-webhooks-page&wplc_action=edit_webhook&id=" . $webhook->id . "' title='" . __("Edit", 'wp-live-chat-support') . "' class='button button-secondary'>" . __("Edit", 'wp-live-chat-support') . "</a> ";
      $content .=         "<a href='?page=wplivechat-menu-webhooks-page&wplc_action=delete_webhook&id=" . $webhook->id . "' title='" . __("Delete", 'wp-live-chat-support') . "' class='button button-secondary'>" . __("Delete", 'wp-live-chat-support') . "</a>";
      $content .=     "</td>";
      $content .= "</tr>";
    }

    $content .=     "</tbody>";
    $content .= "</table>";
  } else {
    $content .= "<p class='notice notice-error' style='max-width:300px'>" . __("No Webhooks", 'wp-live-chat-support') . "</p>";
  }

  return $content;
}


/*
 * Renders webhooks add webhook form
*/
function wplc_webhooks_add_form($force_edit = false, $webhook_id = false) {
  $event_value = 0;
  $url_value = "";
  $method_value = "GET";

  $wplc_webhook_nonce = wp_create_nonce('wplc_webhook_nonce');

  if ($force_edit && $webhook_id !== false) {
    $webhook_id = intval($webhook_id);
    $webhook_data = wplc_webhook_get_data($webhook_id);
    if ($webhook_data !== false) {
      $event_value = $webhook_data->action;
      $url_value = $webhook_data->url;
      $method_value = $webhook_data->method;
    }
  }

  $content = "<form method='POST' action='?page=wplivechat-menu-webhooks-page'>";
  $content .=     "<table class='wp-list-table wplc_list_table widefat striped'>";
  $content .=         "<tr>";
  $content .=             "<td>" . __("Event", 'wp-live-chat-support') . "</td>";
  $content .=             "<td>" . wplc_webhook_render_event_selection_dropdown("add_webhook_event", $event_value, 'width:200px') . "</td>";
  $content .=         "</tr>";

  $content .=         "<tr>";
  $content .=             "<td>" . __("Target URL", 'wp-live-chat-support') . "</td>";
  $content .=             "<td><input placeholder='http://example.com/webhook_handler' name='add_webhook_domain' value='" . $url_value . "' type='text' style='width:500px'></td>";
  $content .=         "</tr>";

  $content .=         "<tr>";
  $content .=             "<td>" . __("Method", 'wp-live-chat-support') . "</td>";
  $content .=             "<td>";
  $content .=                 "<select id='add_webhook_method' name='add_webhook_method' style='width:200px' value='" . $method_value . "'>";
  $content .=                     "<option value='GET' " . ($method_value === "GET" ? "selected" : "") . ">" . __("GET", 'wp-live-chat-support') . "</option>";
  $content .=                     "<option value='POST' " . ($method_value === "POST" ? "selected" : "") . ">" . __("POST", 'wp-live-chat-support') . "</option>";
  $content .=                 "</select>";
  $content .=             "</td>";
  $content .=         "</tr>";

  $content .=         "<tr>";
  $content .=             "<td><input type='submit' name='" . ($force_edit ? "edit_webhook" : "add_webhook") . "' value='" . ($force_edit ?  __("Save Changes", 'wp-live-chat-support') : __("Add New", 'wp-live-chat-support')) . "' class='button button-primary'></td>";
  $content .=             "<td>" . ($force_edit ? "<input type='hidden' name='edit_webhook_id' value='" . $webhook_id . "' >" : "") . "</td>";
  $content .=         "</tr>";

  $content .=     "</table>";
  $content .= "<input name='wplc_webhook_nonce' type='hidden' value='" . $wplc_webhook_nonce . "' >";
  $content .= "</form>";

  return $content;
}

function wplc_webhooks_confirm_delete_prompt() {
  $id = intval($_GET['id']);
  $wplc_webhook_nonce = wp_create_nonce('wplc_webhook_nonce');
  $content = "<table class='wp-list-table wplc_list_table widefat striped' style='max-width:350px'>";
  $content .=     "<tr>";
  $content .=         "<td><strong>" . __("Are you sure you want to delete this webhook?", 'wp-live-chat-support') . "</strong></td>";
  $content .=      "</tr>";
  $content .=     "<tr>";
  $content .=         "<td>";
  $content .=             "<a href='?page=wplivechat-menu-webhooks-page&wplc_action=delete_webhook_confirmed&id=" . $id . "&wplc_webhook_nonce=" . $wplc_webhook_nonce . "' title='" . __("Confirm", 'wp-live-chat-support') . "' class='button button-primary'>" . __("Confirm", 'wp-live-chat-support') . "</a> ";
  $content .=             "<a href='?page=wplivechat-menu-webhooks-page' title='" . __("Cancel", 'wp-live-chat-support') . "' class='button button-secondary'>" . __("Cancel", 'wp-live-chat-support') . "</a> ";
  $content .=         "</td>";
  $content .=      "</tr>";
  $content .= "</table>";
  return $content;
}

function wplc_webhook_render_event_selection_dropdown($name, $selected = 0, $styles = '') {
  global $wplc_webhook_events;

  $content = "<select id='$name' name='$name' style='$styles'>";
  foreach ($wplc_webhook_events as $key => $value) {
    $content .= "<option value='$key' " . (intval($key) === intval($selected) ? "selected " : "") . ">" . $value . "</option>";
  }
  $content .= "</select>";

  return $content;
}

add_action("wplc_fire_webhook", "wplc_webhook_send", 10, 2);
/*
 * Sends the payload to any matched events - Magic
*/
function wplc_webhook_send($event_code, $payload) {
  global $wplc_webhook_events;
  if (isset($event_code) && isset($payload)) {
    $event_code = intval($event_code);
    if (array_key_exists($event_code, $wplc_webhook_events)) {
      $matches = wplc_webhook_get_matched_hooks(intval($event_code));
      if ($matches !== false) {
        //fire off the hooks
        $error_found = false;
        foreach ($matches as $webhook) {
          $target_url = isset($webhook->url) ? $webhook->url : false;
          $method = isset($webhook->method) && $webhook->method === "GET" ? "GET" : "POST";

          if (!is_array($payload)) {
            //Not an array, no worries we will fix that
            $payload = array("other" => $payload);
          }

          $payload = array(
            "event" => $wplc_webhook_events[intval($event_code)],
            "data" => json_encode($payload),
            "time_sent" => time()
          );

          if ($target_url !== false && $target_url !== "") {
            $result = "";
            if ($method === "POST") {
              /** Replaced with WP HTTP API Calls */

              $response = wp_remote_post(
                $target_url,
                array(
                  'method' => 'POST',
                  'timeout' => 45,
                  'redirection' => 5,
                  'httpversion' => '1.0',
                  'blocking' => true,
                  'headers' => array(),
                  'body' => $payload,
                  'cookies' => array()
                )
              );

              if (is_wp_error($response)) {
                //This has failed
                $error_found = true;
                $result = "Failed! No Response.";
              }
            } else {
              /** Replaced with WP HTTP API Calls */

              $get_data = http_build_query($payload);
              $response = wp_remote_get($target_url . "?" . $get_data);
              if (is_wp_error($response)) {
                //This has failed
                $error_found = true;
                $result = "Failed! No Response.";
              }
            }
          }
        }
      }
    }
  }
}

add_action("wplc_change_chat_status_hook", "wplc_webhook_status_change_monitor", 10, 2);
/*
 * Special delegate function for status changes 
 */
function wplc_webhook_status_change_monitor($cid, $status) {
  switch (intval($status)) {
    case 2:
      //Chat Request
      do_action("wplc_fire_webhook", 4, array("chat_id" => $cid));
      break;
    case 3:
      //Agent Accept
      do_action("wplc_fire_webhook", 5, array("chat_id" => $cid));
      break;
  }
}

add_action("wplc_log_user_on_page_after_hook", "wplc_webhook_new_visitor_monitor", 10, 2);
/*
 * Special Delegate function for new visitor (log user on page)
*/
function wplc_webhook_new_visitor_monitor($dbid, $wplc_session_data) {
  do_action("wplc_fire_webhook", 3, array("chat_id" => $dbid));
}

add_action("wp_login", "wplc_webhook_login_monitor", 10, 2);
/*
 * Watches all login activity to track when an agent logs in
*/
function wplc_webhook_login_monitor($user_login, $user) {
  if ($user->has_cap('wplc_ma_agent')) {
    do_action("wplc_fire_webhook", 0, array("agent_id" => $user->ID));
  }
}

add_action("wplc_hook_admin_settings_save", "wplc_webhook_save_settings_monitor");
/*
 * Checks when settings are saved
*/
function wplc_webhook_save_settings_monitor() {
  do_action("wplc_fire_webhook", 6, array("user_id" => get_current_user_id()));
}
