<?php

use tpay\Lang;
use tpay\PaymentBasic;
use tpay\TException;
use tpay\TransactionAPI;
use tpay\Util;
use tpay\Validate;

require_once 'TpayGatewayBase.php';

class WC_Gateway_Tpay_Basic extends TpayGatewayBase
{
    const GATEWAY_NAME = 'WC_Gateway_Tpay_Basic';

    //MUST BE OLD NAME!
    const GATEWAY_ID = 'transferuj';

    const BANK_VIEW = 'bank_view';

    private $seller_id;

    private $security_code;

    private $blik_on;

    private $api_key;

    private $api_pass;

    private $enable_IP_validation;

    private $online_methods_only;

    public function __construct()
    {
        $this->setEnvironment();
        $this->setConfig();
        $this->init_form_fields();
        if (strlen($this->api_key) === 40 && strlen($this->api_pass) > 0) {
            $this->supports = array('refunds');
        }
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        parent::__construct();
    }

    public function init_form_fields()
    {
        include_once 'SettingsTpay.php';
        $charge = $this->get_option(static::DOPLATA);
        $list = $this->get_option('bank_list');
        $shippingSettings = $this->getShippingMethods();
        if (!is_array($shippingSettings)) {
            $shippingSettings = array();
        }
        $settingsTpay = new SettingsTpay();
        $this->form_fields = $settingsTpay->getSettings($charge, $list, $shippingSettings);
    }

    /**
     * Check if this gateway is enabled and available in the user's country.
     * @return bool
     */
    public function is_available()
    {
        if (get_woocommerce_currency() !== "PLN" || $this->enabled !== 'yes') {
            return false;
        }
        if ($this->isAvailableForShippingMethod($this->shippingMethods) === false) {
            return false;
        }

        return parent::is_available();
    }

    public function basketReload()
    {
        //przeladowanie koszyka zamowienia po wybraniu platnosci tpay.com
        include_once '_tpl/basketReload.html';
    }

    /**
     * Generates box with gateway name and description, terms acceptance checkbox and channel list
     */
    public function payment_fields()
    {
        $lang = new Lang;
        strcmp($this->language, 'pl_PL') === 0 ? $lang::setLang('pl') : $lang::setLang('en');
        $orderAmount = $this->getCartTotal();
        $data['merchant_id'] = $this->seller_id;
        $data['online_only'] = $this->online_methods_only;
        $data['show_regulations_checkbox'] = true;
        $data['regulation_url'] = static::TPAY_REGULATIONS_URL;
        $data['form'] = '';
        $data['showInstallments'] = $orderAmount >= 300 && $orderAmount <= 9259;
        echo $this->description;
        if ($this->blik_on === 1) {
            include_once '_tpl/blikForm.phtml';
        }
        if ($this->paymentType() === 1 || $this->paymentType() === 2) {
            $data['small_list'] = $this->paymentType() === 2;
            include_once '_tpl/bankSelection.phtml';
        }
    }

    public function paymentType()
    {
        if ($this->get_option(static::BANK_LIST) === '0' && $this->get_option(static::BANK_VIEW) === '0') {
            $type = 1;
        } elseif ($this->get_option(static::BANK_LIST) === '0' && $this->get_option(static::BANK_VIEW) === '1') {
            $type = 2;
        } elseif ($this->get_option(static::BANK_LIST) === '1') {
            $type = 3;
        } else {
            $type = 0;
        }

        return $type;
    }

    /**
     * Generates admin options
     */
    public function admin_options()
    {
        include_once '_tpl/settingsAdmin.phtml';
    }

    /**
     * Sends and receives data to/from tpay.com server
     */
    public function gateway_communication()
    {
        if (filter_input(INPUT_GET, static::ORDER_ID)) {
            $transactionConfig = $this->getTransactionConfig(filter_input(INPUT_GET, static::ORDER_ID));
            $this->createTransaction($transactionConfig);
        } else {
            $this->verifyPaymentResponse();
        }
        //exit must be present in this function!
        exit;
    }

