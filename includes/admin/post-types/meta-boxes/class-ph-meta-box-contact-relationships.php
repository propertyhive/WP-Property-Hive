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

        $original_post = $post;
        
        $total_profiles = 0;
        
        $owner_profiles = array();
        // get properties where this is the owner
        $args = array(
            'post_type' => 'property',
            'nopaging' => true,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_owner_contact_id',
                    'value' => $thepostid,
                    'compare' => '='
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':"' . $thepostid . '"',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':' . $thepostid . ';',
                    'compare' => 'LIKE'
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

        $potential_owner_profiles = array();
        // get appraisals where this is the owner and where not instructed
        $args = array(
            'post_type' => 'appraisal',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_property_owner_contact_id',
                    'value' => $thepostid,
                    'compare' => '='
                ),
                array(
                    'key' => '_status',
                    'value' => 'instructed',
                    'compare' => '!='
                )
            )
        );

        $appraisal_query = new WP_Query($args);
        
        if ($appraisal_query->have_posts())
        {
            while ($appraisal_query->have_posts())
            {
                $appraisal_query->the_post();
                
                $potential_owner_profiles[] = $post;
                
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
                    if ($department == 'residential-lettings')
                    {
                        $owner_type = __( 'Property Landlord', 'propertyhive' );
                    }   
                    echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_property_data_' . $property_post->ID . '">' . $owner_type . '</a>
                    </li>';
                    
                    ++$tab;
                }

                foreach ($potential_owner_profiles as $appraisal_post)
                {
                    $owner_type = __( 'Potential Owner', 'propertyhive' );
                    $department = get_post_meta($appraisal_post->ID, '_department', TRUE);
                    if ($department == 'residential-lettings')
                    {
                        $owner_type = __( 'Potential Landlord', 'propertyhive' );
                    }   
                    echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_appraisal_data_' . $appraisal_post->ID . '">' . $owner_type . ' (' . ucwords( str_replace("_", " ", get_post_meta( $appraisal_post->ID, '_status', TRUE ) ) ) . ')</a>
                    </li>';
                    
                    ++$tab;
                }

                $applicant_departments_count = array(
                    'residential-sales' => 0,
                    'residential-lettings' => 0,
                    'commercial' => 0,
                );
                foreach ($applicant_profiles as $key => $applicant_profile)
                {
                    $label = __( 'New Applicant', 'propertyhive' );

                    if ( isset($applicant_profile['department']) )
                    {
                        if ( isset($applicant_profile['relationship_name']) && $applicant_profile['relationship_name'] != '' )
                        {
                            $label = __( $applicant_profile['relationship_name'], 'propertyhive' );
                        }
                        else
                        {
                            if ( $applicant_profile['department'] == 'residential-sales' )
                            {
                                $label = __( 'Sales Applicant', 'propertyhive' );
                            }
                            elseif ( $applicant_profile['department'] == 'residential-lettings' )
                            {
                                $label = __( 'Lettings Applicant', 'propertyhive' );
                            }
                            elseif ( $applicant_profile['department'] == 'commercial' )
                            {
                                $label = __( 'Commercial Applicant', 'propertyhive' );
                            }
                        }

                        if ( isset($applicant_departments_count[$applicant_profile['department']]) )
                        {
                            ++$applicant_departments_count[$applicant_profile['department']];
                        }
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
                
                $contact_id = $thepostid;
                
                $tab = 0;
                foreach ($owner_profiles as $property_post)
                {
                    $the_property = new PH_Property( $property_post->ID );
                    
                    echo '<div id="tab_property_data_' . $property_post->ID . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Address', 'propertyhive') . '</label>';
                            echo $the_property->get_formatted_full_address('<br>');
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

                foreach ($potential_owner_profiles as $appraisal_post)
                {
                    $the_appraisal = new PH_Appraisal( $appraisal_post->ID );
                    
                    echo '<div id="tab_appraisal_data_' . $appraisal_post->ID . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Address', 'propertyhive') . '</label>';
                            echo ( ( $the_appraisal->get_formatted_full_address('<br>') != '' ) ? $the_appraisal->get_formatted_full_address('<br>') : '-' );
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Appraisal Status', 'propertyhive') . '</label>';
                            echo ucwords(str_replace("_", " ", $the_appraisal->status));
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label></label>';
                            echo '<a href="' . get_edit_post_link( $appraisal_post->ID ) . '" class="button">' . __( 'View Appraisal Record', 'propertyhive' ) . '</a>';
                        echo '</p>';
                        
                        echo '
                        </div>
                    </div>';
                    ++$tab;
                }

                $departments = ph_get_departments();

                foreach ($applicant_profiles as $key => $applicant_profile)
                {
                    echo '<div id="tab_applicant_data_' . $key . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        
                        <div class="options_group applicant-fields-' . $key . '" style="float:left; width:100%;">';
                        
                        $department_options = array();

                        foreach ( $departments as $department_key => $value )
                        {
                            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $department_key) ) == 'yes' )
                            {
                                $department_options[$department_key] = $value;
                            }
                        }

                        $value = ( ( isset($applicant_profile['department']) && $applicant_profile['department'] != '' ) ? $applicant_profile['department'] : get_option( 'propertyhive_primary_department' ));
                        $args = array( 
                            'id' => '_applicant_department_' . $key,
                            'label' => 'Looking For',
                            'value' => $value,
                            'options' => $department_options
                        );
                        if (count($department_options) == 1)
                        {
                            foreach ($department_options as $department_key => $value)
                            {
                                $args['value'] = $department_key;
                            }
                        }
                        propertyhive_wp_radio( $args );

                        echo '<div class="propertyhive-applicant-residential-sales-details-' . $key . '">';

                        // Display Relationship Name if it's already set
                        // Or we're editing a profile and at least two of this department exist
                        // Or we're creating a new profile and one of that department exist
                        // Or the filter is set that always displays the name field
                        if (
                            isset($applicant_profile['relationship_name'])
                            ||
                            ( isset($applicant_profile['department']) && $applicant_departments_count['residential-sales'] > 1 )
                            ||
                            ( !isset($applicant_profile['department']) && $applicant_departments_count['residential-sales'] > 0 )
                            ||
                            ( apply_filters( 'propertyhive_always_show_applicant_relationship_name', false ) === true )
                        ) {
                            // Relationship Name
                            propertyhive_wp_text_input( array(
                                'id' => '_relationship_name_residential-sales_' . $key,
                                'label' => __( 'Name', 'propertyhive' ),
                                'desc_tip' => false,
                                'type' => 'text',
                                'class' => '',
                                'custom_attributes' => array(
                                    'style' => 'width:100%; max-width:323px;'
                                ),
                                'placeholder' => 'Sales Applicant',
                                'value' => ( ( isset($applicant_profile['relationship_name']) ) ? $applicant_profile['relationship_name'] : '' )
                            ) );
                        }

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

                        $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
                        $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

                        if ( $percentage_lower != '' && $percentage_higher != '' )
                        {
                            $match_price_range_lower = '';
                            if ( !isset($applicant_profile['match_price_range_lower']) || ( isset($applicant_profile['match_price_range_lower']) && $applicant_profile['match_price_range_lower'] == '' ) )
                            {
                                if ( isset($applicant_profile['max_price']) && $applicant_profile['max_price'] != '' )
                                {
                                    if ( $percentage_lower != '' )
                                    {
                                        $match_price_range_lower = $applicant_profile['max_price'] - ( $applicant_profile['max_price'] * ( $percentage_lower / 100 ) );
                                    }
                                }
                            }
                            else
                            {
                                $match_price_range_lower = $applicant_profile['match_price_range_lower'];
                            }

                            $match_price_range_higher = '';
                            if ( !isset($applicant_profile['match_price_range_higher']) || ( isset($applicant_profile['match_price_range_higher']) && $applicant_profile['match_price_range_higher'] == '' ) )
                            {
                                if ( isset($applicant_profile['max_price']) && $applicant_profile['max_price'] != '' )
                                {
                                    if ( $percentage_higher != '' )
                                    {
                                        $match_price_range_higher = $applicant_profile['max_price'] + ( $applicant_profile['max_price'] * ( $percentage_higher / 100 ) );
                                    }
                                }
                            }
                            else
                            {
                                $match_price_range_higher = $applicant_profile['match_price_range_higher'];
                            }

                            echo '<p class="form-field applicant_match_price_range_field ">
                            
                                <label for="_applicant_match_price_range_' . $key . '">' . __('Match Price Range', 'propertyhive') . ' (&pound;)</label>
                                
                                <input type="text" class="" name="_applicant_match_price_range_lower_' . $key . '" id="_applicant_match_price_range_lower_' . $key . '" value="' . $match_price_range_lower . '" style="width:20%; max-width:150px;">
                                <span style="float:left; margin:0 5px;">to</span>
                                <input type="text" class="" name="_applicant_match_price_range_higher_' . $key . '" id="_applicant_match_price_range_higher_' . $key . '" value="' . $match_price_range_higher . '" style="width:20%; max-width:150px;">
                                
                            </p>';

                            echo '<script>

                                var previous_max_price_' . $key . ' = ' . ( ( isset($applicant_profile['max_price']) && $applicant_profile['max_price'] != '' ) ? $applicant_profile['max_price'] : '\'\'' ) . ';

                                jQuery(document).ready(function()
                                {
                                    jQuery(\'#_applicant_maximum_price_' . $key . '\').change(function()
                                    {
                                        if ( previous_max_price_' . $key . ' == \'\' )
                                        {
                                            if ( jQuery(this).val().replace(/\D/g, \'\') != \'\' && jQuery(\'#_applicant_match_price_range_lower_' . $key . '\').val().replace(/\D/g, \'\') == \'\' )
                                            {
                                                var max_price = jQuery(this).val().replace(/\D/g, \'\');

                                                max_price = parseInt(max_price) - parseInt( max_price * ( ' . $percentage_lower . ' / 100 ) );

                                                jQuery(\'#_applicant_match_price_range_lower_' . $key . '\').val(max_price);
                                            }

                                            if ( jQuery(this).val().replace(/\D/g, \'\') != \'\' && jQuery(\'#_applicant_match_price_range_higher_' . $key . '\').val().replace(/\D/g, \'\') == \'\' )
                                            {
                                                var max_price = jQuery(this).val().replace(/\D/g, \'\');

                                                max_price = parseInt(max_price) + parseInt( max_price * ( ' . $percentage_higher . ' / 100 ) );

                                                jQuery(\'#_applicant_match_price_range_higher_' . $key . '\').val(max_price);
                                            }
                                        }

                                        previous_max_price_' . $key . ' = jQuery(this).val();
                                    });
                                });

                            </script>';
                        }

                        echo '</div>';

                        echo '<div class="propertyhive-applicant-residential-lettings-details-' . $key . '">';

                        // Display Relationship Name if it's already set
                        // Or we're editing a profile and at least two of this department exist
                        // Or we're creating a new profile and one of that department exist
                        // Or the filter is set that always displays the name field
                        if (
                            isset($applicant_profile['relationship_name'])
                            ||
                            ( isset($applicant_profile['department']) && $applicant_departments_count['residential-lettings'] > 1 )
                            ||
                            ( !isset($applicant_profile['department']) && $applicant_departments_count['residential-lettings'] > 0 )
                            ||
                            ( apply_filters( 'propertyhive_always_show_applicant_relationship_name', false ) === true )
                        ) {
                            // Relationship Name
                            propertyhive_wp_text_input( array(
                                'id' => '_relationship_name_residential-lettings_' . $key,
                                'label' => __( 'Name', 'propertyhive' ),
                                'desc_tip' => false,
                                'type' => 'text',
                                'class' => '',
                                'custom_attributes' => array(
                                    'style' => 'width:100%; max-width:323px;'
                                ),
                                'placeholder' => 'Lettings Applicant',
                                'value' => ( ( isset($applicant_profile['relationship_name']) ) ? $applicant_profile['relationship_name'] : '' )
                            ) );
                        }

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

                        echo '<div class="propertyhive-applicant-residential-details-' . $key . '">';

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

                        // Residential Types
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

                        echo '</div>'; // end 'propertyhive-applicant-residential-details-' . $key

                        echo '<div class="propertyhive-applicant-commercial-details-' . $key . '">';

                        // Display Relationship Name if it's already set
                        // Or we're editing a profile and at least two of this department exist
                        // Or we're creating a new profile and one of that department exist
                        // Or the filter is set that always displays the name field
                        if (
                            isset($applicant_profile['relationship_name'])
                            ||
                            ( isset($applicant_profile['department']) && $applicant_departments_count['commercial'] > 1 )
                            ||
                            ( !isset($applicant_profile['department']) && $applicant_departments_count['commercial'] > 0 )
                            ||
                            ( apply_filters( 'propertyhive_always_show_applicant_relationship_name', false ) === true )
                        ) {
                            // Relationship Name
                            propertyhive_wp_text_input( array(
                                'id' => '_relationship_name_commercial_' . $key,
                                'label' => __( 'Name', 'propertyhive' ),
                                'desc_tip' => false,
                                'type' => 'text',
                                'class' => '',
                                'custom_attributes' => array(
                                    'style' => 'width:100%; max-width:323px;'
                                ),
                                'placeholder' => 'Commercial Applicant',
                                'value' => ( ( isset($applicant_profile['relationship_name']) ) ? $applicant_profile['relationship_name'] : '' ),
                            ) );
                        }

                        $args = array( 
                            'id' => '_applicant_available_as_' . $key,
                            'label' => 'Available As',
                            'value' => array('sale', 'rent'),
                            'options' => array(
                                'sale' => 'For Sale',
                                'rent' => 'To Rent'
                            )
                        );
                        if ( isset($applicant_profile['available_as']) && is_array($applicant_profile['available_as']) )
                        {
                            $args['value'] = array();
                            foreach ($applicant_profile['available_as'] as $value)
                            {
                                $args['value'][] = $value;
                            }
                        }
                        propertyhive_wp_checkboxes( $args );

                        // Floor Area
                        propertyhive_wp_text_input( array( 
                            'id' => '_applicant_minimum_floor_area_' . $key, 
                            'label' => __( 'Min Floor Area', 'propertyhive' ) . ' (Sq Ft)', 
                            'desc_tip' => false, 
                            'type' => 'text',
                            'class' => '',
                            'custom_attributes' => array(
                                'style' => 'width:100%; max-width:150px;'
                            ),
                            'value' => ( ( isset($applicant_profile['min_floor_area']) ) ? $applicant_profile['min_floor_area'] : '' )
                        ) );

                        propertyhive_wp_text_input( array( 
                            'id' => '_applicant_maximum_floor_area_' . $key, 
                            'label' => __( 'Max Floor Area', 'propertyhive' ) . ' (Sq Ft)', 
                            'desc_tip' => false, 
                            'type' => 'text',
                            'class' => '',
                            'custom_attributes' => array(
                                'style' => 'width:100%; max-width:150px;'
                            ),
                            'value' => ( ( isset($applicant_profile['max_floor_area']) ) ? $applicant_profile['max_floor_area'] : '' )
                        ) );

                        // Commercial Types
                    ?>
                        <p class="form-field"><label for="_applicant_commercial_property_types_<?php echo $key; ?>"><?php _e( 'Property Types', 'propertyhive' ); ?></label>
                        <select id="_applicant_commercial_property_types_<?php echo $key; ?>" name="_applicant_commercial_property_types_<?php echo $key; ?>[]" multiple="multiple" data-placeholder="Start typing to add property types..." class="multiselect attribute_values">
                            <?php
                                $options = array( '' => '' );
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => 0
                                );
                                $terms = get_terms( 'commercial_property_type', $args );
                                
                                $selected_values = array();
                                $term_list = ( ( isset($applicant_profile['commercial_property_types']) ) ? $applicant_profile['commercial_property_types'] : array() );
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

                        echo '</div>'; // end 'propertyhive-applicant-commercial-details-' . $key

                        // Locations
                        if ( get_option('propertyhive_applicant_locations_type') != 'text' )
                        {
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
                        }
                        else
                        {
                            propertyhive_wp_text_input( array(
                                'id' => '_applicant_location_text_' . $key,
                                'label' => __( 'Location', 'propertyhive' ),
                                'desc_tip' => false,
                                'type' => 'text',
                                'class' => '',
                                'value' => ( ( isset($applicant_profile['location_text']) ) ? $applicant_profile['location_text'] : '' )
                            ) );
                        }

                        do_action('propertyhive_contact_applicant_requirements_details_fields', $thepostid, $key);

                        // Additional Requirement Notes
                        propertyhive_wp_textarea_input( array( 
                            'id' => '_applicant_requirement_notes_' . $key, 
                            'label' => __( 'Additional Requirements', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'class' => '',
                            'value' => ( ( isset($applicant_profile['notes']) ) ? $applicant_profile['notes'] : '' )
                        ) );

                        propertyhive_wp_checkbox( array( 
                            'id' => '_send_matching_properties_' . $key, 
                            'label' => __( 'Send Matching Properties', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => ( ( ( isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] == 'yes' ) || !isset($applicant_profile['send_matching_properties']) ) ? 'yes' : '' )
                        ) );

                        $auto_property_match = get_option( 'propertyhive_auto_property_match', '' );

                        if ( $auto_property_match == 'yes' )
                        {
                            // Auto match emails are enabled. Add ability to disable on per-applicant basis
                            propertyhive_wp_checkbox( array( 
                                'id' => '_auto_match_disabled_' . $key, 
                                'label' => __( 'Disable Auto-Match', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => ( ( isset($applicant_profile['auto_match_disabled']) && $applicant_profile['auto_match_disabled'] == 'yes' ) ? 'yes' : '' )
                            ) );
                        }

                        propertyhive_wp_checkbox( array( 
                            'id' => '_grading_' . $key, 
                            'label' => __( 'Hot Applicant', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => ( ( ( isset($applicant_profile['grading']) && $applicant_profile['grading'] == 'hot' ) ) ? 'yes' : '' )
                        ) );

                        echo '

                        </div>

                        <div class="actions">

                            <a 
                                href="' . admin_url('admin.php?page=ph-matching-properties&contact_id=' . $contact_id . '&applicant_profile=' . $key) . '" 
                                class="button view-matching-properties-' . $key . '" 
                                ' . ( ( isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] == '' ) ? ' disabled title="Send Matching Properties not selected"' : '' ) . '
                            >' . __('View Matching Properties', 'propertyhive') . '</a>

                            <a 
                                href="' . wp_nonce_url( admin_url( 'post.php?post=' . $contact_id . '&action=edit#propertyhive-contact-relationships' ), $key, 'delete_applicant_relationship' ) . '" 
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

                        var applicant_details_changed_' . $key . ' = false;
                        jQuery(document).ready(function()
                        {
                            showHideApplicantDepartmentMetaBox_' . $key . '();

                            jQuery(\'input[type=\\\'radio\\\'][name=\\\'_applicant_department_' . $key . '\\\']\').change(function()
                            {
                                 showHideApplicantDepartmentMetaBox_' . $key . '();
                            });

                            jQuery(\'.applicant-fields-' . $key . ' input, .applicant-fields-' . $key . ' select, .applicant-fields-' . $key . ' textarea\').change(function()
                            {
                                applicant_details_changed_' . $key . ' = true;
                            });

                            jQuery(\'a.view-matching-properties-' . $key . '\').click(function(e)
                            {
                                if (applicant_details_changed_' . $key . ')
                                {
                                    alert(\'You\\\'ve made changes to the requirements. Please save the changes before viewing matching properties\');
                                    return false;
                                }

                                return true;
                            });
                        });
                        
                        function showHideApplicantDepartmentMetaBox_' . $key . '()
                        {
                            jQuery(\'.propertyhive-applicant-residential-details-' . $key . '\').hide();
                            jQuery(\'.propertyhive-applicant-residential-sales-details-' . $key . '\').hide();
                            jQuery(\'.propertyhive-applicant-residential-lettings-details-' . $key . '\').hide();
                            jQuery(\'.propertyhive-applicant-commercial-details-' . $key . '\').hide();
                            
                            switch (jQuery(\'input[type=\\\'radio\\\'][name=\\\'_applicant_department_' . $key . '\\\']:checked\').val())
                            {
                                case "residential-sales":
                                {
                                    jQuery(\'.propertyhive-applicant-residential-details-' . $key . '\').show();
                                    jQuery(\'.propertyhive-applicant-residential-sales-details-' . $key . '\').show();
                                    break;
                                }
                                case "residential-lettings":
                                {
                                    jQuery(\'.propertyhive-applicant-residential-details-' . $key . '\').show();
                                    jQuery(\'.propertyhive-applicant-residential-lettings-details-' . $key . '\').show();
                                    break;
                                }
                                case "commercial":
                                {
                                    jQuery(\'.propertyhive-applicant-commercial-details-' . $key . '\').show();
                                    break;
                                }
                            }
                            
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

                        echo '<option value=""></option>';

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

        $post = $original_post;
        
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

        $hot_applicant = ''; // use this to set a global meta key on the contact so applicants can be filtered by hot
        if ( $num_applicant_profiles > 0 )
        {
            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                $existing_applicant_profile = get_post_meta( $post_id, '_applicant_profile_' . $i, TRUE );

                $applicant_profile = array();
                $applicant_profile['department'] = ph_clean($_POST['_applicant_department_' . $i]);
                if ( $_POST['_applicant_department_' . $i] == 'residential-sales' )
                {
                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['_applicant_maximum_price_' . $i]));

                    $applicant_profile['max_price'] = $price;

                    // Not used yet but could be if introducing currencies in the future.
                    $applicant_profile['max_price_actual'] = $price;

                    if ( $price != '' && $price != 0 )
                    {
                        if ( isset($_POST['_applicant_match_price_range_lower_' . $i]) )
                        {
                            $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['_applicant_match_price_range_lower_' . $i]));
                            $applicant_profile['match_price_range_lower'] = $price;
                            $applicant_profile['match_price_range_lower_actual'] = $price;
                        }

                        if ( isset($_POST['_applicant_match_price_range_higher_' . $i]) )
                        {
                            $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['_applicant_match_price_range_higher_' . $i]));
                            $applicant_profile['match_price_range_higher'] = $price;
                            $applicant_profile['match_price_range_higher_actual'] = $price;
                        }
                    }
                }
                elseif ( $_POST['_applicant_department_' . $i] == 'residential-lettings' )
                {
                    $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_applicant_maximum_rent_' . $i]));

                    $applicant_profile['max_rent'] = $rent;
                    $applicant_profile['rent_frequency'] = ph_clean($_POST['_applicant_rent_frequency_' . $i]);

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

                if ( $_POST['_applicant_department_' . $i] == 'residential-sales' || $_POST['_applicant_department_' . $i] == 'residential-lettings' )
                {
                    $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['_applicant_minimum_bedrooms_' . $i]));
                    $applicant_profile['min_beds'] = $beds;

                    if ( isset($_POST['_applicant_property_types_' . $i]) && is_array($_POST['_applicant_property_types_' . $i]) && !empty($_POST['_applicant_property_types_' . $i]) )
                    {
                        $applicant_profile['property_types'] = ph_clean($_POST['_applicant_property_types_' . $i]);
                    }
                }

                if ( $_POST['_applicant_department_' . $i] == 'commercial' )
                {
                    $applicant_profile['available_as'] = ( isset($_POST['_applicant_available_as_' . $i]) && !empty($_POST['_applicant_available_as_' . $i]) ) ? ph_clean($_POST['_applicant_available_as_' . $i]) : array();

                    $floor_area = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_applicant_minimum_floor_area_' . $i]));
                    $applicant_profile['min_floor_area'] = $floor_area;
                    $applicant_profile['min_floor_area_actual'] = $floor_area;

                    $floor_area = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_applicant_maximum_floor_area_' . $i]));
                    $applicant_profile['max_floor_area'] = $floor_area;
                    $applicant_profile['max_floor_area_actual'] = $floor_area;

                    $applicant_profile['floor_area_units'] = 'sqft';

                    if ( isset($_POST['_applicant_commercial_property_types_' . $i]) && is_array($_POST['_applicant_commercial_property_types_' . $i]) && !empty($_POST['_applicant_commercial_property_types_' . $i]) )
                    {
                        $applicant_profile['commercial_property_types'] = ph_clean($_POST['_applicant_commercial_property_types_' . $i]);
                    }
                }

                if ( isset($_POST['_relationship_name_' . $_POST['_applicant_department_' . $i] . '_' . $i]) &&  $_POST['_relationship_name_' . $_POST['_applicant_department_' . $i] . '_' . $i] != '')
                {
                    $applicant_profile['relationship_name'] = ph_clean($_POST['_relationship_name_' . $_POST['_applicant_department_' . $i] . '_' . $i]);
                }

                if ( get_option('propertyhive_applicant_locations_type') != 'text' )
                {
                    if ( isset($_POST['_applicant_locations_' . $i]) && is_array($_POST['_applicant_locations_' . $i]) && !empty($_POST['_applicant_locations_' . $i]) )
                    {
                        $applicant_profile['locations'] = ph_clean($_POST['_applicant_locations_' . $i]);
                    }

                    // If the other type of location is set for this applicant, retain that data
                    if ( !empty($existing_applicant_profile) && isset($existing_applicant_profile['location_text']) && $existing_applicant_profile['location_text'] != '' )
                    {
                        $applicant_profile['location_text'] = ph_clean($existing_applicant_profile['location_text']);
                    }
                }
                else
                {
                    if ( isset($_POST['_applicant_location_text_' . $i]) && !empty($_POST['_applicant_location_text_' . $i]) )
                    {
                        $applicant_profile['location_text'] = ph_clean($_POST['_applicant_location_text_' . $i]);
                    }

                    // If the other type of location is set for this applicant, retain that data
                    if ( !empty($existing_applicant_profile) && isset($existing_applicant_profile['locations']) && !empty($existing_applicant_profile['locations']) )
                    {
                        $applicant_profile['locations'] = ph_clean($existing_applicant_profile['locations']);
                    }
                }

                $applicant_profile['notes'] = sanitize_textarea_field($_POST['_applicant_requirement_notes_' . $i]);

                $applicant_profile['send_matching_properties'] = ( ( isset($_POST['_send_matching_properties_' . $i]) ) ? ph_clean($_POST['_send_matching_properties_' . $i]) : '' );
                $applicant_profile['auto_match_disabled'] = ( ( isset($_POST['_auto_match_disabled_' . $i]) ) ? ph_clean($_POST['_auto_match_disabled_' . $i]) : '' );

                $applicant_profile['grading'] = ( isset($_POST['_grading_' . $i]) && ph_clean($_POST['_grading_' . $i]) == 'yes' ) ? 'hot' : '';
                if ( isset($_POST['_grading_' . $i]) && ph_clean($_POST['_grading_' . $i]) == 'yes' )
                {
                    $hot_applicant = 'yes';
                }

                update_post_meta( $post_id, '_applicant_profile_' . $i, $applicant_profile );

                do_action( 'propertyhive_save_contact_applicant_requirements', $post_id, $i );
            }
        }

        update_post_meta( $post_id, '_hot_applicant', $hot_applicant );

        $third_party_categories = array();
        if ( isset($_POST['_third_party_category']) && is_array($_POST['_third_party_category']) && !empty($_POST['_third_party_category']) )
        {
            foreach ( $_POST['_third_party_category'] as $category )
            {
                if ( ph_clean($category) != '' )
                {
                    $third_party_categories[] = (int)$category;
                }
            }
        }
        update_post_meta( $post_id, '_third_party_categories', $third_party_categories );

        do_action( 'propertyhive_save_contact_relationships', $post_id );
    }

}
