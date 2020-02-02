<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/stratos-vetsos-08262473/
 * @since      1.0.0
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/admin
 * @author     FullStack <vetsos.s@gmail.com>
 */
class Wc_Smart_Cod_Admin extends WC_Gateway_COD {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {

		parent::__construct();
		$this->plugin_name = 'wc-smart-cod';
		$this->version = SMART_COD_VER;
		$this->new_wc = false;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_settings_api_form_fields_cod', array( $this, 'extend_cod' ) );
		add_action( 'woocommerce_settings_api_sanitized_fields_cod', array( $this, 'clean_up_settings' ) );
		add_action( 'woocommerce_delete_shipping_zone', array( $this, 'clean_up_gateway' ) );
	}

	public static function ajax_search_categories() {

		check_ajax_referer( 'search-categories', 'security' );

		$term = wc_clean( empty( $term ) ? stripslashes( $_GET[ 'term' ] ) : $term );

		if ( empty( $term ) ) {
			wp_die();
		}

		$categories = get_terms( 'product_cat', array(
			'hide_empty' => false,
			'search' => $term
		));

		$response = array();
		if( is_array( $categories ) && ! empty( $categories ) ) {
			foreach( $categories as $category ) {
				$response[ $category->term_id ] = $category->name;
			}
		}

		wp_send_json( $response );

		die();

	}

	protected function has_native_zone_method() {
		return Wc_Smart_Cod::wc_version_check()
		&& isset( $this->settings[ 'enable_for_methods' ] )
		&& ! empty( $this->settings[ 'enable_for_methods' ] );
	}

	protected function get_selected_products( $settings ) {

		global $wpdb;

		$products = $settings[ 'product_restriction' ];
		$placeholders = array_fill( 0, count( $products ), '%s' );
		$format = implode(', ', $placeholders );
		$query = "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.ID IN ( $format )";

		$_products = $wpdb->get_results(
			 $wpdb->prepare( $query, $products )
		);

		$response = array();
		foreach( $_products as $product ) {
			if( ( $key = array_search( $product->ID, $products ) ) !== false ) {
				unset( $products[ $key ] );
			}
			$response[ $product->ID ] = $product->post_title . ' (#' . $product->ID . ')';
		}
		/**
		 * check for deleted products
		 */

		if( ! empty( $products ) ) {
			foreach( $products as $product ) {
				if( ( $key = array_search( $product, $settings[ 'product_restriction' ] ) ) !== false ) {
					unset( $settings[ 'product_restriction' ][ $key ] );
				}
			}

			$settings[ 'product_restriction' ] = array_values( $settings[ 'product_restriction' ] );
			update_option( 'woocommerce_cod_settings', $settings );
			$this->settings = $settings;

		}

		return $response;

	}

	protected function get_selected_categories( $settings ) {

		global $wpdb;

		$categories = $settings[ 'category_restriction' ];

		$_categories = get_terms( 'product_cat', array(
			'hide_empty' => false,
			'include' => $settings[ 'category_restriction' ]
		));

		$response = array();
		foreach( $_categories as $category ) {
			if( ( $key = array_search( $category->term_id, $categories ) ) !== false ) {
				unset( $categories[ $key ] );
			}
			$response[ $category->term_id ] = $category->name;
		}

		/**
		 * check for deleted categories
		 */

		 if( ! empty( $categories ) ) {
	 		foreach( $categories as $category ) {
	 			if( ( $key = array_search( $category, $settings[ 'category_restriction' ] ) ) !== false ) {
	 				unset( $settings[ 'category_restriction' ][ $key ] );
	 			}
	 		}

	 		$settings[ 'category_restriction' ] = array_values( $settings[ 'category_restriction' ] );
	 		update_option( 'woocommerce_cod_settings', $settings );
			$this->settings = $settings;

	 	}

		return $response;

	}

	protected function get_product_categories() {

		$product_categories = get_terms( 'product_cat', array(
			'hide_empty' => false
		));

		if( ! $product_categories || ! is_array( $product_categories ) || empty( $product_categories ) )
			return false;

		$response = array();

		foreach( $product_categories as $cat ) {
			$response[ $cat->term_id ] = $cat->name;
		}

		return $response;

	}

	public function migrate_shipping_zone_methods( $unset_array ) {

		foreach( $unset_array as $key ) {
			unset( $this->settings[ $key ] );
		}

	}

