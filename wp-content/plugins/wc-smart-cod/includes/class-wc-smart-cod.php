<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/stratos-vetsos-08262473/
 * @since      1.0.0
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/includes
 * @author     FullStack <vetsos.s@gmail.com>
 */
class Wc_Smart_Cod {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Smart_Cod_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wc-smart-cod';
		define( 'SMART_COD_VER', '1.4.9.6' );

		add_action( 'plugins_loaded', array( $this, 'load_dependencies' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'load_smart_cod' ) );
		add_action( 'admin_notices', array( $this, 'activate_notice' ) );
		add_action( 'after_plugin_row_wc-smart-cod/wc-smart-cod.php', array( $this, 'add_warning' ) );

	}

	public function add_warning( $plugins ) {
		if( SMART_COD_VER === '1.4.9.5' || SMART_COD_VER === '1.4.9.6' ) {
			echo '
			<tr>
				<td colspan="3">
					<div class="notice inline notice-success notice-alt">
						<p class="small">
							Users that are using the settings "Disable if cart amount is greater than" and "Disable extra fee if cart amount is greater than this limit" should revise their <a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cod' ) . '">settings</a> and ensure that everything works properly.&nbsp<a href="https://wordpress.org/plugins/wc-smart-cod/" target="_blank">Learn more</a> for the update
						</p>
					</div>
				</td>
			</tr>';
		}
	}

	public static function wc_version_check( $version = '3.4' ) {
		if ( class_exists( 'WooCommerce' ) ) {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
				return true;
			}
		}
		return false;
	}

	public function activate_notice() {

		if( get_transient( 'wc-smart-cod-activated' ) ) : ?>
			<div class="updated notice is-dismissible">
				<p>Thank you for using WooCommerce Smart COD! Setup your settings <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cod' ); ?>">here</a>.</p>
			</div>
			<?php
			delete_transient( 'wc-smart-cod-activated' );
		endif;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wc_Smart_Cod_Loader. Orchestrates the hooks of the plugin.
	 * - Wc_Smart_Cod_i18n. Defines internationalization functionality.
	 * - Wc_Smart_Cod_Admin. Defines all hooks for the admin area.
	 * - Wc_Smart_Cod_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */

	public function load_smart_cod( $gateways ) {

		$key = array_search( 'WC_Gateway_COD', $gateways );
		if( $key ) {
			$gateways[ $key ] = 'Wc_Smart_Cod_Admin';
		}

 		return $gateways;

 	}

	public function load_dependencies() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-smart-cod-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-smart-cod-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-smart-cod-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wc-smart-cod-public.php';

		$this->loader = new Wc_Smart_Cod_Loader();
		$admin_class = 'Wc_Smart_Cod_Admin';

		add_action( 'wp_ajax_wcsmartcod_json_search_categories', array( $admin_class, 'ajax_search_categories' ) );

		// /$this->define_admin_hooks();
		$this->set_locale();
		$this->define_public_hooks();
		$this->loader->run();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wc_Smart_Cod_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wc_Smart_Cod_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wc_Smart_Cod_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wc_Smart_Cod_Public( $this->get_plugin_name() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wc_Smart_Cod_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
