<?php
/**
 * PropertyHive Header Stripes
 *
 * 
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Header Stripes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Admin_Header_Stripes
 */
class PH_Admin_Header_Stripes {

	/**
	 * Constructor
	 */
	public function __construct() {

		//add_action( 'edit_form_top', array( $this, 'add_header_stripe' ), 10, 1 );

	}

	/**
	 * Add PH Header Stripe
	 */
	public function add_header_stripe( $post ) {

		global $post, $propertyhive, $property;

		if ($post->post_status != 'auto-draft')
		{
			if ($post->post_type == 'property')
			{
				$property = new PH_Property((int)$post->ID);

				echo '<div style="background:#2ea2cc; border:1px solid #0074a2; border-radius:4px; padding:10px 15px; color:#FFF;">';

				echo propertyhive_get_property_thumbnail('thumbnail', 'alignleft');

				echo '<h1>' . esc_html(get_the_title($post->ID)) . '</h1>';
				echo '<h3>' . $property->get_formatted_price() . '</h3>';

				echo '<div class="clear"></div>';

				echo '</div>';
			}
		}

	}

}

new PH_Admin_Header_Stripes();