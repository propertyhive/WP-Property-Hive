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

if ($property->department == 'commercial')
{
?>
<div class="floor-area">

	<?php echo $property->get_formatted_floor_area(); ?>

</div>
<?php
}
?>