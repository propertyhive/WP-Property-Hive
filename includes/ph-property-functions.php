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
 * @param array $args (default: array()) Contains all arguments to be used to get this product.
 * @return PH_Product
 */
function get_property( $the_property = false, $args = array() ) {
	return new PH_Property( $the_property );
}

/**
 * Function that returns an array containing the IDs of the featured products.
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

	global $post, $product;

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

add_action( 'template_redirect', 'ph_track_property_view', 20 );
