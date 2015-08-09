<?php
/**
 * Single Property Thumbnails
 *
 * @author 		BIOSTALL
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $propertyhive, $property;

$gallery_attachments = $property->get_gallery_attachment_ids();

if ( !empty($gallery_attachments) ) {
	?>
	<div class="thumbnails flexslider" id="carousel">
	    
	    <ul class="slides">
	    <?php

		$loop = 0;
		$columns = apply_filters( 'propertyhive_property_thumbnails_columns', 3 );

		foreach ( $gallery_attachments as $attachment_id ) {
                
            $classes = array();

			if ( $loop == 0 || $loop % $columns == 0 )
				$classes[] = 'first';

			if ( ( $loop + 1 ) % $columns == 0 )
				$classes[] = 'last';

			$image_link = wp_get_attachment_url( $attachment_id );
            
			if ( ! $image_link )
				continue;

			$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_property_small_thumbnail_size', 'thumbnail' ) );
			$image_class = esc_attr( implode( ' ', $classes ) );
			$image_title = esc_attr( get_the_title( $attachment_id ) );

			echo '<li>'.apply_filters( 'propertyhive_single_property_image_thumbnail_html', $image, $attachment_id, $post->ID, $image_class ) . '</li>';

			++$loop;
		}

	?>
	   </ul>
	   
	</div>
	<?php
}