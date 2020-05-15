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

if ( trim(strip_tags($description)) != '' )
{
?>
<div class="description">
    
    <h4><?php _e( 'Full Details', 'propertyhive' ); ?></h4>
    
    <div class="description-contents"><?php echo $description; ?></div>

</div>
<?php
}
?>