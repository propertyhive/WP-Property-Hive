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
                'key'   => 'on_market',
                'value' => 'yes'
            ),
			array(
				'key' 	=> 'featured',
				'value' => 'yes'
			)
		),
		'fields' => 'id=>parent'
	) );

	$featured_property_ids = array_keys( $property_ids );

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
		$api_key = get_option('propertyhive_google_maps_api_key');
	    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
	    wp_enqueue_script('googlemaps');

	    echo '<div id="property_map_canvas" style="height:' . str_replace( "px", "", ( ( isset($args['height']) && !empty($args['height']) ) ? $args['height'] : '400' ) ) . 'px"></div>';
?>
<script>

	// We declare vars globally so developers can access them
	var property_map; // Global declaration of the map
	var property_marker; // Global declaration of the marker
			
	function initialize_property_map() {
				
		var myLatlng = new google.maps.LatLng(<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>);
		var myOptions = {
	  		zoom: <?php echo ( ( isset($args['zoom']) && !empty($args['zoom']) ) ? $args['zoom'] : '14' ); ?>,
			center: myLatlng,
	  		mapTypeId: google.maps.MapTypeId.ROADMAP,
	  		scrollwheel: <?php echo ( ( isset($args['scrollwheel']) && ($args['scrollwheel'] === 'false' || $args['scrollwheel'] === FALSE) ) ? 'false' : 'true' ); ?>
	  	}
		property_map = new google.maps.Map(document.getElementById("property_map_canvas"), myOptions);
				
		var myLatlng = new google.maps.LatLng(<?php echo $property->latitude; ?>, <?php echo $property->longitude; ?>);
			
		var markerOptions = {
			map: property_map,
			position: myLatlng		
		};
		property_marker = new google.maps.Marker(markerOptions);
	}
	
	if(window.addEventListener) {
		window.addEventListener('load', initialize_property_map);
	}else{
		window.attachEvent('onload', initialize_property_map);
	}

</script>
<?php
	}
}

function get_property_street_view( $args = array() )
{
	global $property;

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
		var myOptions = {
			center: myLatlng
	  	}
		property_street_view = new google.maps.Map(document.getElementById("property_street_view_canvas"), myOptions);
				
		var streetViewOptions = {
	    	position: myLatlng,
			pov: {
				heading: 90,
				pitch: 0,
				zoom: 0
			}
		};
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

add_filter( 'get_post_metadata', function ( $value, $post_id, $meta_key, $single ) 
{
	static $is_recursing = false; // Used to prevent infinite loop

	// Only filter if we're not recursing and if it is a post thumbnail ID
	if ( ! $is_recursing && $meta_key === '_thumbnail_id' && get_post_type( $post_id ) == 'property' ) 
	{
		$is_recursing = true;

		$value = get_post_thumbnail_id( $post_id );

		$is_recursing = false;

		if ( $value == '' ) // If we haven't already get a thumbnail ID (i.e. in the case where someone has added theme support)
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