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

		$this->add_control(
			'output_ratio',
			[
				'label' => __( 'Image Ratio', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Uploaded Ratio', 'propertyhive' ),
					'3:2' => __( '3:2', 'propertyhive' ),
					'4:3' => __( '4:3', 'propertyhive' ),
					'16:9' => __( '16:9', 'propertyhive' ),
					'1:1' => __( 'Square', 'propertyhive' ),
				],
				'default' => ''
			]
		);

		do_action( 'propertyhive_elementor_widget_property_image_controls', $this );

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

		$output_ratio = isset($settings['output_ratio']) ? $settings['output_ratio'] : '';

		if ( $output_ratio != '' )
		{
			// output div with image as background
			$url = '';
			if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	        {
	        	$photos = $property->_photo_urls;
	        	if ( isset($photos[$image_number-1]) )
	        	{
	        		$url = $photos[$image_number-1]['url'];
	        	}
	        }
	        else
	        {
				$gallery_attachment_ids = $property->get_gallery_attachment_ids();

				if ( isset($gallery_attachment_ids[$image_number-1]) )
				{
					$url = wp_get_attachment_image_src( $gallery_attachment_ids[$image_number-1], $settings['image_size'] );
					$url = $url[0];
				}
			}

			// convert ratio to percentage
	        $numbers = explode(':', $output_ratio);
	        $percent = ( ( (int)$numbers[1] / (int)$numbers[0] ) * 100 ) . '%';

	        echo '<div style="background:url(' . $url . ') no-repeat center center; background-size:cover; padding-bottom:' . $percent . '">';
		}
		else
		{
			// output <img>
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

		do_action( 'propertyhive_elementor_widget_property_image_render_after', $settings, $property );
	}

}