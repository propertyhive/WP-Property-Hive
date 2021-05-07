<?php
/**
 * Property Residential Lettings Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Residential_Lettings_Details
 */
class PH_Meta_Box_Property_Residential_Lettings_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        // Currency / Rent / Rent Frequency
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

        $rent_frequency = get_post_meta( $post->ID, '_rent_frequency', true );

        echo '<p class="form-field rent_field ">
        
            <label for="_rent">' . __('Rent', 'propertyhive') . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . $currencies[$selected_currency] . '</span>)' : '' ) . '</label>';
        
        if ( count($currencies) > 1 )
        {
            echo '<select id="_rent_currency" name="_rent_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_sybmol)
            {
                echo '<option value="' . $currency_code . '"' . ( ($currency_code == $selected_currency) ? ' selected' : '') . '>' . $currency_sybmol . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_rent_currency" value="' . $selected_currency . '">';
        }

        echo '<input type="text" class="" name="_rent" id="_rent" value="' . ph_display_price_field( get_post_meta( $post->ID, '_rent', true ) ) . '" placeholder="" style="width:20%;">
            
            <select id="_rent_frequency" name="_rent_frequency" class="select" style="width:auto">
                <option value="pd"' . ( ($rent_frequency == 'pd') ? ' selected' : '') . '>' . __('Per Day', 'propertyhive') . '</option>
                <option value="pppw"' . ( ($rent_frequency == 'pppw') ? ' selected' : '') . '>' . __('Per Person Per Week', 'propertyhive') . '</option>
                <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
                <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
                <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
                <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>
            </select>
            
        </p>';

        // POA
        propertyhive_wp_checkbox( array( 
            'id' => '_rent_poa', 
            'label' => __( 'Rent On Application', 'propertyhive' ), 
            'desc_tip' => false,
            'value' => get_post_meta( $post->ID, '_poa', true )
        ) );
        
        // Deposit
        propertyhive_wp_text_input( array( 
            'id' => '_deposit', 
            'label' => __( 'Deposit', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => '',
            'custom_attributes' => array(
                'style' => 'width:20%'
            ),
            'value' => ph_display_price_field( get_post_meta( $post->ID, '_deposit', true ) ),
        ) );
        
        // Furnished
        $options = array( '' => '' );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'furnished', $args );
        
        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($post->ID, 'furnished', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }
        
        $args = array( 
            'id' => 'furnished_id', 
            'label' => __( 'Furnishing', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        propertyhive_wp_select( $args );
        
        // Available Date
        propertyhive_wp_text_input( array( 
            'id' => '_available_date', 
            'label' => __( 'Available Date', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => 'short date-picker',
            'placeholder' => 'YYYY-MM-DD',
            'custom_attributes' => array(
                'maxlength' => 10,
                'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
            )
        ) );
        
        do_action('propertyhive_property_residential_lettings_details_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        // Only save meta info if department is 'residential-lettings'
        $department = get_post_meta($post_id, '_department', TRUE);
        
        if ( $department == 'residential-lettings' || ph_get_custom_department_based_on( $department ) == 'residential-lettings' )
        {
            update_post_meta( $post_id, '_currency', ph_clean($_POST['_rent_currency']) );

            $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_rent']));
            update_post_meta( $post_id, '_rent', $rent );
            update_post_meta( $post_id, '_rent_frequency', ph_clean($_POST['_rent_frequency']) );
            
            // Store price in common currency (GBP) and frequency (PCM) used for ordering
            $ph_countries = new PH_Countries();
            $ph_countries->update_property_price_actual( $post_id );

            update_post_meta( $post_id, '_poa', ( isset($_POST['_rent_poa']) ? ph_clean($_POST['_rent_poa']) : '' ) );
            
            update_post_meta( $post_id, '_deposit', preg_replace("/[^0-9.]/", '', ph_clean($_POST['_deposit'])) );
            update_post_meta( $post_id, '_available_date', ph_clean($_POST['_available_date']) );
            
            if ( !empty($_POST['furnished_id']) )
            {
                wp_set_post_terms( $post_id, (int)$_POST['furnished_id'], 'furnished' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'furnished' );
            }

            do_action( 'propertyhive_save_property_residential_lettings_details', $post_id );
        }
    }

}
