<?php
/**
 * Contact Relationships
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Contact_Details
 */
class PH_Meta_Box_Contact_Relationships {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        $total_profiles = 0;
        
        $owner_profiles = array();
        
        // get properties where this is the owner
        $args = array(
            'post_type' => 'property',
            'meta_query' => array(
                array(
                    'key' => '_owner_contact_id',
                    'value' => $post->ID,
                    'compare' => '='
                )
            )
        );
        
        $property_query = new WP_Query($args);
        
        if ($property_query->have_posts())
        {
            while ($property_query->have_posts())
            {
                $property_query->the_post();
                
                $owner_profiles[] = $post;
                
                ++$total_profiles;
            }
        }
        wp_reset_postdata();
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="panel-wrap">
            
            <div class="ph-tabs-back"></div>

                <ul class="contact_data_tabs ph-tabs">';
                
                $tab = 0;
                foreach ($owner_profiles as $property_post)
                {
                    $owner_type = __( 'Property Owner', 'propertyhive' );
                    $department = get_post_meta($property_post->ID, '_department', TRUE);
                    if ($department == 'lettings')
                    {
                        $owner_type = __( 'Property Landlord', 'propertyhive' );
                    }   
                    echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_property_data_' . $property_post->ID . '">'.$owner_type.'</a>
                    </li>';
                    
                    ++$tab;
                }
                
                echo '<li class="property_tab' . ( ($tab == 0) ? ' active' : '') . '">
                        <a href="#tab_add_relationship">' . __( 'Add Relationship', 'propertyhive' ) . '</a>
                    </li>';
                    
                echo '</ul>';
                
                /*echo '<div id="modal_add_relationship" title="' . __( 'Add Relationship', 'propertyhive' ) . '">
                    <p>Add relationship</p>
                </div>';*/
                
                $contact_id = $post->ID;
                
                $tab = 0;
                foreach ($owner_profiles as $property_post)
                {
                    $the_property = new PH_Property( $property_post->ID );
                    
                    echo '<div id="tab_property_data_' . $property_post->ID . '" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                        <div class="options_group">';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Address', 'propertyhive') . '</label>';
                            echo $the_property->get_formatted_summary_address('<br>');
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Price', 'propertyhive') . '</label>';
                            echo $the_property->get_formatted_price();
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Bedrooms', 'propertyhive') . '</label>';
                            echo $the_property->_bedrooms;
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label>' . __('Status', 'propertyhive') . '</label>';
                            echo ( ($the_property->_on_market == 'yes') ? __('On Market', 'propertyhive') : __('Not On Market', 'propertyhive') );
                        echo '</p>';
                        
                        echo '<p class="form-field">';
                            echo '<label></label>';
                            echo '<a href="' . get_edit_post_link( $property_post->ID ) . '" class="button">' . __( 'View Property Record', 'propertyhive' ) . '</a>';
                        echo '</p>';
                        
                        echo '
                        </div>
                    </div>';
                    ++$tab;
                }

                echo '<div id="tab_add_relationship" class="panel propertyhive_options_panel" style="' . ( ($tab == 0) ? 'display:block;' : 'display:none;') . '">
                    <div class="options_group">';
                
                    echo '<p class="form-field">';
                        echo '<label>' . __('New Relationship Type', 'propertyhive') . '</label>';
                        //echo '<a href="#" class="button">' . __( 'New Applicant', 'propertyhive' ) . '</a><br><br>';
                        echo '<a href="' . admin_url( 'post-new.php?post_type=property&owner_contact_id=' . $thepostid ) . '" class="button">' . __( 'New Property Owner / Landlord', 'propertyhive' ) . '</a><br><br>';
                        //echo '<a href="#" class="button">' . __( 'New Third Party', 'propertyhive' ) . '</a>';
                    echo '</p>';
                
                echo '
                        </div>
                    </div>';
                
                echo '<div class="clear"></div>
            
            </div>
            
        </div>';
        
        echo '</div>';
        
        //do_action('propertyhive_contact_relationships_fields');
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        //update_post_meta( $post_id, 'telephone_number', $_POST['telephone_number'] );
        //update_post_meta( $post_id, 'email_address', $_POST['email_address'] );
        //update_post_meta( $post_id, 'contact_notes', $_POST['contact_notes'] );
    }

}
