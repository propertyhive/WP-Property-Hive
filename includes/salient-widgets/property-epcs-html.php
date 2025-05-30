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
.epcs h4 { display:none; }
</style>
<?php
}

if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
{
    $epc_urls = $property->_epc_urls;
    if ( is_array($epc_urls) && !empty( $epc_urls ) )
    {
    	echo '<div class="epcs">';

            echo '<h4>' . esc_html(__( 'EPCs', 'propertyhive' )) . '</h4>';

            foreach ($epc_urls as $epc)
            {
            	echo '<a href="' . esc_url($epc['url']) . '" data-fancybox="epcs" rel="nofollow"><img src="' . esc_url($epc['url']) . '" alt=""></a>';
            }

        echo '</div>';
    }
}
else
{
	$epc_attachment_ids = $property->get_epc_attachment_ids();

	if ( !empty($epc_attachment_ids) )
	{
		echo '<div class="epcs">';

			echo '<h4>' . esc_html(__( 'EPCs', 'propertyhive' )) . '</h4>';

			foreach ( $epc_attachment_ids as $attachment_id )
			{
				if ( wp_attachment_is_image($attachment_id) )
                {
					echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="epcs" rel="nofollow"><img src="' . esc_url(wp_get_attachment_url($attachment_id)) . '" alt=""></a>';
				}
				else
				{
					echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow">' . esc_html(__( 'View EPC', 'propertyhive' )) . '</a>';
				}
			}

		echo '</div>';
	}
}