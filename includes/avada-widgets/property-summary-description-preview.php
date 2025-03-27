<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-property-summary-description-preview-template">

	<#
		var text_color        = params.text_color,
			text_transform    = '' !== params.text_transform ? params.text_transform : '',
			styleTag          = '',
			titleHTML 		  = '';

		if ( params.show_title && params.show_title == 'yes' )
		{
			titleHTML = '<h4>Summary Description</h4>';
		}

		if ( 'none' !== text_transform ) {
			styleTag += 'text-transform: ' + text_transform + ';';
		}

		if ( text_color && ( -1 !== text_color.indexOf( 'var(--' ) ) ) {
			text_color = getComputedStyle( document.documentElement ).getPropertyValue( text_color.replace( 'var(', '' ).replace( ')', '' ) );
		}

		if ( text_color && ( -1 !== text_color.replace( /\s/g, '' ).indexOf( 'rgba(255,255,255' ) || '#ffffff' === text_color ) ) {
			text_color = '#dddddd';
		}

		if ( params.content_align && '' !== params.content_align ) {
			styleTag += 'text-align: ' + params.content_align + ';';
		}

		if ( params.sep_color && '' !== params.sep_color ) {
			styleTag += 'border-color: ' + params.sep_color + ';';
		}

		if ( text_color ) {
			styleTag += 'color: ' + text_color + ';';
		}
	#>

	<div class="fusion-module-property-summary-description-preview">
		{{{titleHTML}}}
		Nestled in a quiet, family-friendly neighborhood, this beautifully updated 3-bedroom, 2-bathroom home blends charm with modern convenience. With a spacious backyard, natural light throughout, and thoughtful design touches, it's move-in ready and waiting for you.
	</div>

</script>
