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

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'database' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'QC;}vgFRl@3,idO^oc[,fII2)&kC5q,CBw2UeJAT7U>([2r1FzkK:DcB]9rpNzip' );
define( 'SECURE_AUTH_KEY',  '4r&npPGZkV}o`ve$k5eJHiT8m7$+6a0V>odXz9<Wg%}H8#pFC?aem}y;`%W$Bx5U' );
define( 'LOGGED_IN_KEY',    'IUn(>}h_r$3ZL::;B+*2(f3S&q@jj2PF8(uU|8;CT!DE2h|WTGF0he5g`m:<?IKh' );
define( 'NONCE_KEY',        'YdPkV2c&o$<5K(odvrx0dX:()`8ZKNk$~;&q+TV:@1?mu(?V^^0G?2@q*C-exl:a' );
define( 'AUTH_SALT',        'f3G;.Ejq dm1D+.X Sapc~vQDz5|E[-Enj(m^TT-tXp07uQcES47h1+*s;#dn!dI' );
define( 'SECURE_AUTH_SALT', 'Fg>-h[dP>OJPv&!l9Aw^W/wBK<2:[lc`+lBDEAa@[)|Y9+,4NAG_InC/oI6,=>i(' );
define( 'LOGGED_IN_SALT',   'Cug}U(4fgl[$21UieL)Q6}fPIV$;!Dii8sL,cN9G }#V)};?9m:>K@/ QQ:cZBR2' );
define( 'NONCE_SALT',       '^cc3;?{<XM4-fakh)_n#(I_LwwMZG&T|J5UTIxkC;p~t-!=3 xmtLPTk]46f]~gt' );

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
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
