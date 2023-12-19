<?php

function get_ph_pro_features()
{
    $features = array();

    if ( false === ( $features = get_transient( 'propertyhive_features' ) ) || isset($_GET['ph_force_get_features']) ) 
    {
        // It wasn't there, so regenerate the data and save the transient
        $response = wp_remote_get(
            'https://wp-property-hive.com/add-ons-json.php',
            array(
                'timeout' => 10
            )
        );

        if ( !is_wp_error( $response ) && is_array( $response ) )
        {
            $body = $response['body']; // use the content

            $add_ons = json_decode($body, TRUE);
            
            if ( $add_ons !== FALSE && is_array($add_ons) && !empty($add_ons) )
            {
                $features = $add_ons;
                set_transient( 'propertyhive_features', $features, DAY_IN_SECONDS );
            }
        }
    }

    $features = apply_filters( 'propertyhive_pro_features', $features );

    return $features;
}

function get_ph_pro_feature( $requested_feature )
{
	$features = get_ph_pro_features();

	foreach ( $features as $feature )
	{
        $slug = explode("/", $feature['wordpress_plugin_file']);
        $slug = $slug[0];

		if ( $slug == $requested_feature )
		{
			return $feature;
		}
	}

	return false;
}