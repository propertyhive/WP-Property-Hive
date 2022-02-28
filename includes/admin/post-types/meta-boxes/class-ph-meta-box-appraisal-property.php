<?php
/**
 * Appraisal Property Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appraisal_Property
 */
class PH_Meta_Box_Appraisal_Property {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        $status = get_post_meta( $thepostid, '_status', TRUE );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        if ( $status == 'instructed' )
        {
            $appraisal = new PH_Appraisal((int)$thepostid);

            echo '<p class="form-field"><label for="">Address</label>' . $appraisal->get_formatted_full_address('<br>') . '<br><a href="' . get_edit_post_link( $appraisal->property_id ) . '">View Property</a></p>';

            echo '<p class="form-field"><label for="">Department</label>' . ucwords(str_replace("-", " ", $appraisal->department)) . '</p>';

            echo '<p class="form-field"><label for="">Bedrooms</label>' . $appraisal->bedrooms . '</p>';

            echo '<p class="form-field"><label for="">Bathrooms</label>' . $appraisal->bathrooms . '</p>';

            echo '<p class="form-field"><label for="">Reception Rooms</label>' . $appraisal->reception_rooms . '</p>';

            echo '<p class="form-field"><label for="">Property Type</label>' . $appraisal->property_type . '</p>';

            echo '<p class="form-field"><label for="">Parking</label>' . $appraisal->parking . '</p>';

            echo '<p class="form-field"><label for="">Outside Space</label>' . $appraisal->outside_space . '</p>';

            echo '<p class="form-field"><label for="">Council Tax Band</label>' . $appraisal->council_tax_band . '</p>';

