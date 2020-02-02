<?php
/**
 * Plugin Name: Send System Info
 * Plugin URI: https://www.mattcromwell.com
 * Description: Displays System Info for debugging.  This info can be emailed and/or displayed to support personnel via unique URL.
 * Version: 1.3
 * Author: Matt Cromwell
 * Author URI: https://www.mattcromwell.com
 * License: GPLv2+
 * textdomain: send-system-info
 */

/**
 * Copyright (c) 2017 Matt Cromwell (https://www.mattcromwell.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * System Info textarea based on Easy Digital Downloads by Pippin Williamson.
 * http://easydigitaldownloads.com/
 * Used with permission.
 *
 * @package SSI
 * @author  Matt Cromwell
 * @version 1.3
 */

class Send_System_Info_Plugin {

	/**
	 * Load hooks
	 *
	 * @since  1.0
	 * @action plugins_loaded
	 *
	 * @return void
	 */
	static function setup() {
		define( 'SSI_DIR', plugin_dir_path( __FILE__ ) );
		define( 'SSI_INC_DIR', SSI_DIR . 'includes/' );
		define( 'SSI_VIEWS_DIR', SSI_DIR . 'views/' );

		require_once SSI_INC_DIR . 'email.php';
		require_once SSI_INC_DIR . 'viewer.php';
		require_once SSI_INC_DIR . 'browser.php';

		register_activation_hook( __FILE__, array( __CLASS__, 'generate_url' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_submenu_page' ) );
		add_action( 'wp_ajax_regenerate_url', array( __CLASS__, 'generate_url' ) );
		add_action( 'wp_ajax_delete_ssi_url', array( __CLASS__, 'delete_ssi_url' ) );
		add_action( 'wp_ajax_download_system_info', array( __CLASS__, 'download_info' ) );
		add_action( 'template_redirect', array( 'Send_System_Info_Viewer', 'remote_view' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_link' ) );
	}

	/**
	 * Print direct link to Send System Info page from Plugins Page
	 *
	 * @since  1.0
	 * @filter plugin_action_links_
	 *
	 * @param  array  Array of links
	 * @return array  Updated Array of links
	 */
	static function action_link( $links ) {
		$links[] = '<a href="' . admin_url( 'tools.php?page=send-system-info.php' ) . '">' . __( 'View System Info', 'send-system-info' ) . '</a>';
		return $links;
	}

	/**
	 * Enqueue Javascript
	 *
	 * @since  1.0
	 * @action admin_print_scripts-
	 *
	 * @return void
	 */
	static function enqueue_js() {
		wp_register_script( 'ssi-script', plugins_url( '/ui/send-system-info.js', __FILE__ ), array( 'jquery'), mt_rand(), false );
		wp_localize_script( 'ssi-script', 'systemInfoAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( 'ssi-script' );
	}

	/**
	 * Enqueue CSS
	 *
	 * @since  1.0
	 * @action admin_print_styles-
	 *
	 * @return void
	 */
	static function enqueue_css() {
		wp_enqueue_style( 'ssi-style', plugins_url( '/ui/send-system-info.css', __FILE__ ) );
	}

	/**
	 * Register submenu page and enqueue styles and scripts.
	 * Only viewable by Administrators
	 *
	 * @since  1.0
	 * @action admin_menu
	 *
	 * @return void
	 */
	static function register_submenu_page() {
		$page = add_submenu_page(
			'tools.php',
			__( 'System Info', 'send-system-info' ),
			__( 'Send System Info', 'send-system-info' ),
			'manage_options',
			'send-system-info',
			array( __CLASS__, 'render_info' )
		);

		//Enqueue scripts and styles on the Plugin Settings page only
		add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'enqueue_css' ) );
		add_action( 'admin_print_scripts-' . $page, array( __CLASS__, 'enqueue_js' ) );
	}

	/**
	 * Render plugin page title, information and info textarea
	 *
	 * @since  1.0
	 *
	 * @return void
	 */
	static function render_info() {
		$email_sent = Send_System_Info_Email::send_email();
		if ( $email_sent && 'sent' == $email_sent ) {
			printf( '<div id="message" class="updated"><p>%s</p></div>', __( 'Email sent successfully.', 'send-system-info' ) );
		} elseif ( $email_sent && 'error' == $email_sent ) {
			printf( '<div id="message" class="error"><p>%s</p></div>', __( 'Error sending Email.', 'send-system-info' ) );
		}

		$path = SSI_VIEWS_DIR . 'send-system-info.php';

		/**
		 * Change the path for the send system info view.
		 *
		 * Use this to replace the default form for sending the system info (views/send-system-info.php) with a custom view.
		 *
		 * @since	1.1.0
		 *
		 * @param 	string	$path 	Path to custom view.
		 * @return	string			Path to output view.
		 */
		$path = apply_filters( 'ssi_view_path_send_system_info', $path );
		include( $path );
	}

	/**
	 * Generate Text file download
	 *
	 * @since  1.0
	 *
	 * @return void
	 */
	static function download_info() {
		if ( ! isset( $_POST['send-system-info-textarea'] ) || empty( $_POST['send-system-info-textarea'] ) ) {
			return;
		}

		header( 'Content-type: text/plain' );

		//Text file name marked with Unix timestamp
		header( 'Content-Disposition: attachment; filename=system_info_' . time() . '.txt' );

		echo $_POST['send-system-info-textarea'];
		die();
	}

	/**
	 * Gather data, then generate System Info
	 *
	 * Based on System Info sumbmenu page in Easy Digital Downloads
	 * by Pippin Williamson
	 *
	 * @since  1.0
	 *
	 * @return void
	 */
	static function display() {
		$browser = new Browser();
		if ( get_bloginfo( 'version' ) < '3.4' ) {
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
			$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
		} else {
			$theme_data = wp_get_theme();
			$theme      = $theme_data->Name . ' ' . $theme_data->Version;
		}

		// Try to identify the hosting provider
		$host = false;
		if ( defined( 'WPE_APIKEY' ) ) {
			$host = 'WP Engine';
		} elseif ( defined( 'PAGELYBIN' ) ) {
			$host = 'Pagely';
		}

		$request['cmd'] = '_notify-validate';

		$params = array(
			'sslverify' => false,
			'timeout'   => 60,
			'body'      => $request,
		);

		$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
			$WP_REMOTE_POST = 'wp_remote_post() works' . "\n";
		} else {
			$WP_REMOTE_POST = 'wp_remote_post() does not work' . "\n";
		}

		return self::display_output( $browser, $theme, $host, $WP_REMOTE_POST );
	}

	/**
	 * Render System Info
	 *
	 * Based on System Info sumbmenu page in Easy Digital Downloads
	 * by Pippin Williamson
	 *
	 * @since  1.0
	 *
	 * @param   string  Browser information
	 * @param   string  Theme Data
	 * @param   string  Theme name
	 * @param   string  Host
	 * @param   string  WP Remote Host
	 * @return  string  Output of System Info display
	 */
	//Render Info Display
	static function display_output( $browser, $theme, $host, $WP_REMOTE_POST ) {
		global $wpdb;
		ob_start();
		$path = SSI_VIEWS_DIR . 'output.php';

		/**
		 * Change the path for the output view.
		 *
		 * Use this to replace the default admin page for this plugin (views/output.php) with a custom view.
		 *
		 * @since	1.1.0
		 *
		 * @param 	string	$path 	Path to custom view.
		 * @return	string			Path to output view.
		 */
		$path = apply_filters( 'ssi_view_path_output', $path );

		include( $path );
		return ob_get_clean();
	}

	/**
	 * Size Conversions
	 *
	 * @author Chris Christoff
	 * @since 1.0
	 *
	 * @param  unknown    $v
	 * @return int|string
	 */
	static function let_to_num( $v ) {
		$l   = substr( $v, -1 );
		$ret = substr( $v, 0, -1 );

		switch ( strtoupper( $l ) ) {
			case 'P': // fall-through
			case 'T': // fall-through
			case 'G': // fall-through
			case 'M': // fall-through
			case 'K': // fall-through
				$ret *= 1024;
				break;
			default:
				break;
		}

		return $ret;
	}

	/**
	 * Generate Random URL for the remote view.
	 * Saves result to options.  If it's an ajax request
	 * the new query value is sent back to the js script.
	 *
	 * @since  1.0
	 * @action wp_ajax_regenerate_url
	 *
	 * @return void
	 */
	static function generate_url() {
		$alphabet    = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
		$value       = array();
		$alphaLength = strlen( $alphabet ) - 1;
		for ( $i = 0; $i < 32; $i++ ) {
			$n     = rand( 0, $alphaLength );
			$value[] = $alphabet[$n];
		}
		$value = implode( $value );
		update_option( 'system_info_remote_url', $value );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$output = home_url() . '/?system_info=' . $value;
			wp_send_json( $output );
		}
	}

	/**
	 * Delete SSI Generated URL action.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */

	static function delete_ssi_url() {

		delete_option( 'system_info_remote_url' );

	}

	/**
	 * Delete URL option on uninstall.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	static function uninstall() {
		delete_option( 'system_info_remote_url' );
	}

}
//Load Plugin on 'plugins_loaded'
add_action( 'plugins_loaded', array( 'Send_System_Info_Plugin', 'setup' ) );
