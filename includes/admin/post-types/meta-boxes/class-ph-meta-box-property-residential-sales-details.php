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
        
        // Price
        propertyhive_wp_text_input( array( 
            'id' => '_price', 
            'label' => __( 'Price', 'propertyhive' ) . ' (&pound;)', 
            'desc_tip' => false, 
            //'description' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'propertyhive' ), 
            'type' => 'text',
            'class' => '',
            'custom_attributes' => array(
                'style' => 'width:10%'
            )
        ) );
        
        // POA
        propertyhive_wp_checkbox( array( 
            'id' => '_poa', 
            'label' => __( 'Price On Application', 'propertyhive' ), 
            'desc_tip' => false,
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
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($post->ID, 'tenure', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
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
        
        if ($department == 'residential-sales')
        {
            $price = preg_replace("/[^0-9]/", '', $_POST['_price']);
            update_post_meta( $post_id, '_price', $price );
            
            // Store price used for ordering. Not used yet but could be if introducing currencies in the future.
            update_post_meta( $post_id, '_price_actual', $price );
            
            update_post_meta( $post_id, '_poa', ( isset($_POST['_poa']) ? $_POST['_poa'] : '' ) );
            
            if ( !empty($_POST['price_qualifier_id']) )
            {
                wp_set_post_terms( $post_id, $_POST['price_qualifier_id'], 'price_qualifier' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'price_qualifier' );
            }
            
            if ( !empty($_POST['sale_by_id']) )
            {
                wp_set_post_terms( $post_id, $_POST['sale_by_id'], 'sale_by' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'sale_by' );
            }
            
            if ( !empty($_POST['tenure_id']) )
            {
                wp_set_post_terms( $post_id, $_POST['tenure_id'], 'tenure' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'tenure' );
            }
            
            //update_post_meta( $post_id, 'price_qualifier_id', $_POST['price_qualifier_id'] );
            //update_post_meta( $post_id, 'sale_by_id', $_POST['sale_by_id'] );
            //update_post_meta( $post_id, 'tenure_id', $_POST['tenure_id'] );
        }
    }

}
