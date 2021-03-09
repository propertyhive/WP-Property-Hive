<?php
/**
 * Loop Floor Area
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;
?>
<div class="floor-area">

	<?php echo $property->get_formatted_floor_area(); ?>

</div>