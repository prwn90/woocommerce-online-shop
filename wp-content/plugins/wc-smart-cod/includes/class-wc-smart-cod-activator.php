<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/stratos-vetsos-08262473/
 * @since      1.0.0
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/includes
 * @author     FullStack <vetsos.s@gmail.com>
 */
class Wc_Smart_Cod_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function activate() {
		set_transient( 'wc-smart-cod-activated', true, 30 );
	}

}
