<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$property = new PH_Property((int)$property_id);

?>

<div class="thumbnail">
	<img src="<?php echo esc_url($property->get_main_photo_src()); ?>" alt="">
</div>

<div class="details">

	<div class="address"><a href="<?php echo esc_url(get_edit_post_link((int)$property_id)); ?>"><?php echo esc_html($property->get_formatted_summary_address()); ?></a></div>

	<div class="price"><?php echo $property->get_formatted_price(); ?></div>

</div>

<div style="clear:both"></div>