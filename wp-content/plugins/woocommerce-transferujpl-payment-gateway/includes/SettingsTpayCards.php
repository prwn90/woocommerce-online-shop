<?php

class SettingsTpayCards
{
    const TITLE = 'title';

    const TYPE = 'type';

    const DEFAULT_SETTING = 'default';

    const DESCRIPTION = 'description';

    const WOOCOMMERCE = 'woocommerce';

    const GATEWAY_NAME = 'WC_Gateway_Tpay_Cards';

    const SELECT = 'select';

    const OPTIONS = 'options';

    const DESC_TIP = 'desc_tip';

    const HTTP = 'http:';

    const HTTPS = 'https:';

    public function getSettings($shippingSettings = array())
    {
        if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (is_ssl())
        ) {
            $domain = str_replace(static::HTTP, static::HTTPS, home_url('/'));
            $notify_link = add_query_arg('wc-api', static::GATEWAY_NAME, $domain);
        } else {
            $domain = str_replace(static::HTTPS, static::HTTP, home_url('/'));
            $notify_link = add_query_arg('wc-api', static::GATEWAY_NAME, $domain);
        }

        $options = array(
            'enabled' => array(
                static::TITLE => __('Włącz/Wyłącz', static::WOOCOMMERCE),
                static::TYPE => 'checkbox',
                'label' => __('Włącz metodę płatności kartami przez Tpay.', static::WOOCOMMERCE),
                static::DEFAULT_SETTING => 'no',
                static::DESCRIPTION => sprintf(__(' <a href="%s" TARGET="_blank">Zarejestruj konto w systemie
  tpay.com</a>.', static::WOOCOMMERCE), 'https://secure.tpay.com/panel/rejestracja.html'),
            ),
            'doc_link' => array(
                static::TITLE => __('Instrukcja', static::WOOCOMMERCE),
                static::TYPE => static::TITLE,
                static::DESCRIPTION => sprintf(__(' <a href="%s" TARGET="_blank">Instrukcja konfiguracji
 </a>', static::WOOCOMMERCE), 'https://support.tpay.com/pl/developer/addons/woocommerce/woocommerce-instrukcja'),
            ),
            static::TITLE => array(
                static::TITLE => __('Nazwa metody płatności', static::WOOCOMMERCE),
                static::TYPE => 'text',
                static::DEFAULT_SETTING => __('Tpay credit cards', static::WOOCOMMERCE),
                static::DESC_TIP => true,
            ),
            'proxy_server'         => array(
                static::TITLE           => __('Mój serwer korzysta z komunikacji przez proxy', static::WOOCOMMERCE),
                static::TYPE            => static::SELECT,
                static::DEFAULT_SETTING => 0,
                static::OPTIONS         => array(
                    0 => __('NIE', static::WOOCOMMERCE),
                    1 => __('TAK', static::WOOCOMMERCE),
                ),
            ),
            'shipping_methods' => array(
                'title'             => __('Włącz dla wysyłki - opcja niedostępna w niektórych instalacjach Woocommerce', static::WOOCOMMERCE),
                'type'              => 'multiselect',
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 400px;',
                'default'           => '',
                'description'       => __('Wybierz metody wysyłki dla których chcesz włączyć płatności. Jeśli dla wszystkich, pozostaw to pole puste.', static::WOOCOMMERCE),
                'options'           => $shippingSettings,
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'data-placeholder' => __('Wybierz metody wysyłki', static::WOOCOMMERCE),
                ),
            ),
            'resp_link' => array(
                static::TITLE => __('Link powiadomień do panelu odbiorcy płatności',
                    static::WOOCOMMERCE),
                static::TYPE => static::TITLE,
                static::DESCRIPTION => add_query_arg(array('type' => 'sale'), $notify_link) .
                    '<br/>Zwróć uwagę czy Twoja strona korzysta z protokołu https. Jeśli link został błędnie rozpoznany jako http, zamień protokół http na https przed wprowadzeniem ustawień w panelu odbiorcy płatności',
            ),
            'debugMode' => array(
                static::TITLE => __('Tryb debugowania', static::WOOCOMMERCE),
                static::TYPE => 'checkbox',
                static::DEFAULT_SETTING => 'no',
                static::DESCRIPTION => __('Wyłącz w trybie produkcyjnym.'
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
            'midNumber' => array(
                static::TITLE => __("Numer MID'u", static::WOOCOMMERCE),
                static::TYPE => static::SELECT,
                static::DEFAULT_SETTING => '0',
                static::OPTIONS => array(),
            ),
        );
        for ($i = 1; $i < 11; $i++) {

            $array = array(
                'midOn' . $i => array(
                    static::TITLE => __('Włącz MID', static::WOOCOMMERCE),
                    static::TYPE => 'checkbox',
                    'label' => __("Używaj tego MID'u", static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => 'no',
                ),
                'midType' . $i => array(
                    static::TITLE => __('Konto wielowalutowe', static::WOOCOMMERCE),
                    static::TYPE => static::SELECT,
                    static::DEFAULT_SETTING => '0',
                    static::OPTIONS => array(
                        '0' => __('NIE - tylko PLN', static::WOOCOMMERCE),
                        '1' => __('TAK - wszystkie obsługiwane waluty', static::WOOCOMMERCE),
                    ),
                ),
                'midDomain' . $i => array(
                    static::TITLE => __("Domena przypisana do MID'u", static::WOOCOMMERCE),
                    static::TYPE => 'text',
                    static::DEFAULT_SETTING => $domain,
                ),
                'midCurrency' . $i => array(
                    static::TITLE => __("Waluty, dla których ma być używany MID oddzielone przecinkiem
                     np. EUR,USD (puste jeśli wszystkie)"
                        , static::WOOCOMMERCE),
                    static::TYPE => 'text',
                    static::DEFAULT_SETTING => '',
                ),

                static::DESCRIPTION . $i => array(
                    static::TITLE => __('Opis', static::WOOCOMMERCE),
                    static::TYPE => 'textarea',
                    static::DESCRIPTION => __('Ustawia opis bramki, który widzi użytkownik
                     przy tworzeniu zamówienia.'
                        , static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __('System płatności tpay.com to bezpieczny i szybki sposób płatności, który został wybrany przez Odbiorcę płatności w celu przyjęcia od Ciebie zapłaty.',
                        static::WOOCOMMERCE
                    )
                ),
                'opis' . $i => array(
                    static::TITLE => __('Tytuł transakcji', static::WOOCOMMERCE),
                    static::TYPE => 'text',
                    static::DESCRIPTION => __('Ustawia opis transakcji, do którego zostanie autoamtycznie
                 dodane "Zamówienie nr (ID)".'
                        , static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __(''
                        , static::WOOCOMMERCE)
                ),
                'cardApiKey' . $i => array(
                    static::TITLE => __('Klucz API', static::WOOCOMMERCE),
                    static::TYPE => 'text',
                    static::DESCRIPTION => __('Klucz API wygenerowany w panelu odbiorcy płatności tpay.com.'
                        , static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                    static::DESC_TIP => true,
                ),
                'cardApiPassword' . $i => array(
                    static::TITLE => __('Hasło API', static::WOOCOMMERCE),
                    static::TYPE => 'text',
                    static::DESCRIPTION => __('Hasło do klucza API', static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                    static::DESC_TIP => true,
                ),
                'verificationCode' . $i => array(
                    static::TITLE => __('Kod weryfikacyjny', static::WOOCOMMERCE),
                    static::TYPE => 'text',
                    static::DESCRIPTION => __('Kod weryfikacyjny', static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                    static::DESC_TIP => true,
                ),
                'hashAlg' . $i => array(
                    static::TITLE => __('Typ hash', static::WOOCOMMERCE),
                    static::TYPE => static::SELECT,
                    static::DEFAULT_SETTING => 'sha1',
                    static::OPTIONS => array(
                        'sha1' => __('sha1', static::WOOCOMMERCE),
                        'sha256' => __('sha256', static::WOOCOMMERCE),
                        'sha512' => __('sha512', static::WOOCOMMERCE),
                        'ripemd160' => __('ripemd160', static::WOOCOMMERCE),
                        'ripemd320' => __('ripemd320', static::WOOCOMMERCE),
                        'md5' => __('md5', static::WOOCOMMERCE),
                    ),
                ),
                'keyRSA' . $i => array(
                    static::TITLE => __('Klucz RSA', static::WOOCOMMERCE),
                    static::TYPE => 'textarea',
                    static::DESCRIPTION => __('Klucz publiczny RSA', static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                    static::DESC_TIP => true,
                ),
                'doplata' . $i => array(
                    static::TITLE => __('Dopłata doliczana za korzystanie z metody płatności'
                        , static::WOOCOMMERCE),
                    static::TYPE => static::SELECT,
                    static::DEFAULT_SETTING => '0',
                    static::OPTIONS => array(
                        '0' => __('NIE', static::WOOCOMMERCE),
                        '1' => __('PLN', static::WOOCOMMERCE),
                        '2' => __('%', static::WOOCOMMERCE),
                    ),
                ),
                'kwota_doplaty' . $i => array(
                    static::TITLE => __('Kwota dopłaty', static::WOOCOMMERCE),
                    static::TYPE => "text",
                    static::DESCRIPTION => __('Kwota jaka zostanie doliczona do zamówienia.
                 Jako separator liczb należy wykorzystać kropkę', static::WOOCOMMERCE),
                    static::DEFAULT_SETTING => __('0', static::WOOCOMMERCE),
                    static::DESC_TIP => true,
                ),
            );
            $options['midNumber'][static::OPTIONS][$i] = __((string)$i, static::WOOCOMMERCE);
            $options = array_merge($options, $array);
        }

        return $options;
    }
}
