<?php
/**
 * File that define P24_Multi_Currency class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Multi_Currency
 *
 * Add elementary functions to support multi currency.
 */
class P24_Multi_Currency {

	/**
	 * Instance of core of plugin.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * The currency to display for users.
	 *
	 * @var string|null
	 */
	private $active_currency;

	/**
	 * P24_Multi_Currency constructor.
	 *
	 * @param P24_Core    $plugin_core Instance of main plugin.
	 * @param string|null $active_currency Active currency to set.
	 */
	public function __construct( P24_Core $plugin_core, $active_currency ) {
		$this->plugin_core     = $plugin_core;
		$this->active_currency = $active_currency;
	}

	/**
	 * Get active currency.
	 *
	 * @return string
	 */
	public function get_active_currency() {
		if ( ! $this->active_currency ) {
			$multipliers = $this->get_multipliers();
			if ( $this->plugin_core->is_in_user_mode() ) {
				/* Try load from session. */
				$session               = WC()->session;
				$this->active_currency = $session->get( 'przelewy24_active_currency' );
				if ( ! $this->active_currency || ! array_key_exists( $this->active_currency, $multipliers ) ) {
					$this->active_currency = get_option( 'woocommerce_currency' );
					$this->save_currency_to_session();
				}
			} else {
				/* On admin panel. */
				if ( isset( $_COOKIE['admin_p24_currency'] ) ) {
					$this->active_currency = sanitize_text_field( wp_unslash( $_COOKIE['admin_p24_currency'] ) );
				}
				if ( ! $this->active_currency || ! array_key_exists( $this->active_currency, $multipliers ) ) {
					$this->active_currency = get_option( 'woocommerce_currency' );
				}
			}
		}

		return $this->active_currency;
	}

	/**
	 * Get default currency.
	 *
	 * It may be different from active currency.
	 *
	 * @return string
	 */
	public function get_default_currency() {
		return get_option( 'woocommerce_currency' );
	}

	/**
	 * Save active currency to session.
	 *
	 * The function has to be called after session creation.
	 */
	public function save_currency_to_session() {
		$session             = WC()->session;
		$old_active_currency = $session->get( 'przelewy24_active_currency' );
		if ( $this->active_currency !== $old_active_currency ) {
			$session->set( 'przelewy24_active_currency', $this->active_currency );
		}
	}

	/**
	 * Get array of multipliers for currencies.
	 *
	 * @return array
	 */
	public function get_multipliers() {
		$set             = get_option( 'przelewy24_multi_currency_multipliers', [] );
		$default         = get_option( 'woocommerce_currency' );
		$set[ $default ] = 1;
		return $set;
	}

	/**
	 * Get list of available currencies.
	 *
	 * It is based on multipliers.
	 *
	 * @return array
	 */
	public function get_available_currencies() {
		$set  = $this->get_multipliers();
		$keys = array_keys( $set );
		return array_combine( $keys, $keys );
	}

	/**
	 * Method for filter that change default currency.
	 *
	 * The default value is ignored.
	 * The wrapped function should always return something.
	 *
	 * @param mixed $default Default value provided by filter.
	 * @return string
	 */
	public function try_change_default_currency( $default ) {
		return $this->get_active_currency() ?: $default;
	}

	/**
	 * Try change price format.
	 *
	 * @param mixed  $default Default value.
	 * @param string $option Name of option.
	 * @return mixed
	 */
	private function try_change_price_format( $default, $option ) {
		/* Do not change defaults on admin panel. */
		if ( ! $this->plugin_core->is_in_user_mode() ) {
			return $default;
		}
		$currency = $this->get_active_currency();
		$formats  = get_option( 'przelewy24_multi_currency_formats', [] );
		if ( array_key_exists( $currency, $formats ) ) {
			if ( array_key_exists( $option, $formats[ $currency ] ) ) {
				$ret = $formats[ $currency ][ $option ];
				if ( 'thousand_separator' === $option || '' !== $ret ) {
					return $ret;
				}
			}
		}
		return $default;
	}

