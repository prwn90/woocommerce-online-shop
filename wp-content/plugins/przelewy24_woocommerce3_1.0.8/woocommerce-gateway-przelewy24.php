<?php
/**
 * Plugin Name: WooCommerce Przelewy24 Payment Gateway
 * Plugin URI: http://www.przelewy24.pl/pobierz
 * Description: Przelewy24 Payment gateway for woocommerce.
 * Version: 1.0.8
 * Author: DialCom24 Sp. z o.o.
 * Author URI: http://www.przelewy24.pl/
 */
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

define('PRZELEWY24_URI', plugin_dir_url(__FILE__));
define('PRZELEWY24_PATH', dirname(__FILE__));

add_action('plugins_loaded', 'woocommerce_p24_init', 0);

register_activation_hook(__FILE__, 'woocommerce_gateway_przelewy24_payment_activate');

function woocommerce_gateway_przelewy24_payment_activate()
{
    global $wpdb;
    $wpdb->query('
          CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'woocommerce_p24_data(
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            data_type VARCHAR(32) NOT NULL,
            data_id INT NOT NULL,
            custom_key VARCHAR(32) NOT NULL,
            custom_value TEXT,
            INDEX search_key (data_type, data_id, custom_key),
            INDEX get_key (data_type, data_id)
          ) ENGINE = MYISAM;
        ');

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "woocommerce_p24_custom_data'") ===  $wpdb->prefix . 'woocommerce_p24_custom_data' && !$wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "woocommerce_p24_data")) {
        $wpdb->query('
        INSERT INTO  ' . $wpdb->prefix . 'woocommerce_p24_data
            SELECT * FROM ' . $wpdb->prefix . 'woocommerce_p24_custom_data'
        );
    }
}

/**
 * Helper to display errors.
 *
 * @param string $callback The text do display.
 */
function woocommerce_p24_error($callback) {
    if (is_plugin_active(plugin_basename(__FILE__))) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', $callback);
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function woocommerce_p24_init()
{
	load_plugin_textdomain('przelewy24', false, dirname(plugin_basename(__FILE__)) . '/lang/');

	if (!class_exists('WC_Payment_Gateway')) {
        woocommerce_p24_error('woocommerce_not_installed_error');
        return;
    }

    if (!check_woocommerce_version_compatibility(3, 4)) {
        woocommerce_p24_error('woocommerce_version_error');
        return;
    };

    if (!extension_loaded('soap')) {
        woocommerce_p24_error('php_soap_extension_error');
        return;
    }

    if (!extension_loaded('curl')) {
        woocommerce_p24_error('php_curl_extension_error');
        return;
    }

    require_once 'includes/shared-libraries/autoloader.php';
    require_once 'includes/class_przelewy24.php';
    require_once 'includes/Przelewy24Generator.class.php';
    require_once 'includes/WC_Gateway_Przelewy24.php';
    require_once 'includes/shared-libraries/classes/Przelewy24Product.php';
    require_once(PRZELEWY24_PATH . "/includes/Przelewy24Helpers.class.php");

    require_once 'includes/p24-autoload.php';

    add_filter('woocommerce_payment_gateways', 'woocommerce_p24_add_gateway');
    add_action('woocommerce_order_actions', 'woocommerce_p24_action_add' );
    add_filter('woocommerce_email_actions', 'woocommerce_p24_email_filter');
    add_action('woocommerce_order_action_wc_przelewy24_email_action', 'woocommerce_p24_email_send' );

    $plugin_core = new P24_Core();
    $plugin_core->bind_core_events();
}

/**
 * Get instance of plugin core class.
 *
 * @return P24_Core|null
 */
function get_przelewy24_plugin_instance() {
    return apply_filters( 'przelewy24_plugin_instance', null );
}

/**
 * Get list of available currencies.
 *
 * If multi currency is not activated, the null will be returned.
 *
 * @return array|null
 */
function get_przelewy24_multi_currency_options() {
    return apply_filters( 'przelewy24_multi_currency_options', null );
}

/**
 * add_action.
 */
function woocommerce_p24_add_gateway($methods)
{
    $methods[] = 'WC_Gateway_Przelewy24';
    return $methods;
}

function my_stored_cards()
{
    $t['my_cards'] = __('Moje zapisane karty', 'przelewy24');
    $t['are_you_sure'] = __('Czy jesteś pewien?', 'przelewy24');
    $t['delete_card'] = __('Usuń kartę', 'przelewy24');
    $t['cc_forget'] = __('Nie pamiętaj moich kart', 'przelewy24');
    $t['no_cards'] = __('Brak zapamiętanych kart płatniczych', 'przelewy24');
    $t['save'] = __('Zapisz', 'przelewy24');

    if (isset($_GET['cardrm']) && (int)$_GET['cardrm'] > 0) {
        WC_Gateway_Przelewy24::del_card(get_current_user_id(), (int)$_GET['cardrm']);
    }

    if (isset($_POST['act']) && $_POST['act'] == 'cc_forget') {
        WC_Gateway_Przelewy24::set_cc_forget(get_current_user_id(), $_POST['cc_forget'] == '1');
        if (isset($_POST['cc_forget']) && $_POST['cc_forget'] == '1') {
            $all_cards = WC_Gateway_Przelewy24::get_all_cards(get_current_user_id());
            if (is_array($all_cards)) {
                foreach ($all_cards as $item) {
                    WC_Gateway_Przelewy24::del_card(get_current_user_id(), (int)$item->id);
                }
            }
        }
    }

    $cc_forget_checked = WC_Gateway_Przelewy24::get_cc_forget(get_current_user_id()) ? ' checked="checked" ' : '';
    $all_cards = WC_Gateway_Przelewy24::get_all_cards(get_current_user_id());
    echo <<<HTML
            <h2>{$t['my_cards']}</h2>
                <div id="my-stored-cards">
                <p>
                    <form method="post">
                        <label for="cc_forget">
                            <input type="hidden" name="act" value="cc_forget">
                            <input type="checkbox" name="cc_forget" id="cc_forget" {$cc_forget_checked} value="1" onChange="jQuery('#cc_forget_save').fadeIn()">
                            <span> {$t['cc_forget']} </span>
                            <button class="button" id="cc_forget_save" style="display:none"> {$t['save']} </button>
                        </label>
                    </form>
                </p>
HTML;
    if (is_array($all_cards) && sizeof($all_cards) > 0) {
        foreach ($all_cards as $item) {
            $ccard = $item->custom_value;
            $ccard['exp'] = substr($ccard['exp'], 0, 2) . '/' . substr($ccard['exp'], 2);
            echo <<<HTML
                    <div class="ccbox">
                        <h5 class="page-heading">{$ccard['type']}</h5>
                        <p>{$ccard['mask']}</p>
                        <p>{$ccard['exp']}</p>
                        <a class="button" href="?cardrm={$item->id}"
                            onclick="return confirm('{$t['are_you_sure']}');"
                            title="{$t['delete_card']}">
                            {$t['delete_card']}
                        </a>
                    </div>                
HTML;
        }
    } else {
        echo "<h5>{$t['no_cards']}</h5>";
    }
    echo <<<HTML
            </div>
            <style>
                #my-stored-cards .ccbox {
                    background: #fbfbfb;
                    border: 1px solid #d6d4d4;
                    padding: 1em;
                    margin: 1em;
                    width: 40%;
                    display: inline-block;
                }
                #my-stored-cards .ccbox:nth-child(odd) { margin-left:1%; }
            </style>            
HTML;
}

