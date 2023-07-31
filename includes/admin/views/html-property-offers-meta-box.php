<?php
    $meta_query = array(
        array(
            'key' => '_property_id',
            'value' => $post_id,
        ),
    );

    if ( isset($selected_status) && !empty($selected_status) )
    {
        $meta_query[] = array(
            'key' => '_status',
            'value' => $selected_status,
        );
    }

    $args = array(
        'post_type'   => 'offer',
        'nopaging'    => true,
        'orderby'     => 'meta_value',
        'order'       => 'DESC',
        'meta_key'    => '_offer_date_time',
        'post_status' => 'publish',
        'meta_query'  => $meta_query,
    );
    $offers_query = new WP_Query( $args );
    $offers_count = $offers_query->found_posts;

    $columns = array(
        'date' => __( 'Offer Date', 'propertyhive' ),
        'applicant' =>  __( 'Applicant(s)', 'propertyhive' ),
        'amount' => __( 'Offer Amount', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_property_offers_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_offer_status_filter">
            <option value=""><?php echo esc_html(__( 'All Statuses', 'propertyhive' )); ?></option>
            <?php
                $offer_statuses = ph_get_offer_statuses();

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
        <span class="displaying-num"><?php echo $offers_count; ?> item<?php echo $offers_count != 1 ? 's' : ''; ?></span>
    </div>
    <br class="clear" />
</div>
<table class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
        <?php
            foreach ( $columns as $column_key => $column )
            {
                ?>
                <th scope="col" id='<?php echo esc_attr($column_key); ?>' class='manage-column column-<?php echo esc_attr($column_key); ?>'><?php echo esc_html($column); ?></th>
                <?php
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
                $the_offer = new PH_Offer( get_the_ID() );

                $edit_link = get_edit_post_link( get_the_ID() );

                $column_data = array(
                    'date' => '<a href="' . esc_url($edit_link) . '" target="' . esc_attr(apply_filters('propertyhive_subgrid_link_target', '')) . '" data-offer-id="' . esc_attr(get_the_ID()) . '">' . esc_html(date("jS F Y", strtotime($the_offer->_offer_date_time))) . '</a>',
                    'applicant' => $the_offer->get_applicants( true, true ),
                    'amount' => esc_html($the_offer->get_formatted_amount()),
                    'status' => esc_html(__( ucwords(str_replace("_", " ", $the_offer->_status)), 'propertyhive' )),
                );

                $row_classes = array( 'status-' . $the_offer->_status );
                $row_classes = apply_filters( 'propertyhive_property_offers_row_classes', $row_classes, get_the_ID(), $the_offer );
                $row_classes = is_array($row_classes) ? array_map( 'sanitize_html_class', array_map( 'strtolower', $row_classes ) ) : array();
                ?>
                    <tr class="<?php echo esc_attr(implode(" ", $row_classes)); ?>" >
                    <?php
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . esc_attr($column_key) . ' column-' . esc_attr($column_key) . '" data-colname="' . esc_attr($column) . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_property_offers_custom_column', $column_key );

                            echo '</td>';
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