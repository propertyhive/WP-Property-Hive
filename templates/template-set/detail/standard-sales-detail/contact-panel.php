<?php
/**
 * Template Set Property Contact Panel.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/contact-panel.php
 *
 * Available variables: $property, $post_id, $template, $button, $hint, $is_demo, $phone, $email, $office, $office_alt, $address, $agent, $agent_role, $agent_initials, $portrait, $media_links, $shortlist_button.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<aside class="ph-template-detail-contact-card ph-template-contact-panel<?php echo $is_demo ? ' is-demo' : ''; ?>" aria-label="<?php esc_attr_e( 'Property contact', 'propertyhive' ); ?>">
	<?php if ( $agent || $portrait ) : ?>
		<div class="ph-template-contact-agent">
			<?php if ( $portrait ) : ?>
				<span class="ph-template-contact-portrait ph-template-contact-portrait-image"><?php echo wp_kses_post( $portrait ); ?></span>
			<?php elseif ( $agent ) : ?>
				<span class="ph-template-contact-portrait ph-template-contact-portrait-initials" aria-hidden="true"><?php echo esc_html( $agent_initials ); ?></span>
			<?php endif; ?>
			<span class="ph-template-contact-agent-meta">
				<?php if ( $agent ) : ?>
					<span class="ph-template-contact-agent-name"><?php echo esc_html( $agent ); ?></span>
				<?php endif; ?>
				<?php if ( $agent_role ) : ?>
					<span class="ph-template-contact-agent-role"><?php echo esc_html( $agent_role ); ?></span>
				<?php endif; ?>
			</span>
		</div>
	<?php endif; ?>
	<p class="ph-template-contact-kicker"><?php esc_html_e( 'Marketed by', 'propertyhive' ); ?></p>
	<h2><?php echo esc_html( $office_alt ); ?></h2>
	<?php if ( $address ) : ?>
		<p class="ph-template-contact-address"><?php echo esc_html( $address ); ?></p>
	<?php endif; ?>
	<?php if ( $hint ) : ?>
		<p class="ph-template-contact-hint"><?php echo esc_html( $hint ); ?></p>
	<?php endif; ?>
	<div class="ph-template-contact-actions">
		<?php if ( $phone ) : ?>
			<a class="ph-template-button ph-template-button-primary" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php esc_html_e( 'Call agent', 'propertyhive' ); ?></a>
		<?php endif; ?>
		<button type="button" class="ph-template-button <?php echo esc_attr( $phone ? 'ph-template-button-secondary' : 'ph-template-button-primary' ); ?>" data-fancybox data-src="#makeEnquiry<?php echo absint( $post_id ); ?>" aria-haspopup="dialog" aria-controls="makeEnquiry<?php echo absint( $post_id ); ?>"><?php echo esc_html( $button ); ?></button>
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
		<?php if ( $email ) : ?>
			<a class="ph-template-contact-link" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'Email agent', 'propertyhive' ); ?></a>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $media_links ) ) : ?>
		<ul class="ph-template-media-links">
			<?php foreach ( array_slice( $media_links, 0, 4 ) as $link ) : ?>
				<li><?php echo esc_html( $link['label'] ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</aside>
