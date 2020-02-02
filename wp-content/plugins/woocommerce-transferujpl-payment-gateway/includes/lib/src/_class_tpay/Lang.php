<?php

/*
 * Created by tpay.com
 */

namespace tpay;

/**
 * Class Lang
 *
 * @package tpay
 */
class Lang
{
    const REGULATIONS = 'regulations';

    /**
     * Current language
     *
     * @var string
     */
    private static $lang = 'en';
    /**
     * Translation data
     *
     * @var array
     */
    private static $data = array(
        'en' => array(

            // GLOBALS
            'fee_info'      => 'Fee for using this payment method: ',
            'pay'           => 'Pay with Tpay',
            'merchant_info' => 'Merchant info',
            'amount'        => 'Amount',
            'order'         => 'Order',
            // BLIK
            'codeInputText' => 'BLIK code',
            'blik_info'              => 'Type in 6 digit code and confirm the order to commit BLIK payment.',
            'blik_info2'             => 'If you want to pay with standard method, leave this field blank.',
            'blik_accept'            => 'By using this method you confirm acceptance',

            // BANK SELECTION
            'cards_and_transfers'    => 'Credit cards and bank transfers',
            'other_methods'          => 'Others',
            'accept'                 => 'I accept the',
            'regulations_url'        => self::REGULATIONS,
            self::REGULATIONS        => 'of Tpay service',
            'acceptance_is_required' => 'Acceptance of regulations is required before payment',

            // CARD
            'saved_card'      => 'Saved card ',
            'new_card'        => 'New card',
            'card_number'     => 'Card number',
            'expiration_date' => 'Expiration date',
            'signature'       => 'For MasterCard, Visa or Discover, it\'s the last three digits
             in the signature area on the back of your card.',
            'name_on_card'    => 'Name on card',
            'name_surname'    => 'Name and surname',
            'save_card'       => 'Save my card',
            'save_card_info'  => 'Let faster payments in future. Card data is stored on external, save server.',
            'saved_card_label' => 'Pay by saved card ',
            'processing'      => 'Processing data, please wait...',
            'card_payment'    => 'Payment',
            'debit'           => 'Please debit my account',
            'not_supported_card' => 'Sorry, your credit card is currently not supported. Please try another payment card or payment method.',
            'not_valid_card' => 'Sorry, your credit card number is invalid. Please enter the valid card number',
            // DAC

            'transfer_details'   => 'Bank transfer details',
            'payment_amount'     => 'The amount of the payment',
            'disposable_account' => 'Disposable account number for the payment',

            // SZKWAL

            'account_number' => 'Account number',
            'payment_title'  => 'Payment title',
            'payment_method' => 'Payment method',
            'szkwal_info'    => 'Your title transfer is dedicated to you and very important for the identification of
             payment. You can create a transfer as defined in its bank to
              quickly and easily fund your account in the future',
            'new_card_label' => 'Pay by a new card',

            // WHITE LABEL

            'go_to_bank' => 'Go to bank',
        ),
        'pl' => array(

            // GLOBALS
            'fee_info' => 'Za korzystanie z płatności online sprzedawca dolicza: ',

            'pay'           => 'Zapłać z Tpay',
            'merchant_info' => 'Dane sprzedawcy',
            'amount'        => 'Kwota',
            'order'         => 'Zamówienie',

            // BLIK
            'codeInputText' => 'Kod BLIK',
            'blik_info'              => 'Jeśli chcesz zapłacić kodem BLIK, wpisz go i dokończ zamówienie.',
            'blik_info2'             => 'W przeciwnym wypadku pozostaw to pole puste.',
            'blik_accept'            => 'Korzystając z tej metody płatności oświadczasz, że akceptujesz',

            // BANK SELECTION
            'cards_and_transfers'    => 'Karty płatnicze i przelewy',
            'other_methods'          => 'Pozostałe',
            'accept'                 => 'Akceptuję',
            'regulations_url'        => 'regulamin',
            self::REGULATIONS        => 'serwisu Tpay',
            'acceptance_is_required' => 'Akceptacja regulaminu jest obowiązkowa, przed rozpoczęciem płatności',

            // CARD
            'saved_card'         => 'Zapisana karta ',
            'new_card'           => 'Nowa karta',
            'card_number'        => 'Numer karty',
            'expiration_date'    => 'Termin ważności',
            'signature'          => 'Dla MasterCard, Visa lub Discover, są to trzy ostatnie
             cyfry umieszczone przy podpisie karty.',
            'name_on_card'       => 'Właściciel karty',
            'name_surname'       => 'Imię i nazwisko',
            'save_card'          => 'Zapisz moją kartę',
            'save_card_info'     => 'Zezwolenie na szybszą płatność w przyszłości.
             Dane karty zostaną zapisane na serwerze Tpay',
            'saved_card_label' => 'Zapłać zapisaną kartą ',
            'processing'         => 'Przetwarzanie danych, proszę czekać...',
            'card_payment'       => 'Zapłać',
            'debit'              => 'Proszę obciążyć moje konto',
            'not_supported_card' => 'Przepraszamy, ten typ karty nie jest obecnie obsługiwany. Prosimy skorzystać z innej karty lub wybrać inną metodę płatności.',
            'not_valid_card' => 'Przepraszamy, wprowadzony numer karty jest niepoprawny. Prosimy wprowadzić prawidłowy numer.',
            'new_card_label' => 'Zapłać nową kartą',
            // DAC

            'transfer_details'   => 'Szczegóły przelewu',
            'payment_amount'     => 'Kwota przelewu',
            'disposable_account' => 'Jednorazowy numer konta dla tej transakcji',

            // SZKWAL

            'account_number' => 'Numer konta',
            'payment_title'  => 'Tytuł przelewu',
            'payment_method' => 'Sposób płatności',
            'szkwal_info'    => 'Twój tytuł przelewu jest dedykowany dla Ciebie i bardzo ważny dla identyfikacji wpłaty.
             Możesz stworzyć przelew zdefiniowany w swoim banku, aby wygodnie i szybko zasilić swoje
              konto w przyszłości.',

            // WHITE LABEL

            'go_to_bank' => 'Przejdź do banku',
        )
    );

    /**
     * Change current language
     *
     * @param string $lang language code
     *
     * @throws TException
     */
    public static function setLang($lang)
    {
        if (isset(static::$data[$lang])) {
            static::$lang = $lang;
        } else {
            throw new TException('No translation for this language');
        }
    }

    /**
     * Get and print translated string
     * @param $key
     */
    public static function l($key)
    {
        echo static::get($key);
    }

    /**
     * Get translated string
     *
     * @param string $key
     *
     * @throws TException
     * @return string
     */
    public static function get($key)
    {
        if (isset(static::$data[static::$lang][$key])) {
            return static::$data[static::$lang][$key];
        } else {
            throw new TException('No translation for this key');
        }
    }
}
