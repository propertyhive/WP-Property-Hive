<?php
/**
 * Single Property Price
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="price">

	<?php echo $property->get_formatted_price(); ?>
	
	<?php
       if ($property->department == 'residential-sales')
       {
           $term_list = wp_get_post_terms($post->ID, 'price_qualifier', array("fields" => "names"));
           if ( isset($term_list[0]) )
           {
	           echo '<span class="price-qualifier">' . $term_list[0] . '</span>';
	       }
       }
    ?>

</div>