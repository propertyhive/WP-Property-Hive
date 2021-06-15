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

		$this->add_control(
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
					'floor_area' => __( 'Floor Area', 'propertyhive' ),
				],
				'default' => 'date',
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

		$this->add_control(
			'bedrooms',
			[
				'label' => __( 'Bedrooms', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
			]
		);

		$this->add_control(
			'minimum_price',
			[
				'label' => __( 'Minimum Price', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'conditions' => [
					'terms' => [
						['name' => 'department', 'operator' => '!=', 'value' => 'commercial'],
					],
				],
			]
		);

		$this->add_control(
			'maximum_price',
			[
				'label' => __( 'Maximum Price', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'conditions' => [
					'terms' => [
						['name' => 'department', 'operator' => '!=', 'value' => 'commercial'],
					],
				],
			]
		);

		$this->add_control(
			'commercial_for_sale',
			[
				'label' => __( 'For Sale (Commercial)', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'department' => [ '', 'commercial' ] ],
			]
		);

		$this->add_control(
			'commercial_to_rent',
			[
				'label' => __( 'To Rent (Commercial)', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [ 'department' => [ '', 'commercial' ] ],
			]
		);

		$this->add_control(
			'country',
			[
				'label' => __( 'Country', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$availabilities = $this->get_terms_array( 'availability' );

		$this->add_control(
			'availability_id',
			[
				'label' => __( 'Availability', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $availabilities,
				'multiple' => true,
			]
		);

		$marketing_flags = $this->get_terms_array( 'marketing_flag' );

		$this->add_control(
			'marketing_flag_id',
			[
				'label' => __( 'Has Marketing Flag', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $marketing_flags,
				'multiple' => true,
			]
		);

		$property_types = $this->get_terms_array( 'property_type' );

		$this->add_control(
			'property_type_id',
			[
				'label' => __( 'Property Type', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $property_types,
				'default' => array( '' ),
				'multiple' => true,
			]
		);

		$locations = $this->get_terms_array( 'location' );

		$this->add_control(
			'location_id',
			[
				'label' => __( 'Location', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $locations,
				'multiple' => true,
			]
		);

		$offices = array();

		$args = array(
			'post_type' => 'office',
			'nopaging' => true,
			'orderby' => 'title',
			'order' => 'ASC'
		);

		$office_query = new WP_Query( $args );

		if ( $office_query->have_posts() )
		{
			while ( $office_query->have_posts() )
			{
				$office_query->the_post();

				$offices[get_the_ID()] = get_the_title();
			}
		}
		wp_reset_postdata();

		$this->add_control(
			'office_id',
			[
				'label' => __( 'Office', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $offices,
				'multiple' => true,
			]
		);

		$args = array(
			'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
		);

		$args = array(
			'orderby'                 => 'display_name',
			'order'                   => 'ASC',
			'role__not_in'            => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') ),
		);

		$wp_users = get_users( $args );

		if ( !empty( $wp_users ))
		{
			foreach ($wp_users as $wp_user)
			{
				$negotiators[$wp_user->ID] = $wp_user->display_name;
			}
		}

		$this->add_control(
			'negotiator_id',
			[
				'label' => __( 'Negotiator', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $negotiators,
				'multiple' => true,
			]
		);

		$this->add_control(
			'no_results_output',
			[
				'label' => __( 'No Results Output', 'propertyhive' ),
				'type' => Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => 'e.g. No Properties Found'
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

			if ( isset( $settings['orderby'] ) && !empty( $settings['orderby'] ) )
			{
				if ( $settings['orderby'] == 'price' )
				{
					$attributes['orderby'] = 'meta_value_num';
					$attributes['meta_key'] = '_price_actual';
				}
				elseif ( $settings['orderby'] == 'floor_area' )
				{
					$attributes['orderby'] = 'meta_value_num';
					$attributes['meta_key'] = '_floor_area_from_sqft';
				}
				else
				{
					$attributes['orderby'] = $settings['orderby'];
				}
			}

			$attributes_to_add = array(
				'columns',
				'department',
				'order',
				'pagination',
				'show_order',
				'show_result_count',
				'address_keyword',
				'bedrooms',
				'minimum_price',
				'maximum_price',
				'commercial_for_sale',
				'commercial_to_let',
				'country',
				'availability_id',
				'marketing_flag_id',
				'property_type_id',
				'location_id',
				'negotiator_id',
				'no_results_output',
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
				$attribute_value = is_array( $settings[$attribute] ) ? implode( ',', $settings[$attribute]) : $settings[$attribute];
				$attributes[$attribute] = $attribute_value;
			}
		}

		return $attributes;
	}

	private function get_terms_array( $term_type )
	{
		$terms_array = array();

		$args = array(
			'hide_empty' => false,
			'parent' => 0
		);
		$terms = get_terms( $term_type, $args );

		if ( !empty( $terms ) && !is_wp_error( $terms ) )
		{
			foreach ($terms as $term)
			{
				$terms_array[$term->term_id] = $term->name;
			}
		}

		return $terms_array;
	}
}