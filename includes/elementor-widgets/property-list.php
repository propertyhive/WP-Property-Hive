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

		$this->start_controls_section(
			'section_additional',
			[
				'label' => __( 'Additional Filters', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'address_keyword',
			[
				'label' => __( 'Address Keyword', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
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

			if ( isset( $settings['posts_per_page'] ) && !empty( $settings['posts_per_page'] ) )
			{
				$attribute_name = in_array( $settings['base_shortcode'], array( 'recent_properties', 'featured_properties' ) ) ? 'per_page' : 'posts_per_page';

				$attributes[$attribute_name] = $settings['posts_per_page'];
			}

			$attributes_to_add = array(
				'columns',
				'department',
				'orderby',
				'order',
				'pagination',
				'show_order',
				'show_result_count',
				'address_keyword',
			);

			$attributes = $this->add_settings_to_attributes( $settings, $attributes, $attributes_to_add );

			echo do_shortcode('[' . $settings['base_shortcode'] . ' ' . implode(' ', array_map(function($key) use ($attributes)
			{
				return $key . '="' . $attributes[$key] . '"';
			}, array_keys($attributes))) . ']');
		}
	}

	private function add_settings_to_attributes( $settings, $attributes, $attributes_to_add )
	{
		foreach( $attributes_to_add as $attribute )
		{
			if ( isset( $settings[$attribute] ) && !empty( $settings[$attribute] ) )
			{
				$attributes[$attribute] = $settings[$attribute];
			}
		}

		return $attributes;
	}
}