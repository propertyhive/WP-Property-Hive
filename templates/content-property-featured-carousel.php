<?php
/**
 * The template for displaying a single property within Featured Property Carousel section.
 *
 * Override this template by copying it to yourtheme/propertyhive/content-property-featured-carousel.php
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property, $propertyhive_loop;

// Store loop count we're currently on
if ( empty( $propertyhive_loop['loop'] ) )
	$propertyhive_loop['loop'] = 0;

// Ensure visibility
if ( ! $property )
	return;

// Increase loop count
++$propertyhive_loop['loop'];

// Extra post classes
$classes = array('clear');
if ( $property->featured == 'yes' ) // only ever would be wouldn't it?
    $classes[] = 'featured';
$classes[] = 'featured-carousel';
?>

<li <?php post_class( $classes ); ?>>

	<?php do_action( 'propertyhive_before_featured_carousel_loop_item' ); ?>

    <div class="thumbnail">
    	<a href="<?php the_permalink(); ?>">
    		<?php
    			/**
    			 * propertyhive_before_featured_carousel_loop_item_title hook
    			 *
    			 * @hooked propertyhive_template_loop_property_thumbnail - 10
    			 */
    			do_action( 'propertyhive_before_featured_carousel_loop_item_title' );
    		?>
        </a>
    </div>
    
    <div class="details">
    
    	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        
    	<?php
    		/**
    		 * propertyhive_after_featured_carousel_loop_item_title hook
    		 *
    		 * @hooked propertyhive_template_loop_price - 10
             * @hooked propertyhive_template_loop_summary - 20
             * @hooked propertyhive_template_loop_actions - 30
    		 */
    		do_action( 'propertyhive_after_featured_carousel_loop_item_title' );
    	?>
	
    </div>
    
	<?php do_action( 'propertyhive_after_featured_carousel_loop_item' ); ?>

</li>