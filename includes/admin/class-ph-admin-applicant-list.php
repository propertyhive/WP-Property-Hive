	<?php
/**
 * PropertyHive Admin Generate Applicant List Class.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Applicant_List' ) ) :

/**
 * PH_Admin_Applicant_List
 */
class PH_Admin_Applicant_List {

	/**
	 * Handles the display of the main Property Hive reports page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {

        $property_types = array();
        $locations = array();
?>
<div class="wrap propertyhive">

	<h1>Generate Applicant List</h1>

	<form method="post" id="mainform" action="" enctype="multipart/form-data" class="applicant-list-form">

        <input type="hidden" name="submitted" value="1">

		<div id="poststuff" class="propertyhive_meta_box">

			<p class="form-field">
				<label>Looking For</label>
				<select name="department">
					<?php
						$departments = array();
                        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
                        {
                            $departments['residential-sales'] = __( 'Residential Sales', 'propertyhive' );
                        }
                        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
                        {
                            $departments['residential-lettings'] = __( 'Residential Lettings', 'propertyhive' );
                        }
                        if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
                        {
                            $departments['commercial'] = __( 'Commercial', 'propertyhive' );
                        }

                        foreach ( $departments as $key => $department )
                        {
                        	echo '<option value="' . $key . '"';
                        	if ( isset($_POST['department']) && $_POST['department'] == $key )
                        	{
                        		echo ' selected';
                        	}
                        	elseif ( !isset($_POST['department']) && $key == get_option( 'propertyhive_primary_department' ) )
                        	{
                        		echo ' selected';
                        	}
                        	echo '>' . $department . '</option>';
                        }
					?>
					
				</select>
			</p>

			<p class="form-field sales-only">
				<label>Maximum Price From</label>
				<input type="text" name="maximum_price_from" value="<?php if ( isset($_POST['maximum_price_from']) ) { echo esc_attr( $_POST['maximum_price_from'] ); } ?>">
			</p>

			<p class="form-field sales-only">
				<label>Maximum Price To</label>
				<input type="text" name="maximum_price_to" value="<?php if ( isset($_POST['maximum_price_to']) ) { echo esc_attr( $_POST['maximum_price_to'] ); } ?>">
			</p>

			<p class="form-field lettings-only">
				<label>Maximum Rent From (PCM)</label>
				<input type="text" name="maximum_rent_from" value="<?php if ( isset($_POST['maximum_rent_from']) ) { echo esc_attr( $_POST['maximum_rent_from'] ); } ?>">
			</p>

			<p class="form-field lettings-only">
				<label>Maximum Rent To (PCM)</label>
				<input type="text" name="maximum_rent_to" value="<?php if ( isset($_POST['maximum_rent_to']) ) { echo esc_attr( $_POST['maximum_rent_to'] ); } ?>">
			</p>

			<p class="form-field residential-only">
				<label>Minimum Bedrooms From</label>
				<input type="number" name="minimum_bedrooms_from" class="short" value="<?php if ( isset($_POST['minimum_bedrooms_from']) ) { echo esc_attr( $_POST['minimum_bedrooms_from'] ); } ?>">
			</p>

			<p class="form-field residential-only">
				<label>Minimum Bedrooms To</label>
				<input type="number" name="minimum_bedrooms_to" class="short" value="<?php if ( isset($_POST['minimum_bedrooms_to']) ) { echo esc_attr( $_POST['minimum_bedrooms_to'] ); } ?>">
			</p>

			<p class="form-field residential-only">
				<label>Property Types</label>
				<select id="property_types" name="property_types[]" multiple="multiple" data-placeholder="Start typing to add property types..." class="multiselect attribute_values">
                    <?php
                        $options = array( '' => '' );
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'property_type', $args );

                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $property_types[$term->term_id] = esc_html( $term->name );

                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                if ( isset($_POST['property_types']) && in_array( $term->term_id, $_POST['property_types'] ) )
                                {
                                    echo ' selected';
                                }
                                echo '>' . esc_html( $term->name ) . '</option>';
                                
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subterms = get_terms( 'property_type', $args );
                                
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        $property_types[$term->term_id] = esc_html( $term->name );

                                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                        if ( isset($_POST['property_types']) && in_array( $term->term_id, $_POST['property_types'] ) )
                                        {
                                            echo ' selected';
                                        }
                                        echo '>- ' . esc_html( $term->name ) . '</option>';
                                    }
                                }
                            }
                        }
                    ?>
                </select>
			</p>

			<p class="form-field">
				<label>Location</label>
				<select id="locations" name="locations[]" multiple="multiple" data-placeholder="Start typing to add locations..." class="multiselect attribute_values">
                    <?php
                        $options = array( '' => '' );
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( 'location', $args );

                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $locations[$term->term_id] = esc_html( $term->name );

                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                if ( isset($_POST['locations']) && in_array( $term->term_id, $_POST['locations'] ) )
                                {
                                    echo ' selected';
                                }
                                echo '>' . esc_html( $term->name ) . '</option>';
                                
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subterms = get_terms( 'location', $args );
                                
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        $locations[$term->term_id] = esc_html( $term->name );

                                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                        if ( isset($_POST['locations']) && in_array( $term->term_id, $_POST['locations'] ) )
                                        {
                                            echo ' selected';
                                        }
                                        echo '>- ' . esc_html( $term->name ) . '</option>';

                                        $args = array(
                                            'hide_empty' => false,
                                            'parent' => $term->term_id
                                        );
                                        $subsubterms = get_terms( 'location', $args );
                                        
                                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                        {
                                            foreach ($subsubterms as $term)
                                            {
                                                $locations[$term->term_id] = esc_html( $term->name );

                                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                                if ( isset($_POST['locations']) && in_array( $term->term_id, $_POST['locations'] ) )
                                                {
                                                    echo ' selected';
                                                }
                                                echo '>- - ' . esc_html( $term->name ) . '</option>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    ?>
                </select>
			</p>

            <p class="form-field">
            <input type="submit" value="<?php echo __( 'Generate Applicant List', 'propertyhive' ); ?>" class="button-primary">
            </p>

		</div>

	</form>

    <div class="applicant-list-results">

        <?php 
            if ( isset($_POST['submitted']) && $_POST['submitted'] == '1' ) 
            { 
                $args = array(
                    'post_type' => 'contact',
                    'nopaging' => true,
                );

                $args['meta_query'] = array();

                $args['meta_query'][] = array(
                    'key' => '_contact_types',
                    'value' => 'applicant',
                    'compare' => 'LIKE'
                );

                $applicant_query = new WP_Query($args);

                $results = array();

                if ( $applicant_query->have_posts() )
                {
                    while ( $applicant_query->have_posts() )
                    {
                        $applicant_query->the_post();

                        $num_applicant_profiles = get_post_meta( get_the_ID(), '_applicant_profiles', TRUE );
                        if ( $num_applicant_profiles == '' )
                        {
                            $num_applicant_profiles = 0;
                        }

                        for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                        {
                            $profile = get_post_meta( get_the_ID(), '_applicant_profile_' . $i, TRUE );

                            $match = true;

                            if ( isset($_POST['department']) )
                            {
                                if ( isset($profile['department']) && $profile['department'] != ph_clean($_POST['department']) )
                                {
                                    $match = false;
                                }
                            }

                            if ( isset($_POST['department']) && $_POST['department'] == 'residential-sales' )
                            {
                                if ( isset($_POST['maximum_price_from']) && ph_clean($_POST['maximum_price_from']) != '' ) 
                                {
                                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price_from']));

                                    if ( isset($profile['max_price_actual']) && $profile['max_price_actual'] != '' && $profile['max_price_actual'] != 0 && $profile['max_price_actual'] < $price )
                                    {
                                        $match = false;
                                    }
                                }
                                if ( isset($_POST['maximum_price_to']) && ph_clean($_POST['maximum_price_to']) != '' )
                                {
                                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price_to']));

                                    if ( isset($profile['max_price_actual']) && $profile['max_price_actual'] != '' && $profile['max_price_actual'] != 0 && $profile['max_price_actual'] > $price )
                                    {
                                        $match = false;
                                    }
                                }
                            }
                            if ( isset($_POST['department']) && $_POST['department'] == 'residential-lettings' )
                            {
                                if ( isset($_POST['maximum_rent_from']) && ph_clean($_POST['maximum_rent_from']) != '' ) 
                                {
                                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_rent_from']));

                                    if ( isset($profile['max_rent_actual']) && $profile['max_rent_actual'] != '' && $profile['max_rent_actual'] != 0 && $profile['max_rent_actual'] < $price )
                                    {
                                        $match = false;
                                    }
                                }
                                if ( isset($_POST['maximum_rent_to']) && ph_clean($_POST['maximum_rent_to']) != '' )
                                {
                                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_rent_to']));

                                    if ( isset($profile['max_rent_actual']) && $profile['max_rent_actual'] != '' && $profile['max_rent_actual'] != 0 && $profile['max_rent_actual'] > $price )
                                    {
                                        $match = false;
                                    }
                                }
                            }
                            if ( isset($_POST['department']) && ( $_POST['department'] == 'residential-sales' || $_POST['department'] == 'residential-lettings' ) )
                            {
                                if ( isset($_POST['minimum_bedrooms_from']) && ph_clean($_POST['minimum_bedrooms_from']) != '' ) 
                                {
                                    $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['minimum_bedrooms_from']));

                                    if ( isset($profile['min_beds']) && $profile['min_beds'] != '' && $profile['min_beds'] != 0 && $profile['min_beds'] < $beds )
                                    {
                                        $match = false;
                                    }
                                }
                                if ( isset($_POST['minimum_bedrooms_to']) && ph_clean($_POST['minimum_bedrooms_to']) != '' ) 
                                {
                                    $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['minimum_bedrooms_to']));

                                    if ( isset($profile['min_beds']) && $profile['min_beds'] != '' && $profile['min_beds'] != 0 && $profile['min_beds'] > $beds )
                                    {
                                        $match = false;
                                    }
                                }

                                // Property Types
                                if ( isset($_POST['property_types']) && is_array($_POST['property_types']) && !empty($_POST['property_types']) )
                                {
                                    $found_type = false;
                                    foreach ($_POST['property_types'] as $search_property_type)
                                    {
                                        if ( isset($profile['property_types']) && is_array($profile['property_types']) && in_array($search_property_type, $profile['property_types']) )
                                        {
                                            $found_type = true;
                                        }
                                    }

                                    if ( !$found_type )
                                    {
                                        $match = false;
                                    }
                                }
                            }

                            if ( isset($_POST['locations']) && is_array($_POST['locations']) && !empty($_POST['locations']) )
                            {
                                $found_type = false;
                                foreach ($_POST['locations'] as $search_location)
                                {
                                    if ( isset($profile['locations']) && is_array($profile['locations']) && in_array($search_location, $profile['locations']) )
                                    {
                                        $found_type = true;
                                    }
                                }

                                if ( !$found_type )
                                {
                                    $match = false;
                                }
                            }

                            if ( $match )
                            {
                                $contact_details = array();
                                if ( get_post_meta( get_the_ID(), '_telephone_number', TRUE ) != '' )
                                {
                                    $contact_details[] = 'T: ' . get_post_meta( get_the_ID(), '_telephone_number', TRUE );
                                }
                                if ( get_post_meta( get_the_ID(), '_email_address', TRUE ) != '' )
                                {
                                    $contact_details[] = 'E: ' . get_post_meta( get_the_ID(), '_email_address', TRUE );
                                }
                                $results[] = array(
                                    'name' => get_the_title(),
                                    'edit_link' => get_edit_post_link(get_the_ID()),
                                    'contact_details' => implode("<br>", $contact_details),
                                    'profile' => $profile
                                );
                            }
                        }
                    }
                }

                wp_reset_postdata();
        ?>
        <br>
        <div class="applicant-list-results">
            <h3><?php echo number_format(count($results)); ?> Applicants Found Matching Your Criteria</h3>
            <table width="100%" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th style="text-align:left;">Applicant Name</th>
                        <th style="text-align:left;">Contact Details</th>
                        <th style="text-align:left;">Requirements</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        if ( !empty($results) )
                        { 
                            foreach ( $results as $result ) 
                            { 
                    ?>
                    <tr>
                        <td><a href="<?php echo $result['edit_link'] ?>" target="_blank"><?php echo $result['name']; ?></a></td>
                        <td><?php echo ( ( $result['contact_details'] != '' ) ? $result['contact_details'] : '-' ); ?></td>
                        <td><?php
                            if ( isset($result['profile']['department']) )
                            {
                                switch ( $result['profile']['department'] )
                                {
                                    case "residential-sales":
                                    {
                                        $output = array();
                                        if ( isset($result['profile']['max_price']) && $result['profile']['max_price'] != '' && $result['profile']['max_price'] != 0 )
                                        {
                                            $output[] = '<strong>Max Price:</strong> &pound;' . number_format($result['profile']['max_price']);
                                        }
                                        if ( isset($result['profile']['min_beds']) && $result['profile']['min_beds'] != '' && $result['profile']['min_beds'] != 0 )
                                        {
                                            $output[] = '<strong>Min Beds:</strong> ' . number_format($result['profile']['min_beds']);
                                        }
                                        if ( isset($result['profile']['property_types']) && is_array($result['profile']['property_types']) && !empty($result['profile']['property_types']) )
                                        {
                                            $output_types = array();
                                            foreach ( $result['profile']['property_types'] as $profile_type )
                                            {
                                                $output_types[] = $property_types[$profile_type];
                                            }
                                            $output[] = '<strong>Property Types:</strong> ' . implode(", ", $output_types);
                                        }
                                        if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
                                        {
                                            $output_locations = array();
                                            foreach ( $result['profile']['locations'] as $profile_location )
                                            {
                                                $output_locations[] = $locations[$profile_location];
                                            }
                                            $output[] = '<strong>Locations:</strong> ' . implode(", ", $output_locations);
                                        }
                                        if ( isset($result['profile']['notes']) && $result['profile']['notes'] != '' )
                                        {
                                            $output[] = '<strong>Additional Requirements:</strong> ' . nl2br($result['profile']['notes']);
                                        }
                                        echo( !empty($output) ? implode("<br>", $output) : '-' );
                                        break;
                                    }
                                    case "residential-lettings":
                                    {
                                        $output = array();
                                        if ( isset($result['profile']['max_rent']) && $result['profile']['max_rent'] != '' && $result['profile']['max_rent'] != 0 )
                                        {
                                            $output[] = '<strong>Max Rent:</strong> &pound;' . number_format($result['profile']['max_rent']) . $result['profile']['rent_frequency'];
                                        }
                                        if ( isset($result['profile']['min_beds']) && $result['profile']['min_beds'] != '' && $result['profile']['min_beds'] != 0 )
                                        {
                                            $output[] = '<strong>Min Beds:</strong> ' . number_format($result['profile']['min_beds']);
                                        }
                                        if ( isset($result['profile']['property_types']) && is_array($result['profile']['property_types']) && !empty($result['profile']['property_types']) )
                                        {
                                            $output_types = array();
                                            foreach ( $result['profile']['property_types'] as $profile_type )
                                            {
                                                $output_types[] = $property_types[$profile_type];
                                            }
                                            $output[] = '<strong>Property Types:</strong> ' . implode(", ", $output_types);
                                        }
                                        if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
                                        {
                                            $output_locations = array();
                                            foreach ( $result['profile']['locations'] as $profile_location )
                                            {
                                                $output_locations[] = $locations[$profile_location];
                                            }
                                            $output[] = '<strong>Locations:</strong> ' . implode(", ", $output_locations);
                                        }
                                        if ( isset($result['profile']['notes']) && $result['profile']['notes'] != '' )
                                        {
                                            $output[] = '<strong>Additional Requirements:</strong> ' . nl2br($result['profile']['notes']);
                                        }
                                        echo( !empty($output) ? implode("<br>", $output) : '-' );
                                        break;
                                    }
                                    case "commercial":
                                    {
                                        $output = array();
                                        /*if ( isset($result['profile']['max_price']) && $result['profile']['max_price'] != '' && $result['profile']['max_price'] != 0 )
                                        {
                                            $output[] = '<strong>Max Price:</strong> &pound;' . number_format($result['profile']['max_price']);
                                        }
                                        if ( isset($result['profile']['min_beds']) && $result['profile']['min_beds'] != '' && $result['profile']['min_beds'] != 0 )
                                        {
                                            $output[] = '<strong>Min Beds:</strong> ' . number_format($result['profile']['min_beds']);
                                        }
                                        if ( isset($result['profile']['property_types']) && is_array($result['profile']['property_types']) && !empty($result['profile']['property_types']) )
                                        {
                                            $output_types = array();
                                            foreach ( $result['profile']['property_types'] as $profile_type )
                                            {
                                                $output_types[] = $property_types[$profile_type];
                                            }
                                            $output[] = '<strong>Property Types:</strong> ' . implode(", ", $output_types);
                                        }*/
                                        if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
                                        {
                                            $output_locations = array();
                                            foreach ( $result['profile']['locations'] as $profile_location )
                                            {
                                                $output_locations[] = $locations[$profile_location];
                                            }
                                            $output[] = '<strong>Locations:</strong> ' . implode(", ", $output_locations);
                                        }
                                        if ( isset($result['profile']['notes']) && $result['profile']['notes'] != '' )
                                        {
                                            $output[] = '<strong>Additional Requirements:</strong> ' . nl2br($result['profile']['notes']);
                                        }
                                        echo( !empty($output) ? implode("<br>", $output) : '-' );
                                        break;
                                    }
                                }
                            }
                        ?></td>
                    </tr>
                    <?php 
                            } 
                        }
                        else
                        {
                    ?>
                    <tr>
                        <td colspan="3" style="text-align:center"><?php echo __( 'No matching applicants found', 'propertyhive' ); ?></td>
                    </tr>
                    <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <?php 
            } 
        ?>

    </div>

