<?php
/**
 * Property Marketing
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
                    'description' => __( 'Setting the property to be on the market means the property will be displayed on the website, and portals too if a <a href="https://wp-property-hive.com/add-ons/" target="_blank">portal add-on</a> is present.', 'propertyhive' ), 
                ) );

                // Availability
                $availability_departments = get_option( 'propertyhive_availability_departments', array() );
                if ( !is_array($availability_departments) ) { $availability_departments = array(); }

                $department_options = array();
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'availability', $args );

                $selected_availability = '';
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $department_options[$term->term_id] = $term->name;
                    }

                    $term_list = wp_get_post_terms($post->ID, 'availability', array("fields" => "ids"));
                    
                    if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                    {
                        $selected_availability = $term_list[0];
                    }
                }

                $args = array( 
                    'id' => '_availability', 
                    'label' => __( 'Availability', 'propertyhive' ), 
                    'options' => $department_options,
                    'desc_tip' => false,
                );
                if ($selected_availability != '')
                {
                    $args['value'] = $selected_availability;
                }
                propertyhive_wp_select( $args );
                
                // Featured
                propertyhive_wp_checkbox( array( 
                    'id' => '_featured', 
                    'label' => __( 'Featured', 'propertyhive' ),
                    //'description' => __( 'Setting the property to be on the market enables it to be displayed on the website and in applicant matches', 'propertyhive' ), 
                ) );
                
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'marketing_flag', $args );

                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    $options = array();
                    $selected_values = array();

                    foreach ($terms as $term)
                    {
                        $options[$term->term_id] = $term->name;

                        $term_list = wp_get_post_terms($post->ID, 'marketing_flag', array("fields" => "ids"));
                    
                        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                        {
                            if (in_array($term->term_id, $term_list))
                            {
                                $selected_values[] = $term->term_id;
                            }
                        }
                    }

                    propertyhive_wp_checkboxes( array( 
                        'name' => '_marketing_flags', 
                        'label' => __( 'Marketing Flags', 'propertyhive' ), 
                        'options' => $options,
                        'value' => $selected_values,
                        //'description' => __( 'Setting the property to be on the market enables it to be displayed on the website and in applicant matches', 'propertyhive' ), 
                    ) );
                }
        
            do_action('propertyhive_property_marketing_fields');
    	   
            echo '</div>';
        
        echo '</div>';

        if ( !empty($availability_departments) )
        {
?>
<script>
var selected_availability = '<?php echo $selected_availability; ?>';
var availability_departments = <?php echo json_encode($availability_departments); ?>;

let availabilities = new Map();
<?php foreach ( $department_options as $term_id => $name ) { ?>
availabilities.set("<?php echo $term_id; ?>", "<?php echo $name; ?>");
<?php } ?>

jQuery(document).ready(function()
{
    fill_availability_dropdown();

    jQuery('[name=\'_department\']').change(function()
    {
        fill_availability_dropdown();
    });
});

function fill_availability_dropdown()
{
    var department = jQuery('[name=\'_department\']:checked').val();
    if ( department == '' )
    {
        department = '<?php echo get_option( 'propertyhive_primary_department', 'residential-sales' ); ?>';
    }

    if ( Object.keys(availability_departments).length > 0 )
    {
        jQuery('select[name=\'_availability\']').empty();

        for ( let [i, value] of availabilities ) 
        {
            var this_availability_departments = [];
            var availability_departments_exist = true;
            if ( typeof availability_departments[i] !== 'undefined' )
            {
                this_availability_departments = availability_departments[i];
            }
            else
            {
                availability_departments_exist = false;
            }

            if ( jQuery.inArray( department, this_availability_departments ) > -1 || !availability_departments_exist )
            {
                jQuery('select[name=\'_availability\']').append( jQuery("<option />").val(i).text(value) );
            }
            jQuery('select[name=\'_availability\']').val(selected_availability);
        }
        if ( jQuery('select[name=\'_availability\']').val() == '' || jQuery('select[name=\'_availability\']').val() == null )
        {
            jQuery('select[name=\'_availability\']').val( jQuery("select[name=\'_availability\'] option:first").val() );
        }
    }
}
</script>
<?php
        }
           
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta($post_id, '_on_market', ( isset($_POST['_on_market']) ? ph_clean($_POST['_on_market']) : '' ) );
        update_post_meta($post_id, '_featured', ( isset($_POST['_featured']) ? ph_clean($_POST['_featured']) : '' ) );
		if ( isset($_POST['_featured']) )
		{
			// Flush the cache when submitted
			delete_transient("ph_featured_properties");
		}

        if ( !empty($_POST['_availability']) )
        {
            wp_set_post_terms( $post_id, (int)$_POST['_availability'], 'availability' );
        }
        else
        {
            // Setting to blank
            wp_delete_object_term_relationships( $post_id, 'availability' );
        }

        wp_delete_object_term_relationships( $post_id, 'marketing_flag' );
        if ( !empty($_POST['_marketing_flags']) )
        {
            wp_set_post_terms( $post_id, ph_clean($_POST['_marketing_flags']), 'marketing_flag' );
        }

        do_action( 'propertyhive_save_property_marketing', $post_id );
    }

}
