<?php
/**
 * Elementor Property EPCs Link Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_EPCs_Link_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-epcs-link';
	}

	public function get_title() {
		return __( 'EPCs Link', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'epc', 'epcs' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'EPCs', 'propertyhive' ),
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

		if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        {
        	$epc_urls = $property->epc_urls;
            if ( !is_array($epc_urls) ) { $epc_urls = array(); }

            if ( !empty($epc_urls) )
			{
				$i = 0;
				foreach ( $epc_urls as $epc )
				{
					$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp' );
					$image = false;
					foreach ( $image_extensions as $image_extension )
					{
						if ( strpos(strtolower($epc['url']), '.' . $image_extension) )
						{
							$image = true;
						}
					}
					if ( $image )
					{
						echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . esc_url($epc['url']) . '" data-fancybox="epcs" rel="nofollow">' . esc_html( ( count($epc_urls) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) ) . '</a>';
						++$i;
					}
					else
					{
						echo '<a href="' . esc_url($epc['url']) . '" rel="nofollow" target="_blank">' . esc_html( ( count($epc_urls) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) ) . '</a>';
					}
				}
			}
        }
        else
        {
			$epc_attachment_ids = $property->get_epc_attachment_ids();

			if ( !empty($epc_attachment_ids) )
			{
				$i = 0;
				foreach ( $epc_attachment_ids as $attachment_id )
				{
					echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="epc" rel="nofollow">' . esc_html( ( count($epc_attachment_ids) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) ) . '</a>';
					++$i;
				}
			}
		}

	}

}