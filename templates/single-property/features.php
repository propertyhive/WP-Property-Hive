<?php
/**
 * Single Property Features
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

$features = $property->get_features();

if ( !empty($features) )
{
?>
<div class="features">
    
    <h4><?php echo esc_html(__( 'Property Features', 'propertyhive' )); ?></h4>
    
    <ul>
<?php
    foreach ($features as $feature)
    {
?>
        <li><?php echo esc_html($feature); ?></li>
<?php
    }
?>
    </ul>

</div>
<?php
}
?>