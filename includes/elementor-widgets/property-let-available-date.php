<?php
/**
 * Elementor Property Let Date Available From Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Let_Available_Date_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-let-available-date';
	}

	public function get_title() {
		return __( 'Let Available From', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'available from', 'available date', 'let date' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Available Date', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-calendar',
					'library' => 'solid',
				],
			]
		);

		$this->add_control(
			'before',
			[
				'label' => __( 'Before', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Available From', 'propertyhive' ),
			]
		);

		$this->add_control(
			'after',
			[
				'label' => __( 'After', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Available Date', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-widget-let-available-date',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
				    'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-let-available-date' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} .elementor-widget-let-available-date' => 'text-align: {{VALUE}}',
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

		if ( $property->department != 'residential-lettings' && ph_get_custom_department_based_on( $property->department ) != 'residential-lettings' && $property->department != 'rooms' )
		{
			return;
		}

		if ( $property->available_date == '' )
		{
			return;
		}

        echo '<div class="elementor-widget-let-available-date">';
        if ( isset($settings['icon']) && !empty($settings['icon']) )
        {
        	\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
        	echo ' ';
        }
        if ( isset($settings['before']) && !empty($settings['before']) )
        {
        	echo $settings['before'] . ' ';
        }
        echo $property->get_available_date();
        if ( isset($settings['after']) && !empty($settings['after']) )
        {
        	echo ' ' . $settings['after'];
        }
        echo '</div>';

	}

}