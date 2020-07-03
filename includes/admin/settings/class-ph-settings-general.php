<?php
/**
 * PropertyHive General Settings
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_General' ) ) :

/**
 * PH_Settings_General
 */
class PH_Settings_General extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 5 );
		add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            ''              => __( 'General', 'propertyhive' ),
            'modules'       => __( 'Modules', 'propertyhive' ),
            'international' => __( 'International', 'propertyhive' ),
            'map'           => __( 'Map', 'propertyhive' ),
            'gdpr'          => __( 'GDPR', 'propertyhive' ),
            'misc'          => __( 'Miscellaneous', 'propertyhive' ),
        );

        return $sections;
    }

	
	/**
	 * Get general settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		  
        $departments = ph_get_departments();

		$settings = array(

			array( 'title' => __( 'General Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),
        
        );

        $i = 0;
        foreach ( $departments as $key => $value )
        {
            $checkboxgroup = 'middle';
            if ( $i == 0 ) { $checkboxgroup = 'start'; }
            if ( $i == (count($departments) - 1) ) { $checkboxgroup = 'end'; }

            $settings[] = array(
                'title'   => __( 'Active Departments', 'propertyhive' ),
                'desc'    => $value,
                'id'      => 'propertyhive_active_departments_' . str_replace("residential-", "", $key),
                'type'    => 'checkbox',
                'default' => ( ( in_array($key, array('residential-sales', 'residential-lettings', 'commercial')) ) ? 'yes' : '' ),
                'checkboxgroup' => $checkboxgroup,
            );

            ++$i;
        }

        $settings[] = array(
            'title'   => __( 'Primary Department', 'propertyhive' ),
            'id'      => 'propertyhive_primary_department',
            'type'    => 'radio',
            'default' => 'residential-sales',
            'options' => $departments,
        );
            
        $settings[] = array(
            'title' => __( 'Property Search Results Page', 'propertyhive' ),
            'id'        => 'propertyhive_search_results_page_id',
            'type'      => 'single_select_page',
            'default'   => '',
            'css'       => 'min-width:300px;',
            'desc'  => __( 'This sets the page of your property search results', 'propertyhive' ),
        );

        $settings[] = array(
            'title'   => __( 'Lettings Fees (Residential)', 'propertyhive' ),
            'id'      => 'propertyhive_lettings_fees',
            'type'    => 'textarea',
            'css'	  => 'height:150px; width:100%; max-width:400px'
        );

        $settings[] = array(
            'title'   => __( 'Lettings Fees (Commercial)', 'propertyhive' ),
            'id'      => 'propertyhive_lettings_fees_commercial',
            'type'    => 'textarea',
            'css'     => 'height:150px; width:100%; max-width:400px'
        );

        $settings[] = array(
            'title'   => __( 'Display Link To Lettings Fees Next To Price', 'propertyhive' ),
            'desc'    => __( 'In Search Results', 'propertyhive' ),
            'id'      => 'propertyhive_lettings_fees_display_search_results',
            'type'    => 'checkbox',
            'checkboxgroup' => 'start',
        );

        $settings[] = array(
            'title'   => __( 'Display Link To Lettings Fees Next To Price', 'propertyhive' ),
            'desc'    => __( 'On Property Details Page', 'propertyhive' ),
            'id'      => 'propertyhive_lettings_fees_display_single_property',
            'type'    => 'checkbox',
            'checkboxgroup' => 'end',
        );
            
		$settings[] = array( 'type' => 'sectionend', 'id' => 'general_options');

        return apply_filters( 'propertyhive_general_settings', $settings );
	}

	/**
	 * Get general modules settings array
	 *
	 * @return array
	 */
	public function get_general_modules_setting() {
		    
		return apply_filters( 'propertyhive_general_modules_settings', array(

			array( 'title' => __( 'Disabled Modules', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'modules_options' ),

			array(
                'type'    => 'html',
                'html'    => __( 'Here you can choose which modules are enabled or disabled within Property Hive. Check the modules you <strong>DO NOT</strong> wish to use from the list below', 'propertyhive' ) . ':',
            ),
            
            array(
                'title'   => __( 'Disabled Modules', 'propertyhive' ),
                'desc'    => __( 'Contacts (Applicants, Owners/Landlords and Third Party Contacts)', 'propertyhive' ),
                'id'      => 'propertyhive_module_disabled_contacts',
                'type'    => 'checkbox',
                'default' => '',
                'checkboxgroup' => 'start'
            ),

            array(
                'title'   => __( 'Disabled Modules', 'propertyhive' ),
                'desc'    => __( 'Appraisals', 'propertyhive' ),
                'id'      => 'propertyhive_module_disabled_appraisals',
                'type'    => 'checkbox',
                'default' => '',
                'checkboxgroup' => 'middle'
            ),
            
            array(
                'title'   => __( 'Disabled Modules', 'propertyhive' ),
                'desc'    => __( 'Viewings', 'propertyhive' ),
                'id'      => 'propertyhive_module_disabled_viewings',
                'type'    => 'checkbox',
                'default' => '',
                'checkboxgroup' => 'middle'
            ),

            array(
                'title'   => __( 'Disabled Modules', 'propertyhive' ),
                'desc'    => __( 'Offers and Sales', 'propertyhive' ),
                'id'      => 'propertyhive_module_disabled_offers_sales',
                'type'    => 'checkbox',
                'default' => '',
                'checkboxgroup' => 'middle'
            ),

            array(
                'title'   => __( 'Disabled Modules', 'propertyhive' ),
                'desc'    => __( 'Tenancies', 'propertyhive' ),
                'id'      => 'propertyhive_module_disabled_tenancies',
                'type'    => 'checkbox',
                'default' => '',
                'checkboxgroup' => 'middle'
            ),

            array(
                'title'   => __( 'Disabled Modules', 'propertyhive' ),
                'desc'    => __( 'Enquiries', 'propertyhive' ),
                'id'      => 'propertyhive_module_disabled_enquiries',
                'type'    => 'checkbox',
                'default' => '',
                'checkboxgroup' => 'end'
            ),
            
			array( 'type' => 'sectionend', 'id' => 'modules_options'),

		) ); // End general module settings
	}

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
            ),

            array(
                'title'   => __( 'Price Decimal Separator', 'propertyhive' ),
                'id'      => 'propertyhive_price_decimal_separator',
                'type'    => 'text',
                'default' => '.',
                'css'       => 'width:50px;',
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

	/**
	 * Get map settings array
	 *
	 * @return array
	 */
	public function get_general_map_setting() {
		    
		$settings = array(

			array( 'title' => __( 'Map Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'map_options' ),

            array(
                'title'   => __( 'Google Maps API Key', 'propertyhive' ),
                'id'      => 'propertyhive_google_maps_api_key',
                'type'    => 'text',
                'desc'	=> '<p>' . __( 'If you have a Google Maps API key you can enter it here. You can generate an API key <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>.<br>This is used when displaying the map when adding/editing properties, and if using our <a href="https://wp-property-hive.com/addons/map-search/" target="_blank">Map Search</a> or <a href="https://wp-property-hive.com/addons/radial-search/" target="_blank">Radial Search</a> add ons.<br>When creating your API key we recommend that you enable the Geocoding library. More about this can be found <a href="https://docs.wp-property-hive.com/user-guide/maps-co-ordinates-and-geocoding/" target="_blank">here</a>.', 'propertyhive' ) . '</p>'
            )

        );

        if ( apply_filters( 'propertyhive_use_google_maps_geocoding_api_key', false) === true )
        {
            $settings[] = array(
                'title'   => __( 'Google Maps Geocoding API Key', 'propertyhive' ),
                'id'      => 'propertyhive_google_maps_geocoding_api_key',
                'type'    => 'text',
                'desc'  => '<p>' . __( 'If you have referer restrictions applied to the main API key entered then server side geocoding requests will be blocked. To get around this you can setup a separate API key specifically for geocoding and enter it here, with IP restrictions applied instead if required.<br>More about this can be found <a href="https://docs.wp-property-hive.com/user-guide/maps-co-ordinates-and-geocoding/" target="_blank">here</a>.', 'propertyhive' ) . '</p>'
            );
        }

		$settings[] = array( 'type' => 'sectionend', 'id' => 'map_options');

        return apply_filters( 'propertyhive_general_map_settings', $settings );
	}

    /**
     * Get GDPR settings array
     *
     * @return array
     */
    public function get_general_gdpr_setting() {
            
        return apply_filters( 'propertyhive_general_gdpr_settings', array(

            array( 'title' => __( 'GDPR Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'gdpr_options' ),

            array(
                'title'   => __( 'Store Property Enquiries', 'propertyhive' ),
                'id'      => 'propertyhive_store_property_enquiries',
                'type'    => 'checkbox',
                'default' => 'yes',
                'desc'  => __( 'If using the default property enquiry form that comes with Property Hive, select this option if enquiries should be saved in the \'Enquiries\' section of Property Hive. Note that regardless of whether this is ticked or not, the enquiry will still be sent via email. This option simply determines whether we save it to the database or not.', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Property Enquiry Form Disclaimer', 'propertyhive' ),
                'id'      => 'propertyhive_property_enquiry_form_disclaimer',
                'type'    => 'wysiwyg',
                'desc'  => __( 'Add disclaimer text, including a link to a privacy policy, that will appear on the property enquiry form.', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Applicant Registration Form Disclaimer', 'propertyhive' ),
                'id'      => 'propertyhive_applicant_registration_form_disclaimer',
                'type'    => 'wysiwyg',
                'desc'  => __( 'Add disclaimer text, including a link to a privacy policy, that will appear on the applicant registration form.', 'propertyhive' )
            ),

            array( 'type' => 'sectionend', 'id' => 'gdpr_options'),

        ) ); // End general GDPR settings
    }

	/**
	 * Get misc settings array
	 *
	 * @return array
	 */
	public function get_general_misc_setting() {
		    
		return apply_filters( 'propertyhive_general_misc_settings', array(

			array( 'title' => __( 'Property Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'property_options' ),

            array(
                'title'   => __( 'Search By Address Should', 'propertyhive' ),
                'id'      => 'propertyhive_address_keyword_compare',
                'type'    => 'radio',
                'default' => '=',
                'options' => array(
                    '=' => __( 'Match Keyword Exactly', 'propertyhive' ),
                    'LIKE' => __( 'Perform Loose Search', 'propertyhive' ),
                ),
                'desc_tip'  => __( 'Applicable if you allow users to search by entering a location. If \'Match Keyword Exactly\' is selected a search for \'Walton\' would not return properties in \'Walton On Thames\', but would prevent properties in \'Lincolnshire\' appearing when searching for \'Lincoln\'. \'Perform Loose Search\' would do the opposite.', 'propertyhive' )
            ),

            array(
                'title' => __( 'Commercial Display In Search Results', 'propertyhive' ),
                'id'        => 'propertyhive_commercial_display',
                'type'      => 'select',
                'default'   => '',
                'css'       => 'min-width:300px;',
                'options'   => array(
                    '' => __( 'Display top level properties and units', 'propertyhive' ),
                    'top_level_only' => __( 'Display top level properties only', 'propertyhive' ),
                    'top_level_only_but_units_when_filtered' => __( 'Display top level properties only but units as well if matching filters', 'propertyhive' ),
                ),
            ),

            array(
                'title'   => __( 'When Entering Features', 'propertyhive' ),
                'id'      => 'propertyhive_features_type',
                'type'    => 'radio',
                'options' => array(
                	'' => __( 'Allow Features To Be Freetyped', 'propertyhive' ),
                	'checkbox' => __( 'Select From A Predefined List (Editable from \'Custom Fields\')', 'propertyhive' ),
                ),
            ),

            array(
                'title'   => __( 'Enable Auto-Incremental Reference Numbers', 'propertyhive' ),
                'id'      => 'propertyhive_auto_incremental_reference_numbers',
                'type'    => 'checkbox',
                'desc'  => __( 'Will prefill the property reference with an auto-incremental number when adding a new property', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Next Incremental Reference Number', 'propertyhive' ),
                'id'      => 'propertyhive_auto_incremental_next',
                'type'    => 'number',
                'default' => 1,
            ),

			array( 'type' => 'sectionend', 'id' => 'property_options'),

            array( 'title' => __( 'Applicant Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'applicant_registration_options' ),

            array(
                'title'   => __( 'Allow Applicants To Login and Manage Account', 'propertyhive' ),
                'id'      => 'propertyhive_applicant_users',
                'type'    => 'checkbox',
                'desc'  => __( 'Applicable when you allow users to register on a form using the [applicant_registration_form] shortcode.<br><br>Selecting this will allow applicants to create a password when registering, then login to manage their details and requirements. They will be created as a WordPress user with \'Property Hive Contact\' role.<br><br>If unticked applicants will still be able to register and they\'ll go into Property Hive as an applicant, they just won\'t be able to login at a later date.', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Send Email When Applicant Registers', 'propertyhive' ),
                'id'      => 'propertyhive_new_registration_alert',
                'type'    => 'checkbox',
                'desc'  => __( 'Choose whether an email should be sent alerting you to the fact someone has registered. The email will be sent to the chosen office. Offices can be managed click selecting the \'Offices\' tab above.', 'propertyhive' )
            ),

            array(
                'title' => __( 'My Account Page', 'propertyhive' ),
                'id'        => 'propertyhive_my_account_page_id',
                'type'      => 'single_select_page',
                'default'   => '',
                'css'       => 'min-width:300px;',
                'desc'  => __( 'This sets the \'My Account\' page. This page should contain the [propertyhive_my_account] shortcode', 'propertyhive' ),
            ),

            array(
                'title' => __( 'Match Price Range % (Lower)', 'propertyhive' ),
                'id'        => 'propertyhive_applicant_match_price_range_percentage_lower',
                'type'      => 'number',
                'default'   => '20',
                'desc'  => '<p>' . __( 'When a maximum price is entered against an applicants requirements we create a \'Match Price Range\' which determines the price of properties that would get matched to the applicant.<br>We do this because you:<br>a) Wouldn\'t want to send them properties that were way below their specified maximum price and<br>b) Would probably want to send them properties which are slightly just over the price specified as, if the perfect property came along, a lot of people would actually be able to increase their maximum.<br>These settings determine the % above and below the maximum price entered that the \'Match Price Range\' will default to per applicant.<br>For example, if the maximum price entered was &pound;500,000, the lower setting was 20% and the higher setting was 5%, the applicant would actually match to properties that were priced &pound;400,000 - &pound;525,000.<br>This price range can be overwritten on a per-applicant basis, or disabled completely by leaving these settings empty.<br>Note that this currently only applies to applicants looking for sales properties and changing this value at a later date will only effect applicants that don\'t already have a match price range set.', 'propertyhive' ) . '</p>',
            ),

            array(
                'title' => __( 'Match Price Range % (Higher)', 'propertyhive' ),
                'id'        => 'propertyhive_applicant_match_price_range_percentage_higher',
                'type'      => 'number',
                'default'   => '5',
            ),

            array( 'type' => 'sectionend', 'id' => 'applicant_registration_options'),

            array( 'title' => __( 'Property Media Storage', 'propertyhive' ), 'type' => 'title', 'desc' => 'By default media attached to properties will be stored in the WordPress media library. If the property media is hosted elsewhere (for example if you import properties from a third party and they allow you to link direct to the files on their server) you can choose to store media as URL\'s. It also means that the images are not downloaded onto your server, thus saving diskspace.<br><br>Note: If you change this you\'ll need to re-add all of the property media for existing properties.<br>Note: Changing this will not delete all existing media or URLs entered.<br>Note: Choosing to store the media as URLs will mean you don\'t benefit from having access to different sized images (i.e. small , medium, large etc).', 'id' => 'media_storage_options' ),

            array(
                'title'   => __( 'Images Stored As', 'propertyhive' ),
                'id'      => 'propertyhive_images_stored_as',
                'type'    => 'select',
                'default' => 'files',
                'options' => array(
                    'files' => __( 'Media Files', 'propertyhive' ),
                    'urls' => __( 'URLs', 'propertyhive' ),
                ),
                'desc'  => __( '', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Floorplans Stored As', 'propertyhive' ),
                'id'      => 'propertyhive_floorplans_stored_as',
                'type'    => 'select',
                'default' => 'files',
                'options' => array(
                    'files' => __( 'Media Files', 'propertyhive' ),
                    'urls' => __( 'URLs', 'propertyhive' ),
                ),
                'desc'  => __( '', 'propertyhive' )
            ),

            array(
                'title'   => __( 'Brochures Stored As', 'propertyhive' ),
                'id'      => 'propertyhive_brochures_stored_as',
                'type'    => 'select',
                'default' => 'files',
                'options' => array(
                    'files' => __( 'Media Files', 'propertyhive' ),
                    'urls' => __( 'URLs', 'propertyhive' ),
                ),
                'desc'  => __( '', 'propertyhive' )
            ),

            array(
                'title'   => __( 'EPCs Stored As', 'propertyhive' ),
                'id'      => 'propertyhive_epcs_stored_as',
                'type'    => 'select',
                'default' => 'files',
                'options' => array(
                    'files' => __( 'Media Files', 'propertyhive' ),
                    'urls' => __( 'URLs', 'propertyhive' ),
                ),
                'desc'  => __( '', 'propertyhive' )
            ),

            array( 'type' => 'sectionend', 'id' => 'media_storage_options'),

		) ); // End general misc settings
	}

	/**
     * Output the settings
     */
    public function output() {
    	global $current_section;

        if ( $current_section ) 
        {
        	switch ($current_section)
            {
            	case "modules": { $settings = $this->get_general_modules_setting(); break; }
                case "international": { $settings = $this->get_general_international_setting(); break; }
                case "map": { $settings = $this->get_general_map_setting(); break; }
                case "gdpr": { $settings = $this->get_general_gdpr_setting(); break; }
                case "misc": { $settings = $this->get_general_misc_setting(); break; }
                default: { die("Unknown setting section"); }
            }
        }
        else
        {
        	$settings = $this->get_settings(); 
        }

        PH_Admin_Settings::output_fields( $settings );
    }

	/**
	 * Output a colour picker input box.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $id
	 * @param mixed $value
	 * @param string $desc (default: '')
	 * @return void
	 */
	function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box"><strong><img class="help_tip" data-tip="' . esc_attr( $desc ) . '" src="' . PH()->plugin_url() . '/assets/images/help.png" height="16" width="16" /> ' . esc_html( $name ) . '</strong>
			<input name="' . esc_attr( $id ). '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		if ( $current_section != '' ) 
        {
        	switch ($current_section)
        	{
        		case 'modules':
				{
					$settings = $this->get_general_modules_setting();

					PH_Admin_Settings::save_fields( $settings );
					break;
				}
				case 'international':
				{
					if (!isset($_POST['propertyhive_countries']) || (isset($_POST['propertyhive_countries']) && empty($_POST['propertyhive_countries'])))
					{
						// If we haven't selected which countries we operate in
						update_option( 'propertyhive_countries', array( ph_clean($_POST['propertyhive_default_country']) ) );
					}
					else
					{
						// We have default country and countries set
						// Make sure default country is in list of countries selected
						if ( !in_array(ph_clean($_POST['propertyhive_default_country']), ph_clean($_POST['propertyhive_countries'])) ) {
							$_POST['propertyhive_default_country'] = $_POST['propertyhive_countries'][0];
						}

						update_option( 'propertyhive_default_country', ph_clean($_POST['propertyhive_default_country']) );
						update_option( 'propertyhive_countries', ph_clean($_POST['propertyhive_countries']) );
					}

                    update_option( 'propertyhive_price_thousand_separator', ph_clean($_POST['propertyhive_price_thousand_separator']) );
                    update_option( 'propertyhive_price_decimal_separator', ph_clean($_POST['propertyhive_price_decimal_separator']) );

                    update_option( 'propertyhive_search_form_currency', ph_clean($_POST['propertyhive_search_form_currency']) );

					do_action( 'propertyhive_update_currency_exchange_rates' );

					break;
				}
				case 'map':
				{
					$settings = $this->get_general_map_setting();

					PH_Admin_Settings::save_fields( $settings );
					break;
				}
                case 'gdpr':
                {
                    $settings = $this->get_general_gdpr_setting();

                    PH_Admin_Settings::save_fields( $settings );
                    break;
                }
				case 'misc':
				{
					$settings = $this->get_general_misc_setting();

					PH_Admin_Settings::save_fields( $settings );
					break;
				}
				default: { die("Unknown setting section"); }
			}
		}
		else
		{
			$settings = $this->get_settings();

			PH_Admin_Settings::save_fields( $settings );

			flush_rewrite_rules();
		}
	}

}

endif;

return new PH_Settings_General();
