<?php
/**
 * File that define P24_Communication_Parser class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to parse input from Przelewy24 backend.
 */
class P24_Communication_Parser {
	/**
	 * Set for true if response is from Przelewy24.
	 *
	 * @var bool
	 */
	private $is_przelewy24_response = false;

	/**
	 * Set for true if CRC is valid.
	 *
	 * @var bool
	 */
	private $is_crc_valid = false;

	/**
	 * The currency of response.
	 *
	 * @var null|string
	 */
	private $currency;

	/**
	 * The response from Przelewy24.
	 *
	 * @var null|array
	 */
	private $response;

	/**
	 * Class to validate messages.
	 *
	 * @var P24_Message_Validator
	 */
	private $message_validator;

	/**
	 * P24_Communication_Parser constructor.
	 *
	 * @param P24_Message_Validator $message_validator P24_Message_Validator instance.
	 */
	public function __construct( P24_Message_Validator $message_validator ) {
		$this->message_validator = $message_validator;
	}

	/**
	 * Return request.
	 *
	 * @return array
	 */
	private function get_post_data() {
		wp_verify_nonce( null ); /* There is no nonce in request. */
		if ( is_array( $_POST ) ) {
			return $_POST;
		} else {
			return array();
		}
	}

	/**
	 * Parse and validate POST response data from Przelewy24.
	 *
	 * @param WC_Gateway_Przelewy24 $gateway The gateway.
	 * @return bool True on success, false otherwise.
	 */
	public function parse_status_response( WC_Gateway_Przelewy24 $gateway ) {
		$this->is_przelewy24_response = false;
		$this->is_crc_valid           = false;
		$this->currency               = null;
		$this->response               = null;

		$data = $this->get_post_data();
		if ( ! isset( $data['p24_session_id'], $data['p24_order_id'], $data['p24_merchant_id'], $data['p24_pos_id'], $data['p24_amount'], $data['p24_currency'], $data['p24_method'], $data['p24_sign'] ) ) {
			/* Early exit. */
			return false;
		}

		$this->is_przelewy24_response = true;

		$session_id  = $this->message_validator->filter_value( 'p24_session_id', $data['p24_session_id'] );
		$merchant_id = $this->message_validator->filter_value( 'p24_merchant_id', $data['p24_merchant_id'] );
		$pos_id      = $this->message_validator->filter_value( 'p24_pos_id', $data['p24_pos_id'] );
		$order_id    = $this->message_validator->filter_value( 'p24_order_id', $data['p24_order_id'] );
		$amount      = $this->message_validator->filter_value( 'p24_amount', $data['p24_amount'] );
		$currency    = $this->message_validator->filter_value( 'p24_currency', $data['p24_currency'] );
		$method      = $this->message_validator->filter_value( 'p24_method', $data['p24_method'] );
		$sign        = $this->message_validator->filter_value( 'p24_sign', $data['p24_sign'] );

		$this->currency = $currency;
		$config         = $gateway->load_settings_from_db_formatted( $currency );
		$expected_sign  = md5( $session_id . '|' . $order_id . '|' . $amount . '|' . $currency . '|' . $config->get_salt() );
		if ( $merchant_id === $config->get_merchant_id()
			&& $pos_id === $config->get_shop_id()
			&& $sign === $expected_sign
		) {
			$this->is_crc_valid = true;
			$this->response     = array(
				'p24_session_id' => $session_id,
				'p24_order_id'   => $order_id,
				'p24_amount'     => $amount,
				'p24_currency'   => $currency,
				'p24_method'     => $method,
			);
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Return if request was valid.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->is_crc_valid;
	}

	/**
	 * Get currency.
	 *
	 * @return string
	 * @throws LogicException If request is not validated.
	 */
	public function get_currency() {
		if ( $this->currency ) {
			return $this->currency;
		} else {
			$msg = 'You have to validate request first.';
			throw new LogicException( $msg );
		}
	}
}
