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
?>

<div class="form-field">
	<label><?php _e( 'Thumbnail', 'yith-woocommerce-brands-add-on' ); ?></label>
	<div id="product_brand_thumbnail" style="float:left;margin-right:10px;"><img alt="<?php _e( 'Product brand thumbnail', 'yith-woocommerce-brands-add-on-premium' ) ?>" src="<?php echo wc_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
	<div style="line-height:60px;">
		<input type="hidden" id="product_brand_thumbnail_id" class="yith_wcbr_upload_image_id" name="product_brand_thumbnail_id" />
		<button id="product_brand_thumbnail_upload" type="button" class="yith_wcbr_upload_image_button button"><?php _e( 'Upload/Add image', 'yith-woocommerce-brands-add-on' ); ?></button>
		<button id="product_brand_thumbnail_remove" type="button" class="yith_wcbr_remove_image_button button"><?php _e( 'Remove image', 'yith-woocommerce-brands-add-on' ); ?></button>
	</div>
	<div class="clear"></div>
</div>