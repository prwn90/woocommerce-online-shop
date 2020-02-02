<?php
/**
 * Admin class
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

if ( ! class_exists( 'YITH_WCBR_Admin' ) ) {
	/**
	 * WooCommerce Brands Admin
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBR_Admin {
		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCBR_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Docs url
		 *
		 * @var string Official documentation url
		 * @since 1.0.0
		 */
		public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-brands-add-on/';

		/**
		 * Premium landing url
		 *
		 * @var string Premium landing url
		 * @since 1.0.0
		 */
		public $premium_landing_url = 'https://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/';

		/**
		 * List of available tab for brands panel
		 *
		 * @var array
		 * @access public
		 * @since 1.0.0
		 */
		public $available_tabs = array();

		/**
		 * Constructor method
		 *
		 * @return \YITH_WCBR_Admin
		 * @since 1.0.0
		 */
		public function __construct() {
			// sets available tab
			$this->available_tabs = apply_filters( 'yith_wcbr_available_admin_tabs', array(
				'settings' => __( 'Settings', 'yith-woocommerce-brands-add-on' ),
				'premium' => __( 'Premium Version', 'yith-woocommerce-brands-add-on' )
			) );

			// register plugin panel
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_wcbr_premium_tab', array( $this, 'print_premium_tab' ) );

			// register plugin links & meta row
			add_filter( 'plugin_action_links_' . YITH_WCBR_INIT, array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'add_plugin_meta' ), 10, 5 );

			// register taxonomy custom fields
			add_action( 'init', array( $this, 'init_brand_taxonomy_fields' ), 15 );
			add_action( 'created_term', array( $this, 'save_brand_taxonomy_fields' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'save_brand_taxonomy_fields' ), 10, 3 );

			// add taxonomy columns
			add_action( 'init', array( $this, 'init_brand_taxonomy_columns' ), 15 );

			// Taxonomy page descriptions
			add_action( YITH_WCBR::$brands_taxonomy . '_pre_add_form', array( $this, 'brand_taxonomy_description' ) );

			// enqueue needed scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		}

		/**
		 * Enqueue plugin admin styles when required
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue() {
			// enqueue admin scripts
			$screen = get_current_screen();
			$path = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'unminified/' : '';
			$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';

			if( $screen->id == 'edit-' . YITH_WCBR::$brands_taxonomy || $screen->id == 'yith-plugins_page_yith_wcbr_panel' ){
				wp_enqueue_media();
				wp_enqueue_script( 'yith-wcbr-admin', YITH_WCBR_URL . 'assets/js/admin/' . $path . 'yith-wcbr' . $suffix . '.js', array( 'jquery' ), false, true );

				wp_localize_script( 'yith-wcbr-admin', 'yith_wcbr', array(
					'labels' => array(
						'upload_file_frame_title' => __( 'Choose an image', 'yith-woocommerce-brands-add-on' ),
						'upload_file_frame_button' => __( 'Use image', 'yith-woocommerce-brands-add-on' )
					),
					'wc_placeholder_img_src' => wc_placeholder_img_src()
				) );
			}
		}

		/* === PLUGIN TAXONOMY METHODS === */

		/**
		 * Init custom fields for brand taxonomy
		 *
		 * @return void
		 * @since 1.1.2
		 */
		public function init_brand_taxonomy_fields() {
			add_action( YITH_WCBR::$brands_taxonomy . '_add_form_fields', array( $this, 'add_brand_taxonomy_fields' ), 15, 1 );
			add_action( YITH_WCBR::$brands_taxonomy . '_edit_form_fields', array( $this, 'edit_brand_taxonomy_fields' ), 15, 1 );
		}

		/**
		 * Prints custom term fields on "Add Brand" page
		 *
		 * @param $term string Current taxonomy id
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function add_brand_taxonomy_fields( $term ) {
			include( YITH_WCBR_DIR . 'templates/admin/add-brand-taxonomy-form.php' );
		}

		/**
		 * Prints custom term fields on "Edit Brand" page
		 *
		 * @param $term string Current taxonomy id
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function edit_brand_taxonomy_fields( $term ) {
			$thumbnail_id = absint( yith_wcbr_get_term_meta( $term->term_id, 'thumbnail_id', true ) );
			$image = $thumbnail_id ? wp_get_attachment_thumb_url( $thumbnail_id ) : wc_placeholder_img_src();

			include( YITH_WCBR_DIR . 'templates/admin/edit-brand-taxonomy-form.php' );
		}

		/**
		 * Save custom term fields
		 *
		 * @param $term_id int Currently saved term id
		 * @param $tt_id int|string Term Taxonomy id
		 * @param $taxonomy string Current taxonomy slug
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function save_brand_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
			if ( isset( $_POST['product_brand_thumbnail_id'] ) && YITH_WCBR::$brands_taxonomy === $taxonomy ) {
				yith_wcbr_update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_brand_thumbnail_id'] ) );
			}
		}

		/**
		 * Add custom columns to brand taxonomy table
		 *
		 * @return void
		 * @since 1.2.2
		 */
		public function init_brand_taxonomy_columns() {
			add_filter( 'manage_edit-' . YITH_WCBR::$brands_taxonomy . '_columns', array( $this, 'brand_taxonomy_columns' ), 15 );
			add_filter( 'manage_' . YITH_WCBR::$brands_taxonomy . '_custom_column', array( $this, 'brand_taxonomy_column' ), 15, 3 );
		}

		/**
		 * Register custom columns for "Add Brand" taxonomy view
		 *
		 * @param $columns mixed Old columns
		 *
		 * @return mixed Filtered array of columns
		 * @since 1.0.0
		 */
		public function brand_taxonomy_columns( $columns ) {
			$new_columns          = array();
			if( isset( $columns['cb'] ) ) {
				$new_columns['cb'] = $columns['cb'];
				unset( $columns['cb'] );
			}

			$new_columns['thumb'] = __( 'Image', 'yith-woocommerce-brands-add-on' );

			return apply_filters('yith_wcbr_brand_taxonomy_columns',array_merge( $new_columns, $columns ));
		}

		/**
		 * Prints custom columns for "Add Brand" taxonomy view
		 *
		 * @param $columns mixed Array of columns to print
		 * @param $column string Id of current column
		 * @param $id int id of term being printed
		 *
		 * @return string Output for the columns
		 */
		public function brand_taxonomy_column( $columns, $column, $id ) {

			if ( 'thumb' == $column ) {

				$thumbnail_id = yith_wcbr_get_term_meta( $id, 'thumbnail_id', true );

				if ( $thumbnail_id ) {
					$image = wp_get_attachment_thumb_url( $thumbnail_id );
				} else {
					$image = wc_placeholder_img_src();
				}

				$image = str_replace( ' ', '%20', $image );

				$columns = '<img src="' . esc_url( $image ) . '" alt="' . __( 'Thumbnail', 'yith-woocommerce-brands-add-on' ) . '" class="wp-post-image" height="48" width="48" />';

			}

			return apply_filters('yith_wcbr_brand_taxonomy_column',$columns,$column,$id);
		}

		/**
		 * Prints description for "Add brad" taxonomy view
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function brand_taxonomy_description() {
			echo wpautop( __( 'Product brands for your store can be managed here. To display more brands here, click on "screen options" link on top of the page.', 'yith-woocommerce-brands-add-on' ) );
		}

		/* === PLUGIN PANEL METHODS === */

		/**
		 * Register panel
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_panel() {
			$args = array(
				'create_menu_page' => true,
				'parent_slug'   => '',
				'page_title'    => __( 'Brands', 'yith-woocommerce-brands-add-on' ),
				'menu_title'    => __( 'Brands', 'yith-woocommerce-brands-add-on' ),
				'capability'    => 'manage_options',
				'parent'        => '',
				'parent_page'   => 'yith_plugin_panel',
				'page'          => 'yith_wcbr_panel',
				'admin-tabs'    => $this->available_tabs,
				'options-path'  => YITH_WCBR_DIR . 'plugin-options'
			);

			/* === Fixed: not updated theme  === */
			if( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( YITH_WCBR_DIR . 'plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Print premium tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_premium_tab() {
			include( YITH_WCBR_DIR . 'templates/admin/brand-premium-panel.php' );
		}

		/* === PLUGIN LINK METHODS === */

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri(){
			return $this->premium_landing_url;
		}

		/**
		 * Add plugin action links
		 *
		 * @param mixed $links Plugins links array
		 *
		 * @return array Filtered link array
		 * @since 1.0.0
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, 'yith_wcbr_panel', defined( 'YITH_WCBR_PREMIUM_INIT' ) );
			return $links;
		}

		/**
		 * Adds plugin row meta
		 *
		 * @param $plugin_meta array Array of unfiltered plugin meta
		 * @param $plugin_file string Plugin base file path
		 *
		 * @return array Filtered array of plugin meta
		 * @since 1.0.0
		 */
		public function add_plugin_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCBR_INIT' ){
			if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
				$new_row_meta_args['slug'] = 'yith-woocommerce-brands-add-on';
			}

			if ( defined( 'YITH_WCBR_PREMIUM_INIT' ) ) {
				$new_row_meta_args['is_premium'] = true;

			}

			return $new_row_meta_args;
		}

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCBR_Admin
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
 * Unique access to instance of YITH_WCBR_Admin class
 *
 * @return \YITH_WCBR_Admin
 * @since 1.0.0
 */
function YITH_WCBR_Admin(){
	return YITH_WCBR_Admin::get_instance();
}