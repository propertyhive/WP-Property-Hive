<?php
/**
 * PropertyHive Admin Functions
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Admin/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get all PropertyHive screen ids
 *
 * @return array
 */
function ph_get_screen_ids() {
	$ph_screen_id = sanitize_title( __( 'PropertyHive', 'propertyhive' ) );

    return apply_filters( 'propertyhive_screen_ids', array(
        'edit-property',
        'property',
        'edit-contact',
        'contact',
        'edit-enquiry',
        'enquiry',
        'edit-appraisal',
        'appraisal',
        'edit-viewing',
        'viewing',
        'edit-offer',
        'offer',
        'edit-sale',
        'sale',
        'tenancy',
        'edit-key_date',
        'property-hive_page_ph-settings',
        'admin_page_ph-generate-applicant-list',
        'admin_page_ph-merge-duplicate-contacts',
        'property-hive_page_ph-home',
    ) );
}

/**
 * Create a page and store the ID in an option.
 *
 * @access public
 * @param mixed $slug Slug for the new page
 * @param mixed $option Option name to store the page's ID
 * @param string $page_title (default: '') Title for the new page
 * @param string $page_content (default: '') Content for the new page
 * @param int $post_parent (default: 0) Parent for the new page
 * @return int page ID
 */
function ph_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
    global $wpdb;

    /*$option_value = get_option( $option );

    if ( $option_value > 0 && get_post( $option_value ) )
        return -1;

    $page_found = null;

    if ( strlen( $page_content ) > 0 ) {
        // Search for an existing page with the specified page content (typically a shortcode)
        $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
    } else {
        // Search for an existing page with the specified page slug
        $page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
    }

    if ( $page_found ) {
        if ( ! $option_value )
            update_option( $option, $page_found );
		
		return $page_found;
    }

    $page_data = array(
        'post_status'       => 'publish',
        'post_type'         => 'page',
        'post_author'       => 1,
        'post_name'         => $slug,
        'post_title'        => $page_title,
        'post_content'      => $page_content,
        'post_parent'       => $post_parent,
        'comment_status'    => 'closed'
    );
    $page_id = wp_insert_post( $page_data );

    if ( $option )
        update_option( $option, $page_id );

    return $page_id;*/
}

/**
 * Output admin fields.
 *
 * Loops though the PropertyHive options array and outputs each field.
 *
 * @param array $options Opens array to output
 */
function propertyhive_admin_fields( $options ) {
    if ( ! class_exists( 'PH_Admin_Settings' ) )
        include 'class-ph-admin-settings.php';

    PH_Admin_Settings::output_fields( $options );
}

/**
 * Update all settings which are passed.
 *
 * @access public
 * @param array $options
 * @return void
 */
function propertyhive_update_options( $options ) {
    if ( ! class_exists( 'PH_Admin_Settings' ) )
        include 'class-ph-admin-settings.php';

    PH_Admin_Settings::save_fields( $options );
}

/**
 * Get a setting from the settings API.
 *
 * @param mixed $option
 * @return string
 */
function propertyhive_settings_get_option( $option_name, $default = '' ) {
    if ( ! class_exists( 'PH_Admin_Settings' ) )
        include 'class-ph-admin-settings.php';

    return PH_Admin_Settings::get_option( $option_name, $default );
}

/**
 * Generate CSS from the less file when changing colours.
 *
 * @access public
 * @return void
 */
function propertyhive_compile_less_styles() {
    /*global $propertyhive;

    $colors         = array_map( 'esc_attr', (array) get_option( 'propertyhive_frontend_css_colors' ) );
    $base_file      = PH()->plugin_path() . '/assets/css/propertyhive-base.less';
    $less_file      = PH()->plugin_path() . '/assets/css/propertyhive.less';
    $css_file       = PH()->plugin_path() . '/assets/css/propertyhive.css';

    // Write less file
    if ( is_writable( $base_file ) && is_writable( $css_file ) ) {

        // Colours changed - recompile less
        if ( ! class_exists( 'lessc' ) )
            include_once( PH()->plugin_path() . '/includes/libraries/class-lessc.php' );
        if ( ! class_exists( 'cssmin' ) )
            include_once( PH()->plugin_path() . '/includes/libraries/class-cssmin.php' );

        try {
            // Set default if colours not set
            if ( ! $colors['primary'] ) $colors['primary'] = '#ad74a2';
            if ( ! $colors['secondary'] ) $colors['secondary'] = '#f7f6f7';
            if ( ! $colors['highlight'] ) $colors['highlight'] = '#85ad74';
            if ( ! $colors['content_bg'] ) $colors['content_bg'] = '#ffffff';
            if ( ! $colors['subtext'] ) $colors['subtext'] = '#777777';

            // Write new color to base file
            $color_rules = "
@primary:       " . $colors['primary'] . ";
@primarytext:   " . ph_light_or_dark( $colors['primary'], 'desaturate(darken(@primary,50%),18%)', 'desaturate(lighten(@primary,50%),18%)' ) . ";

@secondary:     " . $colors['secondary'] . ";
@secondarytext: " . ph_light_or_dark( $colors['secondary'], 'desaturate(darken(@secondary,60%),18%)', 'desaturate(lighten(@secondary,60%),18%)' ) . ";

@highlight:     " . $colors['highlight'] . ";
@highlightext:  " . ph_light_or_dark( $colors['highlight'], 'desaturate(darken(@highlight,60%),18%)', 'desaturate(lighten(@highlight,60%),18%)' ) . ";

@contentbg:     " . $colors['content_bg'] . ";

@subtext:       " . $colors['subtext'] . ";
            ";

            file_put_contents( $base_file, $color_rules );

            $less         = new lessc;
            $compiled_css = $less->compileFile( $less_file );
            $compiled_css = CssMin::minify( $compiled_css );

            if ( $compiled_css )
                file_put_contents( $css_file, $compiled_css );

        } catch ( exception $ex ) {
            wp_die( __( 'Could not compile propertyhive.less:', 'propertyhive' ) . ' ' . $ex->getMessage() );
        }
    }*/
}

