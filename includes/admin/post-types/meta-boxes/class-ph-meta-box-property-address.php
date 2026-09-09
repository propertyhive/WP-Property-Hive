<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Property Address
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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Address; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Property_Address {

	/**
	 * Output the metabox
	 */
	public static function output( $post, $args = array() ) {
        global $wpdb, $thepostid, $pagenow;

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared meta-box global contract; $thepostid is intentionally set for the meta-box output and its included field helpers.
        $thepostid = $post->ID;

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
        
        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<input type="hidden" name="propertyhive_address_present" value="1">';
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $post_parent_id = ( ( isset($post->post_parent) ) ? $post->post_parent : 0 );
        $parent_post = false;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parent defaults; the parent must be an editable property and saving uses the metabox nonce.
        $requested_parent = isset( $_GET['post_parent'] ) && is_scalar( $_GET['post_parent'] ) ? absint( $_GET['post_parent'] ) : 0;
        if ( $requested_parent && get_post_type( $requested_parent ) === 'property' && current_user_can( 'manage_propertyhive' ) && current_user_can( 'edit_post', $requested_parent ) )
        {
            $parent_post = $requested_parent;
            $post_parent_id = $requested_parent;
        }
        propertyhive_wp_hidden_input( array( 
            'id' => 'post_parent', 
            'value' => $post_parent_id
        ) );

        $args = array( 
            'id' => '_reference_number', 
            'label' => __( 'Reference Number', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        $next_auto_increment = FALSE;
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_reference_number', TRUE );
        }
        elseif ( $pagenow == 'post-new.php' )
        {
            if ( get_option( 'propertyhive_auto_incremental_reference_numbers' ) == 'yes' )
            {
                $next = get_option( 'propertyhive_auto_incremental_next', '' );
                if ( $next == '' || (int)$next == 0 )
                {
                    $next = 1;
                }
                $args['value'] = $next;

                $next_auto_increment = $next + 1;
            }
        }
        propertyhive_wp_text_input( $args );

        if ( $next_auto_increment !== FALSE )
        {
            propertyhive_wp_hidden_input( array( 
                'id' => 'next_auto_increment', 
                'value' => $next_auto_increment
            ) );
        }
        
        $args = array( 
            'id' => '_address_name_number', 
            'label' => __( 'Building Name / Number', 'propertyhive' ), 
            'desc_tip' => false, 
            'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_address_name_number', TRUE );
        }
        propertyhive_wp_text_input( $args );
        
        $args = array( 
            'id' => '_address_street', 
            'label' => __( 'Street', 'propertyhive' ), 
            'desc_tip' => false, 
            'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
            'type' => 'text',
        );
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_address_street', TRUE );
        }
        propertyhive_wp_text_input( $args );
        
        $args = array( 
            'id' => '_address_two', 
            'label' => __( 'Address Line 2', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_address_two', TRUE );
        }
        propertyhive_wp_text_input( $args );
        
        $args = array( 
            'id' => '_address_three', 
            'label' => __( 'Town / City', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_address_three', TRUE );
        }
        propertyhive_wp_text_input( $args );
        
        $args = array( 
            'id' => '_address_four', 
            'label' => __( 'County / State', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_address_four', TRUE );
        }
        propertyhive_wp_text_input( $args );
        
        $args = array( 
            'id' => '_address_postcode', 
            'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_address_postcode', TRUE );
        }
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
            if ( $parent_post !== FALSE )
            {
                $args['value'] = get_post_meta( $parent_post, '_address_country', TRUE );
            }
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
            if ( $parent_post !== FALSE )
            {
                $args['value'] = get_post_meta( $parent_post, '_address_country', TRUE );
            }
            propertyhive_wp_select( $args );
        }
        
        // Location
        $options = array( '' => '' );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
        
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
                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                        
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                        
                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                        {
                            foreach ($subsubterms as $term)
                            {
                                $options[$term->term_id] = '- ' . $term->name;
                            }
                        }
                    }
                }
            }

            $term_list = wp_get_post_terms($post->ID, 'location', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }
        
