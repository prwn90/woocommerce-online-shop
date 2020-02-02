<?php


class Przelewy24Generator
{
    /**
     * @var WC_Gateway_Przelewy24
     */
    private $gateway;

    /**
     * Przelewy24Generator constructor.
     *
     * @param WC_Gateway_Przelewy24 $gateway The class that provide configuration.
     */
    public function __construct(WC_Gateway_Przelewy24 $gateway) {
        $this->gateway = $gateway;
    }

    /**
     * Generate przelewy24 button link
     **/
    public function generate_fields_array($order_id, $transaction_id = null)
    {
        global $locale;

        $localization = !empty($locale) ? explode("_", $locale) : 'pl';
        $order = new WC_Order($order_id);
        if (!$order) return false;

        if (is_null($transaction_id)) {
            $transaction_id = $order_id . "_" . uniqid(md5($order_id . '_' . date("ymds")), true);
        }

        // modifies order number if Sequential Order Numbers Pro plugin is installed
        if (class_exists('WC_Seq_Order_Number_Pro')) {
            $seq = new WC_Seq_Order_Number_Pro();
            $description_order_id = $seq->get_order_number($order_id, $order);
        } else if (class_exists('WC_Seq_Order_Number')) {
            $seq = new WC_Seq_Order_Number();
            $description_order_id = $seq->get_order_number($order_id, $order);
        } else {
            $description_order_id = $order_id;
        }

        $config = $this->gateway->load_settings_from_db_formatted( $order->get_currency() );
        $config->access_mode_to_strict();

        //p24_opis depend of test mode
        $desc = ($config->is_p24_operation_mode( 'sandbox' ) ? __('Transakcja testowa', 'przelewy24') . ', ' : '') .
            __('Zamówienie nr', 'przelewy24') . ': ' . $description_order_id . ', ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ', ' . date('Ymdhi');

        //return address URL
        $payment_page = add_query_arg(array('wc-api' => 'WC_Gateway_Przelewy24', 'order_id' => $order_id), home_url('/'));
        $status_page = add_query_arg(array('wc-api' => 'WC_Gateway_Przelewy24'), home_url('/'));

        /*Form send to przelewy24*/

        $amount = $order->get_total() * 100;
        $amount = number_format($amount, 0, "", "");

        $currency = strtoupper($order->get_currency());
        $przelewy24_arg = array(
            'p24_session_id' => addslashes($transaction_id),
            'p24_merchant_id' => (int) $config->get_merchant_id(),
            'p24_pos_id' => (int) $config->get_shop_id(),
            'p24_email' => filter_var($order->get_billing_email(), FILTER_SANITIZE_EMAIL),
            'p24_amount' => (int)$amount,
            'p24_currency' => filter_var($currency, FILTER_SANITIZE_STRING),
            'p24_description' => addslashes($desc),
            'p24_language' => filter_var($localization[0], FILTER_SANITIZE_STRING),
            'p24_client' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'p24_address' => $order->get_billing_address_1(),
            'p24_city' => $order->get_billing_city(),
            'p24_zip' => $order->get_billing_postcode(),
            'p24_country' => $order->get_billing_country(),
            'p24_encoding' => 'UTF-8',
            'p24_url_status' => filter_var($status_page, FILTER_SANITIZE_URL),
            'p24_url_return' => filter_var($payment_page, FILTER_SANITIZE_URL),
            'p24_api_version' => P24_VERSION,
            'p24_ecommerce' => 'woocommerce_' . WOOCOMMERCE_VERSION,
            'p24_ecommerce2' => '1.0.8',
            'p24_method' => (int)get_post_meta($order->get_order_number(), 'p24_method', true),
            'p24_shipping' => number_format($order->get_shipping_total() * 100, 0, '', ''),
            'p24_wait_for_result' => (int) $config->get_p24_wait_for_result(),
        );

        $productsInfo = array();
        foreach ($order->get_items() as $product) {
            $productsInfo[] = array(
                'name' => filter_var($product['name'], FILTER_SANITIZE_STRING),
                'description' => strip_tags(get_post($product['product_id'])->post_content),
                'quantity' => (int)$product['qty'],
                'price' => ($product['line_total'] / $product['qty']) * 100,
                'number' => (int)$product['product_id'],
            );
        }

        $shipping = number_format($order->get_shipping_total() * 100, 0, '', '');
        $translations = array(
            'virtual_product_name' => __('Dodatkowe kwoty [VAT, rabaty]', 'przelewy24'),
            'cart_as_product' => __('Twoje zamówienie', 'przelewy24'),
        );
        $p24Product = new Przelewy24Product($translations);
        $p24ProductItems = $p24Product->prepareCartItems($amount, $productsInfo, $shipping);
        $przelewy24_arg = array_merge($przelewy24_arg, $p24ProductItems);

        $P24 = new Przelewy24Class( $config );
        $przelewy24_arg['p24_sign'] = $P24->trnDirectSign($przelewy24_arg);
        $P24->checkMandatoryFieldsForAction($przelewy24_arg, 'trnDirect');

        return $przelewy24_arg;
    }

