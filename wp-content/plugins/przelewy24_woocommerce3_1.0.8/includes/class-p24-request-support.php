<?php
/**
 * File that define P24_Request_Support class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that analyse requests originated from WordPress but WooCommerce.
 *
 * It supports user mode and admin mode.
 * Requests from WooCommerce are supported in different places.
 *
 * The class interact with $_POST.
 */
class P24_Request_Support {

	const OPTION_KEY_MULTI_CURRENCY_MULTIPLIERS = 'przelewy24_multi_currency_multipliers';
	const OPTION_KEY_MULTI_CURRENCY_FORMATS     = 'przelewy24_multi_currency_formats';
	const OPTION_KEY_COMMON                     = 'przelewy24_common_settings';

	/**
	 * Set of changes.
	 *
	 * @var array
	 */
	private $changes = [];

	/**
	 * Return changes of active currency.
	 *
	 * @return string
	 */
	public function get_currency_changes() {
		if ( array_key_exists( 'currency', $this->changes ) ) {
			return $this->changes['currency'];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if there is new currency to set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_currency_changes( $data ) {
		if ( isset( $data['p24_currency'] ) ) {
			$this->changes['currency'] = $data['p24_currency'];
		}
	}

	/**
	 * Get changes of format.
	 *
	 * @return array|null
	 */
	public function get_format_changes() {
		if ( array_key_exists( 'formats', $this->changes ) ) {
			return $this->changes['formats'];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if there are new formats to set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_format_changes( $data ) {
		if ( array_key_exists( 'p24_currency', $data ) && array_key_exists( 'p24_formats', $data ) ) {
			$this->changes['formats'] = [ $data['p24_currency'] => $data['p24_formats'] ];
		}
	}

	/**
	 * Get changes of multipliers.
	 *
	 * @return array|null
	 */
	public function get_multipliers_changes() {
		if ( array_key_exists( 'p24_multiplers', $this->changes ) ) {
			return $this->changes['p24_multiplers'];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if there are new multipliers to change.
	 *
	 * @param array $data Array of data to analyse.
	 * @throws LogicException If there is a bug in send data.
	 */
	private function preload_multipliers_changes( $data ) {
		if ( isset( $data['p24_multipliers'] ) ) {
			$multipliers = $data['p24_multipliers'];
		} else {
			$multipliers = array();
		}
		$default                         = get_option( 'woocommerce_currency' );
		$multipliers[ $default ]         = 1;
		$multipliers                     = array_map( 'floatval', $multipliers );
		$multipliers                     = array_filter( $multipliers );
		$this->changes['p24_multiplers'] = $multipliers;
	}

	/**
	 * Get list of common changes.
	 *
	 * @return mixed|null
	 */
	public function get_common_changes() {
		if ( array_key_exists( 'p24_common', $this->changes ) ) {
			return $this->changes['p24_common'];
		} else {
			return null;
		}
	}

	/**
	 * Analyse if activation of multi currency is set.
	 *
	 * @param array $data Array of data to analyse.
	 */
	private function preload_multi_currency_changes( $data ) {
		$active = array_key_exists( 'p24_multi_currency_active', $data ) && 'yes' === $data['p24_multi_currency_active'] ? 'yes' : 'no';
		$this->changes['p24_common']['p24_multi_currency'] = $active;
	}

	/**
	 * Process data from GET.
	 */
	private function check_get() {
		$get = $_GET; // WPCS: CSRF ok.
		if ( isset( $get['p24_change_currency'] ) ) {
			$this->changes['currency'] = $get['p24_change_currency'];
		}
	}

	/**
	 * Validate nonce and return request.
	 *
	 * @return null|array
	 */
	private function get_post_data() {
		if ( isset( $_POST['p24_nonce'] ) ) {
			$nonce = sanitize_key( $_POST['p24_nonce'] );
			if ( wp_verify_nonce( $nonce, 'p24_action' ) ) {
				return $_POST;
			}
		}
		return null;
	}

	/**
	 * Analyse the whole request.
	 *
	 * We need call this action very early.
	 */
	public function analyse() {
		$this->check_get();

		$data = $this->get_post_data();
		if ( ! $data ) {
			return;
		}

		if ( isset( $data['p24_action_type_field'] ) ) {
			$field = $data['p24_action_type_field'];
			switch ( $field ) {
				case 'change_currency':
					$this->preload_currency_changes( $data );
					break;
				case 'change_formats':
					$this->preload_currency_changes( $data );
					$this->preload_format_changes( $data );
					break;
				case 'change_multipliers':
					$this->preload_multipliers_changes( $data );
					break;
				case 'activate_multi_currency':
					$this->preload_multi_currency_changes( $data );
					break;
			}
		}
	}

	/**
	 * Flush options to the database.
	 *
	 * This action should be done later thant analyse.
	 */
	public function flush_options() {
		$set_overwrite = [
			self::OPTION_KEY_MULTI_CURRENCY_MULTIPLIERS => $this->get_multipliers_changes(),
		];
		$set_merge     = [
			self::OPTION_KEY_MULTI_CURRENCY_FORMATS => $this->get_format_changes(),
			self::OPTION_KEY_COMMON                 => $this->get_common_changes(),
		];
		foreach ( $set_overwrite as $k => $v ) {
			if ( $v ) {
				update_option( $k, $v );
			}
		}
		foreach ( $set_merge as $k => $v ) {
			if ( $v ) {
				$v = $v + get_option( $k, [] );
				update_option( $k, $v );
			}
		}
	}
}
