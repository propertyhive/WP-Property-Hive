<?php
/**
 * Bricks Builder Property Virtual Tours Link Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Virtual_Tours_Link_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-virtual-tours-link';
  	public $icon         = 'fas fa-video';

	public function get_label() 
	{
	    return esc_html__( 'Virtual Tours Link', 'propertyhive' );
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

		$virtual_tours = $property->get_virtual_tours();

        if ( empty( $virtual_tours ) )
        {
        	return;
        }

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			foreach ($virtual_tours as $virtual_tour)
            {
				echo '<a href="' . $virtual_tour['url'] . '" target="_blank" rel="nofollow"';
				if ( strpos($virtual_tour['url'], 'yout') !== FALSE || strpos($virtual_tour['url'], 'vimeo') !== FALSE )
                {
                    echo ' data-fancybox=""';
                }
				echo '>' . __( $virtual_tour['label'], 'propertyhive' ) . '</a>';
			}

		echo '</div>';
	}
}