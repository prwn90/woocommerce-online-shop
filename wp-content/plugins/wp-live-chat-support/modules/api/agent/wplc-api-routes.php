<?php 
/* Handles all routes related to the WP Live Chat Support API */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('rest_api_init', 'wplc_rest_agent_routes_init');

function wplc_rest_agent_routes_init() {
    register_rest_route('wp_live_chat_support/v1','/accept_chat', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_accept_chat',
                        'permission_callback' => 'wplc_api_agent_permission_check'
    ));

    register_rest_route('wp_live_chat_support/v1','/agent_end_chat', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_agent_end_chat',
                        'permission_callback' => 'wplc_api_agent_permission_check'
    ));
	

    register_rest_route('wp_live_chat_support/v1','/agent_send_message', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_agent_send_message',
                        'permission_callback' => 'wplc_api_agent_permission_check'
    ));

    register_rest_route('wp_live_chat_support/v1','/agent_get_messages', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_agent_get_messages',
                        'permission_callback' => 'wplc_api_agent_permission_check'
    ));


	register_rest_route('wp_live_chat_support/v1','/remote_upload', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_api_remote_upload',
                        'permission_callback' => 'wplc_api_agent_permission_check'
    ));
	
    register_rest_route('wp_live_chat_support/v1','/validate_agent', array(
                        'methods' => 'GET, POST',
                        'callback' => 'wplc_validate_agent_check',
                        'permission_callback' => 'wplc_api_agent_permission_check'
    ));
	

	register_rest_route('wp_live_chat_support/v1', '/get_agent_unread_message_counts', array(
						'methods' => 'GET, POST',
						'callback' => 'wplc_api_get_agent_unread_message_counts_mrg',
                        'permission_callback' => 'wplc_api_agent_permission_check'

	));
	
	register_rest_route('wp_live_chat_support/v1', '/send_agent_message', array(
						'methods' => 'GET, POST',
						'callback' => 'wplc_api_send_agent_message_mrg',
                        'permission_callback' => 'wplc_api_agent_permission_check'
	));
	
	register_rest_route('wp_live_chat_support/v1', '/initiate_chat', array(
						'methods' => 'GET, POST',
						'callback' => 'wplc_api_initiate_chat_mrg',
                        'permission_callback' => 'wplc_api_agent_permission_check'
	));

    do_action("wplc_api_route_hook");
}