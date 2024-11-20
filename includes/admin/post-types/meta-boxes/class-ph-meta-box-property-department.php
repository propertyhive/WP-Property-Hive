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
	public static function output( $post, $args = array() ) {

        global $wpdb, $thepostid;

        $original_post = $post;
        $original_thepostid = $thepostid;

        // Used in the scenario where this meta box isn't used on the property edit page
        if ( isset( $args['args']['property_post'] ) )
        {
            $post = $args['args']['property_post'];
            $thepostid = $post->ID;
            setup_postdata($post);
        }

        $parent_post = false;
        if ( isset($_GET['post_parent']) && $_GET['post_parent'] != '' )
        {
            $parent_post = (int)$_GET['post_parent'];
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $departments = ph_get_departments();
        $custom_departments = ph_get_custom_departments();

        $department_options = array();

        foreach ( $departments as $key => $value )
        {
            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
            {
                $department_options[$key] = $value;
            }
        }
        if ( $parent_post !== FALSE || ( isset($post->post_parent) && $post->post_parent != '' && $post->post_parent != 0 ) )
        {
            $parent_department = get_post_meta( $post->post_parent, '_department', TRUE );
            if ($parent_post !== FALSE)
            {
                $parent_department = get_post_meta( $parent_post, '_department', TRUE );
            }

            if ( $parent_department == 'commercial' )
            {
                foreach ( $departments as $key => $value )
                {
                    if ( $key != 'commercial' )
                    {
                        unset($department_options[$key]);
                    }
                }
            }
        }
        else
        {
            // Make sure property doesn't have any children
            /*$args = array(
                'post_parent'       => $post->ID,
                'post_type'         => 'property', 
                'posts_per_page'    => 1,
            );
            $unit_query = new WP_Query( $args );

            if ( $unit_query->have_posts() )
            {
                foreach ( $departments as $key => $value )
                {
                    if ( $key != 'commercial' )
                    {
                        //unset($department_options[$key]);
                    }
                }
            }
            wp_reset_postdata();*/
        }
        $value = get_post_meta( $post->ID, '_department', TRUE );
        if ( $parent_post !== FALSE )
        {
            $value = get_post_meta( $parent_post, '_department', TRUE );
        }
        if ($value == '')
        {
            $value = get_option( 'propertyhive_primary_department' );
        }
        $args = array( 
            'id' => '_department',
            'label' => 'Department',
            'value' => $value,
            'options' => $department_options
        );
        if (count($department_options) == 1)
        {
            foreach ($department_options as $key => $value)
            {
                $args['value'] = $key;
            }
        }
        propertyhive_wp_radio( $args );
        
        echo '
        <script>
            
            var ph_custom_departments = ' . json_encode($custom_departments) . ';

            jQuery(document).ready(function()
            {
                //showHideDepartmentMetaBox();
                
                jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']\').change(function()
                {
                     showHideDepartmentMetaBox();
                });

                jQuery(\'#_address_country\').change(function()
                {
                     showHideDepartmentMetaBox();
                });
            });
            
            function showHideDepartmentMetaBox()
            {
                jQuery(\'#propertyhive-property-residential-details\').hide();
                jQuery(\'#propertyhive-property-material-information\').hide();
        ';
        foreach ( $departments as $key => $value )
        {
            echo '
                jQuery(\'#propertyhive-property-' . $key . '-details\').hide();
            ';
        }

        $departments_with_residential_details = apply_filters( 'propertyhive_departments_with_residential_details', array( 'residential-sales', 'residential-lettings' ) );
        
        echo '
                var selectedDepartment = jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']:checked\').val();
                var departments_with_residential_details = ' . json_encode($departments_with_residential_details) . ';
                 
                jQuery(\'#propertyhive-property-\' + selectedDepartment + \'-details\').show();
                if ( ph_custom_departments[selectedDepartment] ) { jQuery(\'#propertyhive-property-\' + ph_custom_departments[selectedDepartment].based_on + \'-details\').show(); }

                if ( jQuery.inArray( selectedDepartment, departments_with_residential_details ) != -1 || ( ph_custom_departments[selectedDepartment] && jQuery.inArray( ph_custom_departments[selectedDepartment].based_on, departments_with_residential_details ) != -1 ) )
                {
                    jQuery(\'#propertyhive-property-residential-details\').show();

                    var selected_country = \'\';
                    if (jQuery(\'#_address_country\').is(\'select\')) {
                        selected_country = jQuery(\'#_address_country\').val();
                    } else if (jQuery(\'#_address_country\').is(\'input[type="hidden"]\')) {
                        selected_country = jQuery(\'#_address_country\').val();
                    }
                    ';

        $countries_with_material_information = apply_filters( 'propertyhive_countries_with_material_information', array( 'GB' ) );

        $material_information_sountry_js = array();
        if ( !empty($countries_with_material_information) )
        {
            foreach ( $countries_with_material_information as $country )
            {
                $material_information_sountry_js[] = 'selected_country == \'' . strtoupper($country) . '\'';
            }
            echo '
                    if ( ' . implode(" || ", $material_information_sountry_js) . ' )
                    {
                        jQuery(\'#propertyhive-property-material-information\').show();
                    }
            ';
        }
        
        echo '
                }
            }
            
        </script>';

        do_action('propertyhive_property_department_fields');
        
        echo '</div>';
        
        echo '</div>';
	    
        $post = $original_post;
        $thepostid = $original_thepostid;
        setup_postdata($post);
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_department', ph_clean($_POST['_department']) );

        do_action( 'propertyhive_save_property_department', $post_id );
    }

}
