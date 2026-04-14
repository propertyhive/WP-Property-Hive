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
        $slug = explode("/", $feature['wordpress_plugin_file']);
        $slug = $slug[0];

        if ( is_dir( WP_PLUGIN_DIR . '/' . $slug ) )
        {
            $installed_plugins[] = array(
                'slug' => $slug,
                'plugin' => $feature['wordpress_plugin_file']
            );
        }
    }

    update_option( 'propertyhive_pre_pro_add_ons', $installed_plugins );
}

/**
 * Deactivate Template Assistant add on after moving it's functionality into core
 *
 * @return void
 */
function propertyhive_deactivate_template_assistant() 
{
    if ( ! function_exists( 'is_plugin_active' ) ) 
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $ta_plugin = 'propertyhive-template-assistant/propertyhive-template-assistant.php';

    // Single site.
    if ( ! is_multisite() ) 
    {
        if ( is_plugin_active( $ta_plugin ) ) 
        {
            deactivate_plugins( $ta_plugin, true, false );

            $auto_deactivated = get_option( 'propertyhive_template_assistant_auto_deactivated', null );

            if ( null === $auto_deactivated ) 
            {
                update_option(
                    'propertyhive_template_assistant_auto_deactivated',
                    current_time( 'mysql' ),
                    false
                );
            }

            $existing_ta_settings        = get_option( 'propertyhive_template_assistant', null );
            $existing_ta_settings_backup = get_option( 'propertyhive_template_assistant_backup', null );

            if ( null !== $existing_ta_settings && null === $existing_ta_settings_backup ) 
            {
                update_option(
                    'propertyhive_template_assistant_backup',
                    array(
                        'backed_up_at' => current_time( 'mysql' ),
                        'settings'     => $existing_ta_settings,
                    ),
                    false
                );
            }
        }
        else
        {
            // TA plugin not active. Take backup of settings if exists and empty current settings
            $existing_ta_settings        = get_option( 'propertyhive_template_assistant', null );
            $existing_ta_settings_backup = get_option( 'propertyhive_template_assistant_backup', null );

            if ( null !== $existing_ta_settings && null === $existing_ta_settings_backup ) 
            {
                update_option(
                    'propertyhive_template_assistant_backup',
                    array(
                        'backed_up_at' => current_time( 'mysql' ),
                        'settings'     => $existing_ta_settings,
                    ),
                    false
                );

                delete_option( 'propertyhive_template_assistant' );
            }

        }

        return;
    }

    $was_network_active = false;

    // Multisite: handle network-active first.
    if ( is_plugin_active_for_network( $ta_plugin ) ) 
    {
        $was_network_active = true;

        deactivate_plugins( $ta_plugin, true, true );

        $auto_deactivated = get_site_option( 'propertyhive_template_assistant_auto_deactivated', null );

        if ( null === $auto_deactivated ) 
        {
            update_site_option(
                'propertyhive_template_assistant_auto_deactivated',
                current_time( 'mysql' )
            );
        }
    }

    // Multisite: handle each site individually.
    $sites = get_sites(
        array(
            'fields' => 'ids',
            'number' => 0,
        )
    );

    foreach ( $sites as $blog_id ) 
    {
        switch_to_blog( $blog_id );

        if ( $was_network_active || is_plugin_active( $ta_plugin ) ) 
        {
            deactivate_plugins( $ta_plugin, true, false );

            $auto_deactivated = get_option( 'propertyhive_template_assistant_auto_deactivated', null );

            if ( null === $auto_deactivated ) 
            {
                update_option(
                    'propertyhive_template_assistant_auto_deactivated',
                    current_time( 'mysql' ),
                    false
                );
            }

            $existing_ta_settings        = get_option( 'propertyhive_template_assistant', null );
            $existing_ta_settings_backup = get_option( 'propertyhive_template_assistant_backup', null );

            if ( null !== $existing_ta_settings && null === $existing_ta_settings_backup ) {
                update_option(
                    'propertyhive_template_assistant_backup',
                    array(
                        'backed_up_at' => current_time( 'mysql' ),
                        'settings'     => $existing_ta_settings,
                    ),
                    false
                );
            }
        }
        else
        {
            // TA plugin not active. Take backup of settings if exists and empty current settings
            $existing_ta_settings        = get_option( 'propertyhive_template_assistant', null );
            $existing_ta_settings_backup = get_option( 'propertyhive_template_assistant_backup', null );

            if ( null !== $existing_ta_settings && null === $existing_ta_settings_backup ) 
            {
                update_option(
                    'propertyhive_template_assistant_backup',
                    array(
                        'backed_up_at' => current_time( 'mysql' ),
                        'settings'     => $existing_ta_settings,
                    ),
                    false
                );

                delete_option( 'propertyhive_template_assistant' );
            }
        }

        restore_current_blog();
    }
}