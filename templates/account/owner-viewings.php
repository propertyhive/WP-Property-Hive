<?php
/**
 * Owner viewings page within My Account
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/owner-viewings.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="propertyhive-owner-viewings">

	<h4>Upcoming Viewings</h4>

	<?php
		if ( !empty($upcoming_viewings) )
		{
			echo '
			<table class="viewings-table upcoming-viewings-table" width="100%">
				<tr>
					<th>&nbsp;</th>
					<th>' . esc_html(__( 'Viewing Date/Time', 'propertyhive' )) . '</th>
					<th>' . esc_html(__( 'Property', 'propertyhive' )) . '</th>
				</tr>
			';
			foreach ($upcoming_viewings as $viewing)
			{
				$property = new PH_Property( (int)$viewing->property_id );

				$link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . esc_url(get_permalink( $viewing->property_id )) . '">' : '' );
				$link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

				$date_prefix = ( ( $viewing->status == 'cancelled' ) ? '<span style="text-decoration:line-through">' : '' );
				$date_suffix = ( ( $viewing->status == 'cancelled' ) ? '</span> (' . esc_html(__( 'Cancelled', 'propertyhive' )) . ')' : '' );

				$image = $property->get_main_photo_src();

				echo '<tr>
					<td>' . ( ( $image !== false ) ? $link_prefix . '<img src="' . esc_url($image) . '" width="75" alt="' . esc_attr(get_the_title( $viewing->property_id )) . '">' : '' ) . $link_suffix . '</td>
					<td>' . $date_prefix . esc_html(date( "H:i jS M Y", strtotime( $viewing->start_date_time ) )) . $date_suffix . '</td>
					<td>' . $link_prefix . esc_html(get_the_title( $viewing->property_id )) . $link_suffix . '<br>' . $property->get_formatted_price() . '</td>
				</tr>';
			}
			echo '</table>';
		}
		else
		{
			echo '<p class="propertyhive-info">' . esc_html(__( 'No upcoming viewings scheduled', 'propertyhive' )) . '</p>';
		}
	?>

	<h4>Past Viewings</h4>

	<?php
		if ( !empty($past_viewings) )
		{
			echo '
			<table class="viewings-table upcoming-viewings-table" width="100%">
				<tr>
					<th>&nbsp;</th>
					<th>' . esc_html(__( 'Viewing Date/Time', 'propertyhive' )) . '</th>
					<th>' . esc_html(__( 'Property', 'propertyhive' )) . '</th>
				</tr>
			';
			foreach ($past_viewings as $viewing)
			{
				$property = new PH_Property( (int)$viewing->property_id );

				$link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . esc_url(get_permalink( $viewing->property_id )) . '">' : '' );
				$link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

				$date_prefix = ( ( $viewing->status == 'cancelled' ) ? '<span style="text-decoration:line-through">' : '' );
				$date_suffix = ( ( $viewing->status == 'cancelled' ) ? '</span> (' . esc_html(__( 'Cancelled', 'propertyhive' )) . ')' : '' );

				$image = $property->get_main_photo_src();

				echo '<tr>
					<td>' . ( ( $image !== false ) ? $link_prefix . '<img src="' . esc_url($image) . '" width="75" alt="' . esc_attr(get_the_title( $viewing->property_id )) . '">' : '' ) . $link_suffix . '</td>
					<td>' . $date_prefix . esc_html(date( "H:i jS M Y", strtotime( $viewing->start_date_time ) )) . $date_suffix . '</td>
					<td>' . $link_prefix . esc_html(get_the_title( $viewing->property_id )) . $link_suffix . '<br>' . $property->get_formatted_price() . '</td>
				</tr>';
			}
			echo '</table>';
		}
		else
		{
			echo '<p class="propertyhive-info">' . esc_html(__( 'No past viewings', 'propertyhive' )) . '</p>';
		}
	?>

</div>
