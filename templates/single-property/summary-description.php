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
    
    <div class="summary-contents"><?php echo apply_filters('propertyhive_summary_description_nl2br', true) ? nl2br($summary) : $summary; ?></div>

</div>
<?php
}
?>