<?php
/**
 * Bricks Builder Property Floorplans Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Floorplans_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-floorplans';
  	public $icon         = 'fas fa-ruler-combined';

	public function get_label() 
	{
	    return esc_html__( 'Floorplans', 'propertyhive' );
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
	            $floorplan_urls = $property->_floorplan_urls;
	            if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
	            {
	            	echo '<div class="floorplans">';

	                    echo '<h4>' . esc_html(__( 'Floorplans', 'propertyhive' )) . '</h4>';

		                foreach ($floorplan_urls as $floorplan)
		                {
		                	echo '<a href="' . esc_url($floorplan['url']) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . esc_url($floorplan['url']) . '" alt=""></a>';
		                }

		            echo '</div>';
	            }
	        }
	        else
	       	{
				$floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

				if ( !empty($floorplan_attachment_ids) )
				{
					echo '<div class="floorplans">';

						echo '<h4>' . esc_html(__( 'Floorplans', 'propertyhive' )) . '</h4>';

						foreach ( $floorplan_attachment_ids as $attachment_id )
						{
							if ( wp_attachment_is_image($attachment_id) )
		                    {
								echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . esc_url(wp_get_attachment_url($attachment_id)) . '" alt=""></a>';
							}
							else
							{
								echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow">' . esc_html(__( 'View Floorplan', 'propertyhive' )) . '</a>';
							}
						}

					echo '</div>';
				}
			}

		echo '</div>';
	}
}