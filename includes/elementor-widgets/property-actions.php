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

		$this->add_responsive_control(
			'button_layout',
			[
				'label' => __( 'Button Layout', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'inline' => __( 'Inline', 'propertyhive' ),
					'equal'  => __( 'Equal Width', 'propertyhive' ),
					'fixed'  => __( 'Fixed Width', 'propertyhive' ),
				],
				'default' => 'inline',
				'toggle' => false,
				'condition' => [
					'display' => 'buttons',
				],
			]
		);

		$this->add_responsive_control(
			'fixed_button_width',
			[
				'label' => __( 'Button Width', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 40,
						'max' => 600,
					],
					'%' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'condition' => [
					'display' => 'buttons',
					'button_layout' => 'fixed',
				],
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

		if ( isset( $settings['display'] ) && 'buttons' === $settings['display'] ) 
		{
			$breakpoints = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();

			$tablet_breakpoint = isset($breakpoints['tablet']) ? $breakpoints['tablet']->get_value() : 1024;
			$mobile_breakpoint = isset($breakpoints['mobile']) ? $breakpoints['mobile']->get_value() : 767;

			echo '<style type="text/css">';
			echo '.property_actions ul { display:flex; flex-wrap:wrap; list-style:none; margin:0; padding:0; }';
			echo '.property_actions ul li { float:none }';
			echo '.property_actions ul li a { display:block; width:100%; box-sizing:border-box; text-align:center; }';

			// Desktop
			$this->output_layout_css( $settings, '' );

			// Tablet
			echo '@media (max-width: ' . $tablet_breakpoint . 'px) {';
			$this->output_layout_css( $settings, '_tablet' );
			echo '}';

			// Mobile
			echo '@media (max-width: ' . $mobile_breakpoint . 'px) {';
			$this->output_layout_css( $settings, '_mobile' );
			echo '}';
			echo '</style>';
		}

		propertyhive_template_single_actions();

	}

	protected function output_layout_css( $settings, $suffix = '' ) {

		$layout_key = 'button_layout' . $suffix;
		$width_key  = 'fixed_button_width' . $suffix;

		$layout = isset( $settings[ $layout_key ] ) ? $settings[ $layout_key ] : '';

		if ( ! $layout && '' !== $suffix && isset( $settings['button_layout'] ) ) {
			$layout = $settings['button_layout'];
		}

		switch ( $layout ) {
			case 'equal':
				echo '.property_actions ul li { flex:1 1 0; }';
				break;

			case 'fixed':
				$size = isset( $settings[ $width_key ]['size'] ) ? $settings[ $width_key ]['size'] : '';
				$unit = isset( $settings[ $width_key ]['unit'] ) ? $settings[ $width_key ]['unit'] : 'px';

				if ( $size ) {
					echo '.property_actions ul li { flex:0 0 ' . esc_attr( $size . $unit ) . '; max-width:' . esc_attr( $size . $unit ) . '; }';
				} else {
					echo '.property_actions ul li { flex:0 0 auto; }';
				}
				break;

			case 'inline':
			default:
				echo '.property_actions ul li { flex:0 0 auto; }';
				break;
		}
	}
}