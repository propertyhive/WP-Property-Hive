<?php
/**
 * Bricks Builder Property Features Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Features_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-features';
  	public $icon         = 'fas fa-sterling-sign';

	public function get_label() 
	{
	    return esc_html__( 'Features', 'propertyhive' );
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

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";

			propertyhive_template_single_features();

		echo '</div>';
	}
}