<?php
/**
 * Single Property Description, also known as the full description
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

$description = $property->get_formatted_description();

if ( $description != '' )
{
?>
<div class="description">
    
    <h4><?php _e( 'Full Details', 'propertyhive' ); ?></h4>
    
    <?php echo $description; ?>

</div>
<?php
}
?>