<?php
/**
 * File that define P24_Product_Variation class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class add to parent awareness of multiple currencies.
 */
class P24_Product_Variation extends WC_Product_Variation {

	use P24_Product_Trait;

	/**
	 * P24_Product_Variation constructor.
	 *
	 * @param int $product Id of product.
	 */
	public function __construct( $product = 0 ) {
		$this->populate_internal_data();
		parent::__construct( $product );
	}

}
