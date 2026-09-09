<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Admin_Applicant_List; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Admin_Applicant_List {

	/**
	 * Handles the display of the main Property Hive reports page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public function output() {

		// Applicant filters are read-only; the export endpoint verifies its nonce and capability.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This request only repopulates the read-only filter form and renders its results.
		$request_post = wp_unslash( $_POST );
		$has_department_input = isset( $request_post['department'] ) && is_scalar( $request_post['department'] );
		$department_input = $has_department_input ? sanitize_text_field( $request_post['department'] ) : '';
		$maximum_price_input = ( isset( $request_post['maximum_price'] ) && is_scalar( $request_post['maximum_price'] ) ) ? sanitize_text_field( $request_post['maximum_price'] ) : '';
		$maximum_rent_input = ( isset( $request_post['maximum_rent'] ) && is_scalar( $request_post['maximum_rent'] ) ) ? sanitize_text_field( $request_post['maximum_rent'] ) : '';
		$minimum_bedrooms_input = ( isset( $request_post['minimum_bedrooms'] ) && is_scalar( $request_post['minimum_bedrooms'] ) ) ? sanitize_text_field( $request_post['minimum_bedrooms'] ) : '';
		$property_types_input = array();
		if ( isset( $request_post['property_types'] ) && is_array( $request_post['property_types'] ) ) {
			foreach ( $request_post['property_types'] as $property_type_input ) {
				if ( is_scalar( $property_type_input ) ) {
					$property_types_input[] = absint( $property_type_input );
				}
			}
		}
		$locations_input = array();
		if ( isset( $request_post['locations'] ) && is_array( $request_post['locations'] ) ) {
			foreach ( $request_post['locations'] as $location_input ) {
				if ( is_scalar( $location_input ) ) {
					$locations_input[] = absint( $location_input );
				}
			}
		}
		$include_non_send_matching_properties_input = ( isset( $request_post['include_non_send_matching_properties'] ) && is_scalar( $request_post['include_non_send_matching_properties'] ) ) ? sanitize_text_field( $request_post['include_non_send_matching_properties'] ) : '';
		$submitted_applicant_list = ( isset( $request_post['submitted_applicant_list'] ) && is_scalar( $request_post['submitted_applicant_list'] ) ) ? sanitize_key( $request_post['submitted_applicant_list'] ) : '';

	        $property_types = array();
        $locations = array();
?>
<div class="wrap propertyhive">

	<h1>Generate Applicant List</h1>

	<form method="post" id="mainform" action="" class="applicant-list-form">

        <input type="hidden" name="submitted_applicant_list" value="1">
        <?php wp_nonce_field( 'ph_applicant_export', 'ph_applicant_export_nonce' ); ?>

		<div id="poststuff" class="propertyhive_meta_box">

			<p class="form-field">
				<label><?php echo esc_html__( 'Looking For', 'propertyhive' ); ?></label>
				<select name="department">
					<?php

                        $departments = ph_get_departments();

						$department_options = array();
                        foreach ( $departments as $key => $value )
                        {
                            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                            {
                                $department_options[$key] = $value;
                            }
                        }

                        foreach ( $department_options as $key => $department )
                        {
                        	echo '<option value="' . esc_attr($key) . '"';
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
							if ( $has_department_input && $department_input == $key )
                        	{
                        		echo ' selected';
                        	}
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
							elseif ( ! $has_department_input && $key == get_option( 'propertyhive_primary_department' ) )
                        	{
                        		echo ' selected';
                        	}
                        	echo '>' . esc_html($department) . '</option>';
                        }
					?>
					
				</select>
			</p>

			<p class="form-field sales-only">
				<label><?php echo esc_html__( 'Maximum Price', 'propertyhive' ); ?> <img class="help_tip" data-tip="This will search the applicant's Match Price Range if one is set and return applicants where the price entered falls into this range. Otherwise it will search the Maximum Price and return applicants that have maximum price higher than the value entered" src="<?php echo esc_url(PH()->plugin_url()); ?>/assets/images/help.png" height="16" width="16" /></label>
				<input type="text" name="maximum_price" value="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
	 if ( $maximum_price_input !== '' ) { echo esc_attr( $maximum_price_input ); } ?>">
			</p>

			<p class="form-field lettings-only">
				<label><?php echo esc_html__( 'Maximum Rent (PCM)', 'propertyhive' ); ?></label>
				<input type="text" name="maximum_rent" value="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
	 if ( $maximum_rent_input !== '' ) { echo esc_attr( $maximum_rent_input ); } ?>">
			</p>

			<p class="form-field residential-only">
				<label><?php echo esc_html__( 'Minimum Bedrooms', 'propertyhive' ); ?></label>
				<input type="number" name="minimum_bedrooms" class="short" value="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
	 if ( $minimum_bedrooms_input !== '' ) { echo esc_attr( $minimum_bedrooms_input ); } ?>">
			</p>

			<p class="form-field residential-only">
				<label><?php echo esc_html__( 'Property Types', 'propertyhive' ); ?></label>
				<select id="property_types" name="property_types[]" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Start typing to add property types', 'propertyhive' ); ?>..." class="multiselect attribute_values">
                    <?php
                        $options = array( '' => '' );
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );

                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $property_types[$term->term_id] = $term->name;

                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                                if ( ! empty( $property_types_input ) && in_array( $term->term_id, $property_types_input ) )
                                {
                                    echo ' selected';
                                }
                                echo '>' . esc_html( $term->name ) . '</option>';
                                
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                                
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        $property_types[$term->term_id] = $term->name;

                                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                                        if ( ! empty( $property_types_input ) && in_array( $term->term_id, $property_types_input ) )
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
				<label><?php echo esc_html__( 'Location', 'propertyhive' ); ?></label>
				<select id="locations" name="locations[]" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Start typing to add locations', 'propertyhive' ); ?>..." class="multiselect attribute_values">
                    <?php
                        $options = array( '' => '' );
                        $args = array(
                            'hide_empty' => false,
                            'parent' => 0
                        );
                        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );

                        if ( !empty( $terms ) && !is_wp_error( $terms ) )
                        {
                            foreach ($terms as $term)
                            {
                                $locations[$term->term_id] = $term->name;

                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                                if ( ! empty( $locations_input ) && in_array( $term->term_id, $locations_input ) )
                                {
                                    echo ' selected';
                                }
                                echo '>' . esc_html( $term->name ) . '</option>';
                                
                                $args = array(
                                    'hide_empty' => false,
                                    'parent' => $term->term_id
                                );
                                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                                
                                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                                {
                                    foreach ($subterms as $term)
                                    {
                                        $locations[$term->term_id] = $term->name;

                                        echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                                        if ( ! empty( $locations_input ) && in_array( $term->term_id, $locations_input ) )
                                        {
                                            echo ' selected';
                                        }
                                        echo '>- ' . esc_html( $term->name ) . '</option>';

                                        $args = array(
                                            'hide_empty' => false,
                                            'parent' => $term->term_id
                                        );
                                        $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                                        
                                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                        {
                                            foreach ($subsubterms as $term)
                                            {
                                                $locations[$term->term_id] = $term->name;

                                                echo '<option value="' . esc_attr( $term->term_id ) . '"';
                                                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                                                if ( ! empty( $locations_input ) && in_array( $term->term_id, $locations_input ) )
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
                <label><?php echo esc_html__( 'Include Applicants with \'Send Matching Properties\' Unticked', 'propertyhive' ); ?></label>
                <input type="checkbox" name="include_non_send_matching_properties" value="yes"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
 if ( $include_non_send_matching_properties_input !== '' && sanitize_text_field($include_non_send_matching_properties_input) == 'yes' ) { echo ' checked'; } ?>>
            </p>

            <?php do_action('propertyhive_applicant_list_additional_fields'); ?>

            <p class="form-field">
                <input type="submit" value="<?php echo esc_attr(__( 'Generate Applicant List', 'propertyhive' )); ?>" class="button-primary">
                <a href="" class="button" id="export_applicant_list_results_button"><?php echo esc_html__( 'Export To CSV', 'propertyhive' ); ?></a>
                <input type="hidden" name="export_applicant_list_results" value="">
            </p>

		</div>

	</form>

    <div class="applicant-list-results">

        <?php 
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
            if ( $submitted_applicant_list !== '' && $submitted_applicant_list == '1' )
            { 
                $results = $this->generate_results();
        ?>
        <br>
        <div class="applicant-list-results">
            <h3><?php echo esc_html(number_format(count($results))); ?> Applicants Found Matching Your Criteria</h3>
            <table width="100%" cellpadding="8" cellspacing="0">
                <thead>
                    <tr>
                        <th style="text-align:left;"><?php echo esc_html__( 'Applicant Name', 'propertyhive' ); ?></th>
                        <th style="text-align:left;"><?php echo esc_html__( 'Contact Details', 'propertyhive' ); ?></th>
                        <th style="text-align:left;"><?php echo esc_html__( 'Requirements', 'propertyhive' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        if ( !empty($results) )
                        { 
                            $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
                            $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

                            foreach ( $results as $result ) 
                            {
                                $currency = '&pound;';
                                if ( isset($result['profile']['currency']) && !empty($result['profile']['currency']) )
                                {
                                    $PH_Countries = new PH_Countries();
                                    $selected_currency = $PH_Countries->get_currency($result['profile']['currency']);
                                    if ( $selected_currency !== false )
                                    {
                                        $currency = $selected_currency['currency_symbol'];
                                    }
                                }
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url($result['edit_link']); ?>" target="_blank"><?php echo esc_html($result['name']); ?></a></td>
                        <td><?php
                            $contact_details = array();
                            if ( $result['telephone_number'] != '' )
                            {
                                $contact_details[] = 'T: ' . esc_html($result['telephone_number']);
                            }
                            if ( $result['email_address'] != '' )
                            {
                                $contact_details[] = 'E: ' . esc_html($result['email_address']);
                            }
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Telephone and email text are escaped above; only fixed br markup joins them.
                            echo !empty($contact_details) ? implode("<br>", $contact_details) : '-';
                        ?></td>
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
                                            $output[] = '<strong>Max Price:</strong> ' . $currency . esc_html(ph_display_price_field($result['profile']['max_price']));

                                            if ( $percentage_lower != '' && $percentage_higher != '' )
                                            {
                                                $match_price_range_lower = '';
                                                if ( !isset($result['profile']['match_price_range_lower_actual']) || ( isset($result['profile']['match_price_range_lower_actual']) && $result['profile']['match_price_range_lower_actual'] == '' ) )
                                                {
                                                    if ( isset($result['profile']['max_price']) && $result['profile']['max_price'] != '' )
                                                    {
                                                        $match_price_range_lower = (float) $result['profile']['max_price'] - ( (float) $result['profile']['max_price'] * ( (float) $percentage_lower / 100 ) );
                                                    }
                                                }
                                                else
                                                {
                                                    $match_price_range_lower = $result['profile']['match_price_range_lower'];
                                                }

                                                $match_price_range_higher = '';
                                                if ( !isset($result['profile']['match_price_range_higher_actual']) || ( isset($result['profile']['match_price_range_higher_actual']) && $result['profile']['match_price_range_higher_actual'] == '' ) )
                                                {
                                                    if ( isset($result['profile']['max_price']) && $result['profile']['max_price'] != '' )
                                                    {
                                                        $match_price_range_higher = (float) $result['profile']['max_price'] + ( (float) $result['profile']['max_price'] * ( (float) $percentage_higher / 100 ) );
                                                    }
                                                }
                                                else
                                                {
                                                    $match_price_range_higher = $result['profile']['match_price_range_higher'];
                                                }

                                                if ( 
                                                    $match_price_range_lower != '' && $match_price_range_higher != ''
                                                )
                                                {
                                                    $output[] = '<strong>Max Price Range:</strong> ' . $currency . esc_html(ph_display_price_field($match_price_range_lower)) . ' to ' . $currency . esc_html(ph_display_price_field($match_price_range_higher));
                                                }
                                            }
                                        }
                                        if ( isset($result['profile']['min_beds']) && $result['profile']['min_beds'] != '' && $result['profile']['min_beds'] != 0 )
                                        {
                                            $output[] = '<strong>Min Beds:</strong> ' . esc_html(number_format( (float) $result['profile']['min_beds'] ));
                                        }
                                        if ( isset($result['profile']['property_types']) && is_array($result['profile']['property_types']) && !empty($result['profile']['property_types']) )
                                        {
                                            $output_types = array();
                                            foreach ( $result['profile']['property_types'] as $profile_type )
                                            {
                                                if ( is_scalar( $profile_type ) && isset( $property_types[$profile_type] ) ) {
                                                    $output_types[] = $property_types[$profile_type];
                                                }
                                            }
                                            $output[] = '<strong>Property Types:</strong> ' . esc_html(implode(", ", $output_types));
                                        }
                                        if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
                                        {
                                            $output_locations = array();
                                            foreach ( $result['profile']['locations'] as $profile_location )
                                            {
                                                if ( is_scalar( $profile_location ) && isset( $locations[$profile_location] ) ) {
                                                    $output_locations[] = $locations[$profile_location];
                                                }
                                            }
                                            $output[] = '<strong>Locations:</strong> ' . esc_html(implode(", ", $output_locations));
                                        }
                                        if ( isset($result['profile']['notes']) && $result['profile']['notes'] != '' )
                                        {
                                            $output[] = '<strong>Additional Requirements:</strong> ' . nl2br(esc_html($result['profile']['notes']));
                                        }
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Requirement text is escaped when assembled above; strong/br markup is fixed and currency-symbol filter HTML remains trusted.
                                        echo( !empty($output) ? implode("<br>", $output) : '-' );
                                        break;
                                    }
                                    case "residential-lettings":
                                    {
                                        $output = array();
                                        if ( isset($result['profile']['max_rent']) && $result['profile']['max_rent'] != '' && $result['profile']['max_rent'] != 0 )
                                        {
                                            $output[] = '<strong>Max Rent:</strong> ' . $currency . esc_html(ph_display_price_field($result['profile']['max_rent']) . $result['profile']['rent_frequency']);
                                        }
                                        if ( isset($result['profile']['min_beds']) && $result['profile']['min_beds'] != '' && $result['profile']['min_beds'] != 0 )
                                        {
                                            $output[] = '<strong>Min Beds:</strong> ' . esc_html(number_format( (float) $result['profile']['min_beds'] ));
                                        }
                                        if ( isset($result['profile']['property_types']) && is_array($result['profile']['property_types']) && !empty($result['profile']['property_types']) )
                                        {
                                            $output_types = array();
                                            foreach ( $result['profile']['property_types'] as $profile_type )
                                            {
                                                if ( is_scalar( $profile_type ) && isset( $property_types[$profile_type] ) ) {
                                                    $output_types[] = $property_types[$profile_type];
                                                }
                                            }
                                            $output[] = '<strong>Property Types:</strong> ' . esc_html(implode(", ", $output_types));
                                        }
                                        if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
                                        {
                                            $output_locations = array();
                                            foreach ( $result['profile']['locations'] as $profile_location )
                                            {
                                                if ( is_scalar( $profile_location ) && isset( $locations[$profile_location] ) ) {
                                                    $output_locations[] = $locations[$profile_location];
                                                }
                                            }
                                            $output[] = '<strong>Locations:</strong> ' . esc_html(implode(", ", $output_locations));
                                        }
                                        if ( isset($result['profile']['notes']) && $result['profile']['notes'] != '' )
                                        {
                                            $output[] = '<strong>Additional Requirements:</strong> ' . nl2br(esc_html($result['profile']['notes']));
                                        }
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Requirement text is escaped when assembled above; strong/br markup is fixed and currency-symbol filter HTML remains trusted.
                                        echo( !empty($output) ? implode("<br>", $output) : '-' );
                                        break;
                                    }
                                    case "commercial":
                                    {
                                        $output = array();
                                        if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
                                        {
                                            $output_locations = array();
                                            foreach ( $result['profile']['locations'] as $profile_location )
                                            {
                                                if ( is_scalar( $profile_location ) && isset( $locations[$profile_location] ) ) {
                                                    $output_locations[] = $locations[$profile_location];
                                                }
                                            }
                                            $output[] = '<strong>Locations:</strong> ' . esc_html(implode(", ", $output_locations));
                                        }
                                        if ( isset($result['profile']['notes']) && $result['profile']['notes'] != '' )
                                        {
                                            $output[] = '<strong>Additional Requirements:</strong> ' . nl2br(esc_html($result['profile']['notes']));
                                        }
                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Requirement text is escaped when assembled above; strong/br markup is fixed and currency-symbol filter HTML remains trusted.
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
                        <td colspan="3" style="text-align:center"><?php echo esc_html(__( 'No matching applicants found', 'propertyhive' )); ?></td>
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

var custom_departments = <?php echo json_encode(ph_get_custom_departments()); ?>;
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

                if ( selectedDepartment == 'residential-sales' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == 'residential-sales' ) )
                {
                    jQuery(this).find('.sales-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if ( selectedDepartment == 'residential-lettings' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == 'residential-lettings' ) )
                {
                    jQuery(this).find('.lettings-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if ( selectedDepartment == 'commercial' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == 'commercial' ) )
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

    jQuery('#export_applicant_list_results_button').click(function(e)
    {
        e.preventDefault();
        jQuery('input[name=\'export_applicant_list_results\']').val('1');
        jQuery('#mainform').submit();

        setTimeout(function() { jQuery('input[name=\'export_applicant_list_results\']').val(''); }, 1000);
    });
});

jQuery(window).resize(function() {
    toggleDepartmentFields();
});

</script>
<?php
	}

    public function generate_results()
    {
		// Results are read-only. Normalize the submitted filters before they are used in comparisons or queries.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This method only reads applicant filters; the separate export endpoint verifies its nonce and capability.
		$request_post = wp_unslash( $_POST );
		$has_department = isset( $request_post['department'] ) && is_scalar( $request_post['department'] );
		$department_input = $has_department ? sanitize_text_field( $request_post['department'] ) : '';
		$maximum_price_input = ( isset( $request_post['maximum_price'] ) && is_scalar( $request_post['maximum_price'] ) ) ? sanitize_text_field( $request_post['maximum_price'] ) : '';
		$maximum_rent_input = ( isset( $request_post['maximum_rent'] ) && is_scalar( $request_post['maximum_rent'] ) ) ? sanitize_text_field( $request_post['maximum_rent'] ) : '';
		$minimum_bedrooms_input = ( isset( $request_post['minimum_bedrooms'] ) && is_scalar( $request_post['minimum_bedrooms'] ) ) ? sanitize_text_field( $request_post['minimum_bedrooms'] ) : '';
		$has_property_types = isset( $request_post['property_types'] ) && is_array( $request_post['property_types'] );
		$property_types_input = array();
		if ( $has_property_types ) {
			foreach ( $request_post['property_types'] as $property_type_input ) {
				if ( is_scalar( $property_type_input ) ) {
					$property_types_input[] = absint( $property_type_input );
				}
			}
		}
		$has_locations = isset( $request_post['locations'] ) && is_array( $request_post['locations'] );
		$locations_input = array();
		if ( $has_locations ) {
			foreach ( $request_post['locations'] as $location_input ) {
				if ( is_scalar( $location_input ) ) {
					$locations_input[] = absint( $location_input );
				}
			}
		}
		$has_include_non_send_matching_properties = isset( $request_post['include_non_send_matching_properties'] );

        $search_property_types = array();
        if ( 
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
            $has_department &&
            ( 
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                $department_input == 'residential-sales' ||
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                $department_input == 'residential-lettings' ||
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                ph_get_custom_department_based_on($department_input) == 'residential-sales' ||
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                ph_get_custom_department_based_on($department_input) == 'residential-lettings'
            ) 
        )
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
            if ( $has_property_types && ! empty( $property_types_input ) )
            {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                foreach ( $property_types_input as $property_type )
                {
                    $search_property_types[] = (int)$property_type;

                    $args = array(
                        'hide_empty' => false,
                        'parent' => $property_type
                    );
                    $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );

                    if ( !empty( $terms ) && !is_wp_error( $terms ) )
                    {
                        foreach ($terms as $term)
                        {
                            $search_property_types[] = $term->term_id;

                            $args = array(
                                'hide_empty' => false,
                                'parent' => $term->term_id
                            );
                            $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                            
                            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                            {
                                foreach ($subterms as $term)
                                {
                                    $search_property_types[] = $term->term_id;
                                }
                            }
                        }
                    }
                }
            }
            $search_property_types = array_unique($search_property_types);
        }

        $search_locations = array();
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
        if ( $has_locations && ! empty( $locations_input ) )
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
            foreach ( $locations_input as $location )
            {
                $search_locations[] = (int)$location;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $location
                );
                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );

                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $search_locations[] = $term->term_id;

                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                        
                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            foreach ($subterms as $term)
                            {
                                $search_locations[] = $term->term_id;
                            }
                        }
                    }
                }
            }
        }
        $search_locations = array_unique($search_locations);

        $args = array(
            'post_type' => 'contact',
            'fields' => 'ids',
            'nopaging' => true,
        );

        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Existing applicant membership is stored in serialized _contact_types metadata; preserve complete list/export results and their extension match checks. Query fetches IDs only.
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
            $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
            $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

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

                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                    if ( !$has_include_non_send_matching_properties )
                    {
                        if ( !isset($profile['send_matching_properties']) || ( isset($profile['send_matching_properties']) && $profile['send_matching_properties'] != 'yes' ) )
                        {
                            $match = false;
                        }
                    }

                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                    if ( $has_department )
                    {
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                        if ( isset($profile['department']) && $profile['department'] != ph_clean($department_input) )
                        {
                            $match = false;
                        }
                    }

                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                    if ( $has_department && ( $department_input == 'residential-sales' || ph_get_custom_department_based_on($department_input) == 'residential-sales' ) )
                    {
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                        if ( $maximum_price_input !== '' && ph_clean($maximum_price_input) != '' )
                        {
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            $price = preg_replace("/[^0-9.]/", '', ph_clean($maximum_price_input));

                            if ( $percentage_lower != '' && $percentage_higher != '' )
                            {
                                $match_price_range_lower = '';
                                if ( !isset($profile['match_price_range_lower_actual']) || ( isset($profile['match_price_range_lower_actual']) && $profile['match_price_range_lower_actual'] == '' ) )
                                {
                                    if ( isset($profile['max_price_actual']) && $profile['max_price_actual'] != '' )
                                    {
                                        if ( $percentage_lower != '' )
                                        {
                                            $match_price_range_lower = $profile['max_price_actual'] - ( $profile['max_price_actual'] * ( $percentage_lower / 100 ) );
                                        }
                                    }
                                }
                                else
                                {
                                    $match_price_range_lower = $profile['match_price_range_lower_actual'];
                                }

                                $match_price_range_higher = '';
                                if ( !isset($profile['match_price_range_higher_actual']) || ( isset($profile['match_price_range_higher_actual']) && $profile['match_price_range_higher_actual'] == '' ) )
                                {
                                    if ( isset($profile['max_price_actual']) && $profile['max_price_actual'] != '' )
                                    {
                                        if ( $percentage_higher != '' )
                                        {
                                            $match_price_range_higher = $profile['max_price_actual'] + ( $profile['max_price_actual'] * ( $percentage_higher / 100 ) );
                                        }
                                    }
                                }
                                else
                                {
                                    $match_price_range_higher = $profile['match_price_range_higher_actual'];
                                }

                                if (
                                    !($match_price_range_lower == '' && $match_price_range_higher == '') && // Both bounds are not empty
                                    !(
                                        $price >= $match_price_range_lower &&
                                        $price <= $match_price_range_higher
                                    ) // Price is not within the bounds
                                ) {
                                    $match = false; // Assuming you want to set match to false; adjust as needed
                                }
                            }
                            else
                            {
                                if (
                                    isset($profile['max_price_actual']) &&
                                    $profile['max_price_actual'] !== '' && // Checks if max_price_actual is not an empty string
                                    $price > $profile['max_price_actual']  // Checks if price is greater than max_price_actual
                                )
                                {
                                    $match = false;
                                }
                            }
                        }
                    }
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                    if ( $has_department && ( $department_input == 'residential-lettings' || ph_get_custom_department_based_on($department_input) == 'residential-lettings' ) )
                    {
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                        if ( $maximum_rent_input !== '' && ph_clean($maximum_rent_input) != '' )
                        {
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            $price = preg_replace("/[^0-9.]/", '', ph_clean($maximum_rent_input));

                            if ( isset($profile['max_price_actual']) && $profile['max_price_actual'] != '' && $profile['max_price_actual'] != 0 && $profile['max_price_actual'] < $price )
                            {
                                $match = false;
                            }
                        }
                    }
                    if ( 
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                        $has_department &&
                        ( 
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            $department_input == 'residential-sales' ||
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            $department_input == 'residential-lettings' ||
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            ph_get_custom_department_based_on($department_input) == 'residential-sales' ||
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            ph_get_custom_department_based_on($department_input) == 'residential-lettings'
                        ) 
                    )
                    {
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                        if ( $minimum_bedrooms_input !== '' && ph_clean($minimum_bedrooms_input) != '' )
                        {
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                            $beds = preg_replace("/[^0-9.]/", '', ph_clean($minimum_bedrooms_input));

                            if ( isset($profile['min_beds']) && $profile['min_beds'] != '' && $profile['min_beds'] != 0 && $profile['min_beds'] > $beds )
                            {
                                $match = false;
                            }
                        }

                        // Property Types
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                        if ( $has_property_types && ! empty( $property_types_input ) )
                        {
                            $found_type = false;
                            foreach ( $search_property_types as $search_property_type )
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

                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only applicant filters and form display; CSV export separately verifies its nonce and CRM capability.
                    if ( $has_locations && ! empty( $locations_input ) )
                    {
                        $found_type = false;
                        foreach ( $search_locations as $search_location )
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

                    $match = apply_filters( 'propertyhive_applicant_list_check', $match, get_the_ID(), $profile );

                    if ( $match )
                    {
                        $contact =  new PH_Contact( get_the_ID() );

                        $results[] = array(
                            'contact_id' => get_the_ID(),
                            'applicant_profile_id' => $i,
                            'name' => get_the_title(),
                            'edit_link' => get_edit_post_link(get_the_ID()),
                            'telephone_number' => $contact->_telephone_number,
                            'email_address' => $contact->_email_address,
                            'address' => $contact->get_formatted_full_address(),
                            'profile' => $profile
                        );
                    }
                }
            }
        }

        wp_reset_postdata();

        return $results;
    }

    private function array_2_csv($results)
    {
		// export() verifies the nonce and capability before calling this formatter.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This private formatter only reads the already-authorized export filters.
		$request_post = wp_unslash( $_POST );
		$has_department = isset( $request_post['department'] ) && is_scalar( $request_post['department'] );
		$department_input = $has_department ? sanitize_text_field( $request_post['department'] ) : '';

        $locations = array();
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );

        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $locations[$term->term_id] = html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) );

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $locations[$term->term_id] = html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) );

                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                        
                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                        {
                            foreach ($subsubterms as $term)
                            {
                                $locations[$term->term_id] = html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) );
                            }
                        }
                    }
                }
            }
        }

        $property_types = array();
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );

        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $property_types[$term->term_id] = html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) );

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'property_type' ) ) );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $property_types[$term->term_id] = html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) );
                    }
                }
            }
        }

        $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
        $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

        ob_start();

        $df = fopen("php://output", 'w');

        $columns = array(
            'name' => __( 'Name', 'propertyhive' ),
            'email_address' => __( 'Email Address', 'propertyhive' ),
            'telephone_number' => __( 'Telephone Number', 'propertyhive' ),
            'address' => __( 'Address', 'propertyhive' ),
            'department' => __( 'Department', 'propertyhive' ),
        );

        if ( $has_department )
        {
            $department = ph_clean( $department_input );
            if ( ph_get_custom_department_based_on($department) !== FALSE )
            {
                $department = ph_get_custom_department_based_on($department);
            }
        }
        if ( isset($department) )
        {
            switch ( $department )
            {
                case "residential-sales":
                {
                    $columns['maximum_price'] = __( 'Maximum Price', 'propertyhive' );
                    if ( $percentage_lower != '' && $percentage_higher != '' ) {
                        $columns['maximum_price_range'] = __( 'Maximum Price Range', 'propertyhive' );
                    }
                    break;
                }
                case "residential-lettings":
                {
                    $columns['maximum_rent'] = __( 'Maximum Rent (PCM)', 'propertyhive' );
                    break;
                }
            }
            if ( $department == 'residential-sales' || $department == 'residential-lettings' )
            {
                $columns['currency'] = __( 'Currency', 'propertyhive' );
                $columns['minimum_bedrooms'] = __( 'Minimum Bedrooms', 'propertyhive' );
                $columns['property_types'] = __( 'Property Types', 'propertyhive' );
            }
        }
        
        $columns['locations'] = __( 'Locations', 'propertyhive' );
        $columns['additional_requirements'] = __( 'Additional Requirements', 'propertyhive' );

        // Keep the historical raw POST value as the filter contract for extensions.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Export nonce and capability are verified by export() before this private formatter is called; the raw value is retained for extension compatibility.
        $columns = apply_filters( 'propertyhive_export_applicant_list_columns', $columns, $_POST );

        fputcsv( $df, $columns, ',', '"', '' );

        foreach ($results as $result) 
        {
            $columns = array(
                'name' => $result['name'],
                'email_address' => $result['email_address'],
                'telephone_number' => $result['telephone_number'],
                'address' => $result['address'],
                'department' => ( isset($result['profile']['department']) ? propertyhive_get_department_label( $result['profile']['department'] ) : '-' ),
            );

            if ( isset($department) )
            {
                switch ( $department )
                {
                    case "residential-sales":
                    {
                        $columns['maximum_price'] = ( isset($result['profile']['max_price']) ? $result['profile']['max_price'] : '' );
                        if ( $percentage_lower != '' && $percentage_higher != '' ) {
                            $columns['maximum_price_range'] = '';
                        }

                        if ( !empty($columns['maximum_price']) )
                        {
                            if ( $percentage_lower != '' && $percentage_higher != '' )
                            {
                                $match_price_range_lower = '';
                                if ( !isset($result['profile']['match_price_range_lower_actual']) || ( isset($result['profile']['match_price_range_lower_actual']) && $result['profile']['match_price_range_lower_actual'] == '' ) )
                                {
                                    if ( isset($result['profile']['max_price']) && $result['profile']['max_price'] != '' )
                                    {
                                        $match_price_range_lower = (float) $result['profile']['max_price'] - ( (float) $result['profile']['max_price'] * ( (float) $percentage_lower / 100 ) );
                                    }
                                }
                                else
                                {
                                    $match_price_range_lower = $result['profile']['match_price_range_lower'];
                                }

                                $match_price_range_higher = '';
                                if ( !isset($result['profile']['match_price_range_higher_actual']) || ( isset($result['profile']['match_price_range_higher_actual']) && $result['profile']['match_price_range_higher_actual'] == '' ) )
                                {
                                    if ( isset($result['profile']['max_price']) && $result['profile']['max_price'] != '' )
                                    {
                                        $match_price_range_higher = (float) $result['profile']['max_price'] + ( (float) $result['profile']['max_price'] * ( (float) $percentage_higher / 100 ) );
                                    }
                                }
                                else
                                {
                                    $match_price_range_higher = $result['profile']['match_price_range_higher'];
                                }

                                if ( 
                                    $match_price_range_lower != '' && $match_price_range_higher != ''
                                )
                                {
                                    $columns['maximum_price_range'] = $match_price_range_lower . ' - ' . $match_price_range_higher;
                                }
                            }
                        }

                        $columns['currency'] = ( isset($result['profile']['currency']) ? $result['profile']['currency'] : 'GBP' );

                        break;
                    }
                    case "residential-lettings":
                    {
                        $columns['maximum_rent'] = ( isset($result['profile']['max_rent']) ? $result['profile']['max_rent'] : '' );
                        $columns['currency'] = ( isset($result['profile']['currency']) ? $result['profile']['currency'] : 'GBP' );
                        break;
                    }
                }
                if ( $department == 'residential-sales' || $department == 'residential-lettings' )
                {
                    $columns['minimum_bedrooms'] = ( isset($result['profile']['min_beds']) ? $result['profile']['min_beds'] : '' );

                    $output_types = array();
                    if ( isset($result['profile']['property_types']) && is_array($result['profile']['property_types']) && !empty($result['profile']['property_types']) )
                    {
                        foreach ( $result['profile']['property_types'] as $profile_type )
                        {
                            if ( is_scalar( $profile_type ) && isset( $property_types[$profile_type] ) ) {
                                $output_types[] = $property_types[$profile_type];
                            }
                        }
                    }
                    $columns['property_types'] = implode(", ", $output_types);
                }
            }
            
            $output_locations = array();
            if ( isset($result['profile']['locations']) && is_array($result['profile']['locations']) && !empty($result['profile']['locations']) )
            {
                foreach ( $result['profile']['locations'] as $profile_location )
                {
                    if ( is_scalar( $profile_location ) && isset( $locations[$profile_location] ) ) {
                        $output_locations[] = $locations[$profile_location];
                    }
                }
            }
            $columns['locations'] = implode(", ", $output_locations);

            $columns['additional_requirements'] = isset($result['profile']['notes']) ? $result['profile']['notes'] : '';          

            // Keep the historical raw POST value as the filter contract for extensions.
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Export nonce and capability are verified by export() before this private formatter is called; the raw value is retained for extension compatibility.
            $columns = apply_filters( 'propertyhive_export_applicant_list_row_data', $columns, $_POST, $result['contact_id'], $result['applicant_profile_id'] );

            fputcsv( $df, $columns, ',', '"', '' );
        }
        fclose($df); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closes the php://output CSV stream.

        return ob_get_clean();
    }

    public function export()
    {
        if ( !isset( $_POST['ph_applicant_export_nonce'] ) || ! check_admin_referer( 'ph_applicant_export', 'ph_applicant_export_nonce', false ) ) 
        {
            wp_die( esc_html__( 'Invalid request (nonce failure)', 'propertyhive' ), 403 );
        }

        if ( !current_user_can( 'manage_propertyhive' ) ) 
        {
            wp_die( esc_html__( 'Insufficient permissions', 'propertyhive' ), 403 );
        }

        $filename = 'applicant-list-' . gmdate("YmdHis") . '.csv';

        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

        $results = $this->generate_results();
        
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attachment response is CSV encoded by fputcsv, not HTML; HTML escaping would corrupt exported values.
        echo $this->array_2_csv($results);        

        die();
    }
}

endif;
