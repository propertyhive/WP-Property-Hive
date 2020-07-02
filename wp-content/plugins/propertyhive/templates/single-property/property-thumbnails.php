<?php
/**
 * Single Property Thumbnails
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $propertyhive, $property;

if ( isset($images) && is_array($images) && !empty($images) ) {

	?>
	<div class="thumbnails flexslider" id="carousel">
	    
	    <ul class="slides">
	    <?php

		$loop = 0;
		$columns = apply_filters( 'propertyhive_property_thumbnails_columns', 3 );

		foreach ($images as $image)
        {

            $classes = array();

			if ( $loop == 0 || $loop % $columns == 0 )
				$classes[] = 'first';

			if ( ( $loop + 1 ) % $columns == 0 )
				$classes[] = 'last';

			$image_class = esc_attr( implode( ' ', $classes ) );

			echo '<li>' . apply_filters( 'propertyhive_single_property_image_thumbnail_html', $image['image'], ( isset($image['attachment_id']) ? $image['attachment_id'] : '' ) , $post->ID, $image_class ) . '</li>';

			++$loop;
		}

	?>
	   </ul>
	   
	</div>
	<?php
}