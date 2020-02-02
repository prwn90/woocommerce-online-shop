<?php
/**
 * File that define P24_Config_Accessor class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Accessor for P24_Config_Holder.
 *
 * This class know active currency and style of booleans.
 * These two properties are unknown to P24_Config_Holder.
 */
class P24_Config_Accessor {

	const AM_STRICT    = 'am_strict';
	const AM_WORDPRESS = 'am_wordpress';

	/**
	 * Name of currency that is described by config.
	 *
	 * @var string
	 */
	private $currency;

	/**
	 * The object holding config.
	 *
	 * @var P24_Config_Holder
	 */
	private $config;

	/**
	 * The style of accessor.
	 *
	 * The booleans may be expected to be in form yes/no.
	 *
	 * @var string
	 */
	private $access_mode;

	/**
	 * Array holding translation of bool representations.
	 *
	 * @var array
	 */
	private $bool_translation_table;

	/**
	 * The constructor.
	 *
	 * The default boolean mode is yes/no.
	 *
	 * @param string            $currency Name of currency.
	 * @param P24_Config_Holder $config The config.
	 */
	public function __construct( $currency, P24_Config_Holder $config ) {
		$this->currency = $currency;
		$this->config   = $config;
		$this->access_mode_to_wordpress();
		$this->bool_translation_table = array(
			self::AM_STRICT    => array( false, true ),
			self::AM_WORDPRESS => array( 'no', 'yes' ),
		);
		$this->fix_booleans();
	}

	/**
	 * Convert variable to selected bool representation.
	 *
	 * @param bool $input The input variable to be converted.
	 * @return bool|string
	 * @throws InvalidArgumentException If conversion cannot be made.
	 */
	public function to_selected_bool( $input ) {
		if ( false === $input ) {
			return $this->bool_translation_table[ $this->access_mode ][0];
		} elseif ( true === $input ) {
			return $this->bool_translation_table[ $this->access_mode ][1];
		} else {
			throw new InvalidArgumentException( 'Wrong input type, it has to be bool.' );
		}
	}

	/**
	 * Convert variable from selected bool representation.
	 *
	 * @param bool|string $input The input variable to be converted.
	 * @return bool
	 * @throws InvalidArgumentException If conversion cannot be made.
	 */
	public function from_selected_bool( $input ) {
		$row = $this->bool_translation_table[ $this->access_mode ];
		$key = array_search( $input, $row, true );
		if ( 0 === $key ) {
			return false;
		} elseif ( 1 === $key ) {
			return true;
		} else {
			throw new InvalidArgumentException( 'Wrong input, it cannot be converted to bool.' );
		}
	}

	/**
	 * Convert all booleans in config to PHP representation.
	 */
	private function fix_booleans() {
		$to_fix = array(
			'p24_oneclick',
			'p24_payinshop',
			'p24_acceptinshop',
			'p24_show_paymethods',
			'p24_graphics',
			'p24_wait_for_result',
			'sub_enabled',
		);
		foreach ( $to_fix as $one ) {
			if ( null === $this->config->{$one} ) {
				$this->config->{$one} = false;
			} elseif ( ! is_bool( $this->config->{$one} ) ) {
				$this->config->{$one} = self::from_selected_bool( $this->config->{$one} );
			}
		}
	}

	/**
	 * The cloner.
	 */
	public function __clone() {
		$this->config = clone $this->config;
	}

	/**
	 * Set access mode for booleans to PHP types.
	 *
	 * @return $this
	 */
	public function access_mode_to_strict() {
		$this->access_mode = self::AM_STRICT;
		return $this;
	}

	/**
	 * Set access mode for booleans to strings yes and no.
	 *
	 * @return $this
	 */
	public function access_mode_to_wordpress() {
		$this->access_mode = self::AM_WORDPRESS;
		return $this;
	}

	/**
	 * Get title from config.
	 *
	 * @return string
	 */
	public function get_title() {
		return (string) $this->config->title;
	}

