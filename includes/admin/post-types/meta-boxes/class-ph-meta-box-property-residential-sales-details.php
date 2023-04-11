<?php
/**
 * Property Residential Sales Details
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Residential_Sales_Details
 */
class PH_Meta_Box_Property_Residential_Sales_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        // Currency / Price
        $ph_countries = new PH_Countries();

        $default_country = get_option( 'propertyhive_default_country', 'GB' );
        $countries = get_option( 'propertyhive_countries', array( $default_country ) );
        $currencies = array();
        foreach ( $countries as $country )
        {
            $country = $ph_countries->get_country( $country );

            if ( !isset($currencies[$country['currency_code']]) )
            {
                $currencies[$country['currency_code']] = $country['currency_symbol'];
            }
        }

        // Cater for when no currency selected or currencies have been updated in settings so existing currency doesn't exist
        $selected_currency = get_post_meta( $post->ID, '_currency', true );
        if ( $selected_currency == '' || !isset($currencies[$selected_currency]) )
        {
            $country = $ph_countries->get_country( $default_country );
            $selected_currency = $country['currency_code'];
        }

        echo '<p class="form-field price_field ">
        
            <label for="_price">' . __('Price', 'propertyhive') . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . $currencies[$selected_currency] . '</span>)' : '' ) . '</label>';
         
        if ( count($currencies) > 1 )
        {
            echo '<select id="_price_currency" name="_price_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_sybmol)
            {
                echo '<option value="' . $currency_code . '"' . ( ($currency_code == $selected_currency) ? ' selected' : '') . '>' . $currency_sybmol . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_price_currency" value="' . $selected_currency . '">';
        }

        echo '<input type="text" class="" name="_price" id="_price" value="' . ph_display_price_field( get_post_meta( $post->ID, '_price', true ) ) . '" placeholder="" style="width:15%;">
            
        </p>';
        
        // POA
        propertyhive_wp_checkbox( array( 
            'id' => '_sale_poa', 
            'label' => __( 'Price On Application', 'propertyhive' ), 
            'desc_tip' => false,
            'value' => get_post_meta( $post->ID, '_poa', true )
        ) );
        
        // Price Qualifier
        $options = array( '' => '' );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'price_qualifier', $args );
        
        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($post->ID, 'price_qualifier', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }
        
        $args = array( 
            'id' => 'price_qualifier_id', 
            'label' => __( 'Price Qualifier', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        propertyhive_wp_select( $args );
        
        // Sale By
        $options = array( '' => '' );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'sale_by', $args );
        
        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($post->ID, 'sale_by', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }
        
        $args = array( 
            'id' => 'sale_by_id', 
            'label' => __( 'Sale By', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        propertyhive_wp_select( $args );
        
        // Tenure
        $options = array( '' => '' );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'tenure', $args );
        
        $selected_value = '';
        $selected_name = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($post->ID, 'tenure', array("fields" => "all"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0]->term_id;
                $selected_name = $term_list[0]->name;
            }
        }
        
        $args = array( 
            'id' => 'tenure_id', 
            'label' => __( 'Tenure', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        propertyhive_wp_select( $args );

        echo '<div id="leasehold_information"';
        if ( !in_array( strtolower($selected_name), apply_filters('propertyhive_leasehold_tenure_names', array( 'leasehold', 'share of freehold' ) ) ) )
        { 
            echo ' style="display:none"'; 
        }
        echo '>';

        propertyhive_wp_text_input( array( 
            'id' => '_leasehold_years_remaining', 
            'label' => __( 'Lease Years Remaining', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );

        propertyhive_wp_text_input( array( 
            'id' => '_ground_rent', 
            'label' => __( 'Ground Rent Per Year (&pound;)', 'propertyhive' ), 
            'desc_tip' => false,
            //'class' => '',
            'value' => ph_display_price_field( get_post_meta( $post->ID, '_ground_rent', true ) ),
        ) );

        propertyhive_wp_text_input( array( 
            'id' => '_ground_rent_review_years', 
            'label' => __( 'Ground Rent Review Period (Years)', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );

        propertyhive_wp_text_input( array( 
            'id' => '_service_charge', 
            'label' => __( 'Service Charge Per Year (&pound;)', 'propertyhive' ), 
            'desc_tip' => false,
            //'class' => '',
            'value' => ph_display_price_field( get_post_meta( $post->ID, '_service_charge', true ) ),
        ) );

        propertyhive_wp_text_input( array( 
            'id' => '_service_charge_review_years', 
            'label' => __( 'Service Charge Review Period (Years)', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );

        propertyhive_wp_checkbox( array( 
            'id' => '_shared_ownership', 
            'label' => __( 'Shared Ownership', 'propertyhive' ), 
            'desc_tip' => false,
            'value' => get_post_meta( $post->ID, '_shared_ownership', true )
        ) );

        echo '<div id="shared_ownership_information"';
        if ( get_post_meta( $post->ID, '_shared_ownership', true ) != 'yes' ) { echo ' style="display:none"'; }
        echo '>';
        propertyhive_wp_text_input( array( 
            'id' => '_shared_ownership_percentage', 
            'label' => __( 'Shared Ownership Percentage', 'propertyhive' ) .  ' (%)', 
            'desc_tip' => false,
            'type' => 'number'
        ) );
        echo '</div>';

        echo '</div>';
        
        do_action('propertyhive_property_residential_sales_details_fields');
        
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        // Only save meta info if department is 'residential-sales'
        $department = get_post_meta($post_id, '_department', TRUE);
        
        if ( $department == 'residential-sales' || ph_get_custom_department_based_on( $department ) == 'residential-sales' )
        {
            update_post_meta( $post_id, '_currency', ph_clean($_POST['_price_currency']) );

            $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_price']));
            update_post_meta( $post_id, '_price', $price );
            
            // Store price in common currency (GBP) used for ordering
            $ph_countries = new PH_Countries();
            $ph_countries->update_property_price_actual( $post_id );

            update_post_meta( $post_id, '_poa', ( isset($_POST['_sale_poa']) ? ph_clean($_POST['_sale_poa']) : '' ) );
            
            if ( !empty($_POST['price_qualifier_id']) )
            {
                wp_set_post_terms( $post_id, (int)$_POST['price_qualifier_id'], 'price_qualifier' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'price_qualifier' );
            }
            
            if ( !empty($_POST['sale_by_id']) )
            {
                wp_set_post_terms( $post_id, (int)$_POST['sale_by_id'], 'sale_by' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'sale_by' );
            }
            
            if ( !empty($_POST['tenure_id']) )
            {
                wp_set_post_terms( $post_id, (int)$_POST['tenure_id'], 'tenure' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'tenure' );
            }

            update_post_meta( $post_id, '_leasehold_years_remaining', ( !empty($_POST['_leasehold_years_remaining']) ? (int)$_POST['_leasehold_years_remaining'] : '' ) );

            $ground_rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_ground_rent']));
            update_post_meta( $post_id, '_ground_rent', $ground_rent );

            $ground_rent_review_years = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_ground_rent_review_years']));
            update_post_meta( $post_id, '_ground_rent_review_years', $ground_rent_review_years );

            $service_charge = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_service_charge']));
            update_post_meta( $post_id, '_service_charge', $service_charge );

            $service_charge_review_years = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_service_charge_review_years']));
            update_post_meta( $post_id, '_service_charge_review_years', $service_charge_review_years );

            update_post_meta( $post_id, '_shared_ownership', ( isset($_POST['_shared_ownership']) ? ph_clean($_POST['_shared_ownership']) : '' ) );

            $shared_ownership_percentage = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_shared_ownership_percentage']));
            update_post_meta( $post_id, '_shared_ownership_percentage', $shared_ownership_percentage );

            do_action( 'propertyhive_save_property_residential_sales_details', $post_id );
        }
    }

}
