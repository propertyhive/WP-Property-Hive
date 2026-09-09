<?php
/**
 * Single Property Thumbnails
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $propertyhive, $property;

if ( isset($images) && is_array($images) && !empty($images) ) {

	?>
	<div class="thumbnails flexslider" id="carousel">
	    
	    <ul class="slides">
	    <?php

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
		$loop = 0;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
		$columns = apply_filters( 'propertyhive_property_thumbnails_columns', 3 );

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
		foreach ($images as $image)
        {

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
            $classes = array();

			if ( $loop == 0 || $loop % $columns == 0 )
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$classes[] = 'first';

			if ( ( $loop + 1 ) % $columns == 0 )
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$classes[] = 'last';

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
			$image_class = esc_attr( implode( ' ', $classes ) );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The image producer escapes its attributes; preserve the trusted PHP thumbnail HTML filter.
			echo '<li>' . apply_filters( 'propertyhive_single_property_image_thumbnail_html', $image['image'], ( isset($image['attachment_id']) ? $image['attachment_id'] : '' ) , $post->ID, $image_class ) . '</li>';

			++$loop;
		}

	?>
	   </ul>
	   
	</div>
	<?php
}
