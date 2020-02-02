<?php 
/* Handles all routes related to the WP Live Chat Support API */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('rest_api_init', 'wplc_rest_routes_init');

function wplc_rest_routes_init() {

    register_rest_route('wp_live_chat_support/v1','/end_chat', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_end_chat',
                        'permission_callback' => 'wplc_api_permission_check'
    ));

    register_rest_route('wp_live_chat_support/v1','/send_message', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_send_message',
                        'permission_callback' => 'wplc_api_permission_check'
    ));


    register_rest_route('wp_live_chat_support/v1','/get_messages', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_get_messages',
                        'permission_callback' => 'wplc_api_permission_check'
    ));

    register_rest_route('wp_live_chat_support/v1','/start_chat', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_call_start_chat',
                        'permission_callback' => 'wplc_api_permission_check_start_chat'
    ));

	register_rest_route('wp_live_chat_support/v1','/typing', array(
						'methods' => 'GET, POST',
						'callback' => 'wplc_api_is_typing_mrg',
                        'permission_callback' => 'wplc_api_permission_check'
	));
	
    register_rest_route('wp_live_chat_support/v1','/start_session', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_start_session',
                        'permission_callback' => 'wplc_api_permission_check_start_chat'
    ));
	
    register_rest_route('wp_live_chat_support/v1','/end_session', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_end_session',
                        'permission_callback' => 'wplc_api_permission_check'
    ));
	


    do_action("wplc_api_route_hook");
}