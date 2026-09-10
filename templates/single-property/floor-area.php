<?php
/**
 * Single Property Price
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;
?>
<div class="floor-area">

	<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The formatter escapes built-in area text before its trusted PHP HTML filter.
    echo $property->get_formatted_floor_area(); ?>

</div>