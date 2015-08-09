<?php
/**
 * Property Residential Lettings Details
 *
 * @author 		BIOSTALL
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
        
        $rent_frequency = get_post_meta( $post->ID, '_rent_frequency', true );
        
        // Rent / Rent Frequency
        echo '<p class="form-field rent_field ">
        
            <label for="rent">' . __('Rent', 'propertyhive') . ' (&pound;)</label>
            
            <input type="text" class="" name="_rent" id="_rent" value="' . get_post_meta( $post->ID, '_rent', true ) . '" placeholder="" style="width:20%;">
            
            <select id="_rent_frequency" name="_rent_frequency" class="select short">
                <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
                <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
                <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
                <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>
            </select>
            
        </p>';
        
        // Deposit
        propertyhive_wp_text_input( array( 
            'id' => '_deposit', 
            'label' => __( 'Deposit', 'propertyhive' ) . ' (&pound;)', 
            'desc_tip' => false,
            'class' => '',
            'custom_attributes' => array(
                'style' => 'width:20%'
            )
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
        
        if ($department == 'residential-lettings')
        {
            $rent = preg_replace("/[^0-9.]/", '', $_POST['_rent']);
            update_post_meta( $post_id, '_rent', $rent );
            update_post_meta( $post_id, '_rent_frequency', $_POST['rent_frequency'] );
            
            $price_actual = $rent; // Used for ordering properties. Stored in pcm
            switch ($_POST['_rent_frequency'])
            {
                case "pw": { $price_actual = ($rent * 52) / 12; break; }
                case "pcm": { $price_actual = $rent; break; }
                case "pq": { $price_actual = ($rent * 4) / 52; break; }
                case "pa": { $price_actual = ($rent / 52); break; }
            }
            update_post_meta( $post_id, '_price_actual', $price_actual );
            
            update_post_meta( $post_id, '_deposit', $_POST['_deposit'] );
            update_post_meta( $post_id, '_available_date', $_POST['_available_date'] );
            
            if ( !empty($_POST['furnished_id']) )
            {
                wp_set_post_terms( $post_id, $_POST['furnished_id'], 'furnished' );
            }
            else
            {
                // Setting to blank
                wp_delete_object_term_relationships( $post_id, 'furnished' );
            }
        }
    }

}
