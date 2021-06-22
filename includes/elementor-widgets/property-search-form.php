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
		return __( 'Property Search Form', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-site-search';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'posts', 'search', 'search form' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'propertyhive' ),
			]
		);

		$departments = array( '' => '' ) + ph_get_departments();

		$this->add_control(
			'search_form_id',
			[
				'label' => __( 'Search Form ID', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'default',
			]
		);

		$this->add_control(
			'default_department',
			[
				'label' => __( 'Default Department', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $departments,
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$attributes = array();

		if ( isset( $settings['search_form_id'] ) && !empty( $settings['search_form_id'] ) )
		{
			$attributes['id'] = $settings['search_form_id'];
		}

		if ( isset( $settings['default_department'] ) && !empty( $settings['default_department'] ) )
		{
			$attributes['default_department'] = $settings['default_department'];
		}

		echo do_shortcode( '[property_search_form ' . implode(' ', array_map(function($key) use ($attributes)
			{
				return $key . '="' . $attributes[$key] . '"';
			}, array_keys($attributes))) . ']' );
	}
}