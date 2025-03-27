<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-property-features-preview-template">

	<#
		var text_color        = params.text_color,
			text_transform    = '' !== params.text_transform ? params.text_transform : '',
			styleTag          = '',
			titleHTML 		  = '';

		if ( params.show_title && params.show_title == 'yes' )
		{
			titleHTML = '<h4>Features</h4>';
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

	<div class="fusion-module-property-features-preview">
		{{{titleHTML}}}
		<ul style="{{{ styleTag }}}">
			<li>Four bedrooms</li>
			<li>Private south facing garden</li>
			<li>Fireplace</li>
			<li>En-suite</li>
			<li>Cul-de-sac location</li>
		</ul>
	</div>

</script>
