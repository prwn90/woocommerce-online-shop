<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (class_exists("WP_REST_Request")) {
	if (wplc_user_is_agent()) {
		include_once "wplc-api-routes.php";
		include_once "wplc-api-functions.php";
	}
}