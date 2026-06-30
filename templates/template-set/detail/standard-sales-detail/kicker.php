<?php
/**
 * Template Set Property Detail Kicker.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/kicker.php
 *
 * Available variables: $property, $template, $label.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<p class="ph-template-detail-kicker ph-template-detail-kicker-<?php echo esc_attr( sanitize_html_class( $template ) ); ?>"><?php echo esc_html( $label ); ?></p>
