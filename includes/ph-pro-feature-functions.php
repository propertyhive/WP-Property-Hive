<?php

function get_ph_pro_features()
{
    // Structure:
    // - slug - Internal ID. Should also match the add-ons folder name when necessary
    // - plugin - Directory and plugin file name. Created because not every add ons slug and filename are the same
    // - download_url - URL where plugin can be downloaded from
    // - name - Public name
    // - icon - Icon to show on features page
    // - description
    // - url - URL to public page on Property Hive website
    // - docs_url - URL to documentation
    // - pro - true/false
    // - categories - Array of categories

    $features = array(
        array(
            'slug' => 'propertyhive-template-assistant',
            'plugin' => 'propertyhive-template-assistant/propertyhive-template-assistant.php',
            'download_url' => 'https://wp-property-hive.com/add-on-store/propertyhive-template-assistant-g55ffr7l/propertyhive-template-assistant.zip',
            'name' => __( 'Template Assistant', 'propertyhive' ),
            'icon' => 'dashicons-admin-tools',
            'description' => 'Template Assistant description',
            'url' => 'https://wp-property-hive.com/addons/template-assistant/',
            'docs_url' => 'https://docs.wp-property-hive.com/add-ons/template-assistant/',
            'pro' => false,
            'categories' => array('website_enhancement'),
        ),
        array(
            'slug' => 'propertyhive-property-import',
            'plugin' => 'propertyhive-property-import/propertyhive-property-import.php',
            'download_url' => 'https://wp-property-hive.com/add-on-store/propertyhive-property-import-c350alqg/propertyhive-property-import.zip',
            'name' => __( 'Property Import', 'propertyhive' ),
            'icon' => 'dashicons-download',
            'description' => 'Import properties from CRMs',
            'url' => 'https://wp-property-hive.com/addons/property-import/',
            'docs_url' => 'https://docs.wp-property-hive.com/add-ons/property-import/',
            'pro' => true,
            'categories' => array('import_export'),
            'packages' => array( 'import' )
        ),
        array(
            'slug' => 'propertyhive-map-search',
            'plugin' => 'propertyhive-map-search/propertyhive-map-search.php',
            'download_url' => 'https://wp-property-hive.com/add-on-store/propertyhive-map-search-g11can9z/propertyhive-map-search.zip',
            'name' => __( 'Map View & Draw-a-Search', 'propertyhive' ),
            'icon' => 'dashicons-location-alt',
            'description' => 'Map description here',
            'url' => 'https://wp-property-hive.com/addons/map-search/',
            'docs_url' => 'https://docs.wp-property-hive.com/add-ons/map-view-and-draw-a-search/',
            'pro' => true,
            'categories' => array('website_enhancement'),
            'packages' => array( 'import', 'complete' )
        ),
        array(
            'slug' => 'property-hive-rental-affordability-calculator',
            'plugin' => 'property-hive-rental-affordability-calculator/propertyhive-rental-affordability-calculator.php',
            'download_url' => 'https://downloads.wordpress.org/plugin/property-hive-rental-affordability-calculator.latest-stable.zip',
            'name' => __( 'Rental Affordability Calculator', 'propertyhive' ),
            'icon' => 'dashicons-calculator',
            'description' => 'Output a rental affordability calculator on your website',
            'url' => 'https://wp-property-hive.com/addons/rental-affordability-calculator/',
            'docs_url' => '',
            'pro' => false,
            'categories' => array('website_enhancement', 'free'),
        ),
    );

    $features = apply_filters( 'propertyhive_pro_features', $features );

    return $features;
}

function get_ph_pro_feature( $requested_feature )
{
	$features = get_ph_pro_features();

	foreach ( $features as $feature )
	{
		if ( $feature['slug'] == $requested_feature )
		{
			return $feature;
		}
	}

	return false;
}