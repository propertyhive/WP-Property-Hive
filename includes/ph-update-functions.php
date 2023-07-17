<?php
/**
 * PropertyHive Updates
 *
 * Functions for updating data during an update.
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Update on_market_change_dates for 1.4.68
 *
 * @return void
 */
function propertyhive_update_1468_on_market_change_dates() {
    global $wpdb;

    $args = array(
        'post_type' => 'property',
        'fields' => 'ids',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_on_market',
                'value' => 'yes'
            ),
            array(
                'key' => '_on_market_change_date',
                'compare' => 'NOT EXISTS'
            )
        ),
        'nopaging' => true,
        'suppress_filters' => true,
    );
    $property_query =  new WP_Query($args);

    if ( $property_query->have_posts() )
    {
        while ( $property_query->have_posts() )
        {
            $property_query->the_post();

            $date_post_written = get_the_date( 'Y-m-d H:i:s' );
            add_post_meta( get_the_ID(), '_on_market_change_date', $date_post_written );
        }
    }

    wp_reset_postdata();
}

/**
 * Record the fact we've updated to version 2 from an older version and which add ons were installed at the time of update
 *
 * @return void
 */
function propertyhive_update_200_pre_pro_record_installed_plugins() 
{
    $installed_plugins = array();

    $features = get_ph_pro_features();

    foreach ( $features as $feature )
    {
        if ( is_dir( WP_PLUGIN_DIR . '/' . $feature['slug'] ) )
        {
            $installed_plugins[] = $feature['slug'];
        }
    }

    update_option( 'propertyhive_pre_pro_add_ons', $installed_plugins );
}