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
        'post_type'   => 'sale',
        'nopaging'    => true,
        'orderby'     => 'meta_value',
        'order'       => 'DESC',
        'meta_key'    => '_sale_date_time',
        'post_status' => 'publish',
        'meta_query'  => $meta_query,
    );
    $sales_query = new WP_Query( $args );
    $sales_count = $sales_query->found_posts;

    $columns = array(
        'date' => __( 'Sale Date', 'propertyhive' ),
        'applicant' =>  __( 'Applicant(s)', 'propertyhive' ),
        'amount' => __( 'Sale Amount', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_property_sales_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_sale_status_filter">
            <option value=""><?php echo __( 'All Statuses', 'propertyhive' ); ?></option>
            <?php
                $sale_statuses = ph_get_sale_statuses();

                foreach ( $sale_statuses as $status => $display_status )
                {
                    ?>
                    <option value="<?php echo $status; ?>" <?php selected( $status, $selected_status ); ?>><?php echo $display_status; ?></option>
                    <?php
                }
            ?>
        </select>
        <input type="button" name="filter_action" id="filter-property-sales-grid" class="button" value="Filter">
    </div>
    <div class='tablenav-pages one-page'>
        <span class="displaying-num"><?php echo $sales_count; ?> item<?php echo $sales_count != 1 ? 's' : ''; ?></span>
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
                <th scope="col" id='<?php echo esc_attr($column_key); ?>' class='manage-column column-<?php echo esc_attr($column_key); ?>'><?php echo $column; ?></th>
                <?php
            }
        ?>
        </tr>
    </thead>
    <tbody id="the-list">
    <?php
        if ( $sales_query->have_posts() )
        {
            while ( $sales_query->have_posts() )
            {
                $sales_query->the_post();
                $the_sale = new PH_Sale( get_the_ID() );

                $edit_link = get_edit_post_link( get_the_ID() );

                $column_data = array(
                    'date' => '<a href="' . esc_url($edit_link) . '" target="' . esc_attr(apply_filters('propertyhive_subgrid_link_target', '')) . '">' . date("jS F Y", strtotime($the_sale->_sale_date_time)) . '</a>',
                    'applicant' => $the_sale->get_applicants( true, true ),
                    'amount' => $the_sale->get_formatted_amount(),
                    'status' => __( ucwords(str_replace("_", " ", $the_sale->_status)), 'propertyhive' ),
                );

                $row_classes = array( 'status-' . $the_sale->_status );
                $row_classes = apply_filters( 'propertyhive_property_sales_row_classes', $row_classes, get_the_ID(), $the_sale );
                $row_classes = is_array($row_classes) ? array_map( 'sanitize_html_class', array_map( 'strtolower', $row_classes ) ) : array();
                ?>
                    <tr class="<?php echo implode(" ", $row_classes); ?>" >
                    <?php
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . esc_attr($column_key) . ' column-' . esc_attr($column_key) . '" data-colname="' . esc_attr($column) . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_property_sales_custom_column', $column_key );

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
                <td class="colspanchange" colspan="<?php echo count($columns); ?>"><?php echo __( 'No sales found', 'propertyhive' ); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
    ?>
    </tbody>
</table>