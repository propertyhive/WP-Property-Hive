<?php
/**
 * Template Set Search Intro.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/search/portal-style-search-results/intro.php
 *
 * Available variables: $template, $content.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<section class="ph-template-search-intro ph-template-search-intro-<?php echo esc_attr( sanitize_html_class( $template ) ); ?>">
	<span class="ph-template-search-kicker"><?php echo esc_html( $content['kicker'] ); ?></span>
	<div class="ph-template-search-intro-copy">
		<h2><?php echo esc_html( $content['title'] ); ?></h2>
		<p><?php echo esc_html( $content['body'] ); ?></p>
	</div>
	<?php if ( ! empty( $content['items'] ) ) : ?>
		<ul class="ph-template-search-intro-items">
			<?php foreach ( $content['items'] as $item ) : ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
