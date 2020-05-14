<?php
/**
 * Elementor Property Images Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Images_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-images';
	}

	public function get_title() {
		return __( 'Images', 'propertyhive' );
	}

	public function get_icon() {
		return 'fa fa-images';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'images', 'photos', 'gallery', 'slideshow' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Images', 'plugin-name' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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

		propertyhive_show_property_images();

		// On render widget from Editor - trigger the init manually.
		if ( wp_doing_ajax() ) {
			?>
			<script>
		        // The slider being synced must be initialized first
		        //ph_init_slideshow();
			</script>
			<?php
		}

	}

}