	private function update_wc_smart_cod( $settings, $restriction_settings ) {

		$mode = $settings[ 'restriction_mode' ];
		$old_conditional = array( 'shipping_zone_restrictions', 'country_restrictions', 'restrict_postals' );

		foreach( $old_conditional as $key ) {

			if( $mode === 'include' ) {
				if( array_key_exists( $key, $settings ) ) {
					unset( $settings[ $key ] );
				}
				if( array_key_exists( 'include_' . $key, $settings ) ) {
					$settings[ $key ] = $settings[ 'include_' . $key ];
					unset( $settings[ 'include_' . $key ] );
				}
				$restriction_settings[ $key ] = 1;
			}
			else {
				if( array_key_exists( 'include_' . $key, $settings ) ) {
					unset( $settings[ 'include_' . $key ] );
				}
				$restriction_settings[ $key ] = 0;
			}

		}

		unset( $settings[ 'restriction_mode' ] );
		update_option( 'woocommerce_cod_settings', $settings );
		$this->settings = $settings;

		return array(
			'restriction_settings' => $restriction_settings,
			'settings' => $settings
		);

	}

	protected function analyze_fields( $form_fields, $settings, $restriction_settings, $old_wc_smart_cod ) {

		$fields = array();
		$fee_settings = array_key_exists( 'fee_settings', $settings ) ? $settings[ 'fee_settings' ] : false;
		$fee_settings = $fee_settings ? json_decode( $fee_settings, true ) : array();
		$update_fee = $update_restriction = $needs_update = false;

		foreach( $form_fields as $key => $field ) {

			if( ! array_key_exists( 'class', $field ) ) {
				$fields[ $key ] = $field;
				continue;
			}

			$class_arr = explode( ' ', $field[ 'class' ] );

			if( in_array( 'wc-smart-cod-restriction', $class_arr ) ) {
				// determine include or exclude
				$mode = 'disable';

				if( array_key_exists( $key, $restriction_settings ) ) {
					if( $restriction_settings[ $key ] === 1 ) {
						$form_fields[ $key ][ 'title' ] = str_replace( 'Disable', 'Enable', $form_fields[ $key ][ 'title' ] );
						$mode = 'enable';
					}
				}
				else {
					// first run after update
					// to wc-smart-cod 1.4
					$restriction_settings[ $key ] = 0;
					$update_restriction = true;
				}

				if( ! array_key_exists( 'custom_attributes', $form_fields[ $key ] ) ) {
					$form_fields[ $key ][ 'custom_attributes' ] = array();
				}

				$form_fields[ $key ][ 'custom_attributes' ][ 'data-mode' ] = $mode;

			}

			if( in_array( 'wc-smart-cod-percentage', $class_arr ) ) {

				// determine fixed price
				// or percentage

				if( ! array_key_exists( $key, $fee_settings ) ) {
					$fee_settings[ $key ] = 'fixed';
					$update_fee = true;
				}

			}

		}

		if( $update_restriction ) {
			// transition to wc-smart-cod 1.4
			$settings[ 'restriction_settings' ] = json_encode( $restriction_settings );
			$needs_update = true;
		}

		if( $update_fee ) {
			$settings[ 'fee_settings' ] = json_encode( $fee_settings );
			$needs_update = true;
		}

		if( $needs_update ) {
			update_option( 'woocommerce_cod_settings', $settings );
			$this->settings = $settings;
		}

		$this->restriction_settings = $restriction_settings;
		$this->fee_settings = $fee_settings;

		return $form_fields;

	}

	public function extend_cod( $form_fields ) {
		$this->prepared_fields = $this->get_prepared_fields();
		return array_merge( $form_fields, $this->prepared_fields );
	}

	protected function prepare_states( $countries, $states ) {
		$prepared = array();
		$states = array_filter( array_map( 'array_filter', $states ) );

		foreach( $states as $country_key => $country ) {

			foreach( $country as $key => $state ) {
				if( ! array_key_exists( $country_key, $countries ) ) continue;
				$prepared[ $country_key . '_'. $key ] = $countries[ $country_key ] . ' &mdash; ' . $state;
			}

		}
		return $prepared;
	}

	protected function prepare_user_roles() {

		global $wp_roles;
		$prepared = array();
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		foreach( $wp_roles->roles as $key => $role ) {
			$prepared[ $key ] = $role[ 'name' ];
		}

		$prepared[ 'guest' ] = 'Guest (Customer without account)';

		return $prepared;
	}

	protected function prepare_shipping_classes() {
		global $woocommerce;
		$prepared = array();
		$shipping_classes = $woocommerce->shipping->get_shipping_classes();

		foreach( $shipping_classes as $key => $class ) {
			$prepared[ $class->term_id ] = $class->name;
		}

		return $prepared;
	}

	public function get_prepared_fields() {

		$this->new_wc = class_exists( 'WC_Shipping_Zones' ) ? true : false;
		$existing_settings = $this->settings;
		$existing_zone_restrictions = array();
		$countries = new WC_Countries;
		$states = $countries->get_allowed_country_states();
		$countries = $countries->get_allowed_countries();
		$states = $this->prepare_states( $countries, $states );
		$user_roles = $this->prepare_user_roles();
		$shipping_classes = $this->prepare_shipping_classes();

		$restriction_settings = array_key_exists( 'restriction_settings', $existing_settings ) ? $existing_settings[ 'restriction_settings' ] : false;
		$restriction_settings = $restriction_settings !== false ? json_decode( $restriction_settings, true ) : array();

		$old_wc_smart_cod = array_key_exists( 'restriction_mode', $existing_settings ) ? true : false;

		if( $old_wc_smart_cod ) {
			$updated_data = $this->update_wc_smart_cod( $existing_settings, $restriction_settings );
			$restriction_settings = $updated_data[ 'restriction_settings' ];
			$existing_settings = $updated_data[ 'settings' ];
		}

		$shipping_methods =
		$zone_methods = array();

		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
			$shipping_methods[ $method->id ] = $method->get_method_title();
		}

