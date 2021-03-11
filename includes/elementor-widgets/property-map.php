<?php
/**
 * Elementor Property Map Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Map_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-map';
	}

	public function get_title() {
		return __( 'Map', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-map-marked-alt';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'map', 'location', 'google map' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'settings_section',
			[
				'label' => __( 'Map Settings', 'propertyhive' ),
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

		$this->add_control(
			'zoom',
			[
				'label' => __( 'Zoom', 'elementor' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 14,
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'scrollwheel',
			[
				'label' => __( 'Scrollwheel Zoom', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'true',
				'options' => array(
					'true'  => __( 'True', 'propertyhive' ),
					'false' => __( 'False', 'propertyhive' ),
				),
				'separator' => 'before',
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
		if ( isset($settings['zoom']) && isset($settings['zoom']['size']) && $settings['zoom']['size'] != '' )
		{
			$attributes['zoom'] = $settings['zoom']['size'];
		}
		if ( isset($settings['scrollwheel']) && $settings['scrollwheel'] != '' )
		{
			$attributes['scrollwheel'] = $settings['scrollwheel'];
		}

		get_property_map($attributes);

	}

}