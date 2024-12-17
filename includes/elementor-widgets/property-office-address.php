<?php
/**
 * Elementor Property Office Address Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Office_Address_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-office-address';
	}

	public function get_title() {
		return __( 'Office Address', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-info';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'office' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Office Address', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}}',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
				    'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
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

		if ( $property->office_address != '' )
		{
	        echo $property->office_address;
	    }

	}

}