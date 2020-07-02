<?php
/**
 * Elementor Property Actions Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Actions_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-actions';
	}

	public function get_title() {
		return __( 'Actions', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-hand-pointer';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'actions', 'buttons', 'enquiry' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Actions', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'display',
			[
				'label' => __( 'Display As', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'list' => [
						'title' => __( 'List', 'plugin-domain' ),
						'icon' => 'fa fa-list',
					],
					'buttons' => [
						'title' => __( 'Buttons', 'plugin-domain' ),
						'icon' => 'fa fa-ellipsis-h',
					],
				],
				'default' => 'list',
				'toggle' => false,
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => __( 'Button Background Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a' => 'background: {{VALUE}}',
				],
				'condition' => [
		            'display' => 'buttons'
		        ],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Button Text Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a' => 'color: {{VALUE}}',
				],
				'condition' => [
		            'display' => 'buttons'
		        ],
			]
		);

		$this->add_control(
			'button_padding',
			[
				'label' => __( 'Button Padding', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => 5,
					'right' => 5,
					'bottom' => 5,
					'left' => 5,
					'isLinked' => true,
				],
				'condition' => [
		            'display' => 'buttons'
		        ],
			]
		);

		$this->add_control(
			'button_margin',
			[
				'label' => __( 'Button Margin', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
					'isLinked' => true,
				],
				'condition' => [
		            'display' => 'buttons'
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

		if ( isset($settings['display']) && $settings['display'] == 'buttons' )
		{
			echo '<style type="text/css">';
			echo '.property_actions ul { list-style-type:none; margin:0; padding:0; }';
			echo '.property_actions ul li { display:inline-block; }';
			echo '.property_actions ul li a { display:block; }';
			echo '</style>';
		}

		propertyhive_template_single_actions();

	}
}