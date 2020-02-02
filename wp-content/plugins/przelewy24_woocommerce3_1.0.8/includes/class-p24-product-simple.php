<?php
/**
 * File that define P24_Product_Simple class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;


/**
 * The class add to parent awareness of multiple currencies.
 */
class P24_Product_Simple extends WC_Product_Simple {

	use P24_Product_Trait;

	/**
	 * P24_Product_Simple constructor.
	 *
	 * @param int $product Id of product.
	 */
	public function __construct( $product = 0 ) {
		$this->populate_internal_data();
		parent::__construct( $product );
	}

}
