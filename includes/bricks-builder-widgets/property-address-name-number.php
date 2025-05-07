<?php
/**
 * Bricks Builder Property Address Name Number Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Address_Name_Number_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-address-name-number';
  	public $icon         = 'fas fa-house';

	public function get_label() 
	{
	    return esc_html__( 'Address Name/Number', 'propertyhive' );
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

		if ( $property->address_name_number == '' )
		{
			return;
		}

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			echo esc_html($property->address_name_number);

		echo '</div>';
	}
}