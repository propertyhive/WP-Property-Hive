<?php


class PH_Shortcode_Property_Office_Details extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_office_details", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

		global $property;

		$atts = shortcode_atts( array(
			'address_separator' => '<br>',
			'hyperlink_telephone_number' => true,
			'hyperlink_email_address' => true,
		), $atts, 'property_office_details' );

		$atts['hyperlink_telephone_number'] = (($atts['hyperlink_telephone_number'] === 'true' || $atts['hyperlink_telephone_number'] === true) ? true : false);
		$atts['hyperlink_email_address'] = (($atts['hyperlink_email_address'] === 'true' || $atts['hyperlink_email_address'] === true) ? true : false);

		ob_start();

		if ( $property->office_id != '' )
		{
			echo '<div class="property-office-details">';

				if ( $property->office_name != '' )
				{
					echo '<div class="office-name">' . $property->office_name . '</div>';
				}

				if ( $property->get_office_address( $atts['address_separator'] ) != '' )
				{
					echo '<div class="office-address">' . $property->get_office_address( $atts['address_separator'] ) . '</div>';
				}

				if ( $property->office_telephone_number != '' )
				{
					echo '<div class="office-telephone-number">' . ( ($atts['hyperlink_telephone_number'] === true) ? '<a href="tel:' . $property->office_telephone_number . '">' : '' ) . $property->office_telephone_number . ( ($atts['hyperlink_telephone_number'] === true) ? '</a>' : '' ) .  '</div>';
				}

				if ( $property->office_email_address != '' )
				{
					echo '<div class="office-email-address">' . ( ($atts['hyperlink_email_address'] === true) ? '<a href="mailto:' . $property->office_email_address . '">' : '' ) . $property->office_email_address . ( ($atts['hyperlink_email_address'] === true) ? '</a>' : '' ) .  '</div>';
				}

			echo '</div>';
		}

		return ob_get_clean();
    }
}

new PH_Shortcode_Property_Office_Details();