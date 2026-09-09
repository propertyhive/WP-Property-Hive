<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $meta_query = array(
        array(
            'key' => '_property_id',
            'value' => $post_id,
        ),
    );

    if ( isset($selected_status) && !empty($selected_status) )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $meta_query[] = array(
            'key' => '_status',
            'value' => $selected_status,
        );
    }

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $args = array(
        'post_type'   => 'offer',
        'nopaging'    => true,
        'orderby'     => 'meta_value',
        'order'       => 'DESC',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- The CRM grid orders all linked records by their stored event date; keep metadata ordering for existing display and export parity.
        'meta_key'    => '_offer_date_time',
        'post_status' => 'publish',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required relationship/status metadata restricts this grid to the selected property; retain existing result and status-filter semantics.
        'meta_query'  => $meta_query,
    );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $offers_query = new WP_Query( $args );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $offers_count = $offers_query->found_posts;

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $columns = array(
        'date' => __( 'Offer Date', 'propertyhive' ),
        'applicant' =>  __( 'Applicant(s)', 'propertyhive' ),
        'amount' => __( 'Offer Amount', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $columns = apply_filters( 'propertyhive_property_offers_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_offer_status_filter">
            <option value=""><?php echo esc_html(__( 'All Statuses', 'propertyhive' )); ?></option>
            <?php
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $offer_statuses = ph_get_offer_statuses();

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                foreach ( $offer_statuses as $status => $display_status )
                {
                    ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected( $status, $selected_status ); ?>><?php echo esc_html($display_status); ?></option>
                    <?php
                }
            ?>
        </select>
        <input type="button" name="filter_action" id="filter-property-offers-grid" class="button" value="Filter">
        <a href="" name="export_action" id="export-property-offers-grid" class="button">Export</a>
    </div>
    <div class='tablenav-pages one-page'>
        <span class="displaying-num"><?php echo esc_html($offers_count); ?> item<?php echo $offers_count != 1 ? 's' : ''; ?></span>
    </div>
    <br class="clear" />
</div>
<table class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
        <?php
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $column_i = 0;
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            foreach ( $columns as $column_key => $column )
            {
                ?>
                <th scope="col" id='<?php echo esc_attr($column_key); ?>' class='manage-column column-<?php echo esc_attr($column_key); echo ($column_i == 0 ? ' column-primary' : ''); ?>'><?php echo esc_html($column); ?></th>
                <?php
                ++$column_i;
            }
        ?>
        </tr>
    </thead>
    <tbody id="the-list">
    <?php
        if ( $offers_query->have_posts() )
        {
            while ( $offers_query->have_posts() )
            {
                $offers_query->the_post();
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $the_offer = new PH_Offer( get_the_ID() );

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $edit_link = get_edit_post_link( get_the_ID() );

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $column_data = array(
                    'date' => '<a href="' . esc_url($edit_link) . '" target="' . esc_attr(apply_filters('propertyhive_subgrid_link_target', '')) . '" data-offer-id="' . esc_attr(get_the_ID()) . '">' . esc_html(gmdate("jS F Y", strtotime($the_offer->_offer_date_time))) . '</a>',
                    'applicant' => $the_offer->get_applicants( true, true ),
                    'amount' => esc_html($the_offer->get_formatted_amount()),
                    'status' => esc_html(propertyhive_get_status_label( $the_offer->_status )),
                );

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $row_classes = array( 'status-' . $the_offer->_status );
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $row_classes = apply_filters( 'propertyhive_property_offers_row_classes', $row_classes, get_the_ID(), $the_offer );
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $row_classes = is_array($row_classes) ? array_map( 'sanitize_html_class', array_map( 'strtolower', $row_classes ) ) : array();
                ?>
                    <tr class="<?php echo esc_attr(implode(" ", $row_classes)); ?>" >
                    <?php
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $column_i = 0;
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . esc_attr($column_key) . ' column-' . esc_attr($column_key) . ($column_i == 0 ? ' column-primary' : '') . '" data-colname="' . esc_attr($column) . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core cells are escaped HTML assembled above or by the reviewed PH model formatters; preserve trusted PHP contact-detail filter markup and links.
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_property_offers_custom_column', $column_key );

                            if ( $column_i == 0 ) { echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html(__('Show more details', 'propertyhive' )) . '</span></button>'; }

                            echo '</td>';
                            ++$column_i;
                        }
                    ?>
                    </tr>
                <?php
            }
        }
        else
        {
            ?>
            <tr class="no-items">
                <td class="colspanchange" colspan="<?php echo count($columns); ?>"><?php echo esc_html(__( 'No offers found', 'propertyhive' )); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
    ?>
    </tbody>
</table>