<?php
/**
 * Bricks Builder Property Enquiry Form Link Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Enquiry_Form_Link_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-enquiry-form-link';
  	public $icon         = 'fas fa-pen-to-square';

	public function get_label() 
	{
	    return esc_html__( 'Enquiry Form Link', 'propertyhive' );
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

		?>
		<a data-fancybox data-src="#makeEnquiry<?php echo (int)$property->id; ?>" href="javascript:;"><?php echo esc_html(__( 'Make Enquiry', 'propertyhive' )); ?></a>

	    <!-- LIGHTBOX FORM -->
	    <div id="makeEnquiry<?php echo $property->id; ?>" style="display:none;">
	        
	        <h2><?php echo esc_html(__( 'Make Enquiry', 'propertyhive' )); ?></h2>
	        
	        <p><?php _e( 'Please complete the form below and a member of staff will be in touch shortly.', 'propertyhive' ); ?></p>
	        
	        <?php propertyhive_enquiry_form(); ?>
	        
	    </div>
	    <!-- END LIGHTBOX FORM -->
	<?php

		echo '</div>';
	}
}