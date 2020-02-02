<?php
/**
 * Premium Tab
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Brands AddOn
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'premium' => array(
		'landing' => array(
			'type' => 'custom_tab',
			'action' => 'yith_wcbr_premium_tab'
		)
	)
);