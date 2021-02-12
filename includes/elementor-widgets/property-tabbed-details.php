<?php
/**
 * Elementor Property Tabbed Details Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Tabbed_Details_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-tabbed-details';
	}

	public function get_title() {
		return __( 'Tabbed Details', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-tabs';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'tab', 'tabs', 'tabbed' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_tabs',
			[
				'label' => __( 'Tabs', 'propertyhive' ),
			]
		);

		$this->add_control(
			'type',
			[
				'label' => __( 'Type', 'elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => __( 'Horizontal', 'elementor' ),
					'vertical' => __( 'Vertical', 'elementor' ),
				],
				'prefix_class' => 'elementor-widget-tabs elementor-tabs-view-',
			]
		);

		$this->add_control(
			'property_tabs',
			[
				'label' => __( 'Tabs Items', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'default' => [
					[
						'tab_title' => __( 'Details', 'propertyhive' ),
						'tab_display' => array('features', 'summary_description', 'full_description'),
					],
					[
						'tab_title' => __( 'Floorplan', 'propertyhive' ),
						'tab_display' => array('floorplan'),
					],
					[
						'tab_title' => __( 'Brochure', 'propertyhive' ),
						'tab_display' => array('brochure'),
					],
					[
						'tab_title' => __( 'EPC', 'propertyhive' ),
						'tab_display' => array('epc'),
					],
					[
						'tab_title' => __( 'Virtual Tour', 'propertyhive' ),
						'tab_display' => array('virtual_tour'),
					],
					[
						'tab_title' => __( 'Map View', 'propertyhive' ),
						'tab_display' => array('map'),
					],
					[
						'tab_title' => __( 'Street View', 'propertyhive' ),
						'tab_display' => array('street_view'),
					],
					[
						'tab_title' => __( 'Make Enquiry', 'propertyhive' ),
						'tab_display' => array('make_enquiry'),
					],
				],
				'fields' => [
					[
						'name' => 'tab_title',
						'label' => __( 'Title', 'elementor' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'Tab Title', 'elementor' ),
						'placeholder' => __( 'Tab Title', 'elementor' ),
						'label_block' => true,
					],
					[
						'name' => 'tab_display',
						'label' => __( 'Display', 'elementor' ),
						'type' => \Elementor\Controls_Manager::SELECT2,
						'multiple' => true,
						'default' => array(),
						'label_block' => true,
						'options' => apply_filters( 'propertyhive_elementor_tabbed_details_display_options', array(
							'features' => __( 'Features', 'propertyhive' ),
							'summary_description' => __( 'Summary Description', 'propertyhive' ),
							'full_description' => __( 'Full Description', 'propertyhive' ),
							'floorplan' => __( 'Floorplans', 'propertyhive' ),
							'brochure' => __( 'Brochures', 'propertyhive' ),
							'epc' => __( 'EPCs', 'propertyhive' ),
							'virtual_tour' => __( 'Virtual Tour Links', 'propertyhive' ),
							'embedded_virtual_tour' => __( 'Virtual Tours Embedded', 'propertyhive' ),
							'map' => __( 'Map View', 'propertyhive' ),
							'street_view' => __( 'Street View', 'propertyhive' ),
							'make_enquiry' => __( 'Make Enquiry Form', 'propertyhive' ),
						) )
					],
				],
				'title_field' => '{{{ tab_title }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_tabs_style',
			[
				'label' => __( 'Tabs', 'elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'navigation_width',
			[
				'label' => __( 'Navigation Width', 'elementor' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-tabs-wrapper' => 'width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'type' => 'vertical',
				],
			]
		);

		$this->add_control(
			'border_width',
			[
				'label' => __( 'Border Width', 'elementor' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-title, {{WRAPPER}} .elementor-tab-title:before, {{WRAPPER}} .elementor-tab-title:after, {{WRAPPER}} .elementor-tab-content, {{WRAPPER}} .elementor-tabs-content-wrapper' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => __( 'Border Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-mobile-title, {{WRAPPER}} .elementor-tab-desktop-title.elementor-active, {{WRAPPER}} .elementor-tab-title:before, {{WRAPPER}} .elementor-tab-title:after, {{WRAPPER}} .elementor-tab-content, {{WRAPPER}} .elementor-tabs-content-wrapper' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-desktop-title.elementor-active' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-tabs-content-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_title',
			[
				'label' => __( 'Title', 'elementor' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tab_color',
			[
				'label' => __( 'Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-title' => 'color: {{VALUE}};',
				],
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_1,
				],
			]
		);

		$this->add_control(
			'tab_active_color',
			[
				'label' => __( 'Active Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-title.elementor-active' => 'color: {{VALUE}};',
				],
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_4,
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'tab_typography',
				'selector' => '{{WRAPPER}} .elementor-tab-title',
				'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
			]
		);

		$this->add_control(
			'heading_content',
			[
				'label' => __( 'Content', 'elementor' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => __( 'Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-content' => 'color: {{VALUE}};',
				],
				'scheme' => [
					'type' => \Elementor\Scheme_Color::get_type(),
					'value' => \Elementor\Scheme_Color::COLOR_3,
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .elementor-tab-content',
				'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_3,
			]
		);

		$this->end_controls_section();

	}

	private function show_tab($item)
	{
		global $property;

		$tab_display = $item['tab_display'];

		$show = false;

		foreach ( $item['tab_display'] as $display )
		{
			switch ( $display )
			{
				case "floorplan":
				{
					$attachment_ids = $property->get_floorplan_attachment_ids();
					if ( !empty($attachment_ids) )
					{
						return true;
					}
					break;
				}
				case "brochure":
				{
					$attachment_ids = $property->get_brochure_attachment_ids();
					if ( !empty($attachment_ids) )
					{
						return true;
					}
					break;
				}
				case "epc":
				{
					$attachment_ids = $property->get_epc_attachment_ids();
					if ( !empty($attachment_ids) )
					{
						return true;
					}
					break;
				}
				case "virtual_tour":
				case "embedded_virtual_tour":
				{
					$virtual_tours = $property->get_virtual_tours();
					if ( !empty($virtual_tours) )
					{
						return true;
					}
					break;
				}
				case "map":
				case "street_view":
				{
					if ( $property->latitude != '' && $property->latitude != '0' && $property->longitude != '' && $property->longitude != '0' )
					{
						return true;
					}
					break;
				}
				default:
				{
					return apply_filters( 'propertyhive_elementor_tabbed_details_show_tab', true, $property, $display );
				}
			}
		}

		return $show;
	}

	protected function render() {

		global $property, $post;
		
		if ( is_null($property) && isset($post->ID) && get_post_type($post->ID) )
		{
			$property = new PH_Property($post->ID);
		}

		$tabs = $this->get_settings( 'property_tabs' );

		$id_int = substr( $this->get_id_int(), 0, 3 );
		?>
		<!--<div class="elementor-widget-tabs elementor-tabs-view-horizontal">-->
			<div class="elementor-tabs" role="tablist">
				<div class="elementor-tabs-wrapper">
					<?php
						foreach ( $tabs as $index => $item )
						{
							$show_tab = $this->show_tab($item);
							if ($show_tab)
							{
								$tab_count = $index + 1;

								$tab_title_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tabs', $index );

								$onclick = '';
								if (in_array('map', $item['tab_display']))
								{
									$onclick .= 'setTimeout(function() { initialize_property_map(); }, 10);';
								}
								if (in_array('street_view', $item['tab_display']))
								{
									$onclick .= 'setTimeout(function() { initialize_property_street_view(); }, 10);';
								}
								$onclick = apply_filters( 'propertyhive_elementor_tabbed_details_tab_onclick', $onclick, $property, $item );

								$this->add_render_attribute( $tab_title_setting_key, [
									'id' => 'elementor-tab-title-' . $id_int . $tab_count,
									'class' => [ 'elementor-tab-title', 'elementor-tab-desktop-title' ],
									'data-tab' => $tab_count,
									'tabindex' => $id_int . $tab_count,
									'role' => 'tab',
									'aria-controls' => 'elementor-tab-content-' . $id_int . $tab_count,
									'onclick' => $onclick
								] );
					?>
						<div <?php echo $this->get_render_attribute_string( $tab_title_setting_key ); ?>><?php echo $item['tab_title']; ?></div>
					<?php
								}
							}
					?>
				</div>
				<div class="elementor-tabs-content-wrapper">
					<?php 
						foreach ( $tabs as $index => $item )
						{
							$show_tab = $this->show_tab($item);
							if ($show_tab)
							{
							$tab_count = $index + 1;

							$tab_content_setting_key = $this->get_repeater_setting_key( 'tab_content', 'tabs', $index );

							$tab_title_mobile_setting_key = $this->get_repeater_setting_key( 'tab_title_mobile', 'tabs', $tab_count );

							$this->add_render_attribute( $tab_content_setting_key, [
								'id' => 'elementor-tab-content-' . $id_int . $tab_count,
								'class' => [ 'elementor-tab-content', 'elementor-clearfix' ],
								'data-tab' => $tab_count,
								'role' => 'tabpanel',
								'aria-labelledby' => 'elementor-tab-title-' . $id_int . $tab_count,
							] );

							$this->add_render_attribute( $tab_title_mobile_setting_key, [
								'class' => [ 'elementor-tab-title', 'elementor-tab-mobile-title' ],
								'tabindex' => $id_int . $tab_count,
								'data-tab' => $tab_count,
								'role' => 'tab',
							] );

							$this->add_inline_editing_attributes( $tab_content_setting_key, 'advanced' );
						?>
						<div <?php echo $this->get_render_attribute_string( $tab_title_mobile_setting_key ); ?>><?php echo $item['tab_title']; ?></div>
						<div <?php echo $this->get_render_attribute_string( $tab_content_setting_key ); ?>><?php
							foreach ( $item['tab_display'] as $display )
							{
								switch ( $display )
								{
									case "features":
									{
										propertyhive_template_single_features();
										break;
									}
									case "summary_description":
									{
										propertyhive_template_single_summary();
										break;
									}
									case "full_description":
									{
										propertyhive_template_single_description();
										break;
									}
									case "floorplan":
									{
										$floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

										if ( !empty($floorplan_attachment_ids) )
										{
											echo '<div class="floorplans">';

												foreach ( $floorplan_attachment_ids as $attachment_id )
												{
													if ( wp_attachment_is_image($attachment_id) )
								                    {
														echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
													}
													else
													{
														echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View Floorplan', 'propertyhive' ) . '</a>';
													}
												}

											echo '</div>';
										}
										break;
									}
									case "brochure":
									{
										$brochure_attachment_ids = $property->get_brochure_attachment_ids();

										if ( !empty($brochure_attachment_ids) )
										{
											echo '<div class="brochures">';

												foreach ( $brochure_attachment_ids as $attachment_id )
												{
													if ( wp_attachment_is_image($attachment_id) )
								                    {
														echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="brochures" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
													}
													else
													{
														echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View Brochure', 'propertyhive' ) . '</a>';
													}
												}

											echo '</div>';
										}
										break;
									}
									case "epc":
									{
										$epc_attachment_ids = $property->get_epc_attachment_ids();

										if ( !empty($epc_attachment_ids) )
										{
											echo '<div class="epc">';

												foreach ( $epc_attachment_ids as $attachment_id )
												{
													if ( wp_attachment_is_image($attachment_id) )
								                    {
														echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="epcs" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
													}
													else
													{
														echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View EPC', 'propertyhive' ) . '</a>';
													}
												}

											echo '</div>';
										}
										break;
									}
									case "virtual_tour":
									{
										$virtual_tours = $property->get_virtual_tours();

										if ( !empty($virtual_tours) )
										{
											echo '<div class="virtual-tours">';

											foreach ( $virtual_tours as $virtual_tour )
											{
												echo '<a href="' . $virtual_tour['url'] . '" target="_blank" rel="nofollow">' . $virtual_tour['label'] . '</a>';
											}

											echo '</div>';
										}
										break;
									}
									case "embedded_virtual_tour":
									{
										$virtual_tours = $property->get_virtual_tours();

										if ( !empty($virtual_tours) )
										{
											echo '<div class="virtual-tours">';

											foreach ( $virtual_tours as $virtual_tour )
											{
												echo '<iframe src="' . $virtual_tour['url'] . '" height="500" width="100%" allowFullScreen frameborder="0"></iframe>';
											}

											echo '</div>';
										}
										break;
									}
									case "map":
									{
										echo do_shortcode('[property_map scrollwheel="false"]');
										break;
									}
									case "street_view":
									{
										echo do_shortcode('[property_street_view]');
										break;
									}
									case "make_enquiry":
									{
										propertyhive_enquiry_form();
										break;
									}
								}

								do_action( 'propertyhive_elementor_tabbed_details_tab_contents', $property, $display );
							}
						?></div>
					<?php
							}
						}
					?>
				</div>
			</div>
		<!--</div>-->

		<script>
			jQuery(document).ready(function()
			{
				jQuery('[data-widget_type=\'property-tabbed-details.default\']').each(function()
				{
					jQuery(this).attr('data-widget_type', 'tabs.default');
				});
			});
		</script>
		<?php

	}

}