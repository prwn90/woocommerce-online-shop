<?php
/**
 * Template with tabs of multi currency config.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $tab ) ) {
	throw new LogicException( 'The variable $tab is not set.' );
}

if ( ! isset( $multicurrency ) ) {
	throw new LogicException( 'The variable $multicurrency is not set.' );
}

?>

<nav class="p24-horizontal-tab-menu">

	<?php if ( 'main' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Ustawienia główne' ) ); ?></span>
	<?php else : ?>
		<a href="?page=p24-multi-currency"><?php echo esc_html( __( 'Ustawienia główne' ) ); ?></a>
	<?php endif; ?>

	<?php if ( 'multipliers' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Mnożniki walut' ) ); ?></span>
	<?php elseif ( $multicurrency ) : ?>
		<a href="?page=p24-multi-currency&tab=multipliers"><?php echo esc_html( __( 'Mnożniki walut' ) ); ?></a>
	<?php else : ?>
		<span class="inactive"><?php echo esc_html( __( 'Mnożniki walut' ) ); ?></span>
	<?php endif; ?>

	<?php if ( 'formats' === $tab ) : ?>
		<span class="active"><?php echo esc_html( __( 'Formaty wyświetlania walut' ) ); ?></span>
	<?php elseif ( $multicurrency ) : ?>
		<a href="?page=p24-multi-currency&tab=formats"><?php echo esc_html( __( 'Formaty wyświetlania walut' ) ); ?></a>
	<?php else : ?>
		<span class="inactive"><?php echo esc_html( __( 'Formaty wyświetlania walut' ) ); ?></span>
	<?php endif; ?>

</nav>

<?php