    public function getTransactionConfig($orderId)
    {
        $transactionConfig = $this->getBaseTransactionConfigByOrderId($orderId, $this->security_code);
        if ($this->online_methods_only === 1) {
            $transactionConfig['online'] = 1;
        }
        if ((int)filter_input(INPUT_GET, static::REGULATIONS) === 1) {
            $transactionConfig['accept_tos'] = 1;
        }
        if (filter_input(INPUT_GET, static::GROUP)) {
            $transactionConfig['group'] = (int)filter_input(INPUT_GET, 'group');
        }

        return $transactionConfig;
    }

    public function createTransaction($transactionConfig)
    {
        $optionalParameters = array('address', 'city', 'country', 'language', 'zip', 'phone');
        foreach ($optionalParameters as $parameter) {
            if (array_key_exists($parameter, $transactionConfig) && strlen($transactionConfig[$parameter]) < 1) {
                unset($transactionConfig[$parameter]);
            }
        }

        if (filter_input(INPUT_GET, static::BLIKCODE) && strlen($_GET[static::BLIKCODE]) === 6) {
            $blikCode = filter_input(INPUT_GET, static::BLIKCODE);
            $transactionConfig['group'] = 150;
            $transactionConfig['accept_tos'] = 1;
            try {
                $transactionAPI = new TransactionAPI(
                    $this->api_key,
                    $this->api_pass,
                    $this->seller_id,
                    $this->security_code
                );
                $resp = $transactionAPI->create($transactionConfig);
                $title = $resp['title'];
                $resp = $transactionAPI->blik($blikCode, $title);
            } catch (TException $exception) {
                $redirectUrl = $transactionConfig['return_error_url'];
                header("Location: " . $redirectUrl);

                return false;
            }
            if ($resp['result'] === 1) {
                $redirectUrl = $transactionConfig['return_url'];
                header("Location: " . $redirectUrl);

                return true;
            } else {
                Util::log('Invalid BLIK code', 'User redirected to transaction panel');
                header("Location: https://secure.tpay.com/?title=" . $title);

                return false;
            }
        }
        try {
            $paymentBasic = new PaymentBasic($this->seller_id, $this->security_code);
            $form = $paymentBasic->getTransactionForm($transactionConfig);
        } catch (TException $exception) {
            return false;
        }
        echo $form;

        return true;
    }

    /**
     * Verifies that no errors have occured during transaction
     */
    public function verifyPaymentResponse()
    {
        try {
            $paymentBasic = new PaymentBasic($this->seller_id, $this->security_code);
            if ($this->enable_IP_validation === false) {
                $paymentBasic->disableValidationServerIP();
            }
            $res = $paymentBasic->checkPayment(Validate::PAYMENT_TYPE_BASIC, $this->validateProxyServer);
        } catch (TException $exception) {
            return;
        }
        $this->trId = $res['tr_id'];
        $this->completePayment($res['tr_crc'], $res);
    }

    public function process_payment($orderId)
    {
        global $woocommerce;
        if (
            isset($_POST[static::BLIKCODE])
            && strlen($_POST[static::BLIKCODE]) > 0
            && $this->isValidBlikCode($_POST[static::BLIKCODE]) === false
        ) {
            wc_add_notice(
                __(
                    'Wprowadzony kod BLIK jest niepoprawny. Kod powinien składać się z sześciu cyfr.',
                    static::WOOCOMMERCE
                ),
                'error'
            );

            return array(static::RESULT => 'fail');
        }
        if (isset($_POST['tpay-regulations-input']) && (int)$_POST['tpay-regulations-input'] !== 1) {
            wc_add_notice(
                __(
                    'Aby skorzystać z tej metody płatności musisz zaakceptować regulamin systemu Tpay.',
                    static::WOOCOMMERCE
                ),
                'error'
            );

            return array(static::RESULT => 'fail');
        }
        $woocommerce->cart->empty_cart();

        return array(
            static::RESULT => static::SUCCESS,
            static::REDIRECT => add_query_arg(array(
                static::REGULATIONS => filter_input(INPUT_POST, 'tpay-regulations-input'),
                static::ORDER_ID => $this->crypt($orderId, $this->security_code),
                static::BLIKCODE => filter_input(INPUT_POST, static::BLIKCODE),
                static::GROUP => filter_input(INPUT_POST, 'tpay-channel-input'),
            ), $this->notifyLink),
        );
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = new WC_Order($order_id);
        try {
            $transactionAPI = new TransactionAPI(
                (string)$this->api_key,
                (string)$this->api_pass,
                (int)$this->seller_id,
                (string)$this->security_code
            );
            $transactionAPI->refundAny($order->get_transaction_id(), $amount);

            return true;
        } catch (TException $exception) {
            return false;
        }
    }

