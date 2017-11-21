<?php
/**
 * PropertyHive Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include core functions
include( 'ph-formatting-functions.php' );
include( 'ph-conditional-functions.php' );
include( 'ph-term-functions.php' );
include( 'ph-page-functions.php' );
include( 'ph-property-functions.php' );

/**
 * Get template part (for templates like the single-product).
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 * @return void
 */
function ph_get_template_part( $slug, $name = '' ) {
    $template = '';

    // Look in yourtheme/slug-name.php and yourtheme/propertyhive/slug-name.php
    if ( $name ) {
        $template = locate_template( array( "{$slug}-{$name}.php", PH()->template_path() . "{$slug}-{$name}.php" ) );
    }

    // Get default slug-name.php
    if ( ! $template && $name && file_exists( PH()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
        $template = PH()->plugin_path() . "/templates/{$slug}-{$name}.php";
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/propertyhive/slug.php
    if ( ! $template ) {
        $template = locate_template( array( "{$slug}.php", PH()->template_path() . "{$slug}.php" ) );
    }

    // Allow 3rd party plugin filter template file from their plugin
    $template = apply_filters( 'ph_get_template_part', $template, $slug, $name );

    if ( $template ) {
        load_template( $template, false );
    }
}

/**
 * Get other templates (e.g. property images) passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function ph_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }

    $located = ph_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $located ) ) {
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
        return;
    }

    do_action( 'propertyhive_before_template_part', $template_name, $template_path, $located, $args );

    include( $located );

    do_action( 'propertyhive_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function ph_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = PH()->template_path();
    }

    if ( ! $default_path ) {
        $default_path = PH()->plugin_path() . '/templates/';
    }

    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name
        )
    );

    // Get default template
    if ( ! $template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found
    return apply_filters('propertyhive_locate_template', $template, $template_name, $template_path);
}

/**
 * Set a cookie - wrapper for setcookie using WP constants
 *
 * @param  string  $name   Name of the cookie being set
 * @param  string  $value  Value of the cookie
 * @param  integer $expire Expiry of the cookie
 * @param  string  $secure Whether the cookie should be served only over https
 */
function ph_setcookie( $name, $value, $expire = 0, $secure = false ) {
    if ( ! headers_sent() ) {
        return setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
    } elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        trigger_error( "Cookie cannot be set - headers already sent", E_USER_NOTICE );
    }
    return false;
}

/**
 * Get an image size.
 *
 * @param mixed $image_size
 * @return array
 */
function ph_get_image_size( $image_size ) {
    if ( is_array( $image_size ) ) {
        $width  = isset( $image_size[0] ) ? $image_size[0] : '300';
        $height = isset( $image_size[1] ) ? $image_size[1] : '300';
        $crop   = isset( $image_size[2] ) ? $image_size[2] : 1;

        $size = array(
            'width'  => $width,
            'height' => $height,
            'crop'   => $crop
        );

        $image_size = $width . '_' . $height;
    } else {
        $size = array(
            'width'  => '300',
            'height' => '300',
            'crop'   => 1
        );
    }

    return $size;
}

function get_area_units( )
{
    $size_options = array(
        'sqft' => __( 'Sq Ft', 'propertyhive' ),
        'sqm' => __( 'Sq M', 'propertyhive' ),
        'acre' => __( 'Acres', 'propertyhive' ),
        'hectare' => __( 'Hectares', 'propertyhive' ),
    );

    // $size_options = apply_filters( 'propertyhive_commercial_size_units', $size_options ).
    // Above filter not in use as we need a way to add the conversion rate to sqft also
    // If it's a commonly used unit we could just add it for everyones benefit

    return $size_options;
}

function convert_size_to_sqft( $size, $unit = 'sqft' )
{
    $size_sqft = $size;
    switch ( $unit )
    {
        case "sqm": { $size_sqft = $size * 1; break; }
        case "acre": { $size_sqft = $size * 43560; break; }
        case "hectare": { $size_sqft = $size * 107639; break; }
    }
    return $size_sqft;
}

function get_commercial_price_units( )
{
    $price_options = array(
        'psqft' => __( 'Per Sq Ft', 'propertyhive' ),
        'psqm' => __( 'Per Sq M', 'propertyhive' ),
        'pacre' => __( 'Per Acre', 'propertyhive' ),
        'phectare' => __( 'Per Hectare', 'propertyhive' ),
    );
    
    return $price_options;
}

/**
 * Execute functions hooked on a specific action hook, if it exists,
 * otherwise execute a default hook instead.
 *
 * @param string $tag        The name of the action to be executed (if it exists).
 * @param string $defaultTag The name of the default action to be executed instead.
 */
function ph_do_action_default( $tag, $defaultTag ) {
	if ( has_action( $tag ) ) {
		do_action( $tag );
	} else {
		do_action( $defaultTag );
	}
}