<?php
    $meta_query = array(
        array(
            'key' => '_applicant_contact_id',
            'value' => $post_id
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
        'property' => __( 'Property', 'propertyhive' ),
        'property_owner' => __( 'Property Owner', 'propertyhive' ),
        'amount' => __( 'Offer Amount', 'propertyhive' ),
        'status' => __( 'Status', 'propertyhive' ),
    );

    $columns = apply_filters( 'propertyhive_contact_offers_columns', $columns );
?>

<div class="tablenav top">
    <div class="alignleft actions">
        <select name="_status" id="_offer_status_filter">
            <option value=""><?php echo __( 'All Statuses', 'propertyhive' ); ?></option>
            <?php
                $offer_statuses = ph_get_offer_statuses();

                foreach ( $offer_statuses as $status => $display_status )
                {
                    ?>
                    <option value="<?php echo $status; ?>" <?php selected( $status, $selected_status ); ?>><?php echo $display_status; ?></option>
                    <?php
                }
            ?>
        </select>
        <input type="button" name="filter_action" id="filter-contact-offers-grid" class="button" value="Filter">
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
                <th scope="col" id='<?php echo $column_key; ?>' class='manage-column column-<?php echo $column_key; ?>'><?php echo $column; ?></th>
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
                    'date' => '<a href="' . get_edit_post_link( get_the_ID(), '' ) . '" target="' . apply_filters('propertyhive_subgrid_link_target', '') . '">' . date("jS F Y", strtotime($the_offer->_offer_date_time)) . '</a>',
                    'property' => $the_offer->get_property_address(),
                    'property_owner' => $the_offer->get_property_owners(),
                    'amount' => $the_offer->get_formatted_amount(),
                    'status' => __( ucwords(str_replace("_", " ", $the_offer->_status)), 'propertyhive' ),
                );
                ?>
                    <tr class="status-<?php echo $the_offer->_status; ?>" >
                    <?php
                        foreach ( $columns as $column_key => $column )
                        {
                            echo '<td class="' . $column_key . ' column-' . $column_key . '" data-colname="' . $column . '">';

                            if ( isset( $column_data[$column_key] ) )
                            {
                                echo $column_data[$column_key];
                            }

                            do_action( 'propertyhive_contact_offers_custom_column', $column_key );

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
                <td class="colspanchange" colspan="4">No offers found</td>
            </tr>
            <?php
        }
    ?>
    </tbody>
</table>