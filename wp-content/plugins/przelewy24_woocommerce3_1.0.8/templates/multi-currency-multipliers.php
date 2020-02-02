<?php
/**
 * Template to set currency multipliers.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $multipliers ) ) {
	throw new LogicException( 'The variable $multiplers is not set.' );
}
if ( ! isset( $base_currency ) ) {
	throw new LogicException( 'The variable $base_currency is not set.' );
}
if ( ! isset( $available ) ) {
	throw new LogicException( 'The variable $available is not set.' );
}

?>

<h1><?php echo esc_html( __( 'Mnożniki walut' ) ); ?></h1>

<form method="post">
	<table>
		<?php foreach ( $multipliers as $k => $v ) : ?>
			<?php $html_id = 'id_' . wp_rand(); ?>
			<tr class="js-p24-multiplier-box">
				<th>
					<label for="<?php echo esc_html( $html_id ); ?>"><?php echo esc_html( $k ); ?></label>
				</th>
				<td>
					<input type="number" title="<?php echo esc_attr( $k ); ?>" step="0.000001" min="0" id="<?php echo esc_attr( $html_id ); ?>" name="p24_multipliers[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $v ); ?>" <?php echo $k === $base_currency ? 'disabled' : ''; ?> />
				</td>
				<td>
					<input type="button" value="-" style="<?php echo $k === $base_currency ? 'display: none;' : ''; ?>" />
				</td>
			</tr>
		<?php endforeach; ?>

		<tr class="js-currency-adder">
			<?php $html_id = 'id_' . wp_rand(); ?>
			<th>
				<label for="<?php echo esc_html( $html_id ); ?>"><?php echo esc_html( __( 'Dodaj' ) ); ?></label>
			</th>
			<td>
				<select name="p24_new_currency" id="<?php echo esc_attr( $html_id ); ?>" title="<?php echo esc_attr( __( 'Dodaj' ) ); ?>" >
					<?php foreach ( $available as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>">
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="button" value="+" />
			</td>
		</tr>

		<tr>
			<td></td>
			<td colspan="2">
				<input type="hidden" name="p24_action_type_field" value="change_multipliers" />
				<?php wp_nonce_field( 'p24_action', 'p24_nonce' ); ?>
				<input type="submit" value="<?php echo esc_html( __( 'Zapisz' ) ); ?>" />
			</td>
		</tr>

	</table>
</form>
<?php
