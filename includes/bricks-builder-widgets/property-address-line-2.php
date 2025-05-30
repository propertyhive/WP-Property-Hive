<?php
/**
 * Bricks Builder Property Address Line 2 Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Address_Line_2_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-address-line-2';
  	public $icon         = 'fas fa-house';

	public function get_label() 
	{
	    return esc_html__( 'Address Line 2', 'propertyhive' );
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
		/*$this->controls['form_id'] = [ // Unique control identifier (lowercase, no spaces)
      		'tab' => 'content', // Control tab: content/style
      		//'group' => 'form', // Show under control group
      		'label' => esc_html__( 'Form ID', 'propertyhive' ), // Control label
      		'type' => 'text', // Control type 
      		'default' => esc_html__( 'default', 'bricks' ), // Default setting
    	];*/
	}

	public function render()
	{
		global $property;

		if ( !isset($property->id) ) 
		{
			return;
		}

		if ( $property->address_two == '' )
		{
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			echo esc_html($property->address_two);

		echo '</div>';
	}
}