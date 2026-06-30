<?php
/**
 * Template Set Search Card Badges.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/search/portal-style-search-results/card-badges.php
 *
 * Available variables: $property, $badges, $template.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<span class="ph-template-badges">
	<?php foreach ( $badges as $badge ) : ?>
		<span class="ph-template-badge"><?php echo esc_html( $badge ); ?></span>
	<?php endforeach; ?>
</span>
