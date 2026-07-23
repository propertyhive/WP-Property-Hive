<?php
/**
 * Template Set Search Map Panel.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/search/portal-style-search-results/map-panel.php
 *
 * Available variables: $template, $search_view, $editor_active.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="ph-template-map-panel">
	<div class="ph-template-map-surface">
		<span class="ph-template-map-pin ph-template-map-pin-1"></span>
		<span class="ph-template-map-pin ph-template-map-pin-2"></span>
		<span class="ph-template-map-pin ph-template-map-pin-3"></span>
		<span class="ph-template-map-label"><?php esc_html_e( 'Map view', 'propertyhive' ); ?></span>
	</div>
</div>
