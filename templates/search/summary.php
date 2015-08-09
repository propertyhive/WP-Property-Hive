<?php
/**
 * Loop Summary
 *
 * @author 		BIOSTALL
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;
?>
<div class="summary">

	<?php echo $property->post_excerpt; ?>

</div>