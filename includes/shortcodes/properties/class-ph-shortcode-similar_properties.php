<?php
require_once(__DIR__ ."/../class-ph-shortcode.php");

class PH_Shortcode_Similar_Properties extends PH_Shortcode{
     public function __construct(){
        parent::__construct("similar_properties", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){
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
					if ( isset($atts['carousel']) && !empty($atts['carousel']) )
					{
						$loop_start = str_replace("class=\"properties", "class=\"properties propertyhive-shortcode-carousel", $loop_start);
					}
					echo $loop_start;
				?>

					<?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

						<?php ph_get_template_part( 'content', 'property-featured' ); ?>

					<?php endwhile; // end of the loop. ?>

				<?php propertyhive_property_loop_end(); ?>

			<?php else: ?>

            	<p class="propertyhive-info no-results-message"><?php echo $atts['no_results_output']; ?></p>

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
}