	/**
	 * Try change thousand separator.
	 *
	 * @param string $default Default thousand separator.
	 * @return string
	 */
	public function try_change_thousand_separator( $default ) {
		return $this->try_change_price_format( $default, 'thousand_separator' );
	}

	/**
	 * Try change decimal separator.
	 *
	 * @param string $default Default decimal separator.
	 * @return string
	 */
	public function try_change_decimal_separator( $default ) {
		return $this->try_change_price_format( $default, 'decimal_separator' );
	}

	/**
	 * Try change number of fraction digits.
	 *
	 * @param mixed $default Default number of fraction digits.
	 * @return mixed
	 */
	public function try_change_decimals( $default ) {
		return $this->try_change_price_format( $default, 'decimals' );
	}

	/**
	 * Try change currency position.
	 *
	 * @param string $default Default currency position.
	 * @return string
	 */
	public function try_change_currency_pos( $default ) {
		return $this->try_change_price_format( $default, 'currency_pos' );
	}

	/**
	 * Add scripts used on admin page.
	 */
	public function add_admin_scripts() {
		wp_enqueue_script( 'p24_multi_currency_admin_script', PRZELEWY24_URI . 'assets/js/p24_multi_currency_admin_script.js', [ 'jquery' ], P24_Core::SCRIPTS_VERSION, true );
	}

	/**
	 * Add scripts used on user page.
	 */
	public function add_user_scripts() {
		wp_enqueue_style( 'p24_multi_currency_form', PRZELEWY24_URI . 'assets/css/p24_multi_currency_style.css', [], P24_Core::SCRIPTS_VERSION );
	}

	/**
	 * An AJAX method to change currency for admin.
	 */
	public function admin_ajax_change_currency() {
		header( 'Content-Type: text/plain; charset=utf-8' );
		wc_setcookie( 'admin_p24_currency', $this->get_active_currency() );
		echo 'Ok';
		wp_die();
	}

	/**
	 * Try find name of class for product.
	 *
	 * This method works as filter. The suggested class is required to do proper override.
	 *
	 * @param string $suggested Suggested product class.
	 * @param string $type Product type.
	 * @param string $variation Product variation.
	 * @param int    $product_id Product id.
	 * @return string
	 * @throws LogicException If nothing was provided or found.
	 */
	public function find_product_class( $suggested, $type, $variation, $product_id ) {
		$rx = '/^WC\\_Product\\_(.+)$/';
		if ( preg_match( $rx, $suggested, $m ) ) {
			$class = 'P24_Product_' . $m[1];
		} else {
			$class = $suggested;
		}
		if ( class_exists( $class ) ) {
			return $class;
		} else {
			$msg = "Cannot find class $class, suggested was $suggested, type was $type, variation was $variation, id was $product_id";
			throw new LogicException( $msg );
		}
	}

	/**
	 * Update price hash.
	 *
	 * The multiplier for currency is added.
	 *
	 * @param array      $hash Default hash.
	 * @param WC_Product $product The product.
	 * @param string     $context The context.
	 * @return array
	 * @throws LogicException If the currency is not configured.
	 */
	public function filter_price_hash( $hash, $product, $context ) {
		if ( method_exists( $product, 'get_currency' ) ) {
			$currency    = $product->get_currency( $context );
			$multipliers = $this->get_multipliers();
			if ( ! array_key_exists( $currency, $multipliers ) ) {
				throw new LogicException( "The requested currency $currency is not configured." );
			}
			$hash[] = $multipliers[ $currency ];
		}
		return $hash;
	}

	/**
	 * Try override active currency.
	 *
	 * The communication with Przelewy24 is quite late.
	 *
	 * @param P24_Communication_Parser $parser The P24_Communication_Parser instance.
	 */
	public function try_override_active_currency( P24_Communication_Parser $parser ) {
		if ( $parser->is_valid() ) {
			$this->active_currency = $parser->get_currency();
		}
	}

