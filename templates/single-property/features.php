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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
$features = $property->get_features();

if ( !empty($features) )
{
?>
<div class="features">
    
    <h4><?php echo esc_html(__( 'Property Features', 'propertyhive' )); ?></h4>
    
    <ul>
<?php
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
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