?>
<p class="form-field location_id_field"><label for="location_id"><?php echo esc_html(__( 'Location', 'propertyhive' )); ?></label>
        <select id="location_id" name="location_id[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select location(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php

                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                
                $selected_values = array();
                $term_list = wp_get_post_terms($post->ID, 'location', array("fields" => "ids"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }

                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $options[$term->term_id] = $term->name;

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
                        $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                        
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
                                $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                                
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
<?php

        do_action('propertyhive_property_address_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
        echo '
        <script>
            
            var countries = ' . json_encode($country_js). ';

            // Change currency symbol shown on sales and lettings details meta boxes
            function countryChange(country_code)
            {
                if (typeof countries[country_code] != \'undefined\')
                {
                    jQuery(\'.currency-symbol\').html(countries[country_code].currency_symbol);
                }
                else
                {
                    jQuery(\'.currency-symbol\').html(countries[jQuery(\'#_address_country\').val()].currency_symbol);
                }
            }

            jQuery(document).ready(function()
            {
                countryChange(jQuery(\'#_address_country\').val());

                jQuery(\'select[id=\\\'_address_country\\\']\').change(function()
                {
                    countryChange(jQuery(this).val());
                });
        ';
        if ( apply_filters( 'propertyhive_prefill_full_address_from_title', true ) === true )
        {
            echo '
                    if (jQuery(\'#title\').length > 0)
                    {
                        jQuery(\'#title\').change(function()
                        {
                            // Check title contains something
                            if (jQuery(\'#title\').val() != \'\')
                            {
                                // Check all address fields are empty so we don\'t override anything should the user have customised it already
                                
                                if (jQuery(\'#_address_name_number\').val() == \'\' && jQuery(\'#_address_street\').val() == \'\' && jQuery(\'#_address_two\').val() == \'\' && jQuery(\'#_address_three\').val() == \'\' && jQuery(\'#_address_four\').val() == \'\' && jQuery(\'#_address_postcode\').val() == \'\')
                                {
                                    // Yep. All address fields are empty
                                    
                                    // See if any of the locations can be set
                                    jQuery("#location_id > option").each(function() 
                                    {
                                        if (this.text != \'\')
                                        {
                                            var text_to_search_for = this.text.replace(\'- \', \'\');
                                            if (jQuery(\'#title\').val().indexOf(text_to_search_for) != -1)
                                            {
                                                this.selected = true;
                                            }
                                        }
                                    });
                                    
                                    // Split address and fill related address field
                                    
                                    var address_fields = [
                                        \'_address_name_number\',
                                        \'_address_street\',
                                        \'_address_two\',
                                        \'_address_three\',
                                        \'_address_four\',
                                        \'_address_postcode\'
                                    ];
                                    
                                    // Split title by comma
                                    var explode_title = jQuery(\'#title\').val().split(\',\');

                                    // If last element of address contains numbers, check if it contains a postcode
                                    if (explode_title[explode_title.length - 1].match(/\d+/g) != null)
                                    {
                                        // Extract last element of address
                                        var last_address_element = explode_title.pop().trim();

                                        // Regular Expression that matches postcodes case insensitively in format NN13, NN13 7DR and NN137DR
                                        const postcodeRegex = /^[A-Za-z]{1,2}[0-9][A-Za-z0-9]? ?([0-9][A-Za-z]{2})?$/;

                                        if (postcodeRegex.test(last_address_element))
                                        {
                                            // Entire last element is a valid postcode
                                            var postcode = last_address_element;
                                        }
                                        else
                                        {
                                            // Split address element by spaces
                                            var last_address_element_split = last_address_element.split(\' \');

                                            // Remove any sections that aren\'t valid postcode sections
                                            var last_address_parts_containing_numbers = last_address_element_split.filter(function(address_part) {
                                                return (postcodeRegex.test(address_part) || /[A-Za-z]{1,2}[0-9][A-Za-z0-9]?/.test(address_part) || /[0-9][A-Za-z]{2}/.test(address_part));
                                            });
                                            // Concatenate valid postcode elements
                                            var last_address_element_filtered = last_address_parts_containing_numbers.join(\' \');

                                            // Re-created address element is a valid postcode
                                            if (postcodeRegex.test(last_address_element_filtered))
                                            {
                                                var postcode = last_address_element_filtered;

                                                // Get non-postcode text from address element to put back into address
                                                var last_address_parts_without_numbers = last_address_element_split.filter(function(address_part) {
                                                    return !(postcodeRegex.test(address_part) || /[A-Za-z]{1,2}[0-9][A-Za-z0-9]?/.test(address_part) || /[0-9][A-Za-z]{2}/.test(address_part));
                                                });
                                                // Put other text back into address
                                                explode_title.push(last_address_parts_without_numbers.join(\' \'));
                                            }
                                        }

                                        // If we\'ve found a postcode, put it in the postcode field
                                        if (typeof postcode !== \'undefined\')
                                        {
                                            // Set postcode field to the postcode we found
                                            jQuery(\'#_address_postcode\').val(postcode);
                                        }
                                        else
                                        {
                                            // We couldn\'t find a postcode, so put the final address element back to be processed as normal
                                            explode_title.push(last_address_element);
                                        }
                                    }

                                    for (var i in explode_title)
                                    {
                                        var title_element = jQuery.trim(explode_title[i]); // Trim it to remove any white space either side
                                        
                                        if (title_element != \'\' && address_fields.length > 0)
                                        {
                                            if ( i == 0 )
                                            {
                                                var split_title_element = title_element.split(\' \');
                                                if (jQuery.isNumeric( title_element ) || jQuery.isNumeric( split_title_element[0] )) // check if this is a house number
                                                {
                                                    jQuery(\'#\' + address_fields[0]).val(split_title_element[0]);
                                                    
                                                    title_element = title_element.replace(split_title_element[0], \'\', title_element);
                                                    title_element = jQuery.trim(title_element);
                                                    
                                                    jQuery(\'#\' + address_fields[1]).val(title_element);
                                                    address_fields.splice(0,2);
                                                }
                                                else
                                                {
                                                    jQuery(\'#\' + address_fields[1]).val(title_element);
                                                    address_fields.splice(0,2);
                                                }
                                            }
                                            else
                                            {
                                                // General address element
                                                jQuery(\'#\' + address_fields[0]).val(title_element);
                                                address_fields.splice(0,1);
                                            }
                                        }
                                    }

                                    jQuery(\'#_address_postcode\').trigger(\'change\');
                                }
                            }
                        });
                    }
                ';
        }
        echo '
            });
        
        </script>
        ';

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

        if ( isset( $_POST['_reference_number'] ) && is_string( $_POST['_reference_number'] ) ) {
            update_post_meta( $post_id, '_reference_number', wp_slash( sanitize_text_field( wp_unslash( $_POST['_reference_number'] ) ) ) );
        }
        if ( isset( $_POST['_address_name_number'] ) && is_string( $_POST['_address_name_number'] ) ) {
            update_post_meta( $post_id, '_address_name_number', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_name_number'] ) ) ) );
        }
        if ( isset( $_POST['_address_street'] ) && is_string( $_POST['_address_street'] ) ) {
            update_post_meta( $post_id, '_address_street', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_street'] ) ) ) );
        }
        if ( isset( $_POST['_address_two'] ) && is_string( $_POST['_address_two'] ) ) {
            update_post_meta( $post_id, '_address_two', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_two'] ) ) ) );
        }
        if ( isset( $_POST['_address_three'] ) && is_string( $_POST['_address_three'] ) ) {
            update_post_meta( $post_id, '_address_three', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_three'] ) ) ) );
        }
        if ( isset( $_POST['_address_four'] ) && is_string( $_POST['_address_four'] ) ) {
            update_post_meta( $post_id, '_address_four', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_four'] ) ) ) );
        }
        if ( isset( $_POST['_address_postcode'] ) && is_string( $_POST['_address_postcode'] ) ) {
            update_post_meta( $post_id, '_address_postcode', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_postcode'] ) ) ) );
        }
        if ( isset( $_POST['_address_country'] ) && is_string( $_POST['_address_country'] ) ) {
            update_post_meta( $post_id, '_address_country', wp_slash( ph_clean( wp_unslash( $_POST['_address_country'] ) ) ) );
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Shape-only check rejects nested values before the separately sanitized text or integer conversion below.
        if ( isset( $_POST['location_id'] ) && ( is_scalar( $_POST['location_id'] ) || ( is_array( $_POST['location_id'] ) && count( array_filter( $_POST['location_id'], 'is_scalar' ) ) === count( $_POST['location_id'] ) ) ) ) {
            $location_ids = is_array( $_POST['location_id'] ) ? array_map( 'intval', $_POST['location_id'] ) : (int) $_POST['location_id'];
            wp_set_post_terms( $post_id, $location_ids, 'location' );
        } elseif ( ! isset( $_POST['location_id'] ) && isset( $_POST['propertyhive_address_present'] ) ) {
            wp_delete_object_term_relationships( $post_id, 'location' );
        }

        // default status to 'instructed' if not set already
        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'instructed' );
        }

        if ( isset( $_POST['next_auto_increment'] ) && current_user_can( 'manage_propertyhive' ) && 'yes' === get_option( 'propertyhive_auto_incremental_reference_numbers' ) ) {
            // The hidden field signals allocation; the sequence value is server-controlled.
            $next_reference = max( 1, absint( get_option( 'propertyhive_auto_incremental_next', 1 ) ) );
            if ( $next_reference < PHP_INT_MAX ) {
                update_option( 'propertyhive_auto_incremental_next', $next_reference + 1 );
            }
        }

        do_action( 'propertyhive_save_property_address', $post_id );
    }

}
