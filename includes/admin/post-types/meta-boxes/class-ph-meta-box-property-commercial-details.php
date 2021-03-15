<?php
/**
 * Property Commercial Details
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Commercial_Details
 */
class PH_Meta_Box_Property_Commercial_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {

        $parent_post = false;
        if ( isset($_GET['post_parent']) && $_GET['post_parent'] != '' )
        {
            $parent_post = (int)$_GET['post_parent'];
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $available_as = array();
        if ( get_post_meta( $post->ID, '_for_sale', true ) == 'yes' )
        {
            $available_as[] = 'sale';
        }
        if ( get_post_meta( $post->ID, '_to_rent', true ) == 'yes' )
        {
            $available_as[] = 'rent';
        }
        if ( $parent_post !== FALSE && get_post_meta( $parent_post, '_for_sale', TRUE ) )
        {
            $available_as[] = 'sale';
        }
        if ( $parent_post !== FALSE && get_post_meta( $parent_post, '_to_rent', TRUE ) )
        {
            $available_as[] = 'rent';
        }

        propertyhive_wp_checkboxes( array( 
            'id' => '_available_as', 
            'label' => __( 'Available As', 'propertyhive' ), 
            'desc_tip' => false,
            'value' => $available_as,
            'options' => array(
                'sale' => 'For Sale',
                'rent' => 'To Rent',
            )
        ) );
        
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

        $selected_sale_currency = get_post_meta( $post->ID, '_commercial_price_currency', true );
        if ( $selected_sale_currency == '' )
        {
            $country = $ph_countries->get_country( $default_country );
            $selected_sale_currency = $country['currency_code'];
        }

        $selected_rent_currency = get_post_meta( $post->ID, '_commercial_rent_currency', true );
        if ( $selected_rent_currency == '' )
        {
            $country = $ph_countries->get_country( $default_country );
            $selected_rent_currency = $country['currency_code'];
        }

        // Sale fields
        echo '<div class="commercial-sale-fields"' . ( ( !in_array('sale', $available_as) ) ? ' style="display:none"' : '' ) . '>';

        echo '<p class="form-field price_field">
        
            <label for="_price_from">' . __('Price', 'propertyhive') . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . $currencies[$selected_sale_currency] . '</span>)' : '' ) . '</label>';
         
        if ( count($currencies) > 1 )
        {
            echo '<select id="_commercial_price_currency" name="_commercial_price_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_sybmol)
            {
                echo '<option value="' . $currency_code . '"' . ( ($currency_code == $selected_sale_currency) ? ' selected' : '') . '>' . $currency_sybmol . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_commercial_price_currency" value="' . $selected_sale_currency . '">';
        }

        $price_options = get_commercial_price_units( );

        echo '
        <input type="text" class="" name="_price_from" id="_price_from" value="' . get_post_meta( $post->ID, '_price_from', true ) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left"> - </span>
        <input type="text" class="" name="_price_to" id="_price_to" value="' . get_post_meta( $post->ID, '_price_to', true ) . '" placeholder="" style="width:15%; min-width:85px;">

        <select name="_price_units" id="_price_units">
            <option value=""></option>';
        foreach ( $price_options as $key => $value )
        {
            echo '<option value="' . $key . '"';
            if ( $key == get_post_meta( $post->ID, '_price_units', true ) )
            {
                echo ' selected';
            }
            echo '>' . $value . '</option>';
        }
        echo '</select>

        </p>';

        // POA
        propertyhive_wp_checkbox( array( 
            'id' => '_commercial_price_poa', 
            'label' => __( 'Price On Application', 'propertyhive' ), 
            'desc_tip' => false,
            'value' => get_post_meta( $post->ID, '_price_poa', true )
        ) );
        
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
            'id' => 'commercial_sale_by_id', 
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
        $terms = get_terms( 'commercial_tenure', $args );
        
        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($post->ID, 'commercial_tenure', array("fields" => "ids"));
            
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }
        
