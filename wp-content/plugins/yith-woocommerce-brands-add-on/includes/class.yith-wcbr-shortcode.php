<?php
/**
 * Shortcode class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Brands Add-on
 * @version 1.0.0
 */

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

if ( ! class_exists( 'YITH_WCBR_Shortcode' ) ) {
	/**
	 * WooCommerce Brands Shortcode
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBR_Shortcode {

		/**
		 * Performs all required add_shortcode
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function init() {
			add_shortcode( 'yith_wcbr_show_brand', array( 'YITH_WCBR_Shortcode', 'product_brand' ) );
			add_shortcode( 'yith_wcbr_product_brand', array( 'YITH_WCBR_Shortcode', 'product_brand' ) );

			// register shortcodes to WPBackery Visual Composer & Gutenberg
			add_action( 'vc_before_init', array( 'YITH_WCBR_Shortcode', 'register_vc_shortcodes' ) );
			add_action( 'init', array( 'YITH_WCBR_Shortcode', 'register_gutenberg_blocks' ) );
			add_action( 'yith_plugin_fw_gutenberg_before_do_shortcode', array( 'YITH_WCBR_Shortcode', 'fix_for_gutenberg_blocks' ), 10, 1 );
		}

		/**
		 * Register brands shortcode to visual composer
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public static function register_vc_shortcodes(){
			$vc_map_params = apply_filters( 'yith_wcbr_vc_shortcodes_params', array(

				'yith_wcbr_product_brand' => array(
					'name' => __( 'YITH Product Brand', 'yith-woocommerce-brands-add-on' ),
					'base' => 'yith_wcbr_product_brand',
					'description' => __( 'Adds brand name and logo for a specific product, wherever you want', 'yith-woocommerce-brands-add-on' ),
					'category' => __( 'Brands', 'yith-woocommerce-brands-add-on' ),
					'params' => array(
						array(
							'type' => 'textfield',
							'holder' => 'div',
							'heading' => __( 'Title', 'yith-woocommerce-brands-add-on' ),
							'param_name' => 'title',
							'value' => '',
							'description' => __( 'Text to enter as shortcode title', 'yith-woocommerce-brands-add-on' )
						),
						array(
							'type' => 'textfield',
							'holder' => 'div',
							'heading' => __( 'Product_id', 'yith-woocommerce-brands-add-on' ),
							'param_name' => 'product_id',
							'value' => '',
							'description' => __( 'Enter product ID that will be used to retrieve brands; leave empty to use global product (if defined)', 'yith-woocommerce-brands-add-on' )
						),
						array(
							'type' => 'dropdown',
							'holder' => '',
							'heading' => __( 'Show logo', 'yith-woocommerce-brands-add-on' ),
							'param_name' => 'show_logo',
							'value' => array(
								__( 'Show Logo', 'yith-woocommerce-brands-add-on' ) => 'yes',
								__( 'Do not show Logo', 'yith-woocommerce-brands-add-on' ) => 'no'
							),
							'description' => __( 'Whether to show logo or not. Please, note that if you don\'t mark at least one between Show Logo or Show Title, default option value will take effect', 'yith-woocommerce-brands-add-on' )
						),
						array(
							'type' => 'dropdown',
							'holder' => '',
							'heading' => __( 'Show Title', 'yith-woocommerce-brands-add-on' ),
							'param_name' => 'show_title',
							'value' => array(
								__( 'Show title', 'yith-woocommerce-brands-add-on' ) => 'yes',
								__( 'Do not show title', 'yith-woocommerce-brands-add-on' ) => 'no'
							),
							'description' => __( 'Whether to show title or not. Please, note that if you don\'t mark at least one between Show Logo or Show Title, default option value will take effect', 'yith-woocommerce-brands-add-on' )
						),
					)
				),

			) );

			if( ! empty( $vc_map_params ) && function_exists( 'vc_map' ) ){
				foreach( $vc_map_params as $params ){
					vc_map( $params );
				}
			}
		}

		/**
		 * Register Gutenberg blocks for this plugin
		 *
		 * @return void
		 */
		public static function register_gutenberg_blocks(){
			$blocks = array(
				'yith-wcbr-product-brand' => array(
					'style'          => 'yith-wcbr-shortcode',
					'script'         => 'yith-wcbr',
					'title'          => _x( 'YITH Brands Product Brand', '[gutenberg]: block name', 'yith-woocommerce-brands-add-on' ),
					'description'    => _x( 'Adds brand name and logo for a specific product, wherever you want', '[gutenberg]: block description', 'yith-woocommerce-brands-add-on' ),
					'shortcode_name' => 'yith_wcbr_product_brand',
					'attributes'     => array(
						'title'      => array(
							'type'    => 'text',
							'label'   => __( 'Title', 'yith-woocommerce-brands-add-on' ),
							'default' => '',
						),
						'product_id' => array(
							'type'          => 'text',
							'label'         => __( 'Product_id (leave empty to use global product)', 'yith-woocommerce-brands-add-on' ),
							'default'       => '',
						),
						'show_logo' => array(
							'type'          => 'select',
							'label'         => __( 'Show logo', 'yith-woocommerce-brands-add-on' ),
							'default'       => 'yes',
							'options'         => array(
								'no' => _x( 'Hide logo', '[gutenberg]: Help text', 'yith-woocommerce-brands-add-on' ),
								'yes' => _x( 'Show logo', '[gutenberg]: Help text', 'yith-woocommerce-brands-add-on' ),
							),
						),
						'show_title' => array(
							'type'          => 'select',
							'label'         => __( 'Show Title', 'yith-woocommerce-brands-add-on' ),
							'default'       => 'yes',
							'options'         => array(
								'no' => _x( 'Hide title', '[gutenberg]: Help text', 'yith-woocommerce-brands-add-on' ),
								'yes' => _x( 'Show title', '[gutenberg]: Help text', 'yith-woocommerce-brands-add-on' ),
							),
						)
					),
				),
			);

			yith_plugin_fw_gutenberg_add_blocks( $blocks );
		}

		/**
		 * Fix preview of Gutenberg blocks at backend
		 *
		 * @param $shortcode string Shortcode to render
		 * @return void
		 */
		public static function fix_for_gutenberg_blocks( $shortcode ){
			if( strpos( $shortcode, '[yith_wcbr_product_brand' ) !== false ){
				if( strpos( $shortcode, 'product_id=""' ) !== false ){
					$terms = yith_wcbr_get_terms( YITH_WCBR::$brands_taxonomy, array(
						'hide_empty' => true
					) );

					if( ! empty( $terms ) ){
						$products = get_posts( array(
							'post_type' => 'product',
							'post_status' => 'publish',
							'posts_per_page' => 1,
							'fields' => 'ids',
							'tax_query' => array(
								array(
									'taxonomy' => YITH_WCBR::$brands_taxonomy,
									'terms' => wp_list_pluck( $terms, 'term_id' )
								)
							)
						) );

						if( ! empty( $products ) ){
							global $product;
							$product_id = array_pop( $products );
							$product = wc_get_product( $product_id );
						}
					}
				}
			}
		}

		/**
		 * Returns output for product brand
		 *
		 * @param $atts mixed Array of shortcodes attributes
		 *
		 * @return string Shortcode content
		 * @since 1.0.10
		 */
		public static function product_brand( $atts ){
			/**
			 * The following variables will be extracted from $atts
			 * @var $product_id
			 * @var $title
			 * @var $show_logo
			 * @var $show_title
			 */

			global $product;

			$defaults = array(
				'title'           => '',
				'product_id'      => '',    // int (product id that will be used to retrieve brands; leave empty to use global product, if defined)
				'show_logo'       => 'yes', // yes - no (whether to show brand logo or not)
				'show_title'      => 'yes', // yes - no (whether to show brand title or not)
				'content_to_show' => ''     // both - name - logo (whether to show both brand logo & title, just title, or just logo; when passed overrides show_logo and show_title)
			);

			$atts = shortcode_atts(
				$defaults,
				$atts
			);

			// make attributes available
			extract( $atts );

			if( ! $product && ! $product_id ){
				return '';
			}

			if( ! empty( $content_to_show ) ){
				switch( $content_to_show ){
					case 'name':
						$show_logo = 'no';
						$show_title = 'yes';
						break;
					case 'logo':
						$show_logo = 'yes';
						$show_title = 'no';
						break;
					case 'both':
					default:
						$show_logo = 'yes';
						$show_title = 'yes';
				}
			}

			ob_start();
			YITH_WCBR()->add_single_product_brand_template( $product_id, $title, $show_logo, $show_title );

			return ob_get_clean();
		}

	}
}