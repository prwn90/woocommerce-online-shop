<?php
/**
 * Template to set currency multipliers.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $currency_options ) ) {
	throw new LogicException( 'The variable $currency_options is not set.' );
}
if ( ! isset( $post ) || ! $post instanceof WP_Post ) {
	throw new LogicException( 'The variable $available is not set or is of unsupported class.' );
}

$active_currency = $post->_order_currency;
$order_id        = $post->ID;
?>
<label> <?php echo esc_html( __( 'Waluta' ) ); ?>: <br>
	<select name="p24_order_currency" class="wc-enhanced-select" style="min-width: 50%;">
		<?php foreach ( $currency_options as $currency_option ) : ?>
			<option value="<?php echo esc_attr( $currency_option ); ?>" <?php echo (string) $currency_option === (string) $active_currency ? 'selected="selected"' : ''; ?>>
				<?php echo esc_html( $currency_option ); ?>
			</option>
		<?php endforeach; ?>
	</select>
</label>
<?php
