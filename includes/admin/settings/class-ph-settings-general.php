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
            ''         => __( 'General', 'propertyhive' ),
            'modules'         => __( 'Modules', 'propertyhive' ),
            'international'         => __( 'International', 'propertyhive' ),
            'map'         => __( 'Map', 'propertyhive' ),
            'misc'         => __( 'Miscellaneous', 'propertyhive' ),
        );

        return $sections;
    }

	
	/**
	 * Get general settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		    
		return apply_filters( 'propertyhive_general_settings', array(

			array( 'title' => __( 'General Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),
            
            array(
                'title'   => __( 'Active Departments', 'propertyhive' ),
                'desc'    => __( 'Residential Sales', 'propertyhive' ),
                'id'      => 'propertyhive_active_departments_sales',
                'type'    => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'start'
            ),
            
            array(
                'title'   => __( 'Active Departments', 'propertyhive' ),
                'desc'    => __( 'Residential Lettings', 'propertyhive' ),
                'id'      => 'propertyhive_active_departments_lettings',
                'type'    => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => 'middle'
            ),

            array(
                'title'   => __( 'Active Departments', 'propertyhive' ),
                'desc'    => __( 'Commercial', 'propertyhive' ),
                'id'      => 'propertyhive_active_departments_commercial',
                'type'    => 'checkbox',
                'checkboxgroup' => 'end'
            ),

            array(
                'title'   => __( 'Primary Department', 'propertyhive' ),
                'id'      => 'propertyhive_primary_department',
                'type'    => 'radio',
                'default' => 'residential-sales',
                'options' => array(
                	'residential-sales' => __( 'Residential Sales', 'propertyhive' ),
                	'residential-lettings' => __( 'Residential Lettings', 'propertyhive' ),
                	'commercial' => __( 'Commercial', 'propertyhive' )
                )
            ),
            
            array(
                'title' => __( 'Property Search Results Page', 'propertyhive' ),
                //'desc'      => '<br/>' . sprintf( __( 'The base page can also be used in your <a href="%s">product permalinks</a>.', 'propertyhive' ), admin_url( 'options-permalink.php' ) ),
                'id'        => 'propertyhive_search_results_page_id',
                'type'      => 'single_select_page',
                'default'   => '',
                'css'       => 'min-width:300px;',
                'desc'  => __( 'This sets the page of your property search results', 'propertyhive' ),
            ),

            array(
                'title'   => __( 'Lettings Fees', 'propertyhive' ),
                'id'      => 'propertyhive_lettings_fees',
                'type'    => 'textarea',
                'css'	  => 'height:150px; width:100%; max-width:400px'
            ),
            
			array( 'type' => 'sectionend', 'id' => 'general_options'),

		) ); // End general settings
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

		return apply_filters( 'propertyhive_general_international_settings', array(

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

			array( 'type' => 'sectionend', 'id' => 'international_options'),

		) ); // End general international settings
	}

	/**
	 * Get map settings array
	 *
	 * @return array
	 */
	public function get_general_map_setting() {
		    
		return apply_filters( 'propertyhive_general_map_settings', array(

			array( 'title' => __( 'Map Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'map_options' ),

            array(
                'title'   => __( 'Google Maps API Key', 'propertyhive' ),
                'id'      => 'propertyhive_google_maps_api_key',
                'type'    => 'text',
                'desc'	=> __( 'If you have a Google Maps API key you can enter it here. A map is displayed when adding/editing properties, and if using our <a href="https://wp-property-hive.com/addons/map-search/" target="_blank">Map Search Add On</a>. You can generate an API key <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>.', 'propertyhive' )
            ),

			array( 'type' => 'sectionend', 'id' => 'map_options'),

		) ); // End general map settings
	}

	/**
	 * Get misc settings array
	 *
	 * @return array
	 */
	public function get_general_misc_setting() {
		    
		return apply_filters( 'propertyhive_general_map_settings', array(

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
                'title'   => __( 'When Entering Features', 'propertyhive' ),
                'id'      => 'propertyhive_features_type',
                'type'    => 'radio',
                'options' => array(
                	'' => __( 'Allow Features To Be Freetyped', 'propertyhive' ),
                	'checkbox' => __( 'Select From A Predefined List (Editable from \'Custom Fields\')', 'propertyhive' ),
                ),
                'desc'	=> __( '', 'propertyhive' )
            ),

			array( 'type' => 'sectionend', 'id' => 'property_options'),

            array( 'title' => __( 'Applicant Registration Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'applicant_registration_options' ),

            array(
                'title'   => __( 'Allow Applicants To Login and Manage Account', 'propertyhive' ),
                'id'      => 'propertyhive_applicant_users',
                'type'    => 'checkbox',
                'desc'  => __( 'Applicable when you allow users to register on a form using the [applicant_registration_form] shortcode.<br><br>Selecting this will allow applicants to create a password when registering, then login to manage their details and requirements. They will be created as a WordPress user with \'Property Hive Contact\' role.<br><br>If unticked applicants will still be able to register, it will just be emailed through to the office and no user account created.', 'propertyhive' )
            ),

            array(
                'title' => __( 'My Account Page', 'propertyhive' ),
                //'desc'      => '<br/>' . sprintf( __( 'The base page can also be used in your <a href="%s">product permalinks</a>.', 'propertyhive' ), admin_url( 'options-permalink.php' ) ),
                'id'        => 'propertyhive_my_account_page_id',
                'type'      => 'single_select_page',
                'default'   => '',
                'css'       => 'min-width:300px;',
                'desc'  => __( 'This sets the \'My Account\' page. This page should contain the [propertyhive_my_account] shortcode', 'propertyhive' ),
            ),

            array( 'type' => 'sectionend', 'id' => 'applicant_registration_options'),

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
						update_option( 'propertyhive_countries', array( $_POST['propertyhive_default_country'] ) );
					}
					else
					{
						// We have default country and countries set
						// Make sure default country is in list of countries selected
						if ( !in_array($_POST['propertyhive_default_country'], $_POST['propertyhive_countries']) ) {
							$_POST['propertyhive_default_country'] = $_POST['propertyhive_countries'][0];
						}

						update_option( 'propertyhive_default_country', $_POST['propertyhive_default_country'] );
						update_option( 'propertyhive_countries', $_POST['propertyhive_countries'] );
					}

					do_action( 'propertyhive_update_currency_exchange_rates' );

					break;
				}
				case 'map':
				{
					$settings = $this->get_general_map_setting();

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
