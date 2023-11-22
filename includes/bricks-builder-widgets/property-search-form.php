<?php
/**
 * Bricks Builder Property Search Form Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Search_Form_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-search-form';
  	public $icon         = 'fas fa-magnifying-glass';
  	public $scripts      = ['toggleDepartmentFields'];

	public function get_label() 
	{
	    return esc_html__( 'Search Form', 'propertyhive' );
	}

	// Enqueue element styles and scripts
  	public function enqueue_scripts()
  	{
  		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
		$frontend_script_path = $assets_path . 'js/frontend/';

    	wp_enqueue_script( 'propertyhive_search', $frontend_script_path . 'search' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
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
		$this->controls['form_id'] = [ // Unique control identifier (lowercase, no spaces)
      		'tab' => 'content', // Control tab: content/style
      		//'group' => 'form', // Show under control group
      		'label' => esc_html__( 'Form ID', 'propertyhive' ), // Control label
      		'type' => 'text', // Control type 
      		'default' => 'default', // Default setting
    	];
	}

	public function render()
	{
		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";
	    	echo do_shortcode('[property_search_form id="' . $this->settings['form_id'] . '"]');
	    echo '</div>';
	}
}