<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Appraisal_Property; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
            $formatted_address = $appraisal->get_formatted_full_address( '<br>' );
            $additional_information = $appraisal->additional_property_information;
            if ( ! is_scalar( $additional_information ) )
            {
                $additional_information = '';
            }

            echo '<p class="form-field"><label for="">Address</label>' . wp_kses( $formatted_address, array( 'br' => array() ) ) . '<br><a href="' . esc_url(get_edit_post_link( $appraisal->property_id )) . '">View Property</a></p>';

            echo '<p class="form-field"><label for="">Department</label>' . esc_html(ucwords(str_replace("-", " ", $appraisal->department))) . '</p>';

            echo '<p class="form-field"><label for="">Bedrooms</label>' . esc_html($appraisal->bedrooms) . '</p>';

            echo '<p class="form-field"><label for="">Bathrooms</label>' . esc_html($appraisal->bathrooms) . '</p>';

            echo '<p class="form-field"><label for="">Reception Rooms</label>' . esc_html($appraisal->reception_rooms) . '</p>';

            echo '<p class="form-field"><label for="">Property Type</label>' . esc_html($appraisal->property_type) . '</p>';

            echo '<p class="form-field"><label for="">Parking</label>' . esc_html($appraisal->parking) . '</p>';

            echo '<p class="form-field"><label for="">Outside Space</label>' . esc_html($appraisal->outside_space) . '</p>';

            echo '<p class="form-field"><label for="">Council Tax Band</label>' . esc_html($appraisal->council_tax_band) . '</p>';

            echo '<p class="form-field"><label for="">Additional Information</label>' . nl2br( esc_html( (string) $additional_information ) ) . '</p>';
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
            $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
            
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
                    $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                    
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
        <p class="form-field property_type_id_field"><label for="property_type_id"><?php echo esc_html(__( 'Property Type', 'propertyhive' )); ?></label>
        <select id="property_type_id" name="property_type_id[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select property type(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        $request_post = wp_unslash( $_POST );

        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status != 'instructed' )
        {
            $address_name_number = ( isset( $request_post['_address_name_number'] ) && is_string( $request_post['_address_name_number'] ) ) ? sanitize_text_field( $request_post['_address_name_number'] ) : '';
            $address_street = ( isset( $request_post['_address_street'] ) && is_string( $request_post['_address_street'] ) ) ? sanitize_text_field( $request_post['_address_street'] ) : '';
            $address_two = ( isset( $request_post['_address_two'] ) && is_string( $request_post['_address_two'] ) ) ? sanitize_text_field( $request_post['_address_two'] ) : '';
            $address_three = ( isset( $request_post['_address_three'] ) && is_string( $request_post['_address_three'] ) ) ? sanitize_text_field( $request_post['_address_three'] ) : '';
            $address_four = ( isset( $request_post['_address_four'] ) && is_string( $request_post['_address_four'] ) ) ? sanitize_text_field( $request_post['_address_four'] ) : '';
            $address_postcode = ( isset( $request_post['_address_postcode'] ) && is_string( $request_post['_address_postcode'] ) ) ? sanitize_text_field( $request_post['_address_postcode'] ) : '';
            $address_country = ( isset( $request_post['_address_country'] ) && is_string( $request_post['_address_country'] ) ) ? sanitize_text_field( $request_post['_address_country'] ) : '';
            $department = ( isset( $request_post['_department'] ) && is_string( $request_post['_department'] ) ) ? sanitize_text_field( $request_post['_department'] ) : '';
            update_post_meta( $post_id, '_address_name_number', wp_slash( $address_name_number ) );
            update_post_meta( $post_id, '_address_street', wp_slash( $address_street ) );
            update_post_meta( $post_id, '_address_two', wp_slash( $address_two ) );
            update_post_meta( $post_id, '_address_three', wp_slash( $address_three ) );
            update_post_meta( $post_id, '_address_four', wp_slash( $address_four ) );
            update_post_meta( $post_id, '_address_postcode', wp_slash( $address_postcode ) );
            update_post_meta( $post_id, '_address_country', wp_slash( $address_country ) );

            update_post_meta( $post_id, '_department', wp_slash( $department ) );

            $bedrooms = ( isset( $request_post['_bedrooms'] ) && is_string( $request_post['_bedrooms'] ) ) ? preg_replace( '/[^0-9]/', '', sanitize_text_field( $request_post['_bedrooms'] ) ) : '';
            $bathrooms = ( isset( $request_post['_bathrooms'] ) && is_string( $request_post['_bathrooms'] ) ) ? preg_replace( '/[^0-9]/', '', sanitize_text_field( $request_post['_bathrooms'] ) ) : '';
            $reception_rooms = ( isset( $request_post['_reception_rooms'] ) && is_string( $request_post['_reception_rooms'] ) ) ? preg_replace( '/[^0-9]/', '', sanitize_text_field( $request_post['_reception_rooms'] ) ) : '';
            update_post_meta( $post_id, '_bedrooms', $bedrooms );
            update_post_meta( $post_id, '_bathrooms', $bathrooms );
            update_post_meta( $post_id, '_reception_rooms', $reception_rooms );

            $property_types = self::normalize_term_ids( $request_post, 'property_type_id' );
            if ( null === $property_types )
            {
                // Ignore malformed scalar submissions and preserve existing terms.
            }
            elseif ( ! empty( $property_types ) )
            {
                wp_set_post_terms( $post_id, $property_types, 'property_type' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'property_type' );
            }

            $parkings = self::normalize_term_ids( $request_post, 'parking_ids' );
            if ( null === $parkings )
            {
                // Ignore malformed scalar submissions and preserve existing terms.
            }
            elseif ( ! empty( $parkings ) )
            {
                wp_set_post_terms( $post_id, $parkings, 'parking' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'parking' );
            }
            
            $outside_spaces = self::normalize_term_ids( $request_post, 'outside_space_ids' );
            if ( null === $outside_spaces )
            {
                // Ignore malformed scalar submissions and preserve existing terms.
            }
            elseif ( ! empty( $outside_spaces ) )
            {
                wp_set_post_terms( $post_id, $outside_spaces, 'outside_space' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'outside_space' );
            }

            if ( isset( $request_post['_council_tax_band'] ) && is_string( $request_post['_council_tax_band'] ) )
            {
                update_post_meta( $post_id, '_council_tax_band', wp_slash( sanitize_text_field( $request_post['_council_tax_band'] ) ) );
            }

            $additional_property_information = ( isset( $request_post['_additional_property_information'] ) && is_string( $request_post['_additional_property_information'] ) ) ? sanitize_textarea_field( $request_post['_additional_property_information'] ) : '';
            update_post_meta( $post_id, '_additional_property_information', wp_slash( $additional_property_information ) );
        }

        do_action( 'propertyhive_save_appraisal_property_details', $post_id );
    }

    /**
     * Normalize a multi-select taxonomy request value.
     *
     * @param array  $request_post Uns lashed request data.
     * @param string $key          Request key.
     * @return array|null
     */
    private static function normalize_term_ids( $request_post, $key ) {
        if ( ! array_key_exists( $key, $request_post ) )
        {
            return array();
        }
        if ( ! is_array( $request_post[ $key ] ) )
        {
            return null;
        }

        $term_ids = array();
        foreach ( $request_post[ $key ] as $term_id )
        {
            if ( ! is_scalar( $term_id ) )
            {
                return null;
            }
            if ( absint( $term_id ) > 0 )
            {
                $term_ids[] = absint( $term_id );
            }
        }

        return array_values( array_unique( $term_ids ) );
    }

}