    /**
     * Sets proper transaction status for order based on $status
     * @param int $orderId ; id of an order
     * @param array $notification
     * @return bool
     */
    private function completePayment($orderId, $notification)
    {
        try {
            $order = wc_get_order($orderId);
            if ($notification['tr_status'] === 'CHARGEBACK') {
                $order->update_status('refunded', __('Wykonano zwort transakcji.', static::WOOCOMMERCE), true);

                return true;
            }
            $orderAmount = (double)$order->get_total();
            if ($orderAmount !== $notification['tr_amount']) {
                throw new Exception(
                    sprintf(
                        __('Amounts mismatch: expected %s, received: %s', static::WOOCOMMERCE),
                        $orderAmount,
                        $notification['tr_amount']
                    )
                );
            }
            if ($notification['tr_error'] === 'overpay') {
                $order->add_order_note(__('Zapłacono z nadpłatą.', static::WOOCOMMERCE));
            } elseif ($notification['tr_error'] === 'surcharge') {
                $order->add_order_note(__('Zapłacono z niedopłatą.', static::WOOCOMMERCE));
            } elseif ($notification['tr_error'] === 'none') {
                $order->add_order_note(__('Zapłacono.', static::WOOCOMMERCE));
            }
            $order->payment_complete($this->trId);
            if ($this->autoFinishOrder === 1) {
                $order->update_status('completed');
            }

            return true;
        } catch (Exception $exception) {
            Util::log('Exception in completing payment', $exception->getMessage().print_r($notification, true));

            return false;
        }
    }

    private function isValidBlikCode($code)
    {
        if (strlen($code) !== 6 || !is_numeric($code)) {
            return false;
        }

        return true;
    }

    private function getCartTotal()
    {
        if ($this->wpbo_get_woo_version_number() >= '3.2') {
            $totalTax = WC()->cart->get_cart_contents_tax();
            $totalFee = WC()->cart->get_fee_total();
            $totalProducts = WC()->cart->get_cart_contents_total();
            $orderAmount = $totalProducts + $totalFee + $totalTax;
        } else {
            $orderAmount = WC()->cart->get_cart_total();
        }

        return $orderAmount;
    }

    private function setConfig()
    {
        $this->id = __(static::GATEWAY_ID, static::WOOCOMMERCE);
        $this->title = $this->get_option('title', 'Tpay');
        $this->method_title = __('Tpay', static::WOOCOMMERCE);
        $this->notifyLink = add_query_arg('wc-api', static::GATEWAY_NAME, $this->siteDomain);
        $this->seller_id = (int)$this->get_option('seller_id', 0);
        $this->security_code = $this->get_option('security_code', '');
        $this->blik_on = (int)$this->get_option('blik_on', 0);
        $this->api_key = $this->get_option('api_key', '');
        $this->api_pass = $this->get_option('api_pass', '');
        $this->validateProxyServer = (int)$this->get_option('proxy_server', 0);
        $this->enable_IP_validation = (int)$this->get_option('enable_IP_validation', 1);
        $this->autoFinishOrder = (int)$this->get_option('auto_finish_order', 0);
        $this->shippingMethods = $this->get_option('shipping_methods', array());
        $this->online_methods_only = (int)$this->get_option('online_methods_only');
        $this->transactionDescription = $this->get_option('opis', '');
        $this->surchargeAmount = (float)$this->get_option(static::KWOTA_DOPLATY, 0.00);
        $this->surchargeSetting = (int)$this->get_option(static::DOPLATA, 0);
    }

}
