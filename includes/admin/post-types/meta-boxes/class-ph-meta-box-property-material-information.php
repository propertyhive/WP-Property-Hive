<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Property Material Information
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Material_Information
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Material_Information; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Property_Material_Information {

	/**
	 * Output the metabox
	 */
	public static function output( $post, $args = array() ) {
        
        global $wpdb, $thepostid;

        $original_post = $post;
        $original_thepostid = $thepostid;

        // Used in the scenario where this meta box isn't used on the property edit page
        if ( isset( $args['args']['property_post'] ) )
        {
            $post = $args['args']['property_post'];
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared meta-box global contract; $thepostid is intentionally set for the meta-box output and its included field helpers.
            $thepostid = $post->ID;
            setup_postdata($post);
        }

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        /*propertyhive_wp_text_input( array( 
            'id' => '_bedrooms', 
            'label' => __( 'Bedrooms', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_bathrooms', 
            'label' => __( 'Bathrooms', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_reception_rooms', 
            'label' => __( 'Reception Rooms', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'number'
        ) );*/
        
        // Utilities

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Utilities', 'propertyhive' )) . '</h3>';

        $utilities = array( 
            'electricity' => __( 'electricity type', 'propertyhive' ), 
            'water' => __( 'water type', 'propertyhive' ),  
            'heating' => __( 'heating type', 'propertyhive' ), 
            'broadband' => __( 'broadband type', 'propertyhive' ), 
            'sewerage' => __( 'sewerage type', 'propertyhive' ),  
        );
        foreach ( $utilities as $utility_key => $utility_label )
        {
            // Construct the function name based on the utility
            $function_name = "get_{$utility_key}_types";

            $terms = array();

            // Check if the function exists before calling it
            if ( function_exists($function_name) ) 
            {
                $terms = $function_name();
            }
        ?>
        <p class="form-field <?php echo esc_attr($utility_key); ?>_type_field"><label for="<?php echo esc_attr($utility_key); ?>_type"><?php echo esc_html(ucfirst($utility_label)); ?></label>
            <select id="<?php echo esc_attr($utility_key); ?>_type" name="<?php echo esc_attr($utility_key); ?>_type[]" multiple="multiple" data-placeholder="<?php 
                echo esc_attr(
                    sprintf(
                        /* translators: %s: utility type (e.g. "gas type", "electricity type") */
                        /* translators: %s: Utility name. */
                        sprintf( __( 'Select %s', 'propertyhive' ), $utility_label ),
                        $utility_label
                    )
                ); 
            ?>" class="multiselect attribute_values">
                <?php
                    $selected_values = array();
                    $term_list = get_post_meta($post->ID, '_' . $utility_key . '_type', true);
                    if ( is_array($term_list) && !empty($term_list) )
                    {
                        foreach ( $term_list as $term_id )
                        {
                            $selected_values[] = $term_id;
                        }
                    }
                    
                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ( $terms as $key => $term )
                        {
                            echo '<option value="' . esc_attr( $key ) . '"';
                            if ( in_array( $key, $selected_values ) )
                            {
                                echo ' selected';
                            }
                            echo '>' . esc_html( $term ) . '</option>';
                        }
                    }
                ?>
            </select>
        </p>
<?php
            $args = array( 
                'id' => '_' . $utility_key . '_type_other', 
                'label' => '', 
                'desc_tip' => false, 
                'placeholder' => sprintf(
                    /* translators: %s: utility type (e.g. "gas type", "electricity type") */
                    __( 'Enter other %s', 'propertyhive' ),
                    $utility_label
                ),
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
        }

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Accessibility', 'propertyhive' )) . '</h3>';

        $utilities = array( 
            'accessibility' => __( 'accessibility features', 'propertyhive' ),
        );
        foreach ( $utilities as $utility_key => $utility_label )
        {
            // Construct the function name based on the utility
            $function_name = "get_{$utility_key}_types";

            $terms = array();

            // Check if the function exists before calling it
            if ( function_exists($function_name) ) 
            {
                $terms = $function_name();
            }
        ?>
        <p class="form-field <?php echo esc_attr($utility_key); ?>_field"><label for="<?php echo esc_attr($utility_key); ?>"><?php echo esc_html(ucfirst($utility_label)); ?></label>
            <select id="<?php echo esc_attr($utility_key); ?>" name="<?php echo esc_attr($utility_key); ?>[]" multiple="multiple" data-placeholder="<?php 
                echo esc_attr(
                    sprintf(
                        /* translators: %s: utility type (e.g. "gas type", "electricity type") */
                        /* translators: %s: Utility name. */
                        sprintf( __( 'Select %s', 'propertyhive' ), $utility_label ),
                        $utility_label
                    )
                ); 
            ?>" class="multiselect attribute_values">
                <?php
                    $selected_values = array();
                    $term_list = get_post_meta($post->ID, '_' . $utility_key, true);
                    if ( is_array($term_list) && !empty($term_list) )
                    {
                        foreach ( $term_list as $term_id )
                        {
                            $selected_values[] = $term_id;
                        }
                    }
                    
                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ( $terms as $key => $term )
                        {
                            echo '<option value="' . esc_attr( $key ) . '"';
                            if ( in_array( $key, $selected_values ) )
                            {
                                echo ' selected';
                            }
                            echo '>' . esc_html( $term ) . '</option>';
                        }
                    }
                ?>
            </select>
        </p>
<?php
            $args = array( 
                'id' => '_' . $utility_key . '_other', 
                'label' => '', 
                'desc_tip' => false, 
                'placeholder' => sprintf(
                    /* translators: %s: utility type (e.g. "gas type", "electricity type") */
                    __( 'Enter other %s', 'propertyhive' ),
                    $utility_label
                ),
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
        }

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Restrictions', 'propertyhive' )) . '</h3>';

        $terms = get_restrictions();
    ?>
        <p class="form-field restriction_field"><label for="restriction"><?php echo esc_html( __('Restrictions', 'propertyhive') ); ?></label>
            <select id="restriction" name="restriction[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select restrictions', 'propertyhive' )); ?>" class="multiselect attribute_values">
                <?php
                    $selected_values = array();
                    $term_list = get_post_meta($post->ID, '_restriction', true);
                    if ( is_array($term_list) && !empty($term_list) )
                    {
                        foreach ( $term_list as $term_id )
                        {
                            $selected_values[] = $term_id;
                        }
                    }
                    
                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ( $terms as $key => $term )
                        {
                            echo '<option value="' . esc_attr( $key ) . '"';
                            if ( in_array( $key, $selected_values ) )
                            {
                                echo ' selected';
                            }
                            echo '>' . esc_html( $term ) . '</option>';
                        }
                    }
                ?>
            </select>
        </p>
    <?php

        $args = array( 
            'id' => '_restriction_other', 
            'label' => '', 
            'desc_tip' => false, 
            'placeholder' => __( 'Enter other restrictions', 'propertyhive' ), 
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Rights and easements', 'propertyhive' )) . '</h3>';

        $terms = get_rights();
    ?>
        <p class="form-field rights_field"><label for="right"><?php echo esc_html( __('Rights and easements', 'propertyhive') ); ?></label>
            <select id="right" name="right[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select rights and easements', 'propertyhive' )); ?>" class="multiselect attribute_values">
                <?php
                    $selected_values = array();
                    $term_list = get_post_meta($post->ID, '_right', true);
                    if ( is_array($term_list) && !empty($term_list) )
                    {
                        foreach ( $term_list as $term_id )
                        {
                            $selected_values[] = $term_id;
                        }
                    }
                    
                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ( $terms as $key => $term )
                        {
                            echo '<option value="' . esc_attr( $key ) . '"';
                            if ( in_array( $key, $selected_values ) )
                            {
                                echo ' selected';
                            }
                            echo '>' . esc_html( $term ) . '</option>';
                        }
                    }
                ?>
            </select>
        </p>
    <?php

        $args = array( 
            'id' => '_right_other', 
            'label' => '', 
            'desc_tip' => false, 
            'placeholder' => __( 'Enter other rights and easements', 'propertyhive' ),
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Flood risk', 'propertyhive' )) . '</h3>';

        propertyhive_wp_select( array( 
            'id' => '_flooded_in_last_five_years', 
            'label' => __( 'Flooded in last 5 years?', 'propertyhive' ),
            'options' => array(
                '' => '',
                'no' => __( 'No', 'propertyhive' ),
                'yes' => __( 'Yes', 'propertyhive' ),
            )
        ) );

        $terms = get_flooding_source_types();
    ?>
        <p class="form-field flood_source_type_field"><label for="flood_source_type"><?php echo esc_html( __('Flooding Source', 'propertyhive') ); ?></label>
            <select id="flood_source_type" name="flood_source_type[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select flood source', 'propertyhive' )); ?>" class="multiselect attribute_values">
                <?php
                    $selected_values = array();
                    $term_list = get_post_meta($post->ID, '_flood_source_type', true);
                    if ( is_array($term_list) && !empty($term_list) )
                    {
                        foreach ( $term_list as $term_id )
                        {
                            $selected_values[] = $term_id;
                        }
                    }
                    
                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ( $terms as $key => $term )
                        {
                            echo '<option value="' . esc_attr( $key ) . '"';
                            if ( in_array( $key, $selected_values ) )
                            {
                                echo ' selected';
                            }
                            echo '>' . esc_html( $term ) . '</option>';
                        }
                    }
                ?>
            </select>
        </p>
    <?php

        $args = array( 
            'id' => '_flood_source_type_other', 
            'label' => '', 
            'desc_tip' => false, 
            'placeholder' => __( 'Enter other flood sources', 'propertyhive' ),
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        propertyhive_wp_select( array( 
            'id' => '_flood_defences', 
            'label' => __( 'Are there flood defences?', 'propertyhive' ),
            'options' => array(
                '' => '',
                'no' => __( 'No', 'propertyhive' ),
                'yes' => __( 'Yes', 'propertyhive' ),
            )
        ) );

        do_action('propertyhive_property_material_information_fields');
	   
        echo '</div>';
        
        echo '</div>';
        
        $post = $original_post;
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared meta-box global contract; $thepostid is intentionally set for the meta-box output and its included field helpers.
        $thepostid = $original_thepostid;
        setup_postdata($post);
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

        $department = get_post_meta($post_id, '_department', TRUE);

        $departments_with_residential_details = apply_filters( 'propertyhive_departments_with_residential_details', array( 'residential-sales', 'residential-lettings' ) );
        
        if ( 
            in_array( $department, $departments_with_residential_details ) || 
            in_array( ph_get_custom_department_based_on( $department ), $departments_with_residential_details )
        )
        {
            $list_fields = array(
                'electricity_type', 'water_type', 'heating_type', 'broadband_type',
                'sewerage_type', 'accessibility', 'restriction', 'right', 'flood_source_type',
            );
            foreach ( $list_fields as $field ) {
                $selected = array();
                if ( isset( $_POST[ $field ] ) ) {
                    // A multiselect submits a flat array. Reject malformed shapes without replacing saved data.
                    if ( ! is_array( $_POST[ $field ] ) ) {
                        continue;
                    }
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each array member is checked for string shape, unslashed, and sanitized immediately below before storage.
                    foreach ( $_POST[ $field ] as $item ) {
                        if ( ! is_string( $item ) ) {
                            continue 2;
                        }
                        $selected[] = sanitize_text_field( wp_unslash( $item ) );
                    }
                }
                if ( in_array( 'other', $selected, true ) ) {
                    $other_key = '_' . $field . '_other';
                    if ( ! isset( $_POST[ $other_key ] ) || is_string( $_POST[ $other_key ] ) ) {
                        $other = isset( $_POST[ $other_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $other_key ] ) ) : '';
                        update_post_meta( $post_id, $other_key, wp_slash( $other ) );
                    }
                }
                update_post_meta( $post_id, '_' . $field, wp_slash( $selected ) );
            }

            foreach ( array( '_flooded_in_last_five_years', '_flood_defences' ) as $field ) {
                if ( isset( $_POST[ $field ] ) && ! is_string( $_POST[ $field ] ) ) {
                    continue;
                }
                $value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
                update_post_meta( $post_id, $field, wp_slash( $value ) );
            }

            do_action( 'propertyhive_save_property_material_information', $post_id );
        }
    }

}
