<?php
/**
 * Elementor Property Search Result Count Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Search_Result_Count_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-search-result-count';
	}

	public function get_title() {
		return __( 'Search Result Count', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-list-ol';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'search', 'count' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Search Result Count', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .propertyhive-result-count',
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
					'{{WRAPPER}} .propertyhive-result-count' => 'color: {{VALUE}}',
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
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'propertyhive' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'propertyhive' ),
						'icon' => 'fa fa-align-right',
					],
				],
				'default' => 'left',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .propertyhive-result-count' => 'text-align: {{VALUE}}',
				],
			]
		);
		

		$this->end_controls_section();

	}

	protected function render() {

		global $wp_query;

		$settings = $this->get_settings_for_display();

		if ( is_post_type_archive('property') )
		{
			propertyhive_result_count();
		}
		else
		{
			echo '<p class="propertyhive-result-count">Result count to appear here</p>';
		}
	}
}