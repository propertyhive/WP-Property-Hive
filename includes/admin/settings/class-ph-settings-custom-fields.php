<?php
/**
 * PropertyHive Custom Fields Settings
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Custom_Fields' ) ) :

/**
 * PH_Settings_General
 */
class PH_Settings_Custom_Fields extends PH_Settings_Page {

    const LINKED_POSTS_COLUMN_HEADING = 'Assigned Properties';

    private $custom_field_sections;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'customfields';
        $this->label = __( 'Field Manager', 'propertyhive' );

        add_action( 'admin_init', array( $this, 'check_for_delete_additional_field') );
        add_action( 'admin_init', array( $this, 'check_for_reorder_additional_fields') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 15 );
        add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_admin_field_additional_fields_table', array( $this, 'additional_fields_table' ) );

        add_action( 'propertyhive_admin_field_additional_field_dropdown_options', array( $this, 'additional_field_dropdown_options' ) );

        $this->get_custom_field_sections();
    }

    public function check_for_delete_additional_field()
    {
        if ( isset($_GET['action']) && $_GET['action'] == 'deleteadditionalfield' && isset($_GET['id']) && $_GET['id'] != '' )
        {
            $current_settings = get_option( 'propertyhive_template_assistant', array() );

            $current_id = ( !isset( $_GET['id'] ) ) ? '' : sanitize_title( $_GET['id'] );

            $existing_custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

            if ( !isset($existing_custom_fields[$current_id]) )
            {
                die("Trying to delete a non-existant additional field. Please go back and try again");
            }

            if ( isset($existing_custom_fields[$current_id]) )
            {
                unset($existing_custom_fields[$current_id]);
            }

            $current_settings['custom_fields'] = $existing_custom_fields;

            update_option( 'propertyhive_template_assistant', $current_settings );
        }
    }

    public function check_for_reorder_additional_fields()
    {
        if ( isset($_GET['neworder']) && $_GET['neworder'] != '' )
        {
            $current_settings = get_option( 'propertyhive_template_assistant', array() );

            $current_id = ( !isset( $_GET['id'] ) ) ? '' : sanitize_title( $_GET['id'] );

            $existing_custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

            $new_order = explode(",", sanitize_text_field($_GET['neworder']));
            $new_order = ph_clean($new_order);

            $new_custom_fields = array();

            foreach ( $new_order as $id )
            {
                $new_custom_fields[] = $existing_custom_fields[$id];
            }

            $current_settings['custom_fields'] = $new_custom_fields;

            update_option( 'propertyhive_template_assistant', $current_settings );

            header("Location: " . admin_url('admin.php?page=ph-settings&tab=customfields&section=additional'));
            exit();
        }
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            ''              => __( 'Field Values', 'propertyhive' ),
            'additional'    => __( 'Additional Fields', 'propertyhive' )
        );

        return $sections;
    }

    public function get_custom_field_sections()
    {
        $sections = array();
        
        $residential_active = false;
        $residential_sales_active = false;
        $residential_lettings_active = false;
        $commercial_active = false;

        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' || get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
        {
            $residential_active = true;
            if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
            {
                $residential_sales_active = true;
            }
            if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
            {
                $residential_lettings_active = true;
            }
        }
        if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
        {
            $commercial_active = true;
        }

        $default_departments = ph_get_departments(true);
        $custom_departments = ph_get_custom_departments(false);
        if ( $custom_departments )
        {
            foreach ( $custom_departments as  $key => $custom_department )
            {
                if ( isset($custom_department['based_on']) && get_option('propertyhive_active_departments_' . $key) == 'yes' )
                {
                    foreach ( $default_departments as $dept_key => $value )
                    {
                        if ( $custom_department['based_on'] == $dept_key )
                        {
                            switch ( $custom_department['based_on'] )
                            {
                                case "residential-sales": { $residential_active = true; $residential_sales_active = true; }
                                case "residential-lettings": { $residential_active = true; $residential_lettings_active = true; }
                                case "commercial": { $commercial_active = true; }
                            }
                        }
                    }
                }
            }
        }
        
        // Residential Custom Fields
        $sections[ 'availability' ] = __( 'Availabilities', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_availability', array( $this, 'custom_fields_availability_setting' ) );

        if ( $residential_active )
        {
            $sections[ 'property-type' ] = ( $commercial_active ? __( 'Residential ', 'propertyhive' ) . ' ' : '' ) . __( 'Property Types', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_property_type', array( $this, 'custom_fields_property_type_setting' ) );
        }
        if ( $commercial_active )
        {
            $sections[ 'commercial-property-type' ] = ( $residential_active ? __( 'Commercial', 'propertyhive' ) . ' ' : '' ) . __( 'Property Types', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_commercial_property_type', array( $this, 'custom_fields_commercial_property_type_setting' ) );
        }

        $sections[ 'location' ] = __( 'Locations', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_location', array( $this, 'custom_fields_location_setting' ) );
        
        if ( $residential_active )
        {
            $sections[ 'parking' ] = __( 'Parking', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_parking', array( $this, 'custom_fields_parking_setting' ) );
            
            $sections[ 'outside-space' ] = __( 'Outside Spaces', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_outside_space', array( $this, 'custom_fields_outside_space_setting' ) );
        }

        if ( $residential_sales_active || $commercial_active )
        {
            $sections[ 'price-qualifier' ] = __( 'Price Qualifiers', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_price_qualifier', array( $this, 'custom_fields_price_qualifier_setting' ) );
            
            $sections[ 'sale-by' ] = __( 'Sale By', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_sale_by', array( $this, 'custom_fields_sale_by_setting' ) );
        }

        if ( $residential_active )
        {
            $sections[ 'tenure' ] = ( $commercial_active ? __( 'Residential', 'propertyhive' ) . ' ' : '' ) . __( 'Tenures', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_tenure', array( $this, 'custom_fields_tenure_setting' ) );
        }
        if ( $commercial_active )
        {
            $sections[ 'commercial-tenure' ] = ( $residential_active ? __( 'Commercial', 'propertyhive' ) . ' ' : '' ) . __( 'Tenures', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_commercial_tenure', array( $this, 'custom_fields_commercial_tenure_setting' ) );
        }

        if ( $residential_lettings_active )
        {
            $sections[ 'furnished' ] = __( 'Furnished', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_furnished', array( $this, 'custom_fields_furnished_setting' ) );

            if ( get_option( 'propertyhive_module_disabled_tenancies', '' ) != 'yes' )
            {
                $sections[ 'management-key-date-type' ] = __( 'Management Date Types', 'propertyhive' );
                add_action( 'propertyhive_admin_field_custom_fields_management_key_date_type', array( $this, 'custom_fields_management_key_date_type_setting' ) );
            }
        }

        // Other
        $sections[ 'marketing-flag' ] = __( 'Marketing Flags', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_marketing_flag', array( $this, 'custom_fields_marketing_flag_setting' ) );

        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            $sections[ 'property-feature' ] = __( 'Property Features', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_property_feature', array( $this, 'custom_fields_property_feature_setting' ) );
        }

        $sections = apply_filters( 'propertyhive_admin_field_custom_fields_sections', $sections );

        $this->custom_field_sections = $sections;
    }
    
    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        global $hide_save_button;
        
        $hide_save_button = true;

        $i = 0;
        $html = '<style>

            .ph-custom-fields-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:22px; }
            .ph-custom-fields-grid > div { display:flex; gap:20px; background:#FFF; padding:25px; border:1px solid #AAA }
            .ph-custom-fields-grid > div .ph-grid-image { flex:0 0 50px; }
            .ph-custom-fields-grid > div .ph-grid-image-bg { background:#fbfcd4; border:1px solid #ffcd00; padding:10px; border-radius:7px; }
            .ph-custom-fields-grid > div .ph-grid-image img { max-width:40px; height:40px; display:block }
            .ph-custom-fields-grid > div .feature-card-content { flex:1; min-width:0; }
            .ph-custom-fields-grid > div h3 { margin-top:0; margin-bottom:0.6em }

            @media (max-width:1750px) {

                .ph-custom-fields-grid { grid-template-columns:repeat(3, 1fr); }

            }

            @media (max-width:1370px) {

                .ph-custom-fields-grid { grid-template-columns:repeat(2, 1fr); }

            }

        </style>

        <div class="ph-custom-fields-grid">';
        foreach ( $this->custom_field_sections as $key => $value )
        {
            $image = 'default.png';
            if ( file_exists(PH()->plugin_path() . '/assets/images/admin/settings/custom-fields/' . $key . '.png') )
            {
                $image = $key . '.png';
            }
            $html .= '<div>
                <div class="ph-grid-image">
                    <div class="ph-grid-image-bg"><img alt="" src="' . esc_url(PH()->plugin_url() . '/assets/images/admin/settings/custom-fields/' . $image) . '" /></div>
                </div>
                <div class="feature-card-content">
                    <h3>' . esc_html($value) . '</h3>
                    <p style="margin-top:12px;"><a href="' . esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=' . $key )) . '" class="button-primary">Manage values</a></p>
                </div>
            </div>';
        }
        $html .= '</div>';

        return apply_filters( 'propertyhive_custom_fields_settings', array(

            array( 'title' => __( 'Field Values', 'propertyhive' ), 'type' => 'title', 'desc' => __( 'Manage the values used in dropdowns, filters and fields across properties, contacts and other records.', 'propertyhive' ), 'id' => 'custom_field_options' ),
            
            array(
                'type'      => 'html',
                'title'     => '',
                'html'      => $html,
                'full_width' => true
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_options')
            
        ));
    }

    /**
     * Output the settings
     */
    public function output() {
        global $current_section, $redirect_after_save;

        if ( $current_section ) 
        {
            switch ( $current_section )
            {
                case "additional":
                {
                    global $hide_save_button;
                
                    $hide_save_button = true;
            
                    // The main custom field screen listing them in a table
                    $settings = $this->get_custom_fields_additional_fields_setting();
                
                    PH_Admin_Settings::output_fields( $settings );
                    break;
                }
                case "addadditionalfield":
                case "editadditionalfield":
                {
                    $settings = $this->get_custom_fields_additional_field_settings();

                    $redirect_after_save = admin_url('admin.php?page=ph-settings&tab=customfields&section=additional');
                
                    PH_Admin_Settings::output_fields( $settings );

                    break;
                }
                default:
                {
                    if (isset($_REQUEST['id'])) // we're either adding or editing
                    {
                        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_text_field($_REQUEST['id']);
                        
                        switch ($current_section)
                        {
                            case "availability": { $settings = $this->get_custom_fields_availability_setting(); break; }
                            case "availability-delete": { $settings = $this->get_custom_fields_delete($current_id, 'availability', __( 'Availability', 'propertyhive' )); break; }
                            case "property-type": { $settings = $this->get_custom_fields_property_type_setting(); break; }
                            case "property-type-delete": { $settings = $this->get_custom_fields_delete($current_id, 'property_type', __( 'Property Type', 'propertyhive' )); break; }
                            case "commercial-property-type": { $settings = $this->get_custom_fields_commercial_property_type_setting(); break; }
                            case "commercial-property-type-delete": { $settings = $this->get_custom_fields_delete($current_id, 'commercial_property_type', __( 'Property Type', 'propertyhive' )); break; }
                            case "location": { $settings = $this->get_custom_fields_location_setting(); break; }
                            case "location-delete": { $settings = $this->get_custom_fields_delete($current_id, 'location', __( 'Location', 'propertyhive' )); break; }
                            case "parking": { $settings = $this->get_custom_fields_parking_setting(); break; }
                            case "parking-delete": { $settings = $this->get_custom_fields_delete($current_id, 'parking', __( 'Parking', 'propertyhive' )); break; }
                            case "outside-space": { $settings = $this->get_custom_fields_outside_space_setting(); break; }
                            case "outside-space-delete": { $settings = $this->get_custom_fields_delete($current_id, 'outside_space', __( 'Outside Space', 'propertyhive' )); break; }
                            
                            case "price-qualifier": { $settings = $this->get_custom_fields_price_qualifier_setting(); break; }
                            case "price-qualifier-delete": { $settings = $this->get_custom_fields_delete($current_id, 'price_qualifier', __( 'Price Qualifier', 'propertyhive' )); break; }
                            case "sale-by": { $settings = $this->get_custom_fields_sale_by_setting(); break; }
                            case "sale-by-delete": { $settings = $this->get_custom_fields_delete($current_id, 'sale_by', __( 'Sale By', 'propertyhive' )); break; }
                            case "tenure": { $settings = $this->get_custom_fields_tenure_setting(); break; }
                            case "tenure-delete": { $settings = $this->get_custom_fields_delete($current_id, 'tenure', __( 'Tenure', 'propertyhive' )); break; }
                            case "commercial-tenure": { $settings = $this->get_custom_fields_commercial_tenure_setting(); break; }
                            case "commercial-tenure-delete": { $settings = $this->get_custom_fields_delete($current_id, 'commercial_tenure', __( 'Tenure', 'propertyhive' )); break; }
                            
                            case "furnished": { $settings = $this->get_custom_fields_furnished_setting(); break; }
                            case "furnished-delete": { $settings = $this->get_custom_fields_delete($current_id, 'furnished', __( 'Furnished', 'propertyhive' )); break; }
                            case "management-key-date-type": { $settings = $this->get_custom_fields_management_key_date_type_setting(); break; }
                            case "management-key-date-type-delete": { $settings = $this->get_custom_fields_delete($current_id, 'management_key_date_type', __( 'Management Date Types', 'propertyhive' )); break; }

                            case "marketing-flag": { $settings = $this->get_custom_fields_marketing_flag_setting(); break; }
                            case "marketing-flag-delete": { $settings = $this->get_custom_fields_delete($current_id, 'marketing_flag', __( 'Marketing Flag', 'propertyhive' )); break; }
                            
                            case "property-feature": { $settings = $this->get_custom_fields_property_feature_setting(); break; }
                            case "property-feature-delete": { $settings = $this->get_custom_fields_delete($current_id, 'property_feature', __( 'Property Feature', 'propertyhive' )); break; }

                            default:
                            {
                                $settings = apply_filters( 'propertyhive_custom_fields_section_settings', array(), $current_section );

                                if ( empty($settings) )
                                {
                                    echo 'UNKNOWN CUSTOM FIELD';
                                }
                            }
                        }

                        if ( strpos($current_section, '-delete') === FALSE )
                        {
                            $redirect_after_save = admin_url('admin.php?page=ph-settings&tab=customfields&section=' . $current_section);
                        }

                        PH_Admin_Settings::output_fields( $settings );
                    }
                    else
                    {
                        global $hide_save_button;
                
                        $hide_save_button = true;
                
                        // The main custom field screen listing them in a table
                        $settings = $this->get_custom_fields_setting($current_section);
                    
                        PH_Admin_Settings::output_fields( $settings );
                    }
                }
            }
        }
        else 
        {
            $settings = $this->get_settings();

            PH_Admin_Settings::output_fields( $settings );
        }
    }

    public function get_custom_fields_additional_field_settings()
    {
        global $current_section;

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( !isset($current_settings['custom_fields']) )
        {
            $current_settings['custom_fields'] = array();
        }

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $custom_field_details = array();

        if ($current_id != '')
        {
            $custom_fields = $current_settings['custom_fields'];

            if (isset($custom_fields[$current_id]))
            {
                $custom_field_details = $custom_fields[$current_id];
            }
            else
            {
                die('Trying to edit an additional field which does not exist. Please go back and try again.');
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addadditionalfield' ? 'Add Additional Field' : 'Edit Additional Field' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'customfield' ),

        );

        $settings[] = array(
            'title' => __( 'Field Label', 'propertyhive' ),
            'id'        => 'field_label',
            'default'   => ( (isset($custom_field_details['field_label'])) ? $custom_field_details['field_label'] : ''),
            'type'      => 'text',
            'desc_tip'  =>  false,
            'custom_attributes' => array(
                'placeholder' => 'My New Field'
            )
        );

        if ( isset($custom_field_details['field_name']) )
        {
            $settings[] = array(
                'title' => __( 'Field Name', 'propertyhive' ),
                'id'        => 'field_name',
                'default'   => ( (isset($custom_field_details['field_name'])) ? $custom_field_details['field_name'] : ''),
                'type'      => 'text',
                'desc'  => __( 'Please note that changing this after properties have been saved will result in any data entered being lost', 'propertyhive' ),
            );
        }

        $settings[] = array(
            'title' => __( 'Field Type', 'propertyhive' ),
            'id'        => 'field_type',
            'default'   => ( (isset($custom_field_details['field_type'])) ? $custom_field_details['field_type'] : 'text'),
            'type'      => 'select',
            'desc_tip'  =>  false,
            'options'   => array(
                'text' => 'Text',
                'textarea' => 'Textarea',
                'select' => 'Dropdown',
                'multiselect' => 'Multi-Select',
                'checkbox' => 'Checkbox',
                'date' => 'Date',
                'image' => 'Image',
                'file' => 'File',
            )
        );

        $settings[] = array(
            'title' => __( 'Dropdown Options', 'propertyhive' ),
            'id'        => 'dropdown_options',
            'type'      => 'additional_field_dropdown_options',
        );

        $options = array(
            'property_address' => 'Property Address',
            'property_department' => 'Property Department',
        );

        if ( get_option( 'propertyhive_active_departments_sales', '' ) == 'yes' || get_option( 'propertyhive_active_departments_lettings', '' ) == 'yes' )
        {
            $options['property_residential_details'] = __( 'Property Residential Details', 'propertyhive' );

            if ( get_option( 'propertyhive_active_departments_sales', '' ) == 'yes' )
            {
                $options['property_residential_sales_details'] = __( 'Property Residential Sales Details', 'propertyhive' );
            }
            if ( get_option( 'propertyhive_active_departments_lettings', '' ) == 'yes' )
            {
                $options['property_residential_lettings_details'] = __( 'Property Residential Lettings Details', 'propertyhive' );
            }
        }

        if ( get_option( 'propertyhive_active_departments_commercial', '' ) == 'yes' )
        {
            $options['property_commercial_details'] = __( 'Property Commercial Details', 'propertyhive' );
        }

        $options['property_marketing'] = __( 'Property Marketing', 'propertyhive' );

        if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
        {
            $options['contact_correspondence_address'] = __( 'Contact Correspondence Address', 'propertyhive' );
            $options['contact_contact_details'] = __( 'Contact Contact Details', 'propertyhive' );
        }

        if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
        {
            $options['enquiry_record_details'] = __( 'Enquiry Record Details', 'propertyhive' );
        }

        if ( get_option('propertyhive_module_disabled_appraisals', '') != 'yes' )
        {
            $options['appraisal_details'] = __( 'Appraisal Details', 'propertyhive' );
            $options['appraisal_event'] = __( 'Appraisal Event Details', 'propertyhive' );
        }

        if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
        {
            $options['viewing_details'] = __( 'Viewing Details', 'propertyhive' );
            $options['viewing_event'] = __( 'Viewing Event Details', 'propertyhive' );
        }

        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
        {
            $options['offer_details'] = __( 'Offer Details', 'propertyhive' );
            $options['sale_details'] = __( 'Sale Details', 'propertyhive' );
        }

        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' && get_option('propertyhive_module_disabled_tenancies', '') != 'yes' )
        {
            $options['tenancy_details'] = __( 'Tenancy Details', 'propertyhive' );
            $options['tenancy_management_details'] = __( 'Tenancy Management Details', 'propertyhive' );
            $options['tenancy_deposit_scheme'] = __( 'Tenancy Deposit Scheme Details', 'propertyhive' );
            $options['tenancy_meter_readings'] = __( 'Tenancy Meter Readings', 'propertyhive' );
        }

        $options['office_details'] = __( 'Office Details', 'propertyhive' );

        $options = apply_filters( 'propertyhive_template_assistant_custom_field_sections', $options );

        $settings[] = array(
            'title' => __( 'Section', 'propertyhive' ),
            'id'        => 'meta_box',
            'default'   => ( (isset($custom_field_details['meta_box'])) ? $custom_field_details['meta_box'] : ''),
            'type'      => 'select',
            'desc'  =>  __( 'Please select which meta box on the property record this field should appear in', 'propertyhive' ),
            'options' => $options
        );

        $settings[] = array(
            'title' => __( 'Display On Website', 'propertyhive' ),
            'id'        => 'display_on_website',
            'default'   => ( (isset($custom_field_details['display_on_website']) && $custom_field_details['display_on_website'] == '1') ? 'yes' : ''),
            'type'      => 'checkbox',
        );

        if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
        {
            $settings[] = array(
                'title' => __( 'Add As Match Field To Applicant Requirements', 'propertyhive' ),
                'id'        => 'display_on_applicant_requirements',
                'default'   => ( (isset($custom_field_details['display_on_applicant_requirements']) && $custom_field_details['display_on_applicant_requirements'] == '1') ? 'yes' : ''),
                'type'      => 'checkbox',
            );
        }

        $settings[] = array(
            'title' => __( 'Exact Match Only If Searching On Field', 'propertyhive' ),
            'id'        => 'exact_match',
            'default'   => ( (isset($custom_field_details['exact_match']) && $custom_field_details['exact_match'] == '1') ? 'yes' : ''),
            'type'      => 'checkbox',
            'desc'  =>  __( 'If you\'re using this checkbox in property searches or matches tick this if properties should only be returned with the same ticked status. Alternatively, leave unticked for scenarios like \'Pets Allowed\' whereby properties with it ticked should come back whether search is ticked or not, but not vice versa.', 'propertyhive' ),
        );

        $settings[] = array(
            'title' => __( 'Display On Registration Form / My Account', 'propertyhive' ),
            'id'        => 'display_on_user_details',
            'default'   => ( (isset($custom_field_details['display_on_user_details']) && $custom_field_details['display_on_user_details'] == '1') ? 'yes' : ''),
            'type'      => 'checkbox',
        );

        $settings[] = array(
            'title' => __( 'Show In Admin List', 'propertyhive' ),
            'id'        => 'admin_list',
            'default'   => ( (isset($custom_field_details['admin_list']) && $custom_field_details['admin_list'] == '1') ? 'yes' : ''),
            'type'      => 'checkbox',
        );

        $settings[] = array(
            'title' => __( 'Sortable In Admin List', 'propertyhive' ),
            'id'        => 'admin_list_sortable',
            'default'   => ( (isset($custom_field_details['admin_list_sortable']) && $custom_field_details['admin_list_sortable'] == '1') ? 'yes' : ''),
            'type'      => 'checkbox',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'customfield');

        $settings[] = array(
            'type' => 'html',
            'html' => '<script>

                jQuery(document).ready(function()
                {
                    ph_hide_show_type_related_checkboxes();

                    jQuery(\'#meta_box\').change(function()
                    {
                        ph_hide_show_type_related_checkboxes();
                    });

                    jQuery(\'#field_type\').change(function()
                    {
                        ph_hide_show_type_related_checkboxes();
                    });
                });

                function ph_hide_show_type_related_checkboxes()
                {
                    var meta_box = jQuery(\'#meta_box\').val();

                    jQuery(\'#row_display_on_website\').hide();
                    jQuery(\'#row_display_on_applicant_requirements\').hide();
                    jQuery(\'#row_display_on_user_details\').hide();
                    jQuery(\'#row_exact_match\').hide();

                    jQuery(\'#row_admin_list\').show();
                    jQuery(\'#row_admin_list_sortable\').show();

                    if ( meta_box.indexOf(\'property_\') != -1 )
                    {
                        jQuery(\'#row_display_on_website\').show();
                        
                        if ( jQuery(\'#field_type\').val() == \'select\' || jQuery(\'#field_type\').val() == \'multiselect\' || jQuery(\'#field_type\').val() == \'checkbox\' )
                        {
                            jQuery(\'#row_display_on_applicant_requirements\').show();
                        }

                        if ( jQuery(\'#field_type\').val() == \'checkbox\' )
                        {
                            jQuery(\'#row_exact_match\').show();
                        }
                    }
                    if ( meta_box.indexOf(\'contact_\') != -1 )
                    {
                        jQuery(\'#row_display_on_user_details\').show();
                    }
                    if ( meta_box == \'tenancy_management_details\' )
                    {
                        jQuery(\'#row_admin_list\').hide();
                        jQuery(\'#row_admin_list_sortable\').hide();
                    }
                }

            </script>'
        );

        return $settings;
    }

    public function additional_field_dropdown_options()
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( !isset($current_settings['custom_fields']) )
        {
            $current_settings['custom_fields'] = array();
        }

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $custom_field_details = array();

        if ($current_id != '')
        {
            $custom_fields = $current_settings['custom_fields'];

            if (isset($custom_fields[$current_id]))
            {
                $custom_field_details = $custom_fields[$current_id];
            }
            else
            {
                die('Trying to edit an additional field which does not exist. Please go back and try again.');
            }
        }

        echo '
        <tr valign="top" id="row_dropdown_options">
            <th scope="row" class="titledesc">
                <label for="field_type">Dropdown Options</label>
            </th>
            <td class="forminp forminp-dropdown-options"><div id="sortable_options_' . $current_id . '">';
        if ( isset($custom_field_details['dropdown_options']) && !empty($custom_field_details['dropdown_options']) )
        {
            foreach ( $custom_field_details['dropdown_options'] as $dropdown_option )
            {
                echo '
                    <div><i class="fa fa-reorder" style="cursor:pointer; opacity:0.3"></i> <input type="text" name="dropdown_options[]" value="' . $dropdown_option . '"> <a href="" class="delete-dropdown-option">Delete Option</a></div>
                ';
            }
        }
        else
        {
            // None exist
            echo '
                <div><i class="fa fa-reorder" style="cursor:pointer; opacity:0.3"></i> <input type="text" name="dropdown_options[]" placeholder="Add Option"> <a href="" class="delete-dropdown-option">Delete Option</a></div>
            ';
        }
        echo '
                </div>
                <a href="" class="add-dropdown-option">Add New Option</a>
            </td>
        </tr>

        <script>
            jQuery(document).ready(function()
            {
                toggle_dropdown_options();

                jQuery(\'#field_type\').change(function()
                {
                    toggle_dropdown_options();
                });

                jQuery(\'body\').on(\'click\', \'a.add-dropdown-option\', function(e)
                {
                    e.preventDefault();

                    jQuery(\'.forminp-dropdown-options > div\').append(\'<div><i class="fa fa-reorder" style="cursor:pointer; opacity:0.3"></i> <input type="text" name="dropdown_options[]" placeholder="Add Option"> <a href="" class="delete-dropdown-option">Delete Option</a></div>\');
                });

                jQuery(\'body\').on(\'click\', \'a.delete-dropdown-option\', function(e)
                {
                    e.preventDefault();

                    var confirmBox = confirm(\'Are you sure you wish to delete this option?\');

                    if ( confirmBox )
                    {
                        jQuery(this).parent().remove();
                    }
                });

                jQuery( \'#sortable_options_' . $current_id . '\' )
                .sortable({
                    axis: "y",
                    handle: "i",
                    stop: function( event, ui ) 
                    {
                        // IE doesn\'t register the blur when sorting
                        // so trigger focusout handlers to remove .ui-state-focus
                        //ui.item.children( "h3" ).triggerHandler( "focusout" );
             
                        // Refresh accordion to handle new order
                        //jQuery( this ).accordion( "refresh" );
                    },
                    update: function( event, ui ) 
                    {
                        // Update hidden fields
                        var fields_order = jQuery(this).sortable(\'toArray\');
                        
                        //$(\'#active_fields_order\').val( fields_order.join("|") );
                    }
                });
            });

            function toggle_dropdown_options()
            {
                if ( jQuery(\'#field_type\').val() == \'select\' || jQuery(\'#field_type\').val() == \'multiselect\' )
                {
                    jQuery(\'#row_dropdown_options\').show();
                }
                else
                {
                    jQuery(\'#row_dropdown_options\').hide();
                }
            }
        </script>
        ';
    }

    public function get_custom_fields_additional_fields_setting()
    {
        return apply_filters( 'propertyhive_custom_fields_settings', array(

            array( 'title' => __( 'Additional Fields', 'propertyhive' ), 'type' => 'title', 'desc' => __( 'Create new fields to store information not included by default.', 'propertyhive' ), 'id' => 'additional_field_options' ),
            
            array(
                'type' => 'additional_fields_table',
            ),
            
            array( 'type' => 'sectionend', 'id' => 'additional_field_options')
            
        ));
    }

    /**
     * Output list of additional fields
     *
     * @access public
     * @return void
     */
    public function additional_fields_table() {
        global $wpdb, $post;

        $current_settings = get_option( 'propertyhive_template_assistant', array() );
        $custom_fields = array();
        if ($current_settings !== FALSE)
        {
            if (isset($current_settings['custom_fields']))
            {
                $custom_fields = $current_settings['custom_fields'];
            }
        }
?>
        <tr valign="top">
            <td class="forminp forminp-button">
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=addadditionalfield' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Field', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <td class="forminp">
                <style type="text/css">
                    .ui-sortable-helper {
                        display: table;
                    }
                </style>
                <table class="ph_additional_fields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="padding:8px 10px;" class="field-label"><?php esc_html_e( 'Field Name', 'propertyhive' ); ?></th>
                            <th style="padding:8px 10px;" class="section"><?php esc_html_e( 'Section', 'propertyhive' ); ?></th>
                            <th style="padding:8px 10px;" class="usage"><?php esc_html_e( 'Usage', 'propertyhive' ); ?></th>
                            <th style="padding:8px 10px;" class="website"><?php esc_html_e( 'Display On Website', 'propertyhive' ); ?></th>
                            <th style="padding:8px 10px;" class="admin-list"><?php esc_html_e( 'Show In Admin List', 'propertyhive' ); ?></th>
                            <th style="padding:8px 10px;" class="admin-list-sorting"><?php esc_html_e( 'Sortable In Admin List', 'propertyhive' ); ?></th>
                            <th style="padding:8px 10px;" class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody class="<?php echo !empty($custom_fields) ? 'has-rows' : ''; ?>">
                        <?php

                            if (!empty($custom_fields))
                            {
                                foreach ($custom_fields as $id => $custom_field)
                                {
                                    echo '<tr id="custom_field_' . esc_attr($id) . '">';
                                        echo '<td class="field-label"><span class="sort_anchor" style="cursor:grab "> ⇅ </span>' . esc_html($custom_field['field_label']) . '</td>';
                                        echo '<td class="section">' . esc_html(ucwords( str_replace("_", " ", $custom_field['meta_box']) )) . '</td>';
                                        echo '<td class="usage">';
                                        if ( substr( $custom_field['meta_box'], 0, 8 ) == 'property' ) { echo '<pre style="background:#EEE; padding:5px; display:inline">&lt;?php $property->' . esc_html(ltrim( $custom_field['field_name'], '_' )) . '; ?&gt;</pre>'; }else{ echo '-';}
                                        echo '</td>';
                                        echo '<td class="website">';
                                        if ( substr( $custom_field['meta_box'], 0, 8 ) == 'property' ) { echo esc_html( ( ( isset($custom_field['display_on_website']) && $custom_field['display_on_website'] == '1' ) ? __( 'Yes', 'propertyhive' ) : __( 'No', 'propertyhive' ) ) ); }else{ echo '-'; }
                                        echo '</td>';
                                        echo '<td class="admin-list">';
                                        echo esc_html( ( ( isset($custom_field['admin_list']) && $custom_field['admin_list'] == '1' ) ? __( 'Yes', 'propertyhive' ) : __( 'No', 'propertyhive' ) ) );
                                        echo '</td>';
                                        echo '<td class="sorting">';
                                        if ( ( isset($custom_field['admin_list']) && $custom_field['admin_list'] == '1' ) )
                                        {
                                            echo esc_html( ( ( isset($custom_field['admin_list_sortable']) && $custom_field['admin_list_sortable'] == '1' ) ? __( 'Yes', 'propertyhive' ) : __( 'No', 'propertyhive' ) ) );
                                        }
                                        else
                                        {
                                            echo '-';
                                        }
                                        echo '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=editadditionalfield&id=' . $id )) . '">' . esc_html(__( 'Edit Field', 'propertyhive' )) . '</a>
                                            <a class="button" href="' . esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=additional&action=deleteadditionalfield&id=' . $id )) . '" onclick="var confirmBox = confirm(\'Are you sure you wish to delete this custom field?\'); return confirmBox;">' . esc_html(__( 'Delete', 'propertyhive' )) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="7">' . esc_html(__( 'No additional fields exist', 'propertyhive' )) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>

                <script>
                    jQuery( function($){

                        $('.ph_additional_fields tbody.has-rows').sortable({
                             opacity: 0.8,
                             revert: true,
                             handle: ".sort_anchor",
                             update : function (event, ui) 
                             {
                                    $('.ph_additional_fields tbody.has-rows').sortable( "destroy" );

                                    var new_order = '';
                                    jQuery('.ph_additional_fields tbody.has-rows').find('tr').each( function () 
                                    {
                                        if (new_order != '')
                                        {
                                            new_order += ',';
                                        }
                                        new_order = new_order + jQuery(this).attr('id').replace('custom_field_', '');
                                    });

                                    // reload page
                                    window.location.href = '<?php echo admin_url('admin.php?page=ph-settings&tab=customfields&section=additional'); ?>&neworder=' + new_order;
                            }
                        });

                    });
                </script>
            </td>
        </tr>
        <tr valign="top">
            <td class="forminp forminp-button">
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=addadditionalfield' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Field', 'propertyhive' )); ?></a>
            </td>
        </tr>
<?php
    }

    /**
     * Output custom fields settings.
     *
     * @access public
     * @return void
     */
    public function get_custom_fields_setting($current_section) {
        
        $sections = $this->custom_field_sections;
        
        return apply_filters( 'propertyhive_custom_fields_' . $current_section . '_settings', array(

            array( 'title' => $sections[$current_section], 'type' => 'title', 'desc' => '', 'id' => 'custom_fields_' . $current_section . '_options' ),
            
            array(
                'type'      => 'custom_fields_' . str_replace("-", "_", $current_section),
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_fields_' . $current_section . '_options'),
            
        ));
        
    }

    /**
     * Output list of availabilities
     *
     * @access public
     * @return void
     */
    public function custom_fields_availability_setting() {
        global $post;

        $departments = ph_get_departments();

        $availability_departments = get_option( 'propertyhive_availability_departments', array() );
        if ( !is_array($availability_departments) ) { $availability_departments = array(); }
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=availability&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Availability', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Availability Options', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="availability" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <?php do_action( 'propertyhive_custom_field_availability_table_before_header_column' ); ?>
                            <th class="type"><?php echo esc_html(__( 'Availability', 'propertyhive' )); ?></th>
                            <th class="department"><?php echo esc_html(__( 'Applies To', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'availability', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ( $terms as $term )
                            {
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <?php do_action( 'propertyhive_custom_field_availability_table_before_row_column', $term->term_id ); ?>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="department"><?php
                                $this_availability_departments = array();
                                if ( isset($availability_departments[$term->term_id]) )
                                {
                                    foreach ( $availability_departments[$term->term_id] as $availability_department )
                                    {
                                        if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $availability_department) ) == 'yes' )
                                        {
                                            $this_availability_departments[] = $departments[$availability_department];
                                        }
                                    }
                                }
                                else
                                {
                                    foreach ( $departments as $key => $value )
                                    {
                                        if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                                        {
                                            $this_availability_departments[] = $value;
                                        }
                                    }
                                }

                                echo !empty($this_availability_departments) ? esc_html(implode(", ", $this_availability_departments)) : '-';
                            ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=availability&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=availability-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="6"><?php echo esc_html(__( 'No availability options found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=availability&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Availability', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }
    
    /**
     * Output list of residential property types
     *
     * @access public
     * @return void
     */
    public function custom_fields_property_type_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-type&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Property Type', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Property Types', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <?php do_action( 'propertyhive_custom_field_property_type_table_before_header_column' ); ?>
                            <th class="type"><?php echo esc_html(__( 'Property Type', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'property_type', $args );

                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $parent_term_id = $term->term_id;

                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $parent_term_id
                                );
                                $subterms = get_terms( 'property_type', $args );
                        ?>
                        <tr>
                            <td class="cb"><?php if ( empty( $subterms ) ) { ?><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"><?php }else{ echo '&nbsp;'; } ?></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <?php do_action( 'propertyhive_custom_field_property_type_table_before_row_column', $term->term_id ); ?>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-type&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <?php if ( empty( $subterms ) ) { ?>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-type-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        ?>
                                        <tr>
                                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                                            <?php do_action( 'propertyhive_custom_field_property_type_table_before_row_column', $term->term_id, $parent_term_id ); ?>
                                            <td class="type subtype">&nbsp;&nbsp;&nbsp;- <?php echo esc_html($term->name); ?></td>
                                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                                            <td class="settings">
                                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-type&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-type-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                            </td>
                                        </tr>
                                        <?php   
                                    }
                                }
                        ?>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No property types found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-type&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Property Type', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of commercial property types
     *
     * @access public
     * @return void
     */
    public function custom_fields_commercial_property_type_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-property-type&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Property Type', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Property Types', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <?php do_action( 'propertyhive_custom_field_commercial_property_type_table_before_header_column' ); ?>
                            <th class="type"><?php echo esc_html(__( 'Property Type', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'commercial_property_type', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $parent_term_id = $term->term_id;

                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $parent_term_id
                                );
                                $subterms = get_terms( 'commercial_property_type', $args );
                        ?>
                        <tr>
                            <td class="cb"><?php if ( empty( $subterms ) ) { ?><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"><?php }else{ echo '&nbsp;'; } ?></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <?php do_action( 'propertyhive_custom_field_commercial_property_type_table_before_row_column', $term->term_id ); ?>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-property-type&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <?php if ( empty( $subterms ) ) { ?>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-property-type-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        ?>
                                        <tr>
                                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                                            <?php do_action( 'propertyhive_custom_field_commercial_property_type_table_before_row_column', $term->term_id, $parent_term_id ); ?>
                                            <td class="type subtype">&nbsp;&nbsp;&nbsp;- <?php echo esc_html($term->name); ?></td>
                                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                                            <td class="settings">
                                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-property-type&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-property-type-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                            </td>
                                        </tr>
                                        <?php   
                                    }
                                }
                        ?>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No property types found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-property-type&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Property Type', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of locations
     *
     * @access public
     * @return void
     */
    public function custom_fields_location_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Location', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Locations', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Location', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'location', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subterms = get_terms( 'location', $args );
                        ?>
                        <tr>
                            <td class="cb"><?php if ( empty( $subterms ) ) { ?><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"><?php }else{ echo '&nbsp;'; } ?></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <?php if ( empty( $subterms ) ) { ?>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        $args = array(
                                            'hide_empty' => false,
                                            'parent' => $term->term_id
                                        );
                                        $subsubterms = get_terms( 'location', $args );
                                        ?>
                                        <tr>
                                            <td class="cb"><?php if ( empty( $subsubterms ) ) { ?><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"><?php }else{ echo '&nbsp;'; } ?></td>
                                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                                            <td class="type subtype">&nbsp;&nbsp;&nbsp;- <?php echo esc_html($term->name); ?></td>
                                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                                            <td class="settings">
                                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                                <?php if ( empty( $subsubterms ) ) { ?>
                                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                        {
                                            foreach ($subsubterms as $term)
                                            {
                                                ?>
                                                <tr>
                                                    <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                                                    <td class="id"><?php echo esc_html($term->term_id); ?></td>
                                                    <td class="type subtype">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?php echo esc_html($term->name); ?></td>
                                                    <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                                                    <td class="settings">
                                                        <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                                        <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                                                    </td>
                                                </tr>
                                                <?php   
                                            }
                                        }
                                    }
                                }
                        ?>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No locations found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=location&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Location', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }
    
    /**
     * Output list of parking
     *
     * @access public
     * @return void
     */
    public function custom_fields_parking_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=parking&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Parking', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Parking Options', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="parking" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Parking', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'parking', $args );

                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=parking&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=parking-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No parking options found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=parking&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Parking', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }
    
    /**
     * Output list of outside spaces
     *
     * @access public
     * @return void
     */
    public function custom_fields_outside_space_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=outside-space&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Outside Space', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Outside Spaces', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="outside_space" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Outside Space', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'outside_space', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=outside-space&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=outside-space-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No outside spaces found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=outside-space&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Outside Space', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of price qualifiers
     *
     * @access public
     * @return void
     */
    public function custom_fields_price_qualifier_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=price-qualifier&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Price Qualifier', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Price Qualifiers', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="price_qualifier" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Price Qualifier', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'price_qualifier', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=price-qualifier&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=price-qualifier-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No price qualifiers found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=price-qualifier&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Price Qualifier', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of sale by options
     *
     * @access public
     * @return void
     */
    public function custom_fields_sale_by_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=sale-by&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Sale By', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Sale By Options', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="sale_by" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Sale By', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'sale_by', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr>
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=sale-by&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=sale-by-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No sale by options found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=sale-by&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Sale By', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of residential tenure options
     *
     * @access public
     * @return void
     */
    public function custom_fields_tenure_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=tenure&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Tenure', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Tenures', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="tenure" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Tenure', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'tenure', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=tenure&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=tenure-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No tenure found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=tenure&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Tenure', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of commercial tenure options
     *
     * @access public
     * @return void
     */
    public function custom_fields_commercial_tenure_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-tenure&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Tenure', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Tenures', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="commercial_tenure" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Tenure', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'commercial_tenure', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-tenure&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-tenure-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No tenure found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=commercial-tenure&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Tenure', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of furnished options
     *
     * @access public
     * @return void
     */
    public function custom_fields_furnished_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=furnished&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Furnished Option', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Furnished Options', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="furnished" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Furnished', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'furnished', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=furnished&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=furnished-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No furnished options found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=furnished&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Furnished Option', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

	public function custom_fields_management_key_date_type_setting() {
	?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				&nbsp;
			</th>
			<td class="forminp forminp-button">
				<a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
				<a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=management-key-date-type&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Management Date Type', 'propertyhive' )); ?></a>
			</td>
		</tr>
		<?php foreach( array ('property_management' =>  __( 'Property Management', 'propertyhive' ), 'tenancy_management' => __( 'Tenancy Management', 'propertyhive' ) ) as $type => $title): ?>
		<tr valign="top">
			<th scope="row" class="titledesc no-auto"><?php echo esc_html(__( $title, 'propertyhive' )); ?></th>
			<td class="forminp no-auto">
				<table class="ph_customfields widefat" cellspacing="0">
					<thead>
						<tr>
							<th style="width:1px;">&nbsp;</th>
							<th style="width:40%;"><?php echo esc_html(__( 'Description', 'propertyhive' )); ?></th>
							<th><?php echo esc_html(__( 'Recurrence', 'propertyhive' )); ?></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
				<?php
					$args = array(
						'hide_empty' => false,
						'parent' => 0
					);
					$terms = get_terms( 'management_key_date_type', $args );

					if ( !empty( $terms ) && !is_wp_error( $terms ) )
					{
						$recurrence_rules = get_option( 'propertyhive_key_date_type', array() );
						$recurrence_rules = is_array( $recurrence_rules ) ? $recurrence_rules : array();

						foreach ($terms as $term)
						{
							$recurrence_type = isset($recurrence_rules[$term->term_id]) ? $recurrence_rules[$term->term_id]['recurrence_type'] : '';

							if ( $recurrence_type !== $type)
							{
								continue;
							}


							$recurrence_rule = isset($recurrence_rules[$term->term_id]) ? $recurrence_rules[$term->term_id]['recurrence_rule'] : '';
							$recurrence = array('FREQ' => '', 'INTERVAL' => '');
							foreach (explode(';', $recurrence_rule) as $key_value_pair) {
								list($key, $value) = explode('=', $key_value_pair);
								$recurrence[$key] = $value;
							}

							$frequency = '';
							if ($recurrence['INTERVAL'] <= 1)
							{
								$frequencies = array(
									'ONCE' => __( 'Once', 'propertyhive' ),
									'DAILY' => __( 'Daily', 'propertyhive' ),
									'WEEKLY' => __( 'Weekly', 'propertyhive' ),
									'MONTHLY' => __( 'Monthly', 'propertyhive' ),
									'YEARLY' => __( 'Annually', 'propertyhive' ),
								);

								$frequency = $frequencies[$recurrence['FREQ']];
							}
							else
							{
								$interval = $recurrence['INTERVAL'];
								if (extension_loaded('intl'))
								{
									$formatter = new NumberFormatter(get_locale(), NumberFormatter::SPELLOUT);
									$interval = $formatter->format($recurrence['INTERVAL']);
								}

								$periods = array(
									'DAILY' => __( 'days', 'propertyhive' ),
									'WEEKLY' => __( 'weeks', 'propertyhive' ),
									'MONTHLY' => __( 'months', 'propertyhive' ),
									'YEARLY' => __( 'years', 'propertyhive' ),
								);


								$frequency = sprintf('Every %s %s', $interval, $periods[$recurrence['FREQ']]);
							}

							?>
							<tr>
								<td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
								<td><?php echo esc_html($term->name); ?></td>
								<td><?php echo esc_html($frequency); ?></td>
								<td class="settings">
									<a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=management-key-date-type&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
									<a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=management-key-date-type-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
								</td>
							</tr>
							<?php
						}
					}
					else
					{
						?>
						<tr>
							<td colspan="4"><?php echo esc_html(__( 'No management date types found', 'propertyhive' )); ?></td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php endforeach; ?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				&nbsp;
			</th>
			<td class="forminp forminp-button">
				<a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
				<a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=management-key-date-type&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Management Date Type', 'propertyhive' )); ?></a>
			</td>
		</tr>
		<?php
	}

    /**
     * Output list of marketing flag options
     *
     * @access public
     * @return void
     */
    public function custom_fields_marketing_flag_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=marketing-flag&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Marketing Flag', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Marketing Flags', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields sortable-custom-field widefat" data-taxonomy="marketing_flag" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Marketing Flag', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'marketing_flag', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr id="term-<?php echo esc_attr($term->term_id); ?>">
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=marketing-flag&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=marketing-flag-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No marketing flags found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=marketing-flag&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Marketing Flag', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Output list of property feature options
     *
     * @access public
     * @return void
     */
    public function custom_fields_property_feature_setting() {
        global $post;
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-feature&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Property Feature', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Property Features', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_customfields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px;"><input class="select_all" type="checkbox" style="margin: 2px 0 0 0;"></th>
                            <th class="id" style="width:45px;"><?php echo esc_html(__( 'ID', 'propertyhive' )); ?></th>
                            <th class="type"><?php echo esc_html(__( 'Property Feature', 'propertyhive' )); ?></th>
                            <th class="assigned_count"><?php echo esc_html(__( $this::LINKED_POSTS_COLUMN_HEADING, 'propertyhive' )); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'property_feature', $args );
                        
                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            { 
                        ?>
                        <tr>
                            <td class="cb"><input type="checkbox" name="term_id[]" value="<?php echo esc_attr($term->term_id); ?>"></td>
                            <td class="id"><?php echo esc_html($term->term_id); ?></td>
                            <td class="type"><?php echo esc_html($term->name); ?></td>
                            <td class="assigned_count"><?php echo esc_html($term->count); ?></td>
                            <td class="settings">
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-feature&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Edit', 'propertyhive' )); ?></a>
                                <a class="button" href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-feature-delete&id=' . $term->term_id )); ?>"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <tr>
                            <td colspan="5"><?php echo esc_html(__( 'No property features found', 'propertyhive' )); ?></td>
                        </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete" disabled><?php echo esc_html(__( 'Delete Selected', 'propertyhive' )); ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=customfields&section=property-feature&id=' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Property Feature', 'propertyhive' )); ?></a>
            </td>
        </tr>
    <?php
    }

    /**
     * Show availability add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_availability_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
        
        $taxonomy = 'availability';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $departments = ph_get_departments();

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Availability Option' : 'Edit Availability' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_availability_settings' ),
            
            array(
                'title' => __( 'Availability', 'propertyhive' ),
                'id'        => 'availability_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

        );

        $availability_departments = get_option( 'propertyhive_availability_departments', array() );

        $i = 0;
        foreach ( $departments as $key => $value )
        {
            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
            {
                $args[] = array(
                    'title'     => __( 'Applies To', 'propertyhive' ),
                    'name'      => 'department[' . $key . ']',
                    'id'        => 'department_' . $key,
                    //'default'   => $term_name,
                    'value'     => ( !isset($availability_departments[$current_id]) || in_array($key, $availability_departments[$current_id]) ? 'yes' : '' ),
                    'type'      => 'checkbox',
                    'desc_tip'  =>  false,
                    'desc'      =>  $value,
                    'checkboxgroup' => ( $i == 0 ? 'start' : ( $i == count($departments)-1 ? 'end' : '' ) ),
                );

                ++$i;
            }
        }

        $args[] = array(
            'type'      => 'hidden',
            'id'        => 'taxonomy',
            'default'     => $taxonomy
        );
            
        $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_availability_settings' );
        
        return apply_filters( 'propertyhive_custom_field_availability_settings', $args );
    }

    /**
     * Show residential property type add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_property_type_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
        
        $taxonomy = 'property_type';
        $term_name = '';
        $term_parent = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
            $term_parent = $term->parent;
        }
        
        $existing_terms = array('' => '(' . __ ( 'no parent', 'propertyhive') . ')');
        
        $args = array(
            'hide_empty' => false,
            'parent' => 0,
            'exclude' => array($current_id)
        );
        $terms = get_terms( 'property_type', $args );
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $existing_terms[$term->term_id] = $term->name;
            }
        }
        
        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Property Type' : 'Edit Property Type' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_property_type_settings' ),
            
            array(
                'title' => __( 'Property Type', 'propertyhive' ),
                'id'        => 'property_type_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Parent', 'propertyhive' ),
                'id'        => 'parent_property_type_id',
                'default'   => $term_parent,
                'options'   => $existing_terms,
                'type'      => 'select',
                'desc_tip'  =>  false,
                'desc'      => ''
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_property_type_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_property_type_settings', $args );
    }

    /**
     * Show commercial property type add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_commercial_property_type_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
        
        $taxonomy = 'commercial_property_type';
        $term_name = '';
        $term_parent = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
            $term_parent = $term->parent;
        }
        
        $existing_terms = array('' => '(' . __ ( 'no parent', 'propertyhive') . ')');
        
        $args = array(
            'hide_empty' => false,
            'parent' => 0,
            'exclude' => array($current_id)
        );
        $terms = get_terms( 'commercial_property_type', $args );
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $existing_terms[$term->term_id] = $term->name;
            }
        }
        
        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Property Type' : 'Edit Property Type' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_commercial_property_type_settings' ),
            
            array(
                'title' => __( 'Property Type', 'propertyhive' ),
                'id'        => 'commercial_property_type_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Parent', 'propertyhive' ),
                'id'        => 'parent_commercial_property_type_id',
                'default'   => $term_parent,
                'options'   => $existing_terms,
                'type'      => 'select',
                'desc_tip'  =>  false,
                'desc'      => ''
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_commercial_property_type_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_commercial_property_type_settings', $args );
    }

    /**
     * Show location add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_location_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
        
        $taxonomy = 'location';
        $term_name = '';
        $term_parent = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
            $term_parent = $term->parent;
        }
        
        $existing_terms = array('' => '(' . __ ( 'no parent', 'propertyhive') . ')');
        
        $args = array(
            'hide_empty' => false,
            'parent' => 0,
            'exclude' => array($current_id)
        );
        $terms = get_terms( $taxonomy, $args );
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $existing_terms[$term->term_id] = '- '.$term->name;
                
                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id,
                    'exclude' => array($current_id)
                );
                $terms = get_terms( $taxonomy, $args );
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $existing_terms[$term->term_id] = '- - '.$term->name;
                    }
                }
            }
        }
        
        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Location' : 'Edit Location' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_location_settings' ),
            
            array(
                'title' => __( 'Location', 'propertyhive' ),
                'id'        => 'location_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Parent', 'propertyhive' ),
                'id'        => 'parent_location_id',
                'default'   => $term_parent,
                'options'   => $existing_terms,
                'type'      => 'select',
                'desc_tip'  =>  false,
                'desc'      => ''
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_location_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_location_settings', $args );
    }
    
    /**
     * Show parking add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_parking_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
        
        $taxonomy = 'parking';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Parking Option' : 'Edit Parking' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_parking_settings' ),
            
            array(
                'title' => __( 'Parking', 'propertyhive' ),
                'id'        => 'parking_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_parking_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_parking_settings', $args );
    }
    
    /**
     * Show outside space add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_outside_space_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
        
        $taxonomy = 'outside_space';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Outside Space' : 'Edit Outside Space' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_outside_space_settings' ),
            
            array(
                'title' => __( 'Outside Space', 'propertyhive' ),
                'id'        => 'outside_space_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_outside_space_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_outside_space_settings', $args );
    }

    /**
     * Show custom field delete options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_delete($current_id, $taxonomy, $taxonomy_name)
    {
        global $save_button_text;
        
        $save_button_text = __( 'Delete', 'propertyhive' );
        
        //$taxonomy = 'outside_space';
        //$taxonomy_name = __( 'Outside Space', 'propertyhive' );
        
        if ( isset($_POST['confirm_removal']) && $_POST['confirm_removal'] == 1 )
        {
            // A term has just been deleted
            global $hide_save_button, $show_cancel_button, $cancel_button_href;
            
            $hide_save_button = TRUE;
            $show_cancel_button = TRUE;
            $cancel_button_href = admin_url( 'admin.php?page=ph-settings&tab=customfields&section=' . str_replace("_", "-", $taxonomy) );
            
            $args = array();
                    
            $args[] = array( 'title' => __( 'Successfully Deleted', 'propertyhive' ) . ' ' . $taxonomy_name, 'type' => 'title', 'desc' => '', 'id' => 'custom_field_' . $taxonomy . '_delete' );
            
            $args[] = array(
                'title'     => __( 'Term Deleted', 'propertyhive' ),
                'id'        => '',
                'html'      => $taxonomy_name . __(' deleted successfully', 'propertyhive' ) . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=customfields&section=' . str_replace("_", "-", $taxonomy) ) . '">' . __( 'Go Back', 'propertyhive' ) . '</a>',
                'type'      => 'html',
                'desc_tip'  =>  false,
            );
            
            $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_' . $taxonomy . '_delete' );
        }
        else
        {
            $term_name = '';
            if ($current_id == '')
            {
                die("ID not passed");
            }
            else
            {
                $term_ids = explode("-", $current_id);

                $args = array();

                foreach ( $term_ids as $current_id )
                {
                    $term = get_term( $current_id, $taxonomy );
                    
                    if ( is_null($term) || is_wp_error($term) )
                    {
                        die("Invalid term trying to be deleted");
                    }
                    else
                    {
                        $term_name = $term->name;
                        
                        $args[] = array( 'title' => __( 'Delete', 'propertyhive' ) . ' ' . $taxonomy_name . ': ' . $term_name, 'type' => 'title', 'desc' => '', 'id' => 'custom_field_' . $taxonomy . '_' . $current_id . '_delete' );
                        
                        // Get number of properties assigned to this term
                        $query_args = array(
                            'post_type' => 'property',
                            'nopaging' => true,
                            'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
                            'tax_query' => array(
                                array(
                                    'taxonomy' => $taxonomy,
                                    'field'    => 'id',
                                    'terms'    => $current_id,
                                ),
                            ),
                        );
                        $property_query = new WP_Query( $query_args );
                        
                        $num_properties = $property_query->found_posts;

                        wp_reset_postdata();
                        
                        // Get number of applicants assigned to this term
                        $num_applicants = 0;
                        if ( $taxonomy == 'property_type' || $taxonomy == 'commercial_property_type' ||  $taxonomy == 'location' )
                        {
                            $query_args = array(
                                'post_type' => 'contact',
                                'nopaging' => true,
                                'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
                                'meta_query' => array(
                                    array(
                                        'key' => '_contact_types',
                                        'value' => 'applicant',
                                        'compare' => 'LIKE'
                                    ),
                                ),
                            );
                            $applicant_query = new WP_Query( $query_args );

                            if ( $applicant_query->have_posts() )
                            {
                                while ( $applicant_query->have_posts() )
                                {
                                    $applicant_query->the_post();

                                    $applicant_has_taxonomy = false;

                                    $num_applicant_profiles = get_post_meta( get_the_ID(), '_applicant_profiles', TRUE );
                                    if ( $num_applicant_profiles == '' )
                                    {
                                        $num_applicant_profiles = 0;
                                    }

                                    if ( $num_applicant_profiles > 0 )
                                    {
                                        for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                                        {
                                            $applicant_profile = get_post_meta( get_the_ID(), '_applicant_profile_' . $i, TRUE );

                                            if ( isset($applicant_profile[$taxonomy.'s']) && is_array($applicant_profile[$taxonomy.'s']) && !empty($applicant_profile[$taxonomy.'s']) )
                                            {
                                                if (in_array($current_id, $applicant_profile[$taxonomy.'s']))
                                                {
                                                    $applicant_has_taxonomy = true;
                                                }
                                            }
                                        }
                                    }

                                    if ( $applicant_has_taxonomy )
                                    {
                                        ++$num_applicants;
                                    }
                                }
                            }

                            wp_reset_postdata();
                        }

	                    // Get number of key dates assigned to this term
	                    $num_key_dates= 0;
	                    if ( $taxonomy == 'management_key_date_type' )
	                    {
		                    $query_args = array(
			                    'post_type' => 'key_date',
			                    'nopaging' => true,
			                    'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
			                    'tax_query' => array(
				                    array(
					                    'taxonomy' => $taxonomy,
					                    'field'    => 'id',
					                    'terms'    => $current_id,
				                    ),
			                    ),
		                    );
		                    $key_date_query = new WP_Query( $query_args );

		                    $num_key_dates = $key_date_query->found_posts;

		                    wp_reset_postdata();
	                    }

                        if ($num_properties > 0 || $num_applicants > 0 || $num_key_dates > 0 )
                        {
                            $alternative_terms = array();
                            
                            $alternative_terms['none'] = '-- ' . __( 'Don\'t Reassign', 'propertyhive' ) . ' --';
                            
                            $term_args = array(
                                'hide_empty' => false,
                                'exclude' => $term_ids,
                                'parent' => 0
                            );
                            $terms = get_terms( $taxonomy, $term_args );
                            
                            if ( !empty( $terms ) && !is_wp_error( $terms ) )
                            {
                                foreach ($terms as $term)
                                {
                                    $alternative_terms[$term->term_id] = $term->name;

                                    $term_args = array(
                                        'hide_empty' => false,
                                        'exclude' => $term_ids,
                                        'parent' => $term->term_id
                                    );
                                    $subterms = get_terms( $taxonomy, $term_args );
                                    
                                    if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                    {
                                        foreach ($subterms as $term)
                                        {
                                            $alternative_terms[$term->term_id] = '- ' . $term->name;

                                            $term_args = array(
                                                'hide_empty' => false,
                                                'exclude' => $term_ids,
                                                'parent' => $term->term_id
                                            );
                                            $subsubterms = get_terms( $taxonomy, $term_args );
                                            
                                            if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                            {
                                                foreach ($subsubterms as $term)
                                                {
                                                    $alternative_terms[$term->term_id] = '- - ' . $term->name;
                                                }
                                            }
                                        }
                                    }
                                }
                            } 
                            
                            // There are properties assigned to this term
                            $args[] = array(
                                'title' => __( 'Re-assign to', 'propertyhive' ),
                                'id'        => 'reassign_to_' . $current_id,
                                'default'   => '',
                                'options'   => $alternative_terms,
                                'type'      => 'select',
                                'desc_tip'  =>  false,
                                'desc'      => __( 'There are properties, applicants or management dates that have this term assigned to them. Which, if any, term should they be reassigned to?' , 'propertyhive' )
                            );
                        }

                        $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_' . $taxonomy . '_' . $current_id . '_delete' );
                    }
                }

                $args[] = array( 'title' => __( 'Confirm Removal', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_confirm_delete' );

                $args[] = array(
                    'title' => __( 'Confirm Removal?', 'propertyhive' ),
                    'id'        => 'confirm_removal',
                    'type'      => 'checkbox',
                    'desc_tip'  =>  false,
                );

                $args[] = array(
                    'type'      => 'hidden',
                    'id'        => 'taxonomy',
                    'default'     => $taxonomy
                );

                $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_confirm_delete' );
            }
        }

        return apply_filters( 'propertyhive_custom_field_' . $taxonomy . '_delete', $args );
    }

    /**
     * Show price qualifier add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_price_qualifier_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'price_qualifier';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Price Qualifier' : 'Edit Price Qualifier' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_price_qualifier_settings' ),
            
            array(
                'title' => __( 'Price Qualifier', 'propertyhive' ),
                'id'        => 'price_qualifier_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_price_qualifier_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_price_qualifier_settings', $args );
    }

    /**
     * Show sale by add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_sale_by_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'sale_by';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Sale By' : 'Edit Sale By' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_sale_by_settings' ),
            
            array(
                'title' => __( 'Sale By', 'propertyhive' ),
                'id'        => 'sale_by_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_sale_by_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_sale_by_settings', $args );
    }

    /**
     * Show tenure add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_tenure_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'tenure';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Tenure' : 'Edit Tenure' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_tenure_settings' ),
            
            array(
                'title' => __( 'Tenure', 'propertyhive' ),
                'id'        => 'tenure_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_tenure_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_tenure_settings', $args );
    }

    /**
     * Show commercial tenure add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_commercial_tenure_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'commercial_tenure';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Tenure' : 'Edit Tenure' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_commercial_tenure_settings' ),
            
            array(
                'title' => __( 'Tenure', 'propertyhive' ),
                'id'        => 'commercial_tenure_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_commercial_tenure_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_commercial_tenure_settings', $args );
    }

    /**
     * Show furnished add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_furnished_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'furnished';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Furnished' : 'Edit Furnished' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_furnished_settings' ),
            
            array(
                'title' => __( 'Furnished', 'propertyhive' ),
                'id'        => 'furnished_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_furnished_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_furnished_settings', $args );
    }

	/**
	 * Show key date add/edit options
	 *
	 * @return string
	 */
	public function get_custom_fields_management_key_date_type_setting()
	{
		$current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );

		$taxonomy = 'management_key_date_type';
		$term_name = '';
		if ($current_id != '')
		{
			$term = get_term( $current_id, $taxonomy );
			$term_name = $term->name;
		}

		$recurrence = get_option( 'propertyhive_key_date_type', array() );
		if ( ! is_array($recurrence) )
		{
			$recurrence = array();
		}
		$recurrence_rule = isset($recurrence[$current_id]) ? $recurrence[$current_id]['recurrence_rule'] : '';
		$recurrence_type = isset($recurrence[$current_id]) ? $recurrence[$current_id]['recurrence_type'] : '';

		$recurrence = array(
			'FREQ' => 'ONCE',
			'INTERVAL' => '1',
        );

        if ( !empty($recurrence_rule) )
        {
            foreach (explode(';', $recurrence_rule) as $key_value_pair){
                list($key, $value) = explode('=', $key_value_pair);
                $recurrence[$key] = $value;
            }
        }

		$interval_disabled = $recurrence['FREQ'] == 'ONCE' ? array('disabled' => 'disabled') : array();

		$args = array(

			array( 'title' => __( ( $current_id == '' ? 'Add New Management Date Type' : 'Edit Management Date Type' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_management_key_date_type_settings' ),

			array(
				'title' => __( 'Description', 'propertyhive' ),
				'id'        => 'management_key_date_type_name',
				'default'   => $term_name,
				'type'      => 'text',
				'desc_tip'  =>  false,
			),

			array(
				'title' => __( 'Type', 'propertyhive' ),
				'id'        => 'management_key_date_type_recurrence_type',
				'default'   => $recurrence_type,
				'type'      => 'select',
				'desc_tip'  =>  false,
				'options' => array(
					'tenancy_management' => __( 'Tenancy Management', 'propertyhive' ),
					'property_management' => __( 'Property Management', 'propertyhive' ),
				),
			),

			array(
				'title' => __( 'Frequency', 'propertyhive' ),
				'id'        => 'management_key_date_type_recurrence_freq',
				'default'   => $recurrence['FREQ'],
				'type'      => 'select',
				'desc_tip'  =>  false,
				'options' => array(
					'ONCE' => __( 'Once', 'propertyhive' ),
					'DAILY' => __( 'Daily', 'propertyhive' ),
					'WEEKLY' => __( 'Weekly', 'propertyhive' ),
					'MONTHLY' => __( 'Monthly', 'propertyhive' ),
					'YEARLY' => __( 'Yearly', 'propertyhive' ),
				),
				'custom_attributes' => array('onchange' => "
					jQuery('#management_key_date_type_recurrence_interval').prop( 'disabled', this.value == 'ONCE');
				"),
			),

			array(
				'title' => __( 'Interval', 'propertyhive' ),
				'id'        => 'management_key_date_type_recurrence_interval',
				'default'   => $recurrence['INTERVAL'],
				'type'      => 'number',
				'custom_attributes' => array_merge( array( 'min' => '1', 'required' => 'true'), $interval_disabled ),
				'desc'  => '<p>When setting a key date to Complete, these settings are used to calculate the date the next one should occur.<br>For example, if a key date should happen every 3 months, set Frequency to "Monthly" and Interval to "3".</p>',
			),

			array(
				'type'      => 'hidden',
				'id'        => 'taxonomy',
				'default'     => $taxonomy
			),

			array( 'type' => 'sectionend', 'id' => 'custom_field_management_key_date_type_settings' )

		);

		return apply_filters( 'propertyhive_custom_field_management_key_date_type_setting', $args );
	}

    /**
     * Show marketing flag add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_marketing_flag_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'marketing_flag';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Marketing Flag' : 'Edit Marketing Flag' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_marketing_flag_settings' ),
            
            array(
                'title' => __( 'Marketing Flag', 'propertyhive' ),
                'id'        => 'marketing_flag_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_marketing_flag_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_marketing_flag_settings', $args );
    }

    /**
     * Show property feature add/edit options
     *
     * @access public
     * @return string
     */
    public function get_custom_fields_property_feature_setting()
    {
        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_title( $_REQUEST['id'] );
        
        $taxonomy = 'property_feature';
        $term_name = '';
        if ($current_id != '')
        {
            $term = get_term( $current_id, $taxonomy );
            $term_name = $term->name;
        }

        $args = array(

            array( 'title' => __( ( $current_id == '' ? 'Add New Property Feature' : 'Edit Property Feature' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_property_feature_settings' ),
            
            array(
                'title' => __( 'Property Feature', 'propertyhive' ),
                'id'        => 'property_feature_name',
                'default'   => $term_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'type'      => 'hidden',
                'id'        => 'taxonomy',
                'default'     => $taxonomy
            ),
            
            array( 'type' => 'sectionend', 'id' => 'custom_field_property_feature_settings' )
            
        );
        
        return apply_filters( 'propertyhive_custom_field_property_feature_settings', $args );
    }

    /**
     * Save settings
     */
    public function save() {
        global $current_section, $post;

        if ( $current_section != '' ) 
        {
            switch ($current_section)
            {
                case "addadditionalfield": 
                case "editadditionalfield": 
                {
                    $current_settings = get_option( 'propertyhive_template_assistant', array() );

                    $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                    $existing_custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

                    if ( $current_section == 'editadditionalfield' && $current_id != 'default' && !isset($existing_custom_fields[$current_id]) )
                    {
                        die("Trying to edit a non-existant custom field. Please go back and try again");
                    }

                    $field_name = trim( ( ( isset($_POST['field_name']) ) ? sanitize_title( $_POST['field_name'] ) : '' ) );

                    if ( $field_name == '' )
                    {
                        $field_name = str_replace("-", "_", sanitize_title( $_POST['field_label'] ) );
                    }

                    $field_name = '_' . ltrim( $field_name, '_' );

                    if ( $current_section == 'addadditionalfield' )
                    {
                        $existing_custom_fields[] = array(
                            'field_label' => sanitize_text_field(wp_unslash($_POST['field_label'])),
                            'field_name' => $field_name,
                            'field_type' => ( ( isset($_POST['field_type']) && $_POST['field_type'] != '' ) ? sanitize_text_field($_POST['field_type']) : 'text' ),
                            'dropdown_options' => ( ( isset($_POST['field_type']) && ( $_POST['field_type'] == 'select' || $_POST['field_type'] == 'multiselect' ) && isset($_POST['dropdown_options']) ) ? $_POST['dropdown_options'] : '' ),
                            'meta_box' => sanitize_text_field($_POST['meta_box']),
                            'display_on_website' => ( ( isset($_POST['display_on_website']) ) ? sanitize_text_field($_POST['display_on_website']) : '' ),
                            'display_on_applicant_requirements' => ( ( isset($_POST['display_on_applicant_requirements']) ) ? sanitize_text_field($_POST['display_on_applicant_requirements']) : '' ),
                            'exact_match' => ( ( isset($_POST['exact_match']) ) ? sanitize_text_field($_POST['exact_match']) : '' ),
                            'display_on_user_details' => ( ( isset($_POST['display_on_user_details']) ) ? sanitize_text_field($_POST['display_on_user_details']) : '' ),
                            'admin_list' => ( ( isset($_POST['admin_list']) ) ? sanitize_text_field($_POST['admin_list']) : '' ),
                            'admin_list_sortable' => ( ( isset($_POST['admin_list_sortable']) ) ? sanitize_text_field($_POST['admin_list_sortable']) : '' ),
                        );
                    }
                    else
                    {
                        $existing_custom_fields[$current_id] = array(
                            'field_label' => sanitize_text_field(wp_unslash($_POST['field_label'])),
                            'field_name' => $field_name,
                            'field_type' => ( ( isset($_POST['field_type']) && $_POST['field_type'] != '' ) ? sanitize_text_field($_POST['field_type']) : 'text' ),
                            'dropdown_options' => ( ( isset($_POST['field_type']) && ( $_POST['field_type'] == 'select' || $_POST['field_type'] == 'multiselect' ) && isset($_POST['dropdown_options']) ) ? $_POST['dropdown_options'] : '' ),
                            'meta_box' => sanitize_text_field($_POST['meta_box']),
                            'display_on_website' => ( ( isset($_POST['display_on_website']) ) ? sanitize_text_field($_POST['display_on_website']) : '' ),
                            'display_on_applicant_requirements' => ( ( isset($_POST['display_on_applicant_requirements']) ) ? sanitize_text_field($_POST['display_on_applicant_requirements']) : '' ),
                            'exact_match' => ( ( isset($_POST['exact_match']) ) ? sanitize_text_field($_POST['exact_match']) : '' ),
                            'display_on_user_details' => ( ( isset($_POST['display_on_user_details']) ) ? sanitize_text_field($_POST['display_on_user_details']) : '' ),
                            'admin_list' => ( ( isset($_POST['admin_list']) ) ? sanitize_text_field($_POST['admin_list']) : '' ),
                            'admin_list_sortable' => ( ( isset($_POST['admin_list_sortable']) ) ? sanitize_text_field($_POST['admin_list_sortable']) : '' ),
                        );
                    }

                    $current_settings['custom_fields'] = $existing_custom_fields;

                    // see if this custom field in used in search forms and amend the type accordingly
                    if ( $current_section != 'addadditionalfield' )
                    {
                        if ( isset($current_settings['search_forms']) && !empty($current_settings['search_forms']) )
                        {
                            foreach ( $current_settings['search_forms'] as $search_form_id => $search_form )
                            {
                                // Active fields
                                if ( isset($search_form['active_fields']) && !empty($search_form['active_fields']) )
                                {
                                    foreach ( $search_form['active_fields'] as $field_id => $field_data )
                                    {
                                        if ( $field_name == $field_id )
                                        {
                                            // we found this field. Set type
                                            $current_settings['search_forms'][$search_form_id]['active_fields'][$field_id]['type'] = ( ( isset($_POST['field_type']) && $_POST['field_type'] != '' ) ? sanitize_text_field($_POST['field_type']) : 'text' );
                                        }
                                    }
                                }

                                // Inactive fields
                                if ( isset($search_form['inactive_fields']) && !empty($search_form['inactive_fields']) )
                                {
                                    foreach ( $search_form['inactive_fields'] as $field_id => $field_data )
                                    {
                                        if ( $field_name == $field_id )
                                        {
                                            // we found this field. Set type
                                            $current_settings['search_forms'][$search_form_id]['inactive_fields'][$field_id]['type'] = ( ( isset($_POST['field_type']) && $_POST['field_type'] != '' ) ? sanitize_text_field($_POST['field_type']) : 'text' );
                                        }
                                    }
                                }
                            }
                        }
                    }

                    update_option( 'propertyhive_template_assistant', $current_settings );

                    break; 
                }
                default:
                {
                    if (isset($_REQUEST['id'])) // we're either adding or editing
                    {
                        $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_text_field($_REQUEST['id']);
                        
                        switch ($current_section)
                        {
                            // With heirarchy
                            case "property-type":
                            case "commercial-property-type":
                            case "location":
                            {
                                // TODO: Validate (check for blank fields)
                                
                                if ($current_id == '')
                                {
                                    // Adding new term
                                    
                                    // TODO: Check term doesn't exist already
                                    
                                    wp_insert_term(
                                        ph_clean($_POST[ph_clean($_POST['taxonomy']) . '_name']), // the term 
                                        ph_clean($_POST['taxonomy']), // the taxonomy
                                        array(
                                            'parent' => $_POST['parent_' . ph_clean($_POST['taxonomy']) . '_id']
                                        )
                                    );
                                    
                                    // TODO: Check for errors returned from wp_insert_term()
                                }
                                else
                                {
                                    // Editing term
                                    wp_update_term($current_id, ph_clean($_POST['taxonomy']), array(
                                        'name' => ph_clean($_POST[ph_clean($_POST['taxonomy']).'_name']),
                                         'parent' => ph_clean($_POST['parent_' . $_POST['taxonomy'] . '_id'])
                                    ));
                                    
                                    // TODO: Check for errors returned from wp_update_term()
                                }
                                break;
                            }
                            // Without heirarchy
                            case "availability":
                            case "outside-space":
                            case "parking":
                            case "price-qualifier":
                            case "sale-by":
                            case "tenure":
                            case "commercial-tenure":
                            case "furnished":
                            case "management-key-date-type":
                            case "marketing-flag":
                            case "property-feature":
                            {
                                // TODO: Validate (check for blank fields)
                                
                                if ($current_id == '')
                                {
                                    // Adding new term
                                    
                                    // TODO: Check term doesn't exist already
                                    
                                    $term = wp_insert_term(
                                        ph_clean($_POST[ph_clean($_POST['taxonomy']) . '_name']), // the term 
                                        $_POST['taxonomy'] // the taxonomy
                                    );
                                    
                                    // TODO: Check for errors returned from wp_insert_term()

                                    if ( ! is_wp_error( $term ) )
                                    {
                                        $current_id = isset( $term['term_id'] ) ? $term['term_id'] : 0;
                                    }
                                }
                                else
                                {
                                    // Editing term
                                    wp_update_term($current_id, ph_clean($_POST['taxonomy']), array(
                                        'name' => ph_clean($_POST[ph_clean($_POST['taxonomy']) . '_name'])
                                    ));
                                    
                                    // TODO: Check for errors returned from wp_update_term()
                                }

                                if ( $current_section == 'availability' )
                                {
                                    $availability_departments = get_option( 'propertyhive_availability_departments', array() );
                                    if ( !is_array($availability_departments) ) { $availability_departments = array(); }

                                    $departments = ph_get_departments();

                                    $availability_departments[$current_id] = array();
                                    foreach ( $departments as $key => $value )
                                    {
                                        if ( isset($_POST['department'][$key]) && $_POST['department'][$key] == '1' )
                                        {
                                            $availability_departments[$current_id][] = $key;
                                        }
                                    }

                                    update_option( 'propertyhive_availability_departments', $availability_departments );
                                }

        	                    if ( $current_section == 'management-key-date-type' )
        	                    {
        		                    $options = get_option( 'propertyhive_key_date_type', array() );
        		                    if ( !is_array($options) ) { $options = array(); }

        		                    $recurrence = array();
        		                    if (isset($_POST['management_key_date_type_recurrence_freq'])) {
        			                    $recurrence[] = 'FREQ=' . $_POST['management_key_date_type_recurrence_freq'];
        		                    }
        		                    if (isset($_POST['management_key_date_type_recurrence_interval'])) {
        			                    $recurrence[] = 'INTERVAL=' . $_POST['management_key_date_type_recurrence_interval'];
        		                    }

        	                        $options[$current_id]['recurrence_rule'] = join(';', $recurrence);
        		                    $options[$current_id]['recurrence_type'] = $_POST['management_key_date_type_recurrence_type'];
        		                    update_option( 'propertyhive_key_date_type', $options );
        	                    }

                                break;
                            }
                            case "availability-delete":
                            case "property-type-delete":
                            case "commercial-property-type-delete":
                            case "location-delete":
                            case "parking-delete":
                            case "outside-space-delete":
                            case "price-qualifier-delete":
                            case "sale-by-delete":
                            case "tenure-delete":
                            case "commercial-tenure-delete":
                            case "furnished-delete":
                            case "management-key-date-type-delete":
                            case "marketing-flag-delete":
                            case "property-feature-delete":
                            {
                                if ( isset($_POST['confirm_removal']) && $_POST['confirm_removal'] == '1' )
                                {
                                    $term_ids = explode("-", $current_id);

                                    foreach ( $term_ids as $current_id )
                                    {
                                        // Update properties that have this taxonomy term set
                                        $query_args = array(
                                            'post_type' => 'property',
                                            'nopaging' => true,
                                            'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
                                            'tax_query' => array(
                                                array(
                                                    'taxonomy' => $_POST['taxonomy'],
                                                    'field'    => 'id',
                                                    'terms'    => $current_id,
                                                ),
                                            ),
                                        );
                                        $property_query = new WP_Query( $query_args );
                                        
                                        if ( $property_query->have_posts() )
                                        {
                                            while ( $property_query->have_posts() )
                                            {
                                                $property_query->the_post();
                                                
                                                wp_remove_object_terms( $post->ID, $current_id, ph_clean($_POST['taxonomy']) );
                                                
                                                // Re-assign to another term
                                                if ( isset($_POST['reassign_to_' . $current_id]) && ! empty( $_POST['reassign_to_' . $current_id] ) && $_POST['reassign_to_' . $current_id] != 'none' )
                                                {
                                                    $new_id = $_POST['reassign_to_' . $current_id];
                                                    
                                                    wp_set_post_terms( $post->ID, $new_id, ph_clean($_POST['taxonomy']), TRUE );
                                                    
                                                    // TODO: Check for WP_ERROR
                                                }
                                            }
                                        }
                                        
                                        wp_reset_postdata();

                                        if ( $current_section == 'availability-delete' )
                                        {
                                            // Remove from propertyhive_availability_departments option
                                            $availability_departments = get_option( 'propertyhive_availability_departments', array() );

                                            if ( isset($availability_departments[$current_id]) )
                                            {
                                                unset($availability_departments[$current_id]);
                                                update_option( 'propertyhive_availability_departments', $availability_departments );
                                            }
                                        }

                                        if ( $_POST['taxonomy'] == 'property_type' || $_POST['taxonomy'] == 'commercial_property_type' || $_POST['taxonomy'] == 'location' )
                                        {
                                            $query_args = array(
                                                'post_type' => 'contact',
                                                'nopaging' => true,
                                                'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
                                                'meta_query' => array(
                                                    array(
                                                        'key' => '_contact_types',
                                                        'value' => 'applicant',
                                                        'compare' => 'LIKE'
                                                    ),
                                                ),
                                            );
                                            $applicant_query = new WP_Query( $query_args );

                                            if ( $applicant_query->have_posts() )
                                            {
                                                while ( $applicant_query->have_posts() )
                                                {
                                                    $applicant_query->the_post();
                                                
                                                    $num_applicant_profiles = get_post_meta( get_the_ID(), '_applicant_profiles', TRUE );
                                                    if ( $num_applicant_profiles == '' )
                                                    {
                                                        $num_applicant_profiles = 0;
                                                    }

                                                    if ( $num_applicant_profiles > 0 )
                                                    {
                                                        for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                                                        {
                                                            $applicant_profile = get_post_meta( get_the_ID(), '_applicant_profile_' . $i, TRUE );

                                                            if ( isset($applicant_profile[ph_clean($_POST['taxonomy']).'s']) && is_array($applicant_profile[ph_clean($_POST['taxonomy']).'s']) && !empty($applicant_profile[ph_clean($_POST['taxonomy']).'s']) )
                                                            {
                                                                if (in_array($current_id, $applicant_profile[ph_clean($_POST['taxonomy']).'s']))
                                                                {
                                                                    // This profile has this term set
                                                                    unset($applicant_profile[ph_clean($_POST['taxonomy']).'s'][$current_id]);

                                                                    if ( isset($_POST['reassign_to_' . $current_id]) && ! empty( $_POST['reassign_to_' . $current_id] ) && ph_clean($_POST['reassign_to_' . $current_id]) != 'none' )
                                                                    {
                                                                        $applicant_profile[ph_clean($_POST['taxonomy']).'s'][] = $_POST['reassign_to_' . $current_id];
                                                                        $applicant_profile[ph_clean($_POST['taxonomy']).'s'] = array_unique($applicant_profile[ph_clean($_POST['taxonomy']).'s']);
                                                                    }

                                                                    $applicant_profile[ph_clean($_POST['taxonomy']).'s'] = array_values($applicant_profile[ph_clean($_POST['taxonomy']).'s']);

                                                                    update_post_meta( get_the_ID(), '_applicant_profile_' . $i, $applicant_profile );
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        wp_reset_postdata();

                                        wp_delete_term( $current_id, ph_clean($_POST['taxonomy']) );
                                    }
                                }

                                break;
                            }
                            default:
                            {
                                $section_found = apply_filters( 'propertyhive_custom_fields_save_section', false, $current_section, $current_id );

                                if ( !($section_found) )
                                {
                                    echo 'UNKNOWN CUSTOM FIELD';
                                }
                            }
                        }
                    }
                    else
                    {
                        // Nothing to save. Should always be an id set when editing custom fields.
                        // Even blank ids dictate something is being added
                    }
                }
            }
        }
        else
        {
            // Nothing to save. Should always be a section when editing custom fields
        }
    }
}

endif;

return new PH_Settings_Custom_Fields();