<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PropertyHive countries
 *
 * The PropertyHive countries class stores country data.
 *
 * @class       PH_Countries
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Countries {

	public function __construct() {

		add_action( 'template_redirect', array( $this, 'ph_check_currency_change' ) );

		add_action( 'propertyhive_update_currency_exchange_rates', array( $this, 'ph_update_currency_exchange_rates' ) );

		add_filter( 'propertyhive_search_form_fields_after', array( $this, 'ensure_currency_value_set' ) );
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 * @param  mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( 'countries' == $key ) {
			return $this->get_countries();
		}
	}

	public function ensure_currency_value_set( $form_controls )
	{
		if ( isset($form_controls['currency']) )
		{
			if ( isset($_GET['currency']) && $_GET['currency'] != '' )
			{

			}
			elseif ( isset($_COOKIE['propertyhive_currency']) && $_COOKIE['propertyhive_currency'] != '' )
			{
				$currency = @json_decode(html_entity_decode($_COOKIE['propertyhive_currency']), TRUE);
				if ( !empty($currency) && isset($currency['currency_code']) && array_key_exists(ph_clean($currency['currency_code']), $form_controls['currency']['options']) )
				{
					$form_controls['currency']['value'] = $currency['currency_code'];
				}
			}
		}

		return $form_controls;
	}

	public function ph_check_currency_change()
	{
		if ( is_post_type_archive('property') && isset($_GET['currency']) )
		{
			if ( $_GET['currency'] == '' )
			{
				// Set to blank to reset back to properties entered currency
				unset( $_COOKIE['propertyhive_currency'] );
  				setcookie( 'propertyhive_currency', '', time() - ( 15 * 60 ) );
				return true;
			}

			$currency = $this->get_currency( sanitize_text_field($_GET['currency']) );
			if ( $currency === FALSE )
			{
				$default_country = get_option( 'propertyhive_default_country', 'GB' );
				$default_country = $this->get_country( $default_country );

				$currency = $this->get_currency( (isset($default_country['currency_code'])) ? $default_country['currency_code'] : 'GBP' );
			}

			$currency['exchange_rate'] = 1;
			$exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );
			if ( isset($exchange_rates[$_GET['currency']]) )
			{
				$currency['exchange_rate'] = $exchange_rates[sanitize_text_field($_GET['currency'])];
			}
			
			ph_setcookie( 'propertyhive_currency', htmlentities(json_encode($currency)), time() + (30 * DAY_IN_SECONDS), is_ssl() );
		}
	}

	public function get_country( $country_code ) {

		$countries = $this->get_countries();

		if ( isset($countries[$country_code]) )
		{
			return $countries[$country_code];
		}
		
		return false;
	}

	public function get_currency( $currency_code ) {

		$countries = $this->get_countries();

		foreach ( $countries as $country )
		{
			if ( $country['currency_code'] == $currency_code )
			{
				$currency_symbol = apply_filters( 'propertyhive_currency_symbol', $country['currency_symbol'], $currency_code);
				$currency_prefix = apply_filters( 'propertyhive_currency_prefix', $country['currency_prefix'], $currency_code);
				
				return array(
					'currency_code' => $currency_code,
					'currency_symbol' => $currency_symbol,
					'currency_prefix' => $currency_prefix
				);
			}
		}

		return false;
	}

	/**
	 * Get all countries.
	 * @return array
	 */
	private function get_countries() {
		$countries = array(
			'AR' => array(
				'name' => 'Argentina',
				'currency_code' => 'ARS',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'AU' => array(
				'name' => 'Australia',
				'currency_code' => 'AUD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'AT' => array(
				'name' => 'Austria',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'BB' => array(
				'name' => 'Barbados',
				'currency_code' => 'BBD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'BE' => array(
				'name' => 'Belgium',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'BG' => array(
				'name' => 'Bulgaria',
				'currency_code' => 'BGN',
				'currency_symbol' => 'лв',
				'currency_prefix' => false
			),
			'CA' => array(
				'name' => 'Canada',
				'currency_code' => 'CAD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'CO' => array(
				'name' => 'Colombia',
				'currency_code' => 'COP',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'HR' => array(
				'name' => 'Croatia',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'CY' => array(
				'name' => 'Cyprus',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'CZ' => array(
				'name' => 'Czech Republic',
				'currency_code' => 'CZK',
				'currency_symbol' => 'Kč',
				'currency_prefix' => false
			),
			'DK' => array(
				'name' => 'Denmark',
				'currency_code' => 'DKK',
				'currency_symbol' => 'kr',
				'currency_prefix' => false
			),
			'FI' => array(
				'name' => 'Finland',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'FR' => array(
				'name' => 'France',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'DE' => array(
				'name' => 'Germany',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'GI' => array(
				'name' => 'Gibraltar',
				'currency_code' => 'GBP',
				'currency_symbol' => '&pound;',
				'currency_prefix' => true
			),
			'GR' => array(
				'name' => 'Greece',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'IN' => array(
				'name' => 'India',
				'currency_code' => 'INR',
				'currency_symbol' => '₹',
				'currency_prefix' => true
			),
			'ID' => array(
		        'name' => 'Indonesia',
		        'currency_code' => 'IDR',
		        'currency_symbol' => 'Rp',
		        'currency_prefix' => true
		    ),
			'IE' => array(
				'name' => 'Ireland',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'IT' => array(
				'name' => 'Italy',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'JM' => array(
				'name' => 'Jamaica',
				'currency_code' => 'JMD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'JP' => array(
				'name' => 'Japan',
				'currency_code' => 'JPY',
				'currency_symbol' => '&yen;',
				'currency_prefix' => true
			),
			'KE' => array(
				'name' => 'Kenya',
				'currency_code' => 'KES',
				'currency_symbol' => 'KSh ',
				'currency_prefix' => true
			),
			'LU' => array(
				'name' => 'Luxembourg',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'MY' => array(
				'name' => 'Malaysia',
				'currency_code' => 'MYR',
				'currency_symbol' => 'RM',
				'currency_prefix' => true
			),
			'MT' => array(
				'name' => 'Malta',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'MU' => array(
				'name' => 'Mauritius',
				'currency_code' => 'MUR',
				'currency_symbol' => 'Rs',
				'currency_prefix' => false
			),
			'MA' => array(
				'name' => 'Morocco',
				'currency_code' => 'MAD',
				'currency_symbol' => 'د.م.',
				'currency_prefix' => false
			),
			'NL' => array(
		        'name' => 'Netherlands',
		        'currency_code' => 'EUR',
		        'currency_symbol' => '&euro;',
		        'currency_prefix' => false
		    ),
			'NZ' => array(
				'name' => 'New Zealand',
				'currency_code' => 'NZD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'NO' => array(
				'name' => 'Norway',
				'currency_code' => 'NOK',
				'currency_symbol' => 'kr',
				'currency_prefix' => false
			),
			'PK' => array(
				'name' => 'Pakistan',
				'currency_code' => 'PKR',
				'currency_symbol' => 'Rs',
				'currency_prefix' => false
			),
			'PT' => array(
				'name' => 'Portugal',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'QA' => array(
				'name' => 'Qatar',
				'currency_code' => 'QAR',
				'currency_symbol' => 'QR',
				'currency_prefix' => false
			),
			'RU' => array(
				'name' => 'Russia',
				'currency_code' => 'RUB',
				'currency_symbol' => '₽',
				'currency_prefix' => true
			),
			'VC' => array(
				'name' => 'Saint Vincent and the Grenadines',
				'currency_code' => 'XCD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'SG' => array(
			    'name' => 'Singapore',
			    'currency_code' => 'SGD',
			    'currency_symbol' => '$',
			    'currency_prefix' => true
			),
			'ZA' => array(
				'name' => 'South Africa',
				'currency_code' => 'ZAR',
				'currency_symbol' => 'R',
				'currency_prefix' => true
			),
			'ES' => array(
				'name' => 'Spain',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'SE' => array(
				'name' => 'Sweden',
				'currency_code' => 'SEK',
				'currency_symbol' => 'kr',
				'currency_prefix' => false
			),
			'CH' => array(
				'name' => 'Switzerland',
				'currency_code' => 'CHF',
				'currency_symbol' => 'CHF',
				'currency_prefix' => true
			),
			'TH' => array(
				'name' => 'Thailand',
				'currency_code' => 'THB',
				'currency_symbol' => '฿',
				'currency_prefix' => true
			),
			'TR' => array(
				'name' => 'Turkey',
				'currency_code' => 'TRY',
				'currency_symbol' => '‎₺',
				'currency_prefix' => true
			),
			'AE' => array(
				'name' => 'United Arab Emirates',
				'currency_code' => 'AED',
				'currency_symbol' => '‎د.إ',
				'currency_prefix' => false
			),
			'GB' => array(
				'name' => 'United Kingdom',
				'currency_code' => 'GBP',
				'currency_symbol' => '&pound;',
				'currency_prefix' => true
			),
			'US' => array(
				'name' => 'United States',
				'currency_code' => 'USD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
		);
		
		return apply_filters( 'propertyhive_countries', $countries );
	}

	/**
	 * Outputs the list of countries for use in dropdown boxes.
	 * @param string $selected_country (default: '')
	 * @param bool   $escape (default: false)
	 */
	public function country_dropdown_options( $selected_country = '', $escape = false ) {
		if ( $this->countries ) 
		{
			foreach ( $this->countries as $key => $value )
			{				
				echo '<option';
				if ( $selected_country == $key || ( $selected_country == '' && $key == 'GB' ) ) {
					echo ' selected="selected"';
				}
				echo ' value="' . esc_attr( $key ) . '">' . ( $escape ? esc_js( $value['name'] ) : $value['name'] ) . '</option>';
			}
		}
	}

	public function convert_price_to_gbp( $price, $currency_code )
	{
		if ( trim($price) == '' )
		{
			return $price;
		}

		$exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );

		if ( isset($exchange_rates[$currency_code]) )
		{
			$price = $price / $exchange_rates[$currency_code];
		}

		return $price;
	}

	public function update_property_price_actual( $postID )
	{
		$countries = $this->countries;

		$department = get_post_meta( $postID, '_department', true );
		if ( ph_get_custom_department_based_on( $department ) !== false )
        {
        	$department = ph_get_custom_department_based_on( $department );
        }
        
		$country = get_post_meta( $postID, '_address_country', true );

		if (isset($countries[$country]))
		{
			if ( $department == 'residential-sales' )
			{
				$currency = get_post_meta( $postID, '_currency', true );
				if ( $country == '' )
				{
					$country = get_option( 'propertyhive_default_country', 'GB' );
				}
				if ( $currency == '' )
				{
					$currency = $this->get_country($country);
					$currency = $currency['currency_code'];
				}

				$price = get_post_meta( $postID, '_price', true );
				
				$converted_price = $this->convert_price_to_gbp( $price, $currency );

				update_post_meta( $postID, '_price_actual', $converted_price );
			}
			elseif ( $department == 'residential-lettings' )
			{
				$currency = get_post_meta( $postID, '_currency', true );
				if ( $country == '' )
				{
					$country = get_option( 'propertyhive_default_country', 'GB' );
				}
				if ( $currency == '' )
				{
					$currency = $this->get_country($country);
					$currency = $currency['currency_code'];
				}

				$rent = get_post_meta( $postID, '_rent', true );
				
				$converted_price = 0;
				if ( !empty($rent) && is_numeric($rent) )
				{
					$price = $rent; // Stored in pcm
					$rent_frequency = get_post_meta( $postID, '_rent_frequency', true );
		            switch ($rent_frequency)
		            {
		            	case "pd": { $price = ($rent * 365) / 12; break; }
	                    case "pppw":
	                    {
	                        $bedrooms = get_post_meta( $postID, '_bedrooms', true );
	                        if ( ( $bedrooms !== FALSE && $bedrooms != 0 && $bedrooms != '' ) && apply_filters( 'propertyhive_pppw_to_consider_bedrooms', true ) == true )
	                        {
	                            $price = (($rent * 52) / 12) * $bedrooms;
	                        }
	                        else
	                        {
	                            $price = ($rent * 52) / 12;
	                        }
	                        break;
	                    }
	                    case "pw": { $price = ($rent * 52) / 12; break; }
	                    case "pcm": { $price = $rent; break; }
	                    case "pq": { $price = ($rent * 4) / 12; break; }
	                    case "pa": { $price = ($rent / 12); break; }
		            }

	                $converted_price = $this->convert_price_to_gbp( $price, $currency );
	            }

	            update_post_meta( $postID, '_price_actual', $converted_price );
			}
			if ( $department == 'commercial' )
			{
				if ( get_post_meta( $postID, '_for_sale', true ) == 'yes' )
				{
					$currency = get_post_meta( $postID, '_commercial_price_currency', true );
					if ( $country == '' )
					{
						$country = get_option( 'propertyhive_default_country', 'GB' );
					}
					if ( $currency == '' )
					{
						$currency = $this->get_country($country);
						$currency = $currency['currency_code'];
					}

					$price = get_post_meta( $postID, '_price_from', true );
					if ( $price == '' )
					{
						$price = get_post_meta( $postID, '_price_to', true );
					}

					$converted_price = $this->convert_price_to_gbp( $price, $currency );

					update_post_meta( $postID, '_price_from_actual', $converted_price );

					$price = get_post_meta( $postID, '_price_to', true );
					if ( $price == '' )
					{
						$price = get_post_meta( $postID, '_price_from', true );
					}

					$converted_price = $this->convert_price_to_gbp( $price, $currency );

					update_post_meta( $postID, '_price_to_actual', $converted_price );
				}
				if ( get_post_meta( $postID, '_to_rent', true ) == 'yes' )
				{
					$currency = get_post_meta( $postID, '_commercial_rent_currency', true );
					if ( $country == '' )
					{
						$country = get_option( 'propertyhive_default_country', 'GB' );
					}
					if ( $currency == '' )
					{
						$currency = $this->get_country($country);
						$currency = $currency['currency_code'];
					}

					$rent_units = get_post_meta( $postID, '_rent_units', true );

					$price = get_post_meta( $postID, '_rent_from', true );
					if ( $price == '' )
					{
						$price = get_post_meta( $postID, '_rent_to', true );
					}
					if ( is_numeric($price) )
					{
			            switch ($rent_units)
			            {
			            	case "pd": { $price = ($price * 365) / 12; break; }
			                case "pw": { $price = ($price * 52) / 12; break; }
			                case "pcm": { $price = $price; break; }
			                case "pq": { $price = ($price * 4) / 12; break; }
			                case "pa": { $price = ($price / 12); break; }
			            }
			        }

		            $converted_price = $this->convert_price_to_gbp( $price, $currency );

		            update_post_meta( $postID, '_rent_from_actual', $converted_price );

		            if ( get_post_meta( $postID, '_for_sale', true ) != 'yes' )
					{
						update_post_meta( $postID, '_price_from_actual', $converted_price );
					}

		            $price = get_post_meta( $postID, '_rent_to', true );
		            if ( $price == '' )
					{
						$price = get_post_meta( $postID, '_rent_from', true );
					}
		            if ( is_numeric($price) )
					{
			            switch ($rent_units)
			            {
			            	case "pd": { $price = ($price * 365) / 12; break; }
			                case "pw": { $price = ($price * 52) / 12; break; }
			                case "pcm": { $price = $price; break; }
			                case "pq": { $price = ($price * 4) / 12; break; }
			                case "pa": { $price = ($price / 12); break; }
			            }
			        }

		            $converted_price = $this->convert_price_to_gbp( $price, $currency );

		            update_post_meta( $postID, '_rent_to_actual', $converted_price );
				}
			}
		}

		do_action('propertyhive_property_price_actual_updated', $postID);
	}

	public function ph_update_currency_exchange_rates()
	{
		global $wpdb;

		if ( $this->countries ) 
		{
			$countries = $this->countries;

			// Filter 'propertyhive_new_currency_exchange_rates' allows someone to use their own currency API
			// Return should be in format:
			// array(
			//		'EUR' => x,
			// 		'USD' => x,
			//		... etc
			// )
			$exchange_rates = apply_filters( 'propertyhive_new_currency_exchange_rates', array() ); 
			$previous_exchange_rates = get_option( 'propertyhive_currency_exchange_rates' );

			if ( empty($exchange_rates) )
			{
				// Get all currency exchange rates from GBP
				// We're using the API from https://github.com/fawazahmed0/exchange-api
				$url = 'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/gbp.json';
				$response = wp_remote_get( $url );

				if ( is_array( $response ) )
				{
					$body = wp_remote_retrieve_body( $response );
					$json = json_decode($body, true);

					// If response is valid JSON and contains the core gbp key
					if ( $json !== null && isset( $json['gbp'] ) )
					{
						$exchange_rates_array = $json['gbp'];

						foreach ( $countries as $country )
						{
							$currency_code = $country['currency_code'];

							// If we haven't already got this currency and it's not GBP
							if (!isset($exchange_rates[$currency_code]) && $currency_code != 'GBP')
							{
								// If this currency is in the list we received from the API
								if ( isset( $exchange_rates_array[strtolower( $currency_code )] ) )
								{
									$exchange_rates[$currency_code] = (string)$exchange_rates_array[strtolower( $currency_code )];
								}
							}
						}
					}
				}
			}

			// Only update the settings if the API call was successful and we got exchange rates, or if there were none set previously
			if ( !empty( $exchange_rates ) || empty( $previous_exchange_rates ) )
			{
				$exchange_rates['GBP'] = "1.0000";
				update_option( 'propertyhive_currency_exchange_rates', $exchange_rates );
				update_option( 'propertyhive_currency_exchange_rates_updated', date("Y-m-d") );
			}

			do_action('propertyhive_exchange_rates_updated', $exchange_rates);

			// Loop through all on market properties and update _price_actual meta value to be price in GBP
			$args = array(
				'post_type' => 'property',
				'fields' => 'ids',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => '_on_market',
						'value' => 'yes',
					),
					array(
						'key' => '_address_country',
						'value' => 'GB',
						'compare' => '!=',
					)
				),
				'nopaging' => true,
				'orderby' => 'rand', // order by rand incase there are lots of properties and it times out, at least they should eventually all get processed
			);
			$property_query =  new WP_Query($args);

			if ($property_query->have_posts())
			{
				while ($property_query->have_posts())
				{
					$property_query->the_post();

					$this->update_property_price_actual( get_the_ID() );
				}
			}

			wp_reset_postdata();

		}
	}

}
