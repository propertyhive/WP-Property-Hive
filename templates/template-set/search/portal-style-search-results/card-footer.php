<?php
/**
 * Template Set Search Card Footer.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/search/portal-style-search-results/card-footer.php
 *
 * Available variables: $property, $facts, $phone, $office, $show_branch, $shortlist_button, $template.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="ph-template-card-footer">
	<?php if ( ! empty( $facts ) ) : ?>
		<ul class="ph-template-facts">
			<?php foreach ( $facts as $fact ) : ?>
				<li><span><?php echo esc_html( $fact['label'] ); ?></span> <?php echo esc_html( $fact['value'] ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if ( $show_branch && ( $office || $phone ) ) : ?>
		<div class="ph-template-card-branch">
			<?php if ( $office ) : ?>
				<span><?php echo esc_html( $office ); ?></span>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $shortlist_button ) ) : ?>
		<div class="ph-template-card-actions">
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
		</div>
	<?php endif; ?>
</div>
