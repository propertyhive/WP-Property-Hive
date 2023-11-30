<?php
/**
 * Bricks Builder Property Images Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Images_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-images';
  	public $icon         = 'fas fa-images';
  	public $scripts      = ['ph_init_slideshow'];

	public function get_label() 
	{
	    return esc_html__( 'Images', 'propertyhive' );
	}

	// Enqueue element styles and scripts
  	public function enqueue_scripts()
  	{
  		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

    	wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.7.2', true );
        wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
        wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css', array(), '2.7.2' );
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
		$this->controls['hide_thumbnails'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Hide Thumbnails', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => [
	        	'' => __( 'No', 'propertyhive' ),
				'yes' => __( 'Yes', 'propertyhive' ),
	      	],
	      	//'inline' => true,
	      	//'clearable' => false,
	      	//'pasteStyles' => false,
	      	'default' => '',
	    ];
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) {
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			if ( isset($this->settings['hide_thumbnails']) && 'yes' === $this->settings['hide_thumbnails'] ) 
			{
				remove_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );
			}

			propertyhive_show_property_images();

	    echo '</div>';
	}
}