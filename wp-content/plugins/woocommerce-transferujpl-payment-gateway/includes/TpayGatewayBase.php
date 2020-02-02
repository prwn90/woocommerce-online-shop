<?php

use tpay\TException;
use tpay\Util;

require_once 'lib/src/_class_tpay/Validate.php';
require_once 'lib/src/_class_tpay/Util.php';
require_once 'lib/src/_class_tpay/Exception.php';
require_once 'lib/src/_class_tpay/PaymentBasic.php';
require_once 'lib/src/_class_tpay/PaymentCard.php';
require_once 'lib/src/_class_tpay/CardApi.php';
require_once 'lib/src/_class_tpay/Curl.php';
require_once 'lib/src/_class_tpay/TransactionApi.php';
require_once 'lib/src/_class_tpay/Lang.php';

abstract class TpayGatewayBase extends WC_Payment_Gateway
{
    const REGULATIONS = 'regulations';

    const BLIKCODE = 'blik_code';

    const ORDER_ID = 'orderId';

    const GROUP = 'group';

    const RESULT = 'result';

    const REDIRECT = 'redirect';

    const SUCCESS = 'success';

    const TR_CRC = 'tr_crc';

    const TR_ERROR = 'tr_error';

    const KWOTA_DOPLATY = 'kwota_doplaty';

    const BANK_LIST = 'bank_list';

    const DOPLATA = 'doplata';

    const WOOCOMMERCE = 'woocommerce';

    const HTTP = 'http://';

    const HTTPS = 'https://';

    const WC_API = 'wc-api';

    const HTTP_X_FORWARDED_PROTO = 'HTTP_X_FORWARDED_PROTO';

    const TPAY_LOGO_URL = 'https://tpay.com/img/banners/logo-tpay-50x25.svg';

    const TPAY_REGULATIONS_URL = 'https://secure.tpay.com/regulamin.pdf';

    const GATEWAY_ID = '';

    protected $language = 'pl_PL';

    protected $pluginUrl;

    protected $trId;

    protected $siteDomain;

    protected $tableName;

    protected $authTableName;

    protected $surchargeSetting = 0;

    protected $notifyLink;

    protected $autoFinishOrder;

    protected $transactionDescription;

    protected $surchargeAmount;

    protected $validateProxyServer;

    protected $shippingMethods = array();

    public function __construct()
    {
        global $wpdb;
        $this->has_fields = true;
        $this->language = get_locale();
        $this->tableName = $wpdb->prefix."woocommerce_tpay";
        $this->authTableName = $wpdb->prefix."woocommerce_tpay_clients";
        $this->icon = apply_filters('woocommerce_transferuj_icon', static::TPAY_LOGO_URL);
        if ($this->surchargeSetting !== 0) {
            add_action('woocommerce_cart_calculate_fees', array($this, 'addFeeTpay'), 99);
            add_action('woocommerce_review_order_after_submit', array($this, 'basketReload'));
        }
    }

    public function addFeeTpay()
    {
        if (WC()->session->get('chosen_payment_method') === static::GATEWAY_ID) {
            $cart = WC()->cart;
            switch ($this->surchargeSetting) {
                case 1:
                    $cart->add_fee(
                        __('Opłata za płatność online', static::WOOCOMMERCE),
                        $this->surchargeAmount,
                        true,
                        'standard'
                    );
                    break;
                case 2:
                    if (method_exists($cart, 'get_cart_contents_total') && method_exists($cart, 'get_shipping_total')) {
                        $amount = $cart->get_cart_contents_total() + $cart->get_shipping_total();
                    } else {
                        $amount = $cart->cart_contents_total + $cart->shipping_total;
                    }
                    $fee = $amount * $this->surchargeAmount / 100;
                    $cart->add_fee(
                        __('Opłata za płatność online', static::WOOCOMMERCE),
                        $fee,
                        true,
                        'standard'
                    );
                    break;
                default:
                    break;
            }
        }
    }

    protected function setEnvironment()
    {
        if ((isset($_SERVER[static::HTTP_X_FORWARDED_PROTO]) && $_SERVER[static::HTTP_X_FORWARDED_PROTO] === 'https')
            || (is_ssl())
        ) {
            $this->pluginUrl = str_replace(static::HTTP, static::HTTPS, plugins_url('', __FILE__));
            $this->siteDomain = preg_replace('/\?.*/', '', str_replace(static::HTTP, static::HTTPS, home_url('/')));
        } else {
            $this->pluginUrl = plugins_url('', __FILE__);
            $this->siteDomain = preg_replace('/\?.*/', '', str_replace(static::HTTPS, static::HTTP, home_url('/')));
        }
    }

