<?php
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
            'electricity' => __( 'Electricity Type', 'propertyhive' ), 
            'water' => __( 'Water Type', 'propertyhive' ),  
            'heating' => __( 'Heating Type', 'propertyhive' ), 
            'broadband' => __( 'Broadband Type', 'propertyhive' ), 
            'sewerage' => __( 'Sewerage Type', 'propertyhive' ),  
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
        <p class="form-field <?php echo esc_attr($utility_key); ?>_type_field"><label for="<?php echo esc_attr($utility_key); ?>_type"><?php echo esc_html($utility_label); ?></label>
            <select id="<?php echo esc_attr($utility_key); ?>_type" name="<?php echo esc_attr($utility_key); ?>_type[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select ' . strtolower($utility_label) . '(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
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
                'placeholder' => __( 'Enter ' . strtolower($utility_key) . ' type', 'propertyhive' ), 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
        }

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Accessibility', 'propertyhive' )) . '</h3>';

        $utilities = array( 
            'accessibility' => __( 'Accessibility', 'propertyhive' ),
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
        <p class="form-field <?php echo esc_attr($utility_key); ?>_field"><label for="<?php echo esc_attr($utility_key); ?>"><?php echo esc_html($utility_label); ?></label>
            <select id="<?php echo esc_attr($utility_key); ?>" name="<?php echo esc_attr($utility_key); ?>[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select ' . strtolower($utility_label) . '(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
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
                'placeholder' => __( 'Enter ' . strtolower($utility_key), 'propertyhive' ), 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );
        }

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Restrictions', 'propertyhive' )) . '</h3>';

        $terms = get_restrictions();
    ?>
        <p class="form-field restriction_field"><label for="restriction"><?php echo esc_html( __('Restrictions', 'propertyhive') ); ?></label>
            <select id="restriction" name="restriction[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select restrictions(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
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
            'placeholder' => __( 'Enter restrictions', 'propertyhive' ), 
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Rights & Easements', 'propertyhive' )) . '</h3>';

        $terms = get_rights();
    ?>
        <p class="form-field rights_field"><label for="right"><?php echo esc_html( __('Rights & Easements', 'propertyhive') ); ?></label>
            <select id="right" name="right[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select rights(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
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
            'placeholder' => __( 'Enter rights', 'propertyhive' ), 
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        echo '<h3 style="padding-left:11px;">' . esc_html(__( 'Flood Risk', 'propertyhive' )) . '</h3>';

        propertyhive_wp_checkbox( array( 
            'id' => '_flooded_in_last_five_years', 
            'label' => __( 'Flooded in last 5 years?', 'propertyhive' ),
        ) );

        $terms = get_flooding_source_types();
    ?>
        <p class="form-field flood_source_type_field"><label for="flood_source_type"><?php echo esc_html( __('Flooding Source', 'propertyhive') ); ?></label>
            <select id="flood_source_type" name="flood_source_type[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select flood source(s)', 'propertyhive' )); ?>" class="multiselect attribute_values">
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
            'placeholder' => __( 'Enter flood source', 'propertyhive' ), 
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        propertyhive_wp_checkbox( array( 
            'id' => '_flood_defences', 
            'label' => __( 'Are there flood defences?', 'propertyhive' ),
        ) );

        do_action('propertyhive_property_material_information_fields');
	   
        echo '</div>';
        
        echo '</div>';
        
        $post = $original_post;
        $thepostid = $original_thepostid;
        setup_postdata($post);
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $department = get_post_meta($post_id, '_department', TRUE);

        $departments_with_residential_details = apply_filters( 'propertyhive_departments_with_residential_details', array( 'residential-sales', 'residential-lettings' ) );
        
        if ( 
            in_array( $department, $departments_with_residential_details ) || 
            in_array( ph_get_custom_department_based_on( $department ), $departments_with_residential_details )
        )
        {
            $utilities = array( 
                'electricity' => __( 'Electricity Type', 'propertyhive' ), 
                'water' => __( 'Water Type', 'propertyhive' ),  
                'heating' => __( 'Heating Type', 'propertyhive' ), 
                'broadband' => __( 'Broadband Type', 'propertyhive' ), 
                'sewerage' => __( 'Sewerage Type', 'propertyhive' ),  
            );
            foreach ( $utilities as $utility_key => $utility_label )
            {
                $utility_types = array();
                if ( isset( $_POST[$utility_key . '_type'] ) && !empty( $_POST[$utility_key . '_type'] ) )
                {
                    foreach ( $_POST[$utility_key . '_type'] as $utility_type )
                    {
                        $utility_types[] = ph_clean($utility_type);

                        if ( ph_clean($utility_type) == 'other' )
                        {
                            update_post_meta( $post_id, '_' . $utility_key . '_type_other', $_POST['_' . $utility_key . '_type_other'] );
                        }
                    }
                }
                update_post_meta( $post_id, '_' . $utility_key . '_type', $utility_types );
            }

            $utilities = array( 
                'accessibility' => __( 'Accessibility', 'propertyhive' ), 
            );
            foreach ( $utilities as $utility_key => $utility_label )
            {
                $utility_types = array();
                if ( isset( $_POST[$utility_key] ) && !empty( $_POST[$utility_key] ) )
                {
                    foreach ( $_POST[$utility_key] as $utility_type )
                    {
                        $utility_types[] = ph_clean($utility_type);

                        if ( ph_clean($utility_type) == 'other' )
                        {
                            update_post_meta( $post_id, '_' . $utility_key . '_other', $_POST['_' . $utility_key . '_other'] );
                        }
                    }
                }
                update_post_meta( $post_id, '_' . $utility_key, $utility_types );
            }

            $restrictions = array();
            if ( isset( $_POST['restriction'] ) && !empty( $_POST['restriction'] ) )
            {
                foreach ( $_POST['restriction'] as $restriction )
                {
                    $restrictions[] = ph_clean($restriction);

                    if ( ph_clean($restriction) == 'other' )
                    {
                        update_post_meta( $post_id, '_restriction_other', $_POST['_restriction_other'] );
                    }
                }
            }
            update_post_meta( $post_id, '_restriction', $restrictions );

            $rights = array();
            if ( isset( $_POST['right'] ) && !empty( $_POST['right'] ) )
            {
                foreach ( $_POST['right'] as $right )
                {
                    $rights[] = ph_clean($right);

                    if ( ph_clean($right) == 'other' )
                    {
                        update_post_meta( $post_id, '_right_other', $_POST['_right_other'] );
                    }
                }
            }
            update_post_meta( $post_id, '_right', $rights );

            $flood_source_types = array();
            if ( isset( $_POST['flood_source_type'] ) && !empty( $_POST['flood_source_type'] ) )
            {
                foreach ( $_POST['flood_source_type'] as $flood_source_type )
                {
                    $flood_source_types[] = ph_clean($flood_source_type);

                    if ( ph_clean($flood_source_type) == 'other' )
                    {
                        update_post_meta( $post_id, '_flood_source_type_other', $_POST['_flood_source_type_other'] );
                    }
                }
            }
            update_post_meta( $post_id, '_flood_source_type', $flood_source_types );

            update_post_meta($post_id, '_flooded_in_last_five_years', ( isset($_POST['_flooded_in_last_five_years']) ? ph_clean($_POST['_flooded_in_last_five_years']) : '' ) );
            update_post_meta($post_id, '_flood_defences', ( isset($_POST['_flood_defences']) ? ph_clean($_POST['_flood_defences']) : '' ) );

            do_action( 'propertyhive_save_property_material_information', $post_id );
        }
    }

}
