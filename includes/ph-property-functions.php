<?php
/**
 * PropertyHive Property Functions
 *
 * Functions for property specific things.
 *
 * @author 		BISOTALL
 * @category 	Core
 * @package 	PropertyHive/Functions
 * @version     1.0.0
 */

/**
 * Main function for returning properties, uses the PH_Property_Factory class.
 *
 * @param mixed $the_property Post object or post ID of the property.
 * @param array $args (default: array()) Contains all arguments to be used to get this property.
 * @return PH_Property
 */
function get_property( $the_property = false, $args = array() ) {
	return new PH_Property( $the_property );
}

/**
 * Function that returns an array containing the IDs of the featured properties.
 *
 * @access public
 * @return array
 */
function ph_get_featured_property_ids() {

	// Load from cache
	$featured_property_ids = get_transient( 'ph_featured_properties' );

	// Valid cache found
	if ( false !== $featured_property_ids )
		return $featured_property_ids;

	$featured = get_posts( array(
		'post_type'      => 'property',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'   => '_on_market',
				'value' => 'yes'
			),
			array(
				'key' 	=> '_featured',
				'value' => 'yes'
			)
		),
		'fields' => 'id=>parent'
	) );

	$featured_property_ids = array_keys( $featured );

	set_transient( 'ph_featured_properties', $featured_property_ids, YEAR_IN_SECONDS );

	return $featured_property_ids;
}

/**
 * Get the placeholder image URL for properties
 *
 * @access public
 * @return string
 */
function ph_placeholder_img_src() {
	return apply_filters( 'propertyhive_placeholder_img_src', PH()->plugin_url() . '/assets/images/placeholder.png' );
}

/**
 * Get the placeholder image
 *
 * @access public
 * @return string
 */
function ph_placeholder_img( $size = 'thumbnail' ) {
	$dimensions = ph_get_image_size( $size );

	return apply_filters('propertyhive_placeholder_img', '<img src="' . ph_placeholder_img_src() . '" alt="Placeholder" width="' . esc_attr( $dimensions['width'] ) . '" class="property-placeholder wp-post-image" height="' . esc_attr( $dimensions['height'] ) . '" />' );
}

/**
 * Track property views
 */
function ph_track_property_view() {
	if ( ! is_singular( 'property' ) )
		return;

	global $post;

	// Track in cookie
	if ( apply_filters( 'propertyhive_store_in_recently_viewed_cookie', true ) )
	{
		if ( empty( $_COOKIE['propertyhive_recently_viewed'] ) )
			$viewed_properties = array();
		else
			$viewed_properties = (array) explode( '|', $_COOKIE['propertyhive_recently_viewed'] );

		if ( ! in_array( $post->ID, $viewed_properties ) )
			$viewed_properties[] = $post->ID;

		if ( sizeof( $viewed_properties ) > 15 )
			array_shift( $viewed_properties );

		// Store for session only
		ph_setcookie( 'propertyhive_recently_viewed', implode( '|', $viewed_properties ) );
	}

	// Track in database
	if ( !is_user_logged_in() || ( is_user_logged_in() && !current_user_can('manage_propertyhive') ) )
	{
		// User isn't logged in

		$view_counts = get_post_meta( $post->ID, '_view_statistics', TRUE );

		if ( $view_counts == '' || !is_array($view_counts) )
		{
			$view_counts = array();
		}

		if ( !isset($view_counts[date("Y-m-d")]) )
		{
			$view_counts[date("Y-m-d")] = 0;
		}

		++$view_counts[date("Y-m-d")];
		
		update_post_meta( $post->ID, '_view_statistics', $view_counts );
	}
}

add_action( 'template_redirect', 'ph_track_property_view', 20 );

