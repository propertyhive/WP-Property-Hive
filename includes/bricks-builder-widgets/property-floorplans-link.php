<?php
/**
 * Bricks Builder Property Floorplans Link Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Floorplans_Link_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-floorplans-link';
  	public $icon         = 'fas fa-ruler-combined';

	public function get_label() 
	{
	    return esc_html__( 'Floorplans Link', 'propertyhive' );
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
		/*$this->controls['height'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Height', 'propertyhive' ),
	      	'type' => 'number',
	      	'default' => 400
	    ];*/
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
	        {
	        	$floorplan_urls = $property->floorplan_urls;
	            if ( !is_array($floorplan_urls) ) { $floorplan_urls = array(); }

	            if ( !empty($floorplan_urls) )
				{
					$i = 0;
					foreach ( $floorplan_urls as $floorplan )
					{
						echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . $floorplan['url'] . '" data-fancybox="floorplans" rel="nofollow">' . ( count($floorplan_urls) > 1 ? __( 'Floorplans', 'propertyhive' ) : __( 'Floorplan', 'propertyhive' ) ) . '</a>';
						++$i;
					}
				}
	        }
	        else
	        {
				$floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

				if ( !empty($floorplan_attachment_ids) )
				{
					$i = 0;
					foreach ( $floorplan_attachment_ids as $attachment_id )
					{
						echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="floorplans" rel="nofollow">' . ( count($floorplan_attachment_ids) > 1 ? __( 'Floorplans', 'propertyhive' ) : __( 'Floorplan', 'propertyhive' ) ) . '</a>';
						++$i;
					}
				}
			}

		echo '</div>';
	}
}