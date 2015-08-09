<?php
/**
 * Single Property Rooms, also known as the full description
 *
 * @author      BIOSTALL
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

$rooms = $property->get_formatted_rooms();

if ( $rooms != '' )
{
?>
<div class="rooms">
    
    <h4><?php _e( 'Full Details', 'propertyhive' ); ?></h4>
    
    <?php echo $rooms; ?>

</div>
<?php
}
?>