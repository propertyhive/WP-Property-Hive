<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	"show_title" => "yes",  
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

if ( isset($atts['show_title']) && $atts['show_title'] != 'yes' )
{
?>
<style type="text/css">
.floorplans h4 { display:none; }
</style>
<?php
}

if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
{
    $floorplan_urls = $property->_floorplan_urls;
    if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
    {
    	echo '<div class="floorplans">';

            echo '<h4>' . __( 'Floorplans', 'propertyhive' ) . '</h4>';

            foreach ($floorplan_urls as $floorplan)
            {
            	echo '<a href="' . $floorplan['url'] . '" data-fancybox="floorplans" rel="nofollow"><img src="' . $floorplan['url'] . '" alt=""></a>';
            }

        echo '</div>';
    }
}
else
{
	$floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

	if ( !empty($floorplan_attachment_ids) )
	{
		echo '<div class="floorplans">';

			echo '<h4>' . __( 'Floorplans', 'propertyhive' ) . '</h4>';

			foreach ( $floorplan_attachment_ids as $attachment_id )
			{
				if ( wp_attachment_is_image($attachment_id) )
                {
					echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
				}
				else
				{
					echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View Floorplan', 'propertyhive' ) . '</a>';
				}
			}

		echo '</div>';
	}
}