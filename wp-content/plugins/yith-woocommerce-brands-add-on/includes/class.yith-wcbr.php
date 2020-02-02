<?php
/**
 * Main class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Brands
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

if ( ! class_exists( 'YITH_WCBR' ) ) {
	/**
	 * WooCommerce Brands
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBR {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCBR
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Taxonomy slug
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public static $brands_taxonomy = 'yith_product_brand';
		
		/**
		 * Rewrite for brands
		 *
		 * @var string
		 * @since 1.2.0
		 */
		public static $brands_rewrite = 'product-brands';

		/**
		 * Constructor.
		 *
		 * @return \YITH_WCBR
		 * @since 1.0.0
		 */
		public function __construct() {
			// load plugin-fw
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

			// register new image dimensions
			add_action( 'after_setup_theme', array( $this, 'register_image_size' ) );

			// register brand taxonomy
			add_action( 'init', array( $this, 'register_taxonomy' ) );
			add_filter( 'yith_wcan_product_taxonomy_type', array( $this, 'add_ajax_navigation_taxonomy' ) );

			// register shortcodes
			add_action( 'init', array( 'YITH_WCBR_Shortcode', 'init' ), 5 );

			// add brand template to products page
			add_action( 'woocommerce_product_meta_end', array( $this, 'add_single_product_brand_template' ) );

			// add description to archive page
			add_action( 'woocommerce_archive_description', array( $this, 'add_archive_brand_template' ), 7 );
			add_action( 'yith_before_shop_page_meta', array( $this, 'add_archive_brand_template' ), 7 );

			// enqueue styles
			add_action( 'init', array( $this, 'register_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// register taxonomy as product taxonomy for YIT Layout
			add_filter( 'yit_layout_option_is_product_tax', array( $this, 'register_layout' ) );

			// add brand taxonomy to ones WooCommerce uses to alter count basing on products visibility
			add_filter( 'woocommerce_change_term_counts', array( $this, 'change_term_counts' ) );

            // Set Brand taxonomy term when you duplicate the product
            add_action( 'woocommerce_product_duplicate', array( $this,'woocommerce_product_duplicate' ),10,2 );
		}

		/* === PLUGIN FW LOADER === */

		/**
		 * Loads plugin fw, if not yet created
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if( ! empty( $plugin_fw_data ) ){
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once( $plugin_fw_file );
				}
			}
		}

		/* === TAXONOMY METHODS === */

		/**
		 * Register taxonomy for brands
		 *
		 * @return void
		 * @since 1.0.0
		 */
		 public function register_taxonomy(){
			 self::$brands_taxonomy = apply_filters( 'yith_wcbr_taxonomy_slug', self::$brands_taxonomy );
			 
			 $taxonomy_labels = array(
				 'name' => apply_filters( 'yith_wcbr_taxonomy_label_name', __( 'Brands', 'yith-woocommerce-brands-add-on' ) ),
				 'singular_name' => __( 'Brand', 'yith-woocommerce-brands-add-on' ),
				 'all_items' => __( 'All Brands', 'yith-woocommerce-brands-add-on' ),
				 'edit_item' => __( 'Edit Brand', 'yith-woocommerce-brands-add-on' ),
				 'view_item' => __( 'View Brand', 'yith-woocommerce-brands-add-on' ),
				 'update_item' => __( 'Update Brand', 'yith-woocommerce-brands-add-on' ),
				 'add_new_item' => __( 'Add New Brand', 'yith-woocommerce-brands-add-on' ),
				 'new_item_name' => __( 'New Brand Name', 'yith-woocommerce-brands-add-on' ),
				 'parent_item' => __( 'Parent Brand', 'yith-woocommerce-brands-add-on' ),
				 'parent_item_colon' => __( 'Parent Brand:', 'yith-woocommerce-brands-add-on' ),
				 'search_items' => __( 'Search Brands', 'yith-woocommerce-brands-add-on' ),
				 'separate_items_with_commas' => __( 'Separate brands with commas', 'yith-woocommerce-brands-add-on' ),
				 'not_found' => __( 'No Brands Found', 'yith-woocommerce-brands-add-on' )
			 );

			 $taxonomy_args = array(
				 'label' => apply_filters( 'yith_wcbr_taxonomy_label', __( 'Brands', 'yith-woocommerce-brands-add-on' ) ),
				 'labels' => apply_filters( 'yith_wcbr_taxonomy_labels', $taxonomy_labels ),
				 'public' => true,
				 'show_admin_column' => true,
				 'hierarchical' => true,
				 'rewrite' => array(
					 'slug' => apply_filters( 'yith_wcbr_taxonomy_rewrite', self::$brands_rewrite ),
					 'hierarchical' => true,
					 'with_front' => apply_filters( 'yith_wcbr_taxonomy_with_front', true )
				 ),
                 'capabilities' => apply_filters( 'yith_wcbr_taxonomy_capabilities', array(
                         'manage_terms' => 'manage_product_terms',
                         'edit_terms'   => 'edit_product_terms',
                         'delete_terms' => 'delete_product_terms',
                         'assign_terms' => 'assign_product_terms',
                     )
                 ),
				 'update_count_callback' => '_wc_term_recount',
			 );

			 $object_type = apply_filters( 'yith_wcbr_taxonomy_object_type', 'product' );

			 register_taxonomy( self::$brands_taxonomy, $object_type, $taxonomy_args );

			 if( is_array( $object_type ) && ! empty( $object_type ) ){
				 foreach( $object_type as $type ){
					 register_taxonomy_for_object_type( self::$brands_taxonomy, $type );
				 }
			 }
			 else{
				 register_taxonomy_for_object_type( self::$brands_taxonomy, $object_type );
			 }
		 }

		/**
		 * Register brand taxonomy to change term counts depending on product visibility
		 *
		 * @param $taxonomies array Array of registered taxonomies
		 * @return array Filtered array of registered taxonomies
		 * @since 1.1.2
		 */
		public function change_term_counts( $taxonomies ) {
			$taxonomies[] = self::$brands_taxonomy;

			return $taxonomies;
		}

		/**
		 * Add compatibility to Ajax Navigation, forcing widget to display on brands archive pages
		 *
		 * @param $tax array Valid product taxonomies where to show ajax navigation widget
		 * @return array Filtered array
		 * @since 1.0.0
		 */
		public function add_ajax_navigation_taxonomy( $tax ) {
			return array_merge(
				$tax,
				array( self::$brands_taxonomy )
			);
		}
		
		/* === FRONTEND METHODS === */

		/**
		 * Register frontend scripts
		 *
		 * @return void
		 * @since 1.3.0
		 */
		public function register_scripts() {
			// include payment form template
			$template_name = 'brands.css';
			$locations = array(
				trailingslashit( WC()->template_path() ) . 'yith-wcbr/' . $template_name,
				trailingslashit( WC()->template_path() ) . $template_name,
				'yith-wcbr/' . $template_name,
				$template_name
			);

			$template = locate_template( $locations );

			if( ! $template ){
				$template = YITH_WCBR_URL . 'assets/css/yith-wcbr.css';
			}
			else{
				$search     = array( get_stylesheet_directory(), get_template_directory() );
				$replace    = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
				$template = str_replace( $search, $replace, $template );
			}

			wp_register_style( 'yith-wcbr', $template );
		}

		/**
		 * Enqueue frontend scripts
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			do_action( 'yith_wcbr_enqueue_frontend_style' );
			wp_enqueue_style( 'yith-wcbr' );
		}

		/**
		 * Register thumb size for brand logo on single product page
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_image_size() {
			$default_values = array(
				'width' => 0,
				'height' => 30,
				'crop' => true
			);
			$stored_values = get_option( 'yith_wcbr_single_product_brands_size', $default_values );

			$single_thumb_width = apply_filters( 'yith_wcbr_single_thumb_width', $stored_values['width'] );
			$single_thumb_height = apply_filters( 'yith_wcbr_single_thumb_height', $stored_values['height'] );
			$single_thumb_crop = apply_filters( 'yith_wcbr_single_thumb_crop', isset( $stored_values['crop'] ) ? $stored_values['crop'] : false );

			add_image_size( 'yith_wcbr_logo_size', $single_thumb_width, $single_thumb_height, $single_thumb_crop );
		}

		/**
		 * Include template for brands on single product page
		 *
		 * @param $product_id int|bool Current product id; leave empty to use global product
		 * @param $title string Title to show when using in shortcode
		 * @param $show_logo string|bool Whether to show logo or not (yes - no; false to use default)
		 * @param $show_title string|bool Whether to show title or not (yes - no; false to use default)
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_single_product_brand_template( $product_id = false, $title = '', $show_logo = false, $show_title = false ) {
			global $product;

			$current_product = $product_id ? wc_get_product( $product_id ) : $product;
			$current_product_id = yit_get_product_id( $current_product );

			// retrieve data to use in template
			$brands_taxonomy = self::$brands_taxonomy;
			$before_term_list = apply_filters( 'yith_wcbr_single_product_before_term_list', '' );
			$after_term_list = apply_filters( 'yith_wcbr_single_product_after_term_list', '' );
			$term_list_sep = apply_filters( 'yith_wcbr_single_product_term_list_sep', ', ' );
			$brands_label = get_option( 'yith_wcbr_brands_label' );
			$product_brands = get_the_terms( $current_product_id, self::$brands_taxonomy );
			$product_has_brands = ! is_wp_error( $product_brands ) && $product_brands;
			$content_to_show = get_option( 'yith_wcbr_single_product_brands_content', 'both' );

			if( $show_logo == 'yes' && $show_title == 'yes' ){
				$content_to_show = 'both';
			}
			elseif( $show_logo == 'yes' ){
				$content_to_show = 'logo';
			}
			elseif( $show_title == 'yes' ){
				$content_to_show = 'name';
			}

			$args = array(
				'title' => $title,
				'product' => $current_product,
				'product_id' => $current_product_id,
				'brands_taxonomy' => $brands_taxonomy,
				'before_term_list' => $before_term_list,
				'after_term_list' => $after_term_list,
				'term_list_sep' => $term_list_sep,
				'brands_label' => $brands_label,
				'product_brands' => $product_brands,
				'product_has_brands' => $product_has_brands,
				'content_to_show' => $content_to_show
			);

			// include payment form template
			$template_name = 'single-product-brands.php';

			yith_wcbr_get_template( $template_name, $args );
		}

		/**
		 * Include template for brands on archive product page
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_archive_brand_template() {
			if( is_tax( self::$brands_taxonomy ) && get_query_var( 'paged' ) == 0 ){

				/**
				 * From WC 2.7, WooCommerce adds description for each product taxonomy
				 * We remove default WooCommerce action, to keep using our template
				 * Maybe in a future this function could be remove at all, to leave just WooCommerce behaviour
				 */
				if( version_compare( WC()->version, '2.7.0', '>=' ) ) {
					remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description' );
				}

				// retrieve data to use in template
				$qo = get_queried_object();
				$term_id = $qo->term_id;
				$term = get_term( $term_id, self::$brands_taxonomy );
				$term_description = $term->description;

				// include payment form template
				$template_name = 'archive-product-brands-description.php';
				$locations = array(
					trailingslashit( WC()->template_path() ) . 'yith-wcbr/' . $template_name,
					trailingslashit( WC()->template_path() ) . $template_name,
					'yith-wcbr/' . $template_name,
					$template_name
				);

				$template = locate_template( $locations );

				if( ! $template ){
					$template = YITH_WCBR_DIR . 'templates/' . $template_name;
				}

				include( $template );
			}
		}

		/**
		 * Register Brands as product taxonomy for YIT Layout
		 *
		 * @param $is_product_taxonomy bool Whether current queried object is a product taxonomy
		 *
		 * @return bool Filtered value
		 */
		public function register_layout( $is_product_taxonomy ) {
			global $wp_query;

			if ( $wp_query->is_tax( self::$brands_taxonomy ) ) {
				return true;
			}

			return $is_product_taxonomy;
		}


        /**
         * Set brands for duplicated product
         * @param $duplicate
         * @param $product
         */
        public function woocommerce_product_duplicate( $duplicate, $product ){
            $brands = wp_get_object_terms( $product->get_id(), self::$brands_taxonomy );
            $brands_ids = array();
            if( count($brands) > 0 ){
                foreach ( $brands as $brand ){
                    $brands_ids[] = $brand->term_id;
                }
                wp_set_object_terms( $duplicate->get_id(),$brands_ids,self::$brands_taxonomy );
            }

        }

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCBR
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}

/**
 * Unique access to instance of YITH_WCBR class
 *
 * @return \YITH_WCBR
 * @since 1.0.0
 */
function YITH_WCBR(){
	return YITH_WCBR::get_instance();
}