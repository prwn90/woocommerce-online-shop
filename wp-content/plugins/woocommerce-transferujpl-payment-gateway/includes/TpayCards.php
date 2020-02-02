<?php

use tpay\Lang;
use tpay\PaymentCard;
use tpay\TException;
use tpay\Util;
use tpay\Validate;
use tpay\CardAPI;

require_once 'TpayGatewayBase.php';

class WC_Gateway_Tpay_Cards extends TpayGatewayBase
{
    const CARDDATA = 'card_data';

    const CLIENTNAME = 'client_name';

    const CLIENTEMAIL = 'client_email';

    const CURRENCY = 'currency';

    const TPAY_ID = 'tpayID';

    const ORDER_ID = 'order_id';

    const GATEWAY_ID = 'tpaycards';

    const GATEWAY_NAME = 'WC_Gateway_Tpay_Cards';

    private $midId = 11;

    private $debugMode;

    private $cardApiKey;

    private $cardApiPassword;

    private $verificationCode;

    private $hashAlg;

    private $keyRSA;

    private $paymentMethodDescription;

    public function __construct()
    {
        $this->id = __(static::GATEWAY_ID, static::WOOCOMMERCE);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        $this->setEnvironment();
        $this->init_form_fields();
        $this->shippingMethods = $this->get_option('shipping_methods', array());
        $this->is_available();
        $this->setConfig();
        $this->supports = array('refunds');
        $this->setSubscriptionsSupport();
        add_action('woocommerce_api_wc_gateway_tpay_cards', array($this, 'gateway_communication'));

        parent::__construct();
    }

    public function init_form_fields()
    {
        include_once 'SettingsTpayCards.php';
        $settingsTpay = new SettingsTpayCards();
        $shippingSettings = $this->getShippingMethods();
        if (!is_array($shippingSettings)) {
            $shippingSettings = array();
        }
        $this->form_fields = $settingsTpay->getSettings($shippingSettings);
    }

    public function is_available()
    {
        if ((int)filter_input(INPUT_GET, static::TPAY_ID)) {
            $this->midId = (int)filter_input(INPUT_GET, static::TPAY_ID);

            return parent::is_available();
        } elseif (filter_input(INPUT_POST, static::ORDER_ID)) {
            $id = explode('|', filter_input(INPUT_POST, static::ORDER_ID));
            if (isset($id[1])) {
                $this->midId = $id[1];

                return parent::is_available();
            }

            return false;
        } elseif (isset(WC()->session) && !is_null(WC()->session)) {
            if ($this->isAvailableForShippingMethod($this->shippingMethods) === false) {
                return false;
            }
            $saleCurrency = get_woocommerce_currency();
            $this->setMidForCurrency($saleCurrency);
            if ($this->midId === 11) {
                return false;
            }
            try {
                Validate::validateCardCurrency($saleCurrency);

                return parent::is_available();
            } catch (TException $exception) {
                return false;
            }
        } else {
            return parent::is_available();
        }
    }

    public function admin_options()
    {
        include_once '_tpl/settingsAdminCards.phtml';
    }

    public function basketReload()
    {
        //przeladowanie koszyka zamowienia po wybraniu platnosci tpay.com
        include_once '_tpl/basketReload.html';
    }

    public function gateway_communication()
    {
        if (isset($_POST['type']) && $_POST['type'] === 'deregister') {
            $this->verifyDeregisterNotification();
            exit;
        }
        $paymentCard = new PaymentCard(
            $this->cardApiKey,
            $this->cardApiPassword,
            $this->verificationCode,
            $this->hashAlg,
            $this->keyRSA
        );
        if (isset($_POST['type']) && in_array($_POST['type'], array('sale', 'refund'))) {
            $this->verifyNotification($paymentCard);
            exit;
        }
        if (filter_input(INPUT_GET, static::ORDER_ID)) {
            $paymentResult = false;
            $orderId = filter_input(INPUT_GET, static::ORDER_ID, FILTER_VALIDATE_INT);
            $order = new WC_Order($orderId);
            $transactionData = $this->getTransactionConfig($orderId);
            $savedCardId = filter_input(INPUT_GET, 'savedId', FILTER_VALIDATE_INT);
            $this->setTpayOrder($orderId, $this->midId, $transactionData['language']);
            if ($savedCardId > 0) {
                $user = wp_get_current_user();
                $userId = $user->ID;
                $clientCards = $this->getClientCards($userId);
                foreach ($clientCards as $row => $card) {
                    if (isset($card['id']) && $savedCardId === (int)$card['id']) {
                        $paymentResult = $this->payBySavedCard($paymentCard, $transactionData, $order, $card);
                    }
                }
            } elseif (filter_input(INPUT_GET, static::CARDDATA)) {
                $paymentResult = $this->payByNewCard($paymentCard, $transactionData, $order);
            }
            if ($paymentResult === false) {
                $this->tryToPayByRedirect($paymentCard, $transactionData, $order);
            } else {
                $successUrl = $transactionData['return_url'];
                header("Location: ".$successUrl);
            }
            exit;
        } else {
            //exit must be present in this function!
            exit;
        }
    }

