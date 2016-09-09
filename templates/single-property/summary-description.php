<?php
/**
 * Single Property Summary Description
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

$summary = get_the_excerpt();

if ( $summary != '' )
{
?>
<div class="summary">
    
    <h4><?php _e( 'Property Summary', 'propertyhive' ); ?></h4>
    
    <?php echo nl2br($summary); ?>

</div>
<?php
}
?>