		$shipping_zones =
		$wc_shipping_zones = array();

		$products =
		$product_cats = array();

		if( array_key_exists( 'product_restriction' , $existing_settings )
			&& is_array( $existing_settings[ 'product_restriction' ] )
			&& ! empty( $existing_settings[ 'product_restriction' ] ) ) {

			$products = $this->get_selected_products( $existing_settings );
		}

		if( array_key_exists( 'category_restriction' , $existing_settings )
			&& is_array( $existing_settings[ 'category_restriction' ] )
			&& ! empty( $existing_settings[ 'category_restriction' ] ) ) {

			$product_cats = $this->get_selected_categories( $existing_settings );
		}

		if( $this->new_wc ) {

			$wc_shipping_zones = WC_Shipping_Zones::get_zones();

			foreach( $wc_shipping_zones as $zone ) {
				$zone_key = array_key_exists( 'id', $zone ) ? 'id' : 'zone_id';
				$shipping_zones[ $zone[ $zone_key ] ] = $zone[ 'zone_name' ];

				$_zone = new WC_Shipping_Zone( $zone[ $zone_key ] );
				$methods = $_zone->get_shipping_methods( true );
				foreach( $methods as $key => $method ) {
					$zone_methods[ $zone[ $zone_key ] . '_' . $key ] = $zone[ 'zone_name' ] . ' &mdash; ' . $method->title;
				}

			}
		}

		if( ! empty( $shipping_zones ) ) {
			$shipping_zones[ 0 ] = __( 'Rest of the World', 'woocommerce' );
		}

