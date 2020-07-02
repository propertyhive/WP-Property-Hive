<?php
/**
 * Property Summary Description
 *
 * Replaces the standard excerpt box.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Summary_Description
 */
class PH_Meta_Box_Property_Summary_Description {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
	    
        echo '<div class="propertyhive_meta_box">';
        
		$settings = array(
			'textarea_name'	=> 'excerpt',
			'media_buttons' => false,
			'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
			'tinymce' 	=> array(
				'toolbar1' => 'bold,italic',
				'toolbar2' => '',
			),
			'editor_css'	=> '<style>#wp-excerpt-editor-container .wp-editor-area{height:135px; width:100%;}</style>'
		);

		wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', apply_filters( 'propertyhive_property_summary_description_editor_settings', $settings ) );
	
        echo '</div>';
    
    }

}
