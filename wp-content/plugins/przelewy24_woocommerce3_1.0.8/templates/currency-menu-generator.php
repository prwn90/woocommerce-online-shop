<?php
/**
 * Template for currency selector widget.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $currency_options ) ) {
	throw new LogicException( 'The variable $currency_options is not set.' );
}

?>

<div id="p24-currency-menu" class="posttypediv">
	<div class="tabs-panel tabs-panel-active">
		<ul class="categorychecklist form-no-clear">
			<?php $i = -1; ?>
			<?php foreach ( $currency_options as $currency_option ) : ?>
				<?php $name_prefix = "menu-item[$i]"; ?>
				<?php $label = __( 'Zmiana waluty na', 'przelewy24' ) . ' ' . $currency_option; ?>
				<li>
					<label class="menu-item-title">
						<input
							type="checkbox"
							class="menu-item-checkbox"
							name="<?php echo esc_attr( $name_prefix ); ?>[menu-item-object-id]"
							value="-1">
						<?php echo esc_html( $label ); ?>
					</label>
					<input type="hidden"
						class="menu-item-type"
						name="<?php echo esc_attr( $name_prefix ); ?>[menu-item-type]"
						value="custom">
					<input type="hidden"
						class="menu-item-title"
						name="<?php echo esc_attr( $name_prefix ); ?>[menu-item-title]"
						value="<?php echo esc_attr( $label ); ?>">
					<input type="hidden"
						class="menu-item-url"
						name="<?php echo esc_attr( $name_prefix ); ?>[menu-item-url]"
						value="?p24_change_currency=<?php echo esc_attr( $currency_option ); ?>">
					<input type="hidden"
						class="menu-item-classes"
						name="<?php echo esc_attr( $name_prefix ); ?>[menu-item-classes]"
						value="p24-change-currency">
				</li>
				<?php $i--; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<p class="button-controls">
		<span class="list-controls">
			<a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#p24-currency-menu"
				class="select-all">
				<?php echo __( 'Select All' ); ?>
			</a>
		</span>
		<span class="add-to-menu">
			<input type="submit"
				class="button-secondary submit-add-to-menu right"
				value="<?php echo esc_attr( __( 'Add to Menu' ) ); ?>"
				id="submit-p24-currency-menu"
				name="add-post-type-menu-item">
			<span class="spinner"></span>
		</span>
	</p>
</div>
