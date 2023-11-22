<?php
/**
 * Bricks Builder Property EPCs Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Epcs_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-epcs';
  	public $icon         = 'fas fa-chart-bar';

	public function get_label() 
	{
	    return esc_html__( 'EPCs', 'propertyhive' );
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

			if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	        {
	            $epc_urls = $property->_epc_urls;
	            if ( is_array($epc_urls) && !empty( $epc_urls ) )
	            {
	                foreach ($epc_urls as $epc)
	                {
	                	echo '<a href="' . $epc['url'] . '" data-fancybox="epcs" rel="nofollow"><img src="' . $epc['url'] . '" alt=""></a>';
	                }
	            }
	        }
	        else
	       	{
		        $epc_attachment_ids = $property->get_epc_attachment_ids();

				if ( !empty($epc_attachment_ids) )
				{
					echo '<div class="epcs">';

						echo '<h4>' . __( 'EPCs', 'propertyhive' ) . '</h4>';

						foreach ( $epc_attachment_ids as $attachment_id )
						{
							if ( wp_attachment_is_image($attachment_id) )
		                    {
								echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="epc" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
							}
							else
							{
								echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View EPC', 'propertyhive' ) . '</a>';
							}
						}

					echo '</div>';
				}
			}

		echo '</div>';
	}
}