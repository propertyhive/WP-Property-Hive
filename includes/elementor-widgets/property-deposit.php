<?php
/**
 * Elementor Property Deposit Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Deposit_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-deposit';
	}

	public function get_title() {
		return __( 'Deposit', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'deposit' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Deposit', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-pound-sign',
					'library' => 'solid',
				],
			]
		);

		$this->add_control(
			'before',
			[
				'label' => __( 'Before', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Deposit', 'propertyhive' ),
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
				'selector' => '{{WRAPPER}} .elementor-widget-deposit',
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
					'{{WRAPPER}} .elementor-widget-deposit' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} .elementor-widget-deposit' => 'text-align: {{VALUE}}',
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

		if ( $property->department != 'residential-lettings' && ph_get_custom_department_based_on( $property->department ) != 'residential-lettings' )
		{
			return;
		}

		if ( $property->get_formatted_deposit() == '' )
		{
			return;
		}

        echo '<div class="elementor-widget-deposit>';
        if ( isset($settings['icon']) && !empty($settings['icon']) )
        {
        	\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
        	echo ' ';
        }
        if ( isset($settings['before']) && !empty($settings['before']) )
        {
        	echo $settings['before'] . ' ';
        }
        echo $property->get_formatted_deposit();
        if ( isset($settings['after']) && !empty($settings['after']) )
        {
        	echo ' ' . $settings['after'];
        }
        echo '</div>';

	}

}