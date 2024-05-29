<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

    $meta_query = array(
        array(
            'key' => '_property_id',
            'value' => $post_id,
        ),
    );

    if ( isset($selected_status) && !empty($selected_status) )
    {
        switch ( $selected_status )
        {
            case 'pending':
            {
                $meta_query[] = array(
                    'key' => '_start_date',
                    'value' => date('Y-m-d'),
                    'type' => 'date',
                    'compare' => '>',
                );
                break;
            }
            case 'current':
            {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        array(
                            'key' => '_start_date',
                            'value' => date('Y-m-d'),
                            'type'  => 'date',
                            'compare' => '<=',
                        ),
                        array(
                            'key' => '_end_date',
                            'value' => date('Y-m-d'),
                            'type'  => 'date',
                            'compare' => '>=',
                        )
                    ),
                    array(
                        array(
                            'key' => '_start_date',
                            'value' => date('Y-m-d'),
                            'type'  => 'date',
                            'compare' => '<=',
                        ),
                        array(
                            'key' => '_end_date',
                            'value' => '',
                            'compare' => '=',
                        )
                    )
                );
                break;
            }
            case 'finished':
            {
                $meta_query[] = array(
                    'key' => '_end_date',
                    'value' => date('Y-m-d'),
                    'type' => 'date',
                    'compare' => '<',
                );
                break;
            }
        }
    }

    $args = array(
        'post_type'   => 'tenancy', 
        'nopaging'    => true,
        'orderby'     => 'meta_value',
        'order'       => 'DESC',
        'meta_key'    => '_start_date',
        'post_status' => 'publish',
        'meta_query'  => $meta_query
    );
    $tenancies_query = new WP_Query( $args );
    $tenancies_count = $tenancies_query->found_posts;

    $columns = array(
        'dates' => __( 'Start / End Dates', 'propertyhive' ),
        'tenants' =>  __( 'Tenants(s)', 'propertyhive' ),
        'rent' => __( 'Rent', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_property_tenancies_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_tenancy_status_filter">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo ( isset($selected_status) && $selected_status == 'pending' ) ? 'selected' : ''; ?>>Pending</option>
            <option value="current" <?php echo ( isset($selected_status) && $selected_status == 'current' ) ? 'selected' : ''; ?>> Current</option>
            <option value="finished" <?php echo ( isset($selected_status) && $selected_status == 'finished' ) ? 'selected' : ''; ?>> Finished</option>
        </select>
        <input type="button" name="filter_action" id="filter-property-tenancies-grid" class="button" value="Filter">
    </div>
    <div class='tablenav-pages one-page'>
        <span class="displaying-num"><?php echo esc_attr($tenancies_count); ?> item<?php echo $tenancies_count != 1 ? 's' : ''; ?></span>
    </div>
    <br class="clear" />
</div>
<table class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
        <?php
            $column_i = 0;
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
        if ( $tenancies_query->have_posts() )
        {
            while ( $tenancies_query->have_posts() )
            {
                $tenancies_query->the_post();
                $the_tenancy = new PH_Tenancy( get_the_ID() );

                $edit_link = get_edit_post_link( get_the_ID() );

                $column_data = array(
                    'dates' => '<a href="' . esc_url($edit_link) . '" target="' . esc_attr(apply_filters('propertyhive_subgrid_link_target', '')) . '">
                        Start Date: ' . ( $the_tenancy->_start_date != '' ? esc_html(date( "d/m/Y", strtotime( $the_tenancy->_start_date ) )) : '-' ) . '<br>
                        End Date: ' . ( $the_tenancy->_end_date != '' ? esc_html(date( "d/m/Y", strtotime( $the_tenancy->_end_date ) )) : '-' ) . '
                    </a>',
                    'tenants' => $the_tenancy->get_tenants( true, true ),
                    'rent' => esc_html($the_tenancy->get_formatted_rent()),
                    'status' => esc_html($the_tenancy->get_status()),
                );

                $row_classes = array( 'status-' . $the_tenancy->get_status() );
                $row_classes = apply_filters( 'propertyhive_property_tenancies_row_classes', $row_classes, get_the_ID(), $the_tenancy );
                $row_classes = is_array($row_classes) ? array_map( 'sanitize_html_class', array_map( 'strtolower', $row_classes ) ) : array();
                ?>
                    <tr class="<?php echo esc_attr(implode(" ", $row_classes)); ?>" >
                    <?php
                        $column_i = 0;
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . esc_attr($column_key) . ' column-' . esc_attr($column_key) . ($column_i == 0 ? ' column-primary' : '') . '" data-colname="' . esc_attr($column) . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_property_tenancies_custom_column', $column_key );

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
                <td class="colspanchange" colspan="4"><?php echo esc_html(__( 'No tenancies found', 'propertyhive' )); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
    ?>
    </tbody>
</table>