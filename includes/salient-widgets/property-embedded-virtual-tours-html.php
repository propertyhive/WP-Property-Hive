<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	"show_title" => "yes", 
	"oembed" => "", 
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

if ( isset($atts['show_title']) && $atts['show_title'] != 'yes' )
{
?>
<style type="text/css">
.embedded-virtual-tours h4 { display:none; }
</style>
<?php
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$virtual_tours = $property->get_virtual_tours();

if ( !empty($virtual_tours) )
{
	echo '<div class="embedded-virtual-tours">';

		echo '<h4>' . esc_html__( 'Virtual Tours', 'propertyhive' ) . '</h4>';

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
		foreach ( $virtual_tours as $virtual_tour )
		{
			if ( isset($settings['oembed']) && $settings['oembed'] == 'yes' )
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
				$embed_code = wp_oembed_get($virtual_tour['url']);
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_oembed_get() uses WordPress provider trust and sanitization; preserve supported provider scripts and trusted PHP filters.
				echo $embed_code;
			}
			else
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
				$virtual_tour['url'] = preg_replace(
					"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
					"//www.youtube.com/embed/$2",
					$virtual_tour['url']
				);

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
				$virtual_tour['url'] = preg_replace(
					'#https?://(www\.)?youtube\.com/shorts/([^/?]+)#', 
					'//www.youtube.com/embed/$2', 
					$virtual_tour['url']
				);

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
				$virtual_tour['url'] = preg_replace(
		        	'/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/i',
		        	"//player.vimeo.com/video/$6",
		        	$virtual_tour['url']
		    	);

				echo '<iframe src="' . esc_url($virtual_tour['url']) . '" height="500" width="100%" allowfullscreen frameborder="0" allow="fullscreen"></iframe>';
			}
		}

	echo '</div>';
}
