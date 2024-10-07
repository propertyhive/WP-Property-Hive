<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Shortcodes class.
 *
 * @class 		PH_Shortcodes
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes
		$shortcodes = array(
			'office_map'                   => __CLASS__ . '::office_map',
		);

		require_once(__DIR__ . "/shortcodes/class-ph-shortcode.php");

		require_once(__DIR__ . '/shortcodes/forms/class-ph-shortcode-applicant_registration_form.php');
		require_once(__DIR__ . '/shortcodes/forms/class-ph-shortcode-login_form.php');
		require_once(__DIR__ . '/shortcodes/forms/class-ph-shortcode-reset_password_form.php');
		require_once(__DIR__ . '/shortcodes/forms/class-ph-shortcode-search_form.php');


		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-properties.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-featured_properties.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-recent_properties.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-similar_properties.php');

		require_once(__DIR__ . '/shortcodes/property/class-ph-shortcode-property_map.php');
		require_once(__DIR__ . '/shortcodes/property/class-ph-shortcode-property_office_details.php');
		require_once(__DIR__ . '/shortcodes/property/class-ph-shortcode-property_static_map.php');
		require_once(__DIR__ . '/shortcodes/property/class-ph-shortcode-property_street_view.php');

		require_once(__DIR__ . '/shortcodes/class-ph-shortcode-my_account.php');
	}

	/**
	 * Shortcode Wrapper
	 *
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'propertyhive',
			'before' => null,
			'after'  => null
		)
	) {
		ob_start();

		$before 	= empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after 		= empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	 * Output office map
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function office_map( $atts ) {

		$offices_with_lat_lng = 0;
		if ( !isset($atts['office_id']) || ( isset($atts['office_id']) && $atts['office_id'] == '' ) )
		{
			$args = array(
				'post_type' => 'office',
				'nopaging' => true
			);

			$office_query = new WP_Query( $args );

			if ( $office_query->have_posts() )
			{
				while ( $office_query->have_posts() )
				{
					$office_query->the_post();

					$lat = get_post_meta(get_the_ID(), '_office_latitude', TRUE);
					$lng = get_post_meta(get_the_ID(), '_office_longitude', TRUE);

					if ( $lat != '' && $lng != '' )
					{
						++$offices_with_lat_lng;
					}
				}
			}
			wp_reset_postdata();
		}

		$atts = shortcode_atts( array(
			'office_id'     => '', // if wanting to show map for a particular office
			'height'        => '400',
			'zoom'          => ( ( ( !isset($atts['office_id']) || ( isset($atts['office_id']) && $atts['office_id'] == '' ) ) && !isset($atts['zoom']) && $offices_with_lat_lng > 1 ) ? 'auto' : '14' ),
			'scrollwheel'   => 'true'
		), $atts, 'office_map' );

		ob_start();

		$api_key = get_option('propertyhive_google_maps_api_key', '');
	    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
	    wp_enqueue_script('googlemaps');

	    echo '<div id="office_map_canvas" style="height:' . str_replace( "px", "", ( ( isset($atts['height']) && !empty($atts['height']) ) ? $atts['height'] : '400' ) ) . 'px"></div>';
?>
<script>

	// We declare vars globally so developers can access them
	var office_map; // Global declaration of the map
	var office_marker;
	var ph_office_map_lat_lngs = new Array();

	function initialize_office_map() {

		<?php
			$args = array(
				'post_type' => 'office',
				'posts_per_page' => 1
			);

			if ( isset($atts['office_id']) && (int)$atts['office_id'] != 0 )
			{
				$args['p'] = (int)$atts['office_id'];
			}
			else
			{
				$args['meta_query'] = array(
					array(
						'key' => 'primary',
						'value' => '1'
					)
				);
			}

			$office_query = new WP_Query( $args );

			$lat = '';
			$lng = '';

			if ( $office_query->have_posts() )
			{
				while ( $office_query->have_posts() )
				{
					$office_query->the_post();

					$lat = get_post_meta(get_the_ID(), '_office_latitude', TRUE);
					$lng = get_post_meta(get_the_ID(), '_office_longitude', TRUE);
				}
			}
			if ( $lat == '' || $lng == '' )
			{
				$lat = '51.509865';
				$lng = '-0.118092';
			}
		?>
		var myLatlng = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>);
		var map_options = {
	  		zoom: <?php echo ( ( isset($atts['zoom']) && !empty($atts['zoom']) && $atts['zoom'] != 'auto' ) ? $atts['zoom'] : '14' ); ?>,
			center: myLatlng,
	  		mapTypeId: google.maps.MapTypeId.ROADMAP,
	  		scrollwheel: <?php echo ( ( isset($atts['scrollwheel']) && ($atts['scrollwheel'] === 'false' || $atts['scrollwheel'] === FALSE) ) ? 'false' : 'true' ); ?>
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

  			do_action( 'propertyhive_office_map_options' );
  		?>
		office_map = new google.maps.Map(document.getElementById("office_map_canvas"), map_options);

		<?php
			$args = array(
				'post_type' => 'office',
				'nopaging' => true
			);

			if ( isset($atts['office_id']) && (int)$atts['office_id'] != 0 )
			{
				$args['p'] = (int)$atts['office_id'];
			}

			$office_query = new WP_Query( $args );

			if ( $office_query->have_posts() )
			{
				while ( $office_query->have_posts() )
				{
					$office_query->the_post();

					$lat = get_post_meta(get_the_ID(), '_office_latitude', TRUE);
					$lng = get_post_meta(get_the_ID(), '_office_longitude', TRUE);

					if ( $lat != '' && $lng != '' )
					{
		?>
		var myLatlng = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>);

		var marker_options = {
			map: office_map,
			position: myLatlng,
			title: "<?php echo esc_attr(get_the_title()); ?>"
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
		                echo 'marker_options.icon = \'' . $marker_icon_url . '\';';
		            }
		        }
		    }
		?>

		<?php do_action( 'propertyhive_office_map_marker_options' ); ?>

		office_marker = new google.maps.Marker(marker_options);
		ph_office_map_lat_lngs.push(office_marker.getPosition());
		<?php
					}
				}
			}
			wp_reset_postdata();

			if ( $atts['zoom'] == 'auto' ) { echo 'ph_fit_office_map_to_bounds();'; }
		?>
	}

	function ph_fit_office_map_to_bounds()
	{
        var bounds = new google.maps.LatLngBounds();
        if ( ph_office_map_lat_lngs.length > 0 )
        {
            for ( var i = 0; i < ph_office_map_lat_lngs.length; i++ )
            {
                bounds.extend(ph_office_map_lat_lngs[i]);
            }
            office_map.fitBounds(bounds);
        }
	}

	if(window.addEventListener) {
		window.addEventListener('load', initialize_office_map);
	}else{
		window.attachEvent('onload', initialize_office_map);
	}

</script>
<?php
		return ob_get_clean();
	}

	private static function get_show_order_args( $atts, $args )
	{
		$orderby = '';

		if ( isset( $_GET['orderby'] ) && $_GET['orderby'] != '' )
		{
			$PH_Query = new PH_Query();
			$ordering_args = $PH_Query->get_search_results_ordering_args();

			$args['orderby'] = $ordering_args['orderby'];
			$args['order'] = $ordering_args['order'];

			if ( isset( $ordering_args['meta_key'] ) )
			{
				$args['meta_key'] = $ordering_args['meta_key'];
			}
			else
			{
				unset($args['meta_key']);
			}
		}
		else
		{
			switch ( $atts['orderby'] )
			{
				case 'date':
					$orderby = 'date';
					break;
				case 'meta_value_num':

					switch ( $atts['meta_key'] )
					{
						case '_price_actual':
							$orderby = 'price';
							break;
						case '_floor_area_from_sqft':
							$orderby = 'floor_area';
							break;
					}

					if ( $orderby != '' && !empty($atts['order']) )
					{
						$orderby .= '-' . $atts['order'];
					}
					break;
			}
		}

		return array( $args, $orderby );
	}
}
