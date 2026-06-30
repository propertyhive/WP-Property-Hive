<?php
/**
 * Template Set Mobile Property Actions.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/mobile-cta.php
 *
 * Available variables: $property, $template, $button, $phone, $post_id.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="ph-template-mobile-cta" aria-label="<?php esc_attr_e( 'Property actions', 'propertyhive' ); ?>">
	<?php if ( $phone ) : ?>
		<a class="ph-template-button ph-template-button-secondary" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php esc_html_e( 'Call', 'propertyhive' ); ?></a>
	<?php endif; ?>
	<a class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" href="javascript:;"><?php echo esc_html( $button ); ?></a>
</div>
