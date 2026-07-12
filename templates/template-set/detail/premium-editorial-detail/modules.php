<?php
/**
 * Private Office property modules.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/premium-editorial-detail/modules.php
 *
 * Available variables: $property, $template, $facts, $rooms, $material, $features, $description, $overview, $location_label, $address, $documents, $office, $has_floorplan, $duet_images.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<main class="ph-template-modules ph-template-modules-editorial" aria-label="<?php esc_attr_e( 'Property information', 'propertyhive' ); ?>">
	<?php if ( $overview || $description || $rooms ) : ?><section class="ph-template-editorial-chapter"><span><?php esc_html_e( 'The house', 'propertyhive' ); ?></span><h2><?php esc_html_e( 'Rooms that keep their own company', 'propertyhive' ); ?></h2><?php if ( $overview ) : ?><p class="lede"><?php echo esc_html( $overview ); ?></p><?php elseif ( $description ) : ?><?php echo wp_kses_post( $description ); ?><?php endif; ?></section><?php endif; ?>
	<?php if ( $rooms ) : ?><section class="ph-template-editorial-rooms" aria-label="<?php esc_attr_e( 'Room by room', 'propertyhive' ); ?>"><?php foreach ( $rooms as $room ) : ?><p class="room"><?php if ( $room['name'] ) : ?><strong class="name"><?php echo esc_html( $room['name'] ); ?></strong><?php endif; ?><?php if ( $room['dimensions'] ) : ?><span class="dimension"><?php echo esc_html( $room['dimensions'] ); ?></span><?php endif; ?><?php if ( $room['description'] ) : ?><span class="description"><?php echo esc_html( $room['description'] ); ?></span><?php endif; ?></p><?php endforeach; ?></section><?php endif; ?>
	<?php if ( ! empty( $duet_images ) ) : ?>
		<div class="ph-template-editorial-duet" aria-hidden="true">
			<?php foreach ( $duet_images as $duet_image ) : ?>
				<img src="<?php echo esc_url( $duet_image['src'] ); ?>" alt="<?php echo esc_attr( $duet_image['alt'] ); ?>" loading="lazy">
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( $facts || $features ) : ?><section class="ph-template-editorial-particulars"><span><?php esc_html_e( 'Particulars', 'propertyhive' ); ?></span><?php if ( $facts ) : ?><dl><?php foreach ( $facts as $fact ) : ?><div><dt><?php echo esc_html( $fact['label'] ); ?></dt><i></i><dd><?php echo esc_html( $fact['value'] ); ?></dd></div><?php endforeach; ?></dl><?php endif; ?><?php if ( $features ) : ?><ul><?php foreach ( $features as $feature ) : ?><li><?php echo esc_html( $feature ); ?></li><?php endforeach; ?></ul><?php endif; ?></section><?php endif; ?>
	<?php if ( $material ) : ?><section class="ph-template-editorial-material"><span><?php esc_html_e( 'Material information', 'propertyhive' ); ?></span><dl><?php foreach ( $material as $item ) : ?><div><dt><?php echo esc_html( $item['label'] ); ?></dt><dd><?php echo esc_html( $item['value'] ); ?></dd></div><?php endforeach; ?></dl></section><?php endif; ?>
	<?php if ( $documents || $location_label || $address ) : ?>
		<section class="ph-template-editorial-appendix">
			<span><?php esc_html_e( 'Appendix', 'propertyhive' ); ?></span>
			<?php if ( $documents ) : ?>
				<div class="ph-template-doc-row">
					<?php foreach ( $documents as $document ) : ?>
						<?php
						$doc_type  = ! empty( $document['type'] ) ? sanitize_html_class( $document['type'] ) : '';
						$doc_class = 'ph-template-doc-pill' . ( $doc_type ? ' ph-template-doc-pill-' . $doc_type : '' );
						?>
						<?php if ( ! empty( $document['url'] ) ) : ?>
							<a class="<?php echo esc_attr( $doc_class ); ?>" href="<?php echo esc_url( $document['url'] ); ?>"><?php echo esc_html( $document['label'] ); ?></a>
						<?php else : ?>
							<span class="<?php echo esc_attr( $doc_class ); ?>"><?php echo esc_html( $document['label'] ); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $location_label || $address ) : ?>
				<div class="ph-template-module-map-surface">
					<span class="ph-template-map-pin"></span>
					<span class="ph-template-map-label"><?php echo esc_html( sprintf( __( '%s — precise location shared on enquiry', 'propertyhive' ), $location_label ? $location_label : $address ) ); ?></span>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</main>
