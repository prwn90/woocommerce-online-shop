<?php
/**
 * Template for active multi currency.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $value ) ) {
	throw new LogicException( 'The variable $value is not set.' );
}

?>

<h1><?php echo esc_html( __( 'Moduł multi currency' ) ); ?></h1>

<p class="p24-info">
	Wtyczka płatności Przelewy24 posiada zintegrowany moduł do obsługi wielu walut
	w kontekście jednego sklepu. Po jego aktywacji należy przejść do zakładki
	z mnożnikami i dodać kolejne waluty.
</p>

<form method="post">
	<table>
		<tr>
			<?php $field_id = 'p24_multi_currency_active_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Aktywować moduł multi currency' ) ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="p24_multi_currency_active" id="<?php echo esc_attr( $field_id ); ?>" value="yes" <?php echo $value ? 'checked="checked"' : ''; ?> />
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<input type="hidden" name="p24_action_type_field" value="activate_multi_currency" />
				<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
				<input type="submit" value="<?php echo esc_html( __( 'Zapisz' ) ); ?>" />
			</td>
		</tr>

	</table>
</form>
<?php
