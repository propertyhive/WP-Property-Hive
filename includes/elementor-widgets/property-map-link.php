<?php
/**
 * Elementor Property Map Link Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Map_Link_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-map-link';
	}

	public function get_title() {
		return __( 'Map Link', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'map', 'location', 'google map' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Map Link Settings', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'map_link_type',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__( 'Link Type', 'propertyhive' ),
				'options' => [
					'_blank' => 'Open map in new window',
					'embedded' => 'Open embedded map in lightbox',
					'iframe' => 'Open iframe map in lightbox',
				],
				'default' => '_blank',
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
				'selector' => '{{WRAPPER}} a',
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
					'{{WRAPPER}} a' => 'color: {{VALUE}}',
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
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'propertyhive' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'propertyhive' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
				    'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} a' => 'background: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'padding',
			[
				'label' => __( 'Link Padding', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}} a' => 'display:inline-block; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => 5,
					'right' => 5,
					'bottom' => 5,
					'left' => 5,
					'isLinked' => true,
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

		if ( $property->latitude == '' || $property->longitude == '' || $property->latitude == '0' || $property->longitude == '0' )
		{
			return;
		}

		$link_type = ( isset($settings['map_link_type']) && !empty($settings['map_link_type']) ) ? $settings['map_link_type'] : '_blank';

		switch ($link_type)
		{
			case "_blank":
			{
				echo '<a href="https://www.google.com/maps/?q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '" target="_blank">' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
				break;
			}
			case "embedded":
			{
				echo '<a href="#map_lightbox" data-fancybox>' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
		
				echo '<div id="map_lightbox" style="display:none; width:90%; max-width:800px;">';
		   	 		echo do_shortcode('[property_map]');
		    	echo '</div>';
				break;
			}
			case "iframe":
			{
				echo '<a 
				    href="#" 
				    data-fancybox 
				    data-type="iframe" 
				    data-src="https://maps.google.com/?output=embed&amp;f=q&amp;q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;layer=t&amp;hq=&amp;t=m&amp;z=15"
				>' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
				break;
			}
		}
		
	}
}