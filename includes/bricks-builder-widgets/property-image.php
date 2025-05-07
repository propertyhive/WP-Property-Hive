<?php
/**
 * Bricks Builder Property Image Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Image_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-image';
  	public $icon         = 'fas fa-image';

	public function get_label() 
	{
	    return esc_html__( 'Image', 'propertyhive' );
	}

	public function set_control_groups() 
	{
		/*$this->control_groups['form'] = [
	      	'title' => esc_html__( 'Form', 'propertyhive' ),
	      	'tab' => 'content', // content / style
	    ];*/
	}

	public function set_controls() 
	{
		$this->controls['image_number'] = [ // Unique control identifier (lowercase, no spaces)
      		'tab' => 'content', // Control tab: content/style
      		//'group' => 'form', // Show under control group
      		'label' => esc_html__( 'Image #', 'propertyhive' ), // Control label
      		'type' => 'text', // Control type 
      		'default' => 1
    	];

    	$this->controls['image_size'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Type', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => [
	        	'thumbnail' => __( 'Thumbnail', 'propertyhive' ),
				'medium' => __( 'Medium', 'propertyhive' ),
				'large' => __( 'Large', 'propertyhive' ),
				'full' => __( 'Full', 'propertyhive' ),
	      	],
	      	//'inline' => true,
	      	//'clearable' => false,
	      	//'pasteStyles' => false,
	      	'default' => 'large',
	    ];

	    $this->controls['output_ratio'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Image Ratio', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => [
	        	'' => __( 'Uploaded Ratio', 'propertyhive' ),
				'3:2' => __( '3:2', 'propertyhive' ),
				'4:3' => __( '4:3', 'propertyhive' ),
				'16:9' => __( '16:9', 'propertyhive' ),
				'1:1' => __( 'Square', 'propertyhive' ),
	      	],
	      	//'inline' => true,
	      	//'clearable' => false,
	      	//'pasteStyles' => false,
	      	'default' => 'large',
	    ];

	    do_action( 'propertyhive_bricks_builder_widget_property_image_controls', $this );
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		$root_classes[] = $this->name;

		$settings = $this->settings;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";
	    	
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

	        echo '<div style="background:url(' . esc_url($url) . ') no-repeat center center; background-size:cover; padding-bottom:' . esc_attr($percent) . '">';
		}
		else
		{
			// output <img>
			if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
	        {
	        	$photos = $property->_photo_urls;
	        	if ( isset($photos[$image_number-1]) )
	        	{
	        		echo '<img src="' . esc_url($photos[$image_number-1]['url']) . '" alt="">';
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

		do_action( 'propertyhive_bricks_builder_widget_property_image_render_after', $settings, $property );

	    echo '</div>';
	}
}