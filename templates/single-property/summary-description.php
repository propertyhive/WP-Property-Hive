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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
$summary = get_the_excerpt();

if ( $summary != '' )
{
?>
<div class="summary">
    
    <h4><?php echo esc_html(__( 'Property Summary', 'propertyhive' )); ?></h4>
    
    <div class="summary-contents"><?php echo wp_kses_post( apply_filters('propertyhive_summary_description_nl2br', true) ? ph_nl2br($summary) : $summary ); ?></div>

</div>
<?php
}
?>