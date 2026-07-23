<?php
/**
 * Template Set Mobile Property Actions.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/mobile-cta.php
 *
 * Available variables: $property, $template, $button, $phone, $post_id, $shortlist_button.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<nav class="ph-template-mobile-cta" aria-label="<?php esc_attr_e( 'Property actions', 'propertyhive' ); ?>">
	<?php if ( $phone ) : ?>
		<a class="ph-template-button ph-template-button-secondary" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php esc_html_e( 'Call', 'propertyhive' ); ?></a>
	<?php endif; ?>
	<button type="button" class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" aria-haspopup="dialog" aria-controls="makeEnquiry<?php echo absint( $post_id ); ?>"><?php echo esc_html( $button ); ?></button>
	<?php if ( ! empty( $shortlist_button ) ) : ?>
		<?php
		echo wp_kses(
			$shortlist_button,
			array(
				'a' => array(
					'href'                  => true,
					'class'                 => true,
					'rel'                   => true,
					'data-add-to-shortlist' => true,
				),
			)
		);
		?>
	<?php endif; ?>
</nav>
