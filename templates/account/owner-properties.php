<?php
/**
 * Applicant viewings page within My Account
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/applicant-viewings.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="propertyhive-owner-properties">

	<?php
		if ( !empty($properties) )
		{
			echo '
			<table class="viewings-table upcoming-viewings-table" width="100%">
				<tr>
					<th>&nbsp;</th>
					<th>' . __( 'Address', 'propertyhive' ) . '</th>
					<th>' . __( 'Price', 'propertyhive' ) . '</th>
					<th>' . __( 'Status', 'propertyhive' ) . '</th>
				</tr>
			';
			foreach ($properties as $property)
			{
				$link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . get_permalink( $property->id ) . '">' : '' );
				$link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

				$image = $property->get_main_photo_src();

				echo '<tr>
					<td>' . ( ( $image !== false ) ? $link_prefix . '<img src="' . $image . '" width="75" alt="' . get_the_title( $property->id ) . '">' : '' ) . $link_suffix . '</td>
					<td>' . $link_prefix . get_the_title( $property->id ) . $link_suffix . '</td>
					<td>' . $property->get_formatted_price() . '</td>
					<td>' . $property->availability . '<br>' . ( ( $property->on_market == 'yes' ) ? 'On Market' : 'Not On Market' ) . '</td>
				</tr>';
			}
			echo '</table>';
		}
		else
		{
			'<p class="propertyhive-info">' . _e( 'No properties found', 'propertyhive' ) . '</p>';
		}
	?>

</div>
