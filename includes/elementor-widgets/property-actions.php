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
		return 'eicon-button';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'actions', 'buttons', 'enquiry' ];
	}

	protected function register_controls() {

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
				'label' => __( 'Display As', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'list' => [
						'title' => __( 'List', 'propertyhive' ),
						'icon' => 'eicon-editor-list-ul',
					],
					'buttons' => [
						'title' => __( 'Buttons', 'propertyhive' ),
						'icon' => 'eicon-button',
					],
				],
				'default' => 'list',
				'toggle' => false,
			]
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		// Normal tab
		$this->start_controls_tab(
			'button_style_normal',
			[
				'label' => __( 'Normal', 'propertyhive' ),
				'condition' => [
					'display' => 'buttons',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => __( 'Background Colour', 'propertyhive' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text Colour', 'propertyhive' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover tab
		$this->start_controls_tab(
			'button_style_hover',
			[
				'label' => __( 'Hover', 'propertyhive' ),
				'condition' => [
					'display' => 'buttons',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label' => __( 'Background Colour', 'propertyhive' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_hover_color',
			[
				'label' => __( 'Text Colour', 'propertyhive' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .property_actions ul li a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

	$this->end_controls_tabs();

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