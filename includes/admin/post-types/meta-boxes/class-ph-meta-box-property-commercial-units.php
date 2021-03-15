<?php
/**
 * Property Commercial Units
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Commercial_Units
 */
class PH_Meta_Box_Property_Commercial_Units {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            // Get children posts/properties
            $args = array(
                'post_parent' => $post->ID,
                'post_type'   => 'property', 
                'nopaging'    => true,
                'orderby'     => 'title'
            );
            $unit_query = new WP_Query( $args );

            if ( $unit_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Unit Address</th>
                            <th style="text-align:left;">Size</th>
                            <th style="text-align:left;">Price</th>
                            <th style="text-align:left;">Availability</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $unit_query->have_posts() )
                {
                    $unit_query->the_post();

                    $the_property = new PH_Property(get_the_ID());

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID() ) . '">' . $the_property->get_formatted_summary_address() . '</a></td>';
                        echo '<td style="text-align:left;">';
                        $floor_area = $the_property->get_formatted_floor_area();
                        if ( $floor_area != '' )
                        {
                            echo 'Floor Area: ' . $floor_area . '<br>';
                        }
                        $site_area = $the_property->get_formatted_site_area();
                        if ( $site_area != '' )
                        {
                            echo 'Site Area: ' . $site_area;
                        }

                        if ( $floor_area == '' && $site_area == '' )
                        {
                            echo '-';
                        }
                        echo '</td>';
                        echo '<td style="text-align:left;">';
                        $price = $the_property->get_formatted_price();
                        if ($price == '')
                        {
                            $price = '-';
                        }
                        echo $price;
                        echo '</td>';
                        echo '<td style="text-align:left;">';
                        $term_list = wp_get_post_terms($post->ID, 'availability', array("fields" => "names"));
            
                        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                        {
                           echo $term_list[0]. '<br>';
                        }

                        if (isset($the_property->_on_market) && $the_property->_on_market == 'yes')
                        {
                            echo 'On The Market';
                        }
                        else
                        {
                            echo 'Not On The Market';
                        }
                        
                        if (isset($the_property->_featured) && $the_property->_featured == 'yes')
                        {
                            echo '<br>Featured';
                        }
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No units currently exist for this property', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

            echo '<a href="' . admin_url('post-new.php?post_type=property&post_parent=' . $post->ID) . '" class="button">' . __( 'Add New Unit', 'propertyhive' ) . '</a>';

        do_action('propertyhive_property_commercial_units_fields');
        
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        // Only save meta info if department is 'commercial'
        $department = get_post_meta($post_id, '_department', TRUE);
        
        if ( $department == 'commercial' || ph_get_custom_department_based_on($department) == 'commercial' )
        {
            
        }
    }

}
