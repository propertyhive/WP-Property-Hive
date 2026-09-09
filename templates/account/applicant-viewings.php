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

<div class="propertyhive-applicant-viewings">

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
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
			foreach ($upcoming_viewings as $viewing)
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$property = new PH_Property( (int)$viewing->property_id );

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . esc_url(get_permalink( $viewing->property_id )) . '">' : '' );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$date_prefix = ( ( $viewing->status == 'cancelled' ) ? '<span style="text-decoration:line-through">' : '' );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$date_suffix = ( ( $viewing->status == 'cancelled' ) ? '</span> (' . esc_html(__( 'Cancelled', 'propertyhive' )) . ')' : '' );

				if ( $viewing->status == 'cancelled' && $viewing->cancelled_reason_public == 'yes' && $viewing->cancelled_reason != '' )
				{
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
					$date_suffix .= '<br>' . esc_html(__( 'Reason Cancelled', 'propertyhive' )) . ':<br>' . nl2br(esc_html($viewing->cancelled_reason));
				}

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$image = $property->get_main_photo_src();

				echo '<tr>
					<td>' . ( ( $image !== false ) ? wp_kses_post( $link_prefix ) . '<img src="' . esc_url($image) . '" width="75" alt="' . esc_attr(get_the_title( $viewing->property_id )) . '">' : '' ) . wp_kses_post( $link_suffix ) . '</td>
					<td>' . wp_kses_post( $date_prefix ) . esc_html(gmdate( "H:i jS M Y", strtotime( $viewing->start_date_time ) )) . wp_kses_post( $date_suffix ) . '</td>
					<td>' . wp_kses_post( $link_prefix ) . esc_html(get_the_title( $viewing->property_id )) . wp_kses_post( $link_suffix ) . '<br>' . wp_kses_post( $property->get_formatted_price() ) . '</td>
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
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
			foreach ($past_viewings as $viewing)
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$property = new PH_Property( (int)$viewing->property_id );

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . esc_url(get_permalink( $viewing->property_id )) . '">' : '' );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$date_prefix = ( ( $viewing->status == 'cancelled' ) ? '<span style="text-decoration:line-through">' : '' );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$date_suffix = ( ( $viewing->status == 'cancelled' ) ? '</span> (' . esc_html(__( 'Cancelled', 'propertyhive' )) . ')' : '' );

				if ( $viewing->status == 'cancelled' && $viewing->cancelled_reason_public == 'yes' && $viewing->cancelled_reason != '' )
				{
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
					$date_suffix .= '<br>' . esc_html(__( 'Reason Cancelled', 'propertyhive' )) . ':<br>' . nl2br(esc_html($viewing->cancelled_reason));
				}

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$image = $property->get_main_photo_src();

				echo '<tr>
					<td>' . ( ( $image !== false ) ? wp_kses_post( $link_prefix ) . '<img src="' . esc_url($image) . '" width="75" alt="' . esc_attr(get_the_title( $viewing->property_id )) . '">' : '' ) . wp_kses_post( $link_suffix ) . '</td>
					<td>' . wp_kses_post( $date_prefix ) . esc_html(gmdate( "H:i jS M Y", strtotime( $viewing->start_date_time ) )) . wp_kses_post( $date_suffix ) . '</td>
					<td>' . wp_kses_post( $link_prefix ) . esc_html(get_the_title( $viewing->property_id )) . wp_kses_post( $link_suffix ) . '<br>' . wp_kses_post( $property->get_formatted_price() ) . '</td>
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
