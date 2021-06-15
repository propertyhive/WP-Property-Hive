<?php

/**
 * Elementor Property List Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_List_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-list';
	}

	public function get_title() {
		return __( 'Property List', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-archive-posts';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'posts', 'list', 'search results' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'propertyhive' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columns', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 8,
				'default' => 2,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => __( 'Posts Per Page', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 100,
				'default' => 10,
			]
		);

		$this->add_control(
			'pagination',
			[
				'label' => __( 'Pagination', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'propertyhive' ),
				'label_off' => __( 'Hide', 'propertyhive' ),
				'return_value' => '1',
				'default' => '',
			]
		);

		$this->add_control(
			'show_order',
			[
				'label' => __( 'Order', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'propertyhive' ),
				'label_off' => __( 'Hide', 'propertyhive' ),
				'return_value' => '1',
				'default' => '',
			]
		);

		$this->add_control(
			'show_result_count',
			[
				'label' => __( 'Result Count', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'propertyhive' ),
				'label_off' => __( 'Hide', 'propertyhive' ),
				'return_value' => '1',
				'default' => '',
			]
		);

		$this->add_control(
			'base_shortcode',
			[
				'label' => __( 'Shortcode', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'properties' => __( 'Properties', 'propertyhive' ),
					'recent_properties' => __( 'Recent Properties', 'propertyhive' ),
					'featured_properties' => __( 'Featured Properties', 'propertyhive' ),
				],
				'default' => 'properties',
			]
		);

		$departments = array( '' => 'All' ) + ph_get_departments();

		$this->add_control(
			'department',
			[
				'label' => __( 'Department', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $departments,
				'default' => '',
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'date' => __( 'Date Put On Market', 'propertyhive' ),
					'price' => __( 'Price', 'propertyhive' ),
					'rand' => __( 'Random', 'propertyhive' ),
					'meta_value_num' => __( 'Meta Key Value', 'propertyhive' ),
				],
				'default' => 'date',
			]
		);

		$this->add_control(
			'meta_key',
			[
				'label' => __( 'Meta Key', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'orderby' => 'meta_value_num',
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'desc' => __( 'Descending', 'propertyhive' ),
					'asc' => __( 'Ascending', 'propertyhive' ),
				],
				'default' => 'desc',
				'conditions' => [
					'terms' => [
						['name' => 'orderby', 'operator' => '!=', 'value' => 'rand'],
					],
				],
			]
		);

		$this->end_controls_section();

		parent::_register_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( isset($settings['base_shortcode']) )
		{
			$attributes = array();

			if ( isset( $settings['columns'] ) && !empty( $settings['columns'] ) )
			{
				$attributes['columns'] = $settings['columns'];
			}

			if ( isset( $settings['posts_per_page'] ) && !empty( $settings['posts_per_page'] ) )
			{
				$attribute_name = in_array( $settings['base_shortcode'], array( 'recent_properties', 'featured_properties' ) ) ? 'per_page' : 'posts_per_page';

				$attributes[$attribute_name] = $settings['posts_per_page'];
			}

			if ( isset( $settings['department'] ) && !empty( $settings['department'] ) )
			{
				$attributes['department'] = $settings['department'];
			}

			if ( isset( $settings['orderby'] ) && !empty( $settings['orderby'] ) )
			{
				$attributes['orderby'] = $settings['orderby'];
			}

			if ( isset( $settings['order'] ) && !empty( $settings['order'] ) )
			{
				$attributes['order'] = $settings['order'];
			}

			if ( isset( $settings['pagination'] ) && !empty( $settings['pagination'] ) )
			{
				$attributes['pagination'] = $settings['pagination'];
			}

			if ( isset( $settings['show_order'] ) && !empty( $settings['show_order'] ) )
			{
				$attributes['show_order'] = $settings['show_order'];
			}

			if ( isset( $settings['show_result_count'] ) && !empty( $settings['show_result_count'] ) )
			{
				$attributes['show_result_count'] = $settings['show_result_count'];
			}

			echo do_shortcode('[' . $settings['base_shortcode'] . ' ' . implode(' ', array_map(function($key) use ($attributes)
			{
				return $key . '="' . $attributes[$key] . '"';
			}, array_keys($attributes))) . ']');
		}
	}

}