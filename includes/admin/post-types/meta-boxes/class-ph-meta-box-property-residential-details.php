<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Property Residential Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Residential_Details
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Residential_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Property_Residential_Details {

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

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        propertyhive_wp_text_input( array( 
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
        ) );
        
        // Property Type
        ?>
        <p class="form-field property_type_id_field"><label for="property_type_id"><?php echo esc_html(__( 'Property Type', 'propertyhive' )); ?></label>
        <select id="property_type_id" name="property_type_id[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select property type(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                
                $selected_values = array();
                $term_list = wp_get_post_terms($post->ID, 'property_type', array("fields" => "ids"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }
                
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ( $terms as $term )
                    {
                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                        if ( in_array( $term->term_id, $selected_values ) )
                        {
                            echo ' selected';
                        }
                        echo '>' . esc_html( $term->name ) . '</option>';

                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                        
                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            foreach ($subterms as $term)
                            {
                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                if ( in_array( $term->term_id, $selected_values ) )
                                {
                                    echo ' selected';
                                }
                                echo '>- ' . esc_html( $term->name ) . '</option>';
                                
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                                
                                if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                {
                                    foreach ($subsubterms as $term)
                                    {
                                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                        if ( in_array( $term->term_id, $selected_values ) )
                                        {
                                            echo ' selected';
                                        }
                                        echo '>- - ' . esc_html( $term->name ) . '</option>';
                                    }
                                }
                            }
                        }
                    }
                }
            ?>
        </select>

        <p class="form-field parking_ids_field"><label for="parking_ids"><?php echo esc_html(__( 'Parking', 'propertyhive' )); ?></label>
        <select id="parking_ids" name="parking_ids[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select parking', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'parking' ) ) );
                
                $selected_values = array();
                $term_list = wp_get_post_terms($post->ID, 'parking', array("fields" => "ids"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }
                
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ( $terms as $term )
                    {
                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                        if ( in_array( $term->term_id, $selected_values ) )
                        {
                            echo ' selected';
                        }
                        echo '>' . esc_html( $term->name ) . '</option>';
                    }
                }
            ?>
        </select>

        <p class="form-field outside_space_ids_field"><label for="outside_space_ids"><?php echo esc_html(__( 'Outside Space', 'propertyhive' )); ?></label>
        <select id="outside_space_ids" name="outside_space_ids[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select outside space(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'outside_space' ) ) );
                
                $selected_values = array();
                $term_list = wp_get_post_terms($post->ID, 'outside_space', array("fields" => "ids"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }
                
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ( $terms as $term )
                    {
                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                        if ( in_array( $term->term_id, $selected_values ) )
                        {
                            echo ' selected';
                        }
                        echo '>' . esc_html( $term->name ) . '</option>';
                    }
                }
            ?>
        </select>
<?php
        $tax_band_options = apply_filters( 'propertyhive_property_residential_tax_bands',
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
        propertyhive_wp_select( $args );

        do_action('propertyhive_property_residential_details_fields');
	   
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

        $request_post = wp_unslash( $_POST );
        $input = array();
        foreach ( array( '_bedrooms', '_bathrooms', '_reception_rooms', '_council_tax_band' ) as $key )
        {
            if ( isset( $request_post[ $key ] ) && ! is_string( $request_post[ $key ] ) )
            {
                return;
            }
            $input[ $key ] = isset( $request_post[ $key ] ) ? sanitize_text_field( $request_post[ $key ] ) : '';
        }

        foreach ( array( 'property_type_id', 'parking_ids', 'outside_space_ids' ) as $key )
        {
            $input[ $key ] = array();
            if ( isset( $request_post[ $key ] ) )
            {
                if ( ! is_array( $request_post[ $key ] ) )
                {
                    return;
                }
                foreach ( $request_post[ $key ] as $term_id )
                {
                    if ( ! is_scalar( $term_id ) )
                    {
                        return;
                    }
                    $input[ $key ][] = absint( $term_id );
                }
            }
        }

        global $wpdb;

        $department = get_post_meta($post_id, '_department', TRUE);

        $departments_with_residential_details = apply_filters( 'propertyhive_departments_with_residential_details', array( 'residential-sales', 'residential-lettings' ) );
        
        if ( 
            in_array( $department, $departments_with_residential_details ) || 
            in_array( ph_get_custom_department_based_on( $department ), $departments_with_residential_details )
        )
        {
            $rooms = preg_replace("/[^0-9]/", '', ph_clean($input['_bedrooms']));
            update_post_meta( $post_id, '_bedrooms', $rooms );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($input['_bathrooms']));
            update_post_meta( $post_id, '_bathrooms', $rooms );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($input['_reception_rooms']));
            update_post_meta( $post_id, '_reception_rooms', $rooms );
            
            $property_types = array();
            if ( isset( $input['property_type_id'] ) && !empty( $input['property_type_id'] ) )
            {
                foreach ( $input['property_type_id'] as $property_type_id )
                {
                    $property_types[] = (int)$property_type_id;
                }
            }
            if ( !empty($property_types) )
            {
                wp_set_post_terms( $post_id, $property_types, 'property_type' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'property_type' );
            }

            $parkings = array();
            if ( isset( $input['parking_ids'] ) && !empty( $input['parking_ids'] ) )
            {
                foreach ( $input['parking_ids'] as $parking_id )
                {
                    $parkings[] = (int)$parking_id;
                }
            }
            if ( !empty($parkings) )
            {
                wp_set_post_terms( $post_id, $parkings, 'parking' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'parking' );
            }
            
            $outside_spaces = array();
            if ( isset( $input['outside_space_ids'] ) && !empty( $input['outside_space_ids'] ) )
            {
                foreach ( $input['outside_space_ids'] as $outside_space_id )
                {
                    $outside_spaces[] = (int)$outside_space_id;
                }
            }
            if ( !empty($outside_spaces) )
            {
                wp_set_post_terms( $post_id, $outside_spaces, 'outside_space' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'outside_space' );
            }

            if ( isset( $request_post['_council_tax_band'] ) )
            {
                update_post_meta( $post_id, '_council_tax_band', wp_slash( $input['_council_tax_band'] ) );
            }

            do_action( 'propertyhive_save_property_residential_details', $post_id );
        }
    }

}
