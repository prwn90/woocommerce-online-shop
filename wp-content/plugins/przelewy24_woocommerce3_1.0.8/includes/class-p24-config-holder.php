<?php
/**
 * File that define P24_Config_Holder class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Simple class that hold config.
 *
 * The active currency and style of booleans are unknown to this class.
 * The accessor is external.
 */
class P24_Config_Holder {
	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Identification of merchant.
	 *
	 * @var string
	 */
	public $merchant_id;

	/**
	 * Identification of store.
	 *
	 * It may be the same as $merchant_id.
	 *
	 * @var string
	 */
	public $shop_id;

	/**
	 * Salt or CRC key.
	 *
	 * @var string
	 */
	public $salt;

	/**
	 * Mode of operation.
	 *
	 * @var string
	 */
	public $p24_operation_mode;

	/**
	 * Longer description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Key to API.
	 *
	 * @var string
	 */
	public $p24_api;

	/**
	 * Activate the Onclick.
	 *
	 * @var bool
	 */
	public $p24_oneclick;

	/**
	 * Option to pay in shop via card.
	 *
	 * @var bool
	 */
	public $p24_payinshop;

	/**
	 * Option to accept p24 terms in shop.
	 *
	 * @var bool
	 */
	public $p24_acceptinshop;

	/**
	 * Select pay methods in shop.
	 *
	 * @var bool
	 */
	public $p24_show_paymethods;

	/**
	 * User graphic list of pay options.
	 *
	 * @var bool
	 */
	public $p24_graphics;

	/**
	 * Comma separated list of promoted pay options.
	 *
	 * @var string
	 */
	public $p24_paymethods_first;

	/**
	 * Comma separated list of additional methods.
	 *
	 * @var string
	 */
	public $p24_paymethods_second;

	/**
	 * Wait for transaction result.
	 *
	 * @var bool
	 */
	public $p24_wait_for_result;

	/**
	 * Enable selected currency.
	 *
	 * @var bool
	 */
	public $sub_enabled;
}
