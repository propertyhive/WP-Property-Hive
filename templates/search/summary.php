<?php
/**
 * Loop Summary
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
$summary_length = apply_filters('propertyhive_search_summary_length', 300);
?>
<div class="summary">

	<?php 
		echo esc_html( substr( wp_strip_all_tags( $property->post_excerpt ), 0, $summary_length ) );
		if ( strlen(wp_strip_all_tags($property->post_excerpt)) > $summary_length ) { echo '...'; }
	?>

</div>