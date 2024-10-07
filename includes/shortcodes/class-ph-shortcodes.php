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

		require_once(__DIR__ . '/shortcodes/ph-shortcode-search_form.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-properties.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-featured_properties.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-recent_properties.php');
		require_once(__DIR__ . '/shortcodes/properties/class-ph-shortcode-similar_properties.php');

		new PH_Shortcode_Search_form();
		new PH_Shortcode_Properties();
		new PH_Shortcode_Recent_Properties();
		new PH_Shortcode_Similar_Properties();
		new PH_Shortcode_Featured_Properties();
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
	 * Output property map
	 * Should only be used on a property page or where the $property var is set
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function property_map( $atts ) {

		global $property;

		$atts = shortcode_atts( array(
			'id'        	=> '',
			'height'        => '400',
			'zoom'          => '14',
			'scrollwheel'   => 'true',
			'init_on_load'  => 'true'
		), $atts, 'property_map' );

		ob_start();

		echo get_property_map( $atts );

		return ob_get_clean();
	}

	/**
	 * Output static (image) property map
	 * Should only be used on a property page or where the $property var is set
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function property_static_map( $atts ) {

		global $property;

		$atts = shortcode_atts( array(
			'id'        	=> '',
			'height'        => '400',
			'zoom'          => '14',
			'link'        	=> 'true',
		), $atts, 'property_static_map' );

		ob_start();

		echo get_property_static_map( $atts );

		return ob_get_clean();
	}

	/**
	 * Output property street view
	 * Should only be used on a property page or where the $property var is set
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function property_street_view( $atts ) {

		global $property;

		$atts = shortcode_atts( array(
			'height'        => '400',
		), $atts, 'property_street_view' );

		ob_start();

		echo get_property_street_view( $atts );

		return ob_get_clean();
	}

	/**
	 * Output property office details
	 * Should only be used on a property page or where the $property var is set
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function property_office_details( $atts ) {

		global $property;

		$atts = shortcode_atts( array(
			'address_separator' => '<br>',
			'hyperlink_telephone_number' => true,
			'hyperlink_email_address' => true,
		), $atts, 'property_office_details' );

		$atts['hyperlink_telephone_number'] = (($atts['hyperlink_telephone_number'] === 'true' || $atts['hyperlink_telephone_number'] === true) ? true : false);
		$atts['hyperlink_email_address'] = (($atts['hyperlink_email_address'] === 'true' || $atts['hyperlink_email_address'] === true) ? true : false);

		ob_start();

		if ( $property->office_id != '' )
		{
			echo '<div class="property-office-details">';

				if ( $property->office_name != '' )
				{
					echo '<div class="office-name">' . $property->office_name . '</div>';
				}

				if ( $property->get_office_address( $atts['address_separator'] ) != '' )
				{
					echo '<div class="office-address">' . $property->get_office_address( $atts['address_separator'] ) . '</div>';
				}

				if ( $property->office_telephone_number != '' )
				{
					echo '<div class="office-telephone-number">' . ( ($atts['hyperlink_telephone_number'] === true) ? '<a href="tel:' . $property->office_telephone_number . '">' : '' ) . $property->office_telephone_number . ( ($atts['hyperlink_telephone_number'] === true) ? '</a>' : '' ) .  '</div>';
				}

				if ( $property->office_email_address != '' )
				{
					echo '<div class="office-email-address">' . ( ($atts['hyperlink_email_address'] === true) ? '<a href="mailto:' . $property->office_email_address . '">' : '' ) . $property->office_email_address . ( ($atts['hyperlink_email_address'] === true) ? '</a>' : '' ) .  '</div>';
				}

			echo '</div>';
		}

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

	/**
	 * Output applicant registration form
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function applicant_registration_form( $atts ) {

		$atts = shortcode_atts( array(

		), $atts, 'applicant_registration_form' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		if ( is_user_logged_in() )
		{
			ph_get_template( 'account/already-logged-in.php' );
			return ob_get_clean();
		}

		$form_controls = ph_get_user_details_form_fields();

    	$form_controls = apply_filters( 'propertyhive_user_details_form_fields', $form_controls );

    	$form_controls_2 = ph_get_applicant_requirements_form_fields();

    	$form_controls_2 = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls_2, false );

    	$form_controls = array_merge( $form_controls, $form_controls_2 );

    	if ( get_option( 'propertyhive_applicant_registration_form_disclaimer', '' ) != '' )
	    {
	        $disclaimer = get_option( 'propertyhive_applicant_registration_form_disclaimer', '' );

	        $form_controls['disclaimer'] = array(
	            'type' => 'checkbox',
	            'label' => $disclaimer,
	            'label_style' => 'width:100%;',
	            'required' => true
	        );
	    }

    	$form_controls = apply_filters( 'propertyhive_applicant_registration_form_fields', $form_controls );

    	ph_get_template( 'account/applicant-registration-form.php', array( 'form_controls' => $form_controls ) );

		return ob_get_clean();
	}

	/**
	 * Output 'Login' page
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function login_form( $atts )
	{
		$atts = shortcode_atts( array(

		), $atts, 'login_form' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		if ( is_user_logged_in() )
		{
			ph_get_template( 'account/already-logged-in.php' );
			return ob_get_clean();
		}

		// Check 'propertyhive_applicant_users' setting is enabled
		if ( get_option( 'propertyhive_applicant_users', '' ) != 'yes' )
   		{
   			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		ph_get_template( 'account/login-form.php' );

		return ob_get_clean();

	}

	/**
	 * Output 'Reset Password' page
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function reset_password_form( $atts )
	{
		$atts = shortcode_atts( array(

		), $atts, 'reset_password_form' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		if ( is_user_logged_in() )
		{
			ph_get_template( 'account/already-logged-in.php' );
			return ob_get_clean();
		}

		// Check 'propertyhive_applicant_users' setting is enabled
		if ( get_option( 'propertyhive_applicant_users', '' ) != 'yes' )
   		{
   			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		// check key provided is valid
		if ( !isset($_GET['key']) || empty(ph_clean($_GET['key'])) || !isset($_GET['id']) || empty(absint($_GET['id'])) )
		{
			echo esc_html(__( 'Invalid key or id provided. Please try again', 'propertyhive' ));
			return ob_get_clean();
		}

		$key = ph_clean($_GET['key']);
		$user_id = absint($_GET['id']);

		$userdata = get_userdata( $user_id );
		$user_login = $userdata ? $userdata->user_login : '';

		$user = check_password_reset_key( $key, $user_login );

		if ( is_wp_error( $user ) ) 
		{
			echo esc_html(__( 'This key is invalid or has already been used. Please reset your password again if needed.', 'propertyhive' ));
			return ob_get_clean();
		}

		ph_get_template( 'account/reset-password-form.php', array( 'reset_key' => $key, 'reset_login' => $user_login ) );

		return ob_get_clean();

	}

	/**
	 * Output 'My Account' page
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function my_account( $atts )
	{
		$atts = shortcode_atts( array(

		), $atts, 'my_account' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		// Check user is logged in
		if ( !is_user_logged_in() )
		{
			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		// Check 'propertyhive_applicant_users' setting is enabled
		if ( get_option( 'propertyhive_applicant_users', '' ) != 'yes' )
   		{
   			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		ph_get_template( 'account/my-account.php' );

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
