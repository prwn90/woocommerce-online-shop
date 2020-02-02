<?php

class WOOF_RATE_ALERT {

    protected $notes_for_free = true;

    public function __construct($for_free) {
        $this->notes_for_free = $for_free;
    }

    public function init() {
        if (is_admin()) {

            global $wp_version;
            if (version_compare($wp_version, '4.2', '>=')) {
                $hide_alert = get_option('woof_rate_alert', 0);

                if (!$hide_alert) {
                    $alert = intval(get_option('woof_alert_rev', 0));

                    if (!$alert) {
                        update_option('woof_alert_rev', time());
                        $alert = time();
                    }

                    if (time() >= ($alert + 86400 * 7)) {//7 days
                        add_action('admin_notices', array($this, 'woof_alert'));
                        add_action('network_admin_notices', array($this, 'woof_alert'));
                        add_action('wp_ajax_woof_dismiss_rate_alert', array($this, 'woof_dismiss_alert'));
                    }
                }
            }
        }
    }

    function woof_alert() {

        if (isset($_GET['tab']) AND $_GET['tab'] == 'woof') {
            $support_link = 'https://pluginus.net/support/forum/woof-woocommerce-products-filter/';
            ?>
            <div class="notice notice-warning is-dismissible" id="woof_rate_alert" data-nonce="<?php echo json_encode(wp_create_nonce('woof_dissmiss_rate_alert')) ?>">
                <p class="plugin-card-woocommerce-currency-switcher">
                    <?php printf(__("Hello! Looks like you using <b>WooCommerce Products Filter (WOOF)</b> for some time and I hope this software helped you with your business. If you happy with the plugin functionality and like Products Filter - rate please WOOF with 5-stars, also share your opinion and ideas with us. Thank you!<br /> P.S. If you have troubles you can always ask %s about help.", 'woocommerce-products-filter'), "<a href='{$support_link}' target='_blank'>" . __('support', 'woocommerce-products-filter') . "</a>") ?>
                </p>

                <hr />

                <?php
                $link = 'https://codecanyon.net/downloads#item-11498469';
                if ($this->notes_for_free) {
                    $link = 'https://wordpress.org/support/plugin/woocommerce-products-filter/reviews/#new-post';
                }
                ?>


                <table style="width: 100%; margin-bottom: 7px;">
                    <tr>
                        <td style="width: 33%; text-align: center;">
                            <a href="<?= $link ?>" target="_blank" class="woof-panel-button dashicons-before dashicons-star-filled">&nbsp;<?php echo __('Write marvellous review about WOOF features', 'woocommerce-products-filter') ?></a>
                        </td>

                        <td style="width: 33%; text-align: center;">
                            <a href="javascript: jQuery('#woof_rate_alert .notice-dismiss').trigger('click');void(0);" class="button button-large dashicons-before dashicons-thumbs-up">&nbsp;<?php echo __('It is done!', 'woocommerce-products-filter') ?></a>
                        </td>

                        <td style="width: 33%; text-align: center;">
                            <a href="https://pluginus.net/support/forum/woof-woocommerce-products-filter/" target="_blank" class="woof-panel-button dashicons-before dashicons-hammer"><?php echo __('WooCommerce Products filter SUPPORT', 'woocommerce-products-filter') ?></a>
                        </td>
                    </tr>
                </table>


            </div>
            <script>
                jQuery(function ($) {
                    var alert_w = $('#woof_rate_alert');
                    alert_w.on('click', '.notice-dismiss', function (e) {
                        //e.preventDefault 

                        $.post(ajaxurl, {action: 'woof_dismiss_rate_alert',
                            sec: <?php echo json_encode(wp_create_nonce('woof_dissmiss_rate_alert')) ?>
                        });
                    });
                });
            </script>

            <?php
        }
    }

    public function woof_dismiss_alert() {
        check_ajax_referer('woof_dissmiss_rate_alert', 'sec');

        add_option('woof_rate_alert', 1, '', 'no');
        update_option('woof_rate_alert', 1);

        exit;
    }

}