    /**
     * @param PaymentCard $paymentCard
     * @param array $transactionData
     * @return bool|mixed
     */
    public function processCardSale($paymentCard, $transactionData)
    {
        if ($transactionData['language'] === 'pl') {
            Lang::setLang('pl');
        } else {
            Lang::setLang('en');
        }
        if ($this->debugMode === 'yes') {
            var_dump($transactionData);
        }
        $_POST[static::CARDDATA] = $transactionData[static::CARDDATA];
        $_POST['client_name'] = $transactionData['name'];
        $_POST['client_email'] = $transactionData['email'];
        $_POST['card_save'] = $transactionData['card_save'];

        return $paymentCard->secureSale(
            $transactionData['amount'],
            $transactionData[static::ORDER_ID],
            $transactionData['description'],
            $transactionData[static::CURRENCY],
            true,
            $transactionData['language'],
            $transactionData['return_url'],
            $transactionData['return_error_url'],
            $transactionData['module']
        );
    }

    /**
     * @param PaymentCard $paymentCard
     * @throws TException
     */
    public function verifyNotification($paymentCard)
    {
        $resp = $paymentCard->handleNotification($this->validateProxyServer);
        if (isset($resp[static::ORDER_ID])) {
            $orderId = explode('|', $resp[static::ORDER_ID]);
            $order = new WC_Order($orderId[0]);
            $orderCurrency = method_exists($order, 'get_currency') ?
                $order->get_currency() : $order->get_order_currency();
            $orderCurrency = Validate::validateCardCurrency($orderCurrency);
            if (isset($resp['type']) && $resp['type'] === 'sale' && $order->get_total() !== $resp['amount']) {
                throw new TException(sprintf(
                    'Order amount mismatch. Order: %s paid: %s', $order->get_total()), $resp['amount']
                );
            }
            $paymentCard->validateSign(
                $resp['sign'],
                $resp['sale_auth'],
                $resp['card'],
                $resp['amount'],
                $resp['date'],
                $resp['status'],
                $orderCurrency,
                isset($resp['test_mode']) ? '1' : '', $resp['order_id'],
                $resp['type'],
                isset($resp['sale_ref']) ? $resp['sale_ref'] : '',
                isset($resp['cli_auth']) ? $resp['cli_auth'] : '',
                isset($resp['reason']) ? $resp['reason'] : ''
            );
            $this->trId = $resp['sale_auth'];
            $this->completePayment($order, $resp);
        }
    }

    public function removeCard($token)
    {
        try {
            global $wpdb;
            require_once(ABSPATH.'wp-admin/includes/upgrade.php');
            $wpdb->delete($this->authTableName, array('cliAuth' => $token));
        } catch (Exception $e) {
            Util::logLine($e->getMessage());
        }
    }

    public function payment_fields()
    {
        $user = wp_get_current_user();
        $clientCards = array();
        if ($user->ID) {
            $clientCards = $this->getClientCards($user->ID);
        }
        $data['userCards'] = array();
        foreach ($clientCards as $card) {
            $data['userCards'][] = array(
                'cardId' => $card['id'],
                'shortCode' => $card['cardNoShort'],
            );
        }
        $data['rsa_key'] = $this->keyRSA;
        $lang = new Lang;
        strcmp($this->language, 'pl_PL') === 0 ? $lang::setLang('pl') : $lang::setLang('en');
        $data['regulation_url'] = static::TPAY_REGULATIONS_URL;
        include_once "_tpl/cardForm.phtml";
    }

