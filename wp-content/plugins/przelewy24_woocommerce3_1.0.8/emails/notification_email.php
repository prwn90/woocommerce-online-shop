<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php echo __('Zamówienie nr', 'przelewy24') . ' ' . (int)$order->get_id() . ' ' . __('nie zostało opłacone.', 'przelewy24') .  ' ' . __('Kliknij w poniższy link aby dokończyć płatność przez Przelewy24', 'przelewy24') ?></p>

<div style="text-align:center; font-size:200%; margin-bottom:5%"><a href="<?php echo filter_var($button, FILTER_SANITIZE_URL)?>"><?php echo __('Zapłać z Przelewy24', 'przelewy24')?></a></div>
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

?>

    <h2>
        <?php
        if ( $sent_to_admin ) {
            $before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
            $after  = '</a>';
        } else {
            $before = '';
            $after  = '';
        }
        /* translators: %s: Order ID. */
        echo wp_kses_post( $before . sprintf( __( 'Zamówienie', 'przelewy24' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
        ?>
    </h2>

    <div style="margin-bottom: 40px;">
        <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Produkt', 'przelewy24' ); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Ilość', 'przelewy24' ); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Cena', 'przelewy24' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            echo wc_get_email_order_items( $order, array( // WPCS: XSS ok.
                'show_sku'      => $sent_to_admin,
                'show_image'    => false,
                'image_size'    => array( 32, 32 ),
                'plain_text'    => $plain_text,
                'sent_to_admin' => $sent_to_admin,
            ) );
            ?>
            </tbody>
            <tfoot>
            <?php
            $totals = $order->get_order_item_totals();

            if ( $totals ) {
                $i = 0;
                foreach ( $totals as $total ) {
                    $i++;
                    ?>
                    <tr>
                        <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
                        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
                    </tr>
                    <?php
                }
            }
            if ( $order->get_customer_note() ) {
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Komentarz:', 'przelewy24' ); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
                </tr>
                <?php
            }
            ?>
            </tfoot>
        </table>
    </div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );


do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_footer', $email );

?>