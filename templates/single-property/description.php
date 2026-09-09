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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
$description = $property->get_formatted_description();

if ( trim(wp_strip_all_tags($description)) != '' )
{
?>
<div class="description">
    
    <h4><?php echo esc_html(__( 'Full Details', 'propertyhive' )); ?></h4>
    
    <div class="description-contents"><?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Stored description fields are sanitized before the trusted propertyhive_get_detail and propertyhive_description_output HTML extension hooks; preserve their embed output.
        echo $description;
    ?></div>

</div>
<?php
}
?>