            echo '<p class="form-field"><label for="">Additional Information</label>' . $appraisal->additional_property_information . '</p>';
        }
        else
        {        
            $args = array( 
                'id' => '_address_name_number', 
                'label' => __( 'Building Name / Number', 'propertyhive' ), 
                'desc_tip' => false, 
                'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
                'description' => ( get_post_meta( $thepostid, '_address_name_number', TRUE ) == '' ? '<a href="" id="property-address-same-as-owners">Use Owner\'s Address</a>' : '' ),
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
            
            $args = array( 
                'id' => '_address_street', 
                'label' => __( 'Street', 'propertyhive' ), 
                'desc_tip' => false, 
                'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
                'type' => 'text',
            );
            propertyhive_wp_text_input( $args );
            
            $args = array( 
                'id' => '_address_two', 
                'label' => __( 'Address Line 2', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
            
            $args = array( 
                'id' => '_address_three', 
                'label' => __( 'Town / City', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
            
            $args = array( 
                'id' => '_address_four', 
                'label' => __( 'County / State', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
            
            $args = array( 
                'id' => '_address_postcode', 
                'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );

            // Country dropdown
            $countries = get_option( 'propertyhive_countries', array( 'GB' ) );
            $property_country = get_post_meta( $thepostid, '_address_country', TRUE );
            $default_country = get_option( 'propertyhive_default_country', 'GB' );
            $country_js = array();
            if ( $property_country == '' )
            {
                $property_country = $default_country;
            }

            // Make sure country is in list of countries we operate in
            if ( !in_array($property_country, $countries) )
            {
                $property_country = $default_country;
            }

            if ( empty($countries) || count($countries) < 2 )
            {
                if ( count($countries) == 1 )
                {
                    $ph_countries = new PH_Countries();
                    $country = $ph_countries->get_country( $countries[0] );
                    $country_js[$countries[0]] = $country;
                }

                $args = array( 
                    'id' => '_address_country',
                    'value' => $property_country,
                );
                propertyhive_wp_hidden_input( $args );
            }
            else
            {
                $ph_countries = new PH_Countries(); // Can't use $this->countries because we're inside a static method

                $country_options = array();
                foreach ( $countries as $country_code )
                {
                    $country = $ph_countries->get_country( $country_code );
                    if ( $country !== false )
                    {
                        $country_options[$country_code] = $country['name'];
                        $country_js[$country_code] = $country;
                    }
                }

                $args = array( 
                    'id' => '_address_country', 
                    'label' => __( 'Country', 'propertyhive' ), 
                    'desc_tip' => false,
                    'options' => $country_options,
                    'value' => $property_country,
                );
                propertyhive_wp_select( $args );
            }

            $departments = array();
            if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
            {
                $departments['residential-sales'] = __( 'Residential Sales', 'propertyhive' );
            }
            if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
            {
                $departments['residential-lettings'] = __( 'Residential Lettings', 'propertyhive' );
            }
            /*if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            {
                $departments['commercial'] = __( 'Commercial', 'propertyhive' );
            }*/

            $value = get_post_meta( $post->ID, '_department', TRUE );
            if ($value == '')
            {
                $value = get_option( 'propertyhive_primary_department', '' );
            }

            $args = array( 
                'id' => '_department',
                'label' => 'Department',
                'options' => $departments,
                'value' => $value,
            );
            if (count($departments) == 1)
            {
                foreach ($departments as $key => $value)
                {
                    $args['value'] = $key;
                }
            }
            propertyhive_wp_radio( $args );

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

            $args = array( 
                'id' => '_additional_property_information', 
                'label' => __( 'Additional Information', 'propertyhive' ), 
                'class' => '',
                'desc_tip' => false, 
            );
            propertyhive_wp_textarea_input( $args );
        }

        do_action('propertyhive_appraisal_property_fields');
	    
        echo '</div>';
        
        echo '</div>';
?>
<script>

    jQuery(document).ready(function()
    {
        jQuery('#property-address-same-as-owners').click(function(e)
        {
            e.preventDefault();

            if ( jQuery('#_appraisal_property_owner_create_new').val() == '1' )
            {
                jQuery('#_address_name_number').val( jQuery('#_property_owner_address_name_number').val() );
                jQuery('#_address_street').val( jQuery('#_property_owner_address_street').val() );
                jQuery('#_address_two').val( jQuery('#_property_owner_address_two').val() );
                jQuery('#_address_three').val( jQuery('#_property_owner_address_three').val() );
                jQuery('#_address_four').val( jQuery('#_property_owner_address_four').val() );
                jQuery('#_address_postcode').val( jQuery('#_property_owner_address_postcode').val() );
            }
            else
            {
                jQuery('a[data-appraisal-property-owner-id]').each(function()
                {
                    jQuery('#_address_name_number').val( jQuery(this).attr('data-appraisal-property-owner-address-name-number') );
                    jQuery('#_address_street').val( jQuery(this).attr('data-appraisal-property-owner-address-street') );
                    jQuery('#_address_two').val( jQuery(this).attr('data-appraisal-property-owner-address-two') );
                    jQuery('#_address_three').val( jQuery(this).attr('data-appraisal-property-owner-address-three') );
                    jQuery('#_address_four').val( jQuery(this).attr('data-appraisal-property-owner-address-four') );
                    jQuery('#_address_postcode').val( jQuery(this).attr('data-appraisal-property-owner-address-postcode') );
                });
            }
        });
    });

</script>
<?php        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status != 'instructed' )
        {
            update_post_meta( $post_id, '_address_name_number', ph_clean($_POST['_address_name_number']) );
            update_post_meta( $post_id, '_address_street', ph_clean($_POST['_address_street']) );
            update_post_meta( $post_id, '_address_two', ph_clean($_POST['_address_two']) );
            update_post_meta( $post_id, '_address_three', ph_clean($_POST['_address_three']) );
            update_post_meta( $post_id, '_address_four', ph_clean($_POST['_address_four']) );
            update_post_meta( $post_id, '_address_postcode', ph_clean($_POST['_address_postcode']) );
            update_post_meta( $post_id, '_address_country', ph_clean($_POST['_address_country']) );

            update_post_meta( $post_id, '_department', ph_clean($_POST['_department']) );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['_bedrooms']));
            update_post_meta( $post_id, '_bedrooms', $rooms );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['_bathrooms']));
            update_post_meta( $post_id, '_bathrooms', $_POST['_bathrooms'] );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['_reception_rooms']));
            update_post_meta( $post_id, '_reception_rooms', $_POST['_reception_rooms'] );

            $property_types = array();
            if ( isset( $_POST['property_type_id'] ) && !empty( $_POST['property_type_id'] ) )
            {
                foreach ( $_POST['property_type_id'] as $property_type_id )
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
            if ( isset( $_POST['parking_ids'] ) && !empty( $_POST['parking_ids'] ) )
            {
                foreach ( $_POST['parking_ids'] as $parking_id )
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
            if ( isset( $_POST['outside_space_ids'] ) && !empty( $_POST['outside_space_ids'] ) )
            {
                foreach ( $_POST['outside_space_ids'] as $outside_space_id )
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

            if ( isset( $_POST['_council_tax_band'] ) )
            {
                update_post_meta( $post_id, '_council_tax_band', $_POST['_council_tax_band'] );
            }

            update_post_meta( $post_id, '_additional_property_information', sanitize_textarea_field($_POST['_additional_property_information']) );
        }

        do_action( 'propertyhive_save_appraisal_property_details', $post_id );
    }

}
