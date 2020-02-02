<?php
/**
 * File that define P24_Message_Validator class.
 *
 * @package Przelewy24
 */

/**
 * Class P24_Message_Validator.
 */
class P24_Message_Validator {
	/**
	 * Validate version.
	 *
	 * @param string $version Reference to version variable.
	 * @return bool
	 */
	private function validate_version( &$version ) {
		if ( preg_match( '/^[0-9]+(?:\.[0-9]+)*(?:[\.\-][0-9a-z]+)?$/', $version ) ) {
			return true;
		}
		$version = '';
		return false;
	}

	/**
	 * Validate email.
	 *
	 * @param string $email Reference to email variable.
	 * @return bool
	 */
	private function validate_email( &$email ) {
		$email = filter_var( $email, FILTER_VALIDATE_EMAIL );
		if ( $email ) {
			return true;
		}
		$email = '';
		return false;
	}

	/**
	 * Validate number.
	 *
	 * @param string|float|int $value Reference to value that should be a number.
	 * @param bool|int         $min The minimum allowed value, false for unbound.
	 * @param bool|int         $max The maximum allowed value, false for unbound.
	 * @return bool
	 */
	private function validate_number( &$value, $min = false, $max = false ) {
		if ( is_numeric( $value ) ) {
			$value = (int) $value;
			if ( ( false !== $min && $value < $min ) || ( false !== $max && $value > $max ) ) {
				return false;
			}
			return true;
		}
		$value = ( false !== $min ? $min : 0 );
		return false;
	}

	/**
	 * Validate string.
	 *
	 * @param string $value The reference to string.
	 * @param int    $len The maximum length, 0 for unlimited.
	 * @return bool
	 */
	private function validate_string( &$value, $len = 0 ) {
		if ( preg_match( '/<[^<]+>/', $value, $m ) === 1 ) {
			return false;
		}

		if ( 0 === $len ^ strlen( $value ) <= $len ) {
			return true;
		}
		$value = '';
		return false;
	}

	/**
	 * Validate URL.
	 *
	 * @param string $url The reference to url.
	 * @param int    $len The maximum length, 0 for unlimited.
	 * @return bool
	 */
	private function validate_url( &$url, $len = 0 ) {
		if ( 0 === $len ^ strlen( $url ) <= $len ) {
			if ( preg_match( '@^https?://[^\s/$.?#].[^\s]*$@iS', $url ) ) {
				return true;
			}
		}
		$url = '';
		return false;
	}

	/**
	 * Validate enum.
	 *
	 * @param string $value The reference to value.
	 * @param array  $haystack The list of valid values.
	 * @return bool
	 */
	private function validate_enum( &$value, $haystack ) {
		if ( in_array( strtolower( $value ), $haystack, true ) ) {
			return true;
		}
		$value = $haystack[0];
		return false;
	}

	/**
	 * Validate field.
	 *
	 * @param string $field The name of field.
	 * @param mixed  $value The reference to value.
	 * @return boolean
	 */
	public function validate_field( $field, &$value ) {
		$ret = false;
		switch ( $field ) {
			case 'p24_session_id':
				$ret = $this->validate_string( $value, 100 );
				break;
			case 'p24_description':
				$ret = $this->validate_string( $value, 1024 );
				break;
			case 'p24_address':
				$ret = $this->validate_string( $value, 80 );
				break;
			case 'p24_country':
			case 'p24_language':
				$ret = $this->validate_string( $value, 2 );
				break;
			case 'p24_client':
			case 'p24_city':
				$ret = $this->validate_string( $value, 50 );
				break;
			case 'p24_merchant_id':
			case 'p24_pos_id':
			case 'p24_order_id':
			case 'p24_amount':
			case 'p24_method':
			case 'p24_time_limit':
			case 'p24_channel':
			case 'p24_shipping':
				$ret = $this->validate_number( $value );
				break;
			case 'p24_wait_for_result':
				$ret = $this->validate_number( $value, 0, 1 );
				break;
			case 'p24_api_version':
				$ret = $this->validate_version( $value );
				break;
			case 'p24_sign':
				if ( strlen( $value ) === 32 && ctype_xdigit( $value ) ) {
					$ret = true;
				} else {
					$value = '';
				}
				break;
			case 'p24_url_return':
			case 'p24_url_status':
				$ret = $this->validate_url( $value, 250 );
				break;
			case 'p24_currency':
				$ret = (bool) preg_match( '/^[A-Z]{3}$/', $value );
				if ( ! $ret ) {
					$value = '';
				}
				break;
			case 'p24_email':
				$ret = $this->validate_email( $value );
				break;
			case 'p24_encoding':
				$ret = $this->validate_enum( $value, array( 'iso-8859-2', 'windows-1250', 'urf-8', 'utf8' ) );
				break;
			case 'p24_transfer_label':
				$ret = $this->validate_string( $value, 20 );
				break;
			case 'p24_phone':
				$ret = $this->validate_string( $value, 12 );
				break;
			case 'p24_zip':
				$ret = $this->validate_string( $value, 10 );
				break;
			default:
				if ( strpos( $field, 'p24_quantity_' ) === 0 || strpos( $field, 'p24_price_' ) === 0 || strpos( $field, 'p24_number_' ) === 0 ) {
					$ret = $this->validate_number( $value );
				} elseif ( strpos( $field, 'p24_name_' ) === 0 || strpos( $field, 'p24_description_' ) === 0 ) {
					$ret = $this->validate_string( $value, 127 );
				} else {
					$value = '';
				}
				break;
		}
		return $ret;
	}

	/**
	 * Filter value.
	 *
	 * @param string           $field The name of field.
	 * @param string|float|int $value The value to test.
	 * @return bool|string
	 */
	public function filter_value( $field, $value ) {
		return ( $this->validate_field( $field, $value ) ) ? addslashes( $value ) : false;
	}
}
