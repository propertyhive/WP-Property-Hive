<?php
/**
 * Private Office contact letter.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/premium-editorial-detail/contact-panel.php
 *
 * Available variables: $property, $post_id, $template, $button, $hint, $is_demo, $phone, $email, $office, $office_alt, $address, $agent, $agent_role, $agent_initials, $portrait, $media_links, $shortlist_button.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="ph-template-detail-contact-card ph-template-editorial-letter" aria-label="<?php esc_attr_e( 'Private viewing', 'propertyhive' ); ?>"><div>
	<span><?php esc_html_e( 'By appointment', 'propertyhive' ); ?></span><h2><?php esc_html_e( 'Viewings are private, and unhurried', 'propertyhive' ); ?></h2>
	<?php // Letter shows a portrait only when a real negotiator photo exists — an initials circle reads like a label here. ?>
	<?php if ( $portrait ) : ?><span class="ph-template-contact-portrait ph-template-contact-portrait-image"><?php echo wp_kses_post( $portrait ); ?></span><?php endif; ?>
	<?php if ( $hint ) : ?><p><?php echo esc_html( $hint ); ?></p><?php endif; ?><?php if ( $agent ) : ?><p class="ph-template-letter-signature"><?php echo esc_html( $agent ); ?></p><?php endif; ?><?php if ( $agent_role ) : ?><small><?php echo esc_html( $agent_role ); ?></small><?php endif; ?>
	<div class="ph-template-contact-actions"><a class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" href="javascript:;"><?php esc_html_e( 'Arrange a private viewing', 'propertyhive' ); ?></a><?php if ( ! empty( $media_links ) ) : ?><a class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" href="javascript:;"><?php esc_html_e( 'Request the brochure', 'propertyhive' ); ?></a><?php endif; ?></div>
	<?php if ( $phone ) : ?><p class="ph-template-letter-quiet"><?php echo esc_html( sprintf( __( 'Or telephone %s', 'propertyhive' ), $phone ) ); ?></p><?php endif; ?>
</div></section>
