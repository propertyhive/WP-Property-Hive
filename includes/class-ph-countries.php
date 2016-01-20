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

	public function get_country( $country_code ) {

		$countries = $this->get_countries();

		if ( isset($countries[$country_code]) )
		{
			return $countries[$country_code];
		}
		
		return false;
	}

	/**
	 * Get all countries.
	 * @return array
	 */
	private function get_countries() {
		return array(
			'AU' => array(
				'name' => 'Australia',
				'currency_code' => 'AUD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'AU' => array(
				'name' => 'Austria',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => true
			),
			'BE' => array(
				'name' => 'Belgium',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => true
			),
			'BG' => array(
				'name' => 'Bulgaria',
				'currency_code' => 'BGN',
				'currency_symbol' => 'лв',
				'currency_prefix' => true
			),
			'CA' => array(
				'name' => 'Canada',
				'currency_code' => 'CAD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'HR' => array(
				'name' => 'Croatia',
				'currency_code' => 'HRK',
				'currency_symbol' => 'kn',
				'currency_prefix' => false
			),
			'CY' => array(
				'name' => 'Cyprus',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => true
			),
			'CZ' => array(
				'name' => 'Czech Republic',
				'currency_code' => 'CZK',
				'currency_symbol' => 'Kč',
				'currency_prefix' => false
			),
			'FR' => array(
				'name' => 'France',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => true
			),
			'JP' => array(
				'name' => 'Japan',
				'currency_code' => 'JPY',
				'currency_symbol' => '&yen;',
				'currency_prefix' => true
			),
			'PT' => array(
				'name' => 'Portugal',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => true
			),
			'RU' => array(
				'name' => 'Russia',
				'currency_code' => 'RUB',
				'currency_symbol' => '₽',
				'currency_prefix' => true
			),
			'ES' => array(
				'name' => 'Spain',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => true
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

}