<?php

class SettingsTpay
{
    const VISIBILITY_VISIBLE = 'visibility: visible';

    const VISIBILITY_HIDDEN = 'visibility: hidden';

    const TITLE = 'title';

    const TYPE = 'type';

    const DEFAULT_SETTING = 'default';

    const DESCRIPTION = 'description';

    const WOOCOMMERCE = 'woocommerce';

    const SELECT = 'select';

    const OPTIONS = 'options';

    const DESC_TIP = 'desc_tip';

    public function getSettings($charge, $list, $shippingSettings = array())
    {
        $ukryjD = static::VISIBILITY_VISIBLE;
        $ukryjK = static::VISIBILITY_VISIBLE;

        if ($charge == '0') {
            $ukryjD = static::VISIBILITY_HIDDEN;
        }
        if ($list == '1') {
            $ukryjK = static::VISIBILITY_HIDDEN;
        }

        return array(
            'enabled' => array(
                static::TITLE => __('Włącz/Wyłącz', static::WOOCOMMERCE),
                static::TYPE => 'checkbox',
                'label' => __('Włącz metodę płatności przez tpay.com.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => 'yes',
                static::DESCRIPTION => sprintf(__(' <a href="%s" TARGET="_blank">Zarejestruj konto w systemie
  tpay.com</a>.', static::WOOCOMMERCE), 'https://secure.tpay.com/panel/rejestracja.html'),
            ),
            'documentation' => array(
                static::TITLE => __('Instrukcja konfiguracji', static::WOOCOMMERCE),
                static::TYPE => static::TITLE,
                static::DESCRIPTION => sprintf(__(' <a href="%s" TARGET="_blank">
 Link do instrukcji konfiguracji modułu</a>.', static::WOOCOMMERCE),
                    'https://support.tpay.com/pl/developer/addons/woocommerce/woocommerce-instrukcja'),
            ),
            static::TITLE => array(
                static::TITLE => __('Nazwa', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DEFAULT_SETTING => __('Tpay', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'opis' => array(
                static::TITLE => __('Tytuł transakcji', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DESCRIPTION => __('Pozwala zmodyfikować opis transakcji, do którego zostanie autoamtycznie
                 dodane "Zamówienie nr (order_id)".'
                    , static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __(''
                    , static::WOOCOMMERCE),
            ),
            'seller_id' => array(
                static::TITLE => __('ID sprzedawcy', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DESCRIPTION => __('Twoje ID sprzedawcy w systemie tpay.com.
                 Liczba co najmniej czterocyfrowa (może być pięciocyfowa), np. 12345', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'security_code' => array(
                static::TITLE => __('Kod bezpieczeństwa', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DESCRIPTION => __('Kod bezpieczeństwa Twojego konta na tpay.com.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'blik_on' => array(
                static::TITLE => __('Włącz płatności blikiem na stronie sklepu', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => '0',
                static::OPTIONS => array(
                    '0' => __('NIE', static::WOOCOMMERCE),
                    '1' => __('TAK', static::WOOCOMMERCE),
                ),
            ),
            'api_key' => array(
                static::TITLE => __('Klucz API', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DESCRIPTION => __('Klucz API wygenerowany w panelu odbiorcy płatności tpay.com.'
                    , static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'api_pass' => array(
                static::TITLE => __('Hasło API', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DESCRIPTION => __('Hasło do klucza API', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'doplata' => array(
                static::TITLE => __('Dopłata doliczana za korzystanie z tej metody płatności', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => '0',
                static::OPTIONS => array(
                    '0' => __('NIE', static::WOOCOMMERCE),
                    '1' => __('PLN', static::WOOCOMMERCE),
                    '2' => __('%', static::WOOCOMMERCE),
                ),
            ),
            'kwota_doplaty' => array(
                static::TITLE => __('Kwota dopłaty', static::WOOCOMMERCE),
                static::TYPE => "text",
                'css' => $ukryjD,
                static::DESCRIPTION => __('Kwota jaka zostanie doliczona do zamówienia.
                 Jako separator liczb należy wykorzystać kropkę', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'bank_list' => array(
                static::TITLE => __('Włącz wybór banku na stronie sklepu', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => '0',
                static::OPTIONS => array(
                    '0' => __('TAK', static::WOOCOMMERCE),
                    '1' => __('NIE', static::WOOCOMMERCE),
                ),
            ),
            'bank_view' => array(
                static::TITLE => __('Widok listy kanałów', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => '0',
                'css' => $ukryjK,
                static::OPTIONS => array(
                    '0' => __('Kafelki', static::WOOCOMMERCE),
                    '1' => __('Lista', static::WOOCOMMERCE),
                ),
            ),
            'shipping_methods' => array(
                'title' => __('Włącz dla wysyłki - opcja niedostępna w niektórych instalacjach Woocommerce',
                    'woocommerce'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'css' => 'width: 400px;',
                'default' => '',
                'description' => __('Wybierz metody wysyłki dla których chcesz włączyć płatności. Jeśli dla wszystkich, pozostaw to pole puste.',
                    'woocommerce'),
                'options' => $shippingSettings,
                'desc_tip' => true,
                'custom_attributes' => array(
                    'data-placeholder' => __('Wybierz metody wysyłki', 'woocommerce'),
                ),
            ),
            'auto_finish_order' => array(
                static::TITLE => __('Automatycznie oznaczaj zamówienie jako zrealizowane', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => 0,
                static::OPTIONS => array(
                    0 => __('NIE', static::WOOCOMMERCE),
                    1 => __('TAK', static::WOOCOMMERCE),
                ),
            ),
            'proxy_server' => array(
                static::TITLE => __('Mój serwer korzysta z komunikacji przez proxy', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => 0,
                static::OPTIONS => array(
                    0 => __('NIE', static::WOOCOMMERCE),
                    1 => __('TAK', static::WOOCOMMERCE),
                ),
            ),
            'enable_IP_validation' => array(
                static::TITLE => __('Weryfikuj adres serwera powiadomień (zalecane)', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => 1,
                static::OPTIONS => array(
                    1 => __('TAK', static::WOOCOMMERCE),
                    0 => __('NIE', static::WOOCOMMERCE),
                ),
            ),
            'online_methods_only' => array(
                static::TITLE => __('Pokaż tylko metody płatności księgujące online', static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => 0,
                static::OPTIONS => array(
                    0 => __('NIE', static::WOOCOMMERCE),
                    1 => __('TAK', static::WOOCOMMERCE),
                ),
            ),
        );
    }
}