        $args = array( 
            'id' => 'commercial_tenure_id', 
            'label' => __( 'Tenure', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        propertyhive_wp_select( $args );

        do_action('propertyhive_property_commercial_sale_details_fields');

        echo '</div>';

        // Rent Fields
        echo '<div class="commercial-rent-fields"' . ( ( !in_array('rent', $available_as) ) ? ' style="display:none"' : '' ) . '>';

        echo '<p class="form-field price_field">
        
            <label for="_rent_from">' . __('Rent', 'propertyhive') . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . $currencies[$selected_rent_currency] . '</span>)' : '' ) . '</label>';
         
        if ( count($currencies) > 1 )
        {
            echo '<select id="_commercial_rent_currency" name="_commercial_rent_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_sybmol)
            {
                echo '<option value="' . $currency_code . '"' . ( ($currency_code == $selected_rent_currency) ? ' selected' : '') . '>' . $currency_sybmol . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_commercial_rent_currency" value="' . $selected_rent_currency . '">';
        }

        $rent_units = get_post_meta( $post->ID, '_rent_units', true );

        echo '
        <input type="text" class="" name="_rent_from" id="_rent_from" value="' . get_post_meta( $post->ID, '_rent_from', true ) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left; padding:0 5px"> - </span>
        <input type="text" class="" name="_rent_to" id="_rent_to" value="' . get_post_meta( $post->ID, '_rent_to', true ) . '" placeholder="" style="width:15%; min-width:85px;">
        
        <select name="_rent_units" id="_rent_units">
            <option value="pw"' . ( ($rent_units == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
            <option value="pcm"' . ( ($rent_units == 'pcm' || $rent_units == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
            <option value="pq"' . ( ($rent_units == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
            <option value="pa"' . ( ($rent_units == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>';
        foreach ( $price_options as $key => $value )
        {
            echo '<option value="' . $key . '"';
            if ( $key == $rent_units )
            {
                echo ' selected';
            }
            echo '>' . $value . '</option>';
        }
        echo '</select>

        </p>';
        
        // POA
        propertyhive_wp_checkbox( array( 
            'id' => '_commercial_rent_poa', 
            'label' => __( 'Rent On Application', 'propertyhive' ), 
            'desc_tip' => false,
            'value' => get_post_meta( $post->ID, '_rent_poa', true )
        ) );

        do_action('propertyhive_property_commercial_rent_details_fields');

        echo '</div>'; // end commercial-rent-fields

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
            'id' => 'commercial_price_qualifier_id', 
            'label' => __( 'Price Qualifier', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        propertyhive_wp_select( $args );

        ?>

        <p class="form-field"><label for="property_type_ids"><?php _e( 'Property Types', 'propertyhive' ); ?></label>
        <select id="property_type_ids" name="property_type_ids[]" multiple="multiple" data-placeholder="<?php _e( 'Select property types', 'propertyhive' ); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'commercial_property_type', $args );

                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ( $terms as $term )
                    {
                        $options[$term->term_id] = $term->name;

                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subterms = get_terms( 'commercial_property_type', $args );

                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            foreach ( $subterms as $subterm )
                            {
                                $options[$subterm->term_id] = '- ' . $subterm->name;

                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $subterm->term_id
                                );
                                $subsubterms = get_terms( 'commercial_property_type', $args );

                                if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                {
                                    foreach ( $subsubterms as $subsubterm )
                                    {
                                        $options[$subsubterm->term_id] = '- - ' . $subsubterm->name;
                                    }
                                }
                            }
                        }
                    }
                }
                
