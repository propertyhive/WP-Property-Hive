<?php
/**
 * Elementor Property Search Form Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Search_Form_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-search-form';
	}

	public function get_title() {
		return __( 'Search Form', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'search', 'form' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Search Form', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$description = '';
		if ( class_exists('PH_Template_Assistant') )
		{
			$description = __( 'Search forms can be managed from within \'<a href="' . admin_url('/admin.php?page=ph-settings&tab=template-assistant&section=search-forms') . '" target="_blank">Property Hive > Settings > Template Assistant > Search Forms</a>\'', 'propertyhive' );
		}

		$this->add_control(
			'form_id',
			[
				'type' => \Elementor\Controls_Manager::TEXT,
				'label' => esc_html__( 'Form ID', 'propertyhive' ),
				'placeholder' => esc_html__( 'e.g. default', 'propertyhive' ),
				'default' => 'default',
				'description' => $description
			]
		);

		$departments = ph_get_departments();

        $department_options = array();

        foreach ( $departments as $key => $value )
        {
            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
            {
                $department_options[$key] = $value;
            }
        }
		$this->add_control(
			'default_department',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__( 'Default Department', 'propertyhive' ),
				'options' => $department_options,
				'default' => get_option( 'propertyhive_primary_department' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'label_style_section',
			[
				'label' => __( 'Labels', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'label' => __( 'Label Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .property-search-form label',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => __( 'Label Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .property-search-form label' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'label_width',
			[
				'label' => esc_html__( 'Label Width', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Default', 'propertyhive' ),
					'block' => esc_html__( 'Full Width (100%)', 'propertyhive' ),
					'inline' => esc_html__( 'Inline (auto)', 'propertyhive' ),
				],
				'selectors' => [
					'{{WRAPPER}} .property-search-form label' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'label_padding',
			[
				'label' => __( 'Label Padding', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .property-search-form label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'label_margin',
			[
				'label' => __( 'Label Margin', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .property-search-form label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'button_style_section',
			[
				'label' => __( 'Search Button', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'label' => __( 'Button Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .property-search-form input[type=\'submit\']',
			]
		);

		$this->add_control(
			'button_color',
			[
				'label' => __( 'Button Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .property-search-form input[type=\'submit\']' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .property-search-form input[type=\'submit\']',
			]
		);

		$this->add_control(
			'button_padding',
			[
				'label' => __( 'Button Padding', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} .property-search-form input[type=\'submit\']' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .property-search-form input[type=\'submit\']' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		echo do_shortcode('[property_search_form id="' . ( ( isset($settings['form_id']) && !empty($settings['form_id']) ) ? $settings['form_id'] : 'default' ) . '"' . ( ( isset($settings['default_department']) && !empty($settings['default_department']) ) ? ' default_department="' . $settings['default_department'] . '"' : '' ) . ']');
	}
}