<?php
/**
 * Bricks Builder Property Bedrooms Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Bedrooms_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-bedrooms';
  	public $icon         = 'fas fa-bed';

	public function get_label() 
	{
	    return esc_html__( 'Bedrooms', 'propertyhive' );
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
		$this->controls['icon'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Icon', 'propertyhive' ),
	      	'type' => 'icon',
	    ];

	    $this->controls['before'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Before', 'propertyhive' ),
	      	'type' => 'text',
	    ];

	    $this->controls['after'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'After', 'propertyhive' ),
	      	'type' => 'text',
	      	'default' => __( 'Bedrooms', 'propertyhive' ),
	    ];
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		if ( $property->bedrooms == '' && $property->bedrooms != 0 )
		{
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			if ( isset( $this->settings['icon'] ) ) 
			{
		    	echo self::render_icon( $this->settings['icon'] );
		    	echo ' ';
		    }

		    if ( isset($this->settings['before']) && !empty($this->settings['before']) )
	        {
	        	echo $this->settings['before'] . ' ';
	        }

			echo $property->bedrooms;

			if ( isset($this->settings['after']) && !empty($this->settings['after']) )
	        {
	        	echo ' ' . $this->settings['after'];
	        }

		echo '</div>';
	}
}