<?php

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the

 * installation. You don't have to use the web site, you can

 * copy this file to "wp-config.php" and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * MySQL settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://codex.wordpress.org/Editing_wp-config.php

 *

 * @package WordPress

 */


// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', '' );


/** MySQL database username */

define( 'DB_USER', '' );


/** MySQL database password */

define( 'DB_PASSWORD', '' );


/** MySQL hostname */

define( 'DB_HOST', '' );


/** Database Charset to use in creating database tables. */

define( 'DB_CHARSET', 'utf8' );


/** The Database Collate type. Don't change this if in doubt. */

define( 'DB_COLLATE', '' );


/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define('AUTH_KEY',         '050b0ad6acbcbae3d5b371d7be43730bc46434ac5affa844e23df8ad72a83c4d');

define('SECURE_AUTH_KEY',  '3c4d0640441c93fc237a7309caa4bd3a6920a00dbf3cfa5090ea54e41361e3fa');

define('LOGGED_IN_KEY',    '0ced071057f46a45e5b68670135f5ede3ab3104fff6ab0fd27f5cf4230c181ca');

define('NONCE_KEY',        '5878c871d43a1392b5a259559e95ab75498d7dd6682a689e6f75067f9e6ab008');

define('AUTH_SALT',        '4e7c75d6de4d0e6c13297936109f84289252fdafbcd4cb0231e76f2aee22a031');

define('SECURE_AUTH_SALT', 'ff4dd05bc2fe1792b9e16d8cb192a7e28331d23faace3a6b1f60686d80e61e91');

define('LOGGED_IN_SALT',   'e85343898f9c34e86c79b7be138be1be013e2d7e090684210982aee2ec342276');

define('NONCE_SALT',       'e9d45280fa60f68e6ade9710f53bd3863df979fbb004671b00396794c52be178');


/**#@-*/


/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix = 'wp_';


/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 *

 * For information on other constants that can be used for debugging,

 * visit the Codex.

 *

 * @link https://codex.wordpress.org/Debugging_in_WordPress

 */

define( 'WP_DEBUG', false );


/* That's all, stop editing! Happy publishing. */

/**

 * The WP_SITEURL and WP_HOME options are configured to access from any hostname or IP address.

 * If you want to access only from an specific domain, you can modify them. For example:

 *  define('WP_HOME','http://example.com');

 *  define('WP_SITEURL','http://example.com');

 *

*/


if ( defined( 'WP_CLI' ) ) {

    $_SERVER['HTTP_HOST'] = 'localhost';

}


define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST'] . '/wordpress');

define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST'] . '/wordpress');



/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

}


/** Sets up WordPress vars and included files. */

require_once( ABSPATH . 'wp-settings.php' );


define('WP_TEMP_DIR', 'C:\Bitnami\wordpress-5.3.2-2/apps/wordpress/tmp');



//  Disable pingback.ping xmlrpc method to prevent Wordpress from participating in DDoS attacks

//  More info at: https://docs.bitnami.com/general/apps/wordpress/troubleshooting/xmlrpc-and-pingback/


if ( !defined( 'WP_CLI' ) ) {

    // remove x-pingback HTTP header

    add_filter('wp_headers', function($headers) {

        unset($headers['X-Pingback']);

        return $headers;

    });

    // disable pingbacks

    add_filter( 'xmlrpc_methods', function( $methods ) {

            unset( $methods['pingback.ping'] );

            return $methods;

    });

    add_filter( 'auto_update_translation', '__return_false' );

}

