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
			'property_static_map'          => __CLASS__ . '::property_static_map',
			'property_street_view'         => __CLASS__ . '::property_street_view',
			'property_office_details'      => __CLASS__ . '::property_office_details',
			'office_map'                   => __CLASS__ . '::office_map',
			'applicant_registration_form'  => __CLASS__ . '::applicant_registration_form',
			'propertyhive_my_account'  	   => __CLASS__ . '::my_account',
			'propertyhive_login_form'  	   => __CLASS__ . '::login_form',
			'propertyhive_reset_password_form' => __CLASS__ . '::reset_password_form',
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
			'id' 					=> 'shortcode',
			'default_department' 	=> ''
		), $atts, 'property_search_form' );

		$form_controls = ph_get_search_form_fields();

		$form_controls = apply_filters( 'propertyhive_search_form_fields_' . $atts['id'], $form_controls, $atts );
		$form_controls = apply_filters( 'propertyhive_search_form_fields', $form_controls, $atts );

		// We 100% need department so make sure it exists. If it doesn't, set a hidden field
	    if ( !isset($form_controls['department']) )
	    {
	        $original_form_controls = ph_get_search_form_fields();
	        $original_department = $original_form_controls['department'];
	        $original_department['type'] = 'hidden';

	        $form_controls['department'] = $original_department;
	    }

		$form_controls = apply_filters( 'propertyhive_search_form_fields_after_' . $atts['id'], $form_controls, $atts );
		$form_controls = apply_filters( 'propertyhive_search_form_fields_after', $form_controls, $atts );

	    if (
	    	isset($atts['default_department']) && in_array($atts['default_department'], array_keys( ph_get_departments() )) &&
	    	( !isset($_REQUEST['department']) )
	    )
	    {
	    	$form_controls['department']['value'] = $atts['default_department'];
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

		global $propertyhive_loop;

		$atts = shortcode_atts( array(
			'columns' 			=> '2',
			'orderby' 			=> 'meta_value_num',
			'order'  			=> 'desc',
			'meta_key' 			=> '_price_actual',
			'ids'     			=> '',
			'department'		=> '', // residential-sales / residential-lettings / commercial / any custom department
			'minimum_price'		=> '',
			'maximum_price'		=> '',
			'bedrooms'			=> '',
			'minimum_bedrooms'	=> '',
			'keyword'			=> '',
			'address_keyword'	=> '',
			'country'			=> '',
			'country_not'		=> '',
			'availability_id'	=> '',
			'marketing_flag'	=> '', // Deprecated. Use marketing_flag_id instead
			'marketing_flag_id'	=> '', // Should be marketing_flag_id. Might deprecate this in the future
			'property_type_id'	=> '',
			'sale_by_id'		=> '',
			'location_id'		=> '',
			'office_id'			=> '',
			'negotiator_id'		=> '',
			'commercial_for_sale' => '',
			'commercial_to_rent' => '',
			'posts_per_page'	=> 10,
			'no_results_output' => '',
			'pagination'        => '',
			'show_order'        => '',
			'show_result_count' => '',
			'carousel' 			=> '',
		), $atts, 'properties' );

		if ( isset($atts['carousel']) && !empty($atts['carousel']) )
		{
			$params = array(
				'items' => 1,
				'controlsPosition' => 'bottom',
				'gutter' => 20,
				'mouseDrag' => true,
				'nav' => false,
				'navPosition' => 'bottom',
				'controlsText' => array("Prev", "Next"),
				'responsive' => array(
					640 => array(
						'items' => (int)$atts['columns']
					)
				)
			);
			$params = apply_filters( 'propertyhive_carousel_params', $params );
			$params = apply_filters( 'propertyhive_properties_carousel_params', $params );
			wp_localize_script( 'propertyhive_carousel', 'propertyhive_carousel_params', $params );

			wp_enqueue_style( 'tiny_slider_css' );
			wp_enqueue_script( 'tiny_slider' );
			wp_enqueue_script( 'propertyhive_carousel' );
		}

		$meta_query = array(
			array(
				'key' 		=> '_on_market',
				'value' 	=> 'yes',
			)
		);

		if ( isset($atts['department']) && in_array($atts['department'], array_keys( ph_get_departments() )) )
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

		if ( isset($atts['minimum_bedrooms']) && $atts['minimum_bedrooms'] != '' && is_numeric($atts['minimum_bedrooms']) )
		{
			$meta_query[] = array(
				'key' => '_bedrooms',
				'value' => sanitize_text_field( $atts['minimum_bedrooms'] ),
				'compare' => '>='
			);
		}

		$base_department = $atts['department'];
		if ( $atts['department'] !== '' && !in_array($atts['department'], array_keys( ph_get_departments( true ) )) )
		{
			$base_department = ph_get_custom_department_based_on($base_department);
		}

		if ( isset($atts['department']) && $base_department == 'residential-sales' && isset($atts['minimum_price']) && $atts['minimum_price'] != '' )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$minimum_price = $atts['minimum_price'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $atts['minimum_price'] to GBP
        		$ph_countries = new PH_Countries();

        		$minimum_price = $ph_countries->convert_price_to_gbp( $minimum_price, $search_form_currency );
        	}

            $meta_query[] = array(
                'key'     => '_price_actual',
                'value'   => sanitize_text_field( floor( $minimum_price ) ),
                'compare' => '>=',
                'type'    => 'NUMERIC'
            );
        }

        if ( isset($atts['department']) && $base_department == 'residential-sales' && isset($atts['maximum_price']) && $atts['maximum_price'] != '' )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$maximum_price = $atts['maximum_price'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $atts['maximum_price'] to GBP
        		$ph_countries = new PH_Countries();

        		$maximum_price = $ph_countries->convert_price_to_gbp( $maximum_price, $search_form_currency );
        	}

            $meta_query[] = array(
                'key'     => '_price_actual',
                'value'   => sanitize_text_field( ceil( $maximum_price ) ),
                'compare' => '<=',
                'type'    => 'NUMERIC'
            );
        }

		if ( isset($atts['address_keyword']) && $atts['address_keyword'] != '' )
		{
			$atts['address_keyword'] = ph_clean( trim( $atts['address_keyword'] ) );

        	$address_keywords = array( $atts['address_keyword'] );

        	if ( strpos( $atts['address_keyword'], ' ' ) !== FALSE )
        	{
        		$address_keywords[] = str_replace(" ", "-", $atts['address_keyword']);
        	}
        	if ( strpos( $atts['address_keyword'], '-' ) !== FALSE )
        	{
        		$address_keywords[] = str_replace("-", " ", $atts['address_keyword']);
        	}
        	if ( strpos( $atts['address_keyword'], '.' ) !== FALSE )
			{
				$address_keywords[] = str_replace(".", "", $atts['address_keyword']);
			}
			if ( stripos( $atts['address_keyword'], 'st ' ) !== FALSE )
			{
				$address_keywords[] = str_ireplace("st ", "st. ", $atts['address_keyword']);
			}
			if ( strpos( $atts['address_keyword'], '\'' ) !== FALSE )
			{
				$address_keywords[] = str_replace("'", "", $atts['address_keyword']);
			}

			$sub_meta_query = array('relation' => 'OR');

			$address_keyword_compare = get_option( 'propertyhive_address_keyword_compare', '=' );
			if ( $address_keyword_compare == 'polygon' )
			{
				$address_keyword_compare = apply_filters('propertyhive_shortcode_address_keyword_compare', '=');
			}

			foreach ( $address_keywords as $address_keyword )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_reference_number',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
	      		$sub_meta_query[] = array(
				    'key'     => '_address_street',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
      			$sub_meta_query[] = array(
				    'key'     => '_address_two',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_three',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_four',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
	      	}
	      	if ( strlen($atts['address_keyword']) <= 4 )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ),
				    'compare' => '='
				);
				// Run regex match where given keyword is at the start of the postcode ^
				// followed by one or zero letters (for WC2E-style postcodes) [a-zA-Z]?
				// then a single space [ ]
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ) . '[a-zA-Z]?[ ]',
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

		if ( isset($atts['keyword']) && $atts['keyword'] != '' )
		{
			$atts['keyword'] = sanitize_text_field( trim( $atts['keyword'] ) );

			$original_keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
			$_REQUEST['keyword'] = $atts['keyword'];

			add_filter( 'posts_where', array( PH()->query, 'keyword_excerpt_where' ), 10, 2 );

			$keywords = array( $atts['keyword'] );

        	if ( strpos( $atts['keyword'], ' ' ) !== FALSE )
        	{
        		$keywords[] = str_replace(" ", "-", $atts['keyword']);
        	}
        	if ( strpos( $atts['keyword'], '-' ) !== FALSE )
        	{
        		$keywords[] = str_replace("-", " ", $atts['keyword']);
        	}
        	if ( strpos( $atts['keyword'], '.' ) !== FALSE )
			{
				$keywords[] = str_replace(".", "", $atts['keyword']);
			}
			if ( stripos( $atts['keyword'], 'st ' ) !== FALSE )
			{
				$keywords[] = str_ireplace("st ", "st. ", $atts['keyword']);
			}
			if ( strpos( $atts['keyword'], '\'' ) !== FALSE )
			{
				$keywords[] = str_replace("'", "", $atts['keyword']);
			}

			$sub_meta_query = array('relation' => 'OR');

			$address_keyword_compare = get_option( 'propertyhive_address_keyword_compare', '=' );
			if ( $address_keyword_compare == 'polygon' )
			{
				$address_keyword_compare = apply_filters('propertyhive_shortcode_address_keyword_compare', '=');
			}

			foreach ( $keywords as $keyword )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_reference_number',
				    'value'   => $keyword,
				    'compare' => $address_keyword_compare
				);
	      		$sub_meta_query[] = array(
				    'key'     => '_address_street',
				    'value'   => $keyword,
				    'compare' => $address_keyword_compare
				);
      			$sub_meta_query[] = array(
				    'key'     => '_address_two',
				    'value'   => $keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_three',
				    'value'   => $keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_four',
				    'value'   => $keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_features_concatenated',
				    'value'   => $keyword,
				    'compare' => 'LIKE'
				);
				$sub_meta_query[] = array(
				    'key'     => '_descriptions_concatenated',
				    'value'   => $keyword,
				    'compare' => 'LIKE'
				);
	      	}
	      	if ( strlen($atts['keyword']) <= 4 )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['keyword'] ),
				    'compare' => '='
				);
				// Run regex match where given keyword is at the start of the postcode ^
				// followed by one or zero letters (for WC2E-style postcodes) [a-zA-Z]?
				// then a single space [ ]
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['keyword'] ) . '[a-zA-Z]?[ ]',
				    'compare' => 'RLIKE'
				);
	      	}
	      	else
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['keyword'] ),
				    'compare' => 'LIKE'
				);
	      	}

	      	$meta_query[] = $sub_meta_query;

			$_REQUEST['keyword'] = $original_keyword; // reset back in case it's used elsewhere
		}

		if ( isset($atts['country']) && $atts['country'] != '' )
		{
			$meta_query[] = array(
				'key' => '_address_country',
				'value' => sanitize_text_field( $atts['country'] ),
				'compare' => '=',
			);
		}

		if ( isset($atts['country_not']) && $atts['country_not'] != '' )
		{
			$meta_query[] = array(
				'key' => '_address_country',
				'value' => sanitize_text_field( $atts['country_not'] ),
				'compare' => '!=',
			);
		}

		if ( isset($atts['office_id']) && $atts['office_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_office_id',
				'value' => explode(",", $atts['office_id']),
				'compare' => 'IN',
			);
		}

		if ( isset($atts['negotiator_id']) && $atts['negotiator_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_negotiator_id',
				'value' => explode(",", $atts['negotiator_id']),
				'compare' => 'IN',
			);
		}

		if ( isset($atts['commercial_for_sale']) && $atts['commercial_for_sale'] != '' )
		{
			$meta_query[] = array(
                'key'     => '_for_sale',
                'value'   => 'yes',
                'compare' => '=',
            );
		}

		if ( isset($atts['commercial_to_rent']) && $atts['commercial_to_rent'] != '' )
		{
			$meta_query[] = array(
                'key'     => '_to_rent',
                'value'   => 'yes',
                'compare' => '=',
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
			// Change field to check when department is specified as commercial, or if commercial is the only active department
			if (
				( isset($atts['department']) && $base_department == 'commercial' ) ||
				(
					!isset($atts['department']) &&
					get_option( 'propertyhive_active_departments_sales' ) != 'yes' &&
					get_option( 'propertyhive_active_departments_lettings' ) != 'yes' &&
					get_option( 'propertyhive_active_departments_commercial' ) == 'yes'
				)
			)
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

		if ( isset($atts['sale_by_id']) && $atts['sale_by_id'] != '' )
		{
			$tax_query[] = array(
                'taxonomy'  => 'sale_by',
                'terms' => explode(",", $atts['sale_by_id']),
                'compare' => 'IN',
            );
		}

		// Change default meta key when department is specified as commercial, or if commercial is the only active department
		if (
			( isset($atts['department']) && $base_department == 'commercial' ) ||
			(
				get_option( 'propertyhive_active_departments_sales' ) != 'yes' &&
				get_option( 'propertyhive_active_departments_lettings' ) != 'yes' &&
				get_option( 'propertyhive_active_departments_commercial' ) == 'yes'
			)
		)
		{
			$atts['meta_key'] = '_floor_area_from_sqft';
		}

		// Get which page we're currently viewing from the URL
		$paged = max( 1, get_query_var( 'paged' ) );

		$args = array(
			'post_type'           => 'property',
			'post_status'         => ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
			'ignore_sticky_posts' => 1,
			'orderby'             => $atts['orderby'],
			'order'               => $atts['order'],
			'posts_per_page'      => $atts['posts_per_page'],
			'paged'               => $paged,
			'meta_query'		  => $meta_query,
			'tax_query'		  	  => $tax_query,
			'has_password' 		  => false,
		);
		if ( ! empty( $atts['meta_key'] ) ) {
			$args['meta_key'] = $atts['meta_key'];
		}

		if ( ! empty( $atts['ids'] ) ) {
			$args['post__in'] = array_map( 'trim', explode( ',', $atts['ids'] ) );
		}
		if ( isset($atts['orderby']) && $atts['orderby'] == 'date' )
		{
			$args['orderby'] = 'meta_value';
			$args['meta_key'] = '_on_market_change_date';
		}

		$args['orderby'] .= ' post_title';

		ob_start();

        do_action('propertyhive_shortcode_properties_before_catalog_ordering');

		if ( isset($atts['show_order']) && $atts['show_order'] != '' )
		{
			list( $args, $orderby ) = self::get_show_order_args( $atts, $args );

			propertyhive_catalog_ordering( $atts['department'], $orderby );
		}

		$args = apply_filters( 'propertyhive_properties_query', $args, $atts );
		$args = apply_filters( 'propertyhive_shortcode_properties_query', $args, $atts );

		$properties = new WP_Query( $args );

		if ( isset($atts['show_result_count']) && $atts['show_result_count'] != '' )
		{
			$total_posts = $properties->found_posts;

			$first = ( $atts['posts_per_page'] * $paged ) - $atts['posts_per_page'] + 1;
			$last = min( $total_posts, $atts['posts_per_page'] * $paged );

			propertyhive_result_count( $paged, $atts['posts_per_page'], $total_posts, $first, $last);
		}

        do_action('propertyhive_shortcode_properties_after_result_count');

		$propertyhive_loop['columns'] = (int)$atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php 
				ob_start();
				propertyhive_property_loop_start(); 
				$loop_start = ob_get_clean();
				$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
				echo $loop_start;
			?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php else: ?>

            <?php echo $atts['no_results_output']; ?>

		<?php endif;

		if ( isset($atts['pagination']) && $atts['pagination'] != '' )
		{
			propertyhive_pagination( $properties->max_num_pages );
		}

		wp_reset_postdata();

		$shortcode_output = ob_get_clean();

		return apply_filters( 'propertyhive_properties_shortcode_output', '<div class="propertyhive propertyhive-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );
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
			'minimum_price'		=> '',
			'office_id'		=> '',
			'negotiator_id'		=> '',
			'availability_id'	=> '',
			'marketing_flag_id'	=> '',
			'property_type_id'	=> '',
			'sale_by_id'		=> '',
			'location_id'		=> '',
			'orderby' 		=> 'date',
			'order' 		=> 'desc',
			'no_results_output' => '',
			'pagination'        => '',
			'show_order'        => '',
			'show_result_count' => '',
			'carousel' 			=> '',
		), $atts, 'recent_properties' );

		if ( isset($atts['carousel']) && !empty($atts['carousel']) )
		{
			$params = array(
				'items' => 1,
				'controlsPosition' => 'bottom',
				'gutter' => 20,
				'mouseDrag' => true,
				'nav' => false,
				'navPosition' => 'bottom',
				'controlsText' => array("Prev", "Next"),
				'responsive' => array(
					640 => array(
						'items' => (int)$atts['columns']
					)
				)
			);
			$params = apply_filters( 'propertyhive_carousel_params', $params );
			$params = apply_filters( 'propertyhive_recent_properties_carousel_params', $params );
			wp_localize_script( 'propertyhive_carousel', 'propertyhive_carousel_params', $params );

			wp_enqueue_style( 'tiny_slider_css' );
			wp_enqueue_script( 'tiny_slider' );
			wp_enqueue_script( 'propertyhive_carousel' );
		}

		$meta_query = PH()->query->get_meta_query();

		if ( isset($atts['department']) && $atts['department'] != '' )
		{
			$meta_query[] = array(
				'key' => '_department',
				'value' => $atts['department'],
				'compare' => '='
			);
		}

		$base_department = $atts['department'];
		if ( $atts['department'] !== '' && !in_array($atts['department'], array_keys( ph_get_departments( true ) )) )
		{
			$base_department = ph_get_custom_department_based_on($base_department);
		}

		if ( isset($atts['department']) && $base_department == 'residential-sales' && isset($atts['minimum_price']) && $atts['minimum_price'] != '' )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$minimum_price = $atts['minimum_price'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $atts['minimum_price'] to GBP
        		$ph_countries = new PH_Countries();

        		$minimum_price = $ph_countries->convert_price_to_gbp( $minimum_price, $search_form_currency );
        	}

            $meta_query[] = array(
                'key'     => '_price_actual',
                'value'   => sanitize_text_field( floor( $minimum_price ) ),
                'compare' => '>=',
                'type'    => 'NUMERIC'
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

		if ( isset($atts['negotiator_id']) && $atts['negotiator_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_negotiator_id',
				'value' => explode(",", $atts['negotiator_id']),
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
			// Change field to check when department is specified as commercial, or if commercial is the only active department
			if (
				( isset($atts['department']) && $base_department == 'commercial' ) ||
				(
					!isset($atts['department']) &&
					get_option( 'propertyhive_active_departments_sales' ) != 'yes' &&
					get_option( 'propertyhive_active_departments_lettings' ) != 'yes' &&
					get_option( 'propertyhive_active_departments_commercial' ) == 'yes'
				)
			)
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

		if ( isset($atts['sale_by_id']) && $atts['sale_by_id'] != '' )
		{
			$tax_query[] = array(
                'taxonomy'  => 'sale_by',
                'terms' => explode(",", $atts['sale_by_id']),
                'compare' => 'IN',
            );
		}

		// Get which page we're currently viewing from the URL
		$paged = max( 1, get_query_var( 'paged' ) );

		$args = array(
			'post_type'				=> 'property',
			'post_status'			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> $atts['per_page'],
			'paged'					=> $paged,
			'orderby' 				=> $atts['orderby'],
			'order' 				=> $atts['order'],
			'meta_query' 			=> $meta_query,
			'tax_query' 			=> $tax_query,
			'has_password' 			=> false,
		);

		if ( isset($atts['orderby']) && $atts['orderby'] == 'date' )
		{
			$args['orderby'] = 'meta_value';
			$args['meta_key'] = '_on_market_change_date';
		}

		$args['orderby'] .= ' post_title';

		ob_start();

		if ( isset($atts['show_order']) && $atts['show_order'] != '' )
		{
			list( $args, $orderby ) = self::get_show_order_args( $atts, $args );

			propertyhive_catalog_ordering( $atts['department'], $orderby );
		}

		$properties = new WP_Query( apply_filters( 'propertyhive_shortcode_recent_properties_query', $args, $atts ) );

		if ( isset($atts['show_result_count']) && $atts['show_result_count'] != '' )
		{
			$total_posts = $properties->found_posts;

			$first = ( $atts['per_page'] * $paged ) - $atts['per_page'] + 1;
			$last = min( $total_posts, $atts['per_page'] * $paged );

			propertyhive_result_count( $paged, $atts['per_page'], $total_posts, $first, $last);
		}

		$propertyhive_loop['columns'] = (int)$atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php 
				ob_start();
				propertyhive_property_loop_start(); 
				$loop_start = ob_get_clean();
				$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
				echo $loop_start;
			?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property-recent' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php else: ?>

            <?php echo $atts['no_results_output']; ?>

		<?php endif;

		if ( isset($atts['pagination']) && $atts['pagination'] != '' )
		{
			propertyhive_pagination( $properties->max_num_pages );
		}

		wp_reset_postdata();

		$shortcode_output = ob_get_clean();

		return apply_filters( 'propertyhive_recent_properties_shortcode_output', '<div class="propertyhive propertyhive-recent-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );

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
			'address_keyword'	=> '',
			'office_id'	=> '',
			'negotiator_id'		=> '',
			'availability_id'	=> '',
			'orderby' 	=> 'rand',
			'order' 	=> 'desc',
			'meta_key' 	=> '',
			'no_results_output' => '',
			'pagination' => '',
			'show_order' => '',
			'show_result_count' => '',
			'carousel' 			=> '',
		), $atts, 'featured_properties' );

		if ( isset($atts['carousel']) && !empty($atts['carousel']) )
		{
			$params = array(
				'items' => 1,
				'controlsPosition' => 'bottom',
				'gutter' => 20,
				'mouseDrag' => true,
				'nav' => false,
				'navPosition' => 'bottom',
				'controlsText' => array("Prev", "Next"),
				'responsive' => array(
					640 => array(
						'items' => (int)$atts['columns']
					)
				)
			);
			$params = apply_filters( 'propertyhive_carousel_params', $params );
			$params = apply_filters( 'propertyhive_featured_properties_carousel_params', $params );
			wp_localize_script( 'propertyhive_carousel', 'propertyhive_carousel_params', $params );

			wp_enqueue_style( 'tiny_slider_css' );
			wp_enqueue_script( 'tiny_slider' );
			wp_enqueue_script( 'propertyhive_carousel' );
		}

		// Get which page we're currently viewing from the URL
		$paged = max( 1, get_query_var( 'paged' ) );

		$args = array(
			'post_type'				=> 'property',
			'post_status' 			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' 		=> $atts['per_page'],
			'paged'                 => $paged,
			'orderby' 				=> $atts['orderby'],
			'order' 				=> $atts['order'],
			'has_password' 			=> false,
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

			$address_keyword_compare = get_option( 'propertyhive_address_keyword_compare', '=' );
			if ( $address_keyword_compare == 'polygon' )
			{
				$address_keyword_compare = apply_filters('propertyhive_shortcode_address_keyword_compare', '=');
			}

			foreach ( $address_keywords as $address_keyword )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_reference_number',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
	      		$sub_meta_query[] = array(
				    'key'     => '_address_street',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
      			$sub_meta_query[] = array(
				    'key'     => '_address_two',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_three',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
				$sub_meta_query[] = array(
				    'key'     => '_address_four',
				    'value'   => $address_keyword,
				    'compare' => $address_keyword_compare
				);
	      	}
	      	if ( strlen($atts['address_keyword']) <= 4 )
	      	{
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ),
				    'compare' => '='
				);
				// Run regex match where given keyword is at the start of the postcode ^
				// followed by one or zero letters (for WC2E-style postcodes) [a-zA-Z]?
				// then a single space [ ]
	      		$sub_meta_query[] = array(
				    'key'     => '_address_postcode',
				    'value'   => sanitize_text_field( $atts['address_keyword'] ) . '[a-zA-Z]?[ ]',
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
				'compare' => 'IN'
			);
		}

		if ( isset($atts['negotiator_id']) && $atts['negotiator_id'] != '' )
		{
			$meta_query[] = array(
				'key' => '_negotiator_id',
				'value' => explode(",", $atts['negotiator_id']),
				'compare' => 'IN',
			);
		}

		$args['meta_query'] = $meta_query;

		if ( ! empty( $atts['meta_key'] ) ) {
			$args['meta_key'] = $atts['meta_key'];
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

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		if ( isset($atts['orderby']) && $atts['orderby'] == 'date' )
		{
			$args['orderby'] = 'meta_value';
			$args['meta_key'] = '_on_market_change_date';
		}

		$args['orderby'] .= ' post_title';

		ob_start();

		if ( isset($atts['show_order']) && $atts['show_order'] != '' )
		{
			list( $args, $orderby ) = self::get_show_order_args( $atts, $args );

			propertyhive_catalog_ordering( $atts['department'], $orderby );
		}
		
		$properties = new WP_Query( apply_filters( 'propertyhive_shortcode_featured_properties_query', $args, $atts ) );

		if ( isset($atts['show_result_count']) && $atts['show_result_count'] != '' )
		{
			$total_posts = $properties->found_posts;

			$first = ( $atts['per_page'] * $paged ) - $atts['per_page'] + 1;
			$last = min( $total_posts, $atts['per_page'] * $paged );

			propertyhive_result_count( $paged, $atts['per_page'], $total_posts, $first, $last);
		}

		$propertyhive_loop['columns'] = (int)$atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php 
				ob_start();
				propertyhive_property_loop_start(); 
				$loop_start = ob_get_clean();
				$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
				echo $loop_start;
			?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property-featured' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php else: ?>

            <?php echo $atts['no_results_output']; ?>

		<?php endif;

		if ( isset($atts['pagination']) && $atts['pagination'] != '' )
		{
			propertyhive_pagination( $properties->max_num_pages );
		}

		wp_reset_postdata();

		$shortcode_output = ob_get_clean();

		return apply_filters( 'propertyhive_featured_properties_shortcode_output', '<div class="propertyhive propertyhive-featured-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );
	}

	/**
	 * Output similar properties
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function similar_properties( $atts ) {

		global $property, $propertyhive_loop;

		$atts = shortcode_atts( array(
			'per_page'					=> '2',
			'columns'					=> '2',
			'orderby'					=> 'rand',
			'order'						=> 'asc',
			'price_percentage_bounds'	=> 10,
			'bedroom_bounds'			=> 0,
			'matching_address_field'	=> '', // only return fields with matching address field. Options: address_two, address_three, address_four, location
			'property_id'				=> '',
			'availability_id'	=> '',
			'no_results_output' => '',
			'carousel' 			=> '',
		), $atts, 'similar_properties' );

		if ( isset($atts['carousel']) && !empty($atts['carousel']) )
		{
			$params = array(
				'items' => 1,
				'controlsPosition' => 'bottom',
				'gutter' => 20,
				'mouseDrag' => true,
				'nav' => false,
				'navPosition' => 'bottom',
				'controlsText' => array("Prev", "Next"),
				'responsive' => array(
					640 => array(
						'items' => (int)$atts['columns']
					)
				)
			);
			$params = apply_filters( 'propertyhive_carousel_params', $params );
			$params = apply_filters( 'propertyhive_similar_properties_carousel_params', $params );
			wp_localize_script( 'propertyhive_carousel', 'propertyhive_carousel_params', $params );
			
			wp_enqueue_style( 'tiny_slider_css' );
			wp_enqueue_script( 'tiny_slider' );
			wp_enqueue_script( 'propertyhive_carousel' );
		}

		if ( $atts['property_id'] == '' && isset($property->id) )
		{
			$atts['property_id'] = $property->id;
		}

		if ($atts['property_id'] != '')
		{
			$department = get_post_meta( $atts['property_id'], '_department', true );

			$args = array(
				'post_type'				=> 'property',
				'post__not_in' 			=> array($atts['property_id']),
				'post_status' 			=> ( ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array('publish', 'private') : 'publish' ),
				'ignore_sticky_posts'	=> 1,
				'posts_per_page' 		=> $atts['per_page'],
				'orderby' 				=> $atts['orderby'],
				'order' 				=> $atts['order'],
				'has_password' 			=> false,
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

			if ( $department != 'commercial' && ph_get_custom_department_based_on( $department ) != 'commercial' )
			{
				// residential
				$bedrooms = get_post_meta( $atts['property_id'], '_bedrooms', true );
				$lower_bedrooms = $bedrooms;
				$higher_bedrooms = $bedrooms;
				if ( !empty($bedrooms) && isset($atts['bedroom_bounds']) && $atts['bedroom_bounds'] != '' && is_numeric($atts['bedroom_bounds']) && $atts['bedroom_bounds'] > 0 )
				{
					$lower_bedrooms = $bedrooms - (int)$atts['bedroom_bounds'];
					$higher_bedrooms = $bedrooms + (int)$atts['bedroom_bounds'];
				}

				if ( isset($atts['bedroom_bounds']) && is_numeric($atts['bedroom_bounds']) )
				{
					$meta_query[] = array(
						'key' 		=> '_bedrooms',
						'value' 	=> array( $lower_bedrooms, $higher_bedrooms ),
						'compare'   => 'BETWEEN',
						'type'      => 'NUMERIC'
					);
				}

				$price = get_post_meta( $atts['property_id'], '_price_actual', true );
				$lower_price = $price;
				$higher_price = $price;
				$atts['price_percentage_bounds'] = str_replace("%", "", $atts['price_percentage_bounds']);
				if ( !empty($price) && isset($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] != '' && is_numeric($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] > 0 )
				{
					$lower_price = $price - ($price * (int)$atts['price_percentage_bounds'] / 100);
					$higher_price = $price + ($price * (int)$atts['price_percentage_bounds'] / 100);
				}

				if ( isset($atts['price_percentage_bounds']) && is_numeric($atts['price_percentage_bounds']) )
				{
					$meta_query[] = array(
						'key' 		=> '_price_actual',
						'value' 	=> array( $lower_price, $higher_price ),
						'compare'   => 'BETWEEN',
						'type'      => 'NUMERIC'
					);
				}
			}
			else
			{
				// commercial
				$for_sale = get_post_meta( $atts['property_id'], '_for_sale', true );
				$to_rent = get_post_meta( $atts['property_id'], '_to_rent', true );

				if ( $for_sale == 'yes' || $to_rent == 'yes' )
				{	
					$sub_meta_query = array('relation' => 'OR');

					if ( $for_sale == 'yes' )
					{
						$prices_sub_query = array('relation' => 'OR');

						$price_from = get_post_meta( $atts['property_id'], '_price_from_actual', true );
						$price_to = get_post_meta( $atts['property_id'], '_price_to_actual', true );

						if ( !empty($price_from) || !empty($price_to) )
						{
							if ( empty($price_from) )
							{
								$price_from = $price_to;
							}
							if ( empty($price_to) )
							{
								$price_to = $price_from;
							}

							$lower_price_from = $price_from;
							$higher_price_from = $price_from;
							if ( isset($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] != '' && is_numeric($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] > 0 )
							{
								$lower_price_from = $price_from - ($price_from * (int)$atts['price_percentage_bounds'] / 100);
								$higher_price_from = $price_from + ($price_from * (int)$atts['price_percentage_bounds'] / 100);
							}

							$lower_price_to = $price_to;
							$higher_price_to = $price_to;
							if ( isset($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] != '' && is_numeric($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] > 0 )
							{
								$lower_price_to = $price_to - ($price_to * (int)$atts['price_percentage_bounds'] / 100);
								$higher_price_to = $price_to + ($price_to * (int)$atts['price_percentage_bounds'] / 100);
							}

							// where price from and price to not blank and price from -15%
							$price_sub_query = array();

							$price_sub_query[] = array(
								'key' => '_price_from_actual',
						        'value'   => array( '', 0 ),
						        'compare' => 'NOT IN'
							);
							$price_sub_query[] = array(
								'key' => '_price_to_actual',
						        'value'   => array( '', 0 ),
						        'compare' => 'NOT IN'
							);
							$price_sub_query[] = array(
								'key' 		=> '_price_to_actual',
								'value' 	=> $lower_price_from,
								'compare'   => '>=',
								'type'      => 'NUMERIC'
							);
							$price_sub_query[] = array(
								'key' 		=> '_price_from_actual',
								'value' 	=> $higher_price_to,
								'compare'   => '<=',
								'type'      => 'NUMERIC'
							);

							$prices_sub_query[] = $price_sub_query;
						}

						$sub_meta_query[] = array(
							array(
								'key' 		=> '_for_sale',
								'value' 	=> $for_sale,
							),
							$prices_sub_query
						);
					}
					elseif ( $to_rent == 'yes' )
					{
						$prices_sub_query = array('relation' => 'OR');

						$price_from = get_post_meta( $atts['property_id'], '_rent_from_actual', true );
						$price_to = get_post_meta( $atts['property_id'], '_rent_to_actual', true );

						if ( !empty($price_from) || !empty($price_to) )
						{
							if ( empty($price_from) )
							{
								$price_from = $price_to;
							}
							if ( empty($price_to) )
							{
								$price_to = $price_from;
							}

							$lower_price_from = $price_from;
							$higher_price_from = $price_from;
							if ( isset($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] != '' && is_numeric($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] > 0 )
							{
								$lower_price_from = $price_from - ($price_from * (int)$atts['price_percentage_bounds'] / 100);
								$higher_price_from = $price_from + ($price_from * (int)$atts['price_percentage_bounds'] / 100);
							}

							$lower_price_to = $price_to;
							$higher_price_to = $price_to;
							if ( isset($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] != '' && is_numeric($atts['price_percentage_bounds']) && $atts['price_percentage_bounds'] > 0 )
							{
								$lower_price_to = $price_to - ($price_to * (int)$atts['price_percentage_bounds'] / 100);
								$higher_price_to = $price_to + ($price_to * (int)$atts['price_percentage_bounds'] / 100);
							}

							// where price from and price to not blank and price from -15%
							$price_sub_query = array();

							$price_sub_query[] = array(
								'key' => '_rent_from_actual',
						        'value'   => array( '', 0 ),
						        'compare' => 'NOT IN'
							);
							$price_sub_query[] = array(
								'key' => '_rent_to_actual',
						        'value'   => array( '', 0 ),
						        'compare' => 'NOT IN'
							);
							$price_sub_query[] = array(
								'key' 		=> '_rent_to_actual',
								'value' 	=> $lower_price_from,
								'compare'   => '>=',
								'type'      => 'NUMERIC'
							);
							$price_sub_query[] = array(
								'key' 		=> '_rent_from_actual',
								'value' 	=> $higher_price_to,
								'compare'   => '<=',
								'type'      => 'NUMERIC'
							);

							$prices_sub_query[] = $price_sub_query;
						}

						$sub_meta_query[] = array(
							array(
								'key' 		=> '_to_rent',
								'value' 	=> $to_rent,
							),
							$prices_sub_query
						);
					}

					$meta_query[] = $sub_meta_query;
				}
			}

			if ( isset($atts['matching_address_field']) && in_array($atts['matching_address_field'], array( 'address_two', 'address_three', 'address_four' )) )
			{
				$address_field = get_post_meta( $atts['property_id'], '_' . $atts['matching_address_field'], true );

				$meta_query[] = array(
					'key' 		=> '_' . $atts['matching_address_field'],
					'value' 	=> $address_field,
				);
			}

			$args['meta_query'] = $meta_query;

			$tax_query = array();

			if ( isset($atts['availability_id']) && $atts['availability_id'] != '' )
			{
				$tax_query[] = array(
	                'taxonomy'  => 'availability',
	                'terms' => explode(",", $atts['availability_id']),
	                'compare' => 'IN',
	            );
			}

			if ( isset($atts['matching_address_field']) && $atts['matching_address_field'] == 'location' )
			{
				$term_list = wp_get_post_terms($atts['property_id'], 'location', array("fields" => "ids"));
            
	            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
	            {
	            	$tax_query[] = array(
		                'taxonomy'  => 'location',
		                'terms' => $term_list,
		                'compare' => 'IN',
		            );
	            }
			}

			if ( ! empty( $tax_query ) ) {
				$args['tax_query'] = $tax_query;
			}

			if ( isset($atts['orderby']) && $atts['orderby'] == 'date' )
			{
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = '_on_market_change_date';
			}

			$args['orderby'] .= ' post_title';

			ob_start();

			$properties = new WP_Query( apply_filters( 'propertyhive_shortcode_similar_properties_query', $args, $atts ) );

			$propertyhive_loop['columns'] = (int)$atts['columns'];

			if ( $properties->have_posts() ) : ?>

				<?php 
					ob_start();
					propertyhive_property_loop_start(); 
					$loop_start = ob_get_clean();
					$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
					echo $loop_start;
				?>

					<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

						<?php ph_get_template_part( 'content', 'property-featured' ); ?>

					<?php endwhile; // end of the loop. ?>

				<?php propertyhive_property_loop_end(); ?>

			<?php else: ?>

            	<?php echo $atts['no_results_output']; ?>

			<?php endif;

			wp_reset_postdata();
		}
		else
		{
			echo 'No property_id passed into similar_properties shortcode';
		}

		$shortcode_output = ob_get_clean();

		return apply_filters( 'propertyhive_similar_properties_shortcode_output', '<div class="propertyhive propertyhive-similar-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );
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
			'scrollwheel'   => 'true'
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
