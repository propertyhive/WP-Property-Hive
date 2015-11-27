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
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

		/*if ( ( $styles = PH_Frontend_Scripts::get_styles() ) && array_key_exists( 'propertyhive-general', $styles ) ) {
			add_action( 'propertyhive_admin_field_frontend_styles', array( $this, 'frontend_styles_setting' ) );
		}*/
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		    
		/*$currency_code_options = get_propertyhive_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_propertyhive_currency_symbol( $code ) . ')';
		}*/

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
                'checkboxgroup' => 'end'
            ),

            array(
                'title'   => __( 'Primary Department', 'propertyhive' ),
                'id'      => 'propertyhive_primary_department',
                'type'    => 'radio',
                'default' => 'residential-sales',
                'options' => array(
                	'residential-sales' => 'Residential Sales',
                	'residential-lettings' => 'Residential Lettings'
                )
            ),
            
            array(
                'title' => __( 'Property Search Results Page', 'propertyhive' ),
                //'desc'      => '<br/>' . sprintf( __( 'The base page can also be used in your <a href="%s">product permalinks</a>.', 'propertyhive' ), admin_url( 'options-permalink.php' ) ),
                'id'        => 'propertyhive_search_results_page_id',
                'type'      => 'single_select_page',
                'default'   => '',
                'class'     => 'chosen_select_nostd',
                'css'       => 'min-width:300px;',
                'desc_tip'  => __( 'This sets the page of your property search results', 'propertyhive' ),
            ),
            
			array( 'type' => 'sectionend', 'id' => 'general_options'),

			/*array(	'title' => __( 'Currency Options', 'propertyhive' ), 'type' => 'title', 'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'propertyhive' ), 'id' => 'pricing_options' ),

			array(
				'title' 	=> __( 'Currency', 'propertyhive' ),
				'desc' 		=> __( 'This controls what currency prices are listed at in the catalog and which currency gateways will take payments in.', 'propertyhive' ),
				'id' 		=> 'propertyhive_currency',
				'css' 		=> 'min-width:350px;',
				'default'	=> 'GBP',
				'type' 		=> 'select',
				'class'		=> 'chosen_select',
				'desc_tip'	=>  true,
				'options'   => $currency_code_options
			),

			array(
				'title' => __( 'Currency Position', 'propertyhive' ),
				'desc' 		=> __( 'This controls the position of the currency symbol.', 'propertyhive' ),
				'id' 		=> 'propertyhive_currency_pos',
				'css' 		=> 'min-width:350px;',
				'class'		=> 'chosen_select',
				'default'	=> 'left',
				'type' 		=> 'select',
				'options' => array(
					'left'        => __( 'Left', 'propertyhive' ) . ' (' . get_propertyhive_currency_symbol() . '99.99)',
					'right'       => __( 'Right', 'propertyhive' ) . ' (99.99' . get_propertyhive_currency_symbol() . ')',
					'left_space'  => __( 'Left with space', 'propertyhive' ) . ' (' . get_propertyhive_currency_symbol() . ' 99.99)',
					'right_space' => __( 'Right with space', 'propertyhive' ) . ' (99.99 ' . get_propertyhive_currency_symbol() . ')'
				),
				'desc_tip'	=>  true,
			),

			array(
				'title' => __( 'Thousand Separator', 'propertyhive' ),
				'desc' 		=> __( 'This sets the thousand separator of displayed prices.', 'propertyhive' ),
				'id' 		=> 'propertyhive_price_thousand_sep',
				'css' 		=> 'width:50px;',
				'default'	=> ',',
				'type' 		=> 'text',
				'desc_tip'	=>  true,
			),

			array(
				'title' => __( 'Decimal Separator', 'propertyhive' ),
				'desc' 		=> __( 'This sets the decimal separator of displayed prices.', 'propertyhive' ),
				'id' 		=> 'propertyhive_price_decimal_sep',
				'css' 		=> 'width:50px;',
				'default'	=> '.',
				'type' 		=> 'text',
				'desc_tip'	=>  true,
			),

			array(
				'title' => __( 'Number of Decimals', 'propertyhive' ),
				'desc' 		=> __( 'This sets the number of decimal points shown in displayed prices.', 'propertyhive' ),
				'id' 		=> 'propertyhive_price_num_decimals',
				'css' 		=> 'width:50px;',
				'default'	=> '2',
				'desc_tip'	=>  true,
				'type' 		=> 'number',
				'custom_attributes' => array(
					'min' 	=> 0,
					'step' 	=> 1
				)
			),

			array( 'type' => 'sectionend', 'id' => 'pricing_options' ),*/

			/*array(	'title' => __( 'Styles and Scripts', 'propertyhive' ), 'type' => 'title', 'id' => 'script_styling_options' ),

			array( 'type' 		=> 'frontend_styles' ),

			array(
				'title' => __( 'Scripts', 'propertyhive' ),
				'desc' 	=> __( 'Enable Lightbox', 'propertyhive' ),
				'id' 		=> 'propertyhive_enable_lightbox',
				'default'	=> 'yes',
				'desc_tip'	=> __( 'Include PropertyHive\'s lightbox. Product gallery images will open in a lightbox.', 'propertyhive' ),
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> 'start'
			),

			array(
				'desc' 		=> __( 'Enable enhanced country select boxes', 'propertyhive' ),
				'id' 		=> 'propertyhive_enable_chosen',
				'default'	=> 'yes',
				'type' 		=> 'checkbox',
				'checkboxgroup'		=> 'end',
				'desc_tip'	=> __( 'This will enable a script allowing the country fields to be searchable.', 'propertyhive' ),
				'autoload'  => false
			),

			array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),*/

		) ); // End general settings
	}

	/**
	 * Output the frontend styles settings.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_styles_setting() {
		/*?><tr valign="top" class="propertyhive_frontend_css_colors">
			<th scope="row" class="titledesc">
				<?php _e( 'Frontend Styles', 'propertyhive' ); ?>
			</th>
			<td class="forminp"><?php

				$base_file = PH()->plugin_path() . '/assets/css/propertyhive-base.less';
				$css_file  = PH()->plugin_path() . '/assets/css/propertyhive.css';

				if ( is_writable( $base_file ) && is_writable( $css_file ) ) {

					// Get settings
					$colors = array_map( 'esc_attr', (array) get_option( 'propertyhive_frontend_css_colors' ) );

					// Defaults
					if ( empty( $colors['primary'] ) ) {
						$colors['primary'] = '#ad74a2';
					}
					if ( empty( $colors['secondary'] ) ) {
						$colors['secondary'] = '#f7f6f7';
					}
					if ( empty( $colors['highlight'] ) ) {
						$colors['highlight'] = '#85ad74';
					}
					if ( empty( $colors['content_bg'] ) ) {
						$colors['content_bg'] = '#ffffff';
					}
					if ( empty( $colors['subtext'] ) ) {
						$colors['subtext'] = '#777777';
					}

					// Show inputs
					$this->color_picker( __( 'Primary', 'propertyhive' ), 'propertyhive_frontend_css_primary', $colors['primary'], __( 'Call to action buttons/price slider/layered nav UI', 'propertyhive' ) );
					$this->color_picker( __( 'Secondary', 'propertyhive' ), 'propertyhive_frontend_css_secondary', $colors['secondary'], __( 'Buttons and tabs', 'propertyhive' ) );
					$this->color_picker( __( 'Highlight', 'propertyhive' ), 'propertyhive_frontend_css_highlight', $colors['highlight'], __( 'Price labels and Sale Flashes', 'propertyhive' ) );
					$this->color_picker( __( 'Content', 'propertyhive' ), 'propertyhive_frontend_css_content_bg', $colors['content_bg'], __( 'Your themes page background - used for tab active states', 'propertyhive' ) );
					$this->color_picker( __( 'Subtext', 'propertyhive' ), 'propertyhive_frontend_css_subtext', $colors['subtext'], __( 'Used for certain text and asides - breadcrumbs, small text etc.', 'propertyhive' ) );

				} else {
					echo '<span class="description">' . __( 'To edit colours <code>propertyhive/assets/css/propertyhive-base.less</code> and <code>propertyhive.css</code> need to be writable. See <a href="http://codex.wordpress.org/Changing_File_Permissions">the Codex</a> for more information.', 'propertyhive' ) . '</span>';
				}

			?></td>
		</tr><?php*/
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
		$settings = $this->get_settings();

		PH_Admin_Settings::save_fields( $settings );

	}

}

endif;

return new PH_Settings_General();
