<?php
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
class PH_Meta_Box_Property_Address {

	/**
	 * Output the metabox
	 */
	public static function output( $post, $args = array() ) {
        global $wpdb, $thepostid;

        $thepostid = $post->ID;

        $original_post = $post;
        $original_thepostid = $thepostid;

        // Used in the scenario where this meta box isn't used on the property edit page
        if ( isset( $args['args']['property_post'] ) )
        {
            $post = $args['args']['property_post'];
            $thepostid = $post->ID;
            setup_postdata($post);
        }
        
        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $post_parent_id = ( ( isset($post->post_parent) ) ? $post->post_parent : 0 );
        $parent_post = false;
        if ( isset($_GET['post_parent']) && $_GET['post_parent'] != '' )
        {
            $post_parent_id = $_GET['post_parent'];
            $parent_post = $post_parent_id;
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
        if ( $parent_post !== FALSE )
        {
            $args['value'] = get_post_meta( $parent_post, '_reference_number', TRUE );
        }
        propertyhive_wp_text_input( $args );
        
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
        $terms = get_terms( 'location', $args );
        
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
                $subterms = get_terms( 'location', $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                        
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( 'location', $args );
                        
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
<p class="form-field"><label for="location_id"><?php _e( 'Location', 'propertyhive' ); ?></label>
        <select id="location_id" name="location_id[]" multiple="multiple" data-placeholder="<?php _e( 'Select location(s)', 'propertyhive' ); ?>" class="multiselect attribute_values">
            <?php

                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'location', $args );
                
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
                        $subterms = get_terms( 'location', $args );
                        
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
                                $subsubterms = get_terms( 'location', $args );
                                
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
                                            var split_title_elements = title_element.split(\' \');
                                            
                                            var numeric_matches = title_element.match(/\d+/g);
                                            if (i == explode_title.length-1 &&  numeric_matches != null)
                                            {
                                                // We\'re on the last bit and it contains a number
                                                for (var j in split_title_elements)
                                                {
                                                    var split_title_element = jQuery.trim(split_title_elements[j]);
                                                    
                                                    var numeric_matches = split_title_element.match(/\d+/g);
                                                    
                                                    if (split_title_element.length >=2 && split_title_element.length <= 4 && numeric_matches != null)
                                                    {
                                                        // This bit of the address element definitely contains postcode bit
                                                        var postcode = split_title_element;
                                                        if (j == (split_title_elements.length - 2))
                                                        {
                                                            var temp_title_element = jQuery.trim(split_title_elements[split_title_elements.length-1]); // Trim it to remove any white space either side
                                                            
                                                            if ((temp_title_element.length >=2 || temp_title_element.length <= 4))
                                                            {
                                                                // We have one element left after this
                                                                postcode += \' \' + temp_title_element;
                                                            }
                                                        }
                                                        jQuery(\'#address_postcode\').val(postcode);
                                                        
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        // General address element
                                                        jQuery(\'#\' + address_fields[0]).val(title_element);
                                                        address_fields.splice(0,1);
                                                    }
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
                                }

                                jQuery(\'#_address_postcode\').trigger(\'change\');
                            }
                        }
                    });
                }
            });
        
        </script>
        ';

        $post = $original_post;
        $thepostid = $original_thepostid;
        setup_postdata($post);
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        update_post_meta( $post_id, '_reference_number', $_POST['_reference_number'] );
        update_post_meta( $post_id, '_address_name_number', $_POST['_address_name_number'] );
        update_post_meta( $post_id, '_address_street', $_POST['_address_street'] );
        update_post_meta( $post_id, '_address_two', $_POST['_address_two'] );
        update_post_meta( $post_id, '_address_three', $_POST['_address_three'] );
        update_post_meta( $post_id, '_address_four', $_POST['_address_four'] );
        update_post_meta( $post_id, '_address_postcode', $_POST['_address_postcode'] );
        update_post_meta( $post_id, '_address_country', $_POST['_address_country'] );

        if ( !empty($_POST['location_id']) )
        {
            wp_set_post_terms( $post_id, $_POST['location_id'], 'location' );
        }
        else
        {
            // Setting to blank
            wp_delete_object_term_relationships( $post_id, 'location' );
        }

        // default status to 'instructed' if not set already
        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'instructed' );
        }

        do_action( 'propertyhive_save_property_address', $post_id );
    }

}