function propertyhive_is_location_in_address( $property, $location )
{
    $address_keywords = array( $location );
    if ( strpos( $location, ' ' ) !== FALSE )
    {
        $address_keywords[] = str_replace(" ", "-", ph_clean($location));
    }
    if ( strpos( $location, '-' ) !== FALSE )
    {
        $address_keywords[] = str_replace("-", " ", ph_clean($location));
    }

    if ( strpos( $location, '.' ) !== FALSE )
    {
        $address_keywords[] = str_replace(".", "", ph_clean($location));
    }
    if ( stripos( $location, 'st ' ) !== FALSE )
    {
        $address_keywords[] = str_ireplace("st ", "st. ", ph_clean($location));
    }

    $location_address_fields = array(
        '_address_street',
        '_address_two',
        '_address_three',
        '_address_four',
        '_address_postcode'
    );
    $location_address_fields = apply_filters( 'propertyhive_address_fields_to_query', $location_address_fields );

    $address_keyword_compare = get_option( 'propertyhive_address_keyword_compare', '=' );
    if ( $address_keyword_compare == 'polygon' )
    {
        $address_keyword_compare = apply_filters('propertyhive_property_match_address_keyword_compare', '=');
    }

    foreach ( $address_keywords as $address_keyword )
    {
        foreach ( $location_address_fields as $address_field )
        {
            if ( $address_field == '_address_postcode' ) { continue; } // ignore postcode as that is handled differently afterwards

            if (
                ( $address_keyword_compare == '=' && strcasecmp($address_keyword, $property->{$address_field}) == 0 )
                ||
                ( $address_keyword_compare == 'LIKE' && stripos($property->{$address_field}, $address_keyword) !== false )
            )
            {
                return true;
            }
        }
    }
    if ( in_array('_address_postcode', $location_address_fields) )
    {
        if ( strlen($location) <= 4 )
        {
            // if location is just the first part of postcode, check if it is present at the start of the postcode
            if ( strcasecmp($address_keyword, substr($property->_address_postcode, 0, strlen($address_keyword))) == 0 )
            {
                return true;
            }
        }
        else
        {
            $postcode = ph_clean($location);
            $postcodes = array(strtolower($postcode));

            if ( preg_match('#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW])[0-9][ABD-HJLNP-UW-Z]{2})$#i', $postcode) )
            {
                // UK postcode found with no space

                if ( strlen($postcode) == 5 )
                {
                    $first_part = substr($postcode, 0, 2);
                    $last_part = substr($postcode, 2, 3);

                    $postcodes[] = strtolower($first_part . ' ' . $last_part);
                }
                elseif ( strlen($postcode) == 6 )
                {
                    $first_part = substr($postcode, 0, 3);
                    $last_part = substr($postcode, 3, 3);

                    $postcodes[] = strtolower($first_part . ' ' . $last_part);
                }
                elseif ( strlen($postcode) == 7 )
                {
                    $first_part = substr($postcode, 0, 4);
                    $last_part = substr($postcode, 4, 3);

                    $postcodes[] = strtolower($first_part . ' ' . $last_part);
                }
            }

            if ( in_array(strtolower($property->_address_postcode), $postcodes) )
            {
                return true;
            }
        }
    }

    return false;
}

function propertyhive_human_time_difference($timestamp)
{
	$now = time() ;

	if ( ! $timestamp )
	{
		return __( 'never', 'propertyhive' );
	}
	if ( $now - $timestamp > 0 )
	{
		return sprintf( __( '%s ago', 'propertyhive' ), human_time_diff( $timestamp, $now ) );
	}
	else if ( $now - $timestamp < 0 )
	{
		return sprintf( __( 'in %s', 'propertyhive' ), human_time_diff( $timestamp, $now ) );
	}
	else
	{
		return __( 'now', 'propertyhive' );
	}
}
