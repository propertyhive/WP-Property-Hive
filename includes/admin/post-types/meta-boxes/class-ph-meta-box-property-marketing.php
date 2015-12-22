<?php
/**
 * Property Residential Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Marketing
 */
class PH_Meta_Box_Property_Marketing {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
            
                // On Market
                propertyhive_wp_checkbox( array( 
                    'id' => '_on_market', 
                    'label' => __( 'On Market', 'propertyhive' ), 
                    'desc_tip' => true,
                    'description' => __( 'Setting the property to be on the market means the property will be displayed on the website, and portals too if a <a href="http://wp-property-hive.com/add-ons/" target="_blank">portal add-on</a> is present.', 'propertyhive' ), 
                ) );

                // Availability
                $options = array();
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'availability', $args );

                $selected_value = '';
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $options[$term->term_id] = $term->name;
                    }

                    $term_list = wp_get_post_terms($post->ID, 'availability', array("fields" => "ids"));
                    
                    if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                    {
                        $selected_value = $term_list[0];
                    }
                }

                $args = array( 
                    'id' => '_availability', 
                    'label' => __( 'Availability', 'propertyhive' ), 
                    'options' => $options,
                    'desc_tip' => false,
                );
                if ($selected_value != '')
                {
                    $args['value'] = $selected_value;
                }
                propertyhive_wp_select( $args );
                
                // Featured
                propertyhive_wp_checkbox( array( 
                    'id' => '_featured', 
                    'label' => __( 'Featured', 'propertyhive' ), 
                    //'desc_tip' => true,
                    //'description' => __( 'Setting the property to be on the market enables it to be displayed on the website and in applicant matches', 'propertyhive' ), 
                ) );
                
                /*propertyhive_wp_checkbox( array( 
                    'id' => 'on_website', 
                    'label' => __( 'Display On Website (disable if on market not set)', 'propertyhive' ), 
                    'desc_tip' => false
                ) );*/
        
            do_action('propertyhive_property_marketing_fields');
    	   
            echo '</div>';
        
        echo '</div>';
           
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta($post_id, '_on_market', ( isset($_POST['_on_market']) ? $_POST['_on_market'] : '' ) );
        update_post_meta($post_id, '_featured', ( isset($_POST['_featured']) ? $_POST['_featured'] : '' ) );

        if ( !empty($_POST['_availability']) )
        {
            wp_set_post_terms( $post_id, $_POST['_availability'], 'availability' );
        }
        else
        {
            // Setting to blank
            wp_delete_object_term_relationships( $post_id, 'availability' );
        }

        do_action( 'propertyhive_save_property_marketing', $post_id );
    }

}
