<?php
/**
 * Bricks Builder Property Reference Number Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Reference_Number_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-reference-number';
  	public $icon         = 'fas fa-tag';

	public function get_label() 
	{
	    return esc_html__( 'Reference Number', 'propertyhive' );
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
	      	'default' => __( 'Reeference:', 'propertyhive' ),
	    ];

	    $this->controls['after'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'After', 'propertyhive' ),
	      	'type' => 'text',
	    ];
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		if ( $property->reference_number == '' )
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

			echo esc_html($property->reference_number);

			if ( isset($this->settings['after']) && !empty($this->settings['after']) )
	        {
	        	echo ' ' . $this->settings['after'];
	        }

		echo '</div>';
	}
}