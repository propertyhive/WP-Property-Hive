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
 * Get template part (for templates like the single-property).
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

function ph_get_custom_departments( $active_only = true )
{
    $return = array();

    $custom_departments = get_option( 'propertyhive_custom_departments', array() );
            
    if ( is_array($custom_departments) && !empty($custom_departments) )
    {
        foreach ( $custom_departments as $key => $custom_department )
        {
            if ( !$active_only || ( $active_only && get_option('propertyhive_active_departments_' . $key) == 'yes' ) )
            {
                $return[$key] = $custom_department;
            }
        }
    }

    return $return;
}

function ph_get_custom_department_based_on( $department )
{
    $custom_departments = get_option( 'propertyhive_custom_departments', array() );
            
    if ( isset($custom_departments[$department]['based_on']) )
    {
        return $custom_departments[$department]['based_on'];
    }

    return false;
}

function ph_get_departments( $raw = false )
{
    $departments = array(
        'residential-sales' => __( 'Residential Sales', 'propertyhive' ),
        'residential-lettings' => __( 'Residential Lettings', 'propertyhive' ),
        'commercial' => __( 'Commercial', 'propertyhive' ),
    );

    return $raw ? $departments : apply_filters( 'propertyhive_departments', $departments );
}

function ph_get_viewing_statuses()
{
    $viewing_statuses = array(
        'pending'                => __( 'Pending', 'propertyhive' ),
        'confirmed'              => '- ' . __( 'Confirmed', 'propertyhive' ),
        'unconfirmed'            => '- ' . __( 'Awaiting Confirmation', 'propertyhive' ),
        'carried_out'            => __( 'Carried Out', 'propertyhive' ),
        'feedback_passed_on'     => '- ' . __( 'Feedback Passed On', 'propertyhive' ),
        'feedback_not_passed_on' => '- ' . __( 'Feedback Not Passed On', 'propertyhive' ),
        'cancelled'              => __( 'Cancelled', 'propertyhive' ),
    );

    return $viewing_statuses;
}

function add_viewing_status_meta_query( $meta_query, $selected_status )
{
    switch ( $selected_status )
    {
        case "confirmed":
        {
            $meta_query[] = array(
                'key' => '_status',
                'value' => 'pending',
            );
            $meta_query[] = array(
                'key' => '_all_confirmed',
                'value' => 'yes',
            );
            break;
        }
        case "unconfirmed":
        {
            $meta_query[] = array(
                'key' => '_status',
                'value' => 'pending',
            );
            $meta_query[] = array(
                'key' => '_all_confirmed',
                'value' => '',
            );
            break;
        }
        case "feedback_passed_on":
        {
            $meta_query[] = array(
                'key' => '_status',
                'value' => 'carried_out',
            );
            $meta_query[] = array(
                'key' => '_feedback_status',
                'value' => array('interested', 'not_interested'),
                'compare' => 'IN'
            );
            $meta_query[] = array(
                'key' => '_feedback_passed_on',
                'value' => 'yes',
            );
            break;
        }
        case "feedback_not_passed_on":
        {
            $meta_query[] = array(
                'key' => '_status',
                'value' => 'carried_out',
            );
            $meta_query[] = array(
                'key' => '_feedback_status',
                'value' => array('interested', 'not_interested'),
                'compare' => 'IN'
            );
            $meta_query[] = array(
                'key' => '_feedback_passed_on',
                'value' => '',
            );
            break;
        }
        default:
        {
            $meta_query[] = array(
                'key' => '_status',
                'value' => sanitize_text_field( $selected_status ),
            );
        }
    }
    return $meta_query;
}

function get_area_units()
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

    if ( $size_sqft != '' )
    {
        switch ( $unit )
        {
            case "sqm": { $size_sqft = $size * 10.7639; break; }
            case "acre": { $size_sqft = $size * 43560; break; }
            case "hectare": { $size_sqft = $size * 107639; break; }
        }
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
 * Get ordinal suffix (st, nd, rd etc) for any number
 *
 * @param int $number
 * @param bool $return_number Include $n in the string returned
 * @return string $number including its ordinal suffix
 */
function ph_ordinal_suffix( $number, $return_words = true, $return_number = true )
{
    $n_last = $number % 100;
    if ( ($n_last > 10 && $n_last << 14) || $number == 0 )
    {
        $suffix = 'th';
        $number_in_words = substr($number, -1) . 'th';
    }
    else
    {
        switch( substr($number, -1) )
        {
            case '1':
                $suffix = 'st';
                $number_in_words = 'First';
                break;
            case '2':
                $suffix = 'nd';
                $number_in_words = 'Second';
                break;
            case '3':
                $suffix = 'rd';
                $number_in_words = 'Third';
                break;
            case '4':
                $suffix = 'th';
                $number_in_words = 'Fourth';
                break;
            case '5':
                $suffix = 'th';
                $number_in_words = 'Fifth';
                break;
            default:
                $suffix = 'th';
                $number_in_words = substr($number, -1) . 'th';
                break;
        }
    }
    return $return_words ? $number_in_words : ( $return_number ? $number . $suffix : $suffix );
}