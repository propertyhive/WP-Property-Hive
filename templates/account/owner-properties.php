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
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
			foreach ($properties as $property)
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . esc_url(get_permalink( $property->id )) . '">' : '' );
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$image = $property->get_main_photo_src();

				echo '<tr>
					<td>' . ( ( $image !== false ) ? wp_kses_post( $link_prefix ) . '<img src="' . esc_url($image) . '" width="75" alt="' . esc_attr(get_the_title( $property->id )) . '">' : '' ) . wp_kses_post( $link_suffix ) . '</td>
					<td>' . wp_kses_post( $link_prefix ) . esc_html(get_the_title( $property->id )) . wp_kses_post( $link_suffix ) . '</td>
					<td>' . wp_kses_post( $property->get_formatted_price() ) . '</td>
					<td>' . esc_html($property->availability) . '<br>' . esc_html( ( $property->on_market == 'yes' ) ? 'On Market' : 'Not On Market' ) . '</td>
				</tr>';
			}
			echo '</table>';
		}
		else
		{
			echo '<p class="propertyhive-info">' . esc_html(__( 'No properties found', 'propertyhive' )) . '</p>';
		}
	?>

</div>
