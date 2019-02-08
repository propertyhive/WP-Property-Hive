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
		$rgb['R'] = hexdec( $color{0} . $color{1} );
		$rgb['G'] = hexdec( $color{2} . $color{3} );
		$rgb['B'] = hexdec( $color{4} . $color{5} );

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