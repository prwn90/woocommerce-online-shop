<?php
/**
 * File that define P24_Core class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core methods for Przelewy 24 plugin.
 */
class P24_Core {

	/**
	 * String to add to loaded scripts.
	 *
	 * @var string
	 */
	const SCRIPTS_VERSION = '1.0.0';

	/**
	 * The null or P24_Multi_Currency instance.
	 *
	 * @var null|P24_Multi_Currency
	 */
	private $multi_currency = null;

	/**
	 * The P24_Request_Support instance.
	 *
	 * @var P24_Request_Support
	 */
	private $request_support;

	/**
	 * The P24_Config_Menu instance.
	 *
	 * @var P24_Config_Menu;
	 */
	private $config_menu;

	/**
	 * The instance of class configuring WP menu.
	 *
	 * @var P24_Multi_Currency_Menu
	 */
	private $wp_menu_support;

	/**
	 * The WC_Gateway_Przelewy24 instance.
	 *
	 * @var WC_Gateway_Przelewy24
	 */
	private $gateway;

	/**
	 * Construct class instance.
	 */
	public function __construct() {
		$this->request_support = new P24_Request_Support();
		$this->wp_menu_support = new P24_Multi_Currency_Menu( $this );
		if ( ! $this->is_in_user_mode() ) {
			$this->config_menu = new P24_Config_Menu( $this );
		}
	}

	/**
	 * Check if page is in user mode.
	 *
	 * @return bool
	 */
	public function is_in_user_mode() {
		if ( is_admin() ) {
			return false;
		} elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if internal multi currency is activated.
	 *
	 * @return bool
	 */
	public function is_multi_currency_active() {
		return (bool) $this->multi_currency;
	}

	/**
	 * Return multi currency instance.
	 *
	 * Should be called only if multi currency i active.
	 *
	 * @return P24_Multi_Currency
	 * @throws LogicException If there is no instance.
	 */
	public function get_multi_currency_instance() {
		if ( $this->is_multi_currency_active() ) {
			return $this->multi_currency;
		} else {
			throw new LogicException( 'Multi currency is not active. It should be tested.' );
		}
	}

	/**
	 * Try override active currency.
	 *
	 * The communication with Przelewy24 is quite late.
	 *
	 * @param P24_Communication_Parser $parser The P24_Communication_Parser instance.
	 */
	public function try_override_active_currency( P24_Communication_Parser $parser ) {
		if ( $this->is_multi_currency_active() ) {
			$this->multi_currency->try_override_active_currency( $parser );
		}
	}

	/**
	 * Return current instance i parameter is null.
	 *
	 * This should be useful in filters.
	 *
	 * @param mixed $default Default value from filter.
	 * @return P24_Core
	 */
	public function get_this_if_null( $default ) {
		return $default ?: $this;
	}

	/**
	 * Render template and output.
	 *
	 * @param string $template The name of template.
	 * @param array  $params The array of parameters.
	 * @throws LogicException If the file is not found.
	 */
	public function render_template( $template, $params = [] ) {
		$dir  = __DIR__ . '/../templates/';
		$file = $template . '.php';
		wc_get_template( $file, $params, $dir, $dir );
	}

	/**
	 * Check if multi currency should be activated.
	 */
	public function should_activate_multi_currency() {
		$common = get_option( P24_Request_Support::OPTION_KEY_COMMON, [] );
		return array_key_exists( 'p24_multi_currency', $common ) && 'yes' === $common['p24_multi_currency'];
	}

	/**
	 * Register gateway.
	 *
	 * The constructor of gateway has to be called in external plugin.
	 *
	 * @param WC_Gateway_Przelewy24 $gateway The gateway instance.
	 */
	public function register_gateway( WC_Gateway_Przelewy24 $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * Get config for currency.
	 *
	 * @param null|string $currency The currency for requested config.
	 * @return P24_Config_Accessor
	 * @throws LogicException If there is no gateway created.
	 */
	public function get_config_for_currency( $currency = null ) {
		if ( ! $this->gateway ) {
			throw new LogicException( 'Gateway in not registered yet.' );
		}
		return $this->gateway->load_settings_from_db_formatted( $currency );
	}

	/**
	 * Get P24_Message_Validator instance.
	 *
	 * @return P24_Message_Validator
	 */
	public function get_message_validator() {
		return new P24_Message_Validator();
	}

	/**
	 * Get P24_Communication_Parser instance.
	 *
	 * @return P24_Communication_Parser
	 */
	public function get_communication_parser() {
		$message_validator = $this->get_message_validator();
		return new P24_Communication_Parser( $message_validator );
	}

	/**
	 * Get default currency.
	 *
	 * @return string
	 */
	public function get_default_currency() {
		if ( $this->is_multi_currency_active() ) {
			return $this->multi_currency->get_default_currency();
		} else {
			return get_woocommerce_currency();
		}
	}

	/**
	 * Late configuration after Woocommerce init.
	 */
	public function after_woocommerce_init() {
		$this->request_support->analyse();
		$this->request_support->flush_options();
		if ( $this->should_activate_multi_currency() ) {
			/* The logic to set active currency is in P24_Multi_Currency class. */
			$currency_changes     = $this->request_support->get_currency_changes();
			$this->multi_currency = new P24_Multi_Currency( $this, $currency_changes );
			$this->multi_currency->bind_events();
			if ( ! $this->is_in_user_mode() ) {
				$this->config_menu->bind_multi_currency_events();
			}
		}
	}

	/**
	 * Bind events.
	 */
	public function bind_core_events() {
		add_filter( 'przelewy24_plugin_instance', [ $this, 'get_this_if_null' ] );
		add_action( 'woocommerce_init', [ $this, 'after_woocommerce_init' ] );
		$this->wp_menu_support->bind_events();
		if ( ! $this->is_in_user_mode() ) {
			$this->config_menu->bind_common_events();
		}
	}
}
