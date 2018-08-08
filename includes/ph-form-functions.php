<?php
/**
 * PropertyHive Form Functions
 *
 * Functions related to drawing forms on the frontend.
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Functions
 * @version     1.0.0
 */

/**
 * Main function for drawing entire property search form. We give the ability for an ID to be passed so differently formatted forms can be used
 * (ie. a homepage search form might be different from a search form on search results)
 *
 * @param string $id
 * @return void
 */
function ph_get_search_form( $id = 'default' ) {

    $form_controls = ph_get_search_form_fields();
    
    $form_controls = apply_filters( 'propertyhive_search_form_fields_' . $id, $form_controls );

    // We 100% need department so make sure it exists. If it doesn't, set a hidden field
    if ( !isset($form_controls['department']) )
    {
        $original_form_controls = ph_get_search_form_fields();
        $original_department = $original_form_controls['department'];
        $original_department['type'] = 'hidden';

        $form_controls['department'] = $original_department;
    }

    // append hidden order and view fields so these are maintained should a new search be performed
    if ( !isset($form_controls['view']) && isset($_REQUEST['view']) && $_REQUEST['view'] != '' ) {
        $form_controls['view'] = array('type' => 'hidden', 'value' => sanitize_text_field( $_REQUEST['view'] ));
    }
    if ( !isset($form_controls['orderby']) && isset($_REQUEST['orderby']) && $_REQUEST['orderby'] != '' ) {
        $form_controls['orderby'] = array('type' => 'hidden', 'value' => sanitize_text_field( $_REQUEST['orderby'] ));
    }
    
    ph_get_template( 'global/search-form.php', array( 'form_controls' => $form_controls, 'id' => $id ) );

}

/**
 * Get default fields to be shown on search forms
 *
 * @return array
 */
