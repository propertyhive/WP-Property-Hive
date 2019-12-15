<?php
/**
 * Tenancy Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Details
 */
class PH_Meta_Box_Tenancy_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $length_units = get_post_meta( $post->ID, '_length_units', true );
        $lease_type = get_post_meta( $post->ID, '_lease_type', true );

        echo '<p class="form-field lease_term_type_field">
        
            <label for="_length">' . __('Lease Term and Type', 'propertyhive') . '</label>

            <input type="number" class="" name="_length" id="_length" value="' . get_post_meta( $post->ID, '_length', true ) . '" placeholder="" style="width:70px">
            
            <select id="_length_units" name="_length_units" class="select" style="width:auto">
                <option value="week"' . ( ($length_units == 'weeks') ? ' selected' : '') . '>' . __('Weeks', 'propertyhive') . '</option>
                <option value="month"' . ( ($length_units == 'months' || $length_units == '') ? ' selected' : '') . '>' . __('Months', 'propertyhive') . '</option>
            </select>

            <select id="_lease_type" name="_lease_type" class="select" style="width:auto">
                <option value="assured_shorthold"' . ( ($lease_type == 'assured_shorthold' || $lease_type == '') ? ' selected' : '') . '>' . __('Assured Shorthold', 'propertyhive') . '</option>
                <option value="assured"' . ( $lease_type == 'assured' ? ' selected' : '') . '>' . __('Assured', 'propertyhive') . '</option>
            </select>
            
        </p>';

        $args = array( 
            'id' => '_start_date', 
            'label' => __( 'Tenancy Start Date', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'date'
        );
        propertyhive_wp_text_input( $args );

        $args = array( 
            'id' => '_end_date', 
            'label' => __( 'Tenancy End Date', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'date'
        );
        propertyhive_wp_text_input( $args );

        $args = array( 
            'id' => '_review_date', 
            'label' => __( 'Review / Renewal Date', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'date'
        );
        propertyhive_wp_text_input( $args );

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

        echo '<input type="text" class="" name="_rent" id="_rent" value="' . get_post_meta( $post->ID, '_rent', true ) . '" placeholder="" style="width:70px">
            
            <select id="_rent_frequency" name="_rent_frequency" class="select" style="width:auto">
                <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
                <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
                <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
                <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>
            </select>
            
        </p>';

        $args = array( 
            'id' => '_management_type', 
            'label' => __( 'Management Type', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => array(
                'let_only' => 'Let Only',
                'fully_managed' => 'Fully Managed'
            ),
        );
        propertyhive_wp_select( $args );

        $management_fee_units = get_post_meta( $post->ID, '_management_fee_units', true );

        echo '<p class="form-field rent_field ">
        
            <label for="_rent">' . __('Management Fee', 'propertyhive') . '</label>';

        echo '<input type="text" class="" name="_management_fee" id="_management_fee" value="' . get_post_meta( $post->ID, '_management_fee', true ) . '" placeholder="" style="width:70px">
            
            <select id="_management_fee_units" name="_management_fee_units" class="select" style="width:auto">
                <option value="percentage"' . ( ($management_fee_units == 'percentage' || $management_fee_units == '') ? ' selected' : '') . '>' . __('%', 'propertyhive') . '</option>
                <option value="fixed"' . ( $management_fee_units == 'fixed' ? ' selected' : '') . '>' . __('Fixed', 'propertyhive') . '</option>
            </select>
            
        </p>';

        do_action('propertyhive_tenancy_details_fields');
        
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'application_pending' );
        }

        update_post_meta( $post_id, '_length', (int)$_POST['_length'] );
        update_post_meta( $post_id, '_length_units', ph_clean($_POST['_length_units']) );
        update_post_meta( $post_id, '_lease_type', ph_clean($_POST['_lease_type']) );

        update_post_meta( $post_id, '_start_date', ph_clean($_POST['_start_date']) );
        update_post_meta( $post_id, '_end_date', ph_clean($_POST['_end_date']) );
        update_post_meta( $post_id, '_review_date', ph_clean($_POST['_review_date']) );

        $amount = preg_replace("/[^0-9]/", '', ph_clean($_POST['_rent']));
        update_post_meta( $post_id, '_rent', $amount );
        update_post_meta( $post_id, '_rent_frequency', ph_clean($_POST['_rent_frequency']) );
        update_post_meta( $post_id, '_currency', ph_clean($_POST['_currency']) );

        update_post_meta( $post_id, '_management_type', ph_clean($_POST['_management_type']) );
        update_post_meta( $post_id, '_management_fee', ph_clean($_POST['_management_fee']) );
        update_post_meta( $post_id, '_management_fee_units', ph_clean($_POST['_management_fee_units']) );

        do_action( 'propertyhive_save_tenancy_details', $post_id );
    }

}
