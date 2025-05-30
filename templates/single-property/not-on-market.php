<?php
/**
 * Not On Market warning
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

if ( $property->on_market == 'yes' )
	return;
?>
<div class="alert alert-danger alert-box">
	<?php echo esc_html(__( 'This property is not currently available. It may be sold or temporarily removed from the market.', 'propertyhive' )); ?>
</div>