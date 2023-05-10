<?php
/**
 * Elementor Property Search Form Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Search_Form_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-search-form';
	}

	public function get_title() {
		return __( 'Search Form', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-magnifying-glass';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'search', 'form' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Search Form', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$description = '';
		if ( class_exists('PH_Template_Assistant') )
		{
			$description = __( 'Search forms can be managed from within \'<a href="' . admin_url('/admin.php?page=ph-settings&tab=template-assistant&section=search-forms') . '" target="_blank">Property Hive > Settings > Template Assistant > Search Forms</a>\'', 'propertyhive' );
		}

		$this->add_control(
			'id',
			[
				'type' => \Elementor\Controls_Manager::TEXT,
				'label' => esc_html__( 'Form ID', 'textdomain' ),
				'placeholder' => esc_html__( 'e.g. default', 'textdomain' ),
				'default' => 'default',
				'description' => $description
			]
		);

		/*$this->add_control(
			'display',
			[
				'label' => __( 'Display As', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'list' => [
						'title' => __( 'List', 'plugin-domain' ),
						'icon' => 'fa fa-list',
					],
					'buttons' => [
						'title' => __( 'Buttons', 'plugin-domain' ),
						'icon' => 'fa fa-ellipsis-h',
					],
				],
				'default' => 'list',
				'toggle' => false,
			]
		);*/

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		/*if ( isset($settings['display']) && $settings['display'] == 'buttons' )
		{
			echo '<style type="text/css">';
			echo '.property_actions ul { list-style-type:none; margin:0; padding:0; }';
			echo '.property_actions ul li { display:inline-block; }';
			echo '.property_actions ul li a { display:block; }';
			echo '</style>';
		}*/

		echo do_shortcode('[property_search_form id="' . ( ( isset($settings['id']) && !empty($settings['id']) ) ? $settings['id'] : 'default' ) . '"]');
	}
}