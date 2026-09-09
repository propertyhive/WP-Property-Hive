<?php
/**
 * Single Property Price
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="price">

	<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Price producers escape stored values before trusted currency, commercial and propertyhive_price_output HTML filters.
        echo $property->get_formatted_price(); ?>
	
	<?php
       	if ( $price_qualifier != '' )
        {
        	echo ' <span class="price-qualifier">' . esc_html($price_qualifier) . '</span>';
       	}

       	if ( $fees != '' )
        {
            echo ' <span class="lettings-fees"><a data-fancybox data-src="#propertyhive_lettings_fees_popup" href="javascript:;">' . esc_html(__( 'Tenancy Info', 'propertyhive' )) . '</a></span>';

            echo '<div id="propertyhive_lettings_fees_popup" style="display:none; max-width:500px;"><h3>' . esc_html(__( 'Tenancy Info', 'propertyhive' )) . '</h3>' . wp_kses_post( $fees ) . '</div>';
        }
    ?>

</div>