<?php
/**
 * Property Department
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Department
 */
class PH_Meta_Box_Property_Department {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        //TODO: Get the departments being used from the plugin settings and only display them options
        
        $departments = array();
        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
        {
            $departments['residential-sales'] = __( 'Residential Sales', 'propertyhive' );
        }
        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
        {
            $departments['residential-lettings'] = __( 'Residential Lettings', 'propertyhive' );
        }
        $value = get_post_meta( $post->ID, '_department', TRUE );
        if ($value == '')
        {
            $value = get_option( 'propertyhive_primary_department' );
        }
        $args = array( 
            'id' => '_department',
            'label' => 'Department',
            'value' => $value,
            'options' => $departments
        );
        if (count($departments) == 1)
        {
            foreach ($departments as $key => $value)
            {
                $args['value'] = $key;
            }
        }
        propertyhive_wp_radio( $args );
        
        echo '
        <script>
            
            jQuery(document).ready(function()
            {
                //showHideDepartmentMetaBox();
                
                jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']\').change(function()
                {
                     showHideDepartmentMetaBox();
                });
            });
            
            function showHideDepartmentMetaBox()
            {
                 jQuery(\'#propertyhive-property-residential-sales-details\').hide();
                 jQuery(\'#propertyhive-property-residential-lettings-details\').hide();
                 
                 jQuery(\'#propertyhive-property-\' + jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']:checked\').val() + \'-details\').show();
            }
            
        </script>';

        do_action('propertyhive_property_department_fields');
        
        echo '</div>';
        
        echo '</div>';
	    
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_department', $_POST['_department'] );
    }

}
