<?php
/**
 * Portal Split property modules.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/conversion-first-sales-detail/modules.php
 *
 * Available variables: $property, $template, $facts, $rooms, $material, $features, $description, $overview, $location_label, $address, $documents, $office, $has_floorplan, $show_purchase_costs.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<section class="ph-template-modules ph-template-property-information ph-template-property-information-portal-split ph-template-modules-portal" aria-label="<?php esc_attr_e( 'Property information', 'propertyhive' ); ?>">
	<?php if ( ! empty( $features ) ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-features">
			<h2><?php esc_html_e( 'Key features', 'propertyhive' ); ?></h2>
			<ul>
				<?php foreach ( $features as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
		</article>
	<?php endif; ?>

	<?php if ( $overview ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-overview">
			<h2><?php esc_html_e( 'Overview', 'propertyhive' ); ?></h2>
			<p><?php echo esc_html( $overview ); ?></p>
		</article>
	<?php endif; ?>

	<?php if ( ! empty( $rooms ) || $description ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-rooms">
			<h2><?php esc_html_e( 'Full details', 'propertyhive' ); ?></h2>
			<?php if ( ! empty( $rooms ) ) : ?>
				<?php foreach ( $rooms as $room ) : ?>
					<p class="room">
						<?php if ( $room['name'] ) : ?><strong class="name"><?php echo esc_html( $room['name'] ); ?></strong><?php endif; ?>
						<?php if ( $room['dimensions'] ) : ?><span class="dimension"><?php echo esc_html( $room['dimensions'] ); ?></span><?php endif; ?>
						<?php if ( $room['description'] ) : ?><span class="description"><?php echo esc_html( $room['description'] ); ?></span><?php endif; ?>
					</p>
				<?php endforeach; ?>
			<?php else : ?>
				<?php echo wp_kses_post( $description ); ?>
			<?php endif; ?>
		</article>
	<?php endif; ?>

	<?php if ( $show_purchase_costs ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-costs">
			<h2><?php esc_html_e( 'Purchase costs', 'propertyhive' ); ?></h2>
			<div>
				<?php if ( shortcode_exists( 'stamp_duty_calculator' ) ) { echo do_shortcode( '[stamp_duty_calculator]' ); } ?>
				<?php if ( shortcode_exists( 'mortgage_calculator' ) ) { echo do_shortcode( '[mortgage_calculator]' ); } ?>
			</div>
		</article>
	<?php endif; ?>

	<?php if ( ! empty( $documents ) ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-documents">
			<h2><?php esc_html_e( 'Documents & media', 'propertyhive' ); ?></h2>
			<div class="ph-template-doc-row">
				<?php foreach ( $documents as $document ) : ?>
					<?php $document_class = 'ph-template-doc-pill ph-template-doc-pill-' . sanitize_html_class( $document['type'] ); ?>
					<?php if ( ! empty( $document['url'] ) ) : ?>
						<a class="<?php echo esc_attr( $document_class ); ?>" href="<?php echo esc_url( $document['url'] ); ?>"><?php echo esc_html( $document['label'] ); ?></a>
					<?php else : ?>
						<span class="<?php echo esc_attr( $document_class ); ?>"><?php echo esc_html( $document['label'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</article>
	<?php endif; ?>

	<?php if ( ! empty( $material ) ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-material">
			<h2><?php esc_html_e( 'Material information', 'propertyhive' ); ?></h2>
			<dl>
				<?php foreach ( $material as $item ) : ?>
					<div><dt><?php echo esc_html( $item['label'] ); ?></dt><dd><?php echo esc_html( $item['value'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</article>
	<?php endif; ?>

	<?php if ( $location_label || $address ) : ?>
		<article class="ph-template-module ph-template-module-block ph-template-module-location">
			<h2><?php esc_html_e( 'Location', 'propertyhive' ); ?></h2>
			<div class="ph-template-module-map-surface">
				<span class="ph-template-map-pin" aria-hidden="true"></span>
				<span class="ph-template-map-label"><?php echo esc_html( $location_label ? $location_label : $address ); ?></span>
			</div>
			<?php if ( $address ) : ?>
				<p><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
		</article>
	<?php endif; ?>
</section>
