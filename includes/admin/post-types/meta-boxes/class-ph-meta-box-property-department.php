<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Department; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared meta-box global contract; $thepostid is intentionally set for the meta-box output and its included field helpers.
            $thepostid = $post->ID;
            setup_postdata($post);
        }

        $parent_post = false;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parent defaults; saving has independent nonce and permission guards.
        $parent_id = isset( $_GET['post_parent'] ) && is_scalar( $_GET['post_parent'] ) ? absint( $_GET['post_parent'] ) : 0;
        if ( $parent_id > 0 && 'property' === get_post_type( $parent_id ) && current_user_can( 'edit_post', $parent_id ) )
        {
            $parent_post = $parent_id;
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
                jQuery(\'#propertyhive-property-' . esc_attr($key) . '-details\').hide();
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

        $countries_with_material_information = is_array( $countries_with_material_information ) ? array_values( array_filter( $countries_with_material_information, 'is_string' ) ) : array();
        if ( !empty($countries_with_material_information) )
        {
            echo '
                    if ( ' . wp_json_encode( array_map( 'strtoupper', $countries_with_material_information ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '.indexOf(selected_country) !== -1 )
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
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared meta-box global contract; $thepostid is intentionally set for the meta-box output and its included field helpers.
        $thepostid = $original_thepostid;
        setup_postdata($post);
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;
        
        if ( isset( $_POST['_department'] ) && is_string( $_POST['_department'] ) ) {
            update_post_meta( $post_id, '_department', wp_slash( ph_clean( wp_unslash( $_POST['_department'] ) ) ) );
        }

        do_action( 'propertyhive_save_property_department', $post_id );
    }

}