add_action('woocommerce_after_my_account', 'my_stored_cards');

function woocommerce_p24_action_add($actions) {

    global $theorder;
    if(!$theorder->is_paid() && $theorder->get_payment_method() === 'przelewy24') {
        $actions['wc_przelewy24_email_action'] = __('Wyślij e-mail do płatności przez Przelewy24', 'przelewy24');
    }
    return $actions;
}

function woocommerce_p24_email_filter($email_filters){
    $email_filters[] = 'woocommerce_order_action_wc_przelewy24_email_action';
    return $email_filters;
}

function woocommerce_p24_email_send($order_id){

    require_once( 'includes/Przelewy24Mailer.class.php' );
    $mailer = new Przelewy24Mailer();
    $mailer->trigger($order_id);

}

function php_soap_extension_error()
{
    echo '<div class="error notice">' . __("Wtyczka Przelewy24 jest nieaktywna, ponieważ brakuje rozszerzenia PHP-SOAP.", "przelewy24") . '</div>';
}

function php_curl_extension_error()
{
    echo '<div class="error notice">' . __("Wtyczka Przelewy24 jest nieaktywna, ponieważ brakuje rozszerzenia PHP-CURL.", "przelewy24") . '</div>';
}

function woocommerce_not_installed_error()
{
    echo '<div class="error notice">' . __("Wtyczka Przelewy24 jest nieaktywna, najpierw musi być zainstalowana i aktywna wtyczka WooCommerce.", "przelewy24") . '</div>';
}

function woocommerce_version_error()
{
    echo '<div class="error">' . __("Wtyczka Przelewy24 jest nieaktywna, ponieważ nie znaleziono odpowiedniej wersji WooCommerce.", "przelewy24") . '</div>';
}

function check_woocommerce_version_compatibility($min, $max)
{
    return (version_compare(WOOCOMMERCE_VERSION, $min, ">=") && version_compare(WOOCOMMERCE_VERSION, $max, "<"));
}


