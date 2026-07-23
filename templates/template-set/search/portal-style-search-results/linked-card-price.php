<?php
/**
 * Template Set Linked Search Card Price.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/search/portal-style-search-results/linked-card-price.php
 *
 * Available variables: $property, $price, $price_qualifier, $fees, $permalink.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="price">
	<a href="<?php echo esc_url( $permalink ); ?>">
		<?php echo wp_kses_post( $price ); ?>
		<?php if ( '' !== $price_qualifier ) : ?>
			<span class="price-qualifier"><?php echo esc_html( $price_qualifier ); ?></span>
		<?php endif; ?>
	</a>
	<?php if ( '' !== $fees ) : ?>
		<span class="lettings-fees"><a data-fancybox data-src="#propertyhive_lettings_fees_popup" href="javascript:;"><?php esc_html_e( 'Tenancy Info', 'propertyhive' ); ?></a></span>
		<div id="propertyhive_lettings_fees_popup" style="display:none; max-width:500px;"><h3><?php esc_html_e( 'Tenancy Info', 'propertyhive' ); ?></h3><?php echo wp_kses_post( $fees ); ?></div>
	<?php endif; ?>
</div>
