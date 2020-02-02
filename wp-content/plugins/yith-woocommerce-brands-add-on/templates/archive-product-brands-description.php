<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

global $product;
?>

<?php if( apply_filters( 'yith_wcbr_print_brand_description', true, $term ) ): ?>
<div class="yith-wcbr-archive-header term-description">
	<?php
	if( ! empty( $term_description ) ){
		echo wpautop( do_shortcode( $term_description ) );
	}
	?>
</div>
<?php endif; ?>