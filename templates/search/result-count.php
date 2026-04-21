<?php
/**
 * Result Count
 *
 * Shows text: Showing x - x of x results
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $propertyhive;

?>
<p class="propertyhive-result-count">
	<?php
	if ( 1 == $total ) {
		_e( 'Showing the single result', 'propertyhive' );
	} elseif ( $total <= $per_page || -1 == $per_page ) {
		printf( 
			/* translators: %s: total number of properties */
			__( 'Showing %s properties', 'propertyhive' ), 
			number_format_i18n($total) 
		);
	} else {
		printf( 
			/* translators: 1: first result number, 2: last result number, 3: total number of properties */
			__( 'Showing %1$s–%2$s of %3$s properties', 'propertyhive' ), 
			number_format_i18n($first), 
			number_format_i18n($last), 
			number_format_i18n($total) 
		);
	}
	?>
</p>