    /**
     * @param $order_id
     * @param bool $autoSubmit
     * @return string
     */
    public function generate_przelewy24_form($order_id, $autoSubmit = true, $makeRecuringForm = false)
    {
        $order = new WC_Order((int)$order_id);
        $przelewy24_arg = $this->generate_fields_array((int)$order_id);
        $przelewy_form = '';
        foreach ($przelewy24_arg as $key => $value)
            $przelewy_form .= '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';

        $config = $this->gateway->load_settings_from_db_formatted( $order->get_currency() );

        $accept_in_shop = '';
        if ( $config->get_p24_acceptinshop() === 'yes') {
            $accept_in_shop = '<p><label><input type="checkbox" required="required" />'. __('Tak, przeczytałem i akceptuję regulamin Przelewy24.', 'przelewy24') .'</label></p>';
        }

        $P24 = new Przelewy24Class( $config );
        $return = '<div id="payment" style="background: none"> ' .
            '<form action="' . $P24->trnDirectUrl() . '" method="post" id="przelewy_payment_form"'.
            ($autoSubmit ? '' : ' onSubmit="return p24_processPayment()" ') .
            '>' .
            $przelewy_form .

            $accept_in_shop .

            '<input type="submit" class="button alt" id="place_order" value="' . __('Potwierdzam zamówienie', 'przelewy24') . '" /> ' .
            '<p style="text-align:right; float:right; width:100%; font-size:12px;">' . __('Złożenie zamówienia wiąże się z obowiązkiem zapłaty', 'przelewy24') . '</p>' .
            '<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Anuluj zamówienie', 'przelewy24') . '</a>' .
            ($autoSubmit ?
                '<script type="text/javascript">jQuery(function(){jQuery("body").block({message: "' .
                __('Dziękujemy za złożenie zamówienia. Za chwilę nastąpi przekierowanie na stronę przelewy24.pl', 'przelewy24') .
                '",overlayCSS: {background: "#fff",opacity: 0.6},css: {padding:20,textAlign:"center",color:"#555",border:"2px solid #AF2325",backgroundColor:"#fff",cursor:"wait",lineHeight:"32px"}});' .
                'jQuery("#przelewy_payment_form input[type=submit]").click();});' .
                '</script>' : '') .
            '</form>' .
            '</div>' .
            '';
        if (!!$makeRecuringForm) {
            $return .= <<<FORMRECURING
                        <form method="post" id="przelewy24FormRecuring" name="przelewy24FormRecuring" accept-charset="utf-8">
                            <input type="hidden" name="p24_session_id" value="{$przelewy24_arg[p24_session_id]}" />
                            <input type="hidden" name="p24_cc" />
                        </form>
FORMRECURING;
        }
        return $return;
    }


    public function create_trnRequest($order) {

        $przelewy24_arg = $this->generate_fields_array($order->get_id());

        $config = $this->gateway->load_settings_from_db_formatted( $order->get_currency() );
        $P24 = new Przelewy24Class( $config );

        foreach ($przelewy24_arg as $key => $value){
            $P24->addValue($key, $value);
        }

        $token = $P24->trnRegister();

        $paymentLink = $P24->getHost() . 'trnRequest/' . $token['token'];

        return $paymentLink;

    }

}
