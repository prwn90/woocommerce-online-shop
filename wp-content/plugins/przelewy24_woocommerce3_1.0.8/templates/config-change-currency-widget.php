<?php
/**
 * Template for configuring currency selector widget.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $widget_title ) ) {
	throw new LogicException( 'The variable $widget_title is not set.' );
}
if ( ! isset( $title_field_id ) ) {
	throw new LogicException( 'The variable $title_field_id is not set.' );
}
if ( ! isset( $title_field_name ) ) {
	throw new LogicException( 'The variable $title_field_name is not set.' );
}

?>

	<p>
		<label for="<?php echo esc_attr( $title_field_id ); ?>">
			<?php esc_html( __( 'Tytuł:' ) ); ?>
		</label>
		<input class="widefat"
			id="<?php echo esc_attr( $title_field_id ); ?>"
			name="<?php echo esc_attr( $title_field_name ); ?>"
			type="text"
			value="<?php echo esc_attr( $widget_title ); ?>" />
	</p>
<?php
