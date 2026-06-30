<?php
/**
 * Template Set Featured Properties Module.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/module/featured-properties-homepage-module/featured-properties.php
 *
 * Available variables: $atts, $source, $search_html, $properties_html.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<section class="propertyhive ph-template-set ph-template-featured-module ph-home-template-featured-properties-homepage-module">
	<div class="ph-template-module-header">
		<?php if ( '' !== $atts['title'] ) : ?>
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $atts['intro'] ) : ?>
			<p><?php echo esc_html( $atts['intro'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( '' !== $search_html ) : ?>
		<div class="ph-template-module-search">
			<?php echo $search_html; ?>
		</div>
	<?php endif; ?>
	<?php echo $properties_html; ?>
	<?php if ( $atts['button_url'] && $atts['button_text'] ) : ?>
		<p class="ph-template-module-action"><a class="ph-template-button ph-template-button-primary" href="<?php echo esc_url( $atts['button_url'] ); ?>"><?php echo esc_html( $atts['button_text'] ); ?></a></p>
	<?php endif; ?>
</section>
