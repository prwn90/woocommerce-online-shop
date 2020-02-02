<?php
/**
 * File that define P24_Product_Variable class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class add to parent awareness of multiple currencies.
 */
class P24_Product_Variable extends WC_Product_Variable {

	use P24_Product_Trait;

	/**
	 * P24_Product_Variable constructor.
	 *
	 * @param int $product Id of product.
	 */
	public function __construct( $product = 0 ) {
		$this->populate_internal_data();
		parent::__construct( $product );
	}

	/**
	 * Get an array of all sale and regular prices from all variations.
	 *
	 * @param  bool $for_display If true, prices will be adapted for display.
	 * @return array Array of prices.
	 */
	public function get_variation_prices( $for_display = false ) {
		$prices     = parent::get_variation_prices( $for_display );
		$new_prices = array();
		foreach ( $prices as $key => $set ) {
			$new_set            = array_map( array( $this, 'compute_native_price' ), $set );
			$new_prices[ $key ] = $new_set;
		}
		return $new_prices;
	}

}
