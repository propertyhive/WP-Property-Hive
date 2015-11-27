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
    
    ph_get_template( 'global/search-form.php', array( 'form_controls' => $form_controls) );

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
    
    $fields['department'] = array(
        'type' => 'radio',
        'options' => $departments,
        'value' => $value
    );
    
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
        'label' => __( 'Min Bedrooms', 'propertyhive' ),
        'options' => array( '' => __( 'No preference', 'propertyhive' ), 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5)
    );
    
    // Property Type
    $options = array( '' => __( 'No preference', 'propertyhive' ) );
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
    }
    
    $fields['property_type'] = array(
        'type' => 'select',
        'show_label' => true, 
        'label' => __( 'Type', 'propertyhive' ),
        'options' => $options
    );
    
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
    
    $form_controls = apply_filters( 'propertyhive_property_enquiry_form_fields', array( 'form_controls' => $form_controls ) );
    
    ph_get_template( 'global/make-enquiry-form.php', $form_controls );
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
    
    $fields['email_address'] = array(
        'type' => 'email',
        'label' => __( 'Email Address', 'propertyhive' ),
        'required' => true
    );
    
    $fields['telephone_number'] = array(
        'type' => 'text',
        'label' => __( 'Telephone Number', 'propertyhive' ),
        'required' => true
    );
    
    $fields['message'] = array(
        'type' => 'textarea',
        'label' => __( 'Message', 'propertyhive' ),
        'required' => true
    );
    
    return $fields;
}

/**
 * Output individual field
 *
 * @return void
 */
function ph_form_field( $key, $field )
{
    $output = '';
    
    switch ($field['type'])
    {
        case "text": 
        case "email": 
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
            $field['required'] = isset( $field['required'] ) ? $field['label'] : false;
            
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
            
            $output .= '<input 
                    type="' . esc_attr( $field['type'] ) . '" 
                    name="' . esc_attr( $key ) . '" 
                    id="' . esc_attr( $key ) . '" 
                    value="' . esc_attr(  $field['value'] ) . '"
                    placeholder="' . esc_attr(  $field['placeholder'] ) . '"
                    class="' . esc_attr( $field['class'] ) . '"
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
            $field['required'] = isset( $field['required'] ) ? $field['label'] : false;
            
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
    }
    
    echo $output;
}