                $selected_values = array();
                $term_list = wp_get_post_terms($post->ID, 'commercial_property_type', array("fields" => "ids"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }
                
                if ( !empty( $options ) && !is_wp_error( $options ) )
                {
                    foreach ( $options as $key => $value )
                    {
                        echo '<option value="' . esc_attr( $key ) . '"';
                        if ( in_array( $key, $selected_values ) )
                        {
                            echo ' selected';
                        }
                        echo '>' . esc_html( $value ) . '</option>';
                    }
                }
            ?>
        </select>

        <?php

        $size_options = get_area_units( );

        $floor_area_units = get_post_meta( $post->ID, '_floor_area_units', true );

        echo '<p class="form-field">

        <label for="_floor_area_from">' . __('Floor Area', 'propertyhive') . '</label>
        
        <input type="text" class="" name="_floor_area_from" id="_floor_area_from" value="' . get_post_meta( $post->ID, '_floor_area_from', true ) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left; padding:0 5px"> - </span>
        <input type="text" class="" name="_floor_area_to" id="_floor_area_to" value="' . get_post_meta( $post->ID, '_floor_area_to', true ) . '" placeholder="" style="width:15%; min-width:85px;">

        <select name="_floor_area_units" id="_floor_area_units">';
        foreach ( $size_options as $key => $value )
        {
            echo '<option value="' . $key . '"';
            if ( $key == $floor_area_units || ($floor_area_units == '' && $key == apply_filters('propertyhive_default_commercial_floor_area_unit', 'sqft')) )
            {
                echo ' selected';
            }
            echo '>' . $value . '</option>';
        }
        echo '</select>

        </p>';

        $site_area_units = get_post_meta( $post->ID, '_site_area_units', true );

        echo '<p class="form-field">

        <label for="_site_area_from">' . __('Site Area', 'propertyhive') . '</label>
        
        <input type="text" class="" name="_site_area_from" id="_site_area_from" value="' . get_post_meta( $post->ID, '_site_area_from', true ) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left; padding:0 5px"> - </span>
        <input type="text" class="" name="_site_area_to" id="_site_area_to" value="' . get_post_meta( $post->ID, '_site_area_to', true ) . '" placeholder="" style="width:15%; min-width:85px;">

        <select name="_site_area_units" id="_site_area_units">';
        foreach ( $size_options as $key => $value )
        {
            echo '<option value="' . $key . '"';
            if ( $key == $site_area_units )
            {
                echo ' selected';
            }
            echo '>' . $value . '</option>';
        }
        echo '</select>

        </p>';

        do_action('propertyhive_property_commercial_details_fields');
        
        echo '</div>';
        
        echo '</div>';
        
        echo '<script>

            jQuery(document).ready(function()
            {
                jQuery(\'#_available_as_sale\').change(function()
                {
                    console.log(jQuery(this).is(\':checked\'));
                    if (jQuery(this).is(\':checked\'))
                    {
                        jQuery(\'.commercial-sale-fields\').slideDown(\'fast\');
                    }
                    else
                    {
                        jQuery(\'.commercial-sale-fields\').slideUp(\'fast\');
                    }
                });

                jQuery(\'#_available_as_rent\').change(function()
                {
                    console.log(jQuery(this).is(\':checked\'));
                    if (jQuery(this).is(\':checked\'))
                    {
                        jQuery(\'.commercial-rent-fields\').slideDown(\'fast\');
                    }
                    else
                    {
                        jQuery(\'.commercial-rent-fields\').slideUp(\'fast\');
                    }
                });
                    
            });

        </script>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        // Only save meta info if department is 'commercial'
        $department = get_post_meta($post_id, '_department', TRUE);
        
        if ( $department == 'commercial' || ph_get_custom_department_based_on( $department ) == 'commercial' )
        {
            update_post_meta( $post_id, '_for_sale', '' );
            update_post_meta( $post_id, '_to_rent', '' );

            if ( isset($_POST['_available_as']) && !empty($_POST['_available_as']) )
            {
                if ( in_array('sale', $_POST['_available_as']) )
                {
                    update_post_meta( $post_id, '_for_sale', 'yes' );

                    update_post_meta( $post_id, '_commercial_price_currency', ph_clean($_POST['_commercial_price_currency']) );

                    $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_price_from']));
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_price_to']));
                    }
                    update_post_meta( $post_id, '_price_from', $price );

                    $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_price_to']));
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_price_from']));
                    }
                    update_post_meta( $post_id, '_price_to', $price );

                    update_post_meta( $post_id, '_price_units', ph_clean($_POST['_price_units']) );

                    update_post_meta( $post_id, '_price_poa', ( isset($_POST['_commercial_price_poa']) ? ph_clean($_POST['_commercial_price_poa']) : '' ) );

                    if ( !empty($_POST['commercial_sale_by_id']) )
                    {
                        wp_set_post_terms( $post_id, (int)$_POST['commercial_sale_by_id'], 'sale_by' );
                    }
                    else
                    {
                        // Setting to blank
                        wp_delete_object_term_relationships( $post_id, 'sale_by' );
                    }
                    
                    if ( !empty($_POST['commercial_tenure_id']) )
                    {
                        wp_set_post_terms( $post_id, (int)$_POST['commercial_tenure_id'], 'commercial_tenure' );
                    }
                    else
                    {
                        // Setting to blank
                        wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
                    }
                }
                if ( in_array('rent', $_POST['_available_as']) )
                {
                    update_post_meta( $post_id, '_to_rent', 'yes' );

                    update_post_meta( $post_id, '_commercial_rent_currency', ph_clean($_POST['_commercial_rent_currency']) );

                    $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_rent_from']));
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_rent_to']));
                    }
                    update_post_meta( $post_id, '_rent_from', $rent );

                    $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_rent_to']));
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_rent_from']));
                    }
                    update_post_meta( $post_id, '_rent_to', $rent );

                    update_post_meta( $post_id, '_rent_units', ph_clean($_POST['_rent_units']) );

                    update_post_meta( $post_id, '_rent_poa', ( isset($_POST['_commercial_rent_poa']) ? ph_clean($_POST['_commercial_rent_poa']) : '' ) );
                }
            }

            if ( !empty($_POST['commercial_price_qualifier_id']) )
            {
                wp_set_post_terms( $post_id, (int)$_POST['commercial_price_qualifier_id'], 'price_qualifier' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'price_qualifier' );
            }

            // Store price in common currency (GBP) used for ordering
            $ph_countries = new PH_Countries();
            $ph_countries->update_property_price_actual( $post_id );

            $property_types = array();
            if ( isset( $_POST['property_type_ids'] ) && !empty( $_POST['property_type_ids'] ) )
            {
                foreach ( $_POST['property_type_ids'] as $property_type_id )
                {
                    $property_types[] = (int)$property_type_id;
                }
            }
            if ( !empty($property_types) )
            {
                wp_set_post_terms( $post_id, $property_types, 'commercial_property_type' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'commercial_property_type' );
            }

            $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_floor_area_from']));
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_floor_area_to']));
            }
            update_post_meta( $post_id, '_floor_area_from', $size );

            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, ph_clean($_POST['_floor_area_units']) ) );

            $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_floor_area_to']));
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_floor_area_from']));
            }
            update_post_meta( $post_id, '_floor_area_to', $size );

            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, ph_clean($_POST['_floor_area_units']) ) );

            update_post_meta( $post_id, '_floor_area_units', ph_clean($_POST['_floor_area_units']) );

            $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_site_area_from']));
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_site_area_to']));
            }
            update_post_meta( $post_id, '_site_area_from', $size );

            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, ph_clean($_POST['_site_area_units']) ) );

            $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_site_area_to']));
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_site_area_from']));
            }
            update_post_meta( $post_id, '_site_area_to', $size );

            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, ph_clean($_POST['_site_area_units']) ) );

            update_post_meta( $post_id, '_site_area_units', ph_clean($_POST['_site_area_units']) );

            do_action( 'propertyhive_save_property_commercial_details', $post_id );
        }
    }

}
