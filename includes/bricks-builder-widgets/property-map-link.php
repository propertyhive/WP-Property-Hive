<?php
/**
 * Bricks Builder Property Map Link Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Map_Link_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-map-link';
  	public $icon         = 'fas fa-map';

	public function get_label() 
	{
	    return esc_html__( 'Map Link', 'propertyhive' );
	}

	// Enqueue element styles and scripts
  	public function enqueue_scripts()
  	{
  		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

    	
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
	    $this->controls['map_link_type'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Link Type', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => array(
				'_blank' => 'Open map in new window',
				'embedded' => 'Open embedded map in lightbox',
				'iframe' => 'Open iframe map in lightbox',
			),
	      	'default' => '_blank',
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

			$link_type = ( isset($this->settings['map_link_type']) && !empty($this->settings['map_link_type']) ) ? $this->settings['map_link_type'] : '_blank';

			switch ($link_type)
			{
				case "_blank":
				{
					echo '<a href="https://www.google.com/maps/?q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '" target="_blank">' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
					break;
				}
				case "embedded":
				{
					echo '<a href="#map_lightbox" data-fancybox>' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
			
					echo '<div id="map_lightbox" style="display:none; width:90%; max-width:800px;">';
			   	 		echo do_shortcode('[property_map]');
			    	echo '</div>';
					break;
				}
				case "iframe":
				{
					echo '<a 
					    href="#" 
					    data-fancybox 
					    data-type="iframe" 
					    data-src="https://maps.google.com/?output=embed&amp;f=q&amp;q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;layer=t&amp;hq=&amp;t=m&amp;z=15"
					>' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
					break;
				}
			}

		echo '</div>';
	}
}