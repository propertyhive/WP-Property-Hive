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

$summary_length = apply_filters('propertyhive_search_summary_length', 300);
?>
<div class="summary">

	<?php 
		echo substr(strip_tags($property->post_excerpt), 0, $summary_length);
		if ( strlen(strip_tags($property->post_excerpt)) > $summary_length ) { echo '...'; } 
	?>

</div>