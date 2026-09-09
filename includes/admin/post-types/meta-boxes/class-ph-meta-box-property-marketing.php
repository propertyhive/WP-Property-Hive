<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Marketing; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'availability' ) ) );

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
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'marketing_flag' ) ) );

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
var selected_availability = '<?php echo esc_js($selected_availability); ?>';
var availability_departments = <?php echo wp_json_encode( $availability_departments, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;

let availabilities = new Map();
<?php foreach ( $department_options as $term_id => $name ) { ?>
availabilities.set("<?php echo (int)$term_id; ?>", "<?php echo esc_js($name); ?>");
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
        department = '<?php echo esc_js(get_option( 'propertyhive_primary_department', 'residential-sales' )); ?>';
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;
        
        foreach ( array( '_on_market', '_featured' ) as $field ) {
            if ( isset( $_POST[ $field ] ) && ! is_string( $_POST[ $field ] ) ) {
                continue;
            }
            $value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
            update_post_meta( $post_id, $field, wp_slash( $value ) );
            if ( '_featured' === $field ) {
                // Both checking and unchecking featured changes the cached property selection.
                delete_transient( 'ph_featured_properties' );
            }
        }

        if ( ! isset( $_POST['_availability'] ) || is_string( $_POST['_availability'] ) ) {
            $availability = isset( $_POST['_availability'] ) ? absint( $_POST['_availability'] ) : 0;
            if ( $availability ) {
                wp_set_post_terms( $post_id, $availability, 'availability' );
            } else {
                wp_delete_object_term_relationships( $post_id, 'availability' );
            }
        }

        $marketing_flags = array();
        $valid_flags = true;
        if ( isset( $_POST['_marketing_flags'] ) ) {
            if ( ! is_array( $_POST['_marketing_flags'] ) ) {
                $valid_flags = false;
            } else {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each checkbox value is shape-checked, unslashed and sanitized below before being passed to the taxonomy API.
                foreach ( $_POST['_marketing_flags'] as $flag ) {
                    if ( ! is_string( $flag ) ) {
                        $valid_flags = false;
                        break;
                    }
                    $marketing_flags[] = sanitize_text_field( wp_unslash( $flag ) );
                }
            }
        }
        if ( $valid_flags ) {
            wp_delete_object_term_relationships( $post_id, 'marketing_flag' );
            if ( $marketing_flags ) {
                wp_set_post_terms( $post_id, $marketing_flags, 'marketing_flag' );
            }
        }

        do_action( 'propertyhive_save_property_marketing', $post_id );
    }

}
