<?php
/**
 * Show options for ordering
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $propertyhive;

?>
<form class="propertyhive-ordering" method="get">
	<?php
		if (
			( !in_array( $department, array('', 'commercial') ) && ph_get_custom_department_based_on($department) != 'commercial' ) ||
			( $department == '' && get_option( 'propertyhive_primary_department' ) != 'commercial' && ph_get_custom_department_based_on(get_option( 'propertyhive_primary_department' )) != 'commercial' )
		)
		{
			$results_orderby = apply_filters( 'propertyhive_results_orderby', array(
				'price-desc' => __( 'Default sorting', 'propertyhive' ),
				'date'       => __( 'Sort by date added', 'propertyhive' ),
				'price-asc'      => __( 'Sort by price: low to high', 'propertyhive' ),
				'price-desc' => __( 'Sort by price: high to low', 'propertyhive' ),
			) );
		}
		else
		{
			$results_orderby = apply_filters( 'propertyhive_results_orderby', array(
				'floor_area-desc' => __( 'Default sorting', 'propertyhive' ),
				'date'       => __( 'Sort by date added', 'propertyhive' ),
				'floor_area-asc'      => __( 'Sort by floor area: low to high', 'propertyhive' ),
				'floor_area-desc' => __( 'Sort by floor area: high to low', 'propertyhive' ),
				'price-asc'      => __( 'Sort by price: low to high', 'propertyhive' ),
				'price-desc' => __( 'Sort by price: high to low', 'propertyhive' ),
			) );
		}

        if ( !empty($results_orderby) )
        {
    ?>
    <select name="orderby" class="orderby">
    <?php
		foreach ( $results_orderby as $id => $name )
		{
			echo '<option value="' . esc_attr( $id ) . '" ' . selected( $orderby, $id, false ) . '>' . esc_attr( $name ) . '</option>';
		}
	?>
	</select>
	<?php
		}
		
		// Keep query string vars intact
		foreach ( $_GET as $key => $val ) {
			if ( 'orderby' === $key || 'submit' === $key )
				continue;
			
			if ( is_array( $val ) ) {
				foreach( $val as $innerVal ) {
					echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $innerVal ) . '" />';
				}
			
			} else {
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
			}
		}
	?>
</form>
