<?php
/**
 * Template to set currency formats.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $currency_options ) ) {
	throw new LogicException( 'The variable $currency_options is not set.' );
}
if ( ! isset( $active_currency ) ) {
	throw new LogicException( 'The variable $active_currency is not set.' );
}
if ( ! isset( $format ) ) {
	throw new LogicException( 'The variable $format is not set.' );
} elseif ( ! is_array( $format ) ) {
	throw new LogicException( 'The variable $format has to be an array.' );
}

?>

<h1><?php echo esc_html( __( 'Formatowanie walut' ) ); ?></h1>

<form method="post">
	<table>

		<tr>
			<?php $field_id = '24_currency_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Aktywna waluta' ) ); ?></label>
			</th>
			<td>
				<select title="<?php echo esc_attr( __( 'Aktywna waluta' ) ); ?>" name="p24_currency" id="<?php echo esc_attr( $field_id ); ?>" class="js_currency_admin_selector">
					<?php foreach ( $currency_options as $currency_option ) : ?>
						<option value="<?php echo esc_attr( $currency_option ); ?>" <?php echo $currency_option === $active_currency ? 'selected="selected"' : ''; ?>>
						<?php echo esc_html( $currency_option ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

		<tr>
			<?php $field_id = 'p24_formats_currency_pos_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Pozycja waluty', 'woocommerce' ) ); ?></label>
			</th>
			<td>
				<select name="p24_formats[currency_pos]" id="<?php echo esc_attr( $field_id ); ?>">
					<option value="left" <?php echo 'left' === $format['currency_pos'] ? 'selected="selected"' : ''; ?>><?php echo esc_html( __( 'Po lewej' ) ); ?></option>
					<option value="right" <?php echo 'right' === $format['currency_pos'] ? 'selected="selected"' : ''; ?>><?php echo esc_html( __( 'Po prawej' ) ); ?></option>
					<option value="left_space" <?php echo 'left_space' === $format['currency_pos'] ? 'selected="selected"' : ''; ?>><?php echo esc_html( __( 'Po lewej ze spacją' ) ); ?></option>
					<option value="right_space" <?php echo 'right_space' === $format['currency_pos'] ? 'selected="selected"' : ''; ?>><?php echo esc_html( __( 'Po prawej ze spacją' ) ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<?php $field_id = 'p24_formats_thousand_separator_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_attr( __( 'Separator trzycyfrowy', 'woocommerce' ) ); ?></label>
			</th>
			<td>
				<input name="p24_formats[thousand_separator]" id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $format['thousand_separator'] ); ?>" />
			</td>
		</tr>

		<tr>
			<?php $field_id = 'p24_formats_decimal_separator_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Separator dziesiętny', 'woocommerce' ) ); ?></label>
			</th>
			<td>
				<input name="p24_formats[decimal_separator]" id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $format['decimal_separator'] ); ?>" />
			</td>
		</tr>

		<tr>
			<?php $field_id = 'p24_formats_decimals_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( __( 'Cyfry po przecinku', 'woocommerce' ) ); ?></label>
			</th>
			<td>
				<input type="number" name="p24_formats[decimals]" id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $format['decimals'] ); ?>" />
			</td>
		</tr>

		<tr>
			<td></td>
			<td>
				<input type="hidden" name="p24_action_type_field" value="change_formats" />
				<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
				<input type="submit" value="<?php echo esc_html( __( 'Zapisz' ) ); ?>" />
			</td>
		</tr>

	</table>

</form>

<?php
