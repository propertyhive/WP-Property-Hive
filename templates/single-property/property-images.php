<?php
/**
 * Single Property Images
 *
 * @author 		BIOSTALL
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $propertyhive, $property;

$gallery_attachments = $property->get_gallery_attachment_ids();
?>
<div class="images">
    
	<?php
		if ( !empty($gallery_attachments) ) {
            
            echo '<div id="slider" class="flexslider"><ul class="slides">';
            
            foreach ($gallery_attachments as $gallery_attachment)
            {
    			 $image_title = esc_attr( get_the_title( $gallery_attachment ) );
    			 $image_link  = wp_get_attachment_url( $gallery_attachment );
         
			     echo '<li>' . apply_filters( 'propertyhive_single_property_image_html', sprintf( '<a href="%s" class="propertyhive-main-image" title="%s" data-rel="prettyPhoto[ph_photos]"><img src="%s" alt="%s"></a>', $image_link, $image_title, $image_link, $image_title ), $post->ID ) . '</li>';
            }
            
            echo '</ul></div>';
            
		} else {

			echo apply_filters( 'propertyhive_single_property_image_html', sprintf( '<img src="%s" alt="Placeholder" />', ph_placeholder_img_src() ), $post->ID );

		}
	?>

	<?php do_action( 'propertyhive_product_thumbnails' ); ?>

</div>
