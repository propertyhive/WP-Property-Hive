<?php
/**
 * Immersive Cinema contact panel.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/immersive-cinema-detail/contact-panel.php
 *
 * Available variables: $property, $post_id, $template, $button, $kicker, $hint, $is_demo, $phone, $email, $office, $office_alt, $address, $agent, $agent_role, $agent_initials, $portrait, $price_qualifier, $media_links, $shortlist_button, $share_button.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$shortlist_button = ! empty( $shortlist_button ) ? $shortlist_button : '';
$share_button     = ! empty( $share_button ) ? $share_button : '';
$allowed_action   = array(
	'a' => array(
		'href'                 => true,
		'class'                => true,
		'rel'                  => true,
		'data-add-to-shortlist' => true,
		'data-fancybox'        => true,
		'data-src'             => true,
		'data-type'            => true,
		'target'               => true,
	),
);
?>
<aside class="ph-template-detail-contact-card ph-template-contact-panel ph-template-contact-panel-immersive-cinema ph-template-cinema-contact" aria-label="<?php esc_attr_e( 'Enquire', 'propertyhive' ); ?>">
	<?php if ( $kicker ) : ?>
		<p class="ph-template-contact-kicker"><?php echo esc_html( $kicker ); ?></p>
	<?php endif; ?>
	<h2><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
	<div class="ph-template-contact-price-block">
		<?php if ( $price_qualifier ) : ?>
			<span class="ph-template-contact-price-qualifier"><?php echo esc_html( $price_qualifier ); ?></span>
		<?php endif; ?>
		<span class="ph-template-contact-price"><?php echo wp_kses_post( $property->get_formatted_price() ); ?></span>
	</div>
	<ul class="ph-template-cinema-facts">
		<?php if ( $property->bedrooms ) : ?>
			<li><span><?php esc_html_e( 'Bedrooms', 'propertyhive' ); ?></span><?php echo esc_html( (string) (int) $property->bedrooms ); ?></li>
		<?php endif; ?>
		<?php if ( $property->bathrooms ) : ?>
			<li><span><?php esc_html_e( 'Bathrooms', 'propertyhive' ); ?></span><?php echo esc_html( (string) (int) $property->bathrooms ); ?></li>
		<?php endif; ?>
		<?php if ( $property->reception_rooms ) : ?>
			<li><span><?php esc_html_e( 'Receptions', 'propertyhive' ); ?></span><?php echo esc_html( (string) (int) $property->reception_rooms ); ?></li>
		<?php endif; ?>
		<?php if ( $property->tenure ) : ?>
			<li><span><?php esc_html_e( 'Tenure', 'propertyhive' ); ?></span><?php echo esc_html( trim( wp_strip_all_tags( (string) $property->tenure ) ) ); ?></li>
		<?php endif; ?>
	</ul>
	<div class="ph-template-contact-actions">
		<button type="button" class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" aria-haspopup="dialog" aria-controls="makeEnquiry<?php echo absint( $post_id ); ?>"><?php echo esc_html( $button ); ?></button>
		<?php if ( $shortlist_button || $share_button ) : ?>
			<div class="ph-template-action-row">
				<?php if ( $shortlist_button ) : ?>
					<?php echo wp_kses( $shortlist_button, $allowed_action ); ?>
				<?php endif; ?>
				<?php if ( $share_button ) : ?>
					<?php echo wp_kses( $share_button, $allowed_action ); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</aside>
