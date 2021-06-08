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
		printf( __( 'Showing %s properties', 'propertyhive' ), number_format($total) );
	} else {
		printf( _x( 'Showing %1$s–%2$s of %3$s properties', '%1$s = first, %2$s = last, %3$s = total', 'propertyhive' ), number_format($first), number_format($last), number_format($total) );
	}
	?>
</p>