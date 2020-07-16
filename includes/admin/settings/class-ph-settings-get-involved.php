<?php
/**
 * PropertyHive Get Involved Settings
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Get_Involved' ) ) :

/**
 * PH_Settings_Get_Involved
 */
class PH_Settings_Get_Involved extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'getinvolved';
		$this->label = __( 'Get Involved', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
    }

    /**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

        global $hide_save_button;

        $hide_save_button = TRUE;

		return apply_filters( 'propertyhive_get_involved_settings', array(

			array( 'title' => __( 'Get Involved', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'get_involved_options' ),

            array(
                'type'      => 'getinvolved',
            ),

			array( 'type' => 'sectionend', 'id' => 'get_involved_options')

		) ); // End add on settings
	}

    /**
     * Output the settings
     */
    public function output()
    {
        $settings = $this->get_settings();

        PH_Admin_Settings::output_fields( $settings );
    }
}

endif;

return new PH_Settings_Get_Involved();
