<?php
/**
 * Template Set Property Context Panel.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/context-panel.php
 *
 * Available variables: $property, $template, $panel.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<section class="ph-template-detail-context-panel ph-template-detail-context-panel-<?php echo esc_attr( sanitize_html_class( $template ) ); ?>">
	<p class="ph-template-panel-kicker"><?php echo esc_html( $panel['kicker'] ); ?></p>
	<h2><?php echo esc_html( $panel['title'] ); ?></h2>
	<p><?php echo esc_html( $panel['body'] ); ?></p>
	<?php if ( ! empty( $panel['items'] ) ) : ?>
		<dl class="ph-template-panel-list">
			<?php foreach ( $panel['items'] as $item ) : ?>
				<div>
					<dt><?php echo esc_html( $item['label'] ); ?></dt>
					<dd><?php echo esc_html( $item['value'] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>
</section>
