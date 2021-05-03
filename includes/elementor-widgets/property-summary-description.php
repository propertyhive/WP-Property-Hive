<?php
/**
 * Elementor Property Summary Description Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Summary_Description_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-summary-description';
	}

	public function get_title() {
		return __( 'Summary Description', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-paragraph';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'summary', 'description', 'excerpt' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Summary Description', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __( 'Show Title', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'propertyhive' ),
				'label_off' => __( 'Hide', 'propertyhive' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __( 'Title Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .summary h4',
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Title Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .summary h4' => 'color: {{VALUE}}',
				],
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'summary_typography',
				'label' => __( 'Summary Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .summary .summary-contents',
			]
		);

		$this->add_control(
			'summary_color',
			[
				'label' => __( 'Summary Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .summary .summary-contents' => 'color: {{VALUE}}',
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

		if ( isset($settings['show_title']) && $settings['show_title'] != 'yes' )
		{
?>
<style type="text/css">
.summary h4 { display:none; }
</style>
<?php
		}

        propertyhive_template_single_summary();

	}

}