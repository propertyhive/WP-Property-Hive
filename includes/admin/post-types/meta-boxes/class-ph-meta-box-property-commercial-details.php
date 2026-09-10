<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Commercial_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Property_Commercial_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {

        $parent_post = false;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parent defaults; the parent must be an editable property and saving uses the metabox nonce.
        $requested_parent = isset( $_GET['post_parent'] ) && is_scalar( $_GET['post_parent'] ) ? absint( $_GET['post_parent'] ) : 0;
        if ( $requested_parent && get_post_type( $requested_parent ) === 'property' && current_user_can( 'manage_propertyhive' ) && current_user_can( 'edit_post', $requested_parent ) )
        {
            $parent_post = $requested_parent;
        }
        
        echo '<input type="hidden" name="propertyhive_commercial_details_present" value="1">';
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
        
            <label for="_price_from">' . esc_html(__('Price', 'propertyhive')) . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . esc_html($currencies[$selected_sale_currency]) . '</span>)' : '' ) . '</label>';
         
        if ( count($currencies) > 1 )
        {
            echo '<select id="_commercial_price_currency" name="_commercial_price_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_symbol)
            {
                echo '<option value="' . esc_attr($currency_code) . '"' . ( ($currency_code == $selected_sale_currency) ? ' selected' : '') . '>' . esc_html($currency_symbol) . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_commercial_price_currency" value="' . esc_attr($selected_sale_currency) . '">';
        }

        $price_options = get_commercial_price_units( );

        echo '
        <input type="text" class="" name="_price_from" id="_price_from" value="' . esc_attr(ph_display_price_field( get_post_meta( $post->ID, '_price_from', true ) )) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left"> - </span>
        <input type="text" class="" name="_price_to" id="_price_to" value="' . esc_attr(ph_display_price_field( get_post_meta( $post->ID, '_price_to', true ) )) . '" placeholder="" style="width:15%; min-width:85px;">

        <select name="_price_units" id="_price_units">
            <option value=""></option>';
        foreach ( $price_options as $key => $value )
        {
            echo '<option value="' . esc_attr($key) . '"';
            if ( $key == get_post_meta( $post->ID, '_price_units', true ) )
            {
                echo ' selected';
            }
            echo '>' . esc_html($value) . '</option>';
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
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'sale_by' ) ) );
        
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
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'commercial_tenure' ) ) );
        
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
        
            <label for="_rent_from">' . esc_html(__('Rent', 'propertyhive')) . ( ( empty($currencies) || count($currencies) <= 1 )  ? ' (<span class="currency-symbol">' . esc_html($currencies[$selected_rent_currency]) . '</span>)' : '' ) . '</label>';
         
        if ( count($currencies) > 1 )
        {
            echo '<select id="_commercial_rent_currency" name="_commercial_rent_currency" class="select" style="width:auto; float:left;">';
            foreach ($currencies as $currency_code => $currency_symbol)
            {
                echo '<option value="' . esc_attr($currency_code) . '"' . ( ($currency_code == $selected_rent_currency) ? ' selected' : '') . '>' . esc_html($currency_symbol) . '</option>';
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="hidden" name="_commercial_rent_currency" value="' . esc_attr($selected_rent_currency) . '">';
        }

        $rent_units = get_post_meta( $post->ID, '_rent_units', true );

        echo '
        <input type="text" class="" name="_rent_from" id="_rent_from" value="' . esc_attr(ph_display_price_field( get_post_meta( $post->ID, '_rent_from', true ) )) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left; padding:0 5px"> - </span>
        <input type="text" class="" name="_rent_to" id="_rent_to" value="' . esc_attr(ph_display_price_field( get_post_meta( $post->ID, '_rent_to', true ) )) . '" placeholder="" style="width:15%; min-width:85px;">
        
        <select name="_rent_units" id="_rent_units">
            <option value="pd"' . ( ($rent_units == 'pd') ? ' selected' : '') . '>' . esc_html(__('Per Day', 'propertyhive')) . '</option>
            <option value="pw"' . ( ($rent_units == 'pw') ? ' selected' : '') . '>' . esc_html(__('Per Week', 'propertyhive')) . '</option>
            <option value="pcm"' . ( ($rent_units == 'pcm') ? ' selected' : '') . '>' . esc_html(__('Per Calendar Month', 'propertyhive')) . '</option>
            <option value="pq"' . ( ($rent_units == 'pq') ? ' selected' : '') . '>' . esc_html(__('Per Quarter', 'propertyhive')) . '</option>
            <option value="pa"' . ( ($rent_units == 'pa' || $rent_units == '') ? ' selected' : '') . '>' . esc_html(__('Per Annum', 'propertyhive')) . '</option>';
        foreach ( $price_options as $key => $value )
        {
            echo '<option value="' . esc_attr($key) . '"';
            if ( $key == $rent_units )
            {
                echo ' selected';
            }
            echo '>' . esc_html($value) . '</option>';
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
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'price_qualifier' ) ) );
        
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

        <p class="form-field"><label for="property_type_ids"><?php echo esc_html(__( 'Property Types', 'propertyhive' )); ?></label>
        <select id="property_type_ids" name="property_type_ids[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select property types', 'propertyhive' )); ?>" class="multiselect attribute_values">
            <?php
                $options = array( '' => '' );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'commercial_property_type' ) ) );

                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ( $terms as $term )
                    {
                        $options[$term->term_id] = $term->name;

                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'commercial_property_type' ) ) );

                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            foreach ( $subterms as $subterm )
                            {
                                $options[$subterm->term_id] = '- ' . $subterm->name;

                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $subterm->term_id
                                );
                                $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'commercial_property_type' ) ) );

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

        <label for="_floor_area_from">' . esc_html(__('Floor Area', 'propertyhive')) . '</label>
        
        <input type="text" class="" name="_floor_area_from" id="_floor_area_from" value="' . esc_attr(get_post_meta( $post->ID, '_floor_area_from', true )) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left; padding:0 5px"> - </span>
        <input type="text" class="" name="_floor_area_to" id="_floor_area_to" value="' . esc_attr(get_post_meta( $post->ID, '_floor_area_to', true )) . '" placeholder="" style="width:15%; min-width:85px;">

        <select name="_floor_area_units" id="_floor_area_units">';
        foreach ( $size_options as $key => $value )
        {
            echo '<option value="' . esc_attr($key) . '"';
            if ( $key == $floor_area_units || ($floor_area_units == '' && $key == apply_filters('propertyhive_default_commercial_floor_area_unit', 'sqft')) )
            {
                echo ' selected';
            }
            echo '>' . esc_html($value) . '</option>';
        }
        echo '</select>

        </p>';

        $site_area_units = get_post_meta( $post->ID, '_site_area_units', true );

        echo '<p class="form-field">

        <label for="_site_area_from">' . esc_html(__('Site Area', 'propertyhive')) . '</label>
        
        <input type="text" class="" name="_site_area_from" id="_site_area_from" value="' . esc_attr(get_post_meta( $post->ID, '_site_area_from', true )) . '" placeholder="" style="width:15%; min-width:85px;">
        <span style="float:left; padding:0 5px"> - </span>
        <input type="text" class="" name="_site_area_to" id="_site_area_to" value="' . esc_attr(get_post_meta( $post->ID, '_site_area_to', true )) . '" placeholder="" style="width:15%; min-width:85px;">

        <select name="_site_area_units" id="_site_area_units">';
        foreach ( $size_options as $key => $value )
        {
            echo '<option value="' . esc_attr($key) . '"';
            if ( $key == $site_area_units )
            {
                echo ' selected';
            }
            echo '>' . esc_html($value) . '</option>';
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;
        
        // Only save meta info if department is 'commercial'
        $department = get_post_meta($post_id, '_department', TRUE);
        
        if ( $department == 'commercial' || ph_get_custom_department_based_on( $department ) == 'commercial' )
        {
            // Validate the complete submitted shape before changing any saved values.
            $input = array();
            $has_input = isset( $_POST['propertyhive_commercial_details_present'] );
            $stored_keys = array( '_commercial_price_poa' => '_price_poa', '_commercial_rent_poa' => '_rent_poa' );
            $term_fields = array( 'commercial_sale_by_id' => 'sale_by', 'commercial_tenure_id' => 'commercial_tenure', 'commercial_price_qualifier_id' => 'price_qualifier' );
            foreach ( array( '_commercial_price_currency', '_commercial_price_poa', '_commercial_rent_currency', '_commercial_rent_poa', '_floor_area_from', '_floor_area_to', '_floor_area_units', '_price_from', '_price_to', '_price_units', '_rent_from', '_rent_to', '_rent_units', '_site_area_from', '_site_area_to', '_site_area_units', 'commercial_sale_by_id', 'commercial_tenure_id', 'commercial_price_qualifier_id' ) as $input_key ) {
                if ( isset( $_POST[$input_key] ) && ! is_string( $_POST[$input_key] ) ) {
                    return;
                }
                if ( isset( $_POST[$input_key] ) ) {
                    $has_input = true;
                    $input[$input_key] = sanitize_text_field( wp_unslash( $_POST[$input_key] ) );
                } elseif ( isset( $term_fields[$input_key] ) ) {
                    $term_ids = wp_get_object_terms( $post_id, $term_fields[$input_key], array( 'fields' => 'ids' ) );
                    $input[$input_key] = ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ? (string) reset( $term_ids ) : '';
                } elseif ( isset( $_POST['propertyhive_commercial_details_present'] ) && isset( $stored_keys[$input_key] ) ) {
                    $input[$input_key] = ''; // An unchecked POA box is absent from the complete form.
                } else {
                    $input[$input_key] = (string) get_post_meta( $post_id, isset( $stored_keys[$input_key] ) ? $stored_keys[$input_key] : $input_key, true );
                }
            }
            $lists = array();
            foreach ( array( '_available_as', 'property_type_ids' ) as $input_key ) {
                if ( isset( $_POST[$input_key] ) && ! is_array( $_POST[$input_key] ) ) {
                    return;
                }
                $lists[$input_key] = array();
                if ( isset( $_POST[$input_key] ) ) {
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Shape-only check rejects nested values before the separately sanitized text or integer conversion below.
                    if ( count( array_filter( $_POST[$input_key], 'is_string' ) ) !== count( $_POST[$input_key] ) ) {
                        return;
                    }
                    $has_input = true;
                    $lists[$input_key] = array_values( array_map( 'sanitize_text_field', wp_unslash( $_POST[$input_key] ) ) );
                } elseif ( ! isset( $_POST['propertyhive_commercial_details_present'] ) ) {
                    if ( $input_key === '_available_as' ) {
                        if ( get_post_meta( $post_id, '_for_sale', true ) === 'yes' ) { $lists[$input_key][] = 'sale'; }
                        if ( get_post_meta( $post_id, '_to_rent', true ) === 'yes' ) { $lists[$input_key][] = 'rent'; }
                    } else {
                        $term_ids = wp_get_object_terms( $post_id, 'commercial_property_type', array( 'fields' => 'ids' ) );
                        $lists[$input_key] = is_wp_error( $term_ids ) ? array() : array_map( 'strval', $term_ids );
                    }
                }
            }

            if ( ! $has_input ) { return; }

            update_post_meta( $post_id, '_for_sale', '' );
            update_post_meta( $post_id, '_to_rent', '' );

            if ( ! empty( $lists['_available_as'] ) )
            {
                if ( in_array( 'sale', $lists['_available_as'], true ) )
                {
                    update_post_meta( $post_id, '_for_sale', 'yes' );

                    update_post_meta( $post_id, '_commercial_price_currency', wp_slash( $input['_commercial_price_currency'] ) );

                    $price = preg_replace("/[^0-9.]/", '', $input['_price_from']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $input['_price_to']);
                    }
                    update_post_meta( $post_id, '_price_from', $price );

                    $price = preg_replace("/[^0-9.]/", '', $input['_price_to']);
                    if ( $price == '' )
                    {
                        $price = preg_replace("/[^0-9.]/", '', $input['_price_from']);
                    }
                    update_post_meta( $post_id, '_price_to', $price );

                    update_post_meta( $post_id, '_price_units', wp_slash( $input['_price_units'] ) );

                    update_post_meta( $post_id, '_price_poa', ( wp_slash( $input['_commercial_price_poa'] ) !== '' ? wp_slash( $input['_commercial_price_poa'] ) : '' ) );

                    if ( $input['commercial_sale_by_id'] !== '' )
                    {
                        wp_set_post_terms( $post_id, (int) $input['commercial_sale_by_id'], 'sale_by' );
                    }
                    else
                    {
                        // Setting to blank
                        wp_delete_object_term_relationships( $post_id, 'sale_by' );
                    }
                    
                    if ( $input['commercial_tenure_id'] !== '' )
                    {
                        wp_set_post_terms( $post_id, (int) $input['commercial_tenure_id'], 'commercial_tenure' );
                    }
                    else
                    {
                        // Setting to blank
                        wp_delete_object_term_relationships( $post_id, 'commercial_tenure' );
                    }
                }
                if ( in_array( 'rent', $lists['_available_as'], true ) )
                {
                    update_post_meta( $post_id, '_to_rent', 'yes' );

                    update_post_meta( $post_id, '_commercial_rent_currency', wp_slash( $input['_commercial_rent_currency'] ) );

                    $rent = preg_replace("/[^0-9.]/", '', $input['_rent_from']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $input['_rent_to']);
                    }
                    update_post_meta( $post_id, '_rent_from', $rent );

                    $rent = preg_replace("/[^0-9.]/", '', $input['_rent_to']);
                    if ( $rent == '' )
                    {
                        $rent = preg_replace("/[^0-9.]/", '', $input['_rent_from']);
                    }
                    update_post_meta( $post_id, '_rent_to', $rent );

                    update_post_meta( $post_id, '_rent_units', wp_slash( $input['_rent_units'] ) );

                    update_post_meta( $post_id, '_rent_poa', ( wp_slash( $input['_commercial_rent_poa'] ) !== '' ? wp_slash( $input['_commercial_rent_poa'] ) : '' ) );
                }
            }

            if ( $input['commercial_price_qualifier_id'] !== '' )
            {
                wp_set_post_terms( $post_id, (int) $input['commercial_price_qualifier_id'], 'price_qualifier' );
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
            if ( ! empty( $lists['property_type_ids'] ) )
            {
                foreach ( $lists['property_type_ids'] as $property_type_id )
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

            $size = preg_replace("/[^0-9.]/", '', $input['_floor_area_from']);
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', $input['_floor_area_to']);
            }
            update_post_meta( $post_id, '_floor_area_from', $size );

            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, wp_slash( $input['_floor_area_units'] ) ) );

            $size = preg_replace("/[^0-9.]/", '', $input['_floor_area_to']);
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', $input['_floor_area_from']);
            }
            update_post_meta( $post_id, '_floor_area_to', $size );

            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, wp_slash( $input['_floor_area_units'] ) ) );

            update_post_meta( $post_id, '_floor_area_units', wp_slash( $input['_floor_area_units'] ) );

            $size = preg_replace("/[^0-9.]/", '', $input['_site_area_from']);
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', $input['_site_area_to']);
            }
            update_post_meta( $post_id, '_site_area_from', $size );

            update_post_meta( $post_id, '_site_area_from_sqft', convert_size_to_sqft( $size, wp_slash( $input['_site_area_units'] ) ) );

            $size = preg_replace("/[^0-9.]/", '', $input['_site_area_to']);
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', $input['_site_area_from']);
            }
            update_post_meta( $post_id, '_site_area_to', $size );

            update_post_meta( $post_id, '_site_area_to_sqft', convert_size_to_sqft( $size, wp_slash( $input['_site_area_units'] ) ) );

            update_post_meta( $post_id, '_site_area_units', wp_slash( $input['_site_area_units'] ) );

            do_action( 'propertyhive_save_property_commercial_details', $post_id );
        }
    }

}