function get_property_map( $args = array() )
{
	global $property;

	if ( $property->latitude != '' && $property->latitude != '0' && $property->longitude != '' && $property->longitude != '0' )
	{
		$id_suffix = ( ( isset($args['id']) && $args['id'] != '' ) ? '_' . $args['id'] : '' );

	    echo '<div id="property_map_canvas' . $id_suffix . '" style="background:#EEE; height:' . str_replace( "px", "", ( ( isset($args['height']) && !empty($args['height']) ) ? $args['height'] : '400' ) ) . 'px"></div>';
		
		if ( get_option('propertyhive_maps_provider') == 'osm' )
		{
			$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/js/leaflet/';

			wp_register_style('leaflet', $assets_path . 'leaflet.css', array(), '1.9.4');
		    wp_enqueue_style('leaflet');

			wp_register_script('leaflet', $assets_path . 'leaflet.js', array(), '1.9.4', false);
		    wp_enqueue_script('leaflet');
?>
<script>

	var property_map<?php echo $id_suffix; ?>;

	function initialize_property_map<?php echo $id_suffix; ?>() 
	{
		if ( property_map<?php echo $id_suffix; ?> != undefined ) { property_map<?php echo $id_suffix; ?>.remove(); }

		L.Icon.Default.imagePath = '<?php echo $assets_path; ?>/images/';

		property_map<?php echo $id_suffix; ?> = L.map("property_map_canvas<?php echo $id_suffix; ?>"<?php echo ( ( isset($args['scrollwheel']) && ($args['scrollwheel'] === 'false' || $args['scrollwheel'] === FALSE) ) ? ', { scrollWheelZoom: false, dragging: !L.Browser.mobile }' : '' ); ?>).setView([<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>], <?php echo ( ( isset($args['zoom']) && !empty($args['zoom']) ) ? $args['zoom'] : '14' ); ?>);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(property_map<?php echo $id_suffix; ?>);

		<?php
			$icon_code = '';
			if ( class_exists( 'PH_Map_Search' ) )
  			{
  				$map_add_on_settings = get_option( 'propertyhive_map_search', array() );

				if ( isset($map_add_on_settings['icon_type']) && $map_add_on_settings['icon_type'] == 'custom_single' && isset($map_add_on_settings['custom_icon_attachment_id']) && $map_add_on_settings['custom_icon_attachment_id'] != '' )
		        {
		            $marker_icon_url = wp_get_attachment_url( $map_add_on_settings['custom_icon_attachment_id'] );
		            if ( $marker_icon_url !== FALSE )
		            {
		?>
		var icon_options = { iconUrl: '<?php echo $marker_icon_url; ?>' }
		<?php
						$size = getimagesize( get_attached_file(  $map_add_on_settings['custom_icon_attachment_id'] ) );
						if ( $size !== FALSE && !empty($size) )
						{
							if (isset($map_add_on_settings['custom_icon_anchor_position']) && $map_add_on_settings['custom_icon_anchor_position'] == 'center')
							{
								$icon_anchor_width = floor($size[0] / 2);
								$icon_anchor_height = floor($size[1] / 2);
							}
							else
							{
								$icon_anchor_width = floor($size[0] / 2);
								$icon_anchor_height = $size[1];
							}

		?>
		icon_options.iconSize = [<?php echo $size[0]; ?>, <?php echo $size[1]; ?>];
		icon_options.iconAnchor = [<?php echo $icon_anchor_width; ?>, <?php echo $icon_anchor_height; ?>];
		<?php
						}
		?>
		var custom_icon = L.icon(icon_options);
		<?php
		                $icon_code = ', { icon: custom_icon }';
		            }
		        }
		    }
		    do_action( 'propertyhive_property_map_actions', $property, $args, $id_suffix );
		?>

		L.marker([<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>]<?php echo $icon_code; ?>).addTo(property_map<?php echo $id_suffix; ?>);
	}

	<?php if ( !isset($args['init_on_load']) || ( isset($args['init_on_load']) && ($args['init_on_load'] === 'true' || $args['init_on_load'] === TRUE) ) ) { ?>
	if (window.addEventListener) {
		window.addEventListener('load', initialize_property_map<?php echo $id_suffix; ?>);
	}else{
		window.attachEvent('onload', initialize_property_map<?php echo $id_suffix; ?>);
	}
	<?php } ?>

</script>
<?php
		}
		else
		{
			$api_key = get_option('propertyhive_google_maps_api_key');
		    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
		    wp_enqueue_script('googlemaps');
?>
<script>

	var property_map<?php echo $id_suffix; ?>;
	var property_marker<?php echo $id_suffix; ?>;
			
	function initialize_property_map<?php echo $id_suffix; ?>() {
				
		var myLatlng = new google.maps.LatLng(<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>);
		var map_options = {
	  		zoom: <?php echo ( ( isset($args['zoom']) && !empty($args['zoom']) ) ? $args['zoom'] : '14' ); ?>,
			center: myLatlng,
	  		mapTypeId: google.maps.MapTypeId.ROADMAP,
	  		scrollwheel: <?php echo ( ( isset($args['scrollwheel']) && ($args['scrollwheel'] === 'false' || $args['scrollwheel'] === FALSE) ) ? 'false' : 'true' ); ?>
	  	}
	  	<?php
  			if ( class_exists( 'PH_Map_Search' ) )
  			{
  				$map_add_on_settings = get_option( 'propertyhive_map_search', array() );

  				if ( isset($map_add_on_settings['style_js']) && trim($map_add_on_settings['style_js']) != '' )
  				{
  					echo 'map_options.styles = ' . trim($map_add_on_settings['style_js']) . ';';
  				}
  			}

  			do_action( 'propertyhive_property_map_options', $property, $args, $id_suffix );
  		?>
		property_map<?php echo $id_suffix; ?> = new google.maps.Map(document.getElementById("property_map_canvas<?php echo $id_suffix; ?>"), map_options);
				
		var myLatlng = new google.maps.LatLng(<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>);
			
		var marker_options = {
			map: property_map<?php echo $id_suffix; ?>,
			position: myLatlng		
		};

		<?php
			if ( class_exists( 'PH_Map_Search' ) )
  			{
  				$map_add_on_settings = get_option( 'propertyhive_map_search', array() );

				if ( isset($map_add_on_settings['icon_type']) && $map_add_on_settings['icon_type'] == 'custom_single' && isset($map_add_on_settings['custom_icon_attachment_id']) && $map_add_on_settings['custom_icon_attachment_id'] != '' )
		        {
		            $marker_icon_url = wp_get_attachment_url( $map_add_on_settings['custom_icon_attachment_id'] );
		            if ( $marker_icon_url !== FALSE )
		            {
		            	echo 'var ph_map_icon = {
						    url: \'' . $marker_icon_url . '\'';
						if ( isset($map_add_on_settings['custom_icon_anchor_position']) && $map_add_on_settings['custom_icon_anchor_position'] == 'center' )
						{
							$size = getimagesize( get_attached_file(  $map_add_on_settings['custom_icon_attachment_id'] ) );
							if ( $size !== FALSE && !empty($size) )
							{
								echo ', anchor: new google.maps.Point(' . floor( $size[0] / 2 ) . ', ' . floor( $size[1] / 2 ) . ')';
							}
						}   
						echo '};';
		                echo 'marker_options.icon = ph_map_icon;';
		            }
		        }
		    }
		?>

		<?php do_action( 'propertyhive_property_map_marker_options' ); ?>

		property_marker<?php echo $id_suffix; ?> = new google.maps.Marker(marker_options);

		<?php do_action( 'propertyhive_property_map_actions' ); ?>
	}
	
	<?php if ( !isset($args['init_on_load']) || ( isset($args['init_on_load']) && ($args['init_on_load'] === 'true' || $args['init_on_load'] === TRUE) ) ) { ?>
	if(window.addEventListener) {
		window.addEventListener('load', initialize_property_map<?php echo $id_suffix; ?>);
	}else{
		window.attachEvent('onload', initialize_property_map<?php echo $id_suffix; ?>);
	}
	<?php } ?>

</script>
<?php
		}
		do_action( 'propertyhive_property_map_after' );
	}
}

