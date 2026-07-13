<?php
/**
 * Private Office masthead brief.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/premium-editorial-detail/highlights.php
 *
 * Available variables: $property, $template, $highlights, $show_brief.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $highlights[0]['value'] ) || ! $show_brief ) {
	return;
}
?>
<p class="ph-template-editorial-brief"><?php echo esc_html( $highlights[0]['value'] ); ?></p>
