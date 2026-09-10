<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$property = new PH_Property((int)$property_id);

?>

<div class="thumbnail">
	<img src="<?php echo esc_url($property->get_main_photo_src()); ?>" alt="">
</div>

<div class="details">

	<div class="address"><a href="<?php echo esc_url(get_edit_post_link((int)$property_id)); ?>"><?php echo esc_html($property->get_formatted_summary_address()); ?></a></div>

	<div class="price"><?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Price producers escape stored values before trusted currency, commercial and propertyhive_price_output HTML filters.
        echo $property->get_formatted_price(); ?></div>

</div>

<div style="clear:both"></div>