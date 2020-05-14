<?php
/**
 * Elementor Property Price Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Price_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-price';
	}

	public function get_title() {
		return __( 'Price', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-pound-sign';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'price' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Price', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .price',
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __( 'Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .price' => 'color: {{VALUE}}',
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

		propertyhive_template_single_price();

	}

}