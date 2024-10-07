<?php
require_once(__DIR__ ."/../class-ph-shortcode.php");

class PH_Shortcode_Featured_Properties extends PH_Shortcode{
     public function __construct(){
        parent::__construct("featured_properties", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){
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

		if ( isset($atts['pagination']) && $atts['pagination'] != '' )
		{
			propertyhive_pagination( $properties->max_num_pages );
		}

		wp_reset_postdata();

		$shortcode_output = ob_get_clean();

		return apply_filters( 'propertyhive_featured_properties_shortcode_output', '<div class="propertyhive propertyhive-featured-properties-shortcode columns-' . (int)$atts['columns'] . '">' . $shortcode_output . '</div>', $shortcode_output );

    }
}

new PH_Shortcode_Featured_Properties() ;