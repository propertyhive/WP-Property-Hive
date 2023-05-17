<?php
/**
 * Elementor Property Image Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Image_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-image';
	}

	public function get_title() {
		return __( 'Image', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-image';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'images', 'photos', 'image', 'photo' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Image', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'image_number',
			[
				'label' => __( 'Image #', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
			]
		);

		$this->add_control(
			'image_size',
			[
				'label' => __( 'Image Size', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'thumbnail' => __( 'Thumbnail', 'propertyhive' ),
					'medium' => __( 'Medium', 'propertyhive' ),
					'large' => __( 'Large', 'propertyhive' ),
					'full' => __( 'Full', 'propertyhive' ),
				],
				'default' => 'large',
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

		$image_number = 1;
		if ( isset($settings['image_number']) && $settings['image_number'] != '' && is_numeric($settings['image_number']) )
		{
			$image_number = (int)$settings['image_number'];
		}

		if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
        	$photos = $property->_photo_urls;
        	if ( isset($photos[$image_number-1]) )
        	{
        		echo '<img src="' . $photos[$image_number-1]['url'] . '" alt="">';
        	}
        }
        else
        {
			$gallery_attachment_ids = $property->get_gallery_attachment_ids();

			if ( isset($gallery_attachment_ids[$image_number-1]) )
			{
				echo wp_get_attachment_image( $gallery_attachment_ids[$image_number-1], $settings['image_size'] );
			}
		}
	}

}