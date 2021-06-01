<?php
/**
 * Elementor Property Street View Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Street_View_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-street-view';
	}

	public function get_title() {
		return __( 'Street View', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-street-view';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'street view', 'map', 'google map' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'settings_section',
			[
				'label' => __( 'Street View Settings', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'height',
			[
				'label' => __( 'Map Height', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'number',
				'default' => 400
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		if ( !isset( $property->id ) ) {
			return;
		}

		$attributes = array();
		if ( isset($settings['height']) && $settings['height'] != '' )
		{
			$attributes['height'] = $settings['height'];
		}

		get_property_street_view($attributes);

	}

}