<?php
    $meta_query = array(
        array(
            'key' => '_property_id',
            'value' => $post_id,
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
        'applicants' =>  __( 'Applicant(s)', 'propertyhive' ),
        'negotiators' => __( 'Attending Negotiator(s)', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_property_viewings_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_viewing_status_filter">
            <option value=""><?php echo __( 'All Statuses', 'propertyhive' ); ?></option>
            <?php
                $viewing_statuses = ph_get_viewing_statuses();

                foreach ( $viewing_statuses as $status => $display_status )
                {
                    ?>
                    <option value="<?php echo $status; ?>" <?php selected( $status, $selected_status ); ?>><?php echo $display_status; ?></option>
                    <?php
                }
            ?>
        </select>
        <input type="button" name="filter_action" id="filter-property-viewings-grid" class="button" value="Filter">
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
                <th scope="col" id='<?php echo $column_key; ?>' class='manage-column column-<?php echo $column_key; ?>'><?php echo $column; ?></th>
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
                    'date_time' => '<a href="' . get_edit_post_link( get_the_ID(), '' ) . '" target="' . apply_filters('propertyhive_subgrid_link_target', '') . '" class="viewing-lightbox" data-viewing-id="' . get_the_ID() . '">' . date("H:i jS F Y", strtotime($the_viewing->_start_date_time)) . '</a>',
                    'applicants' =>  $the_viewing->get_applicants( true, true ),
                    'negotiators' => $the_viewing->get_negotiators(),
                    'status' => $the_viewing->get_status(),
                );
                ?>
                    <tr class="status-<?php echo $the_viewing->_status; ?>" >
                    <?php
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . $column_key . ' column-' . $column_key . '" data-colname="' . $column . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_property_viewings_custom_column', $column_key );

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
                <td class="colspanchange" colspan="4">No viewings found</td>
            </tr>
            <?php
        }
    ?>
    </tbody>
</table>