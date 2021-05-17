<?php
    $meta_query = array(
        array(
            'relation' => 'OR',
            array(
                'key' => 'property_id',
                'value' => $post_id,
            ),
            array(
                'key' => '_property_id',
                'value' => $post_id,
            ),
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
        'post_type'   => 'enquiry',
        'nopaging'    => true,
        'meta_query'  => $meta_query,
    );
    $enquiries_query = new WP_Query( $args );
    $enquiries_count = $enquiries_query->found_posts;

    $columns = array(
        'date_time' => __( 'Date', 'propertyhive' ) . ' / ' . __( 'Time', 'propertyhive' ),
        'subject' =>  __( 'Subject', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
        'negotiator' => __( 'Negotiator', 'propertyhive' ),
        'office' => __( 'Office', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_property_enquiries_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_enquiry_status_filter">
            <option value=""><?php echo __( 'All Statuses', 'propertyhive' ); ?></option>
            <?php
                $enquiry_statuses = ph_get_enquiry_statuses();

                foreach ( $enquiry_statuses as $status => $display_status )
                {
                    ?>
                    <option value="<?php echo $status; ?>" <?php selected( $status, $selected_status ); ?>><?php echo $display_status; ?></option>
                    <?php
                }
            ?>
        </select>
        <input type="button" name="filter_action" id="filter-property-enquiries-grid" class="button" value="Filter">
    </div>
    <div class='tablenav-pages one-page'>
        <span class="displaying-num"><?php echo $enquiries_count; ?> item<?php echo $enquiries_count != 1 ? 's' : ''; ?></span>
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
                <th scope="col" id='<?php echo $column_key; ?>' class='manage-column column-<?php echo $column_key; ?>'><?php echo $column; ?></th>
                <?php
            }
        ?>
        </tr>
    </thead>
    <tbody id="the-list">
    <?php
        if ( $enquiries_query->have_posts() )
        {
            while ( $enquiries_query->have_posts() )
            {
                $enquiries_query->the_post();
                $the_enquiry = new PH_Enquiry( get_the_ID() );

                $edit_link = get_edit_post_link( get_the_ID() );

                $column_data = array(
                    'date_time' => '<a href="' . $edit_link . '" target="' . apply_filters('propertyhive_subgrid_link_target', '') . '">' . get_the_time( 'jS M Y H:i' ) . '</a>',
                    'subject' => get_the_title(),
                    'status' => ucfirst( $the_enquiry->status ),
                    'negotiator' => $the_enquiry->get_negotiator(),
                    'office' => $the_enquiry->get_office(),
                );
                ?>
                    <tr class="status-<?php echo $the_enquiry->_status; ?>" >
                    <?php
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . $column_key . ' column-' . $column_key . '" data-colname="' . $column . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_property_enquiries_custom_column', $column_key );

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
                <td class="colspanchange" colspan="<?php echo count($columns); ?>"><?php echo __( 'No enquiries found', 'propertyhive' ); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
    ?>
    </tbody>
</table>