    public function process_payment($orderId)
    {
        if (isset($_POST['tpay-cards-regulations-input']) && (int)$_POST['tpay-cards-regulations-input'] !== 1) {
            wc_add_notice(
                __(
                    'Aby skorzystać z tej metody płatności musisz zaakceptować regulamin systemu Tpay.',
                    static::WOOCOMMERCE
                ),
                'error'
            );

            return array(static::RESULT => 'fail');
        }

        if (isset($_POST['savedId']) && $_POST['savedId'] === 'new' && empty($_POST[static::CARDDATA])) {
            wc_add_notice(
                __(
                    'Wybierz zapisaną kartę lub wprowadź poprawne dane nowej karty.',
                    static::WOOCOMMERCE
                ),
                'error'
            );

            return array(static::RESULT => 'fail');
        }
        if (!isset($_POST['savedId']) && empty($_POST[static::CARDDATA])) {
            wc_add_notice(__('Wprowadź poprawne dane karty i zaakceptuj regulamin.', static::WOOCOMMERCE), 'error');

            return array(static::RESULT => 'fail');
        }
        $cardSave = filter_input(INPUT_POST, 'card_save');
        if (class_exists('WC_Subscriptions_Order', false)
            && WC_Subscriptions_Order::order_contains_subscription($orderId)
        ) {
            $subscriptionInitialAmount = WC_Subscriptions_Order::get_total_initial_payment($orderId);
            if ($subscriptionInitialAmount <= 0) {
                wc_add_notice(
                    __('Wybrana metoda płatności nie obsługuje zamówień z darmowym okresem próbnym. Prosimy wybrać inną
                     metodę płatności.', static::WOOCOMMERCE),
                    'error'
                );

                return array(static::RESULT => 'fail');
            }
            if ($cardSave !== 'on' && !empty($_POST[static::CARDDATA])
                && (!isset($_POST['savedId']) || $_POST['savedId'] === 'new')
            ) {
                wc_add_notice(
                    __('W celu zakupu usługi subskrypcyjnej należy wyrazić zgodę na zapisanie karty.',
                        static::WOOCOMMERCE),
                    'error'
                );

                return array(static::RESULT => 'fail');
            }
        }
        WC()->cart->empty_cart();

        return array(
            static::RESULT => static::SUCCESS,
            static::REDIRECT => add_query_arg(array(
                static::CARDDATA => filter_input(INPUT_POST, static::CARDDATA),
                static::TPAY_ID => filter_input(INPUT_POST, static::TPAY_ID),
                static::ORDER_ID => $orderId,
                'card_save' => $cardSave,
                'savedId' => filter_input(INPUT_POST, 'savedId'),
            ), $this->notifyLink),
        );
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = new WC_Order($order_id);
        $midId = $this->getOrderMidId($order->get_id());
        $paymentCard = new CardApi(
            $this->get_option('cardApiKey'.$midId),
            $this->get_option('cardApiPassword'.$midId),
            $this->get_option('verificationCode'.$midId),
            $this->get_option('hashAlg'.$midId)
        );
        $currency = Validate::validateCardCurrency(method_exists($order, 'get_currency') ?
            $order->get_currency() : $order->get_order_currency());
        $lang = $this->getClientLanguageByOrderId($order_id);
        if (empty($reason)) {
            switch ($lang) {
                default:
                case 'pl':
                    $reason = __('Zwrot', static::WOOCOMMERCE);
                    break;
                case 'en':
                    $reason = __('Refund', static::WOOCOMMERCE);
                    break;
                case 'de':
                    $reason = __('die Ruckzahlung', static::WOOCOMMERCE);
                    break;
            }
        }
        try {
            $refundResult = $paymentCard->refund('', $order->get_transaction_id(), $reason, $amount, $currency, $lang);

            return ($refundResult['status'] === 'correct');
        } catch (Exception $exception) {
            Util::log('Exception in refunding card payment', $exception->getMessage());

            return false;
        }
    }

    /**
     * @param float $chargeAmount
     * @param WC_Order $order
     * @return bool
     */
    public function scheduled_subscription_payment($chargeAmount, $order)
    {
        if ($order->get_status() !== 'pending') {
            return false;
        }
        $currency = method_exists($order, 'get_currency') ? $order->get_currency() :
            $order->get_order_currency();
        $userId = $order->get_user_id();
        $this->setMidForCurrency($currency);
        $transactionData = $this->getTransactionConfig($order->get_id());
        $transactionData['amount'] = $chargeAmount;
        $transactionData['description'] = $this->getSubRenewalDescription(get_user_locale($userId)) .
            $order->get_order_number();
        $paymentCard = new PaymentCard(
            $this->get_option('cardApiKey'.$this->midId),
            $this->get_option('cardApiPassword'.$this->midId),
            $this->get_option('verificationCode'.$this->midId),
            $this->get_option('hashAlg'.$this->midId),
            $this->get_option('keyRSA'.$this->midId)
        );
        $userCards = $this->getClientCards($userId);
        if (empty($userCards)) {
            $order->add_order_note(__('Użytkownik wyrejestrował wszystkie karty', static::WOOCOMMERCE));
        }
        foreach ($userCards as $row => $card) {
            $result = $this->payBySavedCard($paymentCard, $transactionData, $order, $card);
            if ($result === true) {
                $this->setTpayOrder($order->get_id(), $this->midId, $transactionData['language']);
                WC_Subscriptions_Manager::process_subscription_payments_on_order($order);

                return true;
            } else {
                $order->add_order_note(__('Nieudana płatność kartą nr ', static::WOOCOMMERCE).$card['cardNoShort']);
            }
        }
        WC_Subscriptions_Manager::process_subscription_payment_failure_on_order($order);

        return false;
    }

    /**
     * @param PaymentCard $paymentCard
     * @param array $transactionData
     * @param WC_Order $order
     * @param array $card
     * @return bool
     */
    protected function payBySavedCard($paymentCard, $transactionData, $order, $card)
    {
        $order->add_order_note(__('Płatność zapisaną kartą ', static::WOOCOMMERCE).$card['cardNoShort']);
        $transaction = $paymentCard->getPresaleTransaction(
            $card['cliAuth'],
            $transactionData['description'],
            $transactionData['amount'],
            $transactionData[static::ORDER_ID],
            $transactionData['language'],
            Validate::validateCardCurrency($transactionData[static::CURRENCY])
        );
        $response = $paymentCard->cardSavedSale($card['cliAuth'], $transaction['sale_auth']);
        if ((int)$response['result'] === 1 && $response['status'] === 'correct') {
            $this->trId = $response['sale_auth'];
            $this->completePayment($order, $response);

            return true;
        }
        if (isset($response['err_code']) && (int)$response['err_code'] === 8) {
            $this->removeCard($card['cliAuth']);
        }

        return false;
    }

    private function getSubRenewalDescription($language)
    {
        switch ($language) {
            case (stripos($language, 'en') !== false):
                $description = __('Subscription renewal, order no ', static::WOOCOMMERCE);
                break;
            case (stripos($language, 'de') !== false):
                $description = __('Abonnementverlängerung, Best.-Nr ', static::WOOCOMMERCE);
                break;
            default:
                $description = __('Odnowienie subskrypcji, zamówienie nr ', static::WOOCOMMERCE);
                break;
        }

        return $description;
    }

    private function setMidForCurrency($saleCurrency)
    {
        $counter = 10;
        $validMidId = array();
        $midForCurrency = '';
        $midPLN = '';
        for ($i = 1; $i <= $counter; $i++) {
            if ($this->get_option('midDomain'.$i) === $this->siteDomain) {
                $validMidId[] = $i;
            }
        }
        for ($i = 0; $i < count($validMidId); $i++) {
            $midCurrency = explode(',', $this->get_option('midCurrency'.$validMidId[$i]));
            $midType = $this->get_option('midType'.$validMidId[$i]);
            $midOn = $this->get_option('midOn'.$validMidId[$i]);

            if ((int)$midType === 0 && $saleCurrency === 'PLN' && $midOn !== 'no') {
                $this->midId = $validMidId[$i];
                $midPLN = $validMidId[$i];
                break;
            }
            foreach ($midCurrency as $key => $value) {
                if ((strcasecmp($midCurrency[$key], $saleCurrency) === 0
                        || strcasecmp($midCurrency[$key], filter_input(INPUT_POST, static::CURRENCY)) === 0)
                    && $midOn !== 'no' && (int)$midType === 1
                ) {
                    $this->midId = $validMidId[$i];
                    $midForCurrency = $validMidId[$i];

                } elseif ($midCurrency[$key] === '' && $midOn !== 'no') {
                    $this->midId = $validMidId[$i];
                }
            }
        }
        if (!empty($midForCurrency) && empty($midPLN)) {
            $this->midId = $midForCurrency;
        }
    }

    /**
     * @param int $orderId
     * @return array
     */
    private function getTransactionConfig($orderId)
    {
        $transactionConfig = $this->getBaseTransactionConfigByOrderId($orderId);
        if ((int)wp_get_current_user()->ID > 0 && filter_input(INPUT_GET, 'card_save')) {
            $transactionConfig['card_save'] = filter_input(INPUT_GET, 'card_save');
        } else {
            $transactionConfig['card_save'] = false;
        }
        $transactionConfig[static::ORDER_ID] = $orderId.'|'.$this->midId;
        $transactionConfig[static::CARDDATA] = str_replace(' ', '+', filter_input(INPUT_GET, static::CARDDATA));
        $order = new WC_Order($orderId);
        $transactionConfig[static::CURRENCY] = method_exists($order, 'get_currency') ? $order->get_currency() :
            $order->get_order_currency();

        return $transactionConfig;
    }

    /**
     * @param PaymentCard $paymentCard
     * @param array $transactionData
     * @param WC_Order $order
     * @return bool
     */
    private function payByNewCard($paymentCard, $transactionData, $order)
    {
        $response = $this->processCardSale($paymentCard, $transactionData);
        if (isset($response[static::RESULT])
            && (int)$response[static::RESULT] === 1
            && $response['status'] === 'correct'
        ) {
            $paymentCard->validateSign($response['sign'],
                $response['sale_auth'],
                $response['card'],
                number_format((float)$transactionData['amount'], 2, '.', ''),
                $response['date'],
                $response['status'],
                Validate::validateCardCurrency($transactionData[static::CURRENCY]),
                isset($response['test_mode']) ? '1' : '',
                '',
                '',
                '',
                isset($response['cli_auth']) ? $response['cli_auth'] : ''
            );
            $order->add_order_note(__('Płatność kartą bez 3DS', static::WOOCOMMERCE));
            $this->trId = $response['sale_auth'];
            $this->completePayment($order, $response);

            return true;
        } elseif (isset($response['3ds_url'])) {
            $order->add_order_note(__('Płatność kartą - przekierowano klienta do bramki 3DS', static::WOOCOMMERCE));
            wp_redirect($response['3ds_url']);
            exit;
        } else {
            return false;
        }
    }

    /**
     * @param PaymentCard $paymentCard
     * @param array $transactionData
     * @param WC_Order $order
     */
    private function tryToPayByRedirect($paymentCard, $transactionData, $order)
    {
        $response = $paymentCard->getTransactionUrl(
            $transactionData['address'],
            $transactionData['email'],
            $transactionData['description'],
            $transactionData['amount'],
            Validate::validateCardCurrency($transactionData[static::CURRENCY]),
            $transactionData[static::ORDER_ID],
            !$transactionData['card_save'],
            $transactionData['language'],
            $transactionData['return_url'],
            $transactionData['return_error_url']
        );
        if (isset($response['sale_auth'])) {
            $transactionUrl = 'https://secure.tpay.com/cards/?sale_auth='.$response['sale_auth'];
            $order->add_order_note(__(
                'Nieudana płatność kartą - przekierowano klienta do panelu transakcyjnego. Link transakcji: ',
                    static::WOOCOMMERCE
                ).$transactionUrl
            );
            wp_redirect($transactionUrl);
        } else {
            $this->completePayment($order, $response);
            $errorUrl = $transactionData['return_error_url'];
            if ($this->debugMode === 'yes') {
                var_dump($response);
            } else {
                header("Location: ".$errorUrl);
            }
        }
    }

    private function setTpayOrder($orderId, $midId, $language)
    {
        $language = strtolower($language);
        $orderId = (int)$orderId;
        $midId = (int)$midId;
        $sql = "INSERT INTO $this->tableName SET wooId = $orderId, midId = $midId, client_language = '$language'";
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function verifyDeregisterNotification()
    {
        $paymentCard = new PaymentCard('1', '1', '1', 'sha1', '1');
        $notification = $paymentCard->handleNotification($this->validateProxyServer);
        if (
            isset($notification['type'])
            && $notification['type'] === 'deregister'
            && isset($notification['cli_auth'])
        ) {
            $this->removeCard($notification['cli_auth']);
        }
    }

    /**
     * @param WC_Order $order
     * @param array $notification
     * @return bool
     */
    private function completePayment($order, $notification)
    {
        try {
            if (
                isset($notification['type'], $notification['amount'], $notification['sale_auth'])
                && $notification['type'] === 'refund'
                && $notification['status'] === 'correct'
            ) {
                $this->addOrderRefund($notification, $order);

                return true;
            }
            if (isset($notification['status']) && $notification['status'] === 'correct') {
                $order->add_order_note(__('Zapłacono.', static::WOOCOMMERCE));
                $order->payment_complete($this->trId);
                if ($this->autoFinishOrder === 1) {
                    $order->update_status('completed');
                }
                if (isset($notification['cli_auth'], $notification['card']) && $order->get_user_id() > 0) {
                    $this->saveClientToken($order->get_user_id(), $notification);
                }

                return true;
            }
            $reason = '';
            if (isset($notification['card'])) {
                $reason .= isset($notification['reason']) ? $notification['reason'].' ' : '';
                $reason .= isset($notification['err_desc']) ? $notification['err_desc'] : '';
            }
            if ($reason !== '') {
                $order->update_status('failed', __('Zapłata nie powiodła się.', static::WOOCOMMERCE). ' ' .$reason);

                return true;
            }
        } catch (Exception $exception) {
            Util::log('Exception in completing payment', $exception->getMessage().print_r($notification, true));

            return false;
        }

        return true;
    }

    /**
     * @param int $userId
     * @param array $notification
     */
    private function saveClientToken($userId, $notification)
    {
        $userId = (int)$userId;
        $token = $notification['cli_auth'];
        $cardNoShort = $notification['card'];
        $sql = "INSERT INTO $this->authTableName SET clientId = $userId, cliAuth = '$token', cardNoShort = '$cardNoShort', midId = $this->midId";
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function getClientCards($clientId)
    {
        global $wpdb;
        $clientId = (int)$clientId;
        $sql = "SELECT id, cliAuth, cardNoShort FROM $this->authTableName WHERE clientId = $clientId AND midId = $this->midId";
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }

    private function getOrderMidId($orderId)
    {
        global $wpdb;
        $orderId = (int)$orderId;
        $sql = "SELECT midId FROM $this->tableName WHERE wooId = $orderId";
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $result = $wpdb->get_results($sql);
        return $result[0]->midId;
    }

    private function getClientLanguageByOrderId($orderId)
    {
        global $wpdb;
        $orderId = (int)$orderId;
        $sql = "SELECT client_language FROM $this->tableName WHERE wooId = $orderId";
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $result = $wpdb->get_results($sql);

        return $result[0]->client_language;
    }

    /**
     * @param array $notification
     * @param WC_Order $order
     */
    private function addOrderRefund($notification, $order)
    {
        $order->add_order_note(sprintf(__(
            'Wykonano zwrot transakcji. Kwota zwrotu: %s', static::WOOCOMMERCE),
            number_format($notification['amount'], 2)
        ));
        if ($order->get_total() === $notification['amount']) {
            $order->update_status('refunded', 'Status zamówienia zmieniony na zwrócone.');
        } else {
            wc_create_refund(
                array(
                    'amount' => $notification['amount'],
                    'reason' => sprintf(__('Identyfikator zwrotu: %s', static::WOOCOMMERCE), $notification['sale_auth']),
                    'order_id' => $order->get_id(),
                )
            );
        }
    }

    private function setConfig()
    {
        $this->method_title = __('Tpay credit cards', static::WOOCOMMERCE);
        $this->notifyLink = add_query_arg('wc-api', static::GATEWAY_NAME, $this->siteDomain);
        $this->title = $this->get_option('title', 'Tpay credit cards');
        $this->debugMode = $this->get_option('debugMode', 'no');
        $this->transactionDescription = $this->get_option('opis'.$this->midId);
        $this->surchargeSetting = (int)$this->get_option(static::DOPLATA.$this->midId, 0);
        $this->surchargeAmount = (float)$this->get_option(static::KWOTA_DOPLATY.$this->midId, 0.00);
        $this->paymentMethodDescription = $this->get_option('description'.$this->midId, '');
        $this->cardApiKey = $this->get_option('cardApiKey'.$this->midId, '');
        $this->cardApiPassword = $this->get_option('cardApiPassword'.$this->midId, '');
        $this->verificationCode = $this->get_option('verificationCode'.$this->midId, '');
        $this->hashAlg = $this->get_option('hashAlg'.$this->midId, 'sha1');
        $this->keyRSA = $this->get_option('keyRSA'.$this->midId, '');
        $this->autoFinishOrder = (int)$this->get_option('auto_finish_order', 0);
        $this->validateProxyServer = (int)$this->get_option('proxy_server', 0);
    }

    private function setSubscriptionsSupport()
    {
        $subscriptionsSupport = array(
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions',
        );
        if (class_exists('WC_Subscriptions', false)) {
            $this->supports = array_merge($this->supports, $subscriptionsSupport);
            add_action(
                'woocommerce_scheduled_subscription_payment_'.$this->id,
                array($this, 'scheduled_subscription_payment'), 10, 2
            );
        }
    }

}
