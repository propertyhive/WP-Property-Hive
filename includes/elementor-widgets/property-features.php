<?php
/**
 * Elementor Property Features Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Features_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-features';
	}

	public function get_title() {
		return __( 'Features', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-list';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'features', 'key', 'bullet' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Features', 'propertyhive' ),
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
				'selector' => '{{WRAPPER}} .features h4',
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
					'{{WRAPPER}} .features h4' => 'color: {{VALUE}}',
				],
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'features_typography',
				'label' => __( 'List Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .features li',
			]
		);

		$this->add_control(
			'list_color',
			[
				'label' => __( 'List Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'scheme' => [
					'type' => \Elementor\Core\Schemes\Color::get_type(),
					'value' => \Elementor\Core\Schemes\Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .features li' => 'color: {{VALUE}}',
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

		if ( isset($settings['show_title']) && $settings['show_title'] != 'yes' )
		{
?>
<style type="text/css">
.features h4 { display:none; }
</style>
<?php
		}

		propertyhive_template_single_features();
	}

}