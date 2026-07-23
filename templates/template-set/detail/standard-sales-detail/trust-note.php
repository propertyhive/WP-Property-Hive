<?php
/**
 * Template Set Property Trust Note.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/trust-note.php
 *
 * Available variables: $property, $template.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<p class="ph-template-trust-note"><?php esc_html_e( 'Your details are sent to the agent handling this property.', 'propertyhive' ); ?></p>
