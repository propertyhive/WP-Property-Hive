<?php
/**
 * Contact Relationships
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Relationships
 */
class PH_Meta_Box_Contact_Relationships {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        $total_profiles = 0;
        
        $owner_profiles = array();
        // get properties where this is the owner
        $args = array(
            'post_type' => 'property',
            'meta_query' => array(
                array(
                    'key' => '_owner_contact_id',
                    'value' => $post->ID,
                    'compare' => '='
                )
            )
        );
        
        $property_query = new WP_Query($args);
        
        if ($property_query->have_posts())
        {
            while ($property_query->have_posts())
            {
                $property_query->the_post();
                
                $owner_profiles[] = $post;
                
                ++$total_profiles;
            }
        }
        wp_reset_postdata();

        $applicant_profiles = array();
        $num_applicant_profiles = get_post_meta( $thepostid, '_applicant_profiles', TRUE );
        if ( $num_applicant_profiles == '' )
        {
            $num_applicant_profiles = 0;
        }

        if ( $num_applicant_profiles > 0 ) 
        {
            $total_profiles += $num_applicant_profiles;
            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                $applicant_profiles[] = get_post_meta( $thepostid, '_applicant_profile_' . $i, TRUE );
            }
        }

        $third_party_profiles = array();
        $third_party_categories = get_post_meta( $thepostid, '_third_party_categories', TRUE );
        if ( is_array($third_party_categories) && !empty($third_party_categories) )
        {
            $ph_third_party_contacts = new PH_Third_Party_Contacts();

            foreach ( $third_party_categories as $third_party_category )
            {
                $third_party_profiles[] = $third_party_category;

                ++$total_profiles;
            }
        }

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="panel-wrap">
            
            <div class="ph-tabs-back"></div>

                <ul class="contact_data_tabs ph-tabs">';
                
                $tab = 0;
                foreach ($owner_profiles as $property_post)
                {
                    $owner_type = __( 'Property Owner', 'propertyhive' );
                    $department = get_post_meta($property_post->ID, '_department', TRUE);
                    if ($department == 'lettings')
                    {
                        $owner_type = __( 'Property Landlord', 'propertyhive' );
                    }   
                    echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_property_data_' . $property_post->ID . '">'.$owner_type.'</a>
                    </li>';
                    
                    ++$tab;
                }

                foreach ($applicant_profiles as $key => $applicant_profile)
                {
                    $label = __( 'New Applicant', 'propertyhive' );
                    if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-sales' )
                    {
                        $label = __( 'Sales Applicant', 'propertyhive' );
                    }
                    elseif ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-lettings' )
                    {
                        $label = __( 'Lettings Applicant', 'propertyhive' );
                    }
                    echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_applicant_data_' . $key . '">' . $label . '</a>
                    </li>';
                    
                    ++$tab;
                }

                foreach ($third_party_profiles as $key => $third_party_profile)
                {
                    $label = __( 'New Third Party', 'propertyhive' );
                    if ( $third_party_profile != '' && $third_party_profile != 0 )
                    {
                        $category_name = $ph_third_party_contacts->get_category( $third_party_profile );
                        if ( $category_name !== false )
                        {
                            $label = $category_name;
                        }
                    }
                    echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_third_party_data_' . $key . '">' . $label . '</a>
                    </li>';
                    
                    ++$tab;
                }
                
                echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_add_relationship">' . __( 'Add Relationship', 'propertyhive' ) . '</a>
                    </li>';
                    
                echo '</ul>';
                
                $contact_id = $post->ID;
                
                $tab = 0;
                foreach ($owner_profiles as $property_post)
                {
                    $the_property = new PH_Property( $property_post->ID );
                    
                    echo '<div id="tab_property_data_' . $property_post->ID . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Address', 'propertyhive') . '</label>';
                            echo $the_property->get_formatted_summary_address('<br>');
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Price', 'propertyhive') . '</label>';
                            echo $the_property->get_formatted_price();
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Bedrooms', 'propertyhive') . '</label>';
                            echo $the_property->_bedrooms;
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Status', 'propertyhive') . '</label>';
                            echo ( ($the_property->_on_market == 'yes') ? __('On Market', 'propertyhive') : __('Not On Market', 'propertyhive') );
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label></label>';
                            echo '<a href="' . get_edit_post_link( $property_post->ID ) . '" class="button">' . __( 'View Property Record', 'propertyhive' ) . '</a>';
                        echo '</p>';
                        
                        echo '
                        </div>
                    </div>';
                    ++$tab;
                }

                foreach ($applicant_profiles as $key => $applicant_profile)
                {
                    echo '<div id="tab_applicant_data_' . $key . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        
                        <div class="options_group applicant-fields-' . $key . '" style="float:left; width:100%;">';
                        
                        $departments = array();
                        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
                        {
                            $departments['residential-sales'] = __( 'Buy', 'propertyhive' );
                        }
                        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
                        {
                            $departments['residential-lettings'] = __( 'Rent', 'propertyhive' );
                        }

                        $value = ( ( isset($applicant_profile['department']) && $applicant_profile['department'] != '' ) ? $applicant_profile['department'] : get_option( 'propertyhive_primary_department' ));
                        $args = array( 
                            'id' => '_applicant_department_' . $key,
                            'label' => 'Looking To',
                            'value' => $value,
                            'options' => $departments
                        );
                        if (count($departments) == 1)
                        {
                            foreach ($departments as $department_key => $value)
                            {
                                $args['value'] = $department_key;
                            }
                        }
                        propertyhive_wp_radio( $args );
                        
                        echo '<div id="propertyhive-applicant-residential-sales-details-' . $key . '">';

                        // Price
                        propertyhive_wp_text_input( array( 
                            'id' => '_applicant_maximum_price_' . $key, 
                            'label' => __( 'Maximum Price', 'propertyhive' ) . ' (&pound;)', 
                            'desc_tip' => false, 
                            'type' => 'text',
                            'class' => '',
                            'custom_attributes' => array(
                                'style' => 'width:100%; max-width:150px;'
                            ),
                            'value' => ( ( isset($applicant_profile['max_price']) ) ? $applicant_profile['max_price'] : '' )
                        ) );

                        echo '</div>';

                        echo '<div id="propertyhive-applicant-residential-lettings-details-' . $key . '">';

                        // Rent / Rent Frequency
                        $rent_frequency = ( ( isset($applicant_profile['rent_frequency']) ) ? $applicant_profile['rent_frequency'] : '' );
                        echo '<p class="form-field rent_field ">
                        
                            <label for="_applicant_maximum_rent_' . $key . '">' . __('Maximum Rent', 'propertyhive') . ' (&pound;)</label>
                            
                            <input type="text" class="" name="_applicant_maximum_rent_' . $key . '" id="_applicant_maximum_rent_' . $key . '" value="' . ( ( isset($applicant_profile['max_rent']) ) ? $applicant_profile['max_rent'] : '' ) . '" placeholder="" style="width:20%; max-width:150px;">
                            
                            <select id="_applicant_rent_frequency_' . $key . '" name="_applicant_rent_frequency_' . $key . '" class="select short">
                                <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
                                <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
                                <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
                                <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>
                            </select>
                            
                        </p>';

                        echo '</div>';

                        // Bedrooms
                        propertyhive_wp_text_input( array( 
                            'id' => '_applicant_minimum_bedrooms_' . $key, 
                            'label' => __( 'Minimum Bedrooms', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'type' => 'number',
                            'class' => '',
                            'custom_attributes' => array(
                                'style' => 'width:100%; max-width:75px;'
                            ),
                            'value' => ( ( isset($applicant_profile['min_beds']) ) ? $applicant_profile['min_beds'] : '' )
                        ) );

                        // Types
                    ?>
                        <p class="form-field"><label for="_applicant_property_types_<?php echo $key; ?>"><?php _e( 'Property Types', 'propertyhive' ); ?></label>
                        <select id="_applicant_property_types_<?php echo $key; ?>" name="_applicant_property_types_<?php echo $key; ?>[]" multiple="multiple" data-placeholder="Start typing to add property types..." class="multiselect attribute_values">
                            <?php
                                $options = array( '' => '' );
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => 0
                                );
                                $terms = get_terms( 'property_type', $args );
                                
                                $selected_values = array();
                                $term_list = ( ( isset($applicant_profile['property_types']) ) ? $applicant_profile['property_types'] : array() );
                                if ( is_array($term_list) && !empty($term_list) )
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
                                            }
                                        }
                                    }
                                }
                            ?>
                        </select></p>
                    <?php

                        // Locations
                    ?>
                        <p class="form-field"><label for="_applicant_locations_<?php echo $key; ?>"><?php _e( 'Locations', 'propertyhive' ); ?></label>
                        <select id="_applicant_locations_<?php echo $key; ?>" name="_applicant_locations_<?php echo $key; ?>[]" multiple="multiple" data-placeholder="Start typing to add location..." class="multiselect attribute_values">
                            <?php
                                $options = array( '' => '' );
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => 0
                                );
                                $terms = get_terms( 'location', $args );
                                
                                $selected_values = array();
                                $term_list = ( ( isset($applicant_profile['locations']) ) ? $applicant_profile['locations'] : array() );
                                if ( is_array($term_list) && !empty($term_list) )
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
                        </select></p>
                    <?php

                        // Bedrooms
                        propertyhive_wp_textarea_input( array( 
                            'id' => '_applicant_requirement_notes_' . $key, 
                            'label' => __( 'Additional Requirements', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'class' => '',
                            'value' => ( ( isset($applicant_profile['notes']) ) ? $applicant_profile['notes'] : '' )
                        ) );

                        echo '

                        </div>

                        <div class="actions">

                            <a 
                                href="#TB_inline?width=900&height=550&inlineId=view_matching_properties_' . $key . '" 
                                class="button thickbox view-matching-properties" 
                            >' . __('View Matching Properties', 'propertyhive') . '</a>

                            <a 
                                href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit#propertyhive-contact-relationships' ), $key, 'delete_applicant_relationship' ) . '" 
                                class="button"
                                onclick="var confirmBox = confirm(\'' . __('Are you sure you wish to delete this applicant relationship?', 'propertyhive') . '\'); return confirmBox;"
                            >' . __('Delete Relationship', 'propertyhive') . '</a>

                            <div id="view_matching_properties_' . $key . '" style="display:none;">
                                
                                <div class="loading-properties" style="text-align:center;"><br><br>Loading matching properties...</div>

                                <div class="need-to-save-changes" style="text-align:center;"><br><br>Please save your changes before viewing matching properties</div>

                                <div class="matching-properties" id="matching_properties_' . $key . '" style="display:none;">

                                </div>

                            </div>

                        </div>

                    </div>

                    <script>

                        var applicant_details_changed = false;
                        jQuery(document).ready(function()
                        {
                            showHideApplicantDepartmentMetaBox_' . $key . '();

                            jQuery(\'input[type=\\\'radio\\\'][name=\\\'_applicant_department_' . $key . '\\\']\').change(function()
                            {
                                 showHideApplicantDepartmentMetaBox_' . $key . '();
                            });

                            jQuery(\'.applicant-fields-' . $key . ' input, .applicant-fields-' . $key . ' select, .applicant-fields-' . $key . ' textarea\').change(function()
                            {
                                applicant_details_changed = true;
                            });

                            jQuery(\'a.view-matching-properties.thickbox\').click(function(e)
                            {
                                e.preventDefault();

                                jQuery(\'.matching-properties\').hide();
                                jQuery(\'.need-to-save-changes\').hide();
                                jQuery(\'.loading-properties\').show();

                                if (applicant_details_changed)
                                {
                                    jQuery(\'.matching-properties\').hide();
                                    jQuery(\'.need-to-save-changes\').show();
                                    jQuery(\'.loading-properties\').hide();
                                }
                                else
                                {
                                    // Make AJAX request for matching properties
                                    load_applicant_matching_properties_' . $key . '();
                                }

                            });
                        });
                        
                        function showHideApplicantDepartmentMetaBox_' . $key . '()
                        {
                            jQuery(\'#propertyhive-applicant-residential-sales-details-' . $key . '\').hide();
                            jQuery(\'#propertyhive-applicant-residential-lettings-details-' . $key . '\').hide();
                             
                            jQuery(\'#propertyhive-applicant-\' + jQuery(\'input[type=\\\'radio\\\'][name=\\\'_applicant_department_' . $key . '\\\']:checked\').val() + \'-details-' . $key . '\').show();
                        }

                        function load_applicant_matching_properties_' . $key . '()
                        {
                              // Do AJAX request
                              var data = {
                                  action:         \'propertyhive_load_applicant_matching_properties\',
                                  contact_id:        ' . $post->ID . ',
                                  applicant_profile:     ' . $key . ',
                                  security:       \'' . wp_create_nonce("load-applicant-matching-properties") . '\',
                              };
                    
                              jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) {

                                  jQuery(\'#matching_properties_' . $key . '\').html( response );

                                  jQuery(\'.matching-properties\').show();
                                  jQuery(\'.need-to-save-changes\').hide();
                                  jQuery(\'.loading-properties\').hide();
                                  
                              });
                        }

                    </script>';
                    ++$tab;
                }

                foreach ($third_party_profiles as $key => $category)
                {
                    echo '<div id="tab_third_party_data_' . $key . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';
                        
                        echo '<p class="form-field rent_field ">
                        
                            <label for="_third_party_category_' . $key . '">' . __('Contact Category', 'propertyhive') . '</label>
                            
                            <select id="_third_party_category_' . $key . '" name="_third_party_category[]" class="select short">';

                        if ( $category == '' || $category == 0 )
                        {
                            echo '<option value="0"></option>';
                        }

                        $categories = $ph_third_party_contacts->get_categories();
                        foreach ( $categories as $id => $category_name )
                        {
                            echo '<option value="' . $id . '"';
                            if ( $id == $category )
                            {
                                echo ' selected';
                            }
                            echo '>' . $category_name . '</option>';
                        }

                        echo '</select>
                            
                        </p>';

                        echo '
                        </div>
                    </div>';
                    ++$tab;
                }

                echo '<div id="tab_add_relationship" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                    <div class="options_group">';
                
                    echo '<p class="form-field">';
                        echo '<label>' . __('New Relationship Type', 'propertyhive') . '</label>';
                        echo '<a href="' . wp_nonce_url( admin_url( 'post.php?post=' . $thepostid . '&action=edit#propertyhive-contact-relationships' ), '1', 'add_applicant_relationship' ) . '" class="button">' . __( 'New Applicant Profile', 'propertyhive' ) . '</a><br><br>';
                        echo '<a href="' . admin_url( 'post-new.php?post_type=property&owner_contact_id=' . $thepostid ) . '" class="button">' . __( 'New Property Owner / Landlord', 'propertyhive' ) . '</a><br><br>';
                        echo '<a href="' . wp_nonce_url( admin_url( 'post.php?post=' . $thepostid . '&action=edit#propertyhive-contact-relationships' ), '1', 'add_third_party_relationship' ) . '" class="button">' . __( 'New Third Party Contact', 'propertyhive' ) . '</a>';
                    echo '</p>';
                
                echo '
                        </div>
                    </div>';
                
                echo '<div class="clear"></div>
            
        </div>';
        
        echo '</div>';
        
        //do_action('propertyhive_contact_relationships_fields');
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        $num_applicant_profiles = get_post_meta( $post_id, '_applicant_profiles', TRUE );
        if ( $num_applicant_profiles == '' )
        {
            $num_applicant_profiles = 0;
        }

        if ( $num_applicant_profiles > 0 )
        {
            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                $applicant_profile = array();
                $applicant_profile['department'] = $_POST['_applicant_department_' . $i];
                if ( $_POST['_applicant_department_' . $i] == 'residential-sales' )
                {
                    $price = preg_replace("/[^0-9]/", '', $_POST['_applicant_maximum_price_' . $i]);

                    $applicant_profile['max_price'] = $price;

                    // Not used yet but could be if introducing currencies in the future.
                    $applicant_profile['max_price_actual'] = $price;
                }
                elseif ( $_POST['_applicant_department_' . $i] == 'residential-lettings' )
                {
                    $rent = preg_replace("/[^0-9.]/", '', $_POST['_applicant_maximum_rent_' . $i]);

                    $applicant_profile['max_rent'] = $rent;
                    $applicant_profile['rent_frequency'] = $_POST['_applicant_rent_frequency_' . $i];

                    $price_actual = $rent; // Used for ordering properties. Stored in pcm
                    switch ($_POST['_applicant_rent_frequency_' . $i])
                    {
                        case "pw": { $price_actual = ($rent * 52) / 12; break; }
                        case "pcm": { $price_actual = $rent; break; }
                        case "pq": { $price_actual = ($rent * 4) / 52; break; }
                        case "pa": { $price_actual = ($rent / 52); break; }
                    }
                    $applicant_profile['max_price_actual'] = $price_actual;
                }

                $beds = preg_replace("/[^0-9]/", '', $_POST['_applicant_minimum_bedrooms_' . $i]);
                $applicant_profile['min_beds'] = $beds;

                if ( isset($_POST['_applicant_property_types_' . $i]) && is_array($_POST['_applicant_property_types_' . $i]) && !empty($_POST['_applicant_property_types_' . $i]) )
                {
                    $applicant_profile['property_types'] = $_POST['_applicant_property_types_' . $i];
                }

                if ( isset($_POST['_applicant_locations_' . $i]) && is_array($_POST['_applicant_locations_' . $i]) && !empty($_POST['_applicant_locations_' . $i]) )
                {
                    $applicant_profile['locations'] = $_POST['_applicant_locations_' . $i];
                }

                $applicant_profile['notes'] = $_POST['_applicant_requirement_notes_' . $i];

                update_post_meta( $post_id, '_applicant_profile_' . $i, $applicant_profile );
            }
        }

        $third_party_categories = array();
        if ( isset($_POST['_third_party_category']) && is_array($_POST['_third_party_category']) && !empty($_POST['_third_party_category']) )
        {
            foreach ( $_POST['_third_party_category'] as $category )
            {
                $third_party_categories[]  = $category;
            }
        }
        update_post_meta( $post_id, '_third_party_categories', $third_party_categories );
    }

}
