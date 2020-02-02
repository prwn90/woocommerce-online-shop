<?php
/**
 * Plugin Name: YITH WooCommerce Brands Add-on
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
 * Description: <code><strong>YITH WooCommerce Brands Add-on</strong></code> allows organizing products by brand and improve your shop user experience and your visibility on serach engines. Let your customers browse your shop based on their favourite brands and just with a few clicks. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>
 * Version: 1.3.11
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-brands-add-on
 * Domain Path: /languages/
 * WC requires at least: 3.2.0
 * WC tested up to: 3.9
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Brands Add-on
 * @version 1.0.0
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! defined( 'YITH_WCBR' ) ) {
	define( 'YITH_WCBR', true );
}

if ( ! defined( 'YITH_WCBR_VERSION' ) ) {
	define( 'YITH_WCBR_VERSION', '1.3.11' );
}

if ( ! defined( 'YITH_WCBR_URL' ) ) {
	define( 'YITH_WCBR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_DIR' ) ) {
	define( 'YITH_WCBR_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_INC' ) ) {
	define( 'YITH_WCBR_INC', YITH_WCBR_DIR . 'includes/' );
}

if ( ! defined( 'YITH_WCBR_INIT' ) ) {
	define( 'YITH_WCBR_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCBR_FREE_INIT' ) ) {
	define( 'YITH_WCBR_FREE_INIT', plugin_basename( __FILE__ ) );
}

/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WCBR_DIR . 'plugin-fw/init.php' ) ) {
	require_once( YITH_WCBR_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_WCBR_DIR  );

if( ! function_exists( 'yith_brands_constructor' ) ) {
	function yith_brands_constructor() {
		load_plugin_textdomain( 'yith-woocommerce-brands-add-on', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		require_once( YITH_WCBR_INC . 'functions.yith-wcbr.php' );
		require_once( YITH_WCBR_INC . 'class.yith-wcbr.php' );
		require_once( YITH_WCBR_INC . 'class.yith-wcbr-shortcode.php' );

		// Let's start the game
		YITH_WCBR();

		if( is_admin() ){
			require_once( YITH_WCBR_INC . 'class.yith-wcbr-admin.php' );

			YITH_WCBR_Admin();
		}
	}
}
add_action( 'yith_wcbr_init', 'yith_brands_constructor' );

if( ! function_exists( 'yith_brands_install' ) ) {
	function yith_brands_install() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_wcbr_install_woocommerce_admin_notice' );
		}
		elseif( defined( 'YITH_WCBR_PREMIUM' ) ) {
			add_action( 'admin_notices', 'yith_wcbr_install_free_admin_notice' );
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
		else {
			do_action( 'yith_wcbr_init' );
		}
	}
}
add_action( 'plugins_loaded', 'yith_brands_install', 11 );

if( ! function_exists( 'yith_wcbr_install_woocommerce_admin_notice' ) ) {
	function yith_wcbr_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php echo sprintf( __( '%s is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-brands-add-on' ), 'YITH WooCommerce Brands Add-on' ); ?></p>
		</div>
	<?php
	}
}

if( ! function_exists( 'yith_wcbr_install_free_admin_notice' ) ){
	function yith_wcbr_install_free_admin_notice() {
		?>
		<div class="error">
			<p><?php echo sprintf( __( 'You can\'t activate the free version of %s while you are using the premium one.', 'yith-woocommerce-brands-add-on' ), 'YITH WooCommerce Brands Add-on' ); ?></p>
		</div>
	<?php
	}
}