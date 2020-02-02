<?php
/*
 * Define important constants
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('WPLC_PLUGIN_VERSION', "8.1.6");
define('WPLC_PLUGIN_DIR', dirname(__FILE__));
define('WPLC_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
define('WPLC_PLUGIN', plugin_basename( __FILE__ ) );	
define('WPLC_ACTIVATION_SERVER', 'https://activation.wp-livechat.com' ); // gets unique ID for using chat servers
define('WPLC_CHAT_SERVER', 'https://tcx-live-chat.appspot.com');

?>
