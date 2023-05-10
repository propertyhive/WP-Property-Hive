<?php
/**
 * Elementor Property Search Order Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Search_Order_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-search-order';
	}

	public function get_title() {
		return __( 'Search Order', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-arrow-up-1-9';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'search', 'order', 'sort' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Search Order', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		
		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		propertyhive_catalog_ordering();
	}
}