function ph_get_search_form_fields()
{
    $fields = array();
    
    $departments = array();
    $value = '';
    if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
    {
        $departments['residential-sales'] = __( 'Sales', 'propertyhive' );
        if ($value == '' && (get_option( 'propertyhive_primary_department' ) == 'residential-sales' || get_option( 'propertyhive_primary_department' ) === FALSE) )
        {
            $value = 'residential-sales';
        }
    }
    if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
    {
        $departments['residential-lettings'] = __( 'Lettings', 'propertyhive' );
        if ($value == '' && get_option( 'propertyhive_primary_department' ) == 'residential-lettings')
        {
            $value = 'residential-lettings';
        }
    }
    if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
    {
        $departments['commercial'] = __( 'Commercial', 'propertyhive' );
        if ($value == '' && get_option( 'propertyhive_primary_department' ) == 'commercial')
        {
            $value = 'commercial';
        }
    }
    
    $fields['department'] = array(
        'type' => 'radio',
        'options' => $departments,
        'value' => $value
    );
    
    if ( array_key_exists('residential-sales', $departments) || array_key_exists('residential-lettings', $departments) )
    {
        if ( array_key_exists('residential-sales', $departments) )
        {
            $prices = array(
                '' => __( 'No preference', 'propertyhive' ),
                '100000' => '&pound;100,000',
                '150000' => '&pound;150,000',
                '200000' => '&pound;200,000',
                '250000' => '&pound;250,000',
                '300000' => '&pound;300,000',
                '500000' => '&pound;500,000',
                '750000' => '&pound;750,000',
                '1000000' => '&pound;1,000,000'
            );
            
            $fields['minimum_price'] = array(
                'type' => 'select',
                'show_label' => true, 
                'label' => __( 'Min Price', 'propertyhive' ),
                'before' => '<div class="control control-minimum_price sales-only">',
                'options' => $prices
            );
            
            $fields['maximum_price'] = array(
                'type' => 'select',
                'show_label' => true, 
                'label' => __( 'Max Price', 'propertyhive' ),
                'before' => '<div class="control control-maximum_price sales-only">',
                'options' => $prices
            );
        }
        
        if ( array_key_exists('residential-lettings', $departments) )
        {
            $prices = array(
                '' => __( 'No preference', 'propertyhive' ),
                '500' => '&pound;500 PCM',
                '600' => '&pound;600 PCM',
                '750' => '&pound;750 PCM',
                '1000' => '&pound;1000 PCM',
                '1250' => '&pound;1250 PCM',
                '1500' => '&pound;1500 PCM',
                '2000' => '&pound;2000 PCM'
            );
            
            $fields['minimum_rent'] = array(
                'type' => 'select',
                'show_label' => true, 
                'label' => __( 'Min Rent', 'propertyhive' ),
                'before' => '<div class="control control-minimum_rent lettings-only">',
                'options' => $prices
            );
            
            $fields['maximum_rent'] = array(
                'type' => 'select',
                'show_label' => true, 
                'label' => __( 'Max Rent', 'propertyhive' ),
                'before' => '<div class="control control-maximum_rent lettings-only">',
                'options' => $prices
            );
        }

        $fields['minimum_bedrooms'] = array(
            'type' => 'select',
            'show_label' => true, 
            'label' => __( 'Min Beds', 'propertyhive' ),
            'before' => '<div class="control control-minimum_bedrooms residential-only">',
            'options' => array( '' => __( 'No preference', 'propertyhive' ), 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5)
        );
        
        $fields['property_type'] = array(
            'type' => 'property_type',
            'show_label' => true, 
            'before' => '<div class="control control-property_type residential-only">',
            'label' => __( 'Type', 'propertyhive' ),
        );
    }

    if ( array_key_exists('commercial', $departments) )
    {
        $sizes = array(
            '' => __( 'No preference', 'propertyhive' ),
            '250' => '250 sq ft',
            '500' => '500 sq ft',
            '1000' => '1,000 sq ft',
            '2500' => '2,500 sq ft',
            '5000' => '5,000 sq ft',
            '10000' => '10,000 sq ft',
            '25000' => '25,000 sq ft',
            '50000' => '50,000 sq ft'
        );
        
        $fields['minimum_floor_area'] = array(
            'type' => 'select',
            'show_label' => true, 
            'label' => __( 'Min Floor Area', 'propertyhive' ),
            'before' => '<div class="control control-minimum_floor_area commercial-only">',
            'options' => $sizes
        );
        
        $fields['maximum_floor_area'] = array(
            'type' => 'select',
            'show_label' => true, 
            'label' => __( 'Max Floor Area', 'propertyhive' ),
            'before' => '<div class="control control-maximum_floor_area commercial-only">',
            'options' => $sizes
        );

        // Property Type
        $options = array( '' => __( 'No preference', 'propertyhive' ) );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'commercial_property_type', $args );
        
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
                $subterms = get_terms( 'commercial_property_type', $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                    }
                }
            }
        }
        
        $fields['commercial_property_type'] = array(
            'type' => 'select',
            'show_label' => true, 
            'before' => '<div class="control control-commercial_property_type commercial-only">',
            'label' => __( 'Type', 'propertyhive' ),
            'options' => $options
        );
    }
    
    return $fields;
}

/**
 * Main function for drawing property enquiry form.
 *
 * @param string $id
 * @return void
 */
function propertyhive_enquiry_form()
{
    $form_controls = ph_get_property_enquiry_form_fields();
    
    $form_controls = apply_filters( 'propertyhive_property_enquiry_form_fields', $form_controls );
    
    ph_get_template( 'global/make-enquiry-form.php',array( 'form_controls' => $form_controls ) );
}

/**
 * Get default fields to be shown on search forms
 *
 * @return array
 */
