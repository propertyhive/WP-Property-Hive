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

        $thepostid = $post->ID;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

		$start_date = get_post_meta( $thepostid, '_start_date', TRUE );
		$end_date = get_post_meta( $thepostid, '_end_date', TRUE );
		if ( $start_date || $end_date )
		{
			echo '<p class="form-field">
        
            <label for="">' . esc_html(__( 'Status', 'propertyhive' )) . '</label>';

			if ( $start_date && strtotime( $start_date ) > time() )
			{
				echo esc_html(__( 'Pending', 'propertyhive' ));
			}
			elseif ( 
                $start_date && strtotime( $start_date ) <= time() && 
                ( time() <= strtotime( $end_date ) || $end_date == '' )
            )
			{
				echo esc_html(__( 'Current', 'propertyhive' ));
			}
			elseif ( $end_date && strtotime( $end_date ) < time() )
			{
				echo esc_html(__( 'Finished', 'propertyhive' ));
			}

			echo '</p>';
		}
        
        $length_units = get_post_meta( $thepostid, '_length_units', true );
        $lease_type = get_post_meta( $thepostid, '_lease_type', true );

        $show_lease_length =  apply_filters( 'propertyhive_show_tenancy_lease_length', true );

        if ( $show_lease_length === true)
        {
            $lease_term_type_html = '
                <p class="form-field lease_term_type_field">
            
                <label for="_length">' . esc_html(__('Lease Term and Type', 'propertyhive')) . '</label>

                <input type="number" class="" name="_length" id="_length" value="' . esc_attr(get_post_meta( $post->ID, '_length', true )) . '" placeholder="" style="width:70px">
                
                <select id="_length_units" name="_length_units" class="select" style="width:auto">
                    <option value="week"' . ( $length_units == 'week' ? ' selected' : '') . '>' . esc_html(__('Weeks', 'propertyhive')) . '</option>
                    <option value="month"' . ( ($length_units == 'month' || $length_units == '') ? ' selected' : '') . '>' . esc_html(__('Months', 'propertyhive')) . '</option>
                </select>';
        }
        else
        {
            $lease_term_type_html = '
                <p class="form-field lease_term_type_field">
            
                <label for="_lease_type">' . esc_html(__('Lease Type', 'propertyhive')) . '</label>
            ';
        }

        $lease_term_type_html .= '
            <select id="_lease_type" name="_lease_type" class="select" style="width:auto">
        ';

        $lease_type_options = apply_filters( 'propertyhive_tenancy_lease_types', array(
            'assured_shorthold' => 'Assured Shorthold',
            'assured' => 'Assured',
        ) );

        $i = 1;
        foreach ( $lease_type_options as $lease_type_name => $lease_type_display )
        {
            $lease_term_type_html .= '<option value="' . esc_attr($lease_type_name) . '"' . ( ($lease_type == $lease_type_name || ( $lease_type == '' && $i === 1 ) ) ? ' selected' : '') . '>' . esc_html(__($lease_type_display, 'propertyhive')) . '</option>';
            $i++;
        }

        $lease_term_type_html .=  '
                </select>
            </p>';

        echo $lease_term_type_html;

        $args = array(
            'id' => '_start_date', 
            'label' => __( 'Tenancy Start Date', 'propertyhive' ), 
            'desc_tip' => false, 
            'class' => 'small',
            'type' => 'date'
        );
        propertyhive_wp_text_input( $args );

        $args = array( 
            'id' => '_end_date', 
            'label' => __( 'Tenancy End Date', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => 'small',
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
        
            <label for="_rent">' . esc_html(__('Rent', 'propertyhive')) . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . $currencies[$selected_currency] . '</span>)' : '' ) . '</label>';
        
        if ( count($currencies) > 1 )
        {
            echo '<select id="_rent_currency" name="_rent_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_symbol)
            {
                echo '<option value="' . esc_attr($currency_code) . '"' . ( ($currency_code == $selected_currency) ? ' selected' : '') . '>' . $currency_symbol . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_rent_currency" value="' . esc_attr($selected_currency) . '">';
        }

        echo '<input type="text" class="" name="_rent" id="_rent" value="' . esc_attr(ph_display_price_field( get_post_meta( $post->ID, '_rent', true ) )) . '" placeholder="" style="width:70px">
            
            <select id="_rent_frequency" name="_rent_frequency" class="select" style="width:auto">
                <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . esc_html(__('Per Week', 'propertyhive')) . '</option>
                <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . esc_html(__('Per Calendar Month', 'propertyhive')) . '</option>
                <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . esc_html(__('Per Quarter', 'propertyhive')) . '</option>
                <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . esc_html(__('Per Annum', 'propertyhive')) . '</option>
            </select>
            
        </p>';

		echo '<p class="form-field deposit_field ">
        
            <label for="_summary_deposit">' . esc_html(__('Deposit', 'propertyhive')) . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . $currencies[$selected_currency] . '</span>)' : '' ) . '</label>
			<input type="text" class="" name="_deposit" id="_summary_deposit" value="' . esc_attr(ph_display_price_field( get_post_meta( $post->ID, '_deposit', true ) )) . '" placeholder="" style="width:70px">           
        </p>';

        $args = array(
            'id' => '_notes', 
            'label' => __( 'Additional Notes', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => '',
        );
        propertyhive_wp_textarea_input( $args );

        do_action('propertyhive_tenancy_details_fields');
        
        echo '</div>';
        
        echo '</div>';

        echo '<script>

        jQuery(document).ready(function()
        {
            // Set end date to X weeks/months after when start date changed
            jQuery(\'#_start_date\').change(function()
            {
                if (
                    jQuery(\'#_length\').val() !== undefined && jQuery(\'#_length_units\').val() !== undefined
                    &&
                    jQuery(\'#_length\').val() != \'\' && jQuery(\'#_length_units\').val() != \'\'
                    &&
                    jQuery(this).val() != \'\' && jQuery(\'#_end_date\').val() == \'\' )
                {
                    // Only do stuff if it\'s not been set already. Don\'t want to be messing if things already entered

                    var ms_in_day = 86400000;
                    var start_date = new Date(jQuery(this).val());

                    if ( jQuery(\'#_length_units\').val() == \'week\' )
                    {
                        var end_date = start_date.getTime() + ( jQuery(\'#_length\').val() * 7 * ms_in_day );
                        end_date = new Date( end_date );
                        end_date.setDate( end_date.getDate() - 1 );
                        jQuery(\'#_end_date\').val( end_date.toISOString().substring(0, 10) );
                    }
                    if ( jQuery(\'#_length_units\').val() == \'month\' )
                    {
                        var end_date = add_months(start_date, jQuery(\'#_length\').val());
                        end_date.setDate( end_date.getDate() - 1 );
                        jQuery(\'#_end_date\').val( end_date.toISOString().substring(0, 10) );
                    }
                }
            });

            jQuery(\'select[name=_management_type]\').change(function()
            {
                if ( jQuery(this).val() == \'fully_managed\' )
                {
                    jQuery(\'.form-field.management-fee-details\').show();
                }
                else
                {
                    jQuery(\'.form-field.management-fee-details\').hide();
                }
                
                jQuery(\'select[name=_management_type]\').val(jQuery(this).val());
            });
        });

        </script>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
	    //  die($_POST);
        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'application' );
        }

        if ( isset( $_POST['_length'] ) )
        {
            update_post_meta( $post_id, '_length', (int)$_POST['_length'] );
        }

        if ( isset( $_POST['_length_units'] ) )
        {
            update_post_meta( $post_id, '_length_units', ph_clean($_POST['_length_units']) );
        }

        if ( isset( $_POST['_lease_type'] ) )
        {
            update_post_meta( $post_id, '_lease_type', ph_clean($_POST['_lease_type']) );
        }

        update_post_meta( $post_id, '_start_date', ph_clean($_POST['_start_date']) );
        update_post_meta( $post_id, '_end_date', ph_clean($_POST['_end_date']) );

        $amount = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_rent']));
        update_post_meta( $post_id, '_rent', $amount );
        update_post_meta( $post_id, '_rent_frequency', ph_clean($_POST['_rent_frequency']) );
        update_post_meta( $post_id, '_currency', ph_clean($_POST['_rent_currency']) );

	    $amount = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_deposit']));
	    update_post_meta( $post_id, '_deposit', $amount );

        update_post_meta( $post_id, '_notes', sanitize_textarea_field($_POST['_notes']) );

        do_action( 'propertyhive_save_tenancy_details', $post_id );
    }

}
