<?php
/**
 * Loop Price
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;
?>
<div class="price">

	<?php echo $property->get_formatted_price(); ?>
	
	<?php
        if ( $price_qualifier != '' )
        {
            echo ' <span class="price-qualifier">' . esc_html($price_qualifier) . '</span>';
        }

        if ( $fees != '' )
        {
            echo ' <span class="lettings-fees"><a data-fancybox data-src="#propertyhive_lettings_fees_popup" href="javascript:;">' . esc_html(__( 'Tenancy Info', 'propertyhive' )) . '</a></span>';

            echo '<div id="propertyhive_lettings_fees_popup" style="display:none; max-width:500px;"><h3>' . esc_html(__( 'Tenancy Info', 'propertyhive' )) . '</h3>' . $fees . '</div>';
        }
	?>

</div>