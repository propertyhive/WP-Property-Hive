<?php
/**
 * Honeycomb functions.php
 *
 * @package honeycomb
 */

/**
 * Assign the Honeycomb version to a var
 */
$theme              = wp_get_theme( 'honeycomb' );
$honeycomb_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$honeycomb = (object) array(
	'version' => $honeycomb_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-honeycomb.php',
	'customizer' => require 'inc/customizer/class-honeycomb-customizer.php',
);

require 'inc/honeycomb-functions.php';
require 'inc/honeycomb-template-hooks.php';
require 'inc/honeycomb-template-functions.php';

if ( class_exists( 'Jetpack' ) ) {
	$honeycomb->jetpack = require 'inc/jetpack/class-honeycomb-jetpack.php';
}

if ( honeycomb_is_propertyhive_activated() ) {
	$honeycomb->propertyhive = require 'inc/propertyhive/class-honeycomb-propertyhive.php';

	require 'inc/propertyhive/honeycomb-propertyhive-template-hooks.php';
	require 'inc/propertyhive/honeycomb-propertyhive-template-functions.php';
}

if ( is_admin() ) {
	$honeycomb->admin = require 'inc/admin/class-honeycomb-admin.php';
}

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/inc/honeycomb-update.php' ) ) {
    include_once( dirname( __FILE__ ) . '/inc/honeycomb-update.php' );
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin or child theme so that your customizations aren't lost during updates.
 */
