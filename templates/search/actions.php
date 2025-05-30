<?php
/**
 * Loop Actions
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;
?>
<div class="actions">

    <a href="<?php echo esc_url(get_permalink()); ?>" class="button"><?php echo esc_html(__( 'More Details', 'propertyhive' )); ?></a>	

</div>