	/**
	 * Compute price in provided currency.
	 *
	 * @param mixed  $price Price in default currency.
	 * @param string $currency Provided currency.
	 * @return mixed
	 * @throws LogicException If currency is not found.
	 */
	public function compute_price_in_currency( $price, $currency ) {
		if ( ! $price ) {
			/* We have to preserve different false values. */
			return $price;
		}
		$multipliers = $this->get_multipliers();
		if ( array_key_exists( $currency, $multipliers ) ) {
			$multiplier = $multipliers[ $currency ];
		} else {
			throw new LogicException( "The currency $currency not found in config." );
		}
		if ( 1.0 === (float) $multiplier ) {
			return $price;
		} else {
			return $price * $multiplier;
		}
	}

	/**
	 * Compute prices for sending package.
	 *
	 * @param array $rates The set of rages.
	 *
	 * @return array
	 */
	public function update_package_rates( $rates ) {
		$currency = $this->get_active_currency();

		$ret = array();
		foreach ( $rates as $idx => $rate ) {
			$cost = $rate->get_cost();
			$cost = $this->compute_price_in_currency( $cost, $currency );
			$rate->set_cost( $cost );

			$taxes          = $rate->get_taxes();
			$currencies_map = array_fill_keys( array_keys( $taxes ), $currency );
			$taxes          = array_map( array( $this, 'compute_price_in_currency' ), $taxes, $currencies_map );
			$rate->set_taxes( $taxes );

			$ret[ $idx ] = $rate;
		}
		return $rates;
	}

	/**
	 * Try override one field for multi currency.
	 *
	 * @param string $field The name of field in meta table.
	 * @param array  $sql The SQL split into few parts.
	 *
	 * @return array
	 */
	private function sql_override_field( $field, $sql ) {
		$rxs = '/^(.*\\S)\\s?SUM\\s*\\(\\s*meta_' . $field . '\\.meta_value\\s*\\)(.*)$/Dis';
		$rxj = '/INNER\\s+JOIN\\s+(\\S*postmeta)\\s+AS\\s+(\\S+)\\s+ON\\s*\\([^\\)]*\\.meta_key\\s*\\=\\s*\\\'' . $field . '\\\'[^\\)]*\\)/is';
		if ( preg_match( $rxs, $sql['select'], $ms ) && preg_match( $rxj, $sql['join'], $mj ) ) {
			$meta_tbl      = $mj[1];
			$base_tbl      = $mj[2];
			$our_tbl       = $base_tbl . '_p24dc';
			$our_field     = $field . '_p24dc';
			$select_head   = $ms[1];
			$select_tail   = $ms[2];
			$sql['select'] = "$select_head SUM(IFNULL($our_tbl.meta_value, $base_tbl.meta_value ) )$select_tail";
			$sql['join']   = $sql['join'] . "\n"
				. " LEFT JOIN $meta_tbl AS $our_tbl ON (\n"
				. " $our_tbl.meta_key = '$our_field'\n"
				. " AND $our_tbl.post_id = $base_tbl.post_id\n"
				. " )\n";
		}
		return $sql;
	}

	/**
	 * Override SQL to be compatible with multi currency.
	 *
	 * @param array $sql The SQL split into few parts.
	 *
	 * @return array
	 */
	public function sql_override( $sql ) {
		if ( array_key_exists( 'select', $sql ) && array_key_exists( 'join', $sql ) ) {
			$sql = $this->sql_override_field( '_order_total', $sql );
			$sql = $this->sql_override_field( '_order_tax', $sql );
			$sql = $this->sql_override_field( '_order_shipping', $sql );
			$sql = $this->sql_override_field( '_order_shipping_tax', $sql );
			$sql = $this->sql_override_field( '_order_discount', $sql );
			$sql = $this->sql_override_field( '_order_discount_tax', $sql );
		}
		return $sql;
	}

