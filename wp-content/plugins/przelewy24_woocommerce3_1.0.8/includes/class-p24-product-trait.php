<?php
/**
 * File that define P24_Product_Trait trait.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trait with common methods for overwritten objects.
 */
trait P24_Product_Trait {
	/**
	 * A getter.
	 *
	 * Should be defined in parent class of classes including this trait.
	 *
	 * @param string $prop The name of prop.
	 * @param string $context The name of context.
	 * @return mixed
	 */
	abstract protected function get_prop( $prop, $context = 'view' );

	/**
	 * A setter for internal variables.
	 *
	 * Should be defined in parent class of classes including this trait.
	 *
	 * @param string $prop The name of prop.
	 * @param mixed  $value The value of prop.
	 * @return mixed
	 */
	abstract protected function set_prop( $prop, $value );

	/**
	 * Common code to run in constructor.
	 */
	protected function populate_internal_data() {
		$this->data['currency']                       = get_woocommerce_currency();
		$this->data['default_currency_price']         = '';
		$this->data['default_currency_regular_price'] = '';
		$this->data['default_currency_sale_price']    = '';
	}

	/**
	 * Compute price native to object.
	 *
	 * The object has own currency.
	 * The false values have special meaning.
	 *
	 * @param mixed $price Price in default currency.
	 * @return mixed
	 * @throws LogicException If currency is not found.
	 */
	protected function compute_native_price( $price ) {
		$currency       = $this->get_currency();
		$multi_currency = get_przelewy24_plugin_instance()->get_multi_currency_instance();
		return $multi_currency->compute_price_in_currency( $price, $currency );
	}

	/**
	 * Get currency of the object.
	 *
	 * @param string $context The name of context.
	 * @return mixed
	 */
	public function get_currency( $context = 'view' ) {
		return $this->get_prop( 'currency', $context );
	}

	/**
	 * Set the product's active price.
	 *
	 * @param string $price Price in default currency.
	 */
	public function set_price( $price ) {
		$native_price = $this->compute_native_price( $price );
		$this->set_prop( 'price', wc_format_decimal( $native_price ) );
		$this->set_prop( 'default_currency_price', wc_format_decimal( $price ) );
	}

	/**
	 * Get price.
	 * It is in base currency for editing.
	 *
	 * @param string $context Name of context.
	 * @return mixed
	 */
	public function get_price( $context = 'view' ) {
		if ( 'edit' === $context ) {
			return $this->get_prop( 'default_currency_price', $context );
		} else {
			return $this->get_prop( 'price', $context );
		}
	}

	/**
	 * Set the product's regular price.
	 *
	 * @param string $price Price in default currency.
	 */
	public function set_regular_price( $price ) {
		$native_price = $this->compute_native_price( $price );
		$this->set_prop( 'regular_price', wc_format_decimal( $native_price ) );
		$this->set_prop( 'default_currency_regular_price', wc_format_decimal( $price ) );
	}

	/**
	 * Get regular price.
	 * It is in base currency for editing.
	 *
	 * @param string $context Name of context.
	 * @return mixed
	 */
	public function get_regular_price( $context = 'view' ) {
		if ( 'edit' === $context ) {
			return $this->get_prop( 'default_currency_regular_price', $context );
		} else {
			return $this->get_prop( 'regular_price', $context );
		}
	}

	/**
	 * Set the product's sale price.
	 *
	 * @param string $price  Price in default currency.
	 */
	public function set_sale_price( $price ) {
		$native_price = $this->compute_native_price( $price );
		$this->set_prop( 'sale_price', wc_format_decimal( $native_price ) );
		$this->set_prop( 'default_currency_sale_price', wc_format_decimal( $price ) );
	}

	/**
	 * Get sale price.
	 * It is in base currency for editing.
	 *
	 * @param string $context Name of context.
	 * @return mixed
	 */
	public function get_sale_price( $context = 'view' ) {
		if ( 'edit' === $context ) {
			return $this->get_prop( 'default_currency_sale_price', $context );
		} else {
			return $this->get_prop( 'sale_price', $context );
		}
	}

}
