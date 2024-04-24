<?php
/**
 * Bricks Builder Property Embedded Virtual Tours Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Embedded_Virtual_Tours_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-embedded-virtual-tours';
  	public $icon         = 'fas fa-video';

	public function get_label() 
	{
	    return esc_html__( 'Embedded Virtual Tours', 'propertyhive' );
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
	    $this->controls['oembed'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Use oEmbed', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => [
	        	'no' => __( 'No', 'propertyhive' ),
				'yes' => __( 'Yes', 'propertyhive' ),
	      	],
	      	'default' => __( 'no', 'propertyhive' ),
	    ];
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		$virtual_tours = $property->get_virtual_tours();

		if ( empty($virtual_tours) )
		{
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			echo '<h4>' . __( 'Virtual Tours', 'propertyhive' ) . '</h4>';

			foreach ( $virtual_tours as $virtual_tour )
			{
				if ( isset($this->settings['oembed']) && $this->settings['oembed'] == 'yes' )
				{
					$embed_code = wp_oembed_get($virtual_tour['url']);
    				echo $embed_code;
				}
				else
				{
					$virtual_tour['url'] = preg_replace(
						"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
						"//www.youtube.com/embed/$2",
						$virtual_tour['url']
					);


					$virtual_tour['url'] = preg_replace(
			        	'/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/i',
			        	"//player.vimeo.com/video/$6",
			        	$virtual_tour['url']
			    	);

					echo '<iframe src="' . $virtual_tour['url'] . '" height="500" width="100%" allowfullscreen frameborder="0" allow="fullscreen"></iframe>';
				}
			}

		echo '</div>';
	}
}