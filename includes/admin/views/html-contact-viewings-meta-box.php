<?php
    $meta_query = array(
        array(
            'key' => '_applicant_contact_id',
            'value' => $post_id
        ),
    );

    if ( isset($selected_status) && !empty($selected_status) )
    {
        $meta_query = add_viewing_status_meta_query( $meta_query, $selected_status );
    }

    $args = array(
        'post_type'   => 'viewing',
        'nopaging'    => true,
        'orderby'     => 'meta_value',
        'order'       => 'DESC',
        'meta_key'    => '_start_date_time',
        'post_status' => 'publish',
        'meta_query'  => $meta_query,
    );
    $viewings_query = new WP_Query( $args );
    $viewings_count = $viewings_query->found_posts;

    $columns = array(
        'date_time' => __( 'Date', 'propertyhive' ) . ' / ' . __( 'Time', 'propertyhive' ),
        'property' =>  __( 'Property', 'propertyhive' ),
        'negotiators' => __( 'Attending Negotiator(s)', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_contact_viewings_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_viewing_status_filter">
            <option value=""><?php echo esc_html(__( 'All Statuses', 'propertyhive' )); ?></option>
            <?php
                $viewing_statuses = ph_get_viewing_statuses();

                foreach ( $viewing_statuses as $status => $display_status )
                {
                    ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected( $status, $selected_status ); ?>><?php echo esc_html($display_status); ?></option>
                    <?php
                }
            ?>
        </select>
        <input type="button" name="filter_action" id="filter-contact-viewings-grid" class="button" value="Filter">
        <a href="" name="export_action" id="export-contact-viewings-grid" class="button">Export</a>
    </div>
    <div class='tablenav-pages one-page'>
        <span class="displaying-num"><?php echo $viewings_count; ?> item<?php echo $viewings_count != 1 ? 's' : ''; ?></span>
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
        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();
                $the_viewing = new PH_Viewing( get_the_ID() );

                $edit_link = get_edit_post_link( get_the_ID() );

                $column_data = array(
                    'date_time' => '<a href="' . esc_url($edit_link) . '" target="' . esc_attr(apply_filters('propertyhive_subgrid_link_target', '')) . '" class="viewing-lightbox" data-viewing-id="' . esc_attr(get_the_ID()) . '">' . esc_html(date("H:i jS F Y", strtotime($the_viewing->_start_date_time))) . '</a>',
                    'property' => $the_viewing->get_property_address(),
                    'negotiators' => esc_html($the_viewing->get_negotiators()),
                    'status' => $the_viewing->get_status(),
                );

                $row_classes = array( 'status-' . $the_viewing->_status );
                if ( $the_viewing->_status == 'carried_out' )
                {
                    $row_classes[] = 'applicant-feedback-status-' . $the_viewing->_feedback_status;
                }
                $row_classes = apply_filters( 'propertyhive_contact_viewings_row_classes', $row_classes, get_the_ID(), $the_viewing );
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

                            do_action( 'propertyhive_contact_viewings_custom_column', $column_key );

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
                <td class="colspanchange" colspan="<?php echo count($columns); ?>"><?php echo esc_html(__( 'No viewings found', 'propertyhive' )); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
    ?>
    </tbody>
</table>