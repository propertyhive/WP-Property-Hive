<?php
/**
 * Elementor Property Meta Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Meta_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-meta';
	}

	public function get_title() {
		return __( 'Property Meta', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'meta' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Property Meta', 'propertyhive' ),
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
				'selector' => '{{WRAPPER}} .property_meta li',
			]
		);

		$this->add_control(
			'list_color',
			[
				'label' => __( 'Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
				    'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .property_meta li' => 'color: {{VALUE}}',
				],
			]
		);

		/*$this->add_control(
			'icon',
			[
				'label' => __( 'List Icon', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-check',
					'library' => 'solid',
				],
			]
		);*/

		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		if ( !isset($property->id) ) {
			return;
		}

		propertyhive_template_single_meta();

	}

}