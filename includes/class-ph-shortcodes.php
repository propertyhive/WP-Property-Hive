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
			'properties'                   => __CLASS__ . '::properties',
			'recent_properties'            => __CLASS__ . '::recent_properties',
			'featured_properties'          => __CLASS__ . '::featured_properties',
			'similar_properties'           => __CLASS__ . '::similar_properties',
			'property_search_form'         => __CLASS__ . '::property_search_form',
			'property_map'                 => __CLASS__ . '::property_map',
			'property_street_view'         => __CLASS__ . '::property_street_view',
			'property_office_details'      => __CLASS__ . '::property_office_details',
			'applicant_registration_form'  => __CLASS__ . '::applicant_registration_form',
			'propertyhive_my_account'  	   => __CLASS__ . '::my_account',
			'propertyhive_login_form'  	   => __CLASS__ . '::login_form',
		    'featured_property_carousel'   => __CLASS__ . '::featured_property_carousel',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
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
	 * Output property search form
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function property_search_form( $atts ) {
		$atts = shortcode_atts( array(
			'id' 				=> 'shortcode'
		), $atts );

		$form_controls = ph_get_search_form_fields();

		$form_controls = apply_filters( 'propertyhive_search_form_fields_' . $atts['id'], $form_controls );

		// We 100% need department so make sure it exists. If it doesn't, set a hidden field
	    if ( !isset($form_controls['department']) )
	    {
	        $original_form_controls = ph_get_search_form_fields();
	        $original_department = $original_form_controls['department'];
	        $original_department['type'] = 'hidden';

	        $form_controls['department'] = $original_department;
	    }

		ob_start();

		ph_get_template( 'global/search-form.php', array( 'form_controls' => $form_controls, 'id' => $atts['id'] ) );

		return ob_get_clean();
	}

	/**
	 * List multiple properties shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function properties( $atts ) {
		$atts = shortcode_atts( array(
			'columns' 			=> '2',
			'orderby' 			=> 'meta_value_num',
			'order'  			=> 'desc',
			'meta_key' 			=> '_price_actual',
			'ids'     			=> '',
			'department'		=> '', // residential-sales / residential-lettings / commercial
			'bedrooms'			=> '',
			'address_keyword'	=> '',
			'availability_id'	=> '',
			'marketing_flag'	=> '', // Deprecated. Use marketing_flag_id instead
			'marketing_flag_id'	=> '', // Should be marketing_flag_id. Might deprecate this in the future
			'property_type_id'	=> '',
			'location_id'		=> '',
			'office_id'			=> '',
			'posts_per_page'	=> 10
		), $atts, 'properties' );

		$meta_query = array(
			array(
				'key' 		=> '_on_market',
				'value' 	=> 'yes',
			)
		);

		if ( isset($atts['department']) && in_array($atts['department'], array("residential-sales", "residential-lettings", "commercial")) )
		{
			$meta_query[] = array(
				'key' => '_department',
				'value' => $atts['department'],
				'compare' => '='
			);
		}

		if ( isset($atts['bedrooms']) && $atts['bedrooms'] != '' && is_numeric($atts['bedrooms']) )
		{
			$meta_query[] = array(
				'key' => '_bedrooms',
				'value' => sanitize_text_field( $atts['bedrooms'] ),
				'compare' => '='
			);
		}

		if ( isset($atts['address_keyword']) && $atts['address_keyword'] != '' )
		{
			$atts['address_keyword'] = sanitize_text_field( trim( $atts['address_keyword'] ) );

        	$address_keywords = array( $atts['address_keyword'] );

        	if ( strpos( $atts['address_keyword'], ' ' ) !== FALSE )
        	{
        		$address_keywords[] = str_replace(" ", "-", $atts['address_keyword']);
        	}
        	if ( strpos( $atts['address_keyword'], '-' ) !== FALSE )
        	{
        		$address_keywords[] = str_replace("-", " ", $atts['address_keyword']);
        	}

			$sub_meta_query = array('relation' => 'OR');

			foreach ( $address_keywords as $address_keyword )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_reference_number',
				    'value'   => $address_keyword,
				    'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
				);
	      		$sub_meta_query[] = array(
				    'key'     => '_address_street',
				    'value'   => $address_keyword,
				    'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
				);
      			$sub_meta_query[] = array(
				    'key'     => '_address_two',
				    'value'   => $address_keyword,
				    'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_three',
				    'value'   => $address_keyword,
				    'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_four',
				    'value'   => $address_keyword,
				    'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
				);
	      	}
	      	if ( strlen($atts['address_keyword']) <= 4 )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ),
				    'compare' => '='
				);
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ) . '[ ]',
				    'compare' => 'RLIKE'
				);
	      	}
	      	else
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ),
				    'compare' => 'LIKE'
				);
	      	}

	      	$meta_query[] = $sub_meta_query;
		}

		if ( isset($atts['office_id']) && $atts['office_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_office_id',
				'value' => explode(",", $atts['office_id']),
				'compare' => 'IN',
			);
		}

		$tax_query = array();

		if ( isset($atts['availability_id']) && $atts['availability_id'] != '' )
		{
			$tax_query[] = array(
                'taxonomy'  => 'availability',
                'terms' => explode(",", $atts['availability_id']),
                'compare' => 'IN',
            );
		}

		// Fallback for deprecated marketing_flag
		if ( isset($atts['marketing_flag']) && $atts['marketing_flag'] != '' )
		{
			$atts['marketing_flag_id'] = $atts['marketing_flag'];
		}
		if ( isset($atts['marketing_flag_id']) && $atts['marketing_flag_id'] != '' )
		{
			$tax_query[] = array(
                'taxonomy'  => 'marketing_flag',
                'terms' => explode(",", $atts['marketing_flag_id']),
                'compare' => 'IN',
            );
		}

		if ( isset($atts['property_type_id']) && $atts['property_type_id'] != '' )
		{
			if ( isset($atts['department']) && $atts['department'] == 'commercial' )
			{
				$tax_query[] = array(
		            'taxonomy'  => 'commercial_property_type',
		            'terms' => explode(",", $atts['property_type_id']),
		            'compare' => 'IN',
		        );
			}
			else
			{
				$tax_query[] = array(
		            'taxonomy'  => 'property_type',
		            'terms' => explode(",", $atts['property_type_id']),
		            'compare' => 'IN',
		        );
			}
		}

		if ( isset($atts['location_id']) && $atts['location_id'] != '' )
		{
			$tax_query[] = array(
                'taxonomy'  => 'location',
                'terms' => explode(",", $atts['location_id']),
                'compare' => 'IN',
            );
		}

		// Change default meta key when department is specified as commercial, or if commercial is the only active department
		if ( 
			( isset($atts['department']) && $atts['department'] == 'commercial' ) ||
			(
				get_option( 'propertyhive_active_departments_sales' ) != 'yes' &&
				get_option( 'propertyhive_active_departments_lettings' ) != 'yes' &&
				get_option( 'propertyhive_active_departments_commercial' ) == 'yes'
			)
		)
		{
			$atts['meta_key'] = '_floor_area_from_sqft';
		}

		$args = array(
			'post_type'           => 'property',
			'post_status'         => ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
			'ignore_sticky_posts' => 1,
			'orderby'             => $atts['orderby'],
			'order'               => $atts['order'],
			'posts_per_page'      => $atts['posts_per_page'],
			'meta_query'		  => $meta_query,
			'tax_query'		  	  => $tax_query
		);
		if ( ! empty( $atts['meta_key'] ) ) {
			$args['meta_key'] = $atts['meta_key'];
		}

		if ( ! empty( $atts['ids'] ) ) {
			$args['post__in'] = array_map( 'trim', explode( ',', $atts['ids'] ) );
		}

		ob_start();

		$properties = new WP_Query( apply_filters( 'propertyhive_properties_query', $args, $atts ) );

		$propertyhive_loop['columns'] = $atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php propertyhive_property_loop_start(); ?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="propertyhive propertyhive-properties-shortcode columns-' . $atts['columns'] . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * Recent Properties shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function recent_properties( $atts ) {
		global $propertyhive_loop;

		$atts = shortcode_atts( array(
			'per_page' 		=> '12',
			'columns' 		=> '4',
			'department' 	=> '',
			'office_id'		=> '',
			'orderby' 		=> 'date',
			'order' 		=> 'desc'
		), $atts, 'recent_properties' );

		$meta_query = PH()->query->get_meta_query();

		if ( isset($atts['department']) && $atts['department'] != '' )
		{
			$meta_query[] = array(
				'key' => '_department',
				'value' => $atts['department'],
				'compare' => '='
			);
		}

		if ( isset($atts['office_id']) && $atts['office_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_office_id',
				'value' => explode(",", $atts['office_id']),
				'compare' => 'IN'
			);
		}

		$args = array(
			'post_type'				=> 'property',
			'post_status'			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> $atts['per_page'],
			'orderby' 				=> $atts['orderby'],
			'order' 				=> $atts['order'],
			'meta_query' 			=> $meta_query
		);

		ob_start();

		$properties = new WP_Query( apply_filters( 'propertyhive_shortcode_recent_properties_query', $args, $atts ) );

		$propertyhive_loop['columns'] = $atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php propertyhive_property_loop_start(); ?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property-recent' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="propertyhive propertyhive-recent-properties-shortcode columns-' . $atts['columns'] . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * Output featured properties
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function featured_properties( $atts ) {
		global $propertyhive_loop;

		$atts = shortcode_atts( array(
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'department' => '',
			'office_id'	=> '',
			'orderby' 	=> 'rand',
			'order' 	=> 'desc',
			'meta_key' 	=> '',
		), $atts, 'featured_properties' );

		$args = array(
			'post_type'				=> 'property',
			'post_status' 			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> $atts['per_page'],
			'orderby' 				=> $atts['orderby'],
			'order' 				=> $atts['order'],
		);

		$meta_query = array(
			array(
				'key' 		=> '_on_market',
				'value' 	=> 'yes',
			),
			array(
				'key' 		=> '_featured',
				'value' 	=> 'yes'
			)
		);

		if ( isset($atts['department']) && $atts['department'] != '' )
		{
			$meta_query[] = array(
				'key' => '_department',
				'value' => $atts['department'],
				'compare' => '='
			);
		}

		if ( isset($atts['office_id']) && $atts['office_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_office_id',
				'value' => explode(",", $atts['office_id']),
				'compare' => 'IN'
			);
		}

		$args['meta_query'] = $meta_query;

		if ( ! empty( $atts['meta_key'] ) ) {
			$args['meta_key'] = $atts['meta_key'];
		}

		ob_start();

		$properties = new WP_Query( apply_filters( 'propertyhive_shortcode_featured_properties_query', $args, $atts ) );

		$propertyhive_loop['columns'] = $atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php propertyhive_property_loop_start(); ?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property-featured' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="propertyhive propertyhive-featured-properties-shortcode columns-' . $atts['columns'] . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * Output featured property carousel
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function featured_property_carousel( $atts ) {
	    global $propertyhive_loop;
	    
	    $atts = shortcode_atts( array(
	        'per_page' 	=> '12',
	        'department' => '',
	        'office_id'	=> '',
	        'orderby' 	=> 'rand',
	        'order' 	=> 'desc',
	        'meta_key' 	=> '',
	    ), $atts, 'featured_property_carousel' );
	    
	    $args = array(
	        'post_type'				=> 'property',
	        'post_status' 			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
	        'ignore_sticky_posts'	=> 1,
	        'posts_per_page' 		=> $atts['per_page'],
	        'orderby' 				=> $atts['orderby'],
	        'order' 				=> $atts['order'],
	    );
	    
	    $meta_query = array(
	        array(
	            'key' 		=> '_on_market',
	            'value' 	=> 'yes',
	        ),
	        array(
	            'key' 		=> '_featured',
	            'value' 	=> 'yes'
	        )
	    );
	    
	    if ( isset($atts['department']) && $atts['department'] != '' )
	    {
	        $meta_query[] = array(
	            'key' => '_department',
	            'value' => $atts['department'],
	            'compare' => '='
	        );
	    }
	    
	    if ( isset($atts['office_id']) && $atts['office_id'] != '' )
	    {
	        $meta_query[] = array(
	            'key' => '_office_id',
	            'value' => explode(",", $atts['office_id']),
	            'compare' => 'IN'
	        );
	    }
	    
	    $args['meta_query'] = $meta_query;
	    
	    if ( ! empty( $atts['meta_key'] ) ) {
	        $args['meta_key'] = $atts['meta_key'];
	    }
	    
	    ob_start();
	    
	    $properties = new WP_Query( apply_filters( 'propertyhive_shortcode_featured_property_carousel_query', $args, $atts ) );
	    	    
		if ( $properties->have_posts() ) : ?>

			<ul class="properties clear slides">

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property-featured-carousel' ); ?>

				<?php endwhile; // end of the loop. ?>

			</ul>

		<?php endif;

		wp_reset_postdata();

		return '<div class="propertyhive propertyhive-featured-property-carousel-shortcode flexslider">' . ob_get_clean() . '</div>';
	}
	
	/**
	 * Output similar properties
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function similar_properties( $atts ) {

		$atts = shortcode_atts( array(
			'per_page'					=> '2',
			'columns'					=> '2',
			'orderby'					=> 'rand',
			'order'						=> 'asc',
			'price_percentage_bounds'	=> 10,
			'property_id'				=> '',
		), $atts, 'similar_properties' );

		if ($atts['property_id'] != '')
		{
			$department = get_post_meta( $atts['property_id'], '_department', true );
			$price = get_post_meta( $atts['property_id'], '_price_actual', true );
			$lower_price = $price;
			$higher_price = $price;
			$atts['price_percentage_bounds'] = str_replace("%", "", $atts['price_percentage_bounds']);
			if ( isset($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] != '' && is_numeric($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] > 0 )
			{
				$lower_price = $price - ($price * $atts['price_percentage_bounds'] / 100);
				$higher_price = $price + ($price * $atts['price_percentage_bounds'] / 100);
			}
			$bedrooms = get_post_meta( $atts['property_id'], '_bedrooms', true );

			$args = array(
				'post_type'				=> 'property',
				'post__not_in' 			=> array($atts['property_id']),
				'post_status' 			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' 		=> $atts['per_page'],
				'orderby' 				=> $atts['orderby'],
				'order' 				=> $atts['order'],
			);

			$meta_query = array();

			$meta_query[] = array(
				'key' 		=> '_department',
				'value' 	=> $department,
			);

			$meta_query[] = array(
				'key' 		=> '_on_market',
				'value' 	=> 'yes',
			);

			$meta_query[] = array(
				'key' 		=> '_bedrooms',
				'value' 	=> $bedrooms,
				'type'      => 'NUMERIC'
			);

			$meta_query[] = array(
				'key' 		=> '_price_actual',
				'value' 	=> $lower_price,
				'compare'   => '>=',
				'type'      => 'NUMERIC'
			);

			$meta_query[] = array(
				'key' 		=> '_price_actual',
				'value' 	=> $higher_price,
				'compare'   => '<=',
				'type'      => 'NUMERIC'
			);

			$args['meta_query'] = $meta_query;

			ob_start();

			$properties = new WP_Query( apply_filters( 'propertyhive_shortcode_similar_properties_query', $args, $atts ) );

			$propertyhive_loop['columns'] = $atts['columns'];

			if ( $properties->have_posts() ) : ?>

				<?php propertyhive_property_loop_start(); ?>

					<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

						<?php ph_get_template_part( 'content', 'property-featured' ); ?>

					<?php endwhile; // end of the loop. ?>

				<?php propertyhive_property_loop_end(); ?>

			<?php endif;

			wp_reset_postdata();
		}
		else
		{
			echo 'No property_id passed into similar_properties shortcode';
		}

		return '<div class="propertyhive propertyhive-similar-properties-shortcode columns-' . $atts['columns'] . '">' . ob_get_clean() . '</div>';
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
			'height'        => '400',
			'zoom'          => '14',
			'scrollwheel'   => 'true'
		), $atts, 'property_map' );

		ob_start();

		echo get_property_map( $atts );

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
    
    	$form_controls_2 = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls_2 );

    	ph_get_template( 'account/applicant-registration-form.php', array( 'form_controls' => array_merge( $form_controls, $form_controls_2 ) ) );

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
}
