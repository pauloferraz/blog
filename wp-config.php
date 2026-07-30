<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_local' );

/** Database username */
define( 'DB_USER', 'wpuser' );

/** Database password */
define( 'DB_PASSWORD', 'wp_local_change_me' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define('AUTH_KEY',         'eqq|=X{niJx{lmvm^q{+WXl}flsrPwLy5y|{fpfLcb7p)dS:>G P>l14S-y^u+V`');
define('SECURE_AUTH_KEY',  '9BeL=g?uRtohsbftF=&H`I73WOLkbgMe6;QdirB;Sv3u.UE?/n!hPg(#i}vIhv!D');
define('LOGGED_IN_KEY',    '|lLIMY{7~#^++*7GZY|ncZ|2vBV~H(wcfL}.|<^kZN]QJ!r(r6![h~NW b9is.? ');
define('NONCE_KEY',        '_ppQ64B*!/4@8#y6+g9j&)fS-CCiWZ/rzIeu&pC9v#ugwyVZ4[YeiWad@>epAUD1');
define('AUTH_SALT',        '- 8NV`l-u| 6*mn`Q<PwV2#k;w_9y|C!}.I]3[}(Fm4-[v;$O~MD560B`;4~bV^/');
define('SECURE_AUTH_SALT', 'F~n)JvISE|~6NAiy@|<tNuJaP:v`m}eu;DTX-b..>GG5}Vb6UJ-r0@S=Y)n6iM8F');
define('LOGGED_IN_SALT',   's@oh{K|[;~nnw@w>T1S;yZ$f*.-]C*Q|?{$ZEbq)?;{YC>u90;)7-!a*$I}B9>6t');
define('NONCE_SALT',       'Liu0U`Sp(BO46hD?2tO33|I=!j6{C._(;A`9.84~P#D +MTk8(<ny4$7b=6WPw.e');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
