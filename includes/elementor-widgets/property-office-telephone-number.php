<?php
/**
 * Elementor Property Office Telephone Number Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Office_Telephone_Number_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-office-telephone-number';
	}

	public function get_title() {
		return __( 'Office Telephone Number', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-info';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'office' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Office Telephone Number', 'propertyhive' ),
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
				'selector' => '{{WRAPPER}}',
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
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hyperlink',
			[
				'label' => __( 'Hyperlink', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'propertyhive' ),
				'label_off' => __( 'No', 'propertyhive' ),
				'return_value' => 'yes',
				'default' => 'yes',
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

		if ( $property->office_telephone_number != '' )
		{
			if ( isset($settings['hyperlink']) && $settings['hyperlink'] == 'yes' )
			{
				echo '<a href="tel:' . esc_attr($property->office_telephone_number) . '">';
			}
	        echo esc_html($property->office_telephone_number);
	        if ( isset($settings['hyperlink']) && $settings['hyperlink'] == 'yes' )
			{
				echo '</a>';
			}
	    }

	}

}