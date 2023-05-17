<?php
/**
 * Elementor Property Negotiator Photo Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Negotiator_Photo_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-negotiator-photo';
	}

	public function get_title() {
		return __( 'Negotiator Photo', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'negotiator' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Negotiator Photo', 'propertyhive' ),
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

		if ( $property->negotiator_photo != '' )
		{
	        echo $property->negotiator_photo;
	    }

	}

}