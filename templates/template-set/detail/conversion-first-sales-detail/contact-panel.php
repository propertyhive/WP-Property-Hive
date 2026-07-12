<?php
/**
 * Portal Split contact panel.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/conversion-first-sales-detail/contact-panel.php
 *
 * Available variables: $property, $post_id, $template, $button, $hint, $is_demo, $phone, $email, $office, $office_alt, $address, $agent, $agent_role, $agent_initials, $portrait, $price_qualifier, $media_links, $shortlist_button.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<aside class="ph-template-detail-contact-card ph-template-portal-contact<?php echo $is_demo ? ' is-demo' : ''; ?>" aria-label="<?php esc_attr_e( 'Enquire', 'propertyhive' ); ?>">
	<?php if ( $price_qualifier ) : ?><p class="ph-template-contact-price-qualifier"><?php echo esc_html( $price_qualifier ); ?></p><?php endif; ?>
	<div class="ph-template-contact-price"><?php echo wp_kses_post( $property->get_formatted_price() ); ?></div>
	<?php if ( $hint ) : ?><p class="ph-template-contact-hint"><?php echo esc_html( $hint ); ?></p><?php endif; ?>
	<div class="ph-template-contact-actions"><a class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" href="javascript:;"><?php echo esc_html( $button ); ?></a><?php if ( $email ) : ?><a class="ph-template-button ph-template-button-secondary" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'Email agent', 'propertyhive' ); ?></a><?php endif; ?><?php if ( $phone ) : ?><a class="ph-template-button ph-template-button-secondary" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php esc_html_e( 'Call agent', 'propertyhive' ); ?></a><?php endif; ?></div>
	<?php if ( $agent || $portrait || $office ) : ?><div class="ph-template-contact-agent"><?php if ( $portrait ) : ?><span class="ph-template-contact-portrait ph-template-contact-portrait-image"><?php echo wp_kses_post( $portrait ); ?></span><?php elseif ( $agent ) : ?><span class="ph-template-contact-portrait ph-template-contact-portrait-initials"><?php echo esc_html( $agent_initials ); ?></span><?php endif; ?><span class="ph-template-contact-agent-meta"><b><?php echo esc_html( $agent ? $agent : $office_alt ); ?></b><?php if ( $agent_role ) : ?><small><?php echo esc_html( $agent_role ); ?></small><?php endif; ?></span></div><?php endif; ?>
	<?php if ( $shortlist_button ) : ?><?php echo wp_kses( $shortlist_button, array( 'a' => array( 'href' => true, 'class' => true, 'rel' => true, 'data-add-to-shortlist' => true ) ) ); ?><?php endif; ?>
</aside>
