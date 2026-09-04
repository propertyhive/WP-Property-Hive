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
					<th>' . esc_html(__( 'Address', 'propertyhive' )) . '</th>
					<th>' . esc_html(__( 'Price', 'propertyhive' )) . '</th>
					<th>' . esc_html(__( 'Status', 'propertyhive' )) . '</th>
				</tr>
			';
			foreach ( $properties as $property )
			{
			    $property_url = ( 'yes' === $property->on_market )
			        ? get_permalink( $property->id )
			        : '';

			    $image = $property->get_main_photo_src();

			    echo '<tr>';

			    echo '<td>';

			    if ( false !== $image )
			    {
			        if ( $property_url )
			        {
			            echo '<a href="' . esc_url( $property_url ) . '">';
			        }

			        echo '<img src="' . esc_url( $image ) . '" width="75" alt="' . esc_attr( get_the_title( $property->id ) ) . '">';

			        if ( $property_url )
			        {
			            echo '</a>';
			        }
			    }

			    echo '</td>';

			    echo '<td>';

			    if ( $property_url )
			    {
			        echo '<a href="' . esc_url( $property_url ) . '">';
			    }

			    echo esc_html( get_the_title( $property->id ) );

			    if ( $property_url )
			    {
			        echo '</a>';
			    }

			    echo '</td>';

			    echo '<td>' . wp_kses_post( $property->get_formatted_price() ) . '</td>';

			    echo '<td>' .
				    esc_html( $property->availability ) .
				    '<br>' .
				    ( 'yes' === $property->on_market
				        ? esc_html__( 'On Market', 'propertyhive' )
				        : esc_html__( 'Not On Market', 'propertyhive' )
				    ) .
			    '</td>';

			    echo '</tr>';
			}
			echo '</table>';
		}
		else
		{
			echo '<p class="propertyhive-info">' . esc_html(__( 'No properties found', 'propertyhive' )) . '</p>';
		}
	?>

</div>
