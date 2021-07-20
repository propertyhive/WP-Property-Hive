<?php

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
        <span class="displaying-num"><?php echo $tenancies_count; ?> item<?php echo $tenancies_count != 1 ? 's' : ''; ?></span>
    </div>
    <br class="clear" />
</div>
<table class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
            <th scope="col" id='dates' class='manage-column column-dates'>Start / End Dates</th>
            <th scope="col" id='applicant' class='manage-column column-applicant'>Tenants</th>
            <th scope="col" id='rent' class='manage-column column-rent'>Rent</th>
            <th scope="col" id='status' class='manage-column column-status'>Status</th>
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
                ?>
                    <tr>
                        <td class='dates column-dates' data-colname="Start / End Dates">
                            <a href="<?php echo esc_url( $edit_link ); ?>">
                                Start Date: <?php echo $the_tenancy->_start_date != '' ? date( "d/m/Y", strtotime( $the_tenancy->_start_date ) ) : '-'; ?><br>
                                End Date: <?php echo $the_tenancy->_end_date != '' ? date( "d/m/Y", strtotime( $the_tenancy->_end_date ) ) : '-'; ?>
                            </a>
                        </td>
                        <td class='applicant column-applicant' data-colname="Tenants"><?php echo $the_tenancy->get_tenants( true, true ); ?></td>
                        <td class='rent column-rent' data-colname="Rent"><?php echo $the_tenancy->get_formatted_rent(); ?></td>
                        <td class='status column-status' data-colname="Status"><?php echo $the_tenancy->get_status(); ?></td>
                    </tr>
                <?php
            }
        }
        else
        {
            ?>
            <tr class="no-items">
                <td class="colspanchange" colspan="4"><?php echo __( 'No tenancies found', 'propertyhive' ); ?></td>
            </tr>
            <?php
        }
        wp_reset_postdata();
    ?>
    </tbody>
</table>