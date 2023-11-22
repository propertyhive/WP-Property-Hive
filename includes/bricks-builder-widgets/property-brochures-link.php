<?php
/**
 * Bricks Builder Property Brochures Link Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Brochures_Link_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-brochures-link';
  	public $icon         = 'fas fa-file';

	public function get_label() 
	{
	    return esc_html__( 'Brochures Link', 'propertyhive' );
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
		$this->controls['label'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Label', 'propertyhive' ),
	      	'type' => 'text',
	      	'default' => __( 'Brochure', 'propertyhive' ),
	    ];
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

			$label = isset($this->settings['label']) && !empty($this->settings['label']) ? $this->settings['label'] : __( 'Brochure', 'propertyhive' );

			if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
	        {
	        	$brochure_urls = $property->brochure_urls;
	            if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

	            if ( !empty($brochure_urls) )
				{
					foreach ( $brochure_urls as $brochure )
					{
						echo '<a href="' . $brochure['url'] . '" target="_blank" rel="nofollow">' . $label . '</a>';
					}
				}
	        }
	        else
	        {
				$brochure_attachment_ids = $property->get_brochure_attachment_ids();

				if ( !empty($brochure_attachment_ids) )
				{
					foreach ( $brochure_attachment_ids as $attachment_id )
					{
						echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . $label . '</a>';
					}
				}
			}

		echo '</div>';
	}
}