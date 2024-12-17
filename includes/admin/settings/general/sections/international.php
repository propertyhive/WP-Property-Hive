/**
	 * Get international settings array
	 *
	 * @return array
	 */
	public function get_general_international_setting() {

		$settings = array(

			array( 'title' => __( 'International Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'international_options' ),

			array(
                'title'   => __( 'Default Country', 'propertyhive' ),
                'id'      => 'propertyhive_default_country',
                'type'    => 'single_select_country',
                'css'       => 'min-width:300px;',
            ),

            array(
                'title'   => __( 'Countries Where You Operate', 'propertyhive' ),
                'id'      => 'propertyhive_countries',
                'type'    => 'multi_select_countries',
                'css'       => 'min-width:300px;',
                'desc'	=> __( 'Hold ctrl/cmd whilst clicking to select multiple', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Price Thousand Separator', 'propertyhive' ),
                'id'      => 'propertyhive_price_thousand_separator',
                'type'    => 'text',
                'default' => ',',
                'css'       => 'width:50px;',
                'desc'  => __( 'This only effects prices output on the frontend. Prices entered and displayed in the backend will use the comma character (,) as the thousand separator.', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Price Decimal Separator', 'propertyhive' ),
                'id'      => 'propertyhive_price_decimal_separator',
                'type'    => 'text',
                'default' => '.',
                'css'       => 'width:50px;',
                'desc'  => __( 'This only effects prices output on the frontend. Prices entered and displayed in the backend will use the period character (.) as the decimal separator.', 'propertyhive' )
            )
        );

        $ph_countries = new PH_Countries();
        $ph_countries = $ph_countries->countries;

        $currencies = array();
        $countries = array();
        if ( !empty($ph_countries) )
        {
            foreach ( $ph_countries as $country_code => $country )
            {
                $currencies[$country['currency_code']] = $country['currency_code'];
                $countries[$country_code] = $country;
            }
        }
        $currencies = array_unique($currencies);
        ksort($currencies);

        $settings[] =  array(
            'title'   => __( 'Currency Used In Search Forms', 'propertyhive' ),
            'id'      => 'propertyhive_search_form_currency',
            'type'    => 'select',
            'options' => $currencies,
            'default' => 'GBP',
            'desc'    => __( 'Please note that this doesn\'t change the currency symbol shown in price dropdowns within search forms. The easiest way to achieve that is to use our free <a href="https://wp-property-hive.com/addons/template-assistant/" target="_blank">Template Assistant add on</a>.', 'propertyhive' ),
        );

		$settings[] = array( 'type' => 'sectionend', 'id' => 'international_options');

        $settings[] = array(
            'type' => 'html',
            'html' => '<script>

                var countries = '. json_encode( $countries ) . ';

            </script>'
        );

        return apply_filters( 'propertyhive_general_international_settings', $settings );
	}
