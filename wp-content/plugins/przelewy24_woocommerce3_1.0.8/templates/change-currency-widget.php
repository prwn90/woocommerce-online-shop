<?php
/**
 * Template for currency selector widget.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $widget_title ) ) {
	throw new LogicException( 'The variable $widget_title is not set.' );
}
if ( ! isset( $currency_options ) ) {
	throw new LogicException( 'The variable $currency_options is not set.' );
}
if ( ! isset( $active_currency ) ) {
	throw new LogicException( 'The variable $active_currency is not set.' );
}

?>

<form method="post">
	<h2 class="widget-title"><?php echo esc_html( $widget_title ); ?></h2>

	<div id="p24-change-currency-widget">
	<label> <?php echo esc_html( __( 'Waluta' ) ); ?>
	<select name="p24_currency">
	<?php foreach ( $currency_options as $currency_option ) : ?>
		<option value="<?php echo esc_attr( $currency_option ); ?>" <?php echo (string) $currency_option === (string) $active_currency ? 'selected="selected"' : ''; ?>>
			<?php echo esc_html( $currency_option ); ?>
		</option>
	<?php endforeach; ?>
	</select>
	</label>
	<input type="hidden" name="p24_action_type_field" value="change_currency" />
	<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
	<input type="submit" value="<?php echo esc_attr( __( 'Zmień' ) ); ?>" />
	</div>
</form>
<?php
