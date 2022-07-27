<?php
/**
 * Elementor Property Enquiry Form Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Enquiry_Form_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-enquiry-form';
	}

	public function get_title() {
		return __( 'Enquiry Form', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-keyboard';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'enquiry', 'form' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Enquiry Form', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		if ( !isset($property->id) ) {
			return;
		}

		propertyhive_enquiry_form();

	}

}