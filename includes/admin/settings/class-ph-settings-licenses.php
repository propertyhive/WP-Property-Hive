<?php
/**
 * PropertyHive License Settings
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Licenses' ) ) :

/**
 * PH_Settings_Licenses.
 */
class PH_Settings_Licenses extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'licenses';
		$this->label = __( 'Licenses', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'Licenses', 'propertyhive' ),
		);
		return apply_filters( 'propertyhive_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'propertyhive_licenses_settings', array(

			array( 'title' => __( 'Licenses', 'propertyhive' ), 'type' => 'title', 'id' => 'license_options' ),

			array(
				'title'       => __( 'Header Image URL', 'propertyhive' ),
				'desc'        => __( 'URL to an image you want to show in the email header. Upload images using the media uploader (Admin > Media).', 'propertyhive' ),
				'id'          => 'propertyhive_email_header_image',
				'type'        => 'text',
				'css'         => 'min-width:300px;',
				'placeholder' => 'http://',
				'default'     => '',
				'autoload'    => false,
				'desc_tip'    => true,
			),

			array( 'type' => 'sectionend', 'id' => 'license_options' ),

		) );

		return apply_filters( 'propertyhive_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings();
		PH_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		PH_Admin_Settings::save_fields( $this->get_settings() );
	}
}

endif;

return new PH_Settings_Licenses();