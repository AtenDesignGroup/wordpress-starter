<?php
/**
 * This is a sample wp-config.php file for DDEV.
 * DDEV manages their own wp-config-ddev.php file and will overwrite any changes made to that file.
 *
 * @package ddevapp
 */

if ( getenv( 'true' === 'IS_DDEV_PROJECT' ) ) {
	/** The name of the database for WordPress */
	defined( 'DB_NAME' ) || define( 'DB_NAME', 'db' );

	/** MySQL database username */
	defined( 'DB_USER' ) || define( 'DB_USER', 'db' );

	/** MySQL database password */
	defined( 'DB_PASSWORD' ) || define( 'DB_PASSWORD', 'db' );

	/** MySQL hostname */
	defined( 'DB_HOST' ) || define( 'DB_HOST', 'ddev-wordpress-starter-db' );

	/** WP_HOME URL */
	defined( 'WP_HOME' ) || define( 'WP_HOME', 'https://wordpress-starter.ddev.site' );

	/** WP_SITEURL location */
	defined( 'WP_SITEURL' ) || define( 'WP_SITEURL', WP_HOME . '/' );

	/** Enable debug */
	defined( 'WP_DEBUG' ) || define( 'WP_DEBUG', true );

	/** WordPress environment type. */
	defined( 'WP_ENVIRONMENT_TYPE' ) || define( 'WP_ENVIRONMENT_TYPE', 'local' );

	/**
	 * Set WordPress Database Table prefix if not already set.
	 *
	 * @global string $table_prefix
	 */
	if ( ! isset( $table_prefix ) || empty( $table_prefix ) ) {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$table_prefix = 'wp_';
		// phpcs:enable
	}
}
