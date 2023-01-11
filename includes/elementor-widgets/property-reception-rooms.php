<?php
/**
 * Elementor Property Reception Rooms Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Reception_Rooms_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-reception-rooms';
	}

	public function get_title() {
		return __( 'Reception Rooms', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-couch';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'receptions', 'room', 'reception rooms', 'living rooms' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Reception Rooms', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-couch',
					'library' => 'solid',
				],
			]
		);

		$this->add_control(
			'before',
			[
				'label' => __( 'Before', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'after',
			[
				'label' => __( 'After', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Receptions', 'propertyhive' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Reception Rooms', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .elementor-widget-reception-rooms',
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
					'{{WRAPPER}} .elementor-widget-reception-rooms' => 'color: {{VALUE}}',
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

		if ( $property->reception_rooms != '' && $property->reception_rooms != 0 )
		{
	        echo '<div class="elementor-widget-reception-rooms">';
	        if ( isset($settings['icon']) && !empty($settings['icon']) )
	        {
	        	\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
	        	echo ' ';
	        }
	        if ( isset($settings['before']) && !empty($settings['before']) )
	        {
	        	echo $settings['before'] . ' ';
	        }
	        echo $property->reception_rooms;
	        if ( isset($settings['after']) && !empty($settings['after']) )
	        {
	        	echo ' ' . $settings['after'];
	        }
	        echo '</div>';
	    }

	}

}