<?php
/**
 * Elementor Property Address Full Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Address_Full_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-address-full';
	}

	public function get_title() {
		return __( 'Full Address', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-meta-data';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'address' ];
	}

	protected function register_controls() {


		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Full Address', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'note',
			[
				'label' => __( 'Note', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'This widget will output the full address, including name/number and postcode. Use the \'Post Title\' widget to display the public display address', 'propertyhive' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .elementor-widget-address-full',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-address-full' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_align',
			[
				'label' => __( 'Alignment', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'propertyhive' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'propertyhive' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'propertyhive' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'left',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-address-full' => 'text-align: {{VALUE}}',
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

        echo '<div class="elementor-widget-address-full">';

        echo $property->get_formatted_full_address();

        echo '</div>';
	}

}