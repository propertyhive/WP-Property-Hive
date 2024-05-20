<?php
/**
 * Bricks Builder Property Map Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Map_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-map';
  	public $icon         = 'fas fa-map';
  	public $scripts      = ['initialize_property_map'];

	public function get_label() 
	{
	    return esc_html__( 'Map', 'propertyhive' );
	}

	// Enqueue element styles and scripts
  	public function enqueue_scripts()
  	{
  		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

    	if ( get_option('propertyhive_maps_provider') == 'osm' )
		{
			$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/js/leaflet/';

			wp_register_style('leaflet', $assets_path . 'leaflet.css', array(), '1.9.4');
		    wp_enqueue_style('leaflet');

			wp_register_script('leaflet', $assets_path . 'leaflet.js', array(), '1.9.4', false);
		    wp_enqueue_script('leaflet');
		}
		else
		{
			$api_key = get_option('propertyhive_google_maps_api_key');
		    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
		    wp_enqueue_script('googlemaps');
		}
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
		$this->controls['height'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Height', 'propertyhive' ),
	      	'type' => 'number',
	      	'default' => 400
	    ];

	    $this->controls['zoom'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Zoom', 'propertyhive' ),
	      	'type' => 'number',
	      	'min' => 1,
	      	'max' => 20,
	      	'default' => 14,
	    ];

	    $this->controls['scrollwheel'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Scrollwheel Zoom', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => array(
				'true'  => __( 'True', 'propertyhive' ),
				'false' => __( 'False', 'propertyhive' ),
			),
	      	'default' => 'true',
	    ];
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		if ( $property->latitude == '' || $property->longitude == '' || $property->latitude == '0' || $property->longitude == '0' )
		{
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			$attributes = array();
			if ( isset($this->settings['height']) && $this->settings['height'] != '' )
			{
				$attributes['height'] = $this->settings['height'];
			}
			if ( isset($this->settings['zoom']) && isset($this->settings['zoom']) && $this->settings['zoom'] != '' )
			{
				$attributes['zoom'] = $this->settings['zoom'];
			}
			if ( isset($this->settings['scrollwheel']) && $this->settings['scrollwheel'] != '' )
			{
				$attributes['scrollwheel'] = $this->settings['scrollwheel'];
			}

			get_property_map($attributes);

		echo '</div>';
	}
}