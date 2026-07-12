<?php
/**
 * Immersive Cinema similar properties.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/immersive-cinema-detail/similar-properties.php
 *
 * Available variables: $property, $template, $cards, $layout, $image_style.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<section class="ph-template-similar-properties ph-template-similar-layout-<?php echo esc_attr( sanitize_html_class( $layout ) ); ?> ph-template-similar-images-<?php echo esc_attr( sanitize_html_class( $image_style ) ); ?>" aria-label="<?php esc_attr_e( 'Similar properties', 'propertyhive' ); ?>" data-ph-recommended-properties>
	<div class="ph-template-section-heading">
		<h2><?php esc_html_e( 'Similar properties', 'propertyhive' ); ?></h2>
	</div>
	<div class="ph-template-similar-grid" data-ph-recommended-grid>
		<?php foreach ( $cards as $card ) : ?>
			<article class="ph-template-similar-card" data-ph-recommended-card data-ph-recommended-index="<?php echo esc_attr( (string) $card['index'] ); ?>"<?php echo $card['hidden'] ? ' hidden' : ''; ?>>
				<?php if ( $card['image'] ) : ?>
					<a href="<?php echo esc_url( $card['url'] ); ?>"><img src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" loading="lazy"></a>
				<?php endif; ?>
				<div>
					<h3><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h3>
					<?php if ( $card['price'] ) : ?>
						<p><?php echo wp_kses_post( $card['price'] ); ?></p>
					<?php endif; ?>
					<?php if ( $card['facts'] ) : ?>
						<span><?php echo esc_html( $card['facts'] ); ?></span>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