</div>
<script>

function toggleDepartmentFields()
{
    if (jQuery('#mainform').length > 0)
    {
        // There may be multiple forms on the page so treat each one individually
        jQuery('#mainform').each(function()
        {
            var selectedDepartment = "residential-sales"; // TODO: Use default from settings

            var selected = jQuery(this).find('[name=\'department\']');
            
            jQuery(this).find('.sales-only').hide();
            jQuery(this).find('.lettings-only').hide();
            jQuery(this).find('.residential-only').hide();
            jQuery(this).find('.commercial-only').hide();
            
            if (selected.length > 0)
            {
                selectedDepartment = selected.val();

                // controls won't always be display:block so we should get the 
                // first visible component (that isnt sales/lettings-only) and 
                // use that display
                var display = 'block';

                if (selectedDepartment == 'residential-sales')
                {
                    jQuery(this).find('.sales-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if (selectedDepartment == 'residential-lettings')
                {
                    jQuery(this).find('.lettings-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if (selectedDepartment == 'commercial')
                {
                    jQuery(this).find('.commercial-only').css('display', display);
                }
            }
        });
    }
}

jQuery( function(jQuery) {

	// Multiselect
    jQuery("#mainform select.multiselect").chosen();

    toggleDepartmentFields();
    
    jQuery('#mainform [name=\'department\']').change(function()
    {
        toggleDepartmentFields();
    });

});

jQuery(window).resize(function() {
    toggleDepartmentFields();
});

</script>
<?php
	}

}

endif;