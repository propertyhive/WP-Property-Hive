<?php
/**
 * PropertyHive Formatting
 *
 * Functions for formatting data.
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Clean variables using sanitize_text_field.
 * @param string|array $var
 * @return string|array
 */
function ph_clean( $var ) {

	if ( is_array( $var ) ) {
		return array_map( 'ph_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Strip none numeric or comma chars from phone numbers
 * @param string
 * @return string
 */

function ph_clean_telephone_number( $var ) {

	return preg_replace( "/[^0-9,]/", "", $var );
}

/**
 * Format monetary number value with decimal and thousands separators for display in a form field
 * @param string
 * @return string
 */
function ph_display_price_field( $var )
{
	$float_var = (float)$var;

	// If stored value isn't a valid number with decimals, display as it's stored
	if ( $float_var !== floatval(0) )
	{
		// If there are decimals on the number, display them. If not, display none
		$decimals = $float_var == intval($var) ? 0 : 2;

		// Get custom decimal and thousands separators, if set
		$decimal_separator = get_option('propertyhive_price_decimal_separator', '.');
		$thousands_separator = get_option('propertyhive_price_thousand_separator', ',');

		$var = number_format( $float_var, $decimals, $decimal_separator, $thousands_separator );
	}

	return (string)$var;
}

if ( ! function_exists( 'ph_rgb_from_hex' ) ) {

	/**
	 * Hex darker/lighter/contrast functions for colours.
	 *
	 * @param mixed $color
	 * @return string
	 */
	function ph_rgb_from_hex( $color ) {
		$color = str_replace( '#', '', $color );
		// Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF"
		$color = preg_replace( '~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color );

		$rgb      = array();
		$rgb['R'] = hexdec( $color[0] . $color[1] );
		$rgb['G'] = hexdec( $color[2] . $color[3] );
		$rgb['B'] = hexdec( $color[4] . $color[5] );

		return $rgb;
	}
}

if ( ! function_exists( 'ph_hex_darker' ) ) {

	/**
	 * Hex darker/lighter/contrast functions for colours.
	 *
	 * @param mixed $color
	 * @param int $factor (default: 30)
	 * @return string
	 */
	function ph_hex_darker( $color, $factor = 30 ) {
		$base  = ph_rgb_from_hex( $color );
		$color = '#';

		foreach ( $base as $k => $v ) {
			$amount      = $v / 100;
			$amount      = round( $amount * $factor );
			$new_decimal = $v - $amount;

			$new_hex_component = dechex( $new_decimal );
			if ( strlen( $new_hex_component ) < 2 ) {
				$new_hex_component = "0" . $new_hex_component;
			}
			$color .= $new_hex_component;
		}

		return $color;
	}
}

if ( ! function_exists( 'ph_hex_lighter' ) ) {

	/**
	 * Hex darker/lighter/contrast functions for colours.
	 *
	 * @param mixed $color
	 * @param int $factor (default: 30)
	 * @return string
	 */
	function ph_hex_lighter( $color, $factor = 30 ) {
		$base  = ph_rgb_from_hex( $color );
		$color = '#';

		foreach ( $base as $k => $v ) {
			$amount      = 255 - $v;
			$amount      = $amount / 100;
			$amount      = round( $amount * $factor );
			$new_decimal = $v + $amount;

			$new_hex_component = dechex( $new_decimal );
			if ( strlen( $new_hex_component ) < 2 ) {
				$new_hex_component = "0" . $new_hex_component;
			}
			$color .= $new_hex_component;
		}

		return $color;
	}
}

if ( ! function_exists( 'ph_light_or_dark' ) ) {

	/**
	 * Detect if we should use a light or dark colour on a background colour.
	 *
	 * @param mixed $color
	 * @param string $dark (default: '#000000')
	 * @param string $light (default: '#FFFFFF')
	 * @return string
	 */
	function ph_light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {

		$hex = str_replace( '#', '', $color );

		$c_r = hexdec( substr( $hex, 0, 2 ) );
		$c_g = hexdec( substr( $hex, 2, 2 ) );
		$c_b = hexdec( substr( $hex, 4, 2 ) );

		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return $brightness > 155 ? $dark : $light;
	}
}

if ( ! function_exists( 'ph_format_hex' ) ) {

	/**
	 * Format string as hex.
	 *
	 * @param string $hex
	 * @return string
	 */
	function ph_format_hex( $hex ) {

		$hex = trim( str_replace( '#', '', $hex ) );

		if ( strlen( $hex ) == 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return $hex ? '#' . $hex : null;
	}
}

function ph_nl2br($str) 
{
    // Match any <ul> or <ol> with their content
    $pattern = '/(<ul[^>]*>.*?<\/ul>|<ol[^>]*>.*?<\/ol>)/is';
    $parts = preg_split($pattern, $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    
    foreach ($parts as &$part) {
        // If the part is not a list, apply nl2br
        if (!preg_match($pattern, $part)) {
            $part = nl2br($part);
        }
    }
    
    // Reassemble the string
    return implode('', $parts);
}

function ph_split_address_into_fields( $address )
{
	$fields = [
        'address_name_number' => '',
        'address_street' => '',
        'address_two' => '',
        'address_three' => '',
        'address_four' => '',
        'address_postcode' => '',
        'address_country' => ''
    ];

    // Replace newlines with commas and remove consecutive commas
    $address = preg_replace('/\s*,\s*/', ', ', $address);
    $address = preg_replace('/\s*\n\s*/', ', ', $address);
    $address = preg_replace('/,+/', ',', $address);

    // Explode the address by comma
    $parts = explode(',', $address);

    // Trim whitespace from each part
    $parts = array_map('trim', $parts);

    // Remove empty parts
    $parts = array_filter($parts);

    // Check the first part for building number and street
    if (isset($parts[0])) {
        if (preg_match('/^(\d+)\s+(.*)$/', $parts[0], $matches)) {
            $fields['address_name_number'] = $matches[1];
            $fields['address_street'] = $matches[2];
        } else {
            $fields['address_street'] = $parts[0];
        }
        array_shift($parts);
    }

    // Detect postcode in the remaining parts
    foreach ($parts as $index => $part) {
        if (preg_match('/[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2}/i', $part, $postcodeMatch)) {
            $fields['address_postcode'] = $postcodeMatch[0];
            // Split the part containing the postcode
            $remainingPart = preg_replace('/[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2}/i', '', $part);
            if (!empty(trim($remainingPart))) {
                array_splice($parts, $index, 1, trim($remainingPart));
            } else {
                unset($parts[$index]);
            }
            // Check if the next part is the country
            if (isset($parts[$index + 1])) {
                $fields['address_country'] = $parts[$index + 1];
                unset($parts[$index + 1]);
            }
            break;
        }
    }

    // Assign remaining parts to Address Line 2, Town/City, and County
    $remainingParts = array_values($parts);
    if (isset($remainingParts[0])) $fields['address_two'] = $remainingParts[0];
    if (isset($remainingParts[1])) $fields['address_three'] = $remainingParts[1];
    if (isset($remainingParts[2])) $fields['address_four'] = $remainingParts[2];

    return $fields;
}