function ph_get_property_enquiry_form_fields()
{
    global $post;
    
    $fields = array();
    
    $fields['property_id'] = array(
        'type' => 'hidden',
        'value' => $post->ID
    );
    
    $fields['name'] = array(
        'type' => 'text',
        'label' => __( 'Full Name', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        $fields['name']['value'] = $current_user->display_name;
    }
    
    $fields['email_address'] = array(
        'type' => 'email',
        'label' => __( 'Email Address', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        $fields['email_address']['value'] = $current_user->user_email;
    }
    
    $fields['telephone_number'] = array(
        'type' => 'text',
        'label' => __( 'Number', 'propertyhive' ),
        'required' => true
    );
    
    $fields['message'] = array(
        'type' => 'textarea',
        'label' => __( 'Message', 'propertyhive' ),
        'required' => true
    );

    if ( get_option( 'propertyhive_property_enquiry_form_disclaimer', '' ) != '' )
    {
        $disclaimer = get_option( 'propertyhive_property_enquiry_form_disclaimer', '' );

        $fields['disclaimer'] = array(
            'type' => 'checkbox',
            'label' => $disclaimer,
            'label_style' => 'width:100%;',
            'required' => true
        );
    }
    
    return $fields;
}

/**
 * Get default fields to be shown on applicant registration forms
 *
 * @return array
 */
function ph_get_user_details_form_fields()
{
    global $post;

    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        if ( $current_user instanceof WP_User )
        {
            $contact = new PH_Contact( '', $current_user->ID );
        }
    }
    
    $fields = array();
    
    $fields['name'] = array(
        'type' => 'text',
        'label' => __( 'Full Name', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() && $current_user instanceof WP_User )
    {
        $fields['name']['value'] = $current_user->display_name;
    }
    
    $fields['email_address'] = array(
        'type' => 'email',
        'label' => __( 'Email Address', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() && $current_user instanceof WP_User )
    {
        $fields['email_address']['value'] = $current_user->user_email;
    }

    $fields['telephone_number'] = array(
        'type' => 'text',
        'label' => __( 'Telephone Number', 'propertyhive' ),
        'required' => false
    );
    if ( is_user_logged_in() && $current_user instanceof WP_User )
    {
        $fields['telephone_number']['value'] = $contact->telephone_number;
    }

    if ( get_option( 'propertyhive_applicant_users', '' ) == 'yes' )
    {
        $fields['password'] = array(
            'type' => 'password',
            'label' => __( 'Password', 'propertyhive' ),
            'required' => true
        );

        $fields['password2'] = array(
            'type' => 'password',
            'label' => __( 'Confirm Password', 'propertyhive' ),
            'required' => true
        );
    }
    
    return $fields;
}

/**
 * Get default fields to be shown on applicant registration forms
 *
 * @return array
 */
function ph_get_applicant_requirements_form_fields()
{
    global $post;

    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();
        $applicant_profile = false;

        if ( $current_user instanceof WP_User )
        {
            $contact = new PH_Contact( '', $current_user->ID );

            if ( is_array($contact->contact_types) && in_array('applicant', $contact->contact_types) )
            {
                if ( 
                    $contact->applicant_profiles != '' && 
                    $contact->applicant_profiles > 0 && 
                    $contact->applicant_profile_0 != '' && 
                    is_array($contact->applicant_profile_0) 
                )
                {
                    $applicant_profile = $contact->applicant_profile_0;
                }
            }
        }
    }
    
    $fields = array();
    
    $departments = array();
    $value = '';
    if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
    {
        $departments['residential-sales'] = __( 'Buy', 'propertyhive' );
        if ($value == '' && (get_option( 'propertyhive_primary_department' ) == 'residential-sales' || get_option( 'propertyhive_primary_department' ) === FALSE) )
        {
            $value = 'residential-sales';
        }
    }
    if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
    {
        $departments['residential-lettings'] = __( 'Rent', 'propertyhive' );
        if ($value == '' && get_option( 'propertyhive_primary_department' ) == 'residential-lettings')
        {
            $value = 'residential-lettings';
        }
    }
    $fields['department'] = array(
        'type' => 'radio',
        'label' => __( 'Looking To', 'propertyhive' ),
        'required' => true,
        'show_label' => true,
        'value' => $value,
        'options' => $departments
    );
    if ( is_user_logged_in() && isset($applicant_profile['department']) )
    {
        $fields['department']['value'] = $applicant_profile['department'];
    }
    if ( count($departments) == 1 )
    {
        $fields['department']['type'] = 'hidden';
    }

    $offices = array();
    $value = '';

    $args = array(
        'post_type' => 'office',
        'nopaging' => true,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    $office_query = new WP_Query( $args );

    if ( $office_query->have_posts() )
    {
        while ( $office_query->have_posts() )
        {
            $office_query->the_post();

            $offices[get_the_ID()] = get_the_title();

            if ( get_post_meta(get_the_ID(), 'primary', TRUE) == 1 )
            {
                $value = get_the_ID();
            }
        }
    }
    wp_reset_postdata();

    $fields['office_id'] = array(
        'type' => ( (count($offices) <= 1) ? 'hidden' : 'select' ),
        'label' => __( 'Office', 'propertyhive' ),
        'required' => false,
        'show_label' => true,
        'value' => $value,
        'options' => $offices
    );

    $fields['maximum_price'] = array(
        'type' => 'number',
        'label' => __( 'Maximum Price', 'propertyhive' ),
        'style' => 'max-width:150px;',
        'before' => '<div class="control control-minimum_price sales-only">',
        'required' => false
    );
    if ( is_user_logged_in() && isset($applicant_profile['max_price']) )
    {
        $fields['maximum_price']['value'] = $applicant_profile['max_price'];
    }

    $fields['maximum_rent'] = array(
        'type' => 'number',
        'label' => __( 'Maximum Rent', 'propertyhive' ) . ' (PCM)',
        'style' => 'max-width:150px;',
        'before' => '<div class="control control-minimum_price lettings-only">',
        'required' => false
    );
    if ( is_user_logged_in() && isset($applicant_profile['max_rent']) )
    {
        $fields['maximum_rent']['value'] = $applicant_profile['max_rent'];
    }

    $fields['minimum_bedrooms'] = array(
        'type' => 'number',
        'label' => __( 'Minimum Bedrooms', 'propertyhive' ),
        'style' => 'max-width:80px;',
        'required' => false
    );
    if ( is_user_logged_in() && isset($applicant_profile['min_beds']) )
    {
        $fields['minimum_bedrooms']['value'] = $applicant_profile['min_beds'];
    }

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $terms = get_terms( 'property_type', $args );

    $options = array();

    $selected_value = '';
    if ( !empty( $terms ) && !is_wp_error( $terms ) )
    {
        $options = array( '' => __( 'All Property Types', 'properthive' ) );

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
    }

    if ( !empty($options) )
    {
        $fields['property_type'] = array(
            'type' => 'select',
            'label' => __( 'Property Type', 'propertyhive' ),
            'required' => false,
            'options' => $options,
        );

        if ( is_user_logged_in() && isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
        {
            $fields['property_type']['value'] = $applicant_profile['property_types'][0];
        }
    }

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $terms = get_terms( 'location', $args );

    $options = array();

    $selected_value = '';
    if ( !empty( $terms ) && !is_wp_error( $terms ) )
    {
        $options = array( '' => __( 'All Locations', 'properthive' ) );

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
                }
            }
        }
    }

    if ( !empty($options) )
    {
        $fields['location'] = array(
            'type' => 'select',
            'label' => __( 'Location', 'propertyhive' ),
            'required' => false,
            'options' => $options,
        );

        if ( is_user_logged_in() && isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
        {
            $fields['location']['value'] = $applicant_profile['locations'][0];
        }
    }
    
    $fields['additional_requirements'] = array(
        'type' => 'textarea',
        'label' => __( 'Additional Requirements', 'propertyhive' ),
        'required' => false
    );
    if ( is_user_logged_in() && isset($applicant_profile['notes']) )
    {
        $fields['additional_requirements']['value'] = $applicant_profile['notes'];
    }

    return $fields;
}

/**
 * Output individual field
 *
 * @return void
 */
function ph_form_field( $key, $field )
{
    global $post;

    $output = '';
    
    switch ($field['type'])
    {
        case "text":
        case "email":
        case "date":
        case "number":
        case "password":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : ( ( $field['type'] == 'date' ) ? 'dd/mm/yyyy' : '' );
            $field['required'] = isset( $field['required'] ) ? $field['required'] : false;
            $field['style'] = isset( $field['style'] ) ? $field['style'] : '';
            
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            else
            {
                if ( isset($post->ID) )
                {
                    $value = get_post_meta( $post->ID, '_' . $key, true );
                    if ( $value != '' )
                    {
                        $field['value'] = $value;
                    }
                }
            }
            
            $output .= $field['before'];
            
            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                if ($field['required'])
                {
                    $output .= '<span class="required"> *</span>';
                }
                $output .= '</label>';
            }
            
            $output .= '<input 
                    type="' . esc_attr( $field['type'] ) . '" 
                    name="' . esc_attr( $key ) . '" 
                    id="' . esc_attr( $key ) . '" 
                    value="' . esc_attr(  $field['value'] ) . '"
                    placeholder="' . esc_attr(  $field['placeholder'] ) . '"
                    class="' . esc_attr( $field['class'] ) . '"
                    style="' . esc_attr( $field['style'] ) . '"
                    ' . ( ($field['required']) ? 'required' : '' ) . '
            >';
            
            $output .= $field['after'];
            
            break;   
        }
        case "textarea": 
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
            $field['required'] = isset( $field['required'] ) ? $field['required'] : false;
            
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            
            $output .= $field['before'];
            
            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                if ($field['required'])
                {
                    $output .= '<span class="required"> *</span>';
                }
                $output .= '</label>';
            }
            
            $output .= '<textarea 
                    name="' . esc_attr( $key ) . '" 
                    id="' . esc_attr( $key ) . '" 
                    placeholder="' . esc_attr(  $field['placeholder'] ) . '"
                    class="' . esc_attr( $field['class'] ) . '"
                    ' . ( ($field['required']) ? 'required' : '' ) . '
            >' . esc_attr(  $field['value'] ) . '</textarea>';
            
            $output .= $field['after'];
            
            break;   
        }
        case "checkbox": 
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['label_style'] = isset( $field['label_style'] ) ? $field['label_style'] : '';
            $field['value'] = isset( $field['value'] ) ? $field['value'] : 'yes';
            $field['checked'] = isset( $field['checked'] ) ? $field['checked'] : false;
            if ( isset( $_GET[$key] ) && $_GET[$key] == $field['value'] )
            {
                $field['checked'] = true;
            }
            
            $output .= $field['before'];
            
            $output .= '<label style="' . esc_attr( $field['label_style'] ) . '"><input 
                type="' . esc_attr( $field['type'] ) . '" 
                name="' . esc_attr( $key ) . '" 
                value="' . esc_attr( $field['value'] ) . '"
                class="' . esc_attr( $field['class'] ) . '" 
                ' . checked( $field['checked'], true, false ) . '
            >';
            if ($field['show_label'])
            {
                $output .= ' ' . $field['label'];
            }
            $output .= '</label>';
            
            $output .= $field['after'];
            
            break;
        }
        case "radio": 
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : false;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            
            $output .= $field['before'];
            
            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
            }
            
            foreach ( $field['options'] as $option_key => $value ) 
            {
                $output .= '<label><input 
                    type="' . esc_attr( $field['type'] ) . '" 
                    name="' . esc_attr( $key ) . '" 
                    value="' . esc_attr( $option_key ) . '"
                    class="' . esc_attr( $field['class'] ) . '" 
                    ' . checked( esc_attr( $field['value'] ), esc_attr( $option_key ), false ) . '
                > ' . esc_html( $value ) . '</label>';
            }
            
            $output .= $field['after'];
            
            break;
        }
        case "select": 
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['required'] = isset( $field['required'] ) ? $field['required'] : false;
            $field['options'] = ( isset( $field['options'] ) && is_array( $field['options'] ) ) ? $field['options'] : array();
            
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            
            $output .= $field['before'];
            
            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                if ($field['required'])
                {
                    $output .= '<span class="required"> *</span>';
                }
                $output .= '</label>';
            }
            
            $output .= '<select 
                name="' . esc_attr( $key ) . '" 
                id="' . esc_attr( $key ) . '" 
                class="' . esc_attr( $field['class'] ) . '"
             >';
            
            foreach ( $field['options'] as $option_key => $value ) 
            {
                $output .= '<option 
                    value="' . esc_attr( $option_key ) . '" 
                    ' . selected( esc_attr( $field['value'] ), esc_attr( $option_key ), false ) . '
                >' . esc_html( $value ) . '</option>';
            }
            
            $output .= '</select>';
            
            $output .= $field['after'];
            
            break;
        }
        case "office": 
        {
            $key = 'officeID';
            
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            
            $output .= $field['before'];
            
            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
            }
            
            $output .= '<select 
                name="' . esc_attr( $key ) . '" 
                id="' . esc_attr( $key ) . '" 
                class="' . esc_attr( $field['class'] ) . '"
             >';

             $output .= '<option 
                        value="" 
                        ' . selected( esc_attr( $field['value'] ), esc_attr( '' ), false ) . '
                    >' . esc_html( __( 'No preference', 'propertyhive' ) ) . '</option>';

             $args = array(
                'post_type' => 'office',
                'nopaging' => true,
                'orderby' => 'title',
                'order' => 'ASC'
            );
            $office_query = new WP_Query($args);
            
            if ($office_query->have_posts())
            {
                while ($office_query->have_posts())
                {
                    $office_query->the_post();
            
                    $output .= '<option 
                        value="' . esc_attr( $post->ID ) . '" 
                        ' . selected( esc_attr( $field['value'] ), esc_attr( $post->ID ), false ) . '
                    >' . esc_html( get_the_title() ) . '</option>';
                
                }
            }
            wp_reset_postdata();
            
            $output .= '</select>';
            
            $output .= $field['after'];
            
            break;
        }
        case "country": 
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            
            $output .= $field['before'];
            
            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
            }
            
            $output .= '<select 
                name="' . esc_attr( $key ) . '" 
                id="' . esc_attr( $key ) . '" 
                class="' . esc_attr( $field['class'] ) . '"
             >';

             $output .= '<option 
                        value="" 
                        ' . selected( esc_attr( $field['value'] ), esc_attr( '' ), false ) . '
                    >' . esc_html( __( 'No preference', 'propertyhive' ) ) . '</option>';

            $countries = get_option( 'propertyhive_countries', array() );
            if ( is_array($countries) && !empty($countries) )
            {
                $ph_countries = new PH_Countries;

                foreach ( $countries as $country )
                {
                    $ph_country = $ph_countries->get_country( $country );

                    if ( $ph_country !== FALSE )
                    {
                        $output .= '<option 
                        value="' . esc_attr( $country ) . '" 
                        ' . selected( esc_attr( $field['value'] ), esc_attr( $country ), false ) . '
                        >' . esc_html( $ph_country['name'] ) . '</option>';
                    }
                }
            }
            
            $output .= '</select>';
            
            $output .= $field['after'];
            
            break;
        }
        case "hidden": 
        {
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = $_GET[$key];
            }
            
            $output .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . $field['value'] . '">';
            break;
        }
        case "html": 
        {
            $field['html'] = isset( $field['html'] ) ? $field['html'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';

            $output .= $field['before'];
            $output .= $field['html'];
            $output .= $field['after'];
            
            break;   
        }
        default:
        {
            if ( taxonomy_exists($field['type']) )
            {
                $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
                $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
                $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
                $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
                $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
                $field['blank_option'] = isset( $field['blank_option'] ) ? $field['blank_option'] : __( 'No preference', 'propertyhive' );
                
                $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
                if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
                {
                    $field['value'] = $_GET[$key];
                }
                
                $output .= $field['before'];
                
                if ($field['show_label'])
                {
                    $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
                }
                
                $output .= '<select 
                    name="' . esc_attr( $key ) . '" 
                    id="' . esc_attr( $key ) . '" 
                    class="' . esc_attr( $field['class'] ) . '"
                 >';

                $options = array( '' => $field['blank_option'] );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( $field['type'], $args );
                
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
                        $subterms = get_terms( $field['type'], $args );
                        
                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            foreach ($subterms as $term)
                            {
                                $options[$term->term_id] = '- ' . $term->name;
                                
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subsubterms = get_terms( $field['type'], $args );
                                
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
                }

                foreach ( $options as $option_key => $value ) 
                {
                    $output .= '<option 
                        value="' . esc_attr( $option_key ) . '" 
                        ' . selected( esc_attr( $field['value'] ), esc_attr( $option_key ), false ) . '
                    >' . esc_html( $value ) . '</option>';
                }

                $output .= '</select>';
                
                $output .= $field['after'];
            }
        }
    }
    
    echo $output;
}