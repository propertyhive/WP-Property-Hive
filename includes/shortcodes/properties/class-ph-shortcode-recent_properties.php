<?php
require_once(__DIR__ ."/../class-ph-shortcode.php");

class PH_Shortcode_Recent_Properties extends PH_Shortcode{
     public function __construct(){
        parent::__construct("recent_properties", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){
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
				if ( isset($atts['carousel']) && !empty($atts['carousel']) )
				{
					$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
				}
				echo $loop_start;
			?>

				<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

					<?php ph_get_template_part( 'content', 'property-recent' ); ?>

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

		return apply_filters( 'propertyhive_recent_properties_shortcode_output', '<div class="propertyhive propertyhive-recent-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );

    }
}

new PH_Shortcode_Recent_Properties() ;