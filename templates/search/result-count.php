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

global $propertyhive, $wp_query;

?>
<p class="propertyhive-result-count">
	<?php
	$paged    = max( 1, $wp_query->get( 'paged' ) );
	$per_page = $wp_query->get( 'posts_per_page' );
	$total    = $wp_query->found_posts;
	$first    = ( $per_page * $paged ) - $per_page + 1;
	$last     = min( $total, $wp_query->get( 'posts_per_page' ) * $paged );

	if ( 1 == $total ) {
		_e( 'Showing the single result', 'propertyhive' );
	} elseif ( $total <= $per_page || -1 == $per_page ) {
		printf( __( 'Showing %d properties', 'propertyhive' ), $total );
	} else {
		printf( _x( 'Showing %1$d–%2$d of %3$d properties', '%1$d = first, %2$d = last, %3$d = total', 'propertyhive' ), $first, $last, $total );
	}
	?>
</p>