function get_property_static_map( $args = array() )
{
	global $property;

	if ( get_option('propertyhive_maps_provider') == 'osm' )
	{


	}
	else
	{
		if ( $property->latitude != '' && $property->latitude != '0' && $property->longitude != '' && $property->longitude != '0' )
		{
			$api_key = get_option('propertyhive_google_maps_api_key');

		    $id_suffix = ( ( isset($args['id']) && $args['id'] != '' ) ? '_' . $args['id'] : '' );

		    $link = ( ( isset($args['link']) && ($args['link'] === 'false' || $args['link'] === FALSE) ) ? 'false' : 'true' );

		    $map_url = 'https://maps.googleapis.com/maps/api/staticmap?' .
		    	'center=' . $property->latitude . ',' . $property->longitude .
		    	'&size=1024x' . str_replace( "px", "", ( ( isset($args['height']) && !empty($args['height']) ) ? $args['height'] : '400' ) ) .  
		    	'&zoom=' . ( ( isset($args['zoom']) && !empty($args['zoom']) ) ? $args['zoom'] : '14' ) . 
		    	'&maptype=roadmap' . 
		    	'&markers=%7C%7C' . $property->latitude . ',' . $property->longitude .
		    	'&key=' . urlencode($api_key);

		    echo '<style type="text/css">
		    	#property_static_map' . $id_suffix . ' {
		    		height:' . str_replace( "px", "", ( ( isset($args['height']) && !empty($args['height']) ) ? $args['height'] : '400' ) ) . 'px;
		    		display: block;
				    background-image: url("' . $map_url . '");
				    background-repeat: no-repeat;
				    background-position: 50% 50%;
				    line-height: 0;
				}
		    </style>';
		    
		    if ( $link === true )
		    {
		    	echo '<a id="property_static_map' . $id_suffix . '" href="https://maps.google.com?q=' . $property->latitude . ',' . $property->longitude . '" target="_blank" rel="nofollow"></a>';
			}
			else
			{
				echo '<div id="property_static_map' . $id_suffix . '" ></div>';
			}
		}
	}
}

