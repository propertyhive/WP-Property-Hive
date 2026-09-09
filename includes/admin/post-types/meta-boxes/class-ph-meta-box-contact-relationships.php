<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Contact Relationships
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Relationships
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Contact_Relationships; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Contact_Relationships
{

    /**
     * Output the metabox
     */
    public static function output($post)
    {
        global $post, $wpdb, $thepostid;

        $original_post = $post;

        $total_profiles = 0;

        $owner_profiles = array();
        // get properties where this is the owner
        $args = array(
            'post_type' => 'property',
            'nopaging' => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- The admin relationship panel must load every linked property; this OR matches both scalar and serialized legacy owner IDs, and no bounded query preserves the complete panel contract.
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

        if ($property_query->have_posts()) {
            while ($property_query->have_posts()) {
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
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- The admin relationship panel must load every linked appraisal; the status condition is part of the existing relationship contract and the full result set is required for its tabs.
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

        if ($appraisal_query->have_posts()) {
            while ($appraisal_query->have_posts()) {
                $appraisal_query->the_post();

                $potential_owner_profiles[] = $post;

                ++$total_profiles;
            }
        }

        wp_reset_postdata();

        $applicant_profiles = array();
        $num_applicant_profiles = get_post_meta($thepostid, '_applicant_profiles', TRUE);
        if ($num_applicant_profiles == '') {
            $num_applicant_profiles = 0;
        }

        if ($num_applicant_profiles > 0) {
            $total_profiles += $num_applicant_profiles;
            for ($i = 0; $i < $num_applicant_profiles; ++$i) {
                $applicant_profiles[] = get_post_meta($thepostid, '_applicant_profile_' . $i, TRUE);
            }
        }

        $third_party_profiles = array();
        $third_party_categories = get_post_meta($thepostid, '_third_party_categories', TRUE);
        if (is_array($third_party_categories) && !empty($third_party_categories)) {
            $ph_third_party_contacts = new PH_Third_Party_Contacts();

            foreach ($third_party_categories as $third_party_category) {
                $third_party_profiles[] = $third_party_category;

                ++$total_profiles;
            }
        }

        echo '<div class="propertyhive_meta_box">';

        echo '<div class="panel-wrap">
            
            <div class="ph-tabs-back"></div>

                <ul class="contact_data_tabs ph-tabs">';

        $tab = 0;
        foreach ($owner_profiles as $property_post) {
            $owner_type = __('Property Owner', 'propertyhive');
            $department = get_post_meta($property_post->ID, '_department', TRUE);
            if ($department == 'residential-lettings' || ph_get_custom_department_based_on($department) == 'residential-lettings') {
                $owner_type = __('Property Landlord', 'propertyhive');
            }
            echo '<li class="property_tab' . (($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_property_data_' . esc_attr($property_post->ID) . '">' . esc_html($owner_type) . '</a>
                    </li>';

            ++$tab;
        }

        foreach ($potential_owner_profiles as $appraisal_post) {
            $owner_type = __('Potential Owner', 'propertyhive');
            $department = get_post_meta($appraisal_post->ID, '_department', TRUE);
            if ($department == 'residential-lettings' || ph_get_custom_department_based_on($department) == 'residential-lettings') {
                $owner_type = __('Potential Landlord', 'propertyhive');
            }
            echo '<li class="property_tab' . (($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_appraisal_data_' . esc_attr($appraisal_post->ID) . '">' . esc_html($owner_type . ' (' . ucwords(str_replace("_", " ", get_post_meta($appraisal_post->ID, '_status', TRUE)))) . ')</a>
                    </li>';

            ++$tab;
        }

        $applicant_departments_count = array(
            'residential-sales' => 0,
            'residential-lettings' => 0,
            'commercial' => 0,
        );
        foreach ($applicant_profiles as $key => $applicant_profile) {
            $key = (int) $key;

            $label = __('New Applicant', 'propertyhive');

            if (isset($applicant_profile['department'])) {
                if (isset($applicant_profile['relationship_name']) && $applicant_profile['relationship_name'] != '') {
                    $label = $applicant_profile['relationship_name'];
                } else {
                    $label = /* translators: %s: Applicant department. */ sprintf( __( '%s Applicant', 'propertyhive' ), ucwords( str_replace( '-', ' ', str_replace( 'residential-', '', $applicant_profile['department'] ) ) ) );
                }

                if (isset($applicant_departments_count[$applicant_profile['department']])) {
                    ++$applicant_departments_count[$applicant_profile['department']];
                }
            }
            echo '<li class="property_tab' . (($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_applicant_data_' . esc_attr($key) . '">' . esc_html($label) . '</a>
                    </li>';

            ++$tab;
        }

        foreach ($third_party_profiles as $key => $third_party_profile) {
            $label = __('New Third Party', 'propertyhive');
            if ($third_party_profile != '' && $third_party_profile != 0) {
                $category_name = $ph_third_party_contacts->get_category($third_party_profile);
                if ($category_name !== false) {
                    $label = $category_name;
                }
            }
            echo '<li class="property_tab' . (($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_third_party_data_' . esc_attr($key) . '">' . esc_html($label) . '</a>
                    </li>';

            ++$tab;
        }

        echo '<li class="property_tab' . (($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_add_relationship">' . esc_html(__('Add Relationship', 'propertyhive')) . '</a>
                    </li>';

        echo '</ul>';

        $contact_id = $thepostid;

        $tab = 0;
        foreach ($owner_profiles as $property_post) {
            $the_property = new PH_Property($property_post->ID);

            echo '<div id="tab_property_data_' . esc_attr($property_post->ID) . '" class="panel propertyhive_options_panel" style="' . (($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';

            echo '<p class="form-field">';
            echo '<label>' . esc_html(__('Address', 'propertyhive')) . '</label>';
            echo wp_kses_post( $the_property->get_formatted_full_address('<br>') );
            echo '</p>';

            echo '<p class="form-field">';
            echo '<label>' . esc_html(__('Price', 'propertyhive')) . '</label>';
            echo esc_html($the_property->get_formatted_price());
            echo '</p>';

            echo '<p class="form-field">';
            echo '<label>' . esc_html(__('Bedrooms', 'propertyhive')) . '</label>';
            echo esc_html($the_property->_bedrooms);
            echo '</p>';

            echo '<p class="form-field">';
            echo '<label>' . esc_html(__('Status', 'propertyhive')) . '</label>';
            echo (($the_property->_on_market == 'yes') ? esc_html(__('On Market', 'propertyhive')) : esc_html(__('Not On Market', 'propertyhive')));
            echo '</p>';

            echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="' . esc_attr(get_edit_post_link($property_post->ID)) . '" class="button">' . esc_html(__('View Property Record', 'propertyhive')) . '</a>';
            echo '</p>';

            echo '
                        </div>
                    </div>';
            ++$tab;
        }

        foreach ($potential_owner_profiles as $appraisal_post) {
            $the_appraisal = new PH_Appraisal($appraisal_post->ID);

            echo '<div id="tab_appraisal_data_' . esc_attr($appraisal_post->ID) . '" class="panel propertyhive_options_panel" style="' . (($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';

            echo '<p class="form-field">';
            echo '<label>' . esc_html(__('Address', 'propertyhive')) . '</label>';
            echo wp_kses_post( ($the_appraisal->get_formatted_full_address('<br>') != '') ? $the_appraisal->get_formatted_full_address('<br>') : '-' );
            echo '</p>';

            echo '<p class="form-field">';
            echo '<label>' . esc_html(__('Appraisal Status', 'propertyhive')) . '</label>';
            echo esc_html(ucwords(str_replace("_", " ", $the_appraisal->status)));
            echo '</p>';

            echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="' . esc_attr(get_edit_post_link($appraisal_post->ID)) . '" class="button">' . esc_html(__('View Appraisal Record', 'propertyhive')) . '</a>';
            echo '</p>';

            echo '
                        </div>
                    </div>';
            ++$tab;
        }

        $departments = ph_get_departments();

        foreach ($applicant_profiles as $key => $applicant_profile) {
            echo '<div id="tab_applicant_data_' . esc_attr($key) . '" class="panel propertyhive_options_panel" style="' . (($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        
                        <div class="options_group applicant-fields-' . esc_attr($key) . '" style="float:left; width:100%;">';

            // This profile was created automatically from the Create Applicant button on an enquiry
            if (isset($applicant_profile['added_from_enquiry']) && $applicant_profile['added_from_enquiry'] == 'yes') {
                echo '<div><p style="color:red;" >This applicant profile has been automatically created from an enquiry. Please confirm the requirements below are accurate.</p></div>';
            }

            $department_options = array();

            foreach ($departments as $department_key => $value) {
                if (get_option('propertyhive_active_departments_' . str_replace("residential-", "", $department_key)) == 'yes') {
                    $department_options[$department_key] = $value;
                }
            }

            $value = ((isset($applicant_profile['department']) && $applicant_profile['department'] != '') ? $applicant_profile['department'] : get_option('propertyhive_primary_department'));
            $args = array(
                'id' => '_applicant_department_' . $key,
                'label' => 'Looking For',
                'value' => $value,
                'options' => $department_options
            );
            if (count($department_options) == 1) {
                foreach ($department_options as $department_key => $value) {
                    $args['value'] = $department_key;
                }
            }
            propertyhive_wp_radio($args);

            echo '<div class="propertyhive-applicant-residential-sales-details-' . esc_attr($key) . '">';

            // Display Relationship Name if it's already set
            // Or we're editing a profile and at least two of this department exist
            // Or we're creating a new profile and one of that department exist
            // Or the filter is set that always displays the name field
            if (
                isset($applicant_profile['relationship_name'])
                ||
                (isset($applicant_profile['department']) && $applicant_departments_count['residential-sales'] > 1)
                ||
                (!isset($applicant_profile['department']) && $applicant_departments_count['residential-sales'] > 0)
                ||
                (apply_filters('propertyhive_always_show_applicant_relationship_name', false) === true)
            ) {
                // Relationship Name
                propertyhive_wp_text_input(array(
                    'id' => '_relationship_name_residential-sales_' . $key,
                    'label' => __('Name', 'propertyhive'),
                    'desc_tip' => false,
                    'type' => 'text',
                    'class' => '',
                    'custom_attributes' => array(
                        'style' => 'width:100%; max-width:323px;'
                    ),
                    'placeholder' => 'Sales Applicant',
                    'value' => ((isset($applicant_profile['relationship_name'])) ? $applicant_profile['relationship_name'] : '')
                ));
            }

            // Currency / Price
            $ph_countries = new PH_Countries();
            $default_country = get_option('propertyhive_default_country', 'GB');
            $selected_currency = 'GBP';
            $countries = get_option('propertyhive_countries', array($default_country));
            $currencies = array();

            // default
            $country = $ph_countries->get_country($default_country);

            if ( !isset($currencies[$country['currency_code']]) ) 
            {
                $currencies[$country['currency_code']] = $country['currency_symbol'];
                $selected_currency = $country['currency_code'];
            }

            // other countries
            foreach ( $countries as $country ) 
            {
                $country = $ph_countries->get_country($country);

                if ( !isset($currencies[$country['currency_code']]) ) 
                {
                    $currencies[$country['currency_code']] = $country['currency_symbol'];
                }
            }

            if ( isset($applicant_profile['currency']) && !empty($applicant_profile['currency']) )
            {
                $selected_currency = $applicant_profile['currency'];
            }
            else
            {
                // No currency. Check if existing applicant and set selected currency accordingly
                if ( isset($applicant_profile['max_price']) )
                {
                    // Existing applicant
                    $selected_currency = 'GBP';
                }
            }

            if ( !isset($currencies[$selected_currency]) ) 
            {
                // currency set but not in list of active currencies. Get previously set currency

                $current_currency = $ph_countries->get_currency( $selected_currency );
                if ( $current_currency !== false )
                {
                    $currencies[$selected_currency] = $current_currency['currency_symbol'];
                }
            }

            // Price
            echo '<p class="form-field">';
            echo '<label for="_applicant_maximum_price_' . esc_attr($key) . '">' . esc_html(__('Maximum Price', 'propertyhive')) . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol maximum-price-currency-symbol">' . ( isset($currencies[$selected_currency]) ? esc_html( $currencies[$selected_currency] ) : '' ) . '</span>)' : '' ) . '</label>';

            if ( count($currencies) > 1 )
            {
                echo '<select id="_applicant_currency_sales_' . esc_attr($key) . '" name="_applicant_currency_sales_' . esc_attr($key) . '" class="select" style="width:auto; float:left;">';
                foreach ( $currencies as $currency_code => $currency_symbol ) 
                {
                    echo '<option value="' . esc_attr($currency_code) . '"' . (($currency_code == $selected_currency) ? ' selected' : '') . '>' . esc_html( $currency_symbol ) . '</option>';
                }
                echo '</select>';
            }
            else
            {
                echo '<input type="hidden" name="_applicant_currency_sales_' . esc_attr($key) .  '" id="_applicant_currency_sales_' . esc_attr($key) .'" value="' . esc_attr($selected_currency) . '">';
            }
            echo '<input type="text" class="" name="_applicant_maximum_price_' . esc_attr($key) . '" id="_applicant_maximum_price_' . esc_attr($key) . '" value="' . ( isset($applicant_profile['max_price']) ? esc_attr( ph_display_price_field($applicant_profile['max_price']) ) : '' ) . '" placeholder="" style="width:100%; max-width:150px;">';
            echo '</p>';

            $percentage_lower = get_option('propertyhive_applicant_match_price_range_percentage_lower', '');
            $percentage_higher = get_option('propertyhive_applicant_match_price_range_percentage_higher', '');

            if ( $percentage_lower != '' && $percentage_higher != '' ) 
            {
                $match_price_range_lower = '';
                if ( 
                    !isset($applicant_profile['match_price_range_lower']) || 
                    ( isset($applicant_profile['match_price_range_lower']) && $applicant_profile['match_price_range_lower'] == '' ) 
                ) 
                {
                    if ( isset($applicant_profile['max_price']) && $applicant_profile['max_price'] != '' ) 
                    {
                        if ( $percentage_lower != '' ) 
                        {
                            $match_price_range_lower = $applicant_profile['max_price'] - ($applicant_profile['max_price'] * ($percentage_lower / 100));
                        }
                    }
                }
                else
                {
                    $match_price_range_lower = $applicant_profile['match_price_range_lower'];
                }

                $match_price_range_higher = '';
                if (
                    !isset($applicant_profile['match_price_range_higher']) ||
                    ( isset($applicant_profile['match_price_range_higher']) && $applicant_profile['match_price_range_higher'] == '' )
                ) 
                {
                    if ( isset($applicant_profile['max_price']) && $applicant_profile['max_price'] != '' ) 
                    {
                        if ( $percentage_higher != '' ) 
                        {
                            $match_price_range_higher = $applicant_profile['max_price'] + ($applicant_profile['max_price'] * ($percentage_higher / 100));
                        }
                    }
                }
                else
                {
                    $match_price_range_higher = $applicant_profile['match_price_range_higher'];
                }

                echo '<p class="form-field applicant_match_price_range_field ">
                            
                    <label for="_applicant_match_price_range_' . esc_attr($key) . '">' . esc_html(__('Match Price Range', 'propertyhive')) . ' (<span class="currency-symbol price-range-currency-symbol">' . esc_html( $currencies[$selected_currency] ) . '</span>)</label>
                    
                    <input type="text" class="" name="_applicant_match_price_range_lower_' . esc_attr($key) . '" id="_applicant_match_price_range_lower_' . esc_attr($key) . '" value="' . esc_attr(ph_display_price_field($match_price_range_lower)) . '" style="width:20%; max-width:150px;">
                    <span style="float:left; margin:0 5px;">to</span>
                    <input type="text" class="" name="_applicant_match_price_range_higher_' . esc_attr($key) . '" id="_applicant_match_price_range_higher_' . esc_attr($key) . '" value="' . esc_attr(ph_display_price_field($match_price_range_higher)) . '" style="width:20%; max-width:150px;">
                    &nbsp;
                    <a href="" id="match_price_range_shortcut_' . esc_attr($key) . '" style="display:none">' . esc_html(__('Update', 'propertyhive')) . '</a>
                    
                </p>';

                echo '<script>

                                var previous_max_price_' . esc_js($key) . ' = ' . wp_json_encode( ( isset($applicant_profile['max_price']) && is_numeric($applicant_profile['max_price']) ) ? (float) $applicant_profile['max_price'] : '' ) . ';

                                jQuery(document).ready(function()
                                {
                                    jQuery(\'#match_price_range_shortcut_' . esc_js($key) . '\').click(function(e)
                                    {
                                        e.preventDefault();

                                        if ( jQuery(\'#_applicant_maximum_price_' . esc_js($key) . '\').val().replace(/[^\d.-]/g, \'\') != \'\' )
                                        {
                                            var max_price = jQuery(\'#_applicant_maximum_price_' . esc_js($key) . '\').val().replace(/[^\d.-]/g, \'\');

                                            max_price = parseFloat(max_price) - parseFloat( max_price * ( ' . esc_js($percentage_lower) . ' / 100 ) );

                                            jQuery(\'#_applicant_match_price_range_lower_' . esc_js($key) . '\').val(max_price.toFixed(0));
                                        }

                                        if ( jQuery(\'#_applicant_maximum_price_' . esc_js($key) . '\').val().replace(/[^\d.-]/g, \'\') != \'\' )
                                        {
                                            var max_price = jQuery(\'#_applicant_maximum_price_' . esc_js($key) . '\').val().replace(/[^\d.-]/g, \'\');

                                            max_price = parseFloat(max_price) + parseFloat( max_price * ( ' . esc_js($percentage_higher) . ' / 100 ) );

                                            jQuery(\'#_applicant_match_price_range_higher_' . esc_js($key) . '\').val(max_price.toFixed(0));
                                        }
                                    });

                                    jQuery(\'#_applicant_maximum_price_' . esc_js($key) . '\').change(function()
                                    {
                                        if ( previous_max_price_' . esc_js($key) . ' == \'\' )
                                        {
                                            if ( jQuery(this).val().replace(/\D/g, \'\') != \'\' && jQuery(\'#_applicant_match_price_range_lower_' . absint( $key ) . '\').val().replace(/[^\d.-]/g, \'\') == \'\' )
                                            {
                                                var max_price = jQuery(this).val().replace(/[^\d.-]/g, \'\');

                                                max_price = parseFloat(max_price) - parseFloat( max_price * ( ' . esc_js($percentage_lower) . ' / 100 ) );

                                                jQuery(\'#_applicant_match_price_range_lower_' . esc_js($key) . '\').val(max_price.toFixed(0));
                                            }

                                            if ( jQuery(this).val().replace(/\D/g, \'\') != \'\' && jQuery(\'#_applicant_match_price_range_higher_' . absint( $key ) . '\').val().replace(/[^\d.-]/g, \'\') == \'\' )
                                            {
                                                var max_price = jQuery(this).val().replace(/[^\d.-]/g, \'\');

                                                max_price = parseFloat(max_price) + parseFloat( max_price * ( ' . esc_js($percentage_higher) . ' / 100 ) );

                                                jQuery(\'#_applicant_match_price_range_higher_' . esc_js($key) . '\').val(max_price.toFixed(0));
                                            }
                                        }

                                        jQuery(\'#match_price_range_shortcut_' . esc_js($key) . '\').css(\'display\', \'inline\');

                                        previous_max_price_' . esc_js($key) . ' = jQuery(this).val();
                                    });
                                });

                            </script>';
            }

            do_action('propertyhive_contact_applicant_requirements_residential_sales_details_fields', $thepostid, $key);

            echo '</div>';

            echo '<div class="propertyhive-applicant-residential-lettings-details-' . esc_attr($key) . '">';

            // Display Relationship Name if it's already set
            // Or we're editing a profile and at least two of this department exist
            // Or we're creating a new profile and one of that department exist
            // Or the filter is set that always displays the name field
            if (
                isset($applicant_profile['relationship_name'])
                ||
                (isset($applicant_profile['department']) && $applicant_departments_count['residential-lettings'] > 1)
                ||
                (!isset($applicant_profile['department']) && $applicant_departments_count['residential-lettings'] > 0)
                ||
                (apply_filters('propertyhive_always_show_applicant_relationship_name', false) === true)
            ) {
                // Relationship Name
                propertyhive_wp_text_input(array(
                    'id' => '_relationship_name_residential-lettings_' . $key,
                    'label' => __('Name', 'propertyhive'),
                    'desc_tip' => false,
                    'type' => 'text',
                    'class' => '',
                    'custom_attributes' => array(
                        'style' => 'width:100%; max-width:323px;'
                    ),
                    'placeholder' => 'Lettings Applicant',
                    'value' => ((isset($applicant_profile['relationship_name'])) ? $applicant_profile['relationship_name'] : '')
                ));
            }

            // Rent / Rent Frequency
            $rent_frequency = ((isset($applicant_profile['rent_frequency'])) ? $applicant_profile['rent_frequency'] : '');
            echo '<p class="form-field rent_field ">
                        
                <label for="_applicant_maximum_rent_' . esc_attr($key) . '">' . esc_html(__('Maximum Rent', 'propertyhive')) . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol maximum-rent-currency-symbol">' . ( isset($currencies[$selected_currency]) ? esc_html($currencies[$selected_currency]) : '' ) . '</span>)' : '' ) . '</label>';
                if (count($currencies) > 1) {
                    echo '<select id="_applicant_currency_' .  esc_attr($key) . '" name="_applicant_currency_lettings_' .  esc_attr($key) . '" class="select" style="width:auto; float:left;">';
                    foreach ($currencies as $currency_code => $currency_symbol) {
                        echo '<option value="' . esc_attr($currency_code) . '"' . (($currency_code == $selected_currency) ? ' selected' : '') . '>' . esc_html( $currency_symbol ) . '</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input type="hidden" name="_applicant_currency_lettings_' .  esc_attr($key) . '" id="_applicant_currency_letting_' .  esc_attr($key) .'" value="' . esc_attr($selected_currency) . '">';
                }
                
                echo '<input type="text" class="" name="_applicant_maximum_rent_' . esc_attr($key) . '" id="_applicant_maximum_rent_' . esc_attr($key) . '" value="' . ((isset($applicant_profile['max_rent'])) ? esc_attr(ph_display_price_field($applicant_profile['max_rent'])) : '') . '" placeholder="" style="width:20%; max-width:150px;">
                
                <select id="_applicant_rent_frequency_' . esc_attr($key) . '" name="_applicant_rent_frequency_' . esc_attr($key) . '" class="select short">
                    <option value="pw"' . (($rent_frequency == 'pw') ? ' selected' : '') . '>' . esc_html(__('Per Week', 'propertyhive')) . '</option>
                    <option value="pcm"' . (($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . esc_html(__('Per Calendar Month', 'propertyhive')) . '</option>
                    <option value="pq"' . (($rent_frequency == 'pq') ? ' selected' : '') . '>' . esc_html(__('Per Quarter', 'propertyhive')) . '</option>
                    <option value="pa"' . (($rent_frequency == 'pa') ? ' selected' : '') . '>' . esc_html(__('Per Annum', 'propertyhive')) . '</option>
                </select>
                
            </p>';

            do_action('propertyhive_contact_applicant_requirements_residential_lettings_details_fields', $thepostid, $key);

            echo '</div>';

            echo '<div class="propertyhive-applicant-residential-details-' . esc_attr($key) . '">';

            // Bedrooms
            propertyhive_wp_text_input(array(
                'id' => '_applicant_minimum_bedrooms_' . $key,
                'label' => __('Minimum Bedrooms', 'propertyhive'),
                'desc_tip' => false,
                'type' => 'number',
                'class' => '',
                'custom_attributes' => array(
                    'style' => 'width:100%; max-width:75px;'
                ),
                'value' => ((isset($applicant_profile['min_beds'])) ? $applicant_profile['min_beds'] : '')
            ));

            // Residential Types
            ?>
            <p class="form-field"><label
                    for="_applicant_property_types_<?php echo esc_attr($key); ?>"><?php echo esc_html(__('Property Types', 'propertyhive')); ?></label>
                <select id="_applicant_property_types_<?php echo esc_attr($key); ?>"
                    name="_applicant_property_types_<?php echo esc_attr($key); ?>[]" multiple="multiple"
                    data-placeholder="Start typing to add property types..." class="multiselect attribute_values">
                    <?php
                    $options = array('' => '');
                    $args = array(
                        'hide_empty' => false,
                        'parent' => 0
                    );
                    $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );

                    $selected_values = array();
                    $term_list = ((isset($applicant_profile['property_types'])) ? $applicant_profile['property_types'] : array());
                    if (is_array($term_list) && !empty($term_list)) {
                        foreach ($term_list as $term_id) {
                            $selected_values[] = $term_id;
                        }
                    }

                    if (!empty($terms) && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '"';
                            if (in_array($term->term_id, $selected_values)) {
                                echo ' selected';
                            }
                            echo '>' . esc_html($term->name) . '</option>';

                            $args = array(
                                'hide_empty' => false,
                                'parent' => $term->term_id
                            );
                            $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );

                            if (!empty($subterms) && !is_wp_error($subterms)) {
                                foreach ($subterms as $term) {
                                    echo '<option value="' . esc_attr($term->term_id) . '"';
                                    if (in_array($term->term_id, $selected_values)) {
                                        echo ' selected';
                                    }
                                    echo '>- ' . esc_html($term->name) . '</option>';
                                }
                            }
                        }
                    }
                    ?>
                </select>
            </p>
            <?php

            do_action('propertyhive_contact_applicant_requirements_residential_details_fields', $thepostid, $key);

            echo '</div>'; // end 'propertyhive-applicant-residential-details-' . $key

            echo '<div class="propertyhive-applicant-commercial-details-' . esc_attr($key) . '">';

            // Display Relationship Name if it's already set
            // Or we're editing a profile and at least two of this department exist
            // Or we're creating a new profile and one of that department exist
            // Or the filter is set that always displays the name field
            if (
                isset($applicant_profile['relationship_name'])
                ||
                (isset($applicant_profile['department']) && $applicant_departments_count['commercial'] > 1)
                ||
                (!isset($applicant_profile['department']) && $applicant_departments_count['commercial'] > 0)
                ||
                (apply_filters('propertyhive_always_show_applicant_relationship_name', false) === true)
            ) {
                // Relationship Name
                propertyhive_wp_text_input(array(
                    'id' => '_relationship_name_commercial_' . $key,
                    'label' => __('Name', 'propertyhive'),
                    'desc_tip' => false,
                    'type' => 'text',
                    'class' => '',
                    'custom_attributes' => array(
                        'style' => 'width:100%; max-width:323px;'
                    ),
                    'placeholder' => 'Commercial Applicant',
                    'value' => ((isset($applicant_profile['relationship_name'])) ? $applicant_profile['relationship_name'] : ''),
                ));
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
            if (isset($applicant_profile['available_as']) && is_array($applicant_profile['available_as'])) {
                $args['value'] = array();
                foreach ($applicant_profile['available_as'] as $value) {
                    $args['value'][] = $value;
                }
            }
            propertyhive_wp_checkboxes($args);

            // Floor Area
            propertyhive_wp_text_input(array(
                'id' => '_applicant_minimum_floor_area_' . $key,
                'label' => __('Min Floor Area', 'propertyhive') . ' (Sq Ft)',
                'desc_tip' => false,
                'type' => 'text',
                'class' => '',
                'custom_attributes' => array(
                    'style' => 'width:100%; max-width:150px;'
                ),
                'value' => ((isset($applicant_profile['min_floor_area'])) ? $applicant_profile['min_floor_area'] : '')
            ));

            propertyhive_wp_text_input(array(
                'id' => '_applicant_maximum_floor_area_' . $key,
                'label' => __('Max Floor Area', 'propertyhive') . ' (Sq Ft)',
                'desc_tip' => false,
                'type' => 'text',
                'class' => '',
                'custom_attributes' => array(
                    'style' => 'width:100%; max-width:150px;'
                ),
                'value' => ((isset($applicant_profile['max_floor_area'])) ? $applicant_profile['max_floor_area'] : '')
            ));

            // Commercial Types
            ?>
            <p class="form-field"><label
                    for="_applicant_commercial_property_types_<?php echo esc_attr($key); ?>"><?php echo esc_html(__('Property Types', 'propertyhive')); ?></label>
                <select id="_applicant_commercial_property_types_<?php echo esc_attr($key); ?>"
                    name="_applicant_commercial_property_types_<?php echo esc_attr($key); ?>[]" multiple="multiple"
                    data-placeholder="Start typing to add property types..." class="multiselect attribute_values">
                    <?php
                    $options = array('' => '');
                    $args = array(
                        'hide_empty' => false,
                        'parent' => 0
                    );
                    $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'commercial_property_type' ) ) );

                    $selected_values = array();
                    $term_list = ((isset($applicant_profile['commercial_property_types'])) ? $applicant_profile['commercial_property_types'] : array());
                    if (is_array($term_list) && !empty($term_list)) {
                        foreach ($term_list as $term_id) {
                            $selected_values[] = $term_id;
                        }
                    }

                    if (!empty($terms) && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '"';
                            if (in_array($term->term_id, $selected_values)) {
                                echo ' selected';
                            }
                            echo '>' . esc_html($term->name) . '</option>';

                            $args = array(
                                'hide_empty' => false,
                                'parent' => $term->term_id
                            );
                            $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );

                            if (!empty($subterms) && !is_wp_error($subterms)) {
                                foreach ($subterms as $term) {
                                    echo '<option value="' . esc_attr($term->term_id) . '"';
                                    if (in_array($term->term_id, $selected_values)) {
                                        echo ' selected';
                                    }
                                    echo '>- ' . esc_html($term->name) . '</option>';
                                }
                            }
                        }
                    }
                    ?>
                </select>
            </p>
            <?php

            do_action('propertyhive_contact_applicant_requirements_commercial_details_fields', $thepostid, $key);

            echo '</div>'; // end 'propertyhive-applicant-commercial-details-' . $key

            // Locations
            if (get_option('propertyhive_applicant_locations_type') != 'text') {
                ?>
                <p class="form-field"><label
                        for="_applicant_locations_<?php echo esc_attr($key); ?>"><?php echo esc_html(__('Locations', 'propertyhive')); ?></label>
                    <select id="_applicant_locations_<?php echo esc_attr($key); ?>"
                        name="_applicant_locations_<?php echo esc_attr($key); ?>[]" multiple="multiple"
                        data-placeholder="Start typing to add location..." class="multiselect attribute_values">
                        <?php
                        $options = array('' => '');
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );

                        $selected_values = array();
                        $term_list = ((isset($applicant_profile['locations'])) ? $applicant_profile['locations'] : array());
                        if (is_array($term_list) && !empty($term_list)) {
                            foreach ($term_list as $term_id) {
                                $selected_values[] = $term_id;
                            }
                        }

                        if (!empty($terms) && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                echo '<option value="' . esc_attr($term->term_id) . '"';
                                if (in_array($term->term_id, $selected_values)) {
                                    echo ' selected';
                                }
                                echo '>' . esc_html($term->name) . '</option>';

                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );

                                if (!empty($subterms) && !is_wp_error($subterms)) {
                                    foreach ($subterms as $term) {
                                        echo '<option value="' . esc_attr($term->term_id) . '"';
                                        if (in_array($term->term_id, $selected_values)) {
                                            echo ' selected';
                                        }
                                        echo '>- ' . esc_html($term->name) . '</option>';

                                        $args = array(
                                            'hide_empty' => false,
                                            'parent' => $term->term_id
                                        );
                                        $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );

                                        if (!empty($subsubterms) && !is_wp_error($subsubterms)) {
                                            foreach ($subsubterms as $term) {
                                                echo '<option value="' . esc_attr($term->term_id) . '"';
                                                if (in_array($term->term_id, $selected_values)) {
                                                    echo ' selected';
                                                }
                                                echo '>- - ' . esc_html($term->name) . '</option>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                    </select>
                </p>
                <?php
            } else {
                propertyhive_wp_text_input(array(
                    'id' => '_applicant_location_text_' . $key,
                    'label' => __('Location', 'propertyhive'),
                    'desc_tip' => true,
                    'description' => __('If the applicant has several search locations, you should Add Relationship for each different location', 'propertyhive'),
                    'type' => 'text',
                    'class' => '',
                    'value' => ((isset($applicant_profile['location_text'])) ? $applicant_profile['location_text'] : '')
                ));
            }

            do_action('propertyhive_contact_applicant_requirements_details_fields', $thepostid, $key);

            // Additional Requirement Notes
            propertyhive_wp_textarea_input(array(
                'id' => '_applicant_requirement_notes_' . $key,
                'label' => __('Additional Requirements', 'propertyhive'),
                'desc_tip' => false,
                'class' => '',
                'value' => ((isset($applicant_profile['notes'])) ? $applicant_profile['notes'] : '')
            ));

            propertyhive_wp_checkbox(array(
                'id' => '_send_matching_properties_' . $key,
                'label' => __('Send Matching Properties', 'propertyhive'),
                'desc_tip' => false,
                'value' => (((isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] == 'yes') || (!isset($applicant_profile['send_matching_properties']) && apply_filters('propertyhive_default_applicant_send_matching_properties', true) === true)) ? 'yes' : '')
            ));

            $auto_property_match = get_option('propertyhive_auto_property_match', '');

            if ($auto_property_match == 'yes') {
                // Auto match emails are enabled. Add ability to disable on per-applicant basis
                propertyhive_wp_checkbox(array(
                    'id' => '_auto_match_disabled_' . $key,
                    'label' => __('Disable Auto-Match', 'propertyhive'),
                    'desc_tip' => false,
                    'value' => ((isset($applicant_profile['auto_match_disabled']) && $applicant_profile['auto_match_disabled'] == 'yes') ? 'yes' : '')
                ));
            }

            propertyhive_wp_checkbox(array(
                'id' => '_grading_' . $key,
                'label' => __('Hot Applicant', 'propertyhive'),
                'desc_tip' => false,
                'value' => (((isset($applicant_profile['grading']) && $applicant_profile['grading'] == 'hot')) ? 'yes' : '')
            ));

            echo '

                        </div>

                        <div class="actions">

                            <a 
                                href="' . esc_url(admin_url('admin.php?page=ph-matching-properties&contact_id=' . (int)$contact_id . '&applicant_profile=' . $key)) . '" 
                                class="button view-matching-properties-' . esc_attr($key) . '" 
                                ' . ((isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] == '') ? ' disabled title="Send Matching Properties not selected"' : '') . '
                            >' . esc_html__('View Matching Properties', 'propertyhive') . '</a>

                            <a 
                                href="' . esc_url(wp_nonce_url(admin_url('post.php?post=' . (int)$contact_id . '&action=edit#propertyhive-contact-relationships'), 'propertyhive-delete-applicant-' . (int) $contact_id . '-' . $key, 'delete_applicant_relationship')) . '"
                                class="button"
                                onclick="var confirmBox = confirm(\'' . esc_js(__('Are you sure you wish to delete this applicant relationship?', 'propertyhive')) . '\'); return confirmBox;"
                            >' . esc_html__('Delete Relationship', 'propertyhive') . '</a>

                            <div id="view_matching_properties_' . esc_attr($key) . '" style="display:none;">
                                
                                <div class="loading-properties" style="text-align:center;"><br><br>Loading matching properties...</div>

                                <div class="need-to-save-changes" style="text-align:center;"><br><br>Please save your changes before viewing matching properties</div>

                                <div class="matching-properties" id="matching_properties_' . esc_attr($key) . '" style="display:none;">

                                </div>

                            </div>

                        </div>

                    </div>

                    <script>

                        var applicant_details_changed_' . esc_js($key) . ' = false;
                        var custom_departments = ' . wp_json_encode( ph_get_custom_departments(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';
                        jQuery(document).ready(function()
                        {
                            showHideApplicantDepartmentMetaBox_' . esc_js($key) . '();

                            jQuery(\'input[type=\\\'radio\\\'][name=\\\'_applicant_department_' . esc_js($key) . '\\\']\').change(function()
                            {
                                 showHideApplicantDepartmentMetaBox_' . absint( $key ) . '();
                            });

                            jQuery(\'.applicant-fields-' . esc_js($key) . ' input, .applicant-fields-' . esc_js($key) . ' select, .applicant-fields-' . absint( $key ) . ' textarea\').change(function()
                            {
                                applicant_details_changed_' . esc_js($key) . ' = true;
                            });

                            jQuery(\'a.view-matching-properties-' . esc_js($key) . '\').click(function(e)
                            {
                                if (applicant_details_changed_' . esc_js($key) . ')
                                {
                                    alert(\'You\\\'ve made changes to the requirements. Please save the changes before viewing matching properties\');
                                    return false;
                                }

                                return true;
                            });
                        });
                        
                        function showHideApplicantDepartmentMetaBox_' . esc_js($key) . '()
                        {
                            jQuery(\'.propertyhive-applicant-residential-details-' . esc_js($key) . '\').hide();
                            jQuery(\'.propertyhive-applicant-residential-sales-details-' . esc_js($key) . '\').hide();
                            jQuery(\'.propertyhive-applicant-residential-lettings-details-' . esc_js($key) . '\').hide();
                            jQuery(\'.propertyhive-applicant-commercial-details-' . esc_js($key) . '\').hide();
                            
                            var selectedDepartment = jQuery(\'input[type=\\\'radio\\\'][name=\\\'_applicant_department_' . esc_js($key) . '\\\']:checked\').val();
                            
                            if ( selectedDepartment == \'residential-sales\' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == \'residential-sales\' ) )
                            {
                                jQuery(\'.propertyhive-applicant-residential-details-' . esc_js($key) . '\').show();
                                jQuery(\'.propertyhive-applicant-residential-sales-details-' . esc_js($key) . '\').show();
                            }
                            else if ( selectedDepartment == \'residential-lettings\' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == \'residential-lettings\' ) )
                            {
                                jQuery(\'.propertyhive-applicant-residential-details-' . esc_js($key) . '\').show();
                                jQuery(\'.propertyhive-applicant-residential-lettings-details-' . esc_js($key) . '\').show();
                            }
                            else if ( selectedDepartment == \'commercial\' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == \'commercial\' ) )
                            {
                                jQuery(\'.propertyhive-applicant-commercial-details-' . esc_js($key) . '\').show();
                            }                            
                        }
                        
                    </script>';
            ++$tab;
        }

        foreach ($third_party_profiles as $key => $category) {
            echo '<div id="tab_third_party_data_' . esc_attr($key) . '" class="panel propertyhive_options_panel" style="' . (($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group" style="float:left; width:100%;">';

            echo '<p class="form-field rent_field ">
                        
                            <label for="_third_party_category_' . esc_attr($key) . '">' . esc_html(__('Contact Category', 'propertyhive')) . '</label>
                            
                            <select id="_third_party_category_' . esc_attr($key) . '" name="_third_party_category[]" class="select short">';

            echo '<option value=""></option>';

            $categories = $ph_third_party_contacts->get_categories();
            foreach ($categories as $id => $category_name) {
                echo '<option value="' . esc_attr($id) . '"';
                if ($id == $category) {
                    echo ' selected';
                }
                echo '>' . esc_html($category_name) . '</option>';
            }

            echo '</select>
                            
                        </p>';

            echo '
                        </div>
                    </div>';
            ++$tab;
        }

        echo '<div id="tab_add_relationship" class="panel propertyhive_options_panel" style="' . (($tab == 0) ? 'display:block;' : 'display:none;') . '">
                    <div class="options_group">';

        echo '<p class="form-field">';
        echo '<label>' . esc_html(__('New Relationship Type', 'propertyhive')) . '</label>';
        echo '<a href="' . esc_url(wp_nonce_url(admin_url('post.php?post=' . $thepostid . '&action=edit#propertyhive-contact-relationships'), 'propertyhive-add_applicant_relationship-' . $thepostid, 'add_applicant_relationship')) . '" class="button">' . esc_html(__('New Applicant Profile', 'propertyhive')) . '</a><br><br>';
        echo '<a href="' . esc_url(admin_url('post-new.php?post_type=property&owner_contact_id=' . $thepostid)) . '" class="button">' . esc_html(__('New Property Owner / Landlord', 'propertyhive')) . '</a><br><br>';
        echo '<a href="' . esc_url(wp_nonce_url(admin_url('post.php?post=' . $thepostid . '&action=edit#propertyhive-contact-relationships'), 'propertyhive-add_third_party_relationship-' . $thepostid, 'add_third_party_relationship')) . '" class="button">' . esc_html(__('New Third Party Contact', 'propertyhive')) . '</a>';
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
    public static function save($post_id, $post)

    {
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }
        global $wpdb;

        $get_post_value = static function ( $key ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- The save method verifies propertyhive_meta_nonce and the edit capability before this request-boundary helper is created.
            if ( ! isset( $_POST[ $key ] ) ) {
                return null;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- This is the single request boundary for relationship fields; the save nonce and capability checks run immediately above, and each caller validates the returned scalar or array before sanitizing it for its field type.
            $value = wp_unslash( $_POST[ $key ] );
            return is_scalar( $value ) || is_array( $value ) ? $value : null;
        };

        $num_applicant_profiles = get_post_meta($post_id, '_applicant_profiles', TRUE);
        if ( $num_applicant_profiles == '' ) 
        {
            $num_applicant_profiles = 0;
        }

        $hot_applicant = ''; // use this to set a global meta key on the contact so applicants can be filtered by hot
        $applicant_departments = array();
        if ( $num_applicant_profiles > 0 ) 
        {
            $ph_countries = new PH_Countries();

            for ( $i = 0; $i < $num_applicant_profiles; ++$i ) 
            {
                $existing_applicant_profile = get_post_meta($post_id, '_applicant_profile_' . $i, TRUE);

                $applicant_profile = array();
                $department_value = $get_post_value( '_applicant_department_' . $i );
                $department = is_scalar( $department_value ) ? ph_clean( $department_value ) : '';
                $department_based_on = ph_get_custom_department_based_on( $department );
                $applicant_profile['department'] = $department;

                if ( 'residential-sales' === $department || 'residential-sales' === $department_based_on ) {
                    $maximum_price_value = $get_post_value( '_applicant_maximum_price_' . $i );
                    $price = preg_replace( '/[^0-9.]/', '', is_scalar( $maximum_price_value ) ? ph_clean( $maximum_price_value ) : '' );
                    $currency_value = $get_post_value( '_applicant_currency_sales_' . $i );
                    $currency = is_scalar( $currency_value ) ? ph_clean( $currency_value ) : '';
                    if ( $currency ) {
                        $applicant_profile['currency'] = $currency;
                    }

                    $applicant_profile['max_price'] = $price;
                    $conversion_currency = isset( $applicant_profile['currency'] ) ? $applicant_profile['currency'] : '';
                    $price_actual = $ph_countries->convert_price_to_gbp( $price, $conversion_currency );

                    // Not used yet but could be if introducing currencies in the future.
                    $applicant_profile['max_price_actual'] = $price_actual;

                    if ( $price != '' && $price != 0 ) {
                        $range_lower_value = $get_post_value( '_applicant_match_price_range_lower_' . $i );
                        if ( null !== $range_lower_value && is_scalar( $range_lower_value ) ) {
                            $price = preg_replace( '/[^0-9.]/', '', ph_clean( $range_lower_value ) );
                            $price_actual = $ph_countries->convert_price_to_gbp( $price, $conversion_currency );

                            $applicant_profile['match_price_range_lower'] = $price;
                            $applicant_profile['match_price_range_lower_actual'] = $price_actual;
                        }

                        $range_higher_value = $get_post_value( '_applicant_match_price_range_higher_' . $i );
                        if ( null !== $range_higher_value && is_scalar( $range_higher_value ) ) {
                            $price = preg_replace( '/[^0-9.]/', '', ph_clean( $range_higher_value ) );
                            $price_actual = $ph_countries->convert_price_to_gbp( $price, $conversion_currency );

                            $applicant_profile['match_price_range_higher'] = $price;
                            $applicant_profile['match_price_range_higher_actual'] = $price_actual;
                        }
                    }
                } elseif ( 'residential-lettings' === $department || 'residential-lettings' === $department_based_on ) {
                    $maximum_rent_value = $get_post_value( '_applicant_maximum_rent_' . $i );
                    $rent = preg_replace( '/[^0-9.]/', '', is_scalar( $maximum_rent_value ) ? ph_clean( $maximum_rent_value ) : '' );
                    $currency_value = $get_post_value( '_applicant_currency_lettings_' . $i );
                    $currency = is_scalar( $currency_value ) ? ph_clean( $currency_value ) : '';
                    if ( $currency ) {
                        $applicant_profile['currency'] = $currency;
                    }
                    $rent_frequency_value = $get_post_value( '_applicant_rent_frequency_' . $i );
                    $rent_frequency = is_scalar( $rent_frequency_value ) ? ph_clean( $rent_frequency_value ) : '';
                    $applicant_profile['max_rent'] = $rent;
                    $applicant_profile['rent_frequency'] = $rent_frequency;

                    $price_actual = '';
                    if ( ! empty( $rent ) ) {
                        switch ( $rent_frequency ) {
                            case 'pw':
                                $price_actual = ( $rent * 52 ) / 12;
                                break;
                            case 'pcm':
                                $price_actual = $rent;
                                break;
                            case 'pq':
                                $price_actual = ( $rent * 4 ) / 52;
                                break;
                            case 'pa':
                                $price_actual = $rent / 52;
                                break;
                        }
                    }

                    $conversion_currency = isset( $applicant_profile['currency'] ) ? $applicant_profile['currency'] : '';
                    $price_actual = $ph_countries->convert_price_to_gbp( $price_actual, $conversion_currency );
                    $applicant_profile['max_price_actual'] = $price_actual;
                }

                if ( in_array( $department, array( 'residential-sales', 'residential-lettings' ), true ) || in_array( $department_based_on, array( 'residential-sales', 'residential-lettings' ), true ) ) {
                    $minimum_bedrooms_value = $get_post_value( '_applicant_minimum_bedrooms_' . $i );
                    $beds = preg_replace( '/[^0-9]/', '', is_scalar( $minimum_bedrooms_value ) ? ph_clean( $minimum_bedrooms_value ) : '' );
                    $applicant_profile['min_beds'] = $beds;

                    $property_types_value = $get_post_value( '_applicant_property_types_' . $i );
                    if ( is_array( $property_types_value ) && ! empty( $property_types_value ) ) {
                        $applicant_profile['property_types'] = ph_clean( $property_types_value );
                    }
                }

                if ( 'commercial' === $department || 'commercial' === $department_based_on ) {
                    $available_as_value = $get_post_value( '_applicant_available_as_' . $i );
                    $applicant_profile['available_as'] = ( is_array( $available_as_value ) && ! empty( $available_as_value ) ) ? ph_clean( $available_as_value ) : array();

                    $minimum_floor_area_value = $get_post_value( '_applicant_minimum_floor_area_' . $i );
                    $floor_area = preg_replace( '/[^0-9.]/', '', is_scalar( $minimum_floor_area_value ) ? ph_clean( $minimum_floor_area_value ) : '' );
                    $applicant_profile['min_floor_area'] = $floor_area;
                    $applicant_profile['min_floor_area_actual'] = $floor_area;

                    $maximum_floor_area_value = $get_post_value( '_applicant_maximum_floor_area_' . $i );
                    $floor_area = preg_replace( '/[^0-9.]/', '', is_scalar( $maximum_floor_area_value ) ? ph_clean( $maximum_floor_area_value ) : '' );
                    $applicant_profile['max_floor_area'] = $floor_area;
                    $applicant_profile['max_floor_area_actual'] = $floor_area;
                    $applicant_profile['floor_area_units'] = 'sqft';

                    $commercial_property_types_value = $get_post_value( '_applicant_commercial_property_types_' . $i );
                    if ( is_array( $commercial_property_types_value ) && ! empty( $commercial_property_types_value ) ) {
                        $applicant_profile['commercial_property_types'] = ph_clean( $commercial_property_types_value );
                    }
                }

                $relationship_name_key = '_relationship_name_' . $department . '_' . $i;
                $relationship_name_value = $get_post_value( $relationship_name_key );
                if ( is_scalar( $relationship_name_value ) && '' !== $relationship_name_value ) {
                    $applicant_profile['relationship_name'] = ph_clean( $relationship_name_value );
                }

                if ( get_option( 'propertyhive_applicant_locations_type' ) != 'text' ) {
                    $locations_value = $get_post_value( '_applicant_locations_' . $i );
                    if ( is_array( $locations_value ) && ! empty( $locations_value ) ) {
                        $applicant_profile['locations'] = ph_clean( $locations_value );
                    }

                    // If the other type of location is set for this applicant, retain that data
                    if ( ! empty( $existing_applicant_profile ) && isset( $existing_applicant_profile['location_text'] ) && $existing_applicant_profile['location_text'] != '' ) {
                        $applicant_profile['location_text'] = ph_clean( $existing_applicant_profile['location_text'] );
                        if ( isset( $existing_applicant_profile['location_radius'] ) && $existing_applicant_profile['location_radius'] != '' ) {
                            $applicant_profile['location_radius'] = ph_clean( $existing_applicant_profile['location_radius'] );
                        }
                    }
                } else {
                    $location_text_value = $get_post_value( '_applicant_location_text_' . $i );
                    if ( is_scalar( $location_text_value ) && ! empty( $location_text_value ) ) {
                        $applicant_profile['location_text'] = ph_clean( $location_text_value );
                    }

                    // If the other type of location is set for this applicant, retain that data
                    if ( ! empty( $existing_applicant_profile ) && isset( $existing_applicant_profile['locations'] ) && ! empty( $existing_applicant_profile['locations'] ) ) {
                        $applicant_profile['locations'] = ph_clean( $existing_applicant_profile['locations'] );
                    }
                }

                $notes_value = $get_post_value( '_applicant_requirement_notes_' . $i );
                $applicant_profile['notes'] = is_scalar( $notes_value ) ? sanitize_textarea_field( $notes_value ) : '';

                $send_matching_value = $get_post_value( '_send_matching_properties_' . $i );
                $applicant_profile['send_matching_properties'] = is_scalar( $send_matching_value ) ? ph_clean( $send_matching_value ) : '';
                $auto_match_disabled_value = $get_post_value( '_auto_match_disabled_' . $i );
                $applicant_profile['auto_match_disabled'] = is_scalar( $auto_match_disabled_value ) ? ph_clean( $auto_match_disabled_value ) : '';

                $grading_value = $get_post_value( '_grading_' . $i );
                $grading = is_scalar( $grading_value ) ? ph_clean( $grading_value ) : '';
                $applicant_profile['grading'] = 'yes' === $grading ? 'hot' : '';
                if ( 'yes' === $grading ) {
                    $hot_applicant = 'yes';
                }

                $applicant_departments[] = $applicant_profile['department'];

                update_post_meta( $post_id, '_applicant_profile_' . $i, wp_slash( $applicant_profile ) );

                do_action('propertyhive_save_contact_applicant_requirements', $post_id, $i);
            }

        }

        update_post_meta($post_id, '_hot_applicant', $hot_applicant);

        if (!empty($applicant_departments)) {
            $applicant_departments = array_filter($applicant_departments);
            $applicant_departments = array_unique($applicant_departments);
        }
        update_post_meta($post_id, '_applicant_departments', $applicant_departments);

        $third_party_categories = array();
        $third_party_categories_value = $get_post_value( '_third_party_category' );
        if ( is_array( $third_party_categories_value ) && ! empty( $third_party_categories_value ) ) {
            foreach ( $third_party_categories_value as $category ) {
                $category = is_scalar( $category ) ? ph_clean( $category ) : '';
                if ( '' !== $category ) {
                    $third_party_categories[] = (int) $category;
                }
            }
        }
        update_post_meta($post_id, '_third_party_categories', $third_party_categories);

        do_action('propertyhive_save_contact_relationships', $post_id);
    }

    //wp_die();
}