    protected function getShippingMethods()
    {
        $canUse = true;
        $outdatedSettingsList = array(
            'legacy_flat_rate',
            'woocommerce_local_pickup_settings',
            'woocommerce_flat_rate_settings',
            'woocommerce_free_shipping_settings',
            'woocommerce_international_delivery_settings',
            'woocommerce_local_delivery_settings',
        );
        foreach ($outdatedSettingsList as $setting) {
            $settings = get_option($setting);
            if (isset($settings['enabled']) && $settings['enabled'] !== 'no') {
                $canUse = false;
            }
        }
        $options = array();
        if (class_exists('WC_Shipping', false) && $canUse) {
            $Shipping = WC()->shipping();
            if (method_exists($Shipping, 'get_shipping_methods')) {
                try {
                    $shippingMethods = $Shipping->get_shipping_methods();
                    foreach ($shippingMethods as $method) {
                        if (isset($method->id) && isset($method->method_title)) {
                            $options[$method->id] = $method->method_title;
                        }
                    }
                } catch (Exception $e) {
                    Util::log('Exception in getShippingMethods ', print_r($e, true));
                }

                return $options;
            }
        }

        return $options;
    }

    protected function isAvailableForShippingMethod($shippingMethods)
    {
        if (empty($shippingMethods) || !isset(WC()->session)) {
            return true;
        }
        $chosenShippingMethod = WC()->session->get('chosen_shipping_methods');
        $valid = false;
        if (is_array($chosenShippingMethod)) {
            foreach ($chosenShippingMethod as $methodKey => $methodName) {
                $chosenShippingMethod = $methodName;
            }
        }
        foreach ($shippingMethods as $shippingMethod) {
            if (is_string($chosenShippingMethod) && strpos($chosenShippingMethod, $shippingMethod) !== false) {
                $valid = true;
            }
        }

        return $valid;
    }

    protected function getBaseTransactionConfigByOrderId($orderId, $secret = '')
    {
        if (!is_numeric($orderId)) {
            $orderId = $this->crypt($orderId, $secret, false);
        }
        if ($orderId === false) {
            throw new TException(sprintf('Invalid order ID %s', $orderId));
        }
        $order = wc_get_order($orderId);
        $orderAddress = $order->get_address();
        if (strcmp($this->language, 'pl_PL') === 0) {
            $language = 'pl';
        } elseif (strcmp($this->language, 'de_DE') === 0) {
            $language = 'de';
        } else {
            $language = 'en';
        }
        $description = array(
            'pl' => __('Zamówienie nr', static::WOOCOMMERCE),
            'en' => __('Order no', static::WOOCOMMERCE),
            'de' => __('Bestellnr', static::WOOCOMMERCE),
        );
        $baseTransactionConfig = array(
            'amount' => $order->get_total(),
            'description' => sprintf("%s %s %s", preg_replace('/[^A-Za-z0-9\-\ ]/', '', $this->transactionDescription),
                $description[$language], $order->get_order_number()),
            'language' => $language,
            'crc' => $orderId,
            'email' => $orderAddress['email'],
            'name' => $orderAddress['first_name'].' '.$orderAddress['last_name'],
            'address' => $orderAddress['address_1'].' '.$orderAddress['address_2'],
            'city' => $orderAddress['city'],
            'country' => $orderAddress['country'],
            'zip' => $orderAddress['postcode'],
            'phone' => str_replace(' ', '', $orderAddress['phone']),
            'return_url' => $this->get_return_url($order).'&utm_nooverride=1',
            'return_error_url' => $order->get_checkout_payment_url(),
            'result_url' => $this->notifyLink,
            'module' => 'WooCommerce '.$this->wpbo_get_woo_version_number(),
        );

        return $baseTransactionConfig;
    }

    protected function crypt($string, $secret, $encrypt = true)
    {
        $iv = substr(md5($secret), 16);
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret);

        return $encrypt ? base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv)) :
            openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    protected function wpbo_get_woo_version_number()
    {
        // If get_plugins() isn't available, require it
        if (!function_exists('get_plugins')) {
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        // Create the plugins folder and file variables
        $plugin_folder = get_plugins('/'.'woocommerce');
        $plugin_file = 'woocommerce.php';
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            return $plugin_folder[$plugin_file]['Version'];
        } else {
            return null;
        }
    }

}
