<?php

class Przelewy24OneClickHelper
{
    public static function getCardPaymentIds()
    {
        return array(140, 142, 145, 218);
    }

    public static function updateCustomerRememberCard($customerId, $remember = 0)
    {
        return Db::getInstance()->Execute("REPLACE INTO " . _DB_PREFIX_ . "przelewy24_customersettings (`customer_id`, `card_remember`) " .
            "VALUES (" . (int)$customerId . ", '" . (int)$remember . "')");
    }

    public static function rememberCardForCustomer($customerId)
    {
        $rememberRow = Db::getInstance()->getRow('SELECT `card_remember` FROM ' . _DB_PREFIX_ . 'przelewy24_customersettings WHERE customer_id=' . (int)$customerId);
        if (!isset($rememberRow['card_remember'])) {
            // if empty, then set default value: 1
            return (bool)self::updateCustomerRememberCard($customerId, 1);
        }
        return (bool)($rememberRow['card_remember'] > 0);
    }

    public static function saveCard($customerId, $refId, $expires, $mask, $cardType)
    {
        Db::getInstance()->Execute("REPLACE INTO " . _DB_PREFIX_ . "przelewy24_recuring (`website_id`, `customer_id`, `reference_id`, `expires`, `mask`, `card_type`) " .
            "VALUES (1, " . (int)$customerId . ", '" . pSQL($refId) . "', '" . pSQL($expires) . "', '" . pSQL($mask) . "', '" . pSQL($cardType) . "')");
    }

    public static function escape($string)
    {
        $string = trim($string);
        return htmlspecialchars($string);
    }

    public static function getCustomerCards($customerId)
    {
        $results = Db::getInstance()->ExecuteS(
            ' SELECT * ' . ' FROM ' . _DB_PREFIX_ . 'przelewy24_recuring WHERE customer_id=' . (int)$customerId
        );
        return $results;
    }

    public static function deleteCustomerCard($customerId, $cardId)
    {
        Db::getInstance()->Execute(
            ' DELETE FROM ' . _DB_PREFIX_ . 'przelewy24_recuring ' .
            ' WHERE customer_id=' . (int)$customerId . ' AND id=' . (int)$cardId
        );
    }

    public static function isOneClickEnable($sufix = "")
    {
        $soap = new Przelewy24Soap($sufix);

        if ($soap->checkCardRecurrency() && (int)Configuration::get('P24_ONECLICK_ENABLE') === 1) {
            return true;
        }
        return false;
    }

    public static function getOneclickOrderId($orderId){
        $result = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'przelewy24_oneclick WHERE p24_order_id = ' . (int)$orderId);
        return $result;
    }
}
