<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/stratos-vetsos-08262473/
 * @since      1.0.0
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/public
 * @author     FullStack <vetsos.s@gmail.com>
 */
class Wc_Smart_Cod_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $has_cod_available;
	private $reason;

	public function __construct( $plugin_name ) {

		$this->plugin_name = $plugin_name;
		$this->version = SMART_COD_VER;
		$this->cod_settings = array();

		$this->cart_products =
		$this->settings_analyzed =
		$this->nocharge_amount_mode = false;

		if( is_admin() )
			return;

		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'apply_smart_cod_settings' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_smart_cod_fees' ) );
		add_action( 'woocommerce_update_order_review_fragments', array( $this, 'apply_custom_message' ) );

	}

	protected function analyze_settings() {

		$cod_settings = $this->cod_settings;
		$restriction_settings = array_key_exists( 'restriction_settings', $cod_settings ) ? json_decode( $cod_settings[ 'restriction_settings' ], true ) : array();
		$fee_settings = array_key_exists( 'fee_settings', $cod_settings ) ? json_decode( $cod_settings[ 'fee_settings' ], true ) : array();

		$restriction_modes = wp_parse_args( $restriction_settings, array(
			'role_restriction' => 0,
			'amount_restriction' => 0,
			'shipping_zone_restrictions' => 0,
			'shipping_zone_method_restriction' => 0,
			'country_restrictions' => 0,
			'state_restrictions' => 0,
			'restrict_postals' => 0,
			'city_restrictions' => 0,
			'product_restriction' => 0,
			'category_restriction' => 0,
			'shipping_class_restriction' => 0
		));

		$restriction_settings = array(
			'includes' => array(),
			'excludes' => array()
		);

		foreach( $restriction_modes as $k => $v ) {

			if( ! array_key_exists( $k, $cod_settings ) ) {
				unset( $restriction_modes[ $k ] );
				continue;
			}

			if( $cod_settings[ $k ] === '' || ( is_array( $cod_settings[ $k ] ) && empty( $cod_settings[ $k ] ) ) ) {
				unset( $restriction_modes[ $k ] );
				continue;
			}

			$key = $v === 0 ? 'excludes' : 'includes';
			if( $k === 'nocharge_amount' ) {
				$this->nocharge_amount_mode = $key;
			}
			else if( $k === 'product_restriction' || $k === 'category_restriction' || $k === 'shipping_class_restriction' ) {
				$restriction_settings[ $key ][ $k ] = array(
					'value' => $cod_settings[ $k ],
					'mode' =>  $cod_settings[ $k . '_mode' ]
				);
			}
			else {
				$restriction_settings[ $key ][ $k ] = $cod_settings[ $k ];
			}
		}

		if( $this->has_native_zone_method() ) {
			// native wc setting
			// enabled ignore ours
			unset( $restriction_settings[ 'shipping_zone_method_restriction' ] );
		}

		$this->restriction_settings = $restriction_settings;
		$this->fee_settings = $this->analyze_fee_settings( $fee_settings );

	}

	protected function analyze_fee_settings( $fee_settings ) {

		$fee_table = array(
			'check_overthelimit' => array(),
			'check_country' => array(),
			'check_zoneandmethod' => array(),
			'check_zone' => array(),
			'check_method' => array(),
			'check_normal_fee' => array()
		);

		foreach( $this->cod_settings as $k => $v ) {

			if( ( $k === 'extra_fee' || $k === 'nocharge_amount' || strpos( $k, 'different_charge' ) !== false ) && is_numeric( $v ) ) {

				$value = array(
					'fee' => $v,
					'type' => array_key_exists( $k, $fee_settings ) && in_array( $fee_settings[ $k ], array( 'fixed', 'percentage' ) ) ? $fee_settings[ $k ] : 'fixed',
					'key' => $k
				);

				if( $k === 'extra_fee' ) {
					array_push( $fee_table[ 'check_normal_fee' ], $value );
				}
				else if( $k === 'nocharge_amount' ) {
					array_push( $fee_table[ 'check_overthelimit' ], $value );
				}
				else {
					$key = explode( 'different_charge', $k );
					$key = $key[ 0 ];

					switch( $key ) {

						case '': {
							array_push( $fee_table[ 'check_zone' ], $value );
							break;
						}

						case 'zonemethod_': {
							array_push( $fee_table[ 'check_zoneandmethod' ], $value );
							break;
						}

						case 'include_country_': {
							array_push( $fee_table[ 'check_country' ], $value );
							break;
						}

						case 'method_': {
							array_push( $fee_table[ 'check_method' ], $value );
							break;
						}

					}
				}
			}
		}

		return array_filter( array_map( 'array_filter', $fee_table ) );

	}

	protected function has_native_zone_method() {
		return Wc_Smart_Cod::wc_version_check()
		&& isset( $this->cod_settings[ 'enable_for_methods' ] )
		&& ! empty( $this->cod_settings[ 'enable_for_methods' ] );
	}

	public function get_cod_message( $reason, $settings ) {

		if( ! isset( $settings[ 'cod_unavailable_message' ] ) ) {
			return false;
		}

		$messages = $settings[ 'cod_unavailable_message' ];

		if( ! is_array( $messages ) ) {
			// backwards compatibility
			// before 1.4.4
			if( trim( $messages ) !== '' ) {
				return $messages;
			}
			else {
				return false;
			}

		}

		if( ! $reason ) {
			if( array_key_exists( 'generic', $messages ) && trim( $messages[ 'generic' ] ) !== '' ) {
				return $messages[ 'generic' ];
			}
		}

		if( $reason === 'restrict_postals' ) {
			$reason = 'postal';
		}
		else {
			// extract _restriction
			$reason = substr( rtrim( $reason, 's' ), 0, -12 );
		}

		if( ! array_key_exists( $reason, $messages ) || trim( $messages[ $reason ] ) === '' ) {
			if( array_key_exists( 'generic', $messages ) && trim( $messages[ 'generic' ] ) !== '' ) {
				return $messages[ 'generic' ];
			}
		}
		else {
			return $messages[ $reason ];
		}

		return false;

	}

	public function init_wsc_settings() {

		if( ! $this->settings_analyzed ) {
			$this->get_cod_settings();
			$this->analyze_settings();
			$this->settings_analyzed = true;
		}

	}

	public function apply_custom_message( $data ) {

		$this->init_wsc_settings();
		if( $this->has_cod_available() === false ) {

			$settings = $this->get_cod_settings();
			$message = $this->get_cod_message( $this->reason, $settings );

			if( $message ) {

				$doc = new DOMDocument();
				$doc->loadHTML( mb_convert_encoding( $data[ '.woocommerce-checkout-payment' ], 'HTML-ENTITIES', 'UTF-8' ) );
				$doc->preserveWhiteSpace = false;
				$payment_div = $doc->getElementById( 'payment' );
				if( $payment_div ) {
					$fragment = $doc->createDocumentFragment();
					$fragment->appendXML( '<div class="woocommerce-info cod-unavailable">' . $message . '</div>' );
					if( $payment_div->hasChildNodes() ) {
						$first_element = $payment_div->childNodes->item( 0 );
						$first_element->parentNode->insertBefore( $fragment, $first_element );
					} else {
						$payment_div->appendChild( $fragment );
					}

					$doc->removeChild( $doc->doctype );
					$doc->replaceChild( $doc->firstChild->firstChild->firstChild, $doc->firstChild );

					$data[ '.woocommerce-checkout-payment' ] = $doc->saveHTML();
				}

			}

		}

		return $data;

	}

	private function get_cod_settings() {

		if( ! empty ( $this->cod_settings ) )
			return $this->cod_settings;

		return $this->cod_settings = get_option( 'woocommerce_cod_settings' );

	}

	private function is_new_wc() {
		return class_exists( 'WC_Shipping_Zones' );
	}

	private function get_customer_shipping_zone( $cart ) {

		$package = $cart->get_shipping_packages();

		if( ! is_array( $package ) || ! isset( $package[0] ) ) {
			return false;
		}

		$package = $package[0];
		$customer_shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $package );

		return $customer_shipping_zone->get_id();

	}

	private function get_customer_shipping_method( $inside_zone = false, $full = false ) {

		global $woocommerce;

		$packages = WC()->shipping->get_packages();
		$chosen_rate = isset( $_POST[ 'shipping_method' ] ) ? $_POST[ 'shipping_method' ] : false;

		if( ! $chosen_rate ) {
			$chosen_rate = WC()->session->get( 'chosen_shipping_methods' );
		}

		if( ! $chosen_rate ) {
			return false;
		}

		$chosen_rate = $chosen_rate[0];
		$id = $this->get_method_id( $chosen_rate, $inside_zone, $packages );

		if( $id === false ) {
			// check again for a possible
			// rate change with cached rate
			$chosen_rate = WC()->session->get( 'chosen_shipping_methods' );
			$chosen_rate = $chosen_rate[ 0 ];
			$id = $this->get_method_id( $chosen_rate, $inside_zone, $packages );
		}

		return $full ? $chosen_rate : $id;

	}

	protected function get_method_id( $chosen_rate, $inside_zone, $packages ) {

		foreach ( $packages as $i => $package ) {
			if( isset( $package[ 'rates' ][ $chosen_rate ] ) ) {

				if( ! $inside_zone ) {
					return $package[ 'rates' ][ $chosen_rate ]->method_id;
				}
				else {
					return $package[ 'rates' ][ $chosen_rate ]->instance_id;
				}
			}
		}

		return false;
	}

	public function apply_smart_cod_fees( WC_Cart $cart, $apply_fee = true ) {

		if( $apply_fee && ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		$this->init_wsc_settings();

		$payment_gateway = isset( $_POST[ 'payment_method' ] ) && $_POST[ 'payment_method' ] === 'cod' ? 'cod' : '';

		if( ! $payment_gateway ) {

			$payment_gateway = WC()->session->get( 'chosen_payment_method' );

			// WooCommerce issue
			// when it's only
			// one gateway

			if( ! $payment_gateway ) {

				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
				if( ! empty( $available_gateways ) && current( array_keys( $available_gateways ) ) === 'cod' ) {
					$payment_gateway = 'cod';
				}

			}

		}

		if( ( $payment_gateway !== 'cod' || $this->has_cod_available() === false ) && $apply_fee )
			return;

		global $woocommerce;
		$cart = $woocommerce->cart;
		$settings = $this->get_cod_settings();
		$is_new_wc = $this->is_new_wc();
		$rounding = array_key_exists( 'percentage_rounding', $settings ) && in_array( $settings[ 'percentage_rounding' ], array( 'round_up', 'round_down' ) ) ? $settings[ 'percentage_rounding' ] : 'round_up';
		$has_tax = false;
		if( isset( $settings[ 'extra_fee_tax' ] ) && $settings[ 'extra_fee_tax' ] === 'enable' ) {
			$has_tax = true;
		}
		$extra_fee = 0;

		// check for restrictions and policies

		foreach( $this->fee_settings as $condition => $group ) {

			foreach( $group as $fee ) {

				if( ! $is_new_wc && ( $condition === 'check_zoneandmethod' || $condition === 'check_zone' ) )
					continue;

				if( is_numeric( $extra_fee = $this->{$condition}( $fee, $cart ) ) ) {
					if( $fee[ 'type' ] === 'percentage' ) {
						$extra_fee = $this->calculate_percentage( $extra_fee, $cart, $rounding );
					}
					break 2;
				}

			}

		}

		$extra_fee = apply_filters( 'wc_smart_cod_fee', is_numeric( $extra_fee ) ? $extra_fee : 0, $this->fee_settings );
		if( $apply_fee ) {
			$woocommerce->cart->add_fee( apply_filters( 'wc_smart_cod_fee_title', __( 'Cash on delivery', 'woocommerce' ) ), $extra_fee, $has_tax );
		}
		else {
			return $extra_fee;
		}

	}

	protected function get_actual_total( $cart = false ) {

		if( ! $cart ) {
			global $woocommerce;
			$cart = $woocommerce->cart;
		}

		$total = $cart->cart_contents_total;
		if( ! $total ) return false;

		$settings = $this->get_cod_settings();
		if( array_key_exists( 'cart_amount_mode', $settings ) && ! empty( $settings[ 'cart_amount_mode' ] ) ) {
			if( wc_tax_enabled() ) {
				if( in_array( 'tax', $settings[ 'cart_amount_mode' ] ) ) {
					$taxes =  $cart->get_taxes();
					foreach( $taxes as $tax ) {
						$total = $total + $tax;
					}
				}
			}
			if( in_array( 'shipping', $settings[ 'cart_amount_mode' ] ) ) {
				$total = $total + floatval( $cart->shipping_total );
			}
		}

		return $total;

	}

	protected function calculate_percentage( $percentage, $cart, $rounding ) {

		$total = $cart->total;

		if( ! $total ) {
			$total = $cart->cart_contents_total;
		}

		$extra_fee = ( $percentage * $total ) / 100;
		return $rounding === 'round_up' ? ceil( $extra_fee ) : floor( $extra_fee );

	}

	protected function get_cart_products( $cart ) {

		if( $this->cart_products ) {
			return $this->cart_products;
		}

		$items = $cart->get_cart();
		$products = array();
		foreach( $items as $key => $item ) {
			$id = isset( $item[ 'variation_id' ] ) && $item[ 'variation_id' ] !== 0 ? $item[ 'variation_id' ] : $item[ 'product_id' ];
			array_push( $products, $id );
        }
		return $this->cart_products = $products;
	}

	public function has_cod_available() {

		$has_cod_available = true;

		foreach( $this->restriction_settings[ 'includes' ] as $key => $value ) {
			$has_cod_available = $this->{ 'check_' . $key }( $value, true, $has_cod_available );
			if( ! $has_cod_available ) {
				$this->reason = $key;
				break;
			}
		}

		if( $has_cod_available ) {

			foreach( $this->restriction_settings[ 'excludes' ] as $key => $value ) {
				$has_cod_available = $this->{ 'check_' . $key }( $value, false, $has_cod_available );
				if( ! $has_cod_available ) {
					$this->reason = $key;
					break;
				}
			}

		}

		return $this->has_cod_available = apply_filters( 'wc_smart_cod_available', $has_cod_available, $this->restriction_settings );

	}

	/**
	 * Check cod availability
	 * begin
	 */

	protected function check_shipping_zone_restrictions( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$cart = $woocommerce->cart;
		$package = $cart->get_shipping_packages();

		if( ! is_array( $package ) || ! isset( $package[0] ) ) {
			return $has_cod_available;
		}

		$package = $package[0];
		$customer_shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $package );

		if( ! $customer_shipping_zone ) {
			return $has_cod_available;
		}

		if( $enable ) {
			if( in_array( $customer_shipping_zone->get_id(), $restriction ) ) {
				return true;
			}
			return false;
		}
		else {
			if( in_array( $customer_shipping_zone->get_id(), $restriction ) ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_shipping_zone_method_restriction( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$cart = $woocommerce->cart;
		$customer_shipping_zone = $this->get_customer_shipping_zone( $cart );
		$customer_shipping_zone_method = $this->get_customer_shipping_method( true );

		if( $customer_shipping_zone_method === false || $customer_shipping_zone === false ) {
			return $has_cod_available;
		}

		$needle = $customer_shipping_zone . '_' . $customer_shipping_zone_method;

		if( $enable ) {
			if( in_array( $needle, $restriction ) ) {
				return true;
			}
			return false;
		}
		else {
			if( in_array( $needle, $restriction ) ) {
				return false;
			}
		}

		return $has_cod_available;
	}

	protected function check_restrict_postals( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$postals = explode( ',', trim( $restriction ) );
		$postals = array_map( 'trim', $postals );
		$customer_post_code = $woocommerce->customer->get_shipping_postcode();

		if( ! $customer_post_code ) {
			return $has_cod_available;
		}

		foreach( $postals as $p ) {
			if( ! $p ) {
				continue;
			}
			$prepare = explode( '...', $p );
			$count = count( $prepare );
			if( $count === 1 ) {
				// single
				if( $prepare[0] === $customer_post_code ) {
					return $enable ? true : false;
				}

			}
			else if( $count === 2 ) {
				// range
				if( ! is_numeric( $prepare[0] ) || ! is_numeric( $prepare[1] ) || ! is_numeric( $customer_post_code ) ) {
					continue;
				}

				if( $customer_post_code >= $prepare[0] && $customer_post_code <= $prepare[1] ) {
					return $enable ? true : false;
				}

			}
			else {
				continue;
			}

		}

		if( $enable ) {
			return false;
		}

		return $has_cod_available;

	}

	protected function check_city_restrictions( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$customer_city = $woocommerce->customer->get_shipping_city();

		if( ! $customer_city ) {
			return $has_cod_available;
		}

		$customer_city = trim( $customer_city );

		$restriction = explode( ',', trim( $restriction ) );
		$restriction = array_map( 'trim', $restriction );
		$restriction = array_map( 'strtolower', $restriction );

		if( $enable ) {
			if( in_array( strtolower( $customer_city ), $restriction ) ) {
				return true;
			}
			return false;
		}
		else {
			if( in_array( strtolower( $customer_city ), $restriction ) ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_country_restrictions( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$customer_country = $woocommerce->customer->get_shipping_country();

		if( ! $customer_country ) {
			return $has_cod_available;
		}

		if( $enable ) {
			if( in_array( $customer_country, $restriction ) ) {
				return true;
			}
			return false;
		}
		else {
			if( in_array( $customer_country, $restriction ) ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_state_restrictions( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$customer_country = $woocommerce->customer->get_shipping_country();
		$customer_state = $woocommerce->customer->get_shipping_state();
		$needle = $customer_country . '_' . $customer_state;

		if( ! $customer_country || ! $customer_state ) {
			return $has_cod_available;
		}

		if( $enable ) {
			if( in_array( $needle, $restriction ) ) {
				return true;
			}
			return false;
		}
		else {
			if( in_array( $needle, $restriction ) ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_cart_amount_restriction( $restriction, $enable, $has_cod_available ) {

		$total = $this->get_actual_total();

		if( ! $total ) return $has_cod_available;

		if( $enable ) {
			if( $total < $restriction ) {
				return false;
			}
		}
		else {
			if( $total >= $restriction ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_product_restriction( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$cart = $woocommerce->cart;

		$cart_products = $this->get_cart_products( $cart );

		if( empty( $cart_products ) )
			return false;

		$product_count = count( $cart_products );
		$restrict_count = 0;

		foreach( $cart_products as $product_id ) {

			if( in_array( $product_id, $restriction[ 'value' ] ) ) {
				if( $restriction[ 'mode' ] === 'one_product' ) {
					return $enable ? true : false;
				}
				else {
					$restrict_count++;
				}
			}
			else {
				if ( $restriction[ 'mode' ] === 'all_products' ) {
					return $enable ? false : true;
				}
			}

		}

		if( $restriction[ 'mode' ] === 'all_products' ) {
			if( $restrict_count === $product_count ) {
				return $enable ? true : false;
			}
			else {
				return $enable ? false : true;
			}
		}
		else {
			if( $enable ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_category_restriction( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$cart = $woocommerce->cart;

		$cart_products = $this->get_cart_products( $cart );

		if( empty( $cart_products ) )
			return false;

		$product_count = count( $cart_products );
		$restrict_count = 0;

		foreach( $cart_products as $product_id ) {

			$_product = wc_get_product( $product_id );
			$type = $_product->get_type();
			if( $type === 'variation' ) {
				$_product = wc_get_product( $_product->get_parent_id() );
			}
			$category_ids = $_product->get_category_ids();

			if( array_intersect( $category_ids, $restriction[ 'value' ] ) ) {
				if( $restriction[ 'mode' ] === 'one_product' ) {
					return $enable ? true : false;
				}
				else {
					$restrict_count++;
				}
			}
			else {
				if ( $restriction[ 'mode' ] === 'all_products' ) {
					return $enable ? false : true;
				}
			}

		}

		if( $restriction[ 'mode' ] === 'all_products' ) {
			if( $restrict_count === $product_count ) {
				return $enable ? true : false;
			}
			else {
				return $enable ? false : true;
			}
		}
		else {
			if( $enable ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_shipping_class_restriction( $restriction, $enable, $has_cod_available ) {

		global $woocommerce;
		$cart = $woocommerce->cart;

		$cart_products = $this->get_cart_products( $cart );

		if( empty( $cart_products ) )
			return false;

		$product_count = count( $cart_products );
		$restrict_count = 0;

		foreach( $cart_products as $product_id ) {
			$product = wc_get_product( $product_id );
			$shipping_class_id = $product->get_shipping_class_id();
			if( in_array( $shipping_class_id, $restriction[ 'value' ] ) ) {
				if( $restriction[ 'mode' ] === 'one_product' ) {
					return $enable ? true : false;
				}
				else {
					$restrict_count++;
				}
			}
			else {
				if ( $restriction[ 'mode' ] === 'all_products' ) {
					return $enable ? false : true;
				}
			}

		}

		if( $restriction[ 'mode' ] === 'all_products' ) {
			if( $restrict_count === $product_count ) {
				return $enable ? true : false;
			}
			else {
				return $enable ? false : true;
			}
		}
		else {
			if( $enable ) {
				return false;
			}
		}

		return $has_cod_available;

	}

	protected function check_user_role_restriction( $restriction, $enable, $has_cod_available ) {

		if( is_user_logged_in() ) {
			$user = wp_get_current_user();
		}
		else {
			$user = new stdClass();
			$user->roles = array( 'guest' );
		}

		if( $enable ) {
			if( array_intersect( $restriction, $user->roles ) ) {
				return true;
			}
			return false;
		}
		else {
			if( array_intersect( $restriction, $user->roles ) ) {
				return false;
			}
		}

		return $has_cod_available;
	}

	protected function check_method_restriction() {

		$settings = $this->get_cod_settings();

		if( isset( $settings[ 'enable_for_methods' ] ) ) {
			if( ! empty( $settings[ 'enable_for_methods' ] ) ) {

				if( Wc_Smart_Cod::wc_version_check() ) {
					// over 3.4, now woocommerce
					// supports natively shipping
					// zone method restriction
					$method = $this->get_customer_shipping_method( true, true );

					if( ! $method ) {
						return true;
					}
					if( ! in_array( $method, $settings[ 'enable_for_methods' ] ) ) {
						return false;
					}
				} else {
					$method = $this->get_customer_shipping_method();
					if( ! $method ) {
						return true;
					}
					if( ! in_array( $method, $settings[ 'enable_for_methods' ] ) ) {
						return false;
					}
				}

			}
		}

		return true;

	}

	/**
	 * Check cod availability
	 * end
	 */

	public function apply_smart_cod_settings( $available_gateways ) {

		if( ! function_exists( 'is_checkout' ) || ! is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) )
			return $available_gateways;

		$this->init_wsc_settings();

		if( $this->has_cod_available() === false ) {
			unset( $available_gateways[ 'cod' ] );
		}

		return $available_gateways;

	}

	/**
	 * Check extra fee
	 * start
	 */

	private function check_country( $settings, $cart ) {

		// check if we have a specific
		// country amount set. ( include mode )

		$extra_fee = false;
		$key = $settings[ 'key' ];
		global $woocommerce;
		$customer_country = $woocommerce->customer->get_shipping_country();

		if( $key === 'include_country_different_charge_' . $customer_country ) {
			$extra_fee = $settings[ 'fee' ];
		}

		return $extra_fee;

	}

	private function check_overthelimit( $settings, $cart ) {

		// check if customer is over the limit ( if any )
		// and charge him nothing

		$extra_fee = false;
		$key = $settings[ 'key' ];

		if( ! $this->nocharge_amount_mode ) {
			$this->nocharge_amount_mode = 'excludes';
		}

		if( $key === 'nocharge_amount' ) {

			$total = $this->get_actual_total();
			if( $this->nocharge_amount_mode === 'excludes' ) {
				if( $total >= $settings[ 'fee' ] ) {
					$extra_fee = 0;
				}
			}
			else if( $this->nocharge_amount_mode === 'includes' ) {
				if( $total < $settings[ 'fee' ] ) {
					$extra_fee = 0;
				}
			}
		}

		return $extra_fee;

	}

	private function check_zoneandmethod( $settings, $cart ) {

		// check for specific shipping zones
		// & methods different charges

		$extra_fee = false;
		$key = $settings[ 'key' ];
		$zone_id = $this->get_customer_shipping_zone( $cart );
		$shipping_method = $this->get_customer_shipping_method( true );

		if( $shipping_method === false || $zone_id === false )
			return $extra_fee;

		if( $key === 'zonemethod_different_charge_' . $zone_id . '_method_' . $shipping_method ) {
			$extra_fee = $settings[ 'fee' ];
		}

		return $extra_fee;
	}

	private function check_zone( $settings, $cart ) {

		// check for specific shipping zones
		// different charges

		$extra_fee = false;
		$key = $settings[ 'key' ];
		$zone_id = $this->get_customer_shipping_zone( $cart );

		if( $zone_id === false ) {
			return $extra_fee;
		}

		if( $key === 'different_charge_' . $zone_id ) {
			$extra_fee = $settings[ 'fee' ];
		}

		return $extra_fee;

	}

	private function check_method( $settings, $cart ) {

		// check for specific shipping methods
		// different charges

		$extra_fee = false;
		$key = $settings[ 'key' ];
		$shipping_method = $this->get_customer_shipping_method();

		if( ! $shipping_method )
			return $extra_fee;

		if( $key === 'method_different_charge_' . $shipping_method ) {
			$extra_fee = $settings[ 'fee' ];
		}

		return $extra_fee;

	}

	private function check_normal_fee( $settings, $cart ) {

		$extra_fee = 0;
		$key = $settings[ 'key' ];

		if( $key === 'extra_fee' ) {
			$extra_fee = $settings[ 'fee' ];
		}

		return $extra_fee;

	}

	/**
	 * Check extra fee
	 * end
	 */

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Smart_Cod_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Smart_Cod_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if( function_exists( 'is_checkout' ) && is_checkout() )
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-smart-cod-public.js', array( 'jquery' ), $this->version, false );

	}

}
