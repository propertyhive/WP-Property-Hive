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
	return is_array( $var ) ? array_map( 'ph_clean', $var ) : sanitize_text_field( $var );
}