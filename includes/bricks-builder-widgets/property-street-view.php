<?php
/**
 * Bricks Builder Property Street View Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Street_View_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-street-view';
  	public $icon         = 'fas fa-street-view';
  	public $scripts      = ['initialize_property_street_view'];

	public function get_label() 
	{
	    return esc_html__( 'Street View', 'propertyhive' );
	}

	// Enqueue element styles and scripts
  	public function enqueue_scripts()
  	{
  		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

    	if ( get_option('propertyhive_maps_provider') == 'osm' )
		{
			
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

		if ( get_option('propertyhive_maps_provider') == 'osm' )
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

			get_property_street_view($attributes);

		echo '</div>';
	}
}