function get_property_street_view( $args = array() )
{
	global $property;

	if ( get_option('propertyhive_maps_provider') == 'osm' )
	{


	}
	else
	{
		if ( $property->latitude != '' && $property->latitude != '0' && $property->longitude != '' && $property->longitude != '0' )
		{
			$api_key = get_option('propertyhive_google_maps_api_key');
		    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
		    wp_enqueue_script('googlemaps');

		    echo '<div id="property_street_view_canvas" style="height:' . str_replace( "px", "", ( ( isset($args['height']) && !empty($args['height']) ) ? $args['height'] : '400' ) ) . 'px"></div>';
	?>
	<script>

		// We declare vars globally so developers can access them
		var property_street_view; // Global declaration of the map
				
		function initialize_property_street_view() {
					
			var myLatlng = new google.maps.LatLng(<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>);
			var map_options = {
				center: myLatlng
		  	}

		  	<?php do_action( 'propertyhive_property_street_view_map_options' ); ?>

			property_street_view = new google.maps.Map(document.getElementById("property_street_view_canvas"), map_options);
					
			var streetViewOptions = {
		    	position: myLatlng,
				pov: {
					heading: 90,
					pitch: 0,
					zoom: 0
				}
			};

			<?php do_action( 'propertyhive_property_street_view_options' ); ?>

			var streetView = new google.maps.StreetViewPanorama(document.getElementById("property_street_view_canvas"), streetViewOptions);
			streetView.setVisible(true);
		}
			
		if(window.addEventListener) {
			window.addEventListener('load', initialize_property_street_view);
		}else{
			window.attachEvent('onload', initialize_property_street_view);
		}

	</script>
	<?php
		}
	}
}

