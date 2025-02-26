<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

/**
 * Local configuration information.
 *
 * If you are working in a local/desktop development environment and want to
 * keep your config separate, we recommend using a 'wp-config-local.php' file,
 * which you should also make sure you .gitignore.
 */
if ( file_exists( __DIR__ . '/wp-config-local.php' ) ) {
	// IMPORTANT: ensure your local config does not include wp-settings.php.
	require_once __DIR__ . '/wp-config-local.php';
} elseif ( file_exists( __DIR__ . '/wp-config-ddev.php' ) && getenv( 'IS_DDEV_PROJECT' ) === 'true' ) {
	// IMPORTANT: ensure your local config does not include wp-settings.php.
	$ddev_settings = __DIR__ . '/wp-config-ddev.php';
	if ( is_readable( $ddev_settings ) && ! defined( 'DB_USER' ) ) {
		require_once $ddev_settings;
	}

	/**
	 * This block will be executed if you are NOT running on Pantheon and have NO
	 * wp-config-local.php. Insert alternate config here if necessary.
	 *
	 * If you are only running on Pantheon, you can ignore this block.
	 */
} else {
	define( 'DB_NAME', 'database_name' );
	define( 'DB_USER', 'database_username' );
	define( 'DB_PASSWORD', 'database_password' );
	define( 'DB_HOST', 'database_host' );
	define( 'DB_CHARSET', 'utf8' );
	define( 'DB_COLLATE', '' );
	define( 'AUTH_KEY', 'put your unique phrase here' );
	define( 'SECURE_AUTH_KEY', 'put your unique phrase here' );
	define( 'LOGGED_IN_KEY', 'put your unique phrase here' );
	define( 'NONCE_KEY', 'put your unique phrase here' );
	define( 'AUTH_SALT', 'put your unique phrase here' );
	define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
	define( 'LOGGED_IN_SALT', 'put your unique phrase here' );
	define( 'NONCE_SALT', 'put your unique phrase here' );
}
/**#@-*/

/**
 * WordPress database table prefix.
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
 * You may want to examine $_ENV['PANTHEON_ENVIRONMENT'] to set this to be
 * "true" in dev, but false in test and live.
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
