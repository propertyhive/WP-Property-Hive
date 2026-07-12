<?php
/**
 * Portal Split property modules.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/conversion-first-sales-detail/modules.php
 *
 * Available variables: $property, $template, $facts, $rooms, $material, $features, $description, $overview, $location_label, $address, $documents, $office, $has_floorplan.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<section class="ph-template-modules ph-template-modules-portal" aria-label="<?php esc_attr_e( 'Property information', 'propertyhive' ); ?>">
	<?php if ( ! empty( $features ) ) : ?><article class="ph-template-module-block ph-template-module-features"><h2><?php esc_html_e( 'Key features', 'propertyhive' ); ?></h2><ul><?php foreach ( $features as $feature ) : ?><li><?php echo esc_html( $feature ); ?></li><?php endforeach; ?></ul></article><?php endif; ?>
	<?php if ( $overview ) : ?><article class="ph-template-module-block"><h2><?php esc_html_e( 'Overview', 'propertyhive' ); ?></h2><p><?php echo esc_html( $overview ); ?></p></article><?php endif; ?>
	<?php if ( ! empty( $rooms ) || $description ) : ?><article class="ph-template-module-block ph-template-module-rooms"><h2><?php esc_html_e( 'Full details', 'propertyhive' ); ?></h2><?php if ( ! empty( $rooms ) ) : ?><?php foreach ( $rooms as $room ) : ?><p class="room"><?php if ( $room['name'] ) : ?><strong class="name"><?php echo esc_html( $room['name'] ); ?></strong><?php endif; ?><?php if ( $room['dimensions'] ) : ?><span class="dimension"><?php echo esc_html( $room['dimensions'] ); ?></span><?php endif; ?><?php if ( $room['description'] ) : ?><span class="description"><?php echo esc_html( $room['description'] ); ?></span><?php endif; ?></p><?php endforeach; ?><?php else : ?><?php echo wp_kses_post( $description ); ?><?php endif; ?></article><?php endif; ?>
	<?php if ( ( 'yes' === PH_Template_Set_Request_Context::get_portal_show_costs() || PH_Template_Set_Request_Context::is_template_editor_active() ) && ( shortcode_exists( 'stamp_duty_calculator' ) || shortcode_exists( 'mortgage_calculator' ) ) ) : ?><article class="ph-template-module-block ph-template-module-costs"><h2><?php esc_html_e( 'Purchase costs', 'propertyhive' ); ?></h2><div><?php if ( shortcode_exists( 'stamp_duty_calculator' ) ) { echo do_shortcode( '[stamp_duty_calculator]' ); } ?><?php if ( shortcode_exists( 'mortgage_calculator' ) ) { echo do_shortcode( '[mortgage_calculator]' ); } ?></div></article><?php endif; ?>
	<?php if ( ! empty( $documents ) ) : ?><article class="ph-template-module-block"><h2><?php esc_html_e( 'Documents & media', 'propertyhive' ); ?></h2><div class="ph-template-doc-row"><?php foreach ( $documents as $document ) : ?><?php if ( ! empty( $document['url'] ) ) : ?><a class="ph-template-doc-pill ph-template-doc-pill-<?php echo esc_attr( sanitize_html_class( $document['type'] ) ); ?>" href="<?php echo esc_url( $document['url'] ); ?>"><?php echo esc_html( $document['label'] ); ?></a><?php else : ?><span class="ph-template-doc-pill ph-template-doc-pill-<?php echo esc_attr( sanitize_html_class( $document['type'] ) ); ?>"><?php echo esc_html( $document['label'] ); ?></span><?php endif; ?><?php endforeach; ?></div></article><?php endif; ?>
	<?php if ( ! empty( $material ) ) : ?><article class="ph-template-module-block ph-template-module-material"><h2><?php esc_html_e( 'Material information', 'propertyhive' ); ?></h2><dl><?php foreach ( $material as $item ) : ?><div><dt><?php echo esc_html( $item['label'] ); ?></dt><dd><?php echo esc_html( $item['value'] ); ?></dd></div><?php endforeach; ?></dl></article><?php endif; ?>
	<?php if ( $location_label || $address ) : ?><article class="ph-template-module-block ph-template-module-location"><h2><?php esc_html_e( 'Location', 'propertyhive' ); ?></h2><div class="ph-template-module-map-surface"><span class="ph-template-map-pin"></span><span class="ph-template-map-label"><?php echo esc_html( $location_label ? $location_label : $address ); ?></span></div><?php if ( $address ) : ?><p><?php echo esc_html( $address ); ?></p><?php endif; ?></article><?php endif; ?>
</section>