	/**
	 * Add additional fields to order.
	 *
	 * @param WC_Abstract_Order $order The order to save.
	 */
	public function before_order_save( $order ) {
		$dc = $this->get_default_currency();
		$oc = $order->get_currency();
		if ( $dc !== $order->get_currency() ) {
			$multipliers     = $this->get_multipliers();
			$multiplier      = $multipliers[ $oc ];
			$total           = $order->get_total();
			$dc_total        = $total / $multiplier;
			$tax             = $order->get_cart_tax();
			$dc_tax          = $tax / $multiplier;
			$shipping        = $order->get_shipping_total();
			$dc_shipping     = $shipping / $multiplier;
			$shipping_tax    = $order->get_shipping_tax();
			$dc_shipping_tax = $shipping_tax / $multiplier;
			$discount        = $order->get_discount_total();
			$dc_discount     = $discount / $multiplier;
			$discount_tax    = $order->get_discount_tax();
			$dc_discount_tax = $discount_tax / $multiplier;
			$order->add_meta_data( '_order_total_p24dc', $dc_total, true );
			$order->add_meta_data( '_order_tax_p24dc', $dc_tax, true );
			$order->add_meta_data( '_order_shipping_p24dc', $dc_shipping, true );
			$order->add_meta_data( '_order_shipping_tax_p24dc', $dc_shipping_tax, true );
			$order->add_meta_data( '_cart_discount_p24dc', $dc_discount, true );
			$order->add_meta_data( '_cart_discount_tax_p24dc', $dc_discount_tax, true );
			if ( $order instanceof WC_Order_Refund ) {
				$refund    = $order->get_amount();
				$dc_refund = $refund / $multiplier;
				$order->add_meta_data( '_refund_amount_p24dc', $dc_refund, true );
			}
		}
	}

	/**
	 * Update order currency.
	 *
	 * The function should be called after nonce verification.
	 * We have to get the raw data from global variable, though.
	 *
	 * @param int|null $id The id of order.
	 */
	public function update_order_currency( $id ) {
		if ( isset( $_POST['p24_order_currency'] ) ) {
			$currency  = sanitize_text_field( wp_unslash( $_POST['p24_order_currency'] ) ); // WPCS: CSRF ok.
			$available = $this->get_available_currencies();
			if ( in_array( $currency, $available, true ) ) {
				update_metadata( 'post', $id, '_order_currency', $currency );
			}
		}
	}

	/**
	 * Register widget for multi currency.
	 */
	public function register_widget() {
		$widget = new P24_Currency_Selector_Widget( $this->plugin_core );
		register_widget( $widget );
	}

	/**
	 * Bind events to use multi currency.
	 */
	public function bind_events() {
		add_action( 'wp_ajax_p24_change_currency', [ $this, 'admin_ajax_change_currency' ] );
		add_filter( 'przelewy24_multi_currency_options', [ $this, 'get_available_currencies' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'add_user_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'add_admin_scripts' ] );
		add_action( 'widgets_init', [ $this, 'register_widget' ] );

		if ( $this->plugin_core->is_in_user_mode() ) {
			add_filter( 'woocommerce_currency', [ $this, 'try_change_default_currency' ] );
			add_filter( 'wc_get_price_thousand_separator', [ $this, 'try_change_thousand_separator' ] );
			add_filter( 'wc_get_price_decimal_separator', [ $this, 'try_change_decimal_separator' ] );
			add_filter( 'wc_get_price_decimals', [ $this, 'try_change_decimals' ] );
			add_filter( 'option_woocommerce_currency_pos', [ $this, 'try_change_currency_pos' ] ); /* Core event. */
			add_action( 'wp_loaded', [ $this, 'save_currency_to_session' ] );
			add_filter( 'woocommerce_package_rates', [ $this, 'update_package_rates' ] );
		}

		add_filter( 'woocommerce_product_class', [ $this, 'find_product_class' ], 10, 4 );
		add_action( 'woocommerce_before_order_object_save', [ $this, 'before_order_save' ] );
		add_action( 'woocommerce_before_order_refund_object_save', [ $this, 'before_order_save' ], 10, 2 );
		add_action( 'woocommerce_process_shop_order_meta', [ $this, 'update_order_currency' ] );
		add_filter( 'woocommerce_get_variation_prices_hash', [ $this, 'filter_price_hash' ], 10, 3 );
		add_filter( 'przelewy24_multi_currency_admin_currency', [ $this, 'try_change_default_currency' ] );
		add_filter( 'woocommerce_reports_get_order_report_query', [ $this, 'sql_override' ], 10, 1 );
	}
}
