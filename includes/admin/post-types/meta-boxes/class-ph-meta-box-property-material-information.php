<?php
/**
 * Property Material Information
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Material_Information
 */
class PH_Meta_Box_Property_Material_Information {

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

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        /*propertyhive_wp_text_input( array( 
            'id' => '_bedrooms', 
            'label' => __( 'Bedrooms', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_bathrooms', 
            'label' => __( 'Bathrooms', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_reception_rooms', 
            'label' => __( 'Reception Rooms', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );*/
        
        // Electricity Type
        ?>
        <p class="form-field electricity_type_field"><label for="electricity_type"><?php echo esc_html(__( 'Electricity Type', 'propertyhive' )); ?></label>
        <select id="electricity_type" name="electricity_type[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select electricity type(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php
                $terms = get_electricity_types();

                $selected_values = array();
                $term_list = get_post_meta($post->ID, '_electricity_type', true);
                if ( is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }
                
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ( $terms as $key => $term )
                    {
                        echo '<option value="' . esc_attr( $key ) . '"';
                        if ( in_array( $key, $selected_values ) )
                        {
                            echo ' selected';
                        }
                        echo '>' . esc_html( $term ) . '</option>';
                    }
                }
            ?>
        </select>
<?php
        /*$tax_band_options = apply_filters( 'propertyhive_property_residential_tax_bands',
            array(
                '' => '',
                'A' => 'A',
                'B' => 'B',
                'C' => 'C',
                'D' => 'D',
                'E' => 'E',
                'F' => 'F',
                'G' => 'G',
                'H' => 'H',
                'I' => 'I',
            )
        );
        $args = array(
            'id' => '_council_tax_band',
            'label' => __( 'Council Tax Band', 'propertyhive' ),
            'desc_tip' => false,
            'options' => $tax_band_options
        );

        $selected_tax_band = get_post_meta( $post->ID, '_council_tax_band', true );
        if ( !empty($selected_tax_band) )
        {
            $args['value'] = $selected_tax_band;
        }
        propertyhive_wp_select( $args );*/

        do_action('propertyhive_property_material_information_fields');
	   
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

        $department = get_post_meta($post_id, '_department', TRUE);

        $departments_with_residential_details = apply_filters( 'propertyhive_departments_with_residential_details', array( 'residential-sales', 'residential-lettings' ) );
        
        if ( 
            in_array( $department, $departments_with_residential_details ) || 
            in_array( ph_get_custom_department_based_on( $department ), $departments_with_residential_details )
        )
        {
            //$rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['_bedrooms']));
            //update_post_meta( $post_id, '_bedrooms', $rooms );

            $electricity_types = array();
            if ( isset( $_POST['electricity_type'] ) && !empty( $_POST['electricity_type'] ) )
            {
                foreach ( $_POST['electricity_type'] as $electricity_type )
                {
                    $electricity_types[] = ph_clean($electricity_type);
                }
            }
            update_post_meta( $post_id, '_electricity_type', $electricity_types );

            do_action( 'propertyhive_save_property_material_information', $post_id );
        }
    }

}
