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

    <a href="<?php echo esc_url(get_permalink()); ?>" class="button" aria-label="<?php
        /* translators: %s: property title. */
        echo esc_attr( sprintf( __( 'More Details - %s', 'propertyhive' ), wp_strip_all_tags( get_the_title() ) ) );
    ?>"><?php echo esc_html(__( 'More Details', 'propertyhive' )); ?></a>	

</div>