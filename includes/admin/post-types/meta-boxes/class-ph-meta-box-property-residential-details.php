<?php
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
 * PH_Meta_Box_Property_Address
 */
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
        $options = array( '' => '' );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'property_type', $args );
        
        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
                
                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'property_type', $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                    }
                }
            }

            $term_list = wp_get_post_terms($post->ID, 'property_type', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }

?>
        <p class="form-field property_type_id_field"><label for="property_type_id"><?php _e( 'Property Type', 'propertyhive' ); ?></label>
        <select id="property_type_id" name="property_type_id[]" multiple="multiple" data-placeholder="<?php _e( 'Select property type(s)', 'propertyhive' ); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'property_type', $args );
                
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
                        $subterms = get_terms( 'property_type', $args );
                        
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
                                $subsubterms = get_terms( 'property_type', $args );
                                
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

        <p class="form-field parking_ids_field"><label for="parking_ids"><?php _e( 'Parking', 'propertyhive' ); ?></label>
        <select id="parking_ids" name="parking_ids[]" multiple="multiple" data-placeholder="<?php _e( 'Select parking', 'propertyhive' ); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'parking', $args );
                
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

        <p class="form-field outside_space_ids_field"><label for="outside_space_ids"><?php _e( 'Outside Space', 'propertyhive' ); ?></label>
        <select id="outside_space_ids" name="outside_space_ids[]" multiple="multiple" data-placeholder="<?php _e( 'Select outside space(s)', 'propertyhive' ); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'outside_space', $args );
                
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
    
        do_action('propertyhive_property_residential_details_fields');
	   
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
        
        $rooms = preg_replace("/[^0-9]/", '', $_POST['_bedrooms']);
        update_post_meta( $post_id, '_bedrooms', $rooms );

        $rooms = preg_replace("/[^0-9]/", '', $_POST['_bathrooms']);
        update_post_meta( $post_id, '_bathrooms', $rooms );

        $rooms = preg_replace("/[^0-9]/", '', $_POST['_reception_rooms']);
        update_post_meta( $post_id, '_reception_rooms', $rooms );
        
        $property_types = array();
        if ( isset( $_POST['property_type_id'] ) && !empty( $_POST['property_type_id'] ) )
        {
            foreach ( $_POST['property_type_id'] as $property_type_id )
            {
                $property_types[] = $property_type_id;
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
        if ( isset( $_POST['parking_ids'] ) && !empty( $_POST['parking_ids'] ) )
        {
            foreach ( $_POST['parking_ids'] as $parking_id )
            {
                $parkings[] = $parking_id;
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
        if ( isset( $_POST['outside_space_ids'] ) && !empty( $_POST['outside_space_ids'] ) )
        {
            foreach ( $_POST['outside_space_ids'] as $outside_space_id )
            {
                $outside_spaces[] = $outside_space_id;
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

        do_action( 'propertyhive_save_property_residential_details', $post_id );
    }

}
