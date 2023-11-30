<?php
/**
 * Bricks Builder Property Actions Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Actions_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-actions';
  	public $icon         = 'fas fa-bars';

	public function get_label() 
	{
	    return esc_html__( 'Actions', 'propertyhive' );
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
		$this->controls['display'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Display As', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => [
	        	'list' => __( 'List', 'propertyhive' ),
				'buttons' => __( 'Buttons', 'propertyhive' ),
	      	],
	      	//'inline' => true,
	      	//'clearable' => false,
	      	//'pasteStyles' => false,
	      	'default' => 'list',
	    ];

	    $this->controls['button_background_color'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Button Background Colour', 'propertyhive' ),
	      	'type' => 'color',
	      	'required' => ['layout', '=', ['buttons']],
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

			propertyhive_template_single_actions();

		echo '</div>';
	}
}