function get_electricity_types()
{
	$types = array(
		'mains_supply' => __( 'Mains Supply', 'propertyhive' ),
		'wind_turbine' => __( 'Wind Turbine', 'propertyhive' ),
		'solar_pv_panels' => __( 'Solar PV Panels', 'propertyhive' ),
		'private_supply' => __( 'Private Supply', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_electricity_types', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_electricity_type( $type )
{
	$types = get_electricity_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_water_types()
{
	$types = array(
		'mains_supply' => __( 'Mains Supply', 'propertyhive' ),
		'private_supply' => __( 'Private Supply', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_water_types', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_water_type( $type )
{
	$types = get_water_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_broadband_types()
{
	$types = array(
		'adsl' => __( 'ADSL', 'propertyhive' ),
		'cable' => __( 'Cable', 'propertyhive' ),
		'fttc' => __( 'FTTC (Fibre to the Cabinet)', 'propertyhive' ),
		'fttp' => __( 'FTTP (Fibre to the Premises)', 'propertyhive' ),
		'none' => __( 'None', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_broadband_types', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_broadband_type( $type )
{
	$types = get_broadband_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_heating_types()
{
	$types = array(
	    'air_conditioning' => __( 'Air Conditioning', 'propertyhive' ),
	    'central' => __( 'Central Heating', 'propertyhive' ),
	    'double_glazing' => __( 'Double Glazing', 'propertyhive' ),
	    'eco_friendly' => __( 'Eco-Friendly', 'propertyhive' ),
	    'electric' => __( 'Electric Heating', 'propertyhive' ),
	    'gas' => __( 'Gas Heating', 'propertyhive' ),
	    'gas_central' => __( 'Gas Central Heating', 'propertyhive' ),
	    'night_storage' => __( 'Night Storage Heating', 'propertyhive' ),
	    'oil' => __( 'Oil Heating', 'propertyhive' ),
	    'solar' => __( 'Solar Panels', 'propertyhive' ),
	    'solar_water' => __( 'Solar Water Heating', 'propertyhive' ),
	    'under_floor' => __( 'Underfloor Heating', 'propertyhive' ),
	    'wood_burner' => __( 'Wood Burner', 'propertyhive' ),
	    'open_fire' => __( 'Open Fire', 'propertyhive' ),
	    'biomass_boiler' => __( 'Biomass Boiler', 'propertyhive' ),
	    'ground_source_heat_pump' => __( 'Ground Source Heat Pump', 'propertyhive' ),
	    'air_source_heat_pump' => __( 'Air Source Heat Pump', 'propertyhive' ),
	    'solar_pv_thermal' => __( 'Solar PV Thermal', 'propertyhive' ),
	    'solar_thermal' => __( 'Solar Thermal Heating', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_heating_types', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_heating_type( $type )
{
	$types = get_broadband_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_sewerage_types()
{
	$types = array(
		'mains_supply' => __( 'Mains Supply', 'propertyhive' ),
		'private_supply' => __( 'Private Supply', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_sewerage_types', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_sewerage_type( $type )
{
	$types = get_sewerage_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_flooding_source_types()
{
	$types = array(
    	'river' => __( 'River', 'propertyhive' ),
	    'sea' => __( 'Sea', 'propertyhive' ),
	    'groundwater' => __( 'Groundwater', 'propertyhive' ),
	    'lake' => __( 'Lake', 'propertyhive' ),
	    'reservoir' => __( 'Reservoir', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_flooding_source_types', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_flooding_source_type( $type )
{
	$types = get_flooding_source_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_accessibility_types()
{
	$types = array(
	    'lateral_living' => __( 'Lateral Living', 'propertyhive' ),
	    'level_access' => __( 'Level Access', 'propertyhive' ),
	    'lift_access' => __( 'Lift Access', 'propertyhive' ),
	    'step_free_access' => __( 'Step-Free Access', 'propertyhive' ),
	    'wet_room' => __( 'Wet Room', 'propertyhive' ),
	    'level_access_shower' => __( 'Level Access Shower', 'propertyhive' ),
	    'wide_doorways' => __( 'Wide Doorways', 'propertyhive' ),
	    'ramped_access' => __( 'Ramped Access', 'propertyhive' ),
	    'unsuitableForWheelchairs' => __( 'Unsuitable for Wheelchairs', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_accessibility_types', $types );

	asort($types);

	return $types;
}

function get_accessibility_type( $type )
{
	$types = get_accessibility_types();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_restrictions()
{
	$types = array(
	    'conservation_area' => __( 'Restrictions due to being in a conservation area', 'propertyhive' ),
	    'lease_restrictions' => __( 'Restrictions in the lease', 'propertyhive' ),
	    'listed_building' => __( 'Restrictions due to being listed', 'propertyhive' ),
	    'permitted_development' => __( 'Restrictions on property development', 'propertyhive' ),
	    'real_burdens' => __( 'A real burden applies to this property (Scotland only)', 'propertyhive' ),
	    'holiday_home_rental' => __( 'Holiday home rental restrictions', 'propertyhive' ),
	    'restrictive_covenant' => __( 'Restrictive covenants', 'propertyhive' ),
	    'business_from_property' => __( 'Restrictions on running a business', 'propertyhive' ),
	    'property_subletting' => __( 'Restrictions regarding subletting', 'propertyhive' ),
	    'tree_preservation_order' => __( 'Tree preservation orders', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_restrictions', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_restriction( $type )
{
	$types = get_restrictions();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

function get_rights()
{
	$types = array(
	    'right_of_way_public' => __( 'Public rights of way', 'propertyhive' ),
	    'right_of_way_private' => __( 'Private rights of way', 'propertyhive' ),
	    'registered_easements_hmlr' => __( 'Registered easements/rights with the HMLR (Land Registry)', 'propertyhive' ),
	    'servitudes' => __( 'Servitudes  (Scotland only)', 'propertyhive' ),
	    'shared_driveway' => __( 'Shared driveway', 'propertyhive' ),
	    'loft_access' => __( 'Loft access', 'propertyhive' ),
	    'drain_access' => __( 'Drain access', 'propertyhive' ),
	);

	$types = apply_filters( 'propertyhive_rights', $types );

	asort($types);

	$types['other'] = __( 'Other', 'propertyhive' );

	return $types;
}

function get_right( $type )
{
	$types = get_rights();

	if ( isset($types[$type]) )
	{
		return $types[$type];
	}

	return '';
}

add_filter( 'get_post_metadata', function ( $value, $post_id, $meta_key, $single ) 
{
	static $is_recursing = false; // Used to prevent infinite loop

	// Only filter if we're not recursing and if it is a post thumbnail ID
	if ( ! $is_recursing && $meta_key === '_thumbnail_id' && get_post_type( $post_id ) == 'property' ) 
	{
		$is_recursing = true;

		$value = get_post_thumbnail_id( $post_id );

		$is_recursing = false;

		if ( empty($value) ) // If we haven't already get a thumbnail ID (i.e. in the case where someone has added theme support)
		{
			$photos = get_post_meta( $post_id, '_photos', TRUE );
	        if ( is_array($photos) && !empty($photos) )
	        {
	            $photos = array_filter( $photos );
	            $value = $photos[0];
	        }
		}

		if ( ! $single ) 
		{
			$value = array( $value );
		}
	}
	return $value;
}, 10, 4);

add_filter( 'wpseo_add_opengraph_images', function ( $object ) 
{
	if ( get_option('propertyhive_images_stored_as', '') != 'urls' )
	{
		return $object;
	}

	global $post;

	if ( isset($post->post_type) && $post->post_type == 'property' )
	{
		$property = new PH_Property($post->ID);
		$photos = $property->_photo_urls;

	    if (isset($photos) && is_array($photos) && !empty($photos) && isset($photos[0]) && isset($photos[0]['url']))
	    {
	        $secondary_image = array( 'url' => $photos[0]['url']/*, 'height' => 768, 'width' => 1024*/ );
			$object->add_image( $secondary_image );
	    }
	}

	return $object;
});