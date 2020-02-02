<?php
/**
 * File that define P24_Multi_Currency_Menu class.
 *
 * @package Przelewy24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class P24_Multi_Currency_Menu
 *
 * Update the menu section on admin panel.
 */
class P24_Multi_Currency_Menu {

	/**
	 * The P24_Core instance.
	 *
	 * @var P24_Core
	 */
	private $plugin_core;

	/**
	 * The constructor.
	 *
	 * @param P24_Core $plugin_core The P24_Core instance.
	 */
	public function __construct( P24_Core $plugin_core ) {
		$this->plugin_core = $plugin_core;
	}

	/**
	 * Generate form to select links to change currency in user mode.
	 */
	public function nav_menu_link() {
		$params = [
			'currency_options' => get_przelewy24_multi_currency_options(),
		];
		$this->plugin_core->render_template( 'currency-menu-generator', $params );
	}

	/**
	 * Add box on admin panel to select links.
	 */
	public function add_menu_box() {
		if ( $this->plugin_core->is_multi_currency_active() ) {
			add_meta_box(
				'p24_menu_box',
				__( 'Moduł multi currency', 'przelewy24' ),
				[ $this, 'nav_menu_link' ],
				'nav-menus',
				'side',
				'low'
			);
		}
	}

	/**
	 * Check if selected post should be show in menu.
	 *
	 * @param WP_Post $item The element to show in menu.
	 *
	 * @return bool
	 */
	private function check_drop_inactive_currency( WP_Post $item ) {
		if ( property_exists( $item, 'url' ) ) {
			$url = $item->{'url'};
			$rx  = '/^\\?p24\\_change\\_currency\\=(.+)$/';
			if ( preg_match( $rx, $url, $m ) ) {
				$currency = $m[1];
				$mc       = $this->plugin_core->get_multi_currency_instance();
				if ( $currency === $mc->get_active_currency() ) {
					return true;
				} elseif ( ! in_array( $currency, $mc->get_available_currencies(), true ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Filter user menu items.
	 *
	 * @param array $items Sorted list of items in menu.
	 *
	 * @return array
	 */
	public function filter_user_menu( $items ) {
		$ret = [];
		foreach ( $items as $item ) {
			$classes = $item->classes;
			if ( in_array( 'p24-change-currency', $classes, true ) ) {
				if ( ! $this->plugin_core->is_multi_currency_active() ) {
					/* If there is no multi currency, drop all links. */
					continue;
				}
				if ( $this->check_drop_inactive_currency( $item ) ) {
					continue;
				}
			}
			$ret[] = $item;
		}
		return $ret;
	}

	/**
	 * Bind events.
	 */
	public function bind_events() {
		add_action( 'admin_init', [ $this, 'add_menu_box' ] );
		add_filter( 'wp_nav_menu_objects', [ $this, 'filter_user_menu' ] );
	}

}
