<?php
/**
 * File that define P24_Product_External class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class add to parent awareness of multiple currencies.
 */
class P24_Product_External extends WC_Product_External {

	use P24_Product_Trait;

	/**
	 * P24_Product_External constructor.
	 *
	 * @param int $product The id of product.
	 */
	public function __construct( $product = 0 ) {
		$this->populate_internal_data();
		parent::__construct( $product );
	}

}
