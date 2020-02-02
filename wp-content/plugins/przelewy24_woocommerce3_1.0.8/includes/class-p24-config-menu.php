<?php
/**
 * File that define P24_Config class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods for Przelewy 24 plugin to display admin config.
 *
 * Processing of config is in different class.
 */
class P24_Config_Menu {

	/**
	 * Instance of core of plugin.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * Construct class instance.
	 *
	 * @param P24_Core $plugin_core The core class for plugin.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Render config page.
	 */
	public function render_config_page() {
		$tab           = empty( $_GET['tab'] ) ? 'main' : sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$multicurrency = $this->plugin_core->is_multi_currency_active();
		switch ( $tab ) {
			case 'main':
				$this->render_config_tabs( $tab, $multicurrency );
				$this->render_config_main_page();
				break;
			case 'multipliers':
				$this->render_config_tabs( $tab, $multicurrency );
				$this->render_config_multipliers_page();
				break;
			case 'formats':
				$this->render_config_tabs( $tab, $multicurrency );
				$this->render_config_format_page();
				break;
		}
	}

	/**
	 * Render tabs of config page.
	 *
	 * @param string $tab The active tab.
	 * @param bool   $multicurrency If multi currency is active.
	 */
	private function render_config_tabs( $tab, $multicurrency ) {
		$params = compact( 'tab', 'multicurrency' );
		$this->plugin_core->render_template( 'multi-currency-tabs', $params );
	}

	/**
	 * Render form to activate multi currency module.
	 */
	private function render_config_main_page() {
		$value  = $this->plugin_core->should_activate_multi_currency();
		$params = compact( 'value' );
		$this->plugin_core->render_template( 'multi-currency-main', $params );
	}

	/**
	 * Render form to set currency multipliers.
	 */
	private function render_config_multipliers_page() {
		$available                     = get_woocommerce_currencies();
		$multipliers                   = $this->plugin_core->get_multi_currency_instance()->get_multipliers();
		$base_currency                 = get_option( 'woocommerce_currency' );
		$multipliers[ $base_currency ] = 1;
		$params                        = compact( 'multipliers', 'base_currency', 'available' );
		$this->plugin_core->render_template( 'multi-currency-multipliers', $params );
	}

	/**
	 * Render form to change format of currency.
	 */
	private function render_config_format_page() {
		$formats         = get_option( 'przelewy24_multi_currency_formats', [] );
		$active_currency = $this->plugin_core->get_multi_currency_instance()->get_active_currency();
		if ( array_key_exists( $active_currency, $formats ) ) {
			$format = $formats[ $active_currency ];
		} else {
			$base_currency = get_option( 'woocommerce_currency' );
			if ( array_key_exists( $base_currency, $formats ) ) {
				$format = $formats[ $base_currency ];
			} else {
				$format = [
					'currency_pos'       => get_option( 'woocommerce_currency_pos' ),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'decimals'           => wc_get_price_decimals(),
				];
			}
		}
		$currency_options = get_przelewy24_multi_currency_options();

		$params = compact( 'format', 'active_currency', 'currency_options' );
		$this->plugin_core->render_template( 'multi-currency-formats', $params );
	}

	/**
	 * Prepare common config menu.
	 */
	public function prepare_config_menu() {
		add_submenu_page(
			'woocommerce',
			'P24 Multi Currency',
			'P24 Multi Currency',
			'manage_options',
			'p24-multi-currency',
			[ $this, 'render_config_page' ]
		);
	}

	/**
	 * Add scripts used on admin page.
	 */
	public function add_admin_scripts() {
		wp_enqueue_style( 'p24_multi_currency_admin', PRZELEWY24_URI . 'assets/css/p24_multi_currency_style_admin.css', [], P24_Core::SCRIPTS_VERSION );
	}

	/**
	 * Update WooCommerce settings panels.
	 *
	 * The MultiCurrency make few changes.
	 * Few config items have to be renamed or overwritten.
	 * The required config is added by different functions.
	 *
	 * @param array $input The WooCommerce settings.
	 * @return array
	 */
	public function clear_woocommerce_settings( $input ) {
		$ret = [];
		foreach ( $input as $k => $v ) {
			switch ( $v['id'] ) {
				case 'woocommerce_currency':
					/* Change label. */
					$v['title'] = __( 'Waluta podstawowa', 'przelewy24' );
					$v['desc']  = null;
					$ret[ $k ]  = $v;
					break;
				case 'woocommerce_currency_pos':
				case 'woocommerce_price_thousand_sep':
				case 'woocommerce_price_decimal_sep':
				case 'woocommerce_price_num_decimals':
					/* These options are overwritten by multi currency. */
					break;
				default:
					$ret[ $k ] = $v;
			}
		}
		return $ret;
	}

	/**
	 * Add box to set currency for order.
	 *
	 * This box is used on admin panel.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function add_admin_order_change_currency( WP_Post $post ) {
		$currency_options = $this->plugin_core->get_multi_currency_instance()->get_available_currencies();
		$params           = compact( 'post', 'currency_options' );
		$this->plugin_core->render_template( 'multi-currency-order-edit', $params );
	}

	/**
	 * Add all meta boxes for different pages.
	 */
	public function add_meta_boxes() {
		foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
			add_meta_box( 'p24_admin_order_multi_currency', __( 'Aktywna waluta', 'przelewy24' ), [ $this, 'add_admin_order_change_currency' ], $type, 'side', 'high' );
		}
	}

	/**
	 * Bind common events.
	 */
	public function bind_common_events() {
		add_action( 'admin_enqueue_scripts', [ $this, 'add_admin_scripts' ] );
		add_action( 'admin_menu', [ $this, 'prepare_config_menu' ] );
	}

	/**
	 * Bind multi currency events.
	 */
	public function bind_multi_currency_events() {
		add_filter( 'woocommerce_general_settings', [ $this, 'clear_woocommerce_settings' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

}
