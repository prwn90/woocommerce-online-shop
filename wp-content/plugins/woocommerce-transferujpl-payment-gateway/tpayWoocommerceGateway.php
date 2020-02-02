<?php

/**
 * Tpay Woocommerce payment module
 *
 * Plugin Name: Tpay WooCommerce payment module
 * Plugin URI: https://wordpress.org/plugins/woocommerce-transferujpl-payment-gateway
 * Description: Brama płatności Tpay dla WooCommerce.
 * Author: Tpay
 * Author URI: https://tpay.com
 * Version: 2.8.4
 *
 * WC requires at least: 2.5
 * WC tested up to: 3.8.1
 */

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_init', 'childPluginHasParentPlugin');

    return;
}

if (version_compare(phpversion(), '5.6', '>=' ) !== true) {
    add_action('admin_init', 'displayPhpVersionNotice', 10, 0);
}

add_action('plugins_loaded', 'initTpayGateway');
register_activation_hook(__FILE__, 'installDatabase');

function installDatabase()
{
    global $wpdb;
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE `".$wpdb->prefix."woocommerce_tpay` (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  wooId mediumint(9) NOT NULL,
  midId mediumint(9) NOT NULL,
  client_language VARCHAR(2) NOT NULL DEFAULT 'pl',
  PRIMARY KEY  (id)
) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE `".$wpdb->prefix."woocommerce_tpay_clients` (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  clientId mediumint(9) NOT NULL,
  cliAuth varchar(40) NOT NULL,
  cardNoShort varchar(20) NOT NULL,
  midId mediumint(9) NOT NULL,
  PRIMARY KEY  (id)
) $charset_collate;";
    dbDelta($sql);
}

function initTpayGateway()
{
    $new_tpay_db_version = '1.2';
    $current_tpay_db_version = get_option("tpay_db_version");
    if ($new_tpay_db_version !== $current_tpay_db_version) {
        installDatabase();
        update_option("tpay_db_version", $new_tpay_db_version);
    }
    require_once 'includes/TpayCards.php';
    require_once 'includes/TpayBasic.php';

    add_filter('woocommerce_payment_gateways', 'woocommerceTpayAddGateways');
    add_action('woocommerce_api_wc_gateway_transferuj', array(new WC_Gateway_Tpay_Basic, 'gateway_communication'));
    add_action('woocommerce_api_wc_gateway_tpay_basic', array(new WC_Gateway_Tpay_Basic, 'gateway_communication'));
    add_action('woocommerce_api_wc_gateway_tpay_cards', array(new WC_Gateway_Tpay_Cards, 'gateway_communication'));
}

function woocommerceTpayAddGateways($methods)
{
    $methods[] = 'WC_Gateway_Tpay_Cards';
    $methods[] = 'WC_Gateway_Tpay_Basic';

    return $methods;
}

function childPluginHasParentPlugin()
{
    if (is_admin() && current_user_can('activate_plugins')) {
        add_action('admin_notices', 'displayChildPluginNotice');
        deactivate_plugins(plugin_basename(__FILE__));
        if (filter_input(INPUT_GET, 'activate')) {
            unset($_GET['activate']);
        }
    }
}

function displayChildPluginNotice()
{
    echo '<div class="error"><p>Moduł płatności Tpay wymaga aktywnej wtyczki Woocommerce, którą można pobrać
                    <a target="_blank" href="https://wordpress.org/plugins/woocommerce/">tutaj</a></p></div>';
}

function displayPhpVersionNotice()
{
    echo '<div class="notice notice-warning"><p>Moduł płatności Tpay będzie niebawem wymagał wesji PHP 5.6 lub wyższej. Sprawdź jak zaktualizować wersję PHP
                    <a target="_blank" href="https://docs.woocommerce.com/document/how-to-update-your-php-version/">tutaj</a></p></div>';
}
