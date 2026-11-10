<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * PropertyHive Admin Generate Applicant List Class.
 *
 * Batched/AJAX version to keep memory usage bounded when handling large
 * applicant databases.
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PH_Admin_Applicant_List' ) ) :

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Admin_Applicant_List; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Admin_Applicant_List {

    const BATCH_SIZE = 100;

    /**
     * Main settings page output.
     */
    public function output()
    {
        // Applicant filters are read-only; the export endpoint verifies its nonce and capability.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This request only repopulates the read-only filter form and renders its results.
        $request_post = wp_unslash( $_POST );

        $has_department_input = isset( $request_post['department'] ) && is_scalar( $request_post['department'] );
        $department_input = $has_department_input ? sanitize_text_field( $request_post['department'] ) : '';
        $maximum_price_input = ( isset( $request_post['maximum_price'] ) && is_scalar( $request_post['maximum_price'] ) ) ? sanitize_text_field( $request_post['maximum_price'] ) : '';
        $maximum_rent_input = ( isset( $request_post['maximum_rent'] ) && is_scalar( $request_post['maximum_rent'] ) ) ? sanitize_text_field( $request_post['maximum_rent'] ) : '';
        $minimum_bedrooms_input = ( isset( $request_post['minimum_bedrooms'] ) && is_scalar( $request_post['minimum_bedrooms'] ) ) ? sanitize_text_field( $request_post['minimum_bedrooms'] ) : '';

        $selected_property_types = array();
        if ( isset( $request_post['property_types'] ) && is_array( $request_post['property_types'] ) )
        {
            foreach ( $request_post['property_types'] as $property_type_input )
            {
                if ( is_scalar( $property_type_input ) )
                {
                    $selected_property_types[] = absint( $property_type_input );
                }
            }
        }

        $selected_locations = array();
        if ( isset( $request_post['locations'] ) && is_array( $request_post['locations'] ) )
        {
            foreach ( $request_post['locations'] as $location_input )
            {
                if ( is_scalar( $location_input ) )
                {
                    $selected_locations[] = absint( $location_input );
                }
            }
        }

        $include_non_send_matching_properties_input = (
            isset( $request_post['include_non_send_matching_properties'] ) &&
            is_scalar( $request_post['include_non_send_matching_properties'] )
        ) ? sanitize_text_field( $request_post['include_non_send_matching_properties'] ) : '';

        $property_types = $this->get_taxonomy_term_map( 'property_type' );
        $locations      = $this->get_taxonomy_term_map( 'location' );
        ?>
        <div class="wrap propertyhive">

            <h1><?php echo esc_html__( 'Generate Applicant List', 'propertyhive' ); ?></h1>

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
                                if ( get_option( 'propertyhive_active_departments_' . str_replace( 'residential-', '', $key ) ) == 'yes' )
                                {
                                    $department_options[$key] = $value;
                                }
                            }

                            foreach ( $department_options as $key => $department )
                            {
                                echo '<option value="' . esc_attr( $key ) . '"';

                                if ( $has_department_input && $department_input == $key )
                                {
                                    echo ' selected';
                                }
                                elseif ( ! $has_department_input && $key == get_option( 'propertyhive_primary_department' ) )
                                {
                                    echo ' selected';
                                }

                                echo '>' . esc_html( $department ) . '</option>';
                            }
                            ?>
                        </select>
                    </p>

                    <p class="form-field sales-only">
                        <label>
                            <?php echo esc_html__( 'Maximum Price', 'propertyhive' ); ?>
                            <img class="help_tip" data-tip="This will search the applicant's Match Price Range if one is set and return applicants where the price entered falls into this range. Otherwise it will search the Maximum Price and return applicants that have maximum price higher than the value entered" src="<?php echo esc_url( PH()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
                        </label>
                        <input type="text" name="maximum_price" value="<?php echo esc_attr( $maximum_price_input ); ?>">
                    </p>

                    <p class="form-field lettings-only">
                        <label><?php echo esc_html__( 'Maximum Rent (PCM)', 'propertyhive' ); ?></label>
                        <input type="text" name="maximum_rent" value="<?php echo esc_attr( $maximum_rent_input ); ?>">
                    </p>

                    <p class="form-field residential-only">
                        <label><?php echo esc_html__( 'Minimum Bedrooms', 'propertyhive' ); ?></label>
                        <input type="number" name="minimum_bedrooms" class="short" value="<?php echo esc_attr( $minimum_bedrooms_input ); ?>">
                    </p>

                    <p class="form-field residential-only">
                        <label><?php echo esc_html__( 'Property Types', 'propertyhive' ); ?></label>
                        <select id="property_types" name="property_types[]" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Start typing to add property types', 'propertyhive' ); ?>..." class="multiselect attribute_values">
                            <?php

                            $this->render_taxonomy_options( 'property_type', 0, $selected_property_types );
                            ?>
                        </select>
                    </p>

                    <p class="form-field">
                        <label><?php echo esc_html__( 'Location', 'propertyhive' ); ?></label>
                        <select id="locations" name="locations[]" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Start typing to add locations', 'propertyhive' ); ?>..." class="multiselect attribute_values">
                            <?php

                            $this->render_taxonomy_options( 'location', 0, $selected_locations );
                            ?>
                        </select>
                    </p>

                    <p class="form-field">
                        <label><?php echo esc_html__( 'Include Applicants with \'Send Matching Properties\' Unticked', 'propertyhive' ); ?></label>
                        <input type="checkbox" name="include_non_send_matching_properties" value="yes"<?php checked( $include_non_send_matching_properties_input === 'yes' ); ?>>
                    </p>

                    <?php do_action( 'propertyhive_applicant_list_additional_fields' ); ?>

                    <p class="form-field">
                        <input type="submit" value="<?php echo esc_attr__( 'Generate Applicant List', 'propertyhive' ); ?>" class="button-primary" id="generate_applicant_list_button">
                        <a href="" class="button" id="export_applicant_list_results_button"><?php echo esc_html__( 'Export To CSV', 'propertyhive' ); ?></a>
                        <input type="hidden" name="export_applicant_list_results" value="">
                    </p>

                </div>
            </form>

            <div id="applicant-list-progress" style="display:none; margin:20px 0;">
                <h3 style="margin-bottom:8px;">
                    <?php echo esc_html__( 'Searching applicants...', 'propertyhive' ); ?>
                    <span id="applicant-list-progress-count">0</span>
                    <?php echo esc_html__( 'processed', 'propertyhive' ); ?>
                </h3>
                <progress id="applicant-list-progress-bar" style="width:100%;"></progress>
            </div>

            <div id="applicant-list-error" class="notice notice-error" style="display:none;"><p></p></div>

            <div id="applicant-list-results-wrapper" class="applicant-list-results" style="display:none;">
                <br>
                <h3>
                    <span id="applicant-list-match-count">0</span>
                    <?php echo esc_html__( 'Applicants Found Matching Your Criteria', 'propertyhive' ); ?>
                </h3>

                <table width="100%" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="text-align:left;"><?php echo esc_html__( 'Applicant Name', 'propertyhive' ); ?></th>
                            <th style="text-align:left;"><?php echo esc_html__( 'Contact Details', 'propertyhive' ); ?></th>
                            <th style="text-align:left;"><?php echo esc_html__( 'Requirements', 'propertyhive' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="applicant-list-results-body"></tbody>
                </table>
            </div>

        </div>

        <script>
        var custom_departments = <?php echo wp_json_encode( ph_get_custom_departments() ); ?>;

        function toggleDepartmentFields()
        {
            if (jQuery('#mainform').length > 0)
            {
                jQuery('#mainform').each(function()
                {
                    var selectedDepartment = 'residential-sales';
                    var selected = jQuery(this).find('[name="department"]');
                    var display = 'block';

                    jQuery(this).find('.sales-only').hide();
                    jQuery(this).find('.lettings-only').hide();
                    jQuery(this).find('.residential-only').hide();
                    jQuery(this).find('.commercial-only').hide();

                    if (selected.length > 0)
                    {
                        selectedDepartment = selected.val();

                        if (
                            selectedDepartment == 'residential-sales' ||
                            (custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == 'residential-sales')
                        )
                        {
                            jQuery(this).find('.sales-only').css('display', display);
                            jQuery(this).find('.residential-only').css('display', display);
                        }
                        else if (
                            selectedDepartment == 'residential-lettings' ||
                            (custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == 'residential-lettings')
                        )
                        {
                            jQuery(this).find('.lettings-only').css('display', display);
                            jQuery(this).find('.residential-only').css('display', display);
                        }
                        else if (
                            selectedDepartment == 'commercial' ||
                            (custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == 'commercial')
                        )
                        {
                            jQuery(this).find('.commercial-only').css('display', display);
                        }
                    }
                });
            }
        }

        jQuery(function($) {

            $('#mainform select.multiselect').chosen();
            toggleDepartmentFields();

            $('#mainform [name="department"]').on('change', function() {
                toggleDepartmentFields();
            });

            var applicantSearchRunning = false;

            $('#mainform').on('submit', function(e) {

                // CSV export is still a normal POST/download request.
                if ($('input[name="export_applicant_list_results"]').val() === '1') {
                    return true;
                }

                e.preventDefault();

                if (applicantSearchRunning) {
                    return false;
                }

                applicantSearchRunning = true;

                var $form = $(this);
                var $submitButton = $('#generate_applicant_list_button');
                var offset = 0;
                var totalMatches = 0;
                var totalProcessed = 0;

                $('#applicant-list-results-body').empty();
                $('#applicant-list-match-count').text('0');
                $('#applicant-list-progress-count').text('0');
                $('#applicant-list-error').hide().find('p').empty();
                $('#applicant-list-progress').show();
                $('#applicant-list-results-wrapper').show();

                $submitButton
                    .prop('disabled', true)
                    .val('<?php echo esc_js( __( 'Generating Applicant List...', 'propertyhive' ) ); ?>');

                function finishSearch() {
                    applicantSearchRunning = false;
                    $('#applicant-list-progress').hide();
                    $submitButton
                        .prop('disabled', false)
                        .val('<?php echo esc_js( __( 'Generate Applicant List', 'propertyhive' ) ); ?>');
                }

                function showError(message) {
                    $('#applicant-list-error').show().find('p').text(message);
                    finishSearch();
                }

                function processBatch() {
                    var data = $form.serializeArray();

                    data.push({ name: 'action', value: 'propertyhive_generate_applicant_list_batch' });
                    data.push({ name: 'batch_offset', value: offset });

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: data
                    })
                    .done(function(response) {

                        if (!response || !response.success) {
                            var message = '<?php echo esc_js( __( 'An error occurred generating the applicant list.', 'propertyhive' ) ); ?>';
                            if (response && response.data && response.data.message) {
                                message = response.data.message;
                            }
                            showError(message);
                            return;
                        }

                        var batch = response.data;

                        if (batch.html) {
                            $('#applicant-list-results-body').append(batch.html);
                        }

                        totalMatches += parseInt(batch.matches || 0, 10);
                        totalProcessed += parseInt(batch.processed || 0, 10);

                        $('#applicant-list-match-count').text(totalMatches.toLocaleString());
                        $('#applicant-list-progress-count').text(totalProcessed.toLocaleString());

                        if (batch.has_more) {
                            offset = parseInt(batch.next_offset, 10);
                            window.setTimeout(processBatch, 25);
                        }
                        else {
                            finishSearch();

                            if (totalMatches === 0) {
                                $('#applicant-list-results-body').html(
                                    '<tr><td colspan="3" style="text-align:center;">' +
                                    <?php echo wp_json_encode( __( 'No matching applicants found', 'propertyhive' ) ); ?> +
                                    '</td></tr>'
                                );
                            }
                        }
                    })
                    .fail(function(xhr) {
                        var message = '<?php echo esc_js( __( 'The applicant search request failed. Any completed batches remain displayed.', 'propertyhive' ) ); ?>';

                        if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            message = xhr.responseJSON.data.message;
                        }

                        showError(message);
                    });
                }

                processBatch();
                return false;
            });

            $('#export_applicant_list_results_button').on('click', function(e) {
                e.preventDefault();

                if (applicantSearchRunning) {
                    return false;
                }

                $('input[name="export_applicant_list_results"]').val('1');
                $('#mainform').trigger('submit');

                window.setTimeout(function() {
                    $('input[name="export_applicant_list_results"]').val('');
                }, 1000);
            });
        });

        jQuery(window).on('resize', function() {
            toggleDepartmentFields();
        });
        </script>
        <?php
    }

    /**
     * AJAX endpoint. Processes one bounded batch and returns rendered rows.
     */
    public function ajax_generate_results_batch()
    {
        if (
            ! isset( $_POST['ph_applicant_export_nonce'] ) ||
            ! wp_verify_nonce(
                sanitize_text_field( wp_unslash( $_POST['ph_applicant_export_nonce'] ) ),
                'ph_applicant_export'
            )
        )
        {
            wp_send_json_error(
                array( 'message' => __( 'Invalid request (nonce failure)', 'propertyhive' ) ),
                403
            );
        }

        if ( ! current_user_can( 'manage_propertyhive' ) )
        {
            wp_send_json_error(
                array( 'message' => __( 'Insufficient permissions', 'propertyhive' ) ),
                403
            );
        }

        $offset = isset( $_POST['batch_offset'] ) ? max( 0, absint( $_POST['batch_offset'] ) ) : 0;

        $batch = $this->generate_results_batch( $offset, self::BATCH_SIZE );
        $html  = $this->render_result_rows( $batch['results'] );

        wp_send_json_success(
            array(
                'html'        => $html,
                'matches'     => count( $batch['results'] ),
                'processed'   => $batch['processed'],
                'has_more'    => $batch['has_more'],
                'next_offset' => $offset + $batch['processed'],
            )
        );
    }

    /**
     * Generate one batch of matching applicant profile results.
     *
     * We fetch batch_size + 1 IDs. The extra ID is only used to determine
     * whether another batch exists, avoiding a costly total-row count query.
     */
    public function generate_results_batch( $offset = 0, $batch_size = self::BATCH_SIZE )
    {
        $offset     = max( 0, (int) $offset );
        $batch_size = max( 1, (int) $batch_size );
        $context    = $this->build_search_context();

        $args = array(
            'post_type'              => 'contact',
            'fields'                 => 'ids',
            'posts_per_page'         => $batch_size + 1,
            'offset'                 => $offset,
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
            'meta_query'             => array(
                array(
                    'key'     => '_contact_types',
                    'value'   => 'applicant',
                    'compare' => 'LIKE',
                ),
            ),
        );

        $applicant_query = new WP_Query( $args );
        $applicant_ids   = array_map( 'intval', $applicant_query->posts );
        $has_more        = count( $applicant_ids ) > $batch_size;

        if ( $has_more )
        {
            $applicant_ids = array_slice( $applicant_ids, 0, $batch_size );
        }

        $processed = count( $applicant_ids );
        $results   = array();

        if ( empty( $applicant_ids ) )
        {
            return array(
                'results'   => array(),
                'processed' => 0,
                'has_more'  => false,
            );
        }

        // Bulk prime only this batch's metadata into memory.
        update_meta_cache( 'post', $applicant_ids );

        foreach ( $applicant_ids as $contact_id )
        {
            $num_applicant_profiles = (int) get_post_meta( $contact_id, '_applicant_profiles', true );

            if ( $num_applicant_profiles <= 0 )
            {
                continue;
            }

            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                $profile = get_post_meta( $contact_id, '_applicant_profile_' . $i, true );

                if ( ! is_array( $profile ) )
                {
                    continue;
                }

                if ( ! $this->profile_matches( $profile, $contact_id, $context ) )
                {
                    continue;
                }

                $contact = new PH_Contact( $contact_id );

                $results[] = array(
                    'contact_id'           => $contact_id,
                    'applicant_profile_id' => $i,
                    'name'                 => get_the_title( $contact_id ),
                    'edit_link'            => get_edit_post_link( $contact_id ),
                    'telephone_number'     => $contact->_telephone_number,
                    'email_address'        => $contact->_email_address,
                    'address'              => $contact->get_formatted_full_address(),
                    'profile'              => $profile,
                );
            }
        }

        return array(
            'results'   => $results,
            'processed' => $processed,
            'has_more'  => $has_more,
        );
    }

    /**
     * Build request-dependent matching context once per batch.
     */
    private function build_search_context()
    {
        // Results are read-only. Normalize the submitted filters before they are used in comparisons or queries.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This method only reads applicant filters; the separate export endpoint verifies its nonce and capability.
        $request_post = wp_unslash( $_POST );

        $has_department = isset( $request_post['department'] ) && is_scalar( $request_post['department'] );
        $department = $has_department ? sanitize_text_field( $request_post['department'] ) : '';

        $base_department = '';
        if ( $department !== '' )
        {
            $custom_base = ph_get_custom_department_based_on( $department );
            $base_department = ( $custom_base !== false ) ? $custom_base : $department;
        }

        $is_sales       = ( $department === 'residential-sales' || $base_department === 'residential-sales' );
        $is_lettings    = ( $department === 'residential-lettings' || $base_department === 'residential-lettings' );
        $is_residential = ( $is_sales || $is_lettings );

        $selected_property_types = array();
        if ( isset( $request_post['property_types'] ) && is_array( $request_post['property_types'] ) )
        {
            foreach ( $request_post['property_types'] as $property_type )
            {
                if ( is_scalar( $property_type ) )
                {
                    $selected_property_types[] = absint( $property_type );
                }
            }
            $selected_property_types = array_values( array_unique( $selected_property_types ) );
        }

        $selected_locations = array();
        if ( isset( $request_post['locations'] ) && is_array( $request_post['locations'] ) )
        {
            foreach ( $request_post['locations'] as $location )
            {
                if ( is_scalar( $location ) )
                {
                    $selected_locations[] = absint( $location );
                }
            }
            $selected_locations = array_values( array_unique( $selected_locations ) );
        }

        $search_property_types = array();
        if ( $is_residential && ! empty( $selected_property_types ) )
        {
            $search_property_types = $this->expand_taxonomy_terms( 'property_type', $selected_property_types );
        }

        $search_locations = array();
        if ( ! empty( $selected_locations ) )
        {
            $search_locations = $this->expand_taxonomy_terms( 'location', $selected_locations );
        }

        $maximum_price = ( isset( $request_post['maximum_price'] ) && is_scalar( $request_post['maximum_price'] ) )
            ? sanitize_text_field( $request_post['maximum_price'] )
            : '';

        $maximum_rent = ( isset( $request_post['maximum_rent'] ) && is_scalar( $request_post['maximum_rent'] ) )
            ? sanitize_text_field( $request_post['maximum_rent'] )
            : '';

        $minimum_beds = ( isset( $request_post['minimum_bedrooms'] ) && is_scalar( $request_post['minimum_bedrooms'] ) )
            ? sanitize_text_field( $request_post['minimum_bedrooms'] )
            : '';

        return array(
            'department'          => $department,
            'is_sales'            => $is_sales,
            'is_lettings'         => $is_lettings,
            'is_residential'      => $is_residential,
            'include_non_send'    => isset( $request_post['include_non_send_matching_properties'] ),
            'has_property_filter' => ! empty( $selected_property_types ),
            'has_location_filter' => ! empty( $selected_locations ),
            'property_lookup'     => array_fill_keys( array_map( 'intval', $search_property_types ), true ),
            'location_lookup'     => array_fill_keys( array_map( 'intval', $search_locations ), true ),
            'price'               => ( $is_sales && $maximum_price !== '' ) ? (float) preg_replace( '/[^0-9.]/', '', ph_clean( $maximum_price ) ) : null,
            'rent'                => ( $is_lettings && $maximum_rent !== '' ) ? (float) preg_replace( '/[^0-9.]/', '', ph_clean( $maximum_rent ) ) : null,
            'beds'                => ( $is_residential && $minimum_beds !== '' ) ? (float) preg_replace( '/[^0-9.]/', '', ph_clean( $minimum_beds ) ) : null,
            'percentage_lower'    => get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' ),
            'percentage_higher'   => get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' ),
        );
    }

    /**
     * Check one profile against the request criteria.
     */
    private function profile_matches( $profile, $contact_id, $context )
    {
        if (
            ! $context['include_non_send'] &&
            ( ! isset( $profile['send_matching_properties'] ) || $profile['send_matching_properties'] !== 'yes' )
        )
        {
            return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
        }

        if (
            $context['department'] !== '' &&
            isset( $profile['department'] ) &&
            $profile['department'] !== $context['department']
        )
        {
            return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
        }

        if ( $context['is_sales'] && $context['price'] !== null )
        {
            $price = $context['price'];
            $percentage_lower  = $context['percentage_lower'];
            $percentage_higher = $context['percentage_higher'];

            if ( $percentage_lower !== '' && $percentage_higher !== '' )
            {
                $range_lower  = '';
                $range_higher = '';

                if (
                    ! isset( $profile['match_price_range_lower_actual'] ) ||
                    $profile['match_price_range_lower_actual'] === ''
                )
                {
                    if ( isset( $profile['max_price_actual'] ) && $profile['max_price_actual'] !== '' )
                    {
                        $max_price_actual = (float) $profile['max_price_actual'];
                        $range_lower = $max_price_actual - ( $max_price_actual * ( (float) $percentage_lower / 100 ) );
                    }
                }
                else
                {
                    $range_lower = (float) $profile['match_price_range_lower_actual'];
                }

                if (
                    ! isset( $profile['match_price_range_higher_actual'] ) ||
                    $profile['match_price_range_higher_actual'] === ''
                )
                {
                    if ( isset( $profile['max_price_actual'] ) && $profile['max_price_actual'] !== '' )
                    {
                        $max_price_actual = (float) $profile['max_price_actual'];
                        $range_higher = $max_price_actual + ( $max_price_actual * ( (float) $percentage_higher / 100 ) );
                    }
                }
                else
                {
                    $range_higher = (float) $profile['match_price_range_higher_actual'];
                }

                if (
                    ! ( $range_lower === '' && $range_higher === '' ) &&
                    ! ( $price >= $range_lower && $price <= $range_higher )
                )
                {
                    return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
                }
            }
            elseif (
                isset( $profile['max_price_actual'] ) &&
                $profile['max_price_actual'] !== '' &&
                $price > (float) $profile['max_price_actual']
            )
            {
                return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
            }
        }

        if ( $context['is_lettings'] && $context['rent'] !== null )
        {
            if (
                isset( $profile['max_price_actual'] ) &&
                $profile['max_price_actual'] !== '' &&
                (float) $profile['max_price_actual'] != 0 &&
                (float) $profile['max_price_actual'] < $context['rent']
            )
            {
                return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
            }
        }

        if ( $context['is_residential'] && $context['beds'] !== null )
        {
            if (
                isset( $profile['min_beds'] ) &&
                $profile['min_beds'] !== '' &&
                (float) $profile['min_beds'] != 0 &&
                (float) $profile['min_beds'] > $context['beds']
            )
            {
                return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
            }
        }

        if ( $context['is_residential'] && $context['has_property_filter'] )
        {
            $found = false;

            if ( isset( $profile['property_types'] ) && is_array( $profile['property_types'] ) )
            {
                foreach ( $profile['property_types'] as $profile_type )
                {
                    if ( isset( $context['property_lookup'][(int) $profile_type] ) )
                    {
                        $found = true;
                        break;
                    }
                }
            }

            if ( ! $found )
            {
                return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
            }
        }

        if ( $context['has_location_filter'] )
        {
            $found = false;

            if ( isset( $profile['locations'] ) && is_array( $profile['locations'] ) )
            {
                foreach ( $profile['locations'] as $profile_location )
                {
                    if ( isset( $context['location_lookup'][(int) $profile_location] ) )
                    {
                        $found = true;
                        break;
                    }
                }
            }

            if ( ! $found )
            {
                return (bool) apply_filters( 'propertyhive_applicant_list_check', false, $contact_id, $profile );
            }
        }

        return (bool) apply_filters(
            'propertyhive_applicant_list_check',
            true,
            $contact_id,
            $profile
        );
    }

    /**
     * Render rows for an AJAX batch.
     */
    private function render_result_rows( $results )
    {
        if ( empty( $results ) )
        {
            return '';
        }

        $property_types    = $this->get_taxonomy_term_map( 'property_type' );
        $locations         = $this->get_taxonomy_term_map( 'location' );
        $percentage_lower  = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
        $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

        ob_start();

        foreach ( $results as $result )
        {
            $currency = $this->get_currency_symbol( $result['profile'] );
            ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url( $result['edit_link'] ); ?>" target="_blank">
                        <?php echo esc_html( $result['name'] ); ?>
                    </a>
                </td>
                <td>
                    <?php
                    $contact_details = array();

                    if ( $result['telephone_number'] !== '' )
                    {
                        $contact_details[] = 'T: ' . esc_html( $result['telephone_number'] );
                    }

                    if ( $result['email_address'] !== '' )
                    {
                        $contact_details[] = 'E: ' . esc_html( $result['email_address'] );
                    }

                    echo ! empty( $contact_details ) ? implode( '<br>', $contact_details ) : '-';
                    ?>
                </td>
                <td>
                    <?php
                    echo $this->render_profile_requirements(
                        $result['profile'],
                        $property_types,
                        $locations,
                        $currency,
                        $percentage_lower,
                        $percentage_higher
                    );
                    ?>
                </td>
            </tr>
            <?php
        }

        return ob_get_clean();
    }

    /**
     * Render the requirements column, preserving the original page output.
     */
    private function render_profile_requirements( $profile, $property_types, $locations, $currency, $percentage_lower, $percentage_higher )
    {
        $output = array();

        if ( ! isset( $profile['department'] ) )
        {
            return '-';
        }

        switch ( $profile['department'] )
        {
            case 'residential-sales':
                if ( isset( $profile['max_price'] ) && $profile['max_price'] != '' && $profile['max_price'] != 0 )
                {
                    $output[] = '<strong>' . esc_html__( 'Max Price:', 'propertyhive' ) . '</strong> ' . $currency . esc_html( ph_display_price_field( $profile['max_price'] ) );

                    if ( $percentage_lower !== '' && $percentage_higher !== '' )
                    {
                        $range_lower  = '';
                        $range_higher = '';

                        if (
                            ! isset( $profile['match_price_range_lower_actual'] ) ||
                            $profile['match_price_range_lower_actual'] === ''
                        )
                        {
                            if ( isset( $profile['max_price'] ) && $profile['max_price'] !== '' )
                            {
                                $range_lower = $profile['max_price'] - ( $profile['max_price'] * ( $percentage_lower / 100 ) );
                            }
                        }
                        elseif ( isset( $profile['match_price_range_lower'] ) )
                        {
                            $range_lower = $profile['match_price_range_lower'];
                        }

                        if (
                            ! isset( $profile['match_price_range_higher_actual'] ) ||
                            $profile['match_price_range_higher_actual'] === ''
                        )
                        {
                            if ( isset( $profile['max_price'] ) && $profile['max_price'] !== '' )
                            {
                                $range_higher = $profile['max_price'] + ( $profile['max_price'] * ( $percentage_higher / 100 ) );
                            }
                        }
                        elseif ( isset( $profile['match_price_range_higher'] ) )
                        {
                            $range_higher = $profile['match_price_range_higher'];
                        }

                        if ( $range_lower !== '' && $range_higher !== '' )
                        {
                            $output[] = '<strong>' . esc_html__( 'Max Price Range:', 'propertyhive' ) . '</strong> ' . $currency . esc_html( ph_display_price_field( $range_lower ) ) . ' to ' . $currency . esc_html( ph_display_price_field( $range_higher ) );
                        }
                    }
                }
                break;

            case 'residential-lettings':
                if ( isset( $profile['max_rent'] ) && $profile['max_rent'] != '' && $profile['max_rent'] != 0 )
                {
                    $rent_frequency = isset( $profile['rent_frequency'] ) ? $profile['rent_frequency'] : '';
                    $output[] = '<strong>' . esc_html__( 'Max Rent:', 'propertyhive' ) . '</strong> ' . $currency . esc_html( ph_display_price_field( $profile['max_rent'] ) . $rent_frequency );
                }
                break;
        }

        if ( in_array( $profile['department'], array( 'residential-sales', 'residential-lettings' ), true ) )
        {
            if ( isset( $profile['min_beds'] ) && $profile['min_beds'] != '' && $profile['min_beds'] != 0 )
            {
                $output[] = '<strong>' . esc_html__( 'Min Beds:', 'propertyhive' ) . '</strong> ' . esc_html( number_format( $profile['min_beds'] ) );
            }

            if ( isset( $profile['property_types'] ) && is_array( $profile['property_types'] ) && ! empty( $profile['property_types'] ) )
            {
                $output_types = array();

                foreach ( $profile['property_types'] as $profile_type )
                {
                    if ( isset( $property_types[$profile_type] ) )
                    {
                        $output_types[] = $property_types[$profile_type];
                    }
                }

                if ( ! empty( $output_types ) )
                {
                    $output[] = '<strong>' . esc_html__( 'Property Types:', 'propertyhive' ) . '</strong> ' . esc_html( implode( ', ', $output_types ) );
                }
            }
        }

        if ( isset( $profile['locations'] ) && is_array( $profile['locations'] ) && ! empty( $profile['locations'] ) )
        {
            $output_locations = array();

            foreach ( $profile['locations'] as $profile_location )
            {
                if ( isset( $locations[$profile_location] ) )
                {
                    $output_locations[] = $locations[$profile_location];
                }
            }

            if ( ! empty( $output_locations ) )
            {
                $output[] = '<strong>' . esc_html__( 'Locations:', 'propertyhive' ) . '</strong> ' . esc_html( implode( ', ', $output_locations ) );
            }
        }

        if ( isset( $profile['notes'] ) && $profile['notes'] !== '' )
        {
            $output[] = '<strong>' . esc_html__( 'Additional Requirements:', 'propertyhive' ) . '</strong> ' . nl2br( esc_html( $profile['notes'] ) );
        }

        return ! empty( $output ) ? implode( '<br>', $output ) : '-';
    }

    /**
     * Stream CSV output batch by batch instead of building all results in memory.
     */
    public function export()
    {
        if (
            ! isset( $_POST['ph_applicant_export_nonce'] ) ||
            ! check_admin_referer( 'ph_applicant_export', 'ph_applicant_export_nonce', false )
        )
        {
            wp_die( esc_html__( 'Invalid request (nonce failure)', 'propertyhive' ), 403 );
        }

        if ( ! current_user_can( 'manage_propertyhive' ) )
        {
            wp_die( esc_html__( 'Insufficient permissions', 'propertyhive' ), 403 );
        }

        $filename = 'applicant-list-' . gmdate( 'YmdHis' ) . '.csv';
        $now      = gmdate( 'D, d M Y H:i:s' );

        nocache_headers();
        header( 'Expires: Tue, 03 Jul 2001 06:00:00 GMT' );
        header( 'Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate' );
        header( "Last-Modified: {$now} GMT" );
        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Transfer-Encoding: binary' );

        // Prevent existing output buffers from retaining the entire CSV.
        while ( ob_get_level() > 0 )
        {
            ob_end_clean();
        }

        $out = fopen( 'php://output', 'w' );

        if ( false === $out )
        {
            wp_die( esc_html__( 'Unable to open CSV output stream', 'propertyhive' ) );
        }

        // UTF-8 BOM helps Excel recognise UTF-8 content.
        fwrite( $out, "\xEF\xBB\xBF" );

        $csv_context = $this->get_csv_context();
        fputcsv( $out, $csv_context['columns'], ',', '"', '' );

        $offset = 0;

        do
        {
            $batch = $this->generate_results_batch( $offset, self::BATCH_SIZE );
            $processed = (int) $batch['processed'];
            $has_more  = (bool) $batch['has_more'];

            foreach ( $batch['results'] as $result )
            {
                $row = $this->get_csv_row( $result, $csv_context );				
                fputcsv( $out, $row, ',', '"', '' );
            }

            $offset += $processed;

            // Release this batch before loading the next one.
            unset( $batch, $row );

            if ( function_exists( 'wp_cache_flush_runtime' ) )
            {
                wp_cache_flush_runtime();
            }

            if ( function_exists( 'flush' ) )
            {
                flush();
            }

        } while ( $has_more && $processed > 0 );

        fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closes the php://output CSV stream.
        exit;
    }

    /**
     * CSV context: headers and taxonomy maps loaded once for the export request.
     */
    private function get_csv_context()
    {
        $property_types = $this->get_taxonomy_term_map( 'property_type' );
        $locations      = $this->get_taxonomy_term_map( 'location' );

        foreach ( $property_types as $term_id => $term_name )
        {
            $property_types[$term_id] = html_entity_decode( $term_name, ENT_QUOTES, get_bloginfo( 'charset' ) );
        }

        foreach ( $locations as $term_id => $term_name )
        {
            $locations[$term_id] = html_entity_decode( $term_name, ENT_QUOTES, get_bloginfo( 'charset' ) );
        }

        // Export nonce/capability are checked in export(); normalise the filter value here.
        $request_post = wp_unslash( $_POST );
        $department = ( isset( $request_post['department'] ) && is_scalar( $request_post['department'] ) )
            ? sanitize_text_field( $request_post['department'] )
            : '';
        $base_department = $department;

        if ( $department !== '' )
        {
            $custom_department = ph_get_custom_department_based_on( $department );
            if ( $custom_department !== false )
            {
                $base_department = $custom_department;
            }
        }

        $columns = array(
            'name'             => __( 'Name', 'propertyhive' ),
            'email_address'    => __( 'Email Address', 'propertyhive' ),
            'telephone_number' => __( 'Telephone Number', 'propertyhive' ),
            'address'          => __( 'Address', 'propertyhive' ),
            'department'       => __( 'Department', 'propertyhive' ),
        );

        if ( $base_department === 'residential-sales' )
        {
            $columns['maximum_price'] = __( 'Maximum Price', 'propertyhive' );

            if (
                get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' ) !== '' &&
                get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' ) !== ''
            )
            {
                $columns['maximum_price_range'] = __( 'Maximum Price Range', 'propertyhive' );
            }
        }
        elseif ( $base_department === 'residential-lettings' )
        {
            $columns['maximum_rent'] = __( 'Maximum Rent (PCM)', 'propertyhive' );
        }

        if ( in_array( $base_department, array( 'residential-sales', 'residential-lettings' ), true ) )
        {
            $columns['currency']         = __( 'Currency', 'propertyhive' );
            $columns['minimum_bedrooms'] = __( 'Minimum Bedrooms', 'propertyhive' );
            $columns['property_types']   = __( 'Property Types', 'propertyhive' );
        }

        $columns['locations']               = __( 'Locations', 'propertyhive' );
        $columns['additional_requirements'] = __( 'Additional Requirements', 'propertyhive' );

        // Keep the historical raw POST value as the filter contract for extensions.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Export nonce and capability are verified by export() before this formatter is called.
        $columns = apply_filters( 'propertyhive_export_applicant_list_columns', $columns, $_POST );

        return array(
            'department'        => $base_department,
            'columns'           => $columns,
            'column_keys'       => array_keys( $columns ),
            'property_types'    => $property_types,
            'locations'         => $locations,
            'percentage_lower'  => get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' ),
            'percentage_higher' => get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' ),
        );
    }

    /**
     * Build one CSV row and then align it to the filtered column order.
     */
    private function get_csv_row( $result, $context )
    {
        $profile = $result['profile'];

        $row = array(
            'name'             => $result['name'],
            'email_address'    => $result['email_address'],
            'telephone_number' => $result['telephone_number'],
            'address'          => $result['address'],
            'department'       => isset( $profile['department'] ) ? propertyhive_get_department_label( $profile['department'] ) : '-',
        );

        if ( $context['department'] === 'residential-sales' )
        {
            $row['maximum_price'] = isset( $profile['max_price'] ) ? $profile['max_price'] : '';

            if ( in_array( 'maximum_price_range', $context['column_keys'], true ) )
            {
                $range = '';

                if ( ! empty( $row['maximum_price'] ) )
                {
                    $range_lower  = '';
                    $range_higher = '';

                    if (
                        ! isset( $profile['match_price_range_lower_actual'] ) ||
                        $profile['match_price_range_lower_actual'] === ''
                    )
                    {
                        $range_lower = $row['maximum_price'] - ( $row['maximum_price'] * ( $context['percentage_lower'] / 100 ) );
                    }
                    elseif ( isset( $profile['match_price_range_lower'] ) )
                    {
                        $range_lower = $profile['match_price_range_lower'];
                    }

                    if (
                        ! isset( $profile['match_price_range_higher_actual'] ) ||
                        $profile['match_price_range_higher_actual'] === ''
                    )
                    {
                        $range_higher = $row['maximum_price'] + ( $row['maximum_price'] * ( $context['percentage_higher'] / 100 ) );
                    }
                    elseif ( isset( $profile['match_price_range_higher'] ) )
                    {
                        $range_higher = $profile['match_price_range_higher'];
                    }

                    if ( $range_lower !== '' && $range_higher !== '' )
                    {
                        $range = $range_lower . ' - ' . $range_higher;
                    }
                }

                $row['maximum_price_range'] = $range;
            }

            $row['currency'] = isset( $profile['currency'] ) ? $profile['currency'] : 'GBP';
        }
        elseif ( $context['department'] === 'residential-lettings' )
        {
            $row['maximum_rent'] = isset( $profile['max_rent'] ) ? $profile['max_rent'] : '';
            $row['currency']     = isset( $profile['currency'] ) ? $profile['currency'] : 'GBP';
        }

        if ( in_array( $context['department'], array( 'residential-sales', 'residential-lettings' ), true ) )
        {
            $row['minimum_bedrooms'] = isset( $profile['min_beds'] ) ? $profile['min_beds'] : '';

            $output_types = array();
            if ( isset( $profile['property_types'] ) && is_array( $profile['property_types'] ) )
            {
                foreach ( $profile['property_types'] as $profile_type )
                {
                    if ( isset( $context['property_types'][$profile_type] ) )
                    {
                        $output_types[] = $context['property_types'][$profile_type];
                    }
                }
            }
            $row['property_types'] = implode( ', ', $output_types );
        }

        $output_locations = array();
        if ( isset( $profile['locations'] ) && is_array( $profile['locations'] ) )
        {
            foreach ( $profile['locations'] as $profile_location )
            {
                if ( isset( $context['locations'][$profile_location] ) )
                {
                    $output_locations[] = $context['locations'][$profile_location];
                }
            }
        }

        $row['locations']               = implode( ', ', $output_locations );
        $row['additional_requirements'] = isset( $profile['notes'] ) ? $profile['notes'] : '';

        // Keep the historical raw POST value as the filter contract for extensions.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Export nonce and capability are verified by export() before this formatter is called.
        $row = apply_filters(
            'propertyhive_export_applicant_list_row_data',
            $row,
            $_POST,
            $result['contact_id'],
            $result['applicant_profile_id']
        );

        // Post-filter newline normalisation enforces one physical CSV line per applicant.
        if ( isset( $row['additional_requirements'] ) )
        {
            $row['additional_requirements'] = preg_replace( '/\R/u', ' ', (string) $row['additional_requirements'] );
        }
		
        // Keep rows aligned with the filtered/exported column order.
        $ordered = array();
        foreach ( $context['column_keys'] as $key )
        {
            $ordered[] = isset( $row[$key] ) ? $row[$key] : '';
        }

        return $ordered;
    }

    /**
     * Return all taxonomy terms as term_id => name.
     */
    private function get_taxonomy_term_map( $taxonomy )
    {
        $map = array();
        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            )
        );

        if ( ! is_wp_error( $terms ) )
        {
            foreach ( $terms as $term )
            {
                $map[(int) $term->term_id] = $term->name;
            }
        }

        return $map;
    }

    /**
     * Render taxonomy select options recursively.
     */
    private function render_taxonomy_options( $taxonomy, $parent = 0, $selected_ids = array(), $depth = 0 )
    {
        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'parent'     => (int) $parent,
                'orderby'    => 'name',
                'order'      => 'ASC',
            )
        );

        if ( is_wp_error( $terms ) || empty( $terms ) )
        {
            return;
        }

        foreach ( $terms as $term )
        {
            echo '<option value="' . esc_attr( $term->term_id ) . '"' . selected( in_array( (int) $term->term_id, $selected_ids, true ), true, false ) . '>';
            echo esc_html( str_repeat( '- ', $depth ) . $term->name );
            echo '</option>';

            $this->render_taxonomy_options( $taxonomy, $term->term_id, $selected_ids, $depth + 1 );
        }
    }

    /**
     * Expand selected terms to all descendants with one taxonomy query.
     */
    private function expand_taxonomy_terms( $taxonomy, $selected_ids )
    {
        if ( empty( $selected_ids ) )
        {
            return array();
        }

        $terms = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
                'fields'     => 'id=>parent',
            )
        );

        if ( is_wp_error( $terms ) || empty( $terms ) )
        {
            return array_values( array_unique( array_map( 'intval', $selected_ids ) ) );
        }

        $children = array();

        foreach ( $terms as $term_id => $parent_id )
        {
            $term_id   = (int) $term_id;
            $parent_id = (int) $parent_id;

            if ( ! isset( $children[$parent_id] ) )
            {
                $children[$parent_id] = array();
            }

            $children[$parent_id][] = $term_id;
        }

        $found = array();
        $stack = array_values( array_unique( array_map( 'intval', $selected_ids ) ) );

        while ( ! empty( $stack ) )
        {
            $term_id = (int) array_pop( $stack );

            if ( isset( $found[$term_id] ) )
            {
                continue;
            }

            $found[$term_id] = true;

            if ( isset( $children[$term_id] ) )
            {
                foreach ( $children[$term_id] as $child_id )
                {
                    $stack[] = $child_id;
                }
            }
        }

        return array_keys( $found );
    }

    /**
     * Currency symbol helper for the HTML results table.
     */
    private function get_currency_symbol( $profile )
    {
        $currency = '&pound;';

        if ( isset( $profile['currency'] ) && $profile['currency'] !== '' )
        {
            $countries = new PH_Countries();
            $selected_currency = $countries->get_currency( $profile['currency'] );

            if ( $selected_currency !== false && isset( $selected_currency['currency_symbol'] ) )
            {
                $currency = $selected_currency['currency_symbol'];
            }
        }

        return $currency;
    }
}

endif;
