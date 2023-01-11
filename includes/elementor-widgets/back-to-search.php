<?php
/**
 * Elementor Back To Search Widget.
 *
 * @since 1.0.0
 */
class Elementor_Back_To_Search_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'back-to-search';
	}

	public function get_title() {
		return __( 'Back To Search', 'propertyhive' );
	}

	public function get_icon() {
		return 'fas fa-arrow-left';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'back' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Back To Search', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'note',
			[
				'label' => __( 'Note', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'This requires step 1 and 2 from <a href="https://docs.wp-property-hive.com/developer-guide/miscellaneous-and-snippets/add-back-to-search-results-link-to-property-details-page/" target="_blank">this documentation</a> to be added to your theme for this to work correctly. If not done then the Back To Search link will just always link back to the results page but won\'t retain any search criteria set.', 'propertyhive' ),
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-arrow-left',
					'library' => 'solid',
				],
			]
		);

		$this->add_control(
			'label',
			[
				'label' => __( 'Label', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Back To Search', 'plugin-domain' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'back_to_search_typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .back-to-search',
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
					'{{WRAPPER}} .back-to-search' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} .back-to-search' => 'text-align: {{VALUE}}',
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

		echo '<div class="back-to-search">';
			if ( isset($_SESSION['last_search']) && $_SESSION['last_search'] != '' ) 
			{
				echo '<a href="' . $_SESSION['last_search'] . '">';
			}
			else
			{
				echo '<a href="' . get_permalink(ph_get_page_id( 'search_results' )) . '">';
			}
			if ( isset($settings['icon']) && !empty($settings['icon']) )
	        {
	        	\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
	        	echo ' ';
	        }
			echo $settings['label'];
		echo '</a>';
		echo '</div>';

	}

}