	/**
	 * Set title in config.
	 *
	 * @param string $title The new title.
	 * @return P24_Config_Accessor
	 */
	public function set_title( $title ) {
		$this->config->title = (string) $title;
		return $this;
	}

	/**
	 * Get merchant_id from config.
	 *
	 * @return string
	 */
	public function get_merchant_id() {
		return (string) $this->config->merchant_id;
	}

	/**
	 * Set merchant_id in config.
	 *
	 * @param string $merchant_id The new merchant_id.
	 * @return P24_Config_Accessor
	 */
	public function set_merchant_id( $merchant_id ) {
		$this->config->merchant_id = (string) $merchant_id;
		return $this;
	}

	/**
	 * Get shop_id form config.
	 *
	 * @return string
	 */
	public function get_shop_id() {
		return (string) $this->config->shop_id;
	}

	/**
	 * Set shop_id in config.
	 *
	 * @param string $shop_id The new shop_id.
	 * @return P24_Config_Accessor
	 */
	public function set_shop_id( $shop_id ) {
		$this->config->shop_id = (string) $shop_id;
		return $this;
	}

	/**
	 * Get salt from config.
	 *
	 * @return string
	 */
	public function get_salt() {
		return (string) $this->config->salt;
	}

	/**
	 * Set salt in config.
	 *
	 * @param string $salt The new salt.
	 * @return P24_Config_Accessor
	 */
	public function set_salt( $salt ) {
		$this->config->salt = (string) $salt;
		return $this;
	}

	/**
	 * Get operation mode from config.
	 *
	 * @return string The new p24_operation_mode
	 */
	public function get_p24_operation_mode() {
		return (string) $this->config->p24_operation_mode;
	}

	/**
	 * Set operation mode in config.
	 *
	 * @param string $p24_operation_mode The new p24_operation_mode.
	 * @return P24_Config_Accessor
	 */
	public function set_p24_operation_mode( $p24_operation_mode ) {
		$this->config->p24_operation_mode = (string) $p24_operation_mode;
		return $this;
	}

	/**
	 * Compare modes and return bool.
	 *
	 * @param string $checked The value to compare mode with.
	 * @return bool|string
	 */
	public function is_p24_operation_mode( $checked ) {
		$mode    = $this->get_p24_operation_mode();
		$is_mode = $checked === $mode;
		return $this->to_selected_bool( $is_mode );
	}

	/**
	 * Get description from config.
	 *
	 * @return string
	 */
	public function get_description() {
		return (string) $this->config->description;
	}

	/**
	 * Set description in config.
	 *
	 * @param string $description The new description.
	 * @return P24_Config_Accessor
	 */
	public function set_description( $description ) {
		$this->config->description = (string) $description;
		return $this;
	}

	/**
	 * Get p24_api from config.
	 *
	 * @return string
	 */
	public function get_p24_api() {
		return (string) $this->config->p24_api;
	}

	/**
	 * Set p24_api in config.
	 *
	 * @param string $p24_api The new p24_api.
	 * @return P24_Config_Accessor
	 */
	public function set_p24_api( $p24_api ) {
		$this->config->p24_api = (string) $p24_api;
		return $this;
	}

	/**
	 * Return p24_oneclick from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_p24_oneclick() {
		try {
			$p24_oneclick = $this->config->p24_oneclick;
			return $this->to_selected_bool( $p24_oneclick );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of p24_oneclick is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set p24_oneclick in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $p24_oneclick The new p24_oneclick.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_p24_oneclick( $p24_oneclick ) {
		$this->config->p24_oneclick = $this->from_selected_bool( $p24_oneclick );
		return $this;
	}

	/**
	 * Return p24_payinshop from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_p24_payinshop() {
		try {
			$p24_payinshop = $this->config->p24_payinshop;
			return $this->to_selected_bool( $p24_payinshop );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of p24_payinshop is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set p24_acceptinshop in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $p24_acceptinshop The new p24_oneclick.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_p24_acceptinshop( $p24_acceptinshop ) {
		$this->config->p24_acceptinshop = $this->from_selected_bool( $p24_acceptinshop );
		return $this;
	}

	/**
	 * Return p24_acceptinshop from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_p24_acceptinshop() {
		try {
			$p24_acceptinshop = $this->config->p24_acceptinshop;
			return $this->to_selected_bool( $p24_acceptinshop );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of p24_acceptinshop is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set p24_payinshop in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $p24_payinshop The new p24_payinshop.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_p24_payinshop( $p24_payinshop ) {
		$this->config->p24_payinshop = $this->from_selected_bool( $p24_payinshop );
		return $this;
	}

	/**
	 * Return p24_show_paymethods from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_p24_show_paymethods() {
		try {
			$p24_show_paymethods = $this->config->p24_show_paymethods;
			return $this->to_selected_bool( $p24_show_paymethods );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of p24_show_paymethods is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set p24_show_paymethods in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $p24_show_paymethods The new p24_show_paymethods.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_p24_show_paymethods( $p24_show_paymethods ) {
		$this->config->p24_show_paymethods = $this->from_selected_bool( $p24_show_paymethods );
		return $this;
	}

	/**
	 * Return p24_graphics from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_p24_graphics() {
		try {
			$p24_graphics = $this->config->p24_graphics;
			return $this->to_selected_bool( $p24_graphics );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of p24_graphics is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set p24_graphics in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $p24_graphics The new p24_graphics.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_p24_graphics( $p24_graphics ) {
		$this->config->p24_graphics = $this->from_selected_bool( $p24_graphics );
		return $this;
	}

	/**
	 * Get p24_paymethods_first from config.
	 *
	 * @return string
	 */
	public function get_p24_paymethods_first() {
		return (string) $this->config->p24_paymethods_first;
	}

	/**
	 * Set p24_paymethods_first in config.
	 *
	 * @param string $p24_paymethods_first The new p24_paymethods_first.
	 * @return P24_Config_Accessor
	 */
	public function set_p24_paymethods_first( $p24_paymethods_first ) {
		$this->config->p24_paymethods_first = (string) $p24_paymethods_first;
		return $this;
	}

	/**
	 * Get p24_paymethods_second from config.
	 *
	 * @return string
	 */
	public function get_p24_paymethods_second() {
		return (string) $this->config->p24_paymethods_second;
	}

	/**
	 * Set p24_paymethods_second in config.
	 *
	 * @param string $p24_paymethods_second The new p24_paymethods_second.
	 * @return P24_Config_Accessor
	 */
	public function set_p24_paymethods_second( $p24_paymethods_second ) {
		$this->config->p24_paymethods_second = (string) $p24_paymethods_second;
		return $this;
	}

	/**
	 * Return p24_wait_for_result from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_p24_wait_for_result() {
		try {
			$p24_wait_for_result = $this->config->p24_wait_for_result;
			return $this->to_selected_bool( $p24_wait_for_result );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of p24_wait_for_result is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set p24_wait_for_result in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $p24_wait_for_result The new p24_wait_for_result.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_p24_wait_for_result( $p24_wait_for_result ) {
		$this->config->p24_wait_for_result = $this->from_selected_bool( $p24_wait_for_result );
		return $this;
	}

	/**
	 * Return sub_enabled from config.
	 *
	 * The value is converted to selected representation.
	 *
	 * @return bool|string
	 * @throws LogicException If the internal representation of value is broken.
	 */
	public function get_sub_enabled() {
		try {
			$sub_enabled = $this->config->sub_enabled;
			return $this->to_selected_bool( $sub_enabled );
		} catch ( InvalidArgumentException $ex ) {
			$msg = 'The internal representation of sub_enabled is invalid.';
			throw new LogicException( $msg, 0, $ex );
		}
	}

	/**
	 * Set sub_enabled in config.
	 *
	 * The input is expected to be in selected representation.
	 *
	 * @param bool|string $sub_enabled The new sub_enabled.
	 * @return P24_Config_Accessor
	 * @throws InvalidArgumentException If input is in wrong representation.
	 */
	public function set_sub_enabled( $sub_enabled ) {
		$this->config->sub_enabled = $this->from_selected_bool( $sub_enabled );
		return $this;
	}

}
