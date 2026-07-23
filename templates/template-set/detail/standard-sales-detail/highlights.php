<?php
/**
 * Template Set Property Detail Highlights.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/standard-sales-detail/highlights.php
 *
 * Available variables: $property, $template, $highlights.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<ul class="ph-template-detail-highlights ph-template-detail-highlights-<?php echo esc_attr( sanitize_html_class( $template ) ); ?>">
	<?php foreach ( $highlights as $highlight ) : ?>
		<li><span><?php echo esc_html( $highlight['label'] ); ?></span> <?php echo esc_html( $highlight['value'] ); ?></li>
	<?php endforeach; ?>
</ul>
