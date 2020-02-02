<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Przelewy24Mailer {

    /**
     * Function to find gateway that can provide configuration.
     *
     * @return null|WC_Gateway_Przelewy24 The class that can provide configuration.
     */
    private function find_p24_gateway() {
        $gateways = WC()->payment_gateways()->payment_gateways;
        foreach ($gateways as $gateway) {
            if ($gateway instanceof WC_Gateway_Przelewy24) {
                return $gateway;
            }
        }
        return null;
    }

    /**
     * Send amil.
     *
     * @param WC_Order|null $order Order that need an mail.
     */
    public function trigger($order) {

        if ( !$order )
            return;

        if($order->is_paid() || $order->get_payment_method() !== 'przelewy24')
            return;

        $gateway = $this->find_p24_gateway();
        $generator = new Przelewy24Generator($gateway);

        $mailer = WC()->mailer();

        $recipient = $order->get_billing_email();
        $subject = __('Dokończenie płatności Przelewy24', 'przelewy24');

        $content = $this->get_content( $order, $subject, $mailer, $generator->create_trnRequest($order) );
        $headers = "Content-Type: text/html";

        $mailer->send( $recipient, $subject, $content, $headers );
    }


    function get_content( $order, $heading = false, $mailer, $button ) {

        $template = '/emails/notification_email.php';

        return wc_get_template_html( $template, array(
            'order'         => $order,
            'email_heading' => $heading,
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'         => $mailer,
            'button'        => $button
        ), '', PRZELEWY24_PATH );
    }

}