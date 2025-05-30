<?php
/**
 * Bricks Builder Property EPCs Link Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Epcs_Link_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive';
  	public $name         = 'bricks-builder-property-epcs-link';
  	public $icon         = 'fas fa-chart-bar';

	public function get_label() 
	{
	    return esc_html__( 'EPCs Link', 'propertyhive' );
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

			if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
	        {
	        	$epc_urls = $property->epc_urls;
	            if ( !is_array($epc_urls) ) { $epc_urls = array(); }

	            if ( !empty($epc_urls) )
				{
					$i = 0;
					foreach ( $epc_urls as $epc )
					{
						$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp' );
						$image = false;
						foreach ( $image_extensions as $image_extension )
						{
							if ( strpos(strtolower($epc['url']), '.' . $image_extension) )
							{
								$image = true;
							}
						}
						if ( $image )
						{
							echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . esc_url($epc['url']) . '" data-fancybox="epcs" rel="nofollow">' . esc_html( ( count($epc_urls) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) ) . '</a>';
							++$i;
						}
						else
						{
							echo '<a href="' . esc_url($epc['url']) . '" rel="nofollow" target="_blank">' . esc_html( ( count($epc_urls) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) ) . '</a>';
						}
					}
				}
	        }
	        else
	        {
				$epc_attachment_ids = $property->get_epc_attachment_ids();

				if ( !empty($epc_attachment_ids) )
				{
					$i = 0;
					foreach ( $epc_attachment_ids as $attachment_id )
					{
						echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="epc" rel="nofollow">' . esc_html( ( count($epc_attachment_ids) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) ) . '</a>';
						++$i;
					}
				}
			}

		echo '</div>';
	}
}