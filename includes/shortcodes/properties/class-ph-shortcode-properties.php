<?php
require_once(__DIR__ ."/../class-ph-shortcode.php");

class PH_Shortcode_Properties extends PH_Shortcode{
     public function __construct(){
        parent::__construct("properties", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

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

		if ( isset($atts['department']) && ( $base_department == 'residential-sales' || $base_department == 'residential-lettings' ) && isset($atts['minimum_price']) && $atts['minimum_price'] != '' )
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

        if ( isset($atts['department']) && ( $base_department == 'residential-sales' || $base_department == 'residential-lettings' ) && isset($atts['maximum_price']) && $atts['maximum_price'] != '' )
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

        do_action('propertyhive_shortcode_properties_before_catalog_ordering', $atts);

		if ( isset($atts['show_order']) && $atts['show_order'] != '' )
		{
			list( $args, $orderby ) = self::get_show_order_args( $atts, $args );

			propertyhive_catalog_ordering( $atts['department'], $orderby );
		}

		do_action('propertyhive_shortcode_properties_after_catalog_ordering', $atts);

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

        do_action('propertyhive_shortcode_properties_after_result_count', $atts);

		$propertyhive_loop['columns'] = (int)$atts['columns'];

		if ( $properties->have_posts() ) : ?>

			<?php 
				ob_start();
				propertyhive_property_loop_start(); 
				$loop_start = ob_get_clean();
				if ( isset($atts['carousel']) && !empty($atts['carousel']) )
				{
					$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
				}
				echo $loop_start;
			?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php propertyhive_property_loop_end(); ?>

		<?php else: ?>

            <p class="propertyhive-info no-results-message"><?php echo $atts['no_results_output']; ?></p>

		<?php endif;

		if ( isset($atts['pagination']) && $atts['pagination'] != '' )
		{
			propertyhive_pagination( $properties->max_num_pages );
		}

		wp_reset_postdata();

		$shortcode_output = ob_get_clean();

		return apply_filters( 'propertyhive_properties_shortcode_output', '<div class="propertyhive propertyhive-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );
    }
}

new PH_Shortcode_Properties();