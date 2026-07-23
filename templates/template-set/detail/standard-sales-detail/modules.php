<?php
/**
 * Template Set Property Detail Modules.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/modules.php
 *
 * Available variables: $property, $template, $facts, $location_label, $address, $documents, $office, $has_floorplan.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<section class="ph-template-modules" aria-label="<?php esc_attr_e( 'Property information', 'propertyhive' ); ?>">
	<?php if ( ! empty( $facts ) ) : ?>
		<article class="ph-template-module-card ph-template-module-facts">
			<h4><?php esc_html_e( 'At a glance', 'propertyhive' ); ?></h4>
			<ul class="ph-template-area-list">
				<?php foreach ( $facts as $fact ) : ?>
					<li><span><?php echo esc_html( $fact ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</article>
	<?php endif; ?>
	<?php if ( $has_floorplan ) : ?>
		<article class="ph-template-module-card ph-template-module-floorplan">
			<h4><?php esc_html_e( 'Floorplan', 'propertyhive' ); ?></h4>
			<p class="ph-template-module-foot"><?php esc_html_e( 'Floorplan available for this property.', 'propertyhive' ); ?></p>
		</article>
	<?php endif; ?>
	<?php if ( $location_label || $address ) : ?>
		<article class="ph-template-module-card ph-template-module-map">
			<h4><?php esc_html_e( 'Location', 'propertyhive' ); ?></h4>
			<?php if ( $location_label ) : ?>
				<div class="ph-template-module-map-surface" aria-hidden="true"><span class="ph-template-map-pin"></span><span class="ph-template-map-label"><?php echo esc_html( $location_label ); ?></span></div>
			<?php endif; ?>
			<?php if ( $address ) : ?>
				<p class="ph-template-module-foot"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
		</article>
	<?php endif; ?>
	<?php if ( ! empty( $documents ) ) : ?>
		<article class="ph-template-module-card ph-template-module-documents">
			<h4><?php esc_html_e( 'Documents and viewing', 'propertyhive' ); ?></h4>
			<div class="ph-template-doc-row">
				<?php foreach ( $documents as $document ) : ?>
					<span class="ph-template-doc-pill ph-template-doc-pill-<?php echo esc_attr( sanitize_html_class( $document['type'] ) ); ?>"><span class="ph-template-doc-icon" aria-hidden="true"></span><?php echo esc_html( $document['label'] ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php if ( $office ) : ?>
				<p class="ph-template-module-foot"><?php echo esc_html( sprintf( __( 'Available from %s.', 'propertyhive' ), $office ) ); ?></p>
			<?php endif; ?>
		</article>
	<?php endif; ?>
</section>
