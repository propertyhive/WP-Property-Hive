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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'eoin' );

/** MySQL database username */
define( 'DB_USER', 'eoin' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wpjEOk0e8sJskKzO' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

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
define('AUTH_KEY',         'x[A}!$*-[$0}2!`$7P~ky9vHoI!W#!$yT~|:LQLDT;Ff?x^f0 IgRL_Q!ILSf|~h');
define('SECURE_AUTH_KEY',  'Q0m.!?X2#pdW5o^a*Kik&z[T7F<pK4-XQl_;U(z+Lr&5UjxABnkwF$T|j[4-h<|n');
define('LOGGED_IN_KEY',    'l9Veles+F%fM|R(>U8M&+*d|:F!sp48R_-[Ez&PlhrCSxOgQ!-,OI-j8N6z2}>.(');
define('NONCE_KEY',        '-Czbu;8&zdfREP^{]!fh+a+*t)7e{b_4Fxd6]@#v,-@NC$nua1ekDTz.~d&rk6TR');
define('AUTH_SALT',        'QEV5z7{aw[1[(tsKCd=N;0_+1~QE0:*)sYZ<+LZ/(B_iENZ(te/MK|/T&|eiHflf');
define('SECURE_AUTH_SALT', 'mmv2Uw/N]D6^B ?rOiFy*+W<6|!Lp^F=R]i-3un<QgMXI;9*FatS6SmP>}RH<-;(');
define('LOGGED_IN_SALT',   'j5bd4en0a7_]k(#MaUJEmU@)Y=SR+GGp-xT%5:$!1eL8uK YOE-)~cH-<`VK#>-x');
define('NONCE_SALT',       'rE2[2+LGpK&w=+7HYKdIc$;HTOo)JIO*Yy=iM*PV uDI/`SQ5+|q+ 68X2#LKMF2');

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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