		if( $this->new_wc ) {

			$form_fields[ 'shipping_zone_restrictions' ] = array(
				'title' => __( 'Disable on specific shipping zones', 'wc-smart-cod' ),
				'type' => 'multiselect',
				'class' => 'wc-enhanced-select wc-smart-cod-group wc-smart-cod-restriction',
				'description' => __( 'Select the shipping zones you want to restrict the COD method', 'wc-smart-cod' ),
				'options' => $shipping_zones,
				'desc_tip' => true,
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select shipping zones', 'wc-smart-cod' ),
					'data-name' => 'shipping_zone_restrictions'
				)
			);

		}

		$form_fields[ 'country_restrictions' ] = array(
			'title' => __( 'Disable on specific countries', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-enhanced-select wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the countries you want to restrict the COD method', 'wc-smart-cod' ),
			'options' => $countries,
			'desc_tip' => true,
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select Countries', 'wc-smart-cod' ),
				'data-name' => 'country_restrictions'
			)
		);

		$form_fields[ 'state_restrictions' ] = array(
			'title' => __( 'Disable on specific states', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-enhanced-select wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the states you want to restrict the COD method', 'wc-smart-cod' ),
			'options' => $states,
			'desc_tip' => true,
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select States', 'wc-smart-cod' ),
				'data-name' => 'state_restrictions'
			)
		);

		$form_fields[ 'restrict_postals' ] = array(
			'title' => __( 'Disable on specific postal codes', 'wc-smart-cod' ),
			'type' => 'textarea',
			'class' => 'wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Add the postal codes you want to restrict the COD method. Seperate with a comma. You can use fully numeric ranges as well (e.g<code>55133,55134,55400...55600</code>). For ranges use <code>...</code> for delimiter.', 'wc-smart-cod' ),
			'custom_attributes' => array(
				'data-name' => 'restrict_postals'
			)
		);

		$form_fields[ 'city_restrictions' ] = array(
			'title' => __( 'Disable on specific cities', 'wc-smart-cod' ),
			'type' => 'textarea',
			'class' => 'wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Add the cities you want to restrict the COD method. Seperate with a comma. This cannot guarantee the cod restriction at all times, because the city field on checkout is a free text field and the user can make typos or use different characters / spelling for his city.', 'wc-smart-cod' ),
			'custom_attributes' => array(
				'data-name' => 'city_restrictions'
			)
		);

		$form_fields[ 'cart_amount_restriction' ] = array(
			'title' => __( 'Disable if cart amount is greater or equal than', 'wc-smart-cod' ),
			'type' => 'price',
			'class' => 'wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Add a price limit to restrict the COD method, if the customer\'s cart amount is greater or lower than this limit.', 'wc-smart-cod' ),
			'placeholder' => __( 'Enter Amount', 'wc-smart-cod' ),
			'custom_attributes' => array(
				'data-name' => 'cart_amount_restriction'
			)
		);

		$form_fields[ 'user_role_restriction' ] = array(
			'title' => __( 'Disable on specific user roles', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-enhanced-select wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the user roles you want to restrict the COD method', 'wc-smart-cod' ),
			'options' => $user_roles,
			'desc_tip' => true,
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select Roles', 'wc-smart-cod' ),
				'data-name' => 'user_role_restriction'
			)
		);

		$form_fields[ 'category_restriction_mode' ] = array(
			'title' => __( 'Product Category Restriction Mode', 'wc-smart-cod' ),
			'type' => 'radio',
			'parent_class' => 'wc-smart-cod-field inline',
			'class' => 'wc-smart-cod-group',
			'options' => array(
				'one_product' => __( 'At least one product', 'wc-smart-cod' ),
				'all_products'    => __( 'All products', 'wc-smart-cod' ),
			),
			'default' => 'one_product',
			'description' => __( 'Select "at least one", if you want the COD to be restricted when at least one product of the cart belongs to a restricted category. Select "all", if you want the COD to be restricted if all the cart product\'s belongs to restricted categories. Then add the restricted categories in the select field below.', 'wc-smart-cod' )
		);

		$form_fields[ 'category_restriction' ] = array(
			'title' => __( 'Disable on specific product categories', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-smartcod-categories wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the product categories you want to restrict the COD method', 'wc-smart-cod' ),
			'options' => $product_cats,
			'desc_tip' => true,
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select categories', 'wc-smart-cod' ),
				'data-minimum_input_length' => '1',
				'data-name' => 'category_restriction',
				'data-action' => 'wcsmartcod_json_search_categories'
			)
		);

		$form_fields[ 'product_restriction_mode' ] = array(
			'title' => __( 'Product Restriction Mode', 'wc-smart-cod' ),
			'type' => 'radio',
			'parent_class' => 'wc-smart-cod-field inline',
			'class' => 'wc-smart-cod-group',
			'options' => array(
				'one_product' => __( 'At least one product', 'wc-smart-cod' ),
				'all_products'    => __( 'All products', 'wc-smart-cod' ),
			),
			'default' => 'one_product',
			'description' => __( 'Select "at least one", if you want the COD to be restricted when at least one product of the cart is restricted. Select "all", if you want the COD to be restricted if all of the cart\'s product\'s are restricted. Then add the restricted products in the select field below.', 'wc-smart-cod' )
		);

		$form_fields[ 'product_restriction' ] = array(
			'title' => __( 'Disable on specific products', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-smartcod-products wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the products you want to restrict the COD method', 'wc-smart-cod' ),
			'options' => $products,
			'desc_tip' => true,
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select products', 'wc-smart-cod' ),
				'data-action' => 'woocommerce_json_search_products_and_variations',
				'data-minimum_input_length' => '1',
				'data-name' => 'product_restriction'
			)
		);

		$form_fields[ 'shipping_class_restriction_mode' ] = array(
			'title' => __( 'Shipping Class Restriction Mode', 'wc-smart-cod' ),
			'type' => 'radio',
			'parent_class' => 'wc-smart-cod-field inline',
			'class' => 'wc-smart-cod-group',
			'options' => array(
				'one_product' => __( 'At least one product', 'wc-smart-cod' ),
				'all_products'    => __( 'All products', 'wc-smart-cod' ),
			),
			'default' => 'one_product',
			'description' => __( 'Select "at least one", if you want the COD to be restricted when at least one product of the cart belongs to a restricted shipping class. Select "all", if you want the COD to be restricted if all of the cart\'s product\'s belongs to restricted shipping classes. Then add the restricted shipping classes in the select field below.', 'wc-smart-cod' )
		);

		$form_fields[ 'shipping_class_restriction' ] = array(
			'title' => __( 'Disable on specific shipping classes', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-enhanced-select wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the shipping classes you want to restrict the COD method', 'wc-smart-cod' ),
			'options' => $shipping_classes,
			'desc_tip' => true,
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select Shipping Classes', 'wc-smart-cod' ),
				'data-name' => 'shipping_class_restriction'
			)
		);

		$form_fields[ 'shipping_zone_method_restriction' ] = array(
			'title' => __( 'Disable on specific shipping methods of zones', 'wc-smart-cod' ),
			'type' => 'multiselect',
			'class' => 'wc-enhanced-select wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Select the shipping methods of specific zones, you want to restrict the COD method. <strong>Please note that for WooCommerce versions >= 3.4, this is a built in feature, so if you use the WooCommerce setting, this setting will be disabled. Otherwise leave the WooCommerce setting blank and use our setting.</strong>', 'wc-smart-cod' ),
			'options' => $zone_methods,
			'disabled' => !! $this->has_native_zone_method(),
			'custom_attributes' => array(
				'data-placeholder' => __( 'Select Shipping Methods', 'wc-smart-cod' ),
				'data-name' => 'shipping_zone_method_restriction'
			)
		);

		$form_fields[ 'cod_unavailable_message' ] = array(
			'title' => __( 'COD Unavailable Message', 'wc-smart-cod' ),
			'type' => 'cod_messages',
			'class' => 'wc-smart-cod-group',
			'description' => __( 'An informational text to display before the payment methods, when the COD method is not available for a customer. Leave it empty if you don\'t want to use this feature. You can define different messages per reason.', 'wc-smart-cod' )
		);

		$form_fields[ 'extra_fee' ] = array(
			'title' => __( 'Extra Fee', 'wc-smart-cod' ),
			'type' => 'price',
			'class' => 'wc-smart-cod-group wc-smart-cod-percentage',
			'description' => __( 'The extra amount you charging for cash on delivery (leave blank or zero if you don\'t charge extra)', 'wc-smart-cod' ),
			'desc_tip' => true,
			'placeholder' => __( 'Enter Amount', 'wc-smart-cod' )
		);

		$form_fields[ 'percentage_rounding' ] = array(
			'title' => __( 'Percentage Rounding Settings', 'wc-smart-cod' ),
			'type' => 'radio',
			'parent_class' => 'wc-smart-cod-field inline',
			'class' => 'wc-smart-cod-group',
			'options' => array(
				'round_up' => __( 'Round Up', 'wc-smart-cod' ),
				'round_down'    => __( 'Round Down', 'wc-smart-cod' )
			),
			'default' => 'round_up',
			'description' => __( 'Examples: Round up setting will transform 5.345&euro; to 6&euro;<br />Round down will transform it to 5&euro;.', 'wc-smart-cod' ),
			'desc_tip' => false
		);

		$form_fields[ 'extra_fee_tax' ] = array(
			'title' => __( 'Extra Fee Tax', 'wc-smart-cod' ),
			'type' => 'radio',
			'parent_class' => 'wc-smart-cod-field inline',
			'class' => 'wc-smart-cod-group',
			'options' => array(
				'enable' => __( 'Enable', 'wc-smart-cod' ),
				'disable'    => __( 'Disable', 'wc-smart-cod' )
			),
			'default' => 'disable',
			'description' => __( 'Is extra fee taxable? Use this option if you have taxes enabled in your shop and you want to include tax to COD method.', 'wc-smart-cod' ),
			'desc_tip' => false
		);

		$form_fields[ 'nocharge_amount' ] = array(
			'title' => __( 'Disable extra fee if cart amount is greater or equal than this limit.', 'wc-smart-cod' ),
			'type' => 'price',
			'class' => 'wc-smart-cod-group wc-smart-cod-restriction',
			'description' => __( 'Leave blank or zero if you want to charge for any amount', 'wc-smart-cod' ),
			'desc_tip' => true,
			'placeholder' => __( 'Enter Amount', 'wc-smart-cod' ),
			'custom_attributes' => array(
				'data-name' => 'nocharge_amount'
			)
		);

		$ca_options = array(
			array( 'shipping' => 'Shipping' )
		);

		if( wc_tax_enabled() ) {
			array_unshift( $ca_options, array( 'tax' => 'Tax' ) );
		}

		$form_fields[ 'cart_amount_mode' ] = array(
			'title' => __( 'Select what should be included to the cart amount total', 'wc-smart-cod' ),
			'type' => 'checkboxes',
			'class' => 'wc-smart-cod-group',
			'options' => array(
				'tax' => 'Taxes',
				'shipping' => 'Shipping Costs'
			),
			'default' => array( 'tax', 'shipping' ),
			'description' => __( 'This setting affect those settings: "Disable extra fee if cart amount is greater than this limit." and "Disable if cart amount is greater than". <strong>It defines what is finally calculated as the cart amount.</strong>', 'wc-smart-cod' ),
			'desc_tip' => false
		);

		foreach( $shipping_methods as $key => $shipping_method ) {

			if( empty( $existing_settings[ 'enable_for_methods' ] ) || in_array( $key, $existing_settings[ 'enable_for_methods' ] ) ) {
				$form_fields[ 'method_different_charge_' . $key ] = array(
					'title' => __( 'Charge extra fee differently for shipping method: ', 'wc-smart-cod' ) . '<span class="bold">' . $shipping_method . '</span>',
					'type' => 'price',
					'description' =>  __( 'Enter Amount to charge differently in this shipping method or leave it empty to charge the normal amount', 'wc-smart-cod' ),
					'desc_tip' => true,
					'class' => 'wc-smart-cod-group wc-smart-cod-percentage',
					'placeholder' => __( 'Enter Amount', 'wc-smart-cod' )
				);
			}

		}

		// conditional for include countries

		if( array_key_exists( 'country_restrictions', $restriction_settings ) && $restriction_settings[ 'country_restrictions' ] === 1 ) {

			if( array_key_exists( 'country_restrictions', $existing_settings ) && ! empty( $existing_settings[ 'country_restrictions' ] ) ) {

				foreach( $existing_settings[ 'country_restrictions' ] as $country ) {
					if( ! isset ( $countries[ $country ] ) )
						continue;

					$country_name = $countries[ $country ];

					$form_fields[ 'include_country_different_charge_' . $country ] = array(
						'title' => __( 'Charge extra fee differently for country: ', 'wc-smart-cod' ) . '<span class="bold">' . $country_name . '</span>',
						'type' => 'price',
						'description' =>  __( 'Enter Amount to charge differently in this country or leave it empty to charge the normal amount. Use this field only if you want to have separate prices per country in the same shipping zone, otherwise enter the amount on the shipping zone field', 'wc-smart-cod' ),
						'desc_tip' => true,
						'class' => 'wc-smart-cod-group wc-smart-cod-percentage',
						'placeholder' => __( 'Enter Amount', 'wc-smart-cod' )
					);

				}

			}

		}

		$unset_zone_methods_fees = array();

		if( ! empty( $wc_shipping_zones ) ) {
			foreach( $wc_shipping_zones as $zone ) {

				if( $zone[ $zone_key ] === 0 )
					continue;

				if( array_key_exists( 'shipping_zone_restrictions', $existing_settings ) && is_array( $existing_settings[ 'shipping_zone_restrictions' ] ) && ! empty( $existing_settings[ 'shipping_zone_restrictions' ] ) ) {
					$mode = 'exclude';
					if( array_key_exists( 'shipping_zone_restrictions', $restriction_settings ) && $restriction_settings[ 'shipping_zone_restrictions' ] === 1 ) {
						$mode = 'include';
					}
				}

				if( isset( $mode ) && $mode === 'exclude' && in_array( $zone[ $zone_key ], $existing_settings[ 'shipping_zone_restrictions' ] ) ) {
					continue;
				}

				if( isset( $mode ) && $mode === 'include' && ! in_array( $zone[ $zone_key ], $existing_settings[ 'shipping_zone_restrictions' ] ) ) {
					continue;
				}

				$form_fields[ 'different_charge_' . $zone[ $zone_key ] ] = array(
					'title' => __( 'Charge extra fee differently for shipping zone: ', 'wc-smart-cod' ) . '<span class="bold">' . $zone[ 'zone_name' ] . '</span>',
					'type' => 'price',
					'description' =>  __( 'Enter Amount to charge differently in this shipping zone or leave it empty to charge the normal amount', 'wc-smart-cod' ),
					'desc_tip' => true,
					'class' => 'wc-smart-cod-group wc-smart-cod-fee wc-smart-cod-percentage',
					'placeholder' => __( 'Enter Amount', 'wc-smart-cod' )
				);

				foreach( $zone[ 'shipping_methods' ] as $shipping_method ) {

					if( empty( $existing_settings[ 'enable_for_methods' ] ) || in_array( $shipping_method->id, $existing_settings[ 'enable_for_methods' ] ) ) {

						if( isset( $existing_settings[ 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->id ] ) && is_numeric(
							$existing_settings[ 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->id ] ) ) {
							/**
							 * migrate to 1.4.7
							 */
							$this->settings[ 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->instance_id ] = $existing_settings[ 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->id ];
							if( ! in_array( 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->id, $unset_zone_methods_fees ) ) {
								array_push( $unset_zone_methods_fees, 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->id );
							}
						}

						if( isset( $existing_settings[ 'shipping_zone_method_restriction' ] ) && ! empty( $existing_settings[ 'shipping_zone_method_restriction' ] ) ) {
							$zmode = 'exclude';
							if( array_key_exists( 'shipping_zone_method_restriction', $restriction_settings ) && $restriction_settings[ 'shipping_zone_method_restriction' ] === 1 ) {
								$zmode = 'include';
							}

							if( $zmode === 'exclude' && in_array( $zone[ $zone_key ] . '_' . $shipping_method->instance_id, $existing_settings[ 'shipping_zone_method_restriction' ] ) ) {
								continue;
							}

							if( $zmode === 'include' && ! in_array( $zone[ $zone_key ] . '_' . $shipping_method->instance_id, $existing_settings[ 'shipping_zone_method_restriction' ] ) ) {
								continue;
							}
						}

						$form_fields[ 'zonemethod_different_charge_' . $zone[ $zone_key ] . '_method_' . $shipping_method->instance_id ] = array(
							'title' => __( 'Charge extra fee differently for shipping zone: ', 'wc-smart-cod' ) . '<span class="bold">' . $zone[ 'zone_name' ] . '</span>' . ' ' . __( 'and shipping method: ', 'wc-smart-cod' ) . '<span class="bold">' . $shipping_method->title . '</span>',
							'type' => 'price',
							'description' =>  __( 'Enter Amount to charge differently in this shipping zone with this shipping method or leave it empty to charge the normal amount', 'wc-smart-cod' ),
							'desc_tip' => true,
							'class' => 'wc-smart-cod-group wc-smart-cod-fee wc-smart-cod-percentage',
							'placeholder' => __( 'Enter Amount', 'wc-smart-cod' )
						);
					}
				}
			}
		}

		$form_fields[ 'restriction_settings' ] = array(
			'type' => 'hidden'
		);

		$form_fields[ 'fee_settings' ] = array(
			'type' => 'hidden'
		);

		if( ! empty( $unset_zone_methods_fees ) ) {
			/**
			 * migrate to 1.4.7
			 */
			$this->migrate_shipping_zone_methods( $unset_zone_methods_fees );
		}


		$form_fields = $this->analyze_fields( $form_fields, $existing_settings, $restriction_settings, $old_wc_smart_cod );

		return $form_fields;

	}

	public function generate_cod_messages_html( $key, $data ) {

		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$restrictions = array(
			'generic' => 'Generic Message',
			'shipping_method' => 'Unavailable by restricted shipping method',
			'shipping_zone' => 'Unavailable by restricted shipping zone',
			'country' => 'Unavailable by restricted country',
			'state' => 'Unavailable by restricted state',
			'postal' => 'Unavailable by restricted postal code',
			'city' => 'Unavailable by restricted city',
			'cart_amount' => 'Unavailable by restricted cart amount',
			'user_role' => 'Unavailable by restricted user role',
			'category' => 'Unavailable by restricted product category',
			'product' => 'Unavailable by restricted product',
			'shipping_class' => 'Unavailable by restricted shipping class',
			'shipping_zone_method' => 'Unavailable by restricted shipping method of a specific zone'
		);

		$data = wp_parse_args( $data, $defaults );
		$tvalue = $this->get_option( $key );
		if( ! is_array( $tvalue ) ) {
			// old wsc ( before 1.4.4 )
			// migrate value
			$_value = array();

			if( $tvalue !== '' ) {
				$_value[ 'generic' ] = $tvalue;
			}
			$tvalue = $_value;
		}

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $data ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<select class="select wsc-message-switcher" name="wsc-message-switcher">
					<?php
					foreach( $restrictions as $value => $label ) {
						echo sprintf( '<option value="%s">%s</option>', $value, $label );
					}
					?>
				</select>
				<?php
				$index = 0;
				foreach( $restrictions as $value => $label ) :
					$textarea_value = array_key_exists( $value, $tvalue ) ? esc_textarea( $tvalue[ $value ] ) : '';
					?>
					<fieldset class="wsc-message<?php echo $index > 0 ? ' hidden' : ''; ?>" data-restriction="<?php echo $value; ?>">
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<textarea rows="5" cols="20" class="input-text wide-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ) . '[' . esc_attr( $value ) . ']' ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo $textarea_value; ?></textarea>
					</fieldset>
				<?php
				$index++;
				endforeach;
				echo $this->get_description_html( $data ); ?>
			</td>
		</tr>
		<?php

		return ob_get_clean();

	}

	public function validate_cod_messages_field( $key, $value ) {
		foreach( $value as $k => $v ) {
			$value[ $k ] = $this->validate_textarea_field( $k, $v );
		}
		return $value;
	}

	public function generate_checkboxes_html( $key, $data ) {

		$field_key = $this->get_field_key( $key );

		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
			'parent_class'		=> '',
			'default' => ''
		);

		$data = wp_parse_args( $data, $defaults );
		$value = $this->get_option( $key );

		if( $value === '' ) {
			if( $data[ 'default' ] ) {
				$value = $data[ 'default' ];
			}
		}

		ob_start(); ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $data ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $data['type'] ); ?><?php echo $data[ 'parent_class' ] ? ' ' . esc_attr( $data[ 'parent_class' ] ) : ''; ?>">
				<fieldset>
					<ul>
					<?php
						foreach ( (array) $data['options'] as $option_key => $option_value ) {
							?>
							<li>
								<label><input
									name="<?php echo esc_attr( $field_key ); ?>[]"
									value="<?php echo $option_key; ?>"
									type="checkbox"
									style="<?php echo esc_attr( $data['css'] ); ?>"
									class="<?php echo esc_attr( $data['class'] ); ?>"
									<?php echo $this->get_custom_attribute_html( $data ); ?>
									<?php checked( $option_key, in_array( $option_key, $value ) ? $option_key : false ); ?>
									/> <?php echo esc_attr( $option_value ); ?></label>
							</li>
							<?php
						}
					?>
					</ul>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	public function generate_radio_html( $key, $data ) {

		$field_key = $this->get_field_key( $key );

		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
			'parent_class'		=> '',
			'default' => ''
		);

		$data = wp_parse_args( $data, $defaults );
		$value = esc_attr( $this->get_option( $key ) );

		if( ! $value && ! array_key_exists( $key, $this->settings ) ) {
			if( $data[ 'default' ] ) {
				$value = $data[ 'default' ];
			}
		}

		ob_start(); ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $data ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data[ 'title' ] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $data['type'] ); ?><?php echo $data[ 'parent_class' ] ? ' ' . esc_attr( $data[ 'parent_class' ] ) : ''; ?>">
				<fieldset>
					<ul>
					<?php
						foreach ( (array) $data['options'] as $option_key => $option_value ) {
							?>
							<li>
								<label><input
									name="<?php echo esc_attr( $field_key ); ?>"
									value="<?php echo $option_key; ?>"
									type="radio"
									style="<?php echo esc_attr( $data['css'] ); ?>"
									class="<?php echo esc_attr( $data['class'] ); ?>"
									<?php echo $this->get_custom_attribute_html( $data ); ?>
									<?php checked( $option_key, $value ); ?>
									/> <?php echo esc_attr( $option_value ); ?></label>
							</li>
							<?php
						}
					?>
					</ul>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	public function validate_radio_field( $key, $value ) {
		$value = is_null( $value ) ? '' : $value;
		return wc_clean( stripslashes( $value ) );
	}

	public function validate_checkboxes_field( $key, $value ) {
		$_value = array();
		if( ! $value ) {
			return array();
		}
		foreach( $value as $v ) {
			array_push( $_value, wc_clean( stripslashes( $v ) ) );
		}
		return $_value;
	}

	public function clean_up_gateway( $zone_id ) {

		$settings = get_option( 'woocommerce_cod_settings' );
		foreach( $settings as $key => $setting ) {
			if( $key === 'different_charge_' . $zone_id || strpos( $key, 'zonemethod_different_charge_' . $zone_id ) === 0 )
				unset( $settings [ $key ] );
		}

		update_option( 'woocommerce_cod_settings', $settings );

	}

	public function clean_up_settings( $settings ) {

		if( $settings[ 'fee_settings' ] ) {
			$fee_settings = json_decode( $settings[ 'fee_settings' ], true );
			$needs_update = false;
			foreach( $fee_settings as $k => $v ) {
				if( ! isset( $this->prepared_fields[ $k ] ) ) {
					$has_update = true;
					unset( $fee_settings[ $k ] );
				}
			}
			if( $needs_update ) {
				$settings[ 'fee_settings' ] = json_encode( $fee_settings );
			}
		}

		foreach( $settings as $key => $setting ) {

			if( strpos( $key, 'different_charge_' ) === 0 || strpos( $key, 'zonemethod_different_charge_' ) === 0 || strpos( $key, 'method_different_charge_' ) === 0 ) {
				if( ! is_numeric( $setting ) )
					unset( $settings [ $key ] );
			}

		}

		return $settings;

	}

	protected function get_json_settings( $key ) {

		if( array_key_exists( $key, $this->settings ) ) {
			$option = json_decode( $this->settings[ $key ], true );
		}
		else {
			$option = array();
		}

		return $option;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */

	/**
	 * Register the scripts and styles for the admin area.
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

		$screen = get_current_screen();
		$current_section = isset( $_GET['section'] ) ? $_GET['section'] : '';

		if( isset( $screen->base ) && $screen->base === 'woocommerce_page_wc-settings' && $current_section === 'cod' ) {

			$messages = array(
				'nan' => __( 'This field should be a number only.', 'wc-smart-cod' ),
				'please_save_msg' => __( 'Please save the changes to see the new settings', 'wc-smart-cod' ),
			);

			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-smart-cod-admin.js', array( 'jquery' ), $this->version, false );

			$enhanced_select_variables = array(
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'		=> wp_create_nonce( 'search-products' ),
				'search_categories_nonce'   => wp_create_nonce( 'search-categories' )
			);

			$variables = array(
				'messages' => $messages,
				'enhanced_select' => $enhanced_select_variables,
				'restriction_settings' => ( object ) $this->get_json_settings( 'restriction_settings' ),
				'fee_settings' => ( object ) $this->get_json_settings( 'fee_settings' )
			);

			global $wp_scripts;
			$select2 = array_key_exists( 'select2', $wp_scripts->registered ) ? $wp_scripts->registered[ 'select2' ] : false;
			$select2_ver = $select2 === false ? false : property_exists( $select2, 'ver' ) ? $select2->ver : false;

			if( ! $select2 || ! $select2_ver || version_compare( $select2_ver, '4.0.3' ) === -1 ) {
				// compatibility with older WooCommerce.
				wp_deregister_script( 'select2' );
				wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), '4.0.3', false );
				wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/wc-smart-cod-select2.css', array(), '4.0.3', 'all' );
				wp_enqueue_script( 'select2' );
			}

			wp_localize_script( $this->plugin_name, 'smart_cod_variables', $variables );
			wp_enqueue_script( $this->plugin_name );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-smart-cod-admin.css', array(), $this->version